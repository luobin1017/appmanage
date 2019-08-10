<?php
namespace app\tms_business\controller;
use think\Config;
use think\Controller;
use think\Db;

class Quotation extends Controller
{
	//报价单
    public function index()
    {
		checkpermissions('B301',true);
		historylog("浏览了Quotation页面！");
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

		if(input("seeAll")==1){
			header("Location:".url('quotation/index'));
		}
		//搜索条件
		$sqlwhere=array("","");
		$allno=input("allno");
		$sqlwhere=sqlwhereand("单据编号",$sqlwhere,"allno",$allno,1,1);
		$customname=input("customname");
		$sqlwhere=sqlwhereand("客户名称",$sqlwhere,"customname",$customname,1,1);
		$customid=input("customid");
		$sqlwhere=sqlwhereand("客户代码",$sqlwhere,"customid",$customid,1,1);
		$productno=input("productno");
		$sqlwhere=sqlwhereand("产品代码",$sqlwhere,"product",$productno,1,1);
		$productname=input("productname");
		$sqlwhere=sqlwhereand("产品名称",$sqlwhere,"productname",$productname,1,1);
		$productclass=input("productclass");
		$sqlwhere=sqlwhereand("产品分类",$sqlwhere,"classid",$productclass,1,1);
		$makedate1=input("makedate1");
		$makedate2=input("makedate2");
		$sqlwhere=sqlwheredate("日期",$sqlwhere,"makedate",$makedate1,$makedate2);
		$clientsource=input("clientsource");
		$sqlwhere=sqlwhereand("客户来源",$sqlwhere,"source",$clientsource);
		$makename=input("makename");
		$sqlwhere=sqlwhereand("建档人",$sqlwhere,"makename",$makename,1,1);
		//echo $sqlwhere[0];

		$pagesize = 15;
		$subsql = Db::table('bts_quotation_item')->field('allno as pallno,product,productname')->group("allno")->buildSql();
		$list = Db::table("bts_quotation")->alias('a')->Join([$subsql=>'b'],"b.pallno=a.allno",'left')->where($sqlwhere[0])->order("a.id desc")->paginate($pagesize, false, ['query' => request()->param(),]);

		//$list = Db::table("bts_quotation")->order("id desc")->paginate($pagesize, false, ['query' => request()->param(),]);
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
		$this->assign('sqlwhere', $sqlwhere[1]);
		$this->assign('page', $page);
		return $this->fetch();
    }
	
	//新增报价单
	public function edit()
	{
		$field_var='qno,rno,type,makedate,makename,language,customid,customname,customaddress,contact,phone,fax,email,customorder,currencys,source,priceterm,itemid,productid,productorderid,productname,productpicture,isbasic,other_pd,classid,unit,numprice';
		$field_text='productinfo,productpicture,payment,customized,othercharges,silkscreen,minimum,block,delivery,certificate,samples,approval,remark,validity,pricelist';
		$field_item_var='no_item,itemid,productorderid,product,productid,isbasic,productname,unit,productpicture';
		$field_item_text='productinfo,numprice';
		$action=input('action');
		$no=input('no');
		$data=$_POST;
		if($action=="")//新建单据
		{
			$no=newbasicno("temp");
			$qno=newbasicno("quotation",1);
			$data['qno']=$qno;
			$data['rno']=0;
			$data['language']='en';
			$data['type']='new';
			$data['itemid']=0;
			$data['makedate']=now();
			$data['makename']=Config('username');
			$data['currencys']= 'USD';
			$udata=fieldtotemp($data,$field_var,$field_text);
			$udata['no']=$no;
			db("bts_temp")->insert($udata);
			gourl('?action=update&no='.$no);
		}
		else if($action=="modify"||$action=="import")//修改单据
		{
			$id=input('id');
			$rs = db("bts_quotation")->where("id",$id)->select();
			if($data){showjserr("您要编辑的Quotation不存在！");}
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
			$data = db("bts_quotation_item")->where("no like '".$data['qno']."' and rno like '".$data['rno']."'")->select();
			foreach($data as $item)
			{
				$ino=newbasicno("temp");
				$item['no_item']=$no."_item";
				if($item['numprice']==''){
					$item['numprice'] = $item['num'].','.$item['price'];
				}
				$item['productpicture']=$item['picture'];
				$idata=fieldtotemp($item,$field_item_var,$field_item_text);
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
			$rs = db("bts_custom")->where("allno",$customid)->find();
			if($rs)
			{
				$language = $data['language'];
				$data['customid']=$rs['allno'];
				$data['customname']=$rs[$language.'name'];
				$data['customaddress']=$rs[$language.'address'];
				$data['contact']=$rs['contact1'];
				$data['phone']=$rs['phone1'];
				$data['fax']=$rs['fax'];
				$data['email']=$rs['email1'];
			}
		}
		//选择产品
		if($action=="selectproduct")
		{
			$currencys = input('currencys');
			$pid=input('productid');
			$subsql = db('bts_product')->field('cnname,enname,allno,enpdetion,cnpdetion,picture,classid')->where('allno',$pid)->buildSql();
			$rs = db('bts_product_class')->alias('a')->Join([$subsql=>'b'],"b.classid=a.id",'inner')->find();
			//mydump($rs);
			if($rs)
			{
				$language = $data['language'];
				$data['itemid']="";
				$data['numprice']="";
				$data['productorderid']="";
				$data['classid']=$rs['classid'];
				$data['productname']=$rs[$language.'name'];
				$data['isbasic']=1;
				$data['productid']=$rs['allno'];//产品编号
				$data['units']='PCS';
				$data['productinfo']=$rs[$language.'pdetion'];
				if($rs['picture']!="")$data['productpicture']="\\tms\\upfiles\\product\\".$rs['picture'];
				if($currencys == "USD")
				{
					$pctype=1;
				}elseif($currencys == "HKD")
				{
					$pctype=2;
				}elseif($currencys == "RMB")
				{
					$pctype=3;
				}
				$data['customized']=$rs['customized'.$pctype];
				$data['othercharges']=$rs['othercharges'.$pctype];
				$data['silkscreen']=$rs['silkscreen'.$pctype];
				$data['minimum']=$rs['minimum'.$pctype];
				$data['block']=$rs['block'.$pctype];
				$data['delivery']=$rs['delivery'.$pctype];
				$data['certificate']=$rs['certificate'.$pctype];
				$data['samples']=$rs['samples'.$pctype];
				$data['approval']=$rs['approval'.$pctype];
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
			$data['productid']='';
			$data['unit']='PCS';
			$data['productinfo']='';
			$data['productpicture']='';
			$data['numprice']='';
			$data['productorderid']='';
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
			$data['productpicture']=$temp['productpicture'];
			$data['numprice']=$temp['numprice'];
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
			$idata=setsubmitdata("bts_quotation",$data);
			//dump($idata);
			if($data['type']=="r"){ $idata['rno']=((int)$idata['rno'])+1;}
			//读取临时表细项
			$porderid='str'.fieldinlist($field_item_var,"productorderid");
			$itemrs = db("bts_temp")->field('*,cast('.$porderid.' as signed) orderid')->where("str1",$no."_item")->order("orderid asc")->select();
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
					$idata['rno']=newtabler("bts_quotation","no like '".$data['qno']."'");
				}
				else
				{
					$idata['no']=newbasicno("quotation",1);
					$idata['rno']=0;
				}
				$idata['allno']=showtableno("quotation",$idata['no'],0,$idata['rno']);
				$allno=$idata['allno'];
				$idata['makedate']=now();
				$idata['makename']=Config("username");
				//mydump($idata);
				db("bts_quotation")->insert($idata);
			}
			else if($data['type']=="edit")
			{
				$idata['no']=$data['qno'];
				$allno=showtableno("quotation",$idata['no'],0,$idata['rno']);
				unset($idata['makedate']);
				db("bts_quotation")->where('no like "'.$data['qno'].'" and rno like "'.$data['rno'].'"')->update($idata);
				db("bts_quotation_item")->where('no like "'.$data['qno'].'" and rno like "'.$data['rno'].'"')->delete();
			}
			db("bts_temp")->where("no",$no)->delete();
			//添加细项
			for($i=0;$i<count($itemrs);$i++)
			{
				$listitem=tempfield($itemrs[$i],$field_item_var,$field_item_text);
				$listitem['picture']=$listitem['productpicture'];
				//mydump($listitem);
				$lidata=setsubmitdata("bts_quotation_item",$listitem);
				//dump($lidata);
				//exit();
				$lidata['allno']=$allno;
				$lidata['no']=$idata['no'];
				$lidata['rno']=$idata['rno'];
				$lidata['time']=now();
				//dump($lidata);
				db("bts_quotation_item")->insert($lidata);
			}
			db("bts_temp")->where("str1",$no."_item")->delete();
			
			gourl(url("index"));
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
		$porderid='str'.fieldinlist($field_item_var,"productorderid");
		$itemrs = db("bts_temp")->field('*,cast('.$porderid.' as signed) orderid')->where("str1",$no."_item")->order("orderid asc")->select();
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
		$productclassarray=getpclassarray("bts_product_class",0,"id","cnclassname,enclassname",$productclassarray,0);
		$this->assign('productclassarray', $productclassarray);

		//价格条款
		$pricetermlist=db('bts_clause')->order('id asc')->select();
		$pricetermarray=fieldtoarray($pricetermlist,$list['language'].'name','');

		$this->assign('pricetermarray', $pricetermarray);

		//付款条件
		$paymentlist=db('bts_payment')->order('id asc')->select();
		$paymentarray=fieldtoarray($paymentlist,$list['language'].'name','');
		$this->assign('paymentarray', $paymentarray);


		$this->assign('no',$no);
		$this->assign('field_item_var',$field_item_var);
		$this->assign('field_item_text',$field_item_text);
		
		return $this->fetch();
	}
	public function quotation_show()
	{
		$id =input('id');
		$rs = db('bts_quotation')->where('id',$id)->find();
		$itemarr = db("bts_quotation_item")->where('no = "'.$rs['no'].'" and rno = "'.$rs['rno'].'"')->order('productorderid asc')->select();
		//dump(Db::table('bts_quotation_item')->getLastsql());
		$source = db('bts_source')->where('no',$rs['source'])->find();//客户来源
		$this->assign('rs',$rs);
		$this->assign('source',$source);
		$this->assign('itemarr',$itemarr);
		return $this->fetch();
	}
}
