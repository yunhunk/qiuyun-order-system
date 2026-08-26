<?php
/**
 * 我的工单
 **/

use core\Db;

include "../includes/common.php";
$title = '我的工单';
include './head.php';
if ($isLogin2 == 1) {} else {
    $goto = @getHostUrl();

    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

if ($conf['workorder_open'] != 1) {
    showmsg("系统未开启工单功能！", 3);
}
?>
<link rel="stylesheet" href="<?php echo $cdnserver ?>assets/cmui/css/image-group.css?<?php echo $jsver ?>">
<style>
.gdan_gout{width:100%;height:auto;background-color:#fff;padding-bottom:1em}
.gdan_txt{height:3em;line-height:3em;text-indent:1em;font-family:"微软雅黑";font-weight:800;}
.gdan_txt>span{position:absolute;right:3em;}
.gdan_zhugan{width:96%;height:auto;padding-top:1em;margin-left:2%;padding-left:.5em;padding-right:1em;margin-bottom:1em;border-top:dashed 1px #a9a9a9}
.gdan_kjia1{width:auto;margin-left:4em;margin-top:-3em}
.gdan_xiaozhi{width:100%;height:1em;color:#a9a9a9;margin-bottom:1em}
.gdan_xiaozhi>span{position:absolute;right:3em;}
.gdan_huifu{width:100%;height:auto;margin-top:1em;border-top:solid #ccc 1px}
.gdan_srk{width:98%;height:8em;margin-left:1%;margin-top:1em;border-color:#6495ed}
.gdan_huifu1{width:6em;height:2.5em;border:none;background-color:#1e90ff;color:#fff;margin:.5em 0 .5em 1%}
.gdan_jied{width:100%;height:3em;line-height:3em;text-align:center;color:#129DDE}
<?php if (checkmobile()): ?>
.imageBox {
    width: 100%;
    margin-bottom: 15px;
    overflow-y: hidden;
    -ms-overflow-style: -ms-autohiding-scrollbar;
    border: 1px solid #ddd;
}
.imageBox .imageList .item .btn {
    opacity: 0.9;
    filter: alpha(opacity=90);
}
.imageBox .imageList .item:hover .btn {
    opacity: 1;
    filter: alpha(opacity=100);
}
<?php endif?>
</style>


  <div class="wrapper">
    <div class="col-md-12 center-block" style="float: none;">
<?php

function display_type($type)
{
    if ($type == 1) {
        return '业务补单';
    } elseif ($type == 2) {
        return '卡密错误';
    } elseif ($type == 3) {
        return '充值没到账';
    } elseif ($type == 4) {
        return '中途改了密码';
    } else {
        return '其它问题';
    }

}

function display_status($zt, $id = 0)
{
    if ($zt == 1) {
        return '<font color=green>已完成</font>';
    } elseif ($zt == 2) {
        return '<font color=red>待补充</font>';
    } else {
        return '<font color=blue>待处理</font>';
    }

}

$count1 = $DB->count("SELECT count(*) FROM cmy_workorder WHERE zid= ? AND status=1", array($userrow['zid']));
$count2 = $DB->count("SELECT count(*) FROM cmy_workorder WHERE zid= ? AND status=0", array($userrow['zid']));
$count3 = $DB->count("SELECT count(*) FROM cmy_workorder WHERE zid= ?", array($userrow['zid']));

$my = isset($_GET['my']) ? $_GET['my'] : null;

if ($my == 'add') {
    ?>
<div class="panel panel-default">
<div class="panel-heading"><div class="pull-right"><a href="./workorder.php"><i class="fa fa-times"></i></a></div><h3 class="panel-title"><i class="fa fa-plus"></i>&nbsp;&nbsp;<b>提交工单</b></h3></div>
<div class="panel-body">
<form action="./workorder.php?my=add_submit" method="POST">
<div class="form-group">
<div class="input-group"><div class="input-group-addon">订单编号</div>
<?php

    $orderid = isset($_GET['orderid']) ? input('get.orderid', 1) : null;
    $res     = $orderid ? $DB->get_row("SELECT id,tid,input from `pre_orders` where id= ? limit 1", array($orderid)) : null;
    $skey    = $orderid && isset($_GET['skey']) ? input('get.skey', 1) : null;
    if (isset($_GET['orderid']) && $_GET['orderid'] && $res && getOrderSkey($res, 'check') === $skey) {
        $orderid  = intval($_GET['orderid']);
        $toolname = $DB->get_column("SELECT `name` from cmy_tools where `tid`= ? limit 1", array($res['tid']));
        echo '<input type="text" name="orderid" value="' . $orderid . '_' . $toolname . '_' . $res['input'] . '" class="form-control" disabled/><input type="hidden" name="orderid" value="' . $orderid . '"/>';
    } else {
        echo '<select name="orderid" class="form-control"><option value="0">选择异常的订单（非订单问题不用选）</option>';
        $rs = $DB->query("SELECT id,tid,input FROM cmy_orders WHERE zid= ? or userid= ? order by id desc", array($userrow['zid'], $userrow['zid']));
        while ($res = $DB->fetch($rs)) {
            $toolname = $DB->get_column("SELECT name from cmy_tools where tid= ? limit 1", array($res['tid']));
            echo '<option value="' . $res['id'] . '">' . $res['id'] . '_' . $toolname . '_' . $res['input'] . '</option>';
        }
        echo '</select>';
    }
    ?>
</div>
</div>
<div class="form-group">
<div class="input-group"><div class="input-group-addon">问题类型</div>
    <select name="type" class="form-control">
        <option value="1">业务补单</option>
        <option value="2">卡密错误</option>
        <option value="3">充值没到账</option>
        <option value="4">订单中途改了密码</option>
        <option value="0">其它问题</option>
    </select>
</div>
</div>
<div class="form-group">
<textarea class="form-control" name="name" rows="5" placeholder="填写描述信息" required></textarea>
</div>
<div class="form-group imageBox">
    <label>图片附件（单次最多3张）</label><br/>
    <div class="content">
        <input type="file" id="file" class="hide">
        <input type="text" id="index" value="0" class="hide">
        <input type="text" id="piclist" name="piclist" class="hide">
        <div class="imageBtn">
            <!-- <span class="count">已添加<span id="count">0</span>张</span> -->
            <div class="box" id="add">
                <a href="javascript:;">
                    添加图片附件
                </a>
            </div>
        </div>
        <div class="imageList">

        </div>
    </div>
</div>
<div id="image_template" style="display: none">
    <div class="item item{index}">
        <img data-path="{path}" src="{img}">
        <span class="btn remove">
            <a data-fileid="{fileid}" data-index="{index}" href="javascript:;">删除</a>
        </span>
    </div>
</div>
<input type="submit" class="btn btn-primary btn-block" value="提交"></form>
<br/><a href="./workorder.php">>>返回工单列表</a>
</div>
<div class="panel-footer">
<span class="glyphicon glyphicon-info-sign"></span>
找不到要提交的订单？<a href="../?chadan=1">点击进入查询订单</a>，在订单详情页面点击【投诉订单】可以直接提交工单。
</div>
</div>
<script type="text/javascript">
$(document).on('click', '.remove', function(event) {
    event.preventDefault();
    /* Act on the event */
    var index = $(this).find('a').data('index');
    $(".imageList .item"+index).remove();
    setImageList();
    layer.msg('删除成功');
});
$(document).on('click', '#add', function(event) {
    event.preventDefault();
    /* Act on the event */
    $("#file")[0].click();
});
$(document).on('change', '#file', function(event) {
    event.preventDefault();
    /* Act on the event */
    upload();
});
$(document).on('click', '.imageList img', function(event) {
    event.preventDefault();
    /* Act on the event */
    layer.photos({
        photos: '.imageList',
        anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
    });
});
function upload() {
    var fileObj = $("#file")[0].files[0];
    if (typeof(fileObj) == "undefined" || fileObj.size <= 0) {
        return;
    }
    var els = $(".imageList .item");
    if (els.length>=3) {
        layer.alert('单次最多添加3张图片附件！');
        return;
    }
    var formData = new FormData();
    formData.append("type", "workorder");
    formData.append("file", fileObj);
    formData.append("key", "file");
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        url: "../ajax.php?act=upload",
        data: formData,
        type: "POST",
        dataType: "json",
        cache: false,
        processData: false,
        contentType: false,
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg('上传成功');
                var index = $("#index").val();
                index++;
                $("#index").val(index);
                var html = $("#image_template").html();
                html = html.replace(new RegExp('{index}','g'), index);
                var url   = data.imgSrc;
                if (url.indexOf('http') < 0) {
                   url = '../' + url;
                }
                html = html.replace(new RegExp('{img}','g'), url);
                html = html.replace(new RegExp('{path}','g'), data.path);
                html = html.replace(new RegExp('{fileid}','g'), data.fileid);
                $(".imageList").html(html + $(".imageList").html());
                setImageList();
            } else {
                layer.alert(data.msg);
            }
        },
        error: function(data) {
            layer.msg('服务器错误');
            return false;
        }
    })
}
function setImageList() {
    var els = $(".imageList .item img");
    var list = [];
    for(var i = 0; i < els.length; i++){
        var url = $(els[i]).data('path');
        list.push(url)
    }
    console.log('list', list);
    $("#piclist").val(list.join(','));
}
</script>
<?php
} elseif ($my == 'view') {
    $id   = intval($_GET['id']);
    $rows = $DB->get_row("SELECT * from `pre_workorder` where id= ? and zid= ? limit 1", [$id, $userrow['zid']]);
    if (!$rows) {
        showmsg('当前记录不存在！', 3);
    }

    $contents = explode('*', $rows['content']);
    $myimg    = '//q2.qlogo.cn/headimg_dl?bs=qq&dst_uin=' . $userrow['qq'] . '&src_uin=' . $userrow['qq'] . '&fid=' . $userrow['qq'] . '&spec=100&url_enc=0&referer=bu_interface&term_type=PC';
    $kfimg    = 'https://imgcache.qq.com/open_proj/proj_qcloud_v2/mc_2014/work-order/css/img/custom-service-avatar.svg';
    ?>
    <div class="panel panel-default">
    <div class="panel-heading"><div class="pull-right"><a href="./workorder.php"><i class="fa fa-times"></i></a></div><h3 class="panel-title"><i class="fa fa-sticky-note-o"></i>&nbsp;&nbsp;<b>工单详情</b></h3></div>

    <div class="gdan_gout">
        <div class="gdan_txt">沟通记录 - <?php echo count($contents) ?><span>状态：<?php echo display_status($rows['status']) ?></span></div>
        <!------------------开始沟通------------------------>
        <div class="gdan_zhugan" style="border: none;">
            <img src="<?php echo $myimg ?>" class="img-circle" width="40"/>
            <div class="gdan_kjia1">
                <div class="gdan_xiaozhi">问题描述<span><?php echo $rows['addtime'] ?></span></div>
                <p><?php echo $rows['name'] ?></p><br/>
                <p>订单编号：<?php echo $rows['orderid'] ? $rows['orderid'] : '无订单号'; ?></p>
                <p>问题类型：<?php echo display_type($rows['type']) ?></p>
                <?php
if ($rows['piclist'] && ($conf['workorder_image_api'] != 2 || $conf['workorder_image_read'] == 1)):
        $piclist = [
            "title" => "工单图片附件", //相册标题
            "id"    => $rows['id'], //相册id
            "start" => 0, //初始显示的图片序号，默认0
            "data"  => [],
        ];
        $arr = explode(',', $rows['piclist']);
        foreach ($arr as $key => $img) {
            $piclist['data'][$key] = [
                "alt" => "图片" . $key,
                "pid" => $key + 1, //图片id
                "src" => $img, //原图地址
            ];
        }

        echo '<p>图片附件：<a href="javascript:;" id="imgView">点击查看</a> </p>';
    endif;?>
            </div>
        </div>
        <script type="text/javascript">
        $(document).on('click', '#imgView', function(event) {
            event.preventDefault();
            /* Act on the event */
            var imgJson = <?php echo isset($piclist['data']) ? json_encode($piclist) : '{}' ?>;
            layer.photos({
                photos: imgJson,
                anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
            });
        });
        </script>
    <?php
for ($i = 1; $i < count($contents); $i++) {
        $content = explode('^', $contents[$i]);
        if (count($content) == 3) {
            echo '<div class="gdan_zhugan">
        <img src="' . ($content[0] == 1 ? $kfimg : $myimg) . '" class="img-circle" width="40"/>
        <div class="gdan_kjia1">
        <div class="gdan_xiaozhi">' . ($content[0] == 1 ? '官方客服' : $userrow['user']) . '<span>' . $content[1] . '</span></div>
        ' . $content[2] . '
        </div>
    </div>';
        }
    }
    if ($rows['status'] == 0) {
        ?>
        <div class="gdan_jied">请耐心等待客服处理</div>
        <?php
} elseif ($rows['status'] == 1) {
        ?>
        <div class="gdan_jied">此工单已经结单</div>
        <?php
} elseif ($rows['status'] == 2) {
        ?>
        <div class="gdan_huifu">
        <form action="./workorder.php?my=reply&id=<?php echo $id ?>" method="POST">
            <textarea class="gdan_srk" name="content" placeholder="可输入需要补充的内容，回复后官方客服将会收到你的消息！" required></textarea>
            <?php if ($conf['workorder_image'] == 1): ?>
            <div class="form-group imageBox" style="    width: 98%;
    margin: auto;">
                <label>图片附件（单次最多3张）</label><br/>
                <div class="content">
                    <input type="file" id="file" class="hide">
                    <input type="text" id="index" value="0" class="hide">
                    <input type="text" id="piclist" name="piclist" class="hide">
                    <div class="imageBtn">
                        <!-- <span class="count">已添加<span id="count">0</span>张</span> -->
                        <div class="box" id="add">
                            <a href="javascript:;">
                                添加图片附件
                            </a>
                        </div>
                    </div>
                    <div class="imageList">

                    </div>
                </div>
            </div>
            <div id="image_template" style="display: none">
                <div class="item item{index}">
                    <img data-path="{path}" src="{img}">
                    <span class="btn remove">
                        <a data-fileid="{fileid}" data-index="{index}" href="javascript:;">删除</a>
                    </span>
                </div>
            </div>
            <?php endif?>
            <input type="submit" name="submit" value="提交回复" class="gdan_huifu1" />
            <input type="button" name="submit" value="完结工单" class="gdan_huifu1" style="background-color: mediumseagreen;" onclick="window.location.href='./workorder.php?my=complete&id=<?php echo $id ?>'"/>
        </form>
        </div>
        <?php
}
    ?>
    </div>
    <div class="gdan_txt"><a href="./workorder.php">>>返回工单列表</a></div>
    </div>
    <?php if ($conf['workorder_image'] == 1): ?>
    <script type="text/javascript">
    $(document).on('click', '.remove', function(event) {
        event.preventDefault();
        /* Act on the event */
        var index = $(this).find('a').data('index');
        $(".imageList .item"+index).remove();
        setImageList();
        layer.msg('删除成功');
    });
    $(document).on('click', '#add', function(event) {
        event.preventDefault();
        /* Act on the event */
        $("#file")[0].click();
    });
    $(document).on('change', '#file', function(event) {
        event.preventDefault();
        /* Act on the event */
        upload();
    });
    $(document).on('click', '.imageList img', function(event) {
        event.preventDefault();
        /* Act on the event */
        layer.photos({
            photos: '.imageList',
            anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
        });
    });
    function upload() {
        var fileObj = $("#file")[0].files[0];
        if (typeof(fileObj) == "undefined" || fileObj.size <= 0) {
            return;
        }
        var els = $(".imageList .item");
        if (els.length>=3) {
            layer.alert('单次最多添加3张图片附件！');
            return;
        }
        var orderid = $("SELECT[name='orderid'] option:selected").val();
        var formData = new FormData();
        formData.append("type", "workorder");
        formData.append("file", fileObj);
        formData.append("key", "file");
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            url: "../ajax.php?act=upload",
            data: formData,
            type: "POST",
            dataType: "json",
            cache: false,
            processData: false,
            contentType: false,
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg('上传成功');
                    var index = $("#index").val();
                    index++;
                    $("#index").val(index);
                    var html = $("#image_template").html();
                    html = html.replace(new RegExp('{index}','g'), index);
                    var url   = data.imgSrc;
                    if (url.indexOf('http') < 0) {
                       url = '../' + url;
                    }
                    html = html.replace(new RegExp('{img}','g'), url);
                    html = html.replace(new RegExp('{path}','g'), data.path);
                    html = html.replace(new RegExp('{fileid}','g'), data.fileid);
                    $(".imageList").html(html + $(".imageList").html());
                    setImageList();
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function(data) {
                layer.msg('服务器错误');
                return false;
            }
        })
    }
    function setImageList() {
        var els = $(".imageList .item img");
        var list = [];
        for(var i = 0; i < els.length; i++){
            var url = $(els[i]).data('path');
            list.push(url)
        }
        console.log('list', list);
        $("#piclist").val(list.join(','));
    }
    </script>
    <?php endif?>
<?php
} elseif ($my == 'add_submit') {
    $orderid = intval(input('post.orderid', 1));
    $row     = $DB->get_row("SELECT * FROM `pre_orders` where `id`='{$orderid}'");
    if (!$row) {
        showmsg('该订单' . $orderid . '不存在！', 4);
        exit;
    }

    $type    = intval(input('post.type', 1));
    $piclist = input('post.piclist', 1);
    $name    = input('post.name', 1);
    if (empty($name)) {
        showmsg('描述信息不能为空！');
        exit;
    } elseif ($DB->get_row("SELECT id from `pre_workorder` where zid = ? AND orderid= ? and `status`!=1 limit 1", [$userrow['zid'], $orderid])) {
        showmsg('当前还有未完成的相关工单，请勿重复提交！');
        exit;
    } else {

        $res = $DB->get_row("SELECT * from `pre_orders` where `id`= ? limit 1", [$orderid]);
        if (!$res) {
            showmsg('该工单相关订单记录不存在', 3);
            exit;
        }

        $tool = $DB->get_row("SELECT * from `pre_tools` where `tid`= ? limit 1", [$res['tid']]);
        $sid  = 1;
        if ($tool) {
            $sid = $tool['zid'];
        }

        $sql  = "INSERT into `pre_workorder` (`zid`,`sid`,`type`,`orderid`,`name`,`piclist`,`addtime`,`status`) values (?, ?, ?, ?, ?, ?, ?, ?)";
        $data = array($userrow['zid'], $sid, $type, $orderid, $name, $piclist, $date, '0');
        if ($workid = $DB->insert($sql, $data)) {

            $ts = '无需发送给供货商';
            if ($sid > 1) {
                $user = $DB->get_row("SELECT * from cmy_master where `zid`='{$sid}' limit 1");
                if ($user) {
                    // 扣除提成
                    // if ($sid > 1 && conf('master_tousu_rmb_remove') == 1) {
                    //     Db::name('master')->where(['zd' => $sid])->update([
                    //         'income' => round($user['income'] - $res['price1'], 2),
                    //     ]);
                    //     addMasterPointLogs($sid, $res['price1'], '扣除', '该订单收到投诉提成扣除, 处理好投诉后可联系客服重新发放提成', $orderid);
                    // }

                    // 发送通知
                    if (conf('master_notify_workorder_email') == 1 && validateData($user['email'], 'email')) {
                        try {
                            $ems = new \core\Ems();
                            if ($ems) {
                                $send = $ems->sendEmail($user['email'], '您有新的工单新增, 请及时处理', '<b>商品ID: </b>' . $res['tid'] . '<br/><b>工单ID:</b> ' . $workid . '<br/><b>问题类型：</b>' . display_type($type) . '<br/><b>订单金额:</b> ' . $res['money'] . '元<br/><b>订单份数: </b>' . $res['value'] . '<br/><b>订单时间: </b>' . $res['addtime'] . '<br/>');

                                if ($send === true) {
                                    $ts = '发送通知给供货商' . $user['user'] . '[' . $sid . ']成功';
                                } else {
                                    $ts = '发送通知给供货商' . $user['user'] . '[' . $sid . ']失败, ' . $send;
                                }
                            }
                        } catch (\Throwable $th) {
                            $ts = '发送通知给供货商' . $user['user'] . '[' . $sid . ']失败, 发送执行错误=> ' . $th->getMessage();
                        }
                    }
                } else {
                    if (!$user) {
                        $ts = '发送通知给供货商失败, 供货商不存在 ';
                    } else {
                        $ts = '发送通知给供货商失败, 供货商邮箱绑定不正确： ' . $user['email'];
                    }
                }
                Db::name('workorder')->where(['id' => $workid])->update(['ts' => $ts]);
            } else {
                if ($conf['workorder_mail'] == 1 && $conf['mail_recv']) {
                    $content   = mb_substr($name, 0, 16, 'utf-8');
                    $sub       = '用户提交工单提醒';
                    $msg       = '<b>' . $userrow['user'] . '</b>（UID:' . $userrow['zid'] . '）于 ' . $date . ' 提交工单，请及时进入网站后台工单列表处理。<br/><b>问题类型：</b>' . display_type($type) . '<br/><b>工单标题：</b>' . $content . '<br/>----------<br/>' . $_SERVER['HTTP_HOST'] . '<br/>' . $date;
                    $mail_name = $conf['mail_recv'] ? $conf['mail_recv'] : $conf['mail_name'];
                    send_mail($mail_name, $sub, $msg);
                } else {
                    if ($sid <= 1) {
                        if (conf('work_notice_email') == 1 && validateData($conf['adm_email'], 'email')) {
                            $ems = new \core\Ems();
                            if ($ems) {
                                $send = $ems->sendEmail($conf['adm_email'], '工单提醒！有来自' . $userrow['zid'] . '的工单回复, 请及时处理', '<b>用户UID: </b>' . $userrow['zid'] . '<br/><b>订单ID: </b>' . $orderid . '<br/><b>商品ID: </b>' . $res['tid'] . '<br/><b>工单ID:</b> ' . $workid . '<br/><b>问题类型：</b>' . display_type($type) . '<br/><b>订单金额:</b> ' . $res['money'] . '元<br/><b>订单份数: </b>' . $res['value'] . '<br/><b>订单时间: </b>' . $res['addtime'] . '<br/>');

                                if ($send === true) {
                                    $ts = '发送通知给供货商' . $user['user'] . '[' . $sid . ']成功';
                                } else {
                                    $ts = '发送通知给供货商' . $user['user'] . '[' . $sid . ']失败, ' . $send;
                                }
                            }
                        }
                    }
                }
            }

            showmsg('提交工单成功！请等待管理员处理。<br/><br/><a href="./workorder.php">>>返回工单列表</a>', 1);
        } else {
            showmsg('提交工单失败！' . $DB->error(), 4);
        }
    }
} elseif ($my == 'reply') {
    $id   = (int) $_GET['id'];
    $rows = $DB->get_row("SELECT * from cmy_workorder where id= ? and zid= ? limit 1", array($id, $userrow['zid']));
    if (!$rows) {
        showmsg('当前记录不存在！', 3);
    } elseif ($rows['status'] == 1) {
        showmsg('此工单已经结单', 3);
    } elseif ($rows['status'] == 0) {
        showmsg('请耐心等待客服处理', 3);
    }
    $piclist = input('post.piclist', 1);
    $content = str_replace(array('*', '^', '|'), '', input('post.content', 1));
    if (empty($content)) {
        showmsg('补充信息不能为空！');
    } else {
        $res = $DB->get_row("SELECT * from `pre_orders` where `id`= ? limit 1", [$rows['orderid']]);
        if (!$res) {
            showmsg('该工单相关订单记录不存在', 3);
            exit;
        }

        $tool = $DB->get_row("SELECT * from `pre_tools` where `tid`= ? limit 1", [$res['tid']]);
        $sid  = 1;
        if ($tool) {
            $sid = $tool['zid'];
        }

        if ($piclist && $rows['piclist']) {
            $piclist = $rows['piclist'] . ',' . $piclist;
        } else {
            $piclist = $rows['piclist'];
        }
        $content = addslashes($rows['content']) . '*0^' . $date . '^' . $content;
        if ($DB->query("UPDATE cmy_workorder set content= ?,piclist= ?,status=0 where id= ?", array($content, $piclist, $id))) {
            // 发送通知
            $ts = '无需发送给供货商';
            if ($sid > 1 && conf('master_notify_workorder_email') == 1) {
                $user = $DB->get_row("SELECT * from cmy_master where `zid`='{$sid}' limit 1");
                if ($user && validateData($user['email'], 'email')) {
                    try {
                        $ems = new \core\Ems();
                        if ($ems) {
                            $send = $ems->sendEmail($user['email'], '您有新的工单回复, 请及时处理', '<b>商品ID: </b>' . $res['tid'] . '<br/><b>工单ID:</b> ' . $id . '<br/><b>问题类型：</b>' . display_type($type) . '<br/><b>订单金额:</b> ' . $res['money'] . '元<br/><b>订单份数: </b>' . $res['value'] . '<br/><b>订单时间: </b>' . $res['addtime'] . '<br/>');
                            if ($send === true) {
                                $ts = '发送通知给供货商' . $user['user'] . '[' . $sid . ']成功';
                            } else {
                                $ts = '发送通知给供货商' . $user['user'] . '[' . $sid . ']失败, ' . $send;
                            }
                        }
                    } catch (\Throwable $th) {
                        $ts = '发送通知给供货商' . $user['user'] . '[' . $sid . ']失败, 发送执行错误=> ' . $th->getMessage();
                    }
                } else {
                    if (!$user) {
                        $ts = '发送通知给供货商失败, 供货商不存在 ';
                    } else {
                        $ts = '发送通知给供货商失败, 供货商邮箱绑定不正确： ' . $user['email'];
                    }
                }
                Db::name('workorder')->where(['id' => $id])->update(['ts' => $ts]);
            } else {
                if ($sid <= 1) {
                    if (conf('work_notice_email') == 1 && validateData($conf['adm_email'], 'email')) {
                        $ems = new \core\Ems();
                        if ($ems) {
                            $send = $ems->sendEmail($conf['adm_email'], '工单提醒！有来自' . $userrow['zid'] . '的工单回复, 请及时处理', '<b>用户UID: </b>' . $userrow['zid'] . '<br/><b>订单ID: </b>' . $orderid . '<br/><b>商品ID: </b>' . $res['tid'] . '<br/><b>工单ID:</b> ' . $workid . '<br/><b>问题类型：</b>' . display_type($type) . '<br/><b>订单金额:</b> ' . $res['money'] . '元<br/><b>订单份数: </b>' . $res['value'] . '<br/><b>订单时间: </b>' . $res['addtime'] . '<br/>');

                            if ($send === true) {
                                $ts = '发送通知给供货商' . $user['user'] . '[' . $sid . ']成功';
                            } else {
                                $ts = '发送通知给供货商' . $user['user'] . '[' . $sid . ']失败, ' . $send;
                            }
                        }
                    }
                }
            }
            showmsg('回复工单成功！请等待管理员处理。<br/><br/><a href="./workorder.php">>>返回工单列表</a>', 1);
        } else {
            showmsg('回复工单失败！' . $DB->error(), 4);
        }

    }
} elseif ($my == 'complete') {
    $id   = (int) $_GET['id'];
    $rows = $DB->get_row("SELECT * from cmy_workorder where id= ? and zid= ? limit 1", array($id, $userrow['zid']));
    if (!$rows) {
        showmsg('当前记录不存在！', 3);
    } elseif ($rows['status'] == 1) {
        showmsg('此工单已经结单', 3);
    }

    if ($DB->query("UPDATE cmy_workorder set status=2 where id= ?", array($id))) {
        exit("<script language='javascript'>alert('完结工单成功！');history.go(-1);</script>");
    } else {
        showmsg('完结工单失败！' . $DB->error(), 4);
    }

} elseif ($my == 'delete') {
    $id  = intval($_GET['id']);
    $sql = "DELETE FROM cmy_workorder WHERE id= ? and zid= ?";
    if ($DB->query($sql, array($id, $userrow['zid']))) {
        exit("<script language='javascript'>alert('删除成功！');history.go(-1);</script>");
    } else {
        showmsg('删除失败！' . $DB->error(), 4);
    }

} else {
    ?>
<div class="panel panel-default">
<table class="table table-bordered">
<tbody>
<tr height="25">
<td align="center"><font color="#808080"><b><i class="fa fa-exclamation-circle"></i>待我处理</b></br><b><?php echo $count1 ?></b></font></td>
<td align="center"><font color="#808080"><b><i class="fa fa-clock-o"></i>处理中</b></br></span><b><?php echo $count2 ?></b></font></td>
<td align="center"><font color="#808080"><b><i class="fa fa-check-circle"></i>全部工单</b></br><b><?php echo $count3 ?></b></font></td>
</tr>
</tbody>
</table>
</div>

<div class="panel panel-info" id="workorder_list">
     <div class="panel-heading"><h3 class="panel-title"><i class="fa fa-reorder"></i>&nbsp;&nbsp;<b>我的工单</b></h3></div>
     <div class="panel-body"><a href="./workorder.php?my=add" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;提交工单</a></div>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>ID</th><th>类型</th><th>订单号</th><th>问题描述</th><th>状态</th><th>提交时间</th><th>操作</th></tr></thead>
          <tbody>
<?php
$numrows  = $DB->count("SELECT count(*) from cmy_workorder WHERE zid= ?", array($userrow['zid']));
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

    $rs = $DB->query("SELECT * FROM cmy_workorder WHERE zid=? order by id desc limit ?,?", array($userrow['zid'], $offset, $pagesize));
    while ($res = $DB->fetch($rs)) {
        $content = explode('*', $res['name']);
        $content = mb_substr($content[0], 0, 16, 'utf-8');
        echo '<tr><td><b>' . $res['id'] . '</b></td><td>' . display_type($res['type']) . '</td><td><a href="javascript:showOrder(' . $res['orderid'] . ',\'' . md5($res['orderid'] . SYS_KEY . $res['orderid']) . '\')" title="查询订单详情">' . $res['orderid'] . '</a></td><td><a href="./workorder.php?my=view&id=' . $res['id'] . '">' . $content . '</a></td><td>' . display_status($res['status']) . '</td><td>' . $res['addtime'] . '</td><td><a href="./workorder.php?my=view&id=' . $res['id'] . '" class="btn btn-info btn-xs">查看</a>&nbsp;<a href="./workorder.php?my=delete&id=' . $res['id'] . '" class="btn btn-xs btn-danger" onclick="return confirm(\'你确实要删除此工单吗？\');">删除</a></td></tr>';
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
</div>
<?php include 'footer.php'?>