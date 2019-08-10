<?php
namespace app\tms_setting\controller;
use think\Controller;
use think\Db;

class History extends Controller
{
    public function index()
    {
		checkpermissions('0001',true);
		//用户列表
		$userlist=db('bts_userlist')->order('nouse asc,username asc')->select();
		$userarray=fieldtoarray($userlist,'username','');
		$this->assign('userarray', $userarray);
		
		//搜索条件
		$makedate1=input("makedate1","");
		$makedate2=input("makedate2","");
		$makename=input("makename","");
		$operation=input("operation","");
		$operation2=input("operation2","");
		$keyword=input("keyword","");
		$queststring="";
		if(!empty($_GET)||!empty($_POST))
		{
			$queststring='makedate1='.$makedate1.'&makedate2='.$makedate2.'&makename='.$makename.'&operation='.$operation.'&operation2='.$operation2.'&keyword='.$keyword;
		}
		$this->assign('queststring',$queststring);
		$sqlwhere=' id>0 ';
		if($makename!=''){ $sqlwhere .= ' and username = "'.$makename.'" ';}
		if($makedate1!=''&&$makedate2!=''){ $sqlwhere .= ' and time >= "'.$makedate1.' 0:0:0"  and time <="'. $makedate2.' 23:59:59" ';}
		else if($makedate1!=''){ $sqlwhere .= ' and time >= "'.$makedate1.' 0:0:0"  and time <="'. $makedate1.' 23:59:59" ';}
		else if($makedate2!=''){ $sqlwhere .= ' and time >= "'.$makedate2.' 0:0:0"  and time <="'. $makedate2.' 23:59:59" ';}
		if($operation!=''){ $sqlwhere .= ' and operation like "%'.$operation.'%"';}
		if($operation2!=''){ $sqlwhere .= ' and operation like "%'.$operation2.'%"';}
		if($keyword!=''){ $sqlwhere .= ' and operation like "%'.$keyword.'%"';}
		
		//查询以及分页
		$pagesize=20;
		$list = db('bts_history')->where($sqlwhere)->order("id desc")->paginate($pagesize, false, ['query' => request()->param(),]);
		$page = $list->render();
		$this->assign('pagesize',$pagesize);
		$this->assign('total',$list->total());
		$total= ceil($list->total() / 10);
		$this->assign('totalPage', $total);
		$pagenow = input('page', 1);
		$this->assign('pagenow', $pagenow);
		$this->assign('list',$list);
		$this->assign('page', $page);
		
		return $this->fetch();
    }
	
	public function del()
	{
		checkpermissions('0001',true);
		//搜索条件
		$makedate1=input("makedate1","");
		$makedate2=input("makedate2","");
		$makename=input("makename","");
		$operation=input("operation","");
		$operation2=input("operation2","");
		$keyword=input("keyword","");
		$sqlwhere="";
		if($makename!=''){ $sqlwhere .= ' and username = "'.$makename.'" ';}
		if($makedate1!=''&&$makedate2!=''){ $sqlwhere .= ' and time >= "'.$makedate1.' 0:0:0"  and time <="'. $makedate2.' 23:59:59" ';}
		else if($makedate1!=''){ $sqlwhere .= ' and time >= "'.$makedate1.' 0:0:0"  and time <="'. $makedate1.' 23:59:59" ';}
		else if($makedate2!=''){ $sqlwhere .= ' and time >= "'.$makedate2.' 0:0:0"  and time <="'. $makedate2.' 23:59:59" ';}
		if($operation!=''){ $sqlwhere .= ' and operation like "%'.$operation.'%"';}
		if($operation2!=''){ $sqlwhere .= ' and operation like "%'.$operation2.'%"';}
		if($keyword!=''){ $sqlwhere .= ' and operation like "%'.$keyword.'%"';}
		if($sqlwhere!="")
		{
			$sqlwhere=' id>0 '.$sqlwhere;
			db('bts_history')->where($sqlwhere)->delete();
		}
		$this->success('数据删除成功！','index');
    }
}
