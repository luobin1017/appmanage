<?php
namespace app\tms_setting\controller;
use think\Controller;
use think\Db;

class Kaoqin extends Controller
{
    public function index()
    {
		//用户列表
		$userlist=db('bts_userlist')->order('nouse asc,username asc')->select();
		$userarray=fieldtoarray($userlist,'username','');
		$this->assign('userarray', $userarray);
		
		//搜索条件
		//搜索条件
		$makedate1=input("makedate1","");
		$makedate2=input("makedate2","");
		$uname=input("username","");
		$keyword=input("keyword","");
		$queststring="";
		$sqlwhere=' id>0 ';
		if($uname!=''){ $sqlwhere .= ' and b.uname = "'.$uname.'" ';}
		if($makedate1!=''&&$makedate2!=''){ $sqlwhere .= ' and dwtime >= "'.$makedate1.' 0:0:0"  and dwtime <="'. $makedate2.' 23:59:59" ';}
		else if($makedate1!=''){ $sqlwhere .= ' and dwtime >= "'.$makedate1.' 0:0:0"  and dwtime <="'. $makedate1.' 23:59:59" ';}
		else if($makedate2!=''){ $sqlwhere .= ' and dwtime >= "'.$makedate2.' 0:0:0"  and dwtime <="'. $makedate2.' 23:59:59" ';}
		
		$pagesize=20;
		$idlist = db('bts_userlist')->field('max(id) uid')->group('checkid')->buildSql();
		$userlist = db('bts_userlist')->alias('u')->field('username uname,checkid,'.sysinfo('sys_kaoqinname'))->join([$idlist=>'idlist'],'idlist.uid=u.id')->buildSql();
		$list = db('bts_kaoqin')->alias('a')->join([$userlist=>'b'],'a.enroll=b.checkid','left')->where($sqlwhere)->order("dwtime desc")->paginate($pagesize, false, ['query' => request()->param(),]);
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
	
	public function day()
    {
		//用户列表
		$userlist=db('bts_userlist')->order('nouse asc,username asc')->select();
		$userarray=fieldtoarray($userlist,'username','');
		$this->assign('userarray', $userarray);
		
		//搜索条件
		//搜索条件
		$makedate1=input("makedate1","");
		$makedate2=input("makedate2","");
		$uname=input("username","");
		$keyword=input("keyword","");
		$queststring="";
		$sqlwhere=' id>0 ';
		if($uname!=''){ $sqlwhere .= ' and b.uname = "'.$uname.'" ';}
		if($makedate1!=''&&$makedate2!=''){ $sqlwhere .= ' and dwtime >= "'.$makedate1.' 0:0:0"  and dwtime <="'. $makedate2.' 23:59:59" ';}
		else if($makedate1!=''){ $sqlwhere .= ' and dwtime >= "'.$makedate1.' 0:0:0"  and dwtime <="'. $makedate1.' 23:59:59" ';}
		else if($makedate2!=''){ $sqlwhere .= ' and dwtime >= "'.$makedate2.' 0:0:0"  and dwtime <="'. $makedate2.' 23:59:59" ';}
		
		$pagesize=20;
		$idlist = Db::table('bts_userlist')->field('max(id) uid')->group('checkid')->buildSql();
		$userlist = Db::table('bts_userlist')->alias('u')->field('username uname,checkid,'.sysinfo('sys_kaoqinname'))->join([$idlist=>'idlist'],'idlist.uid=u.id')->buildSql();
		$list = Db::table('bts_kaoqin')->alias('a')->join([$userlist=>'b'],'a.enroll=b.checkid')->where($sqlwhere)->order("dwtime desc")->paginate($pagesize, false, ['query' => request()->param(),]);
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
}
