<?php

/**
 * 顶想云短信服务
 */
class TopthinkSmsPlugin
{
    public static $info = [
        "name"        => "顶想云短信服务",
        "type"        => "plugin",
        'alias'       => 'topthinkSms',
        "description" => "顶想云短信服务, 需要企业资料才可以使用！短信成本较低，审核容易通过且审核快",
        "author"      => "星河",
        "version"     => "1.0.0",
        "build"       => 1000,
        'link'        => 'https://console.topthink.com/sms/sign',
    ];

    protected $client;

    private $SignId;

    private $Token;

    private function getClient($config = [])
    {

        if (!isset($config['token']) || empty($config['token'])) {
            throw new \Exception("顶想云通用API授权码不能为空!", 1);
            return;
        }

        $this->Token = $config['token'];

        if (!isset($config['signId']) || empty($config['signId'])) {
            throw new \Exception("顶想云短信签名ID不能为空! ", 1);
        }

        $this->SignId = $config['signId'];

        // 引入类库
        include __DIR__ . '/vendor/autoload.php';

        $this->client = new \think\api\Client($this->Token);
    }

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
        if (!isset($params['template_id']) || !$params['template_id']) {
            return ['status' => 'error', 'msg' => '模板ID不能为空'];
        }

        $this->getClient($params['config'] ?? []);

        $result = $this->client->smsQueryTemplate()
            ->withId(trim($params['template_id']))
            ->request();
        $arr  = is_array($result) ? $result : json_decode($result, true);
        $data = [];
        if (isset($arr['code']) && $arr['code'] == 0) {
            $data["status"]                  = "success";
            $data['template']['template_id'] = $arr['data']['id'];
            //  本地状态: 1通过0待提交2失败3审核中
            //  接口状态: 审核状态 0 审核中 1 通过审核 -1 驳回
            if ($arr['data']['status'] == 0) {
                $template_status = 3;
            } elseif ($arr['data']['status'] == -1) {
                $data['template']['msg'] = $arr['data']['error'];
                $template_status         = 2;
            } else {
                $template_status = 1;
            }
            $data['template']['template_status'] = $template_status;
        } else {
            $data["status"]                      = "fail";
            $data["template"]["template_status"] = 3;
            $data["msg"]                         = '查询模板状态失败, ' . ($arr['message'] ?? '未知错误');
        }

        return $data;
    }

    public function createCnTemplate($params)
    {

        if (!isset($params['title']) || !$params['title']) {
            return ['status' => 'error', 'msg' => '缺少模板标题或标题为空'];
        }

        if (!isset($params['content']) || !$params['content']) {
            return ['status' => 'error', 'msg' => '缺少模板内容或内容为空'];
        }

        $this->getClient($params['config'] ?? []);

        $result = $this->client->smsAddTemplate()
            ->withName(trim($params['title']))
            ->withSignId($this->SignId)
            ->withContent(trim($params['content']))
            ->withType(1)
            ->withRemark(trim($params['remark'] ?? '线上商城, 用于网站会员注册登录找回密码订单状态通知等: ' . WEB_URL))
            ->request();
        $arr  = is_array($result) ? $result : json_decode($result, true);
        $data = [];
        if (isset($arr['code']) && $arr['code'] == 0) {
            $data["status"]                  = "success";
            $data['template']['template_id'] = $arr['data']['id'];
            //  本地状态: 1通过0待提交2失败3审核中
            //  接口状态: 审核状态 0 审核中 1 通过审核 -1 驳回
            // if ($arr['data']['status'] == 0) {
            //     $template_status = 3;
            // } elseif ($arr['data']['status'] == -1) {
            //     $data['template']['msg'] = $arr['data']['error'];
            //     $template_status         = 2;
            // } else {
            //     $template_status = 1;
            // }
            $template_status                     = 3;
            $data['template']['template_status'] = $template_status;
        } else {
            $data["status"]                      = "fail";
            $data["template"]["template_status"] = 3;
            $data["msg"]                         = '创建模板失败, ' . ($arr['message'] ?? '未知错误');
        }

        return $data;
    }

    public function putCnTemplate($params)
    {

        if (!isset($params['title']) || !$params['title']) {
            return ['status' => 'error', 'msg' => '缺少模板标题或标题为空'];
        }

        if (!isset($params['content']) || !$params['content']) {
            return ['status' => 'error', 'msg' => '缺少模板内容或内容为空'];
        }

        if (!isset($params['template_id']) || !$params['template_id']) {
            return ['status' => 'error', 'msg' => '模板ID不能为空'];
        }

        $this->getClient($params['config'] ?? []);

        $result = $this->client->smsModifyTemplate()
            ->withName(trim($params['title']))
            ->withId(trim($params['template_id']))
            ->withSignId($this->SignId)
            ->withContent(trim($params['content']))
            ->withType(1)
            ->withRemark(trim($params['remark'] ?? '线上商城, 用于网站会员注册登录找回密码订单状态通知等: ' . WEB_URL))
            ->request();
        $arr  = is_array($result) ? $result : json_decode($result, true);
        $data = [];
        if (isset($arr['code']) && $arr['code'] == 0) {
            $data["status"]                  = "success";
            $data['template']['template_id'] = $arr['data']['id'];
            //  本地状态: 1通过0待提交2失败3审核中
            //  接口状态: 审核状态 0 审核中 1 通过审核 -1 驳回
            // if ($arr['data']['status'] == 0) {
            //     $template_status = 3;
            // } elseif ($arr['data']['status'] == -1) {
            //     $data['template']['msg'] = $arr['data']['error'];
            //     $template_status         = 2;
            // } else {
            //     $template_status = 1;
            // }
            $template_status                     = 3;
            $data['template']['template_status'] = $template_status;
        } else {
            $data["status"]                      = "fail";
            $data["template"]["template_status"] = 3;
            $data["msg"]                         = '修改模板失败, ' . ($arr['message'] ?? '未知错误');
        }

        return $data;
    }

    public function deleteCnTemplate($params)
    {
        if (!isset($params['template_id']) || !$params['template_id']) {
            return ['status' => 'error', 'msg' => '模板ID不能为空'];
        }

        $this->getClient($params['config'] ?? []);

        $result = $this->client->smsDeleteTemplate()
            ->withId(trim($params['template_id']))
            ->request();
        $arr  = is_array($result) ? $result : json_decode($result, true);
        $data = [];
        if (isset($arr['code']) && $arr['code'] == 0) {
            $data["status"] = "success";
        } else {
            $data["status"] = "fail";
            // $data["template"]["template_status"] = 3;
            $data["msg"] = '删除模板失败, ' . ($arr['message'] ?? '未知错误');
        }
        return $data;
    }

    public function sendCnSms($params)
    {
        if (!isset($params['template_id']) || !$params['template_id']) {
            return ['status' => 'error', 'msg' => '模板ID不能为空'];
        }

        $this->getClient($params['config'] ?? []);

        $content = $this->templateParam($params['content'], $params['templateParam']);

        $result = $this->client->smsSend()
            ->withSignId($this->SignId)
            ->withTemplateId($params['template_id'])
            ->withPhone(trim($params['mobile']))
            ->withParams($this->templateParamArray($params['content'], $params['templateParam']))
            ->request();
        $arr  = is_array($result) ? $result : json_decode($result, true);
        $data = [];
        if (isset($arr['code']) && $arr['code'] == 0) {
            $data['status']  = 'success';
            $data['content'] = $content;
        } else {
            $data['status']  = "error";
            $data['content'] = $content;
            // $data["template"]["template_status"] = 3;
            $data["msg"]    = '发送失败, ' . ($arr['message'] ?? '未知错误');
            $data['params'] = $params;
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
        $data["status"] = "success";
        return $data;
    }

    private function templateParam($content, $templateParam)
    {
        foreach ($templateParam as $key => $para) {
            $content = str_replace('${' . $key . '}', $para, $content); //模板中的参数替换
        }
        $content = preg_replace("/\\$\{.*?\}/is", "", $content);
        return $content;
    }

    /**
     * 获取模板变量数量
     *
     * @param string $content
     * @return void
     */
    private function templateParamCount($content = '')
    {
        preg_match_all('/\$\{[\w]+\}/', $content, $match);
        return $match && count($match[0]) ? count($match[0]) : 0;
    }

    private function templateParamArray($content, $templateParam)
    {
        $params = [];
        foreach ($templateParam as $key => $val) {
            if (strpos($content, '${' . trim($key) . '}') !== false) {
                if (!in_array(trim($key), ['id', 'email', 'mobile', 'nick', 'site'])) {
                    if (empty($val)) {
                        $val = '';
                    }
                    $params[$key] = $val;
                }
            }
        }

        if (is_array($params)) {
            $params = json_encode($params, JSON_UNESCAPED_UNICODE);
        }
        return $params;
    }
}
