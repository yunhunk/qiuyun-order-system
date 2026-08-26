"use strict";
// VERSION 2293
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
var Cron = {
    runing: null,
    run: function (func, times) {
        this.runing = setInterval(func, times);
        return this;
    },
    stop: function () {
        return clearInterval(this.runing);
    }
}
var workBackCronObj = {
    zt: false
};
var workBackCron = function () {
    var order_id = $("#work_orderid").val();
    showWorksInfo(order_id);
    return 1;
}

function closeWorkCall() {
    if (typeof (workBackCronObj.workCron) == 'object') workBackCronObj.workCron.stop();
    workBackCronObj.zt = false;
}

function random(len) {
    var len = len ? len : 6;
    var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
    var maxPos = $chars.length;
    var pwd = '';
    for (var i = 0; i < len; i++) {
        pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
    }
    return pwd;
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
            $('#count_tool').html(data.tool);
            if (data.gift && data.gift != "" && data.gift != null) {
                $.each(data.gift, function (k, v) {
                    $('#pst_1').append('<li><strong>' + k + '</strong> 获得&nbsp;' + v + '</li>');
                });
                $('.giftlist').show();
                $('.giftlist ul').css('height', (35 * $('#pst_1 li').length) + 'px');
                scollgift();
            }
            if (data.cart_count != null && data.cart_count > 0) {
                $('#cart_count').html(data.cart_count);
                $('#alert_cart').slideDown();
            }
        }
    });
}
var pwdlayer;

function changepwd(id, skey) {
    pwdlayer = layer.open({
        type: 1,
        title: '修改密码',
        skin: 'layui-layer-rim',
        content: '<div class="form-group"><div class="input-group"><div class="input-group-addon">密码</div><input type="text" id="pwd" value="" class="form-control" placeholder="请填写新的密码" required/></div></div><input type="submit" id="save" onclick="saveOrderPwd(' + id + ',\'' + skey + '\')" class="btn btn-primary btn-block" value="保存">'
    });
}

function saveOrderPwd(id, skey) {
    var pwd = $("#pwd").val();
    if (pwd == '') {
        layer.alert('请确保每项不能为空！');
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
                layer.alert(data.msg);
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

function getPoint() {
    //if ($('#tid option:selected').length>0) {console.log($('#tid option:selected').val());console.log($('#tid').val());}
    if ($('#tid option:selected').val() == undefined || $('#tid option:selected').val() == "0") {
        $("#display_list").show();
        $('#inputsname').html("");
        $('#display_price').hide();
        $('#display_num').hide();
        $('#need').val('');
        $('#alert_frame').hide();
        $("#display_toolname").hide();
        return false;
    }
    if (typeof (ui_tool) != 'undefined' && ui_tool == 1) {
        $("#display_list").hide();
        $("#display_toolname").show();
        $("#toolname").html($('#tid option:selected').attr('name'));
    }
    $("#display_tool").show();
    $('#display_price').show();
    var cid = parseInt($('#cid').val());
    if (cid < 1) {
        parseInt($('#cid option:selected').val());
    }
    var sub_cid = parseInt($("#sub_cid").val());
    if (sub_cid < 1) {
        parseInt($('#sub_cid option:selected').val());
    }
    var tid = parseInt($('#tid option:selected').val());
    if (sub_cid > 0) {
        setHistory('cid=' + cid + '&sub_cid=' + sub_cid + '&tid=' + tid);
    } else {
        setHistory('cid=' + cid + '&tid=' + tid);
    }
    var multi = $('#tid option:selected').attr('multi');
    var count = $('#tid option:selected').attr('count');
    var price = $('#tid option:selected').attr('price');
    var active = parseInt($('#tid option:selected').attr('active'));
    var stock_open = parseInt($('#tid option:selected').attr('stock_open'));
    var stock = parseInt($('#tid option:selected').attr('stock'));
    var prices = $('#tid option:selected').attr('prices');
    var unit = $('#tid option:selected').attr('unit');
    if (undefined == unit || unit == '') {
        unit = '个';
    }
    if (typeof (prices) != 'undefined' && prices != '' && prices != 'null') {
        price = price - getFinalPrice(price, prices, 1);
    }
    if (multi == 1 && count > 1) {
        $('#need').val('￥' + price + "元 ➠ " + count + unit);
    } else {
        $('#need').val('￥' + price + "元");
    }
    var shopimg = $('#tid option:selected').attr('shopimg');
    if (typeof (shopimg) === 'string' && shopimg != "" && shopimg != "null") {
        if ($('img[data-name="thumb"]').length > 0) {
            $('img[data-name="thumb"]').attr("src", shopimg);
            $('img[data-name="thumb"]').attr("onerror", "this.src='/assets/img/Product/default.png'");
        } else if ($('img#classImg').length > 0) {
            $('img#classImg').attr("src", shopimg);
            $('img#classImg').attr("onerror", "this.src='/assets/img/Product/default.png'");
        }
    }
    if (active != 1) {
        $('#submit_cart_shop').hide();
        $('#submit_buy').val('禁售中');
        $('#submit_buy').html('禁售中');
    } else if (price == 0) {
        $('#submit_buy').val('免费领取');
        $('#submit_buy').html('免费领取');
    } else {
        if (cartBuy && cartBuy == '1') {
            $('#submit_cart_shop').show();
        }
        $('#submit_buy').val('立即购买');
        $('#submit_buy').html('立即购买');
    }
    if (multi == 1) {
        $('#display_num').show();
    } else {
        $('#display_num').hide();
    }
    var desc = $('#tid option:selected').attr('desc');
    if (active == 1) {
        $('#alert_title').css({
            "font-size": "1.8rem",
            "color": "red",
            "padding": "2px 0",
            "margin": "1px auto",
            "text-align": "center"
        }).html("----商品说明 必看----");
        if (desc && desc != '' && alert != 'null') {
            $('#alert_frame').fadeIn() || $('#alert_frame').css('display', 'block');
            $('#alert_content').html(decodeURI(desc));
        } else {
            $('#alert_frame').hide();
        }
    } else {
        var close_alert = $('#tid option:selected').attr('close_alert');
        $('#alert_title').css({
            "font-size": "1.8rem",
            "color": "red",
            "padding": "2px 0",
            "margin": "1px auto",
            "text-align": "center"
        }).html("----下架说明----");
        if (close_alert && close_alert != "" && close_alert != null) {
            $('#alert_content').html(decodeURI(close_alert));
            //layer.alert(decodeURI(close_alert));
        } else {
            $('#alert_content').html("<center>当前商品维护中，停止下单</center>");
            //layer.alert('当前商品维护中，停止下单！');
        }
        $('#alert_frame').fadeIn() || $('#alert_frame').css('display', 'block');
        return true;
    }
    $('#inputsname').html("");
    var inputname = $('#tid option:selected').attr('inputname');
    if (inputname == 'hide') {
        $('#inputsname').append('<input type="hidden" name="inputvalue" id="inputvalue" value="' + $.cookie('mysid') + '"/>');
    } else if (inputname != '') {
        var gettype = ' gettype=""';
        if (inputname.indexOf("[!shareurl]") >= 0) {
            gettype = ' gettype="!shareurl"';
        } else if (inputname.indexOf("[shareurl]") >= 0) {
            gettype = ' gettype="shareurl"';
        } else if (inputname.indexOf("[shareid]") >= 0) {
            gettype = ' gettype="shareid"';
        } else if (inputname.indexOf("[zpid]") >= 0) {
            gettype = ' gettype="zpid"';
        }
        inputname = inputname.replace('[!shareurl]', '');
        inputname = inputname.replace('[shareurl]', '');
        inputname = inputname.replace('[shareid]', '');
        inputname = inputname.replace('[zpid]', '');
        var placeholder = "";
        if (inputname.indexOf("&") !== (-1)) {
            placeholder = inputname.split('&')[1];
            inputname = inputname.split('&')[0];
        } else {
            placeholder = '输入' + inputname;
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
            inputname = inputname.replace('[!shareurl]', '');
            $('#inputsname').append('<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname">' + selectname + '</div><select name="inputvalue" id="inputvalue" placeholder="' + placeholder + '" inputname="' + selectname + '"  class="form-control input">' + addstr + '</select></div></div>');
        } else if (extendEventBlur == '' && inputname.indexOf('[') > 0 && inputname.indexOf(']') > 0) {
            var addstr = '';
            var selectname = inputname.split('[')[0];
            var selectstr = inputname.split('[')[1].split(']')[0];
            $.each(selectstr.split(','), function (i, v) {
                if (v.indexOf(':') > 0) {
                    i = v.split(':')[0];
                    v = v.split(':')[1];
                } else {
                    i = v;
                }
                addstr += '<option value="' + i + '">' + v + '</option>';
            });
            $('#inputsname').append('<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname">' + selectname + '</div><select name="inputvalue" id="inputvalue" placeholder="' + placeholder + '" inputname="' + selectname + '"  class="form-control input">' + addstr + '</select></div></div>');
        } else {
            $('#inputname').html(inputname);
            $('#inputsname').append('<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname">' + inputname + '</div><input type="text" name="inputvalue" id="inputvalue" ' + gettype + ' placeholder="' + placeholder + '" value="' + ($_GET['qq'] ? $_GET['qq'] : '') + '" inputname="' + inputname + '" class="form-control input" required onblur="checkInput();' + extendEventBlur + '" ' + extendEventAttr + '/></div></div>');
        }
    } else {
        $('#inputsname').append('<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname">下单ＱＱ</div><input type="text" name="inputvalue" placeholder="输入下单QQ" id="inputvalue" value="' + ($_GET['qq'] ? $_GET['qq'] : '') + '" inputname="下单QQ" class="form-control" required onblur="checkInput()"/></div></div>');
    }
    var inputsname = $('#tid option:selected').attr('inputsname');
    if (inputsname != '') {
        $.each(inputsname.split('|'), function (i, value) {
            var gettype = ' gettype=""';
            if (value.indexOf("[!shareurl]") >= 0) {
                gettype = ' gettype="!shareurl"';
            } else if (value.indexOf("[shareurl]") >= 0) {
                gettype = ' gettype="shareurl"';
            } else if (value.indexOf("[shareid]") >= 0) {
                gettype = ' gettype="shareid"';
            } else if (value.indexOf("[zpid]") >= 0) {
                gettype = ' gettype="zpid"';
            }
            value = value.replace('[!shareurl]', '');
            value = value.replace('[shareurl]', '');
            value = value.replace('[shareid]', '');
            value = value.replace('[zpid]', '');
            var extendEventBlur = '';
            var extendEventAttr = '';
            //输入框价格变量
            if (value.indexOf('[int:') > 0) {
                extendEventBlur = 'numChange(this);';
                var str1 = value.split('[int:')[1];
                value = value.split('[int:')[0];
                extendEventAttr = ' price="' + price + '" extendBlurAttr="' + str1.split(']')[0] + '"';
            }
            if (extendEventBlur == '' && value.indexOf('{') > 0 && value.indexOf('}') > 0) {
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
                $('#inputsname').append('<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname' + (i + 2) + '">' + selectname + '</div><select name="inputvalue' + (i + 2) + '" id="inputvalue' + (i + 2) + '" inputname="' + selectname + '" class="form-control input">' + addstr + '</select></div></div>');
            } else if (extendEventBlur == '' && value.indexOf('[') > 0 && value.indexOf(']') > 0) {
                var addstr = '';
                var selectname = value.split('[')[0];
                var selectstr = value.split('[')[1].split(']')[0];
                $.each(selectstr.split(','), function (i, v) {
                    if (v.indexOf(':') > 0) {
                        i = v.split(':')[0];
                        v = v.split(':')[1];
                    } else {
                        i = v;
                    }
                    addstr += '<option value="' + i + '">' + v + '</option>';
                });
                $('#inputsname').append('<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname' + (i + 2) + '">' + selectname + '</div><select name="inputvalue' + (i + 2) + '" id="inputvalue' + (i + 2) + '" inputname="' + selectname + '" class="form-control input">' + addstr + '</select></div></div>');
            } else {
                if (value == '说说ID' || value == '说说ＩＤ') var addstr = '<div class="input-group-addon onclick" onclick="get_shuoshuo(\'inputvalue' + (i + 2) + '\',$(\'#inputvalue\').val())">自动获取</div>';
                else if (value == '日志ID' || value == '日志ＩＤ') var addstr = '<div class="input-group-addon onclick" onclick="get_rizhi(\'inputvalue' + (i + 2) + '\',$(\'#inputvalue\').val())">自动获取</div>';
                else if (value == '抖音评论ID') var addstr = '<div class="input-group-addon onclick" onclick="getCommentList(\'inputvalue' + (i + 2) + '\',$(\'#inputvalue\').val())">自动获取</div>';
                else if (value == '收货人地址' || value == '收货地址' || value == '地址') var addstr = '<div class="input-group-addon onclick" onclick="inputAddress(\'inputvalue' + (i + 2) + '\')">填写地址</div>';
                else {
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
                $('#inputsname').append('<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname' + (i + 2) + '">' + ibtn + '</div><input type="text" name="inputvalue' + (i + 2) + '" placeholder="' + placeholder + '" inputname="' + ibtn + '" id="inputvalue' + (i + 2) + '" value="" ' + gettype + ' class="form-control input" onblur="' + extendEventBlur + '" ' + extendEventAttr + ' required/>' + addstr + '</div></div>');
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

    // 点赞数量
    if ($("#like-num-down").length > 0) {
        console.log('测试', '#like-num-down');
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=getlikecount",
            data: {
                tid: $('#tid option:selected').val()
            },
            dataType: 'json',
            success: function (data) {
                $('#display_like').show();
                $('#like-num-up').html(data.like_up)
                $('#like-num-down').html(data.like_down)
            }
        });

        // 顶
        $(document).on('click', '.like-box-left', function () {
            $.ajax({
                type: "POST",
                url: serverPath + "ajax.php?act=like",
                data: {
                    tid: $('#tid option:selected').val(),
                    type: 'up',
                },
                dataType: 'json',
                success: function (res) {
                    if (res.code == -2) {
                        is_orderWork = false;
                        is_showWork = false;
                        orderid = null;
                        var index = layer.alert('该操作需要登录, 是否登录?', {
                            btn: ['现在登录', '现在注册', '取消操作'],
                            yes: function () {
                                $("#cmLoginModal").modal('show');
                                is_showWork = false;
                                is_orderWork = false;
                                orderid = null;
                                layer.close(index);
                            },
                            btn2: function () {
                                $("#cmRegModal").modal('show');
                                is_showWork = false;
                                is_orderWork = false;
                                orderid = null;
                                layer.close(index);
                            },
                            btn3: function () {
                                layer.close(index);
                            }
                        });
                    }
                    else if (res.code == 0) {
                        generateLikes(document.querySelector('.like-icon-left'), 'up')
                        $('#like-num-up').html('' + (parseInt($('#like-num-up').html()) + 1))
                    } else {
                        layer.alert(res.msg);
                    }
                }
            });
        });

        // 踩
        $(document).on('click', '.like-box-right', function () {
            $.ajax({
                type: "POST",
                url: serverPath + "ajax.php?act=like",
                data: {
                    tid: $('#tid option:selected').val(),
                    type: 'down',
                },
                dataType: 'json',
                success: function (res) {
                    if (res.code == -2) {
                        is_orderWork = false;
                        is_showWork = false;
                        orderid = null;
                        var index = layer.alert('该操作需要登录, 是否登录?', {
                            btn: ['现在登录', '现在注册', '取消操作'],
                            yes: function () {
                                $("#cmLoginModal").modal('show');
                                layer.close(index);
                            },
                            btn2: function () {
                                $("#cmRegModal").modal('show');
                                layer.close(index);
                            },
                            btn3: function () {
                                layer.close(index);
                            }
                        });
                    }
                    else if (res.code == 0) {
                        generateLikes(document.querySelector('.like-icon-right'), 'down');
                        $('#like-num-down').html('' + (parseInt($('#like-num-down').html()) + 1));
                    } else {
                        layer.alert(res.msg);
                    }
                }
            });
        });
    }

    var alert = $('#tid option:selected').attr('alert');
    if (alert && alert != '' && alert != 'null' && active == 1) {
        var area = [$(window).width() > 640 ? '420px' : '97%', 'auto'];
        layer.alert('' + decodeURI(alert) + '', {
            area: area,
            title: '商品提示',
            btn: '我知道了',
            success: function (layero, index) {
                //重定义高度
                setTimeout(() => {
                    var lh = $(layero).height();
                    var wh = $(window).height();
                    if (lh > wh) {
                        layer.style(index, {
                            top: '12px',
                            bottom: '8px',
                            height: (wh - 20) + 'px'
                        });
                    }
                }, 500)
            },
        }, function (index) {
            layer.close(index);
        });
    }
    if ($("#display_batch").length > 0) $("#display_batch").hide();
    if (price > 0) {
        setBatchTips();
    }
}

function generateLikes(el, type) {
    const icon = el;
    type = type || 'up'
    const count = 4; // 生成点赞的数量  
    for (let i = 0; i < count; i++) {
        const svg = icon.querySelector('svg').cloneNode(true);
        const particle = document.createElement('div');
        particle.classList.add('like-particle');
        particle.classList.add('like-particle' + i);
        particle.style.transform = 'scale(' + (Math.random() * (Math.random() * 10 / 2.5)) + ')';
        particle.appendChild(svg);
        // 设置随机的水平和垂直位置  
        let topOffset = type == 'up' ? -randTopOffset() : randTopOffset(); // 垂直偏移   
        particle.style.top = `${topOffset}px`;
        icon.appendChild(particle);
        setTimeout(() => {
            particle.remove(); // 动画完成后移除元素  
        }, 2 * 1000); // 与动画时间一致  
    }
}

function randTopOffset() {
    let topOffset = (Math.random() * 140) + (Math.random() * 12) - (Math.random() * 5);

    if (topOffset >= 65) {
        return topOffset;
    }
    return randTopOffset();
}

function setBatchTips() {
    if ($("#batch_length").length < 1) {
        return false;
    }
    var html = '',
        name = '',
        placeholder = '',
        num = 1;
    var els = $("[class$='input']");
    num = els.length;
    for (var i = 0; i < els.length; i++) {
        name = $(els[i]).attr('inputname');
        if (name.indexOf(':') >= 0) {
            name = name.split(':')[0];
        } else if (name.indexOf('：') >= 0) {
            name = name.split('：')[0];
        }
        if (html == '') {
            html = name;
            placeholder = name + '示例1';
        } else {
            html += ' | ' + name;
            placeholder += '|' + name + '示例1';
        }
    }
    $("#batch_length").val(num);
    $("span#batch_label").html(html + "-份数");
    $("#batch_text").attr('placeholder', placeholder + "-1");
    $("#display_batch").show();
}

function isEmptyVariable($var) {
    if ('undefined' == typeof ($var) || 'null' == $var || '' == $var) {
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
        layer.alert('请先填写QQ号！');
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
                    if (km == 1) {
                        $('#show_shuoshuo').html('<div class="input-group"><div class="input-group-addon onclick" title="上一页" onclick="get_shuoshuo(\'' + id + '\',$(\'#km_inputvalue\').val(),' + km + ',' + lastpage + ')"><i class="fa fa-chevron-left"></i></div><select id="shuoid" class="form-control" onchange="set_shuoshuo(\'' + id + '\');">' + addstr + '</select><div class="input-group-addon onclick" title="下一页" onclick="get_shuoshuo(\'' + id + '\',$(\'#km_inputvalue\').val(),' + km + ',' + nextpage + ')"><i class="fa fa-chevron-right"></i></div></div>');
                    } else {
                        $('#show_shuoshuo').html('<div class="input-group"><div class="input-group-addon onclick" title="上一页" onclick="get_shuoshuo(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + lastpage + ')"><i class="fa fa-chevron-left"></i></div><select id="shuoid" class="form-control" onchange="set_shuoshuo(\'' + id + '\');">' + addstr + '</select><div class="input-group-addon onclick" title="下一页" onclick="get_shuoshuo(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + nextpage + ')"><i class="fa fa-chevron-right"></i></div></div>');
                    }
                } else {
                    if (km == 1) {
                        $('#km_inputsname').append('<div class="form-group" id="show_shuoshuo"><div class="input-group"><div class="input-group-addon onclick" title="上一页" onclick="get_shuoshuo(\'' + id + '\',$(\'#km_inputvalue\').val(),' + km + ',' + lastpage + ')"><i class="fa fa-chevron-left"></i></div><select id="shuoid" class="form-control" onchange="set_shuoshuo(\'' + id + '\');">' + addstr + '</select><div class="input-group-addon onclick" title="下一页" onclick="get_shuoshuo(\'' + id + '\',$(\'#km_inputvalue\').val(),' + km + ',' + nextpage + ')"><i class="fa fa-chevron-right"></i></div></div></div>');
                    } else {
                        $('#inputsname').append('<div class="form-group" id="show_shuoshuo"><div class="input-group"><div class="input-group-addon onclick" title="上一页" onclick="get_shuoshuo(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + lastpage + ')"><i class="fa fa-chevron-left"></i></div><select id="shuoid" class="form-control" onchange="set_shuoshuo(\'' + id + '\');">' + addstr + '</select><div class="input-group-addon onclick" title="下一页" onclick="get_shuoshuo(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + nextpage + ')"><i class="fa fa-chevron-right"></i></div></div></div>');
                    }
                }
                set_shuoshuo(id);
            } else {
                var area = [$(window).width() > 640 ? '75%' : '97%', '550px'];
                layer.open({
                    type: 2,
                    area: area,
                    title: '扫码以获取QQ空间说说',
                    content: './qzone.php',
                    btn: ['取消重试', '关闭并获取'],
                    yes: function (index, layero) {
                        layer.close(index);
                    },
                    btn2: function () {
                        setTimeout(function () {
                            get_shuoshuo(id, uin, km, page)
                        }, 500);
                    }
                });
                //layer.alert(data.msg);
            }
        }
    });
}

function set_shuoshuo(id) {
    var shuoid = $('#shuoid').val();
    $('#' + id).val(shuoid);
}

function upload() {
    var fileObj = $("#file")[0].files[0];
    if (typeof (fileObj) == "undefined" || fileObj.size <= 0) {
        return;
    }
    var orderid = $("select[name='orderid'] option:selected").val();
    var formData = new FormData();
    formData.append("type", "workorder");
    formData.append("file", fileObj);
    formData.append("key", "file");
    formData.append("orderid", orderid);
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        url: serverPath + "ajax.php?act=upload",
        data: formData,
        type: "POST",
        dataType: "json",
        cache: false,
        processData: false,
        contentType: false,
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg('上传成功');
                var index = $("#index").val();
                index++;
                $("#index").val(index);
                var html = $("#image_template").html();
                html = html.replace(new RegExp('{index}', 'g'), index);
                html = html.replace(new RegExp('{img}', 'g'), data.imgSrc);
                html = html.replace(new RegExp('{fileid}', 'g'), data.fileid);
                $(".imageList").html(html + $(".imageList").html());
                setImageList();
            } else {
                layer.alert(data.msg);
            }
        },
        error: function (data) {
            layer.msg('服务器错误');
            return false;
        }
    })
}

function setImageList() {
    var els = $(".imageList .item a");
    var list = [];
    for (var i = 0; i < els.length; i++) {
        var id = $(els[i]).data('fileid');
        list.push(id)
    }
    console.log('list', list);
    $("#imagelist").val(list.join(','));
}

function get_rizhi(id, uin, km, page) {
    km = km || 0;
    page = page || 1;
    if (uin == '') {
        layer.alert('请先填写QQ号！');
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
                    $('#show_rizhi').html('<div class="input-group"><div class="input-group-addon onclick" onclick="get_rizhi(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + lastpage + ')"><i class="fa fa-chevron-left"></i></div><select id="blogid" class="form-control" onchange="set_rizhi(\'' + id + '\');">' + addstr + '</select><div class="input-group-addon onclick" onclick="get_rizhi(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + nextpage + ')"><i class="fa fa-chevron-right"></i></div></div>');
                } else {
                    if (km == 1) {
                        $('#km_inputsname').append('<div class="form-group" id="show_rizhi"><div class="input-group"><div class="input-group-addon onclick" onclick="get_rizhi(\'' + id + '\',$(\'#km_inputvalue\').val(),' + km + ',' + lastpage + ')"><i class="fa fa-chevron-left"></i></div><select id="blogid" class="form-control" onchange="set_rizhi(\'' + id + '\');">' + addstr + '</select><div class="input-group-addon onclick" onclick="get_rizhi(\'' + id + '\',$(\'#km_inputvalue\').val(),' + km + ',' + nextpage + ')"><i class="fa fa-chevron-right"></i></div></div></div>');
                    } else {
                        $('#inputsname').append('<div class="form-group" id="show_rizhi"><div class="input-group"><div class="input-group-addon onclick" onclick="get_rizhi(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + lastpage + ')"><i class="fa fa-chevron-left"></i></div><select id="blogid" class="form-control" onchange="set_rizhi(\'' + id + '\');">' + addstr + '</select><div class="input-group-addon onclick" onclick="get_rizhi(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + nextpage + ')"><i class="fa fa-chevron-right"></i></div></div></div>');
                    }
                }
                set_rizhi(id);
            } else {
                layer.alert(data.msg);
            }
        }
    });
}

function set_rizhi(id) {
    var blogid = $('#blogid').val();
    $('#' + id).val(blogid);
}

function fillOrder(id, skey) {
    if (!confirm('是否确定补交订单？')) return;
    $.ajax({
        type: "POST",
        url: serverPath + "ajax.php?act=fill",
        data: {
            orderid: id,
            skey: skey
        },
        dataType: 'json',
        success: function (data) {
            layer.alert(data.msg);
            $("#submit_query").click();
        }
    });
}

function getsongid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！');
        return false;
    }
    if (songurl.indexOf('.qq.com') < 0) {
        layer.alert('请输入正确的歌曲的分享链接！');
        return false;
    }
    try {
        var songid = songurl.split('s=')[1].split('&')[0];
        layer.msg('ID获取成功！提交下单即可');
    } catch (e) {
        layer.alert('请输入正确的歌曲的分享链接！');
        return false;
    }
    $('#inputvalue').val(songid);
}

function getkuaishouid() {
    var ksurl = $("#inputvalue").val();
    if (ksurl == "") {
        ksurl = $("#inputvalue2").val();
    }
    if (ksurl == '') {
        layer.alert('请确保作品链接不能为空！');
        return false;
    }
    if (ksurl.indexOf('http') < 0) {
        layer.alert('请输入正确的作品链接！');
        return false;
    }
    if (ksurl.indexOf('http') >= 0) {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=getkuaishou",
            data: {
                url: ksurl
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#inputvalue').val(data.authorid);
                    if (inputDisabled) $('#inputvalue').attr('disabled', true);
                    if ($('#inputvalue2').length > 0) {
                        $('#inputvalue2').val(data.videoid);
                        if (inputDisabled) $('#inputvalue2').attr('disabled', true);
                    }
                    layer.msg('ID获取成功！提交下单即可');
                } else {
                    layer.alert(data.msg);
                    return false;
                }
            }
        });
    } else {
        try {
            if (ksurl.indexOf('userId=') > 0) {
                var authorid = ksurl.split('userId=')[1].split('&')[0];
            } else {
                var authorid = ksurl.split('photo/')[1].split('/')[0];
            }
            if (ksurl.indexOf('photoId=') > 0) {
                var videoid = ksurl.split('photoId=')[1].split('&')[0];
            } else {
                var videoid = ksurl.split('photo/')[1].split('/')[1].split('?')[0];
            }
            layer.msg('ID获取成功！提交下单即可');
        } catch (e) {
            layer.alert('请输入正确的作品链接！');
            return false;
        }
        $('#inputvalue').val(authorid);
        if ($('#inputvalue2').length > 0) {
            $('#inputvalue2').val(videoid);
            if (inputDisabled) $('#inputvalue2').attr('disabled', true);
        }
    }
}

function get_kuaishou(id, ksid) {
    getkuaishouid()
}

function gethuoshanid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！');
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
                    $('#inputvalue').val(data.videoid);
                    if (inputDisabled) $('#inputvalue').attr('disabled', true);
                    layer.msg('视频ID获取成功！下单即可');
                } else {
                    layer.alert(data.msg);
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
            $('#inputvalue').val(songid);
            if (inputDisabled) $('#inputvalue').attr('disabled', true);
            layer.msg('视频ID获取成功！下单即可');
        } catch (e) {
            layer.alert('请输入正确的链接！');
            return false;
        }
    }
}

function inputAddress(dom) {
    if (typeof ($("body").distpicker) !== 'function') {
        return layer.alert("省市区支持库文件未引入！请联系平台客服处理");
    }
    var html = '<div class="panel-body"><div id="AddressContent" data-toggle="distpicker">' + ' <div class="form-group"><div class="input-group"><div class="input-group-addon">选择省份</div><select class="form-control" id="province"><option>省份加载中</option></select></div></div>' + ' <div class="form-group"><div class="input-group"><div class="input-group-addon">选择市区</div><select class="form-control" id="city"><option>市区加载中</option></select></div></div>' + ' <div class="form-group"><div class="input-group"><div class="input-group-addon">选择县镇</div><select class="form-control" id="district"><option>县镇加载中</option></select></div>' + '</div><div class="form-group"><div class="input-group"><div class="input-group-addon">详细街道</div><input id="street" class="form-control" type="text" value=""/></div></div>' + '</div><script>var item=$("#AddressContent")||!layer.alert("出错了，加载省份时出现问题！");item.distpicker({ province: "-- 请选择省份 --", city: "-- 请选择市 --", district: "-- 请选择区 --"});</script>';
    layer.open({
        type: 1,
        title: '填写收货地址',
        content: html,
        btn: ['确定', '取消'],
        yes: function () {
            var address = $('#province option:selected').text().indexOf("请选择") == -1 ? $('#province option:selected').text() : "";
            address += $('#city option:selected').text().indexOf("请选择") == -1 ? $('#city option:selected').text() : "";
            address += $('#district option:selected').text().indexOf("请选择") == -1 ? $('#district option:selected').text() : "";
            address += $('#street').val();
            $('#' + dom).val(address);
            //console.log('地址：'+address);
            layer.closeAll();
        }
    });
}

function getlvzhouid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！');
        return false;
    }
    try {
        var songid = songurl.split('sid=')[1];
        $('#inputvalue').val(songid);
        if (inputDisabled) $('#inputvalue').attr('disabled', true);
        layer.msg('ID获取成功！提交下单即可');
    } catch (e) {
        layer.alert('请输入正确的链接！');
        return false;
    }
}

function getdouyinid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！');
        return false;
    }
    if (songurl.indexOf('/v.douyin.com/') > 0 || songurl.indexOf('/s/') > 0) {
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
                    $('#inputvalue').val(data.videoid);
                    if (inputDisabled) $('#inputvalue').attr('disabled', true);
                    layer.msg('ID获取成功！提交下单即可');
                } else {
                    layer.alert(data.msg);
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
            if (inputDisabled) $('#inputvalue').attr('disabled', true);
            layer.msg('ID获取成功！提交下单即可');
        } catch (e) {
            layer.alert('请输入正确的链接！');
            return false;
        }
        $('#inputvalue').val(songid);
    }
}

function getDouyinUserId() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！');
        return false;
    }
    if (songurl.indexOf('/douyin/') >= 0) {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=getDouyinUserId",
            data: {
                url: songurl
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#inputvalue').val(data.videoid);
                    if (inputDisabled) $('#inputvalue').attr('disabled', true);
                    layer.msg('ID获取成功！提交下单即可');
                } else {
                    layer.alert(data.msg);
                    return false;
                }
            }
        });
    } else {
        try {
            if (songurl.indexOf('user/') > 0 && songurl.indexOf('?') > 0) {
                var songid = songurl.split('user/')[1].split('?')[0];
            } else {
                var songid = songurl.split('user/')[1];
            }
            if (inputDisabled) $('#inputvalue').attr('disabled', true);
            layer.msg('主页ID获取成功！提交下单即可');
        } catch (e) {
            layer.alert('请输入正确的主页链接！');
            return false;
        }
        $('#inputvalue').val(songid);
    }
}

function gettoutiaoid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！');
        return false;
    }
    try {
        if (songurl.indexOf('user/') > 0) {
            var songid = songurl.split('user/')[1].split('/')[0];
        } else {
            var songid = songurl.split('profile/')[1].split('/')[0];
        }
        if (inputDisabled) $('#inputvalue').attr('disabled', true);
        layer.msg('ID获取成功！提交下单即可');
    } catch (e) {
        layer.alert('请输入正确的链接！');
        return false;
    }
    $('#inputvalue').val(songid);
}

function getweishiid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！');
        return false;
    }
    if (songurl.indexOf('.qq.com') < 0) {
        layer.alert('请输入正确的链接！');
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
        if (inputDisabled) $('#inputvalue').attr('disabled', true);
        layer.msg('ID获取成功！提交下单即可');
    } catch (e) {
        layer.alert('请输入正确的链接！');
        return false;
    }
    $('#inputvalue').val(songid);
}

function getwsUserid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！');
        return false;
    }
    if (songurl.indexOf('.qq.com') < 0) {
        layer.alert('请输入正确的主页分享链接！');
        return false;
    }
    try {
        if (songurl.indexOf('personal/') > 0) {
            var songid = songurl.split('personal/')[1].split('/')[0];
        } else {
            var songid = songurl.split('id=')[1].split('&')[0];
        }
        $('#inputvalue').val(songid);
        if (inputDisabled) $('#inputvalue').attr('disabled', true);
        layer.msg('ID获取成功！提交下单即可');
    } catch (e) {
        layer.alert('请输入正确的主页分享链接！');
        return false;
    }
}

function getpipixia() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！');
        return false;
    }
    if (songurl.indexOf('/s/') > 0) {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=getpipixia",
            data: {
                url: songurl
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#inputvalue').val(data.videoid);
                    if (inputDisabled) $('#inputvalue').attr('disabled', true);
                    layer.msg('ID获取成功！提交下单即可');
                } else {
                    layer.alert(data.msg);
                    return false;
                }
            }
        });
    } else {
        if (songurl.indexOf('item/') < 0 && songurl.indexOf('pipix') < 0) {
            layer.alert('请输入正确的作品链接！');
            return false;
        }
        try {
            var songid = songurl.split('item/')[1].split('?')[0];
            $('#inputvalue').val(songid);
            layer.msg('ID获取成功！提交下单即可');
            if (inputDisabled) $('#inputvalue').attr('disabled', true);
        } catch (e) {
            layer.alert('请输入正确的作品链接！');
            return false;
        }
    }
}

function getxiaohongshuid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！');
        return false;
    }
    if (songurl.indexOf('http') >= 0) {
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
                    $('#inputvalue').val(data.videoid);
                    if (inputDisabled) $('#inputvalue').attr('disabled', true);
                    layer.msg('ID获取成功！提交下单即可');
                } else {
                    layer.alert(data.msg);
                    return false;
                }
            }
        });
    } else {
        //http://xiaohongshu.com/item/45454a2d1a?efedw=44a4
        if (songurl.indexOf('xiaohongshu.com') < 0 && songurl.indexOf('pipix.com') < 0) {
            layer.alert('请输入正确的链接！');
            return false;
        }
        try {
            var songid = songurl.split('item/')[1].split('?')[0];
            $('#inputvalue').val(songid);
            layer.msg('ID获取成功！提交下单即可');
            if (inputDisabled) $('#inputvalue').attr('disabled', true);
        } catch (e) {
            layer.alert('请输入正确的链接！');
            return false;
        }
    }
}

function biliChange(val) {
    var str1 = 'fZodR9XQDSUm21yCkr6zBqiveYah8bt4xsWpHnJE7jL5VG3guMTKNPAwcF';
    var table = str1.split("");
    var s = [11, 10, 3, 8, 4, 6];
    var xor = 177451812;
    var add = 8728348608;
    var av2bv = function (av) {
        var num = NaN;
        if (Object.prototype.toString.call(av) === '[object Number]') {
            num = av;
        } else if (Object.prototype.toString.call(av) === '[object String]') {
            var regx1 = new RegExp('[0-9]+', 'gi');
            num = parseInt(av.replace(regx1));
        };
        if (isNaN(num) || num <= 0) {
            // 网页版直接输出这个结果了
            return av;
        };
        num = (num ^ xor) + add;
        var str2 = 'bv1  4 1 7  ';
        var result = str2.split(" ");
        var i = 0;
        while (i < 6) {
            // 这里改写差点犯了运算符优先级的坑
            // 果然 Python 也不是特别熟练
            // 说起来 ** 按照传统语法应该写成 Math.pow()，但是我个人更喜欢 ** 一些
            result[s[i]] = table[Math.floor(num / Math.pow(58, i)) % 58];
            i += 1;
        };
        return result.join('');
    };
    var bv2av = function (bv) {
        var str = '';
        if (bv.length === 12) {
            str = bv;
        } else if (bv.length === 10) {
            str = 'BV' + bv;
            // 根据官方 API，BV 号开头的 BV1 其实可以省略
            // 不过单独省略个 B 又不行（
        } else if (bv.length === 9) {
            str = 'BV1' + bv;
        } else {
            return bv;
        };
        var regx2 = new RegExp('^[Bb][Vv][a-zA-Z0-9]{10}$', 'gi');
        if (!str.match(regx2)) {
            console.log("bv2av的ID格式不正确，匹配失败");
            return bv;
        };
        var result = 0;
        var i = 0;
        while (i < 6) {
            result += table.indexOf(str[s[i]]) * Math.pow(58, i);
            i += 1;
        };
        return 'av' + (result - (add ^ xor));
    };
    if (val.substring(0, 2).toLowerCase() == 'bv') {
        return bv2av(val);
    }
    return val;
}

function getbiliid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！');
        return false;
    }
    var reg = /(https|http):\/\/([\w_\-\.]+)\/([a-zA-Z0-9_\-]+)/;
    //console.log(reg.exec(songurl));
    if (reg.test(songurl)) {
        //https://b23.tv/JBnIOh
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=getbilibili",
            data: {
                url: songurl
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    var songid = biliChange(data.videoid);
                    $('#inputvalue').val(songid);
                    if (inputDisabled) $('#inputvalue').attr('disabled', true);
                    layer.msg('ID获取成功！提交下单即可');
                } else {
                    layer.alert(data.msg);
                    return false;
                }
            }
        });
    } else {
        try {
            if (songurl.indexOf('?p') >= 0) {
                var songid = songurl.split('video/')[1].split('?p')[0];
            } else {
                var songid = songurl.split('video/')[1].split('/')[0];
            }
            layer.msg('ID获取成功！提交下单即可');
            songid = biliChange(songid);
            $('#inputvalue').val(songid);
            if (inputDisabled) $('#inputvalue').attr('disabled', true);
        } catch (e) {
            layer.alert('请输入正确的视频链接！');
            return false;
        }
    }
}

function getBiliUserId() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！');
        return false;
    }
    if (songurl.indexOf('/') < 0 || songurl.indexOf('//') < 0) {
        layer.alert('请输入正确的主页链接！尾部带数字的');
        return false;
    }
    try {
        var reg = /\/([0-9]+)$/;
        var match = reg.exec(songurl);
        if (match != null && match.length >= 2) {
            songid = match[1];
            layer.msg('ID获取成功！提交下单即可');
            $('#inputvalue').val(songid);
            if (inputDisabled) $('#inputvalue').attr('disabled', true);
        } else {
            layer.alert('请输入正确的主页链接！');
            return false;
        }
    } catch (e) {
        layer.alert('请输入正确的主页链接！');
        return false;
    }
}

function getzuiyouid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！');
        return false;
    }
    if (songurl.indexOf('izuiyou.com') < 0) {
        layer.alert('请输入正确的帖子链接！');
        return false;
    }
    try {
        var songid = songurl.split('detail/')[1].split('?')[0];
        layer.msg('ID获取成功！提交下单即可');
    } catch (e) {
        layer.alert('请输入正确的帖子链接！');
        return false;
    }
    $('#inputvalue').val(songid);
}

function getmeipaiid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！');
        return false;
    }
    if (songurl.indexOf('meipai.com') < 0) {
        layer.alert('请输入正确的视频链接！');
        return false;
    }
    try {
        var songid = songurl.split('media/')[1].split('?')[0];
        layer.msg('ID获取成功！提交下单即可');
    } catch (e) {
        layer.alert('请输入正确的视频链接！');
        return false;
    }
    $('#inputvalue').val(songid);
}

function getquanminid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！');
        return false;
    }
    if (songurl.indexOf('http') < 0) {
        layer.alert('请输入正确的视频链接！');
        return false;
    }
    try {
        var songid = songurl.split('vid=')[1].split('&')[0];
        layer.msg('ID获取成功！提交下单即可');
    } catch (e) {
        layer.alert('请输入正确的视频链接！');
        return false;
    }
    $('#inputvalue').val(songid);
}

function getmeituid() {
    var songurl = $("#inputvalue").val();
    if (songurl == '') {
        layer.alert('请确保每项不能为空！');
        return false;
    }
    if (songurl.indexOf('meitu.com') < 0) {
        layer.alert('请输入正确的视频链接！');
        return false;
    }
    try {
        var songid = songurl.split('feed_id=')[1].split('&')[0];
        layer.msg('ID获取成功！提交下单即可');
    } catch (e) {
        layer.alert('请输入正确的视频链接！');
        return false;
    }
    $('#inputvalue').val(songid);
}

function getCommentList(id, aweme_id, km, page) {
    km = km || 0;
    page = page || 1;
    if (aweme_id == '') {
        layer.alert('请先填写抖音作品ID！');
        return false;
    }
    if (aweme_id.length != 19) {
        layer.alert('抖音作品ID填写错误');
        return false;
    }
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: "GET",
        url: "https://api.douyin.qlike.cn/api.php?act=GetCommentList&aweme_id=" + aweme_id + "&page=" + page,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.total != 0) {
                var addstr = '';
                $.each(data.comments, function (i, item) {
                    addstr += '<option value="' + item.cid + '">[昵称 => ' + item.user.nickname + '][内容 => ' + item.text + '][赞数量=>' + item.digg_count + ']</option>';
                });
                var nextpage = page + 1;
                var lastpage = page > 1 ? page - 1 : 1;
                if ($('#show_shuoshuo').length > 0) {
                    $('#show_shuoshuo').html('<div class="input-group"><div class="input-group-addon onclick" title="上一页" onclick="getCommentList(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + lastpage + ')"><i class="fa fa-chevron-left"></i></div><select id="shuoid" class="form-control" onchange="set_shuoshuo(\'' + id + '\');">' + addstr + '</select><div class="input-group-addon onclick" title="下一页" onclick="getCommentList(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + nextpage + ')"><i class="fa fa-chevron-right"></i></div></div>');
                } else {
                    if (km == 1) {
                        $('#km_inputsname').append('<div class="form-group" id="show_shuoshuo"><div class="input-group"><div class="input-group-addon onclick" title="上一页" onclick="getCommentList(\'' + id + '\',$(\'#km_inputvalue\').val(),' + km + ',' + lastpage + ')"><i class="fa fa-chevron-left"></i></div><select id="shuoid" class="form-control" onchange="set_shuoshuo(\'' + id + '\');">' + addstr + '</select><div class="input-group-addon onclick" title="下一页" onclick="getCommentList(\'' + id + '\',$(\'#km_inputvalue\').val(),' + km + ',' + nextpage + ')"><i class="fa fa-chevron-right"></i></div></div></div>');
                    } else {
                        $('#inputsname').append('<div class="form-group" id="show_shuoshuo"><div class="input-group"><div class="input-group-addon onclick" title="上一页" onclick="getCommentList(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + lastpage + ')"><i class="fa fa-chevron-left"></i></div><select id="shuoid" class="form-control" onchange="set_shuoshuo(\'' + id + '\');">' + addstr + '</select><div class="input-group-addon onclick" title="下一页" onclick="getCommentList(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + nextpage + ')"><i class="fa fa-chevron-right"></i></div></div></div>');
                    }
                }
                set_shuoshuo(id);
            } else {
                layer.alert('您的作品好像没人评论');
            }
        },
        error: function (a) {
            layer.close(ii);
            layer.alert('网络错误，请稍后重试');
        }
    });
}

function cuidan(orderid, status) {
    // body...
    if (status == 2 || status == 0) {
        if (!$.cookie('' + orderid)) {
            $.cookie('' + orderid, '' + orderid, 12 * 60 * 60);
            layer.alert("催单成功！已为您安排优先处理哦~");
        } else {
            layer.alert("该订单已经催过单了哦~");
        }
    } else if (status == 3) {
        layer.alert("催单失败！该订单异常请根据异常原因的说明操作！");
    } else {
        layer.alert("该订单的状态不可催单！");
    }
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
    $('#qq3').val(content);
    if ($("#tab-query").length > 0) {
        $("#tab-query").tab('show');
    }
    if (typeof (isModal) != undefined) {
        isModal = false;
    }
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
                queryVal = content;
                orderPage = page;
                if (typeof ($_GET['buyok']) != 'undefined' && $_GET['buyok'] == '1') {
                    setHistory('buyok=1&query=' + content + '&page=' + page);
                } else {
                    setHistory('query=' + content + '&page=' + page);
                }
                var status, orderid_1 = {},
                    x = 0;
                $('#list').append('<tr><td colspan="6"><font color="red">温馨提示：订单超过24小时仍待处理请联系客服哦~</font></td></tr>');
                var item = null;
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
                if ($_GET['buyok'] && !!orderid_1.id) {
                    showOrder(orderid_1.id, orderid_1.skey);
                } else if (orderid != null && data.data['order_' + orderid] && (is_showWork == true || is_orderWork == true)) {
                    showOrder(orderid, data.data['order_' + orderid].skey);
                } else {
                    if (x == 0) {
                        layer.alert('未查询到相关订单记录！<br>您可以点击查单处右侧的感叹号按钮获取查询帮助<br><i class="fa fa-exclamation-circle" style="color:red"></i> <span style="color:blue">建议您登陆本站后再查询或联系客服查询</span>');
                    }
                }
            } else {
                layer.alert(data.msg);
            }
            $('#submit_query').val('立即查询');
        }
    });
}

function inputOrder(id) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'GET',
        url: serverPath + 'ajax.php?act=order2&id=' + id,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.open({
                    title: '修改订单数据',
                    shade: 0.3,
                    shadeClose: false, //开启遮罩关闭
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

function checkInputName() {
    return;
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
        url: serverPath + "ajax.php?act=editOrder",
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

function showlist() {
    $("#display_list").show();
    $("#display_toolname").hide();
}

function showWorks(id) {
    layer.closeAll();
    $.ajax({
        type: "POST",
        url: serverPath + "ajax.php?act=workInfo",
        data: {
            orderid: id
        },
        dataType: "json",
        success: function (data) {
            if (data.code == -2) {
                is_orderWork = false;
                is_showWork = true;
                orderid = id;
                var index = layer.alert(data.msg, {
                    btn: ['现在登录', '现在注册', '取消操作'],
                    yes: function () {
                        $("#cmLoginModal").modal('show');
                        $('#tousu').modal("hide");
                        layer.close(index);
                    },
                    btn2: function () {
                        $("#cmRegModal").modal('show');
                        $('#tousu').modal("hide");
                        layer.close(index);
                    },
                    btn3: function () {
                        layer.close(index);
                    }
                });
            } else if (data.works > 0) {
                if ($("#con").length < 1) {
                    return false;
                }
                $("#con").html('');
                $("#work_orderid").val(id);
                $("#work_title").html('订单编号' + id + '与网站客服的沟通记录');
                $.each(data.data, function (index, res) {
                    $("#con").append('<div class="clearfloat"><div class="author-name"><small class="chat-date">' + res.addtime + '</small></div><div class="' + (res.isadmin == 0 ? 'right' : 'left') + '">' + (res.isadmin == 0 ? '<div class="chat-message" style="color:white;">' + res.content + '</div><div class="chat-avatars"><a href="javascript:void(0)"><img width="35" class="qqlogo" src="' + res.myimg + '" alt="头像"></a></div>' : '<div class="chat-avatars"><a href="javascript:void(0)"><img width="35" class="qqlogo" src="https://imgcache.qq.com/open_proj/proj_qcloud_v2/mc_2014/work-order/css/img/custom-service-avatar.svg" alt="头像"></a></div><div class="chat-message">' + res.content + '</div>') + '</div></div>')
                })
                if (undefined != data.ok && data.ok == 1) {
                    $("#work_ok").show();
                    $("#closeWorkInfo").show();
                    $("#huifuWork").hide();
                    $("#work_ok").html(data.info);
                    if (workBackCronObj.zt = true && typeof (workBackCronObj.workCron) == 'object') {
                        workBackCronObj.workCron.stop();
                        workBackCronObj.zt = false;
                    }
                } else {
                    $("#work_ok").hide();
                    $("#closeWorkInfo").hide();
                    $("#huifuWork").show();
                    if (!workBackCronObj.zt) {
                        workBackCronObj.workCron = Cron.run(workBackCron, 5000, "#work");
                        if (typeof (workBackCronObj.workCron) == 'object') {
                            workBackCronObj.zt = true;
                        }
                    }
                }
                $('#work').modal({
                    keyboard: false,
                    backdrop: 'static'
                });
                $("#work").css('display', 'block');
            } else {
                layer.alert(data.msg);
            }
        },
        error: function () {
            layer.alert('访问出错，请重试');
        }
    });
}

function showWorksInfo(id) {
    layer.closeAll();
    $.ajax({
        type: "POST",
        url: serverPath + "ajax.php?act=workInfo",
        data: {
            orderid: id
        },
        dataType: "json",
        success: function (data) {
            if ($("#con").length < 1) {
                return false;
            }
            if (data.works > 0) {
                $("#con").html('');
                $("#work_orderid").val(id);
                $("#work_title").html('订单编号' + id + '与网站客服的沟通记录');
                $.each(data.data, function (index, res) {
                    $("#con").append('<div class="clearfloat"><div class="author-name"><small class="chat-date">' + res.addtime + '</small></div><div class="' + (res.isadmin == 0 ? 'right' : 'left') + '">' + (res.isadmin == 0 ? '<div class="chat-message" style="color:white;">' + res.content + '</div><div class="chat-avatars"><a href="javascript:void(0)"><img width="35" class="qqlogo" src="' + res.myimg + '" alt="头像"></a></div>' : '<div class="chat-avatars"><a href="javascript:void(0)"><img width="35" class="qqlogo" src="https://imgcache.qq.com/open_proj/proj_qcloud_v2/mc_2014/work-order/css/img/custom-service-avatar.svg" alt="头像"></a></div><div class="chat-message">' + res.content + '</div>') + '</div></div>')
                })
                if (typeof (data.ok) != 'undefined' && data.ok == 1) {
                    $("#work_ok").show();
                    $("#closeWorkInfo").show();
                    $("#huifuWork").hide();
                    $("#work_ok").html(data.info);
                    if (typeof (workBackCronObj.workCron) == 'object') {
                        workBackCronObj.workCron.stop();
                    }
                    workBackCronObj.zt = false;
                } else {
                    $("#work_ok").hide();
                    $("#closeWorkInfo").hide();
                    $("#huifuWork").show();
                }
            }
        },
        error: function () {}
    });
}

function work(id, type) {
    layer.closeAll();
    if (type == 1) {
        showWorks(id);
        return false;
    } else {
        setTimeout(function () {
            $("#tousu_id").val(id);
            $('#tousu').modal("show");
        }, 500);
    }
}

function workBack() {
    var order_id = $("#work_orderid").val();
    var content = $("#work_content").val();
    if (!content || content == "") {
        return layer.alert("回复内容不能为空！");
    }
    var ii = layer.load();
    $.ajax({
        type: "POST",
        url: serverPath + "ajax.php?act=workBack",
        data: {
            orderid: order_id,
            content: content
        },
        dataType: "json",
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg("回复成功");
            } else if (data.code == -2) {
                orderid = id;
                var index = layer.alert(data.msg, {
                    btn: ['现在登录', '现在注册', '取消操作'],
                    yes: function () {
                        $("#cmLoginModal").modal('show');
                        $('#tousu').modal("hide");
                        layer.close(index);
                    },
                    btn2: function () {
                        $("#cmRegModal").modal('show');
                        $('#tousu').modal("hide");
                        layer.close(index);
                    },
                    btn3: function () {
                        layer.close(index);
                    }
                });
            } else {
                layer.alert(data.msg);
            }
        },
        error: function () {
            layer.close(ii);
            layer.alert('访问出错，请重试');
        }
    });
}

function tousuOrder() {
    var order_id = $("#tousu_id").val();
    var type = $("#tousu_type").val();
    var qq = $("#tousu_qq").val();
    var content = $("#tousu_content").val();
    if (content == "") {
        return layer.alert("问题描述不能为空！");
    } else if (qq == "") {
        return layer.alert("联系方式不能为空！");
    }
    var ii = layer.load();
    $.ajax({
        type: "POST",
        url: serverPath + "ajax.php?act=orderWork",
        data: {
            orderid: order_id,
            type: type,
            qq: qq,
            content: content
        },
        dataType: "json",
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                $('#tousu').modal("hide");
                var index = layer.alert('售后申请提交成功，耐心等待回复~<br>是否现在打开会话详情', {
                    btn: ['立即查看', '还是不了'],
                    yes: function () {
                        showWorks(order_id);
                    },
                    btn2: function () {
                        layer.close(index);
                    }
                });
            } else if (data.code == -2) {
                is_orderWork = true;
                is_showWork = false;
                orderid = order_id;
                var index = layer.alert(data.msg, {
                    btn: ['现在登录', '现在注册', '取消操作'],
                    yes: function () {
                        $("#cmLoginModal").modal('show');
                        $('#tousu').modal("hide");
                        layer.close(index);
                    },
                    btn2: function () {
                        $("#cmRegModal").modal('show');
                        $('#tousu').modal("hide");
                        layer.close(index);
                    },
                    btn3: function () {
                        layer.close(index);
                    }
                });
            } else if (data.code == -3) {
                is_orderWork = true;
                is_showWork = false;
                showWorks(order_id);
            } else {
                layer.alert(data.msg);
            }
        },
        error: function () {
            layer.close(ii);
            layer.alert("请求访问出错，请稍后重试或联系网站客服处理");
            return false;
        }
    });
}

function cm_login() {
    var username = $("#username").val();
    var password = $("#password").val();
    if ("" == username) {
        return layer.alert('登录账号不能为空！');
    } else if ("" == password) {
        return layer.alert('登录密码不能为空！');
    }
    var ii = layer.load(0, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: serverPath + 'ajax.php?act=login',
        data: 'user=' + username + '&pass=' + password,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 1) {
                layer.msg(data.msg);
                $('#cmLoginModal').modal("hide");
                if ((is_showWork == true || is_orderWork == true) && orderid != null) {
                    if (queryVal == "") {
                        queryVal = orderid;
                    }
                    queryOrder(1, queryVal, orderPage);
                }
            } else {
                layer.alert(data.msg);
            }
        },
        error: function (data) {
            layer.close(ii);
            layer.msg('服务器错误,请联系客服处理');
            return false;
        }
    });
}

function cm_reg() {
    var username = $("#reg_username").val();
    var password = $("#reg_password").val();
    var qq = $("#reg_qq").val();
    if ("" == username) {
        return layer.alert('要注册的登录账号不能为空！');
    } else if ("" == password) {
        return layer.alert('要注册的登录密码不能为空！');
    } else if ("" == qq) {
        return layer.alert('要注册的联系QQ不能为空！');
    }
    var data = {
        user: username,
        pwd: password,
        qq: qq,
    };
    if ($("#captcha_reg").length > 0) {
        var data2 = {
            geetest_challenge: $("input[name='geetest_challenge']").val(),
            geetest_validate: $("input[name='geetest_validate']").val(),
            geetest_seccode: $("input[name='geetest_seccode']").val(),
        };
        data = Object.assign(data, data2);
        layer.close(captcha_reg);
    }
    var ii = layer.load(0, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: serverPath + 'ajax.php?act=reg',
        data: data,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 1) {
                layer.msg(data.msg);
                $('#cmRegModal').modal("hide");
                if ((is_showWork == true || is_orderWork == true) && orderid != null) {
                    if (queryVal == "") {
                        queryVal = orderid;
                    }
                    queryOrder(1, queryVal, orderPage);
                }
            } else if (data.code == 2) {
                $.getScript("//static.geetest.com/static/tools/gt.js");
                captcha_reg = layer.open({
                    type: 1,
                    title: '注册验证-请完成拼图',
                    skin: 'layui-layer-rim',
                    area: ['320px', '100px'],
                    content: '<div id="captcha_reg"><div id="captcha_text">正在加载验证码</div><div id="captcha_wait"><div class="loading"><div class="loading-dot"></div><div class="loading-dot"></div><div class="loading-dot"></div><div class="loading-dot"></div></div></div></div>'
                });
                $.ajax({
                    url: "ajax.php?act=captcha&t=" + (new Date()).getTime(),
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
                        }, handlerEmbed_reg);
                    }
                });
            } else {
                layer.alert(data.msg);
            }
        },
        error: function (data) {
            layer.close(ii);
            layer.msg('服务器错误,请联系客服处理');
            return false;
        }
    });
}

function showOrder(id, skey) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
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
                var item = '<table class="table table-condensed table-hover">';
                item += '<tr><td colspan="6" style="text-align:center"><b>订单基本信息</b></td></tr><tr><td class="info" style="min-width:80px">订单编号</td><td colspan="5">' + id + '</td></tr><tr><td class="info" style="min-width:80px">商品名称</td><td colspan="5">' + data.name + '</td></tr><tr><td class="info" style="min-width:80px">订单金额</td><td colspan="5">' + data.money + '元</td></tr><tr><td class="info" style="min-width:80px">购买时间</td><td colspan="5">' + data.date + '</td></tr><tr><td class="info" style="min-width:80px">下单信息</td><td colspan="5">' + data.inputs + '</td><tr><td class="info" style="min-width:80px">订单状态</td><td colspan="5">' + orderStatus(data.status, data.is_curl) + '</td></tr>';
                if (data.status == 1 && data.is_curl != 2 && data.show_endtime && data.endtime) {
                    item += '<tr><tr><td class="info" style="min-width:80px">更新时间</td><td colspan="5">' + data.endtime + '</td><tr>';
                } else if (data.show_usetime && data.show_usetime == 1 && data.usetime != "") {
                    item += '<tr><tr><td class="info" style="min-width:80px">处理耗时</td><td colspan="5"><span style="background-color:#42a1ff;padding:4px 6px;border-radius:5px" id="order_usetime">0小时0分0秒</span></td><tr>';
                }
                if (typeof (data.kminfo) == "string" && data.kminfo) {
                    item += '<tr><td colspan="6" style="text-align:center"><b>以下是你的卡密信息</b></td><tr><td colspan="6">' + data.kminfo + '</td></tr>';
                } else {
                    if (data.list && 'object' == typeof data.list) {
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
                if (data.complain) {
                    if (data.works) {
                        item += '<tr style="padding-right: 0;"><td colspan="6"><a class="mdui-btn mdui-btn-block mdui-color-pink mdui-ripple" onclick="showWorks(\'' + id + '\');">查看沟通记录</a></td></tr>';
                    } else {
                        if (!data.works) data.works = 0;
                        item += '<tr style="padding-right: 0;"><td colspan="6"><a class="mdui-btn mdui-btn-block mdui-color-pink mdui-ripple" onclick="work(\'' + id + '\',' + (data.works.length > 0 ? 1 : 0) + ');">申请订单售后</a></td></tr>';
                    }
                }

                if (!!data.show_desc && typeof (data.desc) == "string" && data.desc != "") {
                    item += '<tr><td colspan="6" style="text-align:center"><b>商品简介</b></td><tr><td colspan="6" style="white-space: normal;">' + data.desc + '</td></tr>';
                }
                item += '</table>';
                if ($("#showOrder").length > 0) {
                    $("#showOrder_content").html(item);
                    $("#showOrder").modal('show');
                } else {
                    var area = [$(window).width() > 640 ? '540px' : '97%', 'auto'];
                    layer.open({
                        type: 1,
                        title: ['请收藏本站网址到浏览器书签-方便查单', 'color:red'],
                        area: area,
                        content: item,
                        scrollbar: false,
                        btn: '关闭窗口',
                        success: function (layero, index) {
                            if (data.show_usetime && data.usetime && data.usetime > 0) {
                                getUseTime(data.usetime, "#order_usetime");
                            }
                            //重定义高度
                            var lh = $(layero).height();
                            var wh = $(window).height();
                            if (lh > wh) {
                                layer.style(index, {
                                    top: '20px',
                                    bottom: '12px',
                                    height: (wh - 32) + 'px'
                                });
                                var el = $(layero).children('.layui-layer-content');
                                var el2 = $(layero).children('.layui-layer-title');
                                var el3 = $(layero).children('.layui-layer-btn');
                                el.css('height', (wh - el2.outerHeight() - el3.outerHeight() - 32) + 'px');
                            }
                        }
                    });
                }
                if (data.complain && orderid != null) {
                    if (is_orderWork == true) {
                        work(orderid, data.works.length > 0 ? 1 : 0);
                    } else if (is_showWork == true) {
                        showWorks(orderid);
                    }
                }
            } else {
                layer.alert(data.msg);
            }
        }
    });
}
var $timestamp, obj, toTime;
var runUseTime = function () {
    if ($(obj).length < 1) {
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
var handlerEmbed_reg = function (captchaObj) {
    captchaObj.appendTo('#captcha_reg');
    captchaObj.onReady(function () {
        $("#captcha_wait").hide();
    }).onSuccess(function () {
        cm_reg();
    });
};
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
                    if ($('#inputname').html() == '你的邮箱') {
                        $.cookie('email', inputvalue);
                    }
                    layer.alert(data.msg, {
                        end: function () {
                            window.location.href = '?buyok=1';
                        }
                    });
                } else {
                    layer.alert(data.msg);
                    captchaObj.reset();
                }
            }
        });
    });
};

function toTool(cid, tid) {
    setHistory('cid=' + cid + '&tid=' + tid);
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
                var ii2 = layer.msg('正在提交订单请稍候...', {
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
                        layer.close(ii2);
                        if (data.code == 1) {
                            alert(data.msg);
                            window.location.href = serverPath + filename + '?buyok=1';
                        } else if (data.code == 2) {
                            alert(data.msg);
                            window.location.href = serverPath + '?mod=faka&id=' + data.orderid + '&skey=' + data.skey;
                        } else if (data.code == -2) {
                            alert(data.msg);
                            window.location.href = serverPath + filename + '?buyok=1';
                        } else if (data.code == -3) {
                            var confirmobj = layer.confirm('你的余额不足，请充值！', {
                                btn: ['立即充值', '取消']
                            }, function () {
                                window.location.href = serverPath + 'user/index.php#chongzhi';
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
                window.location.href = serverPath + 'other/submit.php?type=' + data.type + '&orderid=' + data.orderid;
            }
        },
        error: function () {
            layer.close(ii);
            layer.alert("服务器错误，请稍后重试或联系网站客服！");
        }
    });
}

function cancel(id) {
    layer.closeAll();
    $.ajax({
        type: "POST",
        url: serverPath + "ajax.php?act=cancel",
        data: {
            orderid: id,
            hashsalt: hashsalt
        },
        dataType: 'json',
        async: true,
        success: function (data) {
            if (data.code == 0) {} else {
                layer.closeAll();
            }
        },
        error: function (data) {
            window.location.reload();
        }
    });
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
                        if ($('#inputvalue').val() != data.videoid) {
                            $('#inputvalue2').val(data.videoid);
                        }
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
    var name = $('#tid option:selected').html();
    var gettype = $("#inputvalue").attr('gettype');
    if (typeof (gettype) == 'undefined' || gettype == '' || gettype != '!shareurl') {
        inputFilter(value);
    }
    if (typeof (name) != 'undefined' && name != 'null') {} else {
        name = '';
    }
    if (title == '歌曲ID' || title == '歌曲ＩＤ' || title == '全民K歌歌曲链接' || title == '歌曲链接' || title == 'K歌歌曲链接') {
        if (value.indexOf("s=") == (-1)) {
            if (value.length != 12 && value.length != 16) {
                layer.msg('请输入正确的K歌作品链接，会自动获取哦！');
                return false;
            }
        } else if (value != '') {
            getsongid();
        }
    } else if (title == '绿洲ID' || title == '绿洲作品ID' || title == '绿洲作品链接') {
        if (value != '' && value.indexOf('http') >= 0) {
            getlvzhouid();
        }
    } else if (title == '微视ID' || title == '微视作品ID' || title == '微视作品ID' || title == '微视作品链接' || title == '微视ＩＤ') {
        if (value != '' && value.indexOf('http') >= 0) {
            getweishiid();
        }
    } else if (title == '微视主页链接' || title == '微视主页ID') {
        if (value != '' && value.indexOf('http') >= 0) {
            getwsUserid();
        }
    } else if (title == '美拍ID' || title == '美拍ＩＤ' || title == '美拍作品ID' || title == '美拍作品ID' || title == '美拍视频ID' || title == '美拍作品链接') {
        if (value != '' && value.indexOf('http') >= 0) {
            getmeipaiid();
        }
    } else if (title == '最右帖子ID') {
        if (value != '' && value.indexOf('http') >= 0) {
            getzuiyouid();
        }
    } else if (title == '全民视频ID' || title == '全民小视频ID') {
        if (value != '' && value.indexOf('http') >= 0) {
            getquanminid();
        }
    } else if (title == '美图作品ID' || title == '美图视频ID') {
        if (value != '' && value.indexOf('http') >= 0) {
            getmeituid();
        }
    } else {
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
}

function checklogin(islogin) {
    if (islogin == 1) {
        return true;
    } else {
        var confirmobj = layer.confirm('为方便反馈处理结果，投诉订单前请先登录网站！', {
            btn: ['登录', '注册', '取消']
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
            console.error('音乐播放: ' + (className.indexOf('on') > -1 ? '已暂停' : '已播放'));
            (className.indexOf('on') > -1) ? $(target).removeClass('on').addClass('off') : $(target).removeClass('off').addClass('on');
            (className.indexOf('on') > -1) ? ids.pause() : ids.play();
        } else {
            console.error('音乐播放: 未找到节点');
        }
    },
    play: function () {
        document.getElementById('media').play();
    }
}

function numChange(el) {
    var value = parseInt($(el).val());
    var rule = $(el).attr('extendBlurAttr');
    if (!isNaN(rule) && value % rule == 0) {
        var num = getAllTimes();
        var cost = $(el).attr('price');
        var price = cost * num;
        $('#tid option:selected').attr('price', price.toFixed(2));
    } else {
        var tips = (rule * 1) + '、' + (rule * 2) + '、' + (rule * 4);
        layer.alert('此项内容必须是' + rule + '的整倍数，如' + tips + '等！');
    }
    $("#num").keyup();
}

function getTimes(el) {
    var value = parseInt($(el).val());
    var rule = $(el).attr('extendBlurAttr');
    if (!!rule && !isNaN(rule) && value % rule == 0) {
        var num = parseInt(value / rule);
        return num;
    }
    return 1;
}

function getAllTimes() {
    var els = $('.input');
    var count = 1;
    for (var i = 0; i < els.length; i++) {
        count = count * getTimes(els[i]);
    }
    //console.log('共' + count + '倍');
    return count;
}

function getAllCount() {
    var els = $('.input');
    var count = 1;
    for (var i = 0; i < els.length; i++) {
        var rule = parseInt($(els[i]).attr('extendBlurAttr'));
        if (!isNaN(rule) && rule > 0) {
            count = count * rule;
        }
    }
    return count;
}

function backfl() {
    if ($("#goodType").length > 0) {
        $("#goodTypeContent").hide();
        $("#goodType").show();
    }
    $("#display_selectclass").show();
    $("#doSearch").show();
    $("#backfl").remove();
}

function submit_captcha() {
    $("#submit_buy").click();
}

$(document).ready(function () {
    if ($("head").length > 0) {
        $("head").append('<style>.layui-layer-btn{display:box;display:-moz-box;display:-webkit-box;width:100%;height: auto;line-height:50px;}.layui-layer-btn a{display:block;-moz-box-flex:1;box-flex:1;-webkit-box-flex:1;font-size:14px;cursor:pointer;margin:5px 6px 0;padding:2px 18px;height: auto;text-align: center;}</style>')
    } else {
        $("body").append('<style>.layui-layer-btn{display:box;display:-moz-box;display:-webkit-box;width:100%;height: auto;line-height:50px;}.layui-layer-btn a{display:block;-moz-box-flex:1;box-flex:1;-webkit-box-flex:1;font-size:14px;cursor:pointer;margin:5px 6px 0;padding:2px 18px;height: auto;text-align: center;}</style>')
    }
    $("#inputvalue").blur(function () {
        checkInput();
    });
    if ($("#inputvalue2").length > 0) {
        $("#inputvalue2").blur(function () {
            checkInput();
        });
    }
    $('.goodTypeChange').click(function () {
        var id = $(this).data('id');
        var img = $(this).data('img');
        setHistory('cid=' + id);
        $("#cid").val(id);
        $("#cid").change();
        $("#goodType").hide('normal');
        $("#goodTypeContent").show('normal');
    });
    $(".nav-tabs,.backType").click(function () {
        history.replaceState({}, null, './');
        $("#goodType").show('normal');
        $("#goodTypeContent").hide('normal');
    })
    $("#showSearchBar").click(function () {
        $("#display_selectclass").slideToggle();
        $("#display_searchBar").slideToggle();
        $("#display_selectclass_sub").hide();
    });
    $("#closeSearchBar").click(function () {
        $("#display_searchBar").slideToggle();
        $("#display_selectclass").slideToggle();
    });
    $("#doSearch").click(function () {
        var kw = $("#searchkw").val();
        if (kw == '') {
            $("#closeSearchBar").click();
            return layer.alert("输入搜索关键词！例如：名片赞");
        }
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $("#tid").empty();
        $("#tid").append('<option value="0">点我选择搜索商品</option>');
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
                        if (typeof (res.stock_id) != 'string' && typeof (res.stock_id) != 'number') {
                            res.stock_id = '0';
                        }
                        $("#tid").append('<option stock_id="' + res.stock_id + '" stock_open="' + res.stock_open + '" stock="' + res.stock + '" value="' + res.tid + '" cid="' + res.cid + '" price="' + res.price + '" unit="' + res.unit + '" shopimg="' + res.shopimg + '" desc="' + encodeURI(res.desc) + '" alert="' + encodeURI(res.alert) + '" close_alert="' + encodeURI(res.close_alert) + '"  inputname="' + res.input + '" inputsname="' + res.inputs + '" multi="' + res.multi + '" isfaka="' + res.isfaka + '" count="' + res.value + '" active="' + res.active + '" prices="' + res.prices + '" max="' + res.max + '" min="' + res.min + '" name="' + res.name + '">' + res.name + '</option>');
                        num++;
                    });
                    $("#tid").val(0);
                    getPoint();
                    if (num == 0 && cid != 0) {
                        layer.alert('没有搜索到相关商品，换个词试试？');
                        $("#tid").html('<option value="0">没有搜索到相关商品</option>');
                        return false;
                    } else {
                        $("#doSearch").hide();
                        if ($("#backfl").length < 1) {
                            $("#display_searchBar").children().append('<div class="input-group-addon" title="返回分类" id="backfl" onclick="backfl()">返回分类</div>');
                        }
                        if ($("#goodType").length > 0) {
                            $("#goodTypeContent").show();
                            $("#goodType").hide();
                        }
                        $("#display_selectclass").hide();
                        if ($("#display_selectclass_sub").length > 0) $("#display_selectclass_sub").hide();
                        $("#display_tool").show();
                        layer.msg('共搜索到' + num + '个商品');
                    }
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg('加载失败，请刷新重试');
                return false;
            }
        });
    });
    $("#cid").change(function () {
        if (typeof (noLoad) != 'undefined' && noLoad) {
            getPoint();
            return true;
        }
        var cid = $(this).val();
        if (cid < 1 && $_GET["cid"]) cid = $_GET["cid"];
        if (cid < 1) {
            console.log("当前选中分类为0或不存在！！");
            $("#tid").empty();
            if (tool_show && tool_show == 1) {
                $("#tid").append('<option value="0">请先选择分类</option>');
            }
            $("#cid").val(0);
            getPoint();
            return false;
        }
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
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
                        if (typeof (data.upcid) != 'undefined' && data.upcid != null && data.upcid > 0) {
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
                            $("#tid").append('<option stock_id="' + res.stock_id + '" stock_open="' + res.stock_open + '" unit="' + res.unit + '" stock="' + res.stock + '" value="' + res.tid + '" cid="' + res.cid + '" price="' + res.price + '" shopimg="' + res.shopimg + '" desc="' + encodeURI(res.desc) + '" alert="' + encodeURI(res.alert) + '" close_alert="' + encodeURI(res.close_alert) + '"  inputname="' + res.input + '" inputsname="' + res.inputs + '" multi="' + res.multi + '" isfaka="' + res.isfaka + '" count="' + res.value + '" active="' + res.active + '" prices="' + res.prices + '" max="' + res.max + '" min="' + res.min + '" name="' + res.name + '">' + res.name + '</option>');
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
                            layer.alert('该分类下没有商品,请重新选择');
                            $("#tid").html('<option value="0">该分类下没有商品</option>');
                        } else {
                            // layer.msg("共" + num + '个商品 请选择商品哦', {
                            //     icon: 1,
                            //     time: 1500
                            // });
                            layer.msg('请选择商品哦', {
                                icon: 1,
                                time: 1500
                            });
                        }
                        getPoint();
                    }
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg('加载失败，请刷新重试');
                return false;
            }
        });
    });
    $("#sub_cid").change(function () {
        var sub_cid = $(this).val();
        if (sub_cid < 1) {
            $("#tid").empty();
            $("#display_tool").hide();
            getPoint();
            return false;
        }
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $("#tid").empty();
        $("#tid").append('<option value="0">请选择商品</option>');
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
                        $("#tid").append('<option stock_id="' + res.stock_id + '" stock_open="' + res.stock_open + '" stock="' + res.stock + '" value="' + res.tid + '" cid="' + res.cid + '" price="' + res.price + '" unit="' + res.unit + '" shopimg="' + res.shopimg + '" desc="' + encodeURI(res.desc) + '" alert="' + encodeURI(res.alert) + '" close_alert="' + encodeURI(res.close_alert) + '"  inputname="' + res.input + '" inputsname="' + res.inputs + '" multi="' + res.multi + '" isfaka="' + res.isfaka + '" count="' + res.value + '" active="' + res.active + '" prices="' + res.prices + '" max="' + res.max + '" min="' + res.min + '" name="' + res.name + '">' + res.name + '</option>');
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
                        layer.alert('该分类下没有商品,请重新选择');
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
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg('加载失败，请刷新重试');
                return false;
            }
        });
    });
    $("#submit_batch").click(function () {
        var tid = $("#tid").val();
        if (typeof (tid) == 'undefined' || tid == 0 || tid == null || ($("#display_tool").length > 0 && $("#display_tool").attr('display') == "none")) {
            if ($(this).html() == "立即免费领取") {
                layer.alert('选择分类->选择商品->填写信息->领取即可');
            } else {
                layer.alert('请先选择分类->选择商品再操作！');
            }
            return false;
        }
        var batch_length = $("#batch_length").val();
        var batch_text = $("#batch_text").val();
        var arr1 = batch_text.split("\n");
        var data = [],
            error = '';
        $.each(arr1, function (index, item) {
            if (item.indexOf('-') >= 0) {
                var arr2 = item.split("-");
                var num = parseInt(arr2[1]);
                var arr3 = arr2[0].split("|");
            } else {
                var arr3 = item.split("|");
                var num = 1;
            }
            if (isNaN(num)) {
                num = 1;
            }
            if (arr3.length < batch_length || arr3[0] == '') {
                error = '第' + (index + 1) + '行数据不完整，请检查是否有' + batch_length + '个有效数据';
                return false;
            }
            data.push({
                data: arr3,
                num: num / 1
            });
        });
        if (error != '') {
            layer.alert(error);
            return false;
        } else if (data.length < 1) {
            layer.alert('请确保至少有一行下单数据！');
            return false;
        }
        console.log(data);
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=cart_batch",
            data: {
                tid: tid,
                data: data,
                stock_id: $("#tid option:selected").attr('stock_id'),
                hashsalt: hashsalt
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg(data.msg);
                } else {
                    layer.alert(data.msg);
                }
            }
        });
    });
    $("#submit_buy").click(function () {
        var tid = $("#tid").val();
        if (typeof (tid) == 'undefined' || tid == 0 || tid == null || ($("#display_tool").length > 0 && $("#display_tool").attr('display') == "none")) {
            if ($(this).html() == "立即免费领取") {
                layer.alert('选择分类->选择商品->填写信息->领取即可');
            } else {
                layer.alert('请先选择分类->选择商品再操作！');
            }
            return false;
        }
        var inputvalue = $("#inputvalue").val();

        if (inputvalue == '' || tid == '') {
            layer.alert('请确保每项不能为空！');
            return false;
        }
        if ($("#inputvalue2").val() == '' || $("#inputvalue3").val() == '' || $("#inputvalue4").val() == '' || $("#inputvalue5").val() == '') {
            layer.alert('请确保每项不能为空！');
            return false;
        }
        if (($('#inputname').html() == '下单ＱＱ' || $('#inputname').html() == 'ＱＱ账号' || $("#inputname").html() == 'QQ账号') && (inputvalue.length < 5 || inputvalue.length > 11 || isNaN(inputvalue))) {
            layer.alert('请输入正确的QQ号！');
            return false;
        }
        var reg = new RegExp('^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+', '');
        if ($('#inputname').html() == '你的邮箱' && !reg.test(inputvalue)) {
            layer.alert('邮箱格式不正确！');
            return false;
        }
        var reg2 = new RegExp('^[1][0-9]{10}$', '');
        if ($('#inputname').html() == '手机号码' && !reg2.test(inputvalue)) {
            layer.alert('手机号码格式不正确！');
            return false;
        }
        if ($("#inputname2").html() == '说说ID' || $("#inputname2").html() == '说说ＩＤ') {
            if ($("#inputvalue2").val().length != 24) {
                layer.alert('说说必须是原创说说！');
                return false;
            }
        }
        if ($("#inputname2").html() == '作品ID' || $("#inputname2").html() == '作品ＩＤ') {
            if ($("#inputvalue2").val() != '' && $("#inputvalue2").val().indexOf('http') >= 0) {
                $("#inputvalue").val($("#inputvalue2").val());
                get_kuaishou('inputvalue2', $('#inputvalue').val());
            }
        }
        if ($("#inputname").html() == '抖音作品ID' || $("#inputname").html() == '火山作品ID' || $("#inputname").html() == '火山直播ID') {
            if ($("#inputvalue").val().length != 19) {
                layer.alert('您输入的作品ID有误！');
                return false;
            }
        }
        if ($("#inputname2").html() == '抖音评论ID') {
            if ($("#inputvalue2").val().length != 19) {
                layer.alert('您输入的评论ID有误！请点击自动获取手动选择评论！');
                return false;
            }
        }

        var code = $("#code").val();

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
                code: code ? code : '',
                hashsalt: hashsalt
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    if ($('#inputname').html() == '你的邮箱') {
                        $.cookie('email', inputvalue);
                    }
                    var paymsg = '<center><h4 style="font-size: 14px;">订单号：' + data.trade_no + '</h4><h2 style="color:red;">￥ ' + data.need + '</h2>';
                    if (data.pay_alipay > 0) {
                        paymsg += '<button class="btn btn-default btn-block" onclick="dopay(\'alipay\',\'' + data.trade_no + '\')" style="margin-top:10px;"><img width="20" src="' + serverPath + 'assets/icon/alipay.ico" class="logo">支付宝</button>';
                    }
                    if (data.pay_qqpay > 0) {
                        paymsg += '<button class="btn btn-default btn-block" onclick="dopay(\'qqpay\',\'' + data.trade_no + '\')" style="margin-top:10px;"><img width="20" src="' + serverPath + 'assets/icon/qqpay.ico" class="logo">QQ钱包</button>';
                    }
                    if (data.pay_wxpay > 0) {
                        paymsg += '<button class="btn btn-default btn-block" onclick="dopay(\'wxpay\',\'' + data.trade_no + '\')" style="margin-top:10px;"><img width="20" src="' + serverPath + 'assets/icon/wechat.ico" class="logo">微信支付</button>';
                    }
                    if (data.pay_rmb > 0) {
                        paymsg += '<button class="btn btn-success btn-block" onclick="dopay(\'rmb\',\'' + data.trade_no + '\')">使用余额支付（剩' + data.user_rmb + '元）</button>';
                    }
                    if (!!data.pay_alert && typeof data.pay_alert === "string" && data.pay_alert != "") {
                        paymsg += '<p>' + data.pay_alert + '</p>';
                    }
                    layer.alert(paymsg + '<hr><a class="btn btn-default btn-block" onclick="cancel(\'' + data.trade_no + '\')">取消订单</a></center>', {
                        btn: [],
                        title: '提交订单成功',
                        closeBtn: false
                    });
                } else if (data.code == 1) {
                    $('#alert_frame').hide();
                    if ($('#inputname').html() == '你的邮箱') {
                        $.cookie('email', inputvalue);
                    }
                    layer.alert(data.msg, {
                        end: function () {
                            window.location.href = '?buyok=1';
                        }
                    });
                } else if (data.code == 2 || data.code == 4) {
                    if (data.code == 2) {
                        // 极致验证
                        $.getScript("//static.geetest.com/static/tools/gt.js", function (response, status) {
                            if (status == 'success') {
                                layer.open({
                                    type: 1,
                                    title: '完成验证',
                                    skin: 'layui-layer-rim',
                                    area: [$(window).width() > 640 ? '320px' : '95%', '220px'],
                                    content: '<div id="captcha"><div id="captcha_text">正在加载验证码</div><div id="captcha_wait"><div class="loading"><div class="loading-dot"></div><div class="loading-dot"></div><div class="loading-dot"></div><div class="loading-dot"></div></div></div></div>'
                                });
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
                            } else {
                                if (status == 'timeout') {
                                    layer.alert('验证模块加载超时，请刷新页面后再试！')
                                    return;
                                }
                                layer.alert('验证模块加载失败，请刷新页面后再试！')
                            }
                        });
                    }
                    else {
                        layer.closeAll();
                        layer.open({
                            type: 1,
                            title: '完成验证',
                            skin: 'layui-layer-rim',
                            area: [$(window).width() > 640 ? '360px' : '95%', '225px'],
                            content: '<div class="panel-body"><div class="form-group"> <div class="input-group"> <div class="input-group-addon"><span class="fa fa-fw fa-adjust"></span></div> <input type="text" name="code" id="code" class="form-control input-lg" required="required" placeholder="输入验证码"/> <span class="input-group-addon" style="padding: 0"> <img id="codeimg" src="./ajax.php?act=captcha&r=' + Math.random() + '" height="43" onclick="this.src=\'./ajax.php?act=captcha&r=' + Math.random() + '\'" title="点击更换验证码"></span> </div> </div><div class="form-group"><input type="button" value="提交验证" onclick="submit_captcha()" style="    margin: 0;" class="btn btn-danger btn-block"/></div></div>'
                        });
                    }
                } else if (data.code == 3) {
                    layer.alert(data.msg, {
                        closeBtn: false
                    }, function () {
                        window.location.reload();
                    });
                } else if (data.code == 4) {
                    var confirmobj = layer.confirm('请登录后再购买，是否现在登录？', {
                        btn: ['登录', '注册', '取消']
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
            },
            error: function (req, err, data) {
                layer.close(ii);
                layer.alert('服务器错误，请稍后重试：' + data);
            }
        });
    });
    $("#submit_cart_shop").click(function () {
        var tid = $("#tid").val();
        if (tid == 0 || tid == null || tid == undefined || $("#display_tool").attr('display') == "none") {
            layer.alert('选择分类-选择商品-填写信息-加入购物车');
            return false;
        }
        var inputvalue = $("#inputvalue").val();
        if (inputvalue == '' || tid == '') {
            layer.alert('请确保每项不能为空！');
            return false;
        }
        if ($("#inputvalue2").val() == '' || $("#inputvalue3").val() == '' || $("#inputvalue4").val() == '' || $("#inputvalue5").val() == '') {
            layer.alert('请确保每项不能为空！');
            return false;
        }
        if (($('#inputname').html() == '下单ＱＱ' || $('#inputname').html() == 'ＱＱ账号' || $("#inputname").html() == 'QQ账号') && (inputvalue.length < 5 || inputvalue.length > 11 || isNaN(inputvalue))) {
            layer.alert('请输入正确的QQ号！');
            return false;
        }
        var reg = new RegExp('^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+', '');
        if ($('#inputname').html() == '你的邮箱' && !reg.test(inputvalue)) {
            layer.alert('邮箱格式不正确！');
            return false;
        }
        reg = new RegExp('^[1][0-9]{10}$', '');
        if ($('#inputname').html() == '手机号码' && !reg.test(inputvalue)) {
            layer.alert('手机号码格式不正确！');
            return false;
        }
        if ($("#inputname2").html() == '说说ID' || $("#inputname2").html() == '说说ＩＤ') {
            if ($("#inputvalue2").val().length != 24) {
                layer.alert('说说必须是原创说说！');
                return false;
            }
        }
        checkInput();
        if ($("#inputname2").html() == '作品ID' || $("#inputname2").html() == '作品ＩＤ') {
            if ($("#inputvalue2").val() != '' && $("#inputvalue2").val().indexOf('http') >= 0) {
                $("#inputvalue").val($("#inputvalue2").val());
                get_kuaishou('inputvalue2', $('#inputvalue').val());
            }
        }
        if ($("#inputname").html() == '抖音作品ID' || $("#inputname").html() == '火山作品ID' || $("#inputname").html() == '火山直播ID') {
            if ($("#inputvalue").val().length != 19) {
                layer.alert('您输入的作品ID有误！');
                return false;
            }
        }
        if ($("#inputname2").html() == '抖音评论ID') {
            if ($("#inputvalue2").val().length != 19) {
                layer.alert('您输入的评论ID有误！请点击自动获取手动选择评论！');
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
                        closeBtn: false
                    }, function () {
                        window.location.reload();
                    });
                } else if (data.code == 4) {
                    var confirmobj = layer.confirm('请登录后再购买，是否现在登录？', {
                        btn: ['登录', '注册', '取消']
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
    $("#submit_checkkm").click(function () {
        var km = $("#km").val();
        if (km == '') {
            layer.alert('请确保卡密不能为空！');
            return false;
        }
        $('#submit_km').val('Loading');
        $('#km_show_frame').hide();
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=checkkm",
            data: {
                km: km
            },
            dataType: 'json',
            success: function (data) {
                if (data.code == 0) {
                    if (data.close == 1) {
                        layer.alert('当前商品维护中，停止下单！');
                        $('#submit_checkkm').val('检查卡密');
                        return false;
                    }
                    $('#submit_checkkm').hide();
                    $('#km').attr("disabled", true);
                    $('#km_tid').val(data.tid);
                    $('#km_name').val(data.name);
                    if (data.msg != '') {
                        $('#km_alert_frame').show();
                        $('#km_alert_frame').html(data.msg);
                    } else {
                        $('#km_alert_frame').hide();
                    }
                    $('#km_inputsname').html("");
                    var inputname = data.inputname;
                    if (inputname != '') {
                        $('#km_inputsname').append('<div class="form-group"><div class="input-group"><div class="input-group-addon" id="km_inputname">' + inputname + '</div><input type="text" name="inputvalue" id="km_inputvalue" value="' + ($_GET['qq'] ? $_GET['qq'] : '') + '" class="form-control" required/></div></div>');
                    } else {
                        $('#km_inputsname').append('<div class="form-group"><div class="input-group"><div class="input-group-addon" id="km_inputname">下单ＱＱ</div><input type="text" name="inputvalue" id="km_inputvalue" value="' + ($_GET['qq'] ? $_GET['qq'] : '') + '" class="form-control" required/></div></div>');
                    }
                    var inputsname = data.inputsname;
                    if (inputsname != '') {
                        $.each(inputsname.split('|'), function (i, value) {
                            if (value == '说说ID' || value == '说说ＩＤ') var addstr = '<div class="input-group-addon onclick" onclick="get_shuoshuo(\'km_inputvalue' + (i + 2) + '\',$(\'#km_inputvalue\').val(),1)">自动获取</div>';
                            else if (value == '日志ID' || value == '日志ＩＤ') var addstr = '<div class="input-group-addon onclick" onclick="get_rizhi(\'km_inputvalue' + (i + 2) + '\',$(\'#km_inputvalue\').val(),1)">自动获取</div>';
                            else var addstr = '';
                            $('#km_inputsname').append('<div class="form-group"><div class="input-group"><div class="input-group-addon" id="km_inputname">' + value + '</div><input type="text" name="inputvalue' + (i + 2) + '" id="km_inputvalue' + (i + 2) + '" value="" class="form-control" required/>' + addstr + '</div></div>');
                        });
                    }
                    $("#km_show_frame").slideDown();
                    if (data.alert != '' && data.alert != 'null') {
                        var ii = layer.alert(data.alert, {
                            btn: ['我知道了'],
                            title: '商品提示',
                            closeBtn: false
                        }, function () {
                            layer.close(ii);
                        });
                    }
                } else {
                    layer.alert(data.msg);
                }
                $('#submit_checkkm').val('检查卡密');
            }
        });
    });
    $("#submit_card").click(function () {
        var km = $("#km").val();
        var inputvalue = $("#km_inputvalue").val();
        if (inputvalue == '' || km == '') {
            layer.alert('请确保每项不能为空！');
            return false;
        }
        if ($("#km_inputvalue2").val() == '' || $("#km_inputvalue3").val() == '' || $("#km_inputvalue4").val() == '' || $("#km_inputvalue5").val() == '') {
            layer.alert('请确保每项不能为空！');
            return false;
        }
        if ($('#km_inputname').html() == '下单ＱＱ' && (inputvalue.length < 5 || inputvalue.length > 11)) {
            layer.alert('请输入正确的QQ号！');
            return false;
        }
        if ($("#km_inputname2").html() == '说说ID' || $("#km_inputname2").html() == '说说ＩＤ') {
            if ($("#km_inputvalue2").val().length != 24) {
                layer.alert('说说必须是原创说说！');
                return false;
            }
        }
        $('#submit_card').val('Loading');
        $('#result1').hide();
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=card",
            data: {
                km: km,
                inputvalue: inputvalue,
                inputvalue2: $("#km_inputvalue2").val(),
                inputvalue3: $("#km_inputvalue3").val(),
                inputvalue4: $("#km_inputvalue4").val(),
                inputvalue5: $("#km_inputvalue5").val()
            },
            dataType: 'json',
            success: function (data) {
                if (data.code == 0) {
                    alert(data.msg);
                    window.location.href = '?buyok=1';
                } else {
                    layer.alert(data.msg);
                }
                $('#submit_card').val('立即购买');
            }
        });
    });
    $("#submit_query").click(function () {
        var qq = $("#qq3").val();
        var type = $("#searchtype").val();
        queryOrder(type, qq, 1);
    });
    $("#submit_lqq").click(function () {
        var qq = $("#qq4").val();
        if (qq == '') {
            layer.alert('QQ号不能为空！');
            return false;
        }
        if (qq.length < 5 || qq.length > 11) {
            layer.alert('请输入正确的QQ号！');
            return false;
        }
        $('#result3').hide();
        if ($.cookie('lqq') && $.cookie('lqq').indexOf(qq) >= 0) {
            $('#result3').html('<div class="alert alert-success"><img src="/assets/img/ico_success.png">&nbsp;该QQ已经提交过，请勿重复提交！</div>');
            $("#result3").slideDown();
            return false;
        }
        $('#submit_lqq').val('Loading');
        $.ajax({
            type: "POST",
            url: serverPath + "ajax.php?act=lqq",
            data: {
                qq: qq,
                salt: hashsalt
            },
            dataType: 'json',
            success: function (data) {
                if ($.cookie('lqq')) {
                    $.cookie('lqq', $.cookie('lqq') + '-' + qq);
                } else {
                    $.cookie('lqq', qq);
                }
                $('#result3').html('<div class="alert alert-success"><img src="/assets/img/ico_success.png">&nbsp;QQ已提交 正在为您排队,可能需要一段时间 请稍后查看圈圈增长情况</div>');
                $("#result3").slideDown();
                $('#submit_lqq').val('立即提交');
            }
        });
    });
    $("#num_add").click(function () {
        var i = parseInt($("#num").val());
        if ($("#need").val() == '') {
            layer.alert('请先选择商品');
            return false;
        }
        var multi = $('#tid option:selected').attr('multi');
        var count = parseInt($('#tid option:selected').attr('count'));
        var unit = $('#tid option:selected').attr('unit');
        if (undefined == unit || unit == '') {
            unit = '个';
        }
        if (multi == '0') {
            layer.alert('该商品不支持选择数量');
            return false;
        }
        i++;
        $("#num").val(i);
        var price = parseFloat($('#tid option:selected').attr('price'));
        var prices = $('#tid option:selected').attr('prices');
        price = price * i;
        if (typeof (prices) != 'undefined' && prices != '' && prices != 'null') {
            price = price - getFinalPrice(price, prices, i);
        }
        count = count * i;
        count = count * getAllTimes() * getAllCount();
        if (count > 1) $('#need').val('￥' + price.toFixed(2) + "元 ➠ " + count + unit);
        else $('#need').val('￥' + price.toFixed(2) + "元");
    });
    $("#num_min").click(function () {
        var i = parseInt($("#num").val());
        if (i <= 1) {
            layer.msg('最低下单一份哦！');
            return false;
        }
        if ($("#need").val() == '') {
            layer.alert('请先选择商品');
            return false;
        }
        var multi = $('#tid option:selected').attr('multi');
        var count = parseInt($('#tid option:selected').attr('count'));
        var unit = $('#tid option:selected').attr('unit');
        if (undefined == unit || unit == '') {
            unit = '个';
        }
        if (multi == '0') {
            layer.alert('该商品不支持选择数量');
            return false;
        }
        i--;
        if (i <= 0) i = 1;
        $("#num").val(i);
        var price = parseFloat($('#tid option:selected').attr('price'));
        var prices = $('#tid option:selected').attr('prices');
        price = price * i;
        if (typeof (prices) != 'undefined' && prices != '' && prices != 'null') {
            price = price - getFinalPrice(price, prices, i);
        }
        count = count * i;
        count = count * getAllTimes() * getAllCount();
        if (count > 1) $('#need').val('￥' + price.toFixed(2) + "元 ➠ " + count + unit);
        else $('#need').val('￥' + price.toFixed(2) + "元");
    });
    $("#num").keyup(function () {
        var i = parseInt($("#num").val());
        if (isNaN(i)) return false;
        var price = parseFloat($('#tid option:selected').attr('price'));
        var count = parseInt($('#tid option:selected').attr('count'));
        var unit = $('#tid option:selected').attr('unit');
        if (undefined == unit || unit == '') {
            unit = '个';
        }
        var prices = $('#tid option:selected').attr('prices');
        if (i < 1) {
            $("#num").val(1);
            i = 1;
        }
        price = price * i;
        if (typeof (prices) != 'undefined' && prices != '' && prices != 'null') {
            price = price - getFinalPrice(price, prices, i);
        }
        count = count * i;
        count = count * getAllTimes() * getAllCount();
        if (count > 1) $('#need').val('￥' + price.toFixed(2) + "元 ➠ " + count + unit);
        else $('#need').val('￥' + price.toFixed(2) + "元");
    });
    $("#back").click(function () {
        showlist();
    });
    var gogo;
    $("#start").click(function () {
        var ii = layer.load(1, {
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
                    layer.alert(choujiang.msg);
                }
            },
            error: function () {
                layer.close(ii);
                layer.alert("服务器错误，请稍后重试！");
            }
        });
    });
    $("#stop").click(function () {
        var ii = layer.load(1, {
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
                                    skin: 'layui-layer-lan',
                                    closeBtn: 0
                                }, function () {
                                    if (typeof (data.sub_cid) != "undefined" && data.sub_cid) {
                                        var shopurl = '?gift=1&cid=' + data.cid + '&sub_cid=' + data.sub_cid + '&tid=' + data.tid;
                                    } else {
                                        var shopurl = '?gift=1&cid=' + data.cid + '&tid=' + data.tid;
                                    }
                                    window.location.href = shopurl;
                                });
                            } else {
                                layer.alert(data.msg, {
                                    icon: 2,
                                    shade: 0.3
                                });
                                $("#roll").html('点击下方按钮开始抽奖');
                                $("#start").css("display", 'block');
                                $("#stop").css("display", 'none');
                            }
                        }
                    });
                } else {
                    layer.alert(msg.msg, {
                        icon: 2,
                        shade: 0.3
                    });
                    $("#start").css("display", 'block');
                    $("#stop").css("display", 'none');
                }
            },
            error: function () {
                layer.close(ii);
                layer.alert("服务器错误，请稍后重试！");
            }
        });
    });

    if ($_GET['buyok'] && typeof (isTemplateMall) == 'undefined') {
        var order_id = $_GET['orderid'];
        $("#tab-query").tab('show');
        //if ($("#tab-query").length>0)$("#tab-query").tab('show');
        $("#submit_query").click();
        isModal = false;
    } else if (typeof (isTemplateMall) == 'undefined' && $_GET['chadan'] || $_GET['query']) {
        isModal = false;
        var qq = $_GET['query'];
        var page = $_GET['page'];
        $("#qq3").val(qq);
        queryOrder(1, qq, page);
    } else if ($_GET['gift']) {
        isModal = false;
    }

    // 获取统计
    setTimeout(function () {
        if ($_GET['cid']) {
            var cid = parseInt($_GET['cid']);
            $("#cid").val(cid);
        }

        if ($("#cid").length > 0) {

            if ($("#cid").attr('type') == 'product') {
                getPoint();
            } else {
                $("#cid").change();
            }
        } else {
            getPoint();
        }

        if (homepage == true) {
            getcount();
        }
    }, 100);

    //站点访问次数
    if ($('#counter').length > 0) {
        var visits = $.cookie("counter")
        if (!visits) {
            visits = 1;
        } else {
            visits = parseInt(visits) + 1;
        }
        $('#counter').html(visits);
        $.cookie("counter", visits, 24 * 60 * 60 * 30);
    }
    if ($('#audio-play').length > 0 && $('#audio-play').is(':visible')) {
        audio_init.play();
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
            area: [$(window).width() > 640 ? '320px' : '97%', '110px'],
            content: '<div id="captcha"><p id="wait" class="text-center">正在加载验证码......</p></div>',
            // success: function(dom, index) {
            //     $(".layui-layer-content").css('height', '');
            //     var width = document.body.offsetWidth;
            //     var windowheight = $(window).height();
            //     var laydom = $("#layui-layer" + index);
            //     var layWidth = laydom.width();
            //     var layHeight = laydom.get(0).offsetHeight;
            //     if (width <= 992) {
            //         if (layWidth <= width * 0.8) {
            //             layWidth = width * 0.9;
            //         }
            //         var left = (width - layWidth) / 2;
            //         if (left < 1) {
            //             left = 3;
            //         }
            //         if (left > width * 0.1) {
            //             left = 10;
            //         }
            //         if (layHeight <= windowheight) {
            //             var top = (windowheight - layHeight) / 2;
            //             $(laydom).css({
            //                 'width': layWidth + "px",
            //                 'left': left + "px",
            //                 'top': top + "px"
            //             });
            //         } else {
            //             $(laydom).css({
            //                 'width': layWidth + "px",
            //                 'left': left + "px",
            //                 'top': "5px"
            //             });
            //         }
            //     }
            // }
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

});

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
    if ($("img.lazy").length > 0) {
        if (typeof $("img.lazy").lazyload == 'function') {
            $("img.lazy").lazyload({
                effect: "fadeIn"
            });
        } else {
            console.log("img Error：lazy is Not found！");
        }
    }

    interval_ref = setInterval(function () {
        if (interval_num > 10) {
            clearInterval(interval_ref);
            window.location.reload();
        }
        interval_num = interval_num + 1;
        $.ajax({
            type: "GET",
            url: serverPath + "ajax.php?act=getClientSession",
            dataType: 'json',
            async: true,
            success: function (data) {
                if (data.code == 0) {
                    hashsalt = data.hashsalt;
                }
            }
        });
    }, 600000);

    $("#qq3").focus(function () {
        layer.tips('输入下单时某个框填写的内容，例如名片赞的填QQ号即可，注意填写完整', this, {
            tips: 1,
            time: 5000
        });
    })

    //修复订单详情的商品详情可能会太宽超出屏幕
    var cssHtml = '<style type="text/css">.table tr td img{max-width: 100%;}</style>';
    if ($("head").length > 0) {
        $("head").append(cssHtml);
    } else {
        $("body").append(cssHtml);
    }
};

window.onbeforeunload = window.unload = function () {
    clearInterval(interval_ref);
}
$(document).on('click', '.remove', function (event) {
    event.preventDefault();
    /* Act on the event */
    var index = $(this).find('a').data('index');
    $(".imageList .item" + index).remove();
    setImageList();
    layer.msg('删除成功');
});
$(document).on('click', '#add', function (event) {
    event.preventDefault();
    /* Act on the event */
    $("#file")[0].click();
});
$(document).on('change', '#file', function (event) {
    event.preventDefault();
    /* Act on the event */
    upload();
});
$(document).on('click', '.imageList img', function (event) {
    event.preventDefault();
    /* Act on the event */
    layer.photos({
        photos: '.imageList',
        anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
    });
});

if ($.cookie('sec_defend_time')) {
    $.removeCookie('sec_defend_time', {
        path: '/'
    });
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
    if (typeof (_modalType) == 'undefined') {
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