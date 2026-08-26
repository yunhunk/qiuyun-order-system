<?php
/**
 * 社区对接日志
 **/
include "../includes/common.php";
checkLogin();
$title = '登录日志';
include './head.php';

function display_type($power)
{
    if ($power == 3) {
        return '主站登录';
    } elseif ($power == 2) {
        return '旗舰分站';
    } elseif ($power == 1) {
        return '专业分站';
    } elseif ($power == 0) {
        return '注册用户';
    } else {
        return '未知代理';
    }

}

if (isset($_GET['kw']) && $_GET['kw'] != "") {
    $kw       = trim(addslashes($_GET['kw']));
    $numrows  = $DB->count("SELECT count(*) from pre_fzlogs WHERE zid= ? or ip = ?", array($kw, $kw));
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
    $rs     = $DB->query("SELECT * FROM pre_fzlogs WHERE zid= ? or ip = ? order by id desc limit ?, ?", array($kw, $kw, $offset, $pagesize));
} else {
    $numrows  = $DB->count("SELECT count(*) from pre_fzlogs WHERE 1");
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
    $rs     = $DB->query("SELECT * FROM pre_fzlogs WHERE 1 order by id desc limit ?, ?", array($offset, $pagesize));
}

echo '<div class="col-xs-12 col-sm-12 col-lg-12 center-block" style="float: none;padding-top:10px;">
 <div class="block">
    <div class="block-title">
      <h3 class="panel-title">' . $title . '</h3>
    </div>
       <form method="get">
        <div class="input-group xs-mb-15">
          <input type="text" placeholder="请输入要搜索登录日志的站点ID或明细类型！" name="kw"
               class="form-control text-center"
               required>
          <span class="input-group-btn">
          <button type="submit" class="btn btn-primary">立即搜索</button>
          </span>
        </div>
      </form>
      当前条件下共有' . $numrows . '条日志
       <div class="table-responsive">
        <table class="table table-striped" style="word-break: break-all;">
          <thead><th style="min-width: 80px;">Id</th><th>站点ID</th><th style="min-width: 70px;">站点类型</th><th style="max-width: 700px;">详情</th><th style="max-width: 170px;">结果</th><th>ip</th><th style="min-width:170px;">时间</th></thead>
          <tbody>';

while ($res = $DB->fetch($rs)) {
    if ($res['zid'] > 0) {
        $row    = $DB->get_row("select power from pre_site where zid= ? limit 1", array($res['zid']));
        $action = display_type($row['power']);
    } else {
        $action = '主站登录';
    }
    echo '<tr><td>' . $res['id'] . '<td><a href="sitelist.php?zid=' . $res['zid'] . '">' . $res['zid'] . '</td><td>' . $action . '</td><td style="max-width: 700px;">' . htmlspecialchars($res['param']) . '</td><td>' . $res['result'] . '</td><td>' . $res['ip'] . '</td><td style="min-width:170px;">' . $res['addtime'] . '</td></tr>';
}

echo ' </tbody>
        </table>
      ';
#分页
$pageList = new \core\Page($numrows, $pagesize, 0, $link);
echo $pageList->showPage();

echo '</div>
  </div>
';

include_once 'footer.php';
