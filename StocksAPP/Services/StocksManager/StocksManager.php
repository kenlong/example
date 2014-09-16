<?php
import("@.Action.StocksManager.StructControlAction");
import("@.Action.StocksManager.StocksManagerAction");
import("@.Action.StocksManager.ConfirmStationAction");
import("@.Action.Scanner.ScannerADD");
import("@.Util.ShareMemory");
import("@.Util.ExecBackend");
import("@.Action.Code.CodeInfo");
class StocksManager
{
	/**
	 * 按品名，型号为分组查询库存
	 *
	 * @param unknown_type $condition
	 * @return unknown
	 */
	public function queryCurrentStocksByGoods($condition='')
	{
		try
		{
			$sql = "select goodsName,spec,sum(qty) as qty,voltage1,current1,". 
						  "direct,constant,grade,madeIn,madeDate,memo" .
					" from " . (DB_PREFIX?DB_PREFIX."_":"") . "currentstocks " ;
			if($condition!='')
				$sql .= " where " .$condition ;
				
			$sql.= "group by goodsName,spec,voltage1,current1";
			
			$fields = "goodsName,spec,qty,voltage1,current1,direct,constant,grade,madeIn,madeDate,memo" ;
			$stocks = new StocksManagerAction();
			$result = $stocks->queryCurrentStocksWithSQL($sql);
			$struct = $stocks->getStruct();

			if($fields!='*')
			{
				$field = explode(',',$fields);
				$visible = array("_type_"=>"INCLUDE","data"=>$field ) ;
				$struct["visible"] = $visible ;
				$struct["item"] = $field ;
			}

			$rtn["struct"] = $struct;
			$rtn["data"] = $result ;
			
			return $rtn ;
		}
		catch (Exception $e)
		{
			system_out("StocksManager.queryCurrentStocksByGoods:".$e);
			return false ;
		}
	}

	
	/**
	 * 查询当前库存
	 *
	 * @param $condition 条件
	 */
	public function queryCurrentStocks($condition)
	{
		try
		{
			$stocks = new StocksManagerAction();
			$result = $stocks->queryCurrentStocks($condition);

			return $result ;
		}
		catch (Executive $e)
		{
			system_out("StocksManager.queryCurrentStocks Exception:$e");
			return false ;
		}
	}


	/**
	 * 以表架位基准查询当前库存
	 *
	 * @param String $condition
	 * @return Array  表架数组
	 */
	public function queryCurrentStocksStructs($condition)
	{
		try
		{
			$stocks = new StocksManagerAction();

			$result = $stocks->queryCurrentStocksStructs($condition);
			if(!$result)
			{
				$result = $stocks->getError();
			}
			return $result ;
		}
		catch (Executive $e)
		{
			system_out("StocksManager.queryCurrentStocksStructs Exception:$e");
			throw new Exception($e);
		}
	}

	/**
	 * 获取单据列表
	 *
	 * @param string $sindate
	 * @param string $eindate
	 * @param string $soutdate
	 * @param string $eoutdate
	 * @param string $goodsName
	 * @param string $code
	 * @param string $billNo
	 * @param string $inoutType
	 * @return array
	 */
	public function queryBillList($sdate='',$edate='',$goodsName='',$code='*',$billNo='',$inoutType='',$place='',$billType='')
	{
		try
		{
			$stoks = new StocksManagerAction();
			$result = $stoks->queryBillList($sdate,$edate,$goodsName,$code,$billNo,$inoutType,$place,$billType);

			$struct = $this->getStruct();

			$visible = array("_type_"=>"INCLUDE","data"=>array("sysno","billNo","billDate","billType","place") ) ;
			$struct["visible"] = $visible ;
			$struct["item"] = array("sysno","billNo","billDate","billType","place") ;
			$struct["width"] = array("billNo"=>120);
			$rtn["struct"] = $struct;
			$rtn["data"] = $result ;

			return $rtn ;
		}
		catch (Exception $e)
		{
			system_out("StocksManager.queryBillList exception:".$e);
			throw new Exception($e);
		}
	}



	/**
	 * 查询出入库记录
	 *
	 * @param string $sdate	开始入库日期
	 * @param string $edate	结束入库日期
	 * @param string $goodsName	品名
	 * @param string $code		条码
	 * @param string $billNo	单据号
	 * @param string $inoutType	出/入库
	 * @param string $place		仓库
	 * @param string $billType	单据类型
	 * @return array
	 */
	public function query($sdate='',$edate='',$goodsName='',$code='*',$billNo='',$inoutType='',$place='',$billType='')
	{
		try
		{
			$stoks = new StocksManagerAction();
			$result = $stoks->query($sdate,$edate,$goodsName,$code,$billNo,$inoutType,'');

			return $result ;
		}
		catch (Executive $e)
		{
			system_out("StocksManager.query Exception:$e");
			throw new Exception($e);
		}
	}

	/**
	 * 保存
	 *
	 * @param Array $data  Example:$data["modify"] = array()
	 *                             $data["delete"} = array()
	 */
	public function save($data)
	{
		try
		{
			$stocks = new StocksManagerAction();
			$result = $stocks->save($data);
			if(!$result)
			{
				$result=$stocks->getError();
			}
			return $result ;
		}
		catch (Executive $e)
		{
			system_out("StocksManager.save Exception:$e");
			throw new Exception($e);
		}
	}

	/**
	 * 通过条码获取产品信息
	 *
	 * @param String $code
	 */
	public function getInfoByCode($code)
	{
		try
		{
			$action = new CodeInfo();
			$result = $action->getInfoByCode($code);
			if(!$result)
			{
				return false ;
			}

			return $result ;
		}
		catch (Exception $e)
		{
			system_out("StocksManager.getInfoByCode Exception:".$e);
			throw new Exception($e);
		}
	}


	/**
	 * 获取Datawindow结构
	 *
	 */
	public function getStruct($type='')
	{
		try
		{
			$stocks = new StocksManagerAction();
			$result = $stocks->getStruct();
			if(!$result)
			{
				$result=$stocks->getError();
				return $result ;
			}

			switch ($type)
			{
				case 'instocks':
					$field = "station,code,factoryNo,goodsName,spec,inqty,voltage1,current1," .
								  "direct,constant,grade,madeIn,madeDate,memo,place" ;
					break ;
				case 'outstocks':
					$field = "station,code,factoryNo,goodsName,spec,outqty,voltage1,current1," .
								  "direct,constant,grade,madeIn,madeDate,memo,place,station" ;
					break ;
				case 'reject':
					$field = "station,code,factoryNo,goodsName,spec,outqty,voltage1,current1," .
								  "direct,constant,grade,madeIn,madeDate,memo,place,station" ;
					break ;
				default:
					$field='*' ;
			}

			//如果字段不是全部的,则把必须的字段替换
			if($field!='*')
			{
				$field = explode(',',$field);
				$visible = array("_type_"=>"INCLUDE","data"=>$field ) ;
				$result["visible"] = $visible ;
				$result["item"] = $field ;
			}

			return $result ;
		}
		catch (Executive $e)
		{
			system_out("StocksManager.getStruct Exception:$e");
			throw new Exception($e);
		}
	}


	/**
	 * 确认表、位置对应码
	 *
	 * @param string $billdata
	 * @param Array $data   Example:$data["station"] = "GN01A0101"
	 *                              $data["code"] = "0001" ;
	 *
	 * @return boolean
	 */
	public function confirmInStation($billdata,$data)
	{
		try
		{
			$action = new ConfirmStationAction();

			//开始确认挂表位置和表的关系
			$result = $action->confirmInstation($billdata,$data);
			if(!$result)
			{
				$result = $action->getError();
			}
			return $result ;
		}
		catch (Exception $e)
		{
			system_out("StocksManager.confirmInStation Exception:$e");
			throw new Exception($e);
		}
	}

	/**
	 * 后台确认表、位置对应码
	 *
	 * @param String $sysno  //单据系统单号
	 * @param array $data    //要确认的表位 Example:array("GN01A0101","GN01A0201")
	 * @param Stirng $type   //类型  1:扫一位置,再扫一表置确认   2:开一位置灯,确认一表   3:周转箱方式,扫描周转箱,然后扫描表
	 */
	public function confirmInStationBackground($billdata,$data,$type)
	{
		try
		{
			$execbackend = new ExecBackEnd();
			$dir = dirname(__FILE__) . "/../../Lib/Action/StocksManager/ConfirmBackground" ;
			$program = '' ;
			ShareMemory::setShareMemory('billdata',$billdata);
			ShareMemory::setShareMemory('data',$data);
			//扫一位置,再扫一表置确认
			if($type=='1')
			{
				$program = "confirmInStationBK1.class" ;
			}

			//表架开一位置灯,确认一表
			if($type=='2')
			{
				$program = "confirmInStationBK2.class" ;
			}

			//周转箱方式确认
			if($type=='3')
			{
				$program = "confirmInStationBK3.class" ;
			}

			//检查程序名是否为空
			if($program=='')
			{
				system_out("StocksManager.confirmInStationBackground error:后台程序名不能为空!");
				return '发生了意外错误';
			}

			system_out("运行后台程序:$program");
			
			//exec
			$result = $execbackend->exec($dir."/$program");

			if(!$result)
			{
				$result = $execbackend->getError();
				system_out("StocksManager.confirmInStationBackground error:$result");
				if($result=='程序已经在运行')
				{
					return '确认挂表程序已经在运行,如果要重新运行,请单击结束挂表!';
				}
				elseif ($result=='所指定的程序不存在')
				{
					return '开启确认挂表程序出错!';
				}
				else
				{
					return '其他错误,开启挂表程序出错';
				}
			}
		}
		catch (Executive $e)
		{
			system_out("StocksManager.confirmInStationBackground Exception:$e");
			throw new Exception($e);
		}

		return true ;

	}


	/**
	 * 确认出库表
	 *
	 * @param array $data  	//单据信息
	 * @param String $validstation  //确认已经取了表的表位
	 * @param string $station  //周转想
	 * @param string $code     //表码
	 * @retur Boolean
	 */
	public function confirmOutStation($data,$validstation='',$station='',$code='')
	{
		try
		{
			$confirm = new ConfirmStationAction();
			$currentstocks = new StocksManagerAction();
			//单表出仓
			if($code!='')
			{
				$result = $currentstocks->queryCurrentStocks("code = '$code'");
				if(sizeof($result)==0)
					return "库存里没有该表计!";
				$rtn = $confirm->confirmOutstation($data,$code);
				return $rtn ;
			}
			//整个周转箱出仓
			elseif ($station!='')
			{
				$result = $currentstocks->queryCurrentStocks("station = '$station'");
				if(sizeof($result)==0)
					return "库存里该周转箱没有表计!";

				for($i=0;$i<sizeof($result);$i++)
				{
					$rtn = $confirm->confirmOutstation($data,$result[$i]["code"]);
					if(!$rtn)
					{
						$rtn = $confirm->getError();
						return $rtn ;
					}
				}
			}
			//表架出仓
			else
			{
				if(is_array($validstation))
				{
					for($i=0;$i<sizeof($validstation);$i++)
					{
						$result = $currentstocks->queryCurrentStocks("station = '$validstation[$i]'");
						if(sizeof($result)>0)
						{
							$rtn = $confirm->confirmOutstation($data,$result[0]["code"]);
							if(!$rtn)
							{
								$rtn = $confirm->getError();
								return $rtn ;
							}
						}
					}
				}
				//完成确认,删除待确认库里的记录
				$this->delUnconfirmStation($validstation,'D');
			}
		}
		catch (Exception $e)
		{
			system_out("StocksManager.confirmOutStation Error:" . $e->getMessage());
			throw new Exception($e);
		}

		return true ;
	}

	/**
	 * 后台处理确认出库
	 * @param array $data 单据表头数据
	 * @param string $type 类型 $type = 'struct' 表架方式确认方式,$type='other' 周转箱/表确认方式
	 */
	public function confirmOutStationBackGround($data,$validstation,$type)
	{
		if($type=='struct')
		{
			$result = $this->confirmOutStation($data,$validstation,'','');

			return $result ;
		}

		//周转想/表出仓方式
		$execbackend = new ExecBackEnd();
		$dir = dirname(__FILE__) . "/../../Lib/Action/StocksManager/ConfirmBackground" ;
		$program = '' ;

		$result = ShareMemory::setShareMemory('data',$data);
		if(!$result)
		{
			system_out("error:".$result);
		}
		$data = ShareMemory::getShareMemory('data');
		$program = "confirmOutStationBK1" ;

		//检查程序名是否为空
		if($program=='')
		{
			system_out("StocksManager.confirmOutStationBackGround error:后台程序名不能为空!");
			return '发生了意外错误';
		}

		//exec
		$result = $execbackend->exec($dir."/$program");
		if(!$result)
		{
			$result = $execbackend->getError();
			system_out("StocksManager.confirmOutStationBackGround error:$result");
			if($result=='程序已经在运行')
			{
				return '确认取表程序已经在运行,如果要重新运行,请单击结束取表!';
			}
			elseif ($result=='所指定的程序不存在')
			{
				return '开启确认取表程序出错!';
			}
			else
			{
				return '其他错误,开启挂表程序出错';
			}
		}

		return true ;
	}

	/**
	 * 完成确认,返回确认错误信息
	 *
	 * @param array $data
	 * @param string $state
	 */
	public function finishConfirm($type='')
	{	
		try
		{
			ShareMemory::setShareMemory('while',false);
			//暂停一下
			//sleep(2);

			$confirmerror = ShareMemory::getShareMemory('confirmerror');
			$rtn = ($confirmerror)?$confirmerror:'' ;

			//是否有两个表,确认了同一个表位
			$confirm = new ConfirmStationAction();
			$result = $confirm->checkWhetherDuplic();
			if($result)
			{
				if($rtn=='')
					$rtn = array();

				for ($i=0;$i<sizeof($result);$i++)
				{
					$err = "[".$result[$i]["station"]."]表位确认了多个表[".$result[$i]['code']."]请更正";
					array_push($rtn,$err);
				}
			}

			//如果没有值,则返回成功
			return $rtn?$rtn:true ;

		}
		catch (Executive $e)
		{
			system_out("StocksManager.finishConfirm error:$e");
			throw new Exception($e);
		}
	}

	/**
	 * 检查已挂表的表位是否全部确认完毕
	 * @param array $data  已经挂了表的表位
	 * @param string $state  U 挂表  D 取表
	 *
	 * @return boolean ;
	 *
	 */
	public function checkStationConfirmComplete($data,$state)
	{
		$stations = array();
		return true ;
		try
		{
			$confirm = new ConfirmStationAction();
			$result = $confirm->queryUnconfirmStation("state ='$state' and confirm=0 ");
			//如果没有记录,则表示全部确认了
			if(!$result)
			{
				return true ;
			}
			
			$temp = "'".implode("','",$data)."'";
			
			$stocks = new CurrentStocksDao();
			$exits = $stocks->findAll("station in (" . $temp . ")")->toResultSet();
			
			if($exits)
			{
				$temp = array();
				for($i=0;$i<sizeof($exits);$i++)
				{
					array_push($temp,$exits[$i]["station"]);
				}
				$exits = $temp ;
			}
			
			for($i=0;$i<sizeof($result);$i++)
			{
				$val = $result[$i]["station"] ;
				$rtn = array_search($val,$exits);
				//如果已经入仓,则跳过
				if(is_numeric($rtn)) continue ;
				
				$rtn = array_search($val,$data);
				if(is_numeric($rtn))
				{
					$stations[] = $val ;
				}
			}

			//返回值
			$rtn = array();

			$data = implode(',',$stations);
			if($data)
			{
				$data = '还有表位挂了表未确认:'.$data ;
				array_push($rtn,$data);
			}

			//是否有两个表,确认了同一个表位
			$confirm = new ConfirmStationAction();
			$result = $confirm->checkWhetherDuplic();
			if($result)
			{
				for ($i=0;$i<sizeof($result);$i++)
				{
					$err = "[".$result[$i]["station"]."]表位确认了多个表[".$result[$i]['code']."]请更正";
					array_push($rtn,$err);
				}
			}

			//如果没有错误信息,则返回true
			return $rtn?$rtn:true ;
		}
		catch (Executive $e)
		{
			system_out("StocksManager.checkStationConfirmComplete error:$e");
			throw new Exception($e);
		}
	}


	/**
	 * 如果是表架的,则删除表架灯开灯记录
	 *
	 * @param array $data
	 * @param string $state
	 */
	public function delUnconfirmStation($data,$state)
	{		
		try
		{
			$confirm = new ConfirmStationAction();
			if(is_array($data))
			{
				$condition = "`station` in ('" . implode($data,"','") . "') and `state`='$state'";
			}
			else
			{
				$condition = "`state`='$state'";
			}
			$confirm->delUnconfirmStation($condition);
		}
		catch (Executive $e)
		{
			system_out("StocksManager.finishConfirn Exceptoin:$e,condition:$condition");
			throw new Exception($e);
		}

		return true ;
	}
}

?>
