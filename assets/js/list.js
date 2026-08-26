"use strict";
if ($("head").length > 0) {
    $("head").append('<style>.layui-layer-btn{display:box;display:-moz-box;display:-webkit-box;width:100%;height: auto;line-height:50px;}.layui-layer-btn a{display:block;-moz-box-flex:1;box-flex:1;-webkit-box-flex:1;font-size:14px;cursor:pointer;margin:5px 6px 0;padding:2px 18px;height: auto;text-align: center;}</style>')
} else {
    $("body").append('<style>.layui-layer-btn{display:box;display:-moz-box;display:-webkit-box;width:100%;height: auto;line-height:50px;}.layui-layer-btn a{display:block;-moz-box-flex:1;box-flex:1;-webkit-box-flex:1;font-size:14px;cursor:pointer;margin:5px 6px 0;padding:2px 18px;height: auto;text-align: center;}</style>')
}
var Corn = {
    runing: null,
    run: function (func, times) {
        this.runing = setInterval(func, times);
    },
    stop: function () {
        clearInterval(this.runing);
    }
}
var workBackCronZt = {};
var workBackCron = function () {
    var display = $("#work").css("display");
    if (display != 'none') {
        var orderid = $("#work_orderid").val();
        showWorksInfo(orderid);
    } else {
        workBackCronZt.cron.stop();
        workBackCronZt.zt = false;
    }
    return false;
}
var filename = (function () {
    var strUrl = window.location.href;
    var arrUrl = strUrl.split("/");
    var strPage = arrUrl[arrUrl.length - 1];
    if (strPage.indexOf("?") > -1) {
        var pageName = strPage.split("?");
        strPage = pageName[0];
    }
    return strPage;
})();
var $timestamp, obj, toTime;
var runUseTime = function () {
    if ($(obj).length < 1) {
        console.log("dom is not ：" + obj);
        clearInterval(toTime);
        return false;
    } else {
        $(obj).html(getTimeToDay($timestamp));
        $timestamp = $timestamp + 1;
        return true;
    }
};

function getUseTime($times, o) {
    $timestamp = $times;
    obj = o;
    toTime = setInterval(runUseTime, 1000);
}

function getTimeToDay($timestamp) {
    if ($timestamp <= 60) {
        return '0天0小时0分' + $timestamp + '秒';
    }
    var $day = Math.floor($timestamp / (3600 * 24));
    var $hour = Math.floor(($timestamp - 3600 * 24 * $day) / 3600);
    var $minutes = Math.floor(($timestamp - 3600 * 24 * $day - $hour * 3600) / 60);
    var $second = $timestamp - 3600 * 24 * $day - $hour * 3600 - $minutes * 60;
    $day = $day > 0 ? $day + '天' : '0天';
    $hour = $hour > 0 ? $hour + '小时' : '0小时';
    $minutes = $minutes > 0 ? $minutes + '分' : '0小时';
    $second = $second + '秒';
    return $day + $hour + $minutes + $second;
}

function orderStatus($zt, $is_curl) {
    if ($zt == 1 && $is_curl == 2) return '<font color=green>已提交</font>';
    else if ($zt == 1 && $is_curl == 4) {
        return '<font color=green>已完成</font>';
    } else if ($zt == 1) {
        return '<font color=green>已提交</font>';
    } else if ($zt == 2) return '<font color=orange>正在处理</font>';
    else if ($zt == 3) return '<font color=red>异常</font>';
    else if ($zt == 4) return '<font color=grey>已退款</font>';
    else if ($zt == 10) return '<font color=#8E9013>待退款</font>';
    else return '<font color=blue>待处理</font>';
}
$(document).on('click', '.showOrder', function (event) {
    event.preventDefault();
    /* Act on the event */
    var orderid = $(this).data('id');
    var skey = $(this).data('skey');
    showOrder(orderid, skey);
});
$(document).on('click', '.inputOrder', function (event) {
    event.preventDefault();
    /* Act on the event */
    var orderid = $(this).data('id');
    inputOrder(orderid);
});

function showOrder(id, skey) {
    if (id == 0) return false;
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: "POST",
        url: "../ajax.php?act=order",
        data: {
            id: id,
            skey: skey
        },
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                var item = '<table class="table table-condensed table-hover">';
                item += '<tr><td colspan="6" style="text-align:center"><b>订单基本信息</b></td></tr><tr><td class="info">订单编号</td><td colspan="5">' + id + '</td></tr><tr><td class="info">商户订单号</td><td colspan="5">' + data.payorder + '</td></tr><tr><td class="info">商品名称</td><td colspan="5">' + data.name + '</td></tr><tr><td class="info">订单金额</td><td colspan="5">' + data.money + '元</td></tr><tr><td class="info">购买时间</td><td colspan="5">' + data.date + '</td></tr><tr><td class="info">下单信息</td><td colspan="5">' + data.inputs + '</td></tr><tr><td class="info">订单状态</td><td colspan="5">' + orderStatus(data.status, data.is_curl) + '</td></tr>';
                if (data.list && 'object' == typeof data.list) {
                    var result = data.list;
                    item += '<tr><td colspan="6" style="text-align:center"><b>订单实时状态</b></td><tr><td class="warning">下单数量</td><td>' + result.num + '</td><td class="warning">下单时间</td><td colspan="3">' + result.add_time + '</td></tr><tr><td class="warning">初始数量</td><td>' + result.start_num + '</td><td class="warning">当前数量</td><td>' + result.now_num + '</td><td class="warning">订单状态</td><td><font color=blue>' + result.order_state + '</font></td></tr>';
                } else if (data.kminfo) {
                    item += '<tr><td colspan="6" style="text-align:center"><b>以下是你的卡密信息</b></td><tr><td colspan="6">' + data.kminfo + '</td></tr>';
                } else if (data.result) {
                    item += '<tr><td colspan="6" style="text-align:center"><b>处理结果</b></td><tr><td colspan="6">' + data.result + '</td></tr>';
                }
                if (data.alert) {
                    item += '<tr><td colspan="6" style="text-align:center"><b>商品简介</b></td><tr><td colspan="6">' + data.desc + '</td></tr>';
                }
                item += '</table>';
                if ($("#showOrder").length > 0) {
                    $("#showOrder_content").html(item);
                    $("#showOrder").modal('show');
                } else {
                    var area = [$(window).width() > 640 ? '540px' : '95%', 'auto'];
                    layer.open({
                        type: 1,
                        title: ['请收藏本站网址到浏览器书签-方便查单', 'color:red'],
                        area: area,
                        content: item,
                        btn: '关闭窗口',
                        yes: function (index, layero) {
                            layer.close(index);
                        },
                        success: function (index, layero) {
                            if (data.show_usetime && data.usetime && data.usetime > 0) {
                                getUseTime(data.usetime, "#order_usetime");
                            }
                            //重定义高度
                            let lh = $(index).height();
                            let wh = $(window).height();
                            if (lh > wh) {
                                layer.style(layero, {
                                    top: '20px',
                                    bottom: '12px',
                                    height: (wh - 32) + 'px'
                                });
                                var el = $(index).children('.layui-layer-content');
                                var el2 = $(index).children('.layui-layer-title');
                                var el3 = $(index).children('.layui-layer-btn');
                                el.css('height', (wh - el2.outerHeight() - el3.outerHeight() - 32) + 'px');
                            }
                        }
                    });
                }
            } else {
                layer.alert(data.msg);
            }
        }
    });
}

function inputOrder(id) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'GET',
        url: '../ajax.php?act=order2&id=' + id,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                var area = [$(window).width() > 640 ? '540px' : '95%', 'auto'];
                layer.open({
                    type: 1,
                    title: "修改订单数据",
                    shadeClose: false, //开启遮罩关闭
                    area: area,
                    content: data.data,
                    btn: ['关闭'],
                    success: function () {
                        checkInputName()
                    }
                });
            } else {
                layer.alert(data.msg);
            }
        },
        error: function (data) {
            layer.msg('服务器错误');
            return false;
        }
    });
}

function saveOrder(id) {
    var inputvalue = $("#edit_inputvalue").val();
    if (inputvalue == '' || $("#edit_inputvalue2").val() == '' || $("#edit_inputvalue3").val() == '') {
        layer.alert('请确保每项不能为空！');
        return false;
    } else if ($("#edit_inputvalue4").val() == '' || $("#edit_inputvalue5").val() == '' || $("#edit_bz").val() == '') {
        layer.alert('请确保每项不能为空！');
        return false;
    }
    $('#save').val('Loading');
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: "POST",
        url: "../ajax.php?act=editOrder",
        dataType: 'json',
        data: {
            id: id,
            inputvalue: inputvalue,
            inputvalue2: $("#edit_inputvalue2").val(),
            inputvalue3: $("#edit_inputvalue3").val(),
            inputvalue4: $("#edit_inputvalue4").val(),
            inputvalue5: $("#edit_inputvalue5").val(),
            bz: $("#edit_bz").val()
        },
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.open({
                    content: '修改订单数据成功！<br>注意使用最新的下单账号查询订单',
                    btn: ['我知道了'],
                    yes: function () {
                        layer.closeAll();
                        $("#qq3").val(data.inputvalue);
                        $('#list').empty();
                        $("#submit_query").click();
                    }
                });
            } else {
                layer.alert(data.msg);
            }
            $('#save').val('保存');
        }
    });
}

function checkInputName() {
    if ($("#edit_inputname").html() == '歌曲ID' || $("#edit_inputname").html() == '歌曲ＩＤ' || $("#edit_inputname").html() == '全民K歌歌曲链接' || $("#edit_inputname").html() == '歌曲链接' || $("#edit_inputname").html() == 'K歌歌曲链接') {
        return;
    } else if ($("#edit_inputname").html() == '火山ID' || $("#edit_inputname").html() == '火山作品ID' || $("#edit_inputname").html() == '火山视频ID' || $("#edit_inputname").html() == '火山ＩＤ') {
        return;
    } else if ($("#edit_inputname").html() == '抖音ID' || $("#edit_inputname").html() == '抖音作品ID' || $("#edit_inputname").html() == '抖音视频ID' || $("#edit_inputname").html() == '抖音ＩＤ' || $("#edit_inputname").html() == '抖音主页ID' || $("#edit_inputname").html() == '抖音作品链接' || $("#edit_inputname").html() == '抖音视频链接' || $("#edit_inputname").html().indexOf('抖喑') >= 0 || $("#edit_inputname").html().indexOf('dy') >= 0 || $("#edit_inputname").html().indexOf('DY') >= 0) {
        return;
    } else if ($("#edit_inputname").html() == '微视ID' || $("#edit_inputname").html() == '微视作品ID' || $("#edit_inputname").html() == '微视ＩＤ' || $("#edit_inputname").html() == '微视主页ID') {
        return;
    } else if ($("#edit_inputname").html() == '头条ID' || $("#edit_inputname").html() == '头条ＩＤ') {
        return;
    } else if ($("#edit_inputname").html() == '小红书ID' || $("#edit_inputname").html() == '小红书作品ID' || $("#edit_inputname").html() == '皮皮虾ID' || $("#edit_inputname").html() == '皮皮虾作品ID') {
        return;
    } else if ($("#edit_inputname").html() == '美拍ID' || $("#edit_inputname").html() == '美拍ＩＤ' || $("#edit_inputname").html() == '美拍作品ID' || $("#edit_inputname").html() == '美拍视频ID') {
        return;
    } else if ($("#edit_inputname").html() == '哔哩哔哩视频ID' || $("#edit_inputname").html() == '哔哩哔哩ID' || $("#edit_inputname").html() == '哔哩视频ID') {
        return;
    } else if ($("#edit_inputname").html() == '最右帖子ID') {
        return;
    } else if ($("#edit_inputname").html() == '全民视频ID' || $("#edit_inputname").html() == '全民小视频ID') {
        return;
    } else if ($("#edit_inputname").html() == '美图作品ID' || $("#edit_inputname").html() == '美图视频ID') {
        return;
    } else if ($("#edit_inputname2").html() == '说说ID' || ($("#edit_inputname2").html() == '说说ＩＤ')) {
        $("#editBtnBox").hide();
    } else if ($("#edit_inputname2").html() == '日志ID' || ($("#edit_inputname2").html() == '日志ＩＤ')) {
        $("#editBtnBox").hide();
    } else {
        $("#editBtnBox").hide();
        $("#editBtnBox2").hide();
    }
}

$(document).ready(function () {
    //修复订单详情的商品详情可能会太宽超出屏幕
    var cssHtml = '<style type="text/css">.table tr td img{max-width: 100%;}</style>';
    if ($("head").length > 0) {
        $("head").append(cssHtml);
    } else {
        $("body").append(cssHtml);
    }
});