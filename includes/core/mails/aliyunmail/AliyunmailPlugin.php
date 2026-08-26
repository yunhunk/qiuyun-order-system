<?php

use core\Random;
use Exception;

class AliyunmailPlugin extends \app\traits\AbstractPlugin
{

    # 基础信息
    public static $info = array(
        'name'        => '阿里云邮件发信',
        "type"        => "plugin",
        'alias'       => 'aliyunmail',
        'description' => '阿里云邮件发信, 稳定快捷提供免费2000封! 控制台: dm.console.aliyun.com',
        'status'      => 1,
        'author'      => '星河',
        'version'     => '1.0.0',
        'build'       => 1000,
        'link'        => 'https://dm.console.aliyun.com/',
    );

    private $AccessKey_Id;
    private $AccessKey_Secret;

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

        if (!isset($this->config['accessKeyId']) || !$this->config['accessKeyId']) {
            throw new Exception("参数不完整, accessKeyId不能为空");
        }

        if (!isset($this->config['accessKeySecret']) || !$this->config['accessKeySecret']) {
            throw new Exception("参数不完整, accessKeySecret不能为空");
        }

        if (!isset($this->config['systememail']) || !$this->config['systememail']) {
            throw new Exception("参数不完整, 未填写系统邮箱名");
        }

        $this->AccessKey_Id     = $this->config['accessKeyId'];
        $this->AccessKey_Secret = $this->config['accessKeySecret'];
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
    public function send($params = [])
    {
        if (isset($params['subject']) && $params['subject']) {
            $subject = $params['subject'];
        } elseif (isset($params['title']) && $params['title']) {
            $subject = $params['title'];
        }

        $url  = 'https://dm.aliyuncs.com/';
        $data = array(
            'Action'           => 'SingleSendMail',
            'AccountName'      => $this->config['systememail'] ?? ($params['systememail'] ?? ''),
            'ReplyToAddress'   => 'false',
            'AddressType'      => 1,
            'ToAddress'        => $params['email'] ?? '',
            'FromAlias'        => $this->config['fromname'] ?? conf('sitename'),
            'Subject'          => $subject ?: '您有新的通知!',
            'HtmlBody'         => $params['content'] ?? '',
            'Format'           => 'JSON',
            'Version'          => '2015-11-23',
            'AccessKeyId'      => $this->AccessKey_Id,
            'SignatureMethod'  => 'HMAC-SHA1',
            'Timestamp'        => gmdate('Y-m-d\TH:i:s\Z'),
            'SignatureVersion' => '1.0',
            'SignatureNonce'   => Random::alnum(8),
        );

        $data['Signature'] = $this->aliyunSignature($data, $this->AccessKey_Secret, 'POST');
        $ch                = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 超时10妙
        $timeout = 10;
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, 1);
        //强制协议为1.0
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        //强制使用IPV4协议解析域名  新加新加
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        //允许重定向
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $json     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode == 200) {
            return ['status' => 'success'];
        } else {
            $error = curl_error($ch);
            if (strstr($error, 'timed') !== false) {
                $error = '等待' . $timeout . '秒后超时，邮件接口无响应';
                return ['status' => 'error', 'msg' => $error];
            } else {
                $arr = json_decode($json, true);
                return ['status' => 'error', 'msg' => $arr['Message']];
            }
        }
    }

    private function aliyunSignature($parameters, $AccessKey_Secret, $method)
    {
        ksort($parameters);
        $canonicalizedQueryString = '';
        foreach ($parameters as $key => $value) {
            $canonicalizedQueryString .= '&' . $this->aliPercentEncode($key) . '=' . $this->aliPercentEncode($value);
        }
        $stringToSign = $method . '&%2F&' . $this->aliPercentEncode(substr($canonicalizedQueryString, 1));
        $signature    = base64_encode(hash_hmac("sha1", $stringToSign, $AccessKey_Secret . "&", true));

        return $signature;
    }

    private function aliPercentEncode($str)
    {

        $str = urlencode($str);
        $str = preg_replace('/\s/', '%20', $str);
        $str = preg_replace('/\+/', '%20', $str);
        $str = preg_replace('/\*/', '%2A', $str);
        $str = preg_replace('/%7E/', '~', $str);
        return $str;
    }
}
