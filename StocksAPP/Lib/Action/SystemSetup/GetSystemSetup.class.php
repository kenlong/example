<?php
class GetSystemSetup
{
	/**
	 * 查询表架参数
	 *
	 */
	public static function queryStructParm()
	{
		$dao = new SystemDao();
		$vo = $dao->find("paramname = 'structadd'");
		if($vo)
		{
			$data["structadd"] = $vo->paramvalue ;
		}
		
		$vo = $dao->find("paramname = 'structport'");
		if($vo)
		{
			$data["structport"] = $vo->paramvalue ;
		}
		
		if($data['structadd']=='' || $data['structport']=='')
		{
			throw new Exception("请先设置表架的地址和端口!");
		}
		Session::set('structadd',$data['structadd']);
		Session::set('structport',$data['structport']);
		
		return $data ;
	}
	
	/**
	 * 查询条码枪参数
	 *
	 */
	public static function queryScannerParm()
	{
		//$data["scanneradd"] = SCANNER_IP ;
		//$data["scannerport"] = SCANNER_PORT ;
		
		$dao = new SystemDao();
		$vo = $dao->find("paramname = 'scanneradd'");
		if($vo)
		{
			$data["scanneradd"] = $vo->paramvalue ;
		}
		
		$vo = $dao->find("paramname = 'scannerport'");
		if($vo)
		{
			$data["scannerport"] = $vo->paramvalue ;
		}
		
		if($data['scanneradd']=='' || $data['scannerport']=='')
		{
			throw new Exception("请先设置条码枪的地址和端口!");
		}
		
		Session::set('scanneradd',$data['scanneradd']);
		Session::set('scannerport',$data['scannerport']);
		
		return $data ;
	}
}
?>