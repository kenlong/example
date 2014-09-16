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

import("@.Action.StocksManager.StocksManagerAction");
import("@.Action.StocksManager.ConfirmStationAction");
import("@.Action.Scanner.ScannerADD");
import("@.Util.ShareMemory");
import("@.Action.StocksManager.StructControlAction");
import("@.Action.SystemSetup.SystemSetup");

$bk = new confirmInStationBK2();

class confirmInStationBK2
{
	//构造函数
	public function __construct()
	{
		//get billdat
		$billdate = ShareMemory::getShareMemory('billdate');
		$data = ShareMemory::getShareMemory('data');
		$this->confirmInStationBK2($billdate,$data);
	}

	/**
	 * /表架开一位置灯,确认一表
	 *
	 * @param string $billdate
	 * @param array $data
	 * @return boolean
	 */
	private function confirmInStationBK2($billdate,$data)
	{
		try
		{
			$stocksmanager = new StocksManagerAction();
			$scanner = new ScannerADD(Session::get('scanneradd'),Session::get('scannerport'));
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

			$station = '' ;
			$code = '' ;
			$limit = 0 ;

			//第一个灯位
			if(sizeof($data)>0)
			{
				$station = $data[0];
				//开灯
				$struct->trunOnPutLightByStation(array($station));
			}
			else
			{
				return ;
			}

			//进行无限循环
			while ($while)
			{
				sleep(1);
				//当前的位置码
				//system_out("station:".$station);

				//如果错了三次,则该表位跳过
				if($limit>=3)
				{
					array_push($confirmerror,"[$station]表位确认不成功!");
					ShareMemory::setShareMemory('confirmerror',$confirmerror);

					//将该位置码去掉
					array_shift($data);
					//获取新的位置码,并重新初始
					if(sizeof($data)>0)
					{
						$station = $data[0];
						$code = '' ;
						$limit = 0 ;

						//开灯
						$struct->trunOnPutLightByStation($station);
					}
					else
					{
						array_push($confirmerror,"表位进行确认完毕,如果还有表位没确认,请重新开始程序!");
						ShareMemory::setShareMemory("confirmerror",$confirmerror);
						return true ;
					}
				}

				//读取是否有人为中断
				$while = ShareMemory::getShareMemory("while");
				if(!$while)
				{
					system_out("break by user");
					//array_push($confirmerror,"人为中断确认!");
					ShareMemory::setShareMemory("confirmerror",$confirmerror);
					break ;
				}

				//读取数据
				$rtn = $scanner->readData();
				if($rtn=='' || $rtn==null || $rtn==false) continue ;
				$rtn = strtoupper($rtn);
				//system_out("result:".$rtn);

				$device = substr($rtn,0,2);
				$rtn = substr($rtn,2);

				//如果编码是表位,则退出
				if(substr($rtn,0,2)=='GN')
				{
					//亮红灯
					$scanner->sendRedLight($device);
					$scanner->readData();

					array_push($confirmerror,"请扫描表码,不要扫描位置码!");
					ShareMemory::setShareMemory('confirmerror',$confirmerror);
					$limit++ ;
					continue ;
				}
				else
				{
					//设置表码
					$code = $rtn;
				} //end if 1


				//如果两者都不为空,则确认
				if($station!='' &&$station!='*' && $code!='' && $code!='*')
				{
					//开始确认挂表位置和表的关系
					$item["station"] = $station ;
					$item["code"] = $code ;
					$result = $action->confirmInstation($billdate,$item);
					//确认失败
					if(!$result)
					{
						//亮红灯
						$scanner->sendRedLight($device);
						$scanner->readData();

						$result = $action->getError();
						//输出错误
						system_out("confirm error:".$result);
						array_push($confirmerror,$result) ;
						//将错误信息写进共享内存
						ShareMemory::setShareMemory('confirmerror',$confirmerror);

						$code = '';
						$limit++ ;

						continue ;
					}

					//确认成功
					//亮绿灯
					$scanner->sendGreenLight($device);
					$scanner->readData();

					//将该位置码去掉
					array_shift($data);

					//如果所有的表都扫完了,则终止循环
					if(sizeof($data)==0)
					{
						array_push($confirmerror,"表位进行确认完毕,如果还有表位没确认,请重新开始程序!");
						ShareMemory::setShareMemory("confirmerror",$confirmerror);
						ShareMemory::setShareMemory("while",false);
						$struct->close(array("No"=>substr($station,0,4)),'put');
						break ;
					}
					else
					{
						$station = $data[0];
						$code = '' ;
						$limit = 0 ;

						//开灯
						$struct->trunOnPutLightByStation(array($station));
					}
				} //end if 2
			}
		}
		catch (Executive $e)
		{
			system_out("StocksManager.confirmInStationBK2 error:$e");
			throw new Exception($e);
		}

		//返回值
		return $confirmerror ;
	}

} //end class confirmInStationBK3


?>