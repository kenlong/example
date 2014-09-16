<?php
import("@.Action.StocksManager.StocksManagerAction");
class ReportPrintField
{
	public function getPrintField($reportname='')
	{
		if($reportname=='') return '' ;
		
		switch ($reportname)
		{
			case 'instocksbill':
				$field = "code,goodsName,spec,voltage1,current1,inqty";
				
				$action = new StocksManagerAction();
				$struct = $action->getStruct();
				//如果字段不是全部的,则把必须的字段替换
				if($field!='*')
				{
					$field = explode(',',$field);
					$visible = array("_type_"=>"INCLUDE","data"=>$field ) ;
					$struct["visible"] = $visible ;
					$struct["item"] = $field ;
					$width = array("code"=>80,'goodsName'=>170,'spec'=>90,'voltage1'=>50,'current1'=>70);
					$struct["width"] = $width ;
				}
				break ;
			case 'outstocksbill':
				$field = "code,goodsName,spec,voltage1,current1,outqty"  ;
				
				$action = new StocksManagerAction();
				$struct = $action->getStruct();
				//如果字段不是全部的,则把必须的字段替换
				if($field!='*')
				{
					$field = explode(',',$field);
					$visible = array("_type_"=>"INCLUDE","data"=>$field ) ;
					$struct["visible"] = $visible ;
					$struct["item"] = $field ;
					$width = array("code"=>80,'goodsName'=>170,'spec'=>90,'voltage1'=>50,'current1'=>70);
					$struct["width"] = $width ;
				}	
				break ;
			default:
				return '' ;
		}
		
		return $struct ;
	}	
}
?>