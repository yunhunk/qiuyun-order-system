<?php
/**
 * 商品价格密价等级管理
 **/
include "../includes/common.php";
checkLogin();

$title = ' 密价等级管理';

checkAuthority('super');

function getIprice($row)
{
    if (null != $row['iprice']) {
        $html = $row['iprice']['kind'] == 2 ? '+' . $row['iprice']['price'] . '%' : '+' . $row['iprice']['price'] . '元';
        $html .= '<a onclick="setIpirce(' . $row['tid'] . ', \'' . $row['name'] . '\')" class="btn btn-success btn-xs" style="margin-left:5px;">重新设置</a><a onclick="delIpirce(' . $row['tid'] . ')" class="btn btn-danger btn-xs" style="margin-left:5px;">取消密价</a>';
    } else {
        $html = '<a onclick="setIpirce(' . $row['tid'] . ', \'' . $row['name'] . '\')" class="btn btn-success btn-xs">点击设置密价</a>';
    }
    return $html;
}
if (!function_exists('display_type')) {
    function display_type($type)
    {
        if ($type == 1) {
            return '<span class="btn btn-primary btn-xs">累加加价</span>';
        } else {
            return '<span class="btn btn-success btn-xs">百分比加价</span>';
        }
    }
}
if (!function_exists('display_status')) {
    function display_status($status, $id)
    {
        if ($status == 1) {
            return '<a class="btn btn-success btn-xs" href="JavaScript:setStatus(0,' . $id . ')">正常</a>';
        } else {
            return '<a class="btn btn-danger btn-xs" href="JavaScript:setStatus(1,' . $id . ')">禁用</font>';
        }

    }
}

if (!function_exists('display_typeVal')) {
    function display_typeVal($type, $price)
    {
        if ($type == 1) {
            return '+' . $price . '元';
        } else {
            return '+' . $price . '%';
        }
    }
}

$my = isset($_GET['my']) ? $_GET['my'] : null;
if ($my == 'add') {
    $name = trim($_POST['name']);
    $row  = $DB->get_row("SELECT * FROM pre_price_super WHERE name= ? limit 1", [$name]);
    if ($row) {
        exit('{"code":-1,"msg":"已存在相同密价！"}');
    }

    $type = intval(trim($_POST['type']));
    $cost = round($_POST['cost'], 2);
    $bz   = trim(getParams('bz', true));
    if ($name == '') {
        exit('{"code":-1,"msg":"密价名称不能为空"}');
    }
    $sqlData = [":type" => $type, ":name" => $name, ":bz" => $bz, ":cost" => $cost, ":addtime" => $date];
    $sql     = "INSERT INTO `pre_price_super` (`type`,`name`,`bz`,`cost`,`status`,`addtime`) values (:type,:name,:bz,:cost,'1',:addtime)";
    if ($DB->query($sql, $sqlData)) {
        exit('{"code":0,"msg":"添加成功！"}');
    } else {
        exit('{"code":-1,"msg":"添加失败，错误返回=>' . $DB->error() . '"}');
    }
} elseif ($my == 'setStatus') {
    $mid    = intval(getParams('mid', true));
    $status = intval(getParams('status', true));
    $row    = $DB->get_row("SELECT * from `pre_price_super` where mid= ? limit 1", [$mid]);
    if (!$row) {
        exit('{"code":-1,"msg":"当前密价等级不存在！"}');
    }

    $sql = "UPDATE pre_price_super SET `status`=:status where mid=:mid limit 1";
    if ($DB->query($sql, [":status" => $status, ":mid" => $mid])) {
        exit('{"code":0,"msg":"修改成功"}');
    } else {
        exit('{"code":-1,"msg":"修改失败，错误返回=>' . $DB->error() . '","id":"' . $id . '"}');
    }
} else if ($my == 'input') {
    $mid = intval($_GET['mid']);
    $row = $DB->get_row("SELECT * from pre_price_super where mid= ? limit 1", [$mid]);
    if (!$row) {
        exit('{"code":-1,"msg":"当前密价等级不存在！"}');
    }

    $result = array("code" => 0, "msg" => "succ", "data" => $row);
    exit(json_encode($result));
} elseif ($my == 'setToolIpirce') {
    $zid = intval(input('post.zid'));
    $row = $DB->get_row("SELECT * from `pre_site` where zid=:zid limit 1", [":zid" => $zid]);
    if (!$row) {
        exit('{"code":-1,"msg":"当前商品密价所属分站不存在！"}');
    }
    $tid        = intval(input('post.tid', 1));
    $kind       = intval(input('post.kind', 1));
    $price      = input('post.price', 1);
    $iprice_arr = [];
    if (!empty($row['iprice'])) {
        $iprice_arr = @json_decode($row['iprice'], true);
    }
    $tool = $DB->get_row("SELECT * from `pre_tools` where tid=:tid limit 1", [":tid" => $tid]);
    if ($kind == 0 && $tool['price1'] == 0) {
        exit('{"code":-1,"msg":"当前商品成本价为0，不支持百分比加价！"}');
    }
    $iprice_arr[$tid]['kind']  = $kind == 0 ? 2 : 1;
    $iprice_arr[$tid]['price'] = $price;
    $iprice                    = @json_encode($iprice_arr);
    $sql_data                  = [":iprice" => $iprice, ":zid" => $zid];
    $sql                       = "UPDATE `pre_site` SET `iprice`=:iprice where `zid`=:zid";
    if ($DB->query($sql, $sql_data)) {
        exit('{"code":0,"msg":"设置成功"}');
    } else {
        exit('{"code":-1,"msg":"设置密价失败，' . $DB->error() . '"}');
    }
} elseif ($my == 'setToolIpirceByBatch') {
    $zid = intval(input('post.zid'));
    $row = $DB->get_row("SELECT * from `pre_site` where zid=:zid limit 1", [":zid" => $zid]);
    if (!$row) {
        exit('{"code":-1,"msg":"当前商品密价所属分站不存在！"}');
    }
    $tids       = input('post.tids', 1);
    $kind       = intval(input('post.kind', 1));
    $price      = input('post.price', 1);
    $iprice_arr = [];
    if (!empty($row['iprice'])) {
        $iprice_arr = @json_decode($row['iprice'], true);
    }

    !is_array($iprice_arr) && $iprice_arr = [];

    foreach ($tids as $key => $tid) {
        $tool = $DB->get_row("SELECT * from `pre_tools` where tid=:tid limit 1", [":tid" => $tid]);
        if ($kind == 2 && $tool['price1'] == 0) {
            continue;
        }
        $iprice_arr[$tid]['kind']  = $kind;
        $iprice_arr[$tid]['price'] = $price;
    }

    $iprice   = @json_encode($iprice_arr);
    $sql_data = [":iprice" => $iprice, ":zid" => $zid];
    $sql      = "UPDATE `pre_site` SET `iprice`=:iprice where `zid`=:zid";
    if ($DB->query($sql, $sql_data)) {
        exit('{"code":0,"msg":"设置成功"}');
    } else {
        exit('{"code":-1,"msg":"设置密价失败，' . $DB->error() . '"}');
    }
} elseif ($my == 'delToolIpirce') {
    $zid = intval(input('post.zid'));
    $row = $DB->get_row("SELECT * from `pre_site` where zid=:zid limit 1", [":zid" => $zid]);
    if (!$row) {
        exit('{"code":-1,"msg":"当前商品密价所属分站不存在！"}');
    }
    $tid        = intval(input('post.tid', 1));
    $iprice_arr = [];
    if (!empty($row['iprice'])) {
        $iprice_arr = @json_decode($row['iprice'], true);
        if (array_key_exists($tid, $iprice_arr)) {
            $iprice_arr[$tid] = null;
            unset($iprice_arr[$tid]);
        }
    }
    $iprice   = @json_encode($iprice_arr);
    $sql_data = [":iprice" => $iprice, ":zid" => $zid];
    $sql      = "UPDATE `pre_site` SET `iprice`=:iprice where `zid`=:zid";
    if ($DB->query($sql, $sql_data)) {
        exit('{"code":0,"msg":"取消成功"}');
    } else {
        exit('{"code":0,"msg":"取消密价失败，' . $DB->error() . '"}');
    }
} else if ($my == 'edit') {
    $mid  = intval(trim($_POST['mid']));
    $type = intval(trim($_POST['type']));
    $cost = floatval($_POST['cost']);
    $name = trim($_POST['name']);
    $bz   = trim(getParams('bz', true));
    if ($name == '') {
        exit('{"code":-1,"msg":"密价名称不能为空"}');
    }
    $sqlData = [":type" => $type, ":cost" => $cost, ":bz" => $bz, ":name" => $name, ":mid" => $mid];
    $sql     = "UPDATE `pre_price_super` set type=:type,cost=:cost,bz=:bz,name=:name where mid=:mid";
    if ($DB->query($sql, $sqlData)) {
        exit('{"code":0,"msg":"编辑密价等级成功！"}');
    } else {
        exit('{"code":-1,"msg":"编辑密价等级失败，错误返回=>' . $DB->error() . '"}');
    }

} elseif ($my == 'del') {
    $mid = intval($_GET['mid']);
    $row = $DB->get_row("SELECT * from `pre_price_super` where mid=:mid limit 1", [":mid" => $mid]);
    if (!$row) {
        exit('{"code":-1,"msg":"当前密价等级不存在！","mid":"' . $mid . '"}');
    }

    $sql = "DELETE from pre_price_super where mid='{$mid}' limit 1";
    if ($DB->query($sql)) {
        exit('{"code":0,"msg":"删除密价等级成功！"}');
    } else {
        exit('{"code":-1,"msg":"删除密价等级失败，错误返回=>' . $DB->error() . '","mid":"' . $mid . '"}');
    }
}

include './head.php';

if ($my == 'reset') {
    $zid = intval(input('get.zid'));
    $row = $DB->get_row("SELECT * from `pre_site` where zid=:zid limit 1", [":zid" => $zid]);
    if (!$row) {
        showmsg('该分站不存在！', 3);
    }
    $sql_data = [":iprice" => '', ":zid" => $zid];
    $sql      = "UPDATE `pre_site` SET `iprice`=:iprice where `zid`=:zid";
    if ($DB->query($sql, $sql_data)) {
        showmsg('重置密价成功', 1);
    } else {
        showmsg('重置密价失败，' . $DB->error(), 3);
    }
} elseif (isset($_GET['zid'])) {
    // 某分站自定义密价格
    $zid = input('get.zid');
    $row = $DB->get_row("SELECT * from `pre_site` where zid=:zid limit 1", [":zid" => $zid]);
    if (!$row) {
        showmsg('该分站不存在！', 3);
    }
    $price_obj = new \core\Price($row['zid'], $row);
    $price_obj->setSuper(1);
    $kw  = input('get.kw', 1, 1);
    $cid = input('get.cid');

    if ($cid > 0) {
        $link     = 'cid=' . $cid;
        $sql_data = [":cid" => $cid, ":offset" => $offset, ":pagesize" => $pagesize];
        $sql      = " `cid`='{$cid}'";

        if ($kw) {
            if (is_numeric($kw)) {
                $sql .= " AND (`name` like '%{$kw}%' OR `tid`='{$kw}')";
            } else {
                $sql .= " AND `name` like '%{$kw}%'";
            }
        }
    } else {
        if ($kw) {
            if (is_numeric($kw)) {
                $sql = " (`name` like '%{$kw}%' OR `tid`='{$kw}')";
            } else {
                $sql = " `name` like '%{$kw}%'";
            }
        } else {
            $sql = ' 1';
        }
    }

    $numrows  = $DB->count("SELECT count(*) FROM `pre_tools` where {$sql}");
    $pagesize = $conf['index_pagesize'] ? $conf['index_pagesize'] : 30;
    $pages    = intval($numrows / $pagesize);
    if ($numrows % $pagesize) {
        $pages++;
    }
    if (isset($_GET['page'])) {
        $page = intval($_GET['page']);
    } else {
        $page = 1;
    }

    $offset   = $pagesize * ($page - 1);
    $sql_data = [':offset' => $offset, ':pagesize' => $pagesize];
    $tools    = $DB->select("SELECT * from `pre_tools` where {$sql} limit :offset, :pagesize", $sql_data);

    $iprice_arr = @json_decode($row['iprice'], true);

    $rows = [];
    foreach ($tools as $key => $v) {
        $cost = $price_obj->getBuyPrice($v['tid']);
        $tool = array(
            'iprice' => null,
            'name'   => $v['name'],
            'tid'    => $v['tid'],
            'price1' => $v['price1'],
            'cost'   => $cost,
        );
        if (array_key_exists($v['tid'], $iprice_arr) && is_array($iprice_arr[$v['tid']])) {
            $tool['iprice'] = $iprice_arr[$v['tid']];
        }
        $rows[] = $tool;
        //die(json_encode($rows));
    }

    echo <<<model
<style>
.form-group{margin-bottom:15px}
</style>
<div class="col-sm-12 col-md-12 center-block" style="float: none;padding-top:10px;">
    <!------修改商品密价 ------->
   <div class="modal fade" id="iprice_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="toolIprice_title">修改商品密价</h4>
            </div>
            <div class="modal-body">
                    <input type="text" id="iprice_tid" class="form-control hide" hidden="hidden"/>
                    <input type="text" id="mode" class="form-control hide" value="1" hidden="hidden"/>
                    <div class="form-group">
                        <div class="input-group">
                          <div class="input-group-addon">加价方式</div>
                            <select class="form-control" onchange="setKind(this.value)" id="iprice_kind">
                            <option value="2">2_百分比加价(推荐)</option>
                            <option value="1">1_直接累加</option>
                            <option value="3">3_固定价格</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group" >
                            <div class="input-group-addon" id="iprice_label">加价值</div>
                            <input type="text" placeholder="加价值" id="iprice_price" value="10" class="form-control cost"/>
                            <span class="input-group-addon cost_name">%</span>
                        </div>
                        <pre>提示：如果主站商品成本价为0，将根据当前商品设置好的分站价格作为成本价来加价</pre>
                    </div>
                    <div class="form-group">
                        <input id="setIprice_submit" type="submit" value="确定设置" class="btn btn-primary btn-block" />
                        <br>
                        <a class="btn btn-danger btn-block" id="submit" data-dismiss="modal">取消添加</a>
                    </div>
            </div>
        </div>
    </div>
</div>
    <!------修改商品密价 END-------->
model;
    $fl_arr    = $DB->query("SELECT * FROM pre_class WHERE upcid is null OR upcid=0 order by sort asc");
    $select_fl = '<option value="-1">所有分类</option>';
    if ($fl_arr) {
        foreach ($fl_arr as $key => $res) {
            $select_fl .= '<option value="' . $res['cid'] . '" ' . ($cid == $res['cid'] ? 'selected' : '') . '>' . $res['name'] . '</option>';
        }
    }

    echo '
<div class="col-xs-12 col-md-12 col-lg-12" style="padding-top:10px;float: none;">
    <div class="block">
        <div class="block-title">
            <h3>分站[<a href="./sitelist.php?zid=' . $zid . '">' . $zid . '</a>]的商品密价列表</h3>
        </div>
        <div class="">
                <form method="GET" class="form-inline">
                    <input type="text" id="iprice_zid" name="zid" class="form-control hide" value="' . $zid . '" hidden="hidden"/>
                    <input type="text" id="iprice_cid" name="cid" class="form-control hide" value="' . $cid . '" hidden="hidden"/>
                    <div class="form-group">
                        <input name="chkAll1" type="checkbox" id="chkAll1" onclick="check1(this.form.list1)"> 反选</input>&nbsp;
                    </div>
                    <div class="form-group">
                        系统共有 <b>' . $numrows . '</b> 个商品&nbsp;
                        <select class="form-control" onchange="getCid(this.value)" default="' . $cid . '" id="cid" required>' . $select_fl . '</select>
                    </div>
                    <div class="form-group">
                        <input type="text" style="margin-left:12px;" class="form-control" name="kw" value="' . $kw . '" placeholder="商品名称(模糊)、商品ID">
                    </div>
                    <div class="form-group">
                        <button type="submit" style="margin-left:12px;" class="btn btn-success">搜索</button>
                    </div>
                    <div class="form-group">
                        <button type="button" id="pset" style="margin-left:12px;" class="btn btn-info">批量设置密价</button>
                    </div>
                    <div class="form-group">
                    <a href="./superPrice.php?my=reset&zid=' . $zid . '" style="margin-left:12px;" class="btn btn-primary">重置当前分站密价</a>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-striped">
                      <thead><tr><th>Tid</th><th>商品名称</th><th>主站成本价</th><th>当前分站价</th><th>自定义密价</th></tr></thead>
                      <tbody>';
    foreach ($rows as $key => $item) {
        echo '<tr><td><div class="checkbox-inline checkbox-md"><input class="" type="checkbox" name="checkbox[]" id="list1" value="' . $item['tid'] . '"><b>' . $item['tid'] . '</b></div></td><td>' . $item['name'] . '</td><td>' . $item['price1'] . '</td><td>' . $item['cost'] . '</td><td>' . getIprice($item) . '</td></tr>';
    }
    echo ' </tbody>
                    </table>
                  </div>';
    #分页
    $pageList = new \core\Page($numrows, $pagesize, 0, $link);
    echo $pageList->showPage();

    ?>
    <script>
    "use strict";
    var checkList = [] || new Array();

    function check1(field) {
        var checkbox = field || document.getElementsByName('checkbox[]');
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
    $("#setIprice_submit").click(function () {
            var mode = $("[name=mode]").val();
            if (mode == 1) {
                var tid = $("#iprice_tid").val();
                var iprice_zid = $("#iprice_zid").val();
                var iprice_kind = $("#iprice_kind").val();
                var iprice_price = $("#iprice_price").val();
                var ii = layer.load(2, {shade:[0.1,'#fff']});
                $.ajax({
                    type : 'POST',
                    url : '?my=setToolIpirce',
                    data: {
                        tid:tid,
                        zid:iprice_zid,
                        kind:iprice_kind,
                        price:iprice_price,
                    },
                    dataType : 'json',
                    success : function(data) {
                    layer.close(ii);
                    if(data.code == 0){
                        layer.msg(data.msg,{
                            time:1000,
                            end:function(){
                                window.location.reload();
                            }
                        });
                    }else{
                        layer.alert(data.msg);
                    }
                    },
                    error:function(data){
                    layer.msg('服务器错误');
                    return false;
                    }
                });
            }else{
                getVals();
                var tids = checkList;
                var iprice_zid = $("#iprice_zid").val();
                var iprice_kind = $("#iprice_kind").val();
                var iprice_price = $("#iprice_price").val();
                var ii = layer.load(2, {shade:[0.1,'#fff']});
                $.ajax({
                    type : 'POST',
                    url : '?my=setToolIpirceByBatch',
                    data: {
                        tids: tids,
                        zid:iprice_zid,
                        kind:iprice_kind,
                        price:iprice_price,
                    },
                    dataType : 'json',
                    success : function(data) {
                    layer.close(ii);
                    if(data.code == 0){
                        layer.msg(data.msg, {
                            time:1000,
                            end:function(){
                                window.location.reload();
                            }
                        });
                    }else{
                        layer.alert(data.msg);
                    }
                    },
                    error:function(data){
                    layer.msg('服务器错误');
                    return false;
                    }
                });
            }
    });
    function getCid(cid){
        var iprice_cid = $("#iprice_cid").val();
        if(cid!=iprice_cid){
            var iprice_zid = $("#iprice_zid").val();
            window.location.href='?zid='+iprice_zid+'&cid='+cid;
        }
    }
    function setIpirce(tid, name){
        $("#iprice_tid").val(tid);
        $("#iprice_modal").modal("show");
        $("[name=mode]").val("1");
        $("#toolIprice_title").html("设置分站商品<b>"+ name +"</b>的密价");
        $("#toolIprice_title").css({'font-size': '14px'});
    }
    $(document).on('click','#pset', function () {
        getVals();
        var tids = checkList;

        if ('object'!==typeof tids || tids.length==0) {
            layer.msg('未勾选有效商品数据');
            return;
        }

        $("[name=mode]").val("2");
        $("#iprice_modal").modal("show");
        $("#toolIprice_title").html("批量设置分站商品密价");
        $("#toolIprice_title").css({'font-size': '17px'});
    });

    function delIpirce(tid){
        var iprice_zid = $("#iprice_zid").val();
        var ii = layer.load(2, {shade:[0.1,'#fff']});
        $.ajax({
            type : 'POST',
            url : '?my=delToolIpirce',
            data: {
                tid:tid,
                zid:iprice_zid
            },
            dataType : 'json',
            success : function(data) {
            layer.close(ii);
            if(data.code == 0){
                layer.msg(data.msg,{
                    end:function(){
                        window.location.reload();
                    }
                });
            }else{
                layer.alert(data.msg);
            }
            },
            error:function(data){
            layer.msg('服务器错误');
            return false;
            }
        });
    }
    function setKind(value){
        if (value==1){
            $(".cost").attr('type','text');
            // $(".cost").val('');
            $(".cost_name").html('元');

        }
        else if (value == 3) {
            // $(".cost").val('');
            $(".cost").attr('type','text');
            $(".iprice_label").html('密价');
            $(".cost_name").html('元');
        }
        else{
            // $(".cost").val('');
            $(".cost").attr('type','text');
            $(".cost_name").html('%');
        }
    }
    </script>
<?php

} else {
    echo <<<model
<style>
.form-group{margin-bottom:15px}
</style>
<div class="col-sm-12 col-md-12 center-block" style="float: none;padding-top:10px;">
    <!------修改密价等级 ------->
   <div class="modal fade" id="edit_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">编辑密价等级</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_mid" class="form-control hide"/>
                <div class="form-group">
                  <div class="input-group">
                  <div class="input-group-addon">密价名称</div>
                  <input type="text" placeholder="密价名称" id="edit_name" value="" class="form-control" /></div>
                </div>
                <div class="form-group">
                  <div class="input-group">
                  <div class="input-group-addon">密价备注</div>
                  <input type="text" placeholder="如：给某某代理/客户专属用" id="edit_bz" value="" class="form-control" /></div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                      <div class="input-group-addon" id="inputname">加价方式</div>
                      <select class="form-control" onchange="setType(this.value)" id="edit_type">
                        <option value="2">2_百分比加价(推荐)</option>
                        <option value="1">1_直接累加</option>
                        <option value="3">3_固定价格</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                    <div class="input-group-addon" id="iprice_label">加价值</div>
                    <input type="text" placeholder="加价值，如10" id="edit_cost" value="" class="form-control cost" />
                    <span class="input-group-addon cost_name">%</span>
                    </div>
                    <pre>提示：当开通了密价等级的分站或用户，购买商品时将使用本加价值作为差价依据，可以填0表示不加价</pre>
                </div>
                <div class="form-group">
                    <input id="edit_super" type="submit" value="确定修改" class="btn btn-primary btn-block" />
                    <br>
                   <a class="btn btn-danger btn-block" id="submit" data-dismiss="modal">取消修改</a>
                </div>
            </div>
        </div>
    </div>
</div>
    <!------修改密价等级 END-------->
 <!------添加密价等级 ------->
   <div class="modal fade" id="add_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">编辑密价等级</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                  <div class="input-group">
                  <div class="input-group-addon">密价名称</div>
                  <input type="text" placeholder="密价名称" id="add_name" value="" class="form-control" /></div>
                </div>
                <div class="form-group">
                  <div class="input-group">
                  <div class="input-group-addon">密价备注</div>
                  <input type="text" placeholder="如：给某某代理/客户专属用" id="add_bz" value="" class="form-control" /></div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                      <div class="input-group-addon" id="inputname">加价方式</div>
                      <select class="form-control" onchange="setType(this.value)" id="add_type">
                        <option value="0">0_百分比加价(推荐)</option>
                        <option value="1">1_直接累加</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                    <div class="input-group-addon">加价值</div>
                    <input type="text" placeholder="加价值，如10" id="add_cost" value="" class="form-control" />
                    <span class="input-group-addon cost_name">%</span>
                    </div>
                    <pre>提示：当开通了密价等级的分站或用户，购买商品时将使用本加价值作为差价依据，可以填0表示不加价</pre>
                </div>
                <div class="form-group">
                    <input id="add_super" type="submit" value="确定添加" class="btn btn-primary btn-block" />
                    <br>
                   <a class="btn btn-danger btn-block" id="submit" data-dismiss="modal">取消添加</a>
                </div>
            </div>
        </div>
    </div>
</div>
    <!------添加密价等级 END-------->
model;
    echo '
    <div class="block">
        <div class="block-title"><h3>密价等级列表</h3></div>
        <div class="">
                <div class="alert alert-danger">
                1.注意一：此处添加的密价规则需要在“分站列表”或“用户列表”给某分站/用户设置密价后才会生效<br/>
                2.注意二：如需设置单独的商品密价，在“分站列表”或“用户列表”点击“密价”即可看到操作入口
                </div>
                <div class="form-inline" style="margin:8px auto;">
                    <a data-toggle="modal" data-target="#add_modal" style="margin-left:12px;" class="btn btn-success">添加密价</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                      <thead><tr><th>MID</th><th>密价名称</th><th>备注</th><th>加价值</th><th>密价规则</th><th>添加时间</th><th>状态</th><th>操作</th></tr></thead>
                      <tbody>';
    $numrows  = $DB->count("SELECT count(*) FROM `pre_price_super`");
    $pagesize = $conf['index_pagesize'] ? $conf['index_pagesize'] : 30;
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
    $rows   = $DB->select("SELECT * FROM `pre_price_super` WHERE 1 order by mid desc limit $offset,$pagesize");
    if (false !== $rows) {
        foreach ($rows as $key => $res) {
            echo '<tr><td>' . $res['mid'] . '</td><td>' . $res['name'] . '</td><td>' . $res['bz'] . '</td><td>' . display_typeVal($res['type'], $res['cost']) . '</td><td>' . display_type($res['type']) . '</td><td>' . $res['addtime'] . '</td><td>' . display_status($res['status'], $res['mid']) . '</td><td><a href="JavaScript:(0)" onclick="input(' . $res['mid'] . ')" class="btn btn-primary btn-xs">编辑</a>&nbsp;&nbsp;<a href="./sitelist.php?mid=' . $res['mid'] . '"  class="btn btn-success btn-xs">分站</a>&nbsp;&nbsp;<a href="JavaScript:void(0)" onclick="del(' . $res['mid'] . ')" class="btn btn-danger btn-xs">删除</a></td></tr>';
        }
    }
    echo ' </tbody>
                    </table>
                  </div>';
#分页
    $pageList = new \core\Page($numrows, $pagesize, 0, $link);
    echo $pageList->showPage();

    ?>
<script>
$("#add_super").click(function () {
    var name    = $("#add_name").val();
    var type    = $("#add_type option:selected").val();
    var cost    = $("#add_cost").val();
    var bz      = $("#add_bz").val();
    if (cost===null || name==="") {
        layer.alert('请确保必填项内容不能为空');
        return false;
    }
    var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type : 'POST',
        url : '?my=add',
        data:'name='+name+'&type='+type+'&cost='+cost+'&bz='+bz,
        dataType : 'json',
        success : function(data) {
          layer.close(ii);
          if(data.code == 0){
            layer.msg(data.msg,{
                time:1000,
                end:function(){
                    window.location.reload();
                }
            });
          }else{
            layer.alert(data.msg);
          }
        },
        error:function(data){
          layer.msg('服务器错误');
          return false;
        }
    });

});

$("#edit_super").click(function () {
    var mid     = $("#edit_mid").val();
    var name    = $("#edit_name").val();
    var type    = $("#edit_type option:selected").val();
    var cost    = $("#edit_cost").val();
    var bz      = $("#edit_bz").val();

    if (cost===null || name==="") {
        layer.alert('请确保必填项内容不能为空');
        return false;
    }

    var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type : 'POST',
        url : '?my=edit',
        data:'name='+name+'&type='+type+'&cost='+cost+'&bz='+bz+'&mid='+mid,
        dataType : 'json',
        success : function(data) {
          layer.close(ii);
          if(data.code == 0){
            layer.msg(data.msg,{
                time:1000,
                end:function(){
                    window.location.reload();
                }
            });
          }else{
            layer.alert(data.msg);
          }
        },
        error:function(data){
          layer.msg('服务器错误');
          return false;
        }
    });

});

function setStatus(status,mid){
    var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type : "POST",
        url : "?my=setStatus",
        dataType : "json",
        data : {status:status,mid:mid},
        success : function(data) {
            layer.close(ii);
            if(data.code == 0){
                layer.msg(data.msg,{
                    time:1000,
                    end:function(){
                        window.location.reload();
                    }
                });
            }
            else{
                layer.alert(data.msg);
            }
        },
        error:function(data){
            layer.close(ii);
            layer.msg("服务器错误");
            return false;
        }
    });
}

function input(mid){
    var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type : "POST",
        url : "?my=input&mid="+mid,
        dataType : 'json',
        success : function(data) {
            layer.close(ii);
            if(data.code==0){
                $("#edit_mid").val(mid);
                $("#edit_name").val(data.data.name);
                $("#edit_type").val(data.data.type);
                $("#edit_bz").val(data.data.bz);
                $("#edit_cost").val(data.data.cost);
                $("#edit_modal").modal("show");
                $("#edit_type").change();

            }else{
               layer.alert(data.msg);
            }
        },
        error: function(ret){
            conlose.log(ret);
            layer.close(ii);
            layer.alert("服务器请求超时，请稍后再试！");
        }
    });

}

function del(mid){
    var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type : "POST",
        url : "./ajax.php?act=delSuperPirce&mid="+mid,
        dataType : 'json',
        success : function(data) {
            layer.close(ii);
            if(data.code==0){
                layer.msg(data.msg,{
                    end:function(){
                        window.location.reload();
                    }
                });
            }else{
               layer.alert(data.msg);
            }
        },
        error: function(ret){
            conlose.log(ret);
            layer.close(ii);
            layer.alert("服务器请求超时，请稍后再试！"+ret);
        }
    });
}

function setType(value){
    if (value==1){
        $(".cost").attr('type','text');
        // $(".cost").val('');
        $("#iprice_label").html('加价值');
        $(".cost_name").html('元');

    }
    else if (value == 3) {
        // $(".cost").val('');
        $(".cost").attr('type','text');
        $("#iprice_label").html('密价');
        $(".cost_name").html('元');
    }
    else{
        // $(".cost").val('');
        $("#iprice_label").html('加价值');
        $(".cost").attr('type','text');
        $(".cost_name").html('%');
    }
}
</script>
<?php

}

include 'footer.php';
