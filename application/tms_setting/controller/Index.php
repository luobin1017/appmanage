<?php
namespace app\tms_setting\controller;
use think\Controller;
use think\Db;

class Index extends Controller
{
    public function index()
    {
		checkpermissions('0001',true);
		$list = db('bts_setting')->select();
		$this->assign('v',$list[0]);
		return $this->fetch();
    }
	
	public function update()
    {
		checkpermissions('0001',true);
		$data = $_POST;
		$result = db('bts_setting')->where('id',1)->update($data);
		$this->success('数据更新成功！','index');
    }
	
	//切换用户界面
	public function switchuser()
    {
		checkpermissions('0001',true);
		//用户列表
		$userlist=db('bts_userlist')->order('nouse asc,username asc')->select();
		$userarray=fieldtoarray($userlist,'username','');
		$this->assign('userarray', $userarray);
		
		$oldusername=cookie('oldusername');
		$this->assign('oldusername',$oldusername);
		return $this->fetch();
    }
	
	//切换到指定用户
	public function switchuser_ok()
	{
		checkpermissions('0001',true);
		$username=input('username');
		$user=db('bts_userlist')->where('username',$username)->select();
		if($user)
		{
			$olduser=cookie("username");
			$oldcode=cookie("code");
			cookie('username',$username);
			cookie('code',$user[0]["logincode"]);
			cookie('oldusername',$olduser);
			cookie('oldcode',$oldcode);
			$this->success('切换成功！','switchuser');
		}
		$this->error('切换失败！');
	}
	
	//切换到旧用户
	public function switchuser_old()
	{
		$olduser=cookie("oldusername");
		$oldcode=cookie("oldcode");
		$user=Db::name('bts_userlist')->where('username', $olduser)->where('logincode',$oldcode)->select();
		if($user)
		{
			$username=$user[0]['username'];
			$code=$user[0]['logincode'];
			cookie('username',$username);
			cookie('code',$code);
			cookie('oldusername',null);
			cookie('oldcode',null);
			$this->success('切换成功！','switchuser');
		}
		$this->error('切换失败！');
	}
}
