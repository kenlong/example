<?php
import("COM.Utils.XMLUtil");
require_once(LIB_PATH.'\Utils\nusoap\nusoap.php');
class ControlService 
{
	/**
	 * 日志
	 *
	 * @var string
	 */
	private $log;
	
	/**
	 * soap客户端
	 *
	 * @var nusoap_client
	 */
	private $authservice;
	private $awbservice;
	private $auth = 'ngu';
	private $authpass = '11111';
	private $userid = 'NGU01';
	private $usercode = '937E8186-BFDE-4D8F-9BE6-00C82A85D4EE';
	private $ticket ;
	
	/**
	 * 建立nusoap client
	 *
	 */
	private function create()
	{
		//登录服务
		if(!$this->authservice)
		{
			$this->authservice = new nusoap_client("http://125.88.6.191/DexExchange/UserService.asmx?WSDL", true);
			$this->authservice->soap_defencoding = 'utf-8';  
			$this->authservice->decode_utf8 = false;
		}
		
		//生成ticket
		$result = $this->authservice->call("Login",array('authId'=>$this->auth,'authPassword'=>$this->authpass),'','');
		if(C("DEBUG_MODE")) $this->addLog("czLogin result:".$result);
		
		if(!$result)
			return false;
		
		if(!$result["LoginResult"])
			return false;
		
		$this->ticket = $result["LoginResult"];

		//制单服务
		if(!$this->awbservice)
		{
			$this->awbservice = new nusoap_client("http://58.248.41.191:8080/MetaData/Awb?wsdl",true);
			$this->awbservice->soap_defencoding = 'utf-8';
			$this->awbservice->decode_utf8 = false;
			
			$header = "<DexHeader xmlns=\"http://tang.csair.com/dex/\"><dexlocation>Awb</dexlocation><dextoken>$this->ticket</dextoken></DexHeader>";
			$this->awbservice->setHeaders($header);
		}
	}
	
		
	/**
	 * control
	 *
	 * @param string $data
	 * @param string $add
	 * @return string
	 */
	public function query($data)
	{
		if(C("DEBUG_MODE")) $this->addLog("query::data:".$data);
		try {
			$xml = simplexml_load_string($data); 
			$request = XMLUtil::XML2array($xml);
			//数据格式不正确
			if(!$request)
				return $this->createResult($request["head"]["type"],'-3','what you request?');
			
			//用户密码不正确
			if($request["head"]["user"] != "user4soap" || $request["head"]["pass"] != 'pass4soap')
				return $this->createResult($request["head"]["type"],'-2');
			
			switch (substr($request["head"]["type"],0,2))
			{
				case '01':
					$rs = $this->billsQuery($request);
					break;
				case '02':
					$rs = $this->order($request);
					break;
				case '03':
					$rs = $this->uploadawb($request);
					break;
				default:
					$rs = $this->createResult($request["head"]["type"],'0',"not a valid action");
			}
			
			return $rs;
		}catch (Exception $e)
		{
			if(C("DEBUG_MODE")) $this->addLog("query error:".print_r($e,true));
			return 'false';
		}
	}
	
	/**
	 * 单据查询 01
	 *
	 * @param string $request
	 */
	private function billsQuery($request)
	{
		try 
		{
			$dno = $request["body"]["no"];
			$dno = ereg_replace("[^a-zA-Z0-9,-]",'',$dno);
			$dno = str_replace(',',"','",$dno);
			$dno = "'".$dno."'";
			
			$condition = "dno in (".$dno.") or adno in (".$dno.")";
			
			$model = new AirwaybillModel();
			$result = $model->where($condition)->select();
			if($result)
			{
				for($i=0;$i<count($result);$i++)
				{
					$item .= "<item>";
					$item .= "<ID>".$result[$i]["ID"]."</ID>";
					$item .= "<DNO>".$result[$i]["ADNO"]."</DNO>";
					$item .= "<DBilldate>".$result[$i]["DBilldate"]."</DBilldate>";
					$item .= "<Departure>".$result[$i]["Departure"]."</Departure>";
					$item .= "<Destination>".$result[$i]["Destination"]."</Destination>";
					$item .= "<Qty>".$result[$i]["Qty"]."</Qty>";
					$item .= "<Weight>".$result[$i]["Weight"]."</Weight>";
					$item .= "<SignIn>".$result[$i]["SignIn"]."</SignIn>";
					$item .= "<SignDate>".$result[$i]["SignDate"]."</SignDate>";
					$item .= "<Memo>".$result[$i]["Memo"]."</Memo>";
					$item .= "</item>";
				}
												
				return $this->createResult($request["head"]["type"],1,'',count($result),$item);
			}
			else 
			{
				return $this->createResult($request["head"]["type"],1,'',0);
			}
		}
		catch (Exception $e)
		{
			return $this->createResult($request["head"]["type"],-1,"error in bills query");
		}
		
	}
	
	/**
	 * 网上管理 02
	 *
	 * @param xml string $request
	 * @return xml string
	 */
	private function order($request)
	{
		try {
			switch (substr($request["head"]["type"],3,2))
			{
				case '01':
					return $this->orderSave($request);
					break;
				case '02':
					return $this->orderQuery($request);
					break;
				case '03':
					return $this->orderDelete($request);
					break;
				default:
					return $this->createResult($request["head"]["type"],-3,'not a valid action in order');
			}
		}catch (Exception $e)
		{
			return $this->createResult($request["head"]["type"],-1,'error in order');
		}
	}
	
	/**
	 * 保存订单
	 *
	 * @param xml string $request
	 * @return xml string
	 */
	private function orderSave($request)
	{
		try 
		{
			$item = $request["body"];
			if($item)
			{
				$model = new OrderModel();
				
				$item["client"] = $item["clientName"];
				$item["qty"] = intval($item["qty"]);
				$item["weight"] = doubleval($item["weight"]);
				$item["cubage"] = floatval($item["cubage"]);
				$item["insuranceValue"] = doubleval($item["insuranceValue"]);
				$item["getMoney"] = doubleval($item["getMoney"]);
				$item["receipt"] = intval($item["receipt"]);
				
				
				//有id的更新,否则新增
				if($item["id"])
				{
					$item["id"] = intval($item["id"]);
					$result = $model->save($item);
				}
				else 
				{
					unset($item["id"]);
					$result = $model->add($item);
				}
				
				if($result === false)
					return $this->createResult($request["head"]["type"],-1,'save/add bill error');
				
				if($item["id"])
					$id = $item["id"];
				else 
					$id = $result;
				
				return $this->createResult($request["head"]["type"],1,'succeed',1,"<item>$id</item>");
			}
			else 
			{
				return $this->createResult($request["head"]["type"],-3,'not a valid action in order save');
			}
		}catch (Exception $e)
		{
			return $this->createResult($request["head"]["type"],-1,'error in order save');
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
			
			$model = new OrderModel();
			
			if($id)
			{
				$result = $model->where("id = $id")->order("id desc")->select();
			}
			else 
			{
				$condition = "userId = '$userid'";
				if($sdate && $edate)
					$condition .= " and (billDate between '$sdate' and '$edate' )";
					
				$result = $model->where($condition)->order("id desc")->select();
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
	 * 删除订单
	 *
	 * @param xml string $request
	 * @return xml string
	 */
	private function orderDelete($request)
	{
		try 
		{
			$id = $request["body"]["id"];
			if(!$id)
				return $this->createResult($request["head"]["type"],-3,'not a valid action');
			
			$model = new OrderModel();
			$result = $model->where("id = $id and status = 0")->delete();
			
			if(!$result)
				return $this->createResult($request["head"]["type"],-1,'delete error');
			
			return $this->createResult($request["head"]["type"],1,'success',1);		
		}catch (Exception $e)
		{
			return $this->createResult($request["head"]["type"],-1,'error in order delete');
		}
	}
	
		
	/**
	 * 南航正单
	 *
	 * @param $request
	 */
	private function uploadawb($request)
	{
		$this->create();
	
		$model = new AirBillModel();

		$ids = split(",",$request["body"]["id"]);
		$count = count($ids);
		
		if($count)
		{
			$j = 0;
			for($i=0;$i<$count;$i++)
			{
				$id = $ids[$i];
				$data = $model->query("select airbill.*,carrier.FueladdRate,A.Areaname as departurename,B.AreaName as destinationname from airbill,carrier,Placecode A,Placecode B where airbill.carrier = carrier.carriercode and airbill.deptno = carrier.deptno and airbill.departure = A.areacode and airbill.destination = B.areacode and airbill.id = $id ");
				if($data)
				{
					$result = $this->createAwbData($data);
					$result = $this->awbservice->call("SaveAwb",array("userId"=>$this->userid,"token"=>$this->usercode,"awb"=>$result),'','');
					$fault = $result["faultstring"];
					if($fault)
					{
						continue;
					}
					$model->execute("update airbill set SendedToTang = 1 where id = $id");	
				}
				$j++;
			}
		}
		
		if($j == 0)
			return $this->createResult($request["head"]["type"],-1,'fault',0);
		elseif($j != $count)
			return $this->createResult($request["head"]["type"],0,'partsuccess',0);
		else 
			return $this->createResult($request["head"]["type"],1,'success',0);
		
	}
	
	/**
	 * 南航上传数据,构造数据
	 *
	 * @param string $data
	 */
	private function createAwbData($data)
	{
		//--++--正单主结构
		//默认值
        $awbBasic["Book_ID"] = 0;
        $awbBasic["SignOfShipper"] = '';
        $awbBasic["SpCode"] = '';
        $awbBasic["Unit"] = 'K';
        $awbBasic["GroupNo"] = 9;
        $awbBasic["HandlingFlag"] = 0;
        $awbBasic["IntDom"] = 0;
        // 运单后缀-国内00000000,国际单00000001
        $awbBasic["AwbPostfix"] = "00000000";
        //运价类别
        $awbBasic["RateClass"] = 'Q';
 
        //从正单赋值
		// 运单前缀
		$zno = split("-",$data[0]["ZNO"]);
       	$awbBasic["AwbPrefix"] = $zno[0];
        // 运单号
        $awbBasic["AwbNo"] = $zno[1];
        // 航程
        $awbBasic["Routing"] = substr($data[0]["Departure"],0,3)."/".substr($data[0]["Destination"],0,3);
        // 承运人
        $awbBasic["Carriers"] = $data[0]["Carrier"];
        //定舱号
        $awbBasic["Book_ID"] = $data[0]["OrderNo"]?$data[0]["OrderNo"]:0;
        // 托运人
        $awbBasic["ShipperName"] = $data[0]["Client"];
        // 托运人地址
        $awbBasic["ShipperAddress"] = trim($data[0]["ClientAddress"])?$data[0]["ClientAddress"]:$data[0]["departurename"];
        // 托运人电话
        $awbBasic["ShipperTelephone"] = mb_substr($data[0]["ClientPhone"],0,40);
        // 收货人
        $awbBasic["ConsigneeName"] = $data[0]["Picker"];
        // 收货人地址
        $awbBasic["ConsigneeAddress"] = trim($data[0]["PickerAddress"])?$data[0]["PickerAddress"]:$data[0]["destinationname"];
        // 收货人电话
        $awbBasic["ConsigneeTelephone"] = mb_substr($data[0]["PickerPhone"],0,40);
        // 货物代码
        $awbBasic["GoodsCode"] = $data[0]["GoodsType"];
        // 品名
        $awbBasic["Goods"] = trim($data[0]["GoodsName"]);
        // 件数
        $awbBasic["Piece"] = $data[0]["QTY"];
        // 重量
        $awbBasic["Weight"] = $data[0]["Weight"];
        // 体积
        $awbBasic["Volume"] = round($data[0]["Weight"]/167,2);       
         // 储运注意事项
        $awbBasic["HandlingInfo"] = $data[0]["Notice"];
 		 // 制单账号
        $awbBasic["Op_ID"] = $this->userid;
        
        
        //--++--正单关联费用
        //运单前缀
        $awbCharge["AwbPrefix"] = $zno[0];
        //运到哪号
        $awbCharge["AwbNo"] = $zno[1];
        // 运单后缀-国内00000000,国际单00000001
        $awbCharge["AwbPostfix"] = '00000000';
        // 运单类型
        $awbCharge["AwbType"] = '普通';
        // 结算注意事项
        $awbCharge["AccountingInfo"] = "文件：限南航承运";
        // 结算折扣号
        $awbCharge["AccountingRule"] = mb_substr($data[0]["ACCInfo"],0,20);
 		//货币代码
        $awbCharge["CurrencyName"] = 'CNY';
         // 支付方式
        $awbCharge["ChgsCode"] = $data[0]["PayType"];
         // 折扣率
        $awbCharge["Discount"] = 100.00;
         //Wt
         $awbCharge["Wt"] = 'P';
        //Other
        $awbCharge["Other>"] = 'P';
        //承运人声明价值
        $awbCharge["DVFCarrier"] = 0;
        //海关声明价值
        $awbCharge["DVFCustomer"] = 0;
         //保险声明价值
        $awbCharge["Insurance"] = $data[0]["InsuranceValue"];
         // 计费重量
        $awbCharge["CWeight"] = $data[0]["NetWeight"];
        // 费率
        $awbCharge["RateCharge"] = $data[0]["Price"];
        // 航班运费
        $awbCharge["WeightCharge"] = $data[0]["Freight"];
         //声明价值附加费
        $awbCharge["ValCharge"] = 0;
     	//保险费率
        $awbCharge["InsuranceRate"] = $data[0]["InsuranceRate"]?$data[0]["InsuranceRate"]:1;
        //保险费
        $awbCharge["InsuranceFee"] = $data[0]["Insurance"] ;
        //地面运费费率
        $awbCharge["GroundRate"] = 0 ;
        //地面运费
        $awbCharge["GroundFee"] = 0 ;
        //燃油附加费率
        $awbCharge["OilRate"]  = $data[0]["FueladdRate"] ;
        //燃油附加费
        $awbCharge["OilFee"] = $data[0]["FuelAdd"] ;
      	//代理费
        $awbCharge["ChargeDueAgent"] = 0 ;
        //承运人其它费用总额,其它费用合计
        $awbCharge["ChargeDueCarrier"] = 0;
        //预付总额
        $awbCharge["PrepaidTotal"] = 0;
        //到付总额
        $awbCharge["CollectTotal"] = 0;
        //税金
        $awbCharge["Tax"] = 0 ;
        //货币兑美元的汇率
        $awbCharge["CurrencyRate"] = 0;
        //实际计费重量
        $awbCharge["ActualCWeight"] = $data[0]["NetWeight"];
        // 实收费率
        $awbCharge["ActualRateCharge"] = $data[0]["Price"];
        //实际航空运费
        $awbCharge["ActualWeightCharge"] = $data[0]["Freight"] ;
        //目标货币
        $awbCharge["CurrencyNameDestination"] = 'CNY';
        // 运价类型
        $awbCharge["RateType"] = 'OTH';
         // 运价代码
        $awbCharge["RateCode"] = '0000';
        // 运价品名
        $awbCharge["RateName'"] = '';
 		// 运价航班
        $awbCharge["RateFlightNo"] = $data[0]["PlaneNo"];
        // 运价航班日期
        $planedate = split(" ",$data[0]["PlaneDate"]);
        $awbCharge["RateFlightDate"] = $planedate[0];//$data[0]["PlaneDate"]; //this.dtpRateFlightDate.Value.ToString("yyyy-MM-dd");
		
        Log::record(print_r($awbBasic,true));
        
        //生成返回值		
		$result = array("awbBasic"=>$awbBasic,"awbCharge"=>$awbCharge);
        
		return $result;
		
	}
	
		
	/**
	 * 建立返回值xml
	 *
	 * @param string $type
	 * @param stirng $code
	 * @param int $rowcount
	 * @param string $data
	 */
	private function createResult($type,$code,$info='',$rowcount=0,$data='')
	{
		$result  = "<?xml version='1.0' encoding='UTF-8'?>";
		$result .= "<result>";
	    $result .= "<head>";
	    $result .=   "<type>$type</type>";
	    $result .=   "<code>$code</code>";
	    $result .=   "<info>$info</info>";
	    $result .=   "<rowcount>$rowcount</rowcount>";
	    $result .= "</head>" ;
	    $result .= "<items>$data</items>";
		$result .= "</result>";

		return $result;
	}
	
	/**
	 * 记录日志
	 *
	 * @param string $data
	 */
	private function addLog($data)
	{
		Log::record($data,Log::ERR);
		return ;
	}	
}
?>