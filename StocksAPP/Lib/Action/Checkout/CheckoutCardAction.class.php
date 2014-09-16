<?php
class CheckoutCardAction
{
	//list
	public function queryList($condition)
	{
		try {
			$dao = new CheckoutCardDao();
			$result = $dao->findAll($condition,'','id,clientNo,clientName,lineName,type,usage,code_main,code_second','clientNo')->toResultSet();
			
			return $result ;
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
	
	//query
	public function query($condition)
	{
		try
		{
			$dao = new CheckoutCardDao();
			$result = $dao->findAll($condition)->toResultSet();
			
			return $result ;
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
		
	}
	
	//save
	public function save($data)
	{
		try
		{
			$dao = new CheckoutCardDao();
			system_out(print_r($data['installer_logger'],true));
			if($data["id"]!='' && $data["id"]!=0)
			{
				$vo = $dao->createVo('modify','','id',$data["id"],$data);
				$result = $dao->save($vo);
			}
			else 
			{
				$vo = $dao->createVo('add','','id',0,$data);
				$result = $dao->add($vo);
			}
			
			return $result;
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
	
	//del
	public function del($data)
	{
		try
		{
			$dao = new CheckoutCardDao();
			$id = $data["id"] ;
			if($id==0 || $id== null || $id=='')
				return true ;
			$result = $dao->deleteById($id);
			
			return $result ;
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
	
	/**
	 * 显示的结构
	 *
	 * @return array
	 */
	public function getStruct()
	{
		$vo = new CheckoutCardVo(array());
		$item  = array_keys(get_object_vars($vo));
		$label = array(	"id"=>"id",
						"clientNo"=>"客户编号",
					 	"clientName"=>"客户名称",
					 	"lineName"=>"运行编号(线路名称)",
					 	"type"=>"类别",
					 	"usage"=>"用途",
					 	"code_main"=>"主表",
					 	"code_second"=>"副表",
					);
		$columnitem = $vo;
		$enabled = array("_type_"=>"Not","data"=>array("id") );
		$visible = array("_type_"=>"include","data"=>array("clientNo","clientName","lineName","type","usage","code_main","code_second") ) ;
		$align = array();
		$width = array();
		$inputtype = array();

		$struct["item"] = $item ;
		$struct["label"] = $label ;
		$struct["columnitem"] = $columnitem ;
		$struct["enabled"] = $enabled ;
		$struct["visible"] = $visible ;
		$struct["align"] = $align ;
		$struct["width"] = $width ;
		$struct["inputtype"] = $inputtype ;

		return $struct ;
	}

	
	
}
?>