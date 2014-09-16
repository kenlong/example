<?php
class WebBusinessAction extends Action 
{
	public function index()
	{
		$model = new Web_OrderModel();
		print_r($model);
	}
	
	public function test()
	{
		vendor("WBService",LIB_PATH."\Service");
		

$request = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<request>
  <head>
    <organid>00000421</organid>
    <user>webbusiness</user>
    <pass>web4321</pass>
    <type>01</type>
  </head>
  <body>
    <BillNo>112233</BillNo>
  </body>
</request>
XML;

/*
$request = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<request>
  <head>
  	<organid>00000387</organid>
    <user>webbusiness</user>
    <pass>web4321</pass>
    <type>02</type>
  </head>
  <body>
    <OrderInfoId>111111111111111</OrderInfoId>
    <BillDate>2010-07-28 15:35:42</BillDate>
    <UserId>abc</UserId>
    <Shipper>张三</Shipper>
    <ShipperPhone>88888888</ShipperPhone>
    <ShipperMobile>13988888888</ShipperMobile>
    <Departure>广州</Departure>
    <ShipperAddress>东山</ShipperAddress>
    <Recipients>李四</Recipients>
    <RecipientsPhone>66666666</RecipientsPhone>
    <RecipientsMobile>13899999999</RecipientsMobile>
    <Destination>北京</Destination>
    <RecipientsAddress>东区</RecipientsAddress>
    <GoodsName>电子配件</GoodsName>
    <Qty>1</Qty>
    <Weight>30</Weight>
    <Volume>0.5</Volume>
    <Package>纸箱</Package>
    <Payment>0</Payment>
    <InsuranceValue>5000</InsuranceValue>
    <Chargeforgoods>3000</Chargeforgoods>
    <Transporttype>0</Transporttype>
    <Pickup>0</Pickup>
    <BillReceiptReturn>1</BillReceiptReturn>
    <InceptAtDoor>1</InceptAtDoor>
    <InceptTelephone>33333333</InceptTelephone>
    <LoadingLocation>东山</LoadingLocation>
    <InceptTime>晚上十二点</InceptTime>
    <Information>小心轻放</Information>
  </body>
</request>
XML;
*/
/*
		$service = new WBService();
		$result = $service->request($request);
*/

		require_once(LIB_PATH.'\Utils\nusoap\nusoap.php');
		$client = new nusoap_client("http://www.wuliucheng.com:8888/APP/IService.php?WSDL",true);
		$client->soap_defencoding = 'UTF-8';   //不传递中文参数的话这行没有也行
		$client->decode_utf8 = false; 
		$result = $client->call('request', array('data'=>$request), '', '');

/*
		$client = new SoapClient("http://192.168.1.10/FGY/APP/IService.php?WSDL");
		$result = $client->request($request);
*/		
		print_r($result);
	}
}
?>