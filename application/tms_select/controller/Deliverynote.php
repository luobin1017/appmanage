<?php
namespace app\tms_select\controller;
use think\Controller;
use think\Db;

class Deliverynote extends Controller
{
	//选择产品
	public function index()
	{
		//搜索条件
		$sqlwhere=array("","");
		$no=input("no");
		$sqlwhere=sqlwhereand("编号",$sqlwhere,"no",$no,1,1);
		$customname=input("company");
		$sqlwhere=sqlwhereand("客户名称",$sqlwhere,"customname",$customname,1,1);
		
		$pagesize = 11;
		$list = db('bts_delivery_note')->where($sqlwhere[0])->order('id desc')->paginate($pagesize, false, ['query' => request()->param(),]);
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
}
