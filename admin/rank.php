<?php
/**
 * 分站排行
 **/
include "../includes/common.php";
checkLogin();

$mod = isset($_GET['mod']) ? $_GET['mod'] : null;

checkAuthority('super');
include './head.php';

echo '<div class="col-xs-12 col-sm-12 col-lg-12 center-block" style="float: none;padding-top:10px;">';

if (isset($_GET['startTime']) && trim($_GET['startTime']) != "") {
    $startTime = trim(daddslashes($_GET['startTime']));
} else {
    $startTime = date('Y-m-d', time()) . ' 00:00:00';
}

if (isset($_GET['endTime']) && trim($_GET['endTime']) != "") {
    $endTime = trim(daddslashes($_GET['endTime']));
} else {
    $endTime = date('Y-m-d', strtotime("+1 day", time())) . ' 00:00:00';
}

if ($mod == 'buy') {
    $title = '分站消费排行榜';
    $sql   = "select a.zid,(select b.sitename from pre_site as b where a.zid=b.zid) as sitename,count(*) as count,sum(point) as point,(point/(select sum(`point`) from pre_points where addtime>='" . $startTime . "' and addtime<='" . $endTime . "' and action='消费')) as prop from pre_points as a where addtime>='" . $startTime . "' and addtime<='" . $endTime . "' and action='消费' group by zid order by point desc limit 500";
} elseif ($mod == 'money') {
    $title = '分站销售排行榜';
    $sql   = "select a.tid,a.zid,(select b.sitename from pre_site as b where a.zid=b.zid) as sitename,count(*) as count,sum(money) as money,(sum(`money`)/(select sum(money) from pre_orders where addtime>='" . $startTime . "' and addtime<='" . $endTime . "' )) as prop from pre_orders as a where addtime>='" . $startTime . "' and addtime<='" . $endTime . "' group by zid order by money desc limit 500";
} elseif ($mod == 'order') {
    $title = '分站订单排行榜';
    $sql   = "select a.tid,a.zid,(select b.sitename from pre_site as b where a.zid=b.zid) as sitename,count(*) as count,sum(money) as money,(count(*)/(select count(*) from pre_orders where addtime>='" . $startTime . "' and addtime<='" . $endTime . "' )) as prop from pre_orders as a where addtime>='" . $startTime . "' and addtime<='" . $endTime . "' group by zid order by count desc limit 500";
} elseif ($mod == 'point') {
    $title = '分站提成排行榜';
    $sql   = "select a.zid,(select b.sitename from pre_site as b where a.zid=b.zid) as sitename,count(*) as count,sum(point) as point,(point/(select sum(`point`) from pre_points where addtime>='" . $startTime . "' and addtime<='" . $endTime . "' and action='提成')) as prop from pre_points as a where addtime>='" . $startTime . "' and addtime<='" . $endTime . "' and action='提成' group by zid order by point desc limit 500";
} elseif ($mod == 'rmb') {
    $title = '分站余额排行榜';
    $sql   = "select a.zid,a.sitename,a.money as money,(a.money/(select sum(`money`) from pre_site where 1)) as prop from pre_site as a where 1 order by money desc limit 500";
} elseif ($mod == 'reg') {
    $title = '分站充值排行榜';
    $sql   = "select a.zid,(select b.sitename from pre_site as b where a.zid=b.zid) as sitename,count(*) as count,sum(point) as point,(point/(select sum(`point`) from pre_points where addtime>='" . $startTime . "' and addtime<='" . $endTime . "' and action='充值')) as prop from pre_points as a where addtime>='" . $startTime . "' and addtime<='" . $endTime . "' and action='充值' group by zid order by point desc limit 500";
} elseif ($mod == 'site') {
    $title = '域名订单排行榜';
    $sql   = "select a.zid,a.siteurl,(select b.sitename from pre_site as b where a.siteurl=b.siteurl or a.siteurl=b.siteurl2) as sitename,count(*) as count,sum(money) as money,(count/(select count(*) from pre_pay where addtime>='" . $startTime . "' and addtime<='" . $endTime . "' and tid!=-1 and tid!=-2 and tid!=-3 and status=1)) as prop from pre_pay as a where addtime>='" . $startTime . "' and addtime<='" . $endTime . "' and tid!=-1 and tid!=-2 and tid!=-3 and status=1 group by siteurl order by count desc";
} elseif ($mod == 'shop') {
    $title = '商品金额排行榜';
    $sql   = "select a.zid,a.tid,(select b.name from pre_tools as b where a.tid=b.tid) as name,count(*) as count,sum(money) as money,(select price1 from pre_tools as c where a.tid=c.tid) as price1,(money/(select sum(money) from pre_pay where addtime>='" . $startTime . "' and addtime<='" . $endTime . "' and tid!=-1 and tid!=-2 and tid!=-3)) as prop from pre_orders as a where addtime>='" . $startTime . "' and addtime<='" . $endTime . "' and tid!=-1 and tid!=-2 and tid!=-3 group by tid order by money desc";
} elseif ($mod == 'warning') {
    $title = '异常订单统计';

    $rs     = $DB->query("SELECT tid,name FROM pre_tools WHERE 1 order by sort asc");
    $select = '';
    while ($res = $DB->fetch($rs)) {
        $funcName[$res['tid']] = $res['name'];
    }

    $thtime    = date("Y-m-d") . ' 00:00:00';
    $newMoney  = 0;
    $newCost   = 0;
    $newProfit = 0;
    $newMoney  = $DB->count("SELECT sum(money) from pre_orders where addtime>='$thtime'");
    $rs        = $DB->query("SELECT * from pre_orders where addtime>='$thtime'");
    $fz_tc     = $DB->count("SELECT sum(point) from pre_points where addtime>='$thtime' and action='提成'");
    $list      = array();
    while ($res = $DB->fetch($rs)) {
        if ($res['money'] == 0) {
            continue;
        }

        $price1 = 0;
        $tool   = $DB->get_row("select price1,cost2,price FROM pre_tools WHERE tid='" . $res['tid'] . "'");
        if ($tool['price1'] > 0) {
            $price1 = $tool['price1'] * $res['value'];
            if ($price1 >= $res['money']) {
                $res['price1'] = $price1;
                $list[]        = $res;
            }
        }
    }

    echo '<div class="block">
     <div class="block-title"><h3 class="panel-title" id="rankTitle">当前数据：';
    echo $title;
    echo '</h3>
         <div class="" style="float: right !important;margin-top:8px;margin-right:8px">
                <a href="?mod=order"  class="btn btn-success btn-xs">订单排行</a>
                <a href="?mod=money"  class="btn btn-primary btn-xs">金额排行</a>
                <a href="?mod=point"  class="btn btn-info btn-xs">提成排行</a>
                <a href="?mod=buy"    class="btn btn-warning btn-xs">消费排行</a>
                <a href="?mod=rmb"    class="btn btn-danger btn-xs">余额排行</a>
                <a href="?mod=reg"    class="btn btn-success btn-xs">充值排行</a>
                <a href="?mod=site"    class="btn btn-primary btn-xs hide">域名排行</a>
         </div>
     </div>
      <table class="table table-striped">
          <thead><tr><th class="text-center">订单ID</th><th class="text-center">商品ID</th><th class="text-center">商品名称</th><th class="text-center">份数</th><th class="text-center">订单金额</th><th class="text-center">成本金额</th><th class="text-center">添加时间</th></tr></thead>
                  <tbody>
              ';
    $i = 1;
    foreach ($list as $res) {
        echo '<tr><td>' . $res['id'] . '</td><td>' . $res['tid'] . '</td><td>' . $funcName[$res['tid']] . '</td><td>' . $res['value'] . '</td><td>' . $res['money'] . '</td><td>' . $res['price1'] . '</td><td>' . $res['addtime'] . '</td></tr>';
    }

    echo '</tbody>
        </table>
    </div>';
    include_once 'footer.php';
    exit;
} else {
    showmsg('排行类型不存在或错误！请返回重试', 3);
    exit;
}
$link = 'mod=' . $mod;
$rs   = $DB->query($sql);

echo '<div class="block">
     <div class="block-title"><h3 class="panel-title" id="rankTitle">当前数据：';
echo $title;
echo '</h3>
         <div class="" style="float: right !important;margin-top:8px;margin-right:8px">
                <a href="?mod=order"  class="btn btn-success btn-xs">订单排行</a>
                <a href="?mod=money"  class="btn btn-primary btn-xs">金额排行</a>
                <a href="?mod=point"  class="btn btn-info btn-xs">提成排行</a>
                <a href="?mod=buy"    class="btn btn-warning btn-xs">消费排行</a>
                <a href="?mod=rmb"    class="btn btn-danger btn-xs">余额排行</a>
                <a href="?mod=reg"    class="btn btn-success btn-xs">充值排行</a>
                <a href="?mod=shop"    class="btn btn-info btn-xs">商品排行</a>
                <a href="?mod=site"   class="btn btn-primary btn-xs hide">域名排行</a>
         </div>
     </div>
      <div class="form-inline">
        <div class="form-group">
          <label>时间筛选：</label>
          <input type="text" class="form-control" id="startTime" value="';
echo $startTime;
echo '" name="startTime" placeholder="开始时间">
          ~
          <input type="text" class="form-control" id="endTime" name="endTime" value="';
echo $endTime;
echo '" placeholder="结束时间">
          <a id="search" class="btn btn-primary">查询</a>
        </div>
      </div>
      <table class="table table-striped">
          <thead><tr><th class="text-center">排名</th>';
echo $mod != 'shop' ? '<th class="text-center">分站ID</th>' : null;
echo $mod == 'order' || $mod == 'money' || $mod == 'shop' ? '<th class="text-center">商品ID</th>' : null;
echo $mod != 'shop' ? '<th class="text-center">分站名称</th>' : null;
echo $mod == 'shop' ? '<th class="text-center">商品名称</th>' : null;
echo $mod == 'buy' ? '<th class="text-center">消费金额</th>' : null;
echo $mod == 'point' ? '<th class="text-center">提成</th>' : null;
echo $mod == 'reg' ? '<th class="text-center">充值金额</th>' : null;
echo $mod == 'site' ? '<th class="text-center">网址</th>' : null;
echo $mod == 'money' || $mod == 'order' || $mod == 'site' || $mod == 'shop' ? '<th class="text-center">销售订单</th>' : null;
echo $mod == 'money' || $mod == 'order' || $mod == 'site' || $mod == 'shop' ? '<th class="text-center">销售金额</th>' : null;
echo $mod == 'shop' ? '<th class="text-center">成本金额</th>' : null;
echo $mod == 'rmb' ? '<th class="text-center">分站余额</th>' : null;
echo '<th class="text-center">占比</th></tr></thead>
          <tbody>
      ';
$i      = 1;
$data   = "";
$money  = 0;
$price1 = 0;
$count  = 0;
while ($res = $DB->fetch($rs)) {
    if ($res['zid'] < 2) {
        $res['zid']      = 1;
        $res['sitename'] = $conf['sitename'];
    }
    $prop = round($res['prop'] * 100, 2);
    $data .= '<tr>
       <td class="text-center"><span class="badge badge-danger">' . $i . '</span></td>';
    $data .= $mod != 'shop' ? '<td class="text-center"><a href="sitelist.php?zid=' . $res['zid'] . '"><b>' . $res['zid'] . '</b></a></td> ' : null;
    $data .= $mod == 'order' || $mod == 'money' || $mod == 'shop' ? '<td class="text-center"><a href="shopedit.php?my=edit&tid=' . $res['tid'] . '"><b>' . $res['tid'] . '</b></a></td>' : null;
    $data .= $mod != 'shop' ? '<td class="text-center">' . $res['sitename'] . '</td>' : null;
    $data .= $mod == 'shop' ? '<td class="text-center">' . $res['name'] . '</td>' : null;
    $data .= $mod == 'buy' ? '<td class="text-center">' . $res['point'] . '元</td>' : null;
    $data .= $mod == 'point' ? '<td class="text-center">' . $res['point'] . '元</td>' : null;
    $data .= $mod == 'site' ? '<td class="text-center">' . $res['siteurl'] . '</td>' : null;
    $data .= $mod == 'reg' ? '<td class="text-center">' . $res['point'] . '元</td>' : null;
    $data .= $mod == 'money' || $mod == 'order' || $mod == 'site' || $mod == 'shop' ? '<td class="text-center"><a href="list.php?tid=' . $res['tid'] . '"><b>' . $res['count'] . '</b></a>个</td>' : null;
    $data .= $mod == 'money' || $mod == 'order' || $mod == 'site' || $mod == 'shop' ? '<td class="text-center">' . $res['money'] . '</td>' : null;
    $data .= $mod == 'rmb' ? '<td class="text-center">' . $res['money'] . '</td>' : null;
    $data .= $mod == 'shop' ? '<td class="text-center">' . round($res['price1'] * $res['count'], 2) . '</td>' : null;
    $data .= '<td class="text-center">' . $prop . '%</td></tr>';
    $i++;
    if ($mod == 'shop') {
        $money += round($res['money'], 2);
        if ($res['money'] > 0) {
            $price1 += round($res['price1'] * $res['count'], 2);
        }
        $count += $res['count'];
    }
}

if ($mod == 'shop') {
    $data = '<tr class="text-center"><td colspan="2.3">销售订单：' . $count . '条</td><td colspan="2.3">销售总额：' . $money . '元</td><td colspan="2.3">成本总额：' . $price1 . '元</td></tr>' . $data;
}

echo $data;

echo '</tbody>
        </table>
    </div>
    <script src="../assets/public/layDate-v5.0.9/laydate.js" type="text/javascript"></script>
    <script>
    $("#search").click(function (){
       var startTime=$("#startTime").val();
       var endTime=$("#endTime").val();
       window.location.href="?' . $link . '&startTime="+startTime+"&endTime="+endTime;
    });
    laydate.render({
      elem: "#startTime"
      ,theme: "molv"
      ,type: "datetime"
    });
    laydate.render({
      elem: "#endTime"
      ,theme: "#393D49"
      ,type: "datetime"
    });
    </script>
';

include_once 'footer.php';
