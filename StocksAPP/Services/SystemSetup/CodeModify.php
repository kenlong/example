<?php
class CodeModify
{
	public function readContent($path)
	{
		if(!file_exists($path))
		{
			$rtn["result"] = false ;
			$rtn["value"] =  "文件不存在" ;
			
			return $rtn ;
		}
		else 
		{
			$content = file_get_contents($path);
			$rtn["result"] = true ;
			$rtn["value"] = $content ;

			return $rtn ;
		}
	}
	
	public function saveContent($path,$content)
	{
		if(file_exists($path))
		{
			exec("cp $path $path".".back -f");
			@unlink($path);
		}
		
		file_put_contents($path,$content);	
		
		return true ;
	}
	
}
?>