<?php
use \core\Card;

class Youyunbao extends Card
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
        $data = $this->parseInputData($row);

        $url     = $config["url"] . "home/api";
        $orderid = $row['id'];
        if ($tool['goods_type'] != '101' && $tool['goods_type'] != '102') {
            $result = '下单失败！商品类型错误或不支持，请核对是否为直冲或卡密类型';
            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . json_encode($data), $result, 0, $orderid);
            return $result;
        }
        $goods_id = 0;
        $password = $config['password'];
        $paypwd   = $config['paypwd'];
        if (empty($password)) {
            return '该货源站登陆密码解析失败，请重新填写保存后再试！';
        }
        if (is_numeric($tool['goods_param'])) {
            $goods_id = $tool['goods_param'];
        } elseif (preg_match('/p\/id\/([\d]+)/', $tool['goods_param'], $match)) {
            $goods_id = $match[1];
        }

        if ($goods_id < 1) {
            $result = '下单失败！下单页面地址不正确，格式为：http://网站地址/home/index/p/id/商品ID';
            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：无', $result, 0, $orderid);
            return $result;
        }

        if (!empty($paypwd)) {
            // $paypwd是对接密码
            $token = md5('user=' . $config['username'] . '&&pass=' . md5($paypwd));
        } else {
            $token = md5('user=' . $config['username'] . '&&pass=' . md5($password));
        }
        $beizhu = [];
        foreach ($data as $value) {
            if (empty($value)) {
                break;
            }
            $beizhu[] = $value;
        }
        $post = [
            'user'   => $config['username'],
            'pass'   => md5($password),
            'order'  => $row['payorder'],
            'mun'    => $num,
            'money'  => $row['money'],
            'spid'   => $goods_id,
            'code'   => $tool['goods_type'],
            'token'  => $token,
            'beizhu' => base64_encode(json_encode($beizhu)),
        ];

        if ($tool['goods_type'] == '102') {
            $post['yibu'] = '1';
        }
        $params = http_build_query($post);
        $result = shequ_get_curl($url, $params, 0, 0, 0, 0, 0, $config['proxy']);
        $json   = json_decode($result, true);
        if (is_array($json)) {
            $Arr = [
                '4003'   => '对接站商品不存在或已下架，或者余额不足',
                '4004'   => '对接站商品库存不足',
                '4005'   => '对接站系统错误，订单创建失败',
                '4006'   => '对接站系统错误，订单改写失败',
                '668877' => '未开通API权限！',
                '70712'  => '低于进货价！',
            ];
            if ($json['code'] == '8888') {
                $message = "下单成功，订单号：" . $json['order'];
                $status  = 2;
                if ($config['orderstatus'] > 0) {
                    $status = $config['orderstatus'];
                }

                if ($tool['goods_type'] == '101') {
                    $row['djorder'] = $json['order'];
                    $this->query($row, $config);
                }

                $djresult = '';

                if ($tool['result']) {
                    $djresult = $tool['result'];
                }

                $data = [
                    ':result'  => $djresult,
                    ':djorder' => $json['order'],
                    ':endtime' => $date,
                    ':status'  => $status,
                    ':id'      => $orderid,
                ];
                $DB->query("UPDATE `pre_orders` set `result`=:result,`djorder`=:djorder,`endtime`=:endtime,`status`=:status,djzt='1' where id=:id", $data);
                log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $message, 1, $orderid);
            } elseif ($json['code'] == 70712) {
                //低于进货价，自动涨价
                updateToolPirce($row['tid'], $row['money']);
                $message = "下单失败，" . $Arr[$json['code']];
                $DB->query("UPDATE `pre_orders` set status='0',djzt='2' where id=:id", [':id' => $orderid]);
                log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $message, 0, $orderid);
            } else {

                $message = $Arr[$json['code']];
                if ($message == "" && $json['text'] != "") {
                    $message = $json['text'];
                } elseif ($message == "" && $json['code'] != "") {
                    $message = $json['code'];
                } else {
                    $message = $result;
                }

                $message = "下单失败，" . $message;

                $DB->query("UPDATE `pre_orders` set status='0',djzt='2' where id=:id", [':id' => $orderid]);
                log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $message, 0, $orderid);
            }

            return $message;
        }

        $DB->query("UPDATE `pre_orders` set status='0',djzt='2' where id=:id", [':id' => $orderid]);

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
            $result['data'] = array(
                "num"         => $arr2["num"],
                "start_num"   => $arr2["start_num"],
                "now_num"     => $arr2["now_num"],
                "tnum"        => $arr2["tnum"],
                "order_state" => $zts[$arr2["status"]],
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
                    ':bz'          => '对接订单状态【' . $zts[$arr2["status"]] . '|' . $arr2["status"] . '】，已同步',
                    ':id'          => $row['id'],
                );
                $DB->query("UPDATE `pre_orders` set `status`=:order_state,`bz`=:bz where id=:id", $sql_data);
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
