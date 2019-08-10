<?php
namespace app\cms\controller;
use think\Controller;
use think\Db;
use app\cms\controller\Mail;

class index extends Controller
{
    public function index()
    {
        //mydump($_COOKIE);
		/*$menu=getmenu(0);
		$this->assign('menu',$menu);*/
        if(checkpermissions_b('1001')==false)
        {
            return $this->fetch('public/hint');
        }
        if(isset(input()['brandcode'])){
            cookie('brandcode',input()['brandcode']);
        }
        $input = input();
        //搜索条件
        $sqlwhere=array("","");
        $email=input("email");
        $sqlwhere=sqlwhereand("邮箱",$sqlwhere,"email",$email,1,1);
        $country=input("country");
        $sqlwhere=sqlwhereand("国家",$sqlwhere,"country",$country,1,1);
        $status=input("status");
        $sqlwhere=sqlwhereand("回复状态",$sqlwhere,"status",$status,0,1);
        $makedate1=input("makedate1");
        $makedate2=input("makedate2");
        $sqlwhere=sqlwheredate("反馈日期",$sqlwhere,"intime",$makedate1,$makedate2);
        $pagesize = 10;
        //dump($sqlwhere);
        //$list = Db::table("bts_pi")->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);
        $subsql1 = db('country_mobile_prefix')->field('country,mobile_prefix,area')->buildSql();
        $subsql = db('app_customer')->field('id as cid,email,location,brandcode,cc.country,cc.mobile_prefix,cc.area')->alias('c')->Join([$subsql1=>'cc'],"c.location=cc.mobile_prefix",'inner')->where('brandcode',$_COOKIE['brandcode'])->buildSql();
        $list = db("app_fankui")->alias('a')->Join([$subsql=>'b'],"b.cid=a.uid",'inner')->where($sqlwhere[0])->order("a.id desc")->paginate($pagesize, false, ['query' => request()->param(),
            'type'     => 'layui',
            'var_page' => 'page',]);
        //dump(Db::table('bts_pi')->getLastSql());
        //mydump($list);
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
        $this->assign('page', $page);
		return $this->fetch();
    }
    //选择品线
    public function select_brand()
    {
        return $this->fetch();
    }
    //反馈回复
    public function feedbackreply()
    {
        $input =input();
        $id = $input['id'];
        $status = $input['status'];
        $email = $input['email'];
        $arr= db('app_fankui')->where("id={$id}")->find();
        $picture = [];
        for($i=0;$i<6;$i++){
            if(!empty($arr['pic'.($i+1)])){
                $picture[] = $arr['pic'.($i+1)];
            }
        }
        $res='';
        if($status==1){
            $res = db('cms_huifu')->where("fid={$id}")->select();
        }
        if(isset($input['huifucontent'])&&$input['huifucontent']!=''){
            $data = array(
                'huifucontent'=>$input['huifucontent'],
                'fid'=>$id,
                'htime'=>date("Y-m-d H:i:s" ,time()),
                'husername'=>cookie('username'),
            );

            $db = db('cms_huifu')->insert($data);
            db('app_fankui')->where('id',$id)->update(array('status'=>'1'));
            if($db){
                $params = [
                    [
                        'to'        => $email,
                        'subject'   => 'from depstech',
                        'body'   => $input['huifucontent'],
                    ]
                ];
                //dump($params);
                $msg = json_encode($params);
                //mydump($msg);
                $this->send($msg);
                $this->success('反馈回复成功!','index');
            }else{
                $this->error('反馈回复失败!');
            }
        }
        $this->assign('status',$status);
        $this->assign('email',$email);
        $this->assign('arr',$arr);
        $this->assign('res',$res);
        $this->assign('picture',$picture);
        return $this->fetch();
    }
    public function phpinfo()
    {
        echo phpinfo();
    }
    //异步任务
    public function send($msg)
    {
        $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        //连接到服务器
        if (!$client->connect('0.0.0.0', 9501, 0.5))
        {
            echo  "connect failed.";
        }
        //向服务器发送数据
        if (!$client->send($msg))
        {
            echo "send ".$msg." failed.";
        }
        //关闭连接
        $client->close();
    }
}