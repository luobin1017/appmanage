<?php
namespace app\tms_select\controller;
use think\Controller;
use think\Db;

class Price extends Controller
{
	
	//选择价格
	public function index()
	{
		$pno = input("pno");
		if($pno == ''){
			exit("<h3>请先选择产品！<h3>");
		}
		$pricetermlist = db('bts_product_price')->where('productid',$pno)->group("priceterm")->order('priceterm asc')->select();
		$pricetermarray=fieldtoarray($pricetermlist,'priceterm','');
		$this->assign('pricetermarray', $pricetermarray);
		
		$priceprintlist = db('bts_product_price')->where('productid',$pno)->group("priceprint")->order('priceprint asc')->select();
		$priceprintarray=fieldtoarray($priceprintlist,'priceprint','');
		$this->assign('priceprintarray', $priceprintarray);

		//搜索条件
		$sqlwhere=array("","");
		$sqlwhere=sqlwhereand("",$sqlwhere,"productid",$pno,1,0);
		$priceterm=input('priceterm');
		if($priceterm==""&&count($pricetermlist)>=0){ $priceterm=$pricetermlist[0]['priceterm'];}
		$sqlwhere=sqlwhereand("",$sqlwhere,"priceterm",$priceterm,1,0);
		$priceprint=input('priceprint');
		if($priceprint==""&&count($priceprintlist)>0){ $priceprint=$priceprintlist[0]['priceprint'];}
		$sqlwhere=sqlwhereand("",$sqlwhere,"priceprint",$priceprint,1,0);
		
		$list = db('bts_product_price')->where($sqlwhere[0])->order('currencys desc,num asc')->select();
		//echo($sqlwhere[0]);
		$this->assign('list',$list);
		return $this->fetch();
	}

}
