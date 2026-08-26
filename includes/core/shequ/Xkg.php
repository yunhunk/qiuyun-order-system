<?php
/**
 * Name   新卡购Api类
 * Author 星河
 * Time   2021-04-30 13:10
 */
namespace core;

use core\Card;

class Xkg extends Card
{
    private $customerid    = ''; //用户编号
    private $tradepassword = ''; //交易密码
    private $key           = '';
    private $url           = '';
    private $isSsl         = 0;
    private $config        = [];

    /**
     * 构造方法初始化
     * @param array   $config        配置信息
     * @param string  $tradepassword 平台交易密码
     * @param string  $key           平台账号密钥
     * @param integer $_ssl          是否https
     */
    public function __construct($config = [], $tradepassword = '', $key = '', $_ssl = 0)
    {
        if ($config['url'] == "") {
            throw new \Exception("平台地址不能为空！");
            return;
        }
        if ($config['username'] == "") {
            throw new \Exception("平台用户编号不能为空！");
            return;
        }
        if ($tradepassword == "" && $config['password'] == "") {
            throw new \Exception("平台交易密码不能为空！");
            return;
        }
        if ($key == "" && $config['paypwd'] == "") {
            throw new \Exception("平台密钥key不能为空！");
            return;
        }
        $this->customerid    = $config['username'];
        $this->tradepassword = !empty($tradepassword) ? $tradepassword : $config['password'];
        $this->key           = !empty($key) ? $key : $config['paypwd'];
        $config['key']       = $this->key;
        $this->config        = $config;
        if ($_ssl) {
            $this->url = "https://" . $config['url'];
        } else {
            $this->url = "http://" . $config['url'];
        }
        $this->isSsl = $_ssl;
    }
    /**
     * 键值对字符串转数组
     * @param  string $param 数据字符串
     * @return string
     */
    private function paramsToArray($param)
    {
        $ret  = [];
        $arr1 = explode('&', $param);
        foreach ($arr1 as $key => $value) {
            $arr2 = explode('=', $value, 2);
            if (count($arr2) == 2 && $arr2[1]) {
                $ret[$arr2[0]] = trim($arr2[1]);
            }
        }
        return $ret;
    }

    /**
     * 获取错误信息
     * @param  integer $code 状态码
     * @return string
     */
    private function getErrorInfo($code = 2001)
    {
        $arr = [
            403  => "禁止访问",
            404  => "请求方法不存在或无权限",
            1000 => "下单成功",
            1001 => "请求参数不合法",
            1003 => "签名错误",
            1004 => "访问频繁，两次间隔不能低于10秒钟",
            1005 => "请求方式错误",
            1006 => "客户编号不存在",
            1007 => "客户编号已经被禁用",
            1008 => "未开通供货接口功能",
            1009 => "未开通进货接口功能",
            1010 => "下单失败",
            2001 => "系统未知错误",
        ];
        return isset($arr[$code]) ? $arr[$code] : '系统未知错误[' . $code . ']';
    }

    /**
     * 获取sign签名
     * @param  string $param 参数数据
     * @param  string $key   key
     * @return string
     */
    private function getMd5Sign($param, $key)
    {
        $signPars = "";
        if (!is_array($param)) {
            //转数组
            $param = $this->paramsToArray((string) $param);
        }
        ksort($param);
        foreach ($param as $k => $v) {
            if ("sign" != $k && "" != $v) {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars = trim($signPars, '&');
        $signPars .= $key;
        $sign = strtoupper(md5($signPars));
        return $sign;
    }

    public function doOrder($row, $config = [], $tool = [])
    {
        global $DB, $date, $webConfig;
        if (empty($this->tradepassword)) {
            return '交易密码不能为空！';
        } elseif (strlen($this->tradepassword) >= 128) {
            return '交易密码需要先转为明文才能操作！';
        } elseif (!is_object($DB)) {
            return '下单失败，调用方法异常！';
        }

        if ($row['status'] == -1) {
            $DB->query("UPDATE `pre_orders` set status='0', djzt='2' where id=:id", [':id' => $row['id']]);
        } elseif ($row['status'] > 0) {
            return '该订单已对接处理！';
        }

        //获取商品信息
        if (!$tool['tid']) {
            $tool = $DB->get_row("SELECT * FROM `pre_tools` where tid=:tid limit 1", [':tid' => $row['tid']]);
        }

        if (!is_array($tool)) {
            return '商品信息不完整，无法对接下单！';
        }

        if ($row["stock_id"] > 0) {
            $stock_row = $DB->get_row("SELECT * FROM `pre_stock` where id='" . $row["stock_id"] . "'");
            $num       = $stock_row['value'] > 1 ? intval($stock_row["value"] * $row["num"]) : $row["num"];
        } else {
            $tool["value"] = $tool["value"] > 0 ? $tool["value"] : 1;
            $row["value"]  = $row["value"] > 0 ? $row["value"] : 1;
            $num           = intval($tool["value"] * $row["value"]);
        }

        $post = [
            'customerid'    => $this->customerid,
            'goodsid'       => $tool['goods_id'],
            'tradepassword' => $this->tradepassword,
            'quantity'      => $num,
            'mark'          => 'Api下单',
        ];

        if (in_array($tool['goods_type'], ['1', '2', '3'])) {
            $post['accountname']   = $row['input'];
            $post['reaccountname'] = $row['input'];
            for ($i = 0; $i < 5; $i++) {
                if ($i == 0) {
                    $post['lblName0'] = $row['input'];
                } else {
                    if ($row['input' . ($i + 1)]) {
                        $post['lblName' . $i] = $row['input' . ($i + 1)];
                    }
                }
            }
        }
        //下单日志参数
        $params = http_build_query($post);

        //获取完整参数
        $post['sign'] = md5($this->customerid . $tool['goods_id'] . $this->key);
        //接口地址
        if ($tool['goods_type'] == 1) {
            $url = $this->url . '/api.php/buyer/buyCardGoodOrder';
        } else {
            $url = $this->url . '/api.php/buyer/buyGoodOrder';
        }
        $text = $this->chenm_curl($url, http_build_query($post));
        if ($webConfig['debug']) {
            @addWebLog('购买商品', "创新售=> " . $url . "\ndata => " . json_encode($post) . "\nresult => " . $text, "Shequ", 1);
        }
        $arr = json_decode($text, true);
        if (is_array($arr)) {
            if ($arr['code'] == 1000) {
                $sql    = "UPDATE `pre_orders` set `endtime`=:endtime,`djorder`=:djorder,`bz`=:bz,`djzt`=1,`status`=:status where id=:id";
                $status = 1;
                if (isset($config['orderstatus'])) {
                    $status = $config['orderstatus'];
                }
                if (isset($arr['data']['orderno'])) {
                    $djorder = $arr['data']['orderno'];
                } else {
                    preg_match('/[\d]{12,20}/', $text, $match);
                    if ($match[0]) {
                        $djorder = $match[0];
                    } else {
                        $djorder = isset($arr['orderno']) ? $arr['orderno'] : $text;
                    }
                }

                $sql_data = array(
                    ':endtime' => $date,
                    ':djorder' => $djorder,
                    ':bz'      => '对接下单成功，订单号：' . $djorder,
                    ':status'  => $status,
                    ':id'      => $row['id'],
                );
                $DB->query($sql, $sql_data);
                if ($tool['goods_type'] == 1) {
                    $row['djorder'] = $djorder;
                    $this->query($row);
                }
                log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, '下单成功，订单ID' . $djorder, 1, $row['id']);
                return '下单成功，订单ID' . $djorder;
            } else {
                if (isset($arr['info'])) {
                    $message = '下单失败，' . $arr['info'];
                } elseif (isset($arr['msg'])) {
                    $message = '下单失败，' . $arr['msg'];
                } else {
                    $message = '下单失败，' . $this->getErrorInfo($arr['code']);
                }
                $DB->query("UPDATE `pre_orders` set `djzt`='2', `status`='0', `endtime`= ? where id= ?", [$date, $row['id']]);
                log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $message, 1, $row['id']);
                return $message;
            }
        } else {
            $DB->query("UPDATE `pre_orders` SET `djzt`='2', `status`='0', `endtime`= ? where id= ?", [$date, $row['id']]);
        }
        if (empty($text)) {
            $message = '返回数据为空，请检查是否有拦截、维护、已经迁移的情况';
        } else {
            $message = '返回数据解析失败，可能网站打不开或出现卡死！' . $text;
        }
        log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $message, 1, $row['id']);
        return $message;

    }

    /**
     * 查询订单
     * @param  array   $row     订单信息
     */
    public function query($row = [])
    {
        global $DB, $date, $webConfig;
        if (!is_array($row) && empty($row['djorder'])) {
            return ['code' => -1, 'msg' => '订单号不能为空或参数类型错误！'];
        }
        //获取商品信息
        $tool = $DB->get_row("SELECT * FROM `pre_tools` where tid=:tid limit 1", [':tid' => $row['tid']]);
        if (!is_array($tool)) {
            return ['code' => -1, 'msg' => '该订单系统商品信息不完整，无法查询详情，请联系客服查询处理！'];
        }
        $post = [
            'orderno' => $row['djorder'],
        ];

        $post['sign'] = md5($this->customerid . $row['djorder'] . $this->key);
        $url          = $this->url . '/api.php/buyer/orderInfo';
        $text         = $this->chenm_curl($url, http_build_query($post));
        if ($webConfig['debug']) {
            addWebLog('查询订单', "新卡购=> " . $url . "\ndata => " . json_encode($post) . "\nresult => " . $text, "Shequ", 1);
        }
        $arr = json_decode($text, true);
        if (is_array($arr)) {
            if ($arr['code'] == 1000) {
                if (isset($arr['data']['cards']) && count($arr['data']['cards'])) {
                    $cardObj = new \core\Card();
                    $ret     = $cardObj->getCardData($row, $config, $arr['data']['cards']);
                    $kmdata  = '';
                    $sql     = "UPDATE `pre_orders` SET `status`=:order_state,`result`=:result,`djzt`=:djzt WHERE id=:id";
                    if (!empty($ret['kmdata'])) {
                        $kmdata   = "卡密信息如下，请参考商品介绍使用：<br>\r\n" . $ret["kmdata"];
                        $sql_data = array(
                            ':order_state' => 1,
                            ':result'      => $kmdata,
                            ':djzt'        => 3,
                            ':id'          => $row['id'],

                        );
                    } else {
                        $kmdata   = '已**，请联系平台客服查询';
                        $sql_data = array(
                            ':order_state' => 1,
                            ':result'      => $kmdata,
                            ':djzt'        => 4,
                            ':id'          => $row['id'],
                        );
                        log_result(getShequTypeName($config["type"]) . "对接", $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $post, '卡密识别失败，' . $ret['msg'], 1, $orderid);
                    }

                    $DB->query($sql, $sql_data);
                }

                if ($row['status'] == 2) {
                    //同步订单
                    if (in_array($arr['data']['status'], ['4', '5'])) {
                        $sql_data = array(
                            ':status' => 3,
                            ':id'     => $row['id'],
                        );
                    } elseif ($arr['data']['status'] == 3) {
                        $sql_data = array(
                            ':status' => 1,
                            ':id'     => $row['id'],
                        );
                    } else {
                        $sql_data = null;
                    }

                    if (null !== $sql_data) {
                        $sql = "UPDATE `pre_orders` set status=:status where id=:id";
                        $DB->query($sql, $sql_data);
                    }
                }
                $status_arr = array(
                    '1' => '等待处理',
                    '2' => '正在处理',
                    '3' => '交易完成',
                    '4' => '交易失败',
                    '5' => '成功已退款',
                );
                $status_code = isset($arr['data']['ostatus']) ? $arr['data']['ostatus'] : $arr['data']['status'];
                $order_state = $status_arr[$status_code];
                if (empty($order_state)) {
                    $order_state = '未知状态';
                }
                $ret['data']['orderid']     = $row['djorder'];
                $ret['data']['num']         = $json['need_num_0'];
                $ret['data']['add_time']    = $row['addtime'];
                $ret['data']['start_num']   = 0;
                $ret['data']['now_num']     = $arr['data']['status'] == 3 ? $arr['data']['quantity'] : 0;
                $ret['data']['siteurl']     = $this->url;
                $ret['data']['order_state'] = $order_state;
                $ret['data']['shopUrl']     = null;
                $ret['msg']                 = "查询成功，订单状态【" . $order_state . "】；状态码【" . $status_code . "】";
            } else {
                $ret = [
                    'code' => 0,
                    'msg'  => '查询失败，' . isset($arr['msg']) ? $arr['msg'] : $arr['info'],
                ];
            }
            return $ret;
        } else {
            $result = substr(htmlspecialchars($text), 0, 500);
            $DB->query("UPDATE `pre_orders` set `djzt`='2', `status`='0', `endtime`= ? where id= ?", [$date, $row['id']]);
            $ret = array('code' => -1, "msg" => "查询订单详情解析失败，请稍后重试！<br>查询返回文本：" . $result, "result" => $text);
            return $ret;
        }
    }

    private function chenm_curl($url, $post = 0, $cookie = 0, $timout = 10)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $httpheader[] = "Accept: */*";
        $httpheader[] = "Connection: close";
        $httpheader[] = "Content-Type:application/x-www-form-urlencoded";
        $httpheader[] = "X-Requested-With: XMLHttpRequest";

        curl_setopt($ch, CURLOPT_TIMEOUT, $timout);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);

        if ($cookie) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }

        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 UBrowser/6.2.4098.3 Safari/537.36');

        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != 200 && $ret == "") {
            $ret = '[' . $httpCode . ']' . curl_error($ch);
        }
        curl_close($ch);
        return $ret;
    }
}
