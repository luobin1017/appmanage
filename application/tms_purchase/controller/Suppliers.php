<?php
namespace app\tms_purchase\controller;
use think\Controller;
use think\Validate;
use think\Db;

class Suppliers extends Controller
{
	//供应商信息
	public function index()
	{
		checkpermissions('0601',true);
		//historylog("浏览了供应商页面！");
		//用户列表
		$userlist=db('bts_userlist')->order('nouse asc,username asc')->select();
		$userarray=fieldtoarray($userlist,'username','');
		$this->assign('userarray', $userarray);
		//搜索条件
		$sqlwhere=array("","");
		$no=input("no");
		$sqlwhere=sqlwhereand("供应商代码",$sqlwhere,"allno",$no,1,1);
		$name=input("name");
		$sqlwhere=sqlwhereand("供应商中/英文名",$sqlwhere,"name",$name,1,1);
		$city=input("city");
		$sqlwhere=sqlwhereand("所在地区",$sqlwhere,"city",$city,1,1);
		$contact=input("contact");
		$sqlwhere=sqlwhereand("联系人名称",$sqlwhere,"contact1",$contact,1,1);
		$phone=input("phone");
		$sqlwhere=sqlwhereand("电话",$sqlwhere,"phone1",$phone,1,1);
		$email=input("email");
		$sqlwhere=sqlwhereand("邮箱",$sqlwhere,"email1",$email,1,1);
		$web=input("web");
		$sqlwhere=sqlwhereand("网址",$sqlwhere,"web",$web,1,1);
		$black=input("black");
		$sqlwhere=sqlwhereand("黑名单",$sqlwhere,"blacklist",$black,0,0);
		$jinyin=input("jinyin");
		$sqlwhere=sqlwhereand("经营商品",$sqlwhere,"jinyin",$jinyin,1,1);
		$xunjia=input("xunjia");
		$sqlwhere=sqlwhereand("曾询价商品",$sqlwhere,"xunjia",$xunjia,1,1);
		$makename=input("makename");
		$sqlwhere=sqlwhereand("建档人",$sqlwhere,"username",$makename,1,1);
		$makedate1 = input("makedate1");
		$makedate2 = input("makedate2");
		$sqlwhere=sqlwheredate("日期",$sqlwhere,"time",$makedate1,$makedate2);

		$pagesize = 20;
		$rs = db("bts_suppliers")->where($sqlwhere[0])->order("no desc")->limit(0,20)->paginate($pagesize,false,['query' => request()->param()]);
		//dump(DB::table("bts_suppliers")->getLastSql());
		$page = $rs->render();
		//  页数量
		$this->assign('pagesize',$pagesize);
		//  总数据
		$this->assign('total',$rs->total());
		//  总页数
		$total= ceil($rs->total() / 20);
		$this->assign('totalPage', $total);
		//  当前页
		$pagenow =input('page', 1);
		$this->assign('pagenow', $pagenow);
		$this->assign('page',$page);
		$this->assign('rs',$rs);
		$this->assign('userlist',$userlist);
		$this->assign('count',$rs->total());
		$this->assign('sqlwhere',$sqlwhere[1]);
		return $this->fetch();
	}
	//客户资料(添加和修改页面)
	public function edit()
	{
		$id = input('id','');
		if(!empty($id)){
			$rs = db('bts_suppliers')->where('id',$id)->find();
		}else{
			$rs=blanktablefield("bts_suppliers");
		}
		$this->assign('arr',$rs);
		return $this->fetch();
	}
	//供应商资料(添加和修改数据操作)
	public function update()
	{
		$arr = $_POST;
		$id = input('id',0);
		unset($arr['Submit']);
		if($id>0){
			unset($arr['own']);
			$arr['uptime'] = date('Y-m-d H:i:s',time());
			$arr['upuser'] = $_COOKIE['username'];
			$arr['upip'] = $_SERVER["REMOTE_ADDR"];
			$rs = db('bts_suppliers')->where('id',$_POST['id'])->update($arr);
		}else{
			$tno=newbasicno('suppliers');
			$tallno=showtableno('suppliers',$tno,0,0);
			$arr['no'] = $tno;//使用函数获取no的最大值
			$arr['allno'] = $tallno;
			$arr['own'] = str_replace($tno,'',$tallno);
			$arr['time'] = date('Y-m-d H:i:s',time());
			$arr['username'] = $_COOKIE['username'];
			$rs = db('bts_suppliers')->insert($arr);
		}
		if($rs){
			$this->success("供应商信息提交成功",url('tms_purchase/suppliers/index'));
		}else{
			$this->error("供应商信息提交失败");
		}

	}
	//供应商信息显示
	public function suppliers_show()
	{
		checkpermissions('0602',true);
		//historylog("浏览了供应商页面！");
		$id = $_GET['id'];
		$arr = db('bts_suppliers')->where('id',$id)->find();
		if(!empty($arr['phone1'])){
			$temp = $arr['phone1'];
			$temp = str_replace('(','',$temp);
			$temp = str_replace(')','',$temp);
			$temp = str_replace(' ','',$temp);
			if(substr($temp , 0 , 3) == "0"){ $temp = substr($temp , -1 , 1);}
			$temp = "+86".$temp;
			$arr['temp'] = $temp;
		}
		if(!empty($arr['phone2'])){
			$temp2 = $arr['phone2'];
			$temp2 = str_replace('(','',$temp2);
			$temp2 = str_replace(')','',$temp2);
			$temp2 = str_replace(' ','',$temp2);
			if(substr($temp2 , 0 , 3) == "0"){ $temp2 = substr($temp2 , -1 , 1);}
			$temp = "+86".$temp2;
			$arr['temp2'] = $temp2;
		}
		//相关PO
		$po = db('bts_po')->where('suppliersid',$arr['allno'])->select();
		$this->assign('po',$po);
		$this->assign('arr',$arr);
		return $this->fetch();
	}

}