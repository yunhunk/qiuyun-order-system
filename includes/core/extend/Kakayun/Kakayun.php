<?php

use \core\Card;

class Kakayun extends Card
{
    /**
     * 配置信息
     *
     * @var array
     */
    private $config = [];

    private $isSsl = false;

    /**
     * 价格监控方式   0所有 1自动分页
     *
     * @var integer
     */
    private $pricejk_type = 1;

    /**
     * 价格监控每次多少页
     *
     * @var integer
     */
    private $pricejk_page_num = 3;

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

    /**
     * 解析json
     *
     * @param string $string 解析字符串
     * @return array|null    返回解析结果
     */
    public function json_decode($string = '')
    {
        $text  = trim(str_replace('\\"', '"', $string), "\xEF\xBB\xBF");
        $array = json_decode($text, true);
        if (is_array($array)) {
            return $array;
        }
        return json_decode('{' . $this->getSubstr($text, '{', '}') . '}', true);
    }

    /**
     * 截取字符串
     *
     * @param string $str
     * @param string $leftStr
     * @param string $rightStr
     * @return string
     */
    public function getSubstr($str = '', $leftStr = '', $rightStr = '')
    {
        $left  = strpos($str, $leftStr);
        $right = strrpos($str, $rightStr, $left);
        if ($left < 0 || $right < $left) {
            return '';
        }
        return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
    }

    public function doOrder($row, $config = [], $tool = [], $num = 1)
    {
        global $DB, $date;

        if ($row['status'] == -1) {
            $DB->query("UPDATE `pre_orders` set `status`='0', djzt='2' where id=:id", [':id' => $row['id']]);
        } elseif ($row['status'] > 0) {
            return '该订单已对接处理！';
        }

        $orderid = $row['id'];
        $num     = intval($num) > 0 ? intval($num) : 1;
        $url     = $config["url"] . "dockapi/index/buy.html";
        $arr     = array(
            "userid"     => $config["username"],
            "outorderno" => $row['payorder'] . rand(111, 999),
            "goodsid"    => $tool['goods_id'],
            "buynum"     => $num,
        );

        if ($tool['goods_type'] == 1) {
            //直冲商品
            $data   = $this->parseInputData($row);
            $attach = [];
            foreach ($data as $value) {
                if (!empty($value)) {
                    $attach[] = $value;
                }
            }
            $arr['attach'] = json_encode($attach);
        }

        $key = $config['password'];

        $post = http_build_query($arr) . '&sign=' . $this->kakayun_getSign($arr, $key);

        $result = shequ_get_curl($url, $post, 0, 0, 0, 0, 0, $config['proxy']);

        $json = json_decode($result, true);

        if (is_array($json)) {
            if ($json['code'] == 1) {
                $status = 1;
                $djzt   = 1;
                if ($config['orderstatus']) {
                    $status = $config['orderstatus'];
                }
                $orderno  = $json['orderno'];
                $cardlist = isset($json['cardlist']) ? $json['cardlist'] : null;
                $sql      = "UPDATE `pre_orders` SET `result`=:result,`djorder`=:djorder,`djzt`=:djzt,`bz`=:bz,`endtime`=:endtime,`status`=:status WHERE `id`=:id";
                if ($cardlist != null) {
                    //卡密商品
                    $djzt   = 4;
                    $status = 2;
                    $ret    = (new Card())->getCardData($row, $config, $json['cardlist']);
                    $kmdata = '';
                    if (!empty($ret['kmdata'])) {
                        $message = "下单成功，订单号：" . $json['orderno'] . '；卡密提取成功';
                        $result  = "卡密信息如下，请参考商品介绍使用：<br>\r\n" . $ret["kmdata"];
                        $djzt    = 3;
                        $status  = 1;
                    } else {
                        $message = "下单成功，订单号：" . $json['orderno'] . '；卡密识别失败，' . $json['msg'];
                        $result  = '订单已处理，但自动**失败，请联系客服取卡';
                    }
                } else {
                    //直冲商品
                    $message = "下单成功，订单号：" . $json['orderno'];
                    $result  = '订单已记录，排队处理中~长时间未开始或未到站请联系客服';
                    if (!empty($tool['result'])) {
                        $result = $tool['result'];
                    }
                }
                $data = [
                    ':result'  => $result,
                    ':djorder' => $json['orderno'],
                    ':djzt'    => $djzt,
                    ':bz'      => '对接信息：' . $message,
                    ':endtime' => $date,
                    ':status'  => $status,
                    ':id'      => $row['id'],
                ];
                $DB->exec($sql, $data);
                log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $post, $message, 1, $orderid);
            } else {
                $DB->query("UPDATE `pre_orders` set status='0',djzt='2' where id=:id", [':id' => $row['id']]);
                $message = "下单失败，" . $json["msg"];
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
            return array('code' => -1, 'msg' => '缺少对接订单号！');
        }

        $djorder = $row['djorder'];

        $arr = array(
            "userid"  => $config["username"],
            "orderno" => $djorder,
        );

        $key = $config['password'];

        $post = http_build_query($arr) . '&sign=' . $this->kakayun_getSign($arr, $key);

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
     * 价格监控
     *
     */
    public function pricejk($shequ = [], $cids = '')
    {
        global $DB, $conf;
        $success       = 0;
        $warnlist      = '';
        $breakMaxPrice = $conf['cron_maxprice'] ? $conf['cron_maxprice'] : '1888';
        $crontime      = isset($shequ['crontime']) && $shequ['crontime'] >= 30 ? $shequ['crontime'] : 60;
        $uptime        = time() - $crontime;
        $rs            = [];
        if ($cids != '') {
            $rs = $DB->select("SELECT * FROM cmy_tools WHERE `shequ`='{$shequ['id']}' AND `uptime`<='{$uptime}' AND is_curl=2 and cid IN ({$cids})");
        }

        $num   = count($rs);
        $count = 0;

        $result = $this->getGoodsListAll($shequ);

        $page = intval($conf['kakayun_get_page']);

        if ($result['code'] == 0) {
            $breakMaxPrice = $conf['cron_maxprice'] ? $conf['cron_maxprice'] : '1888';
            if (!is_array($result['data'])) {
                $result['data'] = [];
            }
            // $goodsList = [];
            $price_arr = [];

            foreach ($result['data'] as $row) {
                $price_arr[$row['id']]['price']        = $row['price'];
                $price_arr[$row['id']]['goods_status'] = $row['active'];
                $price_arr[$row['id']]['goodstype']    = $row['type'];
                $price_arr[$row['id']]['showstock']    = $row['stock'] != null;
                $price_arr[$row['id']]['stock']        = $row['stock'];
            }

            $count = count($price_arr);

            //$this->writeLogs("卡卡云价格返回\n".json_encode($price_arr), 'priceJk.txt');
            $rs = [];
            if ($cids != '') {
                $rs           = $DB->select("SELECT * FROM cmy_tools WHERE shequ='{$shequ['id']}' AND is_curl=2 and cid IN ({$cids})");
                !($rs) && $rs = [];
            }
            $message = '';

            foreach ($rs as $key => $res2) {
                if ($res2['price'] >= $breakMaxPrice) {
                    continue;
                }

                if (array_key_exists($res2['goods_id'], $price_arr)) {
                    if ($res2['value'] < 1) {
                        $res2['value'] = 1;
                    }
                    $info         = $price_arr[$res2['goods_id']];
                    $price1       = sprintf('%.2f', $info['price'] * $res2['value']);
                    $goodstype    = $info['goodstype'];
                    $stock        = null;
                    $goods_status = intval($info['goods_status']);
                    if ($goods_status == 1) {
                        //**商品 或手工
                        if ($goodstype == 0 && $info['stock'] != null) {
                            if ($info['stock'] > 0) {
                                $goods_status = 1;
                                $stock        = $info['stock'];
                            } else {
                                $goods_status = 0;
                                $stock        = 0;
                            }
                        }
                    }

                    $this->setToolActive($goods_status, $res2['tid']);
                    $sql = "`price1`='{$price1}'";
                    if (isset($price1) && $price1 > 0) {
                        $this->setToolPrice($price1, $res2);
                    }

                    $message .= "商品[" . $res2['tid'] . "]更新成功，状态【" . $goods_status . "|" . $price_arr[$res2['goods_id']]['stock'] . "】\n<br>";
                    if ($stock != null) {
                        $sql .= ",`stock_open`='1', `stock`='" . intval($stock) . "'";
                    }

                    $success++;
                    $sql = "UPDATE `pre_tools` SET {$sql} where tid='" . $res2['tid'] . "'";
                    // addWebLog('价格同步日志', '系统：' . getShequTypeName($shequ['type']) . '；执行语句：' . $sql, 'Cron');
                    $DB->query($sql);
                } else {
                    //商品不存在
                    if ($this->pricejk_type != 1) {
                        $message .= "商品[" . $res2['tid'] . "]对接业务不存在，已自动下架\n<br>";
                        $this->setToolActive(0, $res2['tid']);
                        $success++;
                    }
                }
            }
        } else {
            $warnlist = "价格更新失败，" . $result['msg'];
        }

        return [
            'code'     => 0,
            "msg"      => $warnlist ? $warnlist : '对接站查询到个' . $count . '商品' . ($this->pricejk_type == 1 ? '(当前第' . $page . '页)' : '') . ', 共需执行' . $num . '个，成功更新' . $success . '个商品（为避免超时和保证所有商品同步到位，某商品每次检测后间隔' . $crontime . '秒后才会再次检测）',
            "success"  => $success,
            "warnlist" => $warnlist,
        ];
    }

    /**
     * 获取全部商品列表
     *
     * @return array
     */
    public function getGoodsListAll($shequ = [])
    {
        global $conf;
        if ($this->pricejk_type == 1 && !isset($_GET['page'])) {
            $page = intval($conf['kakayun_get_page']);
            if ($page <= 0) {
                $page = 1;
            }
        } else {
            $page = 1;
        }
        $result = [
            'code' => 0,
            'data' => [],
            'msg'  => '',
        ];
        $while = true;
        $num   = 1;
        while ($while) {
            $res = $this->getGoodsList($shequ, 0, $page);

            if ($res['code'] == 0) {
                if (isset($res['data'])) {
                    foreach ($res['data'] as $key => $value) {
                        $result['data'][] = $value;
                    }
                }

                if ($this->pricejk_type == 1 && $num >= $this->pricejk_page_num) {
                    saveSetting('kakayun_get_page', (intval($page) + 1));
                    $while = false;
                } else {
                    if ($num >= $this->pricejk_page_num && ($this->pricejk_type == 1 || !isset($res['allPage'])) || $page >= $res['allPage']) {
                        if ($this->pricejk_type == 1 && !isset($res['allPage']) || $page >= $res['allPage']) {
                            saveSetting('kakayun_get_page', 1);
                        }
                        continue;
                    } else {
                        $page++;
                        $num++;
                        usleep(3.5 * 1000 * 1000);
                    }
                }
            } else {
                return $res;
            }

        }
        return $result;
    }

    /**
     * 获取分类列表 v2支持
     *
     * @return array
     */
    public function getCateList($config = [])
    {
        global $webConfig;
        $config["url"] = shequ_url_parse($config);
        $url           = $config["url"] . "dockapi/v2/getallgoodsgroup";
        $arr           = array(
            "userid" => $config["username"],
        );

        $post = http_build_query($arr) . '&sign=' . $this->kakayun_getSign($arr, $config['password']);

        $result = shequ_get_curl($url, $post, 0, 0, 0, 0, 0, $config['proxy']);

        if ($webConfig['debug']) {
            @addWebLog('获取分类', "url：" . $url . "\ndata：" . $post . "\nresult：" . $result, 'Shequ', 1);
        }

        $json = json_decode($result, true);
        if (is_array($json)) {
            if ($json['code'] == 1) {
                $data = [];
                if (count($json['data']) == 0) {
                    $data = [
                        [
                            'id'   => 0,
                            'name' => '该站点商品分类为空',
                        ],
                    ];
                } else {
                    $data = $this->parseCategoryList($json['data']);
                    array_splice($data, 0, 0, [
                        [
                            'id'       => -1,
                            'name'     => '所有商品',
                            'children' => [
                                [
                                    'id'   => -1,
                                    'name' => '所有商品',
                                ],
                            ],
                        ],
                    ]);
                }
                $ret = ['code' => 0, "msg" => $json['msg'], "data" => $data];
            } else {
                $ret = ['code' => -1, "msg" => $json['msg'], "data" => []];
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

    public function parseCategoryList($data)
    {
        $arr = [];
        // 解析所有一级分类
        foreach ($data as $key => $value) {
            if ($value['brandid'] <= 0) {
                continue;
            }

            if (!$this->array_some($arr, function ($item, $key, $value) {
                return $item['id'] == $value['brandid'];
            }, $value)) {
                $arr[] = [
                    'id'    => $value['brandid'] ?? 0,
                    'name'  => $value['brandname'] ?? '',
                    'image' => $value['brandimgurl'] ?? '',
                ];
            }
        }
        // 解析所有二级分类
        foreach ($arr as $key => &$value) {
            $value['children'] = $this->parseCategorySubList($data, $value['id'], $value['name']);
        }
        return $arr;
    }

    public function parseCategorySubList($data, $brandid, $brandname)
    {
        $temp = [];
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if ($value['groupid'] <= 0) {
                    continue;
                }

                if ($value['brandid'] == $brandid || $value['brandname'] == $brandname) {
                    $temp[] = [
                        'id'    => $value['groupid'] ?? 0,
                        'name'  => $value['groupname'] ?? '',
                        'image' => $value['groupimgurl'] ?? '',
                    ];
                }
            }
        }
        return $temp;
    }

    public function array_some($data, $call = null, ...$params)
    {
        $ret = false;
        if (is_array($data)) {
            foreach ($data as $key => $item) {
                if ($call) {
                    $ret = $call($item, $key, ...$params);
                    if ($ret) {
                        break;
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * 获取商品列表
     *
     * @return array
     */
    public function getGoodsList($config = [], $cid = 0, $page = 1)
    {
        global $CACHE, $webConfig;

        $arr = array(
            "userid" => $config["username"],
        );
        $arr["limit"] = 100;
        $arr["page"]  = intval($page);
        if ($cid > 0) {
            $arr["goodsgroupid"] = $cid;
            $url                 = $config["url"] . "dockapi/v2/getallgoods";
        } else {
            $arr["limit"] = 100;
            $url          = $config["url"] . "dockapi/v2/getallgoods";
        }

        $post   = http_build_query($arr) . '&sign=' . $this->kakayun_getSign($arr, $config['password']);
        $result = shequ_get_curl($url, $post, 0, 0, 0, 0, 0, $config['proxy'], 20);

        $json = json_decode($result, true);
        if (is_array($json)) {
            if ($json['code'] == 1) {
                $data = [];
                foreach ($json['data'] as $row) {
                    if ($cid > 0 || !isset($row['goods'])) {
                        $data[] = [
                            'id'      => $row['goodsid'],
                            'groupid' => $row['goodsgroupid'],
                            'cid'     => $row['goodsgroupid'],
                            'minnum'  => $row['buyminnum'],
                            'maxnum'  => $row['buymaxnum'],
                            'name'    => $row['goodsname'],
                            'price'   => $row['goodsprice'],
                            'type'    => $row['goodstype'],
                            // 'stock'   => $row['stock'],
                            'stock'   => $row['showstock'] ? $row['stock'] : null,
                            'active'  => intval($row['goodsstatus']),
                        ];
                    } else {
                        foreach ($row['goods'] as $key => $tool) {
                            $data[] = [
                                'id'      => $tool['id'],
                                'name'    => $tool['goodsname'],
                                'price'   => $tool['goodsprice'],
                                'minnum'  => $tool['buyminnum'],
                                'maxnum'  => $tool['buymaxnum'],
                                'type'    => $tool['goodstype'],
                                'stock'   => $tool['showstock'] ? $tool['stock'] : null,
                                'active'  => intval($tool['goodsstatus']),
                                'groupid' => $tool['goodsgroupid'],
                                'cid'     => $row['goodsgroupid'],
                                'desc'    => $tool['details'],
                                'shopimg' => $tool['imgurl'],
                                'unit'    => $tool['unit'],
                            ];
                        }
                    }
                }

                if (count($data) == 0 && $cid > 0) {
                    $data[] = [
                        'id'      => 0,
                        'groupid' => 0,
                        'name'    => '该分类下无商品',
                        'price'   => 0,
                        'type'    => 1,
                        'stock'   => 0,
                        'active'  => 0,
                    ];
                }

                $allPage = isset($json['allpage']) && $json['allpage'] > 0 ? $json['allpage'] : 1;

                $ret = array(
                    'code'    => 0,
                    "msg"     => $json['msg'],
                    "data"    => $data,
                    "allPage" => $allPage,
                    "page"    => true,
                    "groupid" => $cid,
                    "type"    => getShequType($config['type']),
                );
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
        $url = $config["url"] . "dockapi/v2/goodsdetails.html";
        $arr = array(
            "userid"  => $config["username"],
            "goodsid" => $goods_id,
        );

        $key = $config['password'];

        $post = http_build_query($arr) . '&sign=' . $this->kakayun_getSign($arr, $key);

        $text = shequ_get_curl($url, $post, 0, 0, 0, 0, 0, $config['proxy']);
        $json = json_decode($text, true);
        if (is_array($json)) {
            if ($json['code'] == 1) {

                $data['input']  = '';
                $data['inputs'] = '';
                $info           = $data['goodsdetails']           = $json['goodsdetails'];
                if ($info['goodstype'] == 1) {
                    // 代充参数
                    if (isset($json['attach'])) {
                        $attach = $json['attach'];
                    } else {
                        $attach = $info['attach'];
                    }

                    foreach ($attach as $key => $value) {
                        if ($key == 0) {
                            $data['input'] = $value['title'];
                        } else {
                            $data['inputs'] .= ($data['inputs'] ? '|' : '') . $value['title'];
                        }
                    }
                }

                if (isset($json['price']) && isset($json['price']['data'])) {
                    $data['price'] = $json['price'];
                } else {
                    $data['price'] = [
                        'code' => 1,
                        'data' => [
                            'goodsprice' => isset($info['goodsprice']) ? $info['goodsprice'] : $info['price'],
                        ],
                    ];
                }

                $data['shopurl'] = $config["url"] . '/pg/' . $goods_id . '.html';
                $result          = [
                    'code' => 0,
                    "msg"  => $json['msg'],
                    "data" => $data,
                    "type" => getShequType($config['type']),
                ];
            } else {
                $result = [
                    'code' => -1,
                    "msg"  => $json['msg'],
                    "data" => [],
                    "type" => getShequType($config['type']),
                ];
            }
        } else {
            $result = [
                'code' => -1,
                "msg"  => "获取卡卡云商品失败，请稍后重试！",
                "data" => $text,
                "type" => getShequType($config['type']),
            ];
        }
        return $result;
    }

    public function kakayun_getSign($param, $key)
    {
        $signPars = "";
        ksort($param);
        reset($param);
        foreach ($param as $k => $v) {
            if ("sign" != $k && "" != $v) {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars = trim($signPars, '&');
        $signPars .= $key;
        $sign = md5($signPars);
        return $sign;
    }
}
