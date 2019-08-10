<?php
namespace app\tms\Model;
use think\File;
use think\Request;
use think\Validate;
class Product
{
    //图片上传
    function upfiles($imageName,$name=""){
        $file = request()->file($imageName);
        $imgp=null;
        if(isset($file)){
            $path = ROOT_PATH . 'upfiles/product/';
            $info = $file->rule('uniqid')->move($path);
            if($info){
                // 成功上传后 获取上传信息
                $a = $info->getSaveName();
                $imgp = str_replace("\\", "/", $a);
                //$imgpath = 'uploads/' . $imgp;
            }
        }
        return $imgp;
    }
    //下拉选项
    function getOption($fid=0,$num=0,$selectId=0,$fstr=''){
        //指定条件 fid=$fid 查询
        $arr  = db('bts_product_class')->where('parentid',$fid)->select();
        //拼字符串
        $optionStr="";
        $num++;
        //决定--的个数
        $strGang=str_repeat('└→', $num-1);
        $str="";
        foreach($arr as $v){
            if($v['id']==$selectId){
                $optionStr.="<option selected='selected' value='".$fstr.",".$v['id'].",'>".$strGang."".$v['classename']."/".$v['classename']."</option>";

            }else{
                $optionStr.="<option value='".$fstr.",".$v['id'].",'>".$strGang."".$v['classename']."/".$v['classename']."</option>";
            }
            //找第一级的子分类
            $str="$fstr,".$v['id'];
            $optionSon=$this->getOption($v['id'],$num,$selectId,$str);
            $optionStr.=$optionSon;
        }
        //返回数据 <option value='id'>名称</option>
        return $optionStr;
    }
    function get_category($fid=0,$num=1)
    {
        //指定条件 fid=$fid 查询

        $arr  = db('bts_product_class')->where('parentid',$fid)->order('orderid')->select();
        //拼字符串
        $trStr="";
        //决定--的个数
        $strGang=str_repeat('——┹', $num-1);
        $num++;
        foreach($arr as $v){
            if($v['parentid']==0) {
                $trStr .= "<tr style='background-color: #EEEEEE'>
							<td>".$strGang."".$v['classename']."</td>
							<td>".$strGang."".$v['classename']."</td>
							<td></td>
							<td><a href=\"" . url('tms_system/menu/menu_update', ['ClassID' => $v['classid']]) . "\" class=\"btn btn-success radius l\">修改</a>
							<a  onClick=\"return confirm('确认要删除？')\" class=\"btn btn-danger radius r\" href=\"" . url('tms_system/menu/menu_del', ['ClassID' => $v['classid']]) . "\">删除</a></td>
						 </tr> ";
            }else{
                $trStr .= "<tr>
							<td>".$strGang."".$v['classename']."</td>
							<td>".$strGang."".$v['classename']."</td>
							<td></td>
							<td><a href=\"" . url('tms_system/menu/menu_update', ['ClassID' => $v['classid']]) . "\" class=\"btn btn-success radius l\">修改</a>
							<a  onClick=\"return confirm('确认要删除？')\" class=\"btn btn-danger radius r\" href=\"" . url('tms_system/menu/menu_del', ['ClassID' => $v['classid']]) . "\">删除</a></td>
						 </tr> ";
            }
            //找第一级的子分类
            $trSon=$this->get_category($v['classid'],$num);
            $trStr.=$trSon;
        }
        //返回数据 <option value='id'>名称</option>
        return $trStr;
    }
}