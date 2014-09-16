<?php
import("@.Action.Interface.MIS");
import("@.Action.StocksIndex.StocksIndexAction");
class CodeInfo
{
	public function getInfoByCode($code)
	{
		try
		{
			$mis = new MIS();
			$result = $mis->getInfoByCode($code);
			if(!$result)
			{
				$result = $this->getInfoByCodeFormMySystem($code);
				if(!$result)
				{
					$result = $this->getInfoByCodeFromCodeDict($code);					
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
	 * 通过条码从本系统中获取产品信息
	 *
	 * @param string $code
	 * @return array
	 */
	public function getInfoByCodeFormMySystem($code)
	{
		try
		{
			$tablename = StocksIndexAction::getTableNameByCode($code);
			if(!$tablename)
			{
				return false ;
			}
			
			$dao = new StocksDao();
			$result = '' ;
			foreach ($tablename as $table)
			{
				$sql = "select * from $table where id=
								( select max(id) from $table where code = '$code') ";
				$result = $dao->query($sql)->toArray();
				if(!$result)
					continue ;
				else 
					break ;
			}
			
			if(!$result)
				return false ;
			else
				return $result[0];

		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
	
	/**
	 * 通过条码字典获取条码信息
	 *
	 * @param  $code
	 */
	private function getInfoByCodeFromCodeDict($code)
	{
		if(!Session::is_set('codeparm') || !Session::is_set('codedict')) return false ;
		
		$codeparm = Session::get('codeparm');
		$codedict = Session::get('codedict');		
		$codeinfo = array();
		foreach ($codeparm as $key => $value )
		{
			$s = (int)$value["start"] -1 ;
			$l = $value["length"] ;
			$data = $codedict[$key][substr($code,$s,$l)];
			
			if($data=='' || $data==null) continue ;
			
			switch ($key)
			{
				case '品名':
					$codeinfo['goodsName'] = $data ;
					break ;
				case '型号':
					$codeinfo['spec'] = $data ;
					break ;
				case '电流':
					$codeinfo['current1'] = $data ;
					break ;
				case '电压':
					$codeinfo['voltage1'] = $data ;
					break ;
				case '方向':
					$codeinfo['direct'] = $data ;
					break ;
				case '常数':
					$codeinfo['constant'] = $data ;
					break ;
				case '等级':
					$codeinfo['grade'] = $data ;
					break ;
				case '生产厂商':
					$codeinfo['madeIn'] = $data ;
					break ;
				case '生产日期':
					$codeinfo['madeDate'] = $data ;
					break ;
			}
		}
		system_out(print_r($codeinfo,true));
		if(sizeof($codeinfo)) 
		{
			$codeinfo['code'] = $code ;
			return $codeinfo ;
		}
		else 
			return false ;			
	}
	
	
}
?>