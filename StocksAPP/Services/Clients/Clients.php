<?php
import("@.Action.Clients.ClientsAction");
class Clients
{
	/**
	 * 查询
	 *
	 * @param Strin $condition 条件
	 * @return array
	 */
	public function query($condition='')
	{
		try
		{
			$action = new ClientsAction();
			$result = $action->query($condition);

			return $result ;

		}
		catch (Exception $e)
		{
			system_out("Clients.query error:".$e);
			throw new Exception($e);
		}
	}

	/**
	 * 保存数据
	 *
	 * @param array $data
	 * @return boolean
	 */
	public function save($data)
	{
		try
		{
			$action = new ClientsAction();
			$result = $action->save($data);

			return true ;
		}
		catch (Exception $e)
		{
			system_out("ClientAction.save error:".$e);
			throw new Exception($e);
		}
	}

	/**
	 * 删除数据
	 *
	 * @param array $data
	 * @return boolean
	 */
	public function deleted($data)
	{
		try {
			$action = new ClientsAction();
			$result = $action->delete($data);

			return true ;
		}
		catch (Exception $e)
		{
			system_out("Clients.delete error:".$e);
			throw new Exception($e);
		}
	}

	/**
	 * 获取datawindw结构
	 *
	 */
	public function getStruct()
	{
		$action = new ClientsAction();
		$result = $action->getStruct();

		return $result;
	}

}
?>