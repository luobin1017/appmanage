<?php
namespace app\tms_business\Model;
use think\File;
use think\Request;
use think\Validate;
class Source
{
    //获取产品分类列表数组
    function getsource($parentid,$num,$list,$id)
    {
        $arr  = db('bts_source')->where('parentid',$parentid)->order('no asc')->select();
        $optionStr="";
        $num++;
        $strGang=str_repeat('└→', $num-1);
        foreach($arr as $v)
        {
            $list[$id][0]=$strGang.$v['classname'];
            $list[$id][1]=$v['id'];
            $id=$id+1;
            $list=$this->getsource($v['id'],$num,$list,$id);
            $id=count($list);
        }
        return $list;
    }
}