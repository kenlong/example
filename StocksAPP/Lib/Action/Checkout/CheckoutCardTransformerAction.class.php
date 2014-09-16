<?php
class CheckoutCardTransformerAction
{
	//query
	public function query($condition)
	{
		try
		{
			$dao = new CheckoutCardTransformerDao();
			$result =$dao->findAll($condition)->toResultSet();
			
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
			$dao = new CheckoutCardTransformerDao();
			if($data["id"]!=0 && $data["id"]!='')
			{
				$vo = $dao->createVo('modify','','id',$data['id'],$data);
				$result = $dao->save($vo);
			}
			else 
			{
				$vo = $dao->createVo('add','','id',0,$data);
				$result = $dao->add($vo);
			}
			
			return $result ;
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
			$dao = new CheckoutCardTransformerDao();
			$id = $data["id"] ;
			if($id=='' || $id==null || $id==0)
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
		$vo = new CheckoutCardTransformerVo(array());
		$item  = array_keys(get_object_vars($vo));
		$label = array(	"id"=>"id",
						"testDate"=>"测试日期",
					 	"fa"=>"fa",
					 	"da"=>"δa",
					 	"aError"=>"A相误差",
					 	"fb"=>"fb",
					 	"db"=>"δb",
					 	"bError"=>"B相误差",
					 	"fc"=>"fc",
					 	"dc"=>"δc",
					 	"cError"=>"C相误差",
					 	"tester"=>"测试人员"
					);
		$columnitem = $vo;
		$enabled = array("_type_"=>"Not","data"=>array("id") );
		$visible = array("_type_"=>"Not","data"=>array("id") ) ;
		$align = array();
		$width = array("id"=>0);
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