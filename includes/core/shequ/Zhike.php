<?php

use core\Card;

/**
 * Name   直客Api类
 * Author 星河
 * Time   2021-07-13 21:16
 */

class Zhike extends Card
{
    private $AppId     = '';
    private $AppSecret = '';
    private $config    = [];
    public $url        = 'www.zhikeshop.cn';
    private $is_ssl    = 0;
    private $time      = 0;

    /**
     * 货源站构造初始化
     * @param array   $config 货源站信息数组
     * @param integer $is_ssl 是否ssl
     */
    /**
     * 货源站构造初始化
     * @param array   $config    货源站信息数组
     * @param string  $AppSecret AppSecret
     * @param integer $is_ssl    是否ssl
     */
    public function __construct($config = [], $AppSecret = '', $is_ssl = 0)
    {
        $this->AppId     = $config['username'];
        $this->AppSecret = $AppSecret;
        $url             = $config['url'];
        if (empty($this->AppId)) {
            throw new \Exception("AppId不能为空！", 1);
        }

        if (empty($this->AppSecret)) {
            throw new \Exception("AppSecret不能为空！", 1);
        }

        if (!preg_match('/^(https|http):\/\/[\w\.\-]+\.[\w\:\/]+\/$/', $url, $match)) {
            throw new \Exception("url格式必须是【http://直客域名/】或【https://直客域名/】这种", 1);
        }
        $this->url = $match[0];
        return true;
    }

    /**
     * 获取AppToken
     * @param  string $queryString 请求查询参数
     * @return string
     */
    private function getAppToken($queryString = '')
    {
        if (substr($queryString, 0, 1) != '/') {
            $queryString = '/' . $queryString;
        }
        $s = $this->AppId . $this->AppSecret;
        $s .= $queryString . $this->time;
        return sha1($s);
    }
    /**
     * 获取AppToken
     * @param  string $queryString 请求查询参数
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
     * 对接下单
     * @param array 订单信息数组
     * @param array 对接社区数组
     * @return string 下单结果
     */

    public function doOrder($row, $config = array())
    {
        global $DB, $date;

        $tool = $DB->get_row("SELECT * FROM `pre_tools` where tid=:tid limit 1", [":tid" => $row["tid"]]);
        if (!$tool) {
            return '该商品信息不存在，无法获取对接信息！';
        }

        $value = $tool['value'] > 0 ? $tool['value'] : 1;
        $num   = $row['value'] > 0 ? $row['value'] * $value : $value;

        $queryString = 'api/client/goods/v2/order';
        $params      = array(

        );
        $post = array(
            'goodsSN'       => $tool['goods_id'],
            'customOrderSN' => 'Api-' . $row['payorder'] . rand(111, 999),
            'number'        => intval($num),
            'orderNote'     => '',
        );

        $post['params'] = [];
        if ($tool['goods_param']) {
            $goods_param = explode('|', $tool['goods_param']);
            $data        = [
                $row['input'],
            ];
            $input[0] = $tool['input'];
            $inputs   = explode('|', $tool['inputs']);
            for ($i = 0; $i < count($inputs); $i++) {
                $input[$i + 1] = $inputs[$i];
            }
            for ($i = 2; $i < 6; $i++) {
                if (isset($row['input' . $i]) && $row['input' . $i]) {
                    $data[$i - 1] = $row['input' . $i];
                }
            }
            foreach ($goods_param as $key => $value) {
                if (isset($input[$key])) {
                    $post['params'][] = [
                        'name'  => $input[$key],
                        'alias' => $value,
                        'value' => $data[$key],
                    ];
                }
            }
        }
        if (count($params) > 0) {
            $queryString .= '?' . http_build_query($params);
        }
        $url        = $this->url . $queryString;
        $this->time = time();
        $AppToken   = $this->getAppToken($queryString);
        $header     = [
            'AppId:' . $this->AppId,
            'AppToken:' . $AppToken,
            'AppTimestamp:' . $this->time,
        ];

        $text = $this->cmy_curl($url, json_encode($post), $header);
        $arr  = json_decode($text, true);
        if (is_array($arr)) {
            if ($arr['code'] == '100') {
                $sql    = "UPDATE `pre_orders` SET `endtime`=:endtime,`djorder`=:djorder,`bz`=:bz,`djzt`=1,`status`=:status where id=:id";
                $status = 1;
                if (isset($config['orderstatus'])) {
                    $status = $config['orderstatus'];
                }
                $message  = '下单成功，订单号' . $arr['result']['orderSN'];
                $sql_data = array(
                    ':endtime' => $date,
                    ':djorder' => $arr['result']['orderSN'],
                    ':bz'      => $message,
                    ':status'  => $status,
                    ':id'      => $row['id'],
                );
                $DB->exec($sql, $sql_data);
                if (empty($tool['goods_param'])) {
                    $this->query($row, $config);
                }
            } else {
                $message = '下单失败，' . $arr['msg'];
                $DB->exec("UPDATE `pre_orders` set `djzt`='2', `status`='0', `endtime`= ? where id= ?", [$date, $row['id']]);
            }
            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $message, 1, $row['id']);
            return $message;
        } else {
            $DB->query("UPDATE `pre_orders` set `djzt`='2', `status`='0', `endtime`= ? where id= ?", [$date, $row['id']]);
        }

        $message = '网站返回数据解析失败：' . $text;
        log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $message, 1, $row['id']);
        return $message;

    }

    /**
     * 查询订单详情
     * @return array
     */
    public function query($row, $config)
    {
        global $DB;
        if (empty($row['djorder'])) {
            return ["code" => -1, "msg" => '对接订单编号不能为空！'];
        }

        $orderid = $row['id'];

        $result         = [];
        $result['code'] = -1;
        $queryString    = 'api/client/goods/v2/order';
        $params         = array(
            'orderSN' => $row['djorder'],
        );

        if (count($params) > 0) {
            $queryString .= '?' . http_build_query($params);
        }
        $url        = $this->url . $queryString;
        $this->time = time();
        $AppToken   = $this->getAppToken($queryString);
        $header     = [
            'AppId:' . $this->AppId,
            'AppToken:' . $AppToken,
            'AppTimestamp:' . $this->time,
        ];
        $text = $this->cmy_curl($url, 0, $header);

        $arr = json_decode($text, true);
        if (is_array($arr)) {
            if ($arr['code'] = 100) {
                $order_state_arr = [
                    '1'  => '已付款',
                    '2'  => '处理中',
                    '3'  => '等待中', //待确认
                    '4'  => '交易成功',
                    '5'  => '退单中',
                    '6'  => '已退单',
                    '7'  => '已退款',
                    '8'  => '排队中', //待处理
                    '-1' => '待提交', //待付款
                ];

                $data        = $arr['result'];
                $orderState  = $data["orderState"];
                $order_state = $order_state_arr[$orderState];
                if ($order_state == "") {
                    $order_state = "状态未知";
                }
                $result['code'] = 0;
                $result['msg']  = '查询接口订单成功：订单状态【' . $order_state . '】；状态码【' . $orderState . '】';
                $now_num        = isset($data["currentNum"]) ? $data["currentNum"] : ($data["orderState"] == 4 ? $data["num"] : 0);
                $result['data'] = array(
                    "num"         => $data["orderNum"],
                    "start_num"   => isset($data['startNum']) ? $data['startNum'] : 0,
                    "now_num"     => $now_num,
                    "order_state" => $order_state,
                    "orderid"     => $row['djorder'],
                    'add_time'    => $data['createdAt'],
                );
                $tool = $DB->get_row("SELECT * FROM cmy_tools WHERE tid=:tid LIMIT 1", [':tid' => $row["tid"]]);
                //同步订单
                if ($row['status'] == 2 || empty(trim($tool['goods_param']))) {
                    if (isset($data['kamiData']) && count($data['kamiData']) > 0) {
                        //卡密商品
                        $cardObj = new \core\Card();
                        $ret     = $cardObj->getCardData($row, $config, $data["kamiData"]);
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
                            $kmdata   = '已发货，请联系平台客服查询';
                            $sql_data = array(
                                ':order_state' => 1,
                                ':result'      => $kmdata,
                                ':djzt'        => 4,
                                ':id'          => $row['id'],
                            );
                        }
                        $DB->query($sql, $sql_data);
                    } else {
                        $sql_data = null;
                        if (in_array($orderState, [5, 6, 7, -1])) {
                            $sql_data = array(
                                ':status' => 3,
                                ':result' => '订单软件状态异常【' . $order_state . '】，请联系网站客服处理',
                                ':id'     => $row['id'],
                            );
                        } elseif ($orderState == 4) {
                            $sql_data = array(
                                ':status' => 1,
                                ':result' => '',
                                ':id'     => $row['id'],
                            );
                        }
                        if (is_array($sql_data)) {
                            $sql = "UPDATE `pre_orders` set `status`=:status,`result`=:result where `id`=:id";
                            $d   = $DB->query($sql, $sql_data);
                        }
                    }
                }
            } else {
                $result['msg'] = '查询接口订单失败：' . $arr['msg'];
            }
        } else {
            $result['msg'] = '查询订单详情失败，' . $text;
        }
        return $result;
    }

    /**
     * 获取商品列表
     * @return array  获取结果数组
     */
    public function getGoodsList()
    {
        global $webConfig;
        $queryString = 'api/client/goods/v2/goods/list';
        $params      = array(

        );
        if (count($params) > 0) {
            $queryString .= '?' . http_build_query($params);
        }
        $url        = $this->url . $queryString;
        $this->time = time();
        $AppToken   = $this->getAppToken($queryString);
        $header     = [
            'AppId:' . $this->AppId,
            'AppToken:' . $AppToken,
            'AppTimestamp:' . $this->time,
        ];
        $text = $this->cmy_curl($url, 0, $header);
        if ($webConfig['debug']) {
            addWebLog('获取商品', "直客url=> " . $url . "\n result => " . $text, "Shequ", 1);
        }
        $arr = json_decode($text, true);
        if (is_array($arr)) {
            if (array_key_exists('code', $arr) && $arr['code'] == 100) {
                $data = [];
                $list = $arr['result']['data'];
                foreach ($list as $key => $item) {
                    $item['id']   = $item['goodsSN'];
                    $item['name'] = $item['goodsName'];
                    $data[$key]   = $item;
                }
                return [
                    "code" => 0,
                    "msg"  => 'succ',
                    "data" => $data,
                ];
            } else {
                return [
                    "code" => -1,
                    "msg"  => '获取商品失败，' . $arr['msg'],
                    "arr"  => $arr,
                ];
            }
        } else {
            $text = htmlspecialchars(mb_substr($text, 0, 500));
            return [
                "code"    => -1,
                "msg"     => '打开对接网站' . $this->url . '失败，' . $text,
                'api_url' => $url,
            ];
        }
    }

    /**
     * 获取商品详情
     * @return array
     */
    public function getGoodsParams($goodsid = 0)
    {
        if ($goodsid == 0) {
            return ["code" => -1, "msg" => '商品ID不能为空！'];
        }
        $queryString = 'api/client/goods/v2/goods';
        $params      = array(
            "goodsSN" => $goodsid,
        );
        if (count($params) > 0) {
            $queryString .= '?' . http_build_query($params);
        }
        $url        = $this->url . $queryString;
        $this->time = time();
        $AppToken   = $this->getAppToken($queryString);
        $header     = [
            'AppId:' . $this->AppId,
            'AppToken:' . $AppToken,
            'AppTimestamp:' . $this->time,
        ];
        $text = $this->cmy_curl($url, 0, $header);
        $json = json_decode($text, true);
        if (is_array($json)) {
            if ($json['code'] == 100) {
                $info = $json['result'];
                if ($info['isClose'] == 1 || $info['goodsStock'] == 0) {
                    $active = 0;
                } else {
                    $active = 1;
                }
                $data = array(
                    'id'         => $info['goodsSN'],
                    'name'       => $info['goodsName'],
                    'price'      => $info['goodsPrice'],
                    'min'        => $info['minOrderNum'],
                    'max'        => intval($info['maxOrderNum']),
                    'active'     => $active,
                    'isClose'    => $info['isClose'],
                    'goodsType'  => $info['goodsType'],
                    'goodsStock' => $info['goodsStock'],
                    'desc'       => $info['goodsDetail'],
                    'input'      => isset($info['paramsTemplate'][0]) ? $info['paramsTemplate'][0]['name'] : '下单账号',
                    'shopimg'    => $info['goodsThumb'],
                    'params'     => '',
                );
                $params = '';
                $inputs = '';
                if (is_array($info['paramsTemplate'])) {
                    foreach ($info['paramsTemplate'] as $key => $option) {
                        if ($key > 0) {
                            $inputs .= $option['name'] . '|';
                        }
                        $params .= $option['alias'] . '|';
                    }
                }
                $data['inputs'] = trim($inputs, '|');
                $data['params'] = trim($params, '|');
                return ["code" => 0, "data" => $data];
            } else {
                return ["code" => -1, "msg" => $json['msg'], "data" => $json, "result" => $json];
            }
        } else {
            return [
                "code"    => -1,
                "msg"     => '打开对接网站' . $this->url . '失败，' . $text,
                'api_url' => $url,
            ];
        }
    }

    private function cmy_curl($url, $post = 0, $header = 0, $timeout = 30)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $httpheader[] = "Accept: */*";
        $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
        $httpheader[] = "Content-Type:application/x-www-form-urlencoded";
        $httpheader[] = "X-Requested-With:XMLHttpRequest";
        if (is_array($header) && count($header)) {
            $httpheader = array_merge($httpheader, $header);
        }

        if ($post) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36');
        //允许重定向
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //重定向最多3次
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        //gzip解压
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            if ($httpCode == 404 || preg_match('/^3\d{2}$/', $httpCode)) {
                $ret = '[' . $httpCode . ']该网站接口存在跳转丢失或网站已迁移，建议使用直客二级域名试试';
            } else {
                $ret = '[' . $httpCode . ']' . curl_error($ch);
            }
        }
        curl_close($ch);
        return $ret;
    }

}
