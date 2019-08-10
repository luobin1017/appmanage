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
		$no = input("no","");
		$name = input("name","");
		$city = input("city","");
		$contact = input("contact","");
		$tel = input("tel","");
		$email = input("email","");
		$fax = input("fax","");
		$web = input("web","");
		$black = input("black","");
		$jinyin = input("jinyin","");
		$xunjia = input("xunjia","");
		$makename = input("makename","");
		$makedate1 = input("makedate1","");
		$makedate2 = input("makedate2","");
		$map = [];
		if(!(empty($no))){ $map['allno']  = ['like',"%$no%"];}
		if(!(empty($name))){ $map['name']  = ['like',"%$name%"];}
		if(!(empty($city))){ $map['city']  = ['like',"%$city%"];}
		if(!(empty($contact))){ $map['contact1']  = ['like',"%$contact%"];}
		if(!(empty($tel))){ $map['phone1']  = ['like',"%$tel%"];}
		if(!(empty($email))){ $map['email1']  = ['like',"%$email%"];}
		if(!(empty($fax))){ $map['fax']  = ['like',"%$fax%"];}
		if(!(empty($web))){ $map['web']  = ['like',"%$web%"];}
		if(!(empty($black))){ $map['blacklist']  = ['=',"$black"];}
		if(!(empty($jinyin))){ $map['jinyin']  = ['like',"%$jinyin%"];}
		if(!(empty($xunjia))){ $map['xunjia']  = ['like',"%$xunjia%"];}
		if(!(empty($makename))){ $map['username']  = ['like',"%$makename%"];}
		if(!(empty($makedate1))){ $map['time']  = [['>',"$makedate1"],['<',"$makedate2"],"and"];}
		$pagesize = 20;
		$rs = db("bts_suppliers")->where($map)->order("no desc")->limit(0,20)->paginate($pagesize,false,['query' => request()->param()]);
		//dump(DB::table("bts_suppliers")->getLastSql());
		$userlist=db('bts_userlist')->order('nouse asc,username asc')->select();
		$userarray=fieldtoarray($userlist,'username','');
		$this->assign('user', $userarray);
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
		return $this->fetch();
	}

	//添加供应商
	public function suppliers_add()
	{
		checkpermissions('0603',true);
		//historylog("浏览了供应商页面！");
		if($_POST){
			$tno=newbasicno('suppliers');
			$tallno=showtableno('suppliers',$tno,0,0);
			$arr = [];									$arr['no'] = $tno;//使用函数获取no的最大值
			$arr['allno'] = $tallno;					$arr['own'] = str_replace($tno,'',$tallno);
			$arr['name'] = $_POST['name'];				$arr['ename'] = $_POST['ename'];
			$arr['city'] = $_POST['city'];				$arr['fax'] = $_POST['fax'];
			$arr['contact1'] = $_POST['contact1'];	$arr['phone1'] = $_POST['phone1'];
			$arr['email1'] = $_POST['email1'];			$arr['mob1'] = $_POST['mob1'];
			$arr['qq1'] = $_POST['qq1'];				$arr['net1'] = $_POST['net1'];
			$arr['contact2'] = $_POST['contact2'];	$arr['phone2'] = $_POST['phone2'];
			$arr['email2'] = $_POST['email2'];			$arr['mob2'] = $_POST['mob2'];
			$arr['qq2'] = $_POST['qq2'];				$arr['net2'] = $_POST['net2'];
			$arr['address'] = $_POST['address'];		$arr['web'] = $_POST['web'];
			$arr['content'] = $_POST['content'];		$arr['jinyin'] = $_POST['jinyin'];
			$arr['xunjia'] = $_POST['xunjia'];			$arr['time'] = date('Y-m-d H:i:s',time());
			$arr['username'] = $_COOKIE['username'];
			$rs = DB::table('bts_suppliers')->insert($arr);
			if($rs){
				$this->success("供应商信息添加成功",url('tms_purchase/suppliers/index'));
			}else{
				$this->error("供应商信息添加失败");
			}
		}
		return $this->fetch();
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
	//修改供应商頁面
	public function suppliers_update()
	{
		checkpermissions('0604',true);
		//historylog("浏览了供应商页面！");
		$arr = db('bts_suppliers')->where('id',$_GET['id'])->find();
		$this->assign('arr',$arr);
		return $this->fetch();
	}
	public function suppliers_update_save()
	{
		$arr['name'] = $_POST['name'];				$arr['ename'] = $_POST['ename'];
		$arr['city'] = $_POST['city'];				$arr['fax'] = $_POST['fax'];
		$arr['contact1'] = $_POST['contact1'];	$arr['phone1'] = $_POST['phone1'];
		$arr['email1'] = $_POST['email1'];			$arr['mob1'] = $_POST['mob1'];
		$arr['qq1'] = $_POST['qq1'];				$arr['net1'] = $_POST['net1'];
		$arr['contact2'] = $_POST['contact2'];	$arr['phone2'] = $_POST['phone2'];
		$arr['email2'] = $_POST['email2'];			$arr['mob2'] = $_POST['mob2'];
		$arr['qq2'] = $_POST['qq2'];				$arr['net2'] = $_POST['net2'];
		$arr['address'] = $_POST['address'];		$arr['web'] = $_POST['web'];
		$arr['content'] = $_POST['content'];		$arr['jinyin'] = $_POST['jinyin'];
		$arr['xunjia'] = $_POST['xunjia'];			$arr['uptime'] = date('Y-m-d H:i:s',time());
		$arr['upuser'] = $_COOKIE['username'];	$arr['upip'] = $_SERVER["REMOTE_ADDR"];
		$rs = db('bts_suppliers')->where('id',$_POST['id'])->update($arr);
		if($rs){
			$this->success("供应商信息修改成功",url('tms_purchase/suppliers/index'));
		}else{
			$this->error("供应商信息修改失败");
		}
	}
}