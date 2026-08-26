<?php
include_once '../includes/common.php';
$yanz_open = 1; //ip地址验证开关
$act       = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

if ($act == "inviteurl") {
    $sid    = (int) daddslashes($_POST['sid']);
    $qq     = (int) daddslashes($_POST['qq']);
    $result = getInviteUrl($sid, $qq);
    exit(json_encode($result));
} elseif ($act == "checkKey") {
    $t      = addslashes($_POST['t']);
    $thtime = date('Y-m-d') . ' 00:00:00';
    $row    = $DB->get_row("select active from pre_inviteorders where t=:t limit 1", [':t' => $t]);
    $logs   = $DB->get_row("select rid from pre_invitelogs where ip=:clientip and addtime>=:thtime limit 1", [':clientip' => $clientip, ':thtime' => $thtime]);
    if ($logs) {
        $result = array('code' => -1, 'msg' => "今天已经帮助过好友啦");
    } elseif ($row && $row['active'] == 0) {
        $result = array('code' => 0, 'msg' => "succ");
    } else {
        $result = array('code' => -1, 'msg' => "不存在或存在违规已被拉黑");
    }
    exit(json_encode($result));
} elseif ($act == "tgUrl") {
    if (stripos($conf['ips_list'], '|') !== false) {
        $ips     = explode('|', $conf['ips_list']);
        $ips_arr = array();
        foreach ($ips as $value) {
            if (stripos($value, '*') !== false) {
                $ip = substr($value, 0, stripos($value, '*'));
            } else {
                $ip = $value;
            }

            if (stripos($clientip, $ip) !== false) {
                exit(json_encode(array('code' => -1, 'msg' => "该ip已被拉黑，无法领取推广任务")));
            }
        }
    } else {
        if ($conf['ips_list'] != "") {
            if (stripos($conf['ips_list'], '*') !== false) {
                $ip = substr($value, 0, stripos($conf['ips_list'], '*'));
            } else {
                $ip = $value;
            }

            if (stripos($clientip, $ip) !== false) {
                exit(json_encode(array('code' => -1, 'msg' => "该ip已被拉黑，无法领取推广任务")));
            }
        }
    }
    $t      = trim(daddslashes($_POST['t']));
    $result = addInvite($t);
    exit(json_encode($result));
} else if ($act == "getUrl") {
    $rid = intval($_POST['rid']);
    $row = $DB->get_row("select * from pre_inviteorders where rid=:rid limit 1", [':rid' => $rid]);
    if ($row) {
        $srow = $DB->get_row("select * from pre_invitetools where sid=:sid limit 1", [':sid' => $row['sid']]);
        $t    = $row['t'];

        $urls = explode('|', $conf['zz_urls_list']);
        foreach ($urls as $url) {
            if (stripos($srow['url'], $url) != false) {
                $tgurl   = $srow['url'];
                $siteurl = $srow['url'];
                break;
            }
        }

        if ($tgurl == "") {
            $tgurl = getTgUrl($urls, $row['t']);
        }

        if ($conf['invite_text']) {
            $text = $conf['invite_text'];
            $text = str_replace('[URL]', $tgurl, $text);
            $text = str_replace(array("\r\n", "\r", "\n"), "", $text);
            $text = str_replace(array("<br>", "[换行]", "<p>"), "\n", $text);
            if (stripos($text, '[URL]') === false) {
                $text = $text . $tgurl;
            }
            $text = strip_tags($text);
        } else {
            $text = "哇，好消息！ 给你们分享一个很厉害的网站\n每天可以免费领取名片贊、空间说说贊、全民K歌、永久QQ钻等等业务\n领取地址：" . $tgurl;
        }

        $result = array('code' => 0, 'msg' => "succ", 'url' => $tgurl, 'name' => $srow['name'], 'text' => $text);
        exit(json_encode($result));
    } else {
        $result = array('code' => -1, 'msg' => "该推广任务不存在！");
    }
    exit(json_encode($result));
} elseif ($act == "tools") {
    $tid = (int) daddslashes($_POST['tid']);
    $sid = (int) daddslashes($_POST['sid']);
    $row = $DB->get_row("select * from pre_invitetools where sid=:sid and status=1 limit 1", [':sid' => $sid]);
    if ($row) {
        $tool = $DB->get_row("select * from pre_tools where tid=:tid limit 1", [':tid' => $tid]);
        if ($tool) {
            if ($row['desc'] != "") {
                $tool['desc'] = $row['desc'];
            }

            $result = array('code' => 0, 'msg' => "succ", 'data' => $tool);
        } else {
            $result = array('code' => -1, 'msg' => "该奖励商品信息不完整或不正确，请联系客服处理！");
        }
    } else {
        $result = array('code' => -1, 'msg' => "该奖励商品不存在或已下架，请联系客服处理！", 'data' => $tool);
    }
    exit(json_encode($result));
} elseif ($act == "jindu") {
    $qq = intval(guolv($_POST['qq']));
    if (empty($qq) || !is_numeric($qq)) {
        $result = array('code' => -1, 'msg' => "请输入正确的QQ");
        exit(json_encode($result));
    } else {
        $rs   = $DB->query("select * from pre_inviteorders where qq='" . $qq . "' order by rid desc");
        $data = '<table class="table table-hover table-striped" style="text-align:center">';
        $data .= ' <tbody><tr><td style="text-align: center;">领取QQ</td> <td style="text-align: center;">奖励名称</td> <td style="text-align: center;">目标</td><td style="text-align: center;">进度</td><td style="text-align: center;">状态</td><td style="text-align: center;">操作</td></tr></tbody>';
        $data .= '<tbody>';
        $i = 0;
        foreach ($rs as $res) {
            $i++;
            $tool = $DB->get_row("select * from pre_invitetools where sid='" . $res['sid'] . "' limit 1");
            if ($res['status'] == 1) {
                $status = '<font color=green>已完成</font>';
            } else {
                $status = '<font color=orange>进行中</font>';
            }
            $data .= '<tr><td style="text-align: center;">' . $res['qq'] . '</td> <td style="text-align: center;">' . $tool['name'] . '</td><td style="text-align: center;">' . $res['countNum'] . '</td><td style="text-align: center;">' . $res['nowNum'] . '</td><td style="text-align: center;">' . $status . '</td><td style="text-align: center;"><a onclick="getUrl(' . $res['rid'] . ')" class="btn btn-success btn-xs">广告词</a></td></tr>';
        }
        if ($i < 1) {
            $data .= '<tr><td style="text-align: center;">无任何推广任务</td></tr>';
            $data .= '</tbody></table>';
        } else {
            $data .= '</tbody></table>';
        }

        $result = array('code' => 0, 'msg' => 'succ', 'data' => $data);
        exit(json_encode($result));
    }
}

if ($_GET['sid'] > 1) {
    $sid   = intval(guolv($_GET['sid']));
    $srow  = $DB->get_row("select * from pre_invitetools where sid=:sid limit 1", [':sid' => $sid]);
    $tool  = $DB->get_row("select * from pre_tools where tid=:tid limit 1", [':tid' => $srow['tid']]);
    $title = '推广领取' . $srow['name'] . '链接生成';
    $name  = '推广免费刷' . $srow['name'];
} else {
    $title    = '推广链接生成';
    $rs       = $DB->select("SELECT * FROM pre_invitetools WHERE status=1 order by sort asc");
    $shoplist = array();
    $shopname = array();
    if ($rs != false && is_array($rs)) {
        foreach ($rs as $key => $res) {
            $shopname[] = $res['name'];
            $shoplist[] = $res;
        }
    }
    // while ($res = $DB->fetch($rs)) {
    //     $tool=$DB->get_row("select * from pre_tools where tid=:tid limit 1", [':tid' => $res['tid']]);
    //     if ($tool) {
    //         $shopname[]=$res['name'];
    //         $shoplist[]=$res;
    //     }
    // }
    $name = '推广免费领商品';
    $tool = $DB->get_row("select * from pre_tools where tid='" . $srow['tid'] . "' limit 1");
}
$count = $DB->count("select count(*) from pre_invitetools where status=1");
?>
<!--
    # 秋云商城Plus-自定义商品推广网站插件
-->
<html>
    <head>
        <meta name="referrer" content="no-referrer">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
        <title>
            <?php echo $conf['sitename'] ?> - <?php echo $title ?>
        </title>
        <meta name="keywords" content="<?php echo $conf['keywords'] ?>">
        <meta name="description" content="<?php echo $conf['description'] ?>">
        <link href="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/plugins.css" type="text/css">
        <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/appui/css/main.css" type="text/css">
        <script src="<?php echo $cdnpublic ?>jquery/1.12.4/jquery.min.js" type="text/javascript"></script>
        <script src="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="<?php echo $cdnpublic ?>layer/3.4.0/layer.js?r=0830011" type="text/javascript"></script>
        <script src="<?php echo $cdnpublic ?>clipboard.js/1.7.1/clipboard.min.js" type="text/javascript"></script>
        <script src="<?php echo $cdnpublic ?>modernizr/2.8.3/modernizr.min.js" type="text/javascript"></script>
          <!--[if lt IE 9]>
          <script src="<?php echo $cdnpublic ?>html5shiv/3.7.3/html5shiv.min.js">
          </script><script src="<?php echo $cdnpublic ?>respond.js/1.4.2/respond.min.js"></script>
         <![endif]-->
         <style type="text/css">
            body {
                background: #ecedf0 url("<?php echo $background_image; ?>") fixed;
                background-repeat:no-repeat;
                background-size:100% 100%;
            }
        /* Slider */
        .slick-slider{position:relative;display:block;-moz-box-sizing:border-box;box-sizing:border-box;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;-webkit-touch-callout:none;-khtml-user-select:none;-ms-touch-action:pan-y;touch-action:pan-y;-webkit-tap-highlight-color:transparent}.slick-list{position:relative;display:block;overflow:hidden;margin:0;padding:0}.slick-list:focus{outline:0}.slick-list.dragging{cursor:pointer;cursor:hand}.slick-slider .slick-list,.slick-slider .slick-track{-webkit-transform:translate3d(0,0,0);-moz-transform:translate3d(0,0,0);-ms-transform:translate3d(0,0,0);-o-transform:translate3d(0,0,0);transform:translate3d(0,0,0)}.slick-track{position:relative;top:0;left:0;display:block}.slick-track:after,.slick-track:before{display:table;content:''}.slick-track:after{clear:both}.slick-loading .slick-track{visibility:hidden}.slick-slide{display:none;float:left;height:100%;min-height:1px}[dir=rtl] .slick-slide{float:right}.slick-slide img{display:block}.slick-slide.slick-loading img{display:none}.slick-slide.dragging img{pointer-events:none}.slick-initialized .slick-slide{display:block}.slick-loading .slick-slide{visibility:hidden}.slick-vertical .slick-slide{display:block;height:auto;border:1px solid transparent}
         @charset 'UTF-8';
         .slick-loading .slick-list{background:url(../image/ajax-loader.gif) center center no-repeat #fff}@font-face{font-family:slick;font-weight:400;font-style:normal;src:url(../font/slick.eot);src:url(../font/slick.eot?#iefix) format('embedded-opentype'),url(../font/slick1.woff) format('woff'),url(../font/slick2.ttf) format('truetype'),url(../font/slick3.svg#slick) format('svg')}.slick-next,.slick-prev{font-size:0;line-height:0;position:absolute;top:50%;display:block;width:20px;height:20px;margin-top:-10px;padding:0;cursor:pointer;color:transparent;border:none;outline:0;background:0 0}.slick-next:focus,.slick-next:hover,.slick-prev:focus,.slick-prev:hover{color:transparent;outline:0;background:0 0}.slick-next:focus:before,.slick-next:hover:before,.slick-prev:focus:before,.slick-prev:hover:before{opacity:1}.slick-next.slick-disabled:before,.slick-prev.slick-disabled:before{opacity:.25}.slick-next:before,.slick-prev:before{font-family:slick;font-size:20px;line-height:1;opacity:.75;color:#fff;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.slick-prev{left:-25px}[dir=rtl] .slick-prev{right:-25px;left:auto}.slick-prev:before{content:'←'}[dir=rtl] .slick-prev:before{content:'→'}.slick-next{right:-25px}[dir=rtl] .slick-next{right:auto;left:-25px}.slick-next:before{content:'→'}[dir=rtl] .slick-next:before{content:'←'}.slick-slider{margin-bottom:30px}.slick-dots{position:absolute;bottom:-45px;display:block;width:100%;padding:0;list-style:none;text-align:center}.slick-dots li{position:relative;display:inline-block;width:20px;height:20px;margin:0 5px;padding:0;cursor:pointer}.slick-dots li button{font-size:0;line-height:0;display:block;width:20px;height:20px;padding:5px;cursor:pointer;color:transparent;border:0;outline:0;background:0 0}.slick-dots li button:focus,.slick-dots li button:hover{outline:0}.slick-dots li button:focus:before,.slick-dots li button:hover:before{opacity:1}.slick-dots li button:before{font-family:slick;font-size:6px;line-height:20px;position:absolute;top:0;left:0;width:20px;height:20px;content:'•';text-align:center;opacity:.25;color:#000;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.slick-dots li.slick-active button:before{opacity:.75;color:#000}
        .list-group-item label{
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
        }
        </style>
    </head>
    <body>
        <br>
        <div class="col-xs-12 col-sm-12 col-md-10 col-lg-6 center-block" style="float: none;padding-top: 50px
        ；">

            <div class="block">
                <div class="block-title">
                    <div class="block-options pull-right">
                    <a href="/" class="btn btn-info" style="color: red;opacity: .8;"><b>返回网站下单</b></a>
                    </div>
                    <h4><i class="fa fa-share-alt"></i>&nbsp;&nbsp;<b><?php echo $name ?></b></h4>
                </div>
                <?php if ($conf['invite_open'] != 1) {?>

                       <div class="panel-body">推广免费领商品功能未开启！<br><a href='/'><<<返回到网站下单</a>
                        <hr/>
                        <a href="javascript:history.back(-1)"><<返回上一页</a></div>
                     </div>
                </div>
               <?php } elseif ($count < 1) {?>
                       <div class="panel-body">当前推广活动中无任何商品<br><a href='/'><<<返回到网站下单</a>
                        <hr/>
                        <a href="javascript:history.back(-1)"><<返回上一页</a></div>
                     </div>
                </div>
                <?php } else {
    ?>
                <ul class="nav nav-tabs nav-tabs-alt" data-toggle="tabs">
                    <li class="active" style="width:50%"><a href="#share" data-toggle="tab"><center><i class="fa fa-share-alt"></i> 推广奖励</center></a></li>
                    <li style="width:50%" class=""><a href="#jd" data-toggle="tab"><center><i class="fa fa-search"></i> 进度查询</center></a></li>
                </ul>
                <div id="myTabContent" class="tab-content">
                      <div class="tab-pane fade active in" id="share">
                        <div class="form-group">
                            <div class="alert alert-info alert-dismissable">
                                <span id="loginmsg">注：选择您需要的奖励，生成您的专属推广链接，一键复制广告语发送到QQ好友/QQ群/微信好友/朋友圈等地方宣传</span>
                                <br><br>
                                <span id="loginmsg">邀请好友访问后，您即可获得下方<font color="red">指定奖励</font>，领取其他的奖励请点击下方按钮！</span>
                                <br><br>
                                <span id="loginmsg">邀请好友访问人数越多，领取的福利越好越多！领取无上限！赶快生成您的专属『推广链接』把网站分享给更多人吧！</span>
                                <br><br>
                                <span id="loginmsg" style="color:red">生成推广链接后点击进度查询可以查看当前推广状态,以及推广所需人数！完成后自动发放奖励！</span>
                                <div id="shopInfo" class="hide"></div>
                            </div>
                            <div class="row text-center">
                            <?php
foreach ($shoplist as $row) {
        $shopimg = '';
        if (!empty($row['shopimg'])) {
            $shopimg = '<img src="' . $row['shopimg'] . '" width="18px">';
        }

        echo '<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4" onclick="setShop(' . $row['tid'] . ',' . $row['sid'] . ',\'' . $row['name'] . '\');">
                                            <ul class="list-group">
                                            <li class="list-group-item" style="padding: 3px 3px 3px 3px;"><label
                                                        class="css-input css-radio css-radio-primary"
                                                        style="font-size: 14px;"><input type="radio" ' . ($row['is_mr'] == 1 ? 'checked="checked" onload="setShop(' . $row['tid'] . ',' . $row['sid'] . ',\'' . $row['name'] . '\');"' : '') . ' name="tid" data-tid="' . $row['tid'] . '"  data-name="' . $row['name'] . '" id="tid" value="' . $row['sid'] . '"><span></span>
                                                    ' . $shopimg . $row['name'] . '</label>
                                            </li>
                                        </ul>
                                    </div>
                                    ';
    }
    ?>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-addon">
                                      查单QQ
                                    </div><input type="text" name="qq" id="qq" class="form-control" placeholder="输入联系QQ，方便下次查询" required="required" onkeydown="if(event.keyCode==13){submit_sub.click()}">
                                </div>
                            </div>
                            <div id="inputsname"></div>
                            <div id="alert_frame" class="alert alert-success animated rubberBand" style="background-color: #b4ffe0;color: #214cb1; display: none"></div>
                            <input type="submit" name="submit" id="submit_sub" value="请选择要领取的奖励" class="btn btn-primary btn-block">
                            <div id="resulturl" style="display:none;"></div>
                        </div>
                        <hr>
                        <div class="form-group">
                            <a href="javascript:history.back()" class="btn btn-primary btn-rounded" style="left">&nbsp;返回上页</a><a href="<?php echo $cdnserver ?>user/regsite.php" class="btn btn-danger btn-rounded" style="float:right;">&nbsp;开启网赚</a>
                        </div>
                    </div>
                    <div class="tab-pane fade in" id="jd">
                        <div class="form-group">
                            <div class="alert alert-info alert-dismissable">
                                <span id="loginmsg">提示：输入领取任务时填写的查询QQ，即可查询任务进度</span>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-addon">
                                      联系QQ
                                    </div><input type="text" name="qq2" id="qq2" class="form-control" placeholder="输入联系QQ，查询任务进度" required="required" onkeydown="if(event.keyCode==13){submit_sub.click()}">
                                </div>
                            </div>
                            <input type="submit" name="submit" id="submit_jd" value="立即查询" class="btn btn-primary btn-block">
                            <div id="jdresult" style="display:none;margin: 6px auto"></div>
                        </div>
                        <hr>
                        <div class="form-group">
                            <a href="javascript:history.back()" class="btn btn-primary btn-rounded" style="left">&nbsp;返回上页</a><a href="<?php echo $cdnserver ?>user/regsite.php" class="btn btn-danger btn-rounded" style="float:right;">&nbsp;开启网赚</a>
                        </div>
                    </div>

                </div>
            </div>

            <div class="list-group-item reed" style="background:#66ccff;">
            <center><h3 class="panel-title"><font color="#fff"><i class="fa fa-bar-chart-o"></i>&nbsp;&nbsp;<b>推广奖励统计</b></font></h3></center>
            </div>
            <div class="panel panel-info">
                <table class="table table-hover table-striped" style="text-align:center">
                <tbody>
                    <tr><td style="text-align: center;">领取QQ</td> <td style="text-align: center;">完成时间</td> <td style="text-align: center;">获得奖励</td></tr>
                </tbody>
                 </table>
                <marquee class="zmd" behavior="scroll" direction="UP" onmouseover="this.stop()" onmouseout="this.start()" scrollamount="5" style="height:12em">
                <table class="table table-hover table-striped" style="text-align:center">
                <tbody>
                    <?php
$count = $DB->count("SELECT count(*) FROM pre_inviteorders WHERE 1");
    if ($count < 10) {
        if (count($shopname) >= 3) {
            $arr = $shopname;
        } else {
            $arr = array('超级会员', '5元话费', '腾讯视频会员', 'CF会员', '影视会员月卡');
        }

        $num = count($arr);

        $times = time();
        for ($i = 0; $i < 40; $i++) {
            $times = $times - mt_rand(800, 10800);
            $time  = date('Y-m-d H:i:s', $times);
            $qq    = mt_rand(111111, 9999999999);
            $qq    = substr($qq, 0, 3) . '***' . substr($qq, -2, 2);
            $name  = $arr[mt_rand(0, $num - 1)];
            if ($name == "") {
                $name = "超级会员";
            }
            echo '<tr><td>恭喜QQ' . $qq . '</td><td>于' . $time . '推广成功</td><td>奖励 ' . $name . '</td></tr>';
        }
    } else {
        $rs = $DB->query("SELECT * FROM pre_inviteorders WHERE 1 order by rid desc limit 50");
        while ($res = $DB->fetch($rs)) {
            $row     = $DB->get_row("select * from pre_invitetools where sid='" . $res['sid'] . "' limit 1");
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
                </tbody>
                </table>
                </marquee>
            </div>
        </div>
<script src="<?php echo $cdnpublic ?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script type="text/javascript">
var isModal = false;
var modalType = 0;
var homepage = false;
var hashsalt = <?php echo $addsalt_js ?>;
</script>
<script src="<?php echo $cdnserver ?>assets/js/main.js?ver=<?php echo VERSION ?>"></script>
<script type="text/javascript">
var windowUrl = window.location.href;
var sname=null,tname=null;
var $_GET = (function(){
    var url = window.document.location.href.toString();
    var u = url.split("?");
    if(typeof(u[1]) == "string"){
        u = u[1].split("&");
        var get = {};
        for(var i in u){
            var j = u[i].split("=");
            get[j[0]] = j[1];
        }
        return get;
    } else {
        return {};
    }
})();

$(document).ready(function () {
    $(document).on('click', 'a[data-toggle="tab"]', function(event) {
        //event.preventDefault();
        setTimeout(function () {
            if (typeof window.history =='object') {
                var string = windowUrl.split('?', 2)[1];
                window.history.replaceState(null, null, './?' + string);
            }
            else{
                window.location.href = windowUrl;
            }
        }, 100);
    });
    $("input[name=tid]").each(function () {
        if (this.checked) {
            setShop($(this).attr('data-tid'),$(this).val(),$(this).attr('data-name'));
        }
        else{
            $(this).click(function () {
               setShop($(this).attr('data-tid'),$(this).val(),$(this).attr('data-name'));
            })
        }
    })
});

function setShop(tid,sid,name) {
    console.log(name);
    $("#shopInfo").attr('data-tid',tid);
    $("#shopInfo").attr('data-sid',sid);
    sname = name;
    $("#submit_sub").val("领取"+sname);
    getInfo(tid,sid);
}

var hashsalt = '<?php echo $addsalt ?>';
var clipboard = new Clipboard('#copyurl');
clipboard.on('success', function(e) {
    layer.msg('链接复制成功，快去邀请好友领取'+sname+'吧！');
});
clipboard.on('error', function(e) {
    layer.msg('<i class="fa fa-fw fa-frown-o text-muted"><\/i> 复制失败，请长按链接后手动复制');
});

var clipboard = new Clipboard('#copycontent');
clipboard.on('success', function(e) {
    layer.msg('广告词复制成功，快去邀请好友领取'+sname+'吧！');
});
clipboard.on('error', function(e) {
    layer.msg('<i class="fa fa-fw fa-frown-o text-muted"><\/i> 复制失败，请长按选中后手动复制');
});

var clipboard = new Clipboard('#copycontent2');
clipboard.on('success', function(e) {
    layer.msg('广告词复制成功，快去邀请好友领取'+tname+'吧！');
});
clipboard.on('error', function(e) {
    layer.msg('<i class="fa fa-fw fa-frown-o text-muted"><\/i> 复制失败，请长按选中后手动复制');
});

 function getInfo(tid,sid){
    $.ajax({
        type : "POST",
        url : "?mod=invite&act=tools",
        data : {tid:tid,sid:sid},
        dataType : 'json',
        success : function(data) {
            if(data.code==0){
                $('#inputsname').html("");
                var inputname = data.data.input;
                if(inputname=='hide'){
                    $('#inputsname').append('<input type="hidden" name="inputvalue" id="inputvalue" value="'+$.cookie('mysid')+'"/>');
                }else if(inputname!=''){
                    if(inputname.indexOf("&")!==(-1)){
                        var btnArr=inputname.split('&');
                        $('#inputsname').append('<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname">'+btnArr[0]+'</div><input type="text" name="inputvalue" id="inputvalue" placeholder="'+btnArr[1]+'" value="'+($_GET['qq']?$_GET['qq']:'')+'" class="form-control" required onblur="checkInput()"/></div></div>');
                    }
                    else{
                          $('#inputname').html(inputname);
                          $('#inputsname').append('<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname">'+inputname+'</div><input type="text" name="inputvalue" id="inputvalue" placeholder="" value="'+($_GET['qq']?$_GET['qq']:'')+'" class="form-control" required onblur="checkInput()"/></div></div>');
                    }

                }else{
                    $('#inputsname').append('<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname">下单ＱＱ</div><input type="text" name="inputvalue" id="inputvalue" value="'+($_GET['qq']?$_GET['qq']:'')+'" class="form-control" required onblur="checkInput()"/></div></div>');
                }

                if (inputname=="" && $('#inputname').length>0) {
                    $('#inputname').html("ＱＱ账号");
                    $('#inputvalue').attr("placeholder","输入ＱＱ账号");
                }
                else{
                    $('#inputvalue').attr("placeholder",'输入'+$('#inputname').html());
                }

                var desc = data.data.desc;
                if (data.data.desc && desc!=null && desc!="" && typeof(desc)!=undefined) {
                    $('#alert_frame').show();
                    $('#alert_frame').html(unescape(desc));
                }
                else{
                    $('#alert_frame').hide();
                }

                var inputsname = data.data.inputs;
                if(inputsname!=''){
                    $.each(inputsname.split('|'), function(i, value) {
                        if(value.indexOf('{')>0 && value.indexOf('}')>0){
                            var addstr = '';
                            var selectname = value.split('{')[0];
                            var selectstr = value.split('{')[1].split('}')[0];
                            $.each(selectstr.split(','), function(i, v) {
                                if(v.indexOf(':')>0){
                                    i = v.split(':')[0];
                                    v = v.split(':')[1];
                                }else{
                                    i = v;
                                }
                                addstr += '<option value="'+i+'">'+v+'</option>';
                            });
                            $('#inputsname').append('<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname'+(i+2)+'">'+selectname+'</div><select name="inputvalue'+(i+2)+'" id="inputvalue'+(i+2)+'" class="form-control">'+addstr+'</select></div></div>');
                        }
                        else if(value.indexOf('[')>0 && value.indexOf(']')>0){
                            var addstr = '';
                            var selectname = value.split('[')[0];
                            var selectstr = value.split('[')[1].split(']')[0];
                            $.each(selectstr.split(','), function(i, v) {
                                if(v.indexOf(':')>0){
                                    i = v.split(':')[0];
                                    v = v.split(':')[1];
                                }else{
                                    i = v;
                                }
                                addstr += '<option value="'+i+'">'+v+'</option>';
                            });
                            $('#inputsname').append('<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname'+(i+2)+'">'+selectname+'</div><select name="inputvalue'+(i+2)+'" id="inputvalue'+(i+2)+'" class="form-control">'+addstr+'</select></div></div>');
                        }
                        else{
                            if(value=='说说ID'||value=='说说ＩＤ')
                                var addstr='<div class="input-group-addon onclick" onclick="get_shuoshuo(\'inputvalue'+(i+2)+'\',$(\'#inputvalue\').val())">自动获取</div>';
                            else if(value=='日志ID'||value=='日志ＩＤ')
                                var addstr='<div class="input-group-addon onclick" onclick="get_rizhi(\'inputvalue'+(i+2)+'\',$(\'#inputvalue\').val())">自动获取</div>';
                            else if (value == '收货人地址'||value == '收货地址')
                                var addstr='<div class="input-group-addon onclick" onclick="inputAddress(\'inputvalue'+(i+2)+'\')">填写地址</div>';
                            else
                                var addstr='';
                                var ibtn='';
                                if(value.indexOf("&")!==(-1)){
                                    var btnArr=value.split('&');
                                    ibtn=btnArr[0];
                                }
                                else{
                                    ibtn=value;
                                }
                                $('#inputsname').append('<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname'+(i+2)+'">'+ibtn+'</div><input type="text" name="inputvalue'+(i+2)+'" id="inputvalue'+(i+2)+'" value="" class="form-control" required/>'+addstr+'</div></div>');
                        }
                        if(value.indexOf("&")!==(-1)){
                            var btnArr=value.split('&');
                            $('#inputvalue'+(i+2)).attr('placeholder',btnArr[1]);
                        }
                    });
                }
                if($("#inputname").html() == '歌曲ID'||$("#inputname").html() == '歌曲ＩＤ'){
                    $('#inputvalue').attr("placeholder", "在此输入歌曲的分享链接 可自动获取");
                }

                if($('#tid option:selected').attr('isfaka')==1){
                    $('#inputvalue').attr("placeholder", "用于接收卡密以及查询订单使用");
                    $('#display_left').show();
                    $.ajax({
                        type : "POST",
                        url : "/ajax.php?act=getleftcount",
                        data : {tid:$('#tid option:selected').val()},
                        dataType : 'json',
                        success : function(data) {
                            $('#leftcount').val(data.count)
                        }
                    });
                    if($.cookie('email'))$('#inputvalue').val($.cookie('email'));
                }else{
                    $('#display_left').hide();
                }

            }else{
                layer.alert(data.msg);
            }
        }
    });
 }

$('#submit_sub').click(function() {
    var qq  = $('#qq').val(),sid = $('#shopInfo').attr("data-sid"),tid = $('#shopInfo').attr("data-tid");
    if (qq == '') {
        layer.alert('请填写您的QQ号码！', {
            icon: 6
        });
        return false;
    }

    if ($("#inputvalue").length>0 && $("#inputvalue").val()=="") {
        return  layer.alert('请确保每项不能为空！');
    }

    if ($("#inputvalue2").length>0 && $("#inputvalue2").val()=="") {
        return  layer.alert('请确保每项不能为空！');
    }

    if ($("#inputvalue3").length>0 && $("#inputvalue3").val()=="") {
       return  layer.alert('请确保每项不能为空！');
    }

    if ($("#inputvalue4").length>0 && $("#inputvalue4").val()=="") {
        return  layer.alert('请确保每项不能为空！');
    }

    if ($("#inputvalue5").length>0 && $("#inputvalue5").val()=="") {
        return  layer.alert('请确保每项不能为空！');
    }

    var ii = layer.load(0, {
        shade: [0.1, '#fff']
    });

    $.ajax({
        type: 'POST',
        url: '?mod=invite&act=inviteurl',
        data: {qq: qq, hashsalt: hashsalt, sid: sid, tid: tid,input:$("#inputvalue").val(),input2:$("#inputvalue2").val(),input3:$("#inputvalue3").val(),input4:$("#inputvalue4").val(),input5:$("#inputvalue5").val()},
        dataType: 'json',
        async: true,
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                $('#resulturl').html('<br><div class="list-group-item list-group-item-success"><i class="fa fa-check-circle-o"><\/i>&nbsp;生成链接成功，复制以下内容邀请好友免费领取'+data.name+'吧！<br>小提示：QQ群，QQ好友，百度贴吧的学生吧、互赞吧等等都是推广好地方哦<\/div><div class="col-xs-12 well well-sm">' + data.text + '<\/div><center><button class="btn btn-success btn-sm" data-clipboard-text="' + data.text + '" id="copycontent">一键复制广告语<\/button>&nbsp;&nbsp;<button class="btn btn-warning btn-sm" data-clipboard-text="' + data.url + '" id="copyurl">一键复制链接<\/button><\/center>');
            } else if (data.code == 1) {
                $('#resulturl').html('<br><div class="list-group-item list-group-item-success"><i class="fa fa-check-circle-o"><\/i>&nbsp;生成链接成功，复制以下内容邀请好友免费领取'+data.name+'吧！<br>小提示：QQ群，QQ好友，百度贴吧的学生吧、互赞吧等等都是推广好地方哦<\/div><div class="col-xs-12 well well-sm">' +  data.text+ '<\/div><center><button class="btn btn-success btn-sm" data-clipboard-text="' + data.text + '" id="copycontent">一键复制广告语<\/button>&nbsp;&nbsp;<button class="btn btn-warning btn-sm" data-clipboard-text="' + data.url + '" id="copyurl">一键复制链接<\/button><\/center>');
            } else {
                $('#resulturl').html('<br><div class="list-group-item list-group-item-warning"><i class="fa fa-close"><\/i>&nbsp;生成链接失败<\/div><div class="col-xs-12 well well-sm">' + data.msg + '<\/div>');
            }
            $('#submit_sub').val('推广链接生成成功');
            $('#resulturl').slideDown();
        },
        error: function() {
            layer.close(ii);
            layer.alert('请重试一遍即可', {
                icon: 6
            });
        }
    })
});
$('#submit_jd').click(function() {
    var qq  = $('#qq2').val();
    if (qq == '') {
        layer.alert('请填写联系QQ！', {
            icon: 6
        });
        return false;
    }
     var ii = layer.load(0, {
        shade: [0.1, '#fff']
    });

    $.ajax({
        type: 'POST',
        url: '?mod=invite&act=jindu',
        data: {qq: qq, hashsalt: hashsalt},
        dataType: 'json',
        async: true,
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                $('#jdresult').html('<div class="list-group-item list-group-item-success"><i class="fa fa-check-circle-o"><\/i>&nbsp;查询任务进度成功！<\/div><div class="col-xs-12 well well-sm"><center>'
                    + data.data + '<\/center>');
            }
            else {
                $('#jdresult').html('<div class="list-group-item list-group-item-warning"><i class="fa fa-close"><\/i>&nbsp;查询进度失败<\/div><div class="col-xs-12 well well-sm">' + data.msg + '<\/div>');
            }
            $('#jdresult').slideDown();
        },
        error: function() {
            layer.close(ii);
            layer.alert('请重试一遍即可', {
                icon: 6
            });
        }
    });

});

function getUrl(rid){
    var ii = layer.load(0, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: '?mod=invite&act=getUrl',
        data: {rid: rid,hashsalt: hashsalt},
        dataType: 'json',
        async: true,
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                tname = data.name;
                layer.alert('<div class="col-xs-12 well well-sm"><center><span style="color:red">当前任务的广告词如下</span> <br><br>' + data.text + '</div><center><button class="btn btn-success btn-sm btn-block" data-clipboard-text="' + data.text + '" id="copycontent2">一键复制宣传词</button></center>',{
                     title:'广告词获取',
                     btn:['我知道了']
                });
            }
            else {
                layer.alert(data.msg);
            }
        },
        error: function() {
            layer.close(ii);
            layer.alert('请重试一遍即可', {
                icon: 6
            });
        }
    })

}

$("#qq").focus(function (){
    layer.tips('输入联系QQ，方便下次查询相关进度', "#qq",{tips: 1,times: 1500});
});
</script>
<?php
}?>
</body>
</html>