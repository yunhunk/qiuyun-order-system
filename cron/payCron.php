<?php
// * 系统任务监控文件 by 星河云Plus
// * 说明：用于网站的各种需要监控任务的处理接口
// * 监控频率建议5~30分钟一次
// * 支付补单监控地址：/cron/payCron.php?act=payCron&key=监控密钥
// * 订单补单监控地址：/cron/orderCron.php?act=orderCron&key=监控密钥
// * 商品价格监控地址：/cron/priceCron.php?act=priceCron&key=监控密钥
// * 卡商订单监控地址：/cron/kashangCron.php?act=kashangCron&key=监控密钥
// * 排行奖励监控地址：/cron/rankCron.php?act=rankCron&key=监控密钥
// * 日常清理监控地址：/cron/dailyCron.php?act=daily&key=监控密钥
// * 注意：千万不要监控太快或使用多节点监控！！！否则可能会出现问题且占用服务器过多性能

if (preg_match('/spider/', $_SERVER['HTTP_USER_AGENT'])) {
    exit;
}

$is_cron = true;
include "../includes/common.php";

if (function_exists("set_time_limit")) {
    @set_time_limit(0);
}

if (function_exists("ignore_user_abort")) {
    @ignore_user_abort(true);
}

@header('Content-Type: text/html; charset=UTF-8');
if (empty($conf['cronkey'])) {
    exit("请先到主站设置->网站核心设置->网站底部，设置好监控密钥");
}

if ($conf['cronkey'] != $_GET['key']) {
    exit("监控密钥不正确");
}

if (!function_exists('curl_pay_get')) {
    function curl_pay_get($url, $post = 0, $timeout = 30)
    {
        $ch           = curl_init($url);
        $httpheader[] = "Accept: */*";
        $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
        $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
        $httpheader[] = "Connection: close";
        //$httpheader[] = "Content-Type:application/x-www-form-urlencoded; charset=UTF-8";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            if (is_array($post)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            }
        }
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; U; Android 4.4.1; zh-cn; R815T Build/JOP40D) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.5 Mobile Safari/533.1");
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }
}

if (!function_exists('payMd5Sign')) {
    function payMd5Sign($arr, $key)
    {
        require_once ROOT . "other/alipay/alipay_core.function.php";
        require_once ROOT . "other/alipay/alipay_md5.function.php";
        //除去待签名参数数组中的空值和签名参数
        $para_filter = paraFilter($arr);
        //对待签名参数数组排序
        $para_sort = argSort($para_filter);
        //把数组所有元素，按照“参数=参数值”的模式 用“&”字符拼接成字符串
        $prestr = createLinkstring($para_sort);
        //生成签名结果
        $mysign = md5Sign($prestr, $key);
        return $mysign;
    }
}

$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
if ($act == 'payCron') {
    //余额支付的补单处理
    if (isset($_GET['out_trade_no'])) {
        $out_trade_no = addslashes($_GET['out_trade_no']);
        $srow         = $DB->get_row("SELECT * FROM pre_pay WHERE trade_no='{$out_trade_no}' limit 1 for update");
        if ($srow['type'] == 'rmb' && $srow['tid'] > 0) {
            require_once SYSTEM_ROOT . 'ajax.class.php';
            if ($DB->count("SELECT count(*) FROM `pre_orders` WHERE `payorder`='{$out_trade_no}'") < 1) {
                try {
                    if ($msg = processOrderAll($srow)) {
                        echo '已成功补单:' . $out_trade_no . "<br/>\n";
                    } else {
                        echo '补单失败:' . $out_trade_no . "，原因请查看分站明细<br/>\n";
                    }
                } catch (\Exception $e) {
                    echo '补单失败:' . $out_trade_no . "，" . $e->getMessage() . "<br/>\n";
                }
            } else {
                echo '无需补单:' . $out_trade_no . "，该订单已创建<br/>\n";
            }
            die;
        }
    }

    if (isset($_GET['out_trade_no']) && "" != $_GET['out_trade_no']) {
        $limit = 1;
    } else {
        if ($_GET['limit']) {
            $limit = intval($_GET['limit']);
        } else {
            if (!isset($_GET['test'])) {
                $paycron_lasttime = $DB->get_column("SELECT v FROM pre_config WHERE k='paycron_lasttime' LIMIT 1");
                if (time() - strtotime($paycron_lasttime) < 30) {
                    exit('访问频率过快！');
                }
            }
            $trade_no = date("YmdHis", strtotime($paycron_lasttime)) . '000';
            $limit    = $DB->count("SELECT count(*) FROM pre_pay WHERE trade_no>'$trade_no'");
        }

        if ($limit < 1) {
            exit('没有订单需要监控！');
        }
        if ($limit > 50) {
            $limit = 50;
        }
    }

    saveSetting('paycron_lasttime', $date);
    require_once SYSTEM_ROOT . 'ajax.class.php';
    if ($conf['epays_open'] == 1) {
        if (!empty($conf['qqpay_pid']) && !empty($conf['qqpay_key']) && !empty($conf['qqpay_url'])) {
            $payInfo[] = array(
                'url' => $conf['qqpay_url'],
                'pid' => $conf['qqpay_pid'],
                'key' => $conf['qqpay_key'],
            );
        }
        if (!empty($conf['alipay_pid']) && !empty($conf['alipay_key']) && !empty($conf['alipay_url'])) {
            $payInfo[] = array(
                'url' => $conf['alipay_url'],
                'pid' => $conf['alipay_pid'],
                'key' => $conf['alipay_key'],
            );
        }

        if (!empty($conf['wxpay_pid']) && !empty($conf['wxpay_key']) && !empty($conf['wxpay_url'])) {
            $payInfo[] = array(
                'url' => $conf['wxpay_url'],
                'pid' => $conf['wxpay_pid'],
                'key' => $conf['wxpay_key'],
            );
        }

        $payapi = payApi();
        if (!empty($payapi) && !empty($conf['epay_pid']) && !empty($conf['epay_key'])) {
            $payInfo[] = array(
                'url' => $payapi,
                'pid' => $conf['epay_pid'],
                'key' => $conf['epay_key'],
            );
        }

        foreach ($payInfo as $key => $info) {
            $payapi = $info['url'];
            echo "<br/>\n【" . $payapi . "】开始查询订单...<br/>\n";
            $postArr = [
                'pid'   => $info['pid'],
                'limit' => $limit,
                'key'   => $info['key'],
            ];
            if (isset($_GET['out_trade_no'])) {
                //查询单条订单
                $postArr['out_trade_no'] = addslashes($_GET['out_trade_no']);
                $postArr['sign']         = payMd5Sign($postArr, $info['key']);
                $post                    = http_build_query($postArr);
                $purl                    = $payapi . 'api.php?act=order&' . $post;
                $data                    = curl_pay_get($purl, 0, 10);
                $json                    = json_decode($data, true);
                $arr['code']             = $json['code'];
                $arr['msg']              = $json['msg'];
                $arr['data']             = $json;
            } else {
                $postArr['sign'] = payMd5Sign($postArr, $info['key']);
                $post            = http_build_query($postArr);
                $purl            = $payapi . 'api.php?act=orders&' . $post;
                $data            = curl_pay_get($purl, 0, 10);
                $arr             = json_decode($data, true);
            }

            if (is_array($arr)) {
                if ($arr['code'] == 1) {
                    foreach ($arr['data'] as $row) {
                        if ($row['status'] == 1) {
                            $out_trade_no = $row['out_trade_no'];
                            $srow         = $DB->get_row("SELECT * FROM `pre_pay` WHERE trade_no='{$out_trade_no}' LIMIT 1");
                            if ($DB->exec("UPDATE `pre_pay` SET `status` ='1' where `trade_no`='{$out_trade_no}'")) {
                                echo '已成功补单:' . $out_trade_no . "<br/>\n";
                                if ($srow['tid'] > 0) {
                                    $count = $DB->count("SELECT count(*) FROM `pre_orders` WHERE `payorder`='{$out_trade_no}'");
                                    if ($count < 1) {
                                        processOrderAll($srow);
                                    }
                                } else {
                                    processOrderAll($srow);
                                }

                            } else {
                                //echo '无需补单:' . $out_trade_no . "<br/>\n";
                            }
                        }
                    }
                    echo '【' . $payapi . "】执行完成" . "<br/>\n";
                } else {
                    echo '【' . $payapi . '】' . $arr['msg'] . "<br/>\n" . $purl . "<br/>\n";
                }
            } else {
                echo '【' . $payapi . '】查询订单失败，' . $data . "<br/>\n";
            }
        }
    } else {
        //易支付的补单处理
        if ($conf['epay_pid'] && $conf['epay_key']) {
            $payapi = payApi();
            echo '【' . $payapi . '】开始查询订单...' . "<br/>\n";
            $postArr = [
                'pid'   => $conf['epay_pid'],
                'limit' => $limit,
                'key'   => $conf['epay_key'],
            ];
            if (isset($_GET['out_trade_no'])) {
                //查询单条订单
                $postArr['out_trade_no'] = addslashes($_GET['out_trade_no']);
                $postArr['sign']         = payMd5Sign($postArr, $conf['epay_key']);
                $post                    = http_build_query($postArr);
                $purl                    = $payapi . 'api.php?act=order&' . $post;
                $data                    = curl_pay_get($purl, $post, 10);
                $json                    = json_decode($data, true);
                $arr['code']             = $json['code'];
                $arr['msg']              = $json['msg'];
                $arr['data']             = $json;
            } else {
                $postArr['sign'] = payMd5Sign($postArr, $conf['epay_key']);
                $post            = http_build_query($postArr);
                $purl            = $payapi . 'api.php?act=orders&' . $post;
                $data            = curl_pay_get($purl, $post, 10);
                $arr             = json_decode($data, true);
            }

            if (is_array($arr)) {
                if ($arr['code'] == 1) {
                    foreach ($arr['data'] as $row) {
                        if ($row['status'] == 1) {
                            $out_trade_no = $row['out_trade_no'];
                            $srow         = $DB->get_row("SELECT * FROM `pre_pay` WHERE trade_no='{$out_trade_no}' LIMIT 1");
                            if ($DB->exec("UPDATE `pre_pay` SET `status` ='1' where `trade_no`='{$out_trade_no}'")) {
                                echo '已成功补单:' . $out_trade_no . "<br/>\n";
                                if ($srow['tid'] > 0) {
                                    $count = $DB->count("SELECT count(*) FROM `pre_orders` WHERE `payorder`='{$out_trade_no}'");
                                    if ($count < 1) {
                                        processOrderAll($srow);
                                    }
                                } else {
                                    processOrderAll($srow);
                                }

                            } else {
                                //echo '无需补单:' . $out_trade_no . "<br/>\n";
                            }
                        }
                    }
                    echo '【' . $payapi . "】执行完成" . "<br/>\n";
                } else {
                    echo '【' . $payapi . '】' . $arr['msg'] . "<br/>\n";
                }
            } else {
                echo '【' . $payapi . '】查询订单失败，' . $data . "<br/>\n";
            }
        } else {
            exit('未配置易支付信息');
        }
    }

} else {
    exit('No act！');
}
