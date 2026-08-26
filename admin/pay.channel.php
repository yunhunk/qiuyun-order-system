<?php

/**
 * 多接口配置
 **/

use core\Db;

include "../includes/common.php";
$title = '支付多接口配置';

checkLogin();

$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
if ($act == "info") {
    $id = input('id', 1);
    if (!$id) {
        json('ID不能为空');
    }
    $row = Db::name('channel')->get(['id' => $id]);
    if ($row) {
        json_success('成功', [
            'row' => $row,
        ]);
    } else {
        json('该通道不存在 =>' . $id);
    }
} elseif ($act == "change") {
    $id = input('id', 1);
    if (!$id) {
        json('ID不能为空');
    }
    $row = Db::name('channel')->get(['id' => $id]);
    if ($row) {
        $update = Db::name('channel')->where([
            'id' => $row['id'],
        ])->update([
            'status' => $row['status'] == 1 ? 0 : 1,
        ]);

        if ($update !== false) {
            json_success('切换状态成功', [
                'row' => $row,
            ]);
        } else {
            json('切换状态失败, ' . Db::error());
        }
    } else {
        json('该通道不存在 =>' . $id);
    }
} elseif ($act == "del") {
    $id = input('id', 1);
    if (!$id) {
        json('ID不能为空');
    }
    $row = Db::name('channel')->get(['id' => $id]);
    if (!$row) {
        json_success('删除成功', [
            'row' => $row,
        ]);
    } else {
        Db::name('channel')->where(['id' => $id])->delete();
        json_success('删除成功');
    }
} elseif ($act == "add") {

    $type = input('type', 1);

    if (!$type || !in_array($type, ['alipay', 'qqpay', 'wxpay'])) {
        json('支付方式错误');
    }

    $name = input('name', 1);
    if (!$name) {
        json('通道名称不能为空');
    }

    $appurl = input('appurl', 1);
    if (!$appurl) {
        json('支付通道地址不能为空');
    }

    $appid = input('appid', 1);
    if (!$appid) {
        json('支付商户ID不能为空');
    }

    $insert = Db::name('channel')->insert([
        'mode'       => input('mode', 1, 1),
        'type'       => input('type', 1, 1),
        'name'       => input('name', 1, 1),
        'status'     => input('status', 1, 1),
        'appurl'     => input('appurl', 1, 1),
        'appid'      => input('appid', 1, 1),
        'appkey'     => input('appkey', 1, 1),
        'min'        => floatval(input('min', 1, 1)),
        'paymin'     => floatval(input('paymin', 1, 1)),
        'paymax'     => floatval(input('paymax', 1, 1)),
        'addtime'    => $date,
        'updatetime' => $date,
    ]);

    if ($insert > 0) {
        json_success('新增成功', [
            'row' => $row,
        ]);
    } else {
        json('添加失败, ' . Db::error());
    }
} elseif ($act == "edit") {
    $id = input('id', 1);
    if (!$id) {
        json('ID不能为空');
    }

    $row = Db::name('channel')->get(['id' => $id]);
    if (!$row) {
        json('该通道不存在 =>' . $id);
    }

    $update = Db::name('channel')->where([
        'id' => $row['id'],
    ])->update([
        'mode'       => input('mode', 1, 1),
        'type'       => input('type', 1, 1),
        'name'       => input('name', 1, 1),
        'status'     => input('status', 1, 1),
        'appurl'     => input('appurl', 1, 1),
        'appid'      => input('appid', 1, 1),
        'appkey'     => input('appkey', 1, 1),
        'min'        => floatval(input('min', 1, 1)),
        'paymin'     => floatval(input('paymin', 1, 1)),
        'paymax'     => floatval(input('paymax', 1, 1)),
        'updatetime' => $date,
    ]);
    if ($update !== false) {
        json_success('更新成功', [
            'row' => $row,
        ]);
    } else {
        json('更新失败, ' . Db::error());
    }
}

function display_mode($mode = 'all')
{
    switch ($mode) {
        case 'index':
            return '前台游客下单';
            break;
        case 'site_order':
            return '登录用户/分站下单';
            break;
        case 'site_recharge':
            return '登录用户/分站充值';
            break;
        case 'master':
            return '供货商充值';
            break;
        default:
            return '所有场景可用';
            break;
    }
}

include './head.php';

$sql = " 1";

function display_type($type)
{
    switch ($type) {
        case 'qqpay':
            return 'QQ钱包';
            break;
        case 'wxpay':
            return '微信支付';
            break;
        default:
            return '支付宝';
            break;
    }
}
?>

<div class="col-xs-12 col-sm-12 col-lg-12 center-block" style="float: none;padding-top:10px;">
    <!-- 插件测试-->
   <div class="modal fade" id="save_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">添加接口通道</h4>
            </div>
            <div class="modal-body">
					<form id="form-save">
                        <input type="hidden" id="id" name="id" value=""/>
                        <input type="hidden" id="action" name="action" value="add"/>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">接口名称:</div>
                                <input class="form-control" value="" name="name">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">支付类型:</div>
                                <select class="form-control" name="type" id="type" defaut="alipay">
                                    <option value="alipay">支付宝</option>
                                    <option value="qqpay">QQ钱包</option>
                                    <option value="wxpay">微信支付</option>
                                </select>
                            </div>
                            <small>此功能可在不同的支付类型下启用不同的接口, 最大程度避免接口冻结和金额损失</small>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">通道场景:</div>
                                <select class="form-control" name="mode" id="mode" defaut="all">
                                    <option value="all">所有场景(不区分场景)</option>
                                    <option value="index">前台下单</option>
                                    <option value="site_order">登录用户/分站下单</option>
                                    <option value="site_recharge">登录用户/分站充值</option>
                                    <option value="master">供货商充值</option>
                                </select>
                            </div>
                            <small>此功能可在不同的场景下启用不同的接口, 最大程度避免接口冻结和金额损失</small>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">易支付接口地址:</div>
                                <input class="form-control" value="" name="appurl">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">易支付商户PID:</div>
                                <input class="form-control" value="" name="appid">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">易支付商户密钥:</div>
                                <input class="form-control" value="" name="appkey">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">充值/下单启用门槛:</div>
                                <input class="form-control" value="" name="min">
                                <div class="input-group-addon">元</div>
                            </div>
                            <small>设置后分站充值金额、前台/用户下单金额等情况金额大于此值时自动调用该接口</small>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">单笔最小金额:</div>
                                <input class="form-control" value="" name="paymin">
                                <div class="input-group-addon">元</div>
                            </div>
                            <small>填0不限制, 设置大于0后系统自动避开该接口, 如果接口都不满足, 将随机选一个开启的接口</small>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">单笔最大金额:</div>
                                <input class="form-control" value="" name="paymax">
                                <div class="input-group-addon">元</div>
                            </div>
                            <small>填0不限制, 设置大于0后系统自动避开该接口, 如果接口都不满足, 将随机选一个开启的接口</small>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">启用状态:</div>
                                <select class="form-control" name="status" id="status" defaut="1">
                                    <option value="1">启用</option>
                                    <option value="0">禁用</option>
                                </select>
                            </div>
                        </div>
                    </form>
					<div class="form-group">
					    <a id="onSave" class="btn btn-primary btn-block">保存</a>
					    <br>
					    <a class="btn btn-danger btn-block" id="submit" data-dismiss="modal">关闭</a>
					</div>
            </div>
        </div>
    </div>
</div>
    <!-- 插件配置 end -->
 <div class="block">
    <div class="block-title">
      <h3 class="panel-title"><?php echo $title; ?></h3>
    </div>
        <form onsubmit="return searchItem()" method="GET" class="form-inline">
            <a id="add" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;添加支付</a>&nbsp;
            <!-- <div class="form-group">
            <select class="form-control" name="cid" default="' . $cid . '" id="cid">
            <option value="-1">所有分类</option>
            ' . $select . '
            </select>
            </div>
            <div class="form-group">
            <input type="text" class="form-control" name="kw" placeholder="请输入商品名称">
            </div>
            <div class="form-group">
                <select class="form-control" name="active" default="' . $active . '" id="active" placeholder="选择上架状态">
                    <option value="-1">所有状态</option>
                    <option value="1">上架中</option>
                    <option value="0">已下架</option>
                </select>
            </div>
            <button type="submit" class="btn btn-info">搜索</button>&nbsp; -->
        </form>
       <div class="table-responsive">
        <table class="table table-striped" style="word-break: break-all;">
          <thead><th>接口名称</th><th>支付场景</th><th>接口类型</th><th>易支付地址</th><th>商户ID</th><th>单笔最小</th><th>状态</th><th>添加/更新时间</th><th>操作</th></thead>
          <tbody>
<?php
$numrows  = $DB->count("SELECT count(*) from `pre_channel` WHERE " . $sql);
$pagesize = 30;
$pages    = intval($numrows / $pagesize);
if ($numrows % $pagesize) {
    $pages++;
}
if (isset($_GET['page'])) {
    $page = intval($_GET['page']);
} else {
    $page = 1;
}
$offset = $pagesize * ($page - 1);

$rs = $DB->query("SELECT * FROM `pre_channel` WHERE {$sql} order by id desc limit $offset,$pagesize");

while ($res = $DB->fetch($rs)) {
    ?>
    <tr>
        <td><?php echo $res['name']; ?></td>
        <td><?php echo display_mode($res['mode']); ?></td>
        <td><?php echo display_type($res['type']); ?></td>
        <td><?php echo $res['appurl']; ?></td>
        <td><?php echo $res['appid']; ?></td>
        <td><?php echo $res['paymin']; ?>元</td>
        <td>
            <span id="change" title="点击切换"  data-id="<?php echo $res['id']; ?>" class="btn btn-<?php echo $res['status'] == 1 ? 'success' : 'danger' ?> btn-xs"><?php echo $res['status'] == 1 ? '启用' : '停用' ?></span>
        </td>
        <td>
            <p><?php echo $res['addtime']; ?></p>
            <p><?php echo $res['updatetime']; ?></p>
        </td>
        <td>
            <span id="onEditOpen" title="点击编辑接口"  data-id="<?php echo $res['id']; ?>" class="btn btn-success btn-xs">编辑</span>
            <span id="onDel" title="点击删除接口" data-id="<?php echo $res['id']; ?>" class="btn btn-primary btn-xs">删除</span>
        </td>
    </tr>
<?php
}

echo ' </tbody>
        </table>
      </div>';

echo '</div>';
?>
<script>
$(document).on('click', '#add', function () {
    // 添加
    $("#myModalLabel").html('添加支付通道');
    $("#save_modal").modal('show');
    $("#action").val('add');
});

$(document).on('click', '#onEditOpen', function () {
    // 编辑
    var id = $(this).data('id');
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "?act=info",
        data: {id: id},
        dataType: 'json',
        success: function (res) {
            layer.close(ii);
            if (res.code == 0) {
                $("#id").val(id);
                $("#action").val('edit');
                $("#save_modal").modal('show');
                $("#myModalLabel").html('编辑支付通道“<b>' + res.data.row.name + '</b>”');
                $('#save_modal').on('shown.bs.modal', function () {
                    $("[name=mode]").attr('default', res.data.row.mode);
                    $("[name=type]").attr('default', res.data.row.type);
                    $("[name=status]").attr('default', res.data.row.status);
                    $("[name=name]").val(res.data.row.name);
                    $("[name=min]").val(res.data.row.min);
                    $("[name=paymin]").val(res.data.row.paymin);
                    $("[name=appurl]").val(res.data.row.appurl);
                    $("[name=appid]").val(res.data.row.appid);
                    $("[name=appkey]").val(res.data.row.appkey);
                    $("[name=paymax]").val(res.data.row.paymax);
                    selectRender();
                })
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


$(document).on('click', '#onSave', function () {
    // 保存
    var postArr =  $("#form-save").serializeArray();
    var post = {};
    $.each(postArr, function (indexInArray, valueOfElement) {
        post[valueOfElement.name] = valueOfElement.value
    });
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    var action =  $("#action").val();
    var id =  $("#id").val();
    $.ajax({
        type: "POST",
        url: "?act=" + action,
        data:  Object.assign(post, { id: id}) ,
        dataType: 'json',
        success: function (res) {
            layer.close(ii);
            if (res.code == 0) {
                layer.msg(res.msg);
                $("#save_modal").modal('hide');
                window.location.reload();
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

$(document).on('click', '#change',  function () {
    var ii = layer.load(2, {shade:[0.1,'#fff']});
    var id = $(this).data('id');
    $.ajax({
        type : 'GET',
        url : '?act=change&id='+id,
        dataType : 'json',
        success : function(data) {
            layer.close(ii);
            if (data.code ==0) {
                layer.msg(data.msg);
                window.location.reload();
            }else {
                layer.alert(res.msg);
            }
        },
        error:function(data){
            layer.close(ii);
            layer.msg('服务器错误');
            return false;
        }
    });
});

$(document).on('click', '#onDel', function () {
    // 保存插件配置
    var id = $(this).data('id');
    del(id);
});

function del(id){
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "?act=del",
        data: {id: id},
        dataType: 'json',
        async: true,
        success: function (res) {
            layer.close(ii);
            if (res.code == 0) {
                layer.msg(res.msg);
                $("#tr_"+ id).remove();
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
}


function selectRender(){
    var items = $("select[default]");
    for (var i = 0; i < items.length; i++) {
        $(items[i]).val($(items[i]).attr("default") || 0);
    }
}
</script>
<?php include 'footer.php';?>