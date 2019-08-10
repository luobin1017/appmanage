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
class Websocket
{
    public function syncSend(){
        $serv = new \swoole_server('0.0.0.0',9501);

//设置异步任务的工作进程数量
        $serv->set(array('task_worker_num' => 4));

//监听数据接收事件
        $serv->on('receive', function($serv, $fd, $from_id, $data) {
            //投递异步任务
            $task_id = $serv->task($data);//非阻塞
            echo "同步代码执行完成\n";
        });

//处理异步任务
        $serv->on('task', function ($serv, $task_id, $from_id, $data) {
            $this->handleFun($data);
            //返回任务执行的结果
            $serv->finish("finish");
        });

//处理异步任务的结果
        $serv->on('finish', function ($serv, $task_id, $data) {
            echo "异步任务执行完成";
        });

        $serv->start();
    }

    function handleFun($data){
        $value = null;
        $arr = json_decode($data,true);
        if(isset($arr[0]['filename'])&&!empty($arr[0]['filename'] ))
        {
            foreach ($arr as $value) {
                $this->sendmail($value['to'],$value['subject'],$value['body'],$value['filepath'],$value['filename']);
            }
        }else{
            foreach ($arr as $value) {
                $this->sendmail($value['to'],$value['subject'],$value['body']);
            }
        }

    }

    public function sendmail($email,$subject='',$body='',$filepath='',$filename='')
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
            if($filename){
                $mail->AddAttachment($filepath,$filename); //path是附件的保存路径 name是附件的名字
            }

            $mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
            $mail->WordWrap = 80; // set word wrap

            $mail->MsgHTML($body);

            $mail->IsHTML(true); // send as HTML

            $mail->Send();
        } catch (phpmailerException $e) {
            echo $e->errorMessage();
        }
    }

    public function send()
    {
        $params = [
            [
                'to'        => '354279266@qq.com',
                'subject'   => '123',
                'content'   => '888',
                'body'   => 'From: test@example.com'
            ],
            [
                'to'        => '354279266@qq.com',
                'subject'   => '123',
                'content'   => '888',
                'body'   => 'From: test@example.com'
            ],

        ];
        $msg = json_encode($params);

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

    public function index()
    {
        $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        $ret = $client->connect("47.90.253.76", 9051);
        if(empty($ret)){
            echo 'error!connect to swoole_server failed';
        } else {
            $data = array(
                'email'=>'354279266@qq.com',
                'subject'=>'test subject',
                'body'=>'test body',
            );
            $data = json_encode($data);
            $client->send($data);//这里只是简单的实现了发送的内容
        }
    }

}