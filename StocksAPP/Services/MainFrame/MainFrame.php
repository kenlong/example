<?php
class MainFrame
{
	public function MainFrame()
	{
		$this->methodTable = array(
				"getMenu"		=>	array("access"=>"remote")
		);
	}

	/**
	 * 获取系统菜单
	 *
	 * @param String $loginname
	 */
	public function getMenu($loginname='')
	{
		try {
			$dao = new ModulesDao();
			$result = $dao->findAll("upnode='0' and enabled = 1",'','*','moduleNo');
			if($result->isEmpty()) throw new Exception("没有系统菜单项") ;

			$parent = $result->toResultSet();
			$dao->commit();
			$ddo = new ModulesDao();
			$result = $ddo->findAll();
			system_out("ddo result:".print_r($result,true));
			
			for($i=0;$i<sizeof($parent);$i++)
			{
				$node = $parent[$i]["moduleNo"] ;
				$result = $dao->findAll("upnode='$node' and enabled = 1",'','*','moduleNo');
				$rtn[$i][0] = $parent[$i] ;
				$rtn[$i][1] = $result->toResultSet() ;
			}
			
			return $rtn ;
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage());
		}
	}
}
?>