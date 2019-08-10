<?php
namespace app\tms_setting\controller;
use think\Controller;
use think\Db;

class Menu extends Controller
{
    public function index()
    {
		checkpermissions('0001',true);
		$list = db('bts_menu')->order("orderid asc")->select();
		$this->assign('list',$list);
		return $this->fetch();
    }
	
	public function edit()
	{
		checkpermissions('0001',true);
		//菜单列表
		$mylist[]="";
		$mylist=getpclassarray("bts_menu",0,"id","menuname,adsfds",$mylist,0);
		$this->assign('menuarray', $mylist);
		
		$id=input('id', 0);
		$list="";
		if($id>0)
		{
			$result = db('bts_menu')->where('id',$id)->select();
			$list=$result[0];
		}
		else
		{
			$list=blanktablefield("bts_menu");
		}
		$this->assign('v',$list);
		return $this->fetch();
	}
	
	public function update()
    {
		checkpermissions('0001',true);
		$data = $_POST;
		if(input('id')>0)
		{
			$result = db('bts_menu')->where('id',$data['id'])->update($data);
		}
		else
		{
			$data['orderid']=99999;
			$result = db('bts_menu')->insert($data);
		}
		updatetree("bts_menu");
		$this->success('数据更新成功！','index');
    }
	
	public function del()
	{
		checkpermissions('0001',true);
		if(input('id')>0)
		{
			$result = db('bts_menu')->where('parentid',input('id'))->select();
			if($result)
			{
				$this->success('该分类下面还有子分类未删除，请先把子分类删除，再删除该分类！','index');
			}
			$result = db('bts_menu')->where('id',input('id'))->delete();
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
			$sql="update bts_menu set orderid=".$i." where id=".$id;
			db()->query($sql);
		}
		updatetree("bts_menu");
		$this->success('数据更新成功！','index');
	}
}