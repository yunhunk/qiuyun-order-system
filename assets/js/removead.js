// 去除运营商和浏览器广告 V0.1 By 星河
var cName;
(function () {
    cName = (function () {
        len = 12;
        var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz';
        var maxPos = $chars.length;
        var pwd = '';
        for (i = 0; i < len; i++) {
            pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
        }
        return pwd;
    })();
    var divs = $('div'),
        imgs = $('img');
    for (var i = 0; i < divs.length; i++) {
        if (typeof ($(divs[i]).attr("class")) == 'string') {
            $(divs[i]).attr("class", $(divs[i]).attr("class") + " " + cName);
        } else {
            $(divs[i]).attr("class", cName);
        }
    }
    for (var i = 0; i < imgs.length; i++) {
        if (typeof ($(imgs[i]).attr("class")) == 'string') {
            $(imgs[i]).attr("class", $(imgs[i]).attr("class") + " " + cName);
        } else {
            $(imgs[i]).attr("class", cName);
        }
    }
})();

function removeBanner() {
    // var divs = $('div'),
    //     str,
    //     id;
    // for (var i = 0; i < divs.length; i++) {
    //     id = $(divs[i]).attr("id");
    //     if ('undefined' === typeof id || id.indexOf('aihecong') < 0) {
    //         str = typeof($(divs[i]).attr("class")) == 'string' ? $(divs[i]).attr("class") : '';
    //         if (str == "" || str.indexOf(cName) < 0) {
    //             $(divs[i]).css('display', 'none');
    //         }
    //     }
    // }
    var imgs = $('img'),
        str2;
    for (var i = 0; i < imgs.length; i++) {
        str2 = typeof ($(imgs[i]).attr("class")) == 'string' ? $(imgs[i]).attr("class") : '';
        if (str2 == '' || str2.indexOf(cName) < 0) {
            $(imgs[i]).css('display', 'none');
        }
    }
    $('iframe').css('display', 'none');
};
setTimeout(function () {
    removeBanner();
}, 50);