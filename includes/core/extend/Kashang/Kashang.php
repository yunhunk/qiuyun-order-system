<?php

use \core\Card;

class Kashang extends Card
{
    /**
     * 配置信息
     *
     * @var array
     */
    private $config = [];

    private $isSsl = false;

    /**
     * 构造方法初始化
     * @param array   $config        配置信息
     * @param integer $_ssl          是否https
     */
    public function __construct($config = [], $_ssl = 0)
    {
        $this->config = $config;

        $this->isSsl = $_ssl;
    }

    public function doOrder($row, $config = [], $tool = [], $num = 1)
    {
        global $DB, $date;

        if ($row['status'] == -1) {
            $DB->query("UPDATE `pre_orders` set `status`='0', djzt='2' where id=:id", [':id' => $row['id']]);
        } elseif ($row['status'] > 0) {
            return '该订单已对接处理！';
        }

        $goods_id = $tool['goods_id'];
        if ($row['value'] < 1) {
            $row['value'] = 1;
        }

        $data = array(
            'customer_id'      => $config['username'],
            'product_id'       => $goods_id,
            'timestamp'        => time(),
            'recharge_account' => $row['input'],
            'quantity'         => $row['value'],
        );

        if (!$tool) {
            $tool = $DB->get_row("SELECT * FROM cmy_tools where tid=:tid limit 1", [':tid' => $row['tid']]);
        }

        $params = explode("|", $tool['goods_param']);
        if (count($params) >= 1) {
            $i = 1;
            foreach ($params as $key) {
                if ($i == 1 && $key != "") {
                    $data['recharge_template_input_items[' . $key . ']'] = $row['input'];
                    $i++;
                } elseif ($key != "") {
                    $data['recharge_template_input_items[' . $key . ']'] = $row['input' . $i];
                    $i++;
                }
            };
        }

        $url    = $config['url'] . "api/buy";
        $post   = http_build_query($data) . "&sign=" . kashang_getSign($data, $config['password']);
        $result = shequ_get_curl($url, $post, 0, 0, 0, 0, 0, 1);

        $json = json_decode($result, true);
        if (is_array($json)) {
            if ($json['code'] == 'ok') {
                $order_id = $json['data']['order_id'];
                $message  = '下单成功，订单号：' . $order_id;
                $status   = 2;
                if ($config['orderstatus']) {
                    $status = $config['orderstatus'];
                }

                $djresult = '';

                if ($tool['result']) {
                    $djresult = $tool['result'];
                }

                $row['djorder'] = $order_id;
                $infoControler  = new \core\InfoControler();
                $infoControler->orderjk_kashang($row, $config);
                $DB->query(
                    "UPDATE `pre_orders` set result= ?,djorder= ?,endtime= ?,`status`= ?,djzt='1' where id= ?",
                    [$djresult, $order_id, $date, $status, $row['id']]
                );
                log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $post, $message, 1, $row['id']);
            } else {
                $DB->query("UPDATE `pre_orders` set status='0',djzt='2' where id=:id", [':id' => $row['id']]);
                $message = '下单失败，' . addslashes($json['message']);
                log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $post, $message, 0, $row['id']);
            }
            return $message;
        } else {
            $DB->query("UPDATE `pre_orders` set status='0', djzt='2' where id=:id", [':id' => $row['id']]);
            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $post, $result, 0, $row['id']);
            return '下单失败，' . $result;
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
            return array('code' => -1, 'msg' => '缺少对接订单号！');
        }

        $djorder = $row['djorder'];

        $arr = array(
            "userid"  => $config["username"],
            "orderno" => $djorder,
        );

        $key = $config['password'];

        $post = http_build_query($arr) . '&sign=' . yile_getSign($arr, $key);

        $url = $config['url'] . "dockapi/index/queryorder.html";

        $result = shequ_get_curl($url, $post, 0, 0, 0, 0, 0, 1);
        $data   = $this->json_decode($result);
        if (is_array($data)) {
            if ($data['code'] != 1) {
                $ret['code'] = -1;
                $ret['msg']  = $data['msg'];
            } else {

                $arr      = $data['data'];
                $stateArr = array(
                    '0' => '等待中', //原未使用，应该是未付款
                    '1' => '已**', //原已使用，应该是已付款，待处理
                    '2' => '订单异常',
                    '3' => '进行中',
                    '4' => '已取消，联系客服',
                    '5' => '已完成',
                );

                $order_state = $stateArr[$arr['status']];
                if (empty($order_state)) {
                    $order_state = "未知状态 [" . $arr['status'] . "]";
                }

                if ($row['status'] == 2) {
                    $sql_data = null;
                    if (in_array($arr['status'], ['1', '5'])) {
                        $sql_data = array(
                            ':status' => 1,
                            ':bz'     => '对接站订单已完成',
                            ':id'     => $row['id'],
                        );
                    } elseif ($arr['status'] == '4') {
                        $sql_data = array(
                            ':status' => 3,
                            ':bz'     => '对接站订单已取消，请核查',
                            ':id'     => $row['id'],
                        );
                    } elseif ($arr['status'] == '2') {
                        $sql_data = array(
                            ':status' => 3,
                            ':bz'     => '对接站订单未付款，请前往付款',
                            ':id'     => $row['id'],

                        );
                    }
                    if (null !== $sql_data) {
                        $sql = "UPDATE `pre_orders` set `status`=:status,`bz`=:bz where `id`=:id";
                        $DB->query($sql, $sql_data);
                    }
                }

                $ret['code']                = 0;
                $ret['msg']                 = "查询成功，订单状态【" . $order_state . "】，状态码【" . $arr['status'] . "】，备注：由于Api接口限制，卡密商品对接时发货失败的将无法自动同步，请手动处理";
                $ret['data']['orderid']     = $djorder;
                $ret['data']['start_num']   = 0;
                $ret['data']['now_num']     = null;
                $ret['data']['end_num']     = null;
                $ret['data']['num']         = intval($row['value']);
                $ret['data']['add_time']    = date('Y-m-d H:i:s', $arr['create_time']);
                $ret['data']['order_state'] = $order_state;
                $ret['data']['siteurl']     = $config['url'];
                $ret['data']['shopUrl']     = '';
            }
            return $ret;
        } else {
            $ret = array('code' => -1, "msg" => "查询" . $row['id'] . "的亿樂订单详情失败，请稍后重试！", "data" => $result);
            return $ret;
        }
    }

    /**
     * 获取分类列表
     *
     * @return array
     */
    public function getCateList($config = [])
    {
        return ['code' => -1, "msg" => "暂不支持直接获取分类"];
    }

    /**
     * 获取商品列表
     *
     * @return array
     */
    public function getGoodsList($config = [])
    {
        global $CACHE, $webConfig;

        $row_cache = $CACHE->read($config["url"] . '_goods');
        if (is_array($row_cache) && $row_cache['expire'] >= time() && $data = @json_decode($row_cache['v'])) {
            $result['code']     = 0;
            $result['msg']      = 'succ';
            $result['data']     = $data;
            $result['is_cache'] = 1;
            return $result;
        }

        $url = $config["url"] . "dockapi/index/getallgoods.html";
        $arr = array(
            "userid" => $config["username"],
        );

        $key = $config['password'];

        $post = http_build_query($arr) . '&sign=' . yile_getSign($arr, $key);

        $result = shequ_get_curl($url, $post, 0, 0, 0, 0, 0, $config['proxy']);

        if ($webConfig['debug']) {
            @addWebLog('获取商品', "url：" . $url . "\ndata：" . $post . "\nresult：" . $result, 'Shequ', 1);
        }

        $json = json_decode($result, true);
        if (is_array($json)) {
            if ($json['status'] == 0) {
                $data = [];
                foreach ($json['data'] as $row) {
                    $classinfo = array(
                        'groupid'   => $row['id'],
                        'groupname' => $row['groupname'],
                    );
                    $data[$row['id']]['class'] = $classinfo;
                    $goods                     = [];
                    foreach ($row['goods'] as $key => $tool) {
                        $goods[] = array(
                            'id'        => $tool['id'],
                            'goodsname' => $tool['goodsname'],
                        );
                    }
                    $data[$row['id']]['goods'] = $goods;
                }
                $ret = array('code' => 0, "msg" => $json['msg'], "data" => $data, "type" => getShequType($config['type']));
                $CACHE->save($config["url"] . '_goods', json_encode($data), time() + 180, 'string');
            } else {
                $ret = array('code' => -1, "msg" => $json['msg'], "data" => null, "type" => getShequType($config['type']));
            }
        } else {
            if (empty($result)) {
                $result = '返回空，可能对方拦截或迁移，请稍后再试！';
            }
            $result = mb_substr($result, 0, 500);
            $ret    = array('code' => -1, "msg" => '网站打开失败，' . $result, "result" => $result, "type" => getShequType($config['type']));
        }

        return $ret;
    }

    /**
     * 获取商品参数
     *
     * @return array
     */
    public function getGoodsParams($config, $goods_id)
    {
        $url  = $config['url'] . "api/product";
        $data = array(
            'customer_id' => $config['username'],
            'product_id'  => $goods_id,
            'timestamp'   => time(),
        );

        $key    = $config['password'];
        $post   = http_build_query($data) . "&sign=" . kashang_getSign($data, $key);
        $result = shequ_get_curl($url, $post, 0, 0, 0, 0, 0, 1);
        //$result=get_curl($url,$post);
        $json = json_decode($result, true);
        if (is_array($json)) {
            if ($json['code'] == 'ok') {
                $ParamsData = $this->getGoodsRechargeParams($config, $goods_id);
                if ($ParamsData['code'] == 0) {
                    $json['data']['input'] = $ParamsData['data']['recharge_account_label'];
                    $recharge_params       = $ParamsData['data']['recharge_params'];
                    if (count($recharge_params) > 0) {
                        foreach ($recharge_params as $row) {
                            if (empty($row['name'])) {
                                $row['name'] = 'QQ号码';
                            }
                            $json['data']['inputs'] .= '|' . $row['name'];
                        }
                        $json['data']['inputs'] = trim($json['data']['inputs'], "|");
                    }
                    $json['data']['recharge_params']      = $recharge_params;
                    $json['data']['recharge-params-info'] = $ParamsData['data'];
                } else {
                    $json['data']['recharge_params'] = $ParamsData;
                }
                $ret = array('code' => 0, "msg" => $json['message'], "data" => $json['data'], "url" => $config['url']);
            } else {
                $ret = array('code' => -1, "msg" => addslashes($json['message']), "shequ" => $config);
            }
        } else {
            $ret = array('code' => -1, "msg" => "获取卡商充值参数失败，请稍后重试！", "data" => $result);
        }
        return $ret;
    }

    public function getGoodsRechargeParams($config, $goods_id)
    {
        $url  = $config['url'] . "api/product/recharge-params";
        $time = time();
        $data = array(
            'customer_id' => $config['username'],
            'product_id'  => $goods_id,
            'timestamp'   => $time,
        );
        $post   = http_build_query($data) . "&sign=" . kashang_getSign($data, $config['password']);
        $result = shequ_get_curl($url, $post, 0, 0, 0, 0, 0, 1);
        //$result=get_curl($url,$post);
        $json = json_decode($result, true);
        if (is_array($json)) {
            if ($json['code'] == 'ok') {
                $ret = array('code' => 0, "msg" => $json['message'], "data" => $json['data'], "url" => $config['url']);
            } else {
                $ret = array('code' => -1, "msg" => addslashes($json['message']), "shequ" => $config);
            }
        } else {
            $ret = array('code' => -1, "msg" => "获取卡商充值参数失败，请稍后重试！", "data" => $result);
        }
        return $ret;
    }
}
