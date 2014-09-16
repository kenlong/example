<?php
class GoodsAction
{
	/**
	 * 查询数据
	 *
	 * @param $condition
	 * @return boolean
	 */
	public function query($condition)
	{
		$dao = new GoodsDao();
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
		$dao = new GoodsDao();
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
		$dao = new GoodsDao();
		
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
	public function deleteById($id)
	{
		$dao = new GoodsDao();
		$result = $dao->deleteById($id);

		return $result ;
	}


	/**
	 * 获取结构
	 *
	 * @return array
	 */
	public function getStruct()
	{
		$vo = new GoodsVo(array());
		$item  = array_keys(get_object_vars($vo));
		$label = array(	"id"=>"id" ,
						"goodsNo"=>"产品编号"	,
						"goodsName"=>"产品名称"	,
						"spec"=>"型号" ,
						"current"=>"电流"	,
						"voltage"=>"电压",
						"direct"=>"方向",
						"constant"=>"常数"	,
						"madeDate"=>"生产日期"
					);
		$columnitem = $vo;
		$enabled = array("_type_"=>"Not","data"=>array("id") );
		$visible = array("_type_"=>"Not","data"=>array("id") ) ;
		$align = array("orderNo"=>"center");
		$width = array("id"=>0,"goodsNo"=>80,"goodsName"=>170,"spec"=>170,"current"=>120,"voltage"=>120);
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
