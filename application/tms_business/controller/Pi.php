<?php
namespace app\tms_business\controller;
use think\Controller;
use think\Db;

class Pi extends Controller
{
	//PI
    public function index()
    {
		//( B401 ) 浏览	( B402 ) 查看	( B403 ) 添加	( B404 ) 修改
		checkpermissions('B401',true);
		historylog("浏览了PI列表！");
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
		$countrylist=db('bts_national')->order('enname asc')->select();
		$countryarray=fieldtoarray($countrylist,'enname','no');
		$this->assign('countryarray', $countryarray);

		if(input("seeAll")==1){
			header("Location:".url('pi/index'));
		}
		//搜索条件
		$sqlwhere=array("","");
		$no=input("allno");
		$sqlwhere=sqlwhereand("单据代码",$sqlwhere,"allno",$no,1,1);
		$makename=input("makename");
		$sqlwhere=sqlwhereand("制单人",$sqlwhere,"makename",$makename,1,1);
		$customid=input("customid");
		$sqlwhere=sqlwhereand("客户代码",$sqlwhere,"customid",$customid,1,1);
		$custompo=input("custompo");
		$sqlwhere=sqlwhereand("客户PO",$sqlwhere,"custompo",$custompo,1,1);
		$customname=input("customname");
		$sqlwhere=sqlwhereand("客户名称",$sqlwhere,"customname",$customname,1,1);
		$clientsource=input("clientsource");
		$sqlwhere=sqlwhereand("客户来源",$sqlwhere,"source",$clientsource);
		$received=input("received");
		//$sqlwhere=sqlwherereceived("收款情况",$sqlwhere,"received",$received,"amount");
		$iscancel=input("iscancel");
		$sqlwhere=sqlwhereand("是否取消",$sqlwhere,"iscancel",$iscancel,0,0);
		$isci=input("isci");
		$sqlwhere=sqlwhereand("小额信保",$sqlwhere,"isci",$isci,0,0);
		$countryid=input("country");
		$sqlwhere=sqlwhereand("国家",$sqlwhere,"nationalid",$countryid);
		$productclass=input("productclass");
		$sqlwhere=sqlwhereand("产品分类",$sqlwhere,"classid",$productclass,1,1);
		$productno=input("productno");
		$sqlwhere=sqlwhereand("产品代码",$sqlwhere,"product",$productno,1,1);
		$productname=input("productname");
		$sqlwhere=sqlwhereand("产品名称",$sqlwhere,"productname",$productname,1,1);
		$makedate3=input("makedate3");
		$makedate4=input("makedate4");
		$sqlwhere=sqlwheredate("开单日期",$sqlwhere,"makedate",$makedate3,$makedate4);
		$pagesize = 20;
		//dump($sqlwhere[0]);
		//$list = Db::table("bts_pi")->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);
		$subsql = Db::table('bts_pi_item')->field('allno as pallno,product,productname')->group("allno")->buildSql();
		$list = Db::table("bts_pi")->alias('a')->Join([$subsql=>'b'],"b.pallno=a.allno",'left')->where($sqlwhere[0])->order("a.id desc")->paginate($pagesize, false, ['query' => request()->param(),]);
		dump(Db::table('bts_pi')->getLastSql());
		dump($sqlwhere);
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
	public function edit()
	{
		if(input('fiaudit')==1){
			echo "<span style='color: red;font-size:30px'>结算审核完成，该单据将无法进行修改！</span>";
			exit();
		}
		$field_var='allno,qno,rno,type,makedate,makename,language,customid,customname,customaddress,contact,phone,fax,email,custompo,currencys,source,rate,priceterm,itemid,productid,productorderid,productname,picture,isbasic,other_pd,classid,unit,amount,num,price';
		$field_text='productinfo,picture,payment,confirmation,othercharges,bankinfo,ciremark,delivery,deliveryaddr,address,content,mark,markpicture,boxplants,boxplantspicture,side,sidepicture,pricelist';
		$field_item_var='no_item,itemid,productorderid,product,productid,isbasic,productname,unit,picture,num,price';
		$field_item_text='productinfo';

		$action=input('action');
		$no=input('no');
		$data=$_POST;
		//dump($no);
		//新建单据
		if($action=="")
		{
			checkpermissions('B403',true);
			$no=newbasicno("temp");//获取temp表最大的NO
			$qno=newbasicno("pi",1);//获取pi表最大的no
			$data['allno']='';
			$data['qno']=$qno;
			$data['rno']=0;
			$data['type']='new';
			$data['language']='en';
			$data['itemid']=0;
			$data['makedate']=now();
			$data['makename']=Config('username');
			$data['currencys']= 'USD';
			$udata=fieldtotemp($data,$field_var,$field_text);//查询数据赋值给临时数据
			$udata['no']=$no;
			db("bts_temp")->insert($udata);
			gourl('?action=update&no='.$no);
		}
		else if($action=="modify"||$action=="import")//修改单据
		{
			checkpermissions('B404',true);
			$id=input('id');
			$rs = db("bts_pi")->where("id",$id)->select();
			if($data){showjserr("您要编辑的PI不存在！");}
			$data=$rs[0];
			$no=newbasicno("temp");
			$data['qno']=$data['no'];
			if($action=="modify")
			{
				$data['type']='edit';
			}
			else
			{
				$data['type']='new';
			}
			$udata['makename']=Config('username');
			$udata=fieldtotemp($data,$field_var,$field_text);
			$udata['no']=$no;
			db("bts_temp")->insert($udata);
			$data = db("bts_pi_item")->where("no like '".$data['qno']."' and rno like '".$data['rno']."'")->select();
			//dump(Db::table('bts_pi_item')->getLastSql());
			//exit();
			foreach($data as $item)
			{
				$ino=newbasicno("temp");
				$item['no_item']=$no."_item";
				$item['picture']=$item['picture'];
				$idata=fieldtotemp($item,$field_item_var,$field_item_text);
				//dump($idata);
				//exit();
				$item['no']=$ino;
				db("bts_temp")->insert($idata);
			}
			gourl("?action=update&no=".$no);
		}
		//选择语种
		if($action=="selectlanguage")
		{
			$data['language']=input('language');
		}
		//选择用户
		if($action=="selectcustom")
		{
			$customid=input('customid');
			$rs = db("bts_custom")->where("allno",$customid)->select();
			if($rs)
			{
				$language = $data['language'];
				$data['customid']=$rs[0]['allno'];
				$data['customname']=$rs[0][$language.'name'];
				$data['customaddress']=$rs[0][$language.'address'];
				$data['contact']=$rs[0]['contact1'];
				$data['phone']=$rs[0]['phone1'];
				$data['fax']=$rs[0]['fax'];
				$data['email']=$rs[0]['email1'];
			}
		}
		//选择产品
		if($action=="selectproduct")
		{
			$language = $data['language'];
			$currencys = input('currencys');
			$pid=input('productid');
			$subsql = db('bts_product')->field("enname,cnname,allno,enpdetion,cnpdetion,picture,classid")->where('allno',$pid)->buildSql();
			$rs = db('bts_product_class')->alias('a')->Join([$subsql=>'b'],"b.classid=a.id",'inner')->find();
			if($rs)
			{
				$data['itemid']="";
				$data['productorderid']="";
				$data['classid']=$rs['classid'];
				$data['productname']=$rs[$language.'name'];
				$data['isbasic']=1;
				$data['productid']=$rs['allno'];//产品编号
				$data['units']='PCS';
				$data['productinfo']=$rs[$language.'pdetion'];
				if($rs['picture']!="")$data['picture']="\\tms\\upfiles\\product\\".$rs['picture'];

			}
		}
		//选择价格
		if($action =="selectprice")
		{
			$priceid=trim(input('priceid'),'|');
			$pricearr = explode("|",$priceid);
			$newprice = array();
			$newpricearr = array();
			foreach($pricearr as $k=>$v){
				$newprice[$k] = explode(",",trim($v,','));
			}
			foreach($newprice as $k=>$v){
				$newpricearr[$k] = join(",", $v);
			}
			$price_over = implode("|", $newpricearr);
			$data['pricelist']=  $price_over;

		}
		//添加、更新细项
		if($action=="add")
		{
			$data['product']=$data['productid'];
			unset($data["productid"]);
			if(isset($data['other_pd']))
			{
				$data["product"]="";
				$data["productname"]="";
				$data["picture"]="";
				$data["productinfo"]=$_POST['productinfo2'];
			}
			$idata=fieldtotemp($data,$field_item_var,$field_item_text);
			$idata['no']=newbasicno("temp");
			$idata['str1']=$no."_item";
			if($data['itemid']!="")
			{
				db("bts_temp")->where('id',$data['itemid'])->update($idata);
			}
			else
			{
				db("bts_temp")->insert($idata);
			}
			$data['other_pd']='';
			$data['productname']='';
			$data['isbasic']='';
			$data['num']='';
			$data['price']='';
			$data['productid']='';
			$data['unit']='PCS';
			$data['productinfo']='';
			$data['picture']='';
			$data['productorderid']='';
			//mydump($data);
		}
		//编辑细项
		if($action=="edit")
		{
			$data['itemid']=$_GET['itemid'];
			$rs=db("bts_temp")->where('id',$data['itemid'])->select();
			$temp=tempfield($rs[0],$field_item_var,$field_item_text);
			$data['productname']=$temp['productname'];
			$data['productid']=$temp['product'];
			if($data['productid']=="")$data['other_pd']=1;
			$data['isbasic']=$temp['isbasic'];
			$data['unit']=$temp['unit'];
			$data['productinfo']=$temp['productinfo'];
			$data['picture']=$temp['picture'];
			$data['num']=$temp['num'];
			$data['price']=$temp['price'];
			$data['productorderid']=$temp['productorderid'];
		}
		//删除细项
		if($action=="del")
		{
			$data['itemid']=$_GET['itemid'];
			if($data['itemid']!="")
			{
				db("bts_temp")->where('id',$data['itemid'])->delete();
			}
		}
		//提交表单
		if($action=="submit")
		{
			//dump($data);exit(0);
			//去除非必要的字段
			$idata=setsubmitdata("bts_pi",$data);
			//dump($idata);
			if($data['type']=="r"){ $idata['rno']=((int)$idata['rno'])+1;}
			//读取临时表细项
			$itemrs = db("bts_temp")->field('*,cast(str2 as signed) orderid')->where("str1",$no."_item")->order("orderid asc")->select();
			if(count($itemrs)<=0)
			{
				showjserr("您还未添加细项!");
			}
			//添加或修改主表
			if($data['type']=="new"||$data['type']=='r')
			{
				if($data['type']=='r')
				{
					$idata['no']=$data['qno'];
					$idata['rno']=newtabler("bts_pi","no like '".$data['qno']."'");
				}
				else
				{
					$idata['no']=newbasicno("pi",1);
					$idata['rno']=0;
					$rate=getrate();
					$idata['rate'] = $rate;
				}
				$idata['allno']=showtableno("pi",$idata['no'],0,$idata['rno']);
				historylog("添加了  <span style='color: red'> ".$idata['allno']."</span>   的PI资料！");
				$idata['makedate']=now();
				db("bts_pi")->insert($idata);
			}
			else if($data['type']=="edit")
			{
				$idata['no']=$data['qno'];
				unset($idata['makedate']);
				db("bts_pi")->where('no like "'.$data['qno'].'" and rno like "'.$data['rno'].'"')->update($idata);
				historylog("更新了PI资料！");
				db("bts_pi_item")->where('no like "'.$data['qno'].'" and rno like "'.$data['rno'].'"')->delete();
			}
			db("bts_temp")->where("no",$no)->delete();
			//添加细项
			for($i=0;$i<count($itemrs);$i++)
			{
				$listitem=tempfield($itemrs[$i],$field_item_var,$field_item_text);
				$listitem['picture']=$listitem['picture'];
				//dump($listitem);
				$lidata=setsubmitdata("bts_pi_item",$listitem);
				//dump($lidata);
				//exit();
				$lidata['allno']=$idata['allno'];
				$lidata['no']=$idata['no'];
				$lidata['rno']=$idata['rno'];
				$lidata['time']=now();
				db("bts_pi_item")->insert($lidata);
				//dump($lidata);
				//exit();
			}
			db("bts_temp")->where("str1",$no."_item")->delete();

			gourl("../");
		}
		//读取数据库操作
		if($action!="update")
		{
			$udata=fieldtotemp($data,$field_var,$field_text);
			db("bts_temp")->where('no',$no)->update($udata);
			gourl('?action=update&no='.$no);
		}
		//读取TEMP表主项
		$rs = db("bts_temp")->where("no",$no)->select();

		$list=tempfield($rs[0],$field_var,$field_text);
		$this->assign('v', $list);
		//dump($list);
		//读取TEMP表细项
		$itemrs = db("bts_temp")->field('*,cast(str2 as signed) orderid')->where("str1",$no."_item")->order("orderid asc")->select();
		$listitem=array();
		for($i=0;$i<count($itemrs);$i++)
		{
			$listitem[$i]=tempfield($itemrs[$i],$field_item_var,$field_item_text);
			$listitem[$i]['id']=$itemrs[$i]['id'];
		}
		$this->assign('listitem', $listitem);

		//客户来源列表
		$clientsourcelist=array();
		$clientsourcelist=getpclassarray("bts_source",0,"id","classname",$clientsourcelist,0);
		$this->assign('clientsourcearray', $clientsourcelist);

		//货币类型
		$currencyslist=db('bts_currency')->order('no desc')->select();
		$currencysarray=fieldtoarray($currencyslist,'currency','');
		$this->assign('currencysarray', $currencysarray);

		//产品分类列表
		$productclassarray=array();
		$productclassarray=getpclassarray("bts_product_class",0,"id","enclassname,cnclassname",$productclassarray,0);
		$this->assign('productclassarray', $productclassarray);

		//价格条款
		$pricetermlist=db('bts_clause')->order('id asc')->select();
		$pricetermarray=fieldtoarray($pricetermlist,$list['language'].'name','');
		$this->assign('pricetermarray', $pricetermarray);

		//付款条件
		$paymentlist=db('bts_payment')->order('id asc')->select();
		$paymentarray=fieldtoarray($paymentlist,$list['language'].'name','');
		$this->assign('paymentarray', $paymentarray);

		//用户列表
		$userlist=db('bts_userlist')->order('nouse asc,username asc')->select();
		$userarray=fieldtoarray($userlist,'username','');
		$this->assign('userarray', $userarray);

		//银行下拉列表
		$banklist = db('bts_bank')->where('iscancel',0)->select();
		$bankarray=fieldtoarray($banklist,'encontent','');
		$this->assign('bankarray', $bankarray);

		$this->assign('no',$no);
		$this->assign('field_item_var',$field_item_var);
		$this->assign('field_item_text',$field_item_text);

		return $this->fetch();
	}
	//查看
	public function pi_show()
	{
		checkpermissions('B402',true);
		//货币类型
		$currencyslist=db('bts_currency')->order('no desc')->select();
		$currencysarray=fieldtoarray($currencyslist,'currency','');
		$this->assign('currencysarray', $currencysarray);

		$id = input("id");
		$action=input('action');
		if($action=="modifyfee"){
			checkpermissions('B504',true);
		}
		//结算审核
		if($action=="fiaudit"){
			$rs=db('bts_pi')->where('id',$id)->update(['fiaudit'=>1]);
			//historylog("修改了编号为  <span style='color: red'>".$data['allno']/"</span>  的PI跟进信息！");
		}
		//结算解锁
		if($action=="defiaudit"){
			$rs=db('bts_pi')->where('id',$id)->update(['fiaudit'=>0]);
			//historylog("修改了编号为  <span style='color: red'>".$data['allno']/"</span>  的PI跟进信息！");
		}
		if($action=="fee"){
			$data = input();
			//对跟进的货币信息进行初始化
			//mydump($data);
			$data = setpigenjincurr($data,"bankcosts",$data['bankcosts_curr']);
			$data = setpigenjincurr($data,"localfreight",$data['localfreight_curr']);
			$data = setpigenjincurr($data,"docfee",$data['docfee_curr']);
			$data = setpigenjincurr($data,"cfsthc",$data['cfsthc_curr']);
			$data = setpigenjincurr($data,"forma",$data['forma_curr']);
			$data = setpigenjincurr($data,"otherfee",$data['otherfee_curr']);
			$data = setpigenjincurr($data,"otherfee2",$data['otherfee2_curr']);
			$data = setpigenjincurr($data,"profits",$data['profits_curr']);
			$data = setpigenjincurr($data,"onetouch",$data['onetouch_curr']);

			$idata = setsubmitdata('bts_pi',$data);
			mydump($idata);
			$rs = db('bts_pi')->where('id',$id)->update($idata);
			historylog("修改了编号为  <span style='color: red'>".$data['allno']/"</span>  的PI跟进信息！");
			gourl("?id=$id");

		}
		$rs = db('bts_pi')->where('id',$id)->find();
		historylog("查看了编号为  <span style='color: red'>".$rs['allno']."</span>  的PI！");
		$itemarr = db("bts_pi_item")->where('no like "'.$rs['no'].'" and rno like "'.$rs['rno'].'"')->select();
		$banker = db('bts_bank')->where('no',$rs['bank'])->find();
		//获取pi跟进的货币类型
		$rs['profits']=getpigenjincurr($rs,'profits');
		$rs['bankcosts']=getpigenjincurr($rs,'bankcosts');
		$rs['localfreight']=getpigenjincurr($rs,'localfreight');
		$rs['docfee']=getpigenjincurr($rs,'docfee');
		$rs['cfsthc']=getpigenjincurr($rs,'cfsthc');
		$rs['forma']=getpigenjincurr($rs,'forma');
		$rs['otherfee']=getpigenjincurr($rs,'otherfee');
		$rs['otherfee2']=getpigenjincurr($rs,'otherfee2');
		$rs['onetouch']=getpigenjincurr($rs,'onetouch');

		$source = db('bts_source')->where('no',$rs['source'])->find();//客户来源
		$this->assign('action',$action);
		$this->assign('rs',$rs);
		$this->assign('source',$source);
		$this->assign('itemarr',$itemarr);
		$this->assign('banker',$banker);
		$this->assign('itemarr',$itemarr);
		return $this->fetch();
	}

}
