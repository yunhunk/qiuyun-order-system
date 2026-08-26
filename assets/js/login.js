var $_GET = (function() {
    var url = window.document.location.href.toString();
    var u = url.split("?");
    if (typeof(u[1]) == "string") {
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

function connect(type) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: "POST",
        url: "ajax.php?act=connect",
        data: {
            type: type,
            back: $_GET['back']
        },
        dataType: 'json',
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                window.location.href = data.url;
            } else {
                layer.alert(data.msg, {
                    icon: 7
                });
            }
        }
    });
}

function quickreg(type) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: "POST",
        url: "ajax.php?act=quickreg",
        data: {
            type: type,
            submit: 'do'
        },
        dataType: 'json',
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                if ($_GET['back'] == 'index') {
                    layer.msg('登录成功，正在跳转到首页', {
                        icon: 1,
                        shade: 0.01,
                        time: 15000
                    });
                    window.location.href = '../';
                } else {
                    layer.msg('登录成功，正在跳转到用户中心', {
                        icon: 1,
                        shade: 0.01,
                        time: 15000
                    });
                    window.location.href = './';
                }
            } else {
                layer.alert(data.msg, {
                    icon: 7
                });
            }
        }
    });
}
var handlerEmbed = function(captchaObj) {
    captchaObj.appendTo('#captcha');
    captchaObj.onReady(function() {
        $("#captcha_wait").hide();
    }).onSuccess(function() {
        var result = captchaObj.getValidate();
        if (!result) {
            return alert('请完成验证');
        }
        $("#captchaform").html('<input type="hidden" name="geetest_challenge" value="' + result.geetest_challenge + '" /><input type="hidden" name="geetest_validate" value="' + result.geetest_validate + '" /><input type="hidden" name="geetest_seccode" value="' + result.geetest_seccode + '" />');
    });
};
var handlerEmbed2 = function(token) {
    if (!token) {
        return alert('请完成验证');
    }
    $("#captchaform").html('<input type="hidden" name="token" value="' + token + '" />');
};
var handlerEmbed3 = function(vaptchaObj) {
    vaptchaObj.render();
    $('#captcha_text').hide();
    vaptchaObj.listen('pass', function() {
        var token = vaptchaObj.getToken();
        if (!token) {
            return alert('请完成验证');
        }
        $("#captchaform").html('<input type="hidden" name="token" value="' + token + '" />');
    })
};

function geetestInt() {
    $.getScript("//static.geetest.com/static/tools/gt.js", function() {
        $.ajax({
            url: "../ajax.php?act=captcha&t=" + (new Date()).getTime(),
            type: "get",
            dataType: "json",
            success: function(data) {
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
    });
}
$(document).ready(function() {
    var captcha_type = $("input[name='captcha_type']").val() || 0;
    $("#submit_login").click(function() {
        var user = $("input[name='user']").val();
        var pass = $("input[name='pass']").val();
        if (user == '' || pass == '') {
            layer.alert('用户名或密码不能为空！');
            return false;
        }
        var data = {
            user: user,
            pass: pass
        };
        var adddata = {};
        if (captcha_type == 1) {
            var geetest_challenge = $("input[name='geetest_challenge']").val();
            var geetest_validate = $("input[name='geetest_validate']").val();
            var geetest_seccode = $("input[name='geetest_seccode']").val();
            if (geetest_challenge == undefined) {
                layer.alert('请先完成滑动验证！');
                return false;
            }
            var adddata = {
                geetest_challenge: geetest_challenge,
                geetest_validate: geetest_validate,
                geetest_seccode: geetest_seccode
            };
        } else {
            if ($("#vcodeform").length > 0) $("#vcodeform").show();
            if ($("#codeform").length > 0) $("#codeform").show();
            if ($("input[name='code']").length > 0 || $("input#code").length > 0) {
                if ($("input[name='code']").length > 0) {
                    adddata = {
                        code: $("input[name='code']").val()
                    };
                } else {
                    adddata = {
                        code: $("input#code").val()
                    };
                }
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
            url: "ajax.php?act=login",
            data: Object.assign(data, adddata),
            dataType: 'json',
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
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
                        var gourl = './';
                    }
                    layer.msg('登录成功，正在跳转..', {
                        icon: 1,
                        shade: 0.01,
                        time: 1000,
                        end: function() {
                            window.location.href = gourl;
                        }
                    });
                } else if (data.code == 3) {
                    layer.msg(data.msg);
                    if ($("img#codeimg").length > 0) {
                        $("img#codeimg").click();
                    }
                } else {
                    layer.alert(data.msg, {
                        icon: 2
                    });
                }
            }
        });
    });
    if (captcha_type == 1) {
        geetestInt();
    }
});