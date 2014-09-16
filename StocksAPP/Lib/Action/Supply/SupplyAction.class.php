<?php
class SupplyAction
{
	/**
	 * 查询数据
	 *
	 * @param $condition
	 * @return boolean
	 */
	public function query($condition)
	{
		$dao = new SupplyDao();
		$result = $dao->findAll($condition);

		if(!$result->isEmpty())
			$result = $result->toResultSet();

		return $result ;
	}


	/**
	 * 查询单个
	 *
	 * @param $id
	 */
	public function queryById($id)
	{
		$dao = new SupplyDao();
		$result = $dao->getById($id);
		if(!$result->isEmpty())
			$result = $result->toArray();

		return $result ;
	}


	/**
	 * 保存数据
	 *
	 * @param $data
	 * @return int
	 */
	public function save($data)
	{
		$dao = new SupplyDao();

		if($data["id"] == null || $data["id"] == "")
		{
			$vo = $dao->createVo("add",'','id',0,$data);
			$result = $dao->add($vo);

			return $result ;
		}
		else
		{
			$vo = $dao->createVo("modify",'','id',$data["id"],$data);
			$result = $dao->save($vo);
			if($result)
				return $data["id"] ;
			else
				return  false ;
		}

	}

	/**
	 * 删除数据
	 *
	 * @param $data
	 */
	public function deleteById($data)
	{
		$dao = new SupplyDao();
		$result = $dao->deleteById($data["id"]);

		return $result ;
	}


	/**
	 * 获取结构
	 *
	 * @return array
	 */
	public function getStruct()
	{
		$vo = new SupplyVo(array());
		$item  = array_keys(get_object_vars($vo));
		$label = array(	"id"=>"id" ,
						"supplyNo"=>"厂商编号"	,
						"supplyName"=>"厂商名称"	,
						"otherName"=>"别名" ,
						"address"=>"地址"	,
						"phone"=>"电话"
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