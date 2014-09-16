<?php
import("@.Util.Excel");
import("@.Util.ExFunction");
import("@.Action.StocksManager.StocksManagerAction");
import("@.Action.StocksIndex.StocksIndexAction");
import("@.Action.Checkout.CheckoutCardAction");

class Report
{
	public function Report()
	{
		$this->methodTable = array(
				"printExcel"		=>	array("access"=>"remote")
		);
		
		ini_set('memory_limit','512M');
	}



	/**
	 * 当前库存查询
	 *
	 * @param string $condition
	 */
	public function queryCurrentStocks($condition='')
	{
		try
		{
			$sql = "select `code`,`goodsName`,`spec`,`qty`,`voltage1`,`current1`," .
									  "`direct`,`constant`,`grade`,`madeIn`,`madeDate`,`memo`,`place`,`station`,`billType`,`billType` " .
									  " from " . (DB_PREFIX?DB_PREFIX."_":"") . "currentstocks " ;
			if($condition!='')
			{
				$sql .= "where " . $condition ;
			}

			$field = "code,goodsName,spec,qty,voltage1,current1," .
								  "direct,constant,grade,madeIn,madeDate,memo,place,station,billType" ;

			$action = new StocksManagerAction();
			$result = $action->queryCurrentStocksWithSQL($sql);
			if($result)
			{
				//计算合计
				$sumrecord['goodsName'] = '合计:' ;
				$sumrecord["qty"] = ExFunction::ex_array_sum($result,'qty');
				array_push($result,$sumrecord);
			}
			
			$struct = $action->getStruct();

			//如果字段不是全部的,则把必须的字段替换
			if($field!='*')
			{
				$field = explode(',',$field);
				$visible = array("_type_"=>"INCLUDE","data"=>$field ) ;
				$struct["visible"] = $visible ;
				$struct["item"] = $field ;
			}

			$rtn["struct"] = $struct;
			$rtn["data"] = $result ;

			return $rtn ;
		}
		catch (Exception $e)
		{
			system_out("Report.currentStocksQuery error".$e);
			throw new ExcelDateUtil($e);
		}
	}


	/**
	 * 查询超期库存
	 *
	 * @param String $condition
	 * @param number $expireday
	 */
	public function queryCurrentStocksExpire($condition='',$expireday=0)
	{
		try
		{
			$stoday = date('y-m-d');
			
			switch (DB_TYPE)
				{
					case 'mysql':
						$sql = "select `code`,`goodsName`,`spec`,`qty`,`voltage1`,`current1`," .
				   					"`direct`,`constant`,`grade`,`madeIn`,`madeDate`,datediff('".$stoday."',indateDate) as overdate,`memo`,place`,`station`" .
				   					" from " . (DB_PREFIX?DB_PREFIX."_":"") . "currentstocks " ;
						break ;
					case 'SQLite':
						$sql = "select `code`,`goodsName`,`spec`,`qty`,`voltage1`,`current1`," .
				   					"`direct`,`constant`,`grade`,`madeIn`,`madeDate`,(julianday('now') - julianday(indateDate)) as overdate,`memo`,place`,`station`" .
				   					" from " . (DB_PREFIX?DB_PREFIX."_":"") . "currentstocks " ;
						break ;
				}

			if($condition!='')
			{
				switch (DB_TYPE)
				{
					case 'mysql':
						$sql .= " where " .$condition . " and datediff('" . $stoday . "',indateDate)>=" . $expireday ;
						break ;
					case 'SQLite':
						$sql .= " where " .$condition . " and julianday('now') - julianday(indateDate)>=" . $expireday ;
						break ;
				}
			}
			else
			{
				switch (DB_TYPE)
				{
					case 'mysql':
						$sql .= "where " . "datediff('" . $stoday . "',indateDate)>=" . $expireday ;
						break ;
					case 'SQLite':
						$sql .= "where " . "datediff('" . $stoday . "',indateDate)>=" . $expireday ;
						$sql .= " where " ."julianday('now') - julianday(indateDate)>=" . $expireday ;
						break ;
				}
				
			}

			$field = "code,goodsName,spec,qty,overdate,voltage1,current1," .
								  "direct,constant,grade,madeIn,madeDate,memo,place,station" ;

			$action = new StocksManagerAction();
			$result = $action->queryCurrentStocksWithSQL($sql);
			
			if($result)
			{
				for($i=0;$i<sizeof($result);$i++)
				{
					$result[$i]["overdate"] = (int)$result[$i]["overdate"] ;
				}
				
				//计算合计
				$sumrecord['goodsName'] = '合计:' ;
				$sumrecord["qty"] = ExFunction::ex_array_sum($result,'qty');
				array_push($result,$sumrecord);
			}
			
			$struct = $action->getStruct();

			//如果字段不是全部的,则把必须的字段替换
			if($field!='*')
			{
				$field = explode(',',$field);
				$visible = array("_type_"=>"INCLUDE","data"=>$field ) ;
				$struct["visible"] = $visible ;
				$struct["item"] = $field ;
			}

			$rtn["struct"] = $struct;
			$rtn["data"] = $result ;

			return $rtn ;
		}
		catch (ExecBackEnd $e)
		{
			system_out("Report.queryCurrentStocksExpire error:".$e);
			throw new ExcelDateUtil($e);
		}
	}

	/**
	 * 查询库存明细
	 *
	 * @param String $condition
	 */
	public function queryStocksDetail($sdate='',$edate='',$goodsName='',$barcode='*',$client='')
	{
		try
		{
			$action = new StocksManagerAction();
			$result = $action->query($sdate,$edate,$goodsName,$barcode,'','','','',$client);
			if($result)
			{
				//计算合计
				$sumrecord['goodsName'] = '合计:' ;
				$sumrecord["inqty"] = ExFunction::ex_array_sum($result,'inqty');
				$sumrecord["outqty"] = ExFunction::ex_array_sum($result,'outqty');
				array_push($result,$sumrecord);
			}
		

			$field = "billNo,billDate,goodsName,madeIn,code,factoryNo,spec,client,billType,inqty,outqty,voltage1,current1," .
								  "direct,constant,grade,madeDate,memo,place,station" ;


			$struct = $action->getStruct();
			//如果字段不是全部的,则把必须的字段替换
			if($field!='*')
			{
				$field = explode(',',$field);
				$visible = array("_type_"=>"INCLUDE","data"=>$field ) ;
				$struct["visible"] = $visible ;
				$struct["item"] = $field ;
			}

			$rtn["struct"] = $struct;
			$rtn["data"] = $result ;
				
			return $rtn ;
		}
		catch (Exception $e)
		{
			system_out("Report.queryStocksDetail error".$e);
			throw new ExcelDateUtil($e);
		}
	}

	/**
	 * 库存一览表
	 *
	 * @param string $condition
	 */
	public function queryCurrentStocksGeneral($condition)
	{
		try
		{
			$storage = new StorageDao();
			$sql = "select storageNo,storageName from " . (DB_PREFIX?DB_PREFIX."_":"") . "storage where stype = '库房' or stype='库区' order by storageNo";
			$storageList = $storage->query($sql)->toArray();

			$stocks = new CurrentStocksDao();
			$sql = "select goodsName,spec from " . (DB_PREFIX?DB_PREFIX."_":"") . "currentstocks " ;
			if($condition!='')
				$sql = $sql . " where " . $condition ;
			$sql .= "group by goodsName,spec" ;
			
			//echo $sql ;
			$goodsList = $stocks->query($sql)->toArray();
			//数据
			$data = array();

			for($i=0;$i<sizeof($goodsList);$i++)
			{
				$item = array();

				$item["goodsName"] = $goodsList[$i]["goodsName"] ;
				$item["spec"] = $goodsList[$i]["spec"] ;

				//计算每个仓库的值
				foreach ($storageList as $place)
				{
					$sql = "goodsName = '".$goodsList[$i]["goodsName"] .
						   		 "' and spec ='" . $goodsList[$i]["spec"] .
						   		 "' and placeno like'" .$place['storageNo'] . "%'" ;
					if($condition!='')
						$sql = $sql . " and " . $condition ;

					//echo $sql ."<br>";
					$sum = $stocks->getSum('qty',$sql);
					$item[$place['storageNo']] = $sum ;
				}

				array_push($data,$item);
			}

			//计算合计
			$sumrecord['goodsName'] = '合计:' ;
			foreach ($storageList as $place)
			{
				$sumrecord[$place['storageNo']] = ExFunction::ex_array_sum($data,$place['storageNo']);
			}
			array_push($data,$sumrecord);



			//表的结构
			$item  = array("goodsName","spec");
			$label = array(	"goodsName"=>"品名" ,
							"spec"=>"型号"
						);
			foreach ($storageList as $place)
			{
				array_push($item,$place['storageNo']);
				$label[$place['storageNo']] = $place['storageName'] ;
			}

			$columnitem = $item;
			$enabled = array("_type_"=>"*");
			$visible = array("_type_"=>"*" ) ;
			$align = array();
			$width = array("qty"=>50);
			$inputtype = array();

			$struct["item"] = $item ;
			$struct["label"] = $label ;
			$struct["columnitem"] = $columnitem ;
			$struct["enabled"] = $enabled ;
			$struct["visible"] = $visible ;
			$struct["align"] = $align ;
			$struct["width"] = $width ;
			$struct["inputtype"] = $inputtype ;

			//返回值
			$rtn["struct"] = $struct;
			$rtn["data"] = $data ;

			return $rtn ;
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}

	public function queryGoodsFllowInfo($condition)
	{
		try
		{
			
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
	
	
	/**
	 * 电能计量装置资产定位表
	 *
	 * @param string $code
	 * @param string $goodsName
	 * @param string $spec
	 * @param string $madeIn
	 * @param string $client
	 * @return boolean/array
	 */
	public function queryLocation($code='',$factoryNo='',$goodsName='',$spec='',$madeIn='',$client='')
	{
		$condition = "ifnull(code,'') like '%$code%' and ifnull(factoryNo,'') like '%$factoryNo%' and 
					  ifnull(goodsName,'') like '%$goodsName%' and ifnull(spec,'') like '%$spec%' and 
					  ifnull(madeIn,'') like '%$madeIn%' and ifnull(client,'') like '%$client%' and 
					  ifnull(code,'')<>'' and code is not null ";
		$codelist = array();
		if($code)
		{
			$codelist = array($code);
		}
		else 
		{
			$tables = StocksIndexAction::getTableNameAll('');
			//如果没有找到表,则返回false
			if(!$tables)
				return false ;
				
			$dao = new StocksDao();
			for($i=0;$i<sizeof($tables);$i++)
			{
				$result = array();
				$result = $dao->findAllDistinct($condition,$tables[$i]['tablename'],'code')->toResultSet();
				$codelist = $this->addInfo($result,$codelist);
			}
		}
		$locationList = array();
		
		//查询某个条码的当前状态
		for($i=0;$i<sizeof($codelist);$i++)
		{
			$result = $this->getStation($codelist[$i]);
			if($result)
				array_push($locationList,$result);
		}
		
		//print_r($locationList);
		
		//表的结构
		$item  = array("code","factoryNo","goodsName","spec","madeIn","location");
		$label = array(	"code"=>"条码",
						"factoryNo"=>"出厂编号",
						"goodsName"=>"品名" ,
						"spec"=>"型号",
						"madeIn"=>"生产厂家",
						"location"=>"存放位置"
					);
		
		$columnitem = $item;
		$enabled = array("_type_"=>"*");
		$visible = array("_type_"=>"*" ) ;
		$align = array();
		$width = array("goodsName"=>180,"madeIn"=>150);
		$inputtype = array();

		$struct["item"] = $item ;
		$struct["label"] = $label ;
		$struct["columnitem"] = $columnitem ;
		$struct["enabled"] = $enabled ;
		$struct["visible"] = $visible ;
		$struct["align"] = $align ;
		$struct["width"] = $width ;
		$struct["inputtype"] = $inputtype ;

		//返回值
		$rtn["struct"] = $struct;
		$rtn["data"] = $locationList ;
	
		return $rtn;
	}
	
	/**
	 * 往数组中添加不重复的值
	 *
	 * @param array $s
	 * @param array $o
	 */
	private function addInfo($s,$o)
	{
		for($i=0;$i<sizeof($s);$i++)
		{
			$val = $s[$i]['code'];
			$finded = array_search($val,$o) ;
			if(is_bool($finded) && $finded==false)
				array_push($o,$val);
		}

		return $o ;
	}
	
	private function getStation($code)
	{
		$dao = new StocksDao();
		$table = StocksIndexAction::getTableNameByCode($code);
		if(!$table)
			return false ;
		rsort($table);
		$result = $dao->findAll("code = '$code' ",$table[0])->toResultSet();
		if(!$result)
			return false ;
		
		$row = sizeof($result) -1 ;
		$rtn['code'] = $code ;
		$rtn['factoryNo'] = $result[$row]['factoryNo'] ;
		$rtn['goodsName'] = $result[$row]['goodsName'] ;
		$rtn['spec'] = $result[$row]['spec'] ;
		$rtn['madeIn'] = $result[$row]['madeIn'] ;
				
		//如果在库存,则写上当前库
		$currentstocks = new StocksManagerAction();
		$curren = $currentstocks->queryCurrentStocks("code = '$code'");
		if($curren)
		{
			$rtn["location"] = '在库['.$curren[0]['place'].']';
		}
		else 
		{
			
			//写上用途/去向 
			switch ($result[$row]['billType'])
			{
				case '调拨出库':
					$rtn['location'] = "调拨到[".$result[$row]['toplace']."]途中";
					break ;
				case '安装领表':
					$checkout = new CheckoutCardAction();
					//print_r($result);
					$install = $checkout->query("clientNo = '".$result[$row]['clientNo']."'");
					
					if ($install)
						$rtn["location"] = "安装到[".$result[$row]['client']."]";
					else 
						$rtn['location'] = "安装到[".$result[$row]['client']."]途中";
					break;
				default:
					$rtn['location'] = '内部移库途中';
			}
		}
					
		return $rtn ;
	}
	
	//come to here
	public function queryLocationDetail($code='')
	{
		$table = StocksIndexAction::getTableNameByCode($code);
		$volist = new VoList();
		$dao = new StocksDao();
		//开始查询
		for($i=0;$i<sizeof($table);$i++)
		{
			$tablename = $table[$i] ;
			$result = $dao->findAll("code='$code'",$tablename,'*','id');
			
			$volist->addAll($result);
		}
		
		$field = "billType,billNo,billDate,goodsName,madeIn,code,factoryNo,spec,client,inqty,outqty,voltage1,current1," .
				  "direct,constant,grade,madeDate,memo,place,station" ;

		$action = new StocksManagerAction();
		$struct = $action->getStruct();
		//如果字段不是全部的,则把必须的字段替换
		if($field!='*')
		{
			$field = explode(',',$field);
			$visible = array("_type_"=>"INCLUDE","data"=>$field ) ;
			$struct["visible"] = $visible ;
			$struct["item"] = $field ;
		}

		//返回值
		$rtn["struct"] = $struct;
		$rtn["data"] = $volist->toResultSet() ;
		
		return $rtn ;
	}
	
	
	/**
	 * 打印输出Excel
	 *
	 * @param array $data
	 * $data format: $data["title"] = array("大标题","一标题","二标题","三标题","四标题");
					 $data["data"] = array(array("a","b","c","d","e","d"),array("1","2","3","4","5"));
					 $data["size"] = 列数 ;
	 * @return sessionid
	 */
	public function printExcel($reportname,$data)
	{
		//输出报表的内容
		file_put_contents(APP_ROOT."/reportsPrint/reports/$reportname",serialize($data));
		return true ;
	}


}
?>
