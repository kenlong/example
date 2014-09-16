<?php
/**
 * 库房管理软件
 * @version 1.0.0.1
 * @package @.StorageManager
 *
 *
 */
class StorageAction{

	/**
	 * 条件查询
	 *
	 * @param String $condition
	 * @return resultSet
	 */
	public function findByCondition($condition='',$field="*")
	{
		$storage = new StorageDao();
		$result = $storage->findAll($condition,'','*','storageNo')->toResultSet();
		return $result ;
	}


	/**
	 * 获取库房列表
	 *
	 *@return boolean
	 */
	public function findAll()
	{
		$result = $this->getChild('0');
		$result = "<node storageName='仓库管理' storageNo='0' upnode='0'>" . $result . "</node>";
		return $result ;
	}

	/**
	 * 获取子项
	 *
	 * @param String $node
	 * @return boolean
	 */
	private function getChild($node)
	{
		$xml = '' ;
		$storage = new StorageDao();
		$result = $storage->findAll("upnode='$node'",'','','storageNo,id');
		$result = $result->toResultSet() ;
		for($i=0;$i<sizeof($result);$i++)
		{
			$tmp = "<node id='" . $result[$i]["id"] . "'".
						  " storageNo='" . $result[$i]["storageNo"] . "'".
				          " storageName='" . $result[$i]["storageName"] . "'".
				          " stype='" . $result[$i]["stype"] . "'".
				          " phone='" . $result[$i]["phone"] . "'".
				          " address='" . $result[$i]["address"] . "'".
				          " xNum='" . $result[$i]["xNum"] . "'".
				          " yNum='" . $result[$i]["yNum"] . "'".
				          " panel='" . $result[$i]["panel"] . "'" . 
				          " comport='" . $result[$i]["comport"] . "'" . 
				          " hardadd='" . $result[$i]["hardadd"] . "'" . 
				          " ipaddress='" . $result[$i]["ipaddress"] . "'" .
				          " upNode='" . $result[$i]["upNode"] . "'".
				      ">" ;

			//获取子级目录树
			$child = $this->getChild($result[$i]["storageNo"]) ;
			if($child != '')
			{
				$tmp = $tmp . $child ;
			}

			$tmp = $tmp . "</node>" ;

			$xml = $xml . $tmp  ;
		}

		return $xml ;

	}



	/**
	 * 新增
	 *
	 * @param $data
	 * @return boolean
	 */
	public function add(&$data)
	{
		$storeage = new StorageDao();
		//$data['yNum'] = intval($data['yNum']);
		//$data['xNum'] = intval($data['xNum']);
		if (empty($data['address'])) $data['address'] = "a";
		if (empty($data['phone'])) $data['phone'] = "168";
		$result = $storeage->add($data);
		//system_out(print_r($data,true));
		return $result ;
	}

	/**
	 * 修改保存
	 * @param $data
	 * @return boolean
	 */
	public function save($data)
	{
		$storage = new StorageDao() ;
		$result = $storage->save($data);
		return $result ;
	}

	/**
	 * 删除库房
	 * @param $id
	 * @return boolean
	 */
	public function deleted($data)
	{
		try
		{
			$storeage = new StorageDao();

			//检查仓库是否已经使用
			$storageName = $data["storageName"];
			$stocks = new CurrentStocksDao();
			$count = $stocks->getCount("place='$storageName' or station = '$storageName'");
			if($count>0)
			{
				$this->setError("该".$data['stype']."含有库存,不能删除!");
				return false ;
			}

			$id = $data["id"] ;
			$result = $storeage->deleteById($id);
		}
		catch (Exception $e)
		{
			system_out("StorageAction delete exception:".$e);
			throw new Exception($e);
		}

		return $result ;
	}

	/**
	 * 设置错误
	 *
	 * @param string $error
	 */
	protected function setError($error)
	{
		//$vorn = '['.date('Y-m-d H:i:s',time()). '] ';
		//$this->errors .= $vorn.$error."\n";
		$this->errors = $error;
	}


	/**
	 * 获取错误
	 *
	 * @return string
	 */
	public function getError()
	{
		$return = ($this->errors) ? $this->errors : '';
        //unset ($this->errors);
        return $return;
	}

}
?>
