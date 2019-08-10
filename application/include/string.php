<?php
//获得当前时间
function now()
{
	return date('Y-m-d H:i:s',time());
}

//将数据库字符进行转义
function coder($str)
{
	$result="";
	for($i=0;$i<strlen($str);$i++)
	{
		$v=substr($str,$i,1);
		if($v=="<")$result.="&lt;";
		else if($v==">")$result.="&gt;";
		else if($v==chr(34))$result.="&quot;";
		else if($v=="&")$result.="&amp;";
		else if($v==chr(13))$result.="<br>";
		else if($v==chr(9))$result.="&nbsp; &nbsp; ";
		else if($v==chr(32))$result.="&nbsp;";
		else if($v=="'")$result.="&#39;";
		else $result.=$v;
	}
	return $result;
}

//在字符串str中查找开头为str1，结束为str2的字符串，type为1时包含查找字符，type为2是不包含查找字符
function strcut($str, $str1, $str2, $type)
{
	$tempstr = $str;
	//字符串开始位置
	$a = strpos($str , $str1);
	//字符串结束位置
	$b = strpos($str , $str2, $a + strlen($str1));
	if ($a ===false || $b ===false)
	{
		$tempstr = "";
	}
	else
	{
		if ($type == 1) { $tempstr = substr($str, $a, $b - $a + strlen($str2)); }
		else if ($type == 2) { $tempstr = substr($str, $a + strlen($str1), $b-$a-strlen($str1)); }
	}
	return $tempstr;
}

//获取远程文件
function geturl($url)
{
	$contents = file_get_contents($url);
	//获取源网页编码
	$encode  = mb_detect_encoding($contents,array("ASCII","UTF-8","GB2312","GBK","BIG5"));
	//获取当前网页编码
	$str="中国人";
	$objencode  = mb_detect_encoding($str,array("ASCII","UTF-8","GB2312","GBK","BIG5"));
	//将网页源码转换成当前网页编码
	$contents = iconv($encode, $objencode,$contents);
	return $contents;
}

//字符串是否为空
function isblank($str)
{
	if($str=='')return true;
	return false;
}

//字符串是否为数字
function isnumber($str)
{
	return is_numeric($str);
}

//字符串是否为邮箱
function isemail($str)
{
	if(!filter_var($str, FILTER_VALIDATE_EMAIL))return false;
	return true;
}

//首字母大写
function upname($str)
{
	$retval="";
	if(strlen($str)>0)
	{
		$retval=strtoupper(substr($str,0,1));
		if(strlen($str)>1)$retval.=strtolower(substr($str,1));
	}
	return $retval;
}

//去除字符串中除数字以外的部分
function cton($str)
{
	if($str=="")return 0;
	$result="";
	for($i=0;$i<strlen($str);$i++)
	{
		$j=substr($str,$i,1);
		if(isnumber($j)||$j==".")$result.=$j;
	}
	return (int)$result;
}

//将指定数字转换成指定长度的数字，前面补充0，一般用于编号
function numtochr($num,$numlen)
{
	$retval="0000000000000000000000000000".$num;
	return substr($retval,strlen($retval)-$numlen);
}

//缩略显示字符串
function strleft($text,$n)
{
	$len=strlen($text);
	$strbefore="";
	$retval="";
	for($i=0;$i<$len;$i++)
	{
		$a=substr($text,$i,1);
		$retval.=$a;
		if($i>=($n-3)&&$strbefore=="")$strbefore=$retval;
		if($i>=$n){ $retval=$strbefore."...";break;}
	}
	return $retval;
}

//将数字转换成3位一逗号的格式,不带小数部分
function str3x($z)
{
	return number_format($z);
}

//将数字转换成3位一逗号的格式,小数点四会五入
function strtocurr($z)
{
	return number_format($z,3);
}

//返回查询参数到URL模式，Page为翻页参数，此函数用于查询时页面中的翻页调用
function urlpara($page)
{
	$retval="";
	if(count($_POST)>0)
	{
		foreach($_POST as $index => $value)
		if($index!=$page&&$index!="submit")$retval.=$index."=".$value."&";
	}
	else
	{
		foreach($_GET as $index => $value)
		{
			if($index!=$page)$retval.=$index."=".$value."&";
		}
	}
	return $retval;
}

//跳转到指定网页str
function gourl($str)
{
	Header("HTTP/1.1 303 See Other");
	Header("Location:$str");
	exit;
}

//显示网址
function showurl($url){echo url($url);}

//顯示出錯信息，並返回上一頁
function showjserr($str)
{
	$html="<!DOCTYPE HTML><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /><title>错误提示 - 外贸管理系统</title></head><body>";
	$html.="<script>\r\n";
	$html.="alert(\"".$str."\");\r\n";
	$html.="history.back();\r\n";
	$html.="</script>\r\n";
	echo $html;
	exit(0);
}

//原始大图地址，缩略图宽度，高度，缩略图地址
function picsmall($big_img, $width, $height, $small_img)
{
	
	$imgage = getimagesize($big_img); //得到原始大图片
	switch ($imgage[2])
	{
		 // 图像类型判断
		case 1:
		$im = imagecreatefromgif($big_img);
		break;
		case 2:
		$im = imagecreatefromjpeg($big_img);
		break;
		case 3:
		$im = imagecreatefrompng($big_img);
		break;
	}
	$src_W = $imgage[0]; //获取大图片宽度
	$src_H = $imgage[1]; //获取大图片高度
	
	//取最小宽度的一边，另外一边按比例
	$ws=$src_W/$width;
	$hs=$src_H/$height;
	if($ws>$hs){ $height=$width/($src_W/$src_H);}
	else if($ws<$hs){ $width=$height/($src_H/$src_W);}
	
	$tn = imagecreatetruecolor($width, $height); //创建缩略图
	imagecopyresampled($tn, $im, 0, 0, 0, 0, $width, $height, $src_W, $src_H); //复制图像并改变大小
	imagejpeg($tn, $small_img); //输出图像
}

//获取Cookie
function getcookie($str)
{
	if(isset($_COOKIE[$str]))
	{
		return $_COOKIE[$str];		
	}
	else
	{
		return "";
	}

}

//获取系统变量
function sysinfo($name)
{
	return Config('sys_info')[$name];
}

//SQL查询连接,返回查询字符串
//fieldname查询字段,fieldname可以用逗号分隔的字符串，代表几个字段OR连接查询
//fieldvalue查询内容
//fieldtype字段类型，0数值型，1字符型,2判断是否大于0，3判断是否为空
//searchtype查询类型，0匹配查询，1模糊查询
function sqlwhereand($searchname,$sqlwhere,$fieldname,$fieldvalue,$fieldtype=0,$searchtype=0)
{
	$sqlstring="";
	if($sqlwhere[0]!=""&&$fieldvalue!="")
	{
		$sqlstring.=" and";
	}
	if($fieldvalue=="")return $sqlwhere;
	$fieldlist=explode(",",$fieldname);
	foreach($fieldlist as $k=>$fieldname)
	{
		if(count($fieldlist)>1&&$k==0)
		{
			$sqlstring.="(";
		}
		else if(count($fieldlist)>1)
		{
			$sqlstring.=" or";
		}	
		if($fieldvalue!="")
		{
			$sqlwhere[1].=" ".$searchname.":".$fieldvalue." ";
			if($fieldtype==0)//整型查询
			{
				$sqlstring.=' '.$fieldname.'='.$fieldvalue;
			}
			else if($fieldtype==1)//字符型查询
			{
				if($searchtype==0)
				{
					$sqlstring.=' '.$fieldname.' like "'.$fieldvalue.'"';
				}
				else
				{
					$sqlstring.=' '.$fieldname.' like "%'.$fieldvalue.'%"';
				}

			}
			else if($fieldtype==2)//判断字段是否大于零
			{
				if((int)$fieldvalue==1)
				{
					$sqlstring.=' '.$fieldname.' > 0 ';
				}
				else if((int)$fieldvalue==2)
				{
					$sqlstring.=' ('.$fieldname.' = 0 || '.$fieldname.' is null) ';
				}
			}
			else if($fieldtype==3)//判断字段是否为空
			{
				if((int)$fieldvalue==1)
				{
					$sqlstring.=' '.$fieldname.' is not null ';
				}
				else if((int)$fieldvalue==2)
				{
					$sqlstring.=' '.$fieldname.' is null ';
				}
			}
		}
		if(count($fieldlist)>1&&$k==count($fieldlist)-1)
		{
			$sqlstring.=")";
		}
		//查询类型为判断大小以及是否为空且值为零时，不设置搜索条件
		if(($fieldtype==2||$fieldtype==3)&&((int)$fieldvalue)==0)$sqlstring="";
	}
	$sqlwhere[0].=$sqlstring;
	return $sqlwhere;
}
//收款情况
//fieldname查询字段
function sqlwherereceived($searchname,$sqlwhere,$fieldname,$fieldvalue,$amount)
{
	$sqlstring="";
	if($sqlwhere[0]!=""&&$fieldvalue!="")
	{
		$sqlstring.=" and";
	}
	if($fieldvalue=="")return $sqlwhere;
	if($fieldvalue==1){
		$sqlstring.=' '.$fieldname.' <=0';//未收任何货款
	}
	elseif($fieldvalue==2){
		$sqlstring.=' '.$fieldname.' >0 and '.$fieldname.' < '.$amount;//已收部分货款
	}
	elseif($fieldvalue==3){
		$sqlstring.=' '.$fieldname.' = '.$amount;//已经收完货款
	}
	elseif($fieldvalue==4){
		$sqlstring.=' '.$fieldname.' > '.$amount;//有多余货款
	}
	$sqlwhere[1].=" ".$searchname.":".$fieldvalue." ";
	$sqlwhere[0].=$sqlstring;
	//mydump($sqlwhere);
	return $sqlwhere;
}
//SQL查询两个日期之间的结果,返回查询字符串
//fieldname查询字段
//$date1,$date2开始日期及结束日期
function sqlwheredate($searchname,$sqlwhere,$fieldname,$date1,$date2="")
{
	if($sqlwhere[0]!=""&&$date1!=""&&$date2!="")
	{
		$sqlwhere[0].=" and";
	}
	if($date1==""&&$date2=="")return $sqlwhere;
	if($date2=='')
	{
		$date2=$date1;
	}
	else if(strtotime($date1)>strtotime($date2))
	{
		$date3=$date2;
		$date2=$date1;
		$date1=$date3;
		
	}
	$date1.=' 0:0:0';
	$date2.=' 23:59:59';
	$sqlwhere[1].=" ".$searchname." 为 ".$date1." - ".$date2." 之间 ";
	$sqlwhere[0] .= ' '.$fieldname.' >= "'.$date1.'"  and '.$fieldname.' <="'. $date2.'"';
	return $sqlwhere;
}

//将指定编号转换为要显示的编号，例如Q20180023R1
function showtableno($type,$no,$no2,$rno)
{
	$result = db('bts_setting_norule')->where('type',$type)->select();
	$showno=$result[0]['no_rule'];
	$showrno=$result[0]['rno_rule'];

	$showno=str_replace('[ex_name]',$result[0]['ex_name'],$showno);
	if($type=="po"&&$no2==0){
		$showno=str_replace('[ex_name]',$result[0]['ex_name']."SZ",$showno);
	}
	$tno=numtochr($no,$result[0]['no_len']);
	$showno=str_replace('[no]',$tno,$showno);
	if(strlen($tno)>4)
	{
		$tyear=substr($tno,0,4);
		$tno2=substr($tno,4);
		$tno_2=$tyear."-".$tno2;
		$tno_3=substr($tyear,2)."".$tno2;
		$tno_4=substr($tyear,2)."-".$tno2;
		$showno=str_replace('[no-2]',$tno_2,$showno);
		$showno=str_replace('[no-3]',$tno_3,$showno);
		$showno=str_replace('[no-4]',$tno_4,$showno);
	}
	if($no2>0){ $showno.="-".numtochr($no2,$result[0]['no2_len']);}
	if($rno>0)
	{
		$showrno=str_replace('[r_name]',$result[0]['r_name'],$showrno);
		$trno=numtochr($rno,$result[0]['r_len']);
		$showrno=str_replace('[r_no]',$trno,$showrno);
		$showno.=$showrno;
	}
	return $showno;
}

//获取表格的最大No+1
function newbasicno($table,$type=0)
{
	$newresult = db('bts_'.$table)->order('no desc')->limit(1)->select();
	//mydump($newresult);
	if(!$newresult)
	{
		$no=0;
	}
	else
	{
		$no=(int)$newresult[0]['no'];
	}
	$no=$no+1;
	if($type==1)
	{
		$nowyear=substr(now(),0,4);
		if(substr($no,0,4)!=$nowyear)
		{
			$result=db("bts_setting_norule")->where("type like '".str_replace(" ","_",$table)."'")->select();
			$len=8;
			if($result){ $len=(int)$result[0]['no_len'];}
			$no=$nowyear.numtochr(1,$len-4);
		}
	}
	return $no;
}

//将数据库临时字段名转换成正式名称
function tempfield($rs,$field_var,$field_text)
{
	$temp1=explode(",",$field_var);
	$temp2=explode(",",$field_text);
	$list[]='';
	for($i=0;$i<count($temp1);$i++)
	{
		$list[$temp1[$i]] = $rs['str'.($i+1)];
	}
	for($i=0;$i<count($temp2);$i++)
	{
		$list[$temp2[$i]] = $rs['content'.($i+1)];
	}
	return $list;
}

//将数据库临时字段名转换成正式名称
function fieldtotemp($data,$field_var,$field_text)
{
	$temp1=explode(",",$field_var);
	$temp2=explode(",",$field_text);
	$list=array();
	for($i=0;$i<count($temp1);$i++)
	{
		if(isset($data[$temp1[$i]]))
		{
			$list['str'.($i+1)] = $data[$temp1[$i]];
		}
		else
		{
			$list['str'.($i+1)] = "";
			//echo @$temp1[$i].":". @$data[$temp1[$i]]."<br>";
		}
	}
	for($i=0;$i<count($temp2);$i++)
	{
		if(isset($data[$temp2[$i]]))
		{
			$list['content'.($i+1)] = $data[$temp2[$i]];
		}
		else
		{
			$list['content'.($i+1)] = "";
		}
	}
	return $list;
}
//获取最新汇率
function getrate()
{
	$rs = db('bts_currency_rate')->query('select * from bts_currency_rate order BY id desc limit 1');
	return $rs[0]['id'];
}
function setsubmitdata($tablename,$data)
{
	$sql="SHOW FULL COLUMNS FROM ".$tablename;
	$result = db()->query($sql);
	$idata=array();
	foreach($result as $v)
	{
		if(isset($data[$v['Field']]))
		{
			$idata[$v['Field']]=$data[$v['Field']];
		}
	}
	return $idata;
}

//将多选框值转换成逗号分隔的字符串
function checkboxtostring($inputname)
{
	if(count(input($inputname."/a"))<=0)return "";
	$frm_tag=$_POST[$inputname];
	for($i=0;$i<count($frm_tag);$i++)
	{
		if($i==0)
		$str_tag = $frm_tag[$i];
		else
		$str_tag = $str_tag.",".$frm_tag[$i];
	}
	return $str_tag;
}

//对比权限
function permissions($p,$plist)
{
	if(strstr(','.$plist.',',','.$p.',')!=false)
	{
		return true;
	}
	return false;
}

//判断PI跟进信息
function getpigenjincurr($rs,$filed)
{
	$retval="USD".$rs[$filed."_usd"];
	if($rs[$filed."_hkd"]>0)$retval="HKD".$rs[$filed."_hkd"];
	if($rs[$filed."_rmb"]>0)$retval="RMB".$rs[$filed."_rmb"];
	return $retval;
}

function setpigenjincurr($rs,$filed,$c)
{
	$rs[$filed."_usd"]=0;
	$rs[$filed."_hkd"]=0;
	$rs[$filed."_rmb"]=0;
	$rs[$filed.'_'.strtolower($c)]=$rs[$filed];
	return $rs;
}
