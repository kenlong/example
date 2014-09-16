<?php
class CodeIndexAction
{
	/**
	 * 获取某条码的所有存储日期
	 * @param string $code 
	 * @return rray 日期数组
	 */
	public static function queryIndexByCode($code)
	{
		$dao = new CodeIndexDao();
		$rtn = $dao->findAll("codeindex like '%$code%'",'','indexdate','id desc','','indexdate')->toResultSet();

		$result = array();
		
		foreach ($rtn as $item)
		{
			array_push($result,$item['indexdate']);
		}
		
		return $result ;
	}

	
	/**
	 * 通过日期,获得某日的条码索引
	 *
	 * @param date $date
	 * @param string '1' 已经关闭,'0' 未关闭,'' 所有
	 */
	public static function queryIndexByDate($date,$closed='0')
	{
		try
		{
			$dao = new CodeIndexDao();
			
			$condition = "indexdate = '$date'" ;
			if($closed!='') $condition .= " and closed = $closed";		
			
			$result = $dao->find($condition);
			if($result) $result = $result->toArray();
			
			return $result ;
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
	
	/**
	 * 储存codeindex
	 *
	 * @param array $data
	 * @return boolean
	 */
	public static function saveIndex($data)
	{
		try
		{
			$dao = new CodeIndexDao();
			if($data['id'] == '' || $data['id'] == null)
			{
				$vo = $dao->createVo('add','','id',0,$data);
				$result = $dao->add($vo);
				return $result ;
			}
			else 
			{
				$vo = $dao->createVo('modify','','id',$data['id'],$data);
				//条码长度
				$codelength = Session::get('codelength');
				//如果没有设置,则默认64
				if((int)$codelength==0) $codelength = 64 ;
				//限定某个stocksindex最长为65536(64K)				
				$maxlength = 65536 ;
				$indexlength = strlen($vo->codeindex) + $codelength + 1 ;
				
				//如果长度超设定的值,则设置关闭,下次不会在拿出来增加索引的了
				if($indexlength>$maxlength) $vo->closed = 1 ;
				
				$dao->save($vo);				
				
				return $vo->id ;
			}
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
	
	/**
	 * 增加条码索引
	 *
	 * @param date $billDate 日期
	 * @param string $code 条码
	 */
	public static  function addCodeIndex($billDate,$code)
	{
		//增加条码索引
		$code .= "/" ;
		$codeindex = CodeIndexAction::queryIndexByDate($billDate);
		if($codeindex)
		{
			$codeindex['codeindex'] .= $code ;	
		}
		else 
		{
			$codeindex['indexdate'] = $billDate ;
			$codeindex['codeindex'] = $code ;
			$codeindex['closed'] = 0 ;
		}
		
		CodeIndexAction::saveIndex($codeindex);
	}	
}
?>