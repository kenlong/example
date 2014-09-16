<?php
class OtherParmSetupAction
{
	public function queryParmList($type,$parmname)
	{
		$dao = new SystemDao();
		$result =$dao->findAll("type='other' and paramname='member'")->toResultSet();
		
		return $result ;
	}
	
	public function addParm($type,$parmname,$parmvalue)
	{
		$dao = new SystemDao();
		
		$result =$dao->find("type = '$type' and paramname = '$parmname' and paramvalue = '$parmvalue' ");
		if($result) return true ;
		
		$vo['type'] = $type ;
		$vo['paramname'] = $parmname ;
		$vo['paramvalue'] = $parmvalue ;
		
		$vo = $dao->createVo('add','','id',0,$vo);
		
		$dao->add($vo);
	}
	
	
}
?>