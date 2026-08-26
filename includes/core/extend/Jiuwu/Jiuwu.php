<?php
use \core\Card;

class Jiuwu extends Card
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

    public function doOrder($row, $config = [], $tool = [], $num)
    {

        global $DB, $date;
        $goods_id    = $tool['goods_id'];
        $goods_type  = $tool['goods_type'];
        $orderid     = $row['id'];
        $input       = $this->parseInputData($row);
        $data        = [];
        $goods_param = explode("|", $tool["goods_param"]);
        $i           = 0;
        foreach ($input as $val) {
            if ($val != "") {
                $data[$goods_param[$i]] = $val;
                $i                      = $i + 1;
            }
        }

        $password = $config['password'];
        if (empty($password)) {
            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；', "对接失败，密码解析失败，请在社区列表重新编辑一下密码相关资料再试", 0, $orderid);
            return "密码解析失败，请在社区列表重新编辑一下密码相关资料再试";
        } elseif ($config["username"] == $password) {
            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . http_build_query($data), "订单ID为" . $orderid . "的订单对接失败！账号和密码必须不相同，请修改登录密码再试", 0, $orderid);
            return "账号和密码必须不相同，请修改登录密码再试";
        }

        $tool = $DB->get_row("SELECT * FROM cmy_tools where tid=:tid limit 1", [':tid' => $row['tid']]); //获取商品信息

        $url = $config['url'] . "index.php?m=home&c=order&a=add";

        $params = "goods_id=" . $goods_id . "&goods_type=" . $goods_type . "&need_num_0=" . $num . "&pay_type=1";
        if (is_array($data) && $data) {
            foreach ($data as $key => $val) {
                $params .= "&" . $key . "=" . urlencode($val);
            }
        }

        $post = $params . "&Api_UserName=" . urlencode($config["username"]) . "&Api_UserMd5Pass=" . MD5($password);

        $result = shequ_get_curl($url, $post, 0, 0, 0, 0, 0, $config['proxy'], 120);
        $arr    = json_decode($result, true);

        if (is_array($arr)) {
            if ($arr['status'] == 1 || $arr['order_id'] > 0) {
                $message = "下单成功，订单号" . $arr['order_id'] . "!";
                $status  = 2;
                if ($config['orderstatus']) {
                    $status = $config['orderstatus'];
                }

                $djresult = '';

                if ($tool['result']) {
                    $djresult = $tool['result'];
                }

                $DB->query(
                    "UPDATE `pre_orders` SET result= ?,djorder= ?,endtime= ?,status= ?,djzt='1' where id= ?",
                    [$djresult, $arr['order_id'], $date, $status, $row['id']]
                );
                if ($orderid > 0) {
                    log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $message, 1, $orderid);
                }
            } else {
                $message = "下单失败，" . $arr["info"];
                if ($orderid > 0) {
                    $DB->query("UPDATE `pre_orders` set status='0',djzt='2' where id=:id", [':id' => $orderid]);
                    log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $message, 0, $orderid);
                }

            }
            return $message;
        }

        $DB->query("UPDATE `pre_orders` set status='0', djzt='2' where id=:id", [':id' => $row['id']]);

        if (preg_match("/<p\\sclass=\"error\">(.*?)<\\/p>/", $result, $msg)) {
            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, '下单失败，' . $msg[1], 0, $orderid);
            return $msg[1];
        }

        $result = str_replace(array("\r\n", "\r", "\n"), "", $result);
        if ($orderid > 0) {
            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, "下单失败，" . htmlspecialchars($result), 0, $orderid);
        }
        return htmlspecialchars($result);
    }

    /**
     * 查询订单
     *
     * @param array $row 订单信息
     * @param array $config 配置信息
     * @return void
     */
    public function query($row = [], $config = [])
    {
        global $DB;
        if (empty($row['djorder'])) {
            return array('code' => -1, 'msg' => '缺少对接订单号！');
        }

        $tool     = $DB->get_row("SELECT * from cmy_tools where tid='" . $row['tid'] . "' limit 1");
        $password = $config['password'];
        $param    = "Api_UserName=" . $config['username'] . "&Api_UserMd5Pass=" . md5($password) . "&return_fields=need_num_0,start_num,now_num,order_state,add_time,goods_id,goods_type";
        $param .= "&orders_id=" . $row['djorder'];
        $curl   = $config['url'] . "index.php?m=Home&c=Order&a=query_orders_detail";
        $result = get_curl($curl, $param);
        $data   = json_decode($result, true);

        if (is_array($data)) {
            if ($data['status'] == 1 || $data['status'] == true) {
                $status_arr = array(
                    '0' => '未开始',
                    '1' => '未开始',
                    '2' => '进行中',
                    '3' => '已完成',
                    '4' => '已退单',
                    '5' => '退单中',
                    '6' => '续费中',
                    '7' => '补单中',
                    '8' => '改密中',
                    '9' => '登录失败',
                );
                $json        = $data['rows'][0];
                $ret['code'] = 0;
                $order_state = $status_arr[$json['order_state']];
                if (empty($order_state)) {
                    $order_state = '未知状态[' . $json['order_state'] . ']！';
                }
                $ret['data']['orderid']     = $row['djorder'];
                $ret['data']['num']         = $json['need_num_0'];
                $ret['data']['add_time']    = $json['add_time'];
                $ret['data']['start_num']   = $json['start_num'];
                $ret['data']['now_num']     = $json['now_num'];
                $ret['data']['siteurl']     = $config['url'];
                $ret['data']['order_state'] = $order_state;
                $ret['data']['shopUrl']     = $config['url'] . 'index.php?m=home&c=goods&a=detail&id=' . $tool['goods_id'] . '&goods_type=' . $tool['goods_type'];

                $ret['msg'] = "查询成功，订单状态【" . $ret['data']['order_state'] . "】；状态码【" . $json['order_state'] . "】";
                if ($row['status'] == 2) {
                    //同步订单状态
                    if (in_array($json['order_state'], ['4', '5', '9'])) {
                        $sql_data = array(
                            ':status' => 3,
                            ':id'     => $row['id'],
                        );
                    } elseif ($json['order_state'] == 3) {
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

            } else {
                $ret['code']   = -1;
                $ret['msg']    = $data['info'] . "对接订单号" . $row['djorder'];
                $ret['result'] = $result;
            }

            return $ret;
        } else {
            $data = str_replace(array("\r\n", "\r", "\n"), "", $data);

            $ret = array('code' => -1, "msg" => "查询" . $row['id'] . "的九五订单详情失败，请稍后重试！<br>" . htmlspecialchars($data), "result" => $result);
            return $ret;
        }
    }

    /**
     * 获取商品列表
     *
     * @return array
     */
    public function getGoodsList($config = [])
    {

        $password = $config['password'];
        if ($password != "" && $password === $config['username']) {
            $result = [
                'code' => -1,
                "msg"  => "获取商品失败，为保证安全，账号和密码不能为空！",
                "data" => null,
                "type" => getShequType($config['type']),
            ];
            return $result;
        }
        $post = 'Api_UserName=' . urlencode($config['username']) . '&Api_UserMd5Pass=' . md5($password);
        $url  = $config['url'] . 'index.php?m=home&c=api&a=user_get_goods_lists_details&' . $post;
        $data = get_curl($url, $post);
        $arr  = json_decode($data, true);
        if (is_array($arr)) {
            if ($arr['status'] == 1) {
                $result = array();
                foreach ($arr['user_goods_lists_details'] as $v) {
                    $result[] = array(
                        "name"    => $v['title'],
                        "close"   => $v['goods_status'],
                        "unit"    => $v['unit'],
                        "id"      => $v['id'],
                        "type"    => $v['goods_type'],
                        "price"   => $v['user_unitprice'],
                        "shopimg" => '//' . getJiuwuImgHost($config['url']) . $v['thumb'],
                        "minnum"  => $v['minbuynum_0'],
                        "maxnum"  => $v['maxbuynum_0'],
                    );
                }
                $ret = array('code' => 0, "msg" => 'succ', "data" => $result);
            } else {
                $msg = isset($arr['msg']) && $arr['msg'] ? $arr['msg'] : $arr['info'];
                $ret = array(
                    'code' => -1,
                    "msg"  => '获取商品列表失败，' . $msg,
                    "data" => [],
                    "user" => [
                        'username'        => $config['username'],
                        'Api_UserMd5Pass' => md5($password),
                    ],
                );
            }
        } elseif (stripos($data, "账号") !== false || stripos($data, "密码") !== false) {
            $ret = array('code' => -1, "msg" => "账号或密码错误，请确认后再试！", "data" => $data);
        } else {
            $data = str_replace(array("\r\n", "\r", "\n"), "", $data);
            $ret  = array('code' => -1, "msg" => "获取玖伍商品列表失败，请稍后重试！<br>" . htmlspecialchars($data), "data" => $data);
        }
        return $ret;
    }

    private function login($url, $user, $pass)
    {
        $post = array(
            'username'          => $user,
            'user_remember'     => 1,
            'username_password' => $pass,
        );
        $get  = get_curl($url . "index.php?m=Home&c=User&a=login", http_build_query($post), 0, 0, 1);
        $data = json_decode('{' . getSubstr($get, "{", "}") . '}', true);
        if (stripos($get, "登录成功") !== false || stripos($get, "/index.php?m=Home") !== false || $data['status'] == 1) {
            if (preg_match_all('/Set-Cookie:\s?([A-Za-z0-9\_=\|]+);/is', $get, $arr2)) {
                $cookie = null;
                foreach ($arr2['1'] as $item) {
                    $cookie .= $item . ';';
                }
                $_SESSION['jiuwu_cookie']      = base64_encode($cookie);
                $_SESSION['jiuwu_cookie_time'] = time();
                $arr                           = array("code" => 0, "cookie" => $cookie, "msg" => $data['info']);
                return $arr;
            }
        } elseif (is_array($data) || is_object($data)) {
            return array("code" => -1, "msg" => $data['info'], "result" => $get);
        }
        return array("code" => -1, "msg" => "登录社区失败！", "result" => $get);
    }

    /**
     * 获取商品参数
     *
     * @return array
     */
    public function getGoodsParams($config, $goods_id)
    {
        $result['code'] = -1;
        if ($_SESSION['jiuwu_cookie'] && time() - $_SESSION['jiuwu_cookie_time'] < 30) {
            $cookie = base64_decode($_SESSION['jiuwu_cookie']);
        } else {
            $loginData = $this->login($config['url'], $config['username'], $config['password']);
            if ($loginData['code'] != 0) {
                $loginData['user'] = $config['username'];
                $loginData['pass'] = $config['password'];
                return $loginData;
            }
            $cookie = $loginData['cookie'];
        }

        $get = get_curl($config['url'] . "index.php?m=Home&c=Goods&a=detail&id=" . $goods_id, 0, 0, $cookie);
        @writeLogs("打开社区" . $config['url'] . "商品页面，返回文本：\n" . $get); //调试日志
        //$start = stripos($get, 'action="/index.php?m=home&c=order');
        $end = stripos($get, '确定下单');
        if ($end > 1) {
            $desc = getSubstr($get, '</header>', '<!--内容-->');
            $desc = preg_replace('/\n|\t|\s\s/', '', $desc);
            $desc = getSubstr($desc, '<div class="container"><div class="row"><div class="col-md-12 banner">', '</div></div></div>');
            //$get = getSubstr($get,'action="/index.php?m=home&c=order','支付方式');
            $string = getSubstr($get, '下单区', '拆分卡密');
            if ($string == "") {
                if (strpos($get, '下单区') < 0) {
                    $string = getSubstr($get, 'a=add&id=' . $goods_id, '卡密充值');
                } else {
                    $string = getSubstr($get, '下单区', '卡密充值');
                }
            }

            $string = str_replace(array("\r\n", "\n", "\r", "\t"), '', $string);
            $string = preg_replace(array('/id="([a-zA-Z0-9\_\-]+)"\s/', '/id="([a-zA-Z0-9\_\-]+)"\t/'), '', $string);
            $arr    = [];
            if (preg_match_all('/>([\w\-\x{4e00}-\x{9fa5}]+)：(<\/span>|<\/span>\s+|<\/span>\s|<\/span>\t|<\/span>\t)<(input|textarea|select)(\s+|\s)(name="([\w\-]+)|class="[\w\-]+" name="([\w\-]+)")/isu', $string, $arr)) {
                $inputs = $arr[1];
                $params = [];
                foreach ($arr[5] as $index => $value) {
                    if (preg_match('/name="([\w\-]+)/isu', $value, $match)) {
                        $params[] = array(
                            'param' => $match[1],
                            'type'  => $arr[3][$index],
                        );
                    }
                    unset($match);
                }
            }

            $inputParams = [];

            foreach ($params as $key => $item) {
                if ($value == 'need_num_0') {
                    continue;
                }

                if ($key > count($inputs) - 1) {
                    continue;
                }

                $inputParams[$key] = array(
                    'name'  => $inputs[$key],
                    'param' => $item['param'],
                    'type'  => $item['type'],
                );
            }

            if (count($inputParams) > 0) {
                $param = "";
                $input = "";
                foreach ($inputParams as $i => $row) {
                    $p = $row['param'];
                    $n = $row['name'];
                    $t = $row['type'];
                    if ($p == 'need_num_0') {
                        continue;
                    } elseif (stripos($p, '_url') !== false) {
                        continue;
                    } elseif (in_array($p, ['goods_id', 'goods_type', 'order_number', 'cardnum'])) {
                        continue;
                    } elseif (in_array($p, ['allnum', 'password', 'user_note', 'ssnr'])) {
                        continue;
                    } elseif (in_array($p, ['kmcz_cardno', 'pay_type', 'usenum'])) {
                        continue;
                    } else {
                        $param .= $p . "|";
                        if ($t == 'select') {
                            //下拉框
                            $select_str = getSubstr($string, 'name="' . $p . '"', '</select>');
                            if (preg_match_all('/<option value=".*?">(.*?)<\/option>/m', $select_str, $select_match)) {
                                $select = $n . '{';
                                foreach ($select_match[1] as $key => $name) {
                                    if (stripos($name, '请选择') === false) {
                                        $select .= trim($name) . ',';
                                    }
                                }
                                $select = rtrim($select, ',');
                                $input .= $select . "}|";
                            } else {
                                $input .= $n . "|";
                            }
                        } else {
                            $input .= $n . "|";
                        }
                    }
                }
                $param = trim($param, '|');
                $input = trim($input, '|');

                $result = array(
                    'code'        => 0,
                    'msg'         => '获取对接参数成功',
                    'param'       => $param,
                    "url"         => $config['url'],
                    "desc"        => $desc,
                    "result"      => $string,
                    "params_data" => $arr,
                    "inputs"      => $input,
                    "inputParams" => $inputParams,
                );
            } else {
                $result['msg']          = '获取商品对接参数失败，多次失败请查看社区对接日志截图给作者';
                $result['url']          = $config['url'];
                $result['params_data']  = ['inputs' => $arr[1], 'params' => $arr[4]];
                $result['params_data2'] = ['inputs' => $arr[1], 'params' => $arr[5]];
                $result['params']       = $params;
                $result['desc']         = $desc;
                $result['inputParams']  = $inputParams;
                $result['result']       = $string;
                log_result("获取九五参数", "1", 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Cooike：' . $cookie . '；Result：' . htmlspecialchars($get), '匹配结果1：' . json_encode($arr), 0, null);
            }

            return $result;
        } else {
            $result['msg']    = '获取商品对接参数失败，频率过快，请稍后再试';
            $result['cookie'] = $cookie;
            return $result;
        }
    }
}
