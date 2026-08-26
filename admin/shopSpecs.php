<?php
/**
 * 商品规格管理
 **/
include "../includes/common.php";
checkLogin();

$title = ' 商品规格管理';

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
    $curl    = input('post.curl');
    $type    = input('post.type');
    $attach  = input('post.attach');
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
        ":curl"    => $curl,
        ":type"    => $type,
        ":attach"  => $attach,
        ":remark"  => $remark,
        ":obj"     => json_encode($obj, JSON_UNESCAPED_UNICODE),
        ":addtime" => $date,
        ":uptime"  => $date,
    ];
    $sql = "INSERT INTO `pre_specs` (`name`,`title`,`curl`,`type`,`attach`,`remark`,`obj`,`status`,`addtime`,`uptime`) VALUES (:name,:title,:curl,:type,:attach,:remark,:obj,'1',:addtime,:uptime)";
    if ($DB->insert($sql, $sqlData)) {
        $_SESSION['priceselect'] = [];
        $_SESSION['specsSelect'] = [];
        $result                  = array("code" => 0, "msg" => "succ");
    } else {
        $result = array("code" => -1, "msg" => "添加失败，" . $DB->error());
    }
    exit(json_encode($result));
} else if ($act == 'input') {
    $id  = intval(input('post.id'));
    $row = $DB->get_row("SELECT * from cmy_specs where id= ? limit 1", [$id]);
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
    $curl    = input('post.curl');
    $type    = input('post.type');
    $attach  = input('post.attach');
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
        ":curl"   => $curl,
        ":type"   => $type,
        ":attach" => $attach,
        ":remark" => $remark,
        ":obj"    => json_encode($obj, JSON_UNESCAPED_UNICODE),
        ":uptime" => $date,
        ":id"     => $id,
    ];
    $sql = "UPDATE `pre_specs` set `name`=:name,`title`=:title,`curl`=:curl,`type`=:type,`attach`=:attach,`remark`=:remark,`obj`=:obj,`uptime`=:uptime where id=:id";
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
    $row    = $DB->get_row("SELECT * from `pre_specs` where id=:id limit 1", [":id" => $id]);
    if (!$row) {
        exit('{"code":-1,"msg":"该条数据不存在！","id":"' . $id . '"}');
    }
    $sql = "UPDATE `pre_specs` set `status`=:status where id=:id";
    if ($DB->query($sql, [":status" => $status, ":id" => $id])) {
        $result = array("code" => 0, "msg" => "succ");
    } else {
        $result = array("code" => -1, "msg" => "删除失败，" . $DB->error());
    }
    exit(json_encode($result));
} elseif ($act == 'del') {
    $id  = intval(input('post.id'));
    $row = $DB->get_row("SELECT * from `pre_specs` where id=:id limit 1", [":id" => $id]);
    if (!$row) {
        exit('{"code":-1,"msg":"该条数据不存在！","id":"' . $id . '"}');
    }
    $sql = "DELETE FROM cmy_specs where id=:id";
    if ($DB->query($sql, [":id" => $id])) {
        $result = array("code" => 0, "msg" => "succ");
    } else {
        $result = array("code" => -1, "msg" => "删除失败，" . $DB->error());
    }
    exit(json_encode($result));
}

include './head.php';

if ($_SESSION['priceselect']) {
    $priceselect = $_SESSION['priceselect'];
} else {
    $rs   = $DB->query('SELECT * FROM cmy_price order by id desc');
    $arr1 = [];
    if ($rs) {
        $arr1 = $DB->fetchAll($rs);
    }
    $_SESSION['priceselectJson'] = json_encode($arr1, JSON_UNESCAPED_UNICODE);

    $priceselect = '<option value="0">不加价或使用商品设置</option>';
    foreach ($arr1 as $key => $res) {
        if ($res['kind'] == 1) {
            $priceselect .= '<option value="' . $res['id'] . '" kind="' . $res['kind'] . '" p_2="' . $res['p_2'] . '" p_1="' . $res['p_1'] . '" p_0="' . $res['p_0'] . '" >' . $res['name'] . '(+' . $res['p_2'] . '元|+' . $res['p_1'] . '元|+' . $res['p_0'] . '元)</option>';
        } else {
            $priceselect .= '<option value="' . $res['id'] . '" kind="' . $res['kind'] . '" p_2="' . $res['p_2'] . '" p_1="' . $res['p_1'] . '" p_0="' . $res['p_0'] . '" >' . $res['name'] . '(+' . $res['p_2'] . '%|+' . $res['p_1'] . '%|+' . $res['p_0'] . '%)</option>';
        }
    }
    $_SESSION['priceselect'] = $priceselect;
}

$mod = isset($_GET['mod']) ? $_GET['mod'] : null;
echo <<<modal
<style>
.form-group{margin-bottom:15px}
.input-group-addon {
    padding: 6px 8px;
}
.layui-layer-ico5 {
    background-position: -149px 0;
}
</style>
<div class="col-sm-12 col-md-12 center-block" style="float: none;padding-top:10px;">
    <!------添加规格 ------->
   <div class="modal fade" id="add_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">添加一个规格</h4>
            </div>
            <div class="modal-body">
                    <form id="addForm" action="?act=add" method="post" role="form">
                    <div class="form-group">
                      <div class="input-group">
                      <div class="input-group-addon">规格名称</div>
                      <input type="text" placeholder="规格名称" name="name" id="add_name" value="" class="form-control" /></div>
                      <pre>如：**规格。用于在商品设置规格时展示，方便选择</pre>
                    </div>
                    <div class="form-group">
                      <div class="input-group">
                      <div class="input-group-addon">规格标题</div>
                      <input type="text" placeholder="规格标题" name="title" id="add_title" value="" class="form-control" /></div>
                      <pre>示例可选：选择面值、选择类型、选择版本</pre>
                    </div>
                    <div class="form-group hide">
                        <div class="input-group">
                            <div class="input-group-addon">发货类型</div>
                            <select class="form-control" name="curl" default="1">
                                <option value="1">代充</option>
                                <option value="2">直冲</option>
                                <option value="0">卡密</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">数据识别</div>
                            <select class="form-control" name="attach" default="0">
                                <option value="0" selected>不需要</option>
                                <option value="kuaishou">kuaishou</option>
                                <option value="kuaishou_uid">kuaishou_uid</option>
                                <option value="douyin">douyin</option>
                                <option value="douyin_uid">douyin_uid</option>
                                <option value="qmkg">qmkg</option>
                                <option value="huoshan">huoshan</option>
                                <option value="weishi">weishi</option>
                                <option value="weishi_uid">weishi_uid</option>
                                <option value="xhs">xhs</option>
                                <option value="xhs_uid">xhs_uid</option>
                                <option value="pipix">pipix</option>
                                <option value="toutiao">toutiao</option>
                                <option value="meipai">meipai</option>
                                <option value="bili">bili</option>
                                <option value="bili_uid">bili_uid</option>
                                <option value="zuiyou">zuiyou</option>
                                <option value="qmvideo">qmvideo</option>
                                <option value="meitu">meitu</option>
                                <option value="shuoshuo">shuoshuo</option>
                            </select>
                        </div>
                        <pre>用于对接**平台时，下单数据的有效内容自动获取识别。如果是实物选【不需要】即可</pre>
                    </div>
                    <div class="form-group">
                      <div class="input-group">
                      <div class="input-group-addon">规格备注</div>
                      <input type="text" placeholder="" name="remark" id="add_remark" value="" class="form-control" /></div>
                    </div>
                    <table class="table table-striped table-bordered table-condensed">
                    <tbody>
                        <input type="hidden" id="add_num" value="1"/>
                        <input type="hidden" id="add_index" value="1"/>
                        <tr><td colspan="5">规格选项 一行一个&nbsp;&nbsp;<a class="btn btn-danger btn-sm" onclick="chenmObj.addOption('add')"><i class="fa fa-plus"></i>&nbsp;添加一个</a></td></tr>
                        <tr align="center"><td>标题</td><td>数量</td><td>库存</td><td>图片</td><td>加价模板</td><td>操作</td></tr>
                        <tbody id="add_options">
                        <tr id="add_option0">
                        <td><input type="text" placeholder="100个"name="options[0][title]" value="" class="form-control input-sm"/></td>
                        <td><input type="text" placeholder="100" name="options[0][value]" value="" class="form-control input-sm" placeholder=""/></td>
                        <td><input type="text"  name="options[0][stock]" placeholder="999" value="999" class="form-control input-sm"/></td>
                        <td><div class="input-group"><input type="file" name="icon_file" onchange="chenmObj.iconUpload(this)" parentElem="#add_option0" class="hide"/><input type="text" name="options[0][icon]" value="" class="form-control input-sm option_icon" placeholder=""/><a onclick="chenmObj.iconClick(this)" parentElem="#add_option0" class="input-group-addon" title="点击上传该版本图片"><i class="fa fa-cloud-upload"></i></a><a onclick="chenmObj.iconView(this)" parentElem="#add_option0" class="input-group-addon" title="点击预览该版本图片"><i class="fa fa-file-image-o"></i></a></div></td>
                        <td><select class="form-control" name="prid" default="0">{$priceselect}</select></td>
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
    <!------添加规格 END-------->
 <!------修改规格 ------->
       <div class="modal fade" id="edit_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel2">修改规格数据</h4>
            </div>
            <div class="modal-body">
                    <form id="editForm" action="?act=edit" method="post" role="form">
                    <input type="hidden" name="edit_id" id="edit_id" value="0"/>
                    <div class="form-group">
                      <div class="input-group">
                      <div class="input-group-addon">规格名称</div>
                      <input type="text" placeholder="规格名称" name="name" id="edit_name" value="" class="form-control" /></div>
                      <pre>如：**规格。用于在商品设置规格时展示，方便选择</pre>
                    </div>
                    <div class="form-group">
                      <div class="input-group">
                      <div class="input-group-addon">规格标题</div>
                      <input type="text" placeholder="规格标题" name="title" id="edit_title" value="" class="form-control" /></div>
                      <pre>示例可选：选择面值、选择类型、选择版本</pre>
                    </div>
                    <div class="form-group hide">
                        <div class="input-group">
                            <div class="input-group-addon">发货类型</div>
                            <select class="form-control" name="curl" default="1">
                                <option value="1">代充</option>
                                <option value="2">直冲</option>
                                <option value="0">卡密</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">数据识别</div>
                            <select class="form-control" name="attach" default="0">
                                <option value="0" selected>不需要</option>
                                <option value="kuaishou">kuaishou</option>
                                <option value="kuaishou_uid">kuaishou_uid</option>
                                <option value="douyin">douyin</option>
                                <option value="douyin_uid">douyin_uid</option>
                                <option value="qmkg">qmkg</option>
                                <option value="huoshan">huoshan</option>
                                <option value="weishi">weishi</option>
                                <option value="weishi_uid">weishi_uid</option>
                                <option value="xhs">xhs</option>
                                <option value="xhs_uid">xhs_uid</option>
                                <option value="pipix">pipix</option>
                                <option value="toutiao">toutiao</option>
                                <option value="meipai">meipai</option>
                                <option value="bili">bili</option>
                                <option value="bili_uid">bili_uid</option>
                                <option value="zuiyou">zuiyou</option>
                                <option value="qmvideo">qmvideo</option>
                                <option value="meitu">meitu</option>
                            </select>
                        </div>
                        <pre>用于对接**平台时，下单数据的有效内容自动获取识别。如果是实物选【不需要】即可</pre>
                    </div>
                    <div class="form-group">
                      <div class="input-group">
                      <div class="input-group-addon">规格备注</div>
                      <input type="text" placeholder="" name="remark" id="edit_remark" value="" class="form-control" /></div>
                    </div>
                    <table class="table table-striped table-bordered table-condensed">
                    <tbody>
                        <input type="hidden" id="edit_num" value="1"/>
                        <input type="hidden" id="edit_index" value="1"/>
                        <tr><td colspan="5">规格选项 一行一个&nbsp;&nbsp;<a class="btn btn-danger btn-sm" onclick="chenmObj.addOption('edit')"><i class="fa fa-plus"></i>&nbsp;添加一个</a></td></tr>
                        <tr align="center"><td>标题</td><td>数量</td><td>库存</td><td>图标</td><td>加价模板</td><td>操作</td></tr>
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
    <!------修改规格 END-------->
modal;
echo '
<div class="col-xs-12 col-md-12 col-lg-12" style="padding-top:10px;float: none;">
    <div class="block">
        <div class="block-title">
            <h3>商品规格列表</h3>
            <span>
                <a href="./shopAttribute.php" class="btn btn-primary btn-xs">商品属性管理</a>
           </span>
        </div>
        <div class="">
                <div class="alert alert-info">
                    说明1：每个规格可重复使用，灵活方便，一个规格可多个类似的商品同时使用，最大化节省上架商品时间！<br>
                    说明2：如果你要出售的商品数量、版本、类型等等中有一样不同之处，就需要在此处添加一个规格！<br>
                    注意：加价模板以商品中设定的为优先，不给商品设置加价模板时才会使用规格选项对应的加价模板<br>
                </div>
                <div class="form-inline" style="margin:8px auto;">
                    <a data-toggle="modal" data-target="#add_model" style="margin-left:12px;" class="btn btn-success">添加一个规格</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                      <thead><tr><th>ID</th><th>规格名称</th><th>添加时间</th><th>状态</th><th>操作</th></tr></thead>
                      <tbody>';
$numrows  = $DB->count("SELECT count(*) FROM cmy_specs");
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
$rs     = $DB->query("SELECT * FROM cmy_specs order by id desc limit $offset,$pagesize");
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
<script src="./assets/js/shopspecs.js?' . $jsver . '"></script>
';
include 'footer.php';
