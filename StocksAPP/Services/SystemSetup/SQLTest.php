<?php
class SQLTest
{
	public function exec($sql,$dbconfig,$unicode='gb2312')
	{
		$dao = new Dao('','jk_dnb_hgk_send','',false,$dbconfig);
		$sql = $this->auto_charset($sql,'utf8',$unicode);
		
		if(strpos($sql,'select')!==false)
		{
			$result = $dao->query($sql)->toArray();
			if($result)
			{
				$result = $this->auto_charset($result,$unicode,'utf8');
				for($i=0;$i<count($result);$i++)
				{
					for($j=count($result[$i]);$j>=0;$j--)
					{
						unset($result[$i][$j]);
					}
				}
				
				$rtn["result"] = true ;
				$rtn["value"]  = $result ;
			}
			else 
			{
				$rtn["result"] = false ;				
			}
		}
		else 
		{
			$result = $dao->execute($sql);
			if($result)
			{
				$rtn["result"] = true ;
				$rtn["value"] = $result ;
			}
			else 
			{
				$rtn["result"] = false ;
			}
		}
			
		return $rtn ;
	}
	
	/**
	 +----------------------------------------------------------
	 * 自动转换字符集 支持数组转换
	 * 需要 iconv 或者 mb_string 模块支持
	 * 如果 输出字符集和模板字符集相同则不进行转换
	 +----------------------------------------------------------
	 * @param string $fContents 需要转换的字符串
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 */
	private function auto_charset($fContents,$from='',$to='')
	{
	    if( strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents)) ){
	        //如果编码相同或者非字符串标量则不转换
	        return $fContents;
	    }
	    $from   =  strtoupper($from)=='UTF8'? 'utf-8':$from;
	    $to       =  strtoupper($to)=='UTF8'? 'utf-8':$to;
	    if(is_string($fContents) ) {
	    	
	    	if(function_exists('iconv')){
	            $rtn = iconv($from,$to,$fContents);
	            return trim($rtn);
	        }elseif(function_exists('mb_convert_encoding')){
				$rtn = mb_convert_encoding ($fContents, $to, $from);
	            return trim($rtn);
	        }else{
	            return $fContents;
	        }
	    }
	    elseif(is_array($fContents)){
	        foreach ( $fContents as $key => $val ) {
				$_key = 	$this->auto_charset($key,$from,$to);
	            $fContents[$_key] = $this->auto_charset($val,$from,$to);
				if($key != $_key ) {
					unset($fContents[$key]);
				}
	        }
	        return $fContents;
	    }
	    elseif(is_object($fContents)) {
			$vars = get_object_vars($fContents);
	        foreach($vars as $key=>$val) {
	            $fContents->$key = $this->auto_charset($val,$from,$to);
	        }
	        return $fContents;
	    }
	    else{
	        //halt('系统不支持对'.gettype($fContents).'类型的编码转换！');
	        return $fContents;
	    }
	}	
}
?>
