<?php
namespace app\tms_business\controller;
use think\Controller;
use think\Db;

class Packinglist extends Controller
{
	//Deliverynote
    public function index()
    {
		//用户列表
		$userlist=db('bts_userlist')->order('nouse asc,username asc')->select();
		$userarray=fieldtoarray($userlist,'username','');
		$this->assign('userarray', $userarray);

		if(input("seeAll")==1){
			header("Location:".url('packinglist/index'));
		}
		//搜索条件
		$sqlwhere=array("","");
		$allno=input("allno");
		$sqlwhere=sqlwhereand("单据编号",$sqlwhere,"allno",$allno,1,1);
		$customname=input("customname");
		$sqlwhere=sqlwhereand("客户名称",$sqlwhere,"customname",$customname,1,1);
		$customid=input("customid");
		$sqlwhere=sqlwhereand("客户代码",$sqlwhere,"customid",$customid,1,1);
		$makename=input("makename");
		$sqlwhere=sqlwhereand("建档人",$sqlwhere,"makename",$makename,1,1);
		$makedate1=input("makedate1");
		$makedate2=input("makedate2");
		$sqlwhere=sqlwheredate("日期",$sqlwhere,"makedate",$makedate1,$makedate2);


		$pagesize = 20;
		/*$subsql = Db::table('bts_product_class')->field('id as pid,classename')->buildSql();
		$list = Db::table("bts_delivery_note")->alias('a')->Join([$subsql=>'b'],"b.pid=a.classid",'left')->where($sqlwhere[0])->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);*/
		$list = db('bts_packing')->where($sqlwhere[0])->order("makedate desc")->paginate($pagesize, false, ['query' => request()->param(),]);
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

	//添加、修改
	public function edit()
	{
		$field_var='dballno,qno,rno,pino,inno,piid,invoiceid,type,makedate,makename,customname,customid,customaddress,contact,orderno,phone,fax,email,itemid,productorderid,markpicture';
		$field_text='content,productmark,productctn,productdes,productnum,productnw,productgw,productmea';
		$field_item_var='no_item,itemid,productorderid,markpicture';
		$field_item_text='productmark,productctn,productdes,productnum,productnw,productgw,productmea';

		$action=input('action');
		$no=input('no');
		$data=$_POST;
		//dump($no);
		//新建单据
		if($action=="")
		{
			$no=newbasicno("temp");//获取temp表最大的NO
			$qno=newbasicno("credit_note",1);//获取表最大的no
			$data['dballno']='';
			$data['qno']=$qno;
			$data['rno']=0;
			$data['pino']='';
			$data['pono']='';
			$data['piid']='';
			$data['poid']='';
			$data['type']='new';
			$data['itemid']=0;
			$data['makedate']=now();
			$data['makename']=Config('username');
			$udata=fieldtotemp($data,$field_var,$field_text);//查询数据赋值给临时数据
			$udata['no']=$no;
			db("bts_temp")->insert($udata);
			gourl('?action=update&no='.$no);
		}
		else if($action=="modify"||$action=="import")//修改单据
		{
			$id=input('id');
			$rs = db("bts_packing")->where("id",$id)->select();
			if($data){showjserr("您要编辑的Packing List不存在！");}
			$data=$rs[0];
			$no=newbasicno("temp");
			$data['qno']=$data['no'];
			$data['dballno']=$data['allno'];
			$data['piid']=$data['pino'];
			$data['invoiceid']=$data['inno'];
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
			$data = db("bts_packing_item")->where("allno",$data['allno'])->select();
			//mydump($data);
			//dump(Db::table('bts_credit_note_item')->getLastSql());
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
		//选择客户
		if($action=="selectcustom")
		{
			$customid=input('customid');
			$rs = db("bts_custom")->where("allno",$customid)->select();
			if($rs)
			{
				$data['customid']=$rs[0]['allno'];
				$data['customname']=$rs[0]['ename'];
				$data['customaddress']=$rs[0]['address'];
				$data['contact']=$rs[0]['contact1'];
				$data['phone']=$rs[0]['phone1'];
				$data['fax']=$rs[0]['fax'];
				$data['email']=$rs[0]['email1'];
			}
		}
		//选择供应商
		if($action=="selectsuppliers")
		{
			$customid=input('suppliersid');
			$rs = db("bts_suppliers")->where("allno",$customid)->select();
			if($rs)
			{
				$data['customid']=$rs[0]['allno'];
				$data['customname']=$rs[0]['ename'];
				$data['customaddress']=$rs[0]['address'];
				$data['contact']=$rs[0]['contact1'];
				$data['phone']=$rs[0]['phone1'];
				$data['fax']=$rs[0]['fax'];
				$data['email']=$rs[0]['email1'];
			}
		}
		//选择PI
		if($action=="selectpi")
		{
			$piid=input('piid');
			$data['piid']=$piid;
			$data['pino']=$piid;
			$rs = db("bts_pi")->where("allno",$piid)->select();
			if($rs)
			{
				$data['customid']=$rs[0]['customid'];
				$data['customname']=$rs[0]['customname'];
				$data['customaddress']=$rs[0]['customaddress'];
				$data['contact']=$rs[0]['contact'];
				$data['phone']=$rs[0]['phone'];
				$data['fax']=$rs[0]['fax'];
				$data['email']=$rs[0]['email'];
			}
			//mydump($data);
		}
		//选择PO
		if($action=="selectpo")
		{
			//mydump(input());
			$poid=input('poid');
			$data['poid']=$poid;
			if($poid!=''){
				$data['pono'].=','.$poid;
			}
			//mydump($data);
		}
		//重新选择pi po
		if($action=="reselect")
		{
			//mydump($data);
			$data['piid']='';
			$data['poid']='';
			$data['pino']='';
			$data['pono']='';
		}
		//添加、更新细项
		if($action=="add")
		{
			/*$field_item_var='no_item,itemid,unit,picture,num,price';
			$field_item_text='productinfo';*/
			$idata=fieldtotemp($data,$field_item_var,$field_item_text);
			$idata['no']=newbasicno("temp");
			$idata['str1']=$no."_item";
			//dump($data['itemid']);
			//mydump($idata);
			if($data['itemid']!=""&&$data['itemid']!=0)
			{
				$rs = db("bts_temp")->where('id',$data['itemid'])->update($idata);
				//mydump($rs);
			}
			else
			{
				/*$field_item_var='no_item,itemid,markpicture,productorderid';
		$field_item_text='productmark,productctn,productdes,productnum,productnw,productgw,productmea';*/
				//mydump($idata);
				$rs=db("bts_temp")->insert($idata);
			}
			$data['markpicture']='';
			$data['productorderid']='';
			$data['productmark']='';
			$data['productctn']='';
			$data['productdes']='';
			$data['productnum']='';
			$data['productnw']='';
			$data['productgw']='';
			$data['productmea']='';
		}
		//编辑细项
		if($action=="edit")
		{
			/*$field_item_var='no_item,itemid,unit,picture,num,price';
			$field_item_text='productinfo';*/
			$data['itemid']=$_GET['itemid'];
			$rs=db("bts_temp")->where('id',$data['itemid'])->select();
			$temp=tempfield($rs[0],$field_item_var,$field_item_text);
			$data['unit']=$temp['unit'];
			$data['picture']=$temp['picture'];
			$data['num']=$temp['num'];
			$data['productorderid']=$temp['productorderid'];
			$data['productref']=$temp['productref'];
			$data['productdes']=$temp['productdes'];
			$data['productctn']=$temp['productctn'];
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
			$allno = $data['dballno'];
			$idata=setsubmitdata("bts_packing",$data);
			$idata['inno'] = $data['invoiceid'];
			//mydump($idata);
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
					$idata['rno']=newtabler("bts_packing","no like '".$data['qno']."'");
				}
				else
				{
					$idata['no']=newbasicno("packing",1);
					$idata['rno']=0;
				}
				$idata['allno']=showtableno("Packlist",$idata['no'],0,$idata['rno']);
				$allno = $idata['allno'];
				$idata['makedate']=now();
				//mydump($idata);
				db("bts_packing")->insert($idata);
			}
			else if($data['type']=="edit")
			{
				$idata['no']=$data['qno'];
				unset($idata['makedate']);
				//mydump($data);
				db("bts_packing")->where('allno',$data['dballno'])->update($idata);
				db("bts_packing_item")->where('allno',$data['dballno'])->delete();
			}
			db("bts_temp")->where("no",$no)->delete();
			//添加细项
			for($i=0;$i<count($itemrs);$i++)
			{
				$listitem=tempfield($itemrs[$i],$field_item_var,$field_item_text);
				//dump($listitem);
				$lidata=setsubmitdata("bts_packing_item",$listitem);
				//dump($lidata);
				//exit();
				//mydump($data);
				$lidata['allno']=$allno;
				$lidata['no']=$idata['no'];
				$lidata['rno']=$idata['rno'];
				$lidata['time']=now();
				//mydump($lidata);
				db("bts_packing_item")->insert($lidata);
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
		//mydump($itemrs);
		$listitem=array();
		for($i=0;$i<count($itemrs);$i++)
		{
			$listitem[$i]=tempfield($itemrs[$i],$field_item_var,$field_item_text);
			$listitem[$i]['id']=$itemrs[$i]['id'];
		}
		$this->assign('listitem', $listitem);

		//用户列表
		$userlist=db('bts_userlist')->order('nouse asc,username asc')->select();
		$userarray=fieldtoarray($userlist,'username','');
		$this->assign('userarray', $userarray);

		$this->assign('no',$no);
		$this->assign('field_item_var',$field_item_var);
		$this->assign('field_item_text',$field_item_text);

		return $this->fetch();
	}
	//查看delivery note
	public function pl_show()
	{
		$id =input('id');
		$rs = db('bts_packing')->where('id',$id)->find();
		$itemarr = db("bts_packing_item")->where('no = "'.$rs['no'].'" and rno = "'.$rs['rno'].'"')->select();
		$this->assign('rs',$rs);
		$this->assign('itemarr',$itemarr);
		return $this->fetch();
	}

}
