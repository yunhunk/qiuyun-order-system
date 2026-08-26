"use strict";
var serverPath = 'string' === typeof serverPath ? serverPath : './';

function dopay(type, orderid) {
    if (type == 'help') {
        var content = $("#demo_url").attr('data-url');
        var clipboard = new Clipboard('.btssn', {
            text: function() {
                return content;
            }
        });
        layer.open({
            title: '代付订单创建成功',
            btn: false,
            content: '<center style="color: seagreen">代付订单已经创建成功，发给好友帮你付款吧~</center><hr><textarea  class="layui-textarea">' + content + '</textarea><hr>' + '<center><button class="layui-btn layui-btn-fluid layui-btn-radius layui-btn-sm layui-btn-normal btssn">点击复制</button></center>',
        });
        clipboard.on('success', function(e) {
            swal({
                title: '恭喜',
                type: 'success',
                html: '代付订单链接已经帮您复制到剪切板上啦，快去发送给朋友让他帮你付款吧~',
                confirmButtonText: '好的',
            });
            layer.closeAll();
        });
        clipboard.on('error', function(e) {
            console.log(e);
            swal({
                title: '异常',
                type: 'warning',
                html: '复制功能好像出了点问题，去手动复制代付订单链接发给朋友吧',
                confirmButtonText: '好的',
            });
            layer.closeAll();
        });
        return false;
    } else {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=otherpay",
            data: {
                orderid: orderid,
                type: type
            },
            dataType: 'json',
            //超时时间30秒
            timeout: 30 * 1000,
            success: function(data) {
                layer.close(ii);
                if (type == 'rmb') {
                    var ii = layer.msg('正在提交订单请稍候...', {
                        icon: 16,
                        shade: 0.5,
                        time: 15000
                    });
                    $.ajax({
                        type: "POST",
                        url: serverPath + "ajax.php?act=payrmb",
                        data: {
                            orderid: orderid
                        },
                        dataType: 'json',
                        success: function(data) {
                            layer.close(ii);
                            if (data.code == 2 && data.skey) {
                                layer.msg('付款成功，即将跳转卡密发货页面..', {
                                    time: 800,
                                    end: function() {
                                        window.location.href = '?mod=faka&id=' + data.orderid + '&skey=' + data.skey;
                                    }
                                });
                            } else if (data.code == 1 || data.code == 2) {
                                layer.msg('付款成功，即将跳转到订单详情..', {
                                    time: 800,
                                    end: function() {
                                        window.location.href = '?buyok=1';
                                    }
                                });
                            } else if (data.code == -2) {
                                layer.alert(data.msg, {
                                    btn: ['我知道了'],
                                    end: function() {
                                        window.location.href = '?buyok=1';
                                    }
                                });
                            } else if (data.code == -3) {
                                var confirmobj = layer.confirm('你的余额不足，请充值！', {
                                    btn: ['立即充值', '取消']
                                }, function() {
                                    window.location.href = './user/#chongzhi';
                                }, function() {
                                    layer.close(confirmobj);
                                });
                            } else if (data.code == -4) {
                                var confirmobj = layer.confirm('你还未登录，是否现在登录？', {
                                    btn: ['登录', '注册', '取消']
                                }, function() {
                                    window.location.href = serverPath + 'user/login.php';
                                }, function() {
                                    window.location.href = serverPath + 'user/reg.php';
                                }, function() {
                                    layer.close(confirmobj);
                                });
                            } else {
                                layer.alert(data.msg);
                            }
                        }
                    });
                } else {
                    window.location.href = serverPath + 'other/submit.php?type=' + type + '&orderid=' + orderid;
                }
            },
            error: function() {
                layer.close(ii);
                layer.msg('系统错误，请稍后根据提示是否重新操作订单！');
                setTimeout(function() {
                    window.location.reload();
                }, 100);
            }
        });
    }
}
$(document).ready(function() {
    $("#dopay").click(function() {
        var paytype = $("input[name=pay]:checked").val();
        var orderid = $('#orderid').val();
        if (paytype == undefined) {
            swal({
                title: '提示',
                type: 'warning',
                html: '<h4>请选择付款方式！</h4>',
                confirmButtonText: '好的',
            });
            return false;
        }
        dopay(paytype, orderid);
    });
})