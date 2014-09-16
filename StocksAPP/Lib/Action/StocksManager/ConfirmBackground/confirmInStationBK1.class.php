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

			//关闭状态
			//开表灯

			$station = '' ;
			$code = '' ;
			system_out("starting confirm");
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
				
				if(substr($rtn,0,2)=='GN')
				{
					//如果位置码位空,则设置位置码
					if($station=='')
					{
						//赋值位置码
						$station = $rtn ;
						if($type=='type1')
						{
							$scanner->sendGreenLight($device);
							$scanner->readData();
						}
						continue ;
					}
					else
					{
						//如果已经扫了表位,且不是原来那个,则报错,重新开始
						if($station!=$rtn)
						{
							if($type=='type1')
							{
								$scanner->sendRedLight($device);
								$scanner->readData();
							}
							$station='' ;
							$code='' ;
							continue ;
						}
						else 
						{
							if($type=='type1')
							{
								$scanner->sendGreenLight($device);
								$scanner->readData();
							}
							continue ;
						}
					}
				}
				else
				{
					//如果没有扫表位置码,就扫表码,报错,重新开始
					if($station=='')
					{
						if($type=='type1')
						{
							$scanner->sendRedLight($device);
							$scanner->readData();
						}
						$station='';
						$code='';
						continue ;
					}
					//设置表码
					$code = $rtn;
				} //end if 1

				//如果两者都不为空,则确认
				if(trim($station)!='' && $station!='*' && trim($code)!='' && $code!='*')
				{
					//开始确认挂表位置和表的关系
					$item["station"] = $station ;
					$item["code"] = $code ;
					$result = $action->confirmInstation($billdata,$item);

					//确认错误
					if(!$result)
					{
						//亮红灯
						if($type=='type1')
						{
							$scanner->sendRedLight($device);
							$scanner->readData();
						}

						//获取错误
						$result = $action->getError();
						array_push($confirmerror,$result) ;
						
						system_out($result);

						//将错误信息写进共享内存
						ShareMemory::setShareMemory('confirmerror',$result);

						$station = '' ;
						$code = '' ;
						continue ;
					}

					//已确认
					//亮绿灯
					if($type=='type1')
					{
						$scanner->sendGreenLight($device);
						$scanner->readData();
					}

					//关闭灯
					try
					{
						system_out("starting trunoff light:$station ,length:".strlen($station));
						$result = $struct->trunOff(array("No"=>substr($station,0,4)),array($station),'put');
						if($result)
							system_out("trunoff light succeed");
						else 
							system_out("trunoff light fault");
					}
					catch(Exception $e)
					{
						system_out("trunoff light error:".$e->getMessage());
						$error = $struct->getLogs();
						system_out("错误日志:".$error);
						$station = '';
						$code = '';
						continue ;
					}
					
					//重置,开始新的等待
					$station = '';
					$code = '';
				} //end if 2
			}
		}
		catch (Executive $e)
		{
			system_out("StocksManager.ConfirmInStationBk1 error$e");
			throw new Exception($e);
		}

		//返回值
		return $confirmerror ;
	}

} //end class confirmInStationBK3


?>