<?php
class Goods
{
	public function Goods(){
		import("@.Action.Goods.GoodsAction");

		$this->methodTable = array(
			"query"		=>	array("access"=>"remote"),
			"queryById"		=>	array("access"=>"remote"),
			"save"		=>	array("access"=>"remote"),
			"deleteData"		=>	array("access"=>"remote"),
			"getStruct"		=>	array("access"=>"remote")
		);
	}


	/**
	 * 查询产品资料
	 *
	 * @param $condition
	 * @return array
	 */
	public function query($condition='')
	{
		$goods = new GoodsAction();
		$result = $goods->query($condition);

		if(!$result)
		{
			throw new Exception("查询产品出错!");
		}

		return $result ;

	}

	/**
	 * 查询by id
	 *
	 * @param $id
	 * @return boolean
	 */
	public function queryById($id)
	{
		$goods = new GoodsAction();
		$result = $goods->queryById($id);

		if(!$result)
		{
			throw new Exception("查询商品资料出错!");
		}

		return $result ;

	}


	/**
	 * 保存数据
	 *
	 * @param $data
	 */
	public function save($data)
	{
		$goods = new GoodsAction();
		$result = $goods->save($data);

		if(!$result)
		{
			throw new Exception("保存数据出错!");
		}

		return $result ;

	}


	/**
	 * 删除数据
	 *
	 * @param $id
	 */
	public function deleteData($data)
	{
		$id = $data["id"];
		$goods = new GoodsAction();
		$result = $goods->deleteById($id);

		if(!$result)
		{
			throw new Exception("删除出错!");
		}

		return $result ;
	}


	/**
	 * 查询显示的结构字段
	 *
	 */
	public function getStruct()
	{
		$goods = new GoodsAction();
		$result = $goods->getStruct();

		return $result ;
	}

}
?>