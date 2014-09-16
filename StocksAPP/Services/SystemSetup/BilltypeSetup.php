<?php
class BilltypeSetup
{
	/**
	 * 查询数据
	 *
	 * @return array
	 */
	public function query()
	{
		$dao = new BilltypeDao();
		$result = $dao->findAll()->toResultSet();
		
		return $result ;
	}
	
	/**
	 * 保存数据
	 *
	 * @param array $data
	 */
	public function save($data)
	{
		try
		{
			$dao = new BilltypeDao();
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
			system_out("BilltypeSetup exception:".$e);
			return false ;
		}
		
		return $id ;
		
	}
	
	/**
	 * 删除
	 *
	 * @param $data
	 * @return boolean
	 */
	public function del($data)
	{
		try 
		{
			$dao = new BilltypeDao();
			
			if($data["id"]!='')
			{
				$dao->deleteById($data["id"]);
			}
		}
		catch (Exception $e)
		{
			system_out("BilltypeSetup exception:".$e);
			return false ;
		}
	}
	
	
}
?>