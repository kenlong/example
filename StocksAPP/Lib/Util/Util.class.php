<?php

class Util extends Base 
{
	public function __construct()
	{
		
	}
	
	/**
	 * 获取用户订阅的消息
	 *
	 * @return Array
	 */
	public function getAlert()
	{
		$userid = Session::get(USER_AUTH_KEY);
		if (!$userid) return false;
		$alertDao = new View_alertDao();
		$vol = $alertDao->findAll("userid={$userid}",'','id,objectid,alert_type,status_time,alertid');
		$arr = array();
		if (!$vol)
			return false;
		else 	
			$arr = $vol->toResultSet();
		return $arr;	
	}
	
	/**
	 * 获取用户标记的文件夹
	 *
	 * @return Array
	 */
	public function getBookMarked()
	{
		$userid = Session::get(USER_AUTH_KEY);
		if (!$userid) return false;
		$bookDao = new BookmarkDao();
		$vol = $bookDao->findAll("userid={$userid}",'','id,name,objectid,bookmarked_time','bookmarked_time DESC');
		$arr = array();
		if (!$vol)
			return false;
		else 	
			$arr = $vol->toResultSet();
		return $arr;
	}
	
	/**
	 * 删除用户标记的文件夹
	 *
	 * @param int $bookId
	 * @return Boolean
	 */
	public function removeBookMarked($bookId)
	{
		$userid = Session::get(USER_AUTH_KEY);
		if (!$userid) return false;
		$bookDao = new BookmarkDao();
		$result = $bookDao->deleteById($bookId);
		return $result;
	}
	
	/**
	 * 获取最新增加的文件
	 *
	 * @return Array
	 */
	public function getRecently()
	{
		$userid = Session::get(USER_AUTH_KEY);
		if (!$userid) return false;
		$listDao = null;
		$whereStr = "";
    	if (Session::is_setLocal('administrator'))
    	{
    		$listDao = new View_objectsDao();
    		$whereStr ="object_type = 2";
    		
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
    		$whereStr .= " ((userid = {$userid} or roleid in({$roleStr}) or groupid in({$groupStr})) and bitset >=1) ) and object_type =2";
      	}	
      	
      	$vol = $listDao->findAllDistinct($whereStr,'','id,name,create_date,status','id DESC','0,10');
      	if ($vol->isEmpty()) return false;
      	$arr = $vol->toResultSet();
      	return $arr;
	}
	
	/**
	 * 获取用户订阅清单
	 *
	 * @return Array
	 */
	public function getSubscriptions()
	{
		$userid = Session::get(USER_AUTH_KEY);
		if (!$userid) return false;
		$subDao = new SubscribeDao();
		$vol = $subDao->findAll("userid={$userid}",'','id,objectid,email,type','objectid');
		$arr = array();
		if (!$vol)
			return false;
		else 	
			$arr = $vol->toResultSet();
		return $arr;
	}
	
	/**
	 * 获取用户签出的文件
	 *
	 * @return Array
	 */
	public function getCheckouted()
	{
		$userid = Session::get(USER_AUTH_KEY);
		if (!$userid) return false;
		$objDao = new View_objectsDao();
		$vol = $objDao->findAll("status_owner={$userid} and status=1",'','id,name,status,status_date,parentid','status_date DESC');
		$arr = array();
		if (!$vol)
			return false;
		else 	
			$arr = $vol->toResultSet();
		return $arr;
	}
	
	/**
	 * 获取订阅消息的详细信息
	 *
	 * @param int $alertId
	 * @return Object
	 */
	public function getAlertInfo($alertId)
	{
		if (!$alertId) return false;
		$userid = Session::get(USER_AUTH_KEY);
		if (!$userid) return false;
		$alertDao = new View_alertDao();
		$vo = $alertDao->find("alertid = {$alertId}");
		if (!$vo) return false;
		$userDao = new UserDao();
		import("@.Util.Tools");
		import("@.ServicesVo.file.AlertdVO");
		$alertVO = new AlertdVO();
		$alertVO->location = Tools::getObjectPath($vo->id);
		$alertVO->fileName = $vo->name;
		$alertVO->fileSize = $vo->filesize + 0;
		$alertVO->fileVersion = $vo->version;
		$alertVO->fileOwnerName = $userDao->getOne('name',"id=".$vo->object_owner);
		$alertVO->fileCreateDate = $vo->create_date;
		$alertVO->fileStateOwnerName = $userDao->getOne('name', "id=".$vo->status_owner);
		$alertVO->fileStateDate = $vo->status_date;
		$alertVO->fileStatus = $vo->status;
		$alertVO->alertType = $vo->alert_type;
		$alertVO->operatorName = $userDao->getOne('name', "id=".$vo->userid);
		$alertVO->operateDate = $vo->status_time;
		return $alertVO;
	}
	
	/**
	 * 删除用户订阅的消息提示
	 *
	 * @param int $alertId
	 * @return Boolean
	 */
	public function removeAlert($alertId)
	{
		if (!$alertId) return false;
		$userid = Session::get(USER_AUTH_KEY);
		if (!$userid) return false;
		$alertDao = new AlertDao();
		return ($alertDao->deleteById($alertId));
	}
	
	/**
	 * 获取用户的工作流实例
	 *
	 * @param Boolean $limit 
	 * @return Array
	 */
	public function getWorkflow($limit = null)
	{
		$userid = Session::get(USER_AUTH_KEY);
		if (!$userid) return false;
		if ($limit)
			$limitStr = "0,10";
		else 
			$limitStr = ""	;
		$caseDao = new WfcasesDao();
		$vol = $caseDao->findAll("created_userid={$userid}",'','*','start_date DESC',$limitStr);
		if ($vol->isEmpty()) return false;
		return $vol->toResultSet();
	}
	
	/**
	 * 获取不重复的工作流文件名
	 *
	 * @return String
	 */
	public function getUniqName()
	{
		$better_token = md5(uniqid(rand(), true));
		$name = $better_token . ".wf";
		return $name;
	}
	
	/**
	 * 获取用户任务列表
	 *
	 * @return Boolean | Array
	 */
	public function getOutstandingWorkitem()
	{
		$userid = Session::get(USER_AUTH_KEY);
		if (!$userid) return false;
		
		$roles = Session::get('_USER_ROLES');
		if ($roles)
			$roleStr = implode(',',$roles);
		else
			return false;
		
		$itemDao = new WfworkitemDao();
		$vol = $itemDao->findAll("role_id in({$roleStr}) and transition_trigger=0 and workitem_status=0");
		if ($vol->isEmpty()) return false;
		return $vol->toResultSet();
	}
}

?>