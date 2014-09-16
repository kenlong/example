<?php
define('SHARE_MEM_SIZE',1048576); //1M
define('MSG_SHMKEY',ftok(CONFIG_PATH ."ftok.messages.php", "m")); //消息共享内存KEY
define('MSG_SEMKEY',ftok(CONFIG_PATH ."ftok.messages.php", "s")); //消息共享内存内部使用信号量KEY
class ShareMemory
{
	/**共享内存变量*/
	private $shm ;

	//构造函数
	public function __construct()
	{
		//$shm = new ShmBase(MSG_SEMKEY,MSG_SHMKEY,SHARE_MEM_SIZE);
	}

	/**
	 * 设置共享内存的值
	 *
	 * @param string $name    //变量名
	 * @param object $value   //值
	 * @return boolean
	 */
	public static function setShareMemory($name,$value)
	{
		try
		{
			$shm = new ShmBase(MSG_SEMKEY,MSG_SHMKEY,SHARE_MEM_SIZE);
			$ret = $shm->lock();
			if ($ret)
			{
				$shm->save($name,$value);
				$shm->unlock();
			}
		}
		catch (Executive $e)
		{
			system_out("ShareMemory.setShareMemory error:$e");
			return false ;
		}

		return true ;
	}

	/**
	 * 获取共享内存的值
	 *
	 * @param string $name   //变量名
	 * @return object        //返回值
	 */
	public static function getShareMemory($name)
	{
		try
		{
			$shm = new ShmBase(MSG_SEMKEY,MSG_SHMKEY,SHARE_MEM_SIZE);
			$ret = $shm->lock();
			if ($ret)
			{
				$tasks = $shm->read($name);
				$shm->unlock();

				$rtn = $tasks?$tasks:'';

				return $rtn ;
			}
		}
		catch (Exception $e)
		{
			system_out("ShareMemory.getShareMemory error:$e");
			return false ;
		}

		return false ;
	}

	/**
	 * 删除共享内存的值
	 *
	 * @param string $name   //变量名
	 * @return object        //返回值
	 */
	public static function delShareMemory($name)
	{
		try
		{
			$shm = new ShmBase(MSG_SEMKEY,MSG_SHMKEY,SHARE_MEM_SIZE);
			$ret = $shm->lock();
			if ($ret)
			{
				$tasks = $shm->delete($name);
				$shm->unlock();

				return true ;
			}
		}
		catch (Exception $e)
		{
			system_out("ShareMemory.getShareMemory error:$e");
			return false ;
		}

		return false ;
	}

}
?>