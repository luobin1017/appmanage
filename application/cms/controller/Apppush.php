<?php
namespace app\cms\controller;
use think\Controller;
use think\Db;
use app\cms\controller\Mail;
use JPush\Client as JPush;
use JPush\Config;
use JPush\PushPayload;
use JPush\Http;
use JPush\Exceptions;

class Apppush extends Controller
{
    public function index()
    {

		/*$menu=getmenu(0);
		$this->assign('menu',$menu);*/
        if(checkpermissions_b('1003')==false)
        {
            return $this->fetch('public/hint');
        }
        if(isset(input()['brandcode'])){
            cookie('brandcode',input()['brandcode']);
        }
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
        $pagesize = 10;
        //dump($sqlwhere);
        //$list = Db::table("bts_pi")->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);
        $list = db('app_customer')->field('id,email,location,isemail,regtime,brandcode')
            ->where('brandcode',$_COOKIE['brandcode'])->where($sqlwhere[0])->paginate($pagesize, false,
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


    //指定用户
    public function index_show()
    {
        /*$menu=getmenu(0);
        $this->assign('menu',$menu);*/
        if(checkpermissions_b('1003')==false)
        {
            return $this->fetch('public/hint');
        }
        if(isset(input()['brandcode'])){
            cookie('brandcode',input()['brandcode']);
        }
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
        $pagesize = 10;
        //dump($sqlwhere);
        //$list = Db::table("bts_pi")->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);
        $subsql1 = db('country_mobile_prefix')->field('country,mobile_prefix,area')->buildSql();
        $list = db('app_customer')->field('id,email,location,isemail,regtime,brandcode,cc.country,cc.mobile_prefix,cc.area')->alias('c')->Join([$subsql1=>'cc'],"c.location=cc.mobile_prefix",'left')
            ->where('brandcode',$_COOKIE['brandcode'])->where($sqlwhere[0])->order("id desc")->paginate($pagesize, false,
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
    //安卓推送
    public function androidpushbyid($data='')
    {
        $input = input();
        $partpush = isset($input['partpush'])?$input['partpush']:'';
        $newget = trim($partpush,',');
        if($partpush){
            $sql = "SELECT * from app_customer WHERE  id IN ({$newget})";
            $xlsData = db('app_customer')->query($sql);
        }else{
            $xlsData = '';
        }
        if($xlsData){
            $list = $xlsData;
        }
        else
        {
            if($input['user']=='all'){
                $list = db('app_customer')->field('id,brandcode,devicetoken')
                    ->where('brandcode',$_COOKIE['brandcode'])->order('id desc')->select();
            }else{
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
                $pagesize = 10;
                //dump($sqlwhere);
                //$list = Db::table("bts_pi")->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);
                $list = db('app_customer')->field('id,email,location,isemail,regtime,brandcode,devicetoken')
                    ->where('brandcode',$_COOKIE['brandcode'])->where($sqlwhere[0])->order('id desc')->select();
            }
        }
        $data = array();
        foreach($list as $k=>$v){
            $data[$k] = $v['devicetoken'];
        }
        //mydump($data);
        /*$app_key = 'cc4b45644369477684682f17';
        $master_secret = '5cbeaf343d419a8367e918f9';
        $client =new Client($app_key, $master_secret);
        try {
            $payload = $client->push()
                ->setPlatform(array('android'))
                ->addRegistrationId($data)
                ->setNotificationAlert('Hi, JPush')
                ->androidNotification('交易变更通知', array(
                    'title' => '收到转账5毛钱',
                    // ---------------------------------------------------
                    'extras' => array(
                        'key' => 'helo'	//可以让前段识别这个推送是干啥的
        ),
        ))
            ->send();
            return json($payload);
        } catch (\JPush\Exceptions\APIConnectionException $e) {
            print $e;
        } catch (\JPush\Exceptions\APIRequestException $e) {
            print $e;
        }*/
        $app_key = 'cc4b45644369477684682f17';
        $master_secret = '5cbeaf343d419a8367e918f9';
        //进行极光推送
        $client = new JPush($app_key, $master_secret);
        try {
            $response = $client->push()
                ->setPlatform(array('ios', 'android'))
                // 一般情况下，关于 audience 的设置只需要调用 addAlias、addTag、addTagAnd  或 addRegistrationId
                // 这四个方法中的某一个即可，这里仅作为示例，当然全部调用也可以，多项 audience 调用表示其结果的交集
                // 即是说一般情况下，下面三个方法和没有列出的 addTagAnd 一共四个，只适用一个便可满足大多数的场景需求

                // ->addAlias('alias')
                //->addTag(array('tag1', 'tag2'))
                ->addRegistrationId($data)//$jpush_key此处jpush_key是手机端的
                //->setNotificationAlert('Hi, JPush')
                ->androidNotification($input['sendcontent'], array(
                    // 'badge' => '+1',
                    // 'content-available' => true,
                    // 'mutable-content' => true,
                    'title' => $input['sendtitle'],
                    'category' => 'jiguang',
                    'extras' => array(
                        'pushTime'=>$input['begintime'],
                        'deadTime'=>$input['endtime'],
                        'contentType'=>$input['sendtype'],
                        'title'=>$input['sendtitle'],
                        'message'=>$input['sendcontent'],
                        'website'=> $input['weburl']
                    ),
                ))
                ->iosNotification($input['sendcontent'], array(
                    // 'badge' => '+1',
                    // 'content-available' => true,
                    // 'mutable-content' => true,
                    'title' => $input['sendtitle'],
                    'category' => 'jiguang',
                    'extras' => array(
                        'pushTime'=>$input['begintime'],
                        'deadTime'=>$input['endtime'],
                        'contentType'=>$input['sendtype'],
                        'title'=>$input['sendtitle'],
                        'message'=>$input['sendcontent'],
                        'website'=> $input['weburl']
                    ),
                ))
                ->message('', array(
                    'title' => '',
                    // 'content_type' => 'text',
                    'extras' => array(
                        'key' => 'value',
                        'jiguang'
                    ),
                ))
                ->options(array(
                    // sendno: 表示推送序号，纯粹用来作为 API 调用标识，
                    // API 返回时被原样返回，以方便 API 调用方匹配请求与返回
                    // 这里设置为 100 仅作为示例

                    // 'sendno' => 100,

                    // time_to_live: 表示离线消息保留时长(秒)，
                    // 推送当前用户不在线时，为该用户保留多长时间的离线消息，以便其上线时再次推送。
                    // 默认 86400 （1 天），最长 10 天。设置为 0 表示不保留离线消息，只有推送当前在线的用户可以收到
                    // 这里设置为 1 仅作为示例

                    // 'time_to_live' => 1,

                    // apns_production: 表示APNs是否生产环境，
                    // True 表示推送生产环境，False 表示要推送开发环境；如果不指定则默认为推送生产环境

                    'apns_production' => false,

                    // big_push_duration: 表示定速推送时长(分钟)，又名缓慢推送，把原本尽可能快的推送速度，降低下来，
                    // 给定的 n 分钟内，均匀地向这次推送的目标用户推送。最大值为1400.未设置则不是定速推送
                    // 这里设置为 1 仅作为示例

                    // 'big_push_duration' => 1
                ))
                ->send();
            $arr = array(
                'pushname'=>cookie('username'),
                'pushtime'=>date('Y-m-d H:i:s',time()),
                'pushobject'=>$input['user'],
                'pushtitle'=>$input['sendtitle'],
                'brandcode'=>cookie('brandcode'),
            );
            $rs = db('cms_historypush')->insert($arr);
            $this->success('邮件推送成功!','historypush');

        } catch (APIConnectionException $e) {
            Log::write($e);
            print 1;
            print $e;
        } catch (APIRequestException $e) {
            Log::write($e);
            print 2;
            print $e;
        }

    }
    //苹果推送
    public function iospushbyid($data)
    {
        /*$app_key ='';
        $master_secret = '';
        $client=new JPush( $client = new JPush($client= new JPush(app_key, $master_secret);
        // 完整的推送示例,包含指定Platform,指定Alias,Tag,指定iOS,Android notification,指定Message等
        $result = $client−>push()−>setPlatform(array(′ios′))−>addRegistrationId( client-&gt;push()-&gt;setPlatform(array(&#x27;ios&#x27;))-&gt;addRegistrationId(client−>push()−>setPlatform(array(
        ′
         ios
        ′
         ))->addRegistrationId(data)
                ->iosNotification('填写需要的内容', array(
        'badge'=>1,//这个是推送的消息显示的未查看的条数
        'title' => '填写需要的内容',
        'extras' => array(
        'key' =>value ,
        ),
        ));
        try {
            $response = result−>send();returnjson( result-&gt;send();return json(result−>send();returnjson(response);
        }catch (\JPush\Exceptions\APIConnectionException $e) {
            print $e;
        }catch(\JPush\Exceptions\APIRequestException $e) {
            print $e;
        }*/
    }
    public function historypush()
    {
        $input = input();
        $sqlwhere=array("","");
        $pushname=input("pushname");
        $sqlwhere=sqlwhereand("推送名称",$sqlwhere,"pushname",$pushname,1,1);
        $pushobject=input("pushobject");
        $sqlwhere=sqlwhereand("推送对象",$sqlwhere,"pushobject",$pushobject,1,1);
        $makedate1=input("begintime");
        $makedate2=input("endtime");
        $sqlwhere=sqlwheredate("推送日期",$sqlwhere,"pushtime",$makedate1,$makedate2);
        $pagesize = 10;
        //dump($sqlwhere);
        //$list = Db::table("bts_pi")->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);
        $list = db('cms_historypush')->where('brandcode',$_COOKIE['brandcode'])->where($sqlwhere[0])->order("id desc")->paginate($pagesize, false,
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

    public function emailpush()
    {
        if(checkpermissions_b('1004')==false)
        {
            return $this->fetch('public/hint');
        }
        return $this->fetch();
    }
    public function emailpush_show()
    {
        if(checkpermissions_b('1004')==false)
        {
            return $this->fetch('public/hint');
        }
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
        $pagesize = 10;
        //dump($sqlwhere);
        //$list = Db::table("bts_pi")->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);
        $subsql1 = db('country_mobile_prefix')->field('country,mobile_prefix,area')->buildSql();
        $list = db('app_customer')->field('id,email,location,isemail,regtime,brandcode,cc.country,cc.mobile_prefix,cc.area')->alias('c')->Join([$subsql1=>'cc'],"c.location=cc.mobile_prefix",'left')
            ->where('brandcode',$_COOKIE['brandcode'])->where($sqlwhere[0])->order("id desc")->paginate($pagesize, false,
                ['query' => request()->param(),
                    'type'     => 'layui',
                    'var_page' => 'page',]);

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
    //发送邮件
    public function send_mail()
    {
        set_time_limit(0);
        $input = input();
        $partpush = trim($input['partpush'],',');
        $partpush = explode(',',$partpush);
        $newarr = [];
        foreach($partpush as $k=>$v){
            if($v!="0"){
                $newarr[]=$v;
            }
        }
        //mydump(111);
        $filenametp = '';
        $filename = '';

        if ($_FILES["pushfile"]["name"]!='') {
            // 移动到框架应用根目录/uploads/ 目录下
            $files = request()->file('pushfile');
            $info = $files->move('uploads/email/');
            if ($info) {
                $filenametp = $info->getSavename();
                $filename = $_FILES["pushfile"]["name"];
            } else {
                // 上传失败获取错误信息
                //echo $file->getError();
                echo json_encode(
                    array(
                        'code' => '0',
                        'msg' => $files->getError(),
                    )
                );
                $this->error($files->getError());
            }
        }

        if($input['user']=='all'){
            $list = db('app_customer')->field('id,brandcode,email')
                ->where('brandcode',$_COOKIE['brandcode'])->order('id desc')->select();
        }else{
            if(empty($newarr)) {
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

                $pagesize = 10;
                //dump($sqlwhere);
                //$list = Db::table("bts_pi")->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);
                $list = db('app_customer')->field('id,email,brandcode')
                    ->where('brandcode',$_COOKIE['brandcode'])->where($sqlwhere[0])->order('id desc')->select();
            }else{
                $newarr = join(',',$newarr);
                //mydump($newarr);
                $list = db('app_customer')->field('id,email,brandcode')
                    ->where('brandcode',$_COOKIE['brandcode'])->where('id','in',"{$newarr}")->select();
            }

        }

        $data = array();
        foreach($list as $k=>$v){
            $params[] =
                [
                    'to'        => $v['email'],
                    'subject'   => $input['sendtitle'],
                    'body'   => $input['sendcontent'],
                    'filepath'   => "uploads/email/{$filenametp}",
                    'filename'   => $filename
                ];
        }

        //dump($params);
        $msg = json_encode($params,JSON_UNESCAPED_SLASHES);
        $this->send($msg);
        /*$mail = new Mail();
        $mail->sendmail($data,$input['sendtitle'],$input['sendcontent'],'html',"uploads/email/{$filename}","{$filename}");*/
        $this->success('邮件推送成功!','historypush');
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
    public function historypushmail()
    {
        $input = input();
        $sqlwhere=array("","");
        $pushname=input("pushname");
        $sqlwhere=sqlwhereand("推送名称",$sqlwhere,"pushname",$pushname,1,1);
        $pushobject=input("pushobject");
        $sqlwhere=sqlwhereand("推送对象",$sqlwhere,"pushobject",$pushobject,1,1);
        $makedate1=input("begintime");
        $makedate2=input("endtime");
        $sqlwhere=sqlwheredate("推送日期",$sqlwhere,"pushtime",$makedate1,$makedate2);
        $pagesize = 10;
        //dump($sqlwhere);
        //$list = Db::table("bts_pi")->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);
        $list = db('cms_historypush')->where('brandcode',$_COOKIE['brandcode'])->where($sqlwhere[0])->paginate($pagesize, false,
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
}