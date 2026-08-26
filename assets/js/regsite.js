//开通分站 By 星河云Plus
"use strict";
$(document).ready(function () {
    $("#qq").blur(function () {
        var qq = $(this).val();
        if (qq != "" && $("input[name='qz']").val() == "") {
            blurQz();
        }
    });
    if (typeof layer.tips === 'function') {
        $("input[name='qz']").focus(function () {
            layer.tips('输入需要的域名前缀，根据爱好拼音填写，如shuaibi', this, {
                tips: 1
            });
        });
        $("#user").focus(function () {
            layer.tips('请填写用户名，建议用容易记住的组合~', this, {
                tips: 1
            });
        });
        $("#pwd").focus(function () {
            layer.tips('请填写用户密码，为避免被盗不要和用户名相同~', this, {
                tips: 1
            });
        });
        $("#qq").focus(function () {
            layer.tips('填写您的QQ号码，支持5~12位的QQ号哦~', this, {
                tips: 1
            });
        });
        $("#name").focus(function () {
            layer.tips('请填写你想要的网站名称，如某某商城等等~', this, {
                tips: 1
            });
        });
        $("input[name='qz']").blur(function () {
            if ($(this).val() == "") return;
            var url = $(this).val() + '.' + $("select[name='domain']").val();
            $("input[name='fzurl']").val(url);
        });
        // layer.tips('忘了账号等可在此处找回密码~', ".findpwd", {
        //     tips: 1
        // });
    }
});

function blurQz() {
    var qq = $("input[name='qq']").val();
    if (qq) {
        var qz = getTjurl(qq);
        $("input[name='qz']").val(qz);
        var url = qz + '.' + $("select[name='domain']").val();
        $("input[name='fzurl']").val(url);
    } else {
        layer.alert("请先填写登录QQ");
        $("input[name='qq']").focus();
    }
}

function getTjurl(qq) {
    var str_arr = qq.split("");
    var num = randomNum(1, 5);
    var hz_arr = ['ds', 'dsw', 'wl', 'dswl', 'dkwl', 'dk', 'dkw', 'pt', 'dspt', 'dswl', 'dswpt'];
    var str = "";
    for (var i = 0; i < num; i++) {
        str += str_arr[randomNum(0, str_arr.length)];
    }
    str += hz_arr[randomNum(0, 10)];
    return str;
}

function randomNum(minNum, maxNum) {
    switch (arguments.length) {
        case 1:
            return parseInt(Math.random() * minNum, 10);
            break;
        case 2:
            return parseInt(Math.random() * (maxNum - minNum) + minNum, 10);
            break;
        default:
            return 0;
            break;
    }
}

function dopay(type, orderid) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: "POST",
        url: "../ajax.php?act=otherpay",
        data: {
            orderid: orderid,
            type: type
        },
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (type == 'rmb') {
                var ii2 = layer.msg('正在提交订单请稍候...', {
                    icon: 16,
                    shade: 0.5,
                    time: 15000
                });
                $.ajax({
                    type: "POST",
                    url: "../ajax.php?act=payrmb",
                    data: {
                        orderid: orderid
                    },
                    dataType: 'json',
                    success: function (data) {
                        layer.close(ii2);
                        if (data.code == 1) {
                            alert(data.msg);
                            window.location.href = './' + filename + '?buyok=1';
                        } else if (data.code == -2) {
                            alert(data.msg);
                            window.location.href = './' + filename + '?buyok=1';
                        } else if (data.code == -3) {
                            var confirmobj = layer.confirm('你的余额不足，请充值！', {
                                btn: ['立即充值', '取消']
                            }, function () {
                                window.location.href = './index.php#chongzhi';
                            }, function () {
                                layer.close(confirmobj);
                            });
                        } else {
                            layer.alert(data.msg);
                        }
                    },
                    error: function () {
                        layer.close(ii2);
                        layer.alert("服务器错误，请稍后重试！");
                    }
                });
            } else {
                window.location.href = '../other/submit.php?type=' + data.type + '&orderid=' + data.orderid;
            }
        },
        error: function () {
            layer.close(ii);
            layer.alert("服务器错误，请稍后重试或联系网站客服！");
        }
    });
}
var handlerEmbed = function (captchaObj) {
    captchaObj.appendTo('#captcha');
    captchaObj.onReady(function () {
        $("#captcha_wait").hide();
    }).onSuccess(function () {
        var result = captchaObj.getValidate();
        if (!result) {
            return alert('请完成验证');
        }
        var kind = $("select[name='kind']").val();
        var qz = $("input[name='qz']").val();
        if (qz == "") {
            return layer.alert('前缀域名不能为空！');
        }
        var domain = $("select[name='domain']").val();
        var name = $("input[name='name']").val();
        if ($("input[name='user']").length > 0) {
            var qq = $("input[name='qq']").val();
            var user = $("input[name='user']").val();
            var pwd = $("input[name='pwd']").val();
        }
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=paysite",
            data: {
                kind: kind,
                qz: qz,
                domain: domain,
                name: name,
                qq: qq,
                user: user,
                pwd: pwd,
                hashsalt: hashsalt,
                geetest_challenge: result.geetest_challenge,
                geetest_validate: result.geetest_validate,
                geetest_seccode: result.geetest_seccode
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code >= 0) {
                    layer.alert('开通分站成功！', {
                        icon: 1,
                        closeBtn: false
                    }, function () {
                        window.location.href = 'regok.php?zid=' + data.zid;
                    });
                } else {
                    layer.alert(data.msg);
                    captchaObj.reset();
                }
            }
        });
    });
};
$(document).ready(function () {
    $("input[name='qz']").blur(function () {
        var qz = $(this).val();
        var domain = $("select[name='domain']").val();
        if (qz) {
            $.get("ajax.php?act=checkdomain", {
                'qz': qz,
                'domain': domain
            }, function (data) {
                if (data == 1) {
                    layer.alert('你所填写的域名已被使用，请更换一个！');
                    //$("input[name='qz']").focus();
                }
            });
        }
    });
    $("input[name='user']").blur(function () {
        var user = $(this).val();
        if (user) {
            $.get("ajax.php?act=checkuser", {
                'user': user
            }, function (data) {
                if (data == 1) {
                    layer.alert('你所填写的用户名已存在！');
                    //$("input[name='user']").focus();
                }
            });
        }
    });
    $("#submit_buy").click(function () {
        var kind = $("select[name='kind']").val();
        var qz = $("input[name='qz']").val();
        var domain = $("select[name='domain']").val();
        var name = $("input[name='name']").val();
        if (qz == '' || name == '') {
            layer.alert('请确保每项不能为空！');
            return false;
        }
        if (qz.length < 2) {
            layer.alert('域名前缀太短！');
            return false;
        } else if (qz.length > 10) {
            layer.alert('域名前缀太长！');
            return false;
        } else if (name.length < 2) {
            layer.alert('网站名称太短！');
            return false;
        }
        if ($("input[name='user']").length > 0) {
            var qq = $("input[name='qq']").val();
            var user = $("input[name='user']").val();
            var pwd = $("input[name='pwd']").val();
            if (qq == '' || user == '' || pwd == '') {
                layer.alert('请确保每项不能为空！');
                return false;
            }
            if (qq.length < 5) {
                layer.alert('QQ格式不正确！');
                return false;
            } else if (user.length < 3) {
                layer.alert('用户名太短');
                return false;
            } else if (user.length > 20) {
                layer.alert('用户名太长');
                return false;
            } else if (pwd.length < 6) {
                layer.alert('密码不能低于6位');
                return false;
            } else if (pwd.length > 30) {
                layer.alert('密码太长');
                return false;
            }
        }
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=paysite",
            data: {
                kind: kind,
                qz: qz,
                domain: domain,
                name: name,
                qq: qq,
                user: user,
                pwd: pwd,
                hashsalt: hashsalt
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    var paymsg = '';
                    if (data.pay_alipay > 0) {
                        paymsg += '<button class="btn btn-default btn-block" onclick="dopay(\'alipay\',\'' + data.trade_no + '\')" style="margin-top:10px;"><img width="20" src="../assets/icon/alipay.ico" class="logo">支付宝</button>';
                    }
                    if (data.pay_qqpay > 0) {
                        paymsg += '<button class="btn btn-default btn-block" onclick="dopay(\'qqpay\',\'' + data.trade_no + '\')" style="margin-top:10px;"><img width="20" src="../assets/icon/qqpay.ico" class="logo">QQ钱包</button>';
                    }
                    if (data.pay_wxpay > 0) {
                        paymsg += '<button class="btn btn-default btn-block" onclick="dopay(\'wxpay\',\'' + data.trade_no + '\')" style="margin-top:10px;"><img width="20" src="../assets/icon/wechat.ico" class="logo">微信支付</button>';
                    }
                    if (data.pay_tenpay > 0) {
                        paymsg += '<button class="btn btn-default btn-block" onclick="dopay(\'tenpay\',\'' + data.trade_no + '\')" style="margin-top:10px;"><img width="20" src="../assets/icon/tenpay.ico" class="logo">财付通</button>';
                    }
                    if (data.pay_rmb > 0) {
                        paymsg += '<button class="btn btn-success btn-block" onclick="dopay(\'rmb\',\'' + data.trade_no + '\')">使用余额支付（还有' + data.user_rmb + '元）</button>';
                    }
                    var lay = layer.alert('<center><h4 style="font-size: 14px;">订单号：' + data.trade_no + '</h4><h2>￥ ' + data.need + '</h2><hr>' + paymsg + '<hr><a class="btn btn-default btn-block" onclick="layer.closeAll()">取消订单</a></center>', {
                        btn: [],
                        title: '提交订单成功',
                        closeBtn: false
                    });
                } else if (data.code == 1) {
                    layer.alert('开通分站成功！', {
                        icon: 1,
                        closeBtn: false
                    }, function () {
                        window.location.href = 'regok.php?zid=' + data.zid;
                    });
                } else if (data.code == 2) {
                    $.getScript("//static.geetest.com/static/tools/gt.js");
                    layer.open({
                        type: 1,
                        title: '完成验证',
                        skin: 'layui-layer-rim',
                        area: ['320px', '100px'],
                        content: '<div id="captcha"><div id="captcha_text">正在加载验证码</div><div id="captcha_wait"><div class="loading"><div class="loading-dot"></div><div class="loading-dot"></div><div class="loading-dot"></div><div class="loading-dot"></div></div></div></div>'
                    });
                    $.ajax({
                        url: "../ajax.php?act=captcha&t=" + (new Date()).getTime(),
                        type: "get",
                        dataType: "json",
                        success: function (data) {
                            $('#captcha_text').hide();
                            $('#captcha_wait').show();
                            initGeetest({
                                gt: data.gt,
                                challenge: data.challenge,
                                new_captcha: data.new_captcha,
                                product: "popup",
                                width: "100%",
                                offline: !data.success
                            }, handlerEmbed);
                        }
                    });
                } else {
                    layer.alert(data.msg);
                }
            }
        });
    });
});