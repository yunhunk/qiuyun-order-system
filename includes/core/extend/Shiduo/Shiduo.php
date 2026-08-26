<?php
use \core\Card;

class Shiduo extends Card
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
            //获取商品信息
            $tool = $DB->get_row("SELECT * FROM cmy_tools where tid=:tid limit 1", [':tid' => $row['tid']]);
            if (!is_array($tool)) {
                return '下单失败！该订单对应的商品信息不存在！';
            }
        }

        $goods_id = $tool['goods_id'];
        $url      = $config["url"] . "api/pay";
        $orderid  = $row['id'];
        $data     = array(
            'appid'        => $config['username'],
            'gid'          => $goods_id,
            'count'        => $num,
            'out_trade_no' => $row['payorder'],
        );
        $appkey = $config['password'];
        $post   = http_build_query($data) . '&sign=' . getSign_shiduo($data, $appkey);
        $params = http_build_query($data);
        $result = shequ_get_curl($url, $post, '', '', 0, 0, 0, $config['proxy']);
        $json   = json_decode($result, true);
        if (is_array($json)) {
            $Arr = [
                '10001' => '参数错误 (可能原因:本地商品ID API商品ID 库存等参数为空)',
                '10002' => '会员账户已禁用',
                '10003' => '签名验证失败 (可能原因:秘钥不对)',
                '10004' => 'API商品价格有误',
                '10005' => 'API账户余额不足',
                '10006' => '扣款失败',
                '10007' => 'API库存不足',
                '10009' => 'API商品已下架',
                '10010' => 'API商品不存在',
                '20000' => '扣款成功, 发货成功',
                '20001' => '扣款成功, 发货失败',
                '20002' => '扣款成功, 发货失败',
            ];
            if ($json['code'] == '1') {
                $msg     = isset($Arr[$json['status']]) ? $Arr[$json['status']] : '未知状态码，请联系货源站站长！[' . $json['status'] . ']';
                $message = "下单成功，订单号：" . $json['OrderId'] . '！【' . $msg . '】';
                $status  = 2;
                if ($config['orderstatus']) {
                    $status = $config['orderstatus'];
                }

                $kmdata = "卡密信息如下，请参考商品介绍使用：<br>\r\n";
                if (is_array($json['kaData']) && count($json['kaData']) > 0) {
                    $ret    = $this->getCardData($row, $config, $json['kaData']);
                    $kmdata = '';
                    if (!empty($ret['kmdata'])) {
                        $kmdata  = "卡密信息如下，请参考商品介绍使用：<br>\r\n" . $ret["kmdata"];
                        $bz      = '解析成功，卡密信息：' . $ret["kmdata"];
                        $sqlData = [$kmdata, $bz, 3, $date, 1, $row['id']];
                    } else {
                        $bz      = '解析失败，请复制相关问题和右方代码提交工单：' . json_encode($json['kaData']);
                        $sqlData = [$kmdata, $bz, 4, $date, 2, $row['id']];
                        log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $post, '卡密识别失败，' . $ret['msg'], 1, $orderid);
                    }
                    $DB->query("UPDATE `pre_orders` set result= ?,bz= ?,`djzt`= ?,endtime= ?,status= ? where id= ?", $sqlData);
                } else {
                    $DB->query("UPDATE `pre_orders` set djorder= ?,endtime= ?,status= ? where id= ?", array($json['OrderId'], $date, $status, $orderid));
                }

                log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $message, 1, $orderid);
            } else {
                $message = $Arr[$json['status']];
                if ($message == "") {
                    $message = $json['msg'];
                }

                $message = "下单失败，" . $message;
                log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $message, 0, $orderid);
            }

            return $message;
        }

        log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, '下单失败，' . $result, 0, $orderid);
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

        return ['code' => -1, 'msg' => '该对接站类型不支持'];
    }

    /**
     * 获取分类列表
     *
     * @return array
     */
    public function getCateList($config = [])
    {
        return ['code' => -1, 'msg' => '该对接站类型不支持'];
    }

    /**
     * 获取商品列表
     *
     * @return array
     */
    public function getGoodsList($config = [], $cid = null)
    {
        return ['code' => -1, 'msg' => '该对接站类型不支持'];
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
