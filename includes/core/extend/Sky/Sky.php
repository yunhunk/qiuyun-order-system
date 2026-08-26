<?php
use \core\Card;

class Sky extends Card
{
    /**
     * 配置信息
     *
     * @var array
     */
    private $config = [];

    private $proxy_url = '';
    private $api_hz    = ''; //API后缀
    private $user      = '';
    private $pass      = '';
    private $url       = '';
    private $isSsl     = 0;

    /**
     * 构造方法初始化
     * @param array   $config        配置信息
     * @param integer $_ssl          是否https
     */
    public function __construct($config = [], $_ssl = 0)
    {
        $this->config = $config;
        $_url         = $config['url'];
        $this->user   = $config['username'];
        $this->pass   = $config['password'];
        if (!strstr($_url, $this->api_hz)) {
            preg_match('/[\w\.\-]+\.[\w\:]+/', $_url, $arr);
            if (substr(strtolower($_url), 0, 5) === 'https') {
                $this->url = "https://" . $arr[0] . $this->api_hz . '/';
            } else {
                $this->url = "http://" . $arr[0] . $this->api_hz . '/';
            }
        } else {
            $this->url = $_url;
        }

        $this->isSsl = $_ssl;
        //判断是否有session，没session检查更新一次
        if (!$_SESSION['check_update_sky']) {
            $this->sky_update();
            $_SESSION['check_update_sky'] = true;
        }

        $this->isSsl = $_ssl;
    }

    public function doOrder($row, $config = [], $tool = [], $num = 1)
    {
        global $DB, $date;
        if (empty($this->pass)) {
            return '社区登录密码不能为空！';
        }

        $goods_id         = $tool['goods_id'];
        $data             = $this->parseInputData($row);
        $url              = $this->url . 'api/user/order';
        $_p               = array();
        $_p['sid']        = $goods_id;
        $_p['api_user']   = $this->user;
        $_p['out_danhao'] = $row['payorder'] . rand(111, 999);
        $_p['num']        = $num;
        if (is_array($data) && $data) {
            foreach ($data as $key => $val) {
                $_p['value' . ($key + 1)] = $val;
            }
        }
        $params        = http_build_query($_p);
        $_p['api_pwd'] = $this->pass;
        $orderid       = $row['id'];
        $text          = $this->chenm_curl($url, http_build_query($_p));
        if (is_array($arr = json_decode($text, true))) {
            if ($arr['status'] == 0) {
                if ($arr['type'] == 0) {
                    $ret          = $this->getCardData($row, $config, $arr["kms2"]);
                    $result_order = '';
                    if (!empty($ret['kmdata'])) {
                        $result_order = "卡密信息如下，请参考商品介绍使用：<br>\r\n" . $ret["kmdata"];
                        $sql_data     = array(
                            ':djorder' => $arr['message'],
                            ':result'  => $result_order,
                            ':djzt'    => 3,
                            ':endtime' => $date,
                            ':status'  => '1',
                            ':id'      => $row['id'],
                        );
                    } else {
                        $result_order = '请联系客服查看卡密信息';
                        $bz           = "已发货，卡密失败失败：<br>\n" . json_encode($arr["kms2"]);
                        $sql_data     = array(
                            ':djorder' => $arr['message'],
                            ':result'  => $result_order,
                            ':djzt'    => 3,
                            ':endtime' => $date,
                            ':status'  => '1',
                            ':id'      => $row['id'],
                        );
                        log_result(getShequTypeName($config["type"]) . "对接", $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；', '卡密识别失败，' . $ret['msg'], 1, $orderid);
                    }
                    $DB->query("UPDATE `pre_orders` set `djorder`=:djorder,`result`=:result,`djzt`=:djzt,endtime=:endtime,status=:status where id=:id", $sql_data);
                } else {
                    $sql    = "UPDATE `pre_orders` set `endtime`=:endtime,`djorder`=:djorder,`bz`=:bz,`djzt`=1,`status`=:status where id=:id";
                    $status = 1;
                    if (isset($config['orderstatus'])) {
                        $status = $config['orderstatus'];
                    }
                    $sql_data = array(
                        ':endtime' => $date,
                        ':djorder' => $arr['message'],
                        ':bz'      => '对接成功，订单号' . $arr['message'],
                        ':status'  => $status,
                        ':id'      => $row['id'],
                    );
                    $DB->query($sql, $sql_data);
                }
                log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, '下单成功，订单号' . $arr['message'], 1, $row['id']);
                return '下单成功，订单号' . $arr['message'];
            } else {
                $message = '下单失败，' . $arr['message'];
                $DB->query("UPDATE `pre_orders` set `djzt`='2', `status`='0', `endtime`= ? where id= ?", [$date, $row['id']]);
                log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $arr['message'], 1, $row['id']);
                return $arr['message'];
            }
        } else {
            $message = '网站数据解析失败，' . $text;
            $DB->query("UPDATE `pre_orders` set `djzt`='2', `status`='0', `endtime`= ? where id= ?", [$date, $row['id']]);
        }
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
        $result         = [];
        $result['code'] = -1;
        $zts            = array(0 => "等待中", 1 => "进行中", 2 => "退单中", 3 => "已退单", 4 => "异常中", 5 => "补单中", 6 => "已完成", 7 => "已退款", 8 => "待补单", 9 => "退款中");
        $url            = $this->url . 'api/user/order/query';
        $_p             = array('id' => $row['djorder']);
        $post           = http_build_query($_p);
        $data           = $this->chenm_curl($url, $post);
        if (!($arr = json_decode($data, true))) {
            $result['msg'] = '查询订单详情失败，' . $data;
        } else {
            $arr2           = $arr['data'][0];
            $result['code'] = 0;
            $result['msg']  = 'succ';
            $order_state    = isset($zts[$arr2["status"]]) ? $zts[$arr2["status"]] : '未知状态[' . $arr2["status"] . ']';
            $result['data'] = array(
                "num"         => $arr2["num"],
                "start_num"   => $arr2["start_num"],
                "now_num"     => $arr2["now_num"],
                "tnum"        => $arr2["tnum"],
                "order_state" => $order_state,
                "orderid"     => $row['djorder'],
                'add_time'    => $row['addtime'],
            );

            if ($row['status'] == 2) {
                if ($arr2["status"] == 4 || $arr2["status"] == 7 || $arr2["status"] == 9) {
                    $status = 3;
                } elseif ($arr2["status"] == 1 || $arr2["status"] == 0) {
                    $status = 2;
                } else {
                    $status = 1;
                }
                $sql_data = array(
                    ':order_state' => $status,
                    ':bz'          => '对接订单，接口查询状态【' . $order_state . '】(如手动查看不一致，可能对接方接口延迟)',
                    ':id'          => $row['id'],
                );
                $DB->query("UPDATE `pre_orders` SET `status`=:order_state,`bz`=:bz where id=:id", $sql_data);
            }

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
        $url  = $this->url . "api/user/class/list";
        $_p   = "";
        $ret  = $this->chenm_curl($url, $_p);
        $json = json_decode($ret, true);
        if (!is_array($json)) {
            return ["code" => -1, "msg" => '打开对接网站' . $this->url . '失败'];
        }

        if ($json['status'] == 0) {
            return ["code" => 0, "data" => $json['data']];
        } else {
            return ["code" => -1, "msg" => isset($json['message']) ? $json['message'] : $json['msg']];
        }
    }

    /**
     * 获取商品列表
     *
     * @return array
     */
    public function getGoodsList($config = [], $cid = 0)
    {
        $url = $this->url . "api/user/goods/list";
        $_p  = "api_user=" . $this->user;
        if ($cid) {
            $_p .= "&cid=" . $cid;
        }
        $ret  = $this->chenm_curl($url, $_p);
        $json = json_decode($ret, true);
        if (!is_array($json)) {
            return ["code" => -1, "msg" => '打开对接网站' . $this->url . '失败'];
        }
        if ($json['status'] == 0) {
            $list = [];
            foreach ($json['data'] as $v) {
                $post   = '';
                $input  = $v['post'][0]['name'];
                $inputs = '';
                foreach ($v['post'] as $k => $p) {
                    $post .= $p['param'] . "|";
                    if ($k > 0) {
                        $inputs .= $p['name'] . "|";
                    }
                }

                $post   = trim($post, '|');
                $inputs = trim($inputs, '|');
                $list[] = array('id' => $v['goodsid'], 'cid' => $v['cid'], 'type' => $v['modelid'], 'active' => isset($v['close']) ? ($v['close'] == 1 ? '0' : '1') : $v['wh'], 'name' => $v['name'], "shopimg" => $v["image_url"], "minnum" => $v["minnum"], "maxnum" => $v["maxnum"], "price" => $v["price"], "desc" => stripslashes(htmlspecialchars_decode(base64_decode($v["content"]))), 'param' => $post, 'input' => $input, 'inputs' => $inputs);
            }
            return ["code" => 0, "data" => $list];
        } else {
            return ["code" => -1, "msg" => isset($json['message']) ? $json['message'] : $json['msg']];
        }

    }

    /**
     * 获取商品参数
     *
     * @return array
     */
    public function getGoodsParams($config = [], $goodsid)
    {
        $url  = $this->url . "api/user/goods/info";
        $_p   = "api_user=" . $this->user . "&sid=" . $goodsid . "&goodsid=" . $goodsid;
        $ret  = $this->chenm_curl($url, $_p);
        $json = json_decode($ret, true);
        if (!is_array($json)) {
            return ["code" => -1, "msg" => '打开对接网站' . $this->url . '失败'];
        }
        if ($json['status'] == 0) {
            $list = [];
            if ($v = $json['data']) {
                $post  = '';
                $post2 = '';
                foreach ($v['post'] as $k => $p) {
                    $post .= "{$p['param']}|";
                    $post2 .= "{$p['name']}|";
                }
                $post  = trim($post, '|');
                $post2 = trim($post2, '|');
                $list  = array(
                    'id'      => $v['goodsid'],
                    'cid'     => $v['cid'],
                    'type'    => $v['modelid'],
                    'active'  => isset($v['close']) ? ($v['close'] == 1 ? '0' : '1') : $v['wh'],
                    'close'   => $v['close'],
                    'name'    => $v['name'],
                    'name'    => $v['name'],
                    "img"     => $v["image_url"],
                    "shopimg" => $v["image_url"],
                    "minnum"  => $v["minnum"],
                    "maxnum"  => $v["maxnum"],
                    "price"   => $v["price"],
                    "desc"    => stripslashes(htmlspecialchars_decode(base64_decode($v["content"]))),
                    'param'   => $post,
                    'param2'  => $post2,
                    'post'    => $v['post'],
                );
            }
            return ["code" => 0, "data" => $list];
        } else {
            return ["code" => -1, "msg" => isset($json['message']) ? $json['message'] : $json['msg']];
        }
    }

    //测试国内ip能否返回正常httpcode
    public function httpcode($url, $arr = [])
    {
        $parse   = parse_url($url);
        $url2    = $parse["host"];
        $url     = str_replace("http://" . $parse["host"] . "/", "http://" . $arr[0] . "/", $url);
        $ch      = curl_init();
        $timeout = 3;
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        $httpheader[] = "Accept: */*";
        $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
        $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
        $httpheader[] = "Connection: close";
        $httpheader[] = "Host:" . $arr[1];
        $httpheader[] = "SKY-YM:" . $url2;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $httpcode;
    }
    //获取最新的authcode加密后的国内ip与备案伪装域名
    public function sky_update()
    {
        $url  = 'http://api.skysq.top/api.php?act=check&version=other';
        $file = SYSTEM_ROOT . 'sky.txt';
        $data = $this->chenm_curl($url);
        if ($json = json_decode($data, true)) {
            file_put_contents($file, $json['data']);
        }
    }

    //cul函数
    public function sky_curl($url, $post = 0)
    {
        $string   = file_get_contents(SYSTEM_ROOT . 'sky.txt'); //获取文件内容
        $api_data = explode('|', authcode($string, 'DECODE', 'xy3')); //解密并且成为数组
        $ch       = curl_init();
        $parse    = parse_url($url); //获取url的域名
        if (!strstr($parse["host"], $this->api_hz)) {
            $url2 = $parse["host"] . $this->api_hz; //自动加后缀
        } else {
            $url2 = $parse["host"];
        }

        if ($_SESSION['sky_gn'] == '') {
            //没有session判断国内是否正常的话
            $http_code = $this->httpcode("http://" . $url2, $api_data);
            $use_gn    = $_SESSION['sky_gn']    = $http_code == 200 ? true : false;
            //code返回正常则使用国内对接，更快
        } else {
            $use_gn = $_SESSION['sky_gn'];
            //code返回不正常则使用海外对接
        }
        if ($use_gn) {
            //访问地址替换为ip
            $url = str_replace("http://" . $parse["host"] . "/", "http://" . $api_data[0] . "/", $url);
        } else {
            //自动加后缀
            $url = str_replace("http://" . $parse["host"] . "/", "http://" . $url2 . "/", $url);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        $httpheader[] = "Accept: */*";
        $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
        $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
        $httpheader[] = "Connection: close";
        if ($use_gn) {
            //如果国内正常则header伪造使用假的备案域名，同时发送真实的需要访问的域名
            $httpheader[] = "Host:" . $api_data[1];
            $httpheader[] = "SKY-YM:" . $url2;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36');
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($ch);
        if ($ret === false) {
            return 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);
        return $ret;
    }

    //cul函数
    public function chenm_curl($url, $post = 0, $timeout = 10)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        $httpheader[] = "Accept: */*";
        $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
        $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
        $httpheader[] = "Connection: close";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36');
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($ch);
        if ($ret == false) {
            return 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);
        return $ret;
    }
}
