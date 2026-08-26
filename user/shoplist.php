<?php
/**
 * 商品管理
 **/
include "../includes/common.php";
$title = '商品管理';
if ($isLogin2 == 1) {} else {
    $goto = @getHostUrl();

    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

$act                 = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
$fenzhan_price_class = explode(',', $conf['fenzhan_price_class']);
if ($act == "up_price") {
    $up  = intval($_POST['up']);
    $up2 = intval($_POST['up2']);

    if ($userrow['power'] >= 2 && $up2 >= $up) {
        exit('{"code":-1,"msg":"下级设定价格不能大于等于出售设定价格！"}');
    }

    if ($up <= 0) {
        exit('{"code":-1,"msg":"输入值不正确"}');
    }

    $price_obj = new \core\Price($userrow['zid'], $userrow);
    $price_arr = [];
    $rs        = $DB->query("SELECT * from cmy_tools");
    $a         = floatval($up / 100);
    $a2        = floatval($up2 / 100);
    $data      = [];
    if ($rs) {
        $data = $DB->fetchAll($rs);
    }

    if ($conf['fenzhan_price_open'] == 1) {
        if ($a * 100 >= $conf['fenzhan_price_max']) {
            $a = $conf['fenzhan_price_max'] / 100;
        }

        if ($a2 * 100 >= $conf['fenzhan_price_max']) {
            $a2 = ($conf['fenzhan_price_max'] - 5) / 100;
        }
    }

    $fenzhan_price_class = [];
    if ("" != $conf['fenzhan_price_class']) {
        $fenzhan_price_class = explode(',', $conf['fenzhan_price_class']);
    }

    if ($conf['fenzhan_price_max'] < 1) {
        $conf['fenzhan_price_open'] = 0;
    }
    //只需要记住加价倍数，前台需要时再实时计算，既提升性能又保证价格实时性
    foreach ($data as $row) {
        if ($row['price'] <= 0) {
            continue;
        }

        if ($conf['fenzhan_price_open'] == 1 && in_array($row['cid'], $fenzhan_price_class)) {
            if ($a * 100 > $conf['fenzhan_price_max']) {
                $a = sprintf('%.2f', $conf['fenzhan_price_max'] / 100);
            }

            if ($a2 * 100 > $conf['fenzhan_price_max']) {
                $a2 = sprintf('%.2f', $conf['fenzhan_price_max'] / 100);
            }
        }

        $price_arr[$row['tid']]['up']['price'] = sprintf("%.5f", $a + 1);
        if ($userrow['power'] >= 2) {
            $price_arr[$row['tid']]['up']['cost'] = sprintf("%.5f", $a2 + 1);
        }
    }

    $array_data = @serialize($price_arr);
    $DB->query("update `pre_site` set `price`= ? where zid= ?", [$array_data, $userrow['zid']]);
    exit('{"code":0}');
} elseif ($act == 'action') {
    $tid       = intval($_GET['tid']);
    $price_obj = new \core\Price($userrow['zid'], $userrow);
    $row       = $DB->get_row("select * from cmy_tools where tid= ? limit 1", array($tid));
    $price_obj->setToolInfo($tid, $rows);
    $del   = intval($_GET['action']) == 1 ? 0 : 1;
    $price = $price_obj->getToolPrice($tid);
    $cost  = $userrow['power'] == 1 ? $price_obj->getToolCost($tid) : 0;
    if ($price_obj->setPriceInfo($tid, $del, $price, $cost)) {
        exit('{"code":0,"msg":"状态修改成功！"}');
    } else {
        exit('{"code":-1,"msg":"状态修改失败！' . $DB->error() . '"}');
    }
}

include './head.php';

$background_image = 'http://index-css.skyhost.cn/cdn/zip-img/' . rand(1, 19) . '.jpg!gzipimgw';
?>
<style>
.bg{position: fixed;
    width: 100%;
    background-repeat: no-repeat;
    background-size: cover;
    z-index: -1;
    top: 0;
    left: 0;
    background-position: center 0%;
    height: 100%;
    background-attachment: fixed;
}
</style>
<?php

function get_action($action, $tid)
{
    if ($action == 0) {
        return '<a class="btn btn-success btn-xs" href="JavaScript:setAction(' . $action . ',' . $tid . ')">上架中</a>';
    } else {
        return '<a class="btn btn-warning btn-xs" href="JavaScript:setAction(' . $action . ',' . $tid . ')">已下架</font>';
    }
}
$my = isset($_GET['my']) ? $_GET['my'] : null;
?>
<div class="modal fade col-xs-12 " align="left" id="search2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"><br><br><br><br>
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h5 class="modal-title" id="myModalLabel">商品分类</h5>
      </div>
      <div class="modal-body">
      <form action="shoplist.php" method="GET">
<select name="cid" class="form-control"><?php echo $select ?></select><br/>
<input type="submit" class="btn btn-primary btn-block" value="查看"></form>
</div>

    </div>
  </div>
</div>
<?php
$price_obj = new \core\Price($userrow['zid'], $userrow);

if ($my == 'edit') {
    $tid = intval($_GET['tid']);
    $row = $DB->get_row("select * from cmy_tools where tid= ? limit 1", array($tid));
    $price_obj->setToolInfo($tid, $row);
    echo '<div class="panel panel-primary">
<div class="panel-heading"><h3 class="panel-title">修改商品价格</h3></div>';
    echo '<div class="panel-body">';
    echo '<form action="./shoplist.php?my=edit_submit&tid=' . $tid . '" method="POST">
<div class="form-group">
<label>商品名称:</label><br>
<input type="text" class="form-control" name="name" value="' . $row['name'] . '" disabled>
</div>';
    if ($userrow['power'] == 2) {
        echo '
<div class="form-group">
<label>成本价格:</label><br>
<input type="text" class="form-control" name="cost2" value="' . $price_obj->getBuyPrice($tid) . '" disabled>
</div>
<div class="form-group">
<label>下级分站代理价格:</label><br>
<input type="text" class="form-control" name="cost" value="' . $price_obj->getToolCost($tid) . '">
</div>';
    } else {
        echo '
<div class="form-group">
<label>成本价格:</label><br>
<input type="text" class="form-control" name="cost" value="' . $price_obj->getBuyPrice($tid) . '" disabled>
</div>';
    }

    echo '<div class="form-group">
<label>销售价格:</label><br>
<input type="text" class="form-control" name="price" value="' . $price_obj->getToolPrice($tid) . '">
</div>
<div class="form-group">
<label>是否上架:</label><br>
<select class="form-control" name="del" default="' . $price_obj->getToolDel($tid) . '"><option value="0">1_是</option><option value="1">0_否</option></select>
</div>
<input type="submit" class="btn btn-primary btn-block" value="确定修改"></form>
';
    echo '<br/><a href="./shoplist.php">>>返回商品列表</a>
<script>
var items = $("select[default]");
for (i = 0; i < items.length; i++) {
    $(items[i]).val($(items[i]).attr("default")||0);
}
</script>';
} elseif ($my == 'edit_submit') {
    $tid  = intval($_GET['tid']);
    $rows = $DB->get_row("select * from cmy_tools where tid= ? limit 1", array($tid));
    if (!$rows) {
        showmsg('当前记录不存在！', 3);
    }

    $price_obj->setToolInfo($tid, $rows);
    $price = round(daddslashes($_POST['price']), 2);
    $del   = intval($_POST['del']);
    if (!is_numeric($price) || !preg_match('/^[0-9.]+$/', $price)) {
        showmsg('价格输入不规范', 3);
    }

    $price_arr = @unserialize($userrow['price']);
    if ($userrow['power'] == 2) {
        $cost = round(daddslashes($_POST['cost']), 2);
        if (!is_numeric($cost) || !preg_match('/^[0-9.]+$/', $cost)) {
            showmsg('价格输入不规范', 3);
        }

        $buyPrice = $price_obj->getBuyPrice($tid);
        if ($cost < $buyPrice) {
            showmsg('下级代理价格不能低于成本价格！', 3);
        }
        if ($price < $cost) {
            showmsg('销售价格不能低于下级代理价格！', 3);
        }

        $up = sprintf("%.5f", ($price - $buyPrice) / $buyPrice);
        $up = 1 + $up;

        $up2 = sprintf("%.5f", ($cost - $buyPrice) / $buyPrice);
        $up2 = 1 + $up2;

        $price_arr[$tid]['up']['price'] = $up;
        $price_arr[$tid]['up']['cost']  = $up2;
        if (array_key_exists('price', $price_arr[$tid])) {
            unset($price_arr[$tid]['price']);
        }

        if (array_key_exists('cost', $price_arr[$tid])) {
            unset($price_arr[$tid]['cost']);
        }

        if ($conf['fenzhan_price_open'] == 1) {
            $c = ($price - $buyPrice) * 100;
            if ($c >= $conf['fenzhan_price_max']) {
                showmsg('该商品销售价格不能高于成本价格的' . $c . '%！', 3);
            }
        }
        $price_arr[$tid]['del'] = $del;

    } else {
        $buyPrice = $price_obj->getBuyPrice($tid);
        if ($price < $buyPrice) {
            showmsg('销售价格不能低于成本价格！', 3);
        }

        if ($conf['fenzhan_price_open'] == 1) {
            $c = ($price - $buyPrice) * 100;
            if ($c >= $conf['fenzhan_price_max']) {
                showmsg('该商品销售价格不能高于成本价格的' . $c . '%！', 3);
            }
        }

        $up = sprintf("%.5f", ($price - $buyPrice) / $buyPrice);
        $up = 1 + $up;

        $price_arr[$tid]['up']['price'] = $up;
        if (array_key_exists('price', $price_arr[$tid])) {
            unset($price_arr[$tid]['price']);
        }

        if (array_key_exists('cost', $price_arr[$tid])) {
            unset($price_arr[$tid]['cost']);
        }

        if (array_key_exists('cost', $price_arr[$tid]['up'])) {
            unset($price_arr[$tid]['up']['cost']);
        }

        $price_arr[$tid]['del'] = $del;
    }
    $price_data = @serialize($price_arr);
    if ($DB->query("update cmy_site set price= ? where zid= ?", array($price_data, $userrow['zid']))) {
        showmsg('修改商品成功！<br/><br/><a href="./shoplist.php">>>返回商品列表</a>', 1);
    } else {
        showmsg('修改商品失败！' . $DB->error(), 4);
    }

} elseif ($my == 'reset') {
    if ($DB->query("update cmy_site set price=NULL where zid='{$userrow['zid']}'")) {
        showmsg('重置成功！<br/><br/><a href="./shoplist.php">>>返回商品列表</a>', 1);
    } else {
        showmsg('重置失败！' . $DB->error(), 4);
    }

} else {
    echo '<div class="col-xs-12 center-block" style="padding-top:10px; float: none;">
     <div class="panel panel-primary">
    <div class="panel-heading" style="background: linear-gradient(to right,#14b7ff,#b221ff);">
        <h3 class="panel-title"><font color="#fff">商品价格管理</font></h3>
    </div>';

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
    $offset       = $pagesize * ($page - 1);
    $rs           = $DB->query("SELECT * FROM cmy_class WHERE `upcid`=0 order by sort asc");
    $select       = '<option value="0">所有分类</option>';
    $cmy_class[0] = '未分类';
    while ($res = $DB->fetch($rs)) {
        $cmy_class[$res['cid']] = $res['name'];
        $select .= '<option value="' . $res['cid'] . '">' . $res['name'] . '</option>';
        $subClass = $DB->count("SELECT count(*) FROM cmy_class WHERE upcid='" . $res['cid'] . "' order by sort asc");
        if ($subClass > 0) {
            $rs2 = $DB->query("SELECT * FROM cmy_class WHERE active=1 and upcid='" . $res['cid'] . "' order by sort asc");
            while ($res2 = $DB->fetch($rs2)) {
                $cmy_class[$res2['cid']] = $res2['name'];
                $select .= '<option value="' . $res2['cid'] . '">----' . $res2['name'] . '</option>';
            }
        }
    }

    $Cid = intval(input('get.cid', 1));
    if ($Cid > 0) {
        $numrows = $DB->count("SELECT count(*) from cmy_tools where cid= ? and active=1", array($Cid));
        $con     = '<div class="form-inline"><div class="form-group">系统在当前分类下共有 <b>' . $numrows . '</b> 个商品&nbsp;
    <select class="form-control" onchange="getCid(this.value)" default="' . $Cid . '" name="cid" required>' . $select . '</select></div><div class="form-group">
    &nbsp;<a href="shoplist.php?my=reset" onclick="return confirm(\'是否要重置所有商品价格设定，恢复到最初状态？\');" class="btn btn-warning btn-sm">重置价格设定</a>&nbsp;<a class="btn btn-success btn-sm" href="javascript:void(0)" onclick="up_price()"><span class="glyphicon glyphicon-arrow-up"></span>一键设置价格</a></div>';
        $rs = $DB->query("SELECT * FROM cmy_tools WHERE cid= ? and active=1 order by sort asc limit ?, ?", array($Cid, $offset, $pagesize));
    } else {
        $numrows = $DB->count("SELECT count(*) from cmy_tools where active=1");
        $con     = '<div class="form-inline"><div class="form-group">系统共有 <b>' . $numrows . '</b> 个商品&nbsp;
    <select class="form-control" onchange="getCid(this.value)" default="' . $Cid . '" name="cid" required>' . $select . '</select></div><div class="form-group">
    &nbsp;<a href="shoplist.php?my=reset" onclick="return confirm(\'是否要重置所有商品价格设定，恢复到最初状态？\');" class="btn btn-warning btn-sm">重置价格设定</a>&nbsp;<a class="btn btn-success btn-sm" href="javascript:void(0)" onclick="up_price()"><span class="glyphicon glyphicon-arrow-up"></span>一键设置价格</a></div>';
        $rs = $DB->query("SELECT * FROM cmy_tools WHERE active=1 order by sort asc limit ?, ?", array($offset, $pagesize));
    }
    echo $con;

    ?>
    <div class="alert alert-info">
    价格设置说明：价格修改后系统会自动更改为百分比例设置价格，如果您的成本价上涨，此时售价和设置的下级价格也会改变，避免利润受损
    </div>
       <center><small><font color="#ff0000">手机用户可以左右滑动</font></small></center>
        <div class="table-responsive">
                    <table class="table table-vcenter table-condensed table-striped">
          <thead ><tr ><th style="font-size:14px">名称</th><th style="font-size:14px">成本</th><?php if ($userrow['power'] >= 2) {?><th style="font-size:14px">下级</th><?php }?><th style="font-size:14px">销售</th><th>状态</th><th style="font-size:14px">操作</th></tr></thead>
          <tbody>
    <?php

    while ($res = $DB->fetch($rs)) {
        $price_obj->setToolInfo($res['tid'], $res);
        echo '<tr>
            <td><b>' . $res['name'] . '</td>
            <td><font color="#7D9EC0">' . ($userrow['power'] >= 2 ? $price_obj->getBuyPrice($res['tid']) . '元</font></td><td><font color="#9400D3">' . $price_obj->getSubPriceLv1($res['tid']) : $price_obj->getBuyPrice($res['tid'])) . '元</font></td>
            <td><font color="#FF0000">' . $price_obj->getToolPrice($res['tid']) . '元</font></td>
            <td>' . get_action(($price_obj->getToolDel($res['tid']) ? 1 : 0), $res['tid']) . '</td>
            <td><a href="./shoplist.php?my=edit&tid=' . $res['tid'] . '" class="label label-primary">编辑</a></td>
            </tr>';
    }
    ?>
          </tbody>
        </table>
     </div>
<?php
$PageList = new \core\Page($numrows, $pagesize, 0, $link);
    echo $PageList->showPage();
}
?>
</div>
</div>
</div>
<script>
var cid='<?php echo $_GET['cid'] ? $_GET['cid'] : -1; ?>';
$("select[name='cid']").val(cid);
function getCid(cid){
    if(cid==0-2 || cid==0-1){
        window.location.href='./shoplist.php';
    }
    else{
        window.location.href='?cid='+cid;
    }
}

function up_price() {
    var power = '<?php echo $userrow['power'] ?>';
    var ratio_price = '<?php echo $conf['fz_ratio_price'] ? $conf['fz_ratio_price'] : '20' ?>';
    var ratio_cost ='<?php echo $conf['fz_ratio_cost'] ? $conf['fz_ratio_cost'] : '12' ?>';
    if (power >= 2) {
        var html = '<div class="form-group">' + '<div class="input-group"><div class="input-group-addon">销售价格提升</div>' + '<input name="up" id="up" class="form-control" value="' + ratio_price + '"/>' + '  <span class="input-group-addon">%</span>' + '</div><small style="color:red">填整数，例如填' + ratio_price + '，会在对应的商品拿货价基础上提升' + ratio_price + '%后就是出售价格</small>' + '</div>' + '<div class="form-group">' + '<div class="input-group"><div class="input-group-addon">下级价格提升</div>' + ' <input name="up2" id="up2" class="form-control" value="' + ratio_cost + '"/>' + '  <span class="input-group-addon">%</span>' + '</div><small style="color:red">填整数，例如填' + ratio_cost + '，会在对应的商品拿货价基础上提升' + ratio_cost + '%后就是下级拿货价格</small>' + '</div>';
    } else {
        var html = '<div class="form-group">' + '<div class="input-group"><div class="input-group-addon">销售价格提升</div>' + '<input name="up" id="up" class="form-control" value="' + ratio_price + '"/>' + '</div><small style="color:red">填整数，例如填' + ratio_price + '，会在对应的商品拿货价基础上提升' + ratio_price + '%后就是出售价格</small>' + '</div>';
    }
    var area = [$(window).width() > 520 ? '340px' : '95%', 'auto'];
    layer.open({
        title: '商品价格批量设置',
        area: area,
        content: html,
        btnAlign:'c',
        btn: ['确定', '取消'],
        yes: function(index) {
            up = $("#up").val();
            up2 = $("#up2").val() || 0;
            if (up <= 0) {
                layer.alert("销售价格百分百输入值不正确");
                return false;
            }
            if (power >= 2 && up2 <= 0) {
                layer.alert("下级价格百分百输入值不正确");
                return false;
            }
            layer.closeAll();
            $.ajax({
                type: "post",
                url: "?act=up_price",
                data: {
                    up: up,
                    up2: up2
                },
                dataType: "json",
                success: function(data) {
                    if (data.code == 0) {
                        layer.alert('价格提升成功，刷新即可看到效果', function() {
                            window.location.reload();
                        });
                    } else {
                        layer.alert(data.msg);
                    }
                }
            });
        }
    });
}

function setAction(action,tid){
    var ii=layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type : 'GET',
        url : '?act=action',
        dataType : 'json',
        data : {action:action,tid:tid},
        success : function(data) {
            layer.close(ii);
            if(data.code == 0){
                layer.msg(data.msg);
                setTimeout(function(){
                window.location.reload();
                },1000);
            }
            else{
                layer.alert(data.msg);
            }
        },
        error:function(data){
            layer.msg('服务器错误');
            return false;
        }
    });
}
</script>
<?php include 'footer.php'?>
