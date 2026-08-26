<?php
/**
 * 分站管理
 **/
include "../includes/common.php";
$title = '分站管理';
include './head.php';
if ($isLogin2 == 1) {} else {
    $goto = @getHostUrl();

    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

?>
<div class="wrapper">
<div class="col-sm-12">
        <div class="panel panel-default">
<div class="modal fade" align="left" id="search" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">搜索分站</h4>
      </div>
      <div class="modal-body">
      <form action="sitelist.php" method="GET">
<input type="text" class="form-control" name="kw" placeholder="请输入分站用户名或域名或QQ"><br/>
<input type="submit" class="btn btn-primary btn-block" value="搜索"></form>
</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php
if ($userrow['power'] < 2) {
    showmsg('你没有权限使用此功能！', 3);
}
$my              = isset($_GET['my']) ? $_GET['my'] : null;
$fenzhan_siteurl = $conf['fenzhan_siteurl'];
if ($fenzhan_siteurl == "") {
    $fenzhan_siteurl = $conf['fenzhan_domain'];
}

if ($my == 'add') {
    $siteurls = explode(',', $fenzhan_siteurl);
    $select   = '';
    foreach ($siteurls as $siteurl) {
        $select .= '<option value="' . $siteurl . '">' . $siteurl . '</option>';
    }
    echo '<div class="panel panel-default">
<div class="panel-heading font-bold" style="background-color: #9999CC;color: white;">添加一个分站</div>';
    echo '<div class="panel-body">';
    echo '<form action="./sitelist.php?my=add_submit" method="POST">
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
<div class="input-group">
<input type="text" class="form-control" name="qz" value="" placeholder="输入二级前缀" required>
<div class="input-group-addon"><select name="siteurl">' . $select . '</select></div>
</div>
</div>
<div class="form-group">
<label>网站名称:</label><br>
<input type="text" class="form-control" name="sitename" value="' . $conf['sitename'] . '">
</div>
<div class="form-group">
<label>站长QQ:</label><br>
<input type="text" class="form-control" name="qq" value="">
</div>
<div class="form-group">
<label>到期时间:</label><br>
<input type="date" class="form-control" name="endtime" value="' . date("Y-m-d", strtotime("+1 years")) . '" required>
</div>
<input type="submit" class="btn btn-primary btn-block" value="确定添加"></form>';
    echo '<br/><a href="./sitelist.php">>>返回分站列表</a>';
    echo '</div></div>';
} elseif ($my == 'edit') {
    $zid = intval($_GET['zid']);
    $row = $DB->get_row("select * from cmy_site where zid= ? and upzid= ? and power=1 limit 1", array($zid, $userrow['zid']));
    if (!$row) {
        showmsg('当前记录不存在！', 3);
    }

    echo '<div class="panel panel-default">
<div class="panel-heading font-bold" style="background-color: #9999CC;color: white;">修改分站信息</div>';
    echo '<div class="panel-body">';
    echo '<form action="./sitelist.php?my=edit_submit&zid=' . $zid . '" method="POST">
<div class="form-group">
<label>绑定域名:</label><br>
<input type="text" class="form-control" name="siteurl" value="' . $row['siteurl'] . '" disabled>
</div>
<div class="form-group">
<label>额外域名:</label><br>
<input type="text" class="form-control" name="siteurl2" value="' . $row['siteurl2'] . '" placeholder="没有请留空">
<small>如需绑定自己域名，请联系网站客服绑定</small>
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
<label>到期时间:</label><br>
<input type="date" class="form-control" name="endtime" value="' . date("Y-m-d", strtotime($row['endtime'])) . '" required>
</div>
<input type="submit" class="btn btn-primary btn-block" value="确定修改"></form>';
    echo '<br/><a href="./sitelist.php">>>返回分站列表</a>';
    echo '</div></div>';
} elseif ($my == 'add_submit') {
    $user        = trim(strip_tags(daddslashes($_POST['user'])));
    $pwd         = trim(strip_tags(daddslashes($_POST['pwd'])));
    $qz          = trim(strtolower(daddslashes($_POST['qz'])));
    $domain      = trim(strtolower(strip_tags(daddslashes($_POST['siteurl']))));
    $qq          = trim(strip_tags(daddslashes($_POST['qq'])));
    $endtime     = trim(strip_tags(daddslashes($_POST['endtime'])));
    $sitename    = trim(strip_tags(daddslashes($_POST['sitename'])));
    $keywords    = daddslashes($conf['keywords']);
    $description = daddslashes($conf['description']);
    $siteurl     = $qz . '.' . $domain;
    $thtime      = date("Y-m-d") . ' 00:00:00';
    if ($user == null or $pwd == null or $qz == null or $siteurl == null or $endtime == null) {
        showmsg('保存错误,请确保每项都不为空!', 3);
    } elseif (!in_array($domain, explode(',', $fenzhan_siteurl))) {
        showmsg('该域名后缀无法用于分站搭建');
    } elseif (strlen($qz) < 2 || strlen($qz) > 10 || !preg_match('/^[a-z0-9\-]+$/', $qz)) {
        showmsg('域名前缀不合格！');
    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $user)) {
        showmsg('用户名只能为英文或数字！');
    } elseif (!preg_match('/^[a-zA-Z0-9\_\-\.]+$/', $siteurl)) {
        showmsg('域名格式不正确');
    } elseif ($DB->get_row("SELECT * FROM cmy_site WHERE `user`='{$user}' limit 1")) {
        showmsg('用户名已存在！');
    } elseif (strlen($pwd) < 6) {
        showmsg('密码不能低于6位');
    } elseif (strlen($sitename) < 2) {
        showmsg('网站名称太短！');
    } elseif (strlen($qq) < 5 || !preg_match('/^[0-9]+$/', $qq)) {
        showmsg('QQ格式不正确！');
    } elseif (siteIsUse($qz, $siteurl)) {
        showmsg('此前缀已被使用！');
    } elseif ($DB->count("SELECT count(*) FROM cmy_site WHERE upzid='{$userrow['zid']}' and addtime>'$thtime'") > 10) {
        showmsg('你今天添加的分站较多，暂无法后台手动添加，请直接使用前台网址自助开通分站！', 3);
    } else {
        if ($conf['fenzhan_html'] == 1) {
            $anounce = daddslashes($conf['anounce']);
            $alert   = daddslashes($conf['alert']);
        }

        if ($conf['fenzhan_cost'] > 0) {
            //从余额扣除添加分站的成本
            if ($conf['fenzhan_cost'] > getUserRmb()) {
                showmsg('添加专业版分站需要' . $conf['fenzhan_cost'] . '元，当前余额不还差' . round($conf['fenzhan_cost'] - getUserRmb(), 2) . '元', 3);
            }
            $DB->exec(
                "UPDATE `pre_site` set `money`=`money`-:price where `zid`=:zid",
                [':price' => $conf['fenzhan_cost'], ':zid' => $userrow['zid']]
            );
        }

        $sql = "INSERT into `pre_site` (`power`,`upzid`,`siteurl`,`siteurl2`,`user`,`pwd`,`money`,`qq`,`sitename`,`keywords`,`description`,`anounce`,`alert`,`addtime`,`endtime`,`status`) values ('1', ?, ?,NULL, ?, ?,'0', ?, ?, ?, ?, ?, ?, ?, ?,'1')";
        if ($DB->query($sql, array($userrow['zid'], $siteurl, $user, $pwd, $qq, $sitename, $keywords, $description, $anounce, $alert, $date, $endtime))) {
            showmsg('添加分站成功！<br/><br/><a href="./sitelist.php">>>返回分站列表</a>', 1);
        } else {
            showmsg('添加分站失败！' . $DB->error(), 4);
        }

    }
} elseif ($my == 'edit_submit') {
    $zid  = intval(input('post.zid'));
    $rows = $DB->get_row("SELECT * from cmy_site where zid= ? and upzid= ? and power=1 limit 1", [$zid, $userrow['zid']]);
    if (!$rows) {
        showmsg('当前记录不存在！', 3);
    }

    $siteurl2 = input('post.siteurl2');
    $qq       = input('post.qq');
    $endtime  = input('post.endtime');
    $sitename = input('post.sitename');
    if ($sitename == null or $endtime == null) {
        showmsg('保存错误,请确保每项都不为空!', 3);
    } elseif (!empty($siteurl2) && !preg_match('/^[a-zA-Z0-9\_\-\.]+$/', $siteurl2)) {
        showmsg('域名格式不正确');
    } else {
        $fenzhan_domains = explode(',', $fenzhan_siteurl);
        if (!empty($siteurl2)) {
            $ok = 0;
            foreach ($fenzhan_domains as $key => $domains) {
                if (preg_match('/' . $domains . '$/', $siteurl2) && strlen($siteurl2) > strlen($domains)) {

                    $ok++;
                }
            }

            if ($ok < 1) {
                showmsg('该域名后缀无法用于搭建分站');
            }
            if (siteIsUse(str_replace($domains, '', $siteurl2), $siteurl2)) {
                showmsg('此域名已被使用！');
            } elseif (strpos($siteurl2, 'www.') !== false) {
                $siteurl = str_replace('www.', '', $siteurl2);
                if (in_array($siteurl, explode(',', $conf['fenzhan_remain'])) || in_array($siteurl, explode(',', $fenzhan_siteurl))) {
                    showmsg('此域名已被使用！');
                }
            }
        }

        if ($DB->query("UPDATE `pre_site` set siteurl2='$siteurl2',qq='$qq',sitename='$sitename',endtime='$endtime' where zid='{$zid}'")) {
            showmsg('修改分站成功！<br/><br/><a href="./sitelist.php">>>返回分站列表</a>', 1);
        } else {
            showmsg('修改分站失败！' . $DB->error(), 4);
        }

    }
} elseif ($my == 'delete') {
    $zid = intval(input('post.zid'));
    if ($conf['fenzhan_deluser_open'] < 1) {
        showmsg("系统未开启此功能！", 3);
    }
    if ($conf['fenzhan_deluser_open'] == 2) {
        $sql = 'DELETE FROM cmy_site WHERE zid=\'' . $zid . '\'';
        if ($DB->query($sql)) {
            showmsg('删除成功！<br/><br/><a href="./sitelist.php">>>返回分站列表</a>', 1);
        } else {
            showmsg('删除失败！' . $DB->error(), 4);
        }
    } else {
        $hideArr = explode(",", $userrow['hidezid']);
        if (!in_array($zid, $hideArr)) {
            array_push($hideArr, $zid);
        }
        $hidezid = implode(",", $hideArr);
        $sql     = 'UPDATE cmy_site set `hidezid`=\'' . $hidezid . '\' WHERE zid=\'' . $zid . '\'';
        if ($DB->query($sql)) {
            showmsg('删除成功！<br/><br/><a href="./sitelist.php">>>返回分站列表</a>', 1);
        } else {
            showmsg('删除失败！' . $DB->error(), 4);
        }
    }
} else {

    $pagesize = 30;
    $pages    = ceil($numrows / $pagesize);
    $page     = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset   = $pagesize * ($page - 1);

    $numrows = $DB->count("SELECT count(*) from cmy_site where upzid= ? and power=1", array($userrow['zid']));
    if (isset($_GET['zid'])) {
        $zid = intval($_GET['zid']);
        $rs  = $DB->query("SELECT * FROM cmy_site WHERE zid= ? and upzid= ? and power=1 order by zid desc limit ?, ?", array($zid, $userrow['zid'], $offset, $pagesize));
    } elseif (isset($_GET['kw'])) {
        $kw = daddslashes($_GET['kw']);
        $rs = $DB->query("SELECT * FROM cmy_site WHERE (user= ? or siteurl= ? or qq= ?) and upzid= ? and power=1 order by zid desc limit ?, ?", array($kw, $kw, $kw, $userrow['zid'], $offset, $pagesize));
    } else {
        $rs = $DB->query("SELECT * FROM cmy_site WHERE upzid= ? and power=1 order by zid desc limit ?, ?", array($userrow['zid'], $offset, $pagesize));
    }

    $con = '你共有 <b>' . $numrows . '</b> 个下级分站<br/><a href="./sitelist.php?my=add" class="btn btn-primary">添加分站</a>&nbsp;<a href="#" data-toggle="modal" data-target="#search" id="search" class="btn btn-success">搜索</a>';

    echo '<div class="alert" style="background-color: #9999CC;color: white;">';
    echo $con;
    echo '</div>';

    ?>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>ZID</th><th>用户名</th><th>站点名称/站长QQ</th><?php echo $conf['fenzhan_readmoney'] == 1 ? '<th>余额</th>' : ''; ?><th>开通/到期时间</th><th>绑定域名</th><th>操作</th></tr></thead>
          <tbody>
<?php

    $hideArr = explode(",", $userrow['hidezid']);

    while ($res = $DB->fetch($rs)) {
        if (in_array($res['zid'], $hideArr)) {
            continue;
        }

        echo '<tr><td><b>' . $res['zid'] . '</b></td><td>' . $res['user'] . '</td><td>' . $res['sitename'] . '<br/>' . $res['qq'] . '</td>' . ($conf['fenzhan_readmoney'] == 1 ? '<td>' . $res['money'] . '</td>' : '') . '<td>' . $res['addtime'] . '<br/>' . $res['endtime'] . '</td><td>' . $res['siteurl'] . '<br/>' . $res['siteurl2'] . '</td><td><a href="./sitelist.php?my=edit&zid=' . $res['zid'] . '" class="btn btn-info btn-xs">编辑</a>&nbsp;<a href="./sitelist.php?my=delete&zid=' . $res['zid'] . '" class="btn btn-xs btn-danger" onclick="return confirm(\'你确实要删除此站点吗？\');">删除</a></td></tr>';
    }
    ?>
          </tbody>
        </table>
      </div>
<?php
#分页
    $PageList = new \core\Page($numrows, $pagesize, 0, $link);
    echo $PageList->showPage();
}
?>
    </div>
  </div>
  <?php include 'footer.php'?>