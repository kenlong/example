<?php
import("@.Action.StocksIndex.StocksIndexAction");
import("@.Action.StocksManagerAction.StocksManagerAction");
import("@.Action.Code.CodeInfo");
class ConfirmStationAction
{
	private $errors;


	/**
	 * 检查是否有表填了多个表位
	 *
	 */
	public function checkWhetherDuplic()
	{
		try
		{
			$dao = new CurrentStocksDao();
			$result = $dao->findAll("station like 'GN%'",'',"count(1) as qty,station",'','','station','count(1)>1')->toResultSet();
			if($result)
			for($i=0;$i<sizeof($result);$i++)
			{
				$station = $result[$i]['station'] ;
				$res = $dao->findAll("station = '$station'",'','code','','','code')->toResultSet();
				$codes = '' ;
				if($res)
				{
					for($j=0;$j<sizeof($res);$j++)
						$codes .= ','.$res[$j]['code'];
					$result[$i]['code'] = $codes ;
				}
			}
						
			return $result ;
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}



	/**
	 * 查询未确认表位
	 *
	 * @param $condition
	 *
	 * @return Array
	 */
	public function queryUnconfirmStation($condition='')
	{
		try
		{
			$dao = new UnconfirmstationDao();
			$result = $dao->findAll($condition)->toResultSet();
			return $result ;
		}
		catch (Executive $e)
		{
			system_out("UnconfirmstationAction.queryUnconfirmstation error:$e,
						condition:$condition");
			throw new Exception($e);
		}
	}

	/**
	 * 保存未确认表位
	 *
	 * @param  array $data   表位
	 * @param String $state  状态    "U" 挂表 ; "D" 取表
	 */
	public function saveUnconfirmStation($data,$struct,$state)
	{
		$unconfirm = new UnconfirmstationDao();
		$unconfirm->startTrans();

		try
		{
			foreach ($data as $value)
			{
				for($i=0;$i<sizeof($value);$i++)
				{
					$station = $value[$i] ;
					if($station=='' || $station==null) continue ;
					$result = $unconfirm->getCount("station = '$station'");
					if($result==0)
					{
						$vo = $unconfirm->createVo('add','','id',0,$value[$i]);
						$vo->confirm = 0;
						$vo->struct = $struct ;
						$vo->station = $station ;
						$vo->state = $state ;
						$unconfirm->add($vo);
					}
				}
			}
		}
		catch (Executive $e)
		{
			$unconfirm->rollback() ;
			throw new Exception($e);
		}

		$unconfirm->commit() ;
	}

	/**
	 * 通过单据号删除未确认表位
	 *
	 * @param String $condition
	 * @return Boolean
	 */
	public function delUnconfirmStation($condition)
	{
		try
		{
			$dao = new UnconfirmstationDao();
			$dao->deleteAll($condition);
			$dao->commit() ;
		}
		catch (Executive $e)
		{
			system_out("UnconfirmstationAction.delUnconfirmstation error:$e,
						condition:$condition");
			throw new Exception($e);
		}
		return true ;
	}


	/**
	 * 确认表所放的位置
	 *
	 * @param $billno	单据号
	 * @param $data  	数据 array("station"=>,"code"=>)
	 * @return boolean
	 */
	public function confirmInstation($billdata,$data)
	{
		$sysno = $billdata['sysno'] ;
		$billdate = $billdata['billDate'] ;
		//明细表
		$stocks = new StocksDao();
		$stocks->startTrans();

		//当前库存表
		$current = new CurrentStocksDao();
		$current->startTrans();

		//未确认表位
		$unconfirm = new UnconfirmstationDao();
		$unconfirm->startTrans();

		try
		{
			$newinsert = false ;
			
			$station = $data["station"] ;
			$code = $data["code"] ;

			$result=$current->findAll("`code`='$code'")->toResultSet();
			if(sizeof($result)==0 && NOBILL_SCAN==false)
			{
				$stocks->rollback() ;
				$current->rollback() ;
				$unconfirm->rollback() ;
				$this->setError("库存里找不到该条码[$code]对应的表计!");
				return false ;
			}
			else if(sizeof($result)==0) //生成入仓单 
			{
				$newinsert = true ;
				
				$codeinfo = new CodeInfo();
				$codedetail = $codeinfo->getInfoByCode($code);
				
				$vo = $stocks->createVo('add','','id',0,$codedetail);	
				
				$vo->id = 0 ;
				$vo->inoutType = 'IN' ;
				$vo->code = $code ;
				$vo->sysno = $billdata['sysno'] ;
				$vo->placeno = $billdata['placeno'] ;
				$vo->place = $billdata['place'] ;
				$vo->billType = $billdata['billType'] ;
				$vo->billDate = $billdata['billDate'] ;
				$vo->billNo = $billdata['billNo'] ;
				$vo->sendMan = $billdata['sendMan'] ;
				$vo->saveMan = $billdata['saveMan'] ;
				$vo->orderNo = $billdata['orderNo'] ;
				$vo->client = $billdata['client'] ;
				$vo->inqty = 1 ;
				$vo->outqty = 0 ;
				$vo->station = $station ;

				//inert new records
				$stocksmanageraction = new StocksManagerAction();
				$savedata["modify"] = array($vo->toArray());				
				$result = $stocksmanageraction->save($savedata);
				if(!$result)
				{
					system_out("create new recod error:".$stocksmanageraction->getError());
					$this->setError($stocksmanageraction->getError()) ;
					return false ;
				}
			}
			
			$sql = "UPDATE ".(DB_PREFIX?DB_PREFIX."_":"")."currentstocks
			           SET `station`='$station'
					 WHERE `code` ='$code'" ;
			system_out("update sql:".$sql);
			$current->execute($sql) ;

			if(!$newinsert)
			{
				//返写stocks
				$result = StocksIndexAction::getTableNameByDate($billdate);
				if($result=='')
				{
					$stocks->rollback() ;
					$current->rollback() ;
					$unconfirm->rollback() ;
					system_out("get tablename error,billdate:".$billdate);
					$this->setError("获取存储表出错!");
					return false ;
				}
	
				$tablename = $result ;
				$sql = "UPDATE	`$tablename`
						   SET `station` = '$station'
						 WHERE `code` = '$code' and
						 		`sysno` = '$sysno' ";
	
				$stocks->execute($sql);
			}
			
			//标记已经确认的开灯记录
			$sql = "UPDATE ".(DB_PREFIX?DB_PREFIX."_":"")."unconfirmstation
			           SET  `confirm` = 1
					 WHERE  `station` = '$station' and `state`='U'" ;

			$unconfirm->execute($sql);
		}
		catch (Exception $e)
		{
			$stocks->rollback() ;
			$current->rollback() ;
			$unconfirm->rollback() ;
			system_out("UnconfirmstationAction.confirmInstation error:$e,
						sysno:$sysno,data:".print_r($data,true));
			throw new Exception($e);
		}

		//commit
		$stocks->commit() ;
		$current->commit() ;
		$unconfirm->commit() ;
		return true;
	}


	/**
	 * 出仓发货确认
	 *@param array $data  //单据数据
	 * @param string $code  //表码
	 * @return Boolean
	 */
	public function confirmOutstation($data,$code)
	{
		try
		{
			$stocksmanager = new StocksManagerAction();

			$result = $stocksmanager->queryCurrentStocks("code ='$code'");
			if(sizeof($result)>0)
			{
				$item = $result[0] ;

				$item["inoutType"] = 'OUT';
				$item["sysno"] = $data["sysno"];
				$item["billType"] = $data["billType"] ;
				$item["billDate"] = $data["billDate"] ;
				$item["billNo"] = $data["billNo"] ;
				$item["place"] = $data["place"] ;
				$item["placeno"] = $data["placeno"] ;
				$item["toplace"] = $data["toplace"] ;
				$item["sendMan"] = $data["sendMan"] ;
				$item["saveMan"] = $data["saveMan"] ;
				$item["client"] = $data["client"] ;
				$item["address"] = $data["address"] ;
				$item["inqty"] = 0 ;
				$item["outqty"] = $item["qty"] ;
				$item["id"] = '' ;

				$data = array();
				$data["modify"] = array($item);

				$rtn = $stocksmanager->save($data);
				if(!$rtn)
				{
					$error = $stocksmanager->getError();
					$this->setError($error);
					return false ;
				}
				return $rtn;
			}
			else
			{
				$this->setError("表库里没有该表");
				return false ;
			}

		}
		catch (Executive $e)
		{
			system_out("UnconfirmstationAction.confirmOutStation error:$e,
						code:$code,data:".print_r($data,true));
			throw new Exception($e);
		}
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
