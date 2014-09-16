<?php
	class AutoNoAction
	{
		public static function getAutoNo($stype)
		{
			$autono = new AutoNoDao();

			$vo = $autono->find("stype= '$stype'");
			$result = $vo->toArray();
			$result["no"] = $result["no"] + 1;

			$result = $autono->save($result);
			if(!$result)
			{
				throw new Exception("自增单据编号出错!");
				return false ;
			}

			$result  = $autono->find("stype= '$stype'");
			$result = $result->toArray();
			$length = $result["length"] ;
			$result = $result["no"] ;
			if(!$result)
			{
				throw new Exception("获取单据号出错!");
				return false ;
			}
			if($result >pow(10,$length)-1)
			{
				throw new Exception("自动编号溢出!");
				return false ;
			}
			$format = "%0" . $length . "s" ;
			$result =  sprintf($format,$result);
			return $result ;

		}
	}
?>