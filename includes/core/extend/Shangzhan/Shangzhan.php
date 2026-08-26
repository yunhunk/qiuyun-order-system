<?php

use \core\Card;

class Shangzhan extends Card
{
    /**
     * 配置信息
     *
     * @var array
     */
    private $config = [];

    private $customerid = '';
    private $paypwd     = '';
    private $token      = '';
    public $url         = 'www.shangzhanwl.com';

    private $isSsl = false;

    /**
     * 构造方法初始化
     * @param array   $config        配置信息
     * @param integer $_ssl          是否https
     */
    public function __construct($config = [], $_ssl = 0)
    {
        $this->config = $config;

        $this->customerid = $this->config['username'];
        $this->token      = $this->config['password'];
        $this->paypwd     = $this->config['paypwd'];
        if (empty($this->customerid)) {
            throw new \Exception("用户ID不能为空！", 1);
        }

        if (empty($this->token)) {
            throw new \Exception("用户token不能为空！", 1);
        }

        if (empty($this->paypwd)) {
            throw new \Exception("用户交易密码不能为空！", 1);
        }
        $this->url   = shequ_url_parse($this->config);
        $this->isSsl = $_ssl;
        return true;
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
     * 获取请求状态信息
     */
    public function getResultInfo($resultcode, $type = 'buyorder')
    {
        $status_arr = array(
            '403'  => "禁止访问",
            '404'  => "请求方法不存在",
            '1000' => $type == 'buyorder' ? "下单成功" : '查询成功',
            '1001' => "请求参数不合法",
            '1003' => "签名错误",
            '1004' => "访问频繁，两次间隔不能低于10秒钟",
            '1005' => "请求方式错误",
            '1006' => "客户编号不存在",
            '1007' => "客户编号已经被禁用",
            '1008' => "未开通供货接口功能",
            '1009' => "未开通进货接口功能",
            '1010' => $type == 'buyorder' ? "下单失败" : '查询无数据',
            '2001' => "系统未知错误",
        );

        return !empty($status_arr[$resultcode]) ? $status_arr[$resultcode] : '返回状态码未知，' . $resultcode;
    }

    public function doOrder($row, $config = [], $tool = [], $num = 1)
    {
        global $DB, $date;

        if ($row['status'] == -1) {
            $DB->query("UPDATE `pre_orders` set status='0', djzt='2' where id=:id", [':id' => $row['id']]);
        } elseif ($row['status'] > 0) {
            return '该订单已对接处理！';
        }

        if (empty($this->token)) {
            return '对接Key不能为空！';
        } elseif (empty($this->paypwd)) {
            return '对接交易密码不能为空！';
        } elseif (empty($this->customerid)) {
            return '对接用户ID不能为空！';
        }

        $tool = $DB->get_row("SELECT * FROM pre_tools where tid=:tid limit 1", [":tid" => $row["tid"]]);
        if (!$tool) {
            return '该商品信息不存在，无法获取对接信息！';
        }

        $value = $tool['value'] > 0 ? $tool['value'] : 1;
        $num   = $row['value'] > 0 ? $row['value'] * $value : $value;

        if ($tool['goods_type'] == 1) {
            $url = $this->url . 'api.php/Client/createCdKeyOrder';
        } else {
            $url = $this->url . 'api.php/Client/createOrder';
        }

        $_p = array(
            'customerid'       => $this->customerid,
            'goodsid'          => $tool['goods_id'],
            'accountname'      => $row['input'],
            'reaccountname'    => $row['input'],
            'external_orderno' => $row['payorder'],
            'quantity'         => $num,
        );
        $input = $this->parseInputData($row);
        if (count($input) > 0) {
            foreach ($input as $key => $value) {
                $_p['lblName' . $key] = urlencode($value);
            }
        }
        $params              = http_build_query($_p);
        $_p['tradepassword'] = $this->paypwd;
        $_p['sign']          = md5($this->customerid . $tool['goods_id'] . $this->token);

        $data = get_curl($url, http_build_query($_p));
        $arr  = json_decode($data, true);
        if (is_array($arr)) {
            if ($arr['code'] == '1000') {
                $sql    = "UPDATE `pre_orders` SET `endtime`=:endtime,`djorder`=:djorder,`bz`=:bz,`djzt`=1,`status`=:status where id=:id";
                $status = 1;
                if (isset($config['orderstatus'])) {
                    $status = $config['orderstatus'];
                }
                $message  = '下单成功，订单号' . $arr['data']['orderno'];
                $sql_data = array(
                    ':endtime' => $date,
                    ':djorder' => $arr['data']['orderno'],
                    ':bz'      => $message,
                    ':status'  => $status,
                    ':id'      => $row['id'],
                );
                $DB->query($sql, $sql_data);
            } else {
                $message = '下单失败，' . $arr['info'];
                $DB->query("UPDATE `pre_orders` set `djzt`='2', `status`='0', `endtime`= ? where id= ?", [$date, $row['id']]);
            }
            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $message, 1, $row['id']);
            return $message;
        } else {
            $DB->query("UPDATE `pre_orders` set `djzt`='2', `status`='0', `endtime`= ? where id= ?", [$date, $row['id']]);
        }

        $message = '返回数据失败' . $data;
        log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $message, 1, $row['id']);
        return $message;
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
            return ["code" => -1, "msg" => '对接订单编号不能为空！'];
        } elseif (empty($this->token)) {
            return ["code" => -1, "msg" => '对接Key不能为空！'];
        } elseif (empty($this->customerid)) {
            return ["code" => -1, "msg" => '对接用户ID不能为空！'];
        }

        $orderid        = $row['id'];
        $result         = [];
        $result['code'] = -1;
        $url            = $this->url . "api.php/Client/orderDetail";
        $_p             = array(
            'customerid' => $this->customerid,
            'orderno'    => $row['djorder'],
        );
        $_p['sign'] = md5($this->customerid . $this->token);
        $text       = get_curl($url, http_build_query($_p));
        $arr        = $this->json_decode($text);
        if (is_array($arr)) {
            if ($arr['code'] = 1000) {
                $order_state_arr = [
                    '1' => '排队中',
                    '2' => '处理中',
                    '3' => '交易成功',
                    '4' => '处理失败',
                    '5' => '成功退款',
                    '6' => '订单异常',
                ];

                $data        = $arr['data'];
                $order_state = $order_state_arr[$data["orderstate"]];
                if ($order_state == "") {
                    $order_state = "状态未知";
                }
                $result['code'] = 0;
                $result['msg']  = '查询接口订单成功：订单状态【' . $order_state . '】；状态码【' . $data["orderstate"] . '】';
                $result['data'] = array(
                    "num"         => $data["num"],
                    "start_num"   => $data["start_progress"],
                    "now_num"     => $data["current_progress"],
                    "order_state" => $order_state,
                    "orderid"     => $row['djorder'],
                    'add_time'    => $row['addtime'],
                );

                //同步订单
                if ($row['status'] == 2) {
                    $tool = $DB->get_row("SELECT * FROM pre_tools WHERE tid=:tid LIMIT 1", [':tid' => $row["tid"]]);
                    if (is_array($tool) && $tool['goods_type'] == 1) {
                        //卡密商品
                        $kmArr   = $data["recharge"]['card'];
                        $cardObj = new \core\Card();
                        $ret     = $cardObj->getCardData($row, $config, $kmArr);
                        $kmdata  = '';
                        $sql     = "UPDATE `pre_orders` SET `status`=:order_state,`result`=:result,`djzt`=:djzt WHERE id=:id";
                        if (!empty($ret['kmdata'])) {
                            $sql_data = array(
                                ':order_state' => 1,
                                ':result'      => $kmdata,
                                ':djzt'        => 3,
                                ':id'          => $row['id'],

                            );
                            $kmdata = "卡密信息如下，请参考商品介绍使用：<br>\r\n" . $ret["kmdata"];
                        } else {
                            $sql_data = array(
                                ':order_state' => 1,
                                ':result'      => $kmdata,
                                ':djzt'        => 4,
                                ':id'          => $row['id'],
                            );
                            $kmdata = '已发货，请联系平台客服查询';
                            log_result(getShequTypeName($config["type"]) . "对接", $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . json_encode($_p), '卡密识别失败，' . $ret['msg'], 1, $orderid);
                        }
                        $DB->query($sql, $sql_data);
                    } else {
                        if (in_array($data["orderstate"], ['4', '5', '6'])) {
                            $sql_data = array(
                                ':status' => 3,
                                ':result' => '订单软件状态异常【' . $order_state . '】，请联系网站客服处理',
                                ':id'     => $row['id'],
                            );
                        } elseif ($data["orderstate"] == '3') {
                            $sql_data = array(
                                ':status' => 1,
                                ':result' => '订单已完成，如有疑问联系客服核对',
                                ':id'     => $row['id'],
                            );
                        } else {
                            $sql_data = null;
                        }
                        if (null !== $sql_data) {
                            $sql = "UPDATE `pre_orders` set `status`=:status,`result`=:result where id=:id";
                            $DB->query($sql, $sql_data);
                        }
                    }
                }
            } elseif ($arr['code'] = 1010) {
                $result['msg'] = '查询接口订单失败：查无对应订单数据' . $result['msg'];
            } else {
                $result['msg'] = '查询接口订单失败：' . $this->getResultInfo($arr['code']);
            }
        } else {
            $result['msg'] = '查询订单详情失败，' . $text;
        }
        return $result;
    }

    /**
     * 获取分类列表
     *
     * @return array
     */
    public function getCateList($config = [])
    {
        if (empty($this->token)) {
            return ["code" => -1, "msg" => '对接Key不能为空！'];
        }

        $url = $this->url . "api.php/Client/getCateList";
        $_p  = array(
            'customerid' => $this->customerid,
        );
        $_p['sign'] = md5($this->customerid . $this->token);
        $ret        = get_curl($url, http_build_query($_p));
        $arr        = json_decode($ret, true);
        if (is_array($arr)) {
            if ($arr['code'] == 1000) {
                return ["code" => 0, "data" => $arr['data']];
            } else if ($arr['code'] == 1010) {
                return ["code" => -1, "msg" => '该网站一条目录数据也没有！'];
            } else {
                return ["code" => -1, "msg" => $this->getResultInfo($arr['code'], 'goods')];
            }
        } else {
            return ["code" => -1, "msg" => '打开对接网站' . $this->url . '失败，' . $ret];
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

        $num = count($rs);

        $result = $this->getGoodsListAll();

        if ($result['code'] == 0) {
            $arr = [];
            foreach ($result['data'] as $key => $value) {
                $arr[$value['id']] = $value;
            }

            foreach ($rs as $key => $res2) {
                if (empty($res2['goods_id'])) {
                    continue;
                }

                if ($res2['price'] >= $breakMaxPrice) {
                    continue;
                }

                if (!isset($arr[$res2['goods_id']])) {
                    continue;
                }

                if ($res2['value'] < 1) {
                    $res2['value'] = 1;
                }

                $goodsInfo = $arr[$res2['goods_id']];
                $price1    = $goodsInfo['price'] * $res2['value'];
                $desc      = '';
                $shopimg   = $goodsInfo['img'];
                $max       = intval($goodsInfo['quantity']);
                $max       = ceil($max / $res2['value']);
                $stock     = $goodsInfo['stock_num'];

                $this->setToolPrice($price1, $res2);

                if ($conf['cron_status_sync'] != 1) {
                    $active = $goodsInfo['supply_state'] == 1 ? 1 : 0;
                    //同步上下架
                    if ($goodsInfo['type'] == 1 && $goodsInfo['stock_state'] == 3) {
                        $active = $active == 1 ? 1 : 0;
                    } else {
                        $active = $goodsInfo['supply_state'] == 1 && $active == 1 ? 1 : 0;
                    }
                    $this->setToolActive($active, $res2['tid']);
                }

                $sql = "`uptime`='" . time() . "'";

                if ($conf['corn_desc'] == 1 && $desc) {
                    $desc = addslashes(stripslashes($desc));
                    $sql .= ",`desc`='" . $desc . "'";
                }

                if ($conf['corn_stock'] == 1 && $stock !== null) {
                    $sql .= ",`stock`='" . $stock . "'";
                }

                if ($conf['corn_shopimg'] == 1 && $shopimg) {
                    $shopimg = addslashes(stripslashes($shopimg));
                    $sql .= ",`shopimg`='" . $shopimg . "'";
                }

                if ($max > 0) {
                    $sql .= ",`max`='" . $max . "'";
                }

                $sql = "UPDATE `pre_tools` set {$sql} where tid='" . $res2['tid'] . "'";
                addWebLog('价格同步日志', '系统：' . getShequTypeName($shequ['type']) . '；执行语句：' . $sql, 'Cron');
                $DB->query($sql);
                $success++;
            }
        } else {
            $warnlist = "价格更新失败，" . $result['msg'];
        }

        return [
            'code'     => 0,
            "msg"      => '共需执行' . $num . '个，成功更新' . $success . '个商品（为避免超时和保证所有商品同步到位，某商品每次检测后间隔' . $crontime . '秒后才会再次检测）',
            "success"  => $success,
            "warnlist" => $warnlist,
        ];
    }

    /**
     * 获取全部商品列表
     *
     * @return array
     */
    public function getGoodsListAll()
    {
        if (empty($this->token)) {
            return ["code" => -1, "msg" => '对接Key不能为空！'];
        }

        $url = $this->url . "api.php/Client/GoodsList";
        $_p  = array(
            'customerid' => $this->customerid,
        );

        $_p['sign'] = md5($this->customerid . $this->token);
        $ret        = get_curl($url, http_build_query($_p));
        $arr        = json_decode($ret, true);
        if (is_array($arr)) {
            if (array_key_exists('code', $arr)) {
                if ($arr['code'] == 1000) {
                    return ["code" => 0, "data" => $arr['data']];
                } else if ($arr['code'] == 1010) {
                    return ["code" => -1, "msg" => '该目录下无商品数据！'];
                } else {
                    return ["code" => -1, "msg" => $this->getResultInfo($arr['code'], 'goods'), "arr" => $arr];
                }
            } else {
                return ["code" => -1, "msg" => '数据解析失败，请检查原始数据', "arr" => $arr];
            }
        } else {
            return ["code" => -1, "msg" => '打开对接网站' . $this->url . '失败，' . $ret];
        }
    }

    /**
     * 获取商品列表
     *
     * @return array
     */
    public function getGoodsList($config = [], $cate_id = 0)
    {
        if (empty($this->token)) {
            return ["code" => -1, "msg" => '对接Key不能为空！'];
        } elseif ($cate_id == 0) {
            return ["code" => -1, "msg" => '要获取的二级分类ID不能为空！'];
        }
        $url = $this->url . "api.php/Client/getCateGoods";
        $_p  = array(
            'customerid' => $this->customerid,
            'cate_id'    => $cate_id,
        );
        $_p['sign'] = md5($this->customerid . $cate_id . $this->token);
        $ret        = get_curl($url, http_build_query($_p));
        $arr        = json_decode($ret, true);
        if (is_array($arr)) {
            if (array_key_exists('code', $arr)) {
                if ($arr['code'] == 1000) {
                    $data = [];
                    foreach ($arr['data'] as $key => $value) {
                        // 处理图片链接
                        $value['shopimg'] = $value['img'] = str_replace('bdimg.shangzhan.com', 'imgcdn.99kami.com', $value['img']);

                        $value['tid']      = $value['id'];
                        $value['goods_id'] = $value['id'];
                        $data[]            = $value;
                    }
                    return ["code" => 0, "data" => $arr['data']];
                } else if ($arr['code'] == 1010) {
                    return ["code" => -1, "msg" => '该目录下无商品数据！'];
                } else {
                    return ["code" => -1, "msg" => $this->getResultInfo($arr['code'], 'goods'), "arr" => $arr];
                }
            } else {
                return ["code" => -1, "msg" => '数据解析失败，请检查原始数据', "arr" => $arr];
            }
        } else {
            return ["code" => -1, "msg" => '打开对接网站' . $this->url . '失败，' . $ret];
        }
    }

    /**
     * 获取商品参数
     *
     * @return array
     */
    public function getGoodsParams($config = [], $goodsid)
    {
        if (empty($this->token)) {
            return ["code" => -1, "msg" => '对接Key不能为空！'];
        } elseif ($goodsid == 0) {
            return ["code" => -1, "msg" => '商品ID不能为空！'];
        }
        $url = $this->url . "api.php/Client/goodsInfo";
        $_p  = array(
            'customerid' => $this->customerid,
            'id'         => $goodsid,
        );
        $_p['sign'] = md5($this->customerid . $goodsid . $this->token);
        $result     = get_curl($url, http_build_query($_p));
        $json       = json_decode($result, true);
        if (is_array($json)) {
            if (array_key_exists('code', $json)) {
                if ($json['code'] == 1000) {
                    $info = $json['data'];
                    if ($info['type'] == 1 && $info['stock_state'] == 3) {
                        $active = 1;
                    } else {
                        $active = $info['supply_state'] == 1 ? 1 : 0;
                    }

                    $data = array(
                        'id'           => $info['id'],
                        'name'         => $info['name'],
                        'type'         => $info['type'] == 2 ? '0' : '1',
                        'price'        => $info['price'],
                        'min'          => 1,
                        'minnum'       => 1,
                        'max'          => intval($info['quantity']),
                        'maxnum'       => intval($info['quantity']),
                        'active'       => $active,
                        'recharge_url' => $info['recharge_url'],
                        'desc'         => $info['info'],
                        'alert'        => $info['notice'],
                        'input'        => $info['template']['account'],
                        'param'        => '',
                        'input'        => $info['template']['account'],
                        'stock_num'    => $info['stock_num'],
                        'stock_num'    => $info['stock_num'],
                        'shopimg'      => $info['img'],
                    );
                    $inputs = '';
                    if (isset($info['template']) && is_array($info['template']['content'])) {
                        foreach ($info['template']['content'] as $key => $option) {
                            if ($option['type'] == 'select' || $option['type'] == 'radio') {
                                $inputs .= '|' . $option['name'] . '{' . rtrim($option['value'], ',') . '}';
                            } else {
                                $inputs .= '|' . $option['name'];
                            }
                        }
                    }

                    // 处理图片
                    $data['shopimg'] = str_replace('bdimg.shangzhan.com', 'imgcdn.99kami.com', $data['shopimg']);
                    $data['desc']    = preg_replace('/bdimg.shangzhan.com/im', 'imgcdn.99kami.com', $data['desc']);
                    $data['inputs']  = trim($inputs, '|');
                    return ["code" => 0, "data" => $data];
                } else {
                    return ["code" => -1, "msg" => $this->getResultInfo($json['code'], 'goods'), "result" => $json];
                }
            } else {
                return ["code" => -1, "msg" => '数据解析失败，请检查原始数据！商品ID[' . $goodsid . ']', "arr" => $json];
            }
        } else {
            return ["code" => -1, "msg" => '打开对接网站' . $this->url . '失败'];
        }
    }
}
