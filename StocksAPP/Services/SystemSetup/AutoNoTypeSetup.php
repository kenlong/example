<?php
class AutoNoTypeSetup
{
	/**
	 * query
	 *
	 * @return object
	 */
	public function query()
	{
		$dao = new AutoNoDao();
		$result =$dao->findAll()->toResultSet();
		
		return $result ;
	}
	
	/**
	 * save
	 *
	 * @param $data
	 * @return boolean
	 */
	public function save($data)
	{
		try
		{
			$dao = new AutoNoDao() ;
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
			system_out("AutoNoTypeSetup exception:".$e);
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
			$dao = new AutoNoDao();
			
			if($data["id"]!='')
			{
				$dao->deleteById($data["id"]);
			}
		}
		catch (Exception $e)
		{
			system_out("AutoNoTypeSetup exception:".$e);
			return false ;
		}
		
		return true ;
	}
}
?>