<?php
define('APP_NAME', 'GuoyaoSOAP'); 
define('APP_PATH', realpath(".")); 
// 定义ThinkPHP框架路径 
define('THINK_PATH', APP_PATH.'/../ThinkPHP2'); 

// 加载框架入口文件  
require(THINK_PATH."/ThinkPHP.php"); 

//实例化一个网站应用实例 
$App = new App();  
$App->init();

try
{
	vendor("WebOrderService",LIB_PATH."/Service");
	
	ini_set("soap.wsdl_cache_enabled", "0");
	$server = new SoapServer(LIB_PATH."/Service/WebOrder.wsdl", array('soap_version' => SOAP_1_2));   
	$server->setClass("WebOrderService");   
	$server->handle();
}
catch (Exception $e)
{
	Log::record($e->getMessage());	
}
if(C('LOG_RECORD')) Log::save();
?>