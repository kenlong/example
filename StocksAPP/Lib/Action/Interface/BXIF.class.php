<?php
class BXIF
{
	/**
	 * 获取条码信息
	 *
	 * @param $code
	 */
	public function getInfoByCode($code,$dbconfig)
	{
		try 
		{
			$sql = "select * from jk_dnb_hgk_send where fc_tm='".$code."'";
			$dao = new Dao('','jk_dnb_hgk_send','',false,$dbconfig);
			$rtn = $dao->query($sql)->toArray();

			$unicode = $dbconfig['unicode'];
			if(!$unicode)
				$unicode = 'gb2312';
					
			if($rtn)
			{
				$rtn = $this->auto_charset($rtn,$unicode,'utf8');
				
				$rtn = $rtn[0];
				$codeinfo['code'] = $rtn['fc_tm'] ;
				$codeinfo['factoryNo'] = $rtn['fvc_zzh'] ;
				$codeinfo['goodsName'] = $rtn['fvc_mc'] ;
				$codeinfo['spec'] = $rtn['fc_xs'] ;
				$codeinfo['current1'] = $rtn['fc_dl'] ;
				$codeinfo['voltage1'] = $rtn['fc_dy'] ;
				$codeinfo['direct'] = $rtn['fn_fx'] ;
				$codeinfo['constant'] = $rtn['fn_cs'].'/'.$rtn['fn_cs_wg'] ;
				$codeinfo['grade'] = $rtn['fc_dj'].'/'.$rtn['fc_dj_wg'] ;
				$codeinfo['madeIn'] = $rtn['fvc_zzc'] ;
				$codeinfo['madeDate'] = $rtn['fsd_ccrq'] ;
				$codeinfo['memo'] = '电能表' ;
				
				return $codeinfo ;	
			}
			else 
			{
				$sql = "select * from jk_hgq_hgk_send where fc_tm='".$code."'";
				$rtn = $dao->query($sql)->toArray();
				if($rtn)
				{
					$rtn = $this->auto_charset($rtn,$unicode,'utf8');
					
					$rtn = $rtn[0];
					$codeinfo['code'] = $rtn['fc_tm'] ;
					$codeinfo['factoryNo'] = $rtn['fvc_zzh'] ;
					$codeinfo['goodsName'] = $rtn['fvc_mc'] ;
					$codeinfo['spec'] = $rtn['fc_xs'] ;
					$codeinfo['current1'] = $rtn['fc_dl'] ;
					$codeinfo['voltage1'] = $rtn['fc_dy'] ;
					$codeinfo['direct'] = $rtn['fn_fx'] ;
					$codeinfo['constant'] = $rtn['fn_cs'] ;
					$codeinfo['grade'] = $rtn['fc_dj'] ;
					$codeinfo['madeIn'] = $rtn['fvc_zzc'] ;
					$codeinfo['madeDate'] = $rtn['fsd_ccrq'] ;
					$codeinfo['memo'] = '互感器' ;
					
					return $codeinfo ;
				}
			}
			
			return false ;
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
	
	/**
	 * 输出出仓数据
	 *
	 * @param $data
	 */
	public function outPut($data,$dbconfig)
	{
		//无条码不用做
		if(!$data["code"])
			return true ;
		
		$unicode = $dbconfig['unicode'];
		if(!$unicode)
			$unicode = 'gb2312';
					
		$billtypedao = new BilltypeDao();

		$billtype  = $data["billType"];

		$result = $billtypedao->find("billtypeDesc='$billtype'");
		
		$result = $result->toArray();
					
		if($result["stype"]!='OUT')
			return true ;
		
		$dao = new Dao('','jk_dnb_hgk_send','',false,$dbconfig);
		$dao->startTrans();
		try 
		{
			if($data["memo"]=='电能表')
				$sql = "insert into jk_dnb_hgk_recieve (fc_tm) values('".$data["code"]."')";
			else
				$sql = "insert into jk_hgq_hgk_recieve (fc_tm) values('".$data["code"]."')";
			
			$sql = $this->auto_charset($sql,'utf8',$unicode);
			
			$result = $dao->execute($sql);
		}
		catch (Exception $e)
		{
			$dao->rollback() ;
			throw new Exception($e);
		}
		
		$dao->commit();
		return true ;
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