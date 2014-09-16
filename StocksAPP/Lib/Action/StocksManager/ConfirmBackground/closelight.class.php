<?php
//定义项目名称，如果不定义，默认为入口文件名称
define('APP_NAME', 'StocksAPP');
define('APP_PATH', '/opt/APP/StocksAPP');
// 加载FPW框架公共入口文件 
$gatewayPath='/opt/APP/FPW_1.1/FPW.php';
require_once($gatewayPath);
//实例化一个网站应用实例
$App = new App();

//开启session
session_start();


/**获取系统设置的参数**/
import("@.Action.SystemParm.GetSystemParm");
GetSystemParm::querySystemParm();
GetSystemParm::getCodeParameter();

//应用程序初始化
$App->init();

import("@.Action.StocksManager.StructControlAction");
import("@.Action.StocksManager.StocksManagerAction");
import("@.Action.StocksManager.ConfirmStationAction");
import("@.Action.Scanner.ScannerADD");
import("@.Util.ShareMemory");
import("@.Action.SystemSetup.SystemSetup");

$bk = new confirmInStationBK1();

class confirmInStationBK1
{
	//构造函数
	public function __construct()
	{
		//get $billdata
		$billdata = ShareMemory::getShareMemory('billdata');

		//开启后台程序
		$this->confirmInStationBK1($billdata);
	}

	/**
	 * 扫一位置,再扫一表置确认
	 *
	 * @param string $billdata
	 * @return boolean
	 */
	private function confirmInStationBK1($billdata)
	{
		try
		{
			$struct = new StructControlAction();

			//关闭状态
			//开表灯

			$station = '' ;
			$code = '' ;
			system_out("starting trunoff light");

			$result = $struct->trunOff(array("No"=>"GN05"),array("GN05A0101"),'put');
			if(!$result)
				system_out("trun off false");
			else 
				system_out("trun off succeed");
		}
		catch (Executive $e)
		{
			system_out("StocksManager.ConfirmInStationBk1 error:$e");
			throw new Exception($e);
		}

		//返回值
		return $confirmerror ;
	}

} //end class confirmInStationBK3


?>