<?php
class XMLUtil
{
	/**
	 * create an xml attribute with array
	 *
	 * @param array $array
	 * @param string $node
	 * 
	 * array example
	 * array(
	 * 		array('id'=>"2","content"=>"value"),
	 * 		array('id'=>"3","content"=>"value2")
	 * }
	 */
	public static function array2XMLAttribute($array,$node)
	{
		$xw = new XMLWriter();
		$xw->openMemory();
		$xw->startDocument('1.0','utf-8');
		for($i=0;$i<sizeof($array);$i++)
		{
			$xw->startElement($node);
			foreach ($array[$i] as $key => $value)
			{
				$xw->writeAttribute($key,$value);	
			}
			$xw->endElement($node);
		}
		
		return $xw->outputMemory(true);
	}
	
	/**
	 * create an xml with array
	 *
	 * @param array $array
	 * @param string $node
	 * @return string
	 */
	public static function array2XMLElement($array,$node)
	{
		$xw = new XMLWriter();
		$xw->openMemory();
		$xw->startDocument('1.0','utf-8');
		for($i=0;$i<sizeof($array);$i++)
		{
			$xw->startElement($node);
			foreach ($array[$i] as $key => $value)
			{
				$xw->writeElement($key,$value);	
			}
			$xw->endElement($node);
		}
		
		return $xw->outputMemory(true);
	}
	
	/**
	 * Change XML to an array
	 *
	 * @param string $data
	 * @return array
	 */
	public static function XML2array($data)
	{
		$result = array();
		if(is_object($data) || is_array($data))
		{
			if(is_object($data))
				$temp = get_object_vars($data);
			else 
				$temp = $data;
			
			if(!$temp)
				return '';
				
			foreach($temp as $key=>$value) 
				$result[$key] = XMLUtil::XML2array($value);
		}
		else 
		{
			return $data;
		}
		
		return $result;
	}
}
?>