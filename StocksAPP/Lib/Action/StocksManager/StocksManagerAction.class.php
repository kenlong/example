<?php
import("@.Action.StocksIndex.StocksIndexAction");
import("@.Action.Code.CodeIndexAction");
import("@.Action.ParameterSetup.OtherParamSetupAction");
import("@.Action.Interface.MIS");
class StocksManagerAction
{
	private $errors = null ;
	/**
	 * 查询当前库存
	 *
	 * @param $sql
	 */
	public function queryCurrentStocksWithSQL($sql='')
	{
		$dao = new CurrentStocksDao();
		try
		{
			$result = $dao->query($sql)->toArray();
		}
		catch (Executive $e)
		{
			$this->setError("StocksMangerAction.queryCurrentStocks error:
							查询当前库存出错,errcode:$result,sql:$sql") ;
			throw new Exception($e);
		}

		return $result;
	}
	
	/**
	 * 查询当前库存
	 *
	 * @param $conditon
	 */
	public function queryCurrentStocks($condition)
	{
		try 
		{
			$dao = new CurrentStocksDao();
			$result = $dao->findAll($condition)->toResultSet();
			
			return $result ;
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
	

	/**
	 * 按条件查询当前库存
	 *
	 * @param String $condition
	 * @return Array  表架数组
	 */
	public function queryCurrentStocksStructs($condition)
	{
		try
		{
			$dao = new CurrentStocksDao();

			$data = array();

			//查询可用的表架
			switch (DB_TYPE)
			{
				case 'mysql':
					$sql = "select SUBSTRING(station,1,4) as station
							  from " . (DB_PREFIX?DB_PREFIX."_":"") . "currentstocks
						  group by SUBSTRING(station,1,4)
						  order by SUBSTRING(station,1,4) " ;
					break ;
				case 'SQLite':
					$sql = "select substr(station,1,4) as station
							  from " . (DB_PREFIX?DB_PREFIX."_":"") . "currentstocks
						  group by substr(station,1,4)
						  order by substr(station,1,4) " ;
					break ;
				default:
					return false ;
			}
			
			$struct =$dao->query($sql)->toArray();
			//按表架查询
			for($i=0;$i<sizeof($struct);$i++)
			{
				$item = array();
				$stations = array();
				$sql = "select station
				          from " . (DB_PREFIX?DB_PREFIX."_":"") . "currentstocks
				         where station like '" . $struct[$i]["station"]. "%'"
							   . " and " . $condition ;

				$result = $dao->query($sql)->toArray();

				for($j=0;$j<sizeof($result);$j++)
				{
					array_push($stations,$result[$j]["station"]);
				}

				$item["No"] = $struct[$i]["station"] ;
				$item["value"] = $stations ;

				array_push($data,$item);
			}

			return $data ;
		}
		catch (Executive $e)
		{
			$this->setError("按表架方式查询数据出错,errcode:$condition");
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
	public function queryBillList($sdate,$edate,$goodsName='',$code='*',$billNo='',$inoutType='*',$place='',$billType='')
	{
		try
		{
			$querycondition = '' ;
			//如果开始日期和结束日期都有则用between,否则用=
			if($sdate!='' && $edate!='')
			{
				if($querycondition != '') $querycondition .= " and " ;
				$querycondition .= " ( `billDate` between date('$sdate') and date('$edate') ) " ;
			}
			else if($sdate!='')
			{
				if($querycondition != '') $querycondition .= " and " ;
				$querycondition .= " ( `billDate` = date('$sdate') ) ";
			}
			else if($edate!='')
			{
				if($querycondition != '') $querycondition .= " and " ;
				$querycondition .= " ( `billDate` = date('$edate') ) ";
			}

			//品名
			if($goodsName!='')
			{
				if($querycondition!='') $querycondition .= " and " ;
				$querycondition .= " ( `goodsName` = '$goodsName' ) " ;
			}

			//条码
			if($code!='*')
			{
				if($querycondition!='') $querycondition .= " and " ;
				$querycondition .= " ( '$code' = `code` ) ";
			}

			//单据号
			if($billNo!='')
			{
				if($querycondition!='') $querycondition .= " and " ;
				$querycondition .= "( `billNo` = '$billNo') " ;
			}

			//单据类型
			if($inoutType!='')
			{
				if($querycondition!='') $querycondition .= " and " ;
				$querycondition .= "( `inoutType` = '$inoutType') " ;
			}

			//仓库
			if($place!='')
			{
				if($querycondition!='') $querycondition .= " and " ;
				$querycondition .= "( `place` = '$place') " ;
			}

			//单据类型
			if($billType!='')
			{
				if($querycondition!='') $querycondition .= " and " ;
				$querycondition .= "( `billType` = '$billType') " ;
			}
			$table = StocksIndexAction::getTableNameByDate($sdate,$edate);
			if(!$table)
			{
				system_out("StocksManagerAction.queryBillList error:获取表名出错,errcode:$table,
							sdate:$sdate,edate:$edate");
				$this->setError("获取存储表名出错!");
				return false ;
			}
			
			if(is_string($table))
				$table = array(array("tablename"=>$table));
							
			$volist = new VoList();
			
			$dao = new StocksDao();
			
			//开始查询
			for($i=0;$i<sizeof($table);$i++)
			{
				$tablename = $table[$i]["tablename"] ;
				$result = $dao->findAll($querycondition,$tablename,'sysno,billNo,billDate,billType,place','sysno desc','','sysno,billNo,billDate,billType,place');
				
				$volist->addAll($result);
			}
			
			$data = array();			
			$result = $volist->toResultSet();
			
			if(!$result) return false ;
			//将不重复的值拿出来
			$max = sizeof($result);
			for($i=0;$i<$max;$i++)
			{
				$item = array("sysno"=>$result[$i]["sysno"],
							  "billNo"=>$result[$i]["billNo"],
							  "billDate"=>$result[$i]["billDate"],
							  "billType"=>$result[$i]["billType"],
							  "place"=>$result[$i]["place"]);

				$searchresult = array_search($item,$data) ;
				if(is_numeric($searchresult))
				{
					continue ;
				}

				array_push($data,$item);
			}
			
			return $data ;

		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}

	}


	/**
	 * 查询出入库记录
	 *
	 * @param string $sdate	开始日期
	 * @param string $edate	结束日期
	 * @param string $goodsName	品名
	 * @param string $code		条码
	 * @param string $billNo	单据号
	 * @param string $inoutType	出/入库
	 * @param string $place		仓库
	 * @param string $billType	单据类型
	 * @return array
	 */
	public function query($sdate='',$edate='',$goodsName='',$code='*',$billNo='',$inoutType='',$place='',$billType='',$client='')
	{
		try
		{
			$dao = new StocksDao();

			$querycondition = '' ;
			
			//如果开始日期和结束日期都有则用between,否则用=
			if($sdate!='' && $edate!='')
			{
				if($querycondition != '') $querycondition .= " and " ;
				$querycondition .= " ( `billDate` between date('$sdate') and date('$edate') ) " ;
			}
			else if($sdate!='')
			{
				if($querycondition != '') $querycondition .= " and " ;
				$querycondition .= " ( `billDate` = date('$sdate') ) ";
			}
			else if($edate!='')
			{
				if($querycondition != '') $querycondition .= " and " ;
				$querycondition .= " ( `billDate` = date('$edate') ) ";
			}

			//品名
			if($goodsName!='')
			{
				if($querycondition!='') $querycondition .= " and " ;
				$querycondition .= " ( `goodsName` = '$goodsName' ) " ;
			}

			//条码
			if($code!='*')
			{
				if($querycondition!='') $querycondition .= " and " ;
				$querycondition .= " ( '$code' = `code` ) ";
			}

			//单据号
			if($billNo!='')
			{
				if($querycondition!='') $querycondition .= " and " ;
				$querycondition .= "( `billNo` = '$billNo') " ;
			}

			//单据类型
			if($inoutType!='')
			{
				if($querycondition!='') $querycondition .= " and " ;
				$querycondition .= "( `inoutType` = '$inoutType') " ;
			}

			//仓库
			if($place!='')
			{
				if($querycondition!='') $querycondition .= " and " ;
				$querycondition .= "( `place` = '$place') " ;
			}

			//单据类型
			if($billType!='')
			{
				if($querycondition!='') $querycondition .= " and " ;
				$querycondition .= "( `billType` = '$billType') " ;
			}
			
			//客户
			if($client!='')
			{
				if($querycondition!='') $querycondition .= " and " ;
				$querycondition .= "( `client` = '$client') " ;
			}
			
			$table = StocksIndexAction::getTableNameByDate($sdate,$edate);
			if(!$table)
			{
				//system_out("StocksManagerAction.query error:获取表名出错,errcode:$table,
				//			sdate:$sdate,edate:$edate");
				//$this->setError("获取存储表名出错!");
				return false ;
			}
			
			if(is_string($table))
				$table = array(array("tablename"=>$table));

			$volist = new VoList();
			//开始查询
			for($i=0;$i<sizeof($table);$i++)
			{
				$tablename = $table[$i]["tablename"] ;
				$result = $dao->findAll($querycondition,$tablename);
				
				$volist->addAll($result);
			}
			
			return ($volist)?$volist->toResultSet():false;
		}
		catch (Executive $e)
		{
			$this->setError("StocksManagerAction.query error:$e,tablecondition:$tablename
							querycondtion:$querycondition");
			throw new Exception($e);
		}
	}

	/**
	 * 保存
	 *
	 * @param Array $data  Example:$data["modify"] = array()
	 *                             $data["delete"} = array()
	 * @return boolean true if succeed
	 */
	public function save($data)
	{
		//明细表
		$stocks = new StocksDao();
		$stocks->startTrans();

		//当前库存表
		$current = new CurrentStocksDao();
		$current->startTrans();

		$modify = $data["modify"] ;
		$delete = $data["delete"] ;
		//删除的数据
		for($i=0;$i<sizeof($delete);$i++)
		{
			try
			{
				$tablename = StocksIndexAction::getSaveTableName($delete[$i]["billDate"]);				
				if($delete[$i]["id"] !='')
				{
					$vo = $stocks->find("id=".$delete[$i]["id"],$tablename);
					//if($vo==true && $vo->station != '' && $vo->station != null)
					//{
					//	$stocks->rollback();
					//	$current->rollback() ;
					//	$this->setError("[" . $delete[$i]["code"] . "]已经确认位置,不能删除");
					//	return false ;
					//}
					
					$rtn = $stocks->deleteById($delete[$i]["id"],$tablename);

					//减库存
					if($vo != null && !$vo->isEmpty())
					{
						$rtn = $this->createCurrentStocks($current,'',$vo->toArray());
						if(!$rtn)
						{
							$stocks->rollback();
							$current->rollback();
							$this->setError($this->getError());
							return false ;
						}
					}
				} //end if
			}
			catch (Exception $e)
			{
				$current->rollback() ;
				$stocks->rollback() ;
				throw new Exception($e);
			} //end try
		} //end for

		//保存人员近参数表
		$otherparm = new OtherParmSetupAction();
		$otherparm->addParm('other','member',$modify[0]['sendMan']);
		$mis = new MIS();
		
		//添加/修改的数据
		for($i=0;$i<sizeof($modify);$i++)
		{
			//保存进库存明细表
			try
			{
				$tablename = StocksIndexAction::getSaveTableName($modify[$i]["billDate"]);
				if($modify[$i]["id"] =='' || $modify[$i]["id"] == null || $modify[$i]["id"] == 0 )
				{
					$vo = $stocks->createVo('add','','id',0,$modify[$i]);

					$result = $stocks->add($vo,$tablename);
					if(!$result)
					{
						$stocks->rollback() ;
						$current->rollback() ;

						system_out("StocksManagerActin.save error:新增数据保存出错,errcode:$result");
						$this->setError("数据保存出错!") ;
						return false ;
					}
					//生成当前库存表
					$rtn = $this->createCurrentStocks($current,$vo->toArray(),'');
					if(!$rtn)
					{
						$current->rollback();
						$stocks->rollback();
						$this->setError($this->getError());
						return false;
					}
					
					//增加条码索引
					if($vo->code !='' && $vo->code != null)
						CodeIndexAction::addCodeIndex($vo->billDate,$vo->code);
					
					//输出出库列表
					$result = $mis->outPut($modify[$i]);
				}
				else
				{
					$vo = $stocks->find("id=".$modify[$i]["id"],$tablename);
					if(!$vo)
					{
						$stocks->rollback();
						$current->rollback();
						$this->setError("数据保存出错,数据在保存前已被删除!") ;
						return false ;
					}
					
					$oldvo = clone $vo ;
					
			        //给Vo对象赋值
			        $this->refreshStocksInfo(&$vo,$modify[$i]);			        
					
					$result = $stocks->save($vo,$tablename);
					if(!$result)
					{
						$stocks->rollback();
						$current->rollback() ;

						system_out("StocksManagerActin.save error:修改数据保存出错,errcode:$result");
						$this->setError("数据保存出错,数据在保存前已被删除!") ;
						return false ;
					}
					//生成当前库存表
					$rtn = $this->createCurrentStocks($current,$vo->toArray(),$oldvo->toArray());
					if(!$rtn)
					{
						$current->rollback();
						$stocks->rollback();
						$this->setError($this->getError());
						return false;
					}
				} //end if			
			}
			catch (Exception $e)
			{
				$current->rollback() ;
				$stocks->rollback() ;
				throw new Exception($e);
			} //end try
		} //end for

		$stocks->commit() ;
		$current->commit() ;

		return true ;
	}

	/**
	 * 建立条码索引
	 *
	 * @param $billdate
	 * @param $code
	 * @return boolean
	 */
	private function createCodeIndex($billdate,$code)
	{
		try
		{
			$index = new CodeIndexAction();
			
			$vo = CodeIndexAction::queryIndexByDate($billdate,'0');
			if($vo)
			{
				$vo['codeindex'] .= $code ."/";
			}
			else
			{
				$vo = new CodeIndexVo();
				$vo->indexdate = $billdate ;
				$vo->codeindex = $code . "/" ;
				$vo->closed = 0 ;
			}
			
			$result = CodeIndexAction::saveIndex($vo);
			return $result ;
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
	
	
	/**
	 *	建立当前库存
	 *
	 * @param $stocks Dao 库存dao实例
	 * @param $current Dao  当前库存dao实例
	 * @param $tablename String 库存表名
	 * @param $data Array 数据
	 */
	public function createCurrentStocks($current,$newdata,$olddata)
	{
		if($newdata['code']!='' || $olddata['code']!='')
		{
			$result = $this->createCurrentStocksByCode($current,$newdata,$olddata);
		}
		else
		{
			$result = $this->createCurrentStocksByName($current,$newdata,$olddata);
		}

		return $result ;

	}

	/**
	 * 增加条码产品库存
	 *
	 * @param $current
	 * @param $newdata
	 * @param $olddata
	 * @return boolean
	 */
	private function createCurrentStocksByCode($current,$newdata='',$olddata='')
	{
		try
		{  
			//入库,增加库存
			if($newdata!='' && $olddata=='' && $newdata['inqty'] != 0)
			{
				$result = $this->createCurrentStocksByCodeAdd($current,$newdata,$newdata['inqty']);
				return $result ;
			}
			
			//入库删除,减少库存
			if($newdata=='' && $olddata!='' && $olddata['inqty']!=0) 
			{
				$result = $this->createCurrentStocksByCodeDell($current,$olddata,$olddata['inqty']);
				return $result ;
			}
			
			//新增出库,减少库存
			if($newdata!='' && $olddata=='' && $newdata['outqty'] != 0)
			{
				$result = $this->createCurrentStocksByCodeDell($current,$newdata,$newdata['outqty']);
				return $result ;
			}
			
			//出库删除,增增加库存
			if($newdata=='' && $olddata!='' && $olddata['outqty']!=0) 
			{
				$result = $this->createCurrentStocksByCodeAdd($current,$olddata,$olddata['outqty']);
				return $result ;
			}	
			
			//改
			if($newdata["place"]!=$olddata["place"])
			{
				//删旧
				$result = $this->createCurrentStocksByCodeDell($current,$olddata,$olddata['inqty']);
				if(!$result) return false ;
				//加新
				$result = $this->createCurrentStocksByCodeAdd($current,$newdata,$newdata['inqty']);
			}
			else 
			{	
				$result = $this->createCurrentStocksByCodeModify($current,$newdata,$olddata);
			}
			
			return $result ;
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
	
	/**
	 * 增加条码库存
	 *
	 * @param $current
	 * @param $newdata
	 * @return boolean
	 */
	private function createCurrentStocksByCodeAdd($current,$newdata,$qty)
	{
		try 
		{
			$vo = $current->find("code = '".$newdata['code'] . "' and place='" . $newdata['place'] . "'");
			if($vo)
			{
				$this->setError("[".$newdata['code']."][".$newdata["place"]."]该表计已经入库,不能重复入库!");
				return false ;
			}
			else 
			{
				$vo = $current->createVo('add','','id',0,$newdata);
				$vo->place = $newdata['place'] ;
				$vo->indateDate = $newdata["billDate"]	;
				$vo->qty = $qty;							
				$current->add($vo);
			}
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
		
		return true ;
	}
	
	/**
	 * 删除条码库存
	 *
	 * @param  $current
	 * @param  $olddata
	 * @return boolean
	 */
	private function createCurrentStocksByCodeDell($current,$olddata,$qty)
	{
		try
		{
			$vo = $current->find("code = '".$olddata['code'] . "' and place='" . $olddata['place'] . "'");
			if($vo)
			{
				//即时库存
				$vo->qty = $vo->qty - $qty ;
				//库存是否小于0
				if($vo->qty<0)
				{
					$this->setError("[".$olddata['code']."][".$olddata["place"]."]库存不足!");
					return false ;
				}
				//库存是否大于1
				if($vo->qty>1)
				{
					$this->setError("[".$olddata['code']."][".$olddata["place"]."]该表计已经入库,不能重复入库!");
					return false ;
				}
				//库存是否为0
				if($vo->qty==0)
				{
					$current->deleteById($vo->id);
				}
				else 
				{
					$current->save($vo);
				}
			}
			else 
			{
				$this->setError("[".$olddata['code']."][".$olddata["place"]."]库存不足!");
				return false ;
			}
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
		
		return true ;
	}
	
	/**
	 * 更改条码库存
	 *
	 * @param  $current
	 * @param  $newdata
	 * @param  $olddata
	 * @return boolean
	 */
	private function createCurrentStocksByCodeModify($current,$newdata,$olddata)
	{
		try
		{
			$vo = $current->find("code = '".$olddata['code'] . "' and place='" . $olddata['place'] . "'");
			if($vo)
			{
				$this->refreshStocksInfo(&$vo,$newdata);
				$current->save($vo);
			}
			else //库存没有资料,则不更改资料
				return true ;
			
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
		
		return true ;
	}
	
	/**
	 * 通过品名规格建立库存
	 *
	 * @param  $current
	 * @param  $newdata
	 * @param  $olddata
	 * @return boolean
	 */
	private function createCurrentStocksByName($current,$newdata,$olddata)
	{
		try
		{   
			if($newdata!='' && $olddata=='')//增
			{
				$result = $this->createCurrentStocksByNameAdd($current,$newdata);
			}
			elseif ($newdata=='' && $olddata!='')//删
			{
				$result = $this->createCurrentStocksByNameDel($current,$olddata);
			}
			else //改
			{
				$result = $this->createCurrentStocksByNameModify($current,$newdata,$olddata);
			}
			
			return $result ;
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
		
		return true ;	
	}
	
	/**
	 * 增加品名库存
	 *
	 * @param  $current
	 * @param  $newdata
	 */
	private function createCurrentStocksByNameAdd($current,$newdata)
	{
		try
		{
			$vo = $current->find("goodsName= '".$newdata['goodsName']."' and spec = '" . $newdata['spec'] . "' and place = '" . $newdata['place'] . "'");
			if($vo)
			{
				$this->refreshStocksInfo($vo,$newdata);
				$vo->qty = $vo->qty + $newdata['inqty'] - $newdata['outqty'] ;				
				$current->save($vo);
			}
			else 
			{
				$vo = $current->createVo('add','','id',0,$newdata);
				$vo->place = $newdata['place'] ;
				$vo->indateDate = $newdata["billDate"]	;
				$vo->qty = $newdata["inqty"] - $newdata["outqty"] ;
				$current->add($vo);
			}
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
		
		return true ;
	}
	
	/**
	 * 删除品名库存
	 *
	 * @param  $current
	 * @param  $olddata
	 */
	private function createCurrentStocksByNameDel($current,$olddata)
	{
		try
		{
			$vo = $current->find("goodsName= '".$olddata['goodsName']."' and spec = '" . $olddata['spec'] . "' and place = '" . $olddata['place'] . "'");
			if($vo)
			{
				$vo->qty = $vo->qty - $olddata['inqty'] + $olddata['outqty'] ;
				//库存不足
				if($vo->qty<0)
				{
					$this->setError("[".$olddata['goodsName']."][".$olddata['spec']."][".$olddata["place"]."]库存不足!");
					return false ;
				}
				
				//如果库存为0,则清除库存
				if($vo->qty==0)
				{
					$current->deleteById($vo->id);
				}
				else 
				{
					$current->save($vo);
				}
			}
			else 
			{
				$this->setError("[".$olddata['goodsName']."][".$olddata['spec']."][".$olddata["place"]."]库存不足!");
				return false ;
			}
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
		
		return true ;
	}
	
	/**
	 * 修改品名库存
	 *
	 * @param  $current
	 * @param  $newdata
	 * @param  $olddata
	 */
	private function createCurrentStocksByNameModify($current,$newdata,$olddata)
	{
		try
		{
			$vo = $current->find("goodsName= '".$olddata['goodsName']."' and spec = '" . $olddata['spec'] . "' and place = '" . $olddata['place'] . "'");
			if($vo)
			{
				$this->refreshStocksInfo($vo,$newdata);
				$vo->qty = $vo->qty - $olddata['inqty'] + $olddata['outqty']
				                    + $newdata['inqty'] - $newdata['outqty'] ;
				//库存不足
				if($vo->qty<0)
				{
					$this->setError("[".$olddata['goodsName']."][".$olddata['spec']."][".$olddata["place"]."]库存不足!");
					return false ;
				}

				//如果库存为0,则清除库存
				if($vo->qty==0)
				{
					$current->deleteById($vo->id);
				}
				else 
				{
					$current->save($vo);
				}                    
			}
			else 
			{
				$vo = $current->createVo('add','','id',0,$newdata);
				$vo->place = $newdata['place'] ;
				$vo->indateDate = $newdata["billDate"]	;
				$vo->qty = $newdata["qty"] ;
							
				//库存不足
				if($vo->qty<0)
				{
					$this->setError("[".$olddata['goodsName']."][".$olddata['spec']."][".$olddata["place"]."]库存不足!");
					return false ;
				}
				
				//加库存
				if($vo->qty>0) 
					$current->add($vo);
			}
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
		
		return true ;
	}
	
	/**
	 * 更新库存产品资料
	 *
	 * @param  $stocksvo 库存数据
	 * @param  $newdata  单据数据
	 */
	private function refreshStocksInfo($stocksvo,$newdata)
	{
		//如果库存是单据那天生成的,则可以改资料
		if($stocksvo->indateDate <= $newdata["billDate"])
		{
			//给Vo对象赋值
	        foreach ( array_keys(get_object_vars($stocksvo)) as $name)
	        {
	        	if($name=='id') continue ;
	            $val = isset($newdata[$name])?$newdata[$name]:null;
	            //保证赋值有效
	            if(!is_null($val) && property_exists($stocksvo,$name))
	                $stocksvo->$name = $val;
	        }
		}
		
		return true ;
	}
	

	/**
	 * 获取DataWindow结构
	 *
	 * @return array
	 */
	public function getStruct()
	{
		$vo = new StocksVo(array());
		$item  = array_keys(get_object_vars($vo));
		$label = array(	"id"=>"id" ,
						"sysno"=>"syso",
						"station"=>"位置"		,
						"code"=>"条码" ,
						"factoryNo"=>"出厂编号"	,
						"goodsName"=>"品名" ,
						"spec"=>"型号" ,
						"voltage1"=>"电压" ,
						"voltage2"=>"电压2" ,
						"current1"=>"电流" ,
						"current2"=>"电压2"	,
						"baseRange"=>"相/线" ,
						"direct"=>"方向",
						"constant"=>"常数"	,
						"precision"=>"精度"	,
						"lineIn"=>"接入方式"	,
						"ratedLoad"=>"额定负载"	,
						"grade"=>"等级"	,
						"madeIn"=>"生产厂商"		,
						"madeDate"=>"生产日期"	,
						"place"=>"仓库"	,
						"toplace"=>"调拨到"		,
						"billNo"=>"单据号"		,
						"billDate"=>"单据日期"	,
						"billType"=>"单据类型"	,
						"orderNo"=>"申请单号"     ,
						"clientNo"=>"客户编号"	,
						"client"=>"客户"		,
						"address"=>"客户地址"		,
						"sendMan"=>"送表人"		,
						"saveMan"=>"入仓人"		,
						"inqty"=>"入库数量"	,
						"outqty"=>"出库数量",
						"qty"=>"数量",
						"overdate"=>"超期天数",
						"memo"=>"备注"
					);
		$columnitem = $vo;
		$enabled = array("_type_"=>"Not","data"=>array("id","sysno","inoutType",'current2','voltage2') );
		$visible = array("_type_"=>"Not","data"=>array("id","outplace",
					"inplace","billNo","billDate","orderNo","client","address",
					"sendMan","saveMan") ) ;
		$align = array("orderNo"=>"center","inOut"=>"center");
		$width = array("qty"=>50);
		$inputtype = array();
		$editable = array();

		$struct["item"] = $item ;
		$struct["label"] = $label ;
		$struct["columnitem"] = $columnitem ;
		$struct["enabled"] = $enabled ;
		$struct["visible"] = $visible ;
		$struct["align"] = $align ;
		$struct["width"] = $width ;
		$struct["inputtype"] = $inputtype ;
		$struct["editable"] = $editable;

		return $struct ;
	}

	/**
	 * 设置错误
	 *
	 * @param string $error
	 */
	protected function setError($error)
	{
		//$vorn = '['.date('Y-m-d H:i:s',time()). '] ';
		//$this->errors .= $vorn.$error."\n";
		$this->errors = $error;
	}


	/**
	 * 获取错误
	 *
	 * @return string
	 */
	public function getError()
	{
		$return = ($this->errors) ? $this->errors : '';
        //unset ($this->errors);
        return $return;
	}
}
?>
