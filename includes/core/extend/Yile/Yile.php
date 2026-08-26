<?php
use \core\Card;

class Yile extends Card
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

        $orderid       = $row['id'];
        $num           = intval($num) > 0 ? intval($num) : 1;
        $goods_id      = $tool['goods_id'];
        $data          = $this->parseInputData($row);
        $config["url"] = yile_url_parse($config);
        $url           = $config["url"] . "api/order";
        $arr           = array(
            "api_token" => $config["username"],
            "timestamp" => time(),
            "gid"       => $goods_id,
            "num"       => $num,
        );

        $i = 1;
        foreach ($data as $value) {
            if ("" != $value) {
                $arr['value' . $i] = $value;
            }
            $i++;
        }

        $password = $config['password'];

        $post = http_build_query($arr) . '&sign=' . yile_getSign($arr, $password);

        //$result=get_curl($url,$post);
        $result = shequ_get_curl($url, $post, 0, 0, 0, 0, 0, $config['proxy']);
        $json   = json_decode($result, true);

        if (is_array($json)) {
            if ($json['status'] == 0) {
                $status = 1;
                if ($config['orderstatus']) {
                    $status = $config['orderstatus'];
                }

                if (stripos($result, 'card') !== false) {
                    $ret    = (new Card())->getCardData($orderid, $row['tid'], $json['data']);
                    $kmdata = '';
                    if (!empty($ret['kmdata'])) {
                        $kmdata = "卡密信息如下，请参考商品介绍使用：<br>\r\n" . $ret["kmdata"];
                        $DB->query("UPDATE `pre_orders` set result= ?,`djzt`='3',`bz`='卡密内容提取成功',endtime= ?,status= ? where id= ?", [$kmdata, $date, $status, $row['id']]);
                    } else {
                        $DB->query("UPDATE `pre_orders` set result= ?,`djzt`='4',`bz`='卡密内容提取失败',endtime= ?,status= ? where id= ?", [$kmdata, $date, $status, $row['id']]);
                        log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $post, '卡密识别失败，' . $ret['msg'], 1, $orderid);
                    }
                    $message = "下单成功，该商品为卡密类商品，无订单号！卡密内容：" . $ret["kmdata"];
                } else {
                    $message = "下单成功，订单号：" . $json['id'];
                    $DB->query("UPDATE `pre_orders` set djorder= ?,endtime= ?,`bz`= ?,status= ?,djzt='1' where id= ?", array($json['id'], $date, $message, $status, $row['id']));
                }

                log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $post, $message, 1, $orderid);
            } else {
                $DB->query("UPDATE `pre_orders` set status='0',djzt='2' where id=:id", [':id' => $row['id']]);
                $message = "下单失败，" . $json["message"];
                log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $post, $message, 0, $orderid);
            }
            return $message;
        }

        $DB->query("UPDATE `pre_orders` set status='0',djzt='2' where id=:id", [':id' => $row['id']]);
        log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $post, '下单失败，' . $result, 0, $orderid);
        return $result;
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

        $url  = $config['url'] . 'api.php?act=search&id=' . $row['djorder'];
        $text = get_curl($url);
        $json = json_decode($text, true);
        if (is_array($json) && isset($json['code']) && $json['code'] == 0) {
            $ret['code'] = 0;
            if (is_array($json['kmdata']) && count($json['kmdata']) > 0) {
                $addData = $this->getCardData($row, $config, $json['kmdata']);
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
                    if ($json['status'] == 1) {
                        $sql_data = array(
                            ':status' => 1,
                            ':id'     => $row['id'],
                        );
                    } elseif (in_array($json['status'], ['3', '4'])) {
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

            if (is_numeric($json['status'])) {
                $status_arr = array(
                    '0'  => '待处理',
                    '1'  => '已完成',
                    '2'  => '进行中',
                    '3'  => '异常',
                    '4'  => '已退单',
                    '10' => '待退单',
                );
                $order_state = $status_arr[$json['status']];
                if (empty($order_state)) {
                    $order_state = '未知状态，可能该平台已更新！' . $text;
                }
            } else {
                $order_state = $json['order_state'];
            }

            if (isset($json['data']) && count($json['data']) > 0 && array_key_exists('add_time', $json['data'])) {
                $ret['data'] = $json['data'];
            } else {

                $ret['data']['order_state'] = $order_state;
                $ret['data']['orderid']     = $row['djorder'];
                $ret['data']['num']         = $row['value'] * $tool['value'];
                $ret['data']['add_time']    = $row['addtime'];
                $ret['data']['start_num']   = null;
                $ret['data']['now_num']     = null;
                $ret['data']['shopUrl']     = '';
                if ($tool) {
                    $ret['data']['shopUrl'] = $config['url'] . '?cid=' . $tool['cid'] . '&tid=' . $tool['tid'];
                }
                $ret['data']['result'] = $text;
            }

            $ret['msg'] = "查询成功，订单状态【" . $order_state . "】；状态码【" . $json['status'] . "】";

            if ($row['status'] == 2) {
                //同步订单状态
                if (in_array($json['status'], ['3', '4']) || preg_match('/异常|退单|退款/', $order_state)) {
                    $sql_data = array(
                        ':status' => 3,
                        ':id'     => $row['id'],
                    );
                } elseif ($json['status'] == 1 || preg_match('/完成|成功/', $order_state)) {
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
    }

    /**
     * 获取分类列表
     *
     * @return array
     */
    public function getCateList($config = [])
    {
        return array('code' => -1, "msg" => "不支持获取分类", 'data' => []);
    }

    /**
     * 获取商品列表
     *
     * @return array
     */
    public function getGoodsList($config = [], $cid = null)
    {
        $config['url'] = shequ_url_parse($config);
        $url           = $config['url'] . "api.php?act=goodslist";
        $data          = [
            'user' => $config['username'],
            'pass' => $config['password'],
        ];
        if ($cid != null) {
            $url         = $config['url'] . "api.php?act=goodslistbycid";
            $data['cid'] = intval($cid);
        }

        $result = get_curl($url, http_build_query($data));
        $json   = json_decode($result, true);
        if (is_array($json)) {
            if ($json['code'] == 0) {
                foreach ($json['data'] as $key => &$item) {
                    $item['value'] = 1;
                }
                $ret = ['code' => 0, "msg" => "succ", "data" => $json['data']];
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
        $config["url"] = yile_url_parse($config);
        $url           = $config["url"] . "api/goods/info";
        $time          = time();
        $data          = array(
            'api_token' => $config['username'],
            'timestamp' => $time,
            'gid'       => $goods_id,
        );

        $post   = http_build_query($data) . "&sign=" . yile_getSign($data, $config['password']);
        $result = shequ_get_curl($url, $post, 0, 0, 0, 0, 0, $config['proxy']);
        $json   = json_decode($result, true);
        if (is_array($json)) {
            if ($json['status'] == 0) {
                $ret = array('code' => 0, "msg" => 'succ', "data" => $json['data'], "url" => $config['url']);
            } else {
                $ret = array('code' => -1, "msg" => $json['message'], "data" => null, "url" => $config['url']);
            }
        } else {
            $ret = array('code' => -1, "msg" => "获取亿樂参数失败，请稍后重试！", "data" => $result);
        }
        return $ret;
    }
}
