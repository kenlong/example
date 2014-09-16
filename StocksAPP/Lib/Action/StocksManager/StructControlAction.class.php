<?php
import("@.Action.StocksManager.YCBK");
import("@.Action.StocksManager.AddressCover");
import("@.Action.StocksManager.ConfirmStationAction");
import("@.Action.StorageManager.StorageAction");
import("@.Action.StocksManager.StocksManagerAction");
import("@.Action.SystemSetup.GetSystemSetup");
class StructControlAction
{
	/**
	 * 表架地址
	 *
	 * @var string
	 */
	private $address ;

	/**
	 * 端口
	 *
	 * @var string
	 */
	private $port ;

	/**
	 * com口
	 *
	 * @var string
	 */
	private $com ;
	
	/**
	 * comToNet 类型 
	 *
	 * @var unknown_type
	 */
	private $comToNetType ;
	
	/**
	 * 等待时间
	 *
	 * @var int
	 */
	private $waittime = 300000 ;
		
	/**
	 * 循环次数
	 *
	 * @var 
	 */
	private $looptimes = 8 ;

	private $logs ;
	
	private $ycbk ;
	
	//构造函数
	public function __construct()
	{		
		$this->address = Session::get('structadd');
		$this->port = Session::get('structport');
		$this->comToNetType = Session::get('comToNetType');
		$this->com = 'COM1' ;		
	}

	/**
	 * 获取表架参数
	 *@param  String $place  仓库
	 * @return array Example:array( array("No"=>"GN01","xNum"=>10,"yNum"=>5))
	 */
	public function getStructParm($place)
	{
		$storage = new StorageAction();
		$result = $storage->findByCondition("storageName = '$place'");
		if(sizeof($result)>0)
		{
			$upnode = $result[0]["storageNo"] ;
		}
		else
		{
			throw new Exception("获取仓库参数出错!");
		}
		$result = $storage->findByCondition("stype = '表架' and upNode like '$upnode%'");

		$rtn = array();
		for($i=0;$i<sizeof($result);$i++)
		{
			$struct = array() ;
			$struct["No"] = $result[$i]["storageName"] ;
			$struct["xNum"] = $result[$i]["xNum"] ;
			$struct["yNum"] = $result[$i]["yNum"] ;
			$struct["panel"] = $result[$i]["panel"] ;
			$struct["comport"] = $result[$i]["comport"] ;
			$struct["hardadd"] = $result[$i]["hardadd"] ;
			$struct["ipaddress"] = $result[$i]["ipaddress"] ;
			$struct["delay"] = Session::get('structdelay') ;
			
			array_push($rtn,$struct);
		}

		return $rtn ;
	}

	/**
	 * 通过表架号获取表架参数
	 *
	 * @param string $structno
	 * @return arrray
	 */
	public function getStructparmBySingle($structno)
	{
		$storage = new StorageAction();
		$result = $storage->findByCondition("storageName = '$structno'");
		if(!$result)
		{
			return false ;
		}

		return $result[0] ;
	}



	/**
	 * 查询以表架已经使用的表位
	 * 参  数:表架
	 * 返回值: Example: array(1=>array("A"=>"Y","B"="N"),2=>array("A"=>"N","B"="Y"))
	 * 					"Y" 有表 ; "N" 无表
	 */
	public function queryHave($struct)
	{
		//DEBUG
		if(true == STRUCT_DEBUG)
		{
			$result["A"] = array("GN01A0103");
			//$result["A"] = array("GN01A0101","GN01A0102","GN01A0103");
			$result["B"] = array();
			//$result["A"] = array("GN01A0101","GN01A0102","GN01A0301");
			//$result["B"] = array("GN01B0103","GN01B0309");

			return $result ;
		}
		
		//如果表架没有xNum或者yNum,则获取
		if(!$struct["xNum"] || !$struct["yNum"])
		{
			$structparm = $this->getStructparmBySingle($struct["No"]);
			if($structparm)
			{
				$struct["xNum"] = $structparm["xNum"] ;
				$struct["yNum"] = $structparm["yNum"] ;
				$struct["comport"] = $structparm["comport"] ;
				$struct["hardadd"] = $structparm["hardadd"] ;
				$struct["ipaddress"] = $structparm["ipaddress"] ;
				$struct["delay"] = Session::get('structdelay') ;
			}
		}
		
		$this->setLogs("structparms of query:".print_r($struct,true));
		
		$this->address = $struct["ipaddress"]?$struct["ipaddress"]:$this->address ;
		$this->com = $struct["comport"]?$struct["comport"]:'COM1';
		$add = $struct["hardadd"]?$struct["hardadd"]:(int)substr($struct["No"],2,2);
			
		//system_out("address:".$this->address);
		//system_out("com:".$this->com);
		//system_out("hardadd:".$add);
			
		$ycbk = new YCBK($this->address,$this->port,$this->com,$this->comToNetType);
		$A = array();
		$B = array();
		
		
		//操作硬件
		$ycbk->close();
		for($i=0;$i<$this->looptimes;$i++)
		{
			$ycbk->open();
			//获取表架使用情况
			$result = $ycbk->querySwitchStatus($add);
			$ycbk->close();
			if($result)
				break ;
			
			usleep($this->waittime);
		}
				
		if(!$result) throw new Exception("查询表架[".$add."]使用情况出错:".$ycbk->getError(),101);
		
		for($i=1;$i<sizeof($result)+1;$i++)
		{
			//A面
			if($result[$i]["A"]=="Y")
			{
				$station = array() ;
				$tmp = $struct["No"] . AddressCover::singleNumToName($i,'A',$struct["xNum"],$struct["yNum"]);

				array_push($A,$tmp);
			}

			//B面
			if($result[$i]["B"]=="Y")
			{
				$station = array() ;
				$tmp = $struct["No"] . AddressCover::singleNumToName($i,'B',$struct["xNum"],$struct["yNum"]);

				array_push($B,$tmp);
			}
		}
		
		$rtn["A"] = $A;
		$rtn["B"] = $B;
		
		Session::set($struct["No"],$result);
	
		$this->setLogs($ycbk->getLogs());
		
		return $rtn ;
	}

	//查询表位目前所处的状态
	public function queryState($struct,$state='')
	{
		$confirm = new ConfirmStationAction();

		$add = $struct["No"];

		$A = array();
		$B = array();

		$condition = "station like '" . $add . "%' and state = '$state'" ;//and confirm=0" ;
		$result = $confirm->queryUnconfirmStation($condition);
		for($i=0;$i<sizeof($result);$i++)
		{
			$state = array();
			$state["place"] = $result[$i]["station"] ;
			$state["state"] = $result[$i]["state"] ;

			if(substr($state["place"],4,1) =="A")
			{
				array_push($A,$state["place"]);
			}
			else
			{
				array_push($B,$state["place"]);
			}
		}

		$rtn["A"] = $A ;
		$rtn["B"] = $B ;

		return $rtn ;

	}

	//查询该表架已经确认的表位
	public function queryConfirm($struct)
	{
		$current = new StocksManagerAction();
		$add = $struct["No"] ;

		$A = array();
		$B = array();

		$sql = "station like '" . $add . "%'" ;
		$result = $current->queryCurrentStocks($sql);

		for($i=0;$i<sizeof($result);$i++)
		{
			$station = $result[$i]["station"] ;
			if(substr($station,4,1) =="A")
			{
				array_push($A,$station);
			}
			else
			{
				array_push($B,$station);
			}
		}

		$rtn["A"] = $A ;
		$rtn["B"] = $B ;
		
		return $rtn ;
	}



	/**
	 * 打开灯操作
	 *
	 * @param String $struct 表架参数 Example:struct = array("No" = "GN01","xNum"=>10,"yNum"=>5)
	 * @param String array $places 表位 Example:places = array("A"=>array("GN01A0101","GN01A0102")
	 * 														   "B"=>array("GN01B0101","GN01B0102"))
	 * @param String $stype 类型 get(取表),put(挂表)
	 * @return Array
	 */
	public function trunOn($struct,$places,$stype)
	{
		//system_out("places:".print_r($places,true));
		//DEBUG
		if(true==STRUCT_DEBUG)
		{
			$confirm = new ConfirmStationAction();
			try
			{
				$confirm->saveUnconfirmStation($places,$struct["No"],$stype=='put'?"U":"D" );
			}
			catch (Executive $e)
			{
				//system_out("StructControlAction.trunOn:" . print_r($e,true));
				throw new Exception($e);
			}

			$rtn["stype"] = $stype ;
			$rtn["places"] = $places ;
			
			return $rtn;
		}
		
		//如果表架没有xNum或者yNum,则获取
		if(!$struct["xNum"] || !$struct["yNum"])
		{
			$structparm = $this->getStructparmBySingle($struct["No"]);
			if($structparm)
			{
				$struct["xNum"] = $structparm["xNum"] ;
				$struct["yNum"] = $structparm["yNum"] ;
				$struct["comport"] = $structparm["comport"] ;
				$struct["hardadd"] = $structparm["hardadd"] ;
				$struct["ipaddress"] = $structparm["ipaddress"] ;
				$struct["delay"] = Session::get('structdelay') ;
			}
		}
		
		$this->setLogs("structparms of turnon light:".print_r($struct,true));
		
		$this->address = $struct["ipaddress"]?$struct["ipaddress"]:$this->address ;
		$this->com = $struct["comport"]?$struct["comport"]:'COM1';
		$add = $struct["hardadd"]?$struct["hardadd"]:(int)substr($struct["No"],2,2);

		$ycbk = new YCBK($this->address,$this->port,$this->com,$this->comToNetType);

		
		//system_out("places:".print_r($places,true));

		$placesNo = AddressCover::NameToNumber($places,$struct["xNum"],$struct["yNum"]);
		$ycbk->close();
		//system_out(Session::id());
		if(!Session::is_set($struct["No"]))
		{
			system_out("StructControlAccino.trunOn:is not set");
			//硬件操作
			for($i=0;$i<$this->looptimes;$i++)
			{
				$ycbk->open();
				//获取表架使用情况
				$station = $ycbk->querySwitchStatus($add);
				$ycbk->close();
				if($station)
					break ;
				
				usleep($this->waittime);
			}
			
			if(!$station) throw new Exception("查询表架[".$add."]使用情况出错:".$ycbk->getError(),101);
		}
		else 
		{
			$station = Session::get($struct["No"]);
			//system_out("test:".print_r($station,true));
		}
		
		
		
		//设置A面放表的表位的标记
		for($i=0;$i<sizeof($placesNo["A"]);$i++)
		{
			$index = $placesNo["A"][$i];
			$stype=='put'?$station[$index]["A"] = "U":$station[$index]["A"] = "D" ;
		}
		//设置B面放表的表位的标记
		for($i=0;$i<sizeof($placesNo["B"]);$i++)
		{
			$index = $placesNo["B"][$i];
			$stype=='put'?$station[$index]["B"] = "U":$station[$index]["B"] = "D" ;
		}		
		
		//执行硬件操作
		for($i=0;$i<$this->looptimes;$i++)
		{
			$ycbk->open();
			
			//挂表
			if($stype=='put')
			{
				//转换码
				$station = AddressCover::changeCodeForPut($station);
				//system_out("after addresscover station:".print_r($station,true));
				$result = $ycbk->uploadMeterStateLight($add,$station);
			}
			else //取表
			{
				//转换码
				$station = AddressCover::changeCodeForGet($station);
				//system_out("after addresscover station,get:".print_r($station,true) );
				$result = $ycbk->downloadMeterStateLight($add,$station);
			}
			
			$ycbk->close();
			if($result)
				break ;
				
			usleep($this->waittime);
		}
		
		if(!$result) throw new Exception("开启表架[".$add."]灯出错:".$ycbk->getError(),102);

		//保存开了的灯进数据库
		$confirm = new ConfirmStationAction();
		try
		{
			$confirm->saveUnconfirmStation($places,$struct["No"],$stype=='put'?"U":"D" );
		}
		catch (Executive $e)
		{
			$this->close($struct,$stype);
			throw new Exception($e);
		}

		$rtn["stype"] = $stype ;
		$rtn["places"] = $places ;
		
		$this->setLogs($ycbk->getLogs());
		
		return $rtn;
	}

	/**
	 * 开当灯操作
	 *
	 * @param  object $struct
	 * @param  string $place
	 * @param  string $stype 'put' or 'get'
	 * @return boolean
	 */
	public function onlyOneUploadMeterLightOn($struct,$place,$stype)
	{
		if(true==STRUCT_DEBUG)
		{
			return true ;
		}

		//如果表架没有xNum或者yNum,则获取
		if(!$struct["xNum"] || !$struct["yNum"])
		{
			$structparm = $this->getStructparmBySingle($struct["No"]);
			if($structparm)
			{
				$struct["xNum"] = $structparm["xNum"] ;
				$struct["yNum"] = $structparm["yNum"] ;
				$struct["comport"] = $structparm["comport"] ;
				$struct["hardadd"] = $structparm["hardadd"] ;
				$struct["ipaddress"] = $structparm["ipaddress"] ;
				$struct["delay"] = Session::get('structdelay') ;
			}
		}
		
		$this->setLogs("structparms of turnon single light:".print_r($struct,true));
		
		$this->address = $struct["ipaddress"]?$struct["ipaddress"]:$this->address ;
		$this->com = $struct["comport"]?$struct["comport"]:'COM1';
		$add = $struct["hardadd"]?$struct["hardadd"]:(int)substr($struct["No"],2,2);

		
		$ycbk = new YCBK($this->address,$this->port,$this->com,$this->comToNetType);

		
		$index = AddressCover::singleNameToSerialNum($place,$structparm["xNum"],$structparm["yNum"]);
		$pancel = substr($place,4,1);
		
		//操作硬件
		for($i=0;$i<$this->looptimes;$i++)
		{
			$ycbk->open();
			//获取表架使用情况
			$result = $ycbk->onlyOneUploadMeterLightOn($add,$pancel,$index) ;
			$ycbk->close();
			if($result)
				break ;
			
			usleep($this->waittime);
		}
		
		if(!$result) throw new Exception("开表架[".$add."]灯出错:".$ycbk->getError(),100300);

		$this->setLogs($ycbk->getLogs());
		
		return true ;
	}




	/**
	 * 关闭灯操作
	 *
	 * @param String $struct 表架参数 Example:struct = array("No" = "GN01","xNum"=>10,"yNum"=>5)
	 * @param String array $places 表位 Example:places = array("GN01A0101","GN01A0102")
	 * @param String $stype 类型 get(取表),put(挂表)
	 * @return Boolean
	 */
	public function trunOff($struct,$places,$stype)
	{
		//DEBUG
		if(true==STRUCT_DEBUG)
		{
			return true ;
		}

		//如果表架没有xNum或者yNum,则获取
		if(!$struct["xNum"] || !$struct["yNum"])
		{
			$structparm = $this->getStructparmBySingle($struct["No"]);
			if($structparm)
			{
				$struct["xNum"] = $structparm["xNum"] ;
				$struct["yNum"] = $structparm["yNum"] ;
				$struct["comport"] = $structparm["comport"] ;
				$struct["hardadd"] = $structparm["hardadd"] ;
				$struct["ipaddress"] = $structparm["ipaddress"] ;
				$struct["delay"] = Session::get('structdelay')  ;
			}
		}
		
		$this->setLogs("structparms of turnoff light:".print_r($struct,true));
		
		$this->address = $struct["ipaddress"]?$struct["ipaddress"]:$this->address ;
		$this->com = $struct["comport"]?$struct["comport"]:'COM1';
		$add = $struct["hardadd"]?$struct["hardadd"]:(int)substr($struct["No"],2,2);

		$ycbk = new YCBK($this->address,$this->port,$this->com,$this->comToNetType);

		for($i=0;$i<sizeof($places);$i++)
		{
			$index = AddressCover::singleNameToSerialNum($places[$i],$structparm["xNum"],$structparm["yNum"]);
			$panel = substr($places[$i],4,1);
			
			//操作硬件
			for($i=0;$i<$this->looptimes;$i++)
			{
				$ycbk->open();
				//获取表架使用情况
				$result = $ycbk->onlyOneUploadMeterLightOff($add,$panel,$index) ;
				$ycbk->close();
				if($result)
					break ;
				
				usleep($this->waittime);
			}
			
			if(!$result) throw new Exception("关闭表架[".$add."]灯出错:".$ycbk->getError(),100300);
		}

		$this->setLogs($ycbk->getLogs());
		
		return true ;
	}

	/**
	 * 关闭单个灯操作
	 *
	 * @param String $struct 表架参数 Example:struct = array("No" = "GN01","xNum"=>10,"yNum"=>5)
	 * @param String $places
	 * @param String $stype 类型 get(取表),put(挂表)
	 * @return Boolean
	 */
	public function trunoffSingle($struct,$place,$type)
	{
		//DEBUG
		if(true==STRUCT_DEBUG)
		{
			return true ;
		}

		//如果表架没有xNum或者yNum,则获取
		if(!$struct["xNum"] || !$struct["yNum"])
		{
			$structparm = $this->getStructparmBySingle($struct["No"]);
			if($structparm)
			{
				$struct["xNum"] = $structparm["xNum"] ;
				$struct["yNum"] = $structparm["yNum"] ;
				$struct["comport"] = $structparm["comport"] ;
				$struct["hardadd"] = $structparm["hardadd"] ;
				$struct["ipaddress"] = $structparm["ipaddress"] ;
				$struct["delay"] = Session::get('structdelay') ;
			}
		}
		
		$this->setLogs("structparms turnoff single light:".print_r($struct,true));
		
		$this->address = $struct["ipaddress"]?$struct["ipaddress"]:$this->address ;
		$this->com = $struct["comport"]?$struct["comport"]:'COM1';
		$add = $struct["hardadd"]?$struct["hardadd"]:(int)substr($struct["No"],2,2);

		$ycbk = new YCBK($this->address,$this->port,$this->com,$this->comToNetType);

		$index = AddressCover::singleNameToSerialNum($place,$structparm["xNum"],$structparm["yNum"]);
		$panel = substr($place,4,1);
		
		//操作硬件
		for($i=0;$i<$this->looptimes;$i++)
		{
			$ycbk->open();
			//获取表架使用情况
			$result = $ycbk->onlyOneUploadMeterLightOff($add,$panel,$index) ;
			$ycbk->close();
			if($result)
				break ;
			
			usleep($this->waittime);
		}
		
		if(!$result) throw new Exception("关闭表架[".$add."]灯出错:".$ycbk->getError(),100300);

		$this->setLogs($ycbk->getLogs());
		
		return true ;
	}



	/**
	 * 关闭状态
	 *
	 * @param String $struct 表架参数 Example:struct = array("No" = "GN01","xNum"=>10,"yNum"=>5)
	 * @param String $stype 类型 get(取表),put(挂表)
	 * @return Boolean
	 */
	public function close($struct,$stype)
	{
		//DEBUG
		if(true==STRUCT_DEBUG)
		{
			return true ;
		}
	
		//如果表架没有xNum或者yNum,则获取
		if(!$struct["xNum"] || !$struct["yNum"])
		{
			$structparm = $this->getStructparmBySingle($struct["No"]);
			if($structparm)
			{
				$struct["xNum"] = $structparm["xNum"] ;
				$struct["yNum"] = $structparm["yNum"] ;
				$struct["comport"] = $structparm["comport"] ;
				$struct["hardadd"] = $structparm["hardadd"] ;
				$struct["ipaddress"] = $structparm["ipaddress"] ;
				$struct["delay"] = Session::get('structdelay') ;
			}
		}		
		
		$this->address = $struct["ipaddress"]?$struct["ipaddress"]:$this->address ;
		$this->com = $struct["comport"]?$struct["comport"]:'COM1';
		$add = $struct["hardadd"]?$struct["hardadd"]:(int)substr($struct["No"],2,2);
		
		$ycbk = new YCBK($this->address,$this->port,$this->com,$this->comToNetType);
		
		//操作硬件
		for($i=0;$i<$this->looptimes;$i++)
		{
			$ycbk->open();
			
			if($stype=="put")
			{
				$result = $ycbk->exitUploadMeterState($add);
			}
			else
			{
				$result = $ycbk->exitDownloadMeterState($add);
			}
		
			$ycbk->close();
			if($result)
				break ;
			
			usleep($this->waittime);
		}
		
		if(!$result) throw new Exception("关闭表架[".$add."]状态出错:".$ycbk->getError(),1004);

		$this->setLogs($ycbk->getLogs());
		
		return true ;
	}


	/**
	 * 开启警报
	 *
	 * @param String $struct 表架参数 Example:struct = array("No" = "GN01","xNum"=>10,"yNum"=>5)
	 * @return Boolean
	 */
	public function startwarning($struct)
	{
		//DEBUG
		if(true==STRUCT_DEBUG)
		{
			return true ;
		}

		//如果表架没有xNum或者yNum,则获取
		if(!$struct["xNum"] || !$struct["yNum"])
		{
			$structparm = $this->getStructparmBySingle($struct["No"]);
			if($structparm)
			{
				$struct["xNum"] = $structparm["xNum"] ;
				$struct["yNum"] = $structparm["yNum"] ;
				$struct["comport"] = $structparm["comport"] ;
				$struct["hardadd"] = $structparm["hardadd"] ;
				$struct["ipaddress"] = $structparm["ipaddress"] ;
				$struct["delay"] = Session::get('structdelay') ;
			}
		}
		
		$this->address = $struct["ipaddress"]?$struct["ipaddress"]:$this->address ;
		$this->com = $struct["comport"]?$struct["comport"]:'COM1';
		$add = $struct["hardadd"]?$struct["hardadd"]:(int)substr($struct["No"],2,2);

		$ycbk = new YCBK($this->address,$this->port,$this->com,$this->comToNetType);

		//操作硬件
		for($i=0;$i<$this->looptimes;$i++)
		{
			$ycbk->open();
			//获取表架使用情况
			$result = $ycbk->setWarning($add);
			$ycbk->close();
			if($result)
				break ;
			
			usleep($this->waittime);
		}
		
		if(!$result) throw new Exception("开启表架[".$add."]报警出错:".$ycbk->getError(),1005);

		$this->setLogs($ycbk->getLogs());
		
		return true ;
	}

	/**
	 *关闭警报
	 *
	 * @param String $struct 表架参数 Example:struct = array("No" = "GN01","xNum"=>10,"yNum"=>5)
	 * @return boolean
	 */
	public function stopwarning($struct)
	{
		//DEBUG
		if(true==STRUCT_DEBUG)
		{
			return true ;
		}

		//如果表架没有xNum或者yNum,则获取
		if(!$struct["xNum"] || !$struct["yNum"])
		{
			$structparm = $this->getStructparmBySingle($struct["No"]);
			if($structparm)
			{
				$struct["xNum"] = $structparm["xNum"] ;
				$struct["yNum"] = $structparm["yNum"] ;
				$struct["comport"] = $structparm["comport"] ;
				$struct["hardadd"] = $structparm["hardadd"] ;
				$struct["ipaddress"] = $structparm["ipaddress"] ;
				$struct["delay"] = Session::get('structdelay') ;
			}
		}
		
		$this->address = $struct["ipaddress"]?$struct["ipaddress"]:$this->address ;
		$this->com = $struct["comport"]?$struct["comport"]:'COM1';
		$add = $struct["hardadd"]?$struct["hardadd"]:(int)substr($struct["No"],2,2);

		$ycbk = new YCBK($this->address,$this->port,$this->com,$this->comToNetType);

		//操作硬件
		for($i=0;$i<$this->looptimes;$i++)
		{
			$ycbk->open();
			//获取表架使用情况
			$result = $ycbk->exitWarning($add);
			$ycbk->close();
			if($result)
				break ;
			
			usleep($this->waittime);
		}
		
		if(!$result) throw new Exception("关闭表架[".$add."]报警出错:".$ycbk->getError(),1006);

		$this->setLogs($ycbk->getLogs());
		
		return true ;
	}


	/**
	 * 进入休眠状态
	 *
	 * @param String $struct 表架参数 Example:struct = array("No" = "GN01","xNum"=>10,"yNum"=>5)
	 * @return boolean
	 */
	public function startsleep($struct)
	{
		//DEBUG
		if(true==STRUCT_DEBUG)
		{
			return true ;
		}

		//如果表架没有xNum或者yNum,则获取
		if(!$struct["xNum"] || !$struct["yNum"])
		{
			$structparm = $this->getStructparmBySingle($struct["No"]);
			if($structparm)
			{
				$struct["xNum"] = $structparm["xNum"] ;
				$struct["yNum"] = $structparm["yNum"] ;
				$struct["comport"] = $structparm["comport"] ;
				$struct["hardadd"] = $structparm["hardadd"] ;
				$struct["ipaddress"] = $structparm["ipaddress"] ;
				$struct["delay"] = Session::get('structdelay') ;
			}
		}
		
		$this->address = $struct["ipaddress"]?$struct["ipaddress"]:$this->address ;
		$this->com = $struct["comport"]?$struct["comport"]:'COM1';
		$add = $struct["hardadd"]?$struct["hardadd"]:(int)substr($struct["No"],2,2);

		$ycbk = new YCBK($this->address,$this->port,$this->com,$this->comToNetType);

		//操作硬件
		for($i=0;$i<$this->looptimes;$i++)
		{
			$ycbk->open();
			//获取表架使用情况
			$result = $ycbk->warningForInvaildDownloadMeterInSleep($add);
			$ycbk->close();
			if($result)
				break ;
			
			usleep($this->waittime);
		}
		
		if(!$result) throw new Exception("开启表架[".$add."]休眠非法取表警报出错:".$ycbk->getError(),1007);

		$this->setLogs($ycbk->getLogs());
		
		return true ;
	}

	/**
	 * 获取版本信息
	 *
	 * @param String $struct 表架参数 Example:struct = array("No" = "GN01","xNum"=>10,"yNum"=>5)
	 * @return String
	 */
	public function getVersionInfo($struct)
	{
		//DEBUG
		if(true==STRUCT_DEBUG)
		{
			return "iNFO" ;
		}

		//如果表架没有xNum或者yNum,则获取
		if(!$struct["xNum"] || !$struct["yNum"])
		{
			$structparm = $this->getStructparmBySingle($struct["No"]);
			if($structparm)
			{
				$struct["xNum"] = $structparm["xNum"] ;
				$struct["yNum"] = $structparm["yNum"] ;
				$struct["comport"] = $structparm["comport"] ;
				$struct["hardadd"] = $structparm["hardadd"] ;
				$struct["ipaddress"] = $structparm["ipaddress"] ;
				$struct["delay"] = Session::get('structdelay') ;
			}
		}
		
		$this->address = $struct["ipaddress"]?$struct["ipaddress"]:$this->address ;
		$this->com = $struct["comport"]?$struct["comport"]:'COM1';
		$add = $struct["hardadd"]?$struct["hardadd"]:(int)substr($struct["No"],2,2);

		$ycbk = new YCBK($this->address,$this->port,$this->com,$this->comToNetType);
		
		//操作硬件
		for($i=0;$i<$this->looptimes;$i++)
		{
			$ycbk->open();
			//获取表架使用情况
			$result = $ycbk->getVersionInfo($add) ;
			$ycbk->close();
			if($result)
				break ;
			
			usleep($this->waittime);
		}
		
		if(!$result) throw new Exception("查询表架[".$add."]版本信息出错:".$ycbk->getError(),1008);

		return $result ;
	}


	/**
	 * 通过位置码开挂表灯
	 * @param array $place 要开等的位置码数组
	 */
	public function trunOnPutLightByStation($place)
	{
		try
		{
			$data = array();

			for($i=0;$i<sizeof($place);$i++)
			{
				$add = substr($place[$i],0,4);
				$parm["No"] = $add ;
				if(array_key_exists($add,$data))
				{
					if(substr($place[$i],4,1)=="A")
					{
						array_push($data[$add]->A,$place[$i]);
					}
					else
					{
						array_push($data[$add]->B,$place[$i]);
					}
				}
				else
				{
					$item = new structstemp();
					$item->parm = $parm ;

					if(substr($place[$i],4,1)=="A")
					{
						array_push($item->A,$place[$i]);
					}
					else
					{
						array_push($item->B,$place[$i]);
					}
					$data[$add] = $item ;
				}
			}


			//开灯
			foreach ($data as $val)
			{
				$parm = $val->parm ;
				$places["A"] = $val->A ;
				$places["B"] = $val->B ;

				//关闭挂表状态
				$this->close($parm,'put');
				$this->trunOn($parm,$places,'put');
			}
		}
		catch (Executive $e)
		{
			system_out("StocksManager.trunOnPutLightOnStation Exception:$e");
			throw new Exception($e);
		}

		return true ;
	}


	/**
	 * 开AB面日光灯
	 *
	 * @param object $struct 表架参数
	 */
	public function openSunlight($struct)
	{
		//DEBUG
		if(true==STRUCT_DEBUG)
		{
			return true ;
		}

		$this->address = $struct["ipaddress"]?$struct["ipaddress"]:$this->address ;
		$this->com = $struct["comport"]?$struct["comport"]:'COM1';
		$add = $struct["hardadd"]?$struct["hardadd"]:(int)substr($struct["No"],2,2);
		
		$ycbk = new YCBK($this->address,$this->port,$this->com,$this->comToNetType);

		$result = $ycbk->openSunlight($add) ;
		if(!$result)
		{
			$result = $ycbk->openSunlight($add) ;
			if(!$result) throw new Exception("开日表架[".$add."]光灯出错:".$ycbk->getError(),1009);
		}

		return true ;
	}

	/**
	 * 开A面日光灯
	 *
	 * @param object $struct 表架参数
	 */
	public function openSunlightA($struct)
	{
		//DEBUG
		if(true==STRUCT_DEBUG)
		{
			return true ;
		}

		$this->address = $struct["ipaddress"]?$struct["ipaddress"]:$this->address ;
		$this->com = $struct["comport"]?$struct["comport"]:'COM1';
		$add = $struct["hardadd"]?$struct["hardadd"]:(int)substr($struct["No"],2,2);

		$ycbk = new YCBK($this->address,$this->port,$this->com,$this->comToNetType);

		$result = $ycbk->openSunlightA($add) ;
		if(!$result)
		{
			$result = $ycbk->openSunlightA($add) ;
			if(!$result) throw new Exception("开表架[".$add."]A面日光灯出错:".$ycbk->getError(),1010);
		}

		return true ;
	}

	/**
	 * 开B面日光灯
	 *
	 * @param array object $struct 表架参数
	 */
	public function openSunlightB($struct)
	{
		//DEBUG
		if(true==STRUCT_DEBUG)
		{
			return true ;
		}

		$this->address = $struct["ipaddress"]?$struct["ipaddress"]:$this->address ;
		$this->com = $struct["comport"]?$struct["comport"]:'COM1';
		$add = $struct["hardadd"]?$struct["hardadd"]:(int)substr($struct["No"],2,2);

		$ycbk = new YCBK($this->address,$this->port,$this->com,$this->comToNetType);

		$result = $ycbk->openSunlightB($add) ;
		if(!$result)
		{
			$result = $ycbk->openSunlightB($add) ;
			if(!$result) throw new Exception("开表架[".$add."]B面日光灯出错:".$ycbk->getError(),1011);
		}

		return true ;
	}

	/**
	 * 关闭日光灯
	 *
	 * @param array object $struct 表架参数
	 */
	public function closeSunlight($struct)
	{
		//DEBUG
		if(true==STRUCT_DEBUG)
		{
			return true ;
		}

		$this->address = $struct["ipaddress"]?$struct["ipaddress"]:$this->address ;
		$this->com = $struct["comport"]?$struct["comport"]:'COM1';
		$add = $struct["hardadd"]?$struct["hardadd"]:(int)substr($struct["No"],2,2);

		$ycbk = new YCBK($this->address,$this->port,$this->com,$this->comToNetType);

		$result = $ycbk->closeSunlight($add) ;
		if(!$result)
		{
			$result = $ycbk->closeSunlight($add) ;
			if(!$result) throw new Exception("关闭表架[".$add."]日光灯出错:".$ycbk->getError(),1012);
		}

		return true ;
	}

	/**
	 * 关闭日光灯A
	 *
	 * @param array object $struct 表架参数
	 */
	public function closeSunlightA($struct)
	{
		//DEBUG
		if(true==STRUCT_DEBUG)
		{
			return true ;
		}

		$this->address = $struct["ipaddress"]?$struct["ipaddress"]:$this->address ;
		$this->com = $struct["comport"]?$struct["comport"]:'COM1';
		$add = $struct["hardadd"]?$struct["hardadd"]:(int)substr($struct["No"],2,2);

		$ycbk = new YCBK($this->address,$this->port,$this->com,$this->comToNetType);

		$result = $ycbk->closeSunlightA($add) ;
		if(!$result)
		{
			$result = $ycbk->closeSunlightA($add) ;
			if(!$result) throw new Exception("关闭表架[".$add."]A面日光灯出错:".$ycbk->getError(),1013);
		}

		return true ;
	}
	
	public function setLogs($log)
	{
		$this->logs .= $log."\n";
	}
	
	
	/**
	 * 获取表架操作日志
	 *
	 */
	public function getLogs()
	{
		return $this->logs ;
	}
	
	
}//end class StructControlAction

class structstemp
{
	public $parm ;
	public $A = array();
	public $B = array() ;
}

?>
