<?php
namespace app\tms_business\controller;
use think\Controller;
use think\Db;
use app\tms_business\model\Source;
class Quotation extends Controller
{
	//报价单
    public function index()
    {
		checkpermissions('0901',true);
		//historylog("浏览了Quotation页面！");
		$pagesize = 20;
		$list = Db::table("bts_quotation")->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);
		$no = db('bts_setting_norule')->where('type','Quotation')->find();
		$tno = $no['ex_name'];
		$page = $list->render();
		//  页数量
		$this->assign('pagesize',$pagesize);
		//  总数据
		$this->assign('total',$list->total());
		//  总页数
		$total= ceil($list->total() / 15);
		$this->assign('totalPage', $total);
		//  当前页
		$pagenow =input('page', 1);
		$this->assign('pagenow', $pagenow);

		$this->assign('arr',$list);
		$this->assign('page', $page);
		$this->assign('tno', $tno);
		return $this->fetch();
    }
	//新增报价单
	public function quotation_add()
	{
		return $this->fetch();
		/*
		checkpermissions('0903',true);
		if($_POST){
			//dump($_POST);
			if($_POST['customid']!= '' || $_POST['customids']!= ''){
				$customid = $_POST['customid']==''?$_POST['customids']:$_POST['customid'];
				$rs = db('bts_custom')->where('customid',$customid)->field('customid,ename,address,contact1,phone1,email1')->find();
				$this->assign('customdata',json_encode($rs));
			}else{
				$this->assign('customdata',0);
			}
			if($_POST['productid']!= '' || $_POST['pppid']!= ''){
				$productid = $_POST['productid']==''?$_POST['pppid']:$_POST['productid'];
				$subsql = Db::table('bts_product')->field('ename,no,pdetion,picture1,classid')->where('no',$productid)->buildSql();
				$rs = db('bts_product_class')->alias('a')->Join([$subsql=>'b'],"b.classid=a.classid",'inner')->find();
				dump(DB::table('bts_product_class')->getLastSql());
				dump($rs);
				$this->assign('productdata',json_encode($rs));
			}else{
				$this->assign('productdata',0);
			}

		}else{
			$this->assign('customdata',0);
			$this->assign('productdata',0);
		}
		$md = new Source();
		$list[0][0]="";
		$list = $md->getsource(0,0,$list,0);
		$tno=newbasicno('Quotation');
		$no = db('bts_setting_norule')->where('type','Quotation')->find();
		$qno = $no['ex_name'];
		$this->assign('no',$tno);
		$this->assign('qno',$qno);
		$this->assign('source_arr',$list);
		return $this->fetch();
		*/
	}
	//新增报价单提交表单数据
	public function quotation_add_save()
	{
		dump($_POST);
	}
	//选择用户
	public function custom_select()
	{
		if($_POST){

		}
		$pagesize = 13;
		$subsql = Db::table('bts_national')->field('id,no,name as countryname')->buildSql();
		$list = Db::table("bts_custom")->alias('a')->Join([$subsql=>'b'],"b.no=a.nationalid",'inner')->order("a.no desc")->paginate($pagesize, false, ['query' => request()->param(),]);
		$page = $list->render();
		//  页数量
		$this->assign('pagesize',$pagesize);
		//  总数据
		$this->assign('total',$list->total());
		//  总页数
		$total= ceil($list->total() / 13);
		$this->assign('totalPage', $total);
		//  当前页
		$pagenow =input('page', 1);
		$this->assign('pagenow', $pagenow);
		$this->assign('arr',$list);
		$this->assign('page', $page);
		return $this->fetch();
	}
	//选择产品
	public function product_select()
	{
		if($_POST){

		}
		$pagesize = 6;
		$list = db('bts_product')->field('no,ename,picture1')->order('no desc')->paginate($pagesize, false, ['query' => request()->param(),]);
		$page = $list->render();
		//  页数量
		$this->assign('pagesize',$pagesize);
		//  总数据
		$this->assign('total',$list->total());
		//  总页数
		$total= ceil($list->total() / 6);
		$this->assign('totalPage', $total);
		//  当前页
		$pagenow =input('page', 1);
		$this->assign('pagenow', $pagenow);
		$this->assign('rs',$list);
		$this->assign('page', $page);
		return $this->fetch();
	}



}
