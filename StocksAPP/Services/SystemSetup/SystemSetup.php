<?php
class SystemSetup
{
	/**
	 * 查询表架参数
	 *
	 */
	public function queryStructParm()
	{
		$dao = new SystemDao();
		$result = $dao->findAll("paramname = 'structadd' or paramname ='structport' or paramname = 'structdelay' or paramname='comToNetType'")->toResultSet();
		if($result)
		{
			foreach ($result as $item)
			{
				$data[$item['paramname']] = $item['paramvalue'] ;
			}
		}		
				
		return $data ;
	}
	
	/**
	 * 保存表架参数
	 *
	 * @param object $data
	 */
	public function saveStructParm($data)
	{
		if(empty($data)) return ;
		try
		{
			$dao = new SystemDao();
			$dao->deleteAll("paramname = 'structadd' or paramname ='structport' or paramname='structdelay' or paramname='comToNetType'");
			
			foreach ($data as $key => $value)
			{
				$vo = new SystemVo();
				$vo->paramname = $key ;
				$vo->paramvalue = $value ;
				
				$dao->add($vo);
				Session::set($key,$value);
			}
		}
		catch (Exception $e)
		{
			system_out($e);
			return false ;
		}

		return true ;
	}
	
	/**
	 * 查询条码枪参数
	 *
	 */
	public function queryScannerParm()
	{
		$dao = new SystemDao();
		$result = $dao->findAll("paramname = 'scanneradd' or paramname='scannerport' or paramname='scannertype'")->toResultSet();
		if($result)
		{
			foreach ($result as $item)
			{
				$data[$item['paramname']] = $item['paramvalue'] ;
			}
		}		
		
		return $data ;
	}
	
	/**
	 * 保存条码枪参数
	 *
	 * @param object $data
	 */
	public function saveScannerParm($data)
	{
		if(empty($data)) return ;
		
		try
		{
			$dao = new SystemDao();
			$dao->deleteAll("paramname = 'scanneradd' or paramname='scannerport' or paramname='scannertype'");
			
			foreach ($data as $key => $value)
			{
				$vo = new SystemVo();
				$vo->paramname = $key ;
				$vo->paramvalue = $value ;
				
				$dao->add($vo);
				Session::set($key,$value);
			}
		}
		catch (Exception $e)
		{
			system_out($e);
			return false ;
		}
		
		return true ;
	}
	
	/**
	 * 查询服务器配置
	 *
	 */
	public function queryServerParm()
	{
		$dao = new SystemDao();
		$result = $dao->findAll("paramname = 'ipaddress' or paramname='netmask' or paramname='gateway' or paramname='memory_limit'")->toResultSet();
		if($result)
		{
			foreach ($result as $item)
			{
				$data[$item['paramname']] = $item['paramvalue'] ;
			}
		}
		
		//获取服务器内存设置
		if($data['memory_limit']=='')
			$data['memory_limit'] = ini_get('memory_limit');
		
		return $data ;
	}
	
	/**
	 * 保存服务器配置
	 *
	 * @param object $data
	 */
	public function saveServerParm($data)
	{
		if(empty($data)) return ;
		
		try
		{
			$dao = new SystemDao();
			$dao->deleteAll("paramname = 'ipaddress' or paramname='netmask' or paramname = 'gateway' or paramname='memory_limit'");
			
			foreach ($data as $key => $value)
			{
				$vo = new SystemVo();
				$vo->paramname = $key ;
				$vo->paramvalue = $value ;
				
				$dao->add($vo);
			}
			
			/*
			//获取服务器内存设置
			$value = $data["memory"];

			ini_set('memory_limit',$value);
			*/
		}
		catch (Exception $e)
		{
			system_out($e);
			return false ;
		}
		
		//写shell文件
		$filename = APP_PATH . '/../shell/ipaddress';
		@unlink($filename);
		
		$value = "ifconfig eth0:1 " . $data['ipaddress'] . ' netmask '.$data['netmask'] ;
		$value.= '
		';
		$value .= "route   add   default   gw   ".$data['gateway'] ;
		file_put_contents($filename,$value);

		return true ;
	}
	
	/**
	 * 查询周转箱标志头的配置文件
	 *
	 * @return object
	 */
	public function queryBoxheadParm()
	{
		$dao = new SystemDao();
		$result = $dao->findAll("paramname = 'boxhead' or paramname='boxheadlength'")->toResultSet();
		if($result)
		{
			foreach ($result as $item)
			{
				$data[$item['paramname']] = $item['paramvalue'] ;
			}
		}		
		return $data ;
	}
	
	/**
	 * 保存周转箱头标记
	 *
	 * @param object $data
	 */
	public function saveBoxheadParm($data)
	{
		if(empty($data)) return ;
		
		try
		{
			$dao = new SystemDao();
			$dao->deleteAll("paramname = 'boxhead' or paramname='boxheadlenght'");
			
			foreach ($data as $key => $value)
			{
				$vo = new SystemVo();
				$vo->paramname = $key ;
				$vo->paramvalue = $value ;
				
				$dao->add($vo);
				Session::set($key,$value);
			}
		}
		catch (Exception $e)
		{
			system_out($e);
			return false ;
		}
		
		return true ;
	}	
	
	
	/**
	 * 查询条码的配置文件
	 *
	 * @return object
	 */
	public function queryCodeParm()
	{
		$dao = new SystemDao();
		$result = $dao->findAll("paramname = 'codelength'")->toResultSet();
		if($result)
		{
			foreach ($result as $item)
			{
				$data[$item['paramname']] = $item['paramvalue'] ;
			}
		}		
		return $data ;
	}
	
	/**
	 * 保存条码配置文件
	 *
	 * @param object $data
	 */
	public function saveCodeParm($data)
	{
		if(empty($data)) return ;
		
		try
		{
			$dao = new SystemDao();
			$dao->deleteAll("paramname = 'codelength'");
			
			foreach ($data as $key => $value)
			{
				$vo = new SystemVo();
				$vo->paramname = $key ;
				$vo->paramvalue = $value ;
				
				$dao->add($vo);
				Session::set($key,$value);
			}
		}
		catch (Exception $e)
		{
			system_out($e);
			return false ;
		}
		
		return true ;
	}	
	
	
	
	/**
	 * 查询接口配置文件
	 *
	 * @return object
	 */
	public function queryInterfaceParm()
	{
		$dao = new SystemDao();
		$result = $dao->findAll("paramname like 'interface%'")->toResultSet();
		if($result)
		{
			foreach ($result as $item)
			{
				$data[$item['paramname']] = $item['paramvalue'] ;
			}
		}		
		return $data ;
	}
	
	/**
	 * 保存接口配置
	 *
	 * @param object $data
	 */
	public function saveInterfaceParm($data)
	{
		if(empty($data)) return ;
		
		try
		{
			$dao = new SystemDao();
			$dao->deleteAll("paramname like 'interface%'");
			
			foreach ($data as $key => $value)
			{
				$vo = new SystemVo();
				$vo->paramname = $key ;
				$vo->paramvalue = $value ;
				
				$dao->add($vo);
				Session::set($key,$value);
			}
		}
		catch (Exception $e)
		{
			system_out($e);
			return false ;
		}
		
		return true ;
	}	
}
?>
