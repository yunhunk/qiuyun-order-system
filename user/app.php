<?php
/**
 * 分站APP生成
 **/
include "../includes/common.php";
$title = '自助升级站点';

include './head.php';

if ($isLogin2 !== 1) {
    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

?>
<div class="wrapper">
<?php
if ($conf['app_open'] != 1) {
    showmsg('当前站点未开启此功能');
}

if ($userrow['power'] == 0) {
    showmsg('你没有权限使用此功能！', 3);
    die;
}

$select   = '';
$sitepath = substr($sitepath, 0, stripos(trim($sitepath, '/'), '/'));
$urls     = [
    'http://' . $userrow['siteurl'] . $sitepath,
];
if ($userrow['siteurl2']) {
    $urls[] = 'http://' . $userrow['siteurl2'] . $sitepath;
}
foreach ($urls as $key => $url) {
    $select .= '<option value="' . $url . '">' . $url . '</option>';
}

if ($userrow['power'] == 2) {
    $price = sprintf('%.2f', $conf['app_price']);
} else {
    $price = sprintf('%.2f', $conf['app_price2']);
}
?>

<div class="block">
    <div class="block-title clearfix"><h3>我的app管理</h3></div>
        <ul class="nav nav-tabs" data-toggle="tabs">

        <li class="active" style="width:50%"><a href="#index" data-toggle="tab"><center>生成App</center></a></li>
        <li style="width:50%" class=""><a href="#download" class="app-query" data-toggle="tab"><center>App下载</center></a></li>
        </ul>
        <div id="myTabContent" class="tab-content">
              <div class="tab-pane fade active in" id="index">
                  <form id="appForm">
                    <br/>
                    <div class="form-group">
                      <div class="input-group">
                        <div class="input-group-addon">
                          应用名称
                        </div>
                        <input name="name" class="form-control" value="<?php echo $userrow['sitename']; ?>" maxlength="12"/>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="input-group">
                        <div class="input-group-addon">
                          应用网址
                        </div>
                       <select name="url" class="form-control"><?php echo $select; ?></select>
                      </div>
                    </div>
                    <?php if ($conf['app_diy'] == 1): ?>
                    <div class="form-group">
                        <input id="file_icon" onchange="fileUpload(this, 'icon')" style="display:none;" type="file"/>
                        <div class="input-group">
                            <div class="input-group-addon">
                                应用图标
                            </div>
                            <input class="form-control" disabled="" id="icon" placeholder="不上传则使用默认应用图标" value=""/>
                            <input type="hidden" name="icon" value="1"/>
                            <div class="input-group-btn">
                                <a class="btn btn-success fileSelect" data-type="file_icon" title="上传图片">
                                    <i class="glyphicon glyphicon-upload">
                                    </i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <input id="file_background" onchange="fileUpload(this, 'background')" style="display:none;" type="file"/>
                        <div class="input-group">
                            <div class="input-group-addon">
                                应用启动图
                            </div>
                            <input class="form-control" disabled="" id="background" placeholder="不上传则使用默认应用启动图" value=""/>
                            <input type="hidden" name="background" value="2"/>
                            <div class="input-group-btn">
                                <a class="btn btn-success fileSelect" data-type="file_background" title="上传图片">
                                    <i class="glyphicon glyphicon-upload">
                                    </i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif;?>
                  <div class="form-group">
                    <div class="input-group">
                      <div class="input-group-addon">
                        生成花费
                      </div>
                      <input name="need" class="form-control" value="<?php echo $price; ?>" disabled="">
                      <div class="input-group-addon">
                        元
                      </div>
                    </div>
                  </div>
                </form>
                <a class="btn btn-primary btn-block" href="javascript:;" id="submit">开始生成</a>
              </div>
              <div class="tab-pane fade in" id="download">
                   <div id="result">
                   </div>

                   <br/>
                   <a class="btn btn-primary btn-block app-query" href="javascript:;">刷新任务结果</a>
             </div>
             <br/>
             <br/>
        </div>
    </div>
</div>
<script src="<?php echo $cdnpublic; ?>clipboard.js/1.7.1/clipboard.min.js?<?php echo $jsver; ?>"></script>
<script type="text/javascript">
$(document).on('click', '#submit', function(event) {
    event.preventDefault();
    /* Act on the event */
    var name = $("input[name='name']").val();
    var url = $("select[name='url']").val();
    var icon = $("input[name='icon']").data('fileid');
    var background = $("input[name='background']").data('fileid');
    if (name == '') {
        layer.alert('应用名称不能为空！');
        return false;
    }
    var confirmobj = layer.confirm('请确认你的APP信息↓<br/>应用名称：<font color="blue">' + name + '</font><br/>应用网址：<font color="blue">' + url + '</font>', {
        btn: ['确定', '取消']
    }, function() {
        var ii = layer.load(0);
        $.ajax({
            type: 'POST',
            url: 'ajax.php?act=app_add',
            data: $("#appForm").serialize(),
            dataType: 'json',
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.alert(data.msg, {
                        icon: 6,
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
                layer.msg('服务器错误，请稍后再试或联系平台客服');
                return false;
            }
        });
    }, function() {
        layer.close(confirmobj);
    });
});
$(document).on('click', '.app-query', function(event) {
    event.preventDefault();
    /* Act on the event */
    var ii = layer.load(0);
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=app_query',
        dataType: 'json',
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                var item = '<table class="table table-condensed table-hover" id="orderItem">';
                item += '<tr><td colspan="6" style="text-align:center" class="orderTitle"><b>APP生成任务结果<a href="javascript:;" class="pull-right btn btn-xs btn-default app-query"><i class="fa fa-refresh"></i>&nbsp;刷新</a></b></td></tr><tr><td class="info orderTitle">应用名称</td><td colspan="5" class="orderContent">' + data.data.name + '</td></tr><tr><td class="info orderTitle">应用网址</td><td colspan="5" class="orderContent">' + data.data.weburl + '</td></tr></tr><tr><td class="info orderTitle">创建时间</td><td colspan="5" class="orderContent">' + data.data.created_at + '</td></tr><tr><td class="info orderTitle">任务状态</td><td colspan="5" class="orderContent">' + (data.data.status == 1 ? '<span class="label label-success">成功</span>' : data.data.status == -1 ? '<span class="label label-danger">打包失败</span>&nbsp;失败后可尝试重新生成' : '<span class="label label-warning">正在打包，请稍候点击“刷新任务结果”查看</span>') + '</td></tr>';
                if (data.data.status == 1) {
                    var data = data.data;
                    //item += '<tr><td class="info orderTitle">双端下载页面</td><td colspan="5" class="orderContent"><a href="' + data.download_url + '" target="_blank" style="color:blue">' + data.download_url + '</a>（一年内有效）<br/></td></tr>';
                    if (data.lanzou_url) {
                        item += '<tr><td class="info orderTitle">安卓APP下载</td><td colspan="5" class="orderContent">（长期有效）<a href="' + data.lanzou_url + '" target="_blank" style="color:blue">' + data.lanzou_url + '</a></tr>';
                    } else {
                        item += '<tr><td class="info orderTitle">安卓APP下载</td><td colspan="5" class="orderContent">（一年内有效）<a href="' + data.android_url + '" target="_blank" style="color:blue">' + data.android_url + '</a></tr>';
                    }
                    item += '<tr><td class="info orderTitle">iOS APP下载</td><td colspan="5" class="orderContent">（复制到Safari访问打开）<a style="margin-left:3px;color:blue" href="' + data.ios_url + '" target="_blank">' + data.ios_url + '</a><a id="copy-btn" data-clipboard-text="' + data.ios_url + '" href="javascript:;" style="margin-left:3px;" class="btn btn-success btn-xs">复制</a></tr>';
                    // if (navigator.userAgent.indexOf('Windows') > -1) {
                    //     item += '<tr><td class="info orderTitle">扫码下载</td><td colspan="5" class="orderContent"><img style="box-shadow: 3px 3px 16px #eee" src="//api.qrserver.com/v1/create-qr-code/?size=150x150&margin=10&data=' + encodeURIComponent(data.download_url_show) + '"></td></tr>';
                    // }
                }
                item += '</table>';
                $("#result").html(item);
                $("#result").show();
            } else {
                item = '<div class="alert alert-danger"><i class="glyphicon glyphicon-info-sign"></i>&nbsp;' + data.msg + '</div>';
                $("#result").html(item);
                $("#result").show();
            }
        },
        error: function(data) {
            layer.close(ii);
            layer.msg('服务器错误，请稍后再试或联系平台客服');
            return false;
        }
    });
});
$(document).on('click', '.fileSelect', function(event) {
    var el = $(this).data('type');
    $("#" + el)[0].click();
});
$(document).on('click', '#copy-btn', function(event) {
    event.preventDefault();
    /* Act on the event */
    copyText(this);
});

function copyText(el) {
    clipboard = new Clipboard(el);
    clipboard.on('success', function() {
        setTimeout(function() {
            $(el).html('复制成功');
            layer.msg('复制成功~');
        }, 50);
        setTimeout(function() {
            $(el).html('复制');
        }, 2500);
    });
    clipboard.on('error', function() {
        layer.msg('<i class="fa fa-fw fa-frown-o text-muted"><\/i> 订单号失败，请长按选中后手动复制');
    });
    $(el).html('再点一次');
}
function fileUpload(el, des) {
    var fileObj = $(el)[0].files[0];
    if (typeof(fileObj) == "undefined" || fileObj.size <= 0) {
        return;
    }
    var formData = new FormData();
    formData.append("file", fileObj);
    formData.append("type", des);
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        url: "ajax.php?act=app_upload",
        data: formData,
        type: "POST",
        dataType: "json",
        cache: false,
        processData: false,
        contentType: false,
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                $("#" + des).val('上传成功(' + data.data.id + ')');
                $("input[name='" + des + "']").val(data.data.id);
            } else {
                layer.alert(data.msg);
            }
        },
        error: function(data) {
            layer.close(ii);
            layer.msg('服务器错误，请稍后再试或联系平台客服');
            return false;
        }
    })
}

</script>