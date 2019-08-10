<?php
namespace app\tms_select\controller;
use think\Controller;
use think\Db;

class Custom extends Controller
{
	//选择用户
	public function index()
	{
		//搜索条件
		$sqlwhere=array("","");
		$customid=input("customid");
		$sqlwhere=sqlwhereand("客户编号",$sqlwhere,"allno",$customid,1,1);
		$customname=input("customname");
		$sqlwhere=sqlwhereand("客户名称",$sqlwhere,"cnname",$customname,1,1);
		
		$pagesize = 11;
		$subsql = Db::table('bts_national')->field('id,no,cnname as countryname')->buildSql();
		$list = Db::table("bts_custom")->alias('a')->Join([$subsql=>'b'],"b.no=a.nationalid",'inner')->where($sqlwhere[0])->order("a.no desc")->paginate($pagesize, false, ['query' => request()->param(),]);
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
