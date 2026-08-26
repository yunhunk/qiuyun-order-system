<?php if ($conf['invite_open'] == 1) {
    ?>
<div class="panel">
<div class="panel-heading"><h3 class="panel-title"><font color="#000000"><i class="fa fa-bar-chart-o"></i>&nbsp;&nbsp;<b>推广奖励统计</b></font> - <a href="?mod=invite" class="btn btn-warning btn-xs" style="color: white;margin-top:-0.2em;">点我免费领取 <i class=" fa fa-arrow-right"></i></a></h3></div>
<div class="btn-group btn-group-justified">
<a target="_blank" class="btn btn-effect-ripple btn-default collapsed"><b><font color="modal-title">领取QQ</font></b></a>
<a target="_blank" class="btn btn-effect-ripple btn-default collapsed"><b><font color="modal-title">完成时间</font></b></a>
<a target="_blank" class="btn btn-effect-ripple btn-default collapsed"><b><font color="modal-title">获得奖励</font></b></a>
</div>
<marquee class="zmd" behavior="scroll" direction="UP" onmouseover="this.stop()" onmouseout="this.start()" scrollamount="5" style="height:16em">
  <table class="table table-hover table-striped" style="text-align:center">
    <thead>
     <?php
$count = $DB->count("SELECT count(*) FROM pre_inviteorders WHERE 1");
    if ($count < 10) {
        $arr   = array('超级会员', '5元话费', '腾讯视频会员', 'CF会员', '影视会员月卡');
        $times = time();
        for ($i = 0; $i < 40; $i++) {
            $times = $times - mt_rand(800, 10800);
            $time  = date('Y-m-d H:i:s', $times);
            $qq    = mt_rand(111111, 9999999999);
            $qq    = substr($qq, 0, 3) . '***' . substr($qq, -2, 2);
            $name  = $arr[mt_rand(0, 6)];
            if ($name == "") {
                $name = "超级会员";
            }
            echo '<tr><td>恭喜QQ' . $qq . '</td><td>于' . $time . '推广成功</td><td>获得奖励' . $name . '</td></tr>';
        }
    } else {
        $rs = $DB->query("SELECT * FROM pre_inviteorders WHERE 1 order by rid desc limit 50");
        while ($res = $DB->fetch($rs)) {
            $row = $DB->get_row("select * from pre_invitetools where sid='" . $res['sid'] . "' limit 1");

            $shopimg = $row['shopimg'];
            if ($shopimg == "") {
                $trow    = $DB->get_row("select * from pre_tools where tid='" . $row['tid'] . "' limit 1");
                $shopimg = $trow['shopimg'];
            }
            $qq = substr($res['qq'], 0, 3) . '***' . substr($res['qq'], -2, 2);
            echo '<tr><td>恭喜QQ' . $qq . '</td><td>于' . $res['addtime'] . '推广成功</td><td><p style="color:red;text-align:left">获得奖励<img src="' . $shopimg . '" width="18px">' . $row['name'] . '</p></td></tr>';
        }
    }
    ?>
    </thead>
  </table>
</marquee>
</div>
<?php
}?>