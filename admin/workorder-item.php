<?php

include "../includes/common.php";
$my = isset($_GET['my']) ? daddslashes($_GET['my']) : null;

checkAuthority('works');

echo '
<link href="' . $cdnpublic . 'twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
<link rel="stylesheet" href="../assets/cmui/css/main.css?v=0.20">
<script src="' . $cdnpublic . 'jquery/3.1.1/jquery.min.js"></script>
<script src="' . $cdnpublic . 'layer/3.4.0//layer.js"></script>
<style>
.gdan_gout{width:100%;height:auto;background-color:#fff;padding-bottom:1em}
.gdan_txt{height:3em;line-height:3em;text-indent:1em;font-family:"微软雅黑";font-weight:800;}
.gdan_txt>span{position:absolute;right:4em;}
.gdan_zhugan{width:96%;height:auto;padding-top:1em;margin-left:2%;padding-left:.5em;padding-right:1em;margin-bottom:1em;border-top:dashed 1px #a9a9a9}
.gdan_kjia1{width:auto;margin-left:4em;margin-top:-3em}
.gdan_xiaozhi{width:100%;height:1em;color:#a9a9a9;margin-bottom:1em}
.gdan_xiaozhi>span{position:absolute;right:4em;}
.gdan_huifu{width:100%;height:auto;margin-top:1em;border-top:solid #ccc 1px}
.gdan_srk{width:98%;height:8em;margin-left:1%;margin-top:1em;border-color:#6495ed}
.gdan_huifu1{width:6em;height:2.5em;border:none;background-color:#1e90ff;color:#fff;margin:.5em 0 .5em 1%}
.gdan_jied{width:100%;height:3em;line-height:3em;text-align:center;color:#129DDE}
.gdan_cuow{width:100%;height:3em;line-height:3em;text-align:center;color:red}
</style>
';

echo '<div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top:10px;">    ';

function display_zt($zt, $id = 0)
{
    if ($zt == 1) {
        return '<font color=green>已完成</font>';
    } elseif ($zt == 2) {
        return '<font color=orange>处理中</font>';
    } else {
        return '<font color=blue>待处理</font>';
    }

}

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

if ($my == "reply") {
    $id  = intval($_GET['id']);
    $row = $DB->get_row("SELECT * from cmy_workorder where id='{$id}' limit 1");
    if (!$row) {
        showmsg('当前工单记录不存在！', 3);
    } else {
        $content = str_replace(array('*', '^', '|'), '', trim(strip_tags(daddslashes($_POST['content']))));
        $content = $row['content'] . '*1^' . $date . '^' . $content;
        if ($DB->query("UPDATE cmy_workorder set status=2,content='" . $content . "',endtime='{$date}' where id='{$id}'")) {
            $email = intval($_POST['email']);
            if ($email == 1 && $conf['mail_name'] && $conf['mail_pwd']) {
                $site      = $DB->get_row("SELECT * from cmy_site where zid='" . $row['zid'] . "' limit 1");
                $mail_name = $site['qq'] . '@qq.com';
                $content   = mb_substr($content, 0, 16, 'utf-8');
                $sub       = '工单已回复提醒';
                $msg       = '<b>尊敬的' . $site['user'] . ' 您好！</b><br/> 您于 ' . $row['addtime'] . ' 提交的工单有新回复内容，请注意查看。<br/>回复时间：' . $date;
                send_mail($mail_name, $sub, $msg);
            }
            exit("<script language='javascript'>alert('回复工单成功！');history.go(-1);</script>");
        } else {
            exit("<script language='javascript'>alert('回复工单失败！" . $DB->error() . "');history.go(-1);</script>");
        }
    }
} elseif ($my == "complete") {
    $id  = intval($_GET['id']);
    $row = $DB->get_row("SELECT * from cmy_workorder where id='{$id}' limit 1");
    if (!$row) {
        showmsg('当前工单记录不存在！', 3);
    } else {
        if ($DB->query("UPDATE cmy_workorder set status='1',endtime='{$date}' where id='{$id}'")) {
            exit("<script language='javascript'>alert('完结工单成功！');history.go(-1);</script>");
        } else {
            exit("<script language='javascript'>alert('完结工单失败！" . $DB->error() . "');history.go(-1);</script>");
        }
    }
} elseif ($my == "complete") {
//撤销
    $id  = intval($_GET['id']);
    $row = $DB->get_row("SELECT * from cmy_workorder where id= ? limit 1", array($id));
    if (!$row) {
        showmsg('当前工单记录不存在！', 3);
    } else {
        if ($DB->query("UPDATE cmy_workorder set status='1',endtime= ? where id= ?", array($date, $id))) {
            exit("<script language='javascript'>alert('完结工单成功！');history.go(-1);</script>");
        } else {
            exit("<script language='javascript'>alert('完结工单失败！" . $DB->error() . "');history.go(-1);</script>");
        }
    }
} elseif ($my == "view") {
    $id  = intval($_GET['id']);
    $row = $DB->get_row("SELECT * from cmy_workorder where id='{$id}' limit 1");

    if (!$row) {
        echo '<div class="gdan_gout">
                     <div class="gdan_cuow">此工单不存在，请确认后在试！</div>
                </div>';
    } else {
        if ($row['qq']) {
            $qq = $row['qq'];
        } else {
            $qq = '无';
        }
        $site     = $DB->get_row("SELECT * from cmy_site where zid='" . $row['zid'] . "' limit 1");
        $myimg    = 'http://q4.qlogo.cn/headimg_dl?dst_uin=' . $site['qq'] . '&spec=100';
        $kfimg    = 'https://imgcache.qq.com/open_proj/proj_qcloud_v2/mc_2014/work-order/css/img/custom-service-avatar.svg';
        $contents = explode('*', $row['content']);
        $piclist  = null;
        if ($row['piclist']) {
            $piclist = [
                "title" => "工单图片附件", //相册标题
                "id"    => $row['id'], //相册id
                "start" => 0, //初始显示的图片序号，默认0
                "data"  => [],
            ];
            $arr = explode(',', $row['piclist']);
            foreach ($arr as $key => $img) {
                if (stripos($img, 'http') === false) {
                    $img = '../' . $img;
                }
                $piclist['data'][$key] = [
                    "alt" => "图片" . ($key + 1),
                    "pid" => $key + 1, //图片id
                    "src" => $img, //原图地址
                ];
            }
        }

        echo '<div class="gdan_gout">
            <div class="gdan_txt">沟通记录 - ' . count($contents) . '<span>状态：' . display_zt($row['status']) . '</span></div>
            <!------------------开始沟通------------------------>
            <div class="gdan_zhugan" style="border: none;">
                <a href="./sitelist.php?zid=' . $row['zid'] . '" target="_blank"><img src="' . $myimg . '" class="img-circle" width="40"/></a>
                <div class="gdan_kjia1">
                    <div class="gdan_xiaozhi">问题描述<span>' . $row['addtime'] . '</span></div>
                    ' . $row['name'] . '
                    <br/><br/>
                    联系方式：' . $qq . '<br/>
                    订单编号：<a href="./list.php?id=' . $row['orderid'] . '" target="_blank">' . $row['orderid'] . '</a><br/>
                    问题类型：' . display_type($row['type']);
        if ($row['piclist']) {
            echo '<br/>图片附件：<a href="javascript:;" id="imgView">点击查看</a>';
        }

        echo '    </div>
            </div>';
        $num = count($contents);
        for ($i = 1; $i < $num; $i++) {
            $content = explode('^', $contents[$i]);
            if (count($content) == 3) {
                if ($i == $num - 1 && $content[0] == 1) {
                    echo '<div class="gdan_zhugan">
                        <img src="' . ($content[0] == 1 ? $kfimg : $myimg) . '" class="img-circle" width="40"/>
                        <div class="gdan_kjia1">
                        <div class="gdan_xiaozhi">' . ($content[0] == 1 ? '官方客服' : $site['user']) . '&nbsp;&nbsp;<a onclick="if(confirm(\'你确实要撤销此回复吗？\')){cancelWorks(' . $id . ');}">撤销</a><span>' . $content[1] . '</span></div>
                        ' . $content[2] . '
                        </div>
                    </div>';
                } else {
                    echo '<div class="gdan_zhugan">
                        <img src="' . ($content[0] == 1 ? $kfimg : $myimg) . '" class="img-circle" width="40"/>
                        <div class="gdan_kjia1">
                        <div class="gdan_xiaozhi">' . ($content[0] == 1 ? '官方客服' : $site['user']) . '<span>' . $content[1] . '</span></div>
                        ' . $content[2] . '
                        </div>
                    </div>';
                }
            }
        }

        if ($row['status'] == 1) {
            echo '<div class="gdan_jied">该工单已完结！</div>';
        } else {
            echo '<div class="gdan_huifu">
            <form action="./workorder-item.php?my=reply&id=' . $row['id'] . '" method="POST">
                <textarea class="gdan_srk" id="gdcontent" name="content" placeholder="回复后工单状态自动变为已处理 ,分站站点将会收到通知哦！" required></textarea><br/>
                <div class="form-group" style="margin:15px auto;width:95%">快捷模板：' . getModelList() . '</div><br/>
                <input type="checkbox" name="email" id="email" value="1" style="margin-left: 1%;"><label for="email">同时发送提醒邮件到用户邮箱</label><br/>
                <input type="submit" name="submit" value="提交回复" class="gdan_huifu1" />
                <input type="button" name="submit" value="完结工单" class="gdan_huifu1" style="background-color: mediumseagreen;" onclick="window.location.href=\'./workorder-item.php?my=complete&id=' . $row['id'] . '\'"/>
            </form>
            </div>
            ';
        }
        echo '
        </div>';
    }

}

echo "     </div>
    </div>
  </div>
</div>";
echo '
<script>
var id = "' . $row['id'] . '";
$(document).on("click", "#imgView", function(event) {
    event.preventDefault();
    /* Act on the event */
    var imgJson =' . (isset($piclist["data"]) ? json_encode($piclist) : "{}") . ';
    layer.photos({
        photos: imgJson,
        anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
    });
});
function escape2Html(str) {
 var arrEntities={"lt":"<","gt":">","nbsp":" ","amp":"&","quot":\'"\'};
 return str.replace(/&(lt|gt|nbsp|amp|quot);/ig,function(all,t){return arrEntities[t];});
}

function setReply(id,obj){
    var ii=layer.load(2, {shade:[0.1,\'#fff\']});
    $.ajax({
        type : \'POST\',
        url : \'model.php?my=getReply\',
        dataType : \'json\',
        data : {id:id},
        success : function(data) {
            layer.close(ii);
            if(data.code == 0){
                $("#gdcontent").val(escape2Html(data.reply));
            }
            else{
                layer.alert(data.msg);
            }
        },
        error:function(data){
            layer.close(ii);
            layer.msg(\'服务器错误\');
            return false;
        }
    });
}

function cancelWorks(id){
    var ii=layer.load(2, {shade:[0.1,\'#fff\']});
    $.ajax({
        type : \'GET\',
        url : \'ajax.php?act=cancelWorks\',
        dataType : \'json\',
        data : {id:id},
        success : function(data) {
            layer.close(ii);
            if(data.code == 0){
                window.location.reload();
            }
            else{
                alert(data.msg);
            }
        },
        error:function(data){
            layer.close(ii);
            alert(\'服务器错误\');
            return false;
        }
    });
}
</script>';

include_once 'footer.php';
