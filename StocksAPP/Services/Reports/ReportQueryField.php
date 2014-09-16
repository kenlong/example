<?php
class ReportQueryField
{
	/**
	 * 获取报表高级查询的字段
	 *
	 * @param String $reportname 报表名称
	 * @return array
	 */
	public function getQueryField($reportname='')
	{
		if($reportname=='') return '' ;

		switch ($reportname)
		{
			case 'currentStocksQuery':
				$fields = array(
						  		array("label"=>"条码","data"=>"code","stype"=>"string"),
						  		array("label"=>"品名","data"=>"goodsName","stype"=>"string"),
						  		array("label"=>"型号","data"=>"spec","stype"=>"string"),
						  		array("label"=>"电压","data"=>"voltage1","stype"=>"string"),
						  		array("label"=>"电流","data"=>"current1","stype"=>"string"),
						  		array("label"=>"方向","data"=>"direct","stype"=>"string"),
						  		array("label"=>"等级","data"=>"grade","stype"=>"string"),
						  		array("label"=>"精度","data"=>"precision","stype"=>"string"),
						  		array("label"=>"单据类型","data"=>"billType","stype"=>"string"),
						  		array("label"=>"生产厂商","data"=>"madeIn","stype"=>"string"),
						  		array("label"=>"生产日期","data"=>"madeDate","stype"=>"string"),
						  		array("label"=>"仓库","data"=>"place","stype"=>"string"),
						  		array("label"=>"数量","data"=>"qty","stype"=>"number"),
						  		array("label"=>"入库日期","data"=>"indateDate","stype"=>"date")
							);
				break ;
			case 'currentStocksExpireQuery':
				$fields = array(
						  		array("label"=>"入库日期","data"=>"indateDate","stype"=>"date"),
						  		array("label"=>"条码","data"=>"code","stype"=>"string"),
						  		array("label"=>"品名","data"=>"goodsName","stype"=>"string"),
						  		array("label"=>"型号","data"=>"spec","stype"=>"string"),
						  		array("label"=>"电压","data"=>"voltage1","stype"=>"string"),
						  		array("label"=>"电流","data"=>"current1","stype"=>"string"),
						  		array("label"=>"精度","data"=>"precision","stype"=>"string"),
						  		array("label"=>"生产厂商","data"=>"madeIn","stype"=>"string"),
						  		array("label"=>"生产日期","data"=>"madeDate","stype"=>"string"),
						  		array("label"=>"仓库","data"=>"place","stype"=>"string"),
						  		array("label"=>"数量","data"=>"qty","stype"=>"number")
							);
				break ;
			case 'stocksDetailQuery':
				$fields = array(
						  		array("label"=>"进出日期","data"=>"billDate","stype"=>"date"),
						  		array("label"=>"条码","data"=>"code","stype"=>"string"),
						  		array("label"=>"品名","data"=>"goodsName","stype"=>"string"),
						  		array("label"=>"型号","data"=>"spec","stype"=>"string"),
						  		array("label"=>"电压","data"=>"voltage1","stype"=>"string"),
						  		array("label"=>"电流","data"=>"current1","stype"=>"string"),
						  		array("label"=>"精度","data"=>"precision","stype"=>"string"),
						  		array("label"=>"生产厂商","data"=>"madeIn","stype"=>"string"),
						  		array("label"=>"生产日期","data"=>"madeDate","stype"=>"string"),
						  		array("label"=>"仓库","data"=>"place","stype"=>"string"),
						  		array("label"=>"数量","data"=>"qty","stype"=>"number")
							);
				break ;
			case 'currentStocksGeneral' :
				$fields = array(
						  		array("label"=>"品名","data"=>"goodsName","stype"=>"string"),
						  		array("label"=>"型号","data"=>"spec","stype"=>"string"),
						  		array("label"=>"电压","data"=>"voltage1","stype"=>"string"),
						  		array("label"=>"电流","data"=>"current1","stype"=>"string"),
						  		array("label"=>"精度","data"=>"precision","stype"=>"string"),
						  		array("label"=>"生产厂商","data"=>"madeIn","stype"=>"string"),
						  		array("label"=>"生产日期","data"=>"madeDate","stype"=>"string"),
							);
				break ;
			default:
				return '' ;
		}


		return $fields ;

	}


}
?>