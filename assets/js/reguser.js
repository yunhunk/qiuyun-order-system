//注册用户 By 星河云Plus
"use strict";
var $_GET = (function () {
    var url = window.document.location.href.toString();
    var u = url.split("?");
    if (typeof (u[1]) == "string") {
        u = u[1].split("&");
        var get = {};
        for (var i in u) {
            var j = u[i].split("=");
            get[j[0]] = j[1];
        }
        return get;
    } else {
        return {};
    }
})();
$(document).ready(function () {
    $("#user").focus(function () {
        if (typeof (layer.tips) == 'function') layer.tips('请填写用户名，支持字母数字小数点等，区分大小写~', this, {
            tips: 1
        });
    });
    $("#pwd").focus(function () {
        if (typeof (layer.tips) == 'function') layer.tips('请填写用户密码，规则同上！为避免被盗不要和用户名相同~', this, {
            tips: 1
        });
    });
    $("#qq").focus(function () {
        if (typeof (layer.tips) == 'function') layer.tips('填写您的QQ号码，支持5~12位的QQ号哦~', this, {
            tips: 1
        });
    });
    if ($(".findpwd").length > 0) {
        if (typeof (layer.tips) == 'function') layer.tips('忘了账号等可在此处找回密码~', ".findpwd", {
            tips: 1
        });
    }
});
var lay;
var handlerEmbed = function (captchaObj) {
    captchaObj.appendTo('#captcha');
    captchaObj.onReady(function () {
        $("#captcha_wait").hide();
    }).onSuccess(function () {
        var result = captchaObj.getValidate();
        if (!result) {
            return alert('请完成验证');
        }
        layer.close(lay);
        $("#captchaform").html('<input type="hidden" name="geetest_challenge" value="' + result.geetest_challenge + '" /><input type="hidden" name="geetest_validate" value="' + result.geetest_validate + '" /><input type="hidden" name="geetest_seccode" value="' + result.geetest_seccode + '" />');
        if ($("input[name='user']").val() != "") {
            $("#submit_reg").click();
        }
    });
};

function captchaLoad() {
    $.getScript("//static.geetest.com/static/tools/gt.js");
    $.ajax({
        url: "../ajax.php?act=captcha&t=" + (new Date()).getTime(),
        type: "get",
        dataType: "json",
        success: function (data) {
            $('#captchaContent').html('<div class="form-group"><div id="captcha"><div id="captcha_text">正在加载验证码</div><div id="captcha_wait"><div class="loading"><div class="loading-dot"></div><div class="loading-dot"></div><div class="loading-dot"></div><div class="loading-dot"></div></div></div></div></div>');
            $("#vcodeform").hide();
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
}
$(document).ready(function () {
    var captcha_type = $("input[name='captcha_type']").val() || 0;
    $("input[name='user']").blur(function () {
        var user = $(this).val();
        if (user) {
            $.get("ajax.php?act=checkuser", {
                'user': user
            }, function (data) {
                if (data == 1) {
                    layer.alert('你所填写的用户名已存在！');
                }
            });
        }
    });
    $("#submit_reg").click(function () {
        var user = $("input[name='user']").val();
        var pwd = $("input[name='pwd']").val();
        var qq = $("input[name='qq']").val();
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
        var data = {
            user: user,
            pwd: pwd,
            qq: qq,
            hashsalt: hashsalt
        };
        var adddata = {};
        if (captcha_type == 1) {
            var geetest_challenge = $("input[name='geetest_challenge']").val();
            if (geetest_challenge == undefined) {
                layer.msg('请先完成滑动验证！');
                captchaLoad();
                return false;
            }
            var geetest_validate = $("input[name='geetest_validate']").val();
            var geetest_seccode = $("input[name='geetest_seccode']").val();
            var adddata = {
                geetest_challenge: geetest_challenge,
                geetest_validate: geetest_validate,
                geetest_seccode: geetest_seccode
            };
        } else {
            if ($("#vcodeform").length > 0) $("#vcodeform").show();
            if ($("#codeform").length > 0) $("#codeform").show();
            if ($("input[name='code']").length > 0) {
                adddata = {
                    code: $("input[name='code']").val()
                };
                if (!adddata.code) {
                    layer.alert('验证码不能为空！');
                    return false;
                }
            } else if ($("input[name='captcha']").length > 0) {
                adddata = {
                    code: $("input[name='captcha']").val()
                };
                if (!adddata.code) {
                    layer.alert('验证码不能为空！');
                    return false;
                }
            }
        }
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=reguser",
            data: Object.assign(data, adddata),
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 1) {
                    if ($_GET['back'] == 'index') {
                        var gourl = '../';
                    } else if ($_GET['back'] == 'chongzhi' || $_GET['back'] == 'recharge') {
                        var gourl = './recharge.php';
                    } else if ($_GET['back']) {
                        var gourl = decodeURIComponent($_GET['back']);
                        if (gourl.indexOf('http') < 0) {
                            if (gourl.substring(0, 1) == '?') {
                                gourl = '/' + gourl;
                            } else if (gourl.substring(0, 1) != '/') {
                                gourl = '/' + gourl;
                            }
                        }
                    } else {
                        var gourl = './index.php';
                    }
                    layer.msg('注册成功，正在登录跳转..', {
                        icon: 1,
                        shade: 0.01,
                        time: 1000,
                        end: function () {
                            window.location.href = gourl;
                        }
                    });
                } else if (data.code == 2) {
                    layer.msg(data.msg);
                    $("#captchaContent").show();
                    $("#vcodeform").hide();
                    captchaLoad();
                } else if (data.code == 3) {
                    layer.msg(data.msg);
                    $("#vcodeform").show();
                    $("#captchaContent").hide();
                    if ($("img#codeimg").length > 0) {
                        $("img#codeimg").click();
                    }
                } else {
                    layer.alert(data.msg);
                }
            }
        });
    });
    if (captcha_type == 1) {
        captchaLoad();
    }
});