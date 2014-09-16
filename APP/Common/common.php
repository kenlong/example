<?php 
// +----------------------------------------------------------------------
// | ThinkPHP                                                             
// +----------------------------------------------------------------------
// | Copyright (c) 2008 http://thinkphp.cn All rights reserved.      
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>                                  
// +----------------------------------------------------------------------
// $Id$

function toDate($time,$format='Y-m-d H:i:s') 
{
	if( empty($time)) {
		return '';
	}
    $format = str_replace('#',':',$format);
	return date(auto_charset($format),$time);
}

function getStatus($status,$imageShow=true) 
{
   switch($status) {
    	case 0:
            $showText   = '未处理';
            $showImg    = '<IMG SRC="'.APP_PUBLIC_URL.'/Images/state0.bmp"  BORDER="0" ALT="未处理">';
            break;
        case 1:
        default:
            $showText   =   '已处理';
            $showImg    =   '<IMG SRC="'.APP_PUBLIC_URL.'/Images/state1.bmp"  BORDER="0" ALT="已处理">';
    }
    return ($imageShow===true)? auto_charset($showImg) : $showText;
}
?>