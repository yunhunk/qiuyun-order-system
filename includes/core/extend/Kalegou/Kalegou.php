<?php
use \core\Card;

class Kayixin extends Card
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

        if (!$tool || !is_array($tool)) {
            $tool = $DB->get_row("SELECT * FROM cmy_tools where tid=:tid limit 1", [':tid' => $row['tid']]); //获取商品信息
            if (!is_array($tool)) {
                return '下单失败！该订单对应的商品信息不存在！';
            }
        }

        $orderurl = $tool['goods_param'];
        $data     = $this->parseInputData($row);

        $orderid  = $row['id'];
        $password = $config['password'];
        $pwd      = "";
        $i        = 0;
        while ($i < strlen($password)) {
            $pwd .= ord($password[$i]) . ",";
            $i = $i + 1;
        }
        $config["url"] = shequ_url_parse($config);
        $username      = urlencode($config["username"]);
        $url           = $config["url"] . "webnew/Customer/CustomerProcess/CheckCustomerLogin.aspx?UserName=" . $username . "&pwd=" . $pwd . "&CheckCode=&DynamicCode=&FengYunlingCode=&EmailCode=&IsSafe=0&rki=undefined&rk=undefined&pwd1=" . $pwd . "&_=" . time() . "000";
        $data1         = get_curl($url, 0, $config["url"] . "", 0, 1);
        $data2         = strstr($data1, "{");
        $json          = json_decode($data2, true);
        $params        = "num=" . $num . "&url=" . http_build_query($data);
        if ($json["Status"]["Code"] == "success") {

            $cookies = "";
            preg_match_all("/Set-Cookie: (.*);/iU", $data1, $matchs);
            foreach ($matchs[1] as $val) {
                if ($val != "") {
                    $cookies .= $val . "; ";
                }
            }

            preg_match("/PID=(\\d+)&TPID=(\\d+)&StockID=(.*)&/i", $orderurl, $match);
            $PID     = $match[1];
            $TPID    = $match[2];
            $StockID = $match[3];
            $url     = $config["url"] . "Templates/CustomTemplate.aspx?PID=" . $PID . "&TPID=" . $TPID . "&StockID=" . $StockID;
            $params  = "num=" . $num . "&url=" . $url;
            $data1   = get_curl($url, 0, 0, $cookies);
            preg_match("!id=\"__VIEWSTATE\" value=\"(.*?)\"!i", $data1, $VIEWSTATE);
            preg_match("!id=\"__VIEWSTATEGENERATOR\" value=\"(.*?)\"!i", $data1, $VIEWSTATEGENERATOR);
            preg_match("!id=\"HFOrderNo\" value=\"(.*?)\"!i", $data1, $HFOrderNo);
            preg_match("!id=\"HFGameCompanyID\" value=\"(.*?)\"!i", $data1, $HFGameCompanyID);
            preg_match("!id=\"HFParvalue\" value=\"(.*?)\"!i", $data1, $HFParvalue);
            preg_match("!id=\"HFSupOrderNo\" value=\"(.*?)\"!i", $data1, $HFSupOrderNo);
            if ($data[1]) {
                $addstr = "&txtChargeWay=" . urlencode($data[1]);
            }
            $post = "ScriptManager1=UpdatePanel2|ImageButtonBuyCheck&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=" . urlencode($VIEWSTATE[1]) . "&__VIEWSTATEGENERATOR=" . $VIEWSTATEGENERATOR[1] . "&HFProductID=" . $PID . "&HFOrderNo=" . $HFOrderNo[1] . "&HFGameCompanyID=" . $HFGameCompanyID[1] . "&HFTemplateID=" . $TPID . "&HFParvalue=" . $HFParvalue[1] . "&HFSupOrderNo=" . $HFSupOrderNo[1] . "&txtAccountName=" . urlencode($data[0]) . "&txtAccountName1=" . urlencode($data[0]) . $addstr . "&DrCount=" . $num . "&txtComment=&ImageButtonBuyCheck=%E7%A1%AE%E8%AE%A4%E8%B4%AD%E4%B9%B0";
            $data = get_curl($url, $post, $url, $cookies);

            if (strstr($data, "HandTemplateDetail") !== false) {
                $status = 2;
                if ($config['orderstatus']) {
                    $status = $config['orderstatus'];
                }

                $djresult = '';

                $tool = $DB->get_row("SELECT * FROM cmy_tools where tid='" . $row['tid'] . "' limit 1");

                if ($tool['result']) {
                    $djresult = $tool['result'];
                }

                $DB->query(
                    "UPDATE `pre_orders` set result= ?,djorder= ?,endtime= ?,status= ?,djzt='1' where id= ?",
                    [$djresult, $HFOrderNo[1], $date, $status, $row['id']]
                );
                $message = "下单成功!订单号为:" . $HFOrderNo[1];
                log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $message, 1, $row['id']);
                return $message;
            }

            $DB->query("UPDATE `pre_orders` set status='0',djzt='2' where id=:id", [':id' => $orderid]);

            if (preg_match("/alert\\((.*?)\\)/", $data, $msg)) {
                log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, "下单失败!" . $msg[1], 0, $row['id']);
                return $msg[1];
            }

            $data = str_replace(array("\r\n", "\r", "\n"), "", $data);

            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, "下单失败!" . $data, 0, $row['id']);
            return $data;
        }

        $DB->query("UPDATE `pre_orders` set status='0',djzt='2' where id=:id", [':id' => $orderid]);

        log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, "下单失败!" . $json["Status"]["Msg"], 0, $row['id']);
        return $json["Status"]["Msg"];
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
        $data   = json_decode($result, true);
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
        return ['code' => -1, "msg" => "该对接站类型暂不支持"];
    }

    /**
     * 获取商品列表
     *
     * @return array
     */
    public function getGoodsList($config = [])
    {
        return ['code' => -1, "msg" => "该对接站类型暂不支持"];
    }

    /**
     * 获取商品参数
     *
     * @return array
     */
    public function getGoodsParams($config, $goods_id)
    {
        return ['code' => -1, "msg" => "该对接站类型暂不支持"];
    }
}
