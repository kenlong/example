<?php
class GetSystemParm
{
	/**
	 * 获取系统设置的参数
	 *
	 */
	public static function querySystemParm()
	{
		
		$dao = new SystemDao();
		$result = $dao->findAll("ifnull(type,'') <>'coderule'")->toResultSet();
		foreach ($result as $item)
		{
			Session::set($item['paramname'],$item['paramvalue']);
		}
		
		//在这里设置内存
		if(Session::get('memory_limit')!='')
		{
			ini_set('memory_limit',Session::get('memory_limit'));
		}
		
		return $result ;
	}
	
	/**
	 * 获取条码资料基本参数
	 *
	 */
	public static function getCodeParameter()
	{
		$dao = new SystemDao();
		$result = $dao->findAll("type = 'coderule'")->toResultSet();
		
		$codeparm = array();
		
		foreach ($result as $item)
		{
			$ret = explode(',',$item['paramvalue']);
			$codeparm[$item['paramname']]['start'] = $ret[0];
			$codeparm[$item['paramname']]['length'] = $ret[1];
		}
		
		//system_out("codeparm:".print_r($codeparm,true));
		Session::set('codeparm',$codeparm);
		
		$sdao = new CodeDictDao();
		$result = $sdao->findAll()->toResultSet();
		
		$codedict = array();
		
		foreach ($result as $item)
		{
			$codedict[$item['type']][$item['code']] = $item['value'] ;
		}

		//system_out("codedict:".print_r($codedict,true));
		Session::set('codedict',$codedict);	
		
		$rtn['codeparm'] = $codeparm ;
		$rtn['codedict'] = $codedict ;
		
		return $rtn ;		
	}
	
}
?>