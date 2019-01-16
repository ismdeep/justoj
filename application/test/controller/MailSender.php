<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/10/12
 * Time: 10:38 PM
 */

namespace app\test\controller;


use app\extra\controller\BaseController;
use app\extra\util\smtp;


class MailSender extends BaseController
{
    /**
     * @return \think\response\Json
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function index()
    {
        //******************** 配置信息 ********************************
//        $smtpserver = "smtp.qq.com";//SMTP服务器
//        $smtpserverport = 465;//SMTP服务器端口
//        $smtpusermail = "799011746@qq.com";//SMTP服务器的用户邮箱
//        $smtpemailto = 'honi.linux@gmail.com';//发送给谁
//        $smtpuser = "799011746";//SMTP服务器的用户帐号
//        $smtppass = "gxiykjtuixuibcfh";//SMTP服务器的用户密码

        $smtpserver = "mail.jxust.edu.cn";//SMTP服务器
        $smtpserverport = 25;//SMTP服务器端口
        $smtpusermail = "oj@mail.jxust.edu.cn";//SMTP服务器的用户邮箱
        $smtpemailto = 'ismdeep@qq.com';//发送给谁
        $smtpuser = "oj";//SMTP服务器的用户帐号
        $smtppass = "wq1stHack3r";//SMTP服务器的用户密码

        $mailtitle = "OJ系统密码重置激活";//邮件主题
        $mailcontent = "User:\n您好！\n您在JustOJ系统选择了找回密码服务,为了验证您的身份,请将下面字串输入口令重置页面以确认身份:\n\n\n江西理工大学在线评测系统";//邮件内容
        $mailtype = "TXT";//邮件格式（HTML/TXT）,TXT为文本邮件

//        $mail = new PHPMailer();
//        $mail->SMTPDebug = 2;
//        $mail->isSMTP();
//        $mail->Host = '219.229.224.7';
//        $mail->Hostname = 'mail.jxust.edu.cn';
//        $mail->Port = 25;
//        $mail->CharSet = 'UTF-8';
//        $mail->FromName = 'oj';
//        $mail->Username = 'oj@mail.jxust.edu.cn';
//        $mail->Password = 'wq1stHack3r';
//        $mail->SMTPAuth = true;
//        $mail->SMTPSecure = false;
//        $mail->From = 'oj@mail.jxust.edu.cn';
//        $mail->setFrom('oj@mail.jxust.edu.cn', 'oj');
//        $mail->isHTML(true);
//        $mail->addAddress('ismdeep@qq.com', 'ismdeep');
//        $mail->Subject = '重置密码';
//        $mail->Body = 'https://oj.ismdeep.com';
//        $status = $mail->send();
//        return json([
//            'status' => 'success',
//            'data' => $status
//        ]);



        //************************ 配置信息 ****************************

        $smtp = new smtp('mail.jxust.edu.cn', 587, 3);
        $smtp->auth('oj', 'wq1stHack3r');
        $smtp->from('oj@mail.jxust.edu.cn', 'JustOJ');
        $smtp->mailFrom('oj@mail.jxust.edu.cn');
        $smtp->replyTo('oj@mail.jxust.edu.cn');
//        $smtp->priority(4);
//        $smtp->header('MyCustomHeader', 'The value of my custom header');
        $smtp->to('ismdeep@qq.com', 'ismdeep');
        $smtp->subject($mailtitle);
        $smtp->text($mailcontent, 'text/plain', 'utf-8');
        $smtp->charset('UTF-8'); // For subject, names, etc.
        $smtp->send();



        return json([
            'status' => 'success',
            'msg' => ''
        ]);
    }
}