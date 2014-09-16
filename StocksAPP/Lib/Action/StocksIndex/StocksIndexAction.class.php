<?php
import("@.Action.AutoNo.AutoNoAction");
import("@.Action.Code.CodeIndexAction");
class StocksIndexAction
{
	/**
	 * 查询出所有的TableName
	 *
	 * @param $condition
	 * @return Array
	 * @author kenlong.zhang
	 */
	public static function getTableNameAll($condition='')
	{
		$dao = new StocksIndexDao();
		$result = $dao->findAll($condition)->toResultSet();
		return $result ;
	}

	/**
	 * 通过日期获取表名
	 *
	 * @param date $sdte 开始日期
	 * @param dat $edate 结束日期
	 * 
	 * @return array when $begindate and $enddte are not emtpy 
	 *         string when has one empty of $begindate or $enddte 
	 */
	public static function getTableNameByDate($begindate='',$enddte='')
	{
		try 
		{
			if($begindate!='' && $enddte!='')
			{
				$y = substr($enddte,0,4);
				$m = substr($enddte,5,2);
				$sdate = strtotime("$y-$m-1"); // 该月开始
				$edate = strtotime("$y-$m-".date("t",$sdate)); //该月结束
				
				$y = substr($begindate,0,4);
				$m = substr($begindate,5,2);
				$sdate = strtotime("$y-$m-1"); // 该月开始
				
				$begindate = date('Y-m-d',$sdate) ;
				$enddte = date('Y-m-d',$edate) ;
				
				$condition = "begindate >='$begindate' and enddate <='$enddte'" ;
			}
			else if ($begindate!='') 
			{
				$condition = "'$begindate' between begindate and enddate" ;
			}
			else if($enddte!='')
			{
				$condition = "'$enddte' between begindate and enddate" ;
			}
			else
			{
				$condition = '' ;	
			}
			
			$result = StocksIndexAction::getTableNameAll($condition);
			
			//如果两个日期都不为空则,返回数组,否则返回字符串(表名)
			if(($begindate!='' && $enddte != '') || ($begindate=='' && $enddte==''))
			{
				return $result;
			}
			else if($result)
			{
				return $result[0]['tablename'] ;
			}
			
			//默认返回空
			return '' ;
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
		
	/**
	 * 仅仅通过条码获取表名
	 *
	 * @param string $code
	 * @return array
	 */
	public static function getTableNameByCode($code)
	{
		try
		{
			$indexdate = CodeIndexAction::queryIndexByCode($code);
			
			if(!$indexdate) return false ;
			
			sort($indexdate);
			
			$result = array();
			
			foreach ($indexdate as $item)
			{
				$rtn = StocksIndexAction::getTableNameByDate($item);
				$search = array_search($rtn,$result) ;
				if($rtn && $search==false && is_bool($search))
					array_push($result,$rtn);
			}
			
			return $result ;		
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
		
	}
	
	
	/**
	 * 获取数据存放表名
	 *
	 * @param Date $date 日期
	 * @return String 表名
	 */
	public static function getSaveTableName($date)
	{
		try
		{
			$result = StocksIndexAction::getTableNameByDate($date);
			if($result)
			{
				return $result ;
			}
			else
			{
				//create new table
				$tablename = StocksIndexAction::createNewTable();
				
				//create table index
				if($tablename)
				{
					StocksIndexAction::createTableIndex($date,$tablename);
				}
				
				return $tablename ;
			}
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}

	
	/**
	 * 建立新的存储表
	 *
	 * @return boolean 
	 */
	public static function createNewTable()
	{		
		//获取表序列名
		$no = AutoNoAction::getAutoNo('stocksindex');
		$tablename = (DB_PREFIX?DB_PREFIX."_":"")."stocks" . $no ;
		
		//create table ;
		switch (DB_TYPE)
		{
			case 'mysql':
			$sql = <<< SQL
CREATE TABLE `$tablename` (
  `id` int(11) NOT NULL auto_increment,
  `sysno` varchar(50) default NULL COMMENT '系统单号',
  `code` varchar(50) default NULL COMMENT '条码',
  `factoryNo` varchar(50) default NULL COMMENT '出厂编号',
  `goodsName` varchar(50) default NULL COMMENT '品名',
  `spec` varchar(50) default NULL COMMENT '型号',
  `voltage1` varchar(16) default NULL COMMENT '电压1',
  `voltage2` varchar(16) default NULL COMMENT '电压2',
  `current1` varchar(16) default NULL COMMENT '电流',
  `current2` varchar(16) default NULL COMMENT '电压2',
  `baseRange` varchar(16) default NULL COMMENT '相/线',
  `direct` varchar(50) default NULL COMMENT '方向',
  `constant` varchar(16) default NULL COMMENT '常数',
  `precision` varchar(16) default NULL COMMENT '精度',
  `lineIn` varchar(16) default NULL COMMENT '接入方式',
  `ratedLoad` varchar(16) default NULL COMMENT '额定负载',
  `grade` varchar(50) default NULL COMMENT '等级',
  `madeIn` varchar(50) default NULL COMMENT '生产厂家',
  `madeDate` date default NULL COMMENT '生产日期',
  `placeno` varchar(50) default NULL COMMENT '仓库编号',
  `place` varchar(50) default NULL COMMENT '仓库',
  `toplace` varchar(50) default NULL COMMENT '调拨到的仓库',
  `station` varchar(16) default NULL COMMENT '表位/周转箱',
  `billNo` varchar(10) default NULL COMMENT '单号',
  `billDate` date default NULL COMMENT '单据日期',
  `billType` varchar(8) default NULL COMMENT '单据类型',
  `orderNo` varchar(50) default NULL COMMENT '申请单号',
  `clientNo` varchar(32) default NULL COMMENT '客户编号',	
  `client` varchar(50) default NULL COMMENT '供应商/客户',
  `address` varchar(256) default NULL COMMENT '客户地址',
  `sendMan` varchar(16) default NULL COMMENT '送表人/取表人',
  `saveMan` varchar(16) default NULL COMMENT '入库/出库人',
  `inqty` int(11) default NULL COMMENT '入库数',
  `outqty` int(11) default NULL COMMENT '出库数',
  `inoutType` varchar(6) default NULL COMMENT '入库/出库/报废',
  `memo` TEXT  default NULL COMMENT '备注',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;
SQL;
				break ;
			case 'SQLite':
			$sql = <<< SQL
CREATE TABLE `$tablename` (
   id  INTEGER NOT NULL PRIMARY KEY,
   sysno  varchar(50) NULL ,
   code  varchar(50) NULL ,
   factoryNo TEXT NULL ,
   goodsName  varchar(50) NULL ,
   spec  varchar(50) NULL ,
   voltage1  varchar(16) NULL ,
   voltage2  varchar(16) NULL ,
   current1  varchar(16) NULL ,
   current2  varchar(16) NULL ,
   baseRange  varchar(16) NULL ,
   direct  varchar(50) NULL ,
   constant  varchar(16) NULL ,
   precision  varchar(16) NULL ,
   lineIn  varchar(16) NULL ,
   ratedLoad  varchar(16) NULL ,
   grade  varchar(50) NULL ,
   madeIn  varchar(50) NULL ,
   madeDate  date NULL ,
   placeno  varchar(50) NULL ,
   place  varchar(50) NULL ,
   toplace  varchar(50) NULL ,
   station  varchar(16) NULL ,
   billNo  varchar(10) NULL ,
   billDate  date NULL ,
   billType  varchar(8) NULL ,
   orderNo  varchar(50) NULL ,
   clientNo varchar(32) NULL ,
   client  varchar(50) NULL ,
   address  varchar(256) NULL ,
   sendMan  varchar(16) NULL ,
   saveMan  varchar(16) NULL ,
   inqty  INTEGER NULL ,
   outqty  INTEGER NULL ,
   inoutType  varchar(6) NULL,
   memo TEXT  NULL
);
SQL;
				break ;	
			default:
				return false ;
		}
		
		$dao = new StocksIndexDao();
		
		try
		{
			$result = $dao->execute($sql);
		}
		catch (Executive $e)
		{
			throw new Exception($e,'StocksIndexAction.getTableName');
		}
				
		return $tablename ;
	}
	
	/**
	 * 建立表的索引
	 *
	 * @param Date $date
	 * @param String $tablename
	 * @return boolean
	 * @author kenlnog.zhang
	 */
	private static  function createTableIndex($date,$tablename)
	{
		try
		{
			$dao = new StocksIndexDao();
			$vo = new StocksIndexVo();
			
			$y = substr($date,0,4);
			$m = substr($date,5,2);
			
			$sdate = strtotime("$y-$m-1"); // 该月开始
			$edate = strtotime("$y-$m-".date("t",$sdate)); //该月结束
			
			$vo->begindate = date('Y-m-d',$sdate) ;
			$vo->enddate = date('Y-m-d',$edate) ;
			$vo->tablename = $tablename ;
			
			$dao->add($vo);

			$dao->commit() ;
			
			return true ;
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
}
?>
