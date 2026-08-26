<?php
/**
 * 系统数据清理
 **/
include "../includes/common.php";
$title = '系统数据清理';
include './head.php';
checkLogin();
?>
    <div class="col-xs-12 col-sm-12" style="float: none;">
<?php
$mod = isset($_GET['mod']) ? $_GET['mod'] : null;
if ($mod == 'cleancache') {
    $CACHE->clear();
    showmsg('清理系统设置缓存成功!', 1);
} elseif ($mod == 'cleanlog') {
    $DB->query("TRUNCATE TABLE `pre_logs`");
    showmsg('清空社区对接日志成功!', 1);
} elseif ($mod == 'cleanpay') {
    $DB->query("DELETE FROM `pre_pay` WHERE addtime<'" . date("Y-m-d H:i:s", strtotime("-30 days")) . "'");
    $DB->query("DELETE FROM `pre_pay` WHERE addtime<'" . date("Y-m-d H:i:s", strtotime("-3 hours")) . "' and status=0");
    $DB->query("DELETE FROM `pre_cart` WHERE addtime<'" . date("Y-m-d H:i:s", strtotime("-30 days")) . "'");
    $DB->query("DELETE FROM `pre_cart` WHERE addtime<'" . date("Y-m-d H:i:s", strtotime("-12 hours")) . "' and status<2");
    $DB->query("OPTIMIZE TABLE `pre_pay`");
    showmsg('删除30天前支付记录成功!', 1);
} elseif ($mod == 'cleanorders') {
    $DB->query("DELETE FROM `pre_orders` WHERE addtime<'" . date("Y-m-d H:i:s", strtotime("-30 days")) . "'");
    $DB->query("OPTIMIZE TABLE `pre_orders`");
    showmsg('删除30天前订单记录成功!', 1);
} elseif ($mod == 'cleansms') {
    $DB->query("DELETE FROM `pre_sms` WHERE addtime<'" . date("Y-m-d H:i:s", strtotime("-10 days")) . "'");
    $DB->query("OPTIMIZE TABLE `pre_sms`");
    showmsg('删除10天前短信记录成功!', 1);
} elseif ($mod == 'cleanems') {
    $DB->query("DELETE FROM `pre_ems` WHERE addtime<'" . date("Y-m-d H:i:s", strtotime("-10 days")) . "'");
    $DB->query("OPTIMIZE TABLE `pre_ems`");
    showmsg('删除10天前邮件记录成功!', 1);
} elseif ($mod == 'cleanqiandao') {
    $DB->query("DELETE FROM `pre_qiandao` WHERE time<'" . date("Y-m-d H:i:s", strtotime("-30 days")) . "'");
    $DB->query("OPTIMIZE TABLE `pre_qiandao`");
    showmsg('删除30天前签到记录成功!', 1);
} elseif ($mod == 'cleanwork') {
    $DB->query("DELETE FROM `pre_workorder` WHERE addtime<'" . date("Y-m-d H:i:s", strtotime("-30 days")) . "'");
    $DB->query("OPTIMIZE TABLE `pre_workorder`");
    showmsg('删除30天前工单记录成功!', 1);
} elseif ($mod == 'cleanpricejk') {
    $DB->query("DELETE FROM `pre_tools_log_pricejk` WHERE addtime<'" . date("Y-m-d H:i:s", strtotime("-7 days")) . "'");
    $DB->query("OPTIMIZE TABLE `pre_tools_log_pricejk`");
    showmsg('删除7天前价格监控记录成功!', 1);
} elseif ($mod == 'cleanpoints') {
    $DB->query("DELETE FROM `pre_points` WHERE addtime<'" . date("Y-m-d H:i:s", strtotime("-7 days")) . "'");
    $DB->query("OPTIMIZE TABLE `pre_points`");
    showmsg('删除7天前收支明细成功!', 1);
} elseif ($mod == 'cleangift') {
    $DB->query("DELETE FROM `pre_giftlog` WHERE addtime<'" . date("Y-m-d H:i:s", strtotime("-1 days")) . "'");
    $DB->query("OPTIMIZE TABLE `pre_giftlog`");
    showmsg('删除1天前中奖记录成功!', 1);
} elseif ($mod == 'cleaninvite') {
    $DB->query("DELETE FROM `pre_invitelog` WHERE date<'" . date("Y-m-d H:i:s", strtotime("-1 days")) . "'");
    $DB->query("OPTIMIZE TABLE `pre_invitelog`");
    showmsg('删除1天前推广记录成功!', 1);
} elseif ($mod == 'cleanpayi' && $_POST['do'] == 'submit') {
    $days  = intval($_POST['days']);
    $money = daddslashes($_POST['money']);
    if ($days <= 0 || $money == null) {
        showmsg('请确保每项不能为空', 3);
    }

    $DB->query("DELETE FROM `pre_pay` WHERE money<='$money' and addtime<'" . date("Y-m-d H:i:s", strtotime("-{$days} days")) . "'");
    $DB->query("OPTIMIZE TABLE `pre_pay`");
    showmsg('删除支付记录成功!', 1);
} elseif ($mod == 'cleantixian' && $_POST['do'] == 'submit') {
    $days = intval($_POST['days']);
    if ($days <= 0) {
        showmsg('请确保每项不能为空', 3);
    } elseif ($days < 30) {
        showmsg('请确保删除天数不能小于30天', 3);
    }
    $DB->query("DELETE FROM `pre_tixian` WHERE addtime<'" . date("Y-m-d H:i:s", strtotime("-{$days} days")) . "'");
    $DB->query("OPTIMIZE TABLE `pre_tixian`");
    showmsg("删除{$days}天前的提现记录成功!", 1);
} elseif ($mod == 'cleanworkorder' && $_POST['do'] == 'submit') {
    $days = intval($_POST['days']);
    if ($days <= 0) {
        showmsg('请确保每项不能为空', 3);
    } elseif ($days < 10) {
        showmsg('请确保删除天数不能小于10天', 3);
    }
    $DB->query("DELETE FROM `pre_workorder` WHERE addtime<'" . date("Y-m-d H:i:s", strtotime("-{$days} days")) . "'");
    $DB->query("OPTIMIZE TABLE `pre_workorder`");
    showmsg("删除{$days}天前的工单记录成功!", 1);
} elseif ($mod == 'cleanordersi' && $_POST['do'] == 'submit') {
    $days  = intval($_POST['days']);
    $money = daddslashes($_POST['money']);
    if ($days <= 0 || $money == null) {
        showmsg('请确保每项不能为空', 3);
    }

    $DB->query("DELETE FROM `pre_orders` WHERE money<='$money' and addtime<'" . date("Y-m-d H:i:s", strtotime("-{$days} days")) . "'");
    $DB->query("OPTIMIZE TABLE `pre_orders`");
    showmsg('删除订单记录成功!', 1);
} elseif ($mod == 'cleansite' && $_POST['do'] == 'submit') {
    $days  = intval($_POST['days']);
    $money = daddslashes($_POST['money']);
    if ($days <= 0 || $money == null) {
        showmsg('请确保每项不能为空', 3);
    }
    $DB->query("DELETE FROM `pre_site` WHERE `money`<='$money' and addtime<'" . date("Y-m-d H:i:s", strtotime("-{$days} days")) . "' and (lasttime<'" . date("Y-m-d H:i:s", strtotime("-{$days} days")) . "' or lasttime is null)");
    $DB->query("OPTIMIZE TABLE `pre_pay`");
    showmsg('删除分站记录成功!', 1);
} elseif ($mod == 'cleanuser' && $_POST['do'] == 'submit') {
    $days  = intval($_POST['days']);
    $money = daddslashes($_POST['money']);
    if ($days <= 0 || $money == null) {
        showmsg('请确保每项不能为空', 3);
    }

    $DB->query("DELETE FROM `pre_site` WHERE `money`<='$money' and addtime<'" . date("Y-m-d H:i:s", strtotime("-{$days} days")) . "' and (lasttime<'" . date("Y-m-d H:i:s", strtotime("-{$days} days")) . "' or lasttime is null)");
    $DB->query("OPTIMIZE TABLE `pre_pay`");
    showmsg('删除分站记录成功!', 1);
} else {
    ?>
<div class="block">
<div class="block-title"><h3 class="panel-title">系统数据清理</h3></div>
<div class="">
<a href="./clean.php?mod=cleanlog" onclick="return confirm('你确实要清空所有社区对接日志吗？');" class="btn btn-block btn-default">清空社区对接日志</a><br/>
<a href="./clean.php?mod=cleanpay" onclick="return confirm('你确实要删除30天前的支付记录吗？');" class="btn btn-block btn-default">删除30天前支付记录</a><br/>
<a href="./clean.php?mod=cleanorders" onclick="return confirm('你确实要删除30天前的订单记录吗？');" class="btn btn-block btn-default">删除30天前订单记录</a><br/>
<a href="./clean.php?mod=cleanpoints" onclick="return confirm('你确实要删除7天前收支明细吗？');" class="btn btn-block btn-default">删除7天前收支明细</a><br/>
<a href="./clean.php?mod=cleangift" onclick="return confirm('你确实要删除1天前的中奖记录吗？');" class="btn btn-block btn-default">删除1天前中奖记录</a><br/>
<a href="./clean.php?mod=cleaninvite" onclick="return confirm('你确实要删除1天前的推广记录吗？');" class="btn btn-block btn-default">删除1天前推广记录</a><br/>
<a href="./clean.php?mod=cleanqiandao" onclick="return confirm('你确实要删除30天前的签到记录吗？');" class="btn btn-block btn-default">删除30天前签到记录</a><br/>
<a href="./clean.php?mod=cleanwork" onclick="return confirm('你确实要删除30天前的工单记录吗？');" class="btn btn-block btn-default">删除30天前工单记录</a><br/>
<a href="./clean.php?mod=cleanpricejk" onclick="return confirm('你确实要删除7天前价格监控记录吗？');" class="btn btn-block btn-default">删除7天前价格监控记录</a><br/>
<a href="./clean.php?mod=cleansms" onclick="return confirm('你确实要删除10天前短信发送记录？');" class="btn btn-block btn-default">删除10天前短信发送记录</a><br/>
<a href="./clean.php?mod=cleanems" onclick="return confirm('你确实要删除10天前邮件发送记录？');" class="btn btn-block btn-default">删除10天前邮件发送记录</a><br/>
<h4>自定义清理：</h4>
<form action="./clean.php?mod=cleanpayi" method="post" role="form"><input type="hidden" name="do" value="submit"/>
<b>支付记录</b>：<input type="text" name="days" value="" placeholder="天数"/>天前，小于等于<input type="text" name="money" value="" placeholder="金额"/>元的支付记录&nbsp;<input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
</form><br/>
<form action="./clean.php?mod=cleanordersi" method="post" role="form"><input type="hidden" name="do" value="submit"/>
<b>订单记录</b>：<input type="text" name="days" value="" placeholder="天数"/>天前，小于等于<input type="text" name="money" value="" placeholder="金额"/>元的订单记录&nbsp;<input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
</form><br/>
<form action="./clean.php?mod=cleansite" method="post" role="form">
<input type="hidden" name="do" value="submit"/>
<b>分站记录</b>：最后登录时间<input type="text" name="days" value="30" placeholder="天数"/>天前，站点余额小于等于<input type="text" name="money" value="0" placeholder="金额"/>元的分站&nbsp;<input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
</form><br/>
<form action="./clean.php?mod=cleanuser" method="post" role="form">
<input type="hidden" name="do" value="submit"/>
<b>用户记录</b>：最后登录时间<input type="text" name="days" value="30" placeholder="天数"/>天前 用户余额小于等于<input type="text" name="money" value="0" placeholder="金额"/>元的用户&nbsp;<input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
</form><br/>
<form action="./clean.php?mod=cleantixian" method="post" role="form">
    <input type="hidden" name="do" value="submit"/>
<b>提现记录</b>：<input type="text" name="days" value="" placeholder="天数"/>天前的提现记录&nbsp;<input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
</form><br/>
<form action="./clean.php?mod=cleanworkorder" method="post" role="form">
    <input type="hidden" name="do" value="submit"/>
<b>工单记录</b>：<input type="text" name="days" value="" placeholder="天数"/>天前的工单列表记录&nbsp;<input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
</form><br/>
</div>
<div class="panel-footer">
<span class="glyphicon glyphicon-info-sign"></span>
定期清理数据有助于提升网站访问速度
</div>
</div>
<?php
}?>
 </div>
</div>