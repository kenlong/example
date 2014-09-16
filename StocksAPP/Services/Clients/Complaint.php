<?php
import("@.Action.Clients.ComplaintAction");
class Complaint
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
			$action = new ComplaintAction();
			$result = $action->query($condition);

			return $result ;

		}
		catch (Exception $e)
		{
			system_out("Complaint.query error:".$e);
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
			$action = new ComplaintAction();
			$result = $action->save($data);

			return true ;
		}
		catch (Exception $e)
		{
			system_out("Complaint.save error:".$e);
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
			$action = new ComplaintAction();
			$result = $action->delete($data);

			return true ;
		}
		catch (Exception $e)
		{
			system_out("Complaint.delete error:".$e);
			throw new Exception($e);
		}
	}

	/**
	 * 获取datawindw结构
	 *
	 */
	public function getStruct()
	{
		$action = new ComplaintAction();
		$result = $action->getStruct();

		return $result;
	}

}
?>