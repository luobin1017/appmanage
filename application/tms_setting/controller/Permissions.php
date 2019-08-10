<?php
namespace app\tms_setting\controller;
use think\Controller;
use think\Db;

class Permissions extends Controller
{
    public function index()
    {
		checkpermissions('0001',true);
		$plist=explode(',',sysinfo('sys_permissions'));
		$this->assign('plist',$plist);
		
		$list = db('bts_setting_permissions')->order("orderid asc")->select();
		$this->assign('list',$list);
		return $this->fetch();
    }
	
	public function edit()
	{
		checkpermissions('0001',true);
		$plist=explode(',',sysinfo('sys_permissions'));
		//mydump($plist);
		$this->assign('plist',$plist);
		
		$id=input('id', 0);
		$list="";
		if($id>0)
		{
			$result = db('bts_setting_permissions')->where('id',$id)->select();
			$list=$result[0];
		}
		else
		{
			$list=blanktablefield("bts_userlist");
		}
		$this->assign('v',$list);
		return $this->fetch();
	}
	
	public function update()
    {
		checkpermissions('0001',true);
		$data = $_POST;
		$data['permissions']=checkboxtostring('permissions');
		if(input('id')>0)
		{
			$result = db('bts_setting_permissions')->where('id',$data['id'])->update($data);
		}
		else
		{
			$result = db('bts_setting_permissions')->order('orderid desc')->limit(1)->select();
			if($result){ $orderid=9999;}
			else{ $orderid=$result['orderid']+1;}
			$data['orderid']=$orderid;
			$result = db('bts_setting_permissions')->insert($data);
			$this->updatesort();
		}
		$this->success('数据更新成功！','index');
    }
	
	public function del()
	{
		checkpermissions('0001',true);
		if(input('id')>0)
		{
			$result = db('bts_setting_permissions')->where('id',input('id'))->delete();
		}
		updatetree("bts_menu");
		$this->success('数据更新成功！','index');
	}
	
	public function sort()
	{
		checkpermissions('0001',true);
		$idlist=input('id',0);
		$list=explode(",",$idlist);
		for($i=0;$i<count($list);$i++)
		{
			$id=$list[$i];
			$sql="update bts_setting_permissions set orderid=".$i." where id=".$id;
			db()->query($sql);
		}
		$this->updatesort();
		$this->success('数据更新成功！','index');
	}
	
	public function updatesort()
	{
		checkpermissions('0001',true);
		$list = db('bts_setting_permissions')->order("orderid asc")->select();
		for($i=0;$i<count($list);$i++)
		{
			$item=$list[$i];
			$sql="update bts_setting_permissions set orderid=".$i." where id=".$item['id'];
			db()->query($sql);
		}
	}
	
	public function group()
	{
		checkpermissions('0001',true);
		$list = db('bts_setting_permissions_group')->order("id asc")->select();
		$this->assign('list',$list);
		return $this->fetch();
	}
	
	public function group_edit()
	{
		checkpermissions('0001',true);
		$plist=explode(',',sysinfo('sys_permissions'));
		$this->assign('plist',$plist);
		
		$permissions = db('bts_setting_permissions')->order("orderid asc")->select();
		$this->assign('permissions',$permissions);
		
		$id=input('id', 0);
		$list="";
		if($id>0)
		{
			$result = db('bts_setting_permissions_group')->where('id',$id)->select();
			$list=$result[0];
		}
		else
		{
			$list=blanktablefield("bts_userlist");
		}
		$this->assign('v',$list);
		return $this->fetch();
	}
	
	public function group_update()
	{
		checkpermissions('0001',true);
		$data = $_POST;
		$data['permissions']=checkboxtostring('permissions');
		if(input('id')>0)
		{
			$result = db('bts_setting_permissions_group')->where('id',$data['id'])->update($data);
		}
		else
		{
			$result = db('bts_setting_permissions_group')->insert($data);
		}
		$this->success('数据更新成功！','group');
	}
}
