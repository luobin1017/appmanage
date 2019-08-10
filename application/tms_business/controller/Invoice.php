<?php
namespace app\tms_business\controller;
use think\Controller;
use think\Db;

class Invoice extends Controller
{
	//发票
    public function index()
    {
		//用户列表
		$userlist=db('bts_userlist')->order('nouse asc,username asc')->select();
		$userarray=fieldtoarray($userlist,'username','');
		$this->assign('userarray', $userarray);

		if(input("seeAll")==1){
			header("Location:".url('invoice/index'));
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
		$makename=input("makename");
		$sqlwhere=sqlwhereand("建档人",$sqlwhere,"makename",$makename,1,1);
		$makedate1=input("makedate1");
		$makedate2=input("makedate2");
		$sqlwhere=sqlwheredate("日期",$sqlwhere,"makedate",$makedate1,$makedate2);

		$pagesize = 20;
		/*$subsql = Db::table('bts_product_class')->field('id as pid,classename')->buildSql();
		$list = Db::table("bts_invoice")->alias('a')->Join([$subsql=>'b'],"b.pid=a.classid",'left')->where($sqlwhere[0])->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);*/
		$list = db('bts_invoice')->where($sqlwhere[0])->order("makedate desc")->paginate($pagesize, false, ['query' => request()->param(),]);
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
		$field_var='inallno,qno,rno,no2,piid,pino,type,makedate,makename,customname,customid,customaddress,custompo,contact,phone,fax,email,currencys,priceterm,itemid,productorderid,picture,num,unit,price,priceid,amount,bank,visa,isbank,deposit';
		$field_text='productinfo,payment,bankinfo,content';
		$field_item_var='no_item,itemid,unit,picture,num,price,productorderid';
		$field_item_text='productinfo';

		$action=input('action');
		$no=input('no');
		$data=$_POST;
		$language = 'en';
		//dump($no);
		//新建单据
		if($action=="")
		{
			$no=newbasicno("temp");//获取temp表最大的NO
			$data['inallno']='';
			$data['qno']=0;
			$data['rno']=0;
			$data['no2']=0;
			$data['piid']='';
			$data['pino']=0;
			$data['type']='new';
			$data['itemid']=0;
			$data['makedate']=now();
			$data['makename']=Config('username');
			$data['currencys']= 'USD';
			$data['bankinfo']= '';
			//mydump($data);
			$udata=fieldtotemp($data,$field_var,$field_text);//查询数据赋值给临时数据
			$udata['no']=$no;
			db("bts_temp")->insert($udata);
			gourl('?action=update&no='.$no);
		}
		else if($action=="modify"||$action=="import")//修改单据
		{
			$id=input('id');
			$rs = db("bts_invoice")->where("id",$id)->select();
			if($data){showjserr("您要编辑的invoice不存在！");}
			$data=$rs[0];
			$no=newbasicno("temp");
			$data['qno']=$data['pino'];
			$data['inallno']=$data['allno'];
			/*$result = db('bts_setting_norule')->where('type',"PI")->find();
			$ex_name = $result['ex_name'];*/
			$data['piid']=$data['pino'];
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
			$data = db("bts_invoice_item")->where("allno",$data['inallno'])->select();
			//mydump($data);
			//dump(Db::table('bts_invoice_item')->getLastSql());
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
		//选择PI
		if($action=="selectpi")
		{
			//dump(input('piid'));
			//mydump($data);
			$piid=input('piid');
			$rs = db("bts_pi")->where("allno",$piid)->find();
			$arr = db("bts_invoice")->where("pino","like","%".$rs['no']."%")->order("no2 desc")->find();
			$result = db('bts_setting_norule')->where('type',"Invoice")->find();
			$ex_name = $result['ex_name'];
			if($arr){
				$data['inallno'] = $ex_name.$rs['no']."-0".($arr['no2']+1);
				$data['no2']=$arr['no2']+1;
				$data['qno']=$rs['no'];
			}else{
				$data['inallno'] = $ex_name.$rs['no']."-01";
				$data['no2']=1;
				$data['qno']=$rs['no'];
			}
			$data['customid']=$rs['customid'];
			$data['customname']=$rs['customname'];
			$data['customaddress']=$rs['address'];
			$data['contact']=$rs['contact'];
			$data['phone']=$rs['phone'];
			$data['fax']=$rs['fax'];
			$data['email']=$rs['email'];
			//添加细项
			$data1 = db("bts_pi_item")->where("allno",$rs['allno'])->select();
			//mydump($data);
			//dump(Db::table('bts_invoice_item')->getLastSql());
			//exit();
			foreach($data1 as $item)
			{
				$ino=newbasicno("temp");
				$item['no_item']=$no."_item";
				$idata1=fieldtotemp($item,$field_item_var,$field_item_text);
				//dump($idata);
				//exit();
				$item['no']=$ino;
				db("bts_temp")->insert($idata1);
			}
			$data['productorderid'] = '';
			//mydump($data);
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
			//dump($idata);
			if($data['itemid']!=""&&$data['itemid']!=0)
			{
				//mydump($idata);
				$rs = db("bts_temp")->where('id',$data['itemid'])->update($idata);
				//mydump($rs);
			}
			else
			{
				//mydump($idata);
				$rs=db("bts_temp")->insert($idata);
			}
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
			//去除非必要的字段
			$data['visa']= isset($data['visa'])?1:0;
			$data['isbank']= isset($data['isbank'])?1:0;
			//$data['no']=$data['qno'];
			//mydump($data);
			$idata=setsubmitdata("bts_invoice",$data);
			//mydump($idata);
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
					$idata['pino']=$data['qno'];
					$idata['rno']=newtabler("bts_invoice","allno = '".$data['inallno']."'");
				}
				else
				{
					//mydump($idata);
					$idata['rno']=0;
					$idata['pino']=$data['qno'];
					$idata['no']=0;
					//dump($idata);
				}
				$idata['allno'] = showtableno("invoice",$idata['pino'],$idata['no2'],$idata['rno']);
				$idata['makedate']=now();
				db("bts_invoice")->insert($idata);
			}
			else if($data['type']=="edit")
			{
				//mydump($idata);
				$idata['allno']=$data['inallno'];
				unset($idata['makedate']);
				db("bts_invoice")->where('allno',$data['inallno'])->update($idata);
				db("bts_invoice_item")->where('allno',$data['inallno'])->delete();
			}
			db("bts_temp")->where("no",$no)->delete();
			//添加细项
			for($i=0;$i<count($itemrs);$i++)
			{
				$listitem=tempfield($itemrs[$i],$field_item_var,$field_item_text);
				//dump($listitem);
				$lidata=setsubmitdata("bts_invoice_item",$listitem);
				//mydump($idata);
				//exit();
				$lidata['allno']=$idata['allno'];
				$lidata['no']=$idata['pino'];
				$lidata['rno']=$idata['rno'];
				$lidata['time']=now();
				//mydump($lidata);
				db("bts_invoice_item")->insert($lidata);
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
			//mydump($udata);
			gourl('?action=update&no='.$no);
		}
		//读取TEMP表主项
		$rs = db("bts_temp")->where("no",$no)->select();
		$list=tempfield($rs[0],$field_var,$field_text);
		$this->assign('v', $list);
		//mydump($list);
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

		//货币类型
		$currencyslist=db('bts_currency')->order('no desc')->select();
		$currencysarray=fieldtoarray($currencyslist,'currency','');
		$this->assign('currencysarray', $currencysarray);

		//用户列表
		$userlist=db('bts_userlist')->order('nouse asc,username asc')->select();
		$userarray=fieldtoarray($userlist,'username','');
		$this->assign('userarray', $userarray);

		//价格条款
		$pricetermlist=db('bts_clause')->order('id asc')->select();
		$pricetermarray=fieldtoarray($pricetermlist,$language.'name','');
		$this->assign('pricetermarray', $pricetermarray);

		//付款条件
		$paymentlist=db('bts_payment')->order('id asc')->select();
		$paymentarray=fieldtoarray($paymentlist,$language.'name','');
		$this->assign('paymentarray', $paymentarray);

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
		return ["status"=>$newresult['content']];

	}
	//查看Invoice
	public function invoice_show()
	{
		$id =input('id');
		$rs = db('bts_invoice')->where('id',$id)->find();
		$itemarr = db("bts_invoice_item")->where('allno',$rs['allno'])->select();
		$banker = db('bts_bank')->where('no',$rs['bank'])->find();
		$this->assign('rs',$rs);
		$this->assign('source','');
		$this->assign('itemarr',$itemarr);
		$this->assign('banker',$banker);
		return $this->fetch();
	}

}
