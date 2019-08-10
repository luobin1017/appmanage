<?php
namespace app\cms\controller;
use think\Controller;
use think\Db;

class Usercontrol extends Controller
{
    public function index()
    {
        if(checkpermissions_b('1005')==false)
        {
            return $this->fetch('public/hint');
        }
        /*$menu=getmenu(0);
		$this->assign('menu',$menu);*/
        if(isset(input()['brandcode'])){
            cookie('brandcode',input()['brandcode']);
        }

        //搜索条件
        /*$key=array("0"=>'中国','1'=>'美国','2'=>'英国');
        for($i=0;$i<10;$i++){
            $data = array(
                'email'=>rand('100000000000','999999999999').'@qq.com',
                'password'=>'e10adc3949ba59abbe56e057f20f883e',
                'location'=>$key[rand(0,2)],
                'regtime'=>date("Y-m-d H:i:s",strtotime("-".rand('1','5')."years",time())),
                'brandcode'=>rand('1','3'),
                'isemail'=>rand('0','1'),
            );
            db('app_customer')->insert($data);
        }
        mydump($data);*/
        //dump(cookie('brandcode'));
        $input = input();
        $sqlwhere=array("","");
        $email=input("email");
        $sqlwhere=sqlwhereand("邮箱",$sqlwhere,"email",$email,1,1);
        $country=input("country");
        $sqlwhere=sqlwhereand("国家",$sqlwhere,"location",$country,1,1);
        $isemail=input("isemail");
        $sqlwhere=sqlwhereand("接收状态",$sqlwhere,"isemail",$isemail,0,1);
        $makedate1=input("makedate1");
        $makedate2=input("makedate2");
        $sqlwhere=sqlwheredate("反馈日期",$sqlwhere,"regtime",$makedate1,$makedate2);
        $pagesize = 5;
        //dump($sqlwhere);
        //$list = Db::table("bts_pi")->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);
        $list = db('cms_admin')->where($sqlwhere[0])->paginate($pagesize, false,
                ['query' => request()->param(),
                    'type'     => 'layui',
                    'var_page' => 'page',]);
        //dump(Db::table('app_customer')->getLastSql());
        /* $list = db("app_fankui")->alias('a')->Join([$subsql=>'b'],"b.cid=a.uid",'inner')->where($sqlwhere[0])->order("a.id desc")->paginate($pagesize, false, ['query' => request()->param(),
             'type'     => 'layui',
             'var_page' => 'page',]);*/
        //dump(Db::table('bts_pi')->getLastSql());
        //dump(date("Y-m-d H:i:s",strtotime("+1years",strtotime("2016-05-23 10:22:18.000000"))));
        //mydump(date("Y-m-d H:i:s",time()));
        $page = $list->render();
        //  页数量
        $this->assign('pagesize',$pagesize);
        //  总数据
        $this->assign('total',$list->total());
        //  总页数
        $total= ceil($list->total() / $pagesize);
        $this->assign('totalPage', $total);
        //  当前页
        $pagenow =input('page', 1);
        $this->assign('pagenow', $pagenow);
        $this->assign('arr',$list);
        $this->assign('sqlwhere', $sqlwhere[1]);
        $this->assign('sqlandwhere', $sqlwhere[0]);
        $this->assign('page', $page);
        return $this->fetch();
    }
    //新增账号
    public function add()
    {
        if(checkpermissions_b('1005')==false)
        {
            return $this->fetch('public/hint');
        }
        $input = $_POST;
        if($_POST){
            $str = '';
            if (isset($_POST['permissions']) && $_POST['permissions'] != '') {
                foreach ($input['permissions'] as $k => $v) {
                    $str .= $v . ',';
                }
            }
            unset($input['permissions']);
            $input['password'] = base64_encode($input['password']);
            $input['permissions'] = $str;
            $input['logincode'] = uniqid();
            $rs = db('cms_admin')->insert($input);
            if($rs){
                $this->success('新增用户成功！','index');
            }else{
                $this->error('系统错误');
            }
        }
        return $this->fetch();
    }
    //修改权限
    public function update_show()
    {
        $rs = db('cms_admin')->where('id',input()['id'])->find();
        $permissions = $rs['permissions'];
        //$plist = explode(',',$permissions);
        $this->assign('permissions',$permissions);
        $this->assign('rs',$rs);
        return $this->fetch();
    }
    public function update()
    {
        $input = input();
        $str = '';
        if (isset($input['permissions']) && $input['permissions'] != '') {
            foreach ($input['permissions'] as $k => $v) {
                $str .= $v . ',';
            }
        }
        $data = array(
            'permissions'=>$str
        );
        //mydump($input);
        $rs = db('cms_admin')->where('id',$input['id'])->update($data);
        if($rs){
            $this->success('修改用户权限成功！','index');
        }else{
            $this->error('修改用户权限失败');
        }
    }
    //修改密码
    public function update_showpwd()
    {
        $rs = db('cms_admin')->where('id',input()['id'])->find();
        //$plist = explode(',',$permissions);
        $this->assign('rs',$rs);
        return $this->fetch();
    }
    public function updatepwd()
    {
        $input = input();
        $data = array(
            'username'=>$input['username'],
            'password'=>md5($input['password']),
        );
        //mydump($input);
        $rs = db('cms_admin')->where('id',$input['id'])->update($data);
        if($rs){
            $this->success('修改用户信息成功！','index');
        }else{
            $this->error('修改用户信息成功');
        }
    }
}