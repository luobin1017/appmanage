<?php
use think\Db;

include_once APP_PATH . 'include/siteinfo.php';
//判断用户是否登录到系统
if(!checklogin()){gologin();}
function checklogin()
{
	$sys_username=cookie("username");
	$sys_logincode=cookie("code");
	$sys_userjobs=cookie("jobs");
	if($sys_username!=""&&$sys_logincode!="")
	{
		$result=Db::name('cms_admin')->where('username', $sys_username)->where('logincode',$sys_logincode)->select();
		$num_rows=count($result);
		if($num_rows<=0)
		{
			return false;
		}
		else
		{
			Config('username',$result[0]['username']);
			Config('user_permissions',$result[0]['permissions']);
			Config('user_permissions_group',$result[0]['permissions_group']);
			return true;
		}
	}
	return false;
}
?>