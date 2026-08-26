<?php
include '../includes/common.php';
$title = '用户管理';
checkLogin();
$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
if ($act == "setRegular") {
    $zid     = intval($_POST['zid']);
    $regular = intval($_POST['regular']);
    if ($zid < 1) {
        exit('{"code":-1,"msg":"操作失败！该分站不存在"}');
    }

    $row = $DB->query("update pre_site set regular='" . $regular . "' where zid='" . $zid . "'");
    if ($row) {
        exit('{"code":0,"msg":"操作成功！"}');
    } else {
        exit('{"code":-1,"msg":"操作失败！"}');
    }
} elseif ($act == 'delete') {
    $zid = intval($_GET['zid']);
    $sql = "delete from pre_site where zid='" . $zid . "'";
    if ($DB->query($sql)) {
        $result = array('code' => 0, 'msg' => "删除分站" . $zid . "成功！");
    } else {
        $result = array('code' => -1, 'msg' => '删除失败！[error]' . $DB->error());
    }
    exit(json_encode($result));
}

function display_moneyzt($zid, $zt)
{
    if ($zt == 1) {
        return '<span onclick="setRegular(' . $zid . ')" title="固定后分站每次下单后余额都会置为0元，不管是否有余额" class="btn btn-danger btn-xs">已固定</span>';
    } else {
        return '<span onclick="setRegular(' . $zid . ')" title="固定后分站每次下单后余额都会置为0元，不管是否有余额" class="btn btn-info btn-xs">未固定</span>';
    }

}

checkAuthority('users');
include './head.php';

echo '
   <div class="col-md-12 center-block" style="float: none;padding-top:10px">
<div class="modal fade" id="modal-money">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">余额修改</h4>
            </div>
            <div class="modal-body">
                <form id="form-money">
                    <input type="hidden" name="zid" value="">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon p-0">
                                <select name="do"
                                        style="-webkit-border-radius: 0;height:20px;border: 0;outline: none !important;border-radius: 5px 0 0 5px;padding: 0 5px 0 5px;">
                                    <option value="0">充值</option>
                                    <option value="1">扣除</option>
                                    <option value="2">奖励</option>
                                </select>
                            </span>
                            <input type="number" class="form-control" name="money" placeholder="输入金额">
                            <span class="input-group-addon">元</span>

                        </div><br/>
                        <div class="form-group">
                            <div class="input-group" id="input-group"><div class="input-group-addon" id="inputname">备注信息</div>
                            <input type="text" class="form-control" name="bz" placeholder="输入备注"/>
                           </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-info" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="recharge">确定</button>
            </div>
        </div>
    </div>
</div>

<!--密价设置 -->
<div class="modal fade" id="modal-superPrice">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-superPrice-title">密价设置（ZID：0）</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    说明：如需取消密价，全部取消勾选即可！&nbsp;<a onclick="setIpirce()" class="btn btn-primary btn-xs">设置自定义商品密价</a>
                </div>
                <input type="hidden" name="m-zid" value="">
                <input type="hidden" name="m-mid" value="">
                <div class="table-responsive">
                    <table class="table table-striped">
                      <thead><tr><th>勾选[Mid]</th><th>密价名称</th><th>加价属性</th><th>密价备注</th></tr></thead>
                      <tbody id="superPriceList">

                      </tbody>
                    </table>
                 </div>
                 <!--SVG Sprites-->
                <svg class="inline-svg">
                  <symbol id="check" viewbox="0 0 12 10">
                    <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                  </symbol>
                </svg>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-info" data-dismiss="modal">取消操作</button>
                <button onclick="setSuperPrice()" type="button" class="btn btn-primary">提交设置</button>
            </div>
        </div>
    </div>
</div>
<!--密价设置 end-->
';

$my = (isset($_GET['my']) ? $_GET['my'] : null);
if ($my == 'delete') {
    $zid = $_GET['zid'];
    $sql = 'DELETE FROM pre_site WHERE zid=\'' . $zid . '\'';
    if ($DB->query($sql)) {
        showmsg('删除成功！<br/><br/><a href="./userlist.php">>>返回分站列表</a>', 1);
    } else {
        showmsg('删除失败！' . $DB->error(), 4);
    }
} elseif ($my == 'add') {
    echo '<div class="block">
        <div class="block-title"><h3 class="panel-title">添加用户</h3></div>';
    echo '<div class="">';
    echo '<form action="?my=add_submit" method="POST">
    <div class="form-group">
    <div class="form-group">
    <label>上级站点ID:</label><br>
    <input type="text" class="form-control" name="upzid" value="" placeholder="可留空，默认为1">
    </div>
    <div class="form-group">
    <label>用户账号:</label><br>
    <input type="text" class="form-control" name="user" value="" placeholder="格式：字母数字，区分大小写">
    </div>
    <div class="form-group">
    <label>用户密码:</label><br>
    <input type="text" class="form-control" name="pwd" value="" placeholder="格式：字母数字，区分大小写">
    </div>
    <div class="form-group">
    <label>站长QQ:</label><br>
    <input type="text" class="form-control" name="qq" value="">
    </div>
    <div class="form-group">
    <label>站点余额:</label><br>
    <input type="text" class="form-control" name="money" value="" required>
    </div>
    <input type="submit" class="btn btn-primary btn-block" value="确定添加"></form>';
    echo '<br/><a href="./userlist.php">>>返回用户列表</a>';
    echo '</div></div>';
} elseif ($my == 'edit') {
    $zid = intval($_GET['zid']);
    $row = $DB->get_row('select * from pre_site where zid=\'' . $zid . '\' limit 1');
    if (!$row) {
        showmsg("该用户不存在！", 3);
    }

    echo '<div class="block">
        <div class="block-title"><h3 class="panel-title">修改用户信息</h3></div>';
    echo '<div class="">';
    echo '<form action="?my=edit_submit&zid=' . $zid . '" method="POST">
    <div class="form-group">
    <div class="form-group">
    <label>上级站点ID:</label><br>
    <input type="text" class="form-control" name="upzid" value="' . $row['upzid'] . '">
    </div>
    <div class="form-group">
    <label>站点余额:</label><br>
    <input type="text" class="form-control" name="money" value="' . $row['money'] . '" required>
    </div>
    <div class="form-group">
    <label>站长QQ:</label><br>
    <input type="text" class="form-control" name="qq" value="' . $row['qq'] . '">
    </div>
    <div class="form-group">
    <label>重置密码:</label><br>
    <input type="text" class="form-control" name="pwd" value="" placeholder="不重置请留空">
    </div>
    <input type="submit" class="btn btn-primary btn-block" value="确定修改"></form>';
    echo '<br/><a href="./userlist.php">>>返回用户列表</a>';
    echo '</div></div>';
} elseif ($my == 'add_submit') {
    $upzid = $_POST['upzid'] > 0 ? intval($_POST['upzid']) : 1;
    $money = sprintf('%。2f', input('post.money'));
    $user  = input('post.user');
    $pwd   = input('post.pwd');
    $qq    = input('post.qq');
    if (empty($user) || empty($pwd) || empty($qq)) {
        showmsg('添加错误，请确保账号、密码、QQ任意一项都不为空!', 3);
    } else {
        $row = $DB->get_row("SELECT * from `pre_site` where `user`=:user limit 1", [':user' => $user]);
        if ($row) {
            showmsg('添加错误，该用户名已被使用!', 3);
        } else {
            $sql      = "INSERT INTO `pre_site` (`upzid`,`user`,`pwd`,`qq`,`money`,`power`,`status`) VALUES(:upzid,:user,:pwd,:qq,:amount,:power,:status)";
            $sql_data = array(
                ':upzid'  => $upzid,
                ':user'   => $user,
                ':pwd'    => $pwd,
                ':qq'     => $qq,
                ':amount' => $money,
                ':power'  => '0',
                ':status' => '1',
            );
            if ($DB->query($sql, $sql_data)) {
                showmsg('添加用户成功！<br/><br/><a href="./userlist.php">>>返回用户列表</a>', 1);
            } else {
                showmsg('添加用户失败！' . $DB->error(), 4);
            }
        }
    }

} elseif ($my == 'edit_submit') {
    $zid  = $_GET['zid'];
    $rows = $DB->get_row('select * from pre_site where zid=\'' . $zid . '\' limit 1');
    if (!$rows) {
        showmsg('该用户不存在！', 3);
    }

    $upzid       = intval($_POST['upzid']);
    $money       = round($_POST['money'], 2);
    $pay_account = $_POST['pay_account'];
    $pay_name    = $_POST['pay_name'];
    $qq          = $_POST['qq'];
    if (!empty($_POST['pwd'])) {
        $sql = ',pwd=\'' . trim($_POST['pwd']) . '\'';
    }

    if ($qq == null) {
        showmsg('保存错误,请确保每项都不为空!', 3);
    } elseif ($DB->query('update pre_site set upzid=\'' . $upzid . '\',money=\'' . $money . '\',qq=\'' . $qq . '\',pay_account=\'' . $pay_account . '\',pay_name=\'' . $pay_name . '\'' . $sql . ' where zid=\'' . $zid . '\'')) {
        showmsg('修改用户成功！<br/><br/><a href="./userlist.php">>>返回用户列表</a>', 1);
    } else {
        showmsg('修改用户失败！' . $DB->error(), 4);
    }

} else {
    if (isset($_GET['zid'])) {
        $sql  = ' power=0 and  zid=' . $_GET['zid'];
        $link = '&zid=' . $_GET['zid'];
        $rows = $DB->get_row("select * from pre_site where zid='" . intval($_GET['zid']) . "' limit 1");
        if ($rows['power'] > 0) {
            exit('<script language=\'javascript\'>window.location.href=\'./sitelist.php?zid=' . $_GET['zid'] . '\';</script>');
        }
    } elseif (isset($_GET['kw'])) {
        $kw   = trim($_GET['kw']);
        $sql  = ' power=0 and (zid=\'' . $kw . '\' or user=\'' . $kw . '\' or siteurl=\'' . $kw . '\' or siteurl2=\'' . $kw . '\' or qq=\'' . $kw . '\')';
        $link = '&kw=' . $kw;
    } else {
        $sql = ' power=0';
    }

    $numrows = $DB->count('SELECT count(*) from pre_site where ' . $sql);
    $count   = $DB->count('SELECT count(*) from pre_site where power=0 and (tel is not null and tel!=\'\')');
    echo '
            <div class="block">
            <div class="block-title"><h3 class="panel-title">用户列表</h3></div>
            <div class="alert alert-info">系统共有 <b>' . $numrows . '</b> 个用户，' . $count . '个已绑定手机号<br/>
            </div>
            <form method="GET" class="form-inline">
            <div class="form-group">
            <input type="text" onfocus="tips()" class="form-control" id="kw" name="kw" placeholder="请输入用户ID、用户名、用户QQ等">
            <button type="submit" class="btn btn-success">搜索</button>&nbsp;<a href="./userlist.php?my=add" class="btn btn-primary">添加用户</a>
            </div>
            </form>
            ';
    echo '      <div class="table-responsive">
            <table class="table table-striped">
              <thead><tr><th>ZID</th><th>上级站点</th><th>用户名/密码</th><th>用户QQ</th><th>余额</th><th>手机号</th><th>开通/到期时间</th><th>属性</th><th>操作</th></tr></thead>
              <tbody>
    ';
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
    $rs     = $DB->query('SELECT * FROM pre_site WHERE' . $sql . ' order by zid desc limit ' . $offset . ',' . $pagesize);
    while ($res = $DB->fetch($rs)) {
        $tel = $res['tel'];
        if (empty($tel)) {
            $tel = "未绑定";
        }
        echo '<tr id="tr_' . $res['zid'] . '"><td><b>' . $res['zid'] . '</b></td><td>' . ($res['upzid'] > 0 ? '<a href="./sitelist.php?zid=' . $res['upzid'] . '">' . $res['upzid'] . '</a>' : '——') . '</td><td>' . $res['user'] . '<br>****&nbsp;<a onclick="readPwd(this)" pwd="' . $res['pwd'] . '">查看密码</a></td><td>' . $res['qq'] . '</td><td><a href="javascript:showRecharge(\'' . $res['zid'] . '\')" title="点击充值">' . $res['money'] . '</a></td><td>' . $tel . '</td><td>' . $res['addtime'] . '<br/><a href="javascript:setEndtime(' . $res['zid'] . ')" title="点击续期"></a></td><td><span title="设置密价，0表示未开启" class="btn btn-xs btn-orange" onclick="showSuperList(' . $res['zid'] . ',' . $res['mid'] . ')">密价(' . $res['mid'] . ')</span>&nbsp;' . ($res['status'] == 1 ? '<span class="btn btn-xs btn-success" onclick="setActive(' . $res['zid'] . ',0)">开启</span>' : '<span class="btn btn-xs btn-danger" onclick="setActive(' . $res['zid'] . ',1)">关闭</span>') . '&nbsp;' . display_moneyzt($res['zid'], $res['regular']) . '</td><td><a href="./sitelist.php?my=add2&zid=' . $res['zid'] . '" class="btn btn-default btn-xs">开分站</a>&nbsp;<a href="./userlist.php?my=edit&zid=' . $res['zid'] . '" class="btn btn-info btn-xs">编辑</a>&nbsp;<a href="./list.php?zid=' . $res['zid'] . '" class="btn btn-warning btn-xs">订单</a>&nbsp;<a href="./record.php?zid=' . $res['zid'] . '" class="btn btn-success btn-xs">明细</a>&nbsp;<a href="./userlist.php?my=delete&zid=' . $res['zid'] . '" class="btn btn-xs btn-danger" onclick="return if(confirm(\'你确实要删除该用户吗？\')==true){Delete(' . $res['zid'] . ')};">删除</a></td></tr>';
    }

    echo '          </tbody>
        </table>
      </div>
    ';
    #分页
    $pageList = new \core\Page($numrows, $pagesize, 0, $link);
    echo $pageList->showPage();
}

echo "    </div>
  </div>
<script>
function readPwd(that){
    var pwd = $(that).attr('pwd');
    layer.alert('用户密码：'+pwd);
}

function showRecharge(zid) {
    \$(\"input[name='zid']\").val(zid);
    \$('#modal-money').modal('show');
}

function selectMid(mid){
    var items = $(\"input[name='superPirceId']\");
    var isCheck = false;
    for (i = 0; i < items.length; i++) {
        if($(items[i]).val()!=mid && items[i].checked==true){
            $(items[i]).click();
        }
        if($(items[i]).val()==mid && items[i].checked==true){
            isCheck = true;
        }
    }

    if(isCheck){
        $(\"input[name='m-mid']\").val(mid);
    }
    else{
        $(\"input[name='m-mid']\").val('0');
    }
}

function showSuperList(zid, mid){
    \$.ajax({
        type : 'GET',
        url : 'ajax.php?act=superPriceList',
        dataType : 'json',
        success : function(data) {
            if(data.code==0){
                \$('#modal-superPrice-title').html('密价设置（ZID：'+zid+'）');
                \$('#superPriceList').html(data.list);
                \$('input[name=\'m-mid\']').val(mid);
                \$('input[name=\'m-zid\']').val(zid);
                if(mid>0)\$('#mid'+mid).click();
                \$('#modal-superPrice').modal('show');
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
function setIpirce(){
    var zid = \$('input[name=\'m-zid\']').val();
    window.location.href='./superPrice.php?zid='+zid;
}
function setSuperPrice(){
    var mid = $(\"input[name='m-mid']\").val();
    var zid = $(\"input[name='m-zid']\").val();
    var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type : \"POST\",
        url : \"ajax.php?act=setSuperPrice\",
        dataType : 'json',
        data : {mid:mid, zid:zid},
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
            layer.close(ii);
            layer.alert(\"服务器请求超时，请稍后再试！\"+ret);
        }
    });
}

function Delete(zid){
    var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type : 'POST',
        url : '?my=delete',
        data : {zid:zid},
        dataType : 'json',
        success : function(data) {
            layer.close(ii);
            if(data.code == 0){
                layer.msg('删除成功');
                $('tr_'+zid).fadeOut();
            }else{
                layer.alert(data.msg);
            }
        }
    });
}

function setActive(zid,active) {
    \$.ajax({
        type : 'GET',
        url : 'ajax.php?act=setSite&zid='+zid+'&active='+active,
        dataType : 'json',
        success : function(data) {
            window.location.reload();
        },
        error:function(data){
            layer.msg('服务器错误');
            return false;
        }
    });
}

function setRegular(zid,regular){
    \$.ajax({
        type : 'POST',
        url : '?act=setRegular&zid='+zid+'&regular='+regular,
        data: {zid:zid,regular:regular},
        dataType : 'json',
        success : function(data) {
            if(data.code==0){
                layer.msg('保存成功！');
                setTimeout(function (){
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

function setAppUrl(zid,AppUrl){
    \$.ajax({
        type : 'GET',
        url : 'ajax.php?act=setAppUrl&zid='+zid+'&AppUrl='+AppUrl,
        dataType : 'json',
        success : function(data) {
            if(data.code==0){
                layer.msg('保存成功！');
                window.location.reload();
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

\$(document).ready(function(){
    \$(\"#recharge\").click(function(){
        var zid=\$(\"input[name='zid']\").val();
        var actdo=\$(\"select[name='do']\").val();
        var money=\$(\"input[name='money']\").val();
        var bz=\$(\"input[name='bz']\").val();
        if(money==''){layer.alert('请输入金额');return false;}
        var ii = layer.load(2, {shade:[0.1,'#fff']});
        \$.ajax({
            type : \"POST\",
            url : \"ajax.php?act=siteRecharge\",
            data : {zid:zid,actdo:actdo,money:money,bz:bz},
            dataType : 'json',
            success : function(data) {
                layer.close(ii);
                if(data.code == 0){
                    layer.msg('修改余额成功');
                    window.location.reload();
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
});

function tips(){
    layer.tips('请输入用户ID、用户名、用户QQ等','#kw', {tips: 1,time: 3500});
}
</script>";

include_once 'footer.php';
