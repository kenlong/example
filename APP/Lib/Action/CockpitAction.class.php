<?php
class CockpitAction extends Action
{
	public function index()
	{
		$company = $_REQUEST["company"];
		$group = $_REQUEST["group"];
		$dept = $_REQUEST["dept"];
		$year = $_REQUEST["year"];
		$month = $_REQUEST["month"];

		//		使用session不行,这里产生一个sessionid,下面调用时又产生一个sessionid,不知道为什么
		//		Session::set("company",$company);
		//		Session::set("group",$group);
		//		Session::set("year",$year);
		//		Session::set("month",$month);

		Cookie::set("company",$company);
		Cookie::set("group",$group);

		$this->display();
	}

	/**
	 * 获取部门列表
	 */
	public function deptList()
	{
		$company = Cookie::get("company");
		$group = Cookie::get("group");

		Cookie::set("company",'');
		Cookie::set("group",'');

		//$company = '101';
		//$group = '10101';

		$result  = "<Company>$company</Company>";
		$result .= "<Group>$group</Group>";

		if($group)
		{
			$model = new DeptModel();
			$list = $model->query("select deptno,deptname from Dept where groups = '$group'");
			if(count($list))
			{
				$result .= "<DeptList>";

				for($i=0; $i<count($list); $i++)
				{
					$result .= "<Dept>";
					$result .= "<DeptNo>".$list[$i]["deptno"]."</DeptNo>";
					$result .= "<DeptName>".mb_convert_encoding($list[$i]["deptname"],"UTF-8","GB2312")."</DeptName>";
					$result .= "</Dept>";
				}

				$result .= "</DeptList>";
			}
		}
		
		print_r($this->createResult($result));
	}

	/**
	 * 请求数据
	 */
	public function request()
	{
		$company = $_REQUEST["company"];
		$group = $_REQUEST["group"];
		$dept = $_REQUEST["dept"];
		$year = $_REQUEST["year"];
		$month = $_REQUEST["month"];
		
		if(!$dept)
		{
			print_r($this->createResult(''));
		}
		else
		{
			//收入情况
			$income = $this->calIncome($company,$group,$dept,$year,$month);
			$cost = $this->calCost($company,$group,$dept,$year,$month);
			$profit = $income - $cost ;

			if($income/10000 >= 1)
			{
				$unit1 = "万元";
				$income = round($income/10000,2);
				$cost = round($cost/10000,2);
				$profit = round($profit/10000,2);
			}
			else
			{
				$unit1 = "元";
				$income = round($income,2);
				$cost = round($cost,2);
				$profit = round($profit,2);
			}

			//负债情况
			$shouldget = $this->calShouldget($company,$group,$dept,$year,$month);
			$shouldpay = $this->calShouldPay($company,$group,$dept,$year,$month);
			$balance  = $shouldget - $shouldpay;

			if($shouldget/10000 >= 1 or $shouldpay/10000 >=1 )
			{
				$unit2 = "万元";
				$shouldget = round($shouldget/10000,2);
				$shouldpay = round($shouldpay/10000,2);
				$balance = round($balance/10000,2);
			}
			else
			{
				$unit2 = "元";
				$shouldget = round($shouldget,2);
				$shouldpay = round($shouldpay,2);
				$balance = round($balance,2);
			}

			$result  =  "<income><unit>$unit1</unit><a>$income</a><b>$cost</b><c>$profit</c></income>";
			$result .= "<account><unit>$unit2</unit><a>$shouldget</a><b>$shouldpay</b><c>$balance</c></account>";

			//可用现金
			$this->calCash($company,$group,$dept,$year,$month,$total,$used,$balance);
			if($total/10000 >= 1 )
			{
				$unit = "万元";
				$total = round($total/10000,2);
				$used = round($used/10000,2);
				$balance = round($balance/10000,2);
			}
			else 
			{
				$unit = "元";
				$total = round($total,2);
				$used = round($used,2);
				$balance = round($balance,2);
			}
						
			$result .= "<cash><unit>$unit</unit><a>$total</a><b>$used</b><c>$balance</c></cash>";
			
			print_r($this->createResult($result));
		}
	}

	/**
	 * 生成返回数据格式
	 *
	 * @param string $data
	 * @return string
	 */
	private function createResult($data)
	{
		$result  = "<?xml version='1.0' encoding='UTF-8'?>";
		$result .= "<result>$data</result>";

		return $result;
	}


	/**
	 * 计算本期销售收入
	 *
	 * @param string $company
	 * @param string $group
	 * @param string $dept
	 * @param string $year
	 * @param string $month
	 * @return number
	 */
	private function calIncome($company,$group,$dept,$year,$month)
	{
		$model = new DeptModel();

		//本期出港所开且已经配载的单据的收入
		$sql = "select sum(airwaybill.money) as income from airwaybill,peihuo where airwaybill.sysdno = peihuo.sysdno and airwaybill.deptno = '$dept' and peihuo.outport = 1 and airwaybill.cancel = 0 and year(airwaybill.dbilldate) = $year and month(airwaybill.dbilldate) = $month";
		$result = $model->query($sql);
		$income = $result[0]["income"]?$result[0]["income"]:0;
		
		//本期进港收入
		$sql = "select sum(income) as income from financeincome where deptno = '$dept' and outport = 0 and year(billdate) = $year and month(billdate) = $month";
		$result = $model->query($sql);
		$income += $result[0]["income"]?$result[0]["income"]:0;

		return $income;
	}

	/**
	 * 计算本期销售成本
	 *
	 * @param string $company
	 * @param string $group
	 * @param string $dept
	 * @param string $year
	 * @param string $month
	 * @return number
	 */
	private function calCost($company,$group,$dept,$year,$month)
	{
		$model = new DeptModel();

		//本期出港所开且已经配载单据的成本
		$sql = "select sum(airwaybill.cost) as cost from airwaybill,peihuo where airwaybill.sysdno = peihuo.sysdno and airwaybill.deptno = '$dept' and peihuo.outport = 1 and airwaybill.cancel = 0  and year(airwaybill.dbilldate) = $year and month(airwaybill.dbilldate) = $month";
		$result = $model->query($sql);
		$cost = $result[0]["cost"]?$result[0]["cost"]:0;

		//本期进港成本
		$sql = "select sum(cost) as cost from financecost where deptno = '$dept' and outport = 0 and year(billdate) = $year and month(billdate) = $month";
		$result = $model->query($sql);
		$cost += $result[0]["cost"]?$result[0]["cost"]:0;

		return $cost;
	}

	/**
	 * 计算应收账款
	 * 应收客户运费,应收网络公司到付运费,应收网络公司到付货款,应收承运人到付运费,应收承运人到付货款,应收合作分成,应收合作分成到付运费,应收其他
	 * 
	 * @param string $company
	 * @param string $group
	 * @param string $dept
	 * @param string $year
	 * @param string $month
	 * @return number
	 */
	private function calShouldget($company,$group,$dept,$year,$month)
	{
		$model = new DeptModel();

		//1.应收托运人运费
		$sql = "select sum(iaccount) as shouldget from airwaybill where airwaybill.deptno = '$dept' and airwaybill.iaccount <> 0 and airwaybill.geted <> 1 and airwaybill.cancel = 0 and isnull(airwaybill.fromagency,0) = 0";
		$result = $model->query($sql);
		$shouldget = $result[0]["shouldget"]?$result[0]["shouldget"]:0;

		//2.应收网络公司
		//2.1.我方配载
		$sql = "SELECT sum(airwaybill.getmoney + airwaybill.icollect + case when peihuo.deptno = airwaybill.getdeliverdeptno then airwaybill.GetDeliverMoney else 0 end + case when peihuo.deptno = airwaybill.gettransferdeptno then airwaybill.GetTransferMoney else 0 end ) as shouldget
				  FROM peihuo, airwaybill
				 WHERE ( peihuo.sysdno = airwaybill.sysdno ) AND	
				 	  ( ISNULL(peihuo.indeptno,'') <> '' ) AND
				      (  peihuo.stype = '配货' OR ( peihuo.stype = '派送' and airwaybill.arrivedpaytype = '现金') )  AND 
					  ( peihuo.valid = 1 ) AND ( peihuo.separate = 0 ) AND 
					  ( ( airwaybill.getmoney + airwaybill.icollect + case when peihuo.deptno = airwaybill.getdeliverdeptno then AirWayBill.GetDeliverMoney else 0 end + case when peihuo.deptno = airwaybill.gettransferdeptno then AirWayBill.GetTransferMoney else 0 end ) >0 ) AND 
					  ( peihuo.deptno = '$dept' ) ";
		$result = $model->query($sql);
		$shouldget += $result[0]["shouldget"]?$result[0]["shouldget"]:0;

		//2.2.对方配载:应收派送/中转费
		$sql = "SELECT sum( case when dnstocks.placeno = '' then airwaybill.FTransferMoney + airwaybill.FDeliverMoney else 0 end ) as shouldget
				  FROM peihuo,	airwaybill,	dnstocks		
				 WHERE ( peihuo.sysdno = airwaybill.sysdno ) AND	
					   ( dnstocks.dno = peihuo.sysdno AND dnstocks.deptno = peihuo.indeptno ) AND 
					   ( ISNULL(peihuo.indeptno,'') <> '' AND peihuo.stype = '配货') AND 
					   ( peihuo.valid = 1 ) AND ( peihuo.separate = 0 ) AND 
					   ( ( case when dnstocks.placeno = '' then airwaybill.FTransferMoney + airwaybill.FDeliverMoney else 0 end ) >0 ) AND 
						( peihuo.indeptno = '$dept' ) ";
		$result = $model->query($sql);
		$shouldget += $result[0]["shouldget"]?$result[0]["shouldget"]:0;

		//2.3.我方开的进港单:应收中转派送费
		$sql = "SELECT sum(airwaybill.ftransfermoney + airwaybill.fdelivermoney) as shouldget
				  FROM airwaybill	
				 WHERE ( airwaybill.billtype = 'IN' ) AND 
					   ( ( airwaybill.ftransfermoney + airwaybill.fdelivermoney ) <> 0 ) AND 
					   ( airwaybill.deptno = '$dept' ) ";
		$result = $model->query($sql);
		$shouldget += $result[0]["shouldget"]?$result[0]["shouldget"]:0;

		//3应收承运人
		//3.1.应收到付款
		$sql =  "SELECT sum(airwaybill.getmoney) as shouldget
				   FROM peihuo,	airwaybill	
				  WHERE ( peihuo.sysdno = airwaybill.sysdno ) AND	
					    ( (ISNULL(PEIHUO.IndeptNo,'') = '' AND PEIHUO.Stype = '配货' AND 
				   		  ISNULL(PEIHUO.SYSZNO,'') <> '' AND PEIHUO.Valid = 1 ) OR  
				 	     (peihuo.Stype = '派送' AND airwaybill.arrivedpaytype = '现金' ) ) AND 
						 ( (airwaybill.getmoney ) >0 ) AND 
						 ( peihuo.deptno = '$dept' ) ";
		$result = $model->query($sql);
		$shouldget += $result[0]["shouldget"]?$result[0]["shouldget"]:0;

		//3.2.应收到付运费
		$sql = 	"SELECT sum( airwaybill.icollect + case when peihuo.deptno = airwaybill.getdeliverdeptno then AirWayBill.GetDeliverMoney else 0 end + case when peihuo.deptno = airwaybill.gettransferdeptno then AirWayBill.GetTransferMoney else 0 end ) as shouldget
				   FROM peihuo,	airwaybill	
				  WHERE ( peihuo.sysdno = airwaybill.sysdno ) AND	
				  		( (ISNULL(PEIHUO.IndeptNo,'') = '' AND PEIHUO.Stype = '配货' AND PEIHUO.Type <> 'sell' AND 
						  ISNULL(PEIHUO.SYSZNO,'') <> '' AND PEIHUO.Valid = 1 ) OR  
				  		  (peihuo.Stype = '派送' AND airwaybill.arrivedpaytype = '现金' ) ) AND 
				  		( (airwaybill.icollect + case when peihuo.deptno = airwaybill.getdeliverdeptno then AirWayBill.GetDeliverMoney else 0 end + case when peihuo.deptno = airwaybill.gettransferdeptno then AirWayBill.GetTransferMoney else 0 end ) >0 ) AND 
				  		( peihuo.deptno = '$dept' ) ";
		$result = $model->query($sql);
		$shouldget += $result[0]["shouldget"]?$result[0]["shouldget"]:0;

		//4.合作分成
		//4.1.代收货款 配载
		$sql =  "SELECT sum(airwaybill.getmoney) as shouldget
				   FROM peihuo,	airwaybill	
				  WHERE ( peihuo.sysdno = airwaybill.sysdno ) AND	
				  	    ( ISNULL(peihuo.indeptno,'') <> '' AND peihuo.stype = '配货') AND 
						( peihuo.valid = 1 ) AND ( peihuo.separate = 1 ) AND 
					    ( airwaybill.getmoney >0 ) AND 
						( peihuo.deptno = '$dept' ) ";
		$result = $model->query($sql);
		$shouldget += $result[0]["shouldget"]?$result[0]["shouldget"]:0;

		//4.2.货代分成 应收运费分成(没对账部分)
		$sql =  "	SELECT (CASE WHEN ( SUM(airwaybill.freight - airwaybill.discount)*(100 - separatesetup.separate)/100 + SUM(airwaybill.ftransfermoney + airwaybill.fdelivermoney) - SUM(airwaybill.icollect) ) > 0 THEN  ( ROUND(SUM(airwaybill.freight - airwaybill.discount)*(100 - separatesetup.separate)/100,2) + SUM(airwaybill.ftransfermoney + airwaybill.fdelivermoney) - SUM(airwaybill.icollect) ) ELSE 0 END ) as shouldget 
					  FROM airwaybill, separatesetup 
					 WHERE ( airwaybill.takeforwho = separatesetup.departure ) AND 
							 ( airwaybill.deptno = separatesetup.destination) AND 
							 ( ISNULL(AirWayBill.fromagency,0) = 1 ) AND 	
							 ( isnull(airwaybill.SeparateChargeNo,'') = '' ) AND 
							 ( AirWayBill.deptno = '$dept' ) 
					GROUP BY Separatesetup.Separate " ;
		$result = $model->query($sql);
		$shouldget += $result[0]["shouldget"]?$result[0]["shouldget"]:0;

		//5.费用表
		//5.1.我方制单
		$sql = "SELECT sum(chargehead.get) as shouldget
				  FROM chargehead	
				 WHERE ( chargehead.style = 0 ) AND 
					   ( chargehead.paytype <> '现金' ) AND 
					   ( chargehead.get <> 0 ) AND 
					   ( chargehead.deptno = '$dept' ) ";
		$result = $model->query($sql);
		$shouldget += $result[0]["shouldget"]?$result[0]["shouldget"]:0;
		
		//5.1.2对方制单
		$sql = "SELECT sum(chargehead.pay) as shouldget
				  FROM chargehead	
				 WHERE ( chargehead.style = 0 ) AND 
					   ( chargehead.paytype <> '现金' ) AND 
					   ( chargehead.pay <> 0 ) AND 
					   ( chargehead.object = '$dept' ) ";
		$result = $model->query($sql);
		$shouldget += $result[0]["shouldget"]?$result[0]["shouldget"]:0;
		
		//6.费用表结算:已收金额
		$sql = "SELECT -sum(comparisonbalance.geted) as shouldget
				  FROM comparisonbalance	
				 WHERE ( comparisonbalance.style = 1 ) AND
				 	   ( comparisonbalance.deptno = '$dept' ) AND
				 	   ( comparisonbalance.geted <> 0 ) ";
		$result = $model->query($sql);
		$shouldget += $result[0]["shouldget"]?$result[0]["shouldget"]:0;

		return $shouldget;
	}

	/**
	 * 应收账款
	 * 应付客户运费,应付网络公司到付运费,应付网络公司到付货款,应付承运人到付运费,应付承运人到付货款,应付合作分成,应付合作分成到付运费,应付其他
	 * 
	 * @param string $company
	 * @param string $group
	 * @param string $dept
	 * @param string $year
	 * @param string $month
	 * @return number
	 */
	private function calShouldPay($company,$group,$dept,$year,$month)
	{
		$model = new DeptModel();

		//1.应付托运人
		$sql = "SELECT sum( case when isnull(paied,0) = 0 then getmoney else 0 end + case when isnull(dpaied,0) = 0 then discount else 0 end ) as shouldpay
		          FROM AirWayBill
		         WHERE airwaybill.deptno = '$dept' and ( airwaybill.getmoney <> 0 or airwaybill.discount <> 0 ) ";
		$result = $model->query($sql);
		$shouldpay = $result[0]["shouldpay"]?$result[0]["shouldpay"]:0;		
		
		//2.应付网络公司
		//2.1.我方配载:应付派送/中转费
		$sql = "SELECT sum( case when dnstocks.placeno = '' then airwaybill.FTransferMoney + airwaybill.FDeliverMoney else 0 end ) as shouldpay
				  FROM peihuo,	airwaybill,	dnstocks		
				 WHERE ( peihuo.sysdno = airwaybill.sysdno ) AND	
					   ( dnstocks.dno = peihuo.sysdno AND dnstocks.deptno = peihuo.deptno ) AND 
					   ( ( ISNULL(peihuo.indeptno,'') <> '' AND peihuo.stype = '配货') )  AND 
					   ( peihuo.valid = 1 ) AND ( peihuo.separate = 0 ) AND 
					   ( ( case when dnstocks.placeno = '' then airwaybill.FTransferMoney + airwaybill.FDeliverMoney else 0 end ) >0 ) AND 
					   ( peihuo.deptno = '$dept' ) ";
		$result = $model->query($sql);
		$shouldpay += $result[0]["shouldpay"]?$result[0]["shouldpay"]:0;

		//2.2.对方配载:应付代收货款,到付运费
		$sql = "SELECT sum(airwaybill.getmoney + airwaybill.icollect + case when peihuo.deptno = airwaybill.getdeliverdeptno then airwaybill.GetDeliverMoney else 0 end + case when peihuo.deptno = airwaybill.gettransferdeptno then airwaybill.GetTransferMoney else 0 end ) as shouldpay
				  FROM peihuo, airwaybill
				 WHERE ( peihuo.sysdno = airwaybill.sysdno ) AND	
				 	  ( ISNULL(peihuo.indeptno,'') <> '' ) AND
				      ( peihuo.stype = '配货' OR ( peihuo.stype = '派送' and airwaybill.arrivedpaytype = '现金') )  AND 
					  ( peihuo.valid = 1 ) AND ( peihuo.separate = 0 ) AND 
					  ( ( airwaybill.getmoney + airwaybill.icollect + case when peihuo.deptno = airwaybill.getdeliverdeptno then AirWayBill.GetDeliverMoney else 0 end + case when peihuo.deptno = airwaybill.gettransferdeptno then AirWayBill.GetTransferMoney else 0 end ) >0 ) AND 
					  ( peihuo.indeptno = '$dept' ) ";
		$result = $model->query($sql);
		$shouldpay += $result[0]["shouldpay"]?$result[0]["shouldpay"]:0;

		//2.3.自己开的进港单:应付代收货款和到付运费
		$sql = "SELECT sum(airwaybill.getmoney + airwaybill.icollect) as shouldpay
				  FROM airwaybill	
				 WHERE ( airwaybill.billtype = 'IN' ) AND 
					   ( ( airwaybill.getmoney + airwaybill.icollect ) <> 0 ) AND 
					   ( airwaybill.deptno = '$dept' ) " ;
		$result = $model->query($sql);
		$shouldpay += $result[0]["shouldpay"]?$result[0]["shouldpay"]:0;

		//3.应付承运人
		//在5.费用表中统计

		//4.合作分成
		//4.1.代收货款 配载
		$sql =  "SELECT sum(airwaybill.getmoney) as shouldpay
				   FROM peihuo,	airwaybill	
				  WHERE ( peihuo.sysdno = airwaybill.sysdno ) AND	
				  	    ( ISNULL(peihuo.indeptno,'') <> '' AND peihuo.stype = '配货') AND 
						( peihuo.valid = 1 ) AND ( peihuo.separate = 1 ) AND 
					    ( airwaybill.getmoney ) >0 ) AND 
						( peihuo.indeptno = '$dept' ) ";
		$result = $model->query($sql);
		$shouldpay += $result[0]["shouldpay"]?$result[0]["shouldpay"]:0;

		//4.2.货代分成 代收货款
		$sql =  "	SELECT sum(airwaybill.getmoney) as shouldpay
					  FROM airwaybill	
					 WHERE ( ISNULL(AirWayBill.fromagency,0) = 1 ) AND 	
						   ( AirWayBill.getmoney >0 ) AND 						
						   ( AirWayBill.deptno = '$dept' ) ";
		$result = $model->query($sql);
		$shouldpay += $result[0]["shouldpay"]?$result[0]["shouldpay"]:0;
		
		//4.3.货代分成 应收运费分成(没对账部分)
		$sql =  "	SELECT (CASE WHEN ( SUM(airwaybill.freight - airwaybill.discount)*(100 - separatesetup.separate)/100 + SUM(airwaybill.ftransfermoney + airwaybill.fdelivermoney) - SUM(airwaybill.icollect) ) < 0 THEN -( ROUND(SUM(airwaybill.freight - airwaybill.discount)*(100 - separatesetup.separate)/100,2) + SUM(airwaybill.ftransfermoney + airwaybill.fdelivermoney) - SUM(airwaybill.icollect) ) ELSE 0 END ) as shouldpay
					  FROM airwaybill, separatesetup 
					 WHERE ( airwaybill.takeforwho = separatesetup.departure ) AND 
							 ( airwaybill.deptno = separatesetup.destination) AND 
							 ( ISNULL(AirWayBill.fromagency,0) = 1 ) AND 	
							 ( isnull(airwaybill.SeparateChargeNo,'') = '' ) AND 
							 ( AirWayBill.deptno = '$dept' ) 
					GROUP BY Separatesetup.Separate ";
		$result = $model->query($sql);
		$shouldpay += $result[0]["shouldpay"]?$result[0]["shouldpay"]:0;
						
		//5.费用表
		//5.1.我方制单
		$sql = "SELECT sum(chargehead.pay) as shouldpay
				  FROM chargehead	
				 WHERE ( chargehead.style = 0 ) AND 
					   ( chargehead.paytype <> '现金' ) AND 
					   ( chargehead.pay <> 0 ) AND 
					   ( chargehead.deptno = '$dept' ) " ;
		$result = $model->query($sql);
		$shouldpay += $result[0]["shouldpay"]?$result[0]["shouldpay"]:0;	
		
		//5.2.对方制单
		$sql = "SELECT sum(chargehead.get) as shouldpay
				  FROM chargehead	
				 WHERE ( chargehead.style = 0 ) AND 
					   ( chargehead.paytype <> '现金' ) AND 
					   ( chargehead.pay <> 0 ) AND 
					   ( chargehead.object = '$dept' ) " ;
		$result = $model->query($sql);
		$shouldpay += $result[0]["shouldpay"]?$result[0]["shouldpay"]:0;	
		
		//6.费用表结算:已付金额
		$sql = "SELECT sum(comparisonbalance.paied) as shouldpay
				  FROM comparisonbalance	
				 WHERE ( comparisonbalance.style = 1 ) AND
					   ( comparisonbalance.deptno = '$dept' ) AND 
					   ( comparisonbalance.paied <> 0 ) ";
		$result = $model->query($sql);
		$shouldpay += $result[0]["shouldpay"]?$result[0]["shouldpay"]:0;
		
		return $shouldpay;
	}

	/**
	 * 计算可用现金
	 *
	 * @param string $company
	 * @param string $group
	 * @param string $dept
	 * @param string $year
	 * @param stirng $month
	 */
	private function calCash($company,$group,$dept,$year,$month,&$total,&$used,&$balance)
	{
		$model = new DeptModel();
		
		$sql = "select liquidity from dept where deptno = '$group'";
		$result = $model->query($sql);
		$total = $result[0]["liquidity"]?$result[0]["liquidity"]:0;
		
		$sql = "select sum(geted  - payed) as used from cashbook,dept where cashbook.deptno = dept.deptno and dept.groups = '$group'";
		$result = $model->query($sql);
		$used = $result[0]["used"]?$result[0]["used"]:0;
		
		$balance = $total + $used;
	}
	
}

?>