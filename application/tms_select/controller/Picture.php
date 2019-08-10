<?php
namespace app\tms_select\controller;
use think\Controller;
use think\Db;

class Picture extends Controller
{
	public function index()
	{
		$ptype=input("ptype");
		//搜索条件
		$sqlwhere=array("","");
		$sqlwhere=sqlwhereand("类别",$sqlwhere,"ptype",$ptype,1,1);
		$name=input("name");
		$sqlwhere=sqlwhereand("名称",$sqlwhere,"filename",$name,1,1);
		$pagesize=20;
		$list = db('bts_picture')->where($sqlwhere[0])->order('id desc')->paginate($pagesize, false, ['query' => request()->param(),]);
		$page = $list->render();
		//  页数量
		$this->assign('pagesize',$pagesize);
		//  总数据
		$this->assign('total',$list->total());
		//  总页数
		$total= ceil($list->total() / $pagesize);
		$this->assign('totalPage', $total);
		//  当前页
		$pagenow = input('page', 1);
		$this->assign('pagenow', $pagenow);

		$this->assign('list',$list);
		$this->assign('page', $page);
		$this->assign('ptype', $ptype);
		return $this->fetch();
	}
	
	public function upload()
	{
		$ptype=input("ptype");
		$name=input("name");
		$input=input("input");
		$img=input("img");
		$Sys_Upload_Dir="upfiles/other/".strtolower($ptype)."/";
		//上传文件
		myupload_maxsize(1024*400);
		myupload_fileext("|JPG|GIF|BMP|PNG|");
		$data['filename']=myupload_get_filename("file1");
		$data['picture']=myupload_randomname("file1");
		$retval=myupload_save("file1",$Sys_Upload_Dir.$data['picture']);
		if($retval==1){showjserr("上传失败，错误未知！");}
		else if($retval==2){showjserr("上传失败，上传的文件类型不正确！");}
		else if($retval==3){showjserr("上传失败，上传的文件大小超过限制！");}
		//生成缩略图
		picsmall($Sys_Upload_Dir.$data['picture'], 220, 220, $Sys_Upload_Dir.$data['picture']);
		$data['time']=now();
		$data['ptype']=$ptype;
		db('bts_picture')->insert($data);
		gourl("../?ptype=".$ptype."&input=".$input."&img=".$img);
	}

}
