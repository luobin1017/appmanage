<?php
namespace app\tms_setting\controller;
use think\Controller;
use think\Db;

class Norule extends Controller
{
    public function index()
    {
		checkpermissions('0001',true);
		$list = db('bts_setting_norule')->where('id=1')->select();
		$this->assign('p',$list);
		$list = db('bts_setting_norule')->where('id=2')->select();
		$this->assign('c',$list);
		$list = db('bts_setting_norule')->where('id=3')->select();
		$this->assign('s',$list);
		$list = db('bts_setting_norule')->where('id>=4 and id<=12')->select();
		$this->assign('list',$list);
		return $this->fetch();
    }
	
	public function update()
    {
		checkpermissions('0001',true);
		$data = $_POST;
		if($data["ttype"]=="basic")
		{
			$pdata['ex_name']=$data['p_ex_name'];
			$pdata['no_len']=$data['p_no_len'];
			$pdata['no_star']=$data['p_no_star'];
			$pdata['no_rule']=$data['p_no_rule'];
			$result = db('bts_setting_norule')->where('id',1)->update($pdata);
			$cdata['ex_name']=$data['c_ex_name'];
			$cdata['no_len']=$data['c_no_len'];
			$cdata['no_star']=$data['c_no_star'];
			$cdata['no_rule']=$data['c_no_rule'];
			$result = db('bts_setting_norule')->where('id',2)->update($cdata);
			$sdata['ex_name']=$data['s_ex_name'];
			$sdata['no_len']=$data['s_no_len'];
			$sdata['no_star']=$data['s_no_star'];
			$sdata['no_rule']=$data['s_no_rule'];
			$result = db('bts_setting_norule')->where('id',3)->update($sdata);
		}
		else
		{
			$type=$data['ttype'];
			unset($data['ttype']);
			$result = db('bts_setting_norule')->where('Type',$type)->update($data);
		}
		$this->success('数据更新成功！','index');
    }
}
