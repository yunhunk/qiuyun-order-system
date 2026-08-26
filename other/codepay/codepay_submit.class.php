<?php
/**
 * 码支付创建支付发起类
 * 星河
 */
class codePaySubmit
{
    //订单数据
    private $type       = null;
    private $outTradeNo = null;
    private $returnURL  = null;
    private $notifyURL  = null;
    private $name       = null;
    private $money      = null;
    private $siteName   = null;
    private $remark     = null;

    //模拟方式
    private $curl_type = 0;

    //时间戳
    private $time = 0;

    public function __construct($config = [])
    {
        $this->AppId     = $config['AppId'];
        $this->AppSecret = $config['AppSecret'];
        $url             = $config['url'];
        if (empty($this->AppId)) {
            throw new \Exception("AppId不能为空！", 1);
        }

        if (empty($this->AppSecret)) {
            throw new \Exception("AppSecret不能为空！", 1);
        }

        if (!preg_match('/([\w\.\-])+\.[\w]{2,4}/', $url, $match1)) {
            throw new \Exception("url格式错误", 1);
        }
        $url          = $match1[0];
        $this->is_ssl = $config['is_ssl'];
        if ($this->is_ssl) {
            $this->url = "https://" . $url;
        } else {
            $this->url = "http://" . $url;
        }
        return true;
    }

    /**
     * 获取AppToken
     * @param  string $queryString 请求查询参数
     * @return string
     */
    private function getAppToken($queryString = '')
    {
        $s = $this->AppId . $this->AppSecret;
        $s .= $queryString . $this->time;
        return sha1($s);
    }

    /**
     * 设置商户订单号
     */
    public function setOutTradeNo($outTradeNo = '')
    {
        $this->outTradeNo = $outTradeNo;
        return $this;
    }

    /**
     * 设置同步回调
     */
    public function setReturnURL($url = '')
    {
        if (substr($url, 0, 4) !== 'http') {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . '/other/' . $url;
        }
        $this->returnURL = $url;
        return $this;
    }

    /**
     * 设置异步回调
     */
    public function setNotifyURL($url = '')
    {
        if (substr($url, 0, 4) !== 'http') {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . '/other/' . $url;
        }
        $this->notifyURL = $url;
        return $this;
    }

    /**
     * 设置订单商品名称
     */
    public function setName($name = '')
    {
        $this->name = $name;
        return $this;
    }

    /**
     * 设置订单网站名称
     */
    public function setRemark($remark = '')
    {
        $this->remark = $remark;
        return $this;
    }

    /**
     * 设置订单网站名称
     */
    public function setSiteName($siteName = '')
    {
        $this->siteName = $siteName;
        return $this;
    }

    /**
     * 设置支付方式
     * @param string $type alipay:支付宝,tenpay:财付通,wxpay:微信支付
     */
    public function setType($type = 'alipay')
    {
        $type = strtolower($type);
        if ($type == 'qqpay') {
            $type = 'tenpay';
        }
        $this->type = $type;
        return $this;
    }

    /**
     * 设置订单金额
     * @param float $money 订单金额
     */
    public function setMoney($money = 0.00)
    {
        $this->money = $money;
        return $this;
    }

    /**
     * 数组转字符串
     * @param  string $arr 数组
     * @return string
     */
    private function getArrayToString($arr = [])
    {
        $s = '';
        foreach ($arr as $key => $value) {
            $s .= $key . '=' . $value . '&';
        }
        return rtrim($s, '&');
    }

    /**
     * 创建订单
     * @return string
     */
    public function createOrder()
    {
        if (empty($this->type) || !in_array($this->type, ['alipay', 'tenpay', 'wxpay'])) {
            throw new \Exception("支付方式不正确！");
        } else if (empty($this->outTradeNo)) {
            throw new \Exception("商户订单号不能为空！");
        } else if (empty($this->returnURL)) {
            throw new \Exception("同步通知地址不能为空！");
        } else if (empty($this->notifyURL)) {
            throw new \Exception("异步通知地址不能为空！");
        } else if (empty($this->name)) {
            throw new \Exception("商品名称不能为空！");
        } else if (!preg_match('/^[\d\.]+$/', $this->money)) {
            throw new \Exception("订单金额不合法！");
        } else if (empty($this->siteName)) {
            throw new \Exception("网站名称不能为空！");
        }
        $queryString = '/api/notify/v1/order';
        $params      = [
            'type'       => $this->type,
            'outTradeNO' => $this->outTradeNo,
            'returnURL'  => $this->returnURL,
            'notifyURL'  => $this->notifyURL,
            'name'       => $this->name,
            'money'      => $this->money,
            'siteName'   => $this->siteName,
            'remark'     => $this->remark,
        ];
        $this->time = time();
        $url        = $this->url . $queryString;
        $AppToken   = $this->getAppToken($queryString);
        $header     = [
            'AppId: ' . $this->AppId,
            'AppToken: ' . $AppToken,
            'AppTimestamp: ' . $this->time,
        ];

        $text = $this->cmy_curl($url, json_encode($params), $header);
        $json = json_decode($text, true);
        if (is_array($json)) {
            if ($json['code'] == 100 && isset($json['result']['url'])) {
                return ['code' => 0, 'msg' => 'succ', 'code_url' => $json['result']['url']];
            } else if (isset($json['msg'])) {
                return ['code' => -1, 'msg' => $json['msg'], 'code_url' => ''];
            } else if (isset($json['info'])) {
                return ['code' => -1, 'msg' => $json['info'], 'code_url' => ''];
            } else if (isset($json['result']['msg'])) {
                return ['code' => -1, 'msg' => $json['result']['msg'], 'code_url' => ''];
            } else if (isset($json['result']['info'])) {
                return ['code' => -1, 'msg' => $json['result']['info'], 'code_url' => ''];
            } else {
                return ['code' => -1, 'msg' => '支付返回解析失败，' . $text, 'code_url' => ''];
            }
        } else {
            return ['code' => -1, 'msg' => '支付返回解析失败，' . $text, 'code_url' => ''];
        }
    }

    private function cmy_curl($url, $post = 0, $header = 0, $timeout = 30)
    {

        if ($this->curl_type == 1) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            $httpheader[] = "Accept: */*";
            $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
            $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
            $httpheader[] = "Connection: close";
            $httpheader[] = "Content-Type:application/x-www-form-urlencoded; charset=UTF-8";
            $httpheader[] = "X-Requested-With:XMLHttpRequest";
            if (is_array($header) && count($header)) {
                $httpheader = array_merge($httpheader, $header);
            }
            if ($post) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36');
            curl_setopt($ch, CURLOPT_ENCODING, "gzip");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $ret      = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode != 200) {
                if ($httpCode == 404 || $httpCode == 403 || preg_match('/^3[\d]{2}$/', $httpCode)) {
                    $ret = '[' . $httpCode . ']该网站已经迁移或更新，请咨询该平台网站是否有做更改';
                } else {
                    $ret = '[' . $httpCode . ']' . curl_error($ch);
                }
            }
            curl_close($ch);

        } else {
            //file_get_contents 速度更快
            $httpHeader = "";
            if ($header && is_array($header)) {
                foreach ($header as $key => $value) {
                    $httpHeader .= "\n" . $value;
                }
            }
            $httpHeader .= "\nX-Requested-With:XMLHttpRequest";
            if (strtolower(substr($url, 0, 5)) == 'https') {
                $opts = array(
                    'https' => array(
                        'method'  => "POST",
                        'header'  => "Content-Type: application/x-www-form-urlencoded; charset=UTF-8" . $httpHeader,
                        'timeout' => $timeout, //单位秒
                        'content' => $post,
                    ),
                );
            } else {
                $opts = array(
                    'http' => array(
                        'method'  => "POST",
                        'header'  => "Content-Type: application/x-www-form-urlencoded; charset=UTF-8" . $httpHeader,
                        'timeout' => $timeout, //单位秒
                        'content' => $post,
                    ),
                );
            }
            $ret = file_get_contents($url, false, stream_context_create($opts));
        }
        return $ret;
    }
}
