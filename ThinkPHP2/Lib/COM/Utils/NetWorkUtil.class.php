<?php
class NetWorkUtil
{
	/**
	 * 通过腾讯接口获取当前机器的ip地址所属地区
	 *
	 * @return array
	 */
	public static function getCity()
	{
		$ip=file_get_contents("http://fw.qq.com/ipaddress");
		$ip=str_replace('"',' ',$ip);
		$ip2=explode("(",$ip);
		$a=substr($ip2[1],0,-2);
		$b=explode(",",$a);
		
		return $b;		
	}
}

?>