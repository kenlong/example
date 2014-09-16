<?php
import("@.Action.StorageManager.StorageAction");
class StorageManager
{
	public function StorageManager()
	{
		$this->methodTable = array(
			"findByCondition"		=>	array("access"=>"remote"),
			"queryplaces"		=>	array("access"=>"remote"),
			"querytoplaces"		=>	array("access"=>"remote"),
			"findAll"		=>	array("access"=>"remote"),
			"save"		=>	array("access"=>"remote"),
			"deleted"		=>	array("access"=>"remote")
		);
	}


	/**
	 * 条件查询仓库
	 *
	 * @param String $condition
	 * @param String $field
	 *
	 * @return Array 
	 */
	public function findByCondition($condition='',$field="*")
	{
		$storage = new StorageAction();
		$result = $storage->findByCondition($condition,$field);

		return $result ;
	}

	/**
	 * 查询仓库资料
	 *
	 * @return unknown
	 */
	public function queryplaces($parm='')
	{
		$storage = new StorageAction();
		$result = $storage->findByCondition("stype = '库房' or stype='库区'",'storageName');

		return $result ;
	}
	
	/**
	 * 查询下级库
	 *
	 * @return unknown
	 */
	public function querytoplaces($parm='')
	{
		$storage = new StorageAction();
		$result = $storage->findByCondition("stype = '下级库'",'storageName');

		return $result ;
	}
	

	/**
	 * 查询全部
	 * @return boolean
	 */
	public function findAll()
	{
		$storage = new StorageAction();
		$result = $storage->findAll();
		return $result ;
	}

	/**
	 * 修改/新增保存
	 * @param $data
	 * @return int $id
	 */
	public function save($data)
	{
		import("@.Action.AutoNo.AutoNoAction");
		$storage = new StorageAction();

		unset($data["length"]);

		//新增
		if($data["id"]=="")
		{
			$no = AutoNoAction::getAutoNo("storage");

			if($data["upNode"]!='0' && $data["upNode"]!='1')
			{
				$no = $data["upNode"] . $no ;
			}

			$data["storageNo"] = $no ;
			$result = $storage->add($data);
			$data["id"] = $result ;
		}
		else
		{
			$result = $storage->save($data);
		}

		if(!$result)
		{
			throw new Exception("保存仓库数据出错!");
		}

		return $data  ;
	}

	/**
	 * 删除
	 * @parm $id
	 * @return boolean
	 */
	public function deleted($data)
	{
		$storage = new StorageAction();
		$result = $storage->deleted($data);
		if(!$result)
		{
			if($storage->getError()!='')
				throw new Exception($storage->getError());
			else
				throw new Exception("删除仓库出错!");
		}

		return $result ;
	}

}
?>