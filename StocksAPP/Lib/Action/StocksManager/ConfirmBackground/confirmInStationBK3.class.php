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

$bk = new confirmInStationBK3();

class confirmInStationBK3
{
	//构造函数
	public function __construct()
	{
		//get $billdata
		$billdata = ShareMemory::getShareMemory('billdata');
		$this->confirmInStationBK3($billdata);
	}

	/**
	 * 周转箱确认方式
	 *
	 * @param string $billdata
	 * @return boolean
	 */
	private function confirmInStationBK3($billdata)
	{
		try
		{
			$type  = Session::get('scannertype')?Session::get('scannertype'):'type1';
			$stocksmanager = new StocksManagerAction();
			$scanner = new ScannerADD(Session::get('scanneradd'),Session::get('scannerport'),$type);
			$action = new ConfirmStationAction();
			$struct = new StructControlAction();
			$device = null ;  //条码枪地址
			//确认错误信息
			$confirmerror = array();

			//循环参数
			$while=true;

			//删除错误信息
			ShareMemory::delShareMemory("confirmerror");
			//设置共享内存
			ShareMemory::setShareMemory("while",$while);

			$boxhead = Session::get('boxhead');
			$boxheadlength = Session::get('boxheadlength');
			$station = '' ;
			$code = '' ;
			system_out("start confirmInStationBK3");
			//进行无限循环
			while ($while)
			{
				sleep(1);
				//读取是否有人为中断
				$while = ShareMemory::getShareMemory("while");
				if(!$while)
				{
					//array_push($confirmerror,"人为中断确认!");
					ShareMemory::setShareMemory("confirmerror",$confirmerror);

					break ;
				}

				//读取数据
				$rtn = $scanner->readData();
				if(!$rtn) continue ;
				
				//$rtn = strtoupper($rtn);
				$device = $scanner->device ;
											
				//如果是周转箱字母头,则设置station
				if(substr($rtn,0,$boxheadlength)==$boxhead)
				{
					system_out("statino:".$rtn);
					$station = $rtn ;
					if($type=='type1')
					{
						$scanner->sendGreenLight($device);
						$scanner->readData();
					}
				}
				else
				{
					//如果没有扫周转箱码,就扫表码,报错,重新开始
					if($station=='')
					{
						if($type=='type1')
						{
							$scanner->sendRedLight($device);
							$scanner->readData();
						}
						$code='';
						array_push($confirmerror,"请先扫描周转箱:");
						ShareMemory::setShareMemory('confirmerror',$confirmerror);
						continue ;
					}
					else
					{
						//设置表码
						$code = $rtn ;
					}
				} //end if 1

				//system_out("station:".$station);
				//system_out("code:".$code);

				//如果两者都不为空,则确认
				if(trim($station)!='' && $station!= null && trim($code)!='' && $code!=null)
				{
					//system_out('begin confirm');

					//开始确认挂表位置和表的关系
					$item["station"] = $station ;
					$item["code"] = $code ;
					$result = $action->confirmInstation($billdata,$item);
					//确认失败
					if(!$result)
					{
						$result = $action->getError();
						array_push($confirmerror,$result) ;
						system_out($result);

						//将错误信息写进共享内存
						ShareMemory::setShareMemory('confirmerror',$confirmerror);
						$code = '' ;
						
						if($type=='type1')
						{
							$scanner->sendRedLight($device);
							$scanner->readData();
						}

						continue ;
					}

					//确认成功
					if($type=='type1')
					{
						$scanner->sendGreenLight($device);
						$scanner->readData();
					}
					//清空条码继续等待扫描
					$code = '' ;
				} //end if 2

			}
		}
		catch (Executive $e)
		{
			system_out("StocksManager.confirmInStationBK3 error:$e");
			throw new Executive($e) ;
		}


		//返回值
		return  $confirmerror ;
	}
} //end class confirmInStationBK3


?>
