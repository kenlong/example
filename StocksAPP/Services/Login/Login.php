<?php
class Login {
	public function Login(){
		$this->methodTable = array(
			"loginCheck"		=>	array("access"=>"remote")
		);
	}

	/**
	 * 用户登录校验
	 *
	 */
	public function loginCheck($user){
		$dao = new LoginDao();
		$loginName = $user["loginName"] ;
		$passWord = $user["passWord"] ;
		$result = $dao->find("loginName = '$loginName' and passWord = '$passWord'","users") ;
		if(!$result)
		{
			throw new Exception("用户名/密码不正确!");
		}

		return $result ;
	}



}
?>