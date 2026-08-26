<?php
/**
 * QpayNotify.php 业务调用方可做二次封装
 * Created by HelloWorld
 * vers: v1.0.0
 * User: Tencent.com
 */
require_once 'qpayMchUtil.class.php';
class QpayNotify
{
    private $params;
    private $sign;

    public function getParams()
    {
        $post_data    = $_POST;
        $params       = QpayMchUtil::xmlToArray($post_data);
        $this->params = $params;
        $this->sign   = $params['sign'];
        return $params;
    }

    public function verifySign()
    {
        $sign = QpayMchUtil::getSign($this->params);
        return $sign == $this->sign;
    }
}
