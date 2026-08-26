
<?php

/**
 * 商品库存告急
 **/

use core\Db;

include "../includes/common.php";
$title = '商品库存告急';
checkLogin();

$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
if ($act == 'delete') {
    $id  = intval(input('id', 1));
    $sql = "DELETE from `pre_tools` where `tid`='" . $id . "'";
    if ($DB->query($sql)) {
        $result = array('code' => 0, 'msg' => "删除商品" . $zid . "成功！");
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

function display_stock($row = [])
{
    if ($row['is_curl'] == 4) {
        $stock = $row['stock'];
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
                return '<font color="#ff9800">' . $stock . '<font>';
            } else {
                return '<font color="red">' . $stock . '<font>';
            }
        }
    }

    return '<font color="blue">-<font>';
}
?>

<div class="col-xs-12 col-sm-12 col-lg-12 center-block" style="float: none;padding-top:10px;">
<?php

$lasttime         = time() - 86400;
$notify_stock_num = intval($conf['notify_stock_num']) > 0 ? intval($conf['notify_stock_num']) : 0;
if (isset($_GET['zid']) && $_GET['zid'] > 0) {
    $sql = " `active`=1 AND `condition` = 1 and `close`=0 AND `stock`<={$notify_stock_num} AND `stock_time`>={$lasttime} AND `zid`=" . input('zid');
    //$sql  = " `active`=1 AND `condition` = 1 and `stock`<={$notify_stock_num} AND `stock_time`>={$lasttime} AND `zid`=" . input('zid');
    $link = '&zid=' . input('zid');
} elseif (isset($_GET['kw']) && $_GET['kw']) {
    $kw = input('kw');
    // $sql = " `active`=1 AND `condition` = 1 and (name LIKE '%{$kw}%' OR `zid`={$kw})  AND `stock`<={$notify_stock_num} AND `stock_time`>={$lasttime}";
    $sql  = " `active`=1 AND `condition` = 1 and `close`=0 AND  (name LIKE '%{$kw}%' OR `zid`={$kw})  AND `stock`<={$notify_stock_num} `stock_time`>={$lasttime}";
    $link = '&zid=' . input('zid');
} else {
    //$sql = " `active`=1  AND `condition` = 1 and `stock`<={$notify_stock_num} AND `stock_time`>={$lasttime}";
    $sql = " `active`=1 AND `condition` = 1 and `close`=0  AND `stock`<={$notify_stock_num} AND  `stock_time`>={$lasttime}";
}

$sql .= " AND `is_curl`!=2";

$numrows  = $DB->count("SELECT count(*) from `cmy_tools` WHERE {$sql}");
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

$runSql = "SELECT tid,`is_curl`,`name`,`active`,zid,stock,stock_time,stock_open FROM `cmy_tools` WHERE {$sql} order by `stock` desc limit $offset,$pagesize";
$rs     = $DB->query($runSql);
?>
 <div class="block">
    <div class="block-title">
      <h3 class="panel-title"><?php echo $title; ?></h3>
    </div>
        <!-- <pre>执行SQL: <code><?php echo $runSql; ?></code> </pre> -->
       <div class="alert alert-info">系统共有 <b><?php echo $numrows; ?></b> 个商品库存告急, (备注: 预警只检测已上架且未隐藏的所有自营和供货类型商品)<br/>
        </div>
        <form onsubmit="return searchItem()" method="GET" class="form-inline">
            <div class="form-group">
                <input type="text" class="form-control" style="width:200px;" name="kw" placeholder="请输入商品名称、供货商ID">
            </div>
            <button type="submit" class="btn btn-success">搜索</button>&nbsp;
            <a href="./shopstock.php" class="btn btn-default" title="刷新商品列表"><i class="fa fa-refresh"></i></a>
        </form>
        <div class="table-responsive">
        <table class="table table-striped" style="word-break: break-all;margin-bottom: 135px;">
          <thead><th>TID</th><th>商品名称</th><th>商品归属</th><th>上架状态</th><th>联系方式</th><th>剩余库存</th><th>操作</th></thead>
          <tbody>
<?php

while ($res = $DB->fetch($rs)) {
    $userInfo = null;
    if ($res['zid'] > 0) {
        $userInfo = Db::name('master')->get(['zid' => $res['zid']]);
    }

    ?>
    <tr id="tr_<?php echo $res['tid']; ?>">
        <td><div class="checkbox-inline checkbox-md"><input class="" type="checkbox" name="checkbox[]" id="list1" value="<?php echo $res['tid']; ?>"><b><?php echo $res['tid']; ?></b></div></td>
        <td><?php echo $res['name']; ?></td>
        <td><?php echo $res['zid'] > 0 ? '<a class="a-blur" href="./master.user.php?zid=' . $res['zid'] . '">供货商ID: ' . $res['zid'] . '</a>' : "主站商品"; ?></td>
        <td><span  class="btn btn-<?php echo $res['active'] == 1 ? 'success' : 'warning' ?> btn-xs"><?php echo $res['active'] == 1 ? '上架中' : '已下架' ?></span></td>
        <td><a class="showMasterInfo a-blur" data-tid="<?=$res['tid']?>" data-zid="<?=$res['zid']?>"  data-email="<?php echo $userInfo ? $userInfo['email'] : '' ?>"  data-qq="<?php echo $userInfo ? $userInfo['qq'] : '' ?>" data-wechat="<?php echo $userInfo ? $userInfo['wechat'] : '' ?>" data-tel="<?php echo $userInfo ? $userInfo['tel'] : '' ?>"  class="btn btn-info btn-xs">联系方式</a></td>
        <td><?php echo display_stock($res); ?></td>
        <td>
            <?php if ($res['is_curl'] == 4): ?>
            <a href="./fakakms.php?my=add&tid=<?=$res['tid']?>"  class="btn btn-warning btn-xs">去加卡</a>
            <?php else: ?>
            <a href="./shopedit.php?my=edit&tid=<?=$res['tid']?>"  class="btn btn-danger btn-xs">去修改</a>
            <?php endif;?>
        </td>
    </tr>
<?php
}

echo ' </tbody>
        </table>
      </div>';

#分页
$pageList = new \core\Page($numrows, $pagesize, 0, $link);
echo $pageList->showPage();

echo '</div>';
?>

<script>
"use strict";

$(document).ready(function () {
    $(document).on('click', '.showMasterInfo',  function () {
        var email = $(this).data('email');
        var zid = $(this).data('zid');
        var qq = $(this).data('qq');
        var tel = $(this).data('tel');
        var wechat = $(this).data('wechat');
        if (zid < 1) {
            layer.msg('主站商品无联系方式');
        }
        else{
            layer.alert('<div style="padding:12px 8px"><ul class="list-group no-radius"><li class="list-group-item"><b>邮箱:</b>'+ email +'</li><li class="list-group-item"><b>QQ:</b>'+ qq +'</li><li class="list-group-item"><b>微信:</b>'+ wechat +'</li><li class="list-group-item"><b>手机号:</b>'+ tel +'</li></ul></div>', {
            skin: 'layui-layer-lan',
            title:'查看供货商<b>' + zid + '</b>的联系方式'
        })
        }
    });
});

function selectRender(){
    var items = $("select[default]");
    for (var i = 0; i < items.length; i++) {
        $(items[i]).val($(items[i]).attr("default") || 0);
    }
}
</script>

<?php include 'footer.php';?>