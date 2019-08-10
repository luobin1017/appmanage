<?php
namespace app\tms_setting\controller;
use think\Controller;
use think\Db;

class Pdf extends Controller
{
    public function index()
    {
		checkpermissions('0001',true);
		$datainfo=rfile("pdf/config/database.ini");
		$datainfo=mysetconfig("Server","",$datainfo);
		$datainfo=mysetconfig("Database","",$datainfo);
		$datainfo=mysetconfig("Username","",$datainfo);
		$datainfo=mysetconfig("Password","",$datainfo);
		$datainfo=mysetconfig("ServerPort","",$datainfo);
		$this->assign('datainfo', $datainfo);
		$list;
		for($i=0;$i<99;$i++)
		{
			$temp=mygetconfig("类型".($i+1)."中文",$datainfo);
			$temp1=mygetconfig("类型".($i+1)."英文",$datainfo);
			if($temp!="")
			{
				$list[$i]['cnname']=$temp;
				$list[$i]['enname']=$temp1;
				$list[$i]['content']=rfile("pdf/config/".$temp1.".ini");
			}
			else
			{
				break;
			}
		}
		$this->assign('list', $list);
		return $this->fetch();
    }
	
	public function updatedatabase()
    {
		checkpermissions('0001',true);
		$data = $_POST;
		$datainfo=$data['content'];
		$temp=rfile("application/database.php");
		$hostname=mygetdatabase("hostname",$temp);
		$database=mygetdatabase("database",$temp);
		$username=mygetdatabase("username",$temp);
		$password=mygetdatabase("password",$temp);
		$hostport=mygetdatabase("hostport",$temp);
		if($hostport=="")$hostport=3306;
		$datainfo=mysetconfig("Server",$hostname,$datainfo);
		$datainfo=mysetconfig("Database",$database,$datainfo);
		$datainfo=mysetconfig("Username",$username,$datainfo);
		$datainfo=mysetconfig("Password",$password,$datainfo);
		$datainfo=mysetconfig("ServerPort",$hostport,$datainfo);
		sfile("pdf/config/database.ini",$datainfo);
		$this->success('数据更新成功！','index');
    }
	
	public function update()
    {
		checkpermissions('0001',true);
		$data = $_POST;
		sfile("pdf/config/".$data['name'].".ini",$data['content']);
		$this->success('数据更新成功！','index');
    }
}
