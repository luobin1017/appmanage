<?php
namespace app\tms_setting\controller;
use think\Controller;
use think\Db;

class Userlist extends Controller
{
    public function index()
    {
		checkpermissions('0001',true);
		//checkpermissions('2101',true);
		historylog("浏览了用户列表！");
		$pagesize=20;
		$list = db('bts_userlist')->order("nouse asc,username asc")->paginate($pagesize, false, ['query' => request()->param(),]);
		$page = $list->render();
		//  页数量
		$this->assign('pagesize',$pagesize);
		//  总数据
		$this->assign('total',$list->total());
		//  总页数
		$total= ceil($list->total() / $pagesize);
		$this->assign('totalPage', $total);
		//  当前页
		$pagenow = input('page', 1);
		$this->assign('pagenow', $pagenow);

		$this->assign('list',$list);
		$this->assign('page', $page);
		return $this->fetch();
    }
	
	public function edit()
	{
		checkpermissions('0001',true);
		$id=input('id', 0);
		if($id>0)
		{
			$result = db('bts_userlist')->where('id',$id)->select();
			$list=$result[0];
		}
		else
		{
			$list=blanktablefield("bts_userlist");
		}
		$this->assign('v',$list);
		$deplist=explode(',',sysinfo('var_department'));
		$this->assign('deplist',$deplist);
		$joblist=explode(',',sysinfo('var_jobs'));
		$this->assign('joblist',$joblist);
		
		//权限组
		$pg = db('bts_setting_permissions_group')->select();
		$this->assign('permissions_group',$pg);
		
		//权限表
		$plist=explode(',',sysinfo('sys_permissions'));
		$this->assign('plist',$plist);
		
		$permissions = db('bts_setting_permissions')->order("orderid asc")->select();
		$this->assign('permissions',$permissions);
		
		return $this->fetch();
	}
	
	public function update()
    {
		checkpermissions('0001',true);
		$data = $_POST;
		$data['permissions_group']=checkboxtostring('permissions_group');
		$data['permissions']=checkboxtostring('permissions');
		if($data["password"]!="")
		{
			$data["password"]=md5($data['password']);
		}
		else
		{
			unset($data['password']);
		}
		if(input('id')>0)
		{
			$result = db('bts_userlist')->where('id',$data['id'])->update($data);
		}
		else
		{
			$result = db('bts_userlist')->insert($data);
		}
		$this->success('数据更新成功！','index');
    }
}
