<?php
/**
 *  Name  供货系统设置
 *  Auhor 星河
 */

use core\Db;

include '../includes/common.php';
$title = '供货系统设置';
checkLogin();
$act = isset($_GET['act']) ? daddslashes(input('get.act')) : null;

if ($act == 'getPridList') {
    $pridSelect = getPridList();
    exit(json_encode(['code' => 0, 'html' => $pridSelect]));
} elseif ($act == 'onSaveSet') {
    if (!$_POST['do'] == 'submit') {
        exit(json_encode(['code' => -1, 'msg' => '请通过页面保存提交数据！']));
    } else {
        foreach ($_POST as $key => $value) {
            if ($key != 'submit' && $key != 'do') {
                // $value = strFilter($value);
                saveSetting($key, $value);
            }
        }
    }
    $ad = $CACHE->clear();
    if ($ad) {
        exit(json_encode(['code' => 0, 'msg' => '设置修改成功', 'data' => $_POST]));
    } else {
        exit(json_encode(['code' => -1, 'msg' => '设置修改失败, ' . $DB->error()]));
    }
}

if (!function_exists('getPridList')) {
    function getPridList()
    {
        $pridSelect = '';
        $pridRs     = Db::name('price')->select();
        foreach ($pridRs as $key => $value) {
            $pridSelect .= '<option value="' . $value['id'] . '">' . $value['name'] . '</option>';
        }
        return $pridSelect;
    }
}
$pridSelect = getPridList();

checkAuthority('sets');

include './head.php';

$editor_load = true;
?>

<div class="col-xs-12 col-sm-12 col-lg-12 center-block" style="float: none;padding-top:10px;">
    <div class="block">
        <div class="block-title"><h3 class="panel-title">供货系统设置</h3></div>
        <div class="">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#default" data-toggle="tab">基础设置</a></li>
            <li class=""><a href="#tixian" data-toggle="tab">提现设置</a></li>
            <li><a href="#gongao" data-toggle="tab">公告设置</a></li>
            <li><a href="#notify1" data-toggle="tab">通知设置</a></li>
            <li><a href="#authority" data-toggle="tab">权限设置</a></li>
        </ul>
        <div id="myTabContent" class="tab-content" style="padding-top: 10px;">
            <div class="tab-pane fade in active" id="default">
                <form id="form-set1" class="form-horizontal" role="form">
                    <input type="hidden" name="do" value="submit"/>
                    <div class="input-group">
                        <div class="input-group-addon">供货系统总开关:</div>
                        <select class="form-control" name="master_open" default="<?php echo $conf['master_open'] ?>">
                        <option value="0" >0_关闭</option>
                        <option value="1">1_开启</option>
                        </select>
                    </div>
                    <pre>关闭后供货系统所有供货商户都无法在操作使用</pre>
                    <br/>
                    <div class="input-group">
                        <div class="input-group-addon">供货商可用登录方式:</div>
                        <select class="form-control" name="master_login_type" default="<?php echo $conf['master_login_type'] ?>">
                        <option value="0" >0_账号邮箱+手机号登录(默认)</option>
                        <option value="1">1_账号邮箱登录</option>
                        <option value="2">2_手机号登录</option>
                        </select>
                    </div>
                    <pre>没有配置好短信功能时可不选手机号登录方式, 邮箱或者手机号验证开关在主站设置->邮箱/短信验证设置</pre>
                    <br/>
                    <div class="input-group">
                        <div class="input-group-addon">供货商可用注册方式:</div>
                        <select class="form-control" name="master_register_type" default="<?php echo $conf['master_register_type'] ?>">
                        <option value="0" >0_账号邮箱+手机号注册(默认)</option>
                        <option value="1">1_账号邮箱注册</option>
                        <option value="2">2_手机号注册</option>
                        </select>
                    </div>
                    <pre>没有配置好短信功能时可不选手机号登录方式, 邮箱或者手机号验证开关在主站设置->邮箱/短信验证设置</pre>
                    <br/>
                    <div class="input-group">
                        <div class="input-group-addon">商品可点赞踩开关:</div>
                        <select class="form-control" name="master_goods_like" default="<?php echo $conf['master_goods_like'] ?>">
                        <option value="0" >0_关闭</option>
                        <option value="1">1_开启</option>
                        </select>
                    </div>
                    <pre>开启后商品下单页面会多一个点赞和踩的区域模块, 该操作需要登录</pre>
                    <br/>
                    <div class="input-group">
                        <div class="input-group-addon">供货入住保证金:</div>
                        <input  name="master_price" value="<?php echo $conf['master_price']; ?>" type="text" placeholder="入住供货商必须缴纳当前设置的保证金，保证金冻结在押金余额" class="form-control">
                        <div class="input-group-addon">
                            元
                        </div>
                    </div>
                    <br/>
                    <div class="input-group">
                        <div class="input-group-addon">免审核上架商品:</div>
                        <select class="form-control" name="master_vip_goods" default="<?php echo $conf['master_vip_goods'] ?>">
                        <option value="0" >0_关闭</option>
                        <option value="1">1_开启</option>
                        </select>
                    </div>
                    <br/>
                    <div class="input-group">
                        <div class="input-group-addon">免审核押金门槛:</div>
                        <input  name="master_vip_price" value="<?php echo $conf['master_vip_price']; ?>" type="text" placeholder="当供货商的押金大于此金额值时, 才会免审核" class="form-control">
                        <div class="input-group-addon">
                            元
                        </div>
                    </div>
                    <br/>
                    <div class="input-group">
                        <div class="input-group-addon">全站库存预警开关:</div>
                        <select class="form-control" name="notify_stock_open" default="<?php echo $conf['notify_stock_open'] ?>">
                        <option value="0">0_关闭</option>
                        <option value="1">1_开启|后台首页弹窗+邮件通知</option>
                        <option value="2">2_开启|仅邮件通知</option>
                        </select>
                    </div>
                    <pre>此开关关闭后供货商商品库存和主站商品库存不足都不会再预警提示</pre>
                    <br/>
                    <div class="input-group">
                        <div class="input-group-addon">全站库存预警数量:</div>
                        <input  name="notify_stock_num" value="<?php echo $conf['notify_stock_num']; ?>" type="text" placeholder="全站库存预警数量" class="form-control">
                        <div class="input-group-addon">
                            个
                        </div>
                    </div>
                    <pre>小于等于此数量时则会在后台预警, 如果挂了监控则会发送通知, 当前频率为<?php echo intval($conf['master_kucun_notify_time'] > 0 ? $conf['master_kucun_notify_time'] : 6); ?>小时/次<br/>邮件通知监控链接: <a href="<?php echo $weburl; ?>cron/fakaCron.php?act=daily&key=<?php echo $conf['cronkey']; ?>" target="_blank"><?php echo $weburl; ?>cron/fakaCron.php?act=daily&key=<?php echo $conf['cronkey']; ?></a> </pre>
                    <br/>
                    <div class="input-group">
                        <div class="input-group-addon">默认加价模板:</div>
                        <select class="form-control" name="master_vip_prid" default="<?php echo $conf['master_vip_prid'] ?>">
                        <?php echo $pridSelect; ?>
                        </select>
                        <div class="input-group-btn">
                            <a id="pridRf" type="button" class="btn btn-primary">刷新</a>
                        </div>
                        <div class="input-group-btn">
                            <a href="./price.php" target="_blank" class="btn btn-primary">管理</a>
                        </div>
                    </div>
                    <br/>
                    <div class="input-group">
                        <div class="input-group-addon">供货商有投诉开启扣除余额:</div>
                        <select class="form-control" name="master_tousu_rmb_remove" default="<?php echo $conf['master_tousu_rmb_remove'] ?>">
                            <option value="0" >0_关闭</option>
                            <option value="1">1_开启</option>
                        </select>
                    </div>
                    <br/>
                    <div class="input-group">
                        <div class="input-group-addon">0库存商品自动下架天数:</div>
                        <input  name="master_nostock_close_day" value="<?php echo intval($conf['master_nostock_close_day']); ?>" type="text" placeholder="当供货商的押金大于此金额值时, 才会免审核" class="form-control">
                        <div class="input-group-addon">
                            元
                        </div>
                    </div>
                    <pre>当商品卡密库存为0时且超过此值多少天没加卡时, 商品则自动下架</pre>
                    <br/>
                    <div class="form-group">
                        <div class="col-sm-offset-0 col-sm-12">
                            <input id="onSaveSet" data-id="1" value="保存设置" class="btn btn-primary form-control"/><br/>
                        </div>
                    </div>
                </form>
            </div>
            <div class="tab-pane fade " id="tixian">
                <form id="form-set3" class="form-horizontal" role="form">
                    <input type="hidden" name="do" value="submit"/>
                    <div class="input-group">
                        <div class="input-group-addon">供货商提现开关:</div>
                        <select class="form-control" name="master_tixian_open" default="<?php echo $conf['master_tixian_open'] ?>">
                        <option value="0" >0_关闭</option>
                        <option value="1">1_开启</option>
                        </select>
                    </div>
                    <pre>关闭后无法申请提现</pre>
                    <br/>
                    <div class="input-group">
                        <div class="input-group-addon">提现到账费率:</div>
                        <input  name="master_tixian_rate" value="<?php echo $conf['master_tixian_rate']; ?>" type="text" placeholder="提现到账费率" class="form-control">
                        <div class="input-group-addon">
                            %
                        </div>
                    </div>
                    <pre>支持小数后2位。例如提现2.5%的手续费, 那么就填97.5即可</pre>
                    <br/>
                    <div class="form-group">
                        <div class="col-sm-offset-0 col-sm-12">
                            <input id="onSaveSet" data-id="3" value="保存设置" class="btn btn-primary form-control"/><br/>
                        </div>
                    </div>
                </form>
            </div>
            <div class="tab-pane fade" id="gongao">
                <form id="form-set2" class="form-horizontal" role="form">
                    <input type="hidden" name="do" value="submit"/>
                    <div class="input-group">
                        <div class="input-group-addon">供货商后台首页弹窗公告:</div>
                        <div class="" id="editorBox"></div>
                        <textarea class="form-control hide textDom" name="master_gongao_alert" rows="3" placeholder="供货商后台首页弹窗公告, 支持HTML代码"><?php echo $conf['master_gongao_alert']; ?></textarea>
                    </div>
                    <br/>
                    <div class="input-group">
                        <div class="input-group-addon">供货商后台首页公告:</div>
                        <div class="" id="editorBox2"></div>
                        <textarea class="form-control hide textDom2" name="master_gongao_info" rows="3" placeholder="供货商后台首页公告, 支持HTML代码"><?php echo $conf['master_gongao_info']; ?></textarea>
                    </div>
                    <br/>
                    <div class="input-group">
                        <div class="input-group-addon">供货商在线充值公告:</div>
                        <div class="" id="editorBox3"></div>
                        <textarea class="form-control hide textDom3" name="master_gongao_recharge" rows="3" placeholder="供货商在线充值公告, 支持HTML代码"><?php echo $conf['master_gongao_recharge']; ?></textarea>
                    </div>
                    <br/>

                    <div class="form-group">
                        <div class="col-sm-offset-0 col-sm-12">
                            <input id="onSaveSet" data-id="2" value="保存设置" class="btn btn-primary form-control"/><br/>
                        </div>
                    </div>
                </form>
            </div>
            <div class="tab-pane fade" id="notify1">
                <form id="form-set4" class="form-horizontal" role="form">
                    <input type="hidden" name="do" value="submit"/>
                    <div class="input-group">
                        <div class="input-group-addon">供货商工单邮箱通知:</div>
                        <select class="form-control" name="master_notify_workorder_email" default="<?php echo $conf['master_notify_workorder_email'] ?>">
                        <option value="0" >0_关闭</option>
                        <option value="1">1_开启</option>
                        </select>
                    </div>
                    <pre>开启后有供货商所属商品的工单时, 会自动邮箱通知到供货商</pre>
                    <br/>
                    <div class="input-group">
                        <div class="input-group-addon">提现通知管理员:</div>
                        <select class="form-control" name="master_notify_tixian" default="<?php echo $conf['master_notify_tixian'] ?>">
                        <option value="0" >0_关闭</option>
                        <option value="1">1_邮件通知</option>
                        <option value="2">2_短信通知</option>
                        </select>
                    </div>
                    <pre>建议使用邮件通知</pre>
                    <div class="input-group">
                        <div class="input-group-addon">上架/编辑商品邮件通知:</div>
                        <select class="form-control" name="master_notify_goods_email" default="<?php echo $conf['master_notify_goods_email'] ?>">
                            <option value="0" >0_关闭</option>
                            <option value="1">1_开启(免审核不通知)</option>
                            <option value="2">2_开启(免审核也通知)</option>
                        </select>
                    </div>
                    <pre>开启后有供货商上架/编辑商品需要审核时, 邮件通知管理员</pre>
                    <br/>
                    <div class="input-group">
                        <div class="input-group-addon">卡密库存不足通知供货商:</div>
                        <select class="form-control" name="master_kucun_notify_open" default="<?php echo $conf['master_kucun_notify_open'] ?>">
                        <option value="0" >0_关闭</option>
                        <option value="1">1_开启</option>
                        </select>
                    </div>
                    <pre>开启后会优先尝试使用邮件通知，邮件通知失败系统则自动尝试手机短信通知</pre>
                    <br/>
                    <div class="input-group">
                        <div class="input-group-addon">卡密库存不足通知频率:</div>
                        <input  name="master_kucun_notify_time" value="<?php echo $conf['master_kucun_notify_time']; ?>" type="text" placeholder="卡密库存不足通知频率" class="form-control">
                        <div class="input-group-addon">小时/次</div>
                    </div>
                    <br/>
                    <div class="form-group">
                        <div class="col-sm-offset-0 col-sm-12">
                            <input id="onSaveSet" data-id="4" value="保存设置" class="btn btn-primary form-control"/><br/>
                        </div>
                    </div>
                </form>
            </div>
            <div class="tab-pane fade" id="authority">
                <form id="form-set5" class="form-horizontal" role="form">
                    <input type="hidden" name="do" value="submit"/>
                    <div class="input-group">
                        <div class="input-group-addon">供货商删除商品:</div>
                        <select class="form-control" name="master_auth_delete_order" default="<?php echo $conf['master_auth_delete_order'] ?>">
                        <option value="0" >0_关闭权限</option>
                        <option value="1">1_开启权限</option>
                        </select>
                    </div>
                    <pre>可在一定程度上避免供货商删除商品跑路</pre>
                    <br/>
                    <div class="form-group">
                        <div class="col-sm-offset-0 col-sm-12">
                            <input id="onSaveSet" data-id="5" value="保存设置" class="btn btn-primary form-control"/><br/>
                        </div>
                    </div>
                </form>
            </div>
        </div>
      </div>
    </div>
</div>
<script>
$(document).on('click', '#onSaveSet', function () {
    // 保存插件配置
    var formId = $(this).data('id');
    var postArr =  $("#form-set" + formId).serializeArray();
    var post = {};
    $.each(postArr, function (indexInArray, valueOfElement) {
        post[valueOfElement.name] = valueOfElement.value
    });
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "?act=onSaveSet",
        data: post,
        dataType: 'json',
        success: function (res) {
            layer.close(ii);
            if (res.code == 0) {
                layer.msg(res.msg);
            } else {
                layer.alert(res.msg);
            }
            return;
        },
        error: function (data) {
            layer.close(ii);
            layer.msg('服务器错误。请稍后再试');
            return false;
        }
    });
});

$(document).on('click', '#pridRf', function () {
    // 保存插件配置
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "?act=getPridList",
        dataType: 'json',
        success: function (res) {
            layer.close(ii);
            if (res.code == 0) {
                $('[name="master_vip_prid"]').empty().append(res.html);
            } else {
                layer.alert(res.msg);
            }
            return;
        },
        error: function (data) {
            layer.close(ii);
            layer.msg('服务器错误。请稍后再试');
            return false;
        }
    });
});

function selectRender(){
    var items = $("select[default]");
    for (var i = 0; i < items.length; i++) {
        $(items[i]).val($(items[i]).attr("default") || 0);
    }
}

 selectRender();
</script>

<?php include 'footer.php';?>