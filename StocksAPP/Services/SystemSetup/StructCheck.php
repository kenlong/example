<?php
import("@.Action.StocksManager.StructControlAction");
class StructCheck
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
		try
		{
			$result = $this->control->queryHave($struct);
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}
		
		return $this->getLogs();
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
	public function trunOn($struct,$stype)
	{
		try
		{
			$A = array();
			$B = array();
			$structparm = $this->control->getStructparmBySingle($struct["No"]);
			$x = $structparm["xNum"] ;
			$y = $structparm["yNum"] ;
			for($i=1;$i<=$x;$i++)
			{
				for($j=1;$j<=$y;$j++)
				{
					$A[] = $struct["No"]."A".sprintf("%02s",$i).sprintf("%02s",$j);
					$B[] = $struct["No"]."B".sprintf("%02s",$i).sprintf("%02s",$j);
				}
				$places["A"] = $A ;
				$places["B"] = $B ;
			}
			$result = $this->control->trunOn($struct,$places,$stype);
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}

		return $this->getLogs();
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
		try
		{
			$result = $this->control->trunOff($struct,$places,$stype);
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}

		return $this->getLogs();
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
		try
		{
			$this->control->close($struct,$stype);
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}

		return $this->getLogs();
	}


	/**
	 * 开启警报
	 *
	 * @param String $struct 表架参数 Example:struct = array("no" = "GN01","xNum"=>10,"yNum"=>5)
	 * @return Boolean
	 */
	public function startwarning($struct)
	{
		try
		{
			$this->control->startwarning($struct);
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}

		return $this->getLogs();
	}

	/**
	 *关闭警报
	 *
	 * @param String $struct 表架参数 Example:struct = array("no" = "GN01","xNum"=>10,"yNum"=>5)
	 * @return boolean
	 */
	public function stopwarning($struct)
	{
		try
		{
			$this->control->stopwarning($struct);
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}

		return $this->getLogs();
	}


	/**
	 * 进入休眠状态
	 *
	 * @param String $struct 表架参数 Example:struct = array("no" = "GN01","xNum"=>10,"yNum"=>5)
	 * @return boolean
	 */
	public function startsleep($struct)
	{
		try
		{
			$this->control->startsleep($struct) ;
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}

		return $this->getLogs();
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
