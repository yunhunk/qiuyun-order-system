<?php
/**
 * 余额提现
 **/
include "../includes/common.php";
$title = '余额提现';
$mod   = isset($_GET['mod']) ? $_GET['mod'] : null;
if ($conf['fenzhan_tixian_min'] == 0) {
    $conf['fenzhan_tixian_min'] = 5;
}
if ($conf['user_tixian_min'] == 0) {
    $conf['user_tixian_min'] = 5;
}
if ($mod == 'getRate') {
    if ($userrow['power'] > 0) {
        $tixian_rate = 100 - $conf['fenzhan_tixian_rate'];
    } else {
        $tixian_rate = 100 - ($conf['user_tixian_rate'] ? $conf['user_tixian_rate'] : $conf['fenzhan_tixian_rate']);
    }
    $daifu = 0;

    exit('{"code":0,"msg":"succ","tixian_rate":"' . $tixian_rate . '","daifu":"' . $daifu . '","pay_account":"' . $userrow['pay_account'] . '","pay_type":"' . $userrow['pay_type'] . '"}');
} elseif ($mod == 'skimg') {

    if ($conf['fenzhan_skimg'] != 1) {
        exit('{"code":-1,"msg":"系统未开启收款二维码提现！"}');
    }

    if (!isset($_FILES['skimg'])) {
        exit('{"code":-1,"msg":"上传的图片不能为空"}');
    }

    $extension = explode('.', $_FILES['skimg']['name']);
    if (($length = count($extension)) > 1) {
        $ext = strtolower($extension[$length - 1]);
    }

    $uploaded_size    = $_FILES['skimg']['size'];
    $uploaded_tmp     = $_FILES['skimg']['tmp_name'];
    $uploaded_type    = $_FILES['skimg']['type'];
    $uploaded_maxsize = $conf['fenzhan_imglimit'] >= 1024 ? $conf['fenzhan_imglimit'] * 1024 : 1024 * 1024;
    if ($uploaded_size > $uploaded_maxsize) {
        exit('{"code":-1,"msg":"图片文件过大，请重新选择上传较小的文件试试<br>不要用直接截图出来的图片，可以试试先发送给别人，然后点保存再发新的那个图片！","skimgUrl":""}');

    } elseif (in_array($ext, ['png', 'gif', 'jpg', 'webp', 'jpeg', 'bmp'])) {
        $imgpath = '';
        $scsk    = uploadFile_fenzhan('skimg', $imgpath, 'skimg');
        if ($scsk['code'] == 0) {
            $skimg = $scsk['imgSrc'];
            if ($DB->query("UPDATE cmy_site set skimg=:skimg where zid=:zid", [":skimg" => $skimg, ":zid" => $userrow['zid']])) {
                if (is_file(ROOT . $userrow['skimg'])) {
                    unlink(ROOT . $userrow['skimg']); //删除历史收款图
                }
                $userrow['skimg'] = $skimg;
                $result           = array("code" => 0, "msg" => "上传成功！", "skimgUrl" => $skimg);
            } else {
                $result = array("code" => -1, "msg" => "上传失败！" . $DB->error(), "skimgUrl" => "");
                $LOG->writeLog('上传Logo', '上传成功，保存失败' . $DB->error() . '；文件信息：' . json_encode($_FILES['skimg']));
            }
        } else {
            $LOG->writeLog('上传Logo', "上传失败：" . json_encode($scsk) . "\n文件信息：" . json_encode($_FILES['skimg']));
            $result = array("code" => -1, "msg" => $scsk['msg'] . '，请联系站长' . $conf['zzqq'] . '处理');
        }
        exit(json_encode($result));
    } else {
        exit('{"code":-1,"msg":"请上传正确的收款图片！","skimgUrl":""}');
    }
} elseif ($mod == 'tixian') {
    if ($conf['fenzhan_tixian'] != 1 && $userrow['power'] > 0) {
        exit('{"code":-1,"msg":"当前站点未开放提现功能！"}');
    }

    if ($conf['user_tixian'] != 1 && $userrow['power'] == 0) {
        exit('{"code":-1,"msg":"当前站点未开放提现功能！"}');
    }
    $money        = floatval(input('post.money', 1));
    $type         = intval(input('post.type'));
    $pay_account  = input('post.pay_account', 1);
    $account_type = intval(input('post.account_type'));
    if ($conf['fenzhan_tixian_type'] == 2 && $account_type == 0) {
        exit('{"code":-1,"msg":"当前系统未开启余额账户提现功能"}');
    } elseif ($conf['fenzhan_tixian_type'] == 0 && $account_type == 2) {
        exit('{"code":-1,"msg":"当前系统未开启提成账户提现功能"}');
    }

    $realmoney = null;
    if ($userrow['power'] > 0) {
        if ($account_type == 1 && $userrow['point'] < $conf['fenzhan_tixian_min']) {
            exit('{"code":-1,"msg":"您的提成账户不足' . $conf['fenzhan_tixian_min'] . '元！"}');
        } elseif ($account_type == 0 && getUserRmb() < $conf['fenzhan_tixian_min']) {
            exit('{"code":-1,"msg":"您的余额账户不足' . $conf['fenzhan_tixian_min'] . '元！"}');
        }
        $realmoney = round($money * $conf['fenzhan_tixian_rate'] / 100, 2);
    } else {
        if (getUserRmb() < $conf['user_tixian_min']) {
            exit('{"code":-1,"msg":"您的余额账户不足' . $conf['user_tixian_min'] . '元！"}');
        }
        $realmoney = round($money * $conf['user_tixian_rate'] / 100, 2);
    }

    if ($conf['fenzhan_skimg'] == 1 && empty($userrow['skimg'])) {
        exit('{"code":-1,"msg":"请先上传收款二维码再操作！","error":5002}');
    }

    $pay_account = $userrow['pay_account'];

    if (empty($userrow['pay_name'])) {
        exit('{"code":-1,"msg":"您还未设置收款真实姓名！"}');
    } elseif (!preg_match('/^[\d\.]+$/', $money) || $money < 0.01) {
        exit('{"code":-1,"msg":"提现金额格式不正确！"}');
    } elseif ($account_type == 1 && $userrow['point'] < $money) {
        exit('{"code":-1,"msg":"您的提成账户不足' . $money . '元！"}');
    } elseif ($account_type == 0 && getUserRmb() < $money) {
        exit('{"code":-1,"msg":"您的余额账户不足' . $money . '元！"}');
    } else {
        if ($userrow['power'] > 0) {
            if ($money < $conf['fenzhan_tixian_min']) {
                exit('{"code":-1,"msg":"单次提现金额不能小于' . $conf['fenzhan_tixian_min'] . '元！"}');
            }
        } else {
            if ($money < $conf['user_tixian_min']) {
                exit('{"code":-1,"msg":"单次提现金额不能小于' . $conf['user_tixian_min'] . '元！"}');
            }
        }

        if ($account_type == 1) {
            $sql = "UPDATE `pre_site` set `point`=`point`- ? where zid= ?";
        } else {
            $sql = "UPDATE `pre_site` set `money`=`money`- ? where zid= ?";
        }
        if (false !== $DB->exec($sql, [$money, $userrow['zid']])) {
            $data = [
                ':zid'         => $userrow['zid'],
                ':type'        => $type,
                ':subtype'     => $account_type,
                ':money'       => $money,
                ':realmoney'   => $realmoney,
                ':pay_type'    => $userrow['pay_type'],
                ':pay_account' => $pay_account,
                ':pay_name'    => $userrow['pay_name'],
                ':status'      => 0,
                ':addtime'     => $date,
            ];
            $tixian_id = $DB->insert("INSERT INTO `pre_tixian` (`zid`, `type`, `subtype`,  `money`, `realmoney`, `pay_type`, `pay_account`, `pay_name`, `status`, `addtime`) VALUES (:zid, :type, :subtype, :money, :realmoney, :pay_type, :pay_account, :pay_name, :status, :addtime)", $data);
            if ($tixian_id > 0) {
                if ($account_type == 1) {
                    $bz = '成功发起提现 ' . $money . '元，实际到帐' . $realmoney . '元! 当前提成账户剩余' . ($userrow['point'] - $money) . '元';
                } else {
                    $bz = '成功发起提现 ' . $money . '元，实际到帐' . $realmoney . '元! 当前余额账户剩余' . (getUserRmb() - $money) . '元';
                }

                addPointLogs($userrow['zid'], $money, '提现', $bz, null);
                if (empty($conf['fenzhan_tixian_succ'])) {
                    $msg = '<span style="color:blue">提现操作成功，扣除提现手续费后实际到账金额:' . $realmoney . '元！</span><br/><span style="color:red;font-size:17px">提现时间为1~48小时左右，请注意查收，长时间未到联系平台站长！</span>';
                } else {
                    $msg = $conf['fenzhan_tixian_succ'];
                }
                $LOG->writeLog('申请提现', '提现金额：' . $money . ';提现账户：' . $userrow['pay_account'] . ';提现结果：成功');
                $result = array("code" => 0, "msg" => $msg);
            } else {
                $result = array("code" => -1, "msg" => "提现失败，" . $DB->error());
            }
        } else {
            $result = array("code" => -1, "msg" => "提现失败，" . $DB->error());
        }
        exit(json_encode($result));
    }

}

$url      = 'https://api.fcypay.com/';
$m        = md5(rand(1000000, 9999999) . date('YmdHis') . uniqid());
$code_url = $url . 'get_openid_qrcode?mark=' . $m;
$cron_url = $url . 'get_openid_status?mark=' . $m;
include './head.php';
?>
    <div  class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top:10px;">

<?php

if ($conf['fenzhan_tixian'] == 0 && $userrow['power'] > 0) {
    showmsg('当前站点未开放提现功能！');
}

if ($conf['user_tixian'] == 0 && $userrow['power'] == 0) {
    showmsg('当前站点未开放提现功能！');
}

function display_zt($zt, $note = '')
{
    if ($zt == 1) {
        return '<font color=green>已完成</font>';
    } elseif ($zt == 2) {
        return '<span data-tip="驳回原因: ' . $note . '"><font color=orange>已驳回</font></span>';
    } elseif ($zt == 3) {
        return '<span style="color:#644d8c">处理中</span>';
    } else {
        return '<font color=blue>未完成</font>';
    }
}

function display_type($payway, $skimg)
{
    if ($payway == 0) {
        return '支付宝';
    } elseif ($payway == 1) {
        return '微信';
    } elseif ($payway == 2) {
        return 'QQ钱包';
    } else {
        return '请先设置收款方式';
    }

}

function display_type2($pay_type, $type)
{
    $typeval = $type == 2 ? '极速提现' : '正常提现';
    if ($pay_type == 1) {
        return $typeval . '（微信）';
    } elseif ($pay_type == 2) {
        return $typeval . '（QQ钱包）';
    } else {
        return $typeval . '（支付宝）';
    }

}

$numrows = $DB->count("SELECT count(*) from cmy_tixian WHERE zid='{$userrow['zid']}'");

?>
<div class="block">
     <div class="block-title clearfix"><h3>提现管理</h3></div>
        <ul class="nav nav-tabs" data-toggle="tabs">

        <li class="active" style="<?php echo $conf['fenzhan_skimg'] == 1 ? 'width:50%' : '' ?>"><a href="#tixian" data-toggle="tab" ><center>余额提现</center></a></li>
        <?php if ($conf['fenzhan_skimg'] == 1) {?>
        <li style="width:50%" class=""><a href="#skimg" data-toggle="tab"><center>收款图上传</center></a></li>
        <?php }?>
        </ul>
        <div id="myTabContent" class="tab-content">
                <div class="tab-pane fade active in" id="tixian">
                        <?php if ($conf['fenzhan_skimg'] == 1) {
    ?>
                        <div class="list-group-item list-group-item-info">
                            当前收款二维码：
                            <?php

    $skimg = '';
    if ($userrow['skimg']) {
        if (stripos($userrow['skimg'], '//') !== false) {
            $skimg = $userrow['skimg'];
        } elseif (file_exists(ROOT . $userrow['skimg'])) {
            $skimg = '../' . $userrow['skimg'];
        } else {
            $skimg = '';
        }
    } elseif (file_exists(ROOT . 'assets/img/skimg/sk_' . $userrow['zid'] . '.png')) {
        $skimg = '../assets/img/skimg/sk_' . $userrow['zid'] . '.png';
    }

    if ($skimg != "") {
        echo '<img id="tixian_img" width="100" src="' . $skimg . '">';
    } else {
        echo '未上传，请先在右侧栏目上传！<a href="https://shimo.im/docs/heoPWy54QVs0iU7C/">查看二维码获取教程</a>';
    }
    ?>
                            <br>
                            <span style="color: red"><i class="fa fa-exclamation-circle"></i> 如果二维码错误，将无法及时收到提现</span>
                            <a href="https://shimo.im/docs/heoPWy54QVs0iU7C/" target="_blank" class="hide btn btn-xs btn-danger">二维码获取教程</a>
                        </div>
                        <?php
}?>
                        <div class="list-group-item list-group-item-success">
                            <?php
if ($userrow['power'] > 0) {
    echo '最低提现金额：' . $conf['fenzhan_tixian_min'] . '元<br>';
    $tixian_rate = (100 - $conf['fenzhan_tixian_rate']);
} else {
    echo '最低提现金额：' . $conf['user_tixian_min'] . '元<br>';
    $tixian_rate = (100 - $conf['user_tixian_rate']);
}

if ($conf['fenzhan_tixian_alert']) {
    echo $conf['fenzhan_tixian_alert'];
} else {
    echo '提现到账时间：预计1-3个工作日内，等处理即可';
}
?>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="input-group"><div class="input-group-addon">提现类型</div>
                            <select id="account_type" class="form-control">
                                <?php if ($conf['fenzhan_tixian_type'] != 2): ?>
                                    <option value="0" selected>余额账户(剩<?php echo priceFormat(getUserRmb()) ?>元)</option>
                                <?php endif?>
                                <?php if ($userrow['power'] > 0 && $conf['fenzhan_tixian_type'] > 0): ?>
                                <option value="1" <?php if ($conf['fenzhan_tixian_type'] == 2) {echo 'selected';}?>>提成账户(剩<?php echo $userrow['point'] ?>元)</option>
                                <?php endif?>
                            </select>
                        </div></div>
                        <div class="form-group">
                            <div class="input-group"><div class="input-group-addon">收款平台</div>
                            <input type="text" class="form-control" value="<?php echo display_type($userrow['pay_type'], $userrow['skimg']); ?>" disabled>
                            <a href="uset.php?mod=user" class="input-group-addon btn btn-success btn-xs">修改平台</a>
                        </div></div>
                        <div class="form-group">
                            <div class="input-group"><div class="input-group-addon">提现方式</div>
                            <select class="form-control" id="type" name="type" onchange="getRate()" default="1">
                                <option value="1">正常提现（1~2天到账）</option>
                            </select>
                        </div></div>
                        <div class="form-group">
                            <div class="input-group"><div class="input-group-addon" id="pay_account_name">收款账号</div>
                            <input type="text" class="form-control" id="pay_account" value="<?php echo $userrow['pay_account'] ? $userrow['pay_account'] : '请先设置收款账号'; ?>" disabled>
                            <div id="getopenid_display" class="input-group-addon auto" style="padding:0;border:none;display: none">
                               <a onclick="getopenid()" class="btn btn-info btn-auto" style="border-radius: 0;" >点我自动获取</a>
                            </div>
                            <a href="uset.php?mod=user" id="edituser" class="input-group-addon btn-success btn-xs">修改账号</a>
                        </div></div>
                        <div class="form-group">
                            <div class="input-group"><div class="input-group-addon">提现金额</div>
                            <input type="text" name="money" id="money" class="form-control" placeholder="输入要提现金额">

                            </div>
                            <span style="color:red;margin:2px 0;">提示：请确保收款图，提现方式，提现账号全部一致，否则后果自负</span>
                        </div>
                        <div class="form-group">
                            <div class="input-group"><div class="input-group-addon">手续费率</div>
                            <input type="text" name="tixian_rate" id="tixian_rate" value="<?php echo $tixian_rate ?>" class="form-control" disabled>
                             <span class="input-group-addon">%</span>
                            </div>
                        </div>
                        <div class="form-group text-center">
                            <div style="width: 95%:margin:0 auto;">
                            <a href="javascript:(0)" onclick="tixian()" class="btn btn-block btn-success">确认提现</a>
                            </div>

                        </div>
                        <div class="panel panel-info">
                             <div class="panel-heading">提现记录</div>
                                  <div class="panel-body">

                              <div class="table-responsive">
                                <table class="table table-striped">
                                  <thead><tr><th>ID</th><th>金额</th><th>实际到账</th><th>提现方式</th><th>提现账号</th><th>姓名</th><th>申请时间</th><th>完成时间</th><th>状态</th></tr></thead>
                                  <tbody>
                        <?php

$rs = $DB->query("SELECT * FROM cmy_tixian WHERE zid= ? order by id desc limit 10", array($userrow['zid']));
while ($res = $DB->fetch($rs)) {
    echo '<tr><td><b>' . $res['id'] . '</b></td><td>' . $res['money'] . '</td><td>' . $res['realmoney'] . '</td><td>' . display_type2($res['pay_type'], $res['type']) . '</td><td>' . ($res['pay_type'] == 1 && $res['type'] == 2 ? $userrow['pay_account'] : $res['pay_account']) . '</td><td>' . $res['pay_name'] . '</td><td>' . $res['addtime'] . '</td><td>' . ($res['status'] == 1 ? $res['endtime'] : null) . '</td><td>' . display_zt($res['status'], $res['note']) . '</td></tr>';
}
?>
                                  </tbody>
                                </table>
                              </div>
                            </div>
                          </div>
                </div>
                <?php if ($conf['fenzhan_skimg'] == 1) {
    ?>
                <div class="tab-pane fade in" id="skimg">
                        <div class="panel panel-info"><div class="panel-body">

                        <form enctype="multipart/form-data">
                            <input type="file" name="skimg_" id="skimg_"/><br>
                            <a href="javascript:(0)" onclick="upSkimg()" class="btn btn-block btn-success">立即上传</a>
                        </form>
                        <br>现在的收款图：<br><img id="new_img" src="<?php echo $skimg; ?>" style="width:175px">';

                        </div></div>
                </div>
                <?php
}?>

        </div>

        </div>


 </div>
<div id="qrcode" style="display: none;width: 96%;margin: 0 auto;"></div>
<script>
var qrcode, cron, open;

function getRate() {
    var ii = layer.load(1, {
        shade: [0.1, '#fff']
    });
    var type = $("#type").val();
    $.ajax({
        type: 'POST',
        url: "?mod=getRate",
        data: 'type=' + type,
        cache: false,
        dataType: 'json',
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                $('#tixian_rate').val(data.tixian_rate);
                if (data.daifu == 1 && data.pay_type == 1) {
                    $("#pay_account").val('');
                    $("#pay_account_name").html("微信openid");
                    $("#getopenid_display").show();
                    $("#edituser").hide();
                    qrcode = new QRCode("qrcode", {
                        text: '<?php echo $code_url; ?>',
                        width: 300,
                        height: 300,
                    });
                    $("#pay_account").val('→请点击右侧自动获取并扫码');
                    layer.msg('点击微信openid右边的按钮扫码获取');
                } else {
                    $("#pay_account").val(data.pay_account);
                    $("#pay_account_name").html("收款账号");
                    $("#getopenid_display").hide();
                    $("#edituser").show();
                    window.clearInterval(cron);
                }
            } else {
                layer.alert(data.msg);
            }
        },
        error: function(data) {
            layer.close(ii);
            layer.alert('服务器请求超时，请刷新后再试！');
            return false;
        }
    });
}

function getopenid() {
    cron = setInterval(function() {
        $.ajax({
            type: "GET",
            url: '<?php echo $cron_url; ?>&r=' + Math.random(),
            dataType: "json",
            success: function(data) {
                if (data.code) {
                    $("#pay_account").val(data.data);
                    layer.close(open);
                    window.clearInterval(cron);
                }
            }
        });
    }, 3000);
    open = layer.open({
        type: 1,
        title: '',
        content: '<div class="layui-card-body"><h4 style="text-align:center">提现至哪个微信就用哪个微信扫码</h4><center><span style="color:red">然后回到本页面等待自动获取</span></center><div><br>' + $("#qrcode").html() + '</div></div>',
        cancel: function(index, layero) {
            layer.close(open);
            window.clearInterval(cron);
        }
    });
}

function upSkimg() {
    var files = $('#skimg_').prop('files');
    var data = new FormData();
    data.append('skimg', files[0]);
    var ii = layer.load(1, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: "?mod=skimg",
        data: data,
        cache: false,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg(data.msg);
                $('#new_img').attr('src', data.skimgUrl);
                $('#tixian_img').attr('src', data.skimgUrl);
            } else {
                layer.alert(data.msg);
            }
        },
        error: function(data) {
            layer.close(ii);
            layer.alert('服务器错误');
            return false;
        }
    });
};

function tixian() {
    var money = $('#money').val();
    var type = $('#type').val();
    var pay_account = $('#pay_account').val();
    var account_type = $('#account_type option:selected').val();
    if (type == 2 && pay_account == "→请点击右侧自动获取并扫码") {
        return layer.alert('请确保微信openid不能为空！');
    } else if (type == 2 && pay_account == "") {
        return layer.alert('请确保收款账号不能为空！');
    }
    var ii = layer.load(1, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: "?mod=tixian",
        data: {
            money: money,
            type: type,
            pay_account: pay_account,
            account_type: account_type
        },
        timeout: 2500,
        dataType: 'json',
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.open({
                    title: '提现成功',
                    content: data.msg,
                    yes: function() {
                        window.location.reload();
                    }
                });
            } else {
                layer.alert(data.msg);
            }
        },
        error: function(data) {
            layer.close(ii);
            layer.alert('服务器错误');
            return false;
        }
    });
};
$(document).ready(function($) {
    $("#type").change();
});
</script>
<?php include 'footer.php'?>