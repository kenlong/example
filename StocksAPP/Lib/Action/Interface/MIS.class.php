<?php
class MIS
{
	private $db_config ;
	
	public function __construct()
	{
		$this->db_config = array (
            'dbms'     => Session::get('interface_dbms'),
            'username' => Session::get('interface_username'),
            'password' => Session::get('interface_password'),
            'hostname' => Session::get('interface_hostname'),
            'hostport' => Session::get('interface_hostport'),
            'database' => Session::get('interface_database'),
            'unicode'  => Session::get('interface_unicode')
            );
	}
	
	public function getInfoByCode($code)
	{
		try
		{
			//如果没有设置数据库,则退出
			if(!Session::get('interface_database')) return false ;
			
			$interface = Session::get('interface_type');
			if(!$interface) return false ;
			
			import("@.Action.Interface.".$interface);
			
			$mis = new $interface();
			
			$code = $mis->getInfoByCode($code,$this->db_config);
			
			return $code ;	
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
	
	
	/**
	 * 输出出库记录
	 *
	 * @param  $data
	 */
	public function outPut($data)
	{
		try 
		{
			//如果没有设置数据库,则退出
			if(!Session::get('interface_database')) return false ;
			
			$interface = Session::get('interface_type');
			if(!$interface) return false ;
			
			import("@.Action.Interface.".$interface);
			
			$mis = new $interface();
			
			$result = $mis->outPut($data,$this->db_config);
			return $result ;	
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
	
		
	
}

?>