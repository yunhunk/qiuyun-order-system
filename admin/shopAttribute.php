<?php
/**
 * 商品属性管理
 **/
include "../includes/common.php";
checkLogin();

$title = ' 商品属性管理';

checkAuthority('super');

function display_status($status, $id)
{
    if ($status == 1) {
        return '<a class="btn btn-success btn-xs" onclick="chenmObj.setStatus(0,' . $id . ')">正常</a>';
    } else {
        return '<a class="btn btn-danger btn-xs" onclick="chenmObj.setStatus(1,' . $id . ')">禁用</font>';
    }
}

$act = isset($_GET['act']) ? input('get.act') : null;
if ($act == 'add') {
    $name    = input('post.name');
    $title   = input('post.title');
    $type    = input('post.type');
    $remark  = input('post.remark');
    $options = input('post.options');
    $obj     = [];
    foreach ($options as $key => $option) {
        if (array_key_exists('title', $option) && $option['title']) {
            $obj[] = $option;
        }
    }
    $sqlData = [
        ":name"    => $name,
        ":title"   => $title,
        ":type"    => $type,
        ":remark"  => $remark,
        ":obj"     => json_encode($obj, JSON_UNESCAPED_UNICODE),
        ":addtime" => $date,
        ":uptime"  => $date,
    ];
    $sql = "INSERT INTO `pre_attribute` (`name`,`title`,`type`,`remark`,`obj`,`status`,`addtime`,`uptime`) VALUES (:name,:title,:type,:remark,:obj,'1',:addtime,:uptime)";
    if ($DB->insert($sql, $sqlData)) {
        $_SESSION['priceselect'] = [];
        $_SESSION['specsSelect'] = [];
        $result                  = array("code" => 0, "msg" => "succ");
    } else {
        $result = array("code" => -1, "msg" => "添加失败，" . $DB->error());
    }
    exit(json_encode($result));
} elseif ($act == 'getPridList') {
    if ($_SESSION['priceselectJson'] && $data = json_decode($_SESSION['priceselectJson'])) {
        $result = array("code" => 0, "msg" => "succ", "data" => $data);
    } else {
        $rs   = $DB->query('SELECT * FROM pre_price order by id desc');
        $data = [];
        if ($rs) {
            $data = $DB->fetchAll($rs);
        }
        $result = array("code" => 0, "msg" => "succ", "data" => $data);
    }
    exit(json_encode($result));
} else if ($act == 'input') {
    $id  = intval(input('post.id'));
    $row = $DB->get_row("SELECT * from pre_attribute where id= ? limit 1", [$id]);
    if (!$row) {
        exit('{"code":-1,"msg":"该条数据不存在！"}');
    }
    $row['options'] = json_decode($row['obj'], true);
    $result         = array("code" => 0, "msg" => "succ", "data" => $row);
    exit(json_encode($result));
} else if ($act == 'edit') {
    $id      = intval(input('post.edit_id'));
    $name    = input('post.name');
    $title   = input('post.title');
    $type    = input('post.type');
    $remark  = input('post.remark');
    $options = input('post.options');
    $obj     = [];
    foreach ($options as $key => $option) {
        if (array_key_exists('title', $option) && $option['title']) {
            $obj[] = $option;
        }
    }
    $sqlData = [
        ":name"   => $name,
        ":title"  => $title,
        ":type"   => $type,
        ":remark" => $remark,
        ":obj"    => json_encode($obj, JSON_UNESCAPED_UNICODE),
        ":uptime" => $date,
        ":id"     => $id,
    ];
    $sql = "UPDATE `pre_attribute` set `name`=:name,`title`=:title,`type`=:type,`remark`=:remark,`obj`=:obj,`uptime`=:uptime where id=:id";
    if ($DB->query($sql, $sqlData)) {
        $_SESSION['priceselect'] = [];
        $_SESSION['specsSelect'] = [];
        $result                  = array("code" => 0, "msg" => "succ", "id" => $id);
    } else {
        $result = array("code" => -1, "msg" => "编辑失败，" . $DB->error());
    }
    exit(json_encode($result));
} else if ($act == 'setStatus') {
    $id     = intval(input('post.id'));
    $status = intval(input('post.status'));
    $row    = $DB->get_row("SELECT * from `pre_attribute` where id=:id limit 1", [":id" => $id]);
    if (!$row) {
        exit('{"code":-1,"msg":"该条数据不存在！","id":"' . $id . '"}');
    }
    $sql = "UPDATE `pre_attribute` set `status`=:status where id=:id";
    if ($DB->query($sql, [":status" => $status, ":id" => $id])) {
        $result = array("code" => 0, "msg" => "succ");
    } else {
        $result = array("code" => -1, "msg" => "删除失败，" . $DB->error());
    }
    exit(json_encode($result));
} elseif ($act == 'del') {
    $id  = intval(input('post.id'));
    $row = $DB->get_row("SELECT * from `pre_attribute` where id=:id limit 1", [":id" => $id]);
    if (!$row) {
        exit('{"code":-1,"msg":"该条数据不存在！","id":"' . $id . '"}');
    }
    $sql = "DELETE FROM pre_attribute where id=:id";
    if ($DB->query($sql, [":id" => $id])) {
        $result = array("code" => 0, "msg" => "succ");
    } else {
        $result = array("code" => -1, "msg" => "删除失败，" . $DB->error());
    }
    exit(json_encode($result));
}

include './head.php';

$mod = isset($_GET['mod']) ? $_GET['mod'] : null;
echo <<<modal
<style>
.form-group{margin-bottom:15px}
</style>
<div class="col-sm-12 col-md-12 center-block" style="float: none;padding-top:10px;">

    <!------添加属性 ------->
   <div class="modal fade" id="add_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">添加一个属性</h4>
            </div>
            <div class="modal-body">
                    <form id="addForm" action="?act=add" method="post" role="form">
                    <div class="form-group">
                      <div class="input-group">
                      <div class="input-group-addon">属性名称</div>
                      <input type="text" placeholder="属性名称" name="name" id="add_name" value="" class="form-control" /></div>
                      <pre>如：**属性。用于在商品设置属性时展示，方便选择</pre>
                    </div>
                    <div class="form-group">
                      <div class="input-group">
                      <div class="input-group-addon">属性标题</div>
                      <input type="text" placeholder="属性标题" name="title" id="add_title" value="" class="form-control" /></div>
                      <pre>主要实物用，示例可选：选择码数、选择颜色。</pre>
                    </div>
                    <div class="form-group">
                      <div class="input-group">
                      <div class="input-group-addon">属性备注</div>
                      <input type="text" placeholder="" name="remark" id="add_remark" value="" class="form-control" /></div>
                    </div>
                    <table class="table table-striped table-bordered table-condensed">
                    <tbody>
                        <input type="hidden" id="add_num" value="1"/>
                        <input type="hidden" id="add_index" value="1"/>
                        <tr><td colspan="5">属性选项 一行一个&nbsp;&nbsp;<a class="btn btn-danger btn-sm" onclick="chenmObj.addOption('add')"><i class="fa fa-plus"></i>&nbsp;添加一个</a></td></tr>
                        <tr align="center"><td>标题</td><td>值</td><td>备注</td><td>操作</td></tr>
                        <tbody id="add_options">
                        <tr id="add_option0">
                        <td><input type="text" placeholder="中号" name="options[0][title]" value="" class="form-control input-sm"/></td>
                        <td><input type="text" placeholder="中号/M" name="options[0][value]" value="" class="form-control input-sm" placeholder=""/></td>
                        <td><input type="text"  name="options[0][remark]" placeholder="可不填" value="" class="form-control input-sm"/></td>
                        <td><a class="btn btn-warning btn-xs" onclick="chenmObj.delOption('add', 0)">删除</a></td>
                        </tr>
                        </tbody>
                    </table>
                    </form>
                    <div class="form-group">
                        <a onclick="chenmObj.add()" type="submit"class="btn btn-primary btn-block">确定添加</a>
                        <br>
                        <a class="btn btn-danger btn-block" id="submit" data-dismiss="modal">取消添加</a>
                    </div>
            </div>
        </div>
    </div>
</div>
    <!------添加属性 END-------->
 <!------修改属性 ------->
       <div class="modal fade" id="edit_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel2">修改属性数据</h4>
            </div>
            <div class="modal-body">
                    <form id="editForm" action="?act=edit" method="post" role="form">
                    <input type="hidden" name="edit_id" id="edit_id" value="0"/>
                    <div class="form-group">
                      <div class="input-group">
                      <div class="input-group-addon">属性名称</div>
                      <input type="text" placeholder="属性名称" name="name" id="edit_name" value="" class="form-control" /></div>
                      <pre>如：**属性。用于在商品设置属性时展示，方便选择</pre>
                    </div>
                    <div class="form-group">
                      <div class="input-group">
                      <div class="input-group-addon">属性标题</div>
                      <input type="text" placeholder="属性标题" name="title" id="edit_title" value="" class="form-control" /></div>
                      <pre>主要实物用，示例可选：选择码数、选择颜色。</pre>
                    </div>
                    <div class="form-group">
                      <div class="input-group">
                      <div class="input-group-addon">属性备注</div>
                      <input type="text" placeholder="" name="remark" id="edit_remark" value="" class="form-control" /></div>
                    </div>
                    <table class="table table-striped table-bordered table-condensed">
                    <tbody>
                        <input type="hidden" id="edit_num" value="1"/>
                        <input type="hidden" id="edit_index" value="1"/>
                        <tr><td colspan="5">属性选项 一行一个&nbsp;&nbsp;<a class="btn btn-danger btn-sm" onclick="chenmObj.addOption('edit')"><i class="fa fa-plus"></i>&nbsp;添加一个</a></td></tr>
                        <tr align="center"><td>标题</td><td>值</td><td>备注</td><td>操作</td></tr>
                        <tbody id="edit_options">
                        </tbody>
                    </table>
                    </form>
                    <div class="form-group">
                        <a onclick="chenmObj.edit(chenmObj.edit_id)" type="submit"class="btn btn-primary btn-block">确定修改</a>
                        <br>
                        <a class="btn btn-danger btn-block" id="submit" data-dismiss="modal">取消修改</a>
                    </div>
            </div>
        </div>
    </div>
</div>
    <!------修改属性 END-------->
modal;
echo '
<div class="col-xs-12 col-md-12 col-lg-12" style="padding-top:10px;float: none;">
    <div class="block">
        <div class="block-title"><h3>商品属性列表</h3></div>
        <div class="">
                <div class="alert alert-info">
                    说明1：每个属性可重复使用，灵活方便，一个属性可多个类似的商品同时使用，最大化节省上架商品时间！<br>
                    说明2：属性主要用于实物，虚拟商品通常情况不需要属性！<br>
                </div>
                <div class="form-inline" style="margin:8px auto;">
                    <a data-toggle="modal" data-target="#add_model" style="margin-left:12px;" class="btn btn-success">添加一个属性</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                      <thead><tr><th>ID</th><th>属性名称</th><th>添加时间</th><th>状态</th><th>操作</th></tr></thead>
                      <tbody>';
$numrows  = $DB->count("SELECT count(*) FROM pre_attribute");
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
$rs     = $DB->query("SELECT * FROM pre_attribute order by id desc limit $offset,$pagesize");
while ($res = $DB->fetch($rs)) {
    echo '<tr><td>' . $res['id'] . '</td><td>' . $res['name'] . '</td><td>' . $res['addtime'] . '</td><td>' . display_status($res['status'], $res['id']) . '</td><td><a href="JavaScript:(0)" onclick="chenmObj.input(' . $res['id'] . ')" class="btn btn-primary btn-xs">编辑</a>&nbsp;&nbsp;<a href="./shoplist.php?specs_id=' . $res['id'] . '"  class="btn btn-success btn-xs">商品</a>&nbsp;&nbsp;<a href="JavaScript:void(0)" onclick="chenmObj.del(' . $res['id'] . ')" class="btn btn-danger btn-xs">删除</a></td></tr>';
}

echo ' </tbody>
                    </table>
                  </div>';
#分页
$pageList = new \core\Page($numrows, $pagesize, 0, $link);
echo $pageList->showPage();

echo '
<script src="./assets/js/shopattribute.js?' . $jsver . '"></script>
';
include 'footer.php';
