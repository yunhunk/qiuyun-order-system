<?php
/* *
 * 类名：EpayNotify
 * 功能：彩虹易支付通知处理类
 * 详细：处理易支付接口通知返回
 */

require_once SYSTEM_ROOT . "alipay/alipay_core.function.php";
require_once SYSTEM_ROOT . "alipay/alipay_md5.function.php";

class AlipayNotify
{

    public $alipay_config;

    public function __construct($alipay_config)
    {
        $this->alipay_config   = $alipay_config;
        $this->http_verify_url = $this->alipay_config['apiurl'] . 'api.php?';
    }
    public function AlipayNotify($alipay_config)
    {
        $this->__construct($alipay_config);
    }

    public function writeCheckLog($msg)
    {
        $fp = fopen(SYSTEM_ROOT . "/epay/checklog.txt", "a");
        flock($fp, LOCK_EX);
        fwrite($fp, "回调执行时间：" . strftime("%Y%m%d%H%M%S", time()) . "\n" . $msg . "\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    public function verifyNotify()
    {
        if (empty($_GET)) {
            //判断POST来的数组是否为空
            return false;
        } else {
            //生成签名结果
            $isSign = $this->getSignVeryfy($_GET, $_GET["sign"]);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'true';
            if (defined('CHECKNOTIFY') && CHECKNOTIFY == '1') {
                //二次回调验证
                $responseTxt = $this->getResponse($_GET["trade_no"], $_GET["out_trade_no"]);
            }
            //$responseTxt = $this->getResponse($_GET["trade_no"]);

            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match("/true$/i", $responseTxt) && $isSign) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 针对return_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    public function verifyReturn()
    {
        if (empty($_GET)) {
            //判断POST来的数组是否为空
            return false;
        } else {
            //生成签名结果
            $isSign = $this->getSignVeryfy($_GET, $_GET["sign"]);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'true';
            if (defined('CHECKNOTIFY') && CHECKNOTIFY == '1') {
                //二次回调验证
                $responseTxt = $this->getResponse($_GET["trade_no"], $_GET["out_trade_no"]);
            }
            //$responseTxt = $this->getResponse($_GET["trade_no"]);

            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match("/true$/i", $responseTxt) && $isSign) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 获取返回时的签名验证结果
     * @param $para_temp 通知返回来的参数数组
     * @param $sign 返回的签名结果
     * @return 签名验证结果
     */
    public function getSignVeryfy($para_temp, $sign)
    {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = argSort($para_filter);

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = createLinkstring($para_sort);

        $isSgin = false;
        $isSgin = md5Verify($prestr, $sign, $this->alipay_config['key']);

        return $isSgin;
    }

    /**
     * 远程获取数据
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     * @param $url 指定URL完整路径地址
     * @param $cacert_url 指定当前工作目录绝对路径
     * return 远程输出的数据
     */

    public function getHttpResponse($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 超时时间30秒
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 显示输出结果
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); //严格认证
        $responseText = curl_exec($curl);
        //var_dump(curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        return $responseText;
    }

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    public function getResponse($trade_no, $out_trade_no)
    {
        //return 'true';
        $partner     = trim($this->alipay_config['partner']);
        $key         = trim($this->alipay_config['key']);
        $veryfy_url  = $this->http_verify_url . "act=order&pid=" . $partner . "&key=" . $key . "&trade_no=" . $trade_no . "&out_trade_no=" . $out_trade_no;
        $responseTxt = $this->getHttpResponse($veryfy_url);
        $arr         = json_decode($responseTxt, true);

        //回调查询日志，需要时可开启
        //$this->writeCheckLog('订单查询返回->'.$responseTxt);
        if ($arr['status'] == 1) {
            return 'true';
        } else {
            return 'false';
        }
    }
}
