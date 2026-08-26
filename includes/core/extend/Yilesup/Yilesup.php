<?php

use \core\Card;

class Yilesup extends Card
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
        0 => '已下单',
        1 => '已付款',
        2 => '待处理',
        3 => '处理中',
        4 => '补单中',
        5 => '退单中',
        6 => '已完成',
        7 => '已退单',
        8 => '已退单',
        9 => '异常',
    ];

    /**
     * 构造方法初始化
     * @param array   $config        配置信息
     * @param integer $_ssl          是否https
     */
    public function __construct($config = [], $_ssl = 0)
    {
        $this->config = $config;

        if (!isset($this->config['username']) || !$this->config['username']) {
            throw new \Exception("账号不能为空！", 1);
        }

        if (!isset($this->config['password']) || !$this->config['password']) {
            throw new \Exception("密码不能为空！", 1);
        }

        if (!isset($this->config['url']) || !$this->config['url']) {
            throw new \Exception("接口地址不能为空！", 1);
        }

        $this->url = $this->config['url'];

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

    /**
     * 获取返回信息文本
     *
     * @return string
     */
    public function getMsg($data = [])
    {
        if (isset($data['message'])) {
            return $data['message'];
        } elseif (isset($data['msg'])) {
            return $data['msg'];
        } elseif (isset($data['info'])) {
            return $data['info'];
        } else {
            return (string) $data;
        }
    }

    public function doOrder($row, $config = [], $tool = [], $num = 1)
    {
        global $DB, $date;

        if ($row['status'] == -1) {
            $DB->query("UPDATE `pre_orders` set status='0', djzt='2' where id=:id", [':id' => $row['id']]);
        } elseif ($row['status'] > 0) {
            return '该订单已对接处理！';
        }

        if (!$tool) {
            $tool = $DB->get_row("SELECT * FROM cmy_tools where tid=:tid limit 1", [":tid" => $row["tid"]]);
            if (!$tool) {
                return '该商品信息不存在，无法获取对接信息！';
            }
        }

        $goods_id    = $tool['goods_id'];
        $goods_param = $tool['goods_param'];
        $input       = $this->parseInputData($row);

        $result['code'] = -1;
        $param          = explode('|', $goods_param);
        $path           = $this->url . 'openapi/customer/Goods/Buy';

        $params['goods_id'] = intval($goods_id);

        $value = $tool['value'] > 0 ? $tool['value'] : 1;
        $num   = $row['value'] > 0 ? $row['value'] * $value : $value;

        $params['buy_number'] = $num;

        //3
        for ($i = 0; $i < count($param); $i++) {
            $params['buy_params'][$param[$i]] = $input[$i];
        }

        $post = json_encode($params);

        $data = $this->get_curl($path, $post);
        $arr  = json_decode($data, true);
        if (is_array($arr)) {
            if ($arr['code'] == 0) {
                $sql    = "UPDATE `pre_orders` SET `endtime`=:endtime,`djorder`=:djorder,`bz`=:bz,`djzt`=1,`status`=:status where `id`=:id";
                $status = 1;
                if (isset($config['orderstatus'])) {
                    $status = $config['orderstatus'];
                }

                $message  = '下单成功，订单号' . $arr['data']['id'];
                $sql_data = array(
                    ':endtime' => $date,
                    ':djorder' => $arr['data']['id'],
                    ':bz'      => $message,
                    ':status'  => $status,
                    ':id'      => $row['id'],
                );
                $DB->query($sql, $sql_data);
                if ($tool['goods_type'] == 1) {
                    // 查询卡密订单
                    $row['djorder'] = $arr['data']['id'];
                    $this->query($row, $config);
                }
            } else {
                $message = '下单失败，对接站返回：' . $this->getMsg($arr);
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
        }
        $orderid = $row['id'];
        $djorder = $row['djorder'];
        $path    = $this->url . 'openapi/customer/Order/Show';

        $post = '{"ids":[' . $djorder . ']}';

        $text = $this->get_curl($path, $post);
        $arr  = json_decode($text, true);
        if (is_array($arr)) {
            if ($arr['code'] == 0) {
                $data = $arr['data'][0];
                if (!is_array($data)) {
                    return ["code" => -1, "msg" => '查询失败，返回结果不正确 => ' . json_encode($arr)];
                }

                $order_state = $this->status_arr[$data['status']];
                if ($order_state == "") {
                    $order_state = "状态未知[" . $data['status'] . "]";
                }
                $result['code'] = 0;
                $result['msg']  = '查询接口订单成功：订单状态【' . $order_state . '】；状态码【' . $data["status"] . '】';
                $result['data'] = array(
                    "num"         => $data["buy_number"],
                    "start_num"   => $data["start_num"] > 0 ? $data["start_num"] : 0,
                    "now_num"     => $data["current_num"] > 0 ? $data["current_num"] : 0,
                    "order_state" => $order_state,
                    "orderid"     => $row['djorder'],
                    'add_time'    => date("Y-m-d H:i", $data['create_time']),
                );

                //同步订单
                if ($row['status'] == 2) {
                    $tool = $DB->get_row("SELECT * FROM cmy_tools WHERE tid=:tid LIMIT 1", [':tid' => $row["tid"]]);
                    if ($data["status"] == 6 && is_array($tool) && !empty($data['card_code_ids'])) {
                        //卡密商品
                        $cardObj = new \core\Card();
                        $ret     = $cardObj->getCardData($row, $config, explode(",\n", $data['card_code_ids']));
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
                            log_result(getShequTypeName($config["type"]) . "对接", $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $post, '卡密识别失败，' . $ret['msg'], 1, $orderid);
                        }
                        $DB->query($sql, $sql_data);
                    } else {
                        if (in_array($data["status"], [5, 7, 8, 9])) {
                            $sql_data = array(
                                ':status' => 3,
                                ':result' => '订单状态异常【' . $order_state . '】，请联系网站客服处理',
                                ':id'     => $row['id'],
                            );
                        } elseif ($data["status"] == 6) {
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
                $result['msg'] = '查询接口订单失败：' . $this->getMsg($arr);
            }
        } else {
            $result['msg'] = '查询订单详情失败，' . $text;
        }
        return $result;
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

            $result = $this->getGoodsParams([], $res2['goods_id']);
            if ($result && $result['code'] == 0) {
                if ($res2['value'] < 1) {
                    $res2['value'] = 1;
                }

                $goodsInfo = $result['data'];
                $price1    = $goodsInfo['price'] * $res2['value'];
                $desc      = $goodsInfo['desc'];
                $shopimg   = isset($goodsInfo['shopimg']) ? $goodsInfo['shopimg'] : null;
                $min       = $goodsInfo['minnum'];
                $min       = ceil($min / $res2['value']);
                $max       = $goodsInfo['maxnum'];
                $max       = floor($max / $res2['value']);
                $stock     = intval($goodsInfo['stock']);

                $this->setToolPrice($price1, $res2);

                if ($conf['cron_status_sync'] != 1) {
                    $this->setToolActive($goodsInfo['active'], $res2['tid']);
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
                if (preg_match('/不存在|已下架|删除|失效/', $result['message']) == 1) {
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
        return $this->getGoodsList();
    }

    /**
     * 获取分类列表
     *
     * @return array
     */
    public function getCateList($config = [])
    {
        $url = $this->url . 'openapi/customer/Goods/CategoryList';
        $ret = $this->get_curl($url, '{}');
        $arr = json_decode($ret, true);
        if (is_array($arr)) {
            if (array_key_exists('code', $arr)) {
                if ($arr['code'] == 0) {
                    $list = array();
                    foreach ($arr['data'] as $v) {
                        $children = [];
                        if (isset($v['parent_infos']) && is_array($v['parent_infos'])) {
                            foreach ($v['parent_infos'] as $key2 => $value) {
                                $list[] = array(
                                    'id'       => $value['id'],
                                    'shopimg'  => $value['icon'],
                                    'name'     => $value['name'],
                                    'children' => [],
                                    'sort'     => $value['weight'],
                                );
                            }
                        }

                        $list[] = array(
                            'id'       => $v['id'],
                            'name'     => $v['name'],
                            'shopimg'  => $value['icon'],
                            'children' => $children,
                            'sort'     => $v['weight'],
                        );
                    }

                    return [
                        "code" => 0,
                        "data" => $list,
                    ];
                } else {
                    return ["code" => -1, "msg" => $this->getMsg($arr), "result" => $arr];
                }
            } else {
                return ["code" => -1, "msg" => '打开数据解析失败，请检查原始数据', "arr" => $arr];
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

        $url = $this->url . 'openapi/customer/Goods/List';
        $ret = $this->get_curl($url, '{}');
        $arr = json_decode($ret, true);
        if (is_array($arr)) {
            if (array_key_exists('code', $arr)) {
                if ($arr['code'] == 0) {
                    $list = array();
                    foreach ($arr['data'] as $v) {
                        $list[] = array(
                            'id'            => $v['id'],
                            'name'          => $v['name'],
                            'price'         => $v['price'],
                            'minnum'        => $v['buy_min_limit'],
                            'maxnum'        => $v['buy_max_limit'],
                            'category_name' => $v['goods_category_name'],
                            'category_id'   => $v['goods_category_id'] ?? 0,
                            'active'        => $v['status'] == 4 ? 1 : 0,
                        );
                    }
                    return [
                        "code"    => 0,
                        "data"    => $list,
                        "page"    => false,
                        'allPage' => 1,
                    ];
                } else {
                    return ["code" => -1, "msg" => $this->getMsg($arr), "result" => $arr];
                }
            } else {
                return ["code" => -1, "msg" => '打开数据解析失败，请检查原始数据', "arr" => $arr];
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
        $goodsSN = $goodsid > 1 ? $goodsid : trim($_POST['goods_param']);

        $url    = $this->url . 'openapi/customer/Goods/Show';
        $post   = '{"goods_id":' . $goodsSN . '}';
        $result = $this->get_curl($url, $post);
        $json   = json_decode($result, true);
        if (is_array($json)) {
            if ($json['code'] == 0) {

                if (isset($json['data']['is_close'])) {
                    //检查上下架状态
                    $active = $json['data']['is_close'] == 1 ? 0 : 1;
                } else {
                    //检查上下架状态
                    if (isset($json['data']['Status'])) {
                        $active = $json['data']['Status'] == 4 ? 1 : 0;
                    } else {
                        $active = $json['data']['status'] == 4 ? 1 : 0;
                    }
                }

                $active == 0 && $json['data']['name'] = '[已下架]' . $json['data']['name'];

                $return = [
                    'id'      => $json['data']['id'],
                    'name'    => $json['data']['name'],
                    'unit'    => $json['data']['unit'] ?? '',
                    'desc'    => $json['data']['particulars'],
                    'minnum'  => $json['data']['buy_min_limit'],
                    'maxnum'  => $json['data']['buy_max_limit'],
                    'price'   => $json['data']['price'] ?? $json['data']['Price'],
                    'shopimg' => isset($json['data']['ImageUrls']) && is_array($json['data']['ImageUrls']) ? $json['data']['ImageUrls'][0] : '',
                    'active'  => $active,
                    'stock'   => $json['data']['stock'] == -1 ? null : $json['data']['stock'],
                ];

                $buy_params      = isset($json['data']['BuyParams']) ? $json['data']['BuyParams'] : $json['data']['buy_params'];
                $return['input'] = str_replace('：', '', $buy_params[0]['name']);
                $inputs          = '';
                $alias           = '';
                foreach ($buy_params as $row) {
                    $alias .= $row['key'] . '|';
                    if (str_replace('：', '', $row['name']) == $return['input']) {
                        continue;
                    }
                    $inputs .= str_replace('：', '', $row['name']) . '|';
                }
                $return['inputs'] = trim($inputs, '|');
                $return['params'] = trim($alias, '|');
                $return['arr']    = $json['data'];
                return ["code" => 0, "data" => $return];
            } else {
                return ["code" => -1, "msg" => $json['msg'], "result" => $json];
            }

        } else {
            return ["code" => -1, "msg" => '打开对接网站' . $this->url . '失败'];
        }
    }

    private function get_curl($url = '', $post = 0)
    {
        $time   = time();
        $path   = '/' . str_replace(rtrim($this->url, '/') . '/', '', $url);
        $token  = sha1($this->config['username'] . $this->config['password'] . $path . $time);
        $header = [
            'AppId: ' . $this->config['username'],
            'AppToken: ' . $token,
            'AppTimestamp: ' . $time,
        ];

        if ($post) {
            $header[] = 'Content-Type: application/json; charset=UTF-8';
        }

        // var_dump([$time, $path, $token, $header]);
        // die;
        $daili = [];
        $str   = $this->config['paypwd'];
        if (!empty($str)) {
            $daili = explode(',', $str);
        }
        return self::shequ_get_curl($url, $post, $daili, $header);
    }

    private static function shequ_get_curl($url, $post, $daili, $header)
    {

        $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.41 Safari/537.36 Edg/101.0.1210.32';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // 获取头部信息
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        // 返回原生的（Raw）输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        //设置请求头
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //判断post 设置超时时间
        $time = 18;
        if ($post !== 0) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            $time = 59;
        }
        //检查并配置代理
        if (!empty($daili)) {
            if ($daili[0] == 's5') {
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            } elseif ($daili[0] == 's4') {
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
            }
            curl_setopt($ch, CURLOPT_PROXY, $daili[1]);
            curl_setopt($ch, CURLOPT_PROXYPORT, $daili[2]);
        }

        // //强制协议为1.0
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        // //强制使用IPV4协议解析域名  新加新加
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        curl_setopt($ch, CURLOPT_TIMEOUT, $time);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        // 执行并获取返回结果
        $content = curl_exec($ch);
        // 关闭CURL
        curl_close($ch);
        // 解析HTTP数据流
        return $content;
    }
}
