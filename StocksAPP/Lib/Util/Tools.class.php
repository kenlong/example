<?php

/**
 * 系统工具类
 * 
 * @package DMS.Lib.Util
 * @author Benny.Zou <benny.zou@ejet-info.com>
 *
 */
class Tools extends Base 
{
	
	/**
     +----------------------------------------------------------
     * 架构函数
     * 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function __construct()
    {    
    	
    }	
    
    /**
     * 获取当前的时间
     *
     * @return float
     */
    public function getmicrotime() 
    {
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}
	
	/**
	 * 获取浮点数值
	 *
	 * @param float $num
	 * @param int $count
	 * @return float
	 */
	public function floatValue($num,$count) 
	{
	
		$pos = strpos($num,".") + 1 + $count;

		$floatNum = substr($num,0,$pos);
		$checkDigit = substr($num,strlen($floatNum),1);

		$changeDigit = substr($floatNum,strlen($floatNum)-1,1);

		//round up if necessary
		if ($checkDigit>=5) $last = $changeDigit + 1;
		else $last = $changeDigit;

		$value = substr($floatNum,0,strlen($floatNum)-1).$last;

		return $value;

	}
	
	/**
	 * shrinks an array down by removing all null values in the array
	 *
	 * @param Array $arr
	 * @return array
	 */
	public function reduceArray($arr) 
	{

		$newArr = array();
		$num = count($arr);

		for ($row=0;$row<$num;$row++) if ($arr[$row]!=NULL) $newArr[] = $arr[$row];

		return $newArr;

	}
	
	/**
	 * character encoding conversion.  uses iconv if available.  
	 * If not, returns the string unaltered
	 *
	 * @param String $string
	 * @param String $in
	 * @param String $out
	 * @return String
	 */
	public function charConv($string,$in,$out) 
	{
		$str = null;

		//return if unavailable
		if (!Session::is_set("ICONV_SUPPORT")) return $string;

		//return if the encoding strings are the same
		if (strcasecmp($in,$out) == 0) return $string;

		//sanity checking
		if (!$in || !$out) return $string;

		//use the iconv function
		if (Tools::checkAppAvail("iconv")) {

			$file = TEMP_PATH .rand().".txt";

			//write the text to a temp file and pass it to iconv
			$fp = fopen($file,"w");
			fwrite($fp,$string);
			fclose($fp);

			$str = `iconv -c -f "$in" -t "$out" "$file"`;

			//remove our temp file
			unlink($file);

		} else {

			//this tells php to ignore characters it doesn't know
			$out .= "//IGNORE";

			$str = iconv($in,$out,$string);

		}

		return $str;
	}
	
	/**
     * 获取存储路径
     *
     * @param String $filepath
     * @param int $fileid
     * @return String
     */
    public function getStoragePath($filepath,$fileid)
    {
    	$filedir = ceil($fileid / 10000);
    	$filedir = $filepath."/".$filedir;

    	//get rid of the trailing "/" if it exists
    	$check = substr($filedir,strlen($filedir)-1,1);
    	if ($check=="/") $filedir = substr($filedir,0,strlen($filedir)-1);

    	//turn the string into an array
    	$pathArray = explode("/",$filedir);
    	$string = null;

    	//loop thru our array.  If any directory does not exist, create it
    	for ($row=0;$row<count($pathArray);$row++)
    	{

    		$string .= $pathArray[$row]."/";

    		if (!file_exists($string)) {

    			if (mkdir($string)) 
    				chmod($string,"0493");
    			else 
    				return false;

    		}

    	}

    	return $filedir."/".$fileid.".docmgr";

    }
    
     /**
     * parses a generic xml string into an associative 
     *	array.  It does not handle multiple entries
     * 	of a tag within an element.
     * @param String $obj
     * @param String $data
     * @return Array
     */
    public function parseGenericXml($obj,$data) {

    	$list = array();

    	$xml = simplexml_load_string($data);

    	$i = 0;

    	foreach ($xml -> $obj AS $arr) {

    		$fields = null;

    		foreach ($arr -> children() AS $field => $val) {

    			if ($field == "count") continue;

    			$list[$field][$i] = (string)$val;

    		}

    		$i++;

    	}

    	return $list;

    }
    
    /**
     * 检查外部插件是否可用
     *
     * @param String $app
     * @return Boolean
     */
    public function checkAppAvail($app) {

    	$str = `which "$app" 2>/dev/null`;

    	//if which returns nothing, it couldn't find the app
    	if (!$str) return false;

    	$pos = strrpos($str,"/");
    	$str = trim(substr($str,0,$pos));

    	//make sure the app's path is in apache's path
    	$pathArr = explode(":",$_SERVER["PATH"]);

    	if (in_array($str,$pathArr)) return true;
    	else return false;

	}
	
	/**
	 * 检查外部应用是否可用
	 *
	 * @param String $app
	 */
	public function checkRequiredApp($app) 
	{
      
		$str = `which "$app" 2>/dev/null`;
		$error = null;

		//if which returns nothing, it couldn't find the app
		if (!$str) $error = "1";
		else {
			$pos = strrpos($str,"/");
			$str = trim(substr($str,0,$pos));

			//make sure the app's path is in apache's path
			$pathArr = explode(":",$_SERVER["PATH"]);

			if (!in_array($str,$pathArr)) $error = "1";;
		}

		if ($error) {
			$message = "Error!  The application $app could not be found in \n".$_SERVER["PATH"].
	              	   "\nThis application is required by Ejet-Document to run.\n";
			throw new Exception($message,EXTERNAL_APP_ERROR);
		}
	}
	
	/**
	 * 病毒扫描
	 * 扫描一个文件是否有病毒，没有发祥病毒返回"clean" ，如果存在病毒则放回病毒的名称，扫描错误返回false
	 *
	 * @param String $filepath
	 * @return String
	 */
	public function clamAvScan($filepath)
	{
		if (!Session::is_set("CLAMAV_SUPPORT")) return false;

		$str = `clamscan --infected "$filepath"`;

		//return false if there is a scanning error
		if (strstr($str,"Scanned files: 0")) return false;

		//if no infected files are found, return true;
		if (strstr($str,"Infected files: 0")) 
		{
			return "clean";
		} else {

			//viruses were found, display the found virus information
			$pos = strpos($str,"----------- SCAN SUMMARY -----------");
			$vf = trim(substr($str,0,$pos));

			$pos = strpos($vf,":") + 1;
			$vf = "Virus Warning! ".substr($vf,$pos);

			return $vf;

		}
	}
	
	/**
	 * 转换utf-8到gb18030
	 * 用于中文分词，因为前台是flex，flex用的是utf8的字符集
	 *
	 * @param String $string
	 * @return String
	 */
	public function convert2gb($string)
	{
		//$type = strtoupper('STORE_CHARSET');
		$str =iconv("UTF-8","GB18030",$string);
		return $str;
	}
	
	/**
	 * 转换GB18030到UTF-8
	 * 用户中文分词后转换字符集插入数据库
	 *
	 * @param String $string
	 * @return String
	 */
	public function convert2utf8($string)
	{
		$type = strtoupper(STORE_CHARSET);
		if ($type == "GB2312" || $type == "GB18030")
    		return $string;
		$str =iconv("GB18030",STORE_CHARSET,$string);
		return $str;
	}
	
	/**
	 * 转换数据库字符集为用户端的字符集
	 *
	 * @param String $string
	 * @return String
	 */
	public function convertCharset($string)
	{
		$type = strtoupper(LANG_CHARSET);
		$str =iconv(STORE_CHARSET,$type,$string);
		
		return $str;
	}
	/**
	 * 获取浏览的目录路径，parentid为当前的目录
	 *
	 * @param int $parentid
	 * @return String
	 */
	public function getBrowsePath($parentid)
	{
		$cacheName = 'object_path_array';
		$infoArr = Tools::getCache($cacheName);
		if (0 == $parentid)
		{
			return "";
		}
		if (false === $infoArr)
		{
			$infoDao = new View_collectionsDao();	
			$infoVoList = $infoDao->findAll('','','id,name,object_type,parentid');
			if ($infoVoList->isEmpty()) return false;
			$arr = $infoVoList->toArray();
			foreach ($arr AS $keymaster => $value) 
			{
				foreach($value AS $key => $element) 
					$infoArr[$key][$keymaster] = $element;

			}
			Tools::setCache($cacheName,$infoArr);
			
		}
		
		$string = "";
		$ownerArray = array_reverse(Tools::getCatOwner($infoArr,$parentid,null));
		if (count($ownerArray) < 1) return $string;
		
		for ($row=0;$row<count($ownerArray);$row++) 
		{
			$key = array_search($ownerArray[$row],$infoArr["id"]);
			//show links for collections
			//$string .= $infoArr["id"][$key];
			$string .= $infoArr["name"][$key];
			if (($row+1) < count($ownerArray)) $string .= " --> ";
		}
		return $string;		
	}
	
	/**
	 * 设置目录浏览路径
	 *
	 * @return Boolean
	 */
	public function setBrowsePath()
	{
		$cacheName = 'object_path_array';
		$infoArr = array();
		$infoDao = new View_collectionsDao();	
		$infoVoList = $infoDao->findAll('','','id,name,object_type,parentid');
		if ($infoVoList->isEmpty()) return false;
		$arr = $infoVoList->toArray();
		$infoArr=null;
		foreach ($arr AS $keymaster => $value) 
		{
			foreach($value AS $key => $element) 
				$infoArr[$key][$keymaster] = $element;
		}
		if(PROG_DATA_CACHE)
		{
			$cache = Cache::getInstance();
			//全部数据缓存
			$cache->rm($cacheName);
			return $cache->set($cacheName,$infoArr);
		}
		//Tools::setCache($cacheName,$infoArr);
		return false;
	}
	/**
	 * 获取对象的路径
	 *
	 * @param int $objid
	 * @return String
	 */
	public function getObjectPath($objid)
	{
		$objDao = new View_objectsDao();
		$parentid = $objDao->getOne('parentid',"id={$objid}");
		if (false === $parentid)
			return false;
		if ($parentid == 0)	return "";
		$cacheName = 'object_path_array';
		$infoArr = Tools::getCache($cacheName);
		if (false === $infoArr)
		{
			$infoDao = new View_collectionsDao();	
			$infoVoList = $infoDao->findAll('','','id,name,object_type,parentid');
			if ($infoVoList->isEmpty()) return false;
			$arr = $infoVoList->toArray();
			foreach ($arr AS $keymaster => $value) 
			{
				foreach($value AS $key => $element) 
					$infoArr[$key][$keymaster] = $element;

			}
			Tools::setCache($cacheName,$infoArr);
			
		}
		
		$string = "";
		$ownerArray = array_reverse(Tools::getCatOwner($infoArr,$parentid,null));
		if (count($ownerArray) < 1) return $string;
		
		for ($row=0;$row<count($ownerArray);$row++) 
		{
			$key = array_search($ownerArray[$row],$infoArr["id"]);
			//show links for collections
			//$string .= $infoArr["id"][$key];
			$string .= $infoArr["name"][$key];
			if (($row+1) < count($ownerArray)) $string .= " --> ";
		}
		return $string;		
	}
	
	/**
	 * 获取对象最上层的类型
	 *
	 * @param int $objid
	 * @return int
	 */
	public function getObjectTopType($objid)
	{
		$objDao = new View_objectsDao();
		$parentid = $objDao->getOne('parentid',"id={$objid}");
		if (false === $parentid)
			return false;
		if ($parentid == 0)	return false;
		$cacheName = 'object_path_array';
		$infoArr = Tools::getCache($cacheName);
		if (false === $infoArr)
		{
			$infoDao = new View_collectionsDao();	
			$infoVoList = $infoDao->findAll('','','id,name,object_type,parentid');
			if ($infoVoList->isEmpty()) return false;
			$arr = $infoVoList->toArray();
			foreach ($arr AS $keymaster => $value) 
			{
				foreach($value AS $key => $element) 
					$infoArr[$key][$keymaster] = $element;

			}
			self::setCache($cacheName,$infoArr);
			
		}
		$ownerArray = array_reverse(Tools::getCatOwner($infoArr,$parentid,null));
		if (count($ownerArray) < 1) return false;
		$key = array_search($ownerArray[0],$infoArr["id"]);
		if (false === $key) return false;
		return $infoArr["object_type"][$key];
	}
	/**
	 * 第归调用获取路径
	 *
	 * @param Array $infoArr
	 * @param int $id
	 * @param Array $passArray
	 * @return Array
	 */
	public function getCatOwner($infoArr,$id,$passArray)
	{
		
		if (!$passArray) $passArray[] = $id;
		
		$key = array_search($id,$infoArr["id"]);
		$owner = $infoArr["parentid"][$key];
		
		if ($owner!=0 && $owner != $id) 
		{
			$passArray[] = $owner;
			$passArray = Tools::getCatOwner($infoArr,$owner,$passArray);
		}
		
		return $passArray;
	}
	
	/**
	 * 将数据存放到缓存
	 *
	 * @param String $cacheName
	 * @param object $data
	 * @return Boolean
	 */
	public function setCache($cacheName, $data)
	{
		if (!$cacheName)
			return false;
		//如果启用数据缓存则重新缓存
		if(PROG_DATA_CACHE)
		{
			$cache = Cache::getInstance();
			//全部数据缓存
			return $cache->set($cacheName,$data);
		}
		return false;
	}
	
	/**
	 * 在缓存中获取数据
	 *
	 * @param String $cacheName
	 * @return object
	 */
	public function getCache($cacheName)
	{
		if (!$cacheName)
			return false;
		if(PROG_DATA_CACHE)
		{//启用数据动态缓存
			//取得共享内存实例
			$cache = Cache::getInstance();
			//获取共享内存数据
			return $cache->get($cacheName);
		}
		return false;
	}
	
	/**
	 * 根据对象编号授权给群组
	 *
	 * @param int $objid
	 * @param Array $groups
	 * @return Boolean
	 */
	public function grantGroups($objid,$groups)
	{
		import("@.Dao.Object_grant");
		import("@.File.FileLog");
		$objGrantDao = new Object_grantDao();
		$map = new HashMap();
		if (!$objid || !is_array($groups))
			return false;
		
		foreach ($groups as $group)
		{
			if (!is_array($group))
				continue;
			$groupid = $group['id'];
			$bitset = $group['bitset'];
			$map->put('objectid',$objid);
			$map->put('groupid',$groupid);
			$map->put('bitset',$bitset);
			$objGrantDao->deleteAll("objectid = {$objid} and groupid = {$groupid}");
			$objGrantDao->add($map);
		}
		$userid = Session::get(USER_AUTH_KEY);
		FileLog::logEvent('OBJ_PERM_UPDATE',$objid,null,$userid);
		return true;
	}
	
	/**
	 * 根据对象编号授权给角色
	 *
	 * @param int $objid
	 * @param Array $roles
	 * @return Boolean
	 */
	public function grantRoles($objid,$roles)
	{
		import("@.Dao.Object_grant");
		import("@.File.FileLog");
		$objGrantDao = new Object_grantDao();
		$map = new HashMap();
		if (!$objid || !is_array($roles))
			return false;
		foreach ($roles as $role)
		{
			if (!is_array($role))
				continue;
			$roleid = $role['id'];
			$bitset = $role['bitset'];
			$map->put('objectid',$objid);
			$map->put('roleid',$roleid);
			$map->put('bitset',$bitset);
			$objGrantDao->deleteAll("objectid = {$objid} and roleid = {$roleid}");
			$objGrantDao->add($map);
		}
		$userid = Session::get(USER_AUTH_KEY);
		FileLog::logEvent('OBJ_PERM_UPDATE',$objid,null,$userid);
		return true;
	}
	
	/**
	 * 根据对象编号授权给用户
	 *
	 * @param int $objid
	 * @param Array $users
	 * @return Boolean
	 */
	public function grantUsers($objid,$users)
	{
		import("@.Dao.Object_grant");
		import("@.File.FileLog");
		$objGrantDao = new Object_grantDao();
		$map = new HashMap();
		if (!$objid || !is_array($users))
			return false;
		foreach ($users as $user)
		{
			if (!is_array($user))
				continue;
			$userid = $user['id'];
			$bitset = $user['bitset'];
			$map->put('objectid',$objid);
			$map->put('userid',$userid);
			$map->put('bitset',$bitset);
			$objGrantDao->deleteAll("objectid = {$objid} and userid = {$userid}");
			$objGrantDao->add($map);
		}
		$userid = Session::get(USER_AUTH_KEY);
		FileLog::logEvent('OBJ_PERM_UPDATE',$objid,null,$userid);
		return true;
	}
	
	/**
	 * 删除授权用户
	 *
	 * @param int $objid
	 * @return Boolean
	 */
	public function clearGrant($objid)
	{
		if (!$objid) return false;
		import("@.Dao.Object_grant");
		import("@.File.FileLog");
		$objGrantDao = new Object_grantDao();
		$result = $objGrantDao->deleteAll("objectid={$objid}");
		if (false === $result) return false;
		$userid = Session::get(USER_AUTH_KEY);
		FileLog::logEvent('OBJ_PERM_UPDATE',$objid,null,$userid);
		return true;
	}
	/**
	 * 根据编号下载文件
	 *
	 * @param int $objid
	 * @return Boolean
	 */
	public function downloadObject($objid,$ver=null)
	{
		if (!$objid)
		{
			echo "No object to download";
			return false;
		}	
		import("@.Browse.Object");
		import("@.File.FileLog");
		$info = Object::isUserAuthorized($objid,"view");
		$userid = Session::get(USER_AUTH_KEY);
		if (!$info)
		{
			echo "Permission denied!";
			return false;
		}	
		
		if ($info['object_type'] != 2)	
		{
			echo "Can not download this type!";
			return false;
		}
		
		$fileName = $info['name'];
		if ($ver)
			$version = $ver;
		else 	
			$version = $info['version'];
		
		$filehDao = new File_historyDao();
		$vo = $filehDao->find("objectid={$objid} and version={$version}",null,"id,md5sum");
		if (!$vo)
		{
			echo "Can not download this file";
			return false;
		}
		
		$filePath = Tools::getStoragePath(DATA_DIR,$vo->id);
		if (md5_file($filePath) != $vo->md5sum)
		{
			echo "Can not download this file";
			FileLog::logEvent('OBJ_CHECKSUM_VERIFY_FAIL',$objid,null,$userid);
			return false;
		}
		
		if (Session::is_set('CLAMAV_SUPPORT'))
		{
			$r = Tools::clamAvScan(stripslashes($filePath));
			if ($r===FALSE) 
			{
				FileLog::logEvent('OBJ_VIRUS_ERROR',$objid,null,$userid);
			}elseif ($r=="clean"){ 
				FileLog::logEvent('OBJ_VIRUS_PASS',$objid,null,$userid);
			}else {
				FileLog::logEvent('OBJ_VIRUS_FAIL',$objid,null,$userid);
				return false;
			}
		}
		
		
		import("@.File.File");
		$f = new File($fileName);
		$type = $f->get('fileType');
		$fileSize = @filesize($filePath);
		header("Content-Length: {$fileSize}");
		header("Content-Type: {$type}");
		header("Content-Disposition: attachment; filename=\"{$fileName}\"");
		
		$chunksize = 1*(1024*1024); // how many bytes per chunk (this is 1 mb)
		$buffer = null;

		if (!$handle = fopen($filePath, 'rb')) return false;
	
		while (!feof($handle)) 
		{
			$buffer = fread($handle, $chunksize);
			print $buffer;
		}
		@fclose($handle);
		$data = null;
		if ($ver)
			$data = "Version {$ver}";
		
		FileLog::logEvent('OBJ_VIEWED',$objid,$data,$userid);
		return true;
	}
	
	public function downloadZipFile($fileName)
	{
		if (!$fileName) return ;
		$userid = Session::get(USER_AUTH_KEY);
		if (!$userid) return ;
		$dir = TEMP_PATH."{$userid}";
		$filePath = "{$dir}/{$fileName}";
		if (!file_exists("$filePath")) return ;
		$fileSize = @filesize($filePath);
		
		header("Content-Length: {$fileSize}");
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"{$fileName}\"");
		
		$chunksize = 1*(1024*1024); // how many bytes per chunk (this is 1 mb)
		$buffer = null;

		if (!$handle = fopen($filePath, 'rb')) return false;
	
		while (!feof($handle)) 
		{
			$buffer = fread($handle, $chunksize);
			print $buffer;
		}
		`rm -rf "$dir"`;
		return fclose($handle);
	}
	/**
	 * 将需要的文件复制到压缩目录下
	 *
	 * @param object $info
	 * @param String $dir
	 * @return Boolean
	 */
	public function zipProcessFile($info,$dir)
	{
		//import("@.Browse.Object");
		import("@.File.FileLog");
		//$info = Object::isUserAuthorized($objid,"view");
		
		if (!$info || $info['object_type'] != 2)
		{
			return false;
		}
		$objid = $info['id'];
		$userid = $info['userid'];
		$fileName = $info['name'];
		$version = $info['version'];
		
		$filehDao = new File_historyDao();
		$vo = $filehDao->find("objectid={$objid} and version={$version}",null,"id,md5sum");
		if (!$vo)
		{
			return false;
		}
		
		$filePath = Tools::getStoragePath(DATA_DIR,$vo->id);
		if (md5_file($filePath) != $vo->md5sum)
		{
			FileLog::logEvent('OBJ_CHECKSUM_VERIFY_FAIL',$objid,null,$userid);
			return false;
		}
		
		if (Session::is_set('CLAMAV_SUPPORT'))
		{
			$r = Tools::clamAvScan(stripslashes($filePath));
			if ($r===FALSE) 
			{
				FileLog::logEvent('OBJ_VIRUS_ERROR',$objid,null,$userid);
			}elseif ($r=="clean"){ 
				FileLog::logEvent('OBJ_VIRUS_PASS',$objid,null,$userid);
			}else {
				FileLog::logEvent('OBJ_VIRUS_FAIL',$objid,null,$userid);
				return false;
			}
		}
		$fileName = Tools::convertCharset($fileName);
		$destFileName = "{$dir}/{$fileName}";
		
		@copy($filePath,$destFileName);
		FileLog::logEvent('OBJ_VIEWED',$objid,null,$userid);
		return true;
	}
	
	/**
	 * 创建压缩目录，根据对象类型第归调用创建压缩目录或复制需要的文件到压缩目录下
	 *
	 * @param Object $objinfo
	 * @param String $dir
	 * @return Boolean or String
	 */
	public function zipProcessCol($objinfo,$dir)
	{
		if (!$objinfo) return false;
		
		$objs = Tools::getViewObjects($objinfo['id']);
		if (false === $objs) return false;
		$objName = Tools::convertCharset($objinfo['name']);
		$destDir = "{$dir}/{$objName}"; 
			
		if (is_dir("$destDir")) `rm -r "$destDir"`;
		@mkdir("$destDir");
		
		foreach ($objs as $obj)
		{
			if ($obj['object_type'] == 1 || $obj['object_type'] == 4)
				Tools::zipProcessCol($obj,$destDir);
			elseif ($obj['object_type'] == 2)
				Tools::zipProcessFile($obj,$destDir);
		}
		return $destDir;
	}
	
	/**
	 * 根据目录编号生成压缩文件
	 *
	 * @param int $colId
	 * @return Boolean
	 * @return String
	 */
	public function zipCollection($colId) 
	{
		if (!Session::is_set('ZIP_SUPPORT')) return false;
		
		//get out if no collection id was passed
		if (!$colId) return false;
		import("@.Browse.Object");
		$info = Object::isUserAuthorized($colId,"view");
		if (!$info || ($info['object_type'] == 2 || $info['object_type'] == 3 )) return false;
		$userid = $info['userid'];
		//our temp directory for the user
		$dir = TEMP_PATH."{$userid}";

		//create the temp directory. otherwise empty any previous contents in that dir
		if (is_dir("$dir")) `rm -r "$dir"`;
		@mkdir("$dir");
		//create a folder which is a mirror of our collection
		$arcdir = Tools::zipProcessCol($info,$dir);

		if (is_dir("$arcdir")) {

			$arr = explode("/",$arcdir);
			$arcsrc = array_pop($arr);
			
			//zip up our file
			$arc = $info["name"].".zip";
			
			//create our archive
			`cd "$dir"; zip -r -q "$arc" "$arcsrc"`;

			//return the path of the archive with zip on the end
			//return $dir."/".$arc;
			return $info["name"].".zip";

		} else return false;

	}

	/**
	 * 获取查看目录的成员
	 *
	 * @param int $parentid
	 * @return Array
	 * @return Boolean
	 */
	public function getViewObjects($parentid)
	{
		if (!Session::is_set(USER_AUTH_KEY)) return false;
		$listDao = null;
    	$userid = Session::get(USER_AUTH_KEY);
    	$whereStr = "";
    	if (Session::is_setLocal('administrator'))
    	{
    		$listDao = new View_objectsDao();
    		$whereStr ="parentid = {$parentid}";
    		
    	}else {
    		$groups = Session::get('_USER_GROUPS');
    		if ($groups)
    			$groupStr = implode(',',$groups);
    		else 
    			$groupStr = 0;	
    		
    		$roles = Session::get('_USER_ROLES');
    		if ($roles)
    			$roleStr = implode(',',$roles);
    		else 
    			$roleStr = 0;
    		$listDao = new View_objects_permDao();
    		$whereStr = "( (object_owner={$userid} or other_bitset >=1 or (owner_group in({$groupStr}) and group_bitset >= 1)) or ";
    		$whereStr .= " ((userid = {$userid} or roleid in({$roleStr}) or groupid in({$groupStr})) and bitset >=1) ) and parentid = {$parentid}";
       	}
    	  	
    	$vol = $listDao->findAll($whereStr,'','*','object_type,status_date');
    	if (!$vol->isEmpty())
    	{
    		$result = $vol->toResultSet();
    		return $result;
    	}else {
    		return false;
    	}
	}
	
	/**
	 * 获取中文分词
	 *
	 * @param String $method
	 * @param String $context
	 * @return String
	 */
	public function getWord($method,$context)
	{
		$ictDataPath = APP_ROOT;
		$str = "";
		if ("echo" == $method)
			$str = `cd "$ictDataPath"; echo "$context" | ictclas`;
		elseif ("cat" == $method)
			$str = `cd "$ictDataPath"; cat "$context" | ictclas`;
		return $str;		
	}
}

?>