<?php
class IndexAction extends Action{
    public function index()
    {
		vendor("GuoYaoService",LIB_PATH."\Service");

		define('DEBUG',true);
		
		
		$model = new OrderModel();
		//$sql = "UPDATE web_order SET orderNo='G01131009029',billDate='2013-10-09 10:29:05',userId='000032',userName='陈杰财',departure='',client='国药控股广州有限公司',clientName='(国药集团)高唐库',clientPhone='',clientMobile='',clientAddress='广东省-广州市-天河区-高唐工业园高普路91号',destination='',consignee='佛山市顺德区北滘医院',consigneeName='',consigneePhone='',consigneeMobile='',consigneeAddress='广东省-佛山市-顺德区-(北滘医院)(西)顺德区北滘镇林上路东城区一南路1号北滘医院西药库',goods='药品',qty=195,weight=0,cubage=0,package='纸箱',payType='月结',declareValue=0,insuranceValue=0,getMoney=0,transtype='汽运',deliverType='零担',signBackType='',receipt=0,receiptPhone=null,receiptAddress=null,receiptTime=null,requirementDate='2014-05-27 15:00:00',requirementTime='2014-05-27 15:00:00',addition=0,notice='0.0',sendFax=0,company='000032',sysdno=null,synupcount=0,synupsign=0,synupsigncount=0 WHERE id='88819'";
		$sql = "UPDATE web_order SET orderNo='G01131009029',billDate='2013-10-09 10:29:05',userId='000032',userName='陈杰财',departure='',client='',clientName='',clientPhone='',clientMobile='',clientAddress='',destination='',consignee='北滘医院',consigneeName='',consigneePhone='',consigneeMobile='',consigneeAddress='',goods='药品',qty=195,weight=0,cubage=0,package='纸箱',payType='月结',declareValue=0,insuranceValue=0,getMoney=0,transtype='汽运',deliverType='零担',signBackType='',receipt=0,receiptPhone=null,receiptAddress=null,receiptTime=null,requirementDate='2014-05-27 15:00:00',requirementTime='2014-05-27 15:00:00',addition=0,notice='0.0',sendFax=0,company='000032',sysdno=null,synupcount=0,synupsign=0,synupsigncount=0 WHERE id='88819'";
		$result = $model->execute($sql);
		
		//$service = new GuoYaoService();
		
		//echo "kenlong";
		
		//$service->readFromSPL();
		
		//$service->synup();
		
		//$service->updateFlag();
		
		/*
		$headmodel = new OrderModel();
		$find = $headmodel->where("orderNo = 'G01140307156'")->find();
		unset($find['dno']);
		unset($find['synup']);
		unset($find['billNo']);
		unset($find['OrderInfoId']);
		unset($find['status']);
		
		print_r($find);
		*/
		
    }
    
    public function testwebservice()
	{
/*
$request  = "<?xml version='1.0' encoding='utf-8'?>";
$request .= "<request>";
$request .= "<head>";
$request .= "<user>user4soap</user>";
$request .= "<pass>pass4soap</pass>";
$request .= "<type>02-02</type>";
$request .= "</head>";
$request .= "<body>";
$request .= "<userId>kenlong</userId>";
$request .= "<sdate>2010-01-01</sdate>";
$request .= "<edate>2010-05-01</edate>";
$request .= "</body>";
$request .= "</request>";		
*/
/*
$request = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<request>
  <head>
    <user>user4soap</user>
    <pass>pass4soap</pass>
    <type>01</type>
  </head>
  <body>
    <no>NG01-001</no>
  </body>
</request>
XML;
*/
/*
$request = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<request>
  <head>
    <user>user4soap</user>
    <pass>pass4soap</pass>
    <type>02-01</type>
  </head>
  <body>
	  <id></id>
	  <billDate>2010-04-20</billDate>
	  <userName>ken</userName>
	  <departure>广州</departure>
	  <clientName>发货人</clientName>
	  <clientPhone>发货人电话</clientPhone>
	  <clientAddress>发货人地址</clientAddress>
	  <destination>北京</destination>
	  <consignee>收货人</consignee>
	  <consigneePhone>收货人电话</consigneePhone>
	  <consigneeAddress>收货人地址</consigneeAddress>
	  <goods>电子配件</goods>
	  <qty>2</qty>
	  <weight>100</weight>
	  <cubage>1</cubage>
	  <package>纸箱</package>
	  <payType>现金</payType>
	  <insuranceValue>1000</insuranceValue>
	  <getMoney>3000</getMoney>
	  <transtype>空运</transtype>
	  <deliverType>客户自提</deliverType>
	  <signBackType>签收单原件返回</signBackType>
	  <receipt>1</receipt>
	  <receiptPhone>112233</receiptPhone>
	  <receiptAddress>广州某个地方</receiptAddress>
	  <receiptTime>晚上7点后</receiptTime>
	  <notice>承运注意事项</notice>
  </body>
</request>
XML;
*/

/*
$request  = "<?xml version='1.0' encoding='utf-8'?>";
$request .= "<request>";
$request .= "<head>";
$request .= "<user>user4soap</user>";
$request .= "<pass>pass4soap</pass>";
$request .= "<type>03</type>";
$request .= "</head>";
$request .= "<body>";
$request .= "<id>95</id>";
$request .= "</body>";
$request .= "</request>";		


		vendor("ControlService",LIB_PATH."\Service");
		
		$service = new ControlService();
		
		$result = $service->query($request);
		print_r($result);
*/		
		/*
		import("COM.Utils.XMLUtil");
		$xml = simplexml_load_string($result); 
		$data = XMLUtil::XML2array($xml);
		print_r($data);
		*/
	}
	
	public function t()
	{
		$model  =new OrderModel();
		$rs = $model->where("orderNo ='".$head["orderNo"]."'")->find();
		
		print_r($rs);
		
		
		
		/*
		vendor("WebOrderService",LIB_PATH."\Service");
		
		$service = new WebOrderService();
		
		$result = $service->login((object)array("userID"=>"00100001","userPass"=>"0000"));
		
		print_r((object)$result);
		*/
	}
	
	public function guoyao()
	{
		$orderno = $_REQUEST["orderno"];
	
		echo "<META http-equiv=Content-Type content='text/html; charset=utf-8'> <form action='' method=post style='font:13px'>订单号:&nbsp<input type=text id='orderno' name='orderno' value='1111211'>&nbsp<input type=submit value='查询' style='width:60px'></form>";
		echo "<br>";
		
		if(!$orderno)
		{
			return;
		}
		
		vendor("WebOrderService",LIB_PATH."\Service");
		
		$service = new WebOrderService();
		
		$parm->orderNo = $orderno;
		$parm->userID = 'supervisor';
		$result = $service->queryTrack($parm);
		
		$head = $result['queryTrackResult']['BillTrackHead'];
		
		echo "<table border=1>";
		$th = "<tr>";
		$td = "<tr>";
		foreach ($head as $key=>$value)
		{
			$th .= "<th>".$key."</th>";
			$td .= "<td>".$value."</td>";
		}
		$th .= "</tr>";
		$td .= "</tr>";
		
		echo $th;
		echo $td;
		echo "</table>";
		
		$detail = $result['queryTrackResult']['BillTrackDetail'];
		echo "<br>";
		echo "<table border=1>";		
		echo "<tr><th>TrackTime</th><th>Tracker</th><th>Info</th><th>Dept</th></tr>";
		
		for($i=0;$i<count($detail);$i++)
		{
			echo "<tr><td>".$detail[$i]->TrackTime."</td><td>".$detail[$i]->Tracker."</td><td>".$detail[$i]->Info."</td><td>".$detail[$i]->Dept."</td></tr>";
		}
		
		echo "</table>";
		
		
	}
	
    
}
?>