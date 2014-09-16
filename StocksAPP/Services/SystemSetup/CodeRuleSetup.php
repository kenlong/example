<?php
class CodeRuleSetup
{
	/**
	 * 查询可以设置的参数
	 *
	 * @return object
	 */
	public function queryParmList()
	{
		$data = array('品名','型号','电流','电压','方向','常数','等级','生产厂商','生产日期');
		
		return $data ;
	}
	
	/**
	 * 查询参数表
	 *
	 * @return object
	 */
	public function queryCodeRule()
	{
		try
		{
			$dao = new SystemDao();
			$result =$dao->findAll("type='coderule'")->toResultSet();
			for($i=0;$i<sizeof($result);$i++)
			{
				$value = $result[$i]['paramvalue'] ;
				$ret = explode(',',$value);
				$result[$i]['start'] = $ret[0];
				$result[$i]['length'] = $ret[1];
			}
			
			return $result ;
		}
		catch (Exception $e)
		{
			system_out("CodeRuleSetup.queryCodeRule exception:".$e);
			return false ;
		}
	}
	
	/**
	 * 保存参数
	 *
	 * @param object $data
	 * @return boolean
	 */
	public function saveCodeRule($data)
	{
		try
		{
			//system_out(print_r($data,true));
			$dao = new SystemDao();
			$id = 0 ;		
			if($data["id"]!='')
			{
				$vo = $dao->createVo('modif','','id',$data["id"],$data);
				$dao->save($vo);
				$id = $data["id"] ;
			}
			else 
			{
				$vo = $dao->createVo('add','','id',0,$data);
				$id = $dao->add($vo);
			}
		}
		catch (Exception $e)
		{
			system_out("CodeRuleSetup exception:".$e);
			return false ;
		}
		
		return $id ;
	}
	
	/**
	 * 删除参数
	 *
	 * @param object $data
	 * @return boolean
	 */
	public function delCodeRule($data)
	{
		try 
		{
			$dao = new SystemDao();
			if($data["id"]!='')
			{
				$dao->deleteById($data["id"]);
			}
		}
		catch (Exception $e)
		{
			system_out("CodeRuleSetup exception:".$e);
			return false ;
		}
		return true ;
	}
	
	/**
	 * 查询条码对照表
	 *
	 * @param string $condition
	 * @return object
	 */
	public function queryCodeDict($condition)
	{
		$dao = new CodeDictDao();
		$result = $dao->findAll($condition)->toResultSet();
		system_out(print_r($result,true));
		return $result ;
	}
	
	/**
	 * 保存条码对照表
	 *
	 * @param object $data
	 */
	public function saveCodeDict($data)
	{
		try
		{
			$dao = new CodeDictDao();
			$id = 0 ;		
			if($data["id"]!='')
			{
				$vo = $dao->createVo('modif','','id',$data["id"],$data);
				$dao->save($vo);
				$id = $data["id"] ;
			}
			else 
			{
				$vo = $dao->createVo('add','','id',0,$data);
				$id = $dao->add($vo);
			}
		}
		catch (Exception $e)
		{
			system_out("CodeRuleSetup.savecodedict exception:".$e);
			return false ;
		}
		
		return $id ;
	}
	
	/**
	 * 删除条码对照表
	 *
	 * @param object $data
	 */
	public function delCodeDict($data)
	{
		try 
		{
			system_out(print_r($data,true));
			$dao = new CodeDictDao();
			if($data["id"]!='')
			{
				$dao->deleteById($data["id"]);
			}
		}
		catch (Exception $e)
		{
			system_out("CodeRuleSetup exception:".$e);
			return false ;
		}
		return true ;
	}	
}
?>