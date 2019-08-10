<?php
// +----------------------------------------------------------------------
// | API接口
// +----------------------------------------------------------------------
namespace app\api\controller;
use think\Controller;
use think\Db;
use think\Validate;
use think\Request;
use think\File;
use PHPMailer\PHPMailer;
use PHPMailer\Exception;
use app\api\controller\Websocket;
class User
{
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

    //API 注册接口
    public function register()
    {
        //base64_encode()
        $input = input();
        $input['password'] = base64_decode($input['password']);
        if(!empty($input))
        {
            $res = db('app_customer')->where("email='{$input['email']}'")->select();
            if($res)
            {
                echo json_encode(
                    array(
                        'code'=>'2',
                        'msg'=>'Mailbox registered'
                    )
                );
                exit();
            }
            $pwd = $input['password'];
            $input['password'] = md5($input['password']);
            $input['regtime']   = date("Y-m-d" ,time());//注册时间
            $input['intime']   = date("Y-m-d H:i:s" ,time());

            $body = setmailcontent($input['email'],'');//content
            $subject='Welcome to DEPSTECH';
            $params = [
                [
                        'to'        => $input['email'],
                        'subject'   => $subject,
                        'body'      => $body,
                ]
            ];
            //dump($params);
            $msg = json_encode($params);
            //mydump($msg);
            $this->send($msg);
            //$a = $this->sendmail($input['email'],$pwd,$subject,$body);

            $result = db('app_customer')->insert($input);
            //注册成功发送email给前用户
            //******************** 配置信息 ********************************
            //echo json_encode(array('msg'=>1));
            //修改phpmailer1860行，错误的邮箱返回json
            echo json_encode(
                array(
                    'code'=>'1',
                    'msg'=>'registered successfully',
                    'regtime'=>$input['regtime']
                )
            );

        }
    }
    //发送邮件方法
    public function sendmail($email,$pwd='',$subject='',$body='')
    {
        try {
            $mail = new PHPMailer(true); //New instance, with exceptions enabled
            //$body = file_get_contents('contents.html');
            $body = preg_replace('/\\\\/', '', $body); //Strip backslashes
            //$mail->SMTPDebug = 2;
            // 邮件正文为html编码
            $mail->Debugoutput = 'html';
            // 使用smtp鉴权方式发送邮件
            $mail->isSMTP();
            // smtp需要鉴权 这个必须是true
            $mail->SMTPAuth = true;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            // 设置使用ssl加密方式登录鉴权
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;// set the SMTP server port
            $mail->Host = "ssl://hwsmtp.exmail.qq.com"; // SMTP server
            $mail->Username = "support@depstech.com";// SMTP server username
            $mail->Password = "Shenhai2016";// SMTP server password
            //$mail->IsSendmail();  // tell the class to use Sendmail
            //$mail->AddReplyTo("17603012570@163.com", "First Last");
            $mail->From = "support@depstech.com";
            $mail->FromName = "Depstech";//发送者名字
            $to = $email;

            $mail->AddAddress($to);
            //$subject = "First PHPMailer Message";//邮件标题
            $mail->Subject = $subject;

            $mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
            $mail->WordWrap = 80; // set word wrap

            $mail->MsgHTML($body);

            $mail->IsHTML(true); // send as HTML

            $mail->Send();
        } catch (phpmailerException $e) {
            echo $e->errorMessage();
        }
    }
    //API 登录接口   1、未登录   2、已登录
    public function login()
    {
        $input = input();
        $email = $input['email'];
        $password = base64_decode($input['password']);
        $password = md5($password);
        $rs = db('app_customer')->where("email='{$email}'")->where("password='{$password}'")->find();
        if($rs){
            if(empty($rs['status'])||$rs['status']==1){
                unset($rs['password']);
                if($rs){
                    $update = array(
                        'status'=>2,
                        'token'=>settoken(),
                        'intime'=> date("Y-m-d H:i:s" ,time()),
                        'timeout'=> date("Y-m-d H:i:s" ,strtotime("+365 days"))
                    );
                    $rst = db('app_customer')->where('id',$rs['id'])->update($update);
                    $rs['token'] = $update['token'];
                    $rs['intime'] = $update['intime'];
                    $avatar = db('app_avatar')->where('uid',$rs['id'])->find();
                    if(empty($avatar['img'])){
                        $avatar['img'] = "icon.png";
                    }
                    $rs['avatar'] = HTTPS_SERVER .'uploads/images/avatar/'.$avatar['img'];
                    echo json_encode(
                        array(
                            'code'=>'1',
                            'msg'=>'login successfully',
                            'data'=>$rs
                        ),JSON_UNESCAPED_SLASHES
                    );
                    exit();
                }
            }

            //账号密码验证成功后，如果登陆状态是3已登录状态，返回给APP状态3，APP提示已登录，是否强行登录
            if($rs['status']==2){
                unset($rs['password']);
                $update = array(
                    'status'=>2,
                    'token'=>settoken(),
                    'intime'=> date("Y-m-d H:i:s" ,time()),
                    'timeout'=> date("Y-m-d H:i:s" ,strtotime("+365 days"))
                );
                //mydump($update);
                $rst = db('app_customer')->where('id',$rs['id'])->update($update);
                $rs['token'] = $update['token'];
                $avatar = db('app_avatar')->where('uid',$rs['id'])->find();
                if(empty($avatar['img'])){
                    $avatar['img'] = "icon.png";
                }
                $rs['avatar'] = HTTPS_SERVER.'uploads/images/avatar/'.$avatar['img'];
                echo json_encode(
                    array(
                        'code'=>'1',
                        'msg'=>'login successfully',
                        'data'=>$rs
                    ),JSON_UNESCAPED_SLASHES
                );
                exit();
            }
        }else {
            echo json_encode(
                array(
                    'code'=>'0',
                    'msg'=>'Wrong account or password',
                )
            );
        }

    }

    public function getdeviceinfo()
    {
        $input = input();
        $id = $input['id'];

        $token = base64_decode($input['token']);
        $result = db('app_customer')->field('token')->where("id={$id}")->find();
        if($result['token'] == $token){
            $data = array(
                'target'=>$input['target'],
                'appname'=>$input['appname'],
                'appversion'=>$input['appversion'],
                'devicetoken'=>base64_decode($input['devicetoken'])
            );
            $rs =db('app_customer')->where("id",$id)->update($data);
            echo json_encode(
                array(
                    'code'=>'1',
                    'msg'=>'bind successfully',
                )
            );
        }else{
            echo json_encode(
                array(
                    'code'=>'0',
                    'msg'=>'Token authentication fails'
                )
            );
        }
    }

    //用户退出登录 logout
    public function logout()
    {
        $input = input();
        $id = $input['id'];
        $token = base64_decode($input['token']);
        $result = db('app_customer')->field('token')->where("id={$id}")->find();
        if($result['token'] == $token){
            $arr = array('status'=>1,'token'=>'');
            $db = db('app_customer')->where("id={$input['id']}")->update($arr);
            echo json_encode(
                array(
                    'code'=>'1',
                    'msg'=>'Log out successfully',
                )
            );
        }else{
            echo json_encode(
                array(
                    'code'=>'0',
                    'msg'=>'Token authentication fails'
                )
            );
        }
    }
    //API 用户使用的测评 evaluating
    public function evaluating()
    {
        $input = input();
        $id = $input['id'];
        $evaluating = $input['evaluating'];
        $token = base64_decode($input['token']);
        //$brandcode = base64_decode($input['brandcode']);
        $result = db('app_customer')->field('token')->where("id={$id}")->find();
        if($result['token'] == $token){
            $data=array();
            $data['uid'] = $id;
            //$data['productname'] = $input['productname'];
            $data['ceping'] = $evaluating;
            $data['intime'] = date("Y-m-d H:i:s" ,time());
            //$data['brandcode'] = $brandcode;
            $result = db('app_ceping')->insert($data);
            echo json_encode(
                array(
                    'code'=>'1',
                    'msg'=>'Evaluation released successfully',
                )
            );
        }else{
            echo json_encode(
                array(
                    'code'=>'0',
                    'msg'=>'Token authentication fails'
                )
            );
        }
    }

    //API 用户反馈 feedback
    public function feedback()
    {
        $input = input();
        $id = $input['id'];
        $content = $input['content'];
        $token = base64_decode($input['token']);//base64_decode
        //$brandcode = base64_decode($input['brandcode']);
        $result = db('app_customer')->field('token,email')->where("id={$id}")->find();
        if($result['token'] == $token){
            $data = array();
            // 获取表单上传文件
            $files = request()->file('pic');
            if ($files) {
                foreach ($files as $k => $file) {
                    // 移动到框架应用根目录/uploads/ 目录下
                    $info = $file->validate(['size' => 10485760, 'ext' => 'jpg,png,gif'])->move('uploads/images/feedback/');
                    if ($info) {
                        // 成功上传后 获取上传信息
                        //echo $info->getExtension();// 输出 jpg
                        // 输出 42a79759f284b767dfcb2a0197904287.jpg
                        $data['pic' . ($k + 1)] = $info->getSavename();
                    } else {
                        // 上传失败获取错误信息
                        //echo $file->getError();
                        echo json_encode(
                            array(
                                'code' => '0',
                                'msg' => $file->getError(),
                            )
                        );
                        exit();
                    }
                }
            }
            $data['uid'] = $id;
            $data['content'] = $content;
            $data['intime'] = date("Y-m-d H:i:s" ,time());
            //$data['brandcode'] = $brandcode;
            $db = db('app_fankui')->where("id", $id)->insert($data);
            $subject = 'A Feedback from One Customer';
            $body = setfeedcontent($result['email']);
            $params = [
                [
                    'to'        => $result['email'],
                    'subject'   => $subject,
                    'body'   => $body,
                ]
            ];
            //dump($params);
            $msg = json_encode($params);
            //mydump($msg);
            $this->send($msg);
            echo json_encode(
                array(
                    'code' => '1',
                    'msg' => 'Feedback submitted successfully',
                )
            );
        }else {
            echo json_encode(
                array(
                    'code' => '2',
                    'msg'=>'Token authentication fails'
                )
            );
        }

    }
    //用户头像上传  avatar
    function avatar()
    {
        $input = input();
        $id = $input['id'];
        $token = base64_decode($input['token']);//base64_decode
        //$brandcode = base64_decode($input['brandcode']);
        $result = db('app_customer')->field('token')->where("id={$id}")->find();
        if($result['token'] == $token){
            $data = array();
            // 获取表单上传文件
            $files = request()->file('img');
            if ($files) {
                    // 移动到框架应用根目录/uploads/ 目录下
                    $info = $files->validate(['size' => 10485760, 'ext' => 'jpg,png,gif'])->move('uploads/images/avatar/');
                    if ($info) {
                        $data['img'] = $info->getSavename();
                        $data['uid'] = $id;
                        //mydump($upload_pic);
                        $db = db('app_avatar')->where("uid", $id)->find();
                        if($db){
                            $rs = db('app_avatar')->where("uid", $id)->update($data);
                        }else{
                            $rs =db('app_avatar')->insert($data);
                        }
                        echo json_encode(
                            array(
                                'code' => '1',
                                'msg' => 'uploaded successfully',
                                'avatar'=>"http://appserver.depstech.com/uploads/images/avatar/".$data['img']
                            ),JSON_UNESCAPED_SLASHES
                        );
                    } else {
                        // 上传失败获取错误信息
                        //echo $file->getError();
                        echo json_encode(
                            array(
                                'code' => '0',
                                'msg' => $files->getError(),
                            )
                        );
                        exit();
                    }
                }
        }else {
            echo json_encode(
                array(
                    'code' => '2',
                    'msg'=>'Token authentication fails'
                )
            );
        }
    }
    //忘记密码
    public function forgotten()
    {
        $input = input();
        $email = $input['email'];
        $rs = db('app_customer')->where("email='{$email}'")->find();
        if($rs){
            $password = substr(sha1(uniqid(mt_rand(), true)), 0, 10);
            $mdpwd = md5($password);
            $db = db('app_customer')->where("email='{$email}'")->update(array("password"=>$mdpwd));
            $subject = 'Password Has Been Changed';
            $body = setpwdcontent($email,$password);
            //$this->sendmail($email,$password,$subject,$body);
            $params = [
                [
                    'to'        => $email,
                    'subject'   => $subject,
                    'body'   => $body,
                ]
            ];
            //dump($params);
            $msg = json_encode($params);
            //mydump($msg);
            $this->send($msg);
            echo json_encode(
                array(
                    'code' => '1',
                    'msg'=>'successfully'
                )
            );
        }else{
            echo json_encode(
                array(
                    'code' => '0',
                    'msg'=>'email error'
                )
            );
        }
    }
    //修改密码
    public function changepwd()
    {
        $input = input();
        $id = $input['id'];
        $token = base64_decode($input['token']);
        $oldpassword = base64_decode($input['oldpassword']);
        $newpassword = base64_decode($input['newpassword']);
        $result = db('app_customer')->field('token')->where("id={$id}")->find();
        if($result['token'] == $token){
            $oldpassword = md5($oldpassword);
            $rs = db('app_customer')->where("id='{$id}'")->where("password='{$oldpassword}'")->find();
            if($rs){
                $data=array();
                $data['password'] = md5($newpassword);
                //$data['productname'] = $input['productname'];
                $result = db('app_customer')->where('id',$id)->update($data);
                echo json_encode(
                    array(
                        'code'=>'1',
                        'msg'=>'change password successfully',
                    )
                );
                exit();
            }else{
                echo json_encode(
                    array(
                        'code'=>'2',
                        'msg'=>'old password error',
                    )
                );
                exit();
            }
        }else{
            echo json_encode(
                array(
                    'code'=>'0',
                    'msg'=>'Token authentication fails'
                )
            );
        }

    }

    public function updatefirmware()
    {
        $input = input();
        $id = $input['id'];
        $token = base64_decode($input['token']);
        $brand_code = $input['brand_code'];
        $result = db('app_customer')->field('token')->where("id={$id}")->find();
        if($result['token'] == $token){
            $sql = "";
            //$sql = "SELECT * FROM (SELECT * from ((select * from app_firmware ORDER BY id DESC) a JOIN firmware_class b ON a.brandcode=b.brandcode ORDER BY a.id desc) GROUP BY classname";
            //$sql = "SELECT a.*,b.classname_en from app_firmware a JOIN firmware_class b ON a.classname=b.classname_en WHERE a.brandcode=2 GROUP BY classname ORDER BY updatedate desc;";
            $sql = "SELECT a.*,b.classname_en from app_firmware a JOIN firmware_class b ON a.classname=b.classname_en WHERE a.brandcode=2;";
            $rs = db('app_firmware')->query($sql);
            if($rs){
                $arr = [];
                foreach($rs as $k=>$v){
                    unset($v['classname_en']);
                    $arr[$v['classname']] = $v;
                }
                echo json_encode(
                    array(
                        'code' =>'200',
                        'msg'  =>'Get OK.',
                        'class'=>$arr
                    ),JSON_UNESCAPED_SLASHES
                );
            }else{
                echo json_encode(
                    array(
                        'code'=>'201',
                        'msg'=>'No data.'
                    )
                );
            }
        }else{
            echo json_encode(
                array(
                    'code'=>'0',
                    'msg'=>'Token authentication fails.'
                )
            );
        }
    }

}