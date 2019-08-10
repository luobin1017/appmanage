<?php
namespace app\tms_setting\controller;
use think\Controller;
use think\Db;

class Mytool extends Controller
{
    public function index()
    {
		checkpermissions('0001',true);
		return $this->fetch();
    }
	
	//改变日期类型
	public function changdateformat()
	{
		//列出所有表
		$db=db()->query('show tables');
		for($i=0;$i<count($db);$i++)
		{
			//表名
			$tablename=$db[$i]['Tables_in_emoribts'];
			echo "<br>".$tablename."<br>";
			
			//列出所有字段
			$sql="SHOW FULL COLUMNS FROM `".$tablename."`";
			$rs = db()->query($sql);
			//mydump($rs);
			foreach($rs as $v)
			{
				$fieldname=$v['Field'];
				$type=$v['Type'];
				//如果字段类型为timestamp(6)则替换成指定类型
				if($type=='timestamp(6)')
				{
					$sql="ALTER TABLE `".$tablename."` CHANGE `".$fieldname."` `".$fieldname."` DATETIME NULL DEFAULT NULL;";
					db()->query($sql);
					echo $tablename."-".$fieldname." Changed<br>";
					flush();
				}
				//echo $fieldname."<br>";
			}
		}
	}
	
	//自动生成所有产品的petion
	public function makepetion()
	{
		return "";
		$rs = db("bts_product")->where("id>407")->select();
		//mydump($rs);
		foreach($rs as $v)
		{
			$cnpdetion=$v['cnpdetion'];
			if($cnpdetion=="")
			{
				$material=$v['material'];
				$size=$v['itemsize'];
				$imprint=$v['imprint'];
				$packaing=$v['packaging'];
				$carton=$v['cartonsize'];
				$grow=$v['grossweight'];
				$other=$v['other'];
				
				$text="Material: ".$material."\r\n";
				$text.="Size: ".$size."\r\n";
				$text.="Imprint: ".$imprint."\r\n";
				$text.="Packing: ".$packaing."\r\n";
				$text.="Carton: ".$carton."\r\n";
				$text.="G.W.: ".$grow." kgs";
				if($other!="")$text.="\r\nOther: ".$other;
				//$data['enpdetion']=$text;
				$data['cnpdetion']="";
				db("bts_product")->where("id",$v['id'])->update($data);
			}
		}
	}
	
	//将Quotation Item项目转换到新系统
	public function changquotationitem()
	{
		return "";
		$rs = db("bts_quotation")->order("id asc")->select();
		foreach($rs as $v)
		{
			/*
			//更新Customid
			$len=strlen($v['customid']);
			$str='KH00000';
			$newno=substr($str,0,7-$len).$v['customid'];
			echo $v['customid'].":".$newno."<br>";
			$v['customid']=$newno;
			db("bts_quotation1")->where('id='.$v['id'])->update($v);
			*/
			
			$qrs=db("bts_quotation_item1")->where("no like '".$v['no']."' and rno like '".$v['rno']."'")->order("productorderid asc,product asc,num asc")->select();
			//dump($qrs);
			$temp="";
			$oldv['product']='';
			foreach($qrs as $vv)
			{
				$vv['allno']=showtableno("quotation",$vv['no'],0,$vv['rno']);
				if($oldv['product']!=""&&$oldv['product']!=$vv['product'])
				{
					echo $oldv['product'].":".$vv['product'].'<br>';
					unset($oldv['id']);
					$oldv['numprice']=$temp;
					db("bts_quotation_item")->insert($oldv);
					$temp="";
				}
				else if($oldv['product']!="")
				{
					$temp.="|";
				}
				$oldv=$vv;
				$temp.=$vv['num'].",".$vv['price'];
			}
			if($temp!="")
			{
				unset($oldv['id']);
				$oldv['numprice']=$temp;
				db("bts_quotation_item")->insert($oldv);
			}
			
		}
	}
	
	public function createquotationitemno()
	{
		$rs = db("bts_quotation_item")->order("id asc")->select();
		foreach($rs as $v)
		{
			$v['allno']=showtableno("quotation",$v['no'],0,$v['rno']);
			db("bts_quotation_item")->where('id='.$v['id'])->update($v);
		}
	}
}
