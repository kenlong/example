<?php
class ViewLogs
{
	public function getLogFiles()
	{
  		$dirhandle=opendir(APP_PATH."/Logs");
  		$arrayFileName=array();   
  		while(($file=readdir($dirhandle))!==false)
  		{   
  			if(($file!=".")&&($file!=".."))
  			{   
				$arrayFileName[]=$file ;
				//array("FileName"=>substr($file,0,strlen($file)+$typelen));   
  			}
		}
		rsort($arrayFileName);
  		return   $arrayFileName;
	}
	
	public function readLogs($filename)
	{
		$filepath = APP_PATH."/Logs/".$filename;
		$content = file_get_contents($filepath);
		return $content;
	}
}
?>