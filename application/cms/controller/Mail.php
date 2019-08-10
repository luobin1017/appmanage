<?php
namespace app\cms\controller;
use think\Controller;
use think\Db;
use PHPMailer\PHPMailer;
use PHPMailer\Exception;

class Mail extends Controller
{
    //发邮件方法
    public function sendmail($email,$subject='',$body='',$bianma='html',$path='',$name='')
    {
        try {
            $mail = new PHPMailer(true); //New instance, with exceptions enabled
            //$body = file_get_contents('contents.html');
            $body = preg_replace('/\\\\/', '', $body); //Strip backslashes
            //$mail->SMTPDebug = 2;
            // 邮件正文为html编码
            $mail->Debugoutput = $bianma;
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
            if($path){
                $mail->AddAttachment($path,$name); //path是附件的保存路径 name是附件的名字
            }
            $to = $email;
            if(is_array($to)){
                foreach($to as $k=>$v){
                    $mail->addAddress($v);
                }
            }else{
                $mail->addAddress($to);
            }
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

}
