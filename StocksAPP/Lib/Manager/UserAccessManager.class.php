<?php
import("APP.Software.AccessManager");
import("APP.Permission.UserPermissions");
class UserAccessManager extends AccessManager 
{
	/**
	 * 检查用户是否可以读取Swf模块
	 *
	 * @param string $modular ep:Email.inbox
	 * @return bool
	 */
	public function checkSwfPermission($modular)
	{
		if ($this->checkNotAuthSwf($modular) && !$this->checkRequireAuthSwf($modular))return true;
		if (!($uid = Session::get(USER_AUTH_KEY))) return false;
		if (Session::is_setLocal('administrator')) return true;
		if ("MDI.Desktop" == $modular) return true;
		$accessList = Session::get('_ACCESS_LIST');
		if (!$accessList)
		{			
			$accessList = UserPermissions::getGUIPermissions($uid);
		}
		if (array_key_exists($modular,$accessList)) return true;
		return false;
	}
	
	/**
	 * 检查用户是否可以读取PHP模块
	 *
	 * @param string $module ep:Email.inbox
	 * @return bool
	 */
	public function checkModulePermission($module)
	{
		//if (!defined('USER_ACCESS_MANAGER') || "" == USER_ACCESS_MANAGER) return true;
		if ($this->checkNotAuthModule($module) && !$this->checkRequireAuthModule($module)) return true;
		if (!($uid = Session::get(USER_AUTH_KEY))) return false;
		return true;
	}
}
?>