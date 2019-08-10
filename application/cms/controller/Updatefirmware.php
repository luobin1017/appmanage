<?php
namespace app\cms\controller;
use think\Controller;
use think\Db;

class Updatefirmware extends Controller
{
    public function index()
    {
        if (isset(input()['brandcode'])) {
            cookie('brandcode', input()['brandcode']);
        }

        $input = input();
        $sqlwhere = array("", "");
        $email = input("email");
        $sqlwhere = sqlwhereand("邮箱", $sqlwhere, "email", $email, 1, 1);
        $country = input("country");
        $sqlwhere = sqlwhereand("国家", $sqlwhere, "location", $country, 1, 1);
        $isemail = input("isemail");
        $sqlwhere = sqlwhereand("接收状态", $sqlwhere, "isemail", $isemail, 0, 1);
        $makedate1 = input("makedate1");
        $makedate2 = input("makedate2");
        $sqlwhere = sqlwheredate("反馈日期", $sqlwhere, "regtime", $makedate1, $makedate2);
        $pagesize = 10;
        //dump($sqlwhere);
        //$list = Db::table("bts_pi")->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);
        $list = db('app_firmware')->where('brandcode',$_COOKIE['brandcode'])->order('id desc')->paginate($pagesize, false,
            ['query' => request()->param(),
                'type' => 'layui',
                'var_page' => 'page',]);
        $page = $list->render();
        //  页数量
        $this->assign('pagesize', $pagesize);
        //  总数据
        $this->assign('total', $list->total());
        //  总页数
        $total = ceil($list->total() / $pagesize);
        $this->assign('totalPage', $total);
        //  当前页
        $pagenow = input('page', 1);
        $this->assign('pagenow', $pagenow);
        $this->assign('arr', $list);
        $this->assign('sqlwhere', $sqlwhere[1]);
        $this->assign('sqlandwhere', $sqlwhere[0]);
        $this->assign('page', $page);
        return $this->fetch();
    }

    public function add()
    {
        $class = db('firmware_class')->select();

        $result = array();
        if($_GET){
            //mydump($_GET['action']);
            if($_GET['action'] == 'update'){
                $gid = $_GET['id'];
                $result = db('app_firmware')->where('id',$gid)->find();
                $result['type'] = 'update';
                $result['id'] = $gid;
            }elseif($_GET['action'] =='delete' ){
                $gid = $_GET['id'];
                $rs = db('app_firmware')->where('id',$gid)->delete();
                if($rs){
                    $this->success('固件信息删除成功','index');
                }else{
                    $this->error('固件信息删掉失败');
                }
            }
        }
        $input = input();
        if ((!empty($input)) && isset($input['type']) && $input['type']=='') {
            unset($input['type']);
            $input['brandcode'] = $_COOKIE['brandcode'];
            $rs = db('app_firmware')->insert($input);
            if ($rs){
                $this->success('固件信息添加成功','index');
            } else {
                $this->error('固件信息添加失败');
            }
        }
        if(isset($input['type']) && $input['type']=='update'){
            $id = $input['id'];
            unset($input['type']);
            unset($input['id']);
            $rs = db('app_firmware')->where('id',$id)->update($input);
            if($rs){
                $this->success('固件信息修改成功','index');
            }else{
                $this->error('固件信息修改失败');
            }
        }
        if(empty($result)) {
            $result =
                [
                    "id"     =>  '',
                    "type"   =>  "",
                    "filename" => "",
                    "filesize" => "",
                    "version" => "",
                    "downloadurl" => "",
                    "classname" => "",
                    "updatedate" => "",
                    "updatenote_en" => "",
                    "updatenote_cn" => ""
                ];
        }
        $this->assign('class',$class);
        $this->assign('result',$result);
        return $this->fetch();
    }
    function firmware_class()
    {
        if (isset(input()['brandcode'])) {
            cookie('brandcode', input()['brandcode']);
        }
        $input = input();
        $sqlwhere = array("", "");
        $email = input("email");
        $sqlwhere = sqlwhereand("邮箱", $sqlwhere, "email", $email, 1, 1);
        $country = input("country");
        $sqlwhere = sqlwhereand("国家", $sqlwhere, "location", $country, 1, 1);
        $isemail = input("isemail");
        $sqlwhere = sqlwhereand("接收状态", $sqlwhere, "isemail", $isemail, 0, 1);
        $makedate1 = input("makedate1");
        $makedate2 = input("makedate2");
        $sqlwhere = sqlwheredate("反馈日期", $sqlwhere, "regtime", $makedate1, $makedate2);
        $pagesize = 10;
        //dump($sqlwhere);
        //$list = Db::table("bts_pi")->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);
        $list = db('firmware_class')->where('brandcode',$_COOKIE['brandcode'])->order('id asc')->paginate($pagesize, false,
            ['query' => request()->param(),
                'type' => 'layui',
                'var_page' => 'page',]);
        $page = $list->render();
        //  页数量
        $this->assign('pagesize', $pagesize);
        //  总数据
        $this->assign('total', $list->total());
        //  总页数
        $total = ceil($list->total() / $pagesize);
        $this->assign('totalPage', $total);
        //  当前页
        $pagenow = input('page', 1);
        $this->assign('pagenow', $pagenow);
        $this->assign('arr', $list);
        $this->assign('sqlwhere', $sqlwhere[1]);
        $this->assign('sqlandwhere', $sqlwhere[0]);
        $this->assign('page', $page);
        return $this->fetch();
    }
    public function class_add()
    {
        $result = array();
        if($_GET){
            //mydump($_GET['action']);
            if($_GET['action'] == 'update'){
                $gid = $_GET['id'];
                $result = db('firmware_class')->where('id',$gid)->find();
                $result['type'] = 'update';
                $result['id'] = $gid;
            }elseif($_GET['action'] =='delete' ){
                $gid = $_GET['id'];
                $rs = db('firmware_class')->where('id',$gid)->delete();
                if($rs){
                    $this->success('固件分类删除成功','firmware_class');
                }else{
                    $this->error('固件分类删掉失败');
                }
            }
        }
        $input = input();
        if ((!empty($input)) && isset($input['type']) && $input['type']=='') {
            unset($input['type']);
            $input['brandcode'] = $_COOKIE['brandcode'];
            $rs = db('firmware_class')->insert($input);
            if ($rs){
                $this->success('固件分类添加成功','firmware_class');
            } else {
                $this->error('固件分类添加失败');
            }
        }
        if(isset($input['type']) && $input['type']=='update'){
            $id = $input['id'];
            unset($input['type']);
            unset($input['id']);
            $rs = db('firmware_class')->where('id',$id)->update($input);
            if($rs){
                $this->success('固件分类修改成功','firmware_class');
            }else{
                $this->error('固件分类修改失败');
            }
        }
        if(empty($result)) {
            $result =
                [
                    "id"     =>  '',
                    "type"   =>  "",
                    "classname_en" => "",
                    "classname_cn" => ""
                ];
        }
        $this->assign('result',$result);
        return $this->fetch();
    }

}