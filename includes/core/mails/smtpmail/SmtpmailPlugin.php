<?php

use Exception;

class SmtpmailPlugin extends \app\traits\AbstractPlugin
{

    # 基础信息
    public static $info = array(
        'name'        => 'SMTP邮件发信',
        "type"        => "plugin",
        'alias'       => 'smtpmail',
        'description' => 'SMTP邮件发信, 支持163邮箱、QQ邮箱、126邮箱、搜狐邮箱、新浪邮箱等',
        'status'      => 1,
        'author'      => '星河',
        'version'     => '1.0.1',
        'build'       => 1001,
        'link'        => 'https://mail.163.com/',
    );

    public $isDebug = false;

    const ATTACHMENTS_ADDRESS = './upload/common/email/';

    public function __construct($pluginRow)
    {

        $pluginRow = $this->toArray($pluginRow);
        if (!is_array($pluginRow)) {
            throw new Exception("插件初始化参数缺失或异常");
        }

        if (!isset($pluginRow['config'])) {
            throw new Exception("插件初始化参数没有配置信息");
        }

        $this->config = json_decode($pluginRow['config'], true);

        if (!isset($this->config['host']) || !$this->config['host']) {
            throw new Exception("参数不完整, 未填写STMP服务器地址");
        }

        if (!isset($this->config['username']) || !$this->config['username']) {
            throw new Exception("参数不完整, 未填写STMP账号");
        }

        if (!isset($this->config['password']) || !$this->config['password']) {
            throw new Exception("参数不完整, 未填写STMP账号");
        }

        if (!isset($this->config['systememail']) || !$this->config['systememail']) {
            throw new Exception("参数不完整, 未填写系统邮箱名");
        }
    }

    /**
     * 获取插件信息
     *
     * @return array
     */
    public static function getPluginConfig()
    {
        return self::$info;
    }

    # 插件安装
    public function install()
    {
        return true;
    }

    # 插件卸载
    public function unInstall()
    {
        return true; //卸载成功返回true，失败false
    }

    /**
     * 获取插件配置项列表
     *
     * @return array
     */
    public static function getFormList()
    {
        return (include __DIR__ . '/config.php');
    }

    /**
     * 发送邮件
     *
     * @param array $params
     * @return array
     */
    public function send($params)
    {

        $mail = $this->getMail($params['config'] ?? []);

        $mail->addAddress($params['email'] ?? '');

        // $mail->addCC($params['email'] ?? '');

        // $mail->addBCC($params['email'] ?? '');

        if (isset($params['attachments']) && !empty($params['attachments'])) {
            $attachments = explode(',', $params['attachments']);
            foreach ($attachments as $attachment) {
                $originalName = explode('^', $attachment)[1];
                $mail->AddAttachment(self::ATTACHMENTS_ADDRESS . $attachment, $originalName);
            }
        }

        $mail->Body = $params['content'];

        if (isset($params['subject']) && $params['subject']) {
            $mail->Subject = $params['subject'];
        } elseif (isset($params['title']) && $params['title']) {
            $mail->Subject = $params['title'];
        }

        $result = $mail->send();
        $mail->ClearAllRecipients();
        if (!$result) {
            $encoding = mb_detect_encoding($mail->ErrorInfo, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'));
            if ($encoding != "UTF-8") {
                $mail->ErrorInfo = mb_convert_encoding($mail->ErrorInfo, "UTF-8", $encoding);
            }
            $msg = $mail->ErrorInfo;
            if (strpos($msg, ' connect') !== false) {
                $msg = '链接SMTP服务器失败, 可能当前服务器被屏蔽! 请尝试更换阿里云邮箱';
            }
            return ['status' => 'error', 'msg' => $msg];
        }
        return ['status' => 'success'];
    }

    private function getMail($config = [])
    {

        // 引入类库
        if (!class_exists('\PHPMailer\PHPMailer\PHPMailer', false)) {
            include __DIR__ . '/vendor/autoload.php';
        }

        if (!class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
            throw new Exception("缺少PHPMailer依赖");
        }

        $smtpsecure = trim(strtolower($config['smtpsecure']));

        if (!in_array($smtpsecure, ['tls', 'ssl'])) {
            $smtpsecure = 'tls';
        }

        if ($smtpsecure == 'tls') {
            $config['port'] = 587;
        } else if ($smtpsecure == 'ssl') {
            $config['port'] = 465;
        }

        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        $mail->clearCCs();
        $mail->clearBCCs();
        $mail->clearAddresses();
        $mail->clearAttachments();
        $mail->clearAllRecipients();
        // 设置为中文
        $mail->setLanguage('zh_cn');
        //调试模式
        $mail->SMTPDebug = $this->isDebug;
        //使用smtp鉴权方式发送邮件
        $mail->isSMTP();
        $mail->SMTPAuth   = true;
        $mail->Timeout    = 15;
        $mail->Host       = $config['host'];
        $mail->SMTPSecure = $smtpsecure;
        $mail->Port       = $config['port'];
        $mail->CharSet    = $config['charset'];
        $mail->FromName   = $config['fromname'];
        $mail->Username   = $config['username'];
        $mail->Password   = $config['password'];
        $mail->From       = $config['systememail'];
        $mail->isHTML(true);

        return $mail;
    }
}
