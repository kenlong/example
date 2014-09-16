<?php  
/*
// 定义ThinkPHP框架路径 
define('THINK_PATH', '../ThinkPHP'); 
//定义项目名称和路径 
define('APP_NAME', 'APP'); 
define('APP_PATH', '.'); 
*/
//定义项目名称和路径 
define('APP_NAME', 'APP'); 
define('APP_PATH', realpath(".")); 
// 定义ThinkPHP框架路径 
define('THINK_PATH', APP_PATH.'/../ThinkPHP'); 

// 加载框架入口文件  
require(THINK_PATH."/ThinkPHP.php"); 

//实例化一个网站应用实例 
$App = new App();  
//应用程序初始化 
$App->run(); 

?> 