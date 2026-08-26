<?php
/**
 * 支付记录
 **/
include "../includes/common.php";
checkLogin();
$title = '支付记录';

checkAuthority('super');
include './head.php';

echo '<div class="col-md-12 center-block" style="float: none;">';

if (isset($_GET['zid'])) {
    $zid  = intval($_GET['zid']);
    $sql  = " zid='" . $zid . "'";
    $link = '&zid=' . $zid;
} elseif (isset($_GET['kw']) && $_GET['kw'] != "") {
    $kw  = trim(addslashes($_GET['kw']));
    $sql = " zid='" . $kw . "' OR trade_no='" . $kw . "' OR input like '%" . $kw . "%'  OR siteurl='" . $kw . "' OR type='" . $kw . "'";
} else {
    $sql = " 1";
}

$numrows = $DB->count("SELECT count(*) from pre_pay WHERE {$sql}");

echo '<div class="row">
  <div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top: 10px;">
<div class="block">
     <div class="block-title"><h2>订单支付记录</h2></div>
     <div class="alert alert-info">
      其中未付款状态的支付订单在易支付接口下也可能是未通知成功导致的，如有异常情况请自行检查支付接口的订单状态<br/>
      同步：<font color="red">如果出现已付款又没有订单，可点击此按钮对该订单状态同步并且执行补单</font><br/>
      补单：<font color="red">如果易支付补单无效，可手动强制补单</font>
      </div>
      <div class="table-responsive">
        <form method="get">
      <div class="input-group xs-mb-15">
        <input type="text" placeholder="请输入要搜索支付记录的站点ID或支付类型或来源地址！" name="kw"
             class="form-control text-center"
             required>
        <span class="input-group-btn">
        <button type="submit" class="btn btn-primary">立即搜索</button>
        </span>
      </div>
    </form>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>订单号</th><th>商品名称</th><th>站点ID</th><th>订单数据</th><th>来源地址</th><th>来源IP</th><th>支付方式</th><th>金额</th><th>状态</th><th>回调结果</th><th>时间</th><th>操作</th></tr></thead>
          <tbody>';

$pagesize = 30;
$pages    = ceil($numrows / $pagesize);
$page     = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset   = $pagesize * ($page - 1);
$rs       = $DB->query("SELECT * FROM pre_pay WHERE{$sql} order by addtime desc limit $offset,$pagesize");
while ($res = $DB->fetch($rs)) {
    if ($res['status'] == 1) {
        $status = '<span class="btn btn-success btn-xs">成功</span>';
    } else {
        $status = '<span class="btn btn-danger btn-xs">未付款</span>';
    }

    $call_ret = $res['call_ret'];
    if (mb_strlen($call_ret) > 500) {
        $call_ret = mb_substr($call_ret, 0, 500);
    }
    echo '<tr><td><b>' . $res['trade_no'] . '</b></td><td><a href="shoplist.php?tid=' . $res['tid'] . '">' . $res['name'] . '</a></td><td><a href="sitelist.php?zid=' . $res['zid'] . '">' . $res['zid'] . '</a></td><td><div style="width:300px;overflow: auto;">' . $res['input'] . '</div></td><td>' . $res['siteurl'] . '</td><td>' . $res['ip'] . '</td><td>' . $res['type'] . '</td><td>' . $res['money'] . '</td><td>' . $status . '</td><td style="max-width: 320px;word-break: break-all;"><p>' . $call_ret . '</p></td><td>' . $res['addtime'] . '</td><td><a href="/cron/payCron.php?act=payCron&key=' . $conf['cronkey'] . '&out_trade_no=' . $res['trade_no'] . '" class="btn btn-success btn-xs" target="_blank">同步</a>&nbsp;<a onclick="filler(\'' . $res['trade_no'] . '\')" class="btn btn-primary btn-xs" target="_blank">补单</a></td></tr>';
}

echo '</tbody>
        </table>
      </div>';

#分页
$pageList = new \core\Page($numrows, $pagesize, 0, $link);
echo $pageList->showPage();
#分页

echo ' </div>
  </div>
 </div>
</div>
<script type="text/javascript">
function filler(trade_no) {
    layer.confirm("是否确认为【" + trade_no + "】补单？该操作无法撤销", function() {
        var ii = layer.load(2, {
            shade: [0.1, "#fff"]
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=pay_filler",
            dataType: "json",
            data: {
                trade_no: trade_no
            },
            success: function(data) {
                if (data.code == 0) {
                    layer.msg(data.msg, {
                        icon: 6
                    });
                } else {
                    layer.msg(data.msg, {
                        icon: 5
                    });
                }
            },
            error: function(data) {
                layer.msg("服务器错误");
                return false;
            }
        });
    })
}
</script>
';

include_once 'footer.php';
