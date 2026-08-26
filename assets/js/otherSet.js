var $jsCode = "";
if (!$.cookie("toastr")) {
    $.ajax({
        type: "GET",
        url: "otherAjax.php?act=getConfig",
        dataType: 'json',
        success: function (data) {
            if (data.code == 0) {
                var isToastr = data.isToastr;
                var toastrTit = data.toastrTit;
                var toastrMsg = data.toastrMsg;
                var toastrTimeOut = data.toastrTimeOut ? data.toastrTimeOut : 1500;
                var toastrDisplay = data.toastrDisplay ? data.toastrDisplay : "toast-bottom-right";
                if (isToastr && toastrTit && !$.cookie("toastr")) {
                    $jsCode = '<link href="https://cdn.bootcss.com/toastr.js/latest/toastr.min.css" rel="stylesheet"/>';
                    $.getScript("https://cdn.bootcss.com/toastr.js/latest/toastr.min.js", function () {
                        $.cookie("toastr", 'toastr', 6 * 60 * 60);
                        toastr.options = {
                            closeButton: true,             // 是否显示关闭按钮，（提示框右上角关闭按钮）
                            debug: false,                  // 是否使用deBug模式
                            progressBar: true,             // 是否显示进度条，（设置关闭的超时时间进度条）
                            positionClass: toastrDisplay,  // 设置提示款显示的位置
                            onclick: null,                 // 点击消息框自定义事件   
                            timeOut: toastrTimeOut,        //  自动关闭超时时间 
                            extendedTimeOut: 1000,         //  加长展示时间
                        };

                        if (toastrMsg) {
                            toastr.warning(toastrMsg, toastrTit);
                        }

                        if (data.toastrCode) {
                            $jsCode += '<script type="text/javascript">' + data.toastrCode + '</script>';
                        }
                        $("body").append($jsCode);
                    });
                }
            }
        },
        error: function (data) {
            layer.close(ii);
            layer.msg('服务器错误');
            return false;
        }
    });
}
var u = navigator.userAgent;
var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1;
if (!$.cookie('appdTips') && isAndroid) {
    layer.tips('安卓APP端出来啦，下单更方便哦！', '#appurl', {tips: 1, times: 5});
    $.cookie("appdTips", 'appdTips', 24 * 60 * 60);
}

console.log("首页自定义通知插件运行完毕！");