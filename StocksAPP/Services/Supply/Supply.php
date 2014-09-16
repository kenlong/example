<?php
class Supply
{
	public function Supply(){
		import("@.Action.Supply.SupplyAction");

		$this->methodTable = array(
			"query"		=>	array("access"=>"remote"),
			"queryById"		=>	array("access"=>"remote"),
			"save"		=>	array("access"=>"remote"),
			"deleteData"		=>	array("access"=>"remote"),
			"getStruct"		=>	array("access"=>"remote")
		);
	}


	/**
	 * 查询供货商
	 *
	 * @param $condition
	 * @return array
	 */
	public function query($condition='')
	{
		$supply = new SupplyAction();
		$result = $supply->query($condition);

		if(!$result)
		{
			throw new Exception("查询供货商出错!");
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
		$supply = new SupplyAction();
		$result = $supply->queryById($id);

		if(!$result)
		{
			throw new Exception("查询供货商资料出错!");
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
		$supply = new SupplyAction();
		$result = $supply->save($data);

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
		$supply = new SupplyAction();
		$result = $supply->deleteById($id);

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
		$supply = new SupplyAction();
		$result = $supply->getStruct();

		return $result ;
	}

}
?>