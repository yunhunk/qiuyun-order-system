"use strict";
// VERSION 2293
var serverPath = 'string' === typeof serverPath ? serverPath : '../';
var queryVal = null,
    orderid = null,
    is_showWork = false,
    is_orderWork = false,
    orderPage = 1,
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
var Corn = {
    runing: null,
    run: function (func, times) {
        this.runing = setInterval(func, times);
    },
    stop: function () {
        clearInterval(this.runing);
    }
}
var workBackCronZt = {};
var workBackCron = function () {
    var display = $("#work").css("display");
    if (display != 'none') {
        var orderid = $("#work_orderid").val();
        showWorksInfo(orderid);
    } else {
        workBackCronZt.cron.stop();
        workBackCronZt.zt = false;
    }
    return false;
}
var filename = (function () {
    var strUrl = window.location.href;
    var arrUrl = strUrl.split("/");
    var strPage = arrUrl[arrUrl.length - 1];
    if (strPage.indexOf("?") > -1) {
        var pageName = strPage.split("?");
        strPage = pageName[0];
    }
    return strPage;
})();

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

function getFinalPrice(price, prices, num) {
    var arr = [],
        arr2, arr3 = [];
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
    return discount.toFixed(5);
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

function getHtmlDocName() {
    var str = window.location.href;
    str = str.substring(str.lastIndexOf("/") + 1);
    if (str.indexOf('?') >= 0) {
        str = str.substring(0, str.lastIndexOf("?"));
    }
    return str;
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
            $('img[data-name="thumb"]').attr("onerror", "this.src='../assets/img/Product/default.png'");
        } else if ($('img#classImg').length > 0) {
            $('img#classImg').attr("src", shopimg);
            $('img#classImg').attr("onerror", "this.src='../assets/img/Product/default.png'");
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
            $('#alert_frame').show();
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
        var ii = layer.alert('' + decodeURI(alert) + '', {
            area: area,
            btn: ['我知道了'],
            title: '商品提示',
            success: function (layero, index) {
                //重定义高度
                var lh = $(layero).height();
                var wh = $(window).height();
                if (lh > wh) {
                    layer.style(index, {
                        top: '12px',
                        bottom: '8px',
                        height: (wh - 20) + 'px'
                    });
                }
            }
        }, function () {
            layer.close(ii);
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

function getCourseList(el) {
    if (el != '#inputvalue4') {
        console.log(el);
        layer.alert("输入框标题设置顺序不规范，无法获取！");
        return false;
    }
    var tid = $('input#tid').length > 0 ? $('input#tid').val() : $('select#tid option:selected').val();
    var account = $('#inputvalue').val();
    var password = $('#inputvalue2').val();
    if ("" == account || "" == password) {
        layer.alert("登录账号/手机号、登录密码不能为空！");
        return false;
    }
    var schoolName = $('#inputvalue3').val();
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: "POST",
        url: serverPath + "ajax.php?act=getCourseList",
        dataType: 'json',
        data: {
            tid: tid,
            account: account,
            password: password,
            schoolName: schoolName
        },
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                var addstr = '';
                var coursename = '';
                if (chnemMall.isObject(data.data) && data.data.length > 0) {
                    $.each(data.data, function (i, item) {
                        if (i == 0) {
                            coursename = item.name;
                        }
                        addstr += '<option value="' + item.name + '">' + item.name + '</option>';
                    });
                    if ($('#show_CourseList').length > 0) {
                        $('#courseList').empty();
                        $('#courseList').append(addstr);
                    } else {
                        $('#inputsname').append('<div class="form-group" id="show_CourseList"><div class="input-group"><select id="courseList" class="input-form-control" onchange="set_course(this.value,\'' + el + '\');">' + addstr + '</select></div></div>');
                    }
                    $("#courseList").val(coursename);
                    set_course(coursename, el);
                } else {
                    layer.alert("课程列表为空，请检查是否属于【" + data.platformType + "】平台");
                }
            } else {
                layer.alert(data.msg);
            }
        },
        error: function () {
            layer.close(ii);
            layer.msg("请求出现错误，请稍后再试！");
        }
    });
}

function set_course(coursename, el) {
    $(el).val(coursename);
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
                    content: '../qzone.php',
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
                // layer.alert(data.msg);
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
                    $('#inputvalue').val(data.songid);
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
            } else if (songurl.indexOf('item/') > 0) {
                var songid = songurl.split('item/')[1].split('/')[0];
            } else if (songurl.indexOf('room/') > 0) {
                var songid = songurl.split('room/')[1].split('/')[0];
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
            url: "ajax.php?act=getDouyinUserId",
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
            url: "ajax.php?act=getpipixia",
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
    if (songurl.indexOf('bilibili.com') < 0) {
        layer.alert('请输入正确的视频链接！');
        return false;
    }
    try {
        var songid = songurl.split('video/av')[1].split('/')[0];
        songid = biliChange(songid);
        $('#inputvalue').val(songid);
        if (inputDisabled) $('#inputvalue').attr('disabled', true);
        layer.msg('ID获取成功！提交下单即可');
    } catch (e) {
        layer.alert('请输入正确的视频链接！');
        return false;
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
    if (songurl.indexOf('hao222.com') < 0) {
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
                    content: '修改订单数据成功！<br>如更改了订单数据，注意使用最新的下单账号查询订单',
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
                var status;
                if (typeof ($_GET['buyok']) != 'undefined' && $_GET['buyok'] == '1') {
                    setHistory('buyok=1&query=' + content + '&page=' + page);
                } else {
                    setHistory('query=' + content + '&page=' + page);
                }
                var status, orderid_1 = {},
                    x = 0;
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
                if ($_GET['buyok'] && !!orderid_1.id) {
                    showOrder(orderid_1.id, orderid_1.skey);
                } else if (orderid != null && data.data['order_' + orderid] && (is_showWork == true || is_orderWork == true)) {
                    showOrder(orderid, data.data['order_' + orderid].skey);
                } else {
                    if (x == 0) {
                        layer.alert("未查询到相关订单记录！<br>请输入下单时填写的QQ、账号、链接等试试~<br>或点击查单处右侧的感叹号按钮获取查询帮助");
                    }
                }
            } else {
                layer.alert(data.msg);
            }
            $('#submit_query').val('立即查询');
        }
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

function showOrder(id, skey) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    var status = ['<span class="label label-primary">待处理</span>', '<span class="label label-success">已完成</span>', '<span class="label label-warning">处理中</span>', '<span class="label label-danger">异常</span>', '<font color=red>已退款</font>'];
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
                        item += '<tr style="padding-right: 0;"><td colspan="6"><a class="mdui-btn mdui-btn-block mdui-color-pink mdui-ripple" href="./workorder.php?my=view&id=' + id + '">查看沟通记录</a></td></tr>';
                    } else {
                        if (!data.works) data.works = 0;
                        item += '<tr style="padding-right: 0;"><td colspan="6"><a class="mdui-btn mdui-btn-block mdui-color-pink mdui-ripple" href="./workorder.php?my=add&orderid=' + id + '&skey=' + skey + '">申请订单售后</a></td></tr>';
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
                    var area = [$(window).width() > 640 ? '540px' : '95%', 'auto'];
                    layer.open({
                        type: 1,
                        title: ['请收藏本站网址到浏览器书签-方便查单', 'color:red'],
                        area: area,
                        content: item,
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
            } else {
                layer.alert(data.msg);
            }
        }
    });
}
var $timestamp, obj, toTime;
var runUseTime = function () {
    if ($(obj).length < 1) {
        console.log("dom is not ：" + obj);
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
                    window.location.href = './' + filename + '?buyok=1';
                } else {
                    layer.alert(data.msg);
                    captchaObj.reset();
                }
            }
        });
    });
};

function toTool(cid, tid) {
    history.replaceState({}, null, './shop.php?cid=' + cid + '&tid=' + tid);
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
                            window.location.href = './' + filename + '?buyok=1';
                        } else if (data.code == 2) {
                            alert(data.msg);
                            window.location.href = './?mod=faka&id=' + data.orderid + '&skey=' + data.skey;
                        } else if (data.code == -2) {
                            alert(data.msg);
                            window.location.href = './' + filename + '?buyok=1';
                        } else if (data.code == -3) {
                            var confirmobj = layer.confirm('你的余额不足，请充值！', {
                                btn: ['立即充值', '取消']
                            }, function () {
                                window.location.href = './#chongzhi';
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
    //console.log("gettype：" + gettype);
    if (typeof (name) != 'undefined' && name != 'null') {} else {
        name = '';
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

function openCart() {
    var area = [$(window).width() > 640 ? '640px' : '95%', $(window).height() > 600 ? '600px' : '90%'];
    var options = {
        type: 2,
        title: '我的购物车',
        shadeClose: true,
        shade: false,
        maxmin: true,
        moveOut: true,
        area: area,
        content: '../?mod=cart',
        zIndex: layer.zIndex,
        success: function (layero, index) {
            var that = this;
            $(layero).data("callback", that.callback);
            layer.setTop(layero);
            if ($(layero).height() > $(window).height()) {
                layer.style(index, {
                    top: 0,
                    height: $(window).height()
                });
            }
        },
        cancel: function () {
            window.location.reload();
        }
    }
    if ($(window).width() < 480 || (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream && top.$("body").size() > 0)) {
        options.area = [top.$("body").width() + "px", top.$("body").height() + "px"];
        options.offset = [top.$("body").scrollTop() + "px", "0px"];
    }
    layer.open(options);
}

function showlist() {
    $("#display_list").show();
    $("#display_toolname").hide();
}

function numChange(el) {
    var value = parseInt($(el).val());
    var rule = $(el).attr('extendBlurAttr');
    if (!isNaN(rule) && value % rule == 0) {
        var num = getAllTimes();
        var cost = $(el).attr('price');
        var price = cost * num;
        $('#tid option:selected').attr('price', price.toFixed(5));
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
        history.replaceState({}, null, './shop.php?cid=' + id);
        $("#cid").val(id);
        $("#cid").change();
        $("#goodType").hide('normal');
        $("#goodTypeContent").show('normal');
    });
    $(".nav-tabs,.backType").click(function () {
        history.replaceState({}, null, './shop.php');
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
                layer.msg('加载失败，请刷新重试');
                return false;
            }
        });
    });
    $("#cid").change(function () {
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
                            console.log(res.tid, typeof (res.stock_id));
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
                    }
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
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
                error = '第' + (index + 1) + '行数据不完整，请检查是否至少有' + batch_length + '个输入框数据';
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
        var reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/;
        if ($('#inputname').html() == '你的邮箱' && !reg.test(inputvalue)) {
            layer.alert('邮箱格式不正确！');
            return false;
        }
        reg = /^[1][0-9]{10}$/;
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
        //checkInput();
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
                        paymsg += '<button class="btn btn-default btn-block" onclick="dopay(\'alipay\',\'' + data.trade_no + '\')" style="margin-top:10px;"><img width="20" src="' + serverPath + 'assets/icon/alipay.ico" class="logo">支付宝</button>';
                    }
                    if (data.pay_qqpay > 0) {
                        paymsg += '<button class="btn btn-default btn-block" onclick="dopay(\'qqpay\',\'' + data.trade_no + '\')" style="margin-top:10px;"><img width="20" src="' + serverPath + 'assets/icon/qqpay.ico" class="logo">QQ钱包</button>';
                    }
                    if (data.pay_wxpay > 0) {
                        paymsg += '<button class="btn btn-default btn-block" onclick="dopay(\'wxpay\',\'' + data.trade_no + '\')" style="margin-top:10px;"><img width="20" src="' + serverPath + 'assets/icon/wechat.ico" class="logo">微信支付</button>';
                    }
                    if (data.pay_rmb > 0) {
                        paymsg += '<button class="btn btn-success btn-block" onclick="dopay(\'rmb\',\'' + data.trade_no + '\')">使用余额支付</button>';
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
                    alert('领取成功！');
                    window.location.href = './' + filename + '?buyok=1';
                } else if (data.code == 2) {
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
                } else if (data.code == 3) {
                    layer.alert(data.msg, {
                        closeBtn: false
                    }, function () {
                        window.location.reload();
                    });
                } else {
                    layer.alert(data.msg);
                }
            }
        });
    });
    $("#submit_cart_shop").click(function () {
        var tid = $("#tid").val();
        if (tid == 0) {
            layer.alert('请选择商品！');
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
        var reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/;
        if ($('#inputname').html() == '你的邮箱' && !reg.test(inputvalue)) {
            layer.alert('邮箱格式不正确！');
            return false;
        }
        reg = /^[1][0-9]{10}$/;
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
                } else {
                    layer.alert(data.msg);
                }
            }
        });
    });
    $("#submit_query").click(function () {
        var qq = $("#qq3").val();
        var type = $("#searchtype").val();
        queryOrder(type, qq, 1);
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
        if (count > 1) $('#need').val('￥' + price.toFixed(5) + "元 ➠ " + count + unit);
        else $('#need').val('￥' + price.toFixed(5) + "元");
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
        if (count > 1) $('#need').val('￥' + price.toFixed(5) + "元 ➠ " + count + unit);
        else $('#need').val('￥' + price.toFixed(5) + "元");
    });
    $("#num").keyup(function () {
        var i = parseInt($("#num").val());
        if (isNaN(i)) return false;
        var price = parseFloat($('#tid option:selected').attr('price'));
        var count = parseInt($('#tid option:selected').attr('count'));
        var prices = $('#tid option:selected').attr('prices');
        var unit = $('#tid option:selected').attr('unit');
        if (undefined == unit || unit == '') {
            unit = '个';
        }
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
        if (count > 1) $('#need').val('￥' + price.toFixed(5) + "元 ➠ " + count + unit);
        else $('#need').val('￥' + price.toFixed(5) + "元");
    });
    $("#back").click(function () {
        showlist();
    });
    if ($_GET['buyok']) {
        var orderid = $_GET['orderid'];
        $("#tab-query").tab('show');
        $("#submit_query").click();
        isModal = false;
    } else if ($_GET['chadan']) {
        $("#tab-query").tab('show');
        isModal = false;
    } else if ($_GET['query']) {
        var qq = $_GET['query'];
        var page = $_GET['page'];
        $("#qq3").val(qq);
        queryOrder(1, qq, page);
    }
    if ($_GET['cid']) {
        var cid = parseInt($_GET['cid']);
        $("#cid").val(cid);
    }
    $("#cid").change();
    if ($("img.lazy").length > 0) {
        if (typeof $("img.lazy").lazyload == 'function') {
            $("img.lazy").lazyload({
                effect: "fadeIn"
            });
            if (typeof (defaultimg) == 'undefined') {
                var defaultimg = '/assets/img/Product/default.png';
            }
            var items = $("img.lazy");
            for (var i = 0; i < items.length; i++) {
                $(items[i]).attr('error', "this.src='" + defaultimg + "'");
            }
        } else {
            console.log("img Error：lazy is Not found！");
        }
    }
    interval_ref = setInterval(function () {
        if (interval_num > 30) {
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
    }, 60 * 1000);
    window.onbeforeunload = window.unload = function () {
        clearInterval(interval_ref);
    }
    // $.getScript("/assets/js/tgCode.js");
    // $.getScript("/assets/js/hotshop.js");
    //$.getScript("assets/js/otherSet.js");

    //修复订单详情的商品详情可能会太宽超出屏幕
    var cssHtml = '<style type="text/css">.table tr td img{max-width: 100%;}</style>';
    if ($("head").length > 0) {
        $("head").append(cssHtml);
    } else {
        $("body").append(cssHtml);
    }

    $("#qq3").focus(function () {
        layer.tips('输入下单时某个框填写的内容，例如名片赞的填QQ号即可，注意填写完整', this, {
            tips: 1,
            time: 5000
        });
    });
});