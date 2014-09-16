<?php
class ExecBackEnd
{
	/**
	 * 错误信息
	 */
	private $errors ;


	/**
     * 执行后台程序
     * @param string $backend_file
     * @return bool
     */
    public function exec($programpath)
    {
 		$pid_file = $programpath . ".pid" ;
		$backend_file = $programpath . ".php" ;
		$php = PHP_BINDIR . '/php';
		if (file_exists($pid_file))
		{
			$pid = trim(file_get_contents($pid_file));
			if ($this->isProgRunning($pid,$programpath))
			{
				$this->setError("程序已经在运行");
				return false ;
			}
			else
			{
				@unlink($pid_file);
			}
		}

		if (!file_exists($backend_file))
		{
			$this->setError("所指定的程序不存在");
			return false ;
		}
		$cmd = $php . " -f ". $backend_file;
		$pid = $this->runProgInBack($cmd);
		file_put_contents($pid_file,$pid);

    	return true;
    }

    /**
     * 检查进程是否存在
     *
     * @param int $pid
     * @param string $progName
     * @return bool
     */
    private static function isProgRunning($pid,$progName)
	{

		if (!$pid) return false;

	     //$str = `ps --no-headers --pid $pid`;
	     $str = exec("ps --no-headers --pid $pid -f");
	     //system_out("exec return:".$str);
	     if (strstr($str,$pid) && strstr($str,$progName))
	     	return true;
	     else
	     	return false;

	}

	/**
	 * 后台执行进程
	 *
	 * @param string $prog
	 * @return int
	 */
	private static function runProgInBack($prog)
	{

		//$pid = exec("nohup $prog /dev/null 2>&1 & echo $!");
		$pid = exec("$prog > /dev/null 2>&1 & echo $!");
		return $pid;

	}


	/**
	 * 设置错误
	 *
	 * @param string $error
	 */
	protected function setError($error)
	{
		$this->errors = $error ;
	}

	/**
	 * 获取错误
	 *
	 * @return string
	 */
	public function getError()
	{
		$return = ($this->errors) ? $this->errors : '';
        return $return;
	}

}
?>