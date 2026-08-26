<?php
/**
 * qpayQuery.php
 * vers: v1.0.0
 * User: 星河
 */
require_once ROOT . '/other/qqpay/qpayMchUtil.class.php';
class QpayQuery
{
    private $url     = 'https://qpay.qq.com/cgi-bin/pay/qpay_order_query.cgi';
    private $isSSL   = false;
    private $timeout = 10;
    private $xml     = '';
    private $error   = '';
    private $result  = array();

    public function __construct($out_trade_no, $isSSL = 0, $timeout = 5)
    {
        $this->isSSL   = $isSSL;
        $this->timeout = $timeout;
        $this->query($out_trade_no);
    }

    /**
     * 订单查询
     * @param string $out_trade_no
     *
     * @return array
     */
    private function query($out_trade_no)
    {
        if (empty($out_trade_no)) {
            $this->error = '订单号不能为空！';
        } else {
            $params                 = array();
            $params["out_trade_no"] = $out_trade_no;
            //商户号
            $params["mch_id"] = QpayMchConf::$MCH_ID;
            //随机字符串
            $params["nonce_str"] = QpayMchUtil::createNoncestr();

            //签名
            $params["sign"] = QpayMchUtil::getSign($params);

            $xml = QpayMchUtil::arrayToXml($params);
            if ($this->isSSL) {
                $ret = QpayMchUtil::reqByCurlSSLPost($xml, $this->url, $this->timeout);
            } else {
                $ret = QpayMchUtil::reqByCurlNormalPost($xml, $this->url, $this->timeout);
            }

            $arr          = QpayMchUtil::xmlToArray($ret);
            $this->result = $arr;
        }
    }

    /**
     * 订单验证
     *
     * @return Boolean
     */
    public function check()
    {
        if (!empty($this->error)) {
            return ['code' => -1, 'msg' => $this->error];
        } else if (empty($this->result['result_code'])) {
            return ['code' => -1, 'msg' => '订单查询失败'];
        } else {
            if ($this->result['trade_state'] === 'USERPAYING') {
                return ['code' => -1, 'msg' => '用户支付中'];
            } elseif ($this->result['trade_state'] === 'SUCCESS') {
                $sign = QpayMchUtil::getSign($this->result);
                return ['code' => $sign == $this->result['sign'] ? 1 : -1, 'msg' => '成功付款'];
            } else {
                return ['code' => -1, 'msg' => '待付款'];
            }
        }
    }
}
