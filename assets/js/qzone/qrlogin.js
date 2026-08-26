var interval1, interval2, qrocde_server = '';

if (window.location.pathname.indexOf('user') >= 0) {
    qrocde_server = './qrlogin.php';
} else {
    qrocde_server = './user/qrlogin.php';
}

window.getqrpic = function() {
    cleartime();
    var getvcurl = qrocde_server + '?do=getqrpic&r=' + Math.random(1);
    $.get(getvcurl, function(d) {
        if (d.saveOK == 0) {
            $('#qrimg').attr('qrsig', d.qrsig);
            $('#qrimg').attr('qrurl', d.qrcode);
            $('#qrimg').html('<img id="qrcodeimg" onclick="getqrpic()" src="data:image/png;base64,' + d.data + '" title="点击刷新">');
            if (/Android|SymbianOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Windows Phone|Midp/i.test(navigator.userAgent)) {
                $('#mobile').show();
            }
            interval1 = setInterval(loginload, 1000);
            interval2 = setInterval(loadScript, 3500);
        } else {
            alert(d.msg);
        }
    }, 'json');
}

window.ptuiCB = function(code, uin, sid, skey, pskey, superkey, nick) {
    var msg = '请扫描二维码';
    switch (code) {
        case '0':
            $('#login').html('<div class="alert alert-success">登录成功</div><br/><div class="input-group"><span class="input-group-addon">QQ帐号</span><input id="uin" value="' + uin + '" class="form-control" /></div><br/><div class="input-group hide"><span class="input-group-addon">sid</span><input id="sid" value="' + sid + '" class="form-control" /></div><br/><div class="input-group hide"><span class="input-group-addon">pskey</span><input id="pskey" value="' + pskey + '" class="form-control" /></div><br/><div class="input-group hide"><span class="input-group-addon">skey</span><input id="skey" value="' + skey + '" class="form-control" /></div><br/><div class="input-group hide"><span class="input-group-addon">superkey</span><input id="superkey" value="' + superkey + '" class="form-control" /></div><br/><br/><h4>请关闭该页面或返回到之前页面再获取即可</h4><br/>');
            $('#qrimg').hide();
            $('#mobile').hide();
            $('#login').attr("data-lock", "true");
            cleartime();
            break;
        case '1':
            getqrpic(true);
            msg = '请重新扫描二维码';
            break;
        case '2':
            msg = '使用QQ手机版扫描二维码';
            break;
        case '3':
            msg = '扫描成功，请在手机上确认授权登录';
            break;
        default:
            msg = sid;
            console.log(msg);
            break;
    }
    if (msg.indexOf("登录成功") >= 0) {
        cleartime();
    }
    $("#loginload").css({
        'display': 'none'
    });
    $('#loginmsg').html(msg);
}

window.loadScript = function(c) {
    if ($('#login').attr("data-lock") === "true") return;
    var qrsig = $('#qrimg').attr('qrsig');
    c = c ? c : qrocde_server + "?do=qqlogin&qrsig=" + decodeURIComponent(qrsig) + "&r=" + Math.random(1);
    var a = document.createElement("script");
    a.onload = a.onreadystatechange = function() {
        if (!this.readyState || this.readyState === "loaded" || this.readyState === "complete") {
            if (typeof d == "function") {
                d()
            }
            a.onload = a.onreadystatechange = null;
            if (a.parentNode) {
                a.parentNode.removeChild(a)
            }
        }
    };
    a.src = c;
    document.getElementsByTagName("head")[0].appendChild(a)
}


window.loginload = function() {
    if ($('#login').attr("data-lock") === "true") return;
    var load = document.getElementById('loginload').innerHTML;
    var len = load.length;
    if (len > 2) {
        load = '.';
    } else {
        load += '.';
    }
    document.getElementById('loginload').innerHTML = load;
}


window.qrlogin = function() {
    if ($('#login').attr("data-lock") === "true") return;
    var qrsig = $('#qrimg').attr('qrsig');
    var url = qrocde_server + '?do=qqlogin&qrsig=' + decodeURIComponent(qrsig) + '&r=' + Math.random(1);
    $.get(url, function(d) {
        if (d.saveOK == 0) {
            $('#login').html('<div class="alert alert-success">登录成功</div><br/><div class="input-group"><span class="input-group-addon">QQ帐号</span><input id="uin" value="' + uin + '" class="form-control" /></div><br/><div class="input-group hide"><span class="input-group-addon">sid</span><input id="sid" value="' + sid + '" class="form-control" /></div><br/><div class="input-group hide"><span class="input-group-addon">pskey</span><input id="pskey" value="' + pskey + '" class="form-control" /></div><br/><div class="input-group hide"><span class="input-group-addon">skey</span><input id="skey" value="' + skey + '" class="form-control" /></div><br/><div class="input-group hide"><span class="input-group-addon">superkey</span><input id="superkey" value="' + superkey + '" class="form-control" /></div><br/><br/><h4>请关闭该页面或返回到之前页面再获取即可</h4><br/>');
            $('#qrimg').hide();
            $('#mobile').hide();
            $('#login').attr("data-lock", "true");
            cleartime();
        } else if (d.saveOK == 1) {
            getqrpic();
            $('#loginmsg').html('请重新扫描二维码');
        } else if (d.saveOK == 2) {
            $('#loginmsg').html('使用QQ手机版扫描二维码');
        } else if (d.saveOK == 3) {
            $('#loginmsg').html('扫描成功，请在手机上确认授权登录');
        } else {
            cleartime();
            $('#loginmsg').html(d.msg);
        }
    }, 'json');
}

function cleartime() {
    clearInterval(interval1);
    clearInterval(interval2);
}

function mloginurl() {
    var qrurl = $('#qrimg').attr('qrurl');
    $('#loginmsg').html('跳转到QQ登录后请返回此页面');
    var ua = window.navigator.userAgent.toLowerCase();
    var is_ios = ua.indexOf('iphone') > -1 || ua.indexOf('ipad') > -1;
    var schemacallback = '';
    if (is_ios) {
        schemacallback = 'weixin://';
    } else if (ua.indexOf('ucbrowser') > -1) {
        schemacallback = 'ucweb://';
    } else if (ua.indexOf('meizu') > -1) {
        schemacallback = 'mzbrowser://';
    } else if (ua.indexOf('liebaofast') > -1) {
        schemacallback = 'lb://';
    } else if (ua.indexOf('baidubrowser') > -1) {
        schemacallback = 'bdbrowser://';
    } else if (ua.indexOf('baiduboxapp') > -1) {
        schemacallback = 'bdapp://';
    } else if (ua.indexOf('mqqbrowser') > -1) {
        schemacallback = 'mqqbrowser://';
    } else if (ua.indexOf('qihoobrowser') > -1) {
        schemacallback = 'qihoobrowser://';
    } else if (ua.indexOf('chrome') > -1) {
        schemacallback = 'googlechrome://';
    } else if (ua.indexOf('sogoumobilebrowser') > -1) {
        schemacallback = 'SogouMSE://';
    } else if (ua.indexOf('xiaomi') > -1) {
        schemacallback = 'miuibrowser://';
    } else {
        schemacallback = 'googlechrome://';
    }
    if (is_ios) {
        alert('跳转到QQ登录后请手动返回当前浏览器');
        window.location.href = 'wtloginmqq3://ptlogin/qlogin?qrcode=' + encodeURIComponent(qrurl) + '&schemacallback=' + encodeURIComponent(schemacallback);
    } else {
        window.location.href = 'wtloginmqq://ptlogin/qlogin?qrcode=' + encodeURIComponent(qrurl) + '&schemacallback=' + encodeURIComponent(schemacallback);
    }
}
$(document).ready(function() {
    getqrpic();
});