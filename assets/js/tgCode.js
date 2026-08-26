//新版商品分享插件V1.2，兼容所有官方模板和遵循官方模块代码风格的模板
//By 星河  andywickshum@gmail.com  定制联系，目前唯一号
window.onload = function () {
    var btn1 = '<a onclick="get_tgimg()" id="submit_tg" class="btn btn-info btn-block">分享商品</a>';
    var btn2 = '<a onclick="get_tgimg()" id="submit_tg" class="btn btn-info">分享商品</a>';
    var dom = $('<a type="submit" id="submit_buy" class="btn btn-block btn-primary">立即购买</a>');
    if (dom.length > 0) {
        $("#submit_buy").after(btn2);
    }
    else {
        var dom2 = $('<input type="submit" id="submit_buy" class="btn btn-primary btn-block" value="立即购买">');
        if (dom2.length > 0) {
            $("#submit_buy").after(btn1);
        }
        else {
            console.log("分享商品插件已运行，未找到该模板预定义的dom节点！请联系开发者处理");
        }
    }
    console.log("分享商品插件运行完毕！");
}

if (typeof IsPC !== "function") {
    function IsPC() {
        var userAgentInfo = navigator.userAgent;
        var Agents = ["Android", "iPhone",
            "SymbianOS", "Windows Phone",
            "iPad", "iPod"];
        var flag = true;
        for (var i = 0; i < Agents.length; i++) {
            if (userAgentInfo.indexOf(Agents[i]) > 0) {
                flag = false;
                break;
            }
        }
        return flag;
    }
}

function get_tgimg() {
    var cid = $('#cid').val();
    var name = $('#tid option:selected').html();
    var tid = $('#tid option:selected').val();
    var font_size = '';
    var width = '';
    var height = '';
    if (IsPC() == true) {
        width = ($(window).width() * 0.3) + "px";
        height = ($(window).height() * 0.55) + "px";
        font_size = 'font-size:20px;font-size: 2rem;';
    }
    else {
        width = ($(window).width() * 0.8) + "px";
        height = ($(window).height() * 0.8) + "px";
        font_size = 'font-size:16px;font-size: 1.5rem;';
    }
    $.ajax({
        type: "POST",
        url: "/img.php?act=tg_img",
        dataType: 'json',
        data: {cid: cid, tid: tid},
        success: function (data) {
            if (data.code == 0) {
                layer.open({
                    type: 1,
                    title: '商品推广',
                    offset: 'auto',
                    area: ['300px', '420px'], //宽高
                    shade: 0.7,
                    anim: 1,
                    btnAlign: 'c',
                    content: data.str,
                    btn: ['关闭']
                });
                setTimeout(function () {
                    var imgHeight = $('#shopimg').height();
                    if (imgHeight > 85) {
                        $('#tg_name').css('height', imgHeight * 0.7 + 'px');
                    }
                    else {
                        if (imgHeight * 0.5 < 35) {
                            imgHeight = 70;
                        }
                        $('#tg_name').css('height', imgHeight * 0.5 + 'px');
                    }
                }, 180);

            }
            else {
                layer.alert(data.msg);
            }
        },
        error: function (data) {
            layer.alert("服务器出现错误，请稍后再试！");
        }
    });
};