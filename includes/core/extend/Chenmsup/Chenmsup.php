<?php
use \core\Card;

class Chenmsup extends Card
{
    /**
     * 配置信息
     *
     * @var array
     */
    private $config = [];

    private $url = '';

    private $Appid = '';

    private $Appkey = '';

    private $isSsl = 0;
    /**
     * 构造方法初始化
     * @param array   $config        配置信息
     * @param integer $_ssl          是否https
     */
    public function __construct($config = [], $_ssl = 0)
    {
        $this->config = $config;
        if (!isset($this->config['username']) || !$this->config['username']) {
            throw new Exception("错误, 对接APPID不能为空!");
        }

        if (!isset($this->config['password']) || !$this->config['password']) {
            throw new Exception("错误, 对接APPKEY不能为空!");
        }
        $this->url    = shequ_url_parse($this->config);
        $this->Appid  = trim($this->config['username']);
        $this->Appkey = trim($this->config['password']);
        $this->isSsl  = $_ssl;
    }

    public function doOrder($row, $config = [], $tool = [], $num = 1)
    {
        global $DB;

        if (!$tool || !is_array($tool)) {
            $tool = $DB->get_row("SELECT * FROM cmy_tools where tid=:tid limit 1", [':tid' => $row['tid']]); //获取商品信息
            if (!is_array($tool)) {
                return '下单失败！该订单对应的商品信息不存在！';
            }
        }

        $goods_id = $tool['goods_id'];

        if (!is_array($row) || !$row['input']) {
            return '下单失败！提交的订单数据错误或为空';
        }

        $form_inputs = [
            [
                'label' => $tool['input'],
                'value' => $row['input'],
            ],
        ];
        $inputs = explode('|', $tool['inputs']);
        $i      = 2;
        foreach ($inputs as $value) {
            if ($value != "") {
                $form_inputs[] = [
                    'label' => $value,
                    'value' => $row['input' . $i],
                ];
            }
            $i++;
        }

        if ($tool['goods_type'] == 1) {
            $body = trim(json_encode([
                "goods_id" => $goods_id,
                "num"      => $num,
                "order_no" => $row['payorder'],
                "goods_id" => $goods_id,
            ]));
            $url = $this->url . "api/client/v1/createOrderByCard";
        } else {
            $url  = $this->url . "api/client/v1/createOrder";
            $body = trim(json_encode([
                "goods_id" => $goods_id,
                "num"      => $num,
                "order_no" => $row['payorder'],
                "goods_id" => $goods_id,
                "inputs"   => $form_inputs,
            ]));
        }

        $Timestamp = time();
        // 签名
        $header = [
            "Sign: " . md5($this->Appid . $Timestamp . $body . $this->Appkey),
            'Timestamp: ' . $Timestamp,
        ];
        $result = $this->get_curl($url, $body, $header);

        if (!($json = json_decode($result, true))) {
            $json = json_decode('{' . getSubstr($result, '{', '}') . '}', true);
        }

        if (is_array($json)) {
            if ($json['code'] != 200) {
                log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $body, '下单失败，' . $json['message'], 0, $row['id']);
                return '下单失败！' . $json['message'];
            }

            $status = 1;
            if ($config['orderstatus']) {
                $status = $config['orderstatus'];
            }
            $result = "";
            if ($tool['goods_type'] == 1 && isset($json['data']['kmdata']) && is_array($json['data']['kmdata'])) {
                $addData = (new Card())->getCardData($row, $config, $json['kmdata']);
                if (!empty($addData['kmdata'])) {
                    $result = "以下是卡密内容，请参考商品介绍使用<br>\r\n" . $addData['kmdata'];
                    $djzt   = 3;
                } else {
                    $result = "订单已记录并处理，请联系网站客服获取卡密信息";
                    $djzt   = 1;
                }
                $sqlData = [
                    ':result'  => $result,
                    ':djorder' => $json['data']['order_id'],
                    ':status'  => $status,
                    ':id'      => $row['id'],
                ];
                $DB->query("UPDATE `pre_orders` set result=:result,djorder=:djorder,djzt='{$djzt}',status=:status where id=:id", $sqlData);
            } else {
                $result  = !empty($tool['result']) ? $tool['result'] : $result;
                $sqlData = [
                    ':result'  => $result,
                    ':djorder' => $json['data']['order_id'],
                    ':status'  => $status,
                    ':id'      => $row['id'],
                ];
                $DB->query("UPDATE `pre_orders` set result=:result,djorder=:djorder,djzt='1',status=:status where id=:id", $sqlData);
            }

            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $body, '下单成功！订单号：' . $json['data']['order_id'], 1, $row['id']);
            return '下单成功！订单号：' . $json['data']['order_id'];
        } else {
            $DB->query("UPDATE `pre_orders` set status='0',djzt='2' where id=:id", [':id' => $row['id']]);
            $result = str_replace(array("\r\n", "\r", "\n"), "", $result);
            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $body, $result, 0, $row['id']);
            return '下单失败！' . $result;
        }
    }

    /**
     * 查询订单
     *
     * @param array $row 订单信息
     * @param array $config 配置信息
     * @return array
     */
    public function query($row = [], $config = [])
    {
        global $DB;

        if (empty($row['djorder'])) {
            return array('code' => -1, "msg" => "缺少对接订单号！");
        }

        $tool = $DB->get_row("SELECT * from cmy_tools where tid=:tid limit 1", [':tid' => $row['tid']]);

        if (!$tool) {
            return array('code' => -1, "msg" => "该订单对应商品不存在！");
        }

        try {

            $url       = $this->url . "api/client/v1/getOrderInfo";
            $Timestamp = time();
            // 准备请求参数
            $body = trim(json_encode([
                "order_id" => $row['djorder'],
            ]));
            // 签名
            $header = [
                "Sign: " . md5($this->Appid . $Timestamp . $body . $this->Appkey),
                'Timestamp: ' . $Timestamp,
            ];
            $text = $this->get_curl($url, $body, $header);
            $json = json_decode($text, true);
            if (is_array($json) && isset($json['code']) && $json['code'] == 200) {
                $ret['code'] = 0;
                $info        = isset($json['data']) && isset($json['data'][0]) ? $json['data'][0] : null;
                if (!$info) {
                    return array('code' => -1, "msg" => "订单查询返回结果异常！");
                }

                if ($tool['goods_type'] == 1 && isset($info['kmdata']) && is_array($info['kmdata'])) {
                    $addData = $this->getCardData($row, $config, $info['kmdata']);
                    if (!empty($addData['kmdata'])) {
                        $result = "以下是卡密内容，请参考商品介绍使用<br>\r\n" . $addData['kmdata'];
                        $djzt   = 3;
                    } else {
                        $result = '';
                        $djzt   = 1;
                    }
                    $sqlData = [':result' => $result, ':djzt' => $djzt, ':djorder' => $json['orderid'], ':id' => $row['id']];
                    $DB->query("UPDATE `pre_orders` set result=:result,djorder=:djorder,djzt=:djzt,status='1' where id=:id", $sqlData);
                } else {
                    if ($row['status'] == 2) {
                        if ($info['status'] == 1) {
                            $sql_data = array(
                                ':status' => 1,
                                ':id'     => $row['id'],
                            );
                        } elseif (in_array($info['status'], [3, 4, 5, 6, 7])) {
                            $sql_data = array(
                                ':status' => 3,
                                ':id'     => $row['id'],
                            );
                        } else {
                            $sql_data = null;
                        }

                        if (null !== $sql_data) {
                            $sql = "UPDATE `pre_orders` set `status`=:status where id=:id";
                            $DB->query($sql, $sql_data);
                        }
                    }
                }

                $status_arr = array(
                    '0' => '待处理',
                    '1' => '已完成',
                    '2' => '进行中',
                    '3' => '异常',
                    '4' => '已退单',
                    '5' => '部分已退单',
                    '6' => '已取消',
                    '7' => '已关闭',
                );
                $order_state = isset($status_arr[$info['status']]) ? $status_arr[$info['status']] : '未知';
                if (empty($order_state)) {
                    $order_state = '未知状态[' . $info['status'] . ']';
                }

                $ret['data']['order_state'] = $order_state;
                $ret['data']['orderid']     = $row['djorder'];
                $ret['data']['num']         = $row['value'] * $tool['value'];
                $ret['data']['add_time']    = $row['addtime'];
                $ret['data']['start_num']   = 0;
                $ret['data']['now_num']     = $info['num_ok'];
                $ret['data']['shopUrl']     = '';
                $ret['data']['result']      = $json;

                $ret['msg'] = "查询成功，订单状态【" . $order_state . "】；状态码【" . $info['status'] . "】";

                if ($row['status'] == 2) {
                    //同步订单状态
                    if (in_array($info['status'], [3, 4, 5, 6, 7])) {
                        $sql_data = array(
                            ':status' => 3,
                            ':id'     => $row['id'],
                        );
                    } elseif ($info['status'] == 1) {
                        $sql_data = array(
                            ':status' => 1,
                            ':id'     => $row['id'],
                        );
                    } else {
                        $sql_data = null;
                    }

                    if (null !== $sql_data) {
                        $sql = "UPDATE `pre_orders` SET `status`=:status where id=:id";
                        $DB->query($sql, $sql_data);
                    }
                }
            } elseif (isset($json['message'])) {
                $ret = array('code' => -1, "msg" => "查询失败，" . $json['message']);
            } elseif (isset($json['msg'])) {
                $ret = array('code' => -1, "msg" => "查询失败，" . $json['msg']);
            } else {
                $text = str_replace(array("\r\n", "\r", "\n"), "", $text);
                $ret  = array('code' => -1, "msg" => "查询" . $row['id'] . "的订单详情失败，请稍后重试！<br>" . htmlspecialchars($text));
            }
            return $ret;
        } catch (\Throwable $th) {
            return array('code' => -1, "msg" => "出现执行错误，" . $th->getMessage());
        }
    }

    /**
     * 获取分类列表
     *
     * @return array
     */
    public function getCateList($config = [])
    {
        $url    = $this->url . "api/v1/createOrder";
        $result = get_curl($url);
        $json   = json_decode($result, true);
        if (is_array($json) && $json['code'] == 0) {
            $json['url'] = $config['url'];
            $ret         = $json;
        } elseif (isset($json['msg'])) {
            $ret = array('code' => -1, 'url' => $config['url'], "msg" => $json['msg']);
        } elseif (isset($json['message'])) {
            $ret = array('code' => -1, 'url' => $config['url'], "msg" => $json['message']);
        } else {
            $ret = array('code' => -1, "msg" => "网站打开解析失败，" . $result);
        }
        return $ret;
    }

    /**
     * 获取商品列表
     *
     * @return array
     */
    public function getGoodsList($config = [], $cid = null)
    {
        $url       = $this->url . "api/client/v1/getGoodsList";
        $Timestamp = time();
        // 准备请求参数
        if ($cid) {
            $body = trim(json_encode([
                "category_id" => $cid,
            ]));
        } else {
            $body = trim(json_encode([]));
        }
        // 签名
        $header = [
            "Sign: " . md5($this->Appid . $Timestamp . $body . $this->Appkey),
            'Timestamp: ' . $Timestamp,
        ];
        $result = $this->get_curl($url, $body, $header);
        $json   = json_decode($result, true);
        if (is_array($json)) {
            if ($json['code'] == 200) {
                $data = [];
                foreach ($json['data'] as $key => $value) {
                    $input  = '留言';
                    $inputs = '';
                    if (isset($value['form_inputs'])) {
                        $form_inputs = is_array($value['form_inputs']) ? $value['form_inputs'] : json_decode($value['form_inputs'], true);
                        $input       = count($form_inputs) ? $form_inputs[0]['label'] : '留言';
                        foreach ($form_inputs as $key2 => $item) {

                            $options = '';
                            if (isset($item['options']) && is_array($item['options'])) {
                                if ($item['type'] == 'select' || $item['type'] == 'red') {
                                    $options = '{';
                                    foreach ($item['options'] as $key => $value) {
                                        $options .= $value['value'] . ':' . $value['label'] . ',';
                                    }
                                    $options = rtrim($options, ',') . '}';
                                }
                            }
                            if ($key2 > 0) {
                                $inputs .= $item['label'] . $options . '|';
                            } else {
                                $input = $item['label'] . $options;
                            }
                        }
                    }

                    $data[] = [
                        'id'        => $value['gid'],
                        'name'      => '[' . $value['gid'] . ']' . $value['name'],
                        'alert'     => $value['alert'],
                        'desc'      => $value['content'],
                        'stock'     => $value['goods_stock'],
                        'min'       => $value['buy_min'],
                        'max'       => $value['buy_max'],
                        'shopimg'   => $value['image'],
                        'price'     => $value['price'],
                        'active'    => $value['status'],
                        'close'     => 0,
                        'input'     => $input,
                        'inputs'    => $inputs,
                        'param'     => '',
                        'goodstype' => $value['goods_type'] == 1 ? 1 : 0,
                    ];
                }
                $ret = ['code' => 0, "msg" => "succ", "data" => $data];
            } else {
                $ret = ['code' => -1, "msg" => $json['message'], "data" => []];
            }
        } else {
            $ret = ['code' => -1, "msg" => "网站打开失败，请检查该站点防火墙或网站访问状态", "data" => $result];
        }
        $ret['type'] = getShequType($config['type']);
        return $ret;
    }

    /**
     * 获取商品参数
     *
     * @return array
     */
    public function getGoodsParams($config, $goods_id)
    {

        if (!$goods_id) {
            return ['code' => -1, "msg" => '商品ID不能为空!', "data" => []];
        }
        $url       = $this->url . "api/client/v1/getGoodsInfo";
        $Timestamp = time();
        // 准备请求参数
        $body = trim(json_encode([
            "goods_id" => $goods_id,
        ]));
        // 签名
        $header = [
            "Sign: " . md5($this->Appid . $Timestamp . $body . $this->Appkey),
            'Timestamp: ' . $Timestamp,
        ];
        $result = $this->get_curl($url, $body, $header);
        $json   = json_decode($result, true);
        if (is_array($json)) {
            if ($json['code'] == 200) {
                $value  = $json['data'];
                $input  = '留言';
                $inputs = '';
                if (isset($value['form_inputs'])) {
                    $form_inputs = is_array($value['form_inputs']) ? $value['form_inputs'] : json_decode($value['form_inputs'], true);
                    $input       = count($form_inputs) ? $form_inputs[0]['label'] : '留言';
                    foreach ($form_inputs as $key2 => $item) {
                        $options = '';
                        if (isset($item['options']) && is_array($item['options'])) {
                            if ($item['type'] == 'select' || $item['type'] == 'red') {
                                $options = '{';
                                foreach ($item['options'] as $key => $value) {
                                    $options .= $value['value'] . ':' . $value['label'] . ',';
                                }
                                $options = rtrim($options, ',') . '}';
                            }
                        }
                        if ($key2 > 0) {
                            $inputs .= $item['label'] . $options . '|';
                        } else {
                            $input = $item['label'] . $options;
                        }
                    }
                }

                $data = [
                    'id'        => $value['gid'],
                    'name'      => $value['name'],
                    'alert'     => $value['alert'],
                    'desc'      => $value['content'],
                    'stock'     => $value['goods_stock'],
                    'min'       => $value['buy_min'],
                    'max'       => $value['buy_max'],
                    'shopimg'   => $value['image'],
                    'price'     => $value['price'],
                    'active'    => $value['status'],
                    'close'     => 0,
                    'input'     => $input,
                    'inputs'    => trim($inputs, '|'),
                    'param'     => '',
                    'goodstype' => $value['goods_type'] == 1 ? 1 : 0,
                ];
                $ret = ['code' => 0, "msg" => "succ", "data" => $data];
            } else {
                $ret = ['code' => -1, "msg" => $json['message'], "data" => []];
            }
        } else {
            $ret = ['code' => -1, "msg" => "网站打开失败，请检查该站点防火墙或网站访问状态", "data" => $result];
        }
        $ret['type'] = getShequType($config['type']);
        return $ret;
    }

    private function get_curl($url, $post, $header)
    {

        $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.41 Safari/537.36 Edg/101.0.1210.32';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // 获取头部信息
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        // 返回原生的（Raw）输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);

        //判断post 设置超时时间
        $time = 18;
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            $time = 59;
        }

        $header[] = 'Appid: ' . $this->Appid;
        //检查并配置代理
        $header[] = "Accept: */*";
        $header[] = "Accept-Encoding: gzip,deflate,sdch";
        $header[] = "X-Requested-With:XMLHttpRequest";
        if ($post) {
            $header[] = 'Content-Type: application/json; charset=UTF-8';
        } else {
            $header[] = "Content-Type:application/x-www-form-urlencoded; charset=UTF-8";
        }

        //设置请求头
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        // //强制协议为1.0
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        // //强制使用IPV4协议解析域名  新加新加
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        curl_setopt($ch, CURLOPT_TIMEOUT, $time);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        // 执行并获取返回结果
        $content  = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($content == '') {
            if ($httpCode !== 200 && !preg_match('/^3[\d]{2}$/', $httpCode) && $httpCode !== 408) {
                $ret = '[' . $httpCode . ']' . curl_error($ch);
            } else {
                $ret = '[' . $httpCode . ']该网站内部出错，未返回任何内容';
            }
        }
        // 关闭CURL
        curl_close($ch);
        // 解析HTTP数据流
        return $content;
    }
}
