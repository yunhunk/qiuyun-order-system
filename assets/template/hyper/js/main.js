"use strict";
var serverPath = 'string' === typeof serverPath ? serverPath : './';
var queryVal = null,
    orderid = null,
    is_showWork = false,
    is_orderWork = false,
    orderPage = 1,
    filename = '',
    captcha_reg = null,
    inputDisabled = false,
    interval_ref = null,
    interval_num = 0;
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

function getcount() {
    $.ajax({
        type: "GET",
        url: serverPath + "ajax.php?act=getcount",
        dataType: 'json',
        async: true,
        success: function (data) {
            $('#count_yxts').html(data.yxts);
            $('#count_orders').html(data.orders);
            $('#count_orders1').html(data.orders1);
            $('#count_orders2').html(data.orders2);
            $('#count_orders_all').html(data.orders);
            $('#count_orders_today').html(data.orders2);
            $('#count_money').html(data.money);
            $('#count_money1').html(data.money1);
            $('#count_site').html(data.site);
            if (data.gift != null) {
                $.each(data.gift, function (k, v) {
                    $('#pst_1').append('<li class="py-2 align-middle"><strong class="text-truncate d-inline-block align-middle" style="word-break:break-all;max-width:3.5rem;">' + k + '</strong> 获得&nbsp;' + v + '</li>');
                });
                $('.giftlist').show();
                $('.giftlist ul').css('height', (35 * $('#pst_1 li').length) + 'px');
                scollgift();
            }
        }
    });
}

function getHtmlDocName() {
    var str = window.location.href;
    str = str.substring(str.lastIndexOf("/") + 1);
    if (str.indexOf('?') >= 0) {
        str = str.substring(0, str.lastIndexOf("?"));
    }
    console.log(str);
    return str;
}

function getUrlParam(url) {
    url = url || $_GET;
    if (typeof (url) != 'array') {
        return '';
    }
    var ret = '';
    ret.forEach(function (val, index) {
        if (ret == '') {
            ret = index + '=' + val;
        } else {
            ret = ret + '&' + index + '=' + val;
        }
    });
    return ret;
}

function setHistory(queryStr) {
    if (typeof (queryStr) == 'undefined') {
        queryStr == '';
    }
    if (typeof history != "object" || typeof history.replaceState != "function") {
        $.getScript("https://lib.baomitu.com/history.js/1.7.1/native.history.min.js");
    }
    var url = window.document.location.href.toString();
    var u = url.split("?");
    var get = {};
    if (typeof (u[1]) == "string") {
        u = u[1].split("&");
        u.forEach(function (i, index) {
            if (i.indexOf('=') >= 0) {
                var j = i.split("=");
                get[j[0]] = j[1] != "" ? j[1] : '';
            } else {
                get[i] = '1';
            }
        });
    }
    var q = queryStr.split("&");
    q.forEach(function (i, index) {
        if (i.indexOf('=') >= 0) {
            var j = i.split("=");
            get[j[0]] = j[1] != "" ? j[1] : '';
        } else {
            get[i] = '1';
        }
    });
    var filename = getHtmlDocName();
    var str = '';
    if (typeof (get) == 'object' || typeof (get) == 'array') {
        $.each(get, function (index, item) {
            if (str == '') {
                str = index + '=' + item;
            } else {
                str = str + '&' + index + '=' + item;
            }
        });
        return window.history.replaceState(null, null, './' + filename + '?' + str);
    }
    return window.history.replaceState(null, null, './');
}
var pwdlayer;

function changepwd(id, skey) {
    pwdlayer = layer.open({
        type: 1,
        title: '修改密码',
        skin: 'layui-layer-rim',
        content: '<div class="input-group p-2 focused text-left"><div class="input-group my-2"><div class="input-group-prepend" style="margin:0;"><div class="input-group-text wxd-bor-rad0">新密码</div></div><input type="text" id="pwd" value="" class="form-control wxd-bor-rad0 pl-2" placeholder="请填写新的密码" required/></div><input type="submit" id="save" onclick="saveOrderPwd(' + id + ',\'' + skey + '\')" class="btn btn-primary btn-block" value="提交保存"></div>'
    });
}

function saveOrderPwd(id, skey) {
    var pwd = $("#pwd").val();
    if (pwd == '') {
        layer.alert('请确保每项不能为空！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: "POST",
        url: serverPath + "ajax.php?act=changepwd",
        data: {
            id: id,
            pwd: pwd,
            skey: skey
        },
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg('保存成功！');
                layer.close(pwdlayer);
            } else {
                layer.alert(data.msg, {
                    title: '小提示',
                    skin: 'layui-layer-molv layui-layer-wxd'
                });
            }
        }
    });
}

function scollgift() {
    setInterval(function () {
        var frist_li_idx = $("#pst_1 li:first");
        var c_li = frist_li_idx.clone();
        frist_li_idx.animate({
            "marginTop": "-35px",
            "opacity": "hide"
        }, 600, function () {
            $(this).remove();
            $("#pst_1").append(c_li);
        });
    }, 2000);
}

function getFinalPrice(price, prices, num) {
    var arr = [],
        arr2, arr3 = [];
    num = num > 0 ? num : 1;
    if (typeof (prices) != 'string' || prices == '') return 0;
    $.each(prices.split(','), function (index, item) {
        arr2 = item.split('|');
        arr.push('' + arr2[0]);
        arr3[arr2[0]] = '' + arr2[1];
    });
    arr.sort(function (a, b) {
        return b - a;
    });
    var discount = 0;
    $.each(arr, function (index, item) {
        if (num >= item) {
            discount = arr3[item];
            return false;
        }
    });
    if (discount >= price) {
        return 0;
    }
    return discount;
}

function getPoint() {
    if ($('#tid option:selected').val() == undefined || $('#tid option:selected').val() == "0") {
        $('#inputsname').html("");
        $('#need').val('');
        $('#display_price').hide();
        $('#display_num').hide();
        $('#display_left').hide();
        $('#alert_frame').hide();
        return false;
    }
    if ($('#searchkw').val() == '') {
        history.replaceState({}, null, './?cid=' + $('#cid').val() + '&tid=' + $('#tid option:selected').val());
    } else {
        history.replaceState({}, null, './');
    }
    var multi = $('#tid option:selected').attr('multi');
    var count = $('#tid option:selected').attr('count');
    var price = $('#tid option:selected').attr('price');
    var shopimg = $('#tid option:selected').attr('shopimg');
    var active = $('#tid option:selected').attr('active');
    var prices = $('#tid option:selected').attr('prices');
    var stock_open = parseInt($('#tid option:selected').attr('stock_open'));
    var stock = parseInt($('#tid option:selected').attr('stock'));
    var unit = $('#tid option:selected').attr('unit');
    if (undefined == unit || unit == '') {
        unit = '个';
    }
    if (typeof (prices) != 'undefined' && prices != '' && prices != 'null') {
        price = price - getFinalPrice(price, prices, 1);
    }
    $('#display_price').show();
    if (multi == 1 && count > 1) {
        $('#need').val('￥' + price + "元 ➠ " + count + unit);
    } else {
        $('#need').val('￥' + price + "元");
    }
    if (active != 1) {
        $('#submit_buy').val('当前商品已停止下单');
        layer.alert('当前商品维护中，停止下单！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
    } else if (price == 0) {
        $('#submit_buy').val('立即免费领取');
    } else {
        $('#submit_buy').val('立即购买');
    }
    if (multi == 1) {
        $('#display_num').show();
    } else {
        $('#display_num').hide();
    }
    var desc = $('#tid option:selected').attr('desc');
    if (desc != '' && alert != 'null') {
        $('#alert_frame').show();
        $('#alert_frame').html(decodeURI(desc));
    } else {
        $('#alert_frame').hide();
    }
    if (typeof shopimg !== 'undefined' && shopimg != "" && shopimg != "null") {
        if (shopimg.indexOf('http') < 0 && shopimg.indexOf('./') >= 0) {
            shopimg = serverPath + shopimg.split('./', 2)[1];
        }
        if ($('img[data-name="thumb"]').length > 0) {
            $('img[data-name="thumb"]').attr("src", shopimg);
        } else if ($('img#classImg').length > 0) {
            $('img#classImg').attr("src", shopimg);
        }
    }
    var inputnametype = '';
    $('#inputsname').html("");
    var inputname = $('#tid option:selected').attr('inputname');
    if (inputname == 'hide') {
        $('#inputsname').append('<input type="hidden" name="inputvalue" id="inputvalue" value="' + $.cookie('mysid') + '"/>');
    } else {
        var gettype = '';
        if (inputname.indexOf('[!shareurl]') >= 0) {
            gettype = 'shareurl="!shareurl"';
            inputname = inputname.replace('[!shareurl]', '');
        } else if (inputname.indexOf('[shareurl]') >= 0) {
            gettype = 'gettype="shareurl"';
            inputname = inputname.replace('[shareurl]', '');
        } else if (inputname.indexOf('[shareid]') >= 0) {
            gettype = 'gettype="shareid"';
            inputname = inputname.replace('[shareid]', '');
        } else if (inputname.indexOf('[zpid]') >= 0) {
            gettype = 'gettype="zpid"';
            inputname = inputname.replace('[zpid]', '');
        }
        var extendEventBlur = '';
        var extendEventAttr = '';
        //输入框价格变量
        if (inputname.indexOf('[int:') > 0) {
            extendEventBlur = 'numChange(this);';
            var str1 = inputname.split('[int:')[1];
            inputname = inputname.split('[int:')[0];
            extendEventAttr = ' price="' + price + '" extendBlurAttr="' + str1.split(']')[0] + '"';
        }
        var placeholder = "";
        if (inputname.indexOf("&") !== (-1)) {
            placeholder = inputname.split('&')[1];
            inputname = inputname.split('&')[0];
        } else {
            placeholder = '输入' + inputname;
        }
        if (inputname == '') inputname = '下单账号';
        if (extendEventBlur == '' && inputname.indexOf('{') > 0 && inputname.indexOf('}') > 0) {
            var addstr = '';
            var selectname = inputname.split('{')[0];
            var selectstr = inputname.split('{')[1].split('}')[0];
            $.each(selectstr.split(','), function (i, v) {
                if (v.indexOf(':') > 0) {
                    i = v.split(':')[0];
                    v = v.split(':')[1];
                } else {
                    i = v;
                }
                addstr += '<option value="' + i + '">' + v + '</option>';
            });
            $('#inputsname').append('<div class="input-group mb-2"><div class="input-group-append input-group-addon" id="inputname">' + selectname + '</div><select name="inputvalue" id="inputvalue" placeholder="' + placeholder + '" inputname="' + selectname + '"  class="form-control input">' + addstr + '</select></div></div>');
        } else {
            $('#inputname').html(inputname);
            $('#inputsname').append('<div class="input-group mb-2"><div class="input-group-append input-group-addon"><span id="inputname" class="input-group-text">' + inputname + '</span></div><input type="text" name="inputvalue" id="inputvalue" ' + gettype + ' placeholder="' + placeholder + '" value="' + ($_GET['qq'] ? $_GET['qq'] : '') + '" inputname="' + inputname + '" class="form-control input" required onblur="checkInput();' + extendEventBlur + '" ' + extendEventAttr + '/></div>');
        }
    }
    var inputsname = $('#tid option:selected').attr('inputsname');
    if (inputsname != '') {
        $.each(inputsname.split('|'), function (i, value) {
            var gettype = '';
            if (value.indexOf('[!shareurl]') >= 0) {
                gettype = 'shareurl="!shareurl"';
                value = value.replace('[!shareurl]', '');
            } else if (value.indexOf('[shareurl]') >= 0) {
                gettype = 'gettype="shareurl"';
                value = value.replace('[shareurl]', '');
            } else if (value.indexOf('[shareid]') >= 0) {
                gettype = 'gettype="shareid"';
                value = value.replace('[shareid]', '');
            } else if (value.indexOf('[zpid]') >= 0) {
                gettype = 'gettype="zpid"';
                value = value.replace('[zpid]', '');
            }
            if (value.indexOf('[') > 0 && value.indexOf(']') > 0) {
                inputsnametype = value.split('[')[1].split(']')[0];
                value = value.split('[')[0];
            }
            var extendEventBlur = '';
            var extendEventAttr = '';
            //输入框价格变量
            if (value.indexOf('[int:') > 0) {
                extendEventBlur = 'numChange(this);';
                var str1 = value.split('[int:')[1];
                value = value.split('[int:')[0];
                extendEventAttr = ' price="' + price + '" extendBlurAttr="' + str1.split(']')[0] + '"';
            }
            if (value.indexOf('{') > 0 && value.indexOf('}') > 0) {
                var addstr = '';
                var selectname = value.split('{')[0];
                var selectstr = value.split('{')[1].split('}')[0];
                $.each(selectstr.split(','), function (i, v) {
                    if (v.indexOf(':') > 0) {
                        i = v.split(':')[0];
                        v = v.split(':')[1];
                    } else {
                        i = v;
                    }
                    addstr += '<option value="' + i + '">' + v + '</option>';
                });
                $('#inputsname').append('<div class="input-group mb-2 focused text-left"><div class="input-group-text wxd-bor-radr0 wxd-bor-rad0" id="inputname' + (i + 2) + '">' + selectname + '</div><select name="inputvalue' + (i + 2) + '" id="inputvalue' + (i + 2) + '" class="form-control pl-2">' + addstr + '</select></div>');
            } else {
                if (value == '说说ID' || value == '说说ＩＤ') {
                    var addstr = '<div class="input-group-append input-group-addon onclick" onclick="get_shuoshuo(\'inputvalue' + (i + 2) + '\',$(\'#inputvalue\').val())"><span class="btn btn-primary wxd-bor-rad0">获取</span></div>';
                } else if (value == '日志ID' || value == '日志ＩＤ') {
                    var addstr = '<div class="input-group-append input-group-addon onclick" onclick="get_rizhi(\'inputvalue' + (i + 2) + '\',$(\'#inputvalue\').val())"><span class="btn btn-primary wxd-bor-rad0">获取</span></div>';
                } else if (value == '作品ID' || value == '作品ＩＤ') {
                    var addstr = '<div class="input-group-append input-group-addon onclick" onclick="getshareid2(\'inputvalue' + (i + 2) + '\',$(\'#inputvalue\').val())"><span class="btn btn-primary wxd-bor-rad0">获取</span></div>';
                } else if (value == '收货地址' || value == '收货人地址') {
                    var addstr = '<div class="input-group-append input-group-addon onclick" onclick="inputAddress(\'inputvalue' + (i + 2) + '\')"><span class="btn btn-primary wxd-bor-rad0">填写地址</span></div>';
                } else {
                    var addstr = '';
                }
                var ibtn = '';
                if (value.indexOf("&") !== (-1)) {
                    var btnArr = value.split('&');
                    ibtn = btnArr[0];
                    var placeholder = btnArr[1];
                } else {
                    ibtn = value;
                    var placeholder = '输入' + ibtn;
                }
                $('#inputsname').append('<div class="input-group mb-2"><div class="input-group-append input-group-addon"><span id="inputname' + (i + 2) + '" class="input-group-text">' + inputname + '</span></div><input type="text" name="inputvalue' + (i + 2) + '" id="inputvalue' + (i + 2) + '" ' + gettype + ' placeholder="' + placeholder + '" value="' + ($_GET['qq'] ? $_GET['qq'] : '') + '" inputname="' + inputname + '" class="form-control pl-2 input" required onblur="' + extendEventBlur + '" ' + extendEventAttr + ' required/>' + addstr + '</div>');
            }
        });
    }
    var gettype = $('#inputvalue').attr('gettype');
    if (typeof gettype === 'string' && gettype != "null" && gettype != "") {
        if (gettype == 'zpid') {
            $('#inputvalue').attr("placeholder", '粘贴分享链接,自动获取作品ID');
        } else if (gettype == 'shareid') {
            $('#inputvalue').attr("placeholder", '粘贴分享链接，自动获取用户ID');
        } else if (gettype == 'shareurl') {
            $('#inputvalue').attr("placeholder", '粘贴分享链接，自动格式化链接');
        } else {
            $('#inputvalue').removeAttr("placeholder");
        }
    } else {
        $('#inputvalue').removeAttr("placeholder");
    }
    var inputsname = $('#tid option:selected').attr('inputsname');
    if (typeof inputsname === 'string' && inputsname != "" && inputsname != "null") {
        if (inputname.indexOf('[zpid]') >= 0) {
            $('#inputvalue').attr('gettype', 'zpid');
            $('#inputvalue2').attr('gettype', 'zpid');
            $('#inputvalue2').attr("placeholder", '此处输入作品链接，自动获取');
        }
    }
    if ($('#tid option:selected').attr('isfaka') == 1) {
        $('#inputvalue').attr("placeholder", "用于接收卡密以及查询订单使用");
        $('#display_left').show();
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=getleftcount",
            data: {
                tid: $('#tid option:selected').val()
            },
            dataType: 'json',
            success: function (data) {
                $('#leftcount').val(data.count)
            }
        });
        if ($.cookie('email')) $('#inputvalue').val($.cookie('email'));
    } else if (stock_open == 1) {
        $('#display_left').show();
        $('#leftcount').val(stock);
    } else {
        $('#display_left').hide();
    }
    var alert = $('#tid option:selected').attr('alert');
    if (alert && alert != '' && alert != 'null' && active == 1) {
        var ii = layer.alert('' + decodeURI(alert) + '', {
            btn: ['我知道了'],
            title: '商品提示'
        }, function () {
            layer.close(ii);
        });
    }
}

function isEmptyVariable($var) {
    if (typeof $var == 'undefined' || 'null' == $var || '' == $var) {
        return true;
    }
    return false;
}

function isInStr(string, find) {
    if (typeof string != 'string') {
        return false;
    }
    return string.indexOf(find) >= 0;
}

function get_shuoshuo(id, uin, km, page) {
    km = km || 0;
    page = page || 1;
    if (uin == '') {
        layer.alert('请先填写QQ号！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: "GET",
        url: serverPath + "ajax.php?act=getshuoshuo&uin=" + uin + "&page=" + page + "&hashsalt=" + hashsalt,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                var addstr = '';
                $.each(data.data, function (i, item) {
                    addstr += '<option value="' + item.tid + '">' + item.content + '</option>';
                });
                var nextpage = page + 1;
                var lastpage = page > 1 ? page - 1 : 1;
                if ($('#show_shuoshuo').length > 0) {
                    $('#show_shuoshuo').html('<div class="input-group mb-2 focused text-left"><div class="input-group-prepend onclick" title="上一页" onclick="get_shuoshuo(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + lastpage + ')"><span class="btn btn-dark wxd-bor-rad0"><i class="fa fa-chevron-left"></i></span></div><select id="shuoid" class="form-control px-3" onchange="set_shuoshuo(\'' + id + '\');">' + addstr + '</select><div class="input-group-append input-group-addon onclick" title="下一页" onclick="get_shuoshuo(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + nextpage + ')"><span class="btn btn-dark wxd-bor-rad0"><i class="fa fa-chevron-right"></i></span></div></div>');
                } else {
                    $('#inputsname').append('<div class="input-group mb-2 focused text-left" id="show_shuoshuo"><div class="input-group-prepend onclick" title="上一页" onclick="get_shuoshuo(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + lastpage + ')"><span class="btn btn-dark wxd-bor-rad0"><i class="fa fa-chevron-left"></i></span></div><select id="shuoid" class="form-control px-3" onchange="set_shuoshuo(\'' + id + '\');">' + addstr + '</select><div class="input-group-append input-group-addon onclick" title="下一页" onclick="get_shuoshuo(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + nextpage + ')"><span class="btn btn-dark wxd-bor-rad0"><i class="fa fa-chevron-right"></i></span></div></div></div>');
                }
                set_shuoshuo(id);
            } else {
                layer.alert(data.msg, {
                    title: '小提示',
                    skin: 'layui-layer-molv layui-layer-wxd'
                });
            }
        }
    });
}

function set_shuoshuo(id) {
    var shuoid = $('#shuoid').val();
    $('#' + id).val(shuoid);
}

function get_rizhi(id, uin, km, page) {
    km = km || 0;
    page = page || 1;
    if (uin == '') {
        layer.alert('请先填写QQ号！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: "GET",
        url: serverPath + "ajax.php?act=getrizhi&uin=" + uin + "&page=" + page + "&hashsalt=" + hashsalt,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                var addstr = '';
                $.each(data.data, function (i, item) {
                    addstr += '<option value="' + item.blogId + '">' + item.title + '</option>';
                });
                var nextpage = page + 1;
                var lastpage = page > 1 ? page - 1 : 1;
                if ($('#show_rizhi').length > 0) {
                    $('#show_rizhi').html('<div class="input-group"><div class="input-group-prepend onclick" onclick="get_rizhi(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + lastpage + ')"><span class="btn btn-dark wxd-bor-rad0"><i class="fa fa-chevron-left"></i></span></div><select id="blogid" class="form-control px-3" onchange="set_rizhi(\'' + id + '\');">' + addstr + '</select><div class="input-group-append input-group-addon onclick" onclick="get_rizhi(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + nextpage + ')"><span class="btn btn-dark wxd-bor-rad0"><i class="fa fa-chevron-right"></i></span></div></div>');
                } else {
                    $('#inputsname').append('<div class="input-group mb-2 focused text-left" id="show_rizhi"><div class="input-group-prepend onclick" onclick="get_rizhi(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + lastpage + ')"><span class="btn btn-dark wxd-bor-rad0"><i class="fa fa-chevron-left"></i></span></div><select id="blogid" class="form-control px-3" onchange="set_rizhi(\'' + id + '\');">' + addstr + '</select><div class="input-group-append input-group-addon onclick" onclick="get_rizhi(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + nextpage + ')"><span class="btn btn-dark wxd-bor-rad0"><i class="fa fa-chevron-right"></i></span></div></div>');
                }
                set_rizhi(id);
            } else {
                layer.alert(data.msg, {
                    title: '小提示',
                    skin: 'layui-layer-molv layui-layer-wxd'
                });
            }
        }
    });
}

function set_rizhi(id) {
    var blogid = $('#blogid').val();
    $('#' + id).val(blogid);
}

function fillOrder(id, skey) {
    if (!confirm('是否确定补交订单？', {
        title: '小提示',
        skin: 'layui-layer-molv layui-layer-wxd'
    })) return;
    $.ajax({
        type: "POST",
        url: serverPath + "ajax.php?act=fill",
        data: {
            orderid: id,
            skey: skey
        },
        dataType: 'json',
        success: function (data) {
            layer.alert(data.msg, {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            $("#submit_query").click();
        }
    });
}

function getsongid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    if (songurl.indexOf('.qq.com') < 0) {
        layer.alert('请输入正确的歌曲的分享链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    try {
        var songid = songurl.split('s=')[1].split('&')[0];
    } catch (e) {
        layer.alert('请输入正确的歌曲的分享链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    $('#inputvalue').val(songid);
}

function gethuoshanid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    if (songurl.indexOf('.huoshan.com') < 0) {
        layer.alert('请输入正确的链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    if (songurl.indexOf('/s/') > 0) {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=gethuoshan",
            data: {
                url: songurl
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#inputvalue').val(data.itemid);
                } else {
                    layer.alert(data.msg, {
                        title: '小提示',
                        skin: 'layui-layer-molv layui-layer-wxd'
                    });
                    return false;
                }
            }
        });
    } else {
        try {
            if (songurl.indexOf('video/') > 0) {
                var songid = songurl.split('video/')[1].split('/')[0];
            } else if (songurl.indexOf('item/') > 0) {
                var songid = songurl.split('item/')[1].split('/')[0];
            } else if (songurl.indexOf('room/') > 0) {
                var songid = songurl.split('room/')[1].split('/')[0];
            } else {
                var songid = songurl.split('user/')[1].split('/')[0];
            }
        } catch (e) {
            layer.alert('请输入正确的链接！', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        $('#inputvalue').val(songid);
    }
}

function getdouyinid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    if (songurl.indexOf('.douyin.com') < 0 && songurl.indexOf('.iesdouyin.com') < 0) {
        layer.alert('请输入正确的链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    if (songurl.indexOf('/v.douyin.com/') > 0) {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=getdouyin",
            data: {
                url: songurl
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#inputvalue').val(data.songid);
                } else {
                    layer.alert(data.msg, {
                        title: '小提示',
                        skin: 'layui-layer-molv layui-layer-wxd'
                    });
                    return false;
                }
            }
        });
    } else {
        try {
            if (songurl.indexOf('video/') > 0) {
                var songid = songurl.split('video/')[1].split('/')[0];
            } else if (songurl.indexOf('music/') > 0) {
                var songid = songurl.split('music/')[1].split('/')[0];
            } else {
                var songid = songurl.split('user/')[1].split('/')[0];
            }
        } catch (e) {
            layer.alert('请输入正确的链接！', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        $('#inputvalue').val(songid);
    }
}

function gettoutiaoid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    if (songurl.indexOf('.toutiao.com') < 0) {
        layer.alert('请输入正确的链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    try {
        if (songurl.indexOf('user/') > 0) {
            var songid = songurl.split('user/')[1].split('/')[0];
        } else if (songurl.indexOf('group/') > 0) {
            var songid = songurl.split('group/')[1].split('/')[0];
        } else {
            var songid = songurl.split('profile/')[1].split('/')[0];
        }
    } catch (e) {
        layer.alert('请输入正确的链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    $('#inputvalue').val(songid);
}

function getweishiid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    if (songurl.indexOf('.qq.com') < 0) {
        layer.alert('请输入正确的链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    try {
        if (songurl.indexOf('feed/') > 0) {
            var songid = songurl.split('feed/')[1].split('/')[0];
        } else if (songurl.indexOf('personal/') > 0) {
            var songid = songurl.split('personal/')[1].split('/')[0];
        } else {
            var songid = songurl.split('id=')[1].split('&')[0];
        }
    } catch (e) {
        layer.alert('请输入正确的链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    $('#inputvalue').val(songid);
}

function getxiaohongshuid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    if (songurl.indexOf('/t.cn/') > 0 || songurl.indexOf('/xhsurl.com/') > 0 || songurl.indexOf('/w.url.cn/') > 0) {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=getxiaohongshu",
            data: {
                url: songurl
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#inputvalue').val(data.songid);
                } else {
                    layer.alert(data.msg, {
                        title: '小提示',
                        skin: 'layui-layer-molv layui-layer-wxd'
                    });
                    return false;
                }
            }
        });
    } else {
        try {
            if (songurl.indexOf('.xiaohongshu.com') < 0 && songurl.indexOf('pipix.com') < 0) {
                layer.alert('请输入正确的链接！', {
                    title: '小提示',
                    skin: 'layui-layer-molv layui-layer-wxd'
                });
                return false;
            }
            if (songurl.indexOf('profile/') > 0) {
                var songid = songurl.split('profile/')[1].split('?')[0];
            } else if (songurl.indexOf('item/') > 0) {
                var songid = songurl.split('item/')[1].split('?')[0];
            } else if (songurl.indexOf('vendor/') > 0) {
                var songid = songurl.split('vendor/')[1].split('?')[0];
            } else if (songurl.indexOf('?xhsshare') > 0) {
                var songid = songurl.split('?xhsshare')[1].split('?')[0];
            }
        } catch (e) {
            layer.alert('请输入正确的链接！', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        $('#inputvalue').val(songid);
    }
}

function getbilibiliid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    if (songurl.indexOf('.bilibili.com') < 0) {
        layer.alert('请输入正确的视频链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    try {
        var bilisz = /[^0-9.]/g;
        if (songurl.indexOf('video/') > 0) {
            var songid = songurl.split('video/')[1].split('?')[0].replace(bilisz, '');
        } else if (songurl.indexOf('read/') > 0) {
            var songid = songurl.split('read/')[1].split('?')[0].replace(bilisz, '');
        } else {
            var songid = songurl.split('com/')[1].split('?')[0].replace(bilisz, '');
        }
    } catch (e) {
        layer.alert('请输入正确的视频链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    $('#inputvalue').val(songid);
}

function getzuiyouid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    if (songurl.indexOf('.izuiyou.com') < 0) {
        layer.alert('请输入正确的帖子链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    try {
        var songid = songurl.split('detail/')[1].split('?')[0];
    } catch (e) {
        layer.alert('请输入正确的帖子链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    $('#inputvalue').val(songid);
}

function getmeipaiid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    if (songurl.indexOf('meipai.com') < 0) {
        layer.alert('请输入正确的视频链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    if (songurl.indexOf('/s/') > 0) {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=gethuoshan",
            data: {
                url: songurl
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#inputvalue').val(data.songid);
                } else {
                    layer.alert(data.msg, {
                        title: '小提示',
                        skin: 'layui-layer-molv layui-layer-wxd'
                    });
                    return false;
                }
            }
        });
    } else {
        try {
            var songid = songurl.split('media/')[1].split('?')[0];
        } catch (e) {
            layer.alert('请输入正确的视频链接！', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
    }
    $('#inputvalue').val(songid);
}

function getquanminid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    if (songurl.indexOf('hao222.com') < 0) {
        layer.alert('请输入正确的视频链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    try {
        var songid = songurl.split('vid=')[1].split('&')[0];
    } catch (e) {
        layer.alert('请输入正确的视频链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    $('#inputvalue').val(songid);
}

function getmeituid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    if (songurl.indexOf('meitu.com') < 0) {
        layer.alert('请输入正确的视频链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    try {
        var songid = songurl.split('feed_id=')[1].split('&')[0];
    } catch (e) {
        layer.alert('请输入正确的视频链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    $('#inputvalue').val(songid);
}

function getoasisid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    if (songurl.indexOf('weibo.cn') < 0 && songurl.indexOf('weibo.com') < 0) {
        layer.alert('请输入正确的视频链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    try {
        var songid = songurl.split('sid=')[1].split('&')[0];
    } catch (e) {
        layer.alert('请输入正确的视频链接！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    $('#inputvalue').val(songid);
}

function getsharelink() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    if (songurl.indexOf('http') < 0) {
        layer.alert('请输入正确的内容！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    try {
        if (songurl.indexOf('http://') >= 0) {
            var songid = 'http://' + songurl.split('http://')[1].split(' ')[0].split('，')[0];
        } else if (songurl.indexOf('https://') >= 0) {
            var songid = 'https://' + songurl.split('https://')[1].split(' ')[0].split('，')[0];
        }
        if (songid != $("#inputvalue").val()) layer.msg('链接转换成功！下单即可');
    } catch (e) {
        layer.alert('请输入正确的内容！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    $('#inputvalue').val(songid);
}

function getZpId() {
    var url = $("#inputvalue").val();
    if (url == "" && $("#inputvalue2").length > 0) {
        url = $("#inputvalue2").val();
    }
    if (url == '') {
        return layer.alert('请确保每项不能为空！');
    }
    if (url.indexOf('http') < 0) {
        return layer.alert('请输入包含链接的正确内容！');
    }
    try {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=getzpid",
            data: {
                url: url,
                hashsalt: hashsalt
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    if (typeof data.authorid == "string" && data.authorid) {
                        $('#inputvalue').val(data.authorid);
                    } else if (typeof data.songid == "string" && data.songid) {
                        $('#inputvalue').val(data.songid);
                    } else if (typeof data.userid == "string" && data.userid) {
                        $('#inputvalue').val(data.userid);
                    } else if (typeof data.videoid == "string" && data.videoid) {
                        $('#inputvalue').val(data.videoid);
                    } else {
                        return layer.alert('ID自动获取失败，请检查链接是否正确或联系客服处理！');
                    }
                    if (typeof data.videoid == "string" && $('#inputvalue2').length > 0) {
                        $('#inputvalue2').val(data.videoid);
                    }
                    layer.msg('ID自动获取成功，下单即可！');
                } else {
                    layer.alert(data.msg);
                    return false;
                }
            }
        });
    } catch (e) {
        layer.alert('请输入正确的内容！');
        return false;
    }
}

function getShareId() {
    var url = $("#inputvalue").val();
    if (url == '') {
        return layer.alert('请确保每项不能为空！');
    }
    if (url.indexOf('http') < 0) {
        return layer.alert('请输入包含链接的正确内容！');
    }
    try {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=getshareid",
            data: {
                url: url,
                hashsalt: hashsalt
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#inputvalue').val(data.authorid);
                    if (typeof data.videoid == "string" && $('#inputvalue2').length > 0) {
                        $('#inputvalue2').val(data.videoid);
                    }
                    layer.msg('ID自动获取成功，下单即可！');
                } else {
                    layer.alert(data.msg);
                    return false;
                }
            }
        });
    } catch (e) {
        layer.alert('请输入正确的内容！');
        return false;
    }
}

function getShareUrl() {
    var url = $("#inputvalue").val();
    if (url == '') {
        return layer.alert('请确保每项不能为空！');
    }
    if (url.indexOf('http') < 0) {
        return layer.alert('请输入正确的内容！');
    }
    try {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=getshareurl",
            data: {
                url: url,
                hashsalt: hashsalt
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#inputvalue').val(data.shareurl);
                    if (typeof (data.videoid) != "undefined" && $('#inputvalue2').length > 0) $('#inputvalue2').val(data.videoid);
                    layer.msg('链接转换成功！下单即可');
                } else {
                    layer.alert(data.msg);
                    return false;
                }
            }
        });
    } catch (e) {
        layer.alert('请输入正确的内容！');
        return false;
    }
}

function getshareid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    if (songurl.indexOf('http') < 0) {
        layer.alert('请输入正确的内容！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    getZpId();
}

function getshareid2(id, songurl) {
    if (songurl == '') {
        layer.alert('请确保每项不能为空！', {
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
        return false;
    }
    if (songurl.indexOf('http') < 0) {
        return false;
    }
    getZpId();
}

function orderStatus($zt, $is_curl) {
    if ($zt == 1 && $is_curl == 2) return '<font color=green>已处理</font>';
    else if ($zt == 1 && $is_curl == 4) {
        return '<font color=green>已发卡</font>';
    } else if ($zt == 1) {
        return '<font color=green>已完成</font>';
    } else if ($zt == 2) {
        return '<font color=orange>正在处理</font>';
    } else if ($zt == 3) {
        return '<font color=red>异常中</font>';
    } else if ($zt == 4) {
        return '<font color=grey>已退款</font>';
    } else if ($zt == 10) {
        return '<font color=#8E9013>待退款</font>';
    } else {
        if ($is_curl == 4) {
            return '<font color=blue>待发卡</font>';
        } else {
            return '<font color=blue>待处理</font>';
        }
    }
}

function queryOrder(type, content, page) {
    $('#submit_query').val('Loading');
    $('#result2').hide();
    $('#list').html('');
    $.ajax({
        type: "POST",
        url: serverPath + "ajax.php?act=query",
        data: {
            type: type,
            qq: content,
            page: page
        },
        dataType: 'json',
        success: function (data) {
            if (data.code == 0) {
                var status, orderid_1, x = 0;
                $('#list').append('<tr><td colspan="6"><font color="red">温馨提示：订单超过24小时仍待处理请联系客服哦~</font></td></tr>');
                $.each(data.data, function (i, item) {
                    if (x == 0) orderid_1 = item;
                    if (!item.is_curl) item.is_curl = 0;
                    status = orderStatus(item.status, item.is_curl);
                    var str = '<tr order_id=' + item.id + '><td><a onclick="showOrder(\'' + item.id + '\',\'' + item.skey + '\')" title="查看订单详细" class="btn btn-info btn-xs">详细</a></td><td>' + item.input + '</td><td>' + item.name + '</td><td class="hidden-xs">' + item.value + '</td><td class="hidden-xs">' + item.addtime + '</td><td>' + status + '</td><td>';
                    if (Number(item.status) != 1 && Number(item.status) != 4 && Number(item.status) != 10 && Number(item.status) != 3) {
                        str += '&nbsp;<a onclick="cuidan(' + item.id + ',' + item.status + ')" title="催单" class="btn btn-warning  warning btn-xs" style="margin:3px">催单</a>';
                    }
                    if (Number(item.status) == 3) {
                        str += '&nbsp;<a onclick="inputOrder(\'' + item.id + '\')" title="补单" class="btn btn-primary btn-xs">补单</a>';
                    }
                    str += "</td></tr>";
                    $('#list').append(str);
                    if (item.result != null) {
                        if (item.status == 3) {
                            $('#list').append('<tr><td colspan=6><font color="red">异常原因：' + item.result + '</font></td></tr>');
                        }
                    }
                    x++;
                });
                var addstr = '';
                if (data.islast == true) addstr += '<button class="btn btn-primary btn-xs pull-left" onclick="queryOrder(\'' + data.type + '\',\'' + data.content + '\',' + (data.page - 1) + ')">上一页</button>';
                if (data.isnext == true) addstr += '<button class="btn btn-primary btn-xs pull-right" onclick="queryOrder(\'' + data.type + '\',\'' + data.content + '\',' + (data.page + 1) + ')">下一页</button>';
                $('#list').append('<tr><td colspan=6>' + addstr + '</td></tr>');
                $("#result2").slideDown();
                if ($_GET['buyok'] && orderid_1.id) {
                    showOrder(orderid_1.id, orderid_1.skey);
                } else if (orderid != null && data.data['order_' + orderid] && (is_showWork == true || is_orderWork == true)) {
                    showOrder(orderid, data.data['order_' + orderid].skey);
                } else {
                    if (x == 0) {
                        layer.alert('未查询到相关订单记录！<br>您可以点击查单处右侧的感叹号按钮获取查询帮助<br><i class="fa fa-exclamation-circle" style="color:red"></i> <span style="color:blue">建议您登陆本站后再查询或联系客服查询</span>');
                    }
                }
            } else {
                layer.alert(data.msg, {
                    title: '小提示',
                    skin: 'layui-layer-molv layui-layer-wxd'
                });
            }
            $('#submit_query').val('立即查询');
        }
    });
}

function showOrder(id, skey) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    var status = ['<span class="text-info"><i class="fas fa-circle"></i> 待处理</span>', '<span class="text-success"><i class="fas fa-circle"></i> 已完成</span>', '<span class="text-warning"><i class="fas fa-circle"></i> 处理中</span>', '<span class="text-danger"><i class="fas fa-circle"></i> 异常</span>', '<span class="text-secondary"><i class="fas fa-circle"></i> 已退款</span>'];
    $.ajax({
        type: "POST",
        url: serverPath + "ajax.php?act=order",
        data: {
            id: id,
            skey: skey
        },
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                var item = '<table class="table table-condensed table-hover table-sm wxd-table-now">';
                item += '<tr><td colspan="6" style="text-align:center"><b>订单基本信息</b></td></tr><tr><td class="bg-info text-white orderTitle">编号</td><td colspan="5" class="orderContent">' + id + '</td></tr><tr><td class="bg-info text-white orderTitle">名称</td><td colspan="5"><span class="orderContent">' + data.name + '</span></td></tr><tr><td class="bg-info text-white orderTitle">金额</td><td colspan="5" class="orderContent">' + data.money + '元</td></tr><tr><td class="bg-info text-white orderTitle">时间</td><td colspan="5">' + data.date + '</td></tr><tr><td class="bg-info text-white orderTitle">信息</td><td colspan="5" class="orderContent">' + data.inputs + '</td><tr><td class="bg-info text-white orderTitle">状态</td><td colspan="5" class="orderContent">' + status[data.status] + '</td></tr>';
                if (data.complain) {
                    item += '<tr><td class="bg-info text-white">操作</td><td><a href="' + serverPath + 'user/workorder.php?my=add&orderid=' + id + '&skey=' + skey + '" target="_blank" onclick="return checklogin(' + data.islogin + ')" class="btn btn-xs btn-dark">投诉订单</a></td></tr>';
                }
                if (typeof data.kminfo == "string") {
                    item += '<tr><td colspan="6" style="text-align:center"><b>以下是你的卡密信息</b></td><tr><td colspan="6">' + data.kminfo + '</td></tr>';
                } else {
                    if (data.list && data.list.order_state) {
                        item += '<tr><td colspan="6" style="text-align:center"><b>订单实时状态(软件提供仅供参考)</b></td><tr><td class="warning">下单数量</td><td>' + data.list.num + '</td><td class="warning">下单时间</td><td colspan="3">' + data.list.add_time + '</td></tr><tr><td class="warning">初始数量</td><td>' + data.list.start_num + '</td><td class="warning">当前数量</td><td>' + data.list.now_num + '</td><td class="warning">订单状态</td><td><font color=blue>' + data.list.order_state + '</font></td></tr>';
                    } else if (typeof (data.expressInfo) == "object" && typeof (data.expressInfo.msg) == "string") {
                        var expressData = data.expressInfo;
                        if (expressData.code == 0) {
                            item += '<tr><td colspan="6" style="text-align:center"><b>快递/物流进度信息[仅供参考]</b></td>';
                            item += '<tr><td colspan="3" class="warning">处理耗时</td><td >' + expressData.data.takeTime + '</td></tr>';
                            item += '<tr><td colspan="3" class="warning">快递类型</td><td ><img width="25px" src="' + expressData.data.logo + '"/>&nbsp;' + expressData.data.expName + '</td></tr>';
                            if (!!expressData.data.courier) item += '<tr><td colspan="3" class="warning">快递员姓名</td><td>&nbsp;' + expressData.data.courier + '</td></tr>';
                            if (!!expressData.data.courierPhone) item += '<tr><td colspan="3" class="warning">快递员电话</td><td>&nbsp;' + expressData.data.courierPhone + '</td></tr>';
                            item += '<tr><td colspan="3" class="warning">签收状态</td><td>' + expressData.data.status + '</td></tr>';
                            $.each(expressData.data.list, function (i, res) {
                                item += '<tr><td colspan="2" class="warning">' + res.time + '</td><td colspan="4">' + res.status + '</td></tr>';
                            });
                        } else {
                            item += '<tr><td colspan="6" style="text-align:center"><b>订单物流信息</b></td><tr><td class="warning">查询状态</td><td>' + expressData.msg + '</td>';
                            item += '</tr>';
                        }
                    }
                    if (data.result) {
                        item += '<tr><td colspan="6" style="text-align:center"><b>处理结果</b></td><tr><td colspan="6">' + data.result + '</td></tr>';
                    }
                }
                if (!!data.show_desc && typeof (data.desc) == "string" && data.desc != "") {
                    item += '<tr><td colspan="6" style="text-align:center"><b>商品简介</b></td><tr><td colspan="6" style="white-space: normal;">' + data.desc + '</td></tr>';
                }
                item += '</table>';
                var area = [$(window).width() > 640 ? '540px' : '97%', 'auto'];
                layer.open({
                    type: 1,
                    area: area,
                    title: '订单详细信息',
                    skin: 'layui-layer-rim',
                    content: item,
                    btn: '关闭窗口',
                    success: function (layero, index) {
                        //重定义高度
                        let lh = $(layero).height();
                        let wh = $(window).height();
                        if (lh > wh) {
                            console.log('已重定义弹窗样式');
                            layer.style(index, {
                                top: '12px',
                                bottom: '8px',
                                height: (wh - 20) + 'px'
                            });
                        }
                    }
                });
            } else {
                layer.alert(data.msg, {
                    title: '小提示',
                    skin: 'layui-layer-molv layui-layer-wxd'
                });
            }
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
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=pay",
            data: {
                tid: $("#tid").val(),
                inputvalue: $("#inputvalue").val(),
                inputvalue2: $("#inputvalue2").val(),
                inputvalue3: $("#inputvalue3").val(),
                inputvalue4: $("#inputvalue4").val(),
                inputvalue5: $("#inputvalue5").val(),
                num: $("#num").val(),
                hashsalt: hashsalt,
                geetest_challenge: result.geetest_challenge,
                geetest_validate: result.geetest_validate,
                geetest_seccode: result.geetest_seccode
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code >= 0) {
                    $('#alert_frame').hide();
                    alert('领取成功！');
                    window.location.href = '?buyok=1';
                } else {
                    layer.alert(data.msg, {
                        title: '小提示',
                        skin: 'layui-layer-molv layui-layer-wxd'
                    });
                    captchaObj.reset();
                }
            }
        });
    });
};

function toTool(cid, tid) {
    history.replaceState({}, null, './?cid=' + cid + '&tid=' + tid);
    $("#recommend").modal('hide');
    $_GET['tid'] = tid;
    $_GET["cid"] = cid;
    $("#cid").val(cid);
    $("#cid").change();
    $("#goodType").hide('normal');
    $("#goodTypeContent").show('normal');
}

function dopay(type, orderid) {
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
        success: function (data) {
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
                    success: function (data) {
                        layer.close(ii);
                        if (data.code == 1) {
                            alert(data.msg, {
                                title: '小提示',
                                skin: 'layui-layer-molv layui-layer-wxd'
                            });
                            window.location.href = '?buyok=1';
                        } else if (data.code == -2) {
                            alert(data.msg, {
                                title: '小提示',
                                skin: 'layui-layer-molv layui-layer-wxd'
                            });
                            window.location.href = '?buyok=1';
                        } else if (data.code == -3) {
                            var confirmobj = layer.confirm('你的余额不足，请充值！', {
                                btn: ['立即充值', '取消'],
                                title: '小提示',
                                skin: 'layui-layer-molv layui-layer-wxd'
                            }, function () {
                                window.location.href = serverPath + 'user/index.php#chongzhi';
                            }, function () {
                                layer.close(confirmobj);
                            });
                        } else if (data.code == -4) {
                            var confirmobj = layer.confirm('你还未登录，是否现在登录？', {
                                btn: ['登录', '注册', '取消'],
                                title: '小提示',
                                skin: 'layui-layer-molv layui-layer-wxd'
                            }, function () {
                                window.location.href = serverPath + 'user/login.php';
                            }, function () {
                                window.location.href = serverPath + 'user/reg.php';
                            }, function () {
                                layer.close(confirmobj);
                            });
                        } else {
                            layer.alert(data.msg, {
                                title: '小提示',
                                skin: 'layui-layer-molv layui-layer-wxd'
                            });
                        }
                    }
                });
            } else {
                window.location.href = serverPath + 'other/submit.php?type=' + type + '&orderid=' + orderid;
            }
        },
        error: function () {
            layer.close(ii);
            layer.alert('服务器错误，请稍后重试或联系网站客服！', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
        }
    });
}

function cancel(orderid) {
    layer.closeAll();
    $.ajax({
        type: "POST",
        url: serverPath + "ajax.php?act=cancel",
        data: {
            orderid: orderid,
            hashsalt: hashsalt
        },
        dataType: 'json',
        async: true,
        success: function (data) {
            if (data.code == 0) {} else {
                layer.msg(data.msg);
                window.location.reload();
            }
        },
        error: function (data) {
            window.location.reload();
        }
    });
}

function getShareID() {
    var url = $("#inputvalue").val();
    if (url == '') {
        return layer.alert('请确保每项不能为空！');
    }
    if (url.indexOf('http') < 0) {
        return layer.alert('请输入正确的内容！');
    }
    try {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=getshareid",
            data: {
                url: url,
                hashsalt: hashsalt
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#inputvalue').val(data.authorid);
                    if (typeof (data.videoid) != "undefined" && $('#inputvalue2').length > 0) $('#inputvalue2').val(data.videoid);
                    layer.msg('ID获取成功！下单即可');
                } else {
                    layer.alert(data.msg);
                    return false;
                }
            }
        });
    } catch (e) {
        layer.alert('请输入正确的内容！');
        return false;
    }
}

function getShareUrl() {
    var url = $("#inputvalue").val();
    if (url == '') {
        return layer.alert('请确保每项不能为空！');
    }
    if (url.indexOf('http') < 0) {
        return layer.alert('请输入正确的内容！');
    }
    try {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=getshareurl",
            data: {
                url: url,
                hashsalt: hashsalt
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#inputvalue').val(data.shareurl);
                    if (typeof (data.videoid) != "undefined" && $('#inputvalue2').length > 0) $('#inputvalue2').val(data.videoid);
                    layer.msg('链接转换成功！下单即可');
                } else {
                    layer.alert(data.msg);
                    return false;
                }
            }
        });
    } catch (e) {
        layer.alert('请输入正确的内容！');
        return false;
    }
}

function inputFilter(inputvalue) {
    var urlReg = new RegExp("(http|https):\\/\\/[\\w\\.\\/\\-\\$\\!\\?\\(\\)_&=#%+:;]+");
    if (urlReg.test(inputvalue)) {
        var match = urlReg.exec(inputvalue);
        if (match != null) {
            $("#inputvalue").val(match[0]);
        }
    } else {
        console.log("链接匹配失败：" + inputvalue);
    }
    return true;
}

function checkInput() {
    if ($("#inputname").length < 1) {
        return false;
    }
    if ($("#inputvalue").length < 1) {
        return false;
    }
    var title = $("#inputname").html(),
        title2 = null;
    if ($("#inputname2").length > 1 && $("#inputname2").html() != "") {
        title2 = $("#inputname2").html();
    }
    var value = $("#inputvalue").val();
    var value2 = '';
    if ($("#inputvalue2").length > 0) {
        value2 = $("#inputvalue2").val();
    }
    var gettype = $("#inputvalue").attr('gettype');
    if (typeof (gettype) == 'undefined' || gettype == '' || gettype != '!shareurl') {
        inputFilter(value);
    }
    if (typeof gettype == 'string' && gettype != "") {
        if (gettype == 'shareid' && value.indexOf('http') >= 0) {
            getShareId();
        } else if (gettype == 'zpid' && value.indexOf('http') >= 0) {
            getZpId();
        } else if (gettype == 'shareurl') {
            getShareUrl();
        }
    } else if ($("#inputvalue2").length > 0) {
        var gettype = $("#inputvalue2").attr('gettype');
        if (typeof gettype == 'string' && gettype != "") {
            if (gettype == 'zpid' && value2.indexOf('http') >= 0) {
                getZpId();
            }
        }
    }
}

function checklogin(islogin) {
    if (islogin == 1) {
        return true;
    } else {
        var confirmobj = layer.confirm('为方便反馈处理结果，投诉订单前请先登录网站！', {
            btn: ['登录', '注册', '取消'],
            title: '小提示',
            skin: 'layui-layer-molv layui-layer-wxd'
        }, function () {
            window.location.href = serverPath + 'user/login.php';
        }, function () {
            window.location.href = serverPath + 'user/reg.php';
        }, function () {
            layer.close(confirmobj);
        });
        return false;
    }
}

function openCart() {
    window.location.href = './?mod=cart';
}
var audio_init = {
    changeClass: function (target, id) {
        var className = $(target).attr('class');
        var ids = document.getElementById(id);
        if (ids) {
            console.log('网站音乐' + ((className.indexOf('on') > -1) ? '已暂停' : '已播放'));
            (className.indexOf('on') > -1) ? $(target).removeClass('on').addClass('off') : $(target).removeClass('off').addClass('on');
            (className.indexOf('on') > -1) ? ids.pause() : ids.play();
        } else {
            console.log('网站音乐暂停失败, 节点未找到');
        }
    },
    play: function () {
        document.getElementById('media').play();
    }
}
$(document).ready(function () {
    $(document).on('click', '.goodTypeChange', function () {
        var id = $(this).data('id');
        var img = $(this).data('img');
        history.replaceState({}, null, './?cid=' + id);
        $("#searchkw").val('');
        $("#cid").val(id);
        $("#cid").change();
        $("#goodType").hide('normal');
        $("#goodTypeContent").show('normal');
    });
    $(document).on('click', '.nav-tabs,.backType', function () {
        history.replaceState({}, null, './');
        $("#searchkw").val('');
        $("#goodType").show('normal');
        $("#goodTypeContent").hide('normal');
    })
    $(document).on('click', '#showSearchBar', function () {
        $("#display_selectclass").slideToggle();
        $("#display_searchBar").slideToggle();
    });
    $(document).on('click', '#closeSearchBar', function () {
        $("#display_searchBar").slideToggle();
        $("#display_selectclass").slideToggle();
    });
    $(document).on('click', '#doSearch', function () {
        var kw = $("#searchkw").val();
        var search = $("#cid").attr('type') == 'hidden' ? true : false;
        if (kw == '') {
            layer.alert("搜索内容不能为空！", {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return;
        }
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        if (search == true) {
            $("#goodType").hide('normal');
            $("#goodTypeContent").show('normal');
        };
        $("#tid").empty();
        $("#tid").append('<option value="0">请选择商品</option>');
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=gettool",
            data: {
                kw: kw
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    var num = 0;
                    $.each(data.data, function (i, res) {
                        $("#tid").append('<option stock_id="' + res.stock_id + '" value="' + res.tid + '" cid="' + res.cid + '"  price="' + res.price + '" unit="' + res.unit + '"shopimg="' + res.shopimg + '" desc="' + encodeURI(res.desc) + '" alert="' + encodeURI(res.alert) + '" close_alert="' + encodeURI(res.close_alert) + '"  inputname="' + res.input + '" inputsname="' + res.inputs + '" multi="' + res.multi + '" isfaka="' + res.isfaka + '" count="' + res.value + '" active="' + res.active + '" prices="' + res.prices + '" max="' + res.max + '" min="' + res.min + '" name="' + res.name + '">' + res.name + '</option>');
                        num++;
                    });
                    $("#tid").val(0);
                    getPoint();
                    if (num == 0 && cid != 0) $("#tid").html('<option value="0">没有搜索到相关商品</option>');
                    else layer.msg('成功搜索到' + num + '个商品', {
                        icon: 1,
                        time: 1000
                    });
                } else {
                    layer.alert(data.msg, {
                        title: '小提示',
                        skin: 'layui-layer-molv layui-layer-wxd'
                    });
                }
            },
            error: function (data) {
                layer.msg('服务器错误');
                return false;
            }
        });
    });
    $(document).on('change', '#cid', function () {
        var cid = $(this).val();
        if (cid > 0) history.replaceState({}, null, './?cid=' + cid);
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $("#tid").empty();
        $("#tid").append('<option value="0">请选择商品</option>');
        $.ajax({
            type: "GET",
            url: serverPath + "ajax.php?act=gettool&cid=" + cid + "&info=1",
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    if (data.class) {
                        $("#sub_cid").empty();
                        $("#sub_cid").append('<option value="0">请选择二级分类</option>');
                        var num = 0;
                        $.each(data.data, function (i, res) {
                            $("#sub_cid").append('<option value="' + res.cid + '">' + res.name + '</option>');
                            num++;
                        });
                        if ($_GET["sub_cid"]) {
                            var sub_cid = parseInt($_GET["sub_cid"]);
                            if (sub_cid && sub_cid > 0) {
                                $("#sub_cid").val(sub_cid);
                            }
                            $("#sub_cid").change();
                        } else {
                            $("#sub_cid").val(0);
                            layer.msg('请选择二级分类哦', {
                                icon: 1,
                                time: 1500
                            });
                        }
                        $("#display_selectclass_sub").show();
                        $("#tid").empty();
                        $("#display_tool").hide();
                    } else {
                        if (typeof (data.upcid) != 'undefined' && data.upcid > 0) {
                            var sub_cid = cid;
                            var tid = parseInt($_GET["tid"]);
                            setHistory('cid=' + cid + '&sub_cid=' + sub_cid + '&tid=' + tid);
                            $("#cid").val(data.upcid);
                            $("#cid").change();
                            return false;
                        } else {
                            setHistory('cid=' + cid);
                        }
                        $("#display_selectclass_sub").hide();
                        $("#sub_cid").empty();
                        $("#sub_cid").append('<option value="0">请选择二级分类</option>');
                        $("#sub_cid").val(0);
                        $("#tid").empty();
                        $("#tid").append('<option value="0">请选择商品</option>');
                        if (data.info != null) {
                            $("#className").html(data.info.name);
                            $("#classImg").attr('src', data.info.shopimg);
                        }
                        var num = 0;
                        var tid = parseInt($_GET["tid"]);
                        var is_tid = false;
                        $.each(data.data, function (i, res) {
                            if (tid && res.tid == tid) is_tid = true;
                            if (typeof (res.stock_id) != 'string' && typeof (res.stock_id) != 'number') {
                                res.stock_id = '0';
                            }
                            $("#tid").append('<option stock_id="' + res.stock_id + '" value="' + res.tid + '" shopimg="' + res.shopimg + '" cid="' + res.cid + '"  price="' + res.price + '" unit="' + res.unit + '"desc="' + encodeURI(res.desc) + '" alert="' + encodeURI(res.alert) + '" close_alert="' + encodeURI(res.close_alert) + '"  inputname="' + res.input + '" inputsname="' + res.inputs + '" multi="' + res.multi + '" isfaka="' + res.isfaka + '" count="' + res.value + '" active="' + res.active + '" prices="' + res.prices + '" max="' + res.max + '" min="' + res.min + '" name="' + res.name + '">' + res.name + '</option>');
                            num++;
                        });
                        if (tid && is_tid) {
                            $("#tid").val(tid);
                            if ($("#display_tool").length > 0) $("#display_tool").show();
                        } else {
                            $("#tid").val(0);
                        }
                        getPoint(1);
                        if (num == 0 && cid != 0) $("#tid").html('<option value="0">该分类下没有商品</option>');
                    }
                } else {
                    layer.alert(data.msg, {
                        title: '小提示',
                        skin: 'layui-layer-molv layui-layer-wxd'
                    });
                }
            },
            error: function (data) {
                layer.msg('服务器错误');
                return false;
            }
        });
    });
    $(document).on('change', '#sub_cid', function () {
        var sub_cid = $(this).val();
        if (sub_cid < 1) {
            $("#tid").empty();
            $("#display_tool").hide();
            getPoint();
            return false;
        }
        $("#tid").empty();
        $("#tid").append('<option value="0">请选择商品</option>');
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "GET",
            url: serverPath + "ajax.php?act=gettool&cid=" + sub_cid + "&info=1",
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                $("#tid").empty();
                $("#tid").append('<option value="0">请选择商品</option>');
                if (data.code == 0) {
                    if (data.info != null) {
                        $("#className").html(data.info.name);
                        $("#classImg").attr('src', data.info.shopimg);
                    }
                    var num = 0;
                    var tid = parseInt($_GET["tid"]);
                    var is_tid = false;
                    $.each(data.data, function (i, res) {
                        if (tid && res.tid == tid) is_tid = true;
                        if (typeof (res.stock_id) != 'string' && typeof (res.stock_id) != 'number') {
                            res.stock_id = '0';
                        }
                        $("#tid").append('<option stock_id="' + res.stock_id + '" value="' + res.tid + '" cid="' + res.cid + '" shopimg="' + res.shopimg + '"  price="' + res.price + '" unit="' + res.unit + '"desc="' + encodeURI(res.desc) + '" alert="' + encodeURI(res.alert) + '" close_alert="' + encodeURI(res.close_alert) + '"  inputname="' + res.input + '" inputsname="' + res.inputs + '" multi="' + res.multi + '" isfaka="' + res.isfaka + '" count="' + res.value + '" active="' + res.active + '" prices="' + res.prices + '" max="' + res.max + '" min="' + res.min + '" name="' + res.name + '">' + res.name + '</option>');
                        num++;
                    });
                    if (tid && is_tid) {
                        $("#tid").val(tid);
                    } else {
                        $("#tid").val(0);
                    }
                    $("#display_tool").show();
                    if (num < 1) {
                        $("#display_tool").hide();
                        layer.alert('该分类下没有商品,请重新选择', {
                            title: '小提示',
                            skin: 'layui-layer-molv layui-layer-wxd'
                        });
                        $("#tid").html('<option value="0">该分类下没有商品</option>');
                    } else {
                        //layer.msg("共"+num+'个商品 请选择商品哦',{icon:1,time:1500});
                        layer.msg('请选择商品哦', {
                            icon: 1,
                            time: 1500
                        });
                    }
                    getPoint();
                } else {
                    layer.alert(data.msg, {
                        title: '小提示',
                        skin: 'layui-layer-molv layui-layer-wxd'
                    });
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.alert("服务器错误，请稍后重试或联系网站客服！", {
                    title: '小提示',
                    skin: 'layui-layer-molv layui-layer-wxd'
                });
                return false;
            }
        });
    });
    $(document).on('click', '#submit_buy', function () {
        var tid = $("#tid").val();
        if (tid == 0) {
            layer.alert('请选择商品！', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        var inputvalue = $("#inputvalue").val();
        if (inputvalue == '' || tid == '') {
            layer.alert('请确保每项不能为空！', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        if ($("#inputvalue2").val() == '' || $("#inputvalue3").val() == '' || $("#inputvalue4").val() == '' || $("#inputvalue5").val() == '') {
            layer.alert('请确保每项不能为空！', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        if (($('#inputname').html() == '下单ＱＱ' || $('#inputname').html() == 'ＱＱ账号' || $("#inputname").html() == 'QQ账号') && (inputvalue.length < 5 || inputvalue.length > 11 || isNaN(inputvalue))) {
            layer.alert('请输入正确的QQ号！', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        var reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/;
        if ($('#inputname').html() == '你的邮箱' && !reg.test(inputvalue)) {
            layer.alert('邮箱格式不正确！', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        reg = /^[1][0-9]{10}$/;
        if ($('#inputname').html() == '手机号码' && !reg.test(inputvalue)) {
            layer.alert('手机号码格式不正确！', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        if ($("#inputname2").html() == '说说ID' || $("#inputname2").html() == '说说ＩＤ') {
            if ($("#inputvalue2").val().length != 24) {
                layer.alert('说说必须是原创说说！', {
                    title: '小提示',
                    skin: 'layui-layer-molv layui-layer-wxd'
                });
                return false;
            }
        }
        //checkInput();
        if ($("#inputname").html() == '抖音作品ID' || $("#inputname").html() == '火山作品ID' || $("#inputname").html() == '火山直播ID') {
            if ($("#inputvalue").val().length != 19) {
                layer.alert('您输入的作品ID有误！', {
                    title: '小提示',
                    skin: 'layui-layer-molv layui-layer-wxd'
                });
                return false;
            }
        }
        if ($("#inputname2").html() == '抖音评论ID') {
            if ($("#inputvalue2").val().length != 19) {
                layer.alert('您输入的评论ID有误！请点击自动获取手动选择评论！', {
                    title: '小提示',
                    skin: 'layui-layer-molv layui-layer-wxd'
                });
                return false;
            }
        }
        $('#pay_frame').hide();
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=pay",
            data: {
                tid: tid,
                inputvalue: $("#inputvalue").val(),
                inputvalue2: $("#inputvalue2").val(),
                inputvalue3: $("#inputvalue3").val(),
                inputvalue4: $("#inputvalue4").val(),
                inputvalue5: $("#inputvalue5").val(),
                stock_id: $("#tid option:selected").attr('stock_id'),
                num: $("#num").val(),
                hashsalt: hashsalt
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    if ($('#inputname').html() == '你的邮箱') {
                        $.cookie('email', inputvalue);
                    }
                    var paymsg = '<center><h2 style="color:red;">￥ ' + data.need + '</h2>';
                    if (data.pay_alipay > 0) {
                        paymsg += '<button class="btn btn-primary btn-block" onclick="dopay(\'alipay\',\'' + data.trade_no + '\')" style="margin-top:10px;"><i class="fab fa-alipay fa-fw"></i>&nbsp;支付宝</button>';
                    }
                    if (data.pay_qqpay > 0) {
                        paymsg += '<button class="btn btn-info btn-block" onclick="dopay(\'qqpay\',\'' + data.trade_no + '\')" style="margin-top:10px;"><i class="fab fa-qq fa-fw"></i>&nbsp;ＱＱ钱包</button>';
                    }
                    if (data.pay_wxpay > 0) {
                        paymsg += '<button class="btn btn-success btn-block" onclick="dopay(\'wxpay\',\'' + data.trade_no + '\')" style="margin-top:10px;"><i class="fab fa-weixin fa-fw"></i>&nbsp;微信支付</button>';
                    }
                    if (data.pay_rmb > 0) {
                        paymsg += '<button class="btn btn-dark btn-block" onclick="dopay(\'rmb\',\'' + data.trade_no + '\')">余额支付（剩' + data.user_rmb + '元）</button>';
                    }
                    if (typeof (data.pay_alert) === "string" && data.pay_alert != "" && data.pay_alert != "null") {
                        paymsg += '<p>' + data.pay_alert + '</p>';
                    }
                    layer.alert(paymsg + '<hr><a class="btn btn-light btn-block" onclick="cancel(\'' + data.trade_no + '\')">取消订单</a></center>', {
                        btn: [],
                        title: '提交订单成功',
                        skin: 'layui-layer-molv layui-layer-wxd',
                        closeBtn: false
                    });
                } else if (data.code == 1) {
                    $('#alert_frame').hide();
                    if ($('#inputname').html() == '你的邮箱') {
                        $.cookie('email', inputvalue);
                    }
                    alert('领取成功！');
                    window.location.href = '?buyok=1';
                } else if (data.code == 2) {
                    layer.open({
                        type: 1,
                        title: '完成验证',
                        skin: 'layui-layer-rim',
                        area: ['320px', '100px'],
                        content: '<div id="captcha"><div id="captcha_text">正在加载验证码</div><div id="captcha_wait"><div class="captchaloading"><div class="loading-dot"></div><div class="loading-dot"></div><div class="loading-dot"></div><div class="loading-dot"></div></div></div></div>',
                        success: function () {
                            $.getScript("//static.geetest.com/static/tools/gt.js", function () {
                                $.ajax({
                                    url: serverPath + "ajax.php?act=captcha&t=" + (new Date()).getTime(),
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
                            });
                        }
                    });
                } else if (data.code == 3) {
                    layer.alert(data.msg, {
                        title: '小提示',
                        skin: 'layui-layer-molv layui-layer-wxd',
                        closeBtn: false
                    }, function () {
                        window.location.reload();
                    });
                } else if (data.code == 4) {
                    var confirmobj = layer.confirm('请登录后再购买，是否现在登录？', {
                        btn: ['登录', '注册', '取消'],
                        skin: 'layui-layer-molv layui-layer-wxd'
                    }, function () {
                        window.location.href = serverPath + 'user/login.php';
                    }, function () {
                        window.location.href = serverPath + 'user/reg.php';
                    }, function () {
                        layer.close(confirmobj);
                    });
                } else {
                    layer.alert(data.msg, {
                        title: '小提示',
                        skin: 'layui-layer-molv layui-layer-wxd'
                    });
                }
            }
        });
    });
    $(document).on('click', '#submit_cart_shop', function () {
        var tid = $("#tid").val();
        if (tid == 0) {
            layer.alert('请选择商品！', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        var inputvalue = $("#inputvalue").val();
        if (inputvalue == '' || tid == '') {
            layer.alert('请确保每项不能为空！', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        if ($("#inputvalue2").val() == '' || $("#inputvalue3").val() == '' || $("#inputvalue4").val() == '' || $("#inputvalue5").val() == '') {
            layer.alert('请确保每项不能为空！', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        if (($('#inputname').html() == '下单ＱＱ' || $('#inputname').html() == 'ＱＱ账号' || $("#inputname").html() == 'QQ账号') && (inputvalue.length < 5 || inputvalue.length > 11 || isNaN(inputvalue))) {
            layer.alert('请输入正确的QQ号！', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        var reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/;
        if ($('#inputname').html() == '你的邮箱' && !reg.test(inputvalue)) {
            layer.alert('邮箱格式不正确！', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        reg = /^[1][0-9]{10}$/;
        if ($('#inputname').html() == '手机号码' && !reg.test(inputvalue)) {
            layer.alert('手机号码格式不正确！', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        if ($("#inputname2").html() == '说说ID' || $("#inputname2").html() == '说说ＩＤ') {
            if ($("#inputvalue2").val().length != 24) {
                layer.alert('说说必须是原创说说！', {
                    title: '小提示',
                    skin: 'layui-layer-molv layui-layer-wxd'
                });
                return false;
            }
        }
        checkInput();
        if ($("#inputname").html() == '抖音作品ID' || $("#inputname").html() == '火山作品ID' || $("#inputname").html() == '火山直播ID') {
            if ($("#inputvalue").val().length != 19) {
                layer.alert('您输入的作品ID有误！', {
                    title: '小提示',
                    skin: 'layui-layer-molv layui-layer-wxd'
                });
                return false;
            }
        }
        if ($("#inputname2").html() == '抖音评论ID') {
            if ($("#inputvalue2").val().length != 19) {
                layer.alert('您输入的评论ID有误！请点击自动获取手动选择评论！', {
                    title: '小提示',
                    skin: 'layui-layer-molv layui-layer-wxd'
                });
                return false;
            }
        }
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=pay&method=cart_add",
            data: {
                tid: tid,
                inputvalue: $("#inputvalue").val(),
                inputvalue2: $("#inputvalue2").val(),
                inputvalue3: $("#inputvalue3").val(),
                inputvalue4: $("#inputvalue4").val(),
                inputvalue5: $("#inputvalue5").val(),
                num: $("#num").val(),
                hashsalt: hashsalt
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    if ($('#inputname').html() == '你的邮箱') {
                        $.cookie('email', inputvalue);
                    }
                    $('#cart_count').html(data.cart_count);
                    $('#alert_cart').slideDown();
                    layer.msg('添加至购物车成功~点击下方进入购物车列表结算');
                } else if (data.code == 3) {
                    layer.alert(data.msg, {
                        title: '小提示',
                        skin: 'layui-layer-molv layui-layer-wxd',
                        closeBtn: false
                    }, function () {
                        window.location.reload();
                    });
                } else if (data.code == 4) {
                    var confirmobj = layer.confirm('请登录后再购买，是否现在登录？', {
                        btn: ['登录', '注册', '取消'],
                        skin: 'layui-layer-molv layui-layer-wxd'
                    }, function () {
                        window.location.href = serverPath + 'user/login.php';
                    }, function () {
                        window.location.href = serverPath + 'user/reg.php';
                    }, function () {
                        layer.close(confirmobj);
                    });
                } else {
                    layer.alert(data.msg);
                }
            }
        });
    });
    $(document).on('click', '#submit_query', function () {
        if ($("input[name=qq]").length > 0) {
            $("input[name=qq]").attr("id", "qq3");
        }
        var qq = $("#qq3").val(),
            type = $("input[name=queryType]:checked").val();
        queryOrder(type, qq, 1);
    });
    $(document).on('click', '#num_add', function () {
        var i = parseInt($("#num").val());
        if ($("#need").val() == '') {
            layer.alert('请先选择商品', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        var multi = $('#tid option:selected').attr('multi');
        var count = parseInt($('#tid option:selected').attr('count'));
        var unit = $('#tid option:selected').attr('unit');
        if (undefined == unit || unit == '') {
            unit = '个';
        }
        if (multi == '0') {
            layer.alert('该商品不支持选择数量', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        i++;
        $("#num").val(i);
        var price = parseFloat($('#tid option:selected').attr('price'));
        var prices = $('#tid option:selected').attr('prices');
        if (typeof (prices) != 'undefined' && prices != '' && prices != 'null') {
            price = price - getFinalPrice(price, prices, i);
        }
        price = price * i;
        count = count * i;
        if (count > 1) $('#need').val('￥' + price.toFixed(2) + "元 ➠ " + count + unit);
        else $('#need').val('￥' + price.toFixed(2) + "元");
    });
    $(document).on('click', '#num_min', function () {
        var i = parseInt($("#num").val());
        if (i <= 1) {
            layer.msg('最低下单一份哦！', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        if ($("#need").val() == '') {
            layer.alert('请先选择商品', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        var multi = $('#tid option:selected').attr('multi');
        var count = parseInt($('#tid option:selected').attr('count'));
        var unit = $('#tid option:selected').attr('unit');
        if (undefined == unit || unit == '') {
            unit = '个';
        }
        if (multi == '0') {
            layer.alert('该商品不支持选择数量', {
                title: '小提示',
                skin: 'layui-layer-molv layui-layer-wxd'
            });
            return false;
        }
        i--;
        if (i <= 0) i = 1;
        $("#num").val(i);
        var price = parseFloat($('#tid option:selected').attr('price'));
        var prices = $('#tid option:selected').attr('prices');
        if (typeof (prices) != 'undefined' && prices != '' && prices != 'null') {
            price = price - getFinalPrice(price, prices, i);
        }
        price = price * i;
        count = count * i;
        if (count > 1) $('#need').val('￥' + price.toFixed(2) + "元 ➠ " + count + unit);
        else $('#need').val('￥' + price.toFixed(2) + "元");
    });
    $(document).on('blur', '#num', function () {
        var i = parseInt($("#num").val());
        if (isNaN(i)) return false;
        var price = parseFloat($('#tid option:selected').attr('price'));
        var count = parseInt($('#tid option:selected').attr('count'));
        var unit = $('#tid option:selected').attr('unit');
        if (undefined == unit || unit == '') {
            unit = '个';
        }
        var prices = $('#tid option:selected').attr('prices');
        if (i < 1) $("#num").val(1);
        if (typeof (prices) != 'undefined' && prices != '' && prices != 'null') {
            price = price - getFinalPrice(price, prices, i);
        }
        price = price * i;
        count = count * i;
        if (count > 1) $('#need').val('￥' + price.toFixed(2) + "元 ➠ " + count + unit);
        else $('#need').val('￥' + price.toFixed(2) + "元");
    });
    var gogo;
    $(document).on("click", "#start", function () {
        $("#gift").css("display", 'block');
        ii = layer.load(1, {
            shade: 0.3
        });
        $.ajax({
            type: "GET",
            url: serverPath + "ajax.php?act=gift_start",
            dataType: "json",
            success: function (choujiang) {
                layer.close(ii);
                if (choujiang.code == 0) {
                    $("#start").css("display", 'none');
                    $("#stop").css("display", 'block');
                    var obj = eval(choujiang.data);
                    var len = obj.length;
                    gogo = setInterval(function () {
                        var num = Math.floor(Math.random() * len);
                        var id = obj[num]['tid'];
                        var v = obj[num]['name'];
                        $("#roll").html(v);
                    }, 100);
                } else {
                    layer.alert(choujiang.msg, {
                        title: '小提示',
                        skin: 'layui-layer-molv layui-layer-wxd'
                    });
                }
            }
        });
    });
    $(document).on("click", "#stop", function () {
        ii = layer.load(1, {
            shade: 0.3
        });
        clearInterval(gogo);
        $("#roll").html('正在抽奖中..');
        var rand = Math.random(1);
        $.ajax({
            type: "GET",
            url: serverPath + "ajax.php?act=gift_start&action=ok&r=" + rand,
            dataType: "json",
            success: function (msg) {
                layer.close(ii);
                if (msg.code == 0) {
                    $.ajax({
                        type: "POST",
                        url: serverPath + "ajax.php?act=gift_stop&r=" + rand,
                        data: {
                            hashsalt: hashsalt,
                            token: msg.token
                        },
                        dataType: "json",
                        success: function (data) {
                            if (data.code == 0) {
                                $("#roll").html('恭喜您抽到奖品：' + data.name);
                                $("#start").css("display", 'block');
                                $("#stop").css("display", 'none');
                                layer.alert('恭喜您抽到奖品：' + data.name + '，请填写中奖信息', {
                                    skin: 'layui-layer-molv layui-layer-wxd',
                                    closeBtn: 0
                                }, function () {
                                    window.location.href = '?gift=1&cid=' + data.cid + '&tid=' + data.tid;
                                });
                            } else {
                                layer.alert(data.msg, {
                                    icon: 2,
                                    shade: 0.3,
                                    title: '小提示',
                                    skin: 'layui-layer-molv layui-layer-wxd'
                                });
                                $("#roll").html('点击下方按钮开始抽奖');
                                $("#start").css("display", 'block');
                                $("#stop").css("display", 'none');
                                $("#gift").css("display", 'none');
                            }
                        }
                    });
                } else {
                    layer.alert(msg.msg, {
                        icon: 2,
                        shade: 0.3,
                        title: '小提示',
                        skin: 'layui-layer-molv layui-layer-wxd'
                    });
                    $("#start").css("display", 'block');
                    $("#stop").css("display", 'none');
                    $("#gift").css("display", 'none');
                }
            }
        });
    });
    if ($_GET['buyok']) {
        var orderid = $_GET['orderid'];
        $("#tab-query").tab('show');
        $("#submit_query").click();
        isModal = false;
    } else if ($_GET['chadan']) {
        $("#tab-query").tab('show');
        isModal = false;
    }
    if ($_GET['gift']) {
        isModal = false;
    }
    if ($_GET['act'] == 'product') {
        var isModal = false;
    } else if ($_GET['act'] == 'query') {
        var isModal = false;
    } else if ($_GET['buyok']) {
        var isModal = false;
    } else if ($_GET['kw']) {
        var isModal = false;
    } else if ($_GET['act'] == 'search') {
        var isModal = false;
    }
    if (typeof (isModal) !== 'undefined' && isModal === true) {
        if (typeof (_modalType) === 'undefined') {
            var _modalType = false;
        }
        if (_modalType == true) {
            $('#myModal').modal('show');
        } else if (!$.cookie('op')) {
            $('#myModal').modal('show');
            var cookietime = new Date();
            cookietime.setTime(cookietime.getTime() + (24 * 60 * 60 * 1000));
            $.cookie('op', false, {
                expires: cookietime
            });
        }
    }
    if ($_GET['cid']) {
        var cid = parseInt($_GET['cid']);
        $("#cid").val(cid);
    }
    $("#cid").change();
    if ($.cookie('sec_defend_time')) $.removeCookie('sec_defend_time', {
        path: '/'
    });
    if (!$.cookie('op') && isModal == true) {
        $('#myModal').modal({
            keyboard: true
        });
        var cookietime = new Date();
        cookietime.setTime(cookietime.getTime() + (60 * 60 * 1000));
        $.cookie('op', false, {
            expires: cookietime
        });
    }
    if ($_GET['t']) {
        var t = $_GET['t'].toString();
        if (t != '') {
            $.ajax({
                type: "POST",
                url: "?mod=invite&act=checkKey",
                data: {
                    t: t
                },
                dataType: 'json',
                success: function (data) {
                    if (data.code == 0) {
                        var lay = layer.open({
                            title: ['靓仔靓女你好', 'text-align:center;'],
                            content: '好友邀你领取免费会员名片赞等福利！',
                            btn: ['我要领取', '残忍拒绝'],
                            yes: function () {
                                layer.close(lay);
                                var ii = layer.load(2, {
                                    shade: [0.1, '#fff']
                                });
                                $.ajax({
                                    type: "POST",
                                    url: "?mod=invite&act=tgUrl",
                                    data: {
                                        t: t
                                    },
                                    dataType: 'json',
                                    success: function (data) {
                                        layer.close(ii);
                                        if (data.code == 0) {
                                            $.cookie(t, t, 24 * 60 * 60 * 30);
                                            layer.closeAll();
                                        } else if (data.code == -2) {
                                            return false;
                                        } else if (data.code == 2) {
                                            testGeetest();
                                        }
                                    }
                                });
                            },
                            btn2: function () {
                                layer.close(lay);
                            }
                        });
                    }
                }
            });
        }
    }

    function testGeetest() {
        $.getScript("//static.geetest.com/static/tools/gt.js");
        layer.open({
            type: 1,
            title: '用户真人身份验证',
            skin: 'layui-layer-rim',
            area: ['320px', '100px'],
            content: '<div id="captcha"><p id="wait" class="text-center">正在加载验证码......</p></div>',
            success: function (dom, index) {
                $(".layui-layer-content").css('height', '');
                var width = document.body.offsetWidth;
                var windowheight = $(window).height();
                var laydom = $("#layui-layer" + index);
                var layWidth = laydom.width();
                var layHeight = laydom.get(0).offsetHeight;
                if (width <= 992) {
                    if (layWidth <= width * 0.8) {
                        layWidth = width * 0.9;
                    }
                    var left = (width - layWidth) / 2;
                    if (left < 1) {
                        left = 3;
                    }
                    if (left > width * 0.1) {
                        left = 10;
                    }
                    if (layHeight <= windowheight) {
                        var top = (windowheight - layHeight) / 2;
                        $(laydom).css({
                            'width': layWidth + "px",
                            'left': left + "px",
                            'top': top + "px"
                        });
                    } else {
                        $(laydom).css({
                            'width': layWidth + "px",
                            'left': left + "px",
                            'top': "5px"
                        });
                    }
                }
            }
        });
        $.ajax({
            url: serverPath + "ajax.php?act=captcha&t=" + (new Date()).getTime(),
            type: "get",
            dataType: "json",
            success: function (data) {
                initGeetest({
                    gt: data.gt,
                    challenge: data.challenge,
                    new_captcha: data.new_captcha,
                    product: "popup",
                    width: "100%",
                    offline: !data.success
                }, postTkey);
            }
        });
    }
    var postTkey = function (captchaObj) {
        captchaObj.appendTo('#captcha');
        captchaObj.onReady(function () {
            $("#wait").hide();
        }).onSuccess(function () {
            var result = captchaObj.getValidate();
            if (!result) {
                return alert('请完成验证');
            }
            var ii = layer.load(2, {
                shade: [0.1, '#fff']
            });
            var t = $_GET['t'];
            $.ajax({
                type: "POST",
                url: "?mod=invite&act=tgUrl",
                data: {
                    t: t,
                    geetest_challenge: result.geetest_challenge,
                    geetest_validate: result.geetest_validate,
                    geetest_seccode: result.geetest_seccode
                },
                dataType: 'json',
                success: function (data) {
                    layer.closeAll();
                    if (data.code == 0) {
                        $.cookie(t, t, 24 * 60 * 60 * 30);
                        if (data.invite_jump && data.invite_jump == 1) {
                            layer.msg("正在跳转到领取页面...");
                            setTimeout(function () {
                                window.location.href = "./?mod=invite";
                            }, 1200);
                        }
                    } else if (data.code == -2) {
                        return false;
                    } else {
                        layer.alert(data.msg);
                    }
                }
            });
        });
    };
    if ($("img.lazy").length > 0) {
        if (typeof $("img.lazy").lazyload == 'function') {
            $("img.lazy").lazyload({
                effect: "fadeIn"
            });
        } else {
            console.log("img Error：lazy is Not found！");
        }
    }
    var visits = $.cookie("counter")
    if (!visits) {
        visits = 1;
    } else {
        visits = parseInt(visits) + 1;
    }
    $('#counter').html(visits);
    $.cookie("counter", visits, 24 * 60 * 60 * 30);
    if ($('#audio-play').is(':visible')) {
        audio_init.play();
    }
    $("#qq3").focus(function () {
        layer.tips('输入下单时某个框填写的内容，例如名片赞的填QQ号即可，注意填写完整', this, {
            tips: 1,
            time: 5000
        });
    });
});