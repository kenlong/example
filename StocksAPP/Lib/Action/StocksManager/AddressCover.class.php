<?php
class AddressCover
{


	/**
	 * 地址名称转换为数字地址
	 *
	 * @param array $places 地址名称数组
	 * @param int $xNum 横向位个数
	 * @param int $yNum 纵向位个数
	 * @return array
	 */
	public static function NameToNumber($places,$xNum,$yNum)
	{
		$A = array();
		$B = array();

		//A
		for($i=0;$i<sizeof($places["A"]);$i++)
		{
			$place = $places["A"][$i];
			$add = AddressCover::singleNameToNum($place,$xNum,$yNum);
			array_push($A,$add) ;
		}

		//B
		for($i=0;$i<sizeof($places["B"]);$i++)
		{
			$place = $places["B"][$i] ;
			$add = AddressCover::singleNameToNum($place,$xNum,$yNum);
			array_push($B,$add) ;
		}

		$rtn["A"] = $A ;
		$rtn["B"] = $B ;

		return $rtn ;
	}


	/**
	 * 数字地址转换为地址名称
	 *
	 * @param array $add array("A"=>array(),"B"=>array())
	 * @param string $No 表架编号 例如:GN01
	 * @param int $xNum  横向表位个数
	 * @param int $yNum  纵向表位个数
	 * @return array
	 */
	public static function NumberToName($adds,$No,$xNum,$yNum){
		$A = array();
		$B = array();

		//A
		for($i=0;$i<sizeof($adds["A"]);$i++)
		{
			$add = $adds["A"][$i] ;
			$place = $No . AddressCover::singleNumToName($add,"A",$xNum,$yNum);

			array_push($A,$place);
		}

		//B
		for ($i=0;$i<sizeof($adds["B"]);$i++)
		{
			$add = $adds["A"][$i] ;
			$place = $No . AddressCover::singleNumToName($add,"B",$xNum,$yNum);

			array_push($B,$place);
		}

		$rtn["A"] = $A ;
		$rtn["B"] = $B ;

		return $rtn ;
	}

	/**
	 * 单个表位索引转换成名称
	 *
	 * @param int $index     表位序列树
	 * @param String $panel  表位面      例如:GN01A
	 * @param int $xNum      横向表位个数
	 * @param int $yNum      纵向表位个数
	 * @return String
	 */
	public static function singleNumToName($index,$panel,$xNum,$yNum){
		$y = (int)(($index-1)/$xNum) +1;
		$x = ($index-1)%$xNum +1;

		if($panel=="B"){
			$x = $xNum - $x + 1 ;
		}

		$y = sprintf("%02s",$y);
		$x = sprintf("%02s",$x);

		return $panel.$y.$x ;
	}


	/**
	 * 将单个表位名称转换位数字序列
	 * 将B面反转
	 *
	 * @param string $place
	 * @param int $xNum
	 * @param int $yNum
	 * @return string
	 */
	public static function singleNameToNum($place,$xNum,$yNum)
	{
		$panel = substr($place,4,1);
		$y = (int)substr($place,5,2) ;
		$x = (int)substr($place,7,2) ;

		if($panel =='B')
		{
			$x = $xNum - $x + 1 ;
		}

		$rtn = ($y-1) * $xNum + $x ;

		return $rtn ;
	}

	
	/**
	 * 将单个表位名称转换位数字序列
	 * 不将B面反转
	 *
	 * @param string $place
	 * @param int $xNum
	 * @param int $yNum
	 * @return string
	 */
	public static function singleNameToSerialNum($place,$xNum,$yNum)
	{
		$panel = substr($place,4,1);
		$y = (int)substr($place,5,2) ;
		$x = (int)substr($place,7,2) ;

		$rtn = ($y-1) * $xNum + $x ;

		return $rtn ;
	}
	


	/**
	 * 放置表
	 * 将表位代码按照YCBK的格式转换
	 *
	 * @param array $places
	 * @return array
	 */
	public static function changeCodeForPut($places){
		for($i=1;$i<=sizeof($places);$i++){
			if($places[$i]["A"]=="Y" && $places[$i]["B"]=="N"){
				$places[$i]["A"]="F" ;
				$places[$i]["B"]="N" ;
			}
			else if($places[$i]["A"]=="N" && $places[$i]["B"]=="Y"){
				$places[$i]["A"]="N" ;
				$places[$i]["B"]="F" ;
			}
			else if($places[$i]["A"]=="Y" && $places[$i]["B"]=="Y"){
				$places[$i]["A"]="F" ;
				$places[$i]["B"]="F" ;
			}
			else if($places[$i]["A"]=="N" && $places[$i]["B"]=="N"){
				$places[$i]["A"]="D" ;
				$places[$i]["B"]="D" ;
			}
		}
		return $places ;
	}

	/**
	 * 取表
	 * 将表位代码按照YCBK的格式转换
	 *
	 * @param array $places
	 * @return array
	 */
	public static function changeCodeForGet($places){
		for($i=1;$i<=sizeof($places);$i++){
			if($places[$i]["A"]=="Y" && $places[$i]["B"]=="N"){
				$places[$i]["A"]="F" ;
				$places[$i]["B"]="N" ;
			}
			else if($places[$i]["A"]=="N" && $places[$i]["B"]=="Y"){
				$places[$i]["A"]="N" ;
				$places[$i]["B"]="F" ;
			}
			else if($places[$i]["A"]=="Y" && $places[$i]["B"]=="Y"){
				$places[$i]["A"]="F" ;
				$places[$i]["B"]="F" ;
			}
			else if($places[$i]["A"]=="N" && $places[$i]["B"]=="N"){
				$places[$i]["A"]="U" ;
				$places[$i]["B"]="U" ;
			}
		}
		return $places ;
	}
}
?>