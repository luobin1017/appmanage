<?php
namespace app\tms_business\controller;
use think\Controller;
use think\Db;

class Debitnote extends Controller
{
	//DebitNote 借项清单
    public function index()
    {
		//用户列表
		$userlist=db('bts_userlist')->order('nouse asc,username asc')->select();
		$userarray=fieldtoarray($userlist,'username','');
		$this->assign('userarray', $userarray);

		if(input("seeAll")==1){
			header("Location:".url('debitnote/index'));
		}
		//搜索条件
		$sqlwhere=array("","");
		$allno=input("allno");
		$sqlwhere=sqlwhereand("单据编号",$sqlwhere,"allno",$allno,1,1);
		$customname=input("customname");
		$sqlwhere=sqlwhereand("客户名称",$sqlwhere,"customname",$customname,1,1);
		$customid=input("customid");
		$sqlwhere=sqlwhereand("客户代码",$sqlwhere,"customid",$customid,1,1);
		$pino=input("pino");
		$sqlwhere=sqlwhereand("关联PI",$sqlwhere,"pino",$pino,1,1);
		$makedate1=input("makedate1");
		$makedate2=input("makedate2");
		$sqlwhere=sqlwheredate("日期",$sqlwhere,"makedate",$makedate1,$makedate2);
		$makename=input("makename");
		$sqlwhere=sqlwhereand("建档人",$sqlwhere,"makename",$makename,1,1);
		$received=input("received");
		$sqlwhere=sqlwherereceived("收款情况",$sqlwhere,"received",$received,"amount");
		$isxdxb=input("isxdxb");
		$sqlwhere=sqlwhereand("小额信保",$sqlwhere,"isxdxb",$isxdxb,0,0);

		$pagesize = 20;
		/*$subsql = Db::table('bts_product_class')->field('id as pid,classename')->buildSql();
		$list = Db::table("bts_debit_note")->alias('a')->Join([$subsql=>'b'],"b.pid=a.classid",'left')->where($sqlwhere[0])->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);*/
		$list = db('bts_debit_note')->where($sqlwhere[0])->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);
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
		$field_var='dballno,qno,rno,pino,pono,piid,poid,type,makedate,makename,customname,customid,customaddress,contact,phone,fax,email,currencys,itemid,productorderid,picture,num,unit,price,priceid,amount,bank,ischeck,isxdxb,isbank';
		$field_text='productinfo,bankinfo,content';
		$field_item_var='no_item,itemid,unit,picture,num,price,productorderid';
		$field_item_text='productinfo';

		$action=input('action');
		$no=input('no');
		$language='en';
		$data=$_POST;
		//dump($no);
		//新建单据
		if($action=="")
		{
			$no=newbasicno("temp");//获取temp表最大的NO
			$qno=newbasicno("debit_note",1);//获取表最大的no
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
			$data['currencys']= 'USD';
			$data['bankinfo']= '';
			$udata=fieldtotemp($data,$field_var,$field_text);//查询数据赋值给临时数据
			$udata['no']=$no;
			db("bts_temp")->insert($udata);
			gourl('?action=update&no='.$no);
		}
		else if($action=="modify"||$action=="import")//修改单据
		{
			$id=input('id');
			$rs = db("bts_debit_note")->where("id",$id)->select();
			if($data){showjserr("您要编辑的Debit_Note不存在！");}
			$data=$rs[0];
			$no=newbasicno("temp");
			$data['qno']=$data['no'];
			$data['dballno']=$data['allno'];
			$arr1 = strtoarray($data['pino'],',');
			$data['piid']=end($arr1);
			$arr1 = strtoarray($data['pono'],',');
			$data['poid']=end($arr1);
			$bankselect = db('bts_bank')->where('no',$data['bank'])->find();
			$data['bankinfo']=$bankselect['encontent'];
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
			$data = db("bts_debit_note_item")->where("allno",$data['allno'])->select();
			//mydump($data);
			//dump(Db::table('bts_debit_note_item')->getLastSql());
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
				$data['customname']=$rs[0][$language.'name'];
				$data['customaddress']=$rs[0][$language.'address'];
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
			if($piid!=''){
				$data['pino'].=','.$piid;
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
		//选择银行
		if($action=="selectbank")
		{
			$data['bankinfo']=input('bankinfo');
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
				//mydump($idata);
				$rs=db("bts_temp")->insert($idata);
			}
			$data['productorderid']='';
			$data['picture']='';
			$data['num']='';
			$data['price']='';
			$data['unit']='PCS';
			$data['productinfo']='';

			//dump($data);
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
			$data['price']=$temp['price'];
			$data['productorderid']=$temp['productorderid'];
			$data['productinfo']=$temp['productinfo'];
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
			$data['ischeck']= isset($data['ischeck'])?1:0;
			$data['isxdxb']= isset($data['isxdxb'])?1:0;
			$data['isbank']= isset($data['isbank'])?1:0;
			$idata=setsubmitdata("bts_debit_note",$data);
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
					$idata['rno']=newtabler("bts_debit_note","no like '".$data['qno']."'");
				}
				else
				{
					$idata['no']=newbasicno("debit_note",1);
					$idata['rno']=0;
				}
				$idata['allno']=showtableno("debit note",$idata['no'],0,$idata['rno']);
				$allno = $idata['allno'];
				$idata['makedate']=now();
				db("bts_debit_note")->insert($idata);
			}
			else if($data['type']=="edit")
			{
				$idata['no']=$data['qno'];
				unset($idata['makedate']);
				db("bts_debit_note")->where('allno',$data['dballno'])->update($idata);
				db("bts_debit_note_item")->where('allno',$data['dballno'])->delete();
			}
			db("bts_temp")->where("no",$no)->delete();
			//添加细项
			for($i=0;$i<count($itemrs);$i++)
			{
				$listitem=tempfield($itemrs[$i],$field_item_var,$field_item_text);
				//dump($listitem);
				$lidata=setsubmitdata("bts_debit_note_item",$listitem);
				//dump($lidata);
				//exit();
				//mydump($data);
				$lidata['allno']=$allno;
				$lidata['no']=$idata['no'];
				$lidata['rno']=$idata['rno'];
				$lidata['time']=now();
				//mydump($lidata);
				db("bts_debit_note_item")->insert($lidata);
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

		//货币类型
		$currencyslist=db('bts_currency')->order('no desc')->select();
		$currencysarray=fieldtoarray($currencyslist,'currency','');
		$this->assign('currencysarray', $currencysarray);

		//用户列表
		$userlist=db('bts_userlist')->order('nouse asc,username asc')->select();
		$userarray=fieldtoarray($userlist,'username','');
		$this->assign('userarray', $userarray);

		//银行下拉列表
		$banklist = db('bts_bank')->where('iscancel',0)->select();
		$nybank = db('bts_bank')->where('iscancel',0)->limit(1)->select();
		$bankarray=fieldtoarray($banklist,'name','no');
		$this->assign('bankarray', $bankarray);
		$this->assign('nybank', $nybank);

		$this->assign('no',$no);
		$this->assign('field_item_var',$field_item_var);
		$this->assign('field_item_text',$field_item_text);

		return $this->fetch();
	}
	//选择银行
	public function selectbankinfo()
	{
		$sid = $_POST['sid'];
		$newresult = db('bts_bank')->where("no",$sid)->find();
		return ["status"=>$newresult['encontent']];

	}
	//查看Debit Note
	public function db_show()
	{
		$id =input('id');
		$rs = db('bts_debit_note')->where('id',$id)->find();
		$itemarr = db("bts_debit_note_item")->where('no = "'.$rs['no'].'" and rno = "'.$rs['rno'].'"')->select();
		$banker = db('bts_bank')->where('no',$rs['bank'])->find();
		$this->assign('rs',$rs);
		$this->assign('source','');
		$this->assign('itemarr',$itemarr);
		$this->assign('banker',$banker);
		return $this->fetch();
	}

}
