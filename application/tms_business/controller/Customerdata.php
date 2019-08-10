<?php
namespace app\tms_business\controller;
use think\Controller;
use think\Db;

class Customerdata extends Controller
{
	//客户资料
    public function index()
    {
		//用户列表
		$userlist=db('bts_userlist')->order('nouse asc,username asc')->select();
		$userarray=fieldtoarray($userlist,'username','');
		$this->assign('userarray', $userarray);
		
		//产品分类列表
		$productclassrray[]="";
		$productclassrray=getpclassarray("bts_product_class",0,"id","cnclassname,enclassname",$productclassrray,0);
		$this->assign('productclassrray', $productclassrray);
		
		//客户来源列表
		$clientsourcelist[]="";
		$clientsourcelist=getpclassarray("bts_source",0,"id","classname",$clientsourcelist,0);
		$this->assign('clientsourcearray', $clientsourcelist);
		
		//国家地区列表
		$countrylist=db('bts_national')->order('cnname asc,enname asc')->select();
		$countryarray=fieldtoarray($countrylist,'cnname','no');
		$this->assign('countryarray', $countryarray);
		
		//搜索条件
		$sqlwhere=array("","");
		$customid=input("customid");
		$sqlwhere=sqlwhereand("客户编号",$sqlwhere,"allno",$customid,1,1);
		$customname=input("customname");
		$sqlwhere=sqlwhereand("客户名称",$sqlwhere,"enname",$customname,1,1);
		$email=input("email");
		$sqlwhere=sqlwhereand("邮箱",$sqlwhere,"email1,email2,email3,email4,email5,email6,email7,email8,email9,email10",$email,1,1);
		$tel=input("tel");
		$sqlwhere=sqlwhereand("电话",$sqlwhere,"phone1,phone2,phone3,phone4,phone5,phone6,phone7,phone8,phone9,phone10",$tel,1,1);
		$fax=input("fax");
		$sqlwhere=sqlwhereand("传真",$sqlwhere,"fax",$fax,1,1);
		$contact=input("contact");
		$sqlwhere=sqlwhereand('联系人',$sqlwhere,"contact1,contact2,contact3,contact4,contact5,contact6,contact7,contact8,contact9,contact10",$contact,1,1);
		$web=input("web");
		$sqlwhere=sqlwhereand("网址",$sqlwhere,"web",$web,1,1);
		$makedate1=input("makedate1");
		$makedate2=input("makedate2");
		$sqlwhere=sqlwheredate("日期",$sqlwhere,"time",$makedate1,$makedate2);
		$countryid=input("country");
		$sqlwhere=sqlwhereand("国家",$sqlwhere,"nationalid",$countryid);
		$clientsource=input("clientsource");
		$sqlwhere=sqlwhereand("客户来源",$sqlwhere,"source",$clientsource);
		$class=input("class");
		$sqlwhere=sqlwhereand("客户类型",$sqlwhere,"class",$class,1,1);
		$makename=input("makename");
		$sqlwhere=sqlwhereand("建档人",$sqlwhere,"username",$makename,1,1);
		//$productclass=input("productclass");
		//$sqlwhere=sqlwhereand("产品分类",$sqlwhere,"productclass",$productclass,1,1);
		$blacklist=input("blacklist");
		$sqlwhere=sqlwhereand("黑名单",$sqlwhere,"blacklist",$blacklist);
		$vemail=input("vemail");
		$sqlwhere=sqlwhereand("失效邮箱",$sqlwhere,"vemail",$vemail);
		$nosend=input("nosend");
		$sqlwhere=sqlwhereand("不可群发邮件",$sqlwhere,"nosend",$nosend);
		$isairmail=input("isairmail");
		$sqlwhere=sqlwhereand("曾用AriMail寄样板",$sqlwhere,"isairmail",$isairmail);
		$quotation=input("quotation");
		if($quotation!="")
		{
			$sqlwhere[1]=" 曾询价 ";
			if($sqlwhere[0]!="")$sqlwhere[0].=" and";
			$sqlwhere[0].=" customid in (select customid from bts_quotation group by customid)";
		}
		$pi=input("pi");
		if($pi!="")
		{
			$sqlwhere[1]=" 曾开PI ";
			if($sqlwhere[0]!="")$sqlwhere[0].=" and";
			$sqlwhere[0].=" customid in (select customid from bts_pi group by customid)";
		}
		$isdn=input("isdn");
		if($isdn!="")
		{
			$sqlwhere[1]=" 曾开Debit Note ";
			if($sqlwhere[0]!="")$sqlwhere[0].=" and";
			$sqlwhere[0].=" customid in (select customid from bts_debit_note group by customid)";
		}
		//$sqlwhere=sqlwhereand($sqlwhere,"salesman",$productclass,1,1);
		//echo $sqlwhere[0];
		
		$pagesize = 15;
		$subsql = Db::table('bts_national')->field('no,enname as countryname')->buildSql();
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
		$this->assign('sqlwhere', $sqlwhere[1]);
		$this->assign('page', $page);
		return $this->fetch();
    }
	//客户资料(添加和修改页面)
	public function edit()
	{
		$customid = input('customid','');
		if(!empty($customid)){
			$rs = db('bts_custom')->where('allno',$customid)->find();
		}else{
			$rs=blanktablefield("bts_custom");
		}
		//dump($rs);
		//客户来源列表
		$clientsourcelist[]="";
		$clientsourcelist=getpclassarray("bts_source",0,"id","classname",$clientsourcelist,0);
		$this->assign('source_arr', $clientsourcelist);

		$nationallist=db('bts_national')->order('continents asc')->select();//国家->order('name')
		$nationalarr=fieldtoarray($nationallist,'cnname','no');
		$this->assign('nationalarr', $nationalarr);
		
		$userlist=db('bts_userlist')->order('nouse asc,username asc')->select();//用户
		$userarray=fieldtoarray($userlist,'username','');
		$this->assign('userarray', $userarray);
		
		$this->assign('rs',$rs);
		return $this->fetch();
	}
	//客户资料(添加和修改数据操作)
	public function update()
	{
		$arr = $_POST;
		$id = input('id',0);
		unset($arr['select']);unset($arr['Submit']);unset($arr['qq_i']);unset($arr['whatsapp_i']);unset($arr['webchat_i']);
		unset($arr['facebook_i']);unset($arr['google_i']);unset($arr['linkedin_i']);unset($arr['pinterest_i']);
		unset($arr['twitter_i']);unset($arr['instagram_i']);unset($arr['youtube_i']);unset($arr['skype_i']);
		if($id>0){
			$arr['blacklist'] = isset($_POST['blacklist'])?1:0;
			$arr['black'] = $_POST['black'];
			$arr['isairmail'] = isset($_POST['isairmail'])?1:0;
			$arr['airmail'] = isset($_POST['isairmail'])?$_POST['airmail']:'';
			$arr['uptime'] = date("Y-m-d H:i:s",time());
			$arr['upuser'] = Config('username');
			$arr['upip'] = $_SERVER["REMOTE_ADDR"];
			$arr['nosend'] = isset($_POST['nosend'])?1:0;		$arr['isprice'] = isset($_POST['isprice'])?1:0;
			$arr['vemail'] = isset($_POST['vemail'])?1:0;		$arr['isqt'] = isset($_POST['isqt'])?1:0;
			$arr['ispi'] = isset($_POST['ispi'])?1:0;			$arr['isdn'] = isset($_POST['isdn'])?1:0;
			$arr['isci'] = isset($_POST['isci'])?1:0;			$arr['isfall'] = isset($_POST['isfall'])?1:0;
			$arr['isqp'] = isset($_POST['isqp'])?1:0;
			unset($arr['customid']);unset($arr['no']);
			$rs = db('bts_custom')->where('id',$_POST['id'])->update($arr);
		}else{
			$tno=newbasicno('custom');
			$tallno=showtableno('custom',$tno,0,0);
			$arr['no'] = $tno;
			//$arr['customid'] = $tallno;
			$arr['allno'] = $tallno;
			$arr['parentno'] = $tallno;
			$arr['username'] = Config('username');
			$arr['airmail'] = isset($_POST['isairmail'])?$_POST['airmail']:'';
			$arr['isairmail'] = isset($_POST['isairmail'])?1:0;
			$arr['time'] = date("Y-m-d H:i:s",time());
			$arr['upip'] = $_SERVER['REMOTE_ADDR'];
			$arr['uptime'] = date("Y-m-d H:i:s",time());
			$rs = db('bts_custom')->insert($arr);
		}
		if($rs){
			$this->success("客户信息提交成功",url('tms_business/customerdata/index'));
		}else{
			$this->error("客户信息提交失败");
		}

	}
	//查看客户资料
	public function customer_show()
	{
		$customid =input('customid');
		$rs = db('bts_custom')->where('allno',$customid)->find();
		$parent =db('bts_custom')->where('allno',$rs['parentno'])->find();
		$source = db('bts_source')->where('no',$rs['source'])->find();//客户来源
		$national = db('bts_national')->where('no',$rs['nationalid'])->find();//国家
		$this->assign('rs',$rs);
		$this->assign('source',$source);
		$this->assign('parent',$parent);
		$this->assign('national',$national);
		return $this->fetch();
	}

	public function customer_add()
	{
		if($_POST){
			$arr = $_POST;
			$tno=newbasicno('Custom');
			$tallno=showtableno('Custom',$tno,0,0);
			$arr['no'] = $tno;
			$arr['own'] =$_POST['own'];
			unset($arr['select']);unset($arr['Submit']);unset($arr['qq_i']);unset($arr['whatsapp_i']);unset($arr['webchat_i']);
			unset($arr['facebook_i']);unset($arr['google_i']);unset($arr['linkedin_i']);unset($arr['pinterest_i']);
			unset($arr['twitter_i']);unset($arr['instagram_i']);unset($arr['youtube_i']);unset($arr['skype_i']);
			$arr['customid'] = $tallno;
			$arr['ex'] = $_POST['ex'];
			$arr['airmail'] = isset($_POST['isairmail'])?$_POST['airmail']:'';
			$arr['isairmail'] = isset($_POST['isairmail'])?1:0;
			$arr['time'] = date("Y-m-d H:i:s",time());
			$rs = db('bts_custom')->insert($arr);
			if($rs){
				$this->success("客户资料添加成功",url('tms_business/customerdata/index'));
			}else{
				$this->error("客户资料添加失败");
			}

		}
		//用户列表
		$userlist=db('bts_userlist')->order('nouse asc,username asc')->select();
		$userarray=fieldtoarray($userlist,'username','');
		$nationallist=db('bts_national')->order('name')->select();//国家
		$nationalarr=fieldtoarray($nationallist,'name','no');
		$md = new Source();
		$list[0][0]="";
		$list = $md->getsource(0,0,$list,0);
		$this->assign('source_arr',$list);
		$this->assign('userarray', $userarray);
		$this->assign('nationalarr', $nationalarr);
		return $this->fetch();
	}

}
