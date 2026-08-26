<?php
include '../includes/common.php';
$title = '分站管理';
checkLogin();

$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
if ($act == "setRegular") {
    $zid = intval($_POST['zid']);
    if ($zid < 1) {
        exit('{"code":-1,"msg":"操作失败！该分站不存在"}');
    }

    $row = $DB->get_row("select regular pre_site where zid= ? limit 1", array($zid));
    if ($row) {
        $regular = $row['regular'] == 1 ? 0 : 1;
        $rs      = $DB->query("update pre_site set regular= ? where zid= ?", array($regular, $zid));
        if ($rs) {
            exit('{"code":0,"msg":"操作成功！"}');
        } else {
            exit('{"code":-1,"msg":"操作失败！"}');
        }
    } else {
        exit('{"code":-1,"msg":"操作失败！"}');
    }
}
;

function display_moneyzt($zid, $zt)
{
    if ($zt == 1) {
        return '<a onclick="setRegular(' . $zid . ')" title="固定后分站每次下单后余额都会置为0元，不管是否有余额" class="dropdown-menu-right animated fadeInRight" role="menu" aria-labelledby="dropdownMenu' . $zid . '">设置余额固定为零(已固定)</a>';
    } else {
        return '<a onclick="setRegular(' . $zid . ')" title="固定后分站每次下单后余额都会置为0元，不管是否有余额" class="dropdown-menu-right animated fadeInRight" role="menu" aria-labelledby="dropdownMenu' . $zid . '">设置余额固定为零(未固定)</a>';
    }

}

function display_pricedzt($zid, $zt)
{
    if ($zt == 1) {
        return '<a onclick="setPriced(' . $zid . ',0)" title="商品前台价格是否显示此分站设置的商品售价，此功能可用于主站宣传站点。当显示“同步主站”表示关闭" class="dropdown-menu-right animated fadeInRight" role="menu" aria-labelledby="dropdownMenu' . $zid . '">设置为可自定义设置(当前：同步主站)</a>';
    } else {
        return '<a onclick="setPriced(' . $zid . ',1)" title="商品前台价格是否显示为此分站设置的商品售价，此功能可用于主站宣传站点。当显示“同步主站”表示关闭" class="dropdown-menu-right animated fadeInRight" role="menu" aria-labelledby="dropdownMenu' . $zid . '">设置为始终同步主站(当前：自定设置)</a>';
    }
}

checkFileSize();
checkAuthority('fenzhan');
include './head.php';

echo '
    <div class="col-md-12 center-block" style="float: none;padding-top:10px">
<div class="modal fade" align="left" id="search" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">搜索分站</h4>
      </div>
      <div class="modal-body">
      <form action="sitelist.php" method="GET">
<input type="text" class="form-control" name="kw" placeholder="请输入分站用户名或域名"><br/>
<input type="submit" class="btn btn-primary btn-block" value="搜索"></form>
</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" align="left" id="search2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">分类查看</h4>
      </div>
      <div class="modal-body">
      <form action="sitelist.php" method="GET">
<select name="power" class="form-control"><option value="1">专业版</option><option value="2">旗舰版</option></select><br/>
<input type="submit" class="btn btn-primary btn-block" value="查看"></form>
</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!--余额修改 -->
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
                            <span class="input-group-addon">
                                 操作类型
                            </span>
                             <select id="action" name="action" class="form-control">
                                    <option value="1" selected>余额</option>
                                    <option value="2">提成</option>
                            </select>
                        </div><br/>
                        <div class="input-group">
                            <span class="input-group-addon p-0">
                                <select name="do"
                                        style="-webkit-border-radius: 0;height:20px;border: 0;outline: none !important;border-radius: 5px 0 0 5px;padding: 0 5px 0 5px;">
                                    <option value="0">充值</option>
                                    <option value="1">扣除</option>
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
<!--余额修改 end-->

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
if ($my == 'add') {
    $endtime = date('Y-m-d', strtotime('+1 years'));
    echo '<div class="block">
<div class="block-title"><h3 class="panel-title">添加一个分站</h3></div>';
    echo '<div class="">';
    echo '<form action="./sitelist.php?my=add_submit" method="POST">
<div class="form-group">
<label>分站类型:</label><br>
<select class="form-control" name="power"><option value="1">专业版</option><option value="2">旗舰版</option></select>
</div>
<div class="form-group">
<label>管理员用户名:</label><br>
<input type="text" class="form-control" name="user" value="" required>
</div>
<div class="form-group">
<label>管理员密码:</label><br>
<input type="text" class="form-control" name="pwd" value="123456" required>
</div>
<div class="form-group">
<label>绑定域名:</label><br>
<input type="text" class="form-control" name="siteurl" value="" placeholder="分站要用的域名" required>
</div>
<!--div class="form-group">
<label>额外域名:</label><br>
<input type="text" class="form-control" name="siteurl2" placeholder="不需要填写" value="">
</div-->
<div class="form-group">
<label>站点余额:</label><br>
<input type="text" class="form-control" name="money" value="0" required>
</div>
<div class="form-group">
<label>站长QQ:</label><br>
<input type="text" class="form-control" name="qq" value="">
</div>
<div class="form-group">
<label>到期时间:</label><br>
<input type="date" class="form-control" name="endtime" value="' . $endtime . '" required>
</div>
<input type="submit" class="btn btn-primary btn-block" value="确定添加"></form>';
    echo '<br/><a href="./sitelist.php">>>返回分站列表</a>';
    echo '</div></div>';
} elseif ($my == 'add2') {
    $zid     = $_GET['zid'];
    $row     = $DB->get_row('select * from pre_site where zid= ? limit 1', array($zid));
    $endtime = date('Y-m-d', strtotime('+1 years'));
    echo '<div class="block">
<div class="block-title"><h3 class="panel-title">用户开通分站</h3></div>';
    echo '<div class="">';
    echo '<form action="./sitelist.php?my=edit_submit&zid=' . $zid . '" method="POST">
<div class="form-group">
<label>分站类型:</label><br>
<select class="form-control" name="power" default="1"' . "><option value=\"1\">专业版</option><option value=\"2\">旗舰版</option></select>
</div>" . '
  <div class="form-group">
<label>上级站点ID:</label><br>
<input type="text" class="form-control" name="upzid" value="' . $row['upzid'] . '" disabled></div>
' . '<div class="form-group">
<label>绑定域名:</label><br>
<input type="text" class="form-control" name="siteurl" value="' . $row['siteurl'] . '" required>
</div>
<div class="form-group">
<label>额外域名:</label><br>
<input type="text" class="form-control" name="siteurl2" value="' . $row['siteurl2'] . '">
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
<label>站点名称:</label><br>
<input type="text" class="form-control" name="sitename" value="' . $row['sitename'] . '">
</div>
<div class="form-group">
<label>结算账号:</label><br>
<input type="text" class="form-control" name="pay_account" value="' . $row['pay_account'] . '">
</div>
<div class="form-group">
<label>结算姓名:</label><br>
<input type="text" class="form-control" name="pay_name" value="' . $row['pay_name'] . '">
</div>
<div class="form-group">
<label>到期时间:</label><br>
<input type="date" class="form-control" name="endtime" value="' . $endtime . '" required>
</div>
<input type="submit" class="btn btn-primary btn-block" value="确定开通"></form>';
    echo '<br/><a href="./userlist.php">>>返回用户列表</a>';
    echo '<script>
var items = $("select[default]");
for (i = 0; i < items.length; i++) {
    $(items[i]).val($(items[i]).attr("default")||0);
}
</script></div></div>';
} elseif ($my == 'edit') {
    $zid     = $_GET['zid'];
    $row     = $DB->get_row('select * from pre_site where zid=\'' . $zid . '\' limit 1');
    $endtime = date('Y-m-d', strtotime($row['endtime']));
    echo '<div class="block">
<div class="block-title"><h3 class="panel-title">修改分站信息</h3></div>';
    echo '<div class="">';
    echo '<form action="./sitelist.php?my=edit_submit&zid=' . $zid . '" method="POST">
<div class="form-group">
<label>分站类型:</label><br>
<select class="form-control" name="power" default="' . $row['power'] . "\"><option value=\"1\">专业版</option><option value=\"2\">旗舰版</option></select>\r\n</div>" . ($row['power'] < 2 ? '<div class="form-group">
<label>上级站点ID:</label><br>
<input type="text" class="form-control" name="upzid" value="' . $row['upzid'] . "\" disabled>\r\n</div>" : null) . '<div class="form-group">
<label>绑定域名:</label><br>
<input type="text" class="form-control" name="siteurl" value="' . $row['siteurl'] . '" required>
</div>
<div class="form-group">
<label>额外域名:</label><br>
<input type="text" class="form-control" name="siteurl2" value="' . $row['siteurl2'] . '">
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
<label>站点名称:</label><br>
<input type="text" class="form-control" name="sitename" value="' . $row['sitename'] . '">
</div>
<div class="form-group">
<label>站点SEO标题:</label><br>
<input type="text" class="form-control" name="title" value="' . $row['title'] . '">
</div>
<div class="form-group">
<label>站点SEO关键词:</label><br>
<input type="text" class="form-control" name="keywords" value="' . $row['keywords'] . '">
</div>
<div class="form-group">
<label>站点SEO描述:</label><br>
<input type="text" class="form-control" name="description" value="' . $row['description'] . '">
</div>
<div class="form-group">
<label>结算账号:</label><br>
<input type="text" class="form-control" name="pay_account" value="' . $row['pay_account'] . '">
</div>
<div class="form-group">
<label>结算姓名:</label><br>
<input type="text" class="form-control" name="pay_name" value="' . $row['pay_name'] . '">
</div>
<div class="form-group">
<label>到期时间:</label><br>
<input type="date" class="form-control" name="endtime" value="' . $endtime . '" required>
</div>
<div class="form-group">
<label>重置密码:</label><br>
<input type="text" class="form-control" name="pwd" value="" placeholder="不重置请留空">
</div>
<input type="submit" class="btn btn-primary btn-block" value="确定修改"></form>';
    echo '<br/><a href="./sitelist.php">>>返回分站列表</a>';
    echo '</div></div>';
} elseif ($my == 'add_submit') {
    $power       = $_POST['power'];
    $user        = $_POST['user'];
    $pwd         = $_POST['pwd'];
    $siteurl     = $_POST['siteurl'];
    $siteurl2    = $_POST['siteurl2'];
    $money       = $_POST['money'];
    $qq          = $_POST['qq'];
    $endtime     = $_POST['endtime'];
    $sitename    = $conf['sitename'];
    $keywords    = $conf['keywords'];
    $description = $conf['description'];
    if ($user == null || $pwd == null || $siteurl == null || $endtime == null) {
        showmsg('保存错误,请确保每项都不为空!', 3);
    } else {
        $rows = $DB->get_row('select * from pre_site where user=\'' . $user . '\' limit 1');
        if ($rows) {
            showmsg('用户名已存在！', 3);
        }
        $rows = $DB->get_row('select * from pre_site where siteurl=\'' . $siteurl . '\' limit 1');
        if ($rows) {
            showmsg('域名已存在！', 3);
        }
        if ($conf['fenzhan_html'] == 1) {
            $anounce = $conf['anounce'];
            $bottom  = $conf['bottom'];
            $modal   = $conf['modal'];
        }
        $sql = 'insert into `pre_site` (`power`,`siteurl`,`siteurl2`,`user`,`pwd`,`money`,`qq`,`sitename`,`keywords`,`description`,`anounce`,`bottom`,`modal`,`addtime`,`endtime`,`status`) values (\'' . $power . '\',\'' . $siteurl . '\',\'' . $siteurl2 . '\',\'' . $user . '\',\'' . $pwd . '\',\'' . $money . '\',\'' . $qq . '\',\'' . $sitename . '\',\'' . $keywords . '\',\'' . $description . '\',\'' . addslashes($anounce) . '\',\'' . addslashes($bottom) . '\',\'' . addslashes($modal) . '\',\'' . $date . '\',\'' . $endtime . '\',\'1\')';
        if ($DB->query($sql)) {
            showmsg('添加分站成功！<br/><br/><a href="./sitelist.php">>>返回分站列表</a>', 1);
        } else {
            showmsg('添加分站失败！' . $DB->error(), 4);
        }
    }
} elseif ($my == 'edit_submit') {
    $zid  = $_GET['zid'];
    $rows = $DB->get_row('select * from pre_site where zid=\'' . $zid . '\' limit 1');
    if (!$rows) {
        showmsg('当前记录不存在！', 3);
    }
    $power    = $_POST['power'];
    $siteurl  = $_POST['siteurl'];
    $siteurl2 = $_POST['siteurl2'];
    $money    = round($_POST['money'], 2);
    $qq       = $_POST['qq'];
    $endtime  = $_POST['endtime'];
    $sitename = $_POST['sitename'];
    if ($sitename == "") {
        $sitename = "qq网";
    }
    $title       = trim(getParams('title', true, true));
    $keywords    = trim(getParams('keywords', true, true));
    $description = trim(getParams('description', true, true));
    $pay_account = $_POST['pay_account'];
    $pay_name    = $_POST['pay_name'];
    if (!empty($_POST['pwd'])) {
        $sql = ',pwd=\'' . $_POST['pwd'] . '\'';
    }
    if (($power > 0 && $siteurl == null) || $endtime == null) {
        showmsg('保存错误,请确保每项都不为空!', 3);
    } elseif ($DB->query('update pre_site set power=\'' . $power . '\',siteurl=\'' . $siteurl . '\',siteurl2=\'' . $siteurl2 . '\',money=\'' . $money . '\',qq=\'' . $qq . '\',sitename=\'' . $sitename . '\',title=\'' . $title . '\',keywords=\'' . $keywords . '\',description=\'' . $description . '\',pay_account=\'' . $pay_account . '\',pay_name=\'' . $pay_name . '\',endtime=\'' . $endtime . '\'' . $sql . ' where zid=\'' . $zid . '\'')) {
        showmsg('修改分站成功！<br/><br/><a href="./sitelist.php">>>返回分站列表</a>', 1);
    } else {
        showmsg('修改分站失败！' . $DB->error(), 4);
    }
} elseif ($my == 'delete') {
    $zid = intval(getParams('zid', true, true));
    $sql = "DELETE FROM pre_site WHERE zid=:zid";
    if ($DB->query($sql, [':zid' => $zid])) {
        showmsg('删除成功！<br/><br/><a href="./sitelist.php">>>返回分站列表</a>', 1);
    } else {
        showmsg('删除失败！' . $DB->error(), 4);
    }

} else {
    if (isset($_GET['zid'])) {
        $zid  = getParams('zid', true, true);
        $sql  = ' power>0 and zid=' . $zid;
        $link = '&zid=' . $zid;
        $rows = $DB->get_row("select * from pre_site where zid='" . $zid . "' limit 1");
        if ($rows['power'] == 0) {
            exit('<script language=\'javascript\'>window.location.href=\'./userlist.php?zid=' . $_GET['zid'] . '\';</script>');
        }
    } else {
        $sqls  = [' power>0'];
        $link  = '';
        $power = -1;

        if (isset($_GET['kw']) && $_GET['kw'] != '') {
            $kw     = trim(daddslashes($_GET['kw']));
            $sqls[] = ' (zid=\'' . $_GET['kw'] . '\' or user=\'' . $_GET['kw'] . '\' or siteurl=\'' . $_GET['kw'] . '\' or siteurl2=\'' . $_GET['kw'] . '\' or qq=\'' . $_GET['kw'] . '\') ';
            $link .= '&kw=' . $kw;
        }

        if (isset($_GET['power']) && $_GET['power'] != '') {
            $power = intval(input('power'));
            if (in_array($power, [1, 2])) {
                $sqls[] = " `power`={$power} ";
                $link .= '&power=' . $power;
            } else {
                $power = -1;
            }
        }

        $endtime = -1;
        if (isset($_GET['endtime']) && $_GET['endtime'] != '') {
            $endtime = input('endtime');
            if ($endtime == 1) {
                $sqls[] = " endtime>'{$date}' ";
                $link .= '&endtime=' . $endtime;
            } else if ($endtime == 2) {
                $sqls[] = " endtime<'{$date}' ";
                $link .= '&endtime=' . $endtime;
            } else {
                $endtime = -1;
            }
        }
        $status = -1;
        if (isset($_GET['status']) && $_GET['status'] != '') {
            $status = intval(input('status'));
            if ($status > -1) {
                $sqls[] = " status={$status} ";
                $link .= '&status=' . $status;
            } else {
                $status = -1;
            }
        }

        $iprice = -1;
        if (isset($_GET['iprice']) && $_GET['iprice'] != '') {
            $iprice = intval(input('iprice'));
            if ($iprice == 1) {
                $sqls[] = " (`iprice` IS NOT NULL OR `mid`>0) ";
                $link .= '&iprice=' . $status;
            } elseif ($iprice == 0) {
                $sqls[] = " (`iprice` IS NULL AND `mid`=0) ";
                $link .= '&iprice=' . $iprice;
            } else {
                $iprice = -1;
            }
        }
        $sql = implode(' AND ', $sqls);
    }

    $numrows = $DB->count('SELECT count(*) from pre_site where ' . $sql);
    $count   = $DB->count('SELECT count(*) from pre_site where ' . $sql . ' and (tel is not null and tel!=\'\')');
    $count1  = $DB->count('SELECT count(*) from pre_site where ' . $sql . ' and `status`=0');

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
    $rs     = $DB->query('SELECT * FROM pre_site WHERE ' . $sql . ' order by zid desc limit ' . $offset . ',' . $pagesize);
    // $lastSql = '<pre><code>SELECT * FROM pre_site WHERE ' . $sql . ' order by zid desc limit ' . $offset . ',' . $pagesize . "</code></pre>";
    $lastSql = '';
    echo '
    <div class="block">
    <div class="block-title"><h3 class="panel-title">分站列表</h3></div>
    <div class="alert alert-info">' . (count($sqls) > 0 ? "当前条件下" : "系统") . '共有 <b>' . $numrows . '</b> 个分站，' . $count . '个已绑定手机号，' . $count1 . '个已禁用<br/>
    </div>
   ' . $lastSql . '
    <form method="GET" class="form-inline">
        <div class="form-group">
        <a href="./sitelist.php?my=add" class="btn btn-primary">添加分站</a>&nbsp;
        <input type="text" onfocus="tips()" class="form-control" id="kw" name="kw" value="" placeholder="请输入分站ID、分站地址、分站用户名、分站QQ等">
        <div class="form-group">
            <select class="form-control" name="power" default="' . $power . '" id="power" placeholder="选择分站版本">
                <option value="-1">选择分站版本</option>
                <option value="1">专业版</option>
                <option value="2">旗舰版</option>
            </select>
        </div>
        <div class="form-group">
            <select class="form-control" name="endtime" default="' . $endtime . '"  placeholder="选择到期类型">
                <option value="-1">选择到期类型</option>
                <option value="1">未到期</option>
                <option value="2">已到期</option>
            </select>
        </div>
        <div class="form-group">
            <select class="form-control" name="status" default="' . $status . '" placeholder="选择分站状态">
                <option value="-1">选择分站状态</option>
                <option value="1">状态正常</option>
                <option value="0">状态禁用</option>
            </select>
        </div>
        <div class="form-group">
            <select class="form-control" name="iprice" default="' . $iprice . '" placeholder="选择密价状态">
                <option value="-1">选择密价状态</option>
                <option value="1">密价已设置</option>
                <option value="0">密价未设置</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">搜索</button>&nbsp;
        <a href="./sitelist.php" class="btn btn-warning">重置</a>&nbsp;
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">批量操作 <span class="caret"></span></button>
            <ul class="dropdown-menu">
                <li><a href="javascript:operation(1)">批量替换分站站点名称(模糊|全部分站)</a></li>
                <li><a href="javascript:operation(2)">批量替换分站域名(模糊|选中分站)</a></li>
                <li><a href="javascript:operation(3)">批量替换分站域名(模糊|全部分站)</a></li>
                <li><a href="javascript:operation(4)">批量重置分站密价(选中分站)</a></li>
                <li><a href="javascript:operation(5)">批量重置分站密价(全部分站)</a></li>
            </ul>
        </div>
    </form>
    ';
    echo '
    <form name="form1" id="form1">
     <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>ZID</th><th>上级站点</th><th>类型</th><th>用户名/密码</th><th>站点名称/站长QQ</th><th>余额/提成</th><th>手机号</th><th>开通/到期时间</th><th>绑定域名</th><th>属性</th><th>操作</th></tr></thead>
          <tbody>
';

    while ($res = $DB->fetch($rs)) {
        $tel = $res['tel'];
        if (empty($tel)) {
            $tel = "未绑定";
        }

        echo '<tr id="tr_' . $res['zid'] . '"><td><input type="checkbox" name="checkbox[]" id="list1" value="' . $res['zid'] . '" onClick="unselectall1()">&nbsp;&nbsp;<b>' . $res['zid'] . '</b></td><td>' . ($res['upzid'] > 0 ? '<a href="./sitelist.php?zid=' . $res['upzid'] . '">' . $res['upzid'] . '</a>' : '——') . '</td><td><span onclick="setSuper(' . $res['zid'] . ')" title="修改站点类型" class="btn btn-default btn-xs">' . ($res['power'] == 2 ? '<font color=red>旗舰版</font>' : '<font color=blue>专业版</font>') . '</span></td><td>' . $res['user'] . '<br>' . $res['pwd'] . '</td><td style="max-width:300px;word-break: break-all;">' . $res['sitename'] . '<br/>' . $res['qq'] . '</td><td><a onclick="showRecharge(' . $res['zid'] . ')" title="点击充值">' . $res['money'] . '<br>' . $res['point'] . '</a></td><td>' . $tel . '</td><td>' . $res['addtime'] . '<br/><a onclick="setEndtime(' . $res['zid'] . ')">' . $res['endtime'] . '</a></td><td><a href="//' . $res['siteurl'] . '" title="点击访问' . $res['siteurl'] . '" target="_blink" style="color:#3385ff">' . $res['siteurl'] . '</a><br/><a title="点击访问' . $res['siteurl2'] . '" href="//' . $res['siteurl2'] . '" target="_blink" style="color:#3385ff">' . $res['siteurl2'] . '</a></td><td><span title="设置密价，0表示未开启" class="btn btn-xs btn-orange" onclick="showSuperList(' . $res['zid'] . ',' . $res['mid'] . ')">密价(' . $res['mid'] . '|' . (!empty($res['iprice']) ? '已设置' : ($res['mid'] > 0 ? '已设置' : '未设置')) . ')</span>&nbsp;' . ($res['status'] == 1 ? '<span title="设置分站运行状态，设置为关闭后分站会登录不了，不影响前台访问和下单"  class="btn btn-xs btn-success" onclick="setActive(' . $res['zid'] . ',0)">开启</span>' : '<span title="设置分站运行状态，设置为关闭后分站会登录不了，不影响前台访问和下单" class="btn btn-xs btn-warning" onclick="setActive(' . $res['zid'] . ',1)">关闭</span>') . '&nbsp;<div class="dropdown" style="display:inline-block;"><a class="btn btn-default btn-sm dropdown-toggle" id="dropdownMenu' . $res['cid'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">更多 <span class="caret"></span></a><ul class="dropdown-menu dropdown-menu-right animated fadeInRight" role="menu" aria-labelledby="dropdownMenu' . $res['zid'] . '"><li role="presentation">' . display_pricedzt($res['zid'], $res['is_priced']) . '</li><li role="presentation">' . display_moneyzt($res['zid'], $res['regular']) . '</li><li role="presentation"><a onclick="rePrice(' . $res['zid'] . ')" title="操作后把分站的商品价格同步为主站当前设置的价格" class="dropdown-menu-right animated fadeInRight" role="menu" aria-labelledby="dropdownMenu' . $res['zid'] . '">重置分站价格设定</a></li></ul></div></td>';
        echo '<td><a href="./userjump.php?zid=' . $res['zid'] . '" target="_blink" title="一键免密登录到此分站后台" class="btn btn-info btn-xs">登录</a>&nbsp;<a href="./sitelist.php?my=edit&zid=' . $res['zid'] . '" class="btn btn-primary btn-xs">编辑</a>&nbsp;<a href="./list.php?zid=' . $res['zid'] . '" class="btn btn-warning btn-xs">订单</a>&nbsp;<a href="./record.php?zid=' . $res['zid'] . '" class="btn btn-success btn-xs">明细</a>&nbsp;<a href="./sitelist.php?my=delete&zid=' . $res['zid'] . '" class="btn btn-xs btn-danger" onclick="return confirm(\'你确实要删除此站点吗？\');">删除</a>';
        echo '</td></tr>';
    }
    echo '          </tbody>
        </table>

        <p><br><br></p>
      </div>
    </form>
';

# 分页
    $PageList = new \core\Page($numrows, $pagesize, 0, $link);
    echo $PageList->showPage();

}

echo '
 </div>
  </div>
<script src="./assets/js/sitelist.js?' . $jsver . '"></script>
<script type="text/javascript">
if(typeof pageLoad == "undefined" || pageLoad!==true){
    layer.alert("缺少静态js文件，请前往【运维管理-检测更新】点击立即更新并选择强制更新后再试！");
}
</script>
';

include_once 'footer.php';
