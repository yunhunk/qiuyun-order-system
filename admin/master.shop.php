
<?php

/**
 * 供货商品管理
 **/

use core\Db;

include "../includes/common.php";
$title = '供货商品管理';
checkLogin();

$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
if ($act == 'delete') {
    $id  = intval(input('id', 1));
    $sql = "DELETE from `pre_tools` where `tid`='" . $id . "'";
    if ($DB->query($sql)) {
        $result = array('code' => 0, 'msg' => "删除商品ID => " . $id . "成功！");
    } else {
        $result = array('code' => -1, 'msg' => '删除商品失败, ' . $DB->error());
    }
    exit(json_encode($result));
} elseif ($act == 'change') {
    $tid    = intval(input('tid', 1));
    $active = intval(input('active', 1));
    $sql    = Db::name('tools')->where(['tid' => $tid])->update(['active' => $active]);
    if ($sql !== false) {
        $result = array('code' => 0, 'msg' => "切换成功！");
    } else {
        $result = array('code' => -1, 'msg' => '切换失败, ' . $DB->error());
    }
    exit(json_encode($result));
}

function display_stock($row = [])
{
    global $DB;
    if ($row['is_curl'] == 4) {
        if ($row['stock_time'] <= time() || $row['stock'] <= 0) {
            $stock = $DB->count("SELECT count(kid) FROM `pre_faka` WHERE tid='" . $row['tid'] . "' and status=0 and orderid<1");
            // 60分钟更新一次
            $stock_time = time() + 3600;
            $DB->exec("UPDATE `pre_tools` SET `stock`='{$stock}',`stock_time`='{$stock_time}' WHERE tid='" . $row['tid'] . "'");
        } else {
            $stock = $row['stock'];
        }
        if ($stock > 10) {
            $color = 'green';
        } elseif ($stock > 5) {
            $color = 'blue';
        } elseif ($stock > 0) {
            $color = '#ff9800';
        } else {
            $color = 'red';
        }
        return '<span style="color:' . $color . ';"><a style="color:' . $color . ';" data-tip="点击给商品加卡" href="./fakakms.php?my=add&tid=' . $row['tid'] . '">' . $stock . '<span>';
    } else {
        $stock_open = $row['stock_open'];
        $stock      = $row['stock'];
        if ($stock_open == 1) {
            if ($stock > 10) {
                return '<font color="green">' . $stock . '<font>';
            } elseif ($stock > 3) {
                return '<font color="blue">' . $stock . '<font>';
            } elseif ($stock > 0) {
                return '<font color="yellow">' . $stock . '<font>';
            } else {
                return '<font color="red">' . $stock . '<font>';
            }
        }
    }

    return '<font color="blue">-<font>';
}

include './head.php';

$rs           = $DB->query("SELECT * FROM pre_class WHERE upcid is null OR upcid=0 order by sort asc");
$select       = '<option value="0">未分类</option>';
$pre_class[0] = '未分类';
while ($res = $DB->fetch($rs)) {
    $pre_class[$res['cid']] = $res['name'];
    $select .= '<option value="' . $res['cid'] . '">' . $res['name'] . '</option>';
    $subClass = $DB->count("SELECT count(*) FROM pre_class WHERE upcid='" . $res['cid'] . "' order by sort asc");
    if ($subClass > 0) {
        $rs2 = $DB->query("SELECT * FROM pre_class WHERE active=1 and upcid='" . $res['cid'] . "' order by sort asc");
        while ($res2 = $DB->fetch($rs2)) {
            $pre_class[$res2['cid']] = $res2['name'];
            $select .= '<option value="' . $res2['cid'] . '">|--' . $res2['name'] . '</option>';
        }
    }
}

$rs          = $DB->query("SELECT * FROM pre_price order by id asc");
$p_arr       = array();
$p_rows      = array();
$i           = 0;
$priceselect = "<option value=\"0\" kind=\"0\">0_不选择模板</option>";
while ($res = $DB->fetch($rs)) {
    $priceselect .= '<option value="' . $res["id"] . '" kind="' . $res["kind"] . '">' . $res["name"] . '（' . $res["p_0"] . '|' . $res["p_1"] . '|' . $res["p_2"] . '）</option>';
    $p_arr[$i]          = $res;
    $p_rows[$res["id"]] = $res;
    $i++;
}

function display_price($row, $prow, $price1 = null)
{
    if ($price1 !== null) {
        if ($row['prid'] > 0) {
            if (is_array($prow) && array_key_exists('kind', $prow)) {
                $p['price'] = sprintf('%.2f', $prow['kind'] == 2 ? $price1 + $price1 * $prow['p_0'] / 100 : $price1 + $prow['p_0']);
                $p['cost']  = sprintf('%.2f', $prow['kind'] == 2 ? $price1 + $price1 * $prow['p_1'] / 100 : $price1 + $prow['p_1']);
                $p['cost2'] = sprintf('%.2f', $prow['kind'] == 2 ? $price1 + $price1 * $prow['p_2'] / 100 : $price1 + $prow['p_2']);
            } else {
                return '<span onclick="getPrice(' . $row['tid'] . ')">' . $row['price'] . '&nbsp;|&nbsp;' . $row['cost'] . '&nbsp;|&nbsp;' . $row['cost2'] . '&nbsp;|&nbsp;<i class="fa fa-exclamation-triangle" title="该加价模板不存在！" style="color:red"><i></span>';
            }

        } else {
            $p['price'] = $row['price'];
            $p['cost']  = $row['cost'];
            $p['cost2'] = $row['cost2'];
        }
        return '<span onclick="getPrice(' . $row['tid'] . ')">' . $p['price'] . '&nbsp;|&nbsp;' . $p['cost'] . '&nbsp;|&nbsp;' . $p['cost2'] . '</span>';
    } else {
        return '<span onclick="getPrice(' . $row['tid'] . ')">' . $row['price'] . '&nbsp;|&nbsp;' . $row['cost'] . '&nbsp;|&nbsp;' . $row['cost2'] . '&nbsp;</span>';
    }
}

if (isset($_GET['zid']) && $_GET['zid']) {
    $sql  = ' `zid`>0 AND `condition` = 1 and zid=' . input('zid');
    $link = '&zid=' . input('zid');
} elseif (isset($_GET['cid']) && $_GET['cid']) {
    $sql  = ' `zid`>0 AND `condition` = 1 and `cid`=' . input('cid');
    $link = '&zid=' . input('zid');
} elseif (isset($_GET['kw']) && $_GET['kw']) {
    $kw = input('kw', 1);
    if (is_numeric($kw)) {
        $sql = ' `zid`>0 AND `condition` = 1 and zid=\'' . intval($kw) . '\'';
    } else {
        $sql = ' `zid`>0 AND `condition` = 1 and name LIKE \'%' . $kw . '%\'';
    }
    $link = '&kw=' . $kw;
} else {
    $sql = " `zid`>0 AND `condition` = 1";
}

$active = -1;
if (isset($_GET['active']) && $_GET['active'] != '') {
    $active = intval(daddslashes($_GET['active']));
    if ($active > -1 && in_array($active, [0, 1])) {
        $sql .= ' AND `active`=' . $active . '';
        $link .= '&active=' . $active;
        $is_page = false;
    }
}

$numrows = $DB->count("SELECT count(*) from `cmy_tools` WHERE " . $sql);

$numrows2 = $DB->count("SELECT count(*) from `cmy_tools` WHERE " . $sql . "  AND `active`=0");
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

?>
<style>
input[type="checkbox"]{
    width: 16px;
    height: 16px;
}
</style>

<div class="col-xs-12 col-sm-12 col-lg-12 center-block" style="float: none;padding-top:10px;">
 <div class="block">
    <div class="block-title">
      <h3 class="panel-title"><?php echo $title; ?></h3>
    </div>
         <div class="alert alert-info">系统共有 <b><?php echo $numrows; ?></b> 个供货商商品,  <b><?php echo $numrows2; ?></b> 个商品已下架<br/>
        </div>
        <form onsubmit="return searchItem()" method="GET" class="form-inline">
            <a href="./shopedit.php?my=add&amp;cid=" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;添加商品</a>&nbsp;
            <div class="form-group">
            <select class="form-control" name="active" default="<?=$active?>" id="active" placeholder="选择上架状态">
                    <option value="-1">所有状态</option>
                    <option value="1">上架中</option>
                    <option value="0">已下架</option>
                </select>
            </div>
            <div class="form-group">
                <input type="text" class="form-control" name="kw" placeholder="商品名称、供货商ID">
            </div>

            <button type="submit" class="btn btn-success">搜索</button>&nbsp;<div class="btn-group">
            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                更多 <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a id="reset_sort" href="javascript:return;">重置商品排序</a></li>
                <li><a id="edit_name" href="javascript:return;">批量替换商品名称</a></li>
                <li><a id="edit_input" href="javascript:return;">批量替换输入框标题</a></li>
            </ul>
            </div>&nbsp;
            <a href="./master.shop.php" class="btn btn-default" title="刷新商品列表"><i class="fa fa-refresh"></i></a>
        </form>
        <form name="form1" id="form1">
        <div class="table-responsive">
        <table class="table table-striped" style="word-break: break-all;">
          <thead><th>TID</th><th>所属供货商</th><th>商品名称</th><th>商品价格设置</th><th>供货商成本价</th><th>所属分类</th><th>库存</th><th>状态</th><th>操作</th></thead>
          <tbody>
<?php

$rs = $DB->query("SELECT * FROM `cmy_tools` WHERE {$sql} order by tid desc limit $offset,$pagesize");

while ($res = $DB->fetch($rs)) {
    $userInfo = Db::name('master')->get(['zid' => $res['zid']]);
    ?>
    <tr id="tr_<?php echo $res['tid']; ?>">
        <td><div class="checkbox-inline checkbox-md"><input class="" type="checkbox" name="checkbox[]" id="list1" value="<?php echo $res['tid']; ?>"><b><?php echo $res['tid']; ?></b></div></td>
        <td><?php echo $res['zid']; ?>(<?php echo $userInfo ? $userInfo['user'] : '商户不存在'; ?>)</td>
        <td><?php echo $res['name']; ?></td>
        <td><?php echo display_price($res, $p_rows[$res['prid']], $res['price1'] > 0 ? $res['price1'] : null); ?></td>
        <td><?php echo $res['price2']; ?></td>
        <td><a href="?cid=<?php echo $res['cid']; ?>"><?php echo $pre_class[$res['cid']]; ?></a> </td>
        <td><?php echo display_stock($res); ?></td>
        <td>
            <span id="change" title="点击切换"  data-tid="<?php echo $res['tid']; ?>" data-active="<?php echo $res['active'] == 1 ? 0 : 1; ?>" class="btn btn-<?php echo $res['active'] == 1 ? 'success' : 'warning' ?> btn-xs"><?php echo $res['active'] == 1 ? '上架中' : '已下架' ?></span>
        <td>
            <a href="./shopedit.php?my=edit&tid=<?php echo $res['tid']; ?>" class="btn btn-success btn-xs">编辑</a>
            <span id="onDel" title="删除"  data-id="<?php echo $res['tid']; ?>" class="btn btn-danger btn-xs">删除</span>
        </td>
    </tr>
<?php
}

echo ' </tbody>
        </table>
      </div>';

if (!checkmobile()) {
    echo '<style>.fllist{}</style>';
    echo '<footer class="navbar-fixed-bottom">
             <div class="paging-navbar">';
} else {
    echo '<div style="display:block;height:50px;"></div>';
    echo '<style>.fllist{max-width:130px}</style>';
    echo '<footer class="navbar-fixed-bottom">
            <div class="paging-navbar"><div style="padding:5px 8px;">';
}
echo '<div class="form-inline" style="margin-bottom:6px;">
                <input type="hidden" name="prid"/>
                <input type="hidden" name="check_val"/>
                <input type="hidden" name="hide_val"/>
                <input type="hidden" name="pay_val"/>
                <input type="hidden" name="result"/>
                <input name="chkAll1" type="checkbox" id="chkAll1" onClick="this.value=check1(this.form.list1)" value="checkbox">&nbsp;反选&nbsp;
                <div class="form-group">
                    <div class="input-group">
                        <select name="aid" class="form-control">
                            <option selected="">
                                批量操作
                            </option>
                            <option value="1">
                                >改为显示
                            </option>
                            <option value="2">
                                >改为隐藏
                            </option>
                            <option value="3">
                                >改为上架中
                            </option>
                            <option value="4">
                                >改为已下架
                            </option>
                            <option value="16">
                                >设置加价模板
                            </option>
                            <option value="5">
                                >删除选中
                            </option>
                            <option value="6">
                                >复制选中
                            </option>
                        </select>
                        <div class="input-group-addon btn btn-info btn-sm"><a onclick="change()" >执行</a></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <select name="cid" class="form-control" class="">
                            <option selected>将选定商品移动到</option>
                            ' . $select . '
                        </select>
                        <div class="input-group-addon btn btn-primary btn-sm">
                            <a onclick="move()">确定移动</a>
                        </div>
                    </div>
                </div>
        </div>
        </div>
        </div>
        </footer>
        ';

#分页
$pageList = new \core\Page($numrows, $pagesize, 0, $link);
echo $pageList->showPage();
echo '</form>';
echo '</div>';
?>

<script>
"use strict";

var pridList = '';

var checkList = [] || new Array();

function check1(field) {
    var checkbox = document.querySelectorAll('#list1');
    for (var i = 0; i < checkbox.length; i++) {
        if (checkbox[i].checked === false) {
            checkbox[i].checked = true;
        } else {
            checkbox[i].checked = false;
        }
    }
}

function getVals() {
    var checkbox = document.getElementsByName('checkbox[]');
    checkList = [];
    for (var i = 0; i < checkbox.length; i++) {
        if (checkbox[i].checked) {
            checkList.push(checkbox[i].value);
        }
    }
}

function getPridList() {
    if ("" != pridList) {
        return pridList;
    } else {
        $.ajax({
            type: 'POST',
            url: 'ajax.php?act=getPridListSelect',
            dataType: 'html',
            success: function (data) {
                pridList = data;
            },
            error: function (data) {
                layer.msg('获取加价模板列表请求超时，请稍后再试！');
            }
        });
        return pridList;
    }
}

function move() {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=shop_move',
        data: $('#form1').serialize(),
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg(data.msg);
                setInterval(() => {
                    window.location.reload();
                }, 500);
            } else {
                layer.alert(data.msg);
            }
        },
        error: function (data) {
            layer.msg('请求超时');
            listTable();
        }
    });
    return false;
}

function change() {
    var aid = parseInt($('select[name=aid]').val());
    var area = [$(window).width() > 640 ? '540px' : '95%', 'auto'];
    if (aid == 10) {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: 'GET',
            url: 'ajax.php?act=getAllPrice',
            dataType: 'json',
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.open({
                        type: 1,
                        area: area,
                        title: '修改加价模板',
                        content: data.data
                    });
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function(data) {
                layer.msg('服务器错误');
                return false;
            }
        });
        return false;
    }
    if (aid == 16 && $("input[name='prid']").val() == '') {
        var laybox = layer.open({
            type: 1,
            area: area,
            title: '批量设置加价模板',
            content: '<div class="panel-body"><div class="form-group"><div class="input-group"><div class="input-group-addon">请选择模板</div><select class="form-control" id="p_prid">' + pridList + '</select></div></div></div>',
            btn: ['确定批量', '取消操作'],
            yes: function(index, layero) {
                var prid = $("#p_prid").val();
                $("input[name='prid']").val(prid);
                change();
            },
            btn2: function() {
                layer.close(laybox);
            }
        });
        return false;
    }
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=shop_change',
        data: $('#form1').serialize(),
        dataType: 'json',
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                listTable();
                layer.alert(data.msg);
            } else {
                layer.alert(data.msg);
            }
        },
        error: function(data) {
            layer.msg('请求超时');
            listTable();
        }
    });
    return false;
}

$(document).on('click', '#change',  function () {
    var tid = $(this).data('tid');
    var active = $(this).data('active');
    var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type : 'GET',
        url : '?act=change&tid='+tid+'&active='+active,
        dataType : 'json',
        success : function(data) {
            layer.close(ii);
            window.location.reload();
        },
        error:function(data){
            layer.close(ii);
            layer.msg('服务器错误');
            return false;
        }
    });
});


$(document).on('click', '#onDel', function () {
    // 删除
    var id = $(this).data('id');
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "?act=delete",
        data: {id: id},
        dataType: 'json',
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
});

function selectRender(){
    var items = $("select[default]");
    for (var i = 0; i < items.length; i++) {
        $(items[i]).val($(items[i]).attr("default") || 0);
    }
}

$(document).ready(function () {
    getPridList();
    selectRender();
});
</script>

<?php include 'footer.php';?>