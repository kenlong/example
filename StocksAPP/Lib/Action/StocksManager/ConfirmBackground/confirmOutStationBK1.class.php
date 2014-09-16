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

$bk = new confirmOutStationBK1();

class confirmOutStationBK1
{
	//构造函数
	public function __construct()
	{
		//get data
		$data = ShareMemory::getShareMemory('data');
		$this->confirmOutStationBK1($data);
	}

	/**
	 * 周转箱/表出仓方式
	 *
	 * @param array $data  表头数据
	 * @return array
	 */
	public function confirmOutStationBK1($data)
	{
		try
		{
			//system_out("scanneradd:".Session::get('scanneradd'));
			$scanner = new ScannerADD(Session::get('scanneradd'),Session::get('scannerport'));
			$action = new ConfirmStationAction();
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
			$boxheadlenght = Session::get('boxheadlength');
			$station = '' ;
			$code = '' ;
			//进行无限循环
			while ($while)
			{
				sleep(1);
				//读取是否有人为中断
				$while = ShareMemory::getShareMemory("while");
				if(!$while)
					break ;

				//读取数据
				$rtn = $scanner->readData();

				if(trim($rtn)=='' || $rtn==null || $rtn==false || substr($rtn,2)=='OK') continue ;
				$rtn = strtoupper($rtn);
				
				$device = substr($rtn,0,2);
				$rtn = substr($rtn,2);

				system_out("out rtn:".$rtn);
								
				//如果是周转箱字母头,则周转箱出仓
				if(substr($rtn,0,$boxheadlenght)==$boxhead)
				{
					$station = $rtn ;
					$code = '' ;
				}
				else
				{
					$station = '' ;
					$code = $rtn ;
				} //end if 1

				//如果两者有一不为空,则确认
				if(trim($station)!='' || trim($code)!='')
				{
					//system_out("out station:".$station);
					//system_out("out code:".$code);
					$result = $this->confirmOutStation($data,$station,$code);
					//确认失败
					if(is_string($result))
					{
						system_out("confirmOutStationBK1.error:".$result);
						//写错误信息
						array_push($confirmerror,$result) ;
						//将错误信息写进共享内存
						ShareMemory::setShareMemory('confirmerror',$confirmerror);

						$station='' ;
						$code = '' ;

						//亮红灯
						$scanner->sendRedLight($device);
						$scanner->readData();

						continue ;
					}

					//亮绿灯
					$scanner->sendGreenLight($device);
					$scanner->readData();

					//清空数据,继续执行
					$station = '';
					$code = '' ;
				} //end if 2
			}
		}
		catch (Executive $e)
		{
			system_out("StocksManager.confirmOutStationBK1 error:$e");
			throw new Exception($e);
		}
		//返回值
		return  $confirmerror ;

	}

	/**
	 * 确认出库表
	 *
	 * @param array $data  	//单据信息
	 * @param string $station  //周转想
	 * @param string $code     //表码
	 * @retur Boolean
	 */
	private function confirmOutStation($data,$station='',$code='')
	{
		try
		{
			$confirm = new ConfirmStationAction();
			$currentstocks = new StocksManagerAction();
			//单表出仓
			if($code!='')
			{
				$result = $currentstocks->queryCurrentStocks("code = '$code'");
				if(!$result)
				{
					return "库存里没有该表计[$code]!";
				}
				$rtn = $confirm->confirmOutstation($data,$code);
				if(!$rtn)
				{
					$rtn = $confirm->getError();
				}
				return $rtn ;
			}
			//整个周转箱出仓
			elseif ($station!='')
			{
				$result = $currentstocks->queryCurrentStocks("station = '$station'");
				if(sizeof($result)==0)
					return "库存里该周转箱[$station]没有表计!";
				for($i=0;$i<sizeof($result);$i++)
				{
					$rtn = $confirm->confirmOutstation($data,$result[$i]["code"]);
					if(!$rtn)
					{
						$rtn = $confirm->getError();
						return $rtn ;
					}
				}

				return $rtn;
			}
		}
		catch (Exception $e)
		{
			system_out("confirmOutStationBK1.confirmOutStation Error:" . $e->getMessage());
			throw new Exception($e);
		}

		return true ;
	}

} //end class confirmInStationBK3


?>