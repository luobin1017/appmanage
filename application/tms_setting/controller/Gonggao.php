<?php
namespace app\tms_setting\controller;
use think\Controller;
use think\Db;

class Gonggao extends Controller
{
    public function index()
    {
		//搜索条件
		$job=input("job","");
		$keyword=input("keyword","");
		$sqlwhere=' id>0 ';
		if($job!=''){ $sqlwhere .= ' and job = "'.$job.'" ';}
		if($keyword!=''){ $sqlwhere .= ' and uname like "%'.$keyword.'%"';}
		
		$pagesize=20;
		$list = db('bts_job')->where($sqlwhere)->order("id desc")->paginate($pagesize, false, ['query' => request()->param(),]);
		$page = $list->render();
		//  页数量
		$this->assign('pagesize',$pagesize);
		//  总数据
		$this->assign('total',$list->total());
		//  总页数
		$total= ceil($list->total() / 10);
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
		$id=input('id', 0);
		$list="";
		if($id>0)
		{
			$result = db('bts_job')->where('id',$id)->select();
			$list=$result[0];
		}
		else
		{
			$list=blanktablefield("bts_job");
		}
		$this->assign('v',$list);
		return $this->fetch();
	}
	
	public function update()
    {
		$data = $_POST;
		$data['inus']=Config('username');
		$data['indate']=date('Y-m-d H:i:s', time());;
		if(input('id')>0)
		{
			$result = db('bts_job')->where('id',$data['id'])->update($data);
		}
		else
		{
			$result = db('bts_job')->insert($data);
		}
		$this->success('数据更新成功！','index');
    }
}
