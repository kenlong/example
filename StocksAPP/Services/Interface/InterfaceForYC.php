<?php
import("@.Action.Code.CodeInfo");
class InterfaceForYC
{
	public function getInfoByCode($user='',$pass='',$code='')
	{
		//用户检查
		
		$action = new CodeInfo();
		if($code)
			$result = $action->getInfoByCodeFormMySystem($code);
		
		$xw = new XMLWriter();
		$xw->openMemory();
		$xw->startDocument('1.0','gb2312');//版本與編碼
		
		$xw->startElement('packet');
		
		if($result)
			$xw->writeElement('result','true'); //查询条码成功
		else 
			$xw->writeElement('result','false') ;//查询条码失败
		
		$fields = array("code","factoryNo","goodsName","spec","current1","voltage1","direct","constant","grade","madeIn","madeDate");
		
		if($result)
		{
			$xw->startElement('codeInfo');	
			foreach ($result as $key=>$value)
			{
				if(!is_bool(array_search($key,$fields)))
					$xw->writeElement($key,$value);
			}
			$xw->endElement('codeInfo');
		}
		
		$xw->endElement('packet');
		
		return $xw->outputMemory(true) ;
	}
}
?>