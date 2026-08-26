<?php

use \core\Card;

class Kayixinnew extends Card
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
    public $url         = '';
    private $userNo     = '';

    /**
     * 订单状态
     *
     * @var array
     */
    private $status_arr = [
        0 => '待处理', // 待支付
        1 => '待处理',
        2 => '进行中', // 处理中
        3 => '已完成', // 已处理
        4 => '已退单', // 已退单
        5 => '已退款', // 已退款
        7 => '待处理', // 待同步
        8 => '退单中', // 退单中
        9 => '退款中', // 退款中
    ];

    /**
     * 构造方法初始化
     * @param array   $config        配置信息
     * @param integer $_ssl          是否https
     */
    public function __construct($config = [], $_ssl = 0)
    {
        $this->userNo = $config['username'];
        $this->token  = $config['password'];
        if (empty($this->userNo)) {
            throw new \Exception("客户编号不能为空！", 1);
        }

        if (empty($this->token)) {
            throw new \Exception("接口密钥token不能为空！", 1);
        }

        $this->url = $config['url'];
        if (empty($this->url)) {
            throw new \Exception("接口地址不能为空！", 1);
        }
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
            $DB->query("UPDATE `pre_orders` set status='0', djzt='2' where id=:id", [':id' => $row['id']]);
        } elseif ($row['status'] > 0) {
            return '该订单已对接处理！';
        }

        if (empty($this->token)) {
            return '接口密钥不能为空！';
        }

        $tool = $DB->get_row("SELECT * FROM cmy_tools where tid=:tid limit 1", [":tid" => $row["tid"]]);
        if (!$tool) {
            return '该商品信息不存在，无法获取对接信息！';
        }

        $value = $tool['value'] > 0 ? $tool['value'] : 1;
        $num   = $row['value'] > 0 ? $row['value'] * $value : $value;

        $_p = array(
            'userNo'    => $this->userNo,
            'id'        => $tool['goods_id'],
            'safePrice' => $row['money'],
            'count'     => $num,
        );

        $goods_form = json_decode($tool['goods_form'], true);
        if ($goods_form) {
            foreach ($goods_form as $key => $value) {
                $_p[$key] = $value;
            }
        }

        $params     = http_build_query($_p);
        $_p['sign'] = md5(strtolower($this->token . $this->userNo . $tool['goods_id'] . $num));
        if ($tool['goods_type'] == 1) {
            $url = $this->url . 'api/buyCardGoodOrder.htm';
        } else {
            // 人工代充参数
            $_p['account'] = $row['input'];
            $input         = $this->parseInputData($row);
            $goods_param   = explode('|', $tool['goods_param']);
            foreach ($goods_param as $key => $value) {
                $_p['name' . $key] = $value;
                $_p['val' . $key]  = $input[$key];
            }
            $url = $this->url . 'api/buyGoodOrder.htm';
        }

        $data = get_curl($url, http_build_query($_p));
        $arr  = json_decode($data, true);
        if (is_array($arr)) {
            if ($arr['code'] == 1000) {
                $sql    = "UPDATE `pre_orders` SET `endtime`=:endtime,`djorder`=:djorder,`bz`=:bz,`djzt`=1,`status`=:status where `id`=:id";
                $status = 1;
                if (isset($config['orderstatus'])) {
                    $status = $config['orderstatus'];
                }

                $message  = '下单成功，订单号' . $arr['id'];
                $sql_data = array(
                    ':endtime' => $date,
                    ':djorder' => $arr['id'],
                    ':bz'      => $message,
                    ':status'  => $status,
                    ':id'      => $row['id'],
                );
                $DB->query($sql, $sql_data);
                if ($tool['goods_type'] == 1) {
                    // 查询卡密订单
                    $row['djorder'] = $arr['id'];
                    $this->query($row, $config);
                }
            } else {
                $message = '下单失败，对接站返回：' . $arr['msg'];
                $DB->query("UPDATE `pre_orders` set `djzt`='2', `status`='0', `endtime`= ? where id= ?", [$date, $row['id']]);
            }
            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $message, 1, $row['id']);
            return $message;
        } else {
            $DB->query("UPDATE `pre_orders` set `djzt`='2', `status`='0', `endtime`= ? where id= ?", [$date, $row['id']]);
        }
        $message = '对接站打开失败，数据解析失败' . $data;
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
            return ["code" => -1, "msg" => '接口密钥不能为空！'];
        } elseif (empty($this->userNo)) {
            return ["code" => -1, "msg" => '客户编号不能为空！'];
        }

        $orderid        = $row['id'];
        $result         = [];
        $result['code'] = -1;
        $url            = $this->url . 'api/getOrder.htm';
        $_p             = array(
            'userNo' => $this->userNo,
            'id'     => $row['djorder'],
            'sign'   => md5(strtolower($this->token . $this->userNo . $row['djorder'])),
        );
        $text = get_curl($url, http_build_query($_p));
        $arr  = json_decode($text, true);
        if (is_array($arr)) {
            if ($arr['code'] = 1000) {
                $data        = isset($arr['data'][0]) ? $arr['data'][0] : $arr['data'];
                $order_state = isset($this->status_arr[$data['ostatus']]) ? $this->status_arr[$data['ostatus']] : "状态未知";
                if ($order_state == "") {
                    $order_state = "状态未知";
                }
                $result['code'] = 0;
                $result['msg']  = '查询接口订单成功：订单状态【' . $order_state . '】；状态码【' . $data["ostatus"] . '】';
                $result['data'] = array(
                    "num"         => $data["ocount"],
                    "start_num"   => $data["startCount"],
                    "now_num"     => $data["nowCount"],
                    "order_state" => $order_state,
                    "orderid"     => $row['djorder'],
                    'add_time'    => $row['addtime'],
                );

                //同步订单
                if ($row['status'] == 2) {
                    $tool = $DB->get_row("SELECT * FROM cmy_tools WHERE tid=:tid LIMIT 1", [':tid' => $row["tid"]]);
                    if ($data["ostatus"] == 3 && is_array($tool) && $tool['goods_type'] == 1) {
                        //卡密商品
                        $kmArr   = $data['cards'];
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
                            if ($data['recurl']) {
                                $kmdata .= "<br/>\r\n使用地址: " . $data['recurl'];
                            }
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
                        if (in_array($data["ostatus"], ['4', '5', '8', '9'])) {
                            $sql_data = array(
                                ':status' => 3,
                                ':result' => '订单状态异常【' . $order_state . '】，请联系网站客服处理',
                                ':id'     => $row['id'],
                            );
                        } elseif ($data["ostatus"] == '3') {
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
            } else {
                $result['msg'] = '查询接口订单失败：' . $arr['msg'];
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
            return ["code" => -1, "msg" => '接口密钥不能为空！'];
        }

        $url = $this->url . "api/getDirs.htm";
        $_p  = array(
            'userNo' => $this->userNo,
            'sign'   => md5(strtolower($this->token . $this->userNo)),
        );
        $ret = get_curl($url, http_build_query($_p));
        $arr = json_decode($ret, true);
        if (is_array($arr)) {
            if ($arr['code'] == 1000) {
                $data = isset($arr['data2']) ? $arr['data2'] : $arr['data1'];
                array_unshift($data, [
                    'id'   => 0,
                    'name' => '全部商品',
                    'data' => [
                        [
                            'id'   => 0,
                            'name' => '全部商品',
                        ],
                    ],
                ]);
                return ["code" => 0, "data" => $data];
            } else {
                return ["code" => -1, "msg" => '该网站返回：' . $arr['msg'], "ret" => $ret];
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
        $success  = 0;
        $warnlist = '';

        $breakMaxPrice = $conf['cron_maxprice'] ? $conf['cron_maxprice'] : '8888';
        $crontime      = isset($shequ['crontime']) && $shequ['crontime'] >= 30 ? $shequ['crontime'] : 60;
        $uptime        = time() - $crontime;
        $rs            = [];
        if ($cids != '') {
            $rs = $DB->select("SELECT * FROM cmy_tools WHERE shequ='{$shequ['id']}' AND `uptime`<='{$uptime}' AND is_curl=2 and cid IN ({$cids})");
        }
        $max_num    = 500;
        $max_time   = 30;
        $start_time = time();
        $num        = 0;
        foreach ($rs as $key => $res2) {
            if (empty($res2['goods_id'])) {
                continue;
            }

            if ($num >= $max_num) {
                break;
            }

            if ($res2['price'] >= $breakMaxPrice) {
                continue;
            }

            if (time() - $start_time >= $max_time) {
                break;
            }

            $goods_form = json_decode($res2['goods_form'], true);
            $result     = $this->getGoodsParams([], $res2['goods_id'], isset($goods_form['keyId']) ? $goods_form['keyId'] : '');
            if ($result && $result['code'] == 0) {
                if ($res2['value'] < 1) {
                    $res2['value'] = 1;
                }

                $goodsInfo = $result['data'];
                $price1    = $goodsInfo['price'] * $res2['value'];
                $desc      = $goodsInfo['desc'];
                $shopimg   = isset($goodsInfo['shopimg']) ? $goodsInfo['shopimg'] : null;
                $min       = $goodsInfo['min'];
                $min       = ceil($min / $res2['value']);
                $max       = $goodsInfo['max'];
                $max       = floor($max / $res2['value']);
                $stock     = intval($goodsInfo['stock']);

                $this->setToolPrice($price1, $res2);

                if ($conf['cron_status_sync'] != 1) {
                    $active = $goodsInfo['active'];
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

                if ($min > 0 && $min > $res2['min']) {
                    $sql .= ",`max`='" . intval($min) . "'";
                }

                if ($max > 0 && $max < $res2['max']) {
                    $sql .= ",`max`='" . intval($max) . "'";
                }

                $sql = "UPDATE `pre_tools` SET {$sql} where tid='" . $res2['tid'] . "'";
                addWebLog('价格同步日志', '系统：' . getShequTypeName($shequ['type']) . '；执行语句：' . $sql, 'Cron');
                $DB->query($sql);
                $success++;
            } else {
                if (preg_match('/不存在|已下架|删除|失效/', $result['msg']) == 1) {
                    // 自动下架
                    $this->setToolActive(0, $res2['tid']);
                    $warnlist = "商品[" . $res2['tid'] . "]商品已失效，已自动下架";
                } else {
                    $warnlist = "商品[" . $res2['tid'] . "]价格更新失败，" . $result['msg'];
                }
            }
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
            return ["code" => -1, "msg" => '接口密钥不能为空！'];
        }

        $url = $this->url . "api/getGood.htm";
        $_p  = array(
            'userNo' => $this->userNo,
            'type'   => null,
            'sign'   => md5($this->token . $this->userNo),
        );
        $ret = get_curl($url, http_build_query($_p));
        $arr = json_decode($ret, true);
        if (is_array($arr)) {
            if (array_key_exists('code', $arr)) {
                if ($arr['code'] == 1000) {
                    return ["code" => 0, "data" => $arr['data']];
                } else if ($arr['code'] == 1010) {
                    return ["code" => -1, "msg" => '该目录下无商品数据！'];
                } else {
                    return ["code" => -1, "msg" => $arr['msg'], "result" => $arr];
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
    public function getGoodsList($config = [], $cate_id = 0, $page = 1)
    {
        if (empty($this->token)) {
            return ["code" => -1, "msg" => '接口密钥不能为空！'];
        }
        $url = $this->url . "api/getGoods.htm";
        if ($cate_id > 0) {
            $_p = array(
                'userNo' => $this->userNo,
                'type'   => 1,
                'page'   => $page > 0 ? $page : 1,
                'word'   => $cate_id,
                'sign'   => md5(strtolower($this->token . $this->userNo . '1')),
            );
        } else {
            $_p = array(
                'userNo' => $this->userNo,
                'type'   => null,
                'page'   => $page > 0 ? $page : 1,
                'sign'   => md5(strtolower($this->token . $this->userNo)),
            );
        }
        $ret = get_curl($url, http_build_query($_p));
        $arr = json_decode($ret, true);
        if (is_array($arr)) {
            if (array_key_exists('code', $arr)) {
                if ($arr['code'] == 1000) {
                    foreach ($arr['data'] as $key => &$value) {
                        $value['price'] = $value['money'];
                        $value['type']  = $value['type'] == 3 ? '0' : '1';
                    }
                    return ["code" => 0, "data" => $arr['data'], "page" => isset($arr['allPage']) && $arr['allPage'] > $page ? true : false, 'allPage' => $arr['allPage']];
                } else {
                    return ["code" => -1, "msg" => $arr['msg'], "result" => $arr];
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
    public function getGoodsParams($config = [], $goodsid, $keyId = null)
    {
        if (empty($this->token)) {
            return ["code" => -1, "msg" => '接口密钥不能为空！'];
        } elseif ($goodsid == 0) {
            return ["code" => -1, "msg" => '商品ID不能为空！'];
        }

        $url = $this->url . "api/getGood.htm";
        $_p  = array(
            'userNo' => $this->userNo,
            'id'     => $goodsid,
            'keyId'  => $keyId,
            'sign'   => md5(strtolower($this->token . $this->userNo . $goodsid)),
        );
        $result = get_curl($url, http_build_query($_p));
        $json   = json_decode($result, true);
        if (is_array($json)) {
            if ($json['code'] == 1000) {
                $info = $json['data'][0];
                if ($info['type'] == 1) {
                    $active = $info['count'] > 0 ? 1 : 0;
                } else {
                    $active = 1;
                }

                // exit(json_encode($json));
                $data = array(
                    'id'        => $info['id'],
                    'name'      => $info['name'],
                    'mainKey'   => $info['mainKey'],
                    'goodstype' => $info['type'] == 1 ? 1 : 0,
                    'price'     => $info['money'],
                    'minnum'    => intval($info['min']),
                    'maxnum'    => intval($info['max']),
                    'active'    => $active,
                    'stock'     => $info['type'] == 1 ? $info['count'] : null,
                    'desc'      => $info['desc'],
                    'alert'     => $info['describe'] ? $info['describe'] : $info['note'],
                    'inputs'    => '',
                    'input'     => $info['accountName'],
                    'param'     => '',
                    'shopimg'   => count($info['imgs']) > 0 ? $info['imgs'][0]['img'] : $info['img'],
                );
                if (isset($info['templates']) && count($info['templates']) > 0) {
                    foreach ($info['templates'] as $key => $value) {
                        if ($value['type'] == 1 || $value['type'] == 2) {
                            $data['inputs'] .= ($data['inputs'] ? '|' : '') . $value['name'];
                        } else {
                            $select = '{' . trim($data['content'], ',') . '}';
                            $data['inputs'] .= ($data['inputs'] ? '|' : '') . $value['name'] . $select;
                        }
                        $data['param'] .= $data['param'] ? '|' . $value['name'] : $data['name'];
                    }
                }
                return ["code" => 0, "data" => $data];
            } else {
                return ["code" => -1, "msg" => $json['msg'], "result" => $json];
            }
        } else {
            return ["code" => -1, "msg" => '打开对接网站' . $this->url . '失败'];
        }
    }
}
