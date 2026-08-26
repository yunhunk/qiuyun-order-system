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
// * 发卡库存监控地址：/cron/fakaCron.php?act=daily&key=监控密钥
// * 注意：千万不要监控太快或使用多节点监控！！！否则可能会出现问题且占用服务器过多性能

use core\Db;
use core\Ems;
use core\Sms;

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
    exit("请先设置好监控密钥");
}

if ($conf['cronkey'] != $_GET['key']) {
    exit("监控密钥不正确");
}

$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
if ($act == 'daily') {

    // 更新卡密库存
    $list = \core\Db::name('tools')->where([
        'is_curl'    => 4,
        'stock_time' => ['<=', time()],
        'stock'      => ['<=', 5],
    ])->field('tid,stock,active,stock_time')->limit(100)->select();

    $faka = 0;

    $start_time = $now_time = time();

    foreach ($list as $key => $value) {
        // 缓存2小时
        if ($res['stock'] <= 0 || $res['stock_time'] <= time()) {
            $stock = intval($DB->get_column("SELECT count(*) FROM pre_faka WHERE tid= ? AND orderid<=1", [$value['tid']]));
        } else {
            $stock = intval($value['stock']);
        }

        if ($stock > 0) {
            $faka++;
            \core\Db::name('tools')->where([
                'tid' => $value['tid'],
            ])->update([
                'stock_time' => time() + (3600 * 2),
                'stock'      => $stock,
            ]);
        }
    }

    $notify_stock_num = intval($conf['notify_stock_num']) > 0 ? $conf['notify_stock_num'] : 5;

    // 主站库存预警
    if (($conf['notify_stock_open'] == 1 || $conf['notify_stock_open'] == 2) && Ems::checkIsRun()) {
        $list = \core\Db::name('tools')->where([
            'zid'        => ['<=', 1],
            'active'     => ['=', 1],
            'notifytime' => ['<=', time()],
            'stock'      => ['<=', $notify_stock_num],
        ])->field('tid,stock')->limit(200)->select();

        // 邮件通知给站长
        if (($count = count($list)) > 0) {
            $adm_email = $conf['adm_email'];
            $tids      = [];
            foreach ($list as $key => $value) {
                $tids[] = $value['tid'];
            }

            if (validateData($adm_email, 'email')) {
                try {
                    $ems   = new Ems();
                    $send1 = $ems->sendEmail($adm_email, '重要提醒！商品库存预警通知', '检测到<b>' . $count . '<b>个商品库存小于' . $notify_stock_num . '个<br/><br/>商品ID列表如下:<br/>' . implode('<br/>', $tids));
                } catch (\Throwable $th) {
                    $th->getMessage() . '[' . $th->getLine() . ']';
                }
            }
        }
    }

    $master_kucun_notify_open = conf('master_kucun_notify_open');

    if ($master_kucun_notify_open == 1) {
        $count                    = 0;
        $ok                       = 0;
        $master_kucun_notify_time = conf('master_kucun_notify_time');
        if ($master_kucun_notify_time < 1) {
            // 单位: 小时
            $master_kucun_notify_time = 6;
        }

        // 供货商库存
        $list = \core\Db::name('tools')->where([
            'zid'        => ['>', 1],
            'condition'  => ['=', 1],
            'active'     => ['=', 1],
            'notifytime' => ['<=', time()],
            'stock'      => ['<=', $notify_stock_num],
        ])->field('tid,stock')->limit(500)->select();

        $error = [];

        if ($list) {
            $count = count($list);

            foreach ($list as $key => $value) {
                // 查询缓存24小时
                $stock = intval($value['stock']);

                // $stock = Db::name('faka')->where([
                //     'tid'     => $value['tid'],
                //     'zid'     => $value['zid'],
                //     'status ' => 0,
                // ])->count();
                if ($stock <= 0) {
                    $userrow = Db::name('master')->find(['zid' => $value['zid']]);
                    $send1   = $send2   = '未通知';
                    if ($userrow && $userrow['master_open'] == 1) {
                        // 邮件通知给站长
                        if (Ems::checkIsRun() && validateData($userrow['email'], 'email')) {
                            try {
                                $ems   = new Ems();
                                $send1 = $ems->sendEmail($userrow['email'], '您有商品库存不足, 请加卡', '您有ID为' . $value['tid'] . '的商品库存不足' . $notify_stock_num . '个, 请及时加卡, 否则将无法提现');
                            } catch (\Throwable $th) {
                                $send1 = $th->getMessage() . '[' . $th->getLine() . ']';
                            }
                        }

                        // 手机通知给站长
                        if ($send1 !== true && Sms::checkIsRun() && validateData($userrow['tel'], 'mobile')) {
                            try {
                                $sms   = new Sms();
                                $send2 = $sms->send($userrow['tel'], null, 'kucun');
                            } catch (\Throwable $th) {
                                $send2 = $th->getMessage() . '[' . $th->getLine() . ']';
                            }
                        }
                    }

                    if ($send1 === true || $send2 === true) {
                        $ok++;
                        \core\Db::name('tools')->where([
                            'tid' => $value['tid'],
                        ])->update([
                            'notifytime' => time() + ($master_kucun_notify_time * 3600),
                        ]);
                    } else {
                        $error[] = '使用邮件通知到' . $userrow['email'] . '失败,' . $send1 . '; 使用短信通知到' . $userrow['tel'] . '失败,' . $send2 . '';
                    }
                }
            }
        }
        $html = '[' . $date . ']供货商品库存不足通知：共检测到' . $count . '个商品库存不足, 成功通知到' . $ok . "个供货商<br/>\n[" . $date . "]更新卡密商品库存：共成功更新" . $faka . "个商品<br/>\nT通知失败日志如下:<br/>\n" . implode("<br/>\n", $error);
        $html .= "<br/>\n库存检测任务执行完毕, 耗时" . (time() - $start_time) . "秒";
        exit($html);
    } else {
        exit('[' . $date . ']供货商品库存不足通知：当前已关闭');
    }
} else {
    exit('No act！');
}
