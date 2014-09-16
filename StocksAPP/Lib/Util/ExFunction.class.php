<?php
class ExFunction
{
	/**
	 * 合计
	 *
	 * @param array $array
	 * @param string $fields
	 */
	public static function ex_array_sum($array,$fields)
	{
		if(empty($array)) return 0 ;
		$sum = 0 ;
		for($i=0;$i<sizeof($array);$i++)
		{
			$sum += $array[$i][$fields] ;
		}

		return $sum ;

	}

}
?>