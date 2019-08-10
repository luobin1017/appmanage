<?php
namespace app\tms_select\controller;
use think\Controller;
use think\Db;

class Product extends Controller
{
	//选择产品
	public function index()
	{
		//搜索条件
		$sqlwhere=array("","");
		$no=input("no");
		$sqlwhere=sqlwhereand("产品编号",$sqlwhere,"allno",$no,1,1);
		$name=input("name");
		$sqlwhere=sqlwhereand("产品名称",$sqlwhere,"enname,cnname",$name,1,1);
		$classid=input("classid");
		$sqlwhere=sqlwhereand("产品分类",$sqlwhere,"classid",$classid);
		
		$pagesize = 6;
		$list = db('bts_product')->field('allno,enname,picture')->where($sqlwhere[0])->order('allno desc')->paginate($pagesize, false, ['query' => request()->param(),]);
		//echo Db::table('table_name')->getLastSql();exit(0);
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
		$this->assign('rs',$list);
		$this->assign('page', $page);
		
		//产品分类列表
		$productclassrray[]="";
		$productclassrray=getpclassarray("bts_product_class",0,"id","cnclassname,enclassname",$productclassrray,0);
		$this->assign('productclassrray', $productclassrray);
		
		return $this->fetch();
	}
}
