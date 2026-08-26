<?php
/**
 * 系统任务监控组件 By 星河软件工作室
 */
include '../includes/common.php';
$title = '系统任务监控组件';
checkLogin();

include './head.php';

echo '<div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top: 10px">
    <div class="block">
        <div class="block-title"><h3 class="panel-title">' . $title . '</h3></div>
        <div class="">
            <div class="alert" style="background-color: #F8F8FF;color: #0c69c6;">
              * 使用注意：<span style="color:red">网站程序不是软件不可以自动监控，所有任务都需要第三方软件或平台来定时访问“<span>监控地址</span>”来达到自动执行的效果</span><br>
              * 组件说明：用于网站的各种需要监控任务的自动化处理接口<br>
              * 使用方法：<span style="color:red">必须将下方需要监控任务的链接添加在第三方定时任务或宝塔计划任务-访问URL里面，才能达到自动处理的效果</span><br><br>
              * 监控频率建议2~30分钟一次，请根据网站实际需求和服务器性能来监控任务！注意一个链接只能有添加一个监控任务，否则可能数据出错<br>
              一.支付补单，可以将没有回调成功处理的支付订单重新生成订单并处理<br>&nbsp;&nbsp;&nbsp;&nbsp;监控地址：' . $weburl . 'cron/payCron.php?act=payCron&key=' . $conf['cronkey'] . '&nbsp;<a target="_blank" href="' . $weburl . 'cron/payCron.php?act=payCron&key=' . $conf['cronkey'] . '" class="btn btn-success btn-xs">访问</a><br>&nbsp;&nbsp;&nbsp;&nbsp;监控频率：建议1~3分钟<br><br>
              二.订单补单，待处理中的对接订单自动重新补单<br>&nbsp;&nbsp;&nbsp;&nbsp;监控地址：' . $weburl . 'cron/orderCron.php?act=orderCron&key=' . $conf['cronkey'] . '&nbsp;<a target="_blank" href="' . $weburl . 'cron/orderCron.php?act=orderCron&key=' . $conf['cronkey'] . '" class="btn btn-success btn-xs">访问</a><br>&nbsp;&nbsp;&nbsp;&nbsp;监控频率：建议3~10分钟<br><br>
              三.商品同步，支持亿樂、九五、同系统、商战网、时空云、卡商、Kayixin、卡卡云、彩虹、直客的对接商品价格、上架状态、商品介绍、库存同步(介绍和库存部分系统支持)<br>
              &nbsp;&nbsp;&nbsp;&nbsp;通用地址：' . $weburl . 'cron/priceCron.php?act=priceCron&key=' . $conf['cronkey'] . '&nbsp;<a target="_blank" href="' . $weburl . 'cron/priceCron.php?act=priceCron&key=' . $conf['cronkey'] . '" class="btn btn-success btn-xs">访问</a><br>
              &nbsp;&nbsp;&nbsp;
              监控频率：建议3~10分钟<br>
              &nbsp;&nbsp;&nbsp;监控二说明：<span style="color:red">由于商战、亿樂、卡商、直客这些系统有限制，需要单独监控才能保证商品成功同步的效果</span><br>
              &nbsp;&nbsp;&nbsp;监控地址二：' . $weburl . 'cron/priceCronYl.php?act=priceCron&key=' . $conf['cronkey'] . '&nbsp;<a target="_blank" href="' . $weburl . 'cron/priceCronYl.php?act=priceCron&key=' . $conf['cronkey'] . '" class="btn btn-success btn-xs">访问</a>（用于商战、亿樂、卡商、直客等）<br> &nbsp;&nbsp;
              监控频率：建议1~2分钟
              <br>
              <br>&nbsp;&nbsp;&nbsp;&nbsp;通用监控状态：' . (stripos($conf['pricejk_result'], '正常') !== false ? '<font color="green">' . $conf['pricejk_result'] . '</font>' : '<font color="#f7641b">异常或未监控</font>') . '
              <br>&nbsp;&nbsp;&nbsp;&nbsp;亿樂等监控状态：' . (stripos($conf['pricejk_result_yile'], '正常') !== false ? '<font color="green">' . $conf['pricejk_result_yile'] . '</font>' : '<font color="#f7641b">异常或未监控</font>') . '
              <br>&nbsp;&nbsp;&nbsp;&nbsp;状态说明：显示正常只表明监控状态正常，由于货源平台众多不代表就一定成功更新！具体请看监控日志，如果不是宝塔看不了，就自己手动浏览器访问监控地址，看看提示什么错误<br><br>
              四.订单同步，同步部分系统对接订单，目前同系统、彩虹、九五、亿樂、卡商、卡易信、彩虹、同系统、卡卡云、时空云、直客会自动同步订单状态（卡商网选号的不支持）<br>&nbsp;&nbsp;&nbsp;&nbsp;监控地址：' . $weburl . 'cron/kashangCron.php?act=kashangCron&key=' . $conf['cronkey'] . '&nbsp;<a target="_blank" href="' . $weburl . 'cron/kashangCron.php?act=kashangCron&key=' . $conf['cronkey'] . '" class="btn btn-success btn-xs">访问</a><br>&nbsp;&nbsp;&nbsp;&nbsp;监控频率：建议5~30分钟<br><br>
              五.排行奖励，全自动发放分站排行奖励，需要先开启排行奖励<br>&nbsp;&nbsp;&nbsp;&nbsp;监控地址：' . $weburl . 'cron/rankCron.php?act=rankCron&key=' . $conf['cronkey'] . '<br>&nbsp;&nbsp;&nbsp;&nbsp;监控频率：建议每天凌晨0点前几分钟<br><br>
              六.日常清理，日常清理近期无用数据<br>&nbsp;&nbsp;&nbsp;&nbsp;监控地址：' . $weburl . 'cron/dailyCron.php?act=daily&key=' . $conf['cronkey'] . '<br>&nbsp;&nbsp;&nbsp;&nbsp;监控频率：订单很多时监控，建议每天早上6点之前<br><br>
              七.库存监控，库存不足时通知给供货商<br>&nbsp;&nbsp;&nbsp;&nbsp;监控地址：' . $weburl . 'cron/fakaCron.php?act=daily&key=' . $conf['cronkey'] . '<br>&nbsp;&nbsp;&nbsp;&nbsp;监控频率：建议每5~15分钟执行一次<br><br>
              * 更多功能敬请期待···
            </div>
        </div>
      </div>
    </div>';

include_once 'footer.php';
