<?php
import("XMLUtil","think.Util.XMLUtil");
require_once(LIB_PATH.'\Utils\nusoap\nusoap.php');
class WBService 
{
	/**
	 * 日志
	 *
	 * @var string
	 */
	private $log;
	
	/**
	 * 公司编号
	 *
	 * @var string
	 */
	private $company;
	
	/**
	 * soap client
	 *
	 * @var nusoap_client
	 */
	private $client;
	
	/**
	 * 建立nusoap client
	 *
	 */
	private function create($header='US000000000')
	{
		if(!$this->client)
		{
			$this->client = new nusoap_client("http://www.568056.com/Service/OrderService.asmx?wsdl", true);
			$this->client->soap_defencoding = 'utf-8';  
			$this->client->decode_utf8 = false;
			
			/*
			$header = "<SecurityHeader xmlns=\"http://tempuri.org/\"><UserId>US000000000</UserId></SecurityHeader>";
			$this->client->setHeaders($header);
			*/
		}
	}
		
	/**
	 * request
	 * 01 查询单据轨迹 02 保存定单 03回写单据号 04 更改单据状态 
	 * @param string $data
	 * @return string 0 记录不存在 1 成功 -1 失败 -2 用户/密码错误 -3 无效机构号 -9 其他错误
	 */
	public function request($data)
	{		
		try {
			//$this->addLog("kenlong");
			if(C("DEBUG_MODE")) $this->addLog("query::data:".$data);
			
			$xml = simplexml_load_string($data); 
			$request = XMLUtil::XML2array($xml);

			//检查用户/密码是否正确
			if(!$this->check($request["head"],$result))
				return $result;
			
			//开始请求处理		
			switch ($request["head"]["type"])
			{
				case '01':
					$result = $this->track($request);
					break;
				case '02':
					$result = $this->order($request);
					break;
				case '0200':
					$result = $this->orderQuery($request);
					break;
				case '03':
					$result = $this->updatebillno($request);
					break;
				case '04':
					$result = $this->updatestatus($request);
					break;
				default:
					$result = $this->createResult('-9');
			}
		}catch (Exception $e)
		{
			$this->addLog("Not exception error from query:".$e->getMessage());
			$result = $this->createResult($request["head"]["type"]);
		}
		
		return $result;
	}
	
	/**
	 * 检查用户密码是否正确
	 * 检查是否存在此机构号
	 *
	 * @param string $head
	 * @return boolean
	 */
	private function check($head,&$result)
	{
		try
		{
			
			//检查用户/密码
			if($head["user"] != "webbusiness" & $head["pass"] != "web4321")
			{
				$result = $this->createResult($head["type"],-2);
				return false;
			}
			
			//检查是否有此机构号
			if(!trim($head["organid"]))
			{
				$result = $this->createResult($head["type"],-3);
				return false;
			}
			
			if($head["organid"]=='super')
				return true;
			
			$model = new DeptModel();
			$rs= $model->find("organid = '".$head["organid"]."' and type = 'organ'");
			if(!$rs)
			{
				$result = $this->createResult($head["type"],-3);
				return false;
			}
			else 
			{
				$this->company = $rs["DeptNo"];
			}
			
			
			//$this->company = '000003';
			
			return true;
		}
		catch (Exception $e)
		{
			$this->addLog("NotException error from checkUser:".$e->getMessage());
			$result = $this->createResult($head["type"]);
		
			return false;
		}
	}
	
	/**
	 * 01 查询货物轨迹
	 *
	 */
	private function track($request)
	{
		try {
			$dno = $request["body"]["BillNo"];
			$dno = ereg_replace("[^a-zA-Z0-9,-]",'',$dno);
			$dno = str_replace(',',"','",$dno);
			$dno = "'".$dno."'";
						
			$condition = " ( dno in (".$dno.") ) and ( AirWayBill.DBilldate > DATEADD(month,-3,getdate()) ) and ( deptno like '".$this->company."%' ) ";
			
			$model = new AirWayBillModel();
			$result = $model->findAll($condition);
			if($result)
			{
				$item .= "<items>";
				for($i=0;$i<count($result);$i++)
				{
					$item .= "<item>";
					//$item .= "<ID>".$result[$i]["ID"]."</ID>";
					$item .= "<BillNo>".$result[$i]["DNO"]."</BillNo>";
					$item .= "<Billdate>".$result[$i]["DBilldate"]."</Billdate>";
					$item .= "<Departure>".$result[$i]["Departure"]."</Departure>";
					$item .= "<Destination>".$result[$i]["Destination"]."</Destination>";
					$item .= "<Qty>".$result[$i]["Qty"]."</Qty>";
					$item .= "<Weight>".$result[$i]["Weight"]."</Weight>";
					$item .= "<SignIn>".$result[$i]["SignIn"]."</SignIn>";
					$item .= "<SignDate>".str_replace('00:00:00','',$result[$i]["SignDate"])."</SignDate>";
					
					if($result[$i]["Memo"])
					{
						$item .= "<TraceRecord>".$result[$i]["Memo"]."</TraceRecord>";
					}
					else 
					{
						$item .= "<TraceRecord>".$this->trackdetail($result[$i]["SYSDNO"])."</TraceRecord>";
					}
					$item .= "</item>";
				}
				$item .= "</items>";
												
				return $this->createResult($request["head"]["type"],1,'',count($result),$item);
			}
			else 
			{
				return $this->createResult($request["head"]["type"],1,'',0);
			}
		}
		catch (Exception $e)
		{
			$this->addLog("NotException error from send:".$e->getMessage());
			$result = $this->createResult($request["head"]["type"],'-9');
			return $result;
		}
	}
	
	/**
	 * 货物详细轨迹
	 *
	 * @param string $sysdno
	 * @return string
	 */
	private function trackdetail($sysdno)
	{
		if(!$sysdno)
			return '';
		
		$model = new AirWayBillModel();
		$sql = "SELECT DBilldate, DeptName, SignDate, isnull(AirWayBill.SignIn,'') as SignIn FROM AirWayBill, Dept ".
			   "WHERE ( AirWayBill.DeptNo = Dept.DeptNo )  AND ( AirWayBill.SYSDNO = '$sysdno' ) ";
		
		$info = $model->query($sql);
		if(!$info)
			return '';
		
		//编码转换
		$info  =   auto_charset($info,C('DB_CHARSET'),C('TEMPLATE_CHARSET'));
				
		//开始轨迹	
		$track = $info[0]["DBilldate"] . "  由 " . $info[0]["DeptName"] . " 收货开单" . chr(13);
		
		//中途轨迹
		$sql = "SELECT PEIHUO.PHDate, Dept.DeptName, V_Dept.DeptName as Object, Carrier.Carrier, AirBill.CarNo, AirBill.PlaneNo, ".
			   "	   PEIHUO.Stype, PEIHUO.Type, PEIHUO.States, PEIHUO.AffirmDate, AirBill.ZNO, PEIHUO.Updated, ".
			   "	   AirBill.CarrierPhone, PEIHUO.Carrier as SingleCarrier, AirBill.PickerPhone ".
			   "  FROM PEIHUO LEFT OUTER JOIN V_Dept ON PEIHUO.IndeptNo = V_Dept.DeptNo ".
			   "		LEFT OUTER JOIN Carrier ON PEIHUO.Carrier = Carrier.CarrierCode ".
			   "		LEFT OUTER JOIN AirBill ON PEIHUO.SYSZNO = AirBill.SYSZNO, ".
         	   "		Dept  ".
   			   " WHERE ( PEIHUO.DeptNO = Dept.DeptNo ) AND  ".
         	   "       ( ( PEIHUO.SYSDNO = '$sysdno'  ) AND  ".
         	   "		 ( PEIHUO.States <> -1 ) ) ";
        $result = $model->query($sql);
        if($result)
        {
        	//编码转换
        	$result  =   auto_charset($result,C('DB_CHARSET'),C('TEMPLATE_CHARSET'));

        	for($i=0;$i<count($result);$i++)
        	{
        		if($result[$i]["Stype"] == '出仓')
        		{
        			$track .= $result[$i]["Updated"] . "  由 " . $result[$i]["DeptName"] . " 发往 " . $result[$i]["Object"] . chr(13);
        			
        			if($result[$i]["States"])
        			{
        				$track .= $result[$i]["AffirmDate"] . "  到达 " . $result[$i]["Object"] . chr(13);
        			}
        		}
        		
        		if($result[$i]["Stype"] == '配货')
        		{
        			$track .= $result[$i]["PHDate"] . "  ";
        			
        			if(!$result[$i]["Object"]) //外发承运人的
        			{
        				if($result[$i]["Carrier"])
        				{
        					$track .= "由 " . $result[$i]["Carrier"] . " 中转";
        				}
        				else 
        				{
        					$track .= "由 " . $result[$i]["SingleCarrier"] . " 中转";
        				}

        				if($result[$i]["ZNO"])
        				{
        					$track .= " 中转单号:" . $result[$i]["ZNO"];
        				}
        				
        				if($result[$i]["CarrierPone"])
        				{
        					$track .= " 联系电话:" . $result[$i]["CarrierPhone"];
        				}
        				
        				if($result[$i]["PickerPhone"])
        				{
        					$track .= " 提货电话:" . $result[$i]["PickerPhone"];
        				}
        				
        				$track .= chr(13);
        			}
        			else 
        			{
        				$track .= "由 " . $result[$i]["DeptName"] . " 发往 " . $result[$i]["Object"];
        				
        				if($result[$i]["CarNo"])
        				{
        					$track .= " 车牌:" .$result[$i]["CarNo"];
        				}
						
        				if($result[$i]["PlaneNo"])
        				{
        					$track .= " 航班:" .$result[$i]["PlaneNo"];
        					
        					if($result[$i]["ZNO"])
        					{
        						$track .= " 正单号:" .$result[$i]["ZNO"];
        					}
        				}
        				
        				$track .= chr(13);
        			}
        			
        			if($result[$i]["States"])
        			{
        				$track .= $result[$i]["AffirmDate"] . "  到达 " . $result[$i]["Object"] . chr(13);
        			}
        		}
        	}
        }
		
		//签收轨迹
		if($info[0]["SignIn"])
		{
			$track .= $info[0]["SignDate"] . " 货物已签收,  签收人 " .$info[0]["SignIn"];
		}
		
		$track = nl2br($track);
		$track = htmlspecialchars($track);
		
		return $track;
	}
	
	/**
	 * 下订单
	 *
	 * @param string $request
	 */
	private function order($request)
	{	
		$paytype = array("现金","月结","到付");
		$transtype = array("空运","汽运");
		$deliverType = array("市内自提","机场自提","送货上门");
		$signbacktype = array("","随货签收单原件返回","随货签收单传真返回","网络公司签收单传真返回");
		try {
			$data["OrderInfoId"] = $request["body"]["OrderInfoId"];
			$data["billDate"] = $request["body"]["BillDate"];
			$data["userId"] = $request["body"]["UserId"];
			$data["userName"] = $request["body"]["UserId"];
			$data["departure"] = $request["body"]["Departure"];
			$data["clientName"] = $request["body"]["Shipper"];
			$data["clientPhone"] = $request["body"]["ShipperPhone"];
			$data["clientMobile"] = $request["body"]["ShipperMobile"];
			$data["clientAddress"] = $request["body"]["ShipperAddress"];
			$data["destination"] = $request["body"]["Destination"];
			$data["consignee"] = $request["body"]["Recipients"];
			$data["consigneePhone"] = $request["body"]["RecipientsPhone"];
			$data["consigneeMobile"] = $request["body"]["RecipientsMobile"];
			$data["consigneeAddress"] = $request["body"]["RecipientsAddress"];
			$data["goods"] = $request["body"]["GoodsName"];
			$data["qty"] = intval($request["body"]["Qty"]);
			$data["weight"] = doubleval($request["body"]["Weight"]);
			$data["cubage"] = doubleval($request["body"]["Volume"]);
			$data["package"] = $request["body"]["Package"];
			$data["payType"] = $paytype[$request["body"]["Payment"]];
			$data["insuranceValue"] = doubleval($request["body"]["InsuranceValue"]);
			$data["getMoney"] = doubleval($request["body"]["Chargeforgoods"]);
			$data["transtype"] = $transtype[$request["body"]["Transporttype"]];
			$data["deliverType"] = $deliverType[$request["body"]["Pickup"]];
			$data["signBackType"] = $signbacktype[$request["body"]["BillReceiptReturn"]];
			$data["receipt"] = intval($request["body"]["InceptAtDoor"]);
			$data["receiptPhone"] = $request["body"]["InceptTelephone"];
			$data["receiptAddress"] = $request["body"]["LoadingLocation"];
			$data["receiptTime"] = $request["body"]["InceptTime"];
			$data["notice"] = $request["body"]["Information"];
			$data["company"] = $this->company;
						
			//add only for guoyao
			//$data = $request["body"];
			$data["client"] = '';//$data["clientname"];
			$data["consigneeName"] = '';//$data["consignee"];
			/*
			$data["qty"] = intval($data["qty"]);
			$data["weight"] = doubleval($data["weight"]);
			$data["cubage"] = doubleval($data["cubage"]);
			$data["insuranceValue"] = doubleval($data["insuranceValue"]);
			$data["getMoney"] = doubleval($data["getMoney"]);
			$data["receipt"] = intval($data["receipt"]);
			$data["company"] = $this->company;
			*/
			
			$model = new Web_OrderModel();
			
			if(C("DEBUG_MODE")) $this->addLog("data:".print_r($data,true));
			
			if($data["id"])
			{
				$data["id"] = intval($data["id"]);
				$result = $model->save($data);
			}
			else 
			{
				unset($data["id"]);
				$result = $model->add($data);
			}
			
			if(!$result)
				return $this->createResult($request["head"]["type"]);
			else
				return $this->createResult($request["head"]["type"],'1','','1');
		}
		catch (Exception $e)
		{
			$this->addLog("NotException error from send:".$e->getMessage());
			$result = $this->createResult($request["head"]["type"],'-9');
			return $result;
		}
	}
	
	/**
	 * 订单查询
	 *
	 * @param xml string $request
	 * @return xml string
	 */
	private function orderQuery($request)
	{
		try 
		{
			$id = $request["body"]["id"];
			$userid = $request["body"]["userId"];
			$sdate = $request["body"]["sdate"];
			$edate = $request["body"]["edate"];
			
			if(!$id && !$userid)
				return $this->createResult($request["head"]["type"],-3,'not a valid action in order query');
			
			$model = new Web_OrderModel();
			$model->order("id desc");
			
			if($id)
			{
				$result = $model->findAll("id = $id");
			}
			else 
			{
				$condition = "userId = '$userid'";
				if($sdate && $edate)
					$condition .= " and (billDate between '$sdate' and '$edate' )";
					
				$result = $model->findAll($condition);
			}
			
			if(!$result)
				return $this->createResult($request["head"]["type"],1,'no record',0);
			
			for($i=0;$i<count($result);$i++)
			{	
				$item .= "<item>";
				$item .= "<id>".$result[$i]["id"]."</id>";
				$item .= "<billDate>".$result[$i]["billDate"]."</billDate>";
				$item .= "<userName>".$result[$i]["userName"]."</userName>";
				$item .= "<departure>".$result[$i]["departure"]."</departure>";
				$item .= "<clientName>".$result[$i]["clientName"]."</clientName>";
				$item .= "<clientPhone>".$result[$i]["clientPhone"]."</clientPhone>";
				$item .= "<clientAddress>".$result[$i]["clientAddress"]."</clientAddress>";
				$item .= "<destination>".$result[$i]["destination"]."</destination>";
				$item .= "<consignee>".$result[$i]["consignee"]."</consignee>";
				$item .= "<consigneePhone>".$result[$i]["consigneePhone"]."</consigneePhone>";
				$item .= "<consigneeAddress>".$result[$i]["consigneeAddress"]."</consigneeAddress>";
				$item .= "<goods>".$result[$i]["goods"]."</goods>";
				$item .= "<qty>".$result[$i]["qty"]."</qty>";
				$item .= "<weight>".$result[$i]["weight"]."</weight>";
				$item .= "<cubage>".$result[$i]["cubage"]."</cubage>";
				$item .= "<package>".$result[$i]["package"]."</package>";
				$item .= "<payType>".$result[$i]["payType"]."</payType>";
				$item .= "<insuranceValue>".$result[$i]["insuranceValue"]."</insuranceValue>";
				$item .= "<getMoney>".$result[$i]["getMoney"]."</getMoney>";
				$item .= "<transtype>".$result[$i]["transtype"]."</transtype>";
				$item .= "<deliverType>".$result[$i]["deliverType"]."</deliverType>";
				$item .= "<signBackType>".$result[$i]["signBackType"]."</signBackType>";
				$item .= "<receipt>".$result[$i]["receipt"]."</receipt>";
				$item .= "<receiptPhone>".$result[$i]["receiptPhone"]."</receiptPhone>";
				$item .= "<receiptAddress>".$result[$i]["receiptAddress"]."</receiptAddress>";
				$item .= "<receiptTime>".$result[$i]["receiptTime"]."</receiptTime>";
				$item .= "<notice>".$result[$i]["notice"]."</notice>";
				$item .= "<status>".$result[$i]["status"]."</status>";
				$item .= "<dno>".$result[$i]["dno"]."</dno>";
				$item .= "</item>";
			}
			
			return $this->createResult($request["head"]["type"],1,'success',1,$item);		
		}catch (Exception $e)
		{
			$this->addLog("Exception by orderQuery:".print_r($e,true));
			return $this->createResult($request["head"]["type"],-1,'error in order query');
		}
	}
	
	
	
	
	
	
	/**
	 * 回写正是代单号
	 *
	 * @param string $request
	 */
	private function updatebillno($request)
	{
		if(C("DEBUG_MODE")) $this->addLog("updatebillno::data:".print_r($request,true));	
		
		$this->create();
		
		$params = array("orderid"=>$request["body"]["orderid"],"orderCode"=>$request["body"]["billno"]);		
		$result = $this->client->call("UpdateOrderCode",array('parameters' =>$params), '', '');
		
		if(C("DEBUG_MODE")) $this->addLog("updatebillno::result:".print_r($result,true));	
		
		$result = $result["UpdateOrderCodeResult"];
		if(!$result)
			return $this->createResult($request["head"]["type"],-1,'empty result');
		
		if($result == 'false')
		{
			$this->addLog("UpdateOrderCode error");
			return $this->createResult($request["head"]["type"],-1);
		}	
		
		return $this->createResult($request["head"]["type"],1);
	}
	
	/**
	 * 回写单据状态
	 *
	 * @param string $request
	 */
	private function updatestatus($request)
	{
		if(C("DEBUG_MODE")) $this->addLog("updatestatus::data:".print_r($request,true));	
		
		$this->create();
		
		$params = array("orderid"=>$request["body"]["orderid"],"state"=>'2');		
		$result = $this->client->call("UpdateOrderState",array('parameters' =>$params), '', '');
		
		if(C("DEBUG_MODE")) $this->addLog("updatestatus::result:".print_r($result,true));	
		
		$result = $result["UpdateOrderStateResult"];
		if(!$result)
			return $this->createResult($request["head"]["type"],-1,'empty result');
		
		if($result == 'false')
		{
			$this->addLog("UpdateOrderState error");
			return $this->createResult($request["head"]["type"],-1);
		}	
		
		return $this->createResult($request["head"]["type"],1);
	}
	
	/**
	 * 建立错误返回xml
	 *
	 * @param string $code
	 */
	private function createResult($requestcode,$resultcode='-1',$info='',$row='0',$body='')
	{
		$result = "<?xml version='1.0' encoding='utf-8'?>
					<result><head>
						<type>$requestcode</type>
						<code>$resultcode</code>
						<rowcount>$row</rowcount>
						<info>$info</info>
					</head>$body</result>";
		
		return $result;
	}
	
	private function escape_string($str)
	{
		$str = str_replace("'","''",$str);
    	$str = str_replace("&quot;", '"', $str);
        $str = str_replace("&lt;", "<", $str);
        $str = str_replace("&gt;", ">", $str);
        $str = str_replace("&amp;", "&", $str);
        return $str;
	}
	
	/**
	 * 记录日志
	 *
	 * @param string $data
	 */
	private function addLog($data)
	{
		Log::record($data,WEB_LOG_DEBUG);
		return ;
	}
	
	/**
	 * 写日志文件
	 *
	 */
	function __destruct()
	{
		Log::save();
	}
	
}
?>