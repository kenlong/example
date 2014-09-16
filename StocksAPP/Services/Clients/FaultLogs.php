<?php
import("@.Action.Clients.FaultLogsAction");
class FaultLogs
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
			$action = new FaultLogsAction();
			$result = $action->query($condition);

			return $result ;

		}
		catch (Exception $e)
		{
			system_out("FaultLogs.query error:".$e);
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
			$action = new FaultLogsAction();
			$result = $action->save($data);

			return true ;
		}
		catch (Exception $e)
		{
			system_out("FaultLogs.save error:".$e);
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
			$action = new FaultLogsAction();
			$result = $action->delete($data);

			return true ;
		}
		catch (Exception $e)
		{
			system_out("FaultLogs.delete error:".$e);
			throw new Exception($e);
		}
	}
	
	/**
	 * 获取下拉框的值
	 */
	public function getList()
	{
		try {
			$action = new FaultLogsAction();
			$result = $action->getList();
			
			return $result ;
		}
		catch (Exception $e)
		{
			system_out("FaultLogs.getList error:".$e);
			throw new Exception($e);
		}
	}
	
	

	/**
	 * 获取datawindw结构
	 *
	 */
	public function getStruct()
	{
		$action = new FaultLogsAction();
		$result = $action->getStruct();

		return $result;
	}

}
?>