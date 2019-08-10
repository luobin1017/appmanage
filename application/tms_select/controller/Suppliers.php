<?php
namespace app\tms_select\controller;
use think\Controller;
use think\Db;

class Suppliers extends Controller
{
	//选择供应商
	public function index()
	{
		//搜索条件
		$sqlwhere=array("","");
		$customid=input("customid");
		$sqlwhere=sqlwhereand("供应商编号",$sqlwhere,"allno",$customid,1,1);
		$customname=input("customname");
		$sqlwhere=sqlwhereand("供应商名称",$sqlwhere,"name",$customname,1,1);
		
		$pagesize = 11;
		$list = Db::table("bts_suppliers")->where($sqlwhere[0])->order("id desc")->paginate($pagesize, false, ['query' => request()->param(),]);
		$page = $list->render();
		//  页数量
		$this->assign('pagesize',$pagesize);
		//  总数据
		$this->assign('total',$list->total());
		//  总页数
		$total= ceil($list->total() / $pagesize);
		$this->assign('totalPage', $total);
		//  当前页
		$pagenow =input('page', 1);
		$this->assign('pagenow', $pagenow);
		$this->assign('arr',$list);
		$this->assign('page', $page);
		return $this->fetch();
	}
}
