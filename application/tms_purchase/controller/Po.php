<?php
namespace app\tms_purchase\controller;
use think\Controller;
use think\Validate;
use think\Db;

class Po extends Controller
{
	//Po主页
	public function index()
	{
		//checkpermissions('0601',true);
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
		$rs = db("bts_po")->where($sqlwhere[0])->order("makedate desc")->limit(0,20)->paginate($pagesize,false,['query' => request()->param()]);
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
	public function edit()
	{
		$field_var='pno,rno,no2,pino,pono,type,potype,makedate,makename,suppliersid,piid,suppliersname,suppliersaddress,contact,phone,fax,email,currencys,deliverymethod,productorderid,feetype,itemid,simeid,seminame,semiinfo,picture,packing,num,unit,price,feetype,priceid,count,amount,ismain,isonetouch,istaoyue';
		$field_text='payment,delivery,inspection,address,content,usecontent,mark2,markpicture2,side2,sidepicture2,mark,markpicture,side,sidepicture';
		$field_item_var='no_item,itemid,seminame,productorderid,unit,picture,num,price,feetype';
		$field_item_text='semiinfo,packing';
		$action=input('action');
		$no=input('no');
		$data=$_POST;
		//dump($no);
		//新建单据
		if($action=="")
		{
			$no=newbasicno("temp");//获取temp表最大的NO
			$data['pno']='';
			$data['rno']=0;
			$data['no2']=0;
			$data['pino']=0;
			$data['pono']=0;
			$data['type']='new';
			$data['itemid']='';
			$data['piid']='';
			$data['makedate']=now();
			$data['makename']=Config('username');
			$data['currencys']= 'RMB';
			$udata=fieldtotemp($data,$field_var,$field_text);//查询数据赋值给临时数据
			$udata['no']=$no;
			db("bts_temp")->insert($udata);
			gourl('?action=update&no='.$no);
		}
		else if($action=="modify"||$action=="import")//修改单据
		{
			$id=input('id');
			$rs = db("bts_po")->where("id",$id)->select();
			//mydump($rs);
			if($data){showjserr("您要编辑的PO不存在！");}
			$data=$rs[0];
			$no=newbasicno("temp");
			if($action=="modify")
			{
				$data['type']='edit';
			}
			else
			{
				$data['type']='new';
			}
			$udata['makename']=Config('username');
			$data['pno']=$data['allno'];
			$data['pono'] = $data['no'];
			$udata=fieldtotemp($data,$field_var,$field_text);
			$udata['no']=$no;
			//dump($data);
			//mydump($udata);
			db("bts_temp")->insert($udata);
			$data = db("bts_po_item")->where("allno",$data['allno'])->select();
			//dump(Db::table('bts_po_item')->getLastSql());
			//exit();
			foreach($data as $item)
			{
				$ino=newbasicno("temp");
				$item['no_item']=$no."_item";
				$idata=fieldtotemp($item,$field_item_var,$field_item_text);
				//dump($idata);
				//exit();
				$item['no']=$ino;
				db("bts_temp")->insert($idata);
			}
			gourl("?action=update&no=".$no);
		}
		//选择代码类型(样品POSZ XXXXXXXXXXXX)
		if($action=="selectpnotype")
		{
			$piid=input('piid');
			$data['pno']=$piid;
			$data['allno']=$piid;
			$data['no2']=0;
			$data['pino']=0;
			$data['szno']=substr($data['allno'],strpos($data['allno'],'Z')+1);
		}

		//选择PI
		if($action=="selectpi")
		{
			//mydump(input());
			$piid=input('piid');
			$rs = db("bts_pi")->where("allno",$piid)->select();
			$arr = db("bts_po")->where("allno","like","%"."PO".$rs[0]['no']."%")->order("no2 desc")->find();
			//mydump(Db::table("bts_po")->getLastSql());
			if($arr){
				$piid = "PO".$rs[0]['no']."-0".($arr['no2']+1);
				$data['no2']=$arr['no2']+1;
				$data['pino']=$rs[0]['no'];
			}else{
				$piid = "PO".$rs[0]['no']."-01";
				$data['no2']=1;
				$data['pino']=$rs[0]['no'];
			}
			$data['pno']=$piid;
			//mydump($data);
		}
		//选择供应商
		if($action=="selectsuppliers")
		{
			//mydump(input());
			$suppliersid=input('suppliersid');
			$rs = db("bts_suppliers")->where("allno",$suppliersid)->select();
			//mydump($rs);
			if($rs)
			{
				$data['suppliersid']=$rs[0]['allno'];
				$data['suppliersname']=$rs[0]['name'];
				$data['suppliersaddress']=$rs[0]['address'];
				$data['contact']=$rs[0]['contact1'];
				$data['phone']=$rs[0]['phone1'];
				$data['fax']=$rs[0]['fax'];
				$data['email']=$rs[0]['email1'];
				//mydump($data);
			}
		}

		//添加、更新细项
		if($action=="add")
		{
			/*$field_item_var='no_item,itemid,simename,unit,picture,num,price,feetype';
			$field_item_text='semiinfo,packing';*/
			$idata=fieldtotemp($data,$field_item_var,$field_item_text);
			//dump($data);
			$idata['no']=newbasicno("temp");
			$idata['str1']=$no."_item";
			//mydump($idata);
			if($data['itemid']!="")
			{
				$rs = db("bts_temp")->where('id',$data['itemid'])->update($idata);
				//mydump($rs);
			}
			else
			{
				$rs=db("bts_temp")->insert($idata);
			}
			$data['seminame']='';
			$data['semiinfo']='';
			$data['productorderid']='';
			$data['packing'] = '';
			$data['picture']='';
			$data['num']='';
			$data['price']='';
			$data['unit']='PCS';
			//dump($data);
		}
		//编辑细项
		if($action=="edit")
		{
			$data['itemid']=$_GET['itemid'];
			$rs=db("bts_temp")->where('id',$data['itemid'])->select();
			$temp=tempfield($rs[0],$field_item_var,$field_item_text);
			$data['seminame']=$temp['seminame'];
			$data['unit']=$temp['unit'];
			$data['semiinfo']=$temp['semiinfo'];
			$data['packing']=$temp['packing'];
			$data['picture']=$temp['picture'];
			$data['num']=$temp['num'];
			$data['price']=$temp['price'];
			$data['feetype']=$temp['feetype'];
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
			//($data);
			//去除非必要的字段
			//dump($data);
			$data['ismain']= isset($data['ismain'])?1:0;
			$data['isonetouch']= isset($data['isonetouch'])?1:0;
			$data['istaoyue']= isset($data['istaoyue'])?1:0;
			$idata=setsubmitdata("bts_po",$data);
			$idata['no'] = $data['pono'];
			if($data['type']=="r"){ $idata['rno']=((int)$idata['rno'])+1;}
			//读取临时表细项
			$itemrs = db("bts_temp")->field('*,cast(str2 as signed) orderid')->where("str1",$no."_item")->order("orderid asc")->select();
			//mydump($itemrs);
			if(count($itemrs)<=0)
			{
				showjserr("您还未添加细项!");
			}
			//添加或修改主表
			if($data['type']=="new"||$data['type']=='r')
			{
				if($data['type']=='r')
				{
					if(preg_match("/^[a-zA-Z\s]+$/",substr($data['pno'],0,4))){
						$idata['rno']=newtabler("bts_po","no = '".$data['pono']."'");
						$idata['allno'] = showtableno("po",$idata['no'],$idata['no2'],$idata['rno']);
						//mydump($idata);
					}else{
						$idata['rno']=newtabler("bts_po","pino = '".$data['pino']."' and no2='".$data['no2']."'");
						$idata['allno'] = showtableno("po",$idata['pino'],$idata['no2'],$idata['rno']);
					}
				}
				else
				{
					$idata['no']=newbasicno("po");
					if(preg_match("/^[a-zA-Z\s]+$/",substr($data['pno'],0,4))){
						//dump(preg_match("/^[a-zA-Z\s]+$/",substr($data['pno'],0,4))); POSZ
						$idata['rno']=0;
						$idata['no']=newbasicno("po",1);
						$idata['pino']=0;
						$idata['allno'] = showtableno("po",$idata['no'],$idata['no2'],$idata['rno']);
					}else{
						$idata['rno']=0;
						$idata['pino']=$data['pino'];
						$idata['no']=0;
						$idata['allno'] = showtableno("po",$idata['pino'],$idata['no2'],$idata['rno']);
					}
				}
				$idata['makedate']=now();
				//mydump($idata);
				db("bts_po")->insert($idata);
			}
			else if($data['type']=="edit")
			{
				//dump($data);
				//mydump($idata);
				//$idata['no']=$data['pno'];
				unset($idata['makedate']);
				db("bts_po")->where('allno',$idata['allno'])->update($idata);
				db("bts_po_item")->where('allno',$data['allno'])->delete();
			}
			db("bts_temp")->where("no",$no)->delete();
			//添加细项
			for($i=0;$i<count($itemrs);$i++)
			{
				$listitem=tempfield($itemrs[$i],$field_item_var,$field_item_text);
				//dump($listitem);
				$lidata=setsubmitdata("bts_po_item",$listitem);
				//dump($lidata);
				//exit();
				$lidata['allno']=$idata['allno'];
				$lidata['time']=now();
				db("bts_po_item")->insert($lidata);
			}
			db("bts_temp")->where("str1",$no."_item")->delete();

			gourl("../");
		}
		//读取数据库操作
		if($action!="update")
		{
			//dump($data);
			$udata=fieldtotemp($data,$field_var,$field_text);
			//mydump($udata);
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
		//mydump($listitem);
		$this->assign('listitem', $listitem);

		//货币类型
		$currencyslist=db('bts_currency')->order('no desc')->select();
		$currencysarray=fieldtoarray($currencyslist,'currency','');
		$this->assign('currencysarray', $currencysarray);

		//价格条款
		$pricetermlist=db('bts_clause')->order('id asc')->select();
		$pricetermarray=fieldtoarray($pricetermlist,'name','');
		$this->assign('pricetermarray', $pricetermarray);

		//付款条件
		$paymentlist=db('bts_payment')->order('id asc')->select();
		$paymentarray=fieldtoarray($paymentlist,'name','');
		$this->assign('paymentarray', $paymentarray);

		//用户列表
		$userlist=db('bts_userlist')->order('nouse asc,username asc')->select();
		$userarray=fieldtoarray($userlist,'username','');
		$this->assign('userarray', $userarray);

		$this->assign('no',$no);
		$this->assign('field_item_var',$field_item_var);
		$this->assign('field_item_text',$field_item_text);

		return $this->fetch();
	}
	//选择PI(POXXXXXXX) 或者选择样品(POSZXXXXXXX)
	public function selectpi()
	{
		$sid = $_POST['sid'];
		$result = db('bts_setting_norule')->where('type',"PO")->find();
		$ex_name = $result['ex_name']."SZ";
		if($sid == 2){
			$newresult = db('bts_po')->where("no","<>",0)->order('allno desc')->limit(1)->find();
			return ["status"=>$ex_name.($newresult['no']+1)];
		}

	}
	public function po_show()
	{
		$id =input('id');
		$rs = db('bts_po')->where('id',$id)->find();
		$itemarr = db("bts_po_item")->where('allno = "'.$rs['allno'].'"')->select();
		//dump(Db::table('bts_quotation_item')->getLastsql());
		$this->assign('rs',$rs);
		$this->assign('itemarr',$itemarr);
		return $this->fetch();
	}
}