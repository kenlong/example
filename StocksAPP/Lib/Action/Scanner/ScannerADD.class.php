<?php
class ScannerADD
{
	/**
	 * socket句柄
	 *
	 * @var resource
	 */
	private $socket = null;
	
	/**
	 * 设备的IP
	 *
	 * @var string
	 */
	private $host = "0.0.0.0";
	
	/**
	 * socket端口
	 *
	 * @var int
	 */
	private $port = 0;
	
	/**
	 * 条码枪类型
	 *
	 * @var unknown_type
	 */
	private $type ;
	
	/**
	 * 条码枪地址
	 *
	 * @var unknown_type
	 */
	public $device ;	
	
	/**
	 * 设备返回的超时时间,单位为秒
	 *
	 * @var int
	 */
	private $timeOut = 0;
	
	/**
	 * 等待设备响应时间
	 *
	 * @var float
	 */
	private $waitTime = 0.3;
	
	/**
	 * 错误信息
	 *
	 * @var string
	 */
	private $errors;

	private $serial = null;
	/**
	 * 构造函数
	 *
	 * @param string $host
	 * @param int $port
	 * @param string $comPort
	 */
	public function __construct($host = "0.0.0.0",$port = 0,$type='type1')
	{
		$this->host = $host;
		$this->port = $port;
		$this->type = $type;

		$this->open();
	}

	/**
	 * 关闭socket连接
	 *
	 */
	public function __destruct()
	{
		//if (is_resource($this->socket))
			$this->close();
	}


	/**
	 *获取数据
	 *
	 * @return unknown
	 */
	public function readData()
	{
		$result = $this->read();
		if (false === $result)
			return false;

		system_out("in rtn1[".strlen($result)."]:".$result);
				
		$result = ereg_replace("[^a-zA-Z0-9]",'',$result);
		
		system_out("in rtn2[".strlen($result)."]:".$result);
		
		system_out("in code:".$result);
		
		switch ($this->type)
		{
			case 'type1':
				$this->device = substr($result,0,2);
				$result = substr($result,2);
			default:
		}
		
		$len = strlen($result)	;
		
		return $result;
	}

	/**
	 * 发红灯信号
	 *@param string $line  //扫描枪地址
	 */
	public function sendRedLight($deviceAddress='')
	{
		//41 44 53 45 52 30 32 41 0D
		if($deviceAddress=='')
			return  false ;
		$add1 = ord(substr($deviceAddress,0,1));
		$add2 = ord(substr($deviceAddress,1,1));

		$data = '' ;

		$data .= pack('C',0x41);
		$data .= pack('C',0x44);
		$data .= pack('C',0x53);
		$data .= pack('C',0x45);
		$data .= pack('C',0x52);
		$data .= pack('C',$add1); //$data .= pack('C',0x30);
		$data .= pack('C',$add2); //$data .= pack('C',0x32);
		$data .= pack('C',0x41);
		$data .= pack('C',0x0D);

		$this->send($data);

		return true ;
	}


	/**
	 * 发绿灯信号
	 *@param string $line //扫描枪地址
	 */
	public function sendGreenLight($deviceAddress='')
	{
		//41 44 53 4F 4B 30 32 41 0D
		if($deviceAddress=='')
			return  false ;
		$add1 = ord(substr($deviceAddress,0,1));
		$add2 = ord(substr($deviceAddress,1,1));

		$data = '' ;

		$data .= pack('C',0x41);
		$data .= pack('C',0x44);
		$data .= pack('C',0x53);
		$data .= pack('C',0x4F);
		$data .= pack('C',0x4B);
		$data .= pack('C',$add1); //$data .= pack('C',0x30);
		$data .= pack('C',$add2); //$data .= pack('C',0x32);
		$data .= pack('C',0x41);
		$data .= pack('C',0x0D);

		$this->send($data);
	}


	/**
	 * 设置读写超时
	 *
	 * @param int $seconds
	 * @return bool
	 */
	public function setTimeOut($seconds)
	{
		$this->timeOut = $seconds;
		return true;
	}

	/**
	 * 设置等待返回时间
	 *
	 * @param int $seconds
	 * @return bool
	 */
	public function setWaitTime($seconds)
	{
		$this->waitTime = $seconds;
		return true;
	}

	/**
	 * 设置要连接的设备IP
	 *
	 * @param string $host
	 * @return bool
	 */
	public function setHost($host)
	{
		if (is_resource($this->socket))
		{
			$this->setError("The device is already opened");
			return false;
		}

		$this->host = $host;
		return true;
	}

	/**
	 * 设置连接设备的端口
	 *
	 * @param int $port
	 * @return bool
	 */
	public function setPort($port)
	{
		if (is_resource($this->socket))
		{
			$this->setError("The device is already opened");
			return false;
		}

		$this->port = $port;
		return true;
	}


	/**
	 * 打开设备
	 *
	 * @return bool
	 */
	public function open()
	{
		if (is_resource($this->socket))
		{
			$this->setError("The device is already opened");
			return false;
		}

		$this->socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);

		if ($this->socket < 0)
		{
			$error = "Open failed! reason: " . socket_strerror(socket_last_error());
			$this->setError($error);
			return false;
		}

		$result = socket_connect($this->socket,$this->host,$this->port);
		if (false === $result)
		{
			$error = "Open failed! reason: " . socket_strerror(socket_last_error($this->socket));
			$this->setError($error);
			return false;
		}

		return true;
	}

	/**
	 * 关闭设备
	 *
	 * @return bool
	 */
	public function close()
	{
		if (false === COM_TO_NET)
		{
			$this->serial->deviceClose();
			return true;
		}

		if (is_resource($this->socket))
			socket_close($this->socket);
		$this->socket = null;
		return true;
	}


	/**
	 * 发送数据
	 *
	 * @param string $data
	 * @return bool
	 */
	protected function send($data)
	{
		if (!is_resource($this->socket))
		{
			$this->setError("Please call open function first!");
			return false;
		}

		$sendData = $data;

		$result = socket_write($this->socket,$sendData,strlen($sendData));
		if (false === $result)
		{
			$error = "Write failed! reason: " . socket_strerror(socket_last_error($this->socket));
			$this->setError($error);
			return false;
		}
		//system_out("Send : " . print_r(unpack("C*",$sendData),true));
		usleep((int) ($this->waitTime * 1000000));
		return true;
	}

	/**
	 * 接受数据
	 *
	 * @param int $count
	 * @param bool $command
	 * @return string | bool
	 */
	protected function read($count = 128)
	{
		if (!is_resource($this->socket))
		{
			$this->setError("Please call open function first!");
			return false;
		}

		socket_set_nonblock($this->socket);
		$content = '';
		if ($this->timeOut)
		{
			$time = time();
			$while = true;

			while ($while)
			{
				$content = socket_read($this->socket,$count);
				if (false === $content)
				{
					//$error = "Read failed! reason: " . socket_strerror(socket_last_error($this->socket));
					//$this->setError($error);
					//return false;
				}elseif (strlen($content) > 1){
					$while = false;
				}

				if ((time() - $time) >= $this->timeOut)
	            {
	                $this->setError("Read time out!");
	                return false;
	            }
	            else
	            {
	                //sleep(1);
	                usleep(500000);
	                continue;
	            }
			}
		}else {
			$content = socket_read($this->socket,$count);
			if (false === $content)
			{
				$error = "Read failed! reason: " . socket_strerror(socket_last_error($this->socket));
				$this->setError($error);
				return false;
			}
		}

		return $content;
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
        //unset ($this->errors);
        return $return;
	}
}
?>