<?php

/**
 * 短信宝插件
 */
class SmsbaoPlugin extends \app\traits\AbstractPlugin
{
    public static $info = [
        "name"        => "短信宝",
        "type"        => "plugin",
        'alias'       => 'smsbao',
        "description" => "短信宝",
        "author"      => "星河",
        "version"     => "1.0.1",
        "build"       => 1002,
        'link'        => 'http://smsbao.com/',
    ];

    /*
     * 获取插件信息
     *
     * @return array
     */
    public static function getPluginConfig()
    {
        return static::$info;
    }

    public function smsleidcsmartauthorize()
    {

    }

    public function install()
    {
        $smsTemplate = [];
        if (file_exists(__DIR__ . "/config/smsTemplate.php")) {
            $smsTemplate = (require __DIR__ . "/config/smsTemplate.php");
        }
        return $smsTemplate;
    }

    /**
     * 获取模板变量html
     *
     * @return string
     */
    public function getTemplateVarsHtml()
    {
        $smsTemplate = '';
        if (file_exists(__DIR__ . "/config/description.html")) {
            $smsTemplate = file_get_contents(__DIR__ . "/config/description.html");
        }
        return $smsTemplate;
    }

    /**
     * 获取短信模板列表
     *
     * @return string
     */
    public function getTemplateList()
    {
        $smsTemplate = [];
        if (file_exists(__DIR__ . "/config/smsTemplate.php")) {
            $smsTemplate = (require __DIR__ . "/config/smsTemplate.php");
        }
        return $smsTemplate;
    }

    public function unInstall()
    {
        return true;
    }

    public function getCnTemplate($params)
    {
        $data["status"]                      = "fail";
        $data["template"]["template_status"] = 0;
        $data["msg"]                         = '该短信接口需要手动前往“短信宝控制台->国内通用短信->免费报备VIP通道模板”添加';
        return $data;
    }

    public function createCnTemplate($params)
    {
        $data["status"]                      = "fail";
        $data["template"]["template_status"] = 0;
        $data["msg"]                         = '该短信接口需要手动前往“短信宝控制台->国内通用短信->免费报备VIP通道模板”添加';
        return $data;
    }

    public function putCnTemplate($params)
    {
        $data["status"]                      = "fail";
        $data["template"]["template_status"] = 0;
        $data["msg"]                         = '该短信接口需要手动前往“短信宝控制台->国内通用短信->免费报备VIP通道模板”添加';
        return $data;
    }

    public function deleteCnTemplate($params)
    {
        $data["status"] = "success";
        return $data;
    }

    public function sendCnSms($params)
    {
        $content          = $this->templateParam($params["content"], $params["templateParam"]);
        $param["content"] = $this->templateSign($params["config"]["sign"]) . $content;
        $param["mobile"]  = trim($params["mobile"]);
        $resultTemplate   = $this->APIHttpRequestCURL("cn", $param, $params["config"]);
        if ($resultTemplate["status"] == "success") {
            $data["status"]  = "success";
            $data["content"] = $content;
        } else {
            $data["status"]  = "error";
            $data["content"] = $content;
            $data["msg"]     = $resultTemplate["msg"];
        }
        return $data;
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
     * @return array
     */
    public function getGlobalTemplate($params)
    {
        $data["status"]                      = "fail";
        $data["template"]["template_status"] = 0;
        $data["msg"]                         = '该短信接口需要手动前往“短信宝控制台->国内通用短信->免费报备VIP通道模板”添加';
        return $data;
    }

    /**
     * @return array
     */
    public function createGlobalTemplate($params)
    {

        $data["status"]                      = "fail";
        $data["template"]["template_status"] = 0;
        $data["msg"]                         = '该短信接口需要手动前往“短信宝控制台->国内通用短信->免费报备VIP通道模板”添加';
        return $data;
    }

    /**
     * @return array
     */
    public function putGlobalTemplate($params)
    {
        $data["status"]                      = "fail";
        $data["template"]["template_status"] = 0;
        $data["msg"]                         = '该短信接口需要手动前往“短信宝控制台->国内通用短信->免费报备VIP通道模板”添加';
        return $data;
    }

    /**
     * @return array
     */
    public function deleteGlobalTemplate($params)
    {
        $data["status"] = "success";
        return $data;
    }

    /**
     * @return array
     */
    public function sendGlobalSms($params)
    {
        $content          = $this->templateParam($params["content"], $params["templateParam"]);
        $param["content"] = $this->templateSign($params["config"]["sign"]) . $content;
        $param["mobile"]  = trim($params["mobile"]);
        $resultTemplate   = $this->APIHttpRequestCURL("global", $param, $params["config"]);
        if ($resultTemplate["status"] == "success") {
            $data["status"]  = "success";
            $data["content"] = $content;
        } else {
            $data["status"]  = "error";
            $data["content"] = $content;
            $data["msg"]     = $resultTemplate["msg"];
        }
        return $data;
    }

    /**
     * @return array
     */
    private function APIHttpRequestCURL($sms_type = "cn", $params = [], $config = [])
    {
        if ($sms_type == "cn") {
            $url = "http://smsbao.com/sms";
        } else {
            if ($sms_type == "global") {
                $url = "http://smsbao.com/sms";
            }
        }
        $statusStr = [
            "短信发送成功",
            "18446744073709551615" => "参数不全",
            "18446744073709551614" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
            "30"                   => "密码错误",
            "40"                   => "账号不存在",
            "41"                   => "余额不足",
            "42"                   => "帐户已过期",
            "43"                   => "IP地址限制",
            "50"                   => "内容含有敏感词",
            "51"                   => "手机号码不正确",
        ];

        if (!isset($config["user"]) || !$config["user"]) {
            return ["status" => "error", "msg" => '配置错误，短信宝账号不能为空!'];
        }

        if (!isset($config["pass"]) || !$config["pass"]) {
            return ["status" => "error", "msg" => '配置错误，短信宝密码不能为空!'];
        }

        if (!isset($config["sign"]) || !$config["sign"]) {
            return ["status" => "error", "msg" => '配置错误，短信宝签名不能为空!'];
        }

        if (!isset($params["mobile"]) || !$params["mobile"]) {
            return ["status" => "error", "msg" => '接收手机号不能为空!'];
        }

        $user    = $config["user"];
        $pass    = md5($config["pass"]);
        $content = $params["content"];
        $phone   = $params["mobile"];
        $sendurl = $url . "?u=" . $user . "&p=" . $pass . "&m=" . $phone . "&c=" . urlencode($content);
        $result  = file_get_contents($sendurl);
        if ($result == "0") {
            return ["status" => "success", "msg" => $statusStr[$result]];
        }
        return ["status" => "error", "msg" => $statusStr[$result] . ". Code: " . $result];
    }

    /**
     * @return string
     */
    private function templateParam($content, $templateParam)
    {
        foreach ($templateParam as $key => $para) {
            $content = str_replace("{" . $key . "}", $para, $content);
        }
        return $content;
    }

    /**
     * @return string
     */
    private function templateSign($sign)
    {
        $sign = str_replace("【", "", $sign);
        $sign = str_replace("】", "", $sign);
        $sign = "【" . $sign . "】";
        return $sign;
    }
}
