<?php
import("@.Action.StocksManager.StructControlAction");
class Struct
{
	private $control ;

	//构造函数
	public function __construct()
	{
		$this->control = new StructControlAction();
	}

	//获取参数
	public function getStructParm($place)
	{
		$result = $this->control->getStructParm($place);

		return $result ;
	}


	/**
	 * 查询以表架已经使用的表位
	 * 参  数:表架
	 * 返回值: Example: array(1=>array("A"=>"Y","B"="N"),2=>array("A"=>"N","B"="Y"))
	 * 					"Y" 有表 ; "N" 无表
	 */
	public function queryHave($struct)
	{
		$result = $this->control->queryHave($struct);
		return $result ;
	}

	//查询表位目前所处的状态
	public function queryState($struct,$state='')
	{
		$result = $this->control->queryState($struct,$state);
		return $result ;
	}

	/**
	 * 查询表架确认了的表位
	 */
	public function queryConfirm($struct)
	{
		$result = $this->control->queryConfirm($struct);
		return $result ;
	}

	/**
	 * 打开灯操作
	 *
	 * @param String $struct 表架参数 Example:struct = array("no" = "GN01","xNum"=>10,"yNum"=>5)
	 * @param String array $places 表位 Example:places = array("A"=>array("GN01A0101","GN01A0102")
	 * 														   "B"=>array("GN01B0101","GN01B0102"))
	 * @param String $stype 类型 get(取表),put(挂表)
	 * @return Boolean
	 */
	public function trunOn($struct,$places,$stype)
	{
		//system_out("places:".print_r($places,true));
		$result = $this->control->trunOn($struct,$places,$stype);

		return $result;
	}

	/**
	 * 关闭灯操作
	 *
	 * @param String $struct 表架参数 Example:struct = array("no" = "GN01","xNum"=>10,"yNum"=>5)
	 * @param String array $places 表位 Example:places = array("A"=>array("GN01A0101","GN01A0102")
	 * 														   "B"=>array("GN01B0101","GN01B0102"))
	 * @param String $stype 类型 get(取表),put(挂表)
	 * @return Boolean
	 */
	public function trunOff($struct,$places,$stype)
	{
		$result = $this->control->trunOff($struct,$places,$stype);

		return $result ;
	}

	/**
	 * 关闭状态
	 *
	 * @param String $struct 表架参数 Example:struct = array("no" = "GN01","xNum"=>10,"yNum"=>5)
	 * @param String $stype 类型 get(取表),put(挂表)
	 * @return Boolean
	 */
	public function close($struct,$stype)
	{
		$this->control->close($struct,$stype);

		return true ;
	}


	/**
	 * 开启警报
	 *
	 * @param String $struct 表架参数 Example:struct = array("no" = "GN01","xNum"=>10,"yNum"=>5)
	 * @return Boolean
	 */
	public function startwarning($struct)
	{
		$this->control->startwarning($struct);

		return true ;
	}

	/**
	 *关闭警报
	 *
	 * @param String $struct 表架参数 Example:struct = array("no" = "GN01","xNum"=>10,"yNum"=>5)
	 * @return boolean
	 */
	public function stopwarning($struct)
	{
		$this->control->stopwarning($struct);

		return true ;
	}


	/**
	 * 进入休眠状态
	 *
	 * @param String $struct 表架参数 Example:struct = array("no" = "GN01","xNum"=>10,"yNum"=>5)
	 * @return boolean
	 */
	public function startsleep($struct)
	{
		$this->control->startsleep($struct) ;

		return true ;
	}

	/**
	 * 获取版本信息
	 *
	 * @param String $struct 表架参数 Example:struct = array("no" = "GN01","xNum"=>10,"yNum"=>5)
	 * @return String
	 */
	public function getVersionInfo($struct)
	{
		$result = $this->control->getVersionInfo($struct) ;

		return $result ;
	}

	/**
	 * 开AB面日光灯
	 *
	 * @param object $struct
	 * @return boolean
	 */
	public function openSunlight($struct)
	{
		$result = $this->control->openSunlight($struct);

		return $result ;
	}

	/**
	 * 开A面日光灯
	 *
	 * @param object $struct
	 * @return boolean
	 */
	public function openSunlightA($struct)
	{
		$result = $this->control->openSunlightA($struct);

		return $result ;
	}

	/**
	 * 开B面日光灯
	 *
	 * @param object $struct
	 * @return boolean
	 */
	public function openSunlightB($struct)
	{
		$result = $this->control->openSunlightB($struct);

		return $result ;
	}

	/**
	 * 关闭AB面日光灯
	 *
	 * @param object $struct
	 * @return boolean
	 */
	public function closeSunlight($struct)
	{
		$result = $this->control->closeSunlight($struct);

		return $result ;
	}

	/**
	 * 关闭A面日光灯
	 *
	 * @param object $struct
	 * @return boolean
	 */
	public function closeSunlightA($struct)
	{
		$result = $this->control->closeSunlightA($struct);

		return $result ;
	}

	/**
	 * 获取操作日志
	 *
	 * @return unknown
	 */
	public function getLogs()
	{
		return $this->control->getLogs();
	}
	
}
?>
