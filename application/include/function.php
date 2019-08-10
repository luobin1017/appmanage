<?php
use think\Controller;
use think\Db;
use think\Config;

// 自定义函数库

//跳转到登录页面
function gologin()
{
	gourl(SYS_PATH."index.php/cms_login");
}

//跳转到首页
function gohome()
{
	gourl(SYS_PATH."index.php/cms/index/index");
}

//显示提示信息
function showmsg($str)
{
	echo "<!DOCTYPE><html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' /><title>江森外贸管理系统</title></head><body><script>alert('".$str."');</script></body></html>";
}

//将字符串显示出来，并停止运行，用于Debug
function mydump($str)
{
	dump($str);exit(0);
}

//获取客户端IP
function getip()
{
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown'))
	{
		$ip = getenv('HTTP_CLIENT_IP');
	}
	elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) 
	{
		$ip = getenv('HTTP_X_FORWARDED_FOR');
	}
	elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) 
	{
		$ip = getenv('REMOTE_ADDR');
	}
	elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) 
	{
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
}

//将系统配置信息存入Config
function getsiteinfo()
{
	$result=Db::name('bts_setting')->where('id', 1)->select();
	Config('sys_info',$result[0]);
}

//获取系统顶部菜单
function getmenu($id)
{
	$result=Db::name('bts_menu')->where('parentid', $id)->order('orderid asc')->select();
	$num_rows=count($result);
	$i=0;$html="";
	if($id!=0&&$num_rows>0)$html="<ul>";
	foreach($result as $v)
	{
		$i++;
		$bar_id=$v["id"];
		$bar_name=$v["menuname"];
		$bar_url=$v["url"];
		if($id==0&&$i==1)
		{
			$html.="<li class='ali'><a href=\"javascript:void(0);\" onclick=\"menubar_add('".$bar_name."','".$bar_url."');\"><img src='/tms/images/menu".$i.".gif' border='0' /><span>".$bar_name."</span></a>";
		}
		elseif($id==0&&$i!=1)
		{
			$html.="<li class='ali'><a><img src='/tms/images/menu".$i.".gif' border='0' /><span>".$bar_name."</span></a>";
		}
		else
		{
			$html.="<li class='sli'><a href=\"javascript:menubar_add('".$bar_name."','".$bar_url."');\">".$bar_name."</a>";
		}
		$html.=getmenu($v["id"])."</li>";
	}
	if($id!=0&&$num_rows>0)$html.="</ul>";
	return $html;
}
//重命名上传产品图片名称
function product_pic_raname($pic,$no){
	$houzui = substr(strrchr($pic, '.'), 1);
	$a = rand(1000,9999);
	$imgp =$no."_".date("YmdHis",time()).$a.".".$houzui;
	$oldpath = ROOT_PATH . 'upfiles/product/'.$pic;
	$newpath = ROOT_PATH . 'upfiles/product/'.$imgp;
	$oldpath = str_replace("\\", "/", $oldpath);
	$newpath = str_replace("\\", "/", $newpath);
	rename($oldpath,$newpath);
	return $imgp;
}
//轉換數據到在線編輯器
function strtojs($str) {
	if(empty($str)){
		return false;
	 }
	$retvel=$str;
    $retvel=str_replace('\\','\\\\',$retvel);
	$retvel=str_replace("\n","\\n",$retvel);
	$retvel=str_replace("\r\n","\\n",$retvel);
	$retvel=str_replace("\r","\\r",$retvel);
	$retvel=str_replace("'","\\'",$retvel);
	$retvel=str_replace("\"\"","\\\"\"",$retvel);
    $retvel=str_replace("/","\\/",$retvel);
	return $retvel;
}
//將數字轉換成3位一逗號格式
function num_formatl($num)
{
	if(!is_numeric($num)){
		return false;
	}
	$rvalue='';
	$num = explode('.',$num);//把整数和小数分开
	$rl = !isset($num['1']) ? '' : $num['1'];//小数部分的值
	$j = strlen($num[0]) % 3;//整数有多少位
	$sl = substr($num[0], 0, $j);//前面不满三位的数取出来
	$sr = substr($num[0], $j);//后面的满三位的数取出来
	$i = 0;
	while($i <= strlen($sr)){
		$rvalue = $rvalue.','.substr($sr, $i, 3);//三位三位取出再合并，按逗号隔开
	$i = $i + 3;
	}
	$rvalue = $sl.$rvalue;
	$rvalue = substr($rvalue,0,strlen($rvalue)-1);//去掉最后一个逗号
	$rvalue = explode(',',$rvalue);//分解成数组
	if($rvalue[0]==0){
		array_shift($rvalue);//如果第一个元素为0，删除第一个元素
	}
	$rv = $rvalue[0];//前面不满三位的数
	for($i = 1; $i < count($rvalue); $i++){
		$rv = $rv.','.$rvalue[$i];
	}
	if(!empty($rl)){
		$rvalue = $rv.'.'.$rl;//小数不为空，整数和小数合并
	}else{
		$rvalue = $rv;//小数为空，只有整数
	}
	return $rvalue;
}

/*
	显示选择列表
	$id为select名称
	$value为选中值
	$list为选单列表，可为一维或二维数组或用逗号分隔的字符串
	$firsttext第一个选项的名称，为空不显示
	$firstvalue第一个选项的值
	$iskey value值为排序号
*/
function showselect($id,$value,$list,$firsttext,$firstvalue,$iskey=0)
{
	$text="<select name=\"".$id."\" id=\"".$id."\" style=\"height:28px\">";
	if($firsttext!="")$text.="<option value='".$firstvalue."'>".$firsttext."</option>";
	$itemlist=$list;
	if(!is_array( $list)){ $itemlist=explode(',',$list);}
	foreach ($itemlist as $key=>$item)
	{
		if(is_array( $item))
		{
			$t=$item[0];
			$v=(string)($item[1]);
		}
		else
		{
			$t=$item;
			if($iskey)
			{
				$v=$key;
			}
			else
			{
				$v=$item;
			}
		}
		if($t!=NULL){
			$text.="<option value='".$v."'";
			if($v==(string)$value){ $text.=" selected=\"selected\"";}
			$text.=">".$t."</option>";
		}
	}
	$text.="</select>";
	return $text;
}

//将数据库查询结果转换成Select所使用的二维数组
function fieldtoarray($rs,$field1,$field2)
{
	$result[0][0]="";
	$result[0][1]="";
	$i=0;
	if($field2=="")$field2=$field1;
	foreach ($rs as $item)
	{
		$result[$i][0]=$item[$field1];
		$result[$i][1]=$item[$field2];
		$i++;
	}
	return $result;
}

//获取字段在表中的位置
function fieldinlist($list,$field)
{
	$itemlist=explode(',',$list);
	$n=0;
	foreach ($itemlist as $key=>$item)
	{
		if($item==$field){ $n=$key+1;break;}
	}
	return $n;
}

//获取产品分类列表数组
function getproduct($parentid,$num,$list,$id)
{
	$arr  = db('bts_product_class')->where('parentid',$parentid)->order('orderid asc')->select();
	$optionStr="";
	$num++;
	$strGang=str_repeat('└→', $num-1);
	foreach($arr as $v)
	{
		$list[$id][0]=$strGang.$v['classname'].'/'.$v['classename'];
		$list[$id][1]=$v['classid'];
		$id=$id+1;
		$list=getproduct($v['classid'],$num,$list,$id);
		$id=count($list);
	}
	return $list;
}

//更新指定表的树形结构，以及重新设定排序字段
function updatetree($tablename,$parentid=0,$treecode="",$orderid=0)
{
	$rs= db($tablename)->where('parentid',$parentid)->order('orderid asc')->select();
	$count=count($rs);
	$temporderid=$orderid;
	for($i=0;$i<$count;$i++)
	{
		$temporderid++;
		if($i<$count-1)
		{
			$tempcode=1;
		}
		else
		{
			$tempcode=3;
		}
		if($treecode!="")
		{
			$rightone=substr($treecode,strlen($treecode)-1);
			if($rightone==1)
			{
				$tempcode=substr($treecode,0,strlen($treecode)-1)."2".$tempcode;
			}
			else
			{
				$tempcode=substr($treecode,0,strlen($treecode)-1)."0".$tempcode;
			}
		}
		$data['tree']=$tempcode;
		$data['orderid']=$temporderid;
		db($tablename)->where('id',$rs[$i]['id'])->update($data);
		$temporderid=updatetree($tablename,$rs[$i]['id'],$tempcode,$temporderid);
	}
	return $temporderid;
}
/*
 * $table 			表名称
 * $parentid		查询隶属父ID的记录
 * $id_name			要查询的字段名称
 * $name_name		要显示的字段名称，可以用逗号分隔
 * $list			要返回的数组变量
 * $id				调用函数时，ID值必须为零，此值为数组的下标
 * */
//获取指定无限分类的下拉菜单
function getpclassarray($table,$parentid,$id_name,$name_name,$list,$id=0)
{
	$arr  = db($table)->where('parentid',$parentid)->order('orderid asc')->select();
	foreach($arr as $v)
	{
		$tree=$v['tree'];
		$tree=str_replace('0','&nbsp;&nbsp;',$tree);
		$tree=str_replace('1','├ ',$tree);
		$tree=str_replace('2','│ ',$tree);
		$tree=str_replace('3','└ ',$tree);
		$temp=explode(',',$name_name);
		if(count($temp)<=1)	//如果要显示的字段名称只有1个时
		{
			$list[$id][0]=$tree.$v[$name_name];
		}
		else  //如果要显示的字段名称有2个及以上时
		{
			for($i=0;$i<count($temp);$i++)//循环显示所有字段
			{
				
				if($v[$temp[$i]]!="")
				{
					$list[$id][0]=$tree.$v[$temp[$i]];
					break;
				}
			}
		}
		$list[$id][1]=$v[$id_name];
		$id=$id+1;
		$list=getpclassarray($table,$v[$id_name],$id_name,$name_name,$list,$id);
		$id=count($list);
	}
	return $list;
}

//在权限列表里面显示权限
function showpermissions($no,$list,$id)
{
	if(strstr(','.$list.',',','.(string)$id.',')!=false)
	{
		$plist=explode(',',sysinfo('sys_permissions'));
		//echo '<span>( '.$no.numtochr($id,2).' ) '.$plist[$id-1]."</span>";
		return $plist[$id-1];
	}
	return "";
}

//初始化一条基于表的空白array记录
function blanktablefield($tablename)
{
	$sql="SHOW FULL COLUMNS FROM ".$tablename;
	$result = db()->query($sql);
	$list['id']=0;
	foreach($result as $v)
	{
		$list[$v['Field']]="";
	}
	return $list;
}

//
function newtabler($tablename,$sqlwhere)
{
	$rs  = db($tablename)->where($sqlwhere)->order('rno desc')->limit(1)->select();
	$rno=0;
	if($rs)
	{
		$rno=(int)$rs[0]['rno'];
	}
	return $rno+1;
}
//检查用户是否具体权限,$stop表示是否中止程序运行并提示权限问题
function checkpermissions_b($mypermissions)
{
	//用户权限
	$permissions=Config('user_permissions');

	//获取用户分组权限
	//dump($permissions.$p_group);
	//对比权限
	if(strstr(','.$permissions.',',','.$mypermissions.',')!=false)
	{
		return true;
	}
	if(strstr(','.$permissions.',',','.$mypermissions.',')!=false)
	{
		return true;
	}
	else
	{
		return false;
	}
}

//检查用户是否具体权限,$stop表示是否中止程序运行并提示权限问题
function checkpermissions($mypermissions,$stop=false)
{
	//用户权限
	$permissions=Config('user_permissions');
	//获取用户分组权限
	$p_group=Config('user_permissions_group');
	if($p_group!="")
	{
		$result = db('bts_setting_permissions_group')->where('id','in',$p_group)->select();
		if($result) $permissions .=",".$result[0]['permissions'];
	}
	//dump($permissions.$p_group);
	//对比权限
	if(strstr(','.$permissions.',',','.$mypermissions.',')!=false)
	{
		return true;
	}
	else
	{
		if($stop==false)
		{
			return false;
		}
		else
		{
			showerrormsg('您无权限进行此次操作，请确认您要进行的操作是否正确，并及时联系上级获得此操作的权限！');
		}
	}
}


//显示指定消息，并结束程序运行
function showerrormsg($msg)
{
	$string='<!DOCTYPE HTML><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>'.sysinfo('sys_company').' - 外贸管理系统</title>';
	$string.='<script src="/tms/js/all.js" language="javascript"></script></head><body><h2 style="color:red;">';
	$string.=$msg;
	$string.='</h2></body></html>';
	echo $string;
	exit(0);
}

//记录历史数据
function historylog($msg)
{
	$username = Config('username');
	$data['ip']=getip();
	$data['url']='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']; 
	$data['time']=date('Y-m-d H:i:s', time());
	$data['username']=$username;
	$data['operation']=$username." ".$msg;
	$result = db('bts_history')->insert($data);
}
//分割字符串为数组 a,b,c,d
function strtoarray($str,$separate)
{
	$trimstr = trim($str,$separate);
	$arr = explode($separate,$trimstr);
	return $arr;
}
//
function strexplode_numprice($str,$a,$b,$c)
{
	$priceid=trim($str,$a);
	$pricearr = explode($a,$priceid);
	$numarr=array();
	$newprice = array();
	$newpricearr = array();
	foreach($pricearr as $k=>$v){
		$newprice[$k] = explode($b,trim($v,$b));
	}
	if($c == 'num'){
		foreach($newprice as $k=>$v){
			$numarr[$k] = $v['0'];
		}
	}
	else if($c=='price')
	{
		foreach($newprice as $k=>$v){
			$numarr[$k] = $v['1'];
		}
	}
	return $numarr;
}
//设置token
function settoken()
{
	$str = md5(uniqid(md5(microtime(true)),true));  //生成一个不会重复的字符串
	$str = sha1($str);  //加密
	return $str;
}
//邮件内容定义
function setmailcontent($mail,$pwd){
	$content = "<div style=\"background-color:#ECECEC; padding: 35px;\">
<table cellpadding=\"0\" align=\"center\" style=\"width: 600px; margin: 0px auto; text-align: left; position: relative; border-top-left-radius: 5px; border-top-right-radius: 5px; border-bottom-right-radius: 5px; border-bottom-left-radius: 5px; font-size: 14px; font-family:微软雅黑, 黑体; line-height: 1.5; box-shadow: rgb(153, 153, 153) 0px 0px 5px; border-collapse: collapse; \">
<tbody>
<tr>
<td>
<div style=\"padding:25px 35px 40px; background-color:#fff;\">
<h2 style=\"margin: 5px 0px; \">Hi {$mail},</h2>
<pre><p style='font-size: 14px;font-family: sans-serif;'>Thank you very much for registering at DEEPSEA! We are so happy to be with you and we will try our best to provide you with more excitement and convenience!

If you have any concern, please reach us timely and we will spare no efforts to find the solution for you!

Here are our contacts:

E-mail:support@depstech.com

\"Feedback\" in DEPSTECH-View App.

Have a nice day!
</p></pre>
</div>
</td>
</tr>
</tbody>
</table>
</div>";
	return $content;
}
function setpwdcontent($mail,$password){
	$content = "<div style=\"background-color:#ECECEC; padding: 35px;\">
<table cellpadding=\"0\" align=\"center\" style=\"width: 600px; margin: 0px auto; text-align: left; position: relative; border-top-left-radius: 5px; border-top-right-radius: 5px; border-bottom-right-radius: 5px; border-bottom-left-radius: 5px; font-size: 14px; font-family:微软雅黑, 黑体; line-height: 1.5; box-shadow: rgb(153, 153, 153) 0px 0px 5px; border-collapse: collapse; \">
<tbody>
<tr>
<td>
<div style=\"padding:25px 35px 40px; background-color:#fff;\">
<h2 style=\"margin: 5px 0px; \">Hello {$mail},</h2>
<pre><p>Your account password has been changed, and now the new password is: {$password}

Please modify it as soon as possible.

Best Wish,

{$mail}</p></pre>
</div>
</td>
</tr>
</tbody>
</table>
</div>";
	return $content;
}

function setfeedcontent($email){
	$content = "<div style=\"background-color:#ECECEC; padding: 35px;\">
<table cellpadding=\"0\" align=\"center\" style=\"width: 600px; margin: 0px auto; text-align: left; position: relative; border-top-left-radius: 5px; border-top-right-radius: 5px; border-bottom-right-radius: 5px; border-bottom-left-radius: 5px; font-size: 14px; font-family:微软雅黑, 黑体; line-height: 1.5; box-shadow: rgb(153, 153, 153) 0px 0px 5px; border-collapse: collapse; \">
<tbody>
<tr>
<td>
<div style=\"padding:25px 35px 40px; background-color:#fff;\">
<h2 style=\"margin: 5px 0px; \">Hi {$email},</h2>
<pre><p>Thanks a lot for your submission. We have received your message, and we will deal with it immediately！

This is an automatically generated email, please do not reply.

Your understanding will be highly appreciated and have a good day!

DEPSTECH Team</p></pre>
</div>
</td>
</tr>
</tbody>
</table>
</div>";
	return $content;
}
?>