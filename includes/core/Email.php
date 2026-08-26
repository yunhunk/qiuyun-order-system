<?php
/* SMTP Class
 * Example:
 */
namespace core;

use core\PHPMailer\PHPMailer;

class Email
{
    private $mail = null;

    public $ssl = false;

    public $debug;

    public $time_out;

    public function __construct($relay_host = '', $smtp_port = 25, $auth = false, $user, $pass, $ssl = false)
    {
        $this->debug = false;
        if ($ssl == true) {
            $this->ssl  = true;
            $relay_host = 'ssl://' . $relay_host;
        }

        $this->time_out = 30;

        $this->mail = new PHPMailer();

        $this->mail->CharSet = "utf-8"; //设置采用utf-8中文编码

        $this->mail->IsSMTP(); //设置采用SMTP方式发送邮件

        $this->mail->Host = $relay_host; //设置邮件服务器的地址

        $this->mail->Port = $smtp_port; //设置邮件服务器的端口，默认为25

        $this->mail->SMTPAuth = $auth; //设置SMTP是否需要密码验证，true表示需要

        $this->mail->Username = $user;

        $this->mail->Password = $pass;

    }

    public function send($to, $from, $subject = "", $body = "", $fromname = "星河云商城Plus", $reply = '')
    {
        global $conf;

        if (!method_exists($this->mail, 'Send')) {
            return "发信异常，请先初始化邮件类再发送！";
        }

        $this->mail->From = $from; //设置发件人的邮箱地址

        $this->mail->FromName = $fromname; //设置发件人的姓名

        $this->mail->Subject = $subject; //设置邮件的标题

        $this->mail->AltBody = "text/html"; // optional, comment out and test

        $this->mail->Body = $body;

        $this->mail->IsHTML(true); //设置内容是否为html类型

        //$mail->WordWrap = 50;   //设置每行的字符数
        if ($reply) {
            $this->mail->AddReplyTo($from, $from); //设置回复的收件人的地址
        }

        $this->mail->AddAddress($to, "toName"); //设置收件的地址

        if ($this->mail->Send()) {
            return true;
        } else {
            return $this->mail->ErrorInfo;
        }
    }
}
