<?php
use \core\Card;

class Caihon extends Card
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

        $password = $config['password'];

        if (empty($password)) {
            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；', "对接失败，密码解析失败，请在社区列表重新编辑一下密码相关资料再试", 0, $row['id']);
            return "密码解析失败，请在社区列表重新编辑一下密码相关资料再试";
        }
        $param = 'tid=' . $goods_id . '&num=' . $num;
        $param .= '&input1=' . $row['input'];
        $inputs = explode('|', $tool['inputs']);
        $i      = 2;
        foreach ($inputs as $value) {
            if ($value != "" && !empty($row['input' . $i])) {
                $param .= '&input' . $i . '=' . $row['input' . $i];
            }
            $i++;
        }

        $post = $param;
        $turl = $config['url'] . 'api.php?act=pay';
        $param .= '&user=' . $config['username'] . '&pass=' . $password;
        $data = shequ_get_curl($turl . '&' . $param, $param, 0, 0, 0, 0, 0, $config['proxy']);
        $data = trim($data);
        if (!($json = json_decode($data, true))) {
            $json = json_decode('{' . getSubstr($data, '{', '}') . '}', true);
        }
        if (is_array($json)) {
            if ($json['code'] != 0) {
                log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $post, '下单失败，' . $json['message'], 0, $row['id']);
                return '下单失败！' . $json['message'];
            }
            $status = 1;
            if ($config['orderstatus']) {
                $status = $config['orderstatus'];
            }
            $result = "";
            if (is_array($json['kmdata']) && count($json['kmdata']) > 0) {
                $addData = (new Card())->getCardData($row, $config, $json['kmdata']);
                if (!empty($addData['kmdata'])) {
                    $result = "以下是卡密内容，请参考商品介绍使用<br>\r\n" . $addData['kmdata'];
                    $djzt   = 3;
                    $status = 1;
                } else {
                    $result = "订单已记录并处理，请联系网站客服获取卡密信息";
                    $djzt   = 1;
                }
                $sqlData = [
                    ':result'  => $result,
                    ':djorder' => $json['orderid'],
                    ':status'  => $status,
                    ':id'      => $row['id'],
                ];
                $DB->query("UPDATE `pre_orders` set result=:result,djorder=:djorder,djzt='{$djzt}',status=:status where id=:id", $sqlData);
            } else {
                $result  = !empty($tool['result']) ? $tool['result'] : $result;
                $sqlData = [
                    ':result'  => $result,
                    ':djorder' => $json['orderid'],
                    ':status'  => $status,
                    ':id'      => $row['id'],
                ];
                $DB->query("UPDATE `pre_orders` set result=:result,djorder=:djorder,djzt='1',status=:status where id=:id", $sqlData);
            }

            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $post, '下单成功！订单号：' . $json['orderid'], 1, $row['id']);
            return '下单成功！订单号：' . $json['orderid'];
        } else {
            $DB->query("UPDATE `pre_orders` set status='0',djzt='2' where id=:id", [':id' => $row['id']]);
            $data = str_replace(array("\r\n", "\r", "\n"), "", $data);
            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $post, $data, 0, $row['id']);
            return '下单失败！' . $data;
        }
    }

    /**
     * 价格监控
     */
    public function pricejk($shequ = [], $cids = '')
    {
        global $DB, $conf;
        $success  = 0;
        $warnlist = '';

        $rs = [];
        if ($cids != '') {
            $rs = $DB->select("SELECT * FROM cmy_tools WHERE shequ='{$shequ['id']}' AND is_curl=2 and cid IN ({$cids})");
        }

        if ($rs && count($rs) > 0) {
            $breakMaxPrice = $conf['cron_maxprice'] ? $conf['cron_maxprice'] : '8888';
            $json          = $this->getGoodsList($shequ);
            if ($json['code'] == 0) {
                $list = [];
                foreach ($json['data'] as $row) {
                    $list[$row['tid']] = $row;
                }

                $num = count($rs);
                foreach ($rs as $key => $res2) {
                    if ($res2['price'] >= $breakMaxPrice) {
                        continue;
                    }

                    if (isset($list[$res2['goods_id']])) {
                        if ($res2['value'] < 1) {
                            $res2['value'] = 1;
                        }
                        $item         = $list[$res2['goods_id']];
                        $price1       = floatval($item['price'] * $res2['value']);
                        $goods_status = intval($item['close']);
                        // $min          = intval($item['min']);
                        $max     = intval($item['max']);
                        $stock   = isset($item['stock']) ? $item['stock'] : null;
                        $shopimg = addslashes($item['shopimg']);
                        $desc    = $item['desc'];
                        $desc    = addslashes(stripslashes($desc));
                        if ($max > 0) {
                            $max = floor($max / $res2['value']);
                        }

                        if ($conf['cron_status_sync'] != 1) {
                            $this->setToolActive($goods_status == 1 ? 0 : 1, $res2['tid']);
                        }

                        if ($price1 > 0) {
                            $this->setToolPrice($price1, $res2);
                        }

                        $sql = "`price1`='{$price1}'";

                        if ($res2['max'] != $max) {
                            $sql .= ",`max`='" . $max . "'";
                        }

                        if ($conf['corn_desc'] == 1 && $desc) {
                            $sql .= ",`desc`='" . $desc . "'";
                        }

                        if ($conf['corn_stock'] == 1) {
                            if (!is_null($stock)) {
                                // 彩虹的stock为null是不限库存
                                $sql .= ",`stock_open`='1', `stock`='" . intval($stock) . "'";
                            } else {
                                if (isset($item['stock'])) {
                                    $sql .= ",`stock_open`='0', `stock`='0'";
                                }
                            }
                        }

                        if ($conf['corn_shopimg'] == 1 && $shopimg) {
                            if (stripos($shopimg, 'http') === false) {
                                $shopimg = $shequ['url'] . trim($shopimg, '/');
                            }
                            $sql .= ",`shopimg`='" . $shopimg . "'";
                        }
                        $sql = "UPDATE `pre_tools` SET {$sql} where tid='" . $res2['tid'] . "'";
                        $success++;
                        $DB->query($sql);
                    } else {
                        //商品不存在，下架
                        $success++;
                        $this->setToolActive(0, $res2['tid']);
                    }
                }
                return array('code' => 0, "msg" => '共' . $num . '个商品，成功更新' . $success . '个', "success" => $success, "warnlist" => $warnlist);
            } else {
                return array('code' => -1, "msg" => '同步时查询商品数据失败， ' . $json['msg'], "success" => 0);
            }

        } else {
            return array('code' => -1, "msg" => '对接该货源站的本地商品数据为空或查询失败', "success" => 0);
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
        $url  = $config['url'] . 'api.php?act=search&id=' . $row['djorder'];
        $text = get_curl($url);
        $json = $this->json_decode($text);
        if ($json) {
            if ($json['code'] == 0) {
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
            } else {
                $msg = isset($json['message']) ? $json['message'] : $json['msg'];
                $ret = array('code' => -1, "msg" => "查询失败，" . $msg);
            }
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
        $url    = $config['url'] . "api.php?act=classlist";
        $result = get_curl($url);
        $json   = json_decode($result, true);
        if (is_array($json) && $json['code'] == '0') {
            $json['url'] = $config['url'];
            $ret         = $json;
        } else {
            $ret = array('code' => -1, "msg" => "网站打开失败，请检查该站点防火墙或网站访问状态", "data" => $result);
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
        $config['url'] = shequ_url_parse($config);
        $url           = $config['url'] . "api.php?act=goodsdetails";
        $pwd           = $config['password'];
        $post          = 'user=' . $config['username'] . '&pass=' . $pwd . '&tid=' . $goods_id;
        $result        = get_curl($url, $post);
        $json          = json_decode($result, true);
        if (is_array($json)) {
            if ($json['code'] == '0') {
                $ret = array('code' => 0, "msg" => 'succ', "data" => $json['data'], "url" => $config['url']);
            } else {
                $msg = isset($json['message']) ? addslashes($json['message']) : addslashes($json['msg']);
                $ret = array('code' => -1, "msg" => '[goods_id：' . $goods_id . ']' . $msg, "url" => $url);
            }
        } else {
            $ret = array('code' => -1, "msg" => "获取商品详情失败，请稍后重试！", "data" => $result, "url" => $url);
        }
        return $ret;
    }
}
