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
var filename = '';

function getFinalPrice(price, prices, num) {
    var arr = [],
        arr2, arr3 = [];
    num = num > 0 ? num : 1;
    if (typeof(prices) != 'string' || prices == '') return 0;
    $.each(prices.split(','), function(index, item) {
        arr2 = item.split('|');
        arr.push('' + arr2[0]);
        arr3[arr2[0]] = '' + arr2[1];
    });
    arr.sort(function(a, b) {
        return b - a;
    });
    var discount = 0;
    $.each(arr, function(index, item) {
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

function setHistory(queryStr) {
    if (typeof(queryStr) == 'undefined') {
        queryStr == '';
    }
    if (typeof history != "object" || typeof history.replaceState != "function") {
        $.getScript("https://lib.baomitu.com/history.js/1.7.1/native.history.min.js");
    }
    var url = window.document.location.href.toString();
    var u = url.split("?");
    var get = {};
    if (typeof(u[1]) == "string") {
        u = u[1].split("&");
        u.forEach(function(i, index) {
            if (i.indexOf('=') >= 0) {
                var j = i.split("=");
                get[j[0]] = j[1] != "" ? j[1] : '1';
            } else {
                get[i] = '1';
            }
        });
    }
    var q = queryStr.split("&");
    q.forEach(function(i, index) {
        if (i.indexOf('=') >= 0) {
            var j = i.split("=");
            get[j[0]] = j[1] != "" ? j[1] : '1';
        } else {
            get[i] = '1';
        }
    });
    var str = '';
    if (typeof(get) == 'object' || typeof(get) == 'array') {
        $.each(get, function(index, item) {
            if (str == '') {
                str = index + '=' + item;
            } else {
                str = str + '&' + index + '=' + item;
            }
        });
        return history.replaceState({}, null, './' + filename + '?' + str);
    }
    return history.replaceState({}, null, './');
}

function getPoint() {
    if ($("#display_tool").length > 0) {
        $("#display_tool").show();
    }
    $('#display_price').show();
    var tid = parseInt($('#tid').val());
    setHistory('tid=' + tid);
    var multi = $('#tid').attr('multi');
    var count = $('#tid').attr('count');
    var price = $('#tid').attr('price');
    var shopimg = $('#tid').attr('shopimg');
    var active = parseInt($('#tid').attr('active'));
    var prices = $('#tid').attr('prices');
    if (typeof(prices) != 'undefined' && prices != '' && prices != 'null' && typeof(isLogin2) != 'undefined' && isLogin2 == 1) {
        price = price - getFinalPrice(price, prices, 1);
    }
    if (multi == 1 && count > 1) {
        $('#need').val('￥' + price + "元 ➠ " + count + "个");
    } else {
        $('#need').val('￥' + price + "元");
    }
    if (active != 1) {
        $('#submit_buy').val('禁售中');
        $('#submit_buy').html('禁售中');
    } else if (price == 0) {
        $('#submit_buy').val('免费领取');
        $('#submit_buy').html('免费领取');
    } else {
        $('#submit_buy').val('立即购买');
        $('#submit_buy').html('立即购买');
    }
    if (multi == 1) {
        $('#display_num').show();
    } else {
        $('#display_num').hide();
    }
    $('#inputsname').html("");
    var inputname = $('#tid').attr('inputname');
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
            $.each(selectstr.split(','), function(i, v) {
                if (v.indexOf(':') > 0) {
                    i = v.split(':')[0];
                    v = v.split(':')[1];
                } else {
                    i = v;
                }
                addstr += '<option value="' + i + '">' + v + '</option>';
            });
            $('#inputsname').append('<div class="from bl_view_title"><div class="from_wz_3" id="inputname">' + selectname + '：</div><div class="from_in_2"><select name="inputvalue" id="inputvalue" placeholder="' + placeholder + '" class="form-control input">' + addstr + '</select></div></div>');
        } else if (extendEventBlur == '' && inputname.indexOf('[') > 0 && inputname.indexOf(']') > 0) {
            var addstr = '';
            var selectname = inputname.split('[')[0];
            var selectstr = inputname.split('[')[1].split(']')[0];
            $.each(selectstr.split(','), function(i, v) {
                if (v.indexOf(':') > 0) {
                    i = v.split(':')[0];
                    v = v.split(':')[1];
                } else {
                    i = v;
                }
                addstr += '<option value="' + i + '">' + v + '</option>';
            });
            $('#inputsname').append('<div class="from bl_view_title"><div class="from_wz_3" id="inputname">' + selectname + '：</div><div class="from_in_2"><select name="inputvalue" id="inputvalue" placeholder="' + placeholder + '" class="form-control input">' + addstr + '</select></div></div>');
        } else {
            $('#inputname').html(inputname);
            $('#inputsname').append('<div class="from bl_view_title"><div class="from_wz_3" id="inputname">' + inputname + '：</div><div class="from_in_2"><input type="text" name="inputvalue" id="inputvalue" placeholder="' + placeholder + '" value="' + ($_GET['qq'] ? $_GET['qq'] : '') + '" ' + gettype + ' class="form-control input" required onblur="checkInput()' + extendEventBlur + '" ' + extendEventAttr + '/></div></div>');
        }
    } else {
        $('#inputsname').append('<div class="from bl_view_title"><div class="from_wz_3" id="inputname">下单ＱＱ</div><div class="from_in_2"><input type="text" name="inputvalue" placeholder="输入下单QQ" id="inputvalue" value="' + ($_GET['qq'] ? $_GET['qq'] : '') + '" class="form-control input" required onblur="checkInput()"/></div></div>');
    }
    var inputsname = $('#tid').attr('inputsname');
    if (inputsname != '') {
        $.each(inputsname.split('|'), function(i, value) {
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
                $.each(selectstr.split(','), function(i, v) {
                    if (v.indexOf(':') > 0) {
                        i = v.split(':')[0];
                        v = v.split(':')[1];
                    } else {
                        i = v;
                    }
                    addstr += '<option value="' + i + '">' + v + '</option>';
                });
                $('#inputsname').append('<div class="from bl_view_title"><div class="from_wz_3" id="inputname' + (i + 2) + '">' + selectname + '</div><div class="from_in_2"><select name="inputvalue' + (i + 2) + '" id="inputvalue' + (i + 2) + '" class="form-control input">' + addstr + '</select></div></div>');
            } else if (extendEventBlur == '' && value.indexOf('[') > 0 && value.indexOf(']') > 0) {
                var addstr = '';
                var selectname = value.split('[')[0];
                var selectstr = value.split('[')[1].split(']')[0];
                $.each(selectstr.split(','), function(i, v) {
                    if (v.indexOf(':') > 0) {
                        i = v.split(':')[0];
                        v = v.split(':')[1];
                    } else {
                        i = v;
                    }
                    addstr += '<option value="' + i + '">' + v + '</option>';
                });
                $('#inputsname').append('<div class="from bl_view_title"><div class="from_wz_3" id="inputname' + (i + 2) + '">' + selectname + '</div><div class="from_in_2"><select name="inputvalue' + (i + 2) + '" id="inputvalue' + (i + 2) + '" class="form-control input">' + addstr + '</select></div></div>');
            } else {
                if (value == '说说ID' || value == '说说ＩＤ') var addstr = '<div class="from_in_2 yanzheng onclick" onclick="get_shuoshuo(\'inputvalue' + (i + 2) + '\',$(\'#inputvalue\').val())">自动获取</div>';
                else if (value == '日志ID' || value == '日志ＩＤ') var addstr = '<div class="from_in_2 yanzheng onclick" onclick="get_rizhi(\'inputvalue' + (i + 2) + '\',$(\'#inputvalue\').val())">自动获取</div>';
                else if (value == '作品ID' || value == '作品ＩＤ' || value == '快手作品ID') var addstr = '<div class="from_in_2 yanzheng onclick" onclick="get_kuaishou(\'inputvalue' + (i + 2) + '\',$(\'#inputvalue\').val())">自动获取</div>';
                else if (value == '抖音评论ID') var addstr = '<div class="from_in_2 yanzheng onclick" onclick="getCommentList(\'inputvalue' + (i + 2) + '\',$(\'#inputvalue\').val())">自动获取</div>';
                else if (value == '收货人地址' || value == '收货地址') var addstr = '<div class="from_in_2 yanzheng onclick" onclick="inputAddress(\'inputvalue' + (i + 2) + '\')">点我填写地址</div>';
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
                $('#inputsname').append('<div class="from bl_view_title"><div class="from_wz_3" id="inputname' + (i + 2) + '">' + ibtn + '：</div><div class="from_in_2"><input type="text" name="inputvalue' + (i + 2) + '" placeholder="' + placeholder + '" id="inputvalue' + (i + 2) + '" value="" ' + gettype + ' class="form-control input" onblur="' + extendEventBlur + '" ' + extendEventAttr + ' required/></div>' + addstr + '</div></div>');
            }
            if (value.indexOf("&") !== (-1)) {
                var btnArr = value.split('&');
                $('#inputvalue' + (i + 2)).attr('placeholder', btnArr[1]);
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
    if ($('#tid').attr('isfaka') == 1) {
        $('#inputvalue').attr("placeholder", "用于接收卡密以及查询订单使用");
        $('#display_left').show();
        $.ajax({
            type: "POST",
            url: "ajax.php?act=getleftcount",
            data: {
                tid: $('#tid').val()
            },
            dataType: 'json',
            success: function(data) {
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
    var alert = $('#tid').attr('alert');
    if (alert && alert != '' && alert != 'null' && active == 1) {
        var ii = layer.alert('' + unescape(alert) + '', {
            btn: ['我知道了'],
            title: '商品提示'
        }, function() {
            layer.close(ii);
        });
    }
}

function isEmptyVariable($var) {
    if ('undefined' == typeof($var) || 'null' == $var || '' == $var) {
        return true;
    }
    return false;
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
        url: "ajax.php?act=getshuoshuo&uin=" + uin + "&page=" + page + "&hashsalt=" + hashsalt,
        dataType: 'json',
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                var addstr = '';
                $.each(data.data, function(i, item) {
                    addstr += '<option value="' + item.tid + '">' + item.content + '</option>';
                });
                var nextpage = page + 1;
                var lastpage = page > 1 ? page - 1 : 1;
                if ($('#show_shuoshuo').length > 0) {
                    $('#show_shuoshuo').html('<div class="from_wz_3 onclick" title="上一页" onclick="get_shuoshuo(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + lastpage + ')">上一页</div><div class="from_in_2"><select id="shuoid" class="form-control" onchange="set_shuoshuo(\'' + id + '\');">' + addstr + '</select></div><div class="from_in_2 yanzheng onclick" title="下一页" onclick="get_shuoshuo(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + nextpage + ')">下一页</div>');
                } else {
                    $('#inputsname').append('<div class="from bl_view_title" id="show_shuoshuo"><div class="from_wz_3 onclick" title="上一页" onclick="get_shuoshuo(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + lastpage + ')">上一页</div><div class="from_in_2"><select id="shuoid" class="form-control" onchange="set_shuoshuo(\'' + id + '\');">' + addstr + '</select></div><div class="from_in_2 yanzheng onclick" title="下一页" onclick="get_shuoshuo(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + nextpage + ')">下一页</div></div>');
                }
                set_shuoshuo(id);
            } else {
                layer.alert(data.msg);
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
        url: "ajax.php?act=getrizhi&uin=" + uin + "&page=" + page + "&hashsalt=" + hashsalt,
        dataType: 'json',
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                var addstr = '';
                $.each(data.data, function(i, item) {
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
        url: "ajax.php?act=fill",
        data: {
            orderid: id,
            skey: skey
        },
        dataType: 'json',
        success: function(data) {
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
            url: "ajax.php?act=getkuaishou",
            data: {
                url: ksurl
            },
            dataType: 'json',
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#inputvalue').val(data.authorid);
                    $('#inputvalue').attr('disabled', true);
                    if ($('#inputvalue2').length > 0) {
                        $('#inputvalue2').val(data.videoid);
                        $('#inputvalue2').attr('disabled', true);
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
            layer.alert('请输入正确的快手作品链接！');
            return false;
        }
        $('#inputvalue').val(authorid);
        if ($('#inputvalue2').length > 0) {
            $('#inputvalue2').val(videoid);
            $('#inputvalue2').attr('disabled', true);
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
            url: "./ajax.php?act=gethuoshan",
            data: {
                url: songurl
            },
            dataType: 'json',
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#inputvalue').val(data.videoid);
                    $('#inputvalue').attr('disabled', true);
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
            $('#inputvalue').attr('disabled', true);
            layer.msg('视频ID获取成功！下单即可');
        } catch (e) {
            layer.alert('请输入正确的链接！');
            return false;
        }
    }
}

function inputAddress(dom) {
    if (typeof($("body").distpicker) !== 'function') {
        return layer.alert("省市区支持库文件未引入！请联系平台客服处理");
    }
    if ($("#AddressContent").length > 0) {
        try {
            $("#AddressContent").distpicker({
                province: "-- 请选择省份 --",
                city: "-- 请选择市 --",
                district: "-- 请选择区 --"
            });
            $("#city_modal").modal('show');
            $("#submit_address").attr('input', dom);
        } catch (e) {
            layer.msg("加载出错了~麻烦在地址框输入地址哦");
        }
    } else {
        layer.msg("加载出错，麻烦在地址框输入地址哦");
    }
}

function keyAddress() {
    $("#city_modal").modal('hide');
    var address = $('#province option:selected').text().indexOf("请选择") == -1 ? $('#province option:selected').text() : "";
    address += $('#city option:selected').text().indexOf("请选择") == -1 ? $('#city option:selected').text() : "";
    address += $('#district option:selected').text().indexOf("请选择") == -1 ? $('#district option:selected').text() : "";
    address += $('#street').val();
    var dom = $("#submit_address").attr('input');
    $('#' + dom).val(address);
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
            success: function(data) {
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
            url: "ajax.php?act=getdouyin",
            data: {
                url: songurl
            },
            dataType: 'json',
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#inputvalue').val(data.videoid);
                    $('#inputvalue').attr('disabled', true);
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
            $('#inputvalue').attr('disabled', true);
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
        $('#inputvalue').attr('disabled', true);
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
        $('#inputvalue').attr('disabled', true);
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
        $('#inputvalue').attr('disabled', true);
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
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#inputvalue').val(data.videoid);
                    $('#inputvalue').attr('disabled', true);
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
            $('#inputvalue').attr('disabled', true);
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
    if (songurl.indexOf('http') > 0) {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=getxiaohongshu",
            data: {
                url: songurl
            },
            dataType: 'json',
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#inputvalue').val(data.videoid);
                    $('#inputvalue').attr('disabled', true);
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
            $('#inputvalue').attr('disabled', true);
        } catch (e) {
            layer.alert('请输入正确的链接！');
            return false;
        }
    }
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
        layer.msg('ID获取成功！提交下单即可');
        $('#inputvalue').val(songid);
        $('#inputvalue').attr('disabled', true);
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
            $('#inputvalue').attr('disabled', true);
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
        success: function(data) {
            layer.close(ii);
            if (data.total != 0) {
                var addstr = '';
                $.each(data.comments, function(i, item) {
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
        error: function(a) {
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
    if ($zt == 1 && $is_curl == 2) return '<font color=green>已提交</font>';
    else if ($zt == 1 && $is_curl == 4) {
        return '<font color=green>已完成</font>';
    } else if ($zt == 1) {
        return '<font color=green>已提交</font>';
    } else if ($zt == 2) return '<font color=orange>正在处理</font>';
    else if ($zt == 3) return '<font color=red>异常</font>';
    else if ($zt == 4) return '<font color=grey>已退款</font>';
    else if ($zt == 10) return '<font color=#8E9013>待退款</font>';
    else return '<font color=blue>待处理</font>';
}

function queryOrder(type, content, page) {
    $('#qq3').val(content);
    if ($("#tab-query").length > 0) {
        $("#tab-query").tab('show');
    }
    if (typeof(isModal) != undefined) {
        isModal = false;
    }
    $('#submit_query').val('Loading');
    $('#result2').hide();
    $('#list').html('');
    $.ajax({
        type: "POST",
        url: "./ajax.php?act=query",
        data: {
            type: type,
            qq: content,
            page: page
        },
        dataType: 'json',
        success: function(data) {
            if (data.code == 0) {
                queryVal = content;
                orderPage = page;
                if (typeof($_GET['buyok']) != 'undefined' && $_GET['buyok'] == '1') {
                    setHistory('buyok=1&query=' + content + '&page=' + page);
                } else {
                    setHistory('query=' + content + '&page=' + page);
                }
                var status, orderid_1, x = 0;
                $.each(data.data, function(i, item) {
                    if (x == 0) orderid_1 = item;
                    if (!item.is_curl) item.is_curl = 0;
                    status = orderStatus(item.status, item.is_curl);
                    var str = '<tr order_id=' + item.id + '><td><a onclick="showOrder(' + item.id + ',\'' + item.skey + '\')" title="查看订单详细" class="btn btn-info btn-xs">详细</a></td><td>' + item.input + '</td><td>' + item.name + '</td><td class="hidden-xs">' + item.value + '</td><td class="hidden-xs">' + item.addtime + '</td><td>' + status + '</td><td>';
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

function inputOrder(id) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'GET',
        url: './ajax.php?act=order2&id=' + id,
        dataType: 'json',
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.open({
                    title: '修改订单数据',
                    shade: 0.3,
                    shadeClose: false, //开启遮罩关闭
                    content: data.data,
                    btn: ['关闭'],
                    success: function() {
                        checkInputName()
                    }
                });
            } else {
                layer.alert(data.msg);
            }
        },
        error: function(data) {
            layer.msg('服务器错误');
            return false;
        }
    });
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
        url: "ajax.php?act=editOrder",
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
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.open({
                    content: '修改订单数据成功！<br>注意使用最新的下单账号查询订单',
                    btn: ['我知道了'],
                    yes: function() {
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
        url: "ajax.php?act=workInfo",
        data: {
            orderid: id
        },
        dataType: "json",
        success: function(data) {
            if (data.code == -2) {
                is_orderWork = false;
                is_showWork = true;
                orderid = id;
                var index = layer.alert(data.msg, {
                    btn: ['现在登录', '现在注册', '取消操作'],
                    yes: function() {
                        $("#cmLoginModal").modal('show');
                        $('#tousu').modal("hide");
                        layer.close(index);
                    },
                    btn2: function() {
                        $("#cmRegModal").modal('show');
                        $('#tousu').modal("hide");
                        layer.close(index);
                    },
                    btn3: function() {
                        layer.close(index);
                    }
                });
            } else if (data.works > 0) {
                if ($("#con").length < 1) {
                    return false;
                }
                $("#con").html('');
                $("#work_orderid").val(id);
                $("#work_title").html('订单ID' + orderid + '与网站客服的沟通记录');
                $.each(data.data, function(index, res) {
                    $("#con").append('<div class="clearfloat"><div class="author-name"><small class="chat-date">' + res.addtime + '</small></div><div class="' + (res.isadmin == 0 ? 'right' : 'left') + '">' + (res.isadmin == 0 ? '<div class="chat-message" style="color:white;">' + res.content + '</div><div class="chat-avatars"><a href="javascript:void(0)"><img width="35" class="qqlogo" src="' + res.myimg + '" alt="头像"></a></div>' : '<div class="chat-avatars"><a href="javascript:void(0)"><img width="35" class="qqlogo" src="https://imgcache.qq.com/open_proj/proj_qcloud_v2/mc_2014/work-order/css/img/custom-service-avatar.svg" alt="头像"></a></div><div class="chat-message">' + res.content + '</div>') + '</div></div>')
                })
                if (undefined != data.ok && data.ok == 1) {
                    $("#work_ok").show();
                    $("#closeWorkInfo").show();
                    $("#huifuWork").hide();
                    $("#work_ok").html(data.info);
                    if (workBackCronZt.cron) {
                        workBackCronZt.cron.stop();
                        workBackCronZt.zt = false;
                    }
                } else {
                    $("#work_ok").hide();
                    $("#closeWorkInfo").hide();
                    $("#huifuWork").show();
                    if (!workBackCronZt.zt) {
                        workBackCronZt.cron = new Cron(workBackCron, 5000, "#work");
                        workBackCronZt.zt = true;
                        workBackCronZt.cron.run();
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
        error: function() {
            layer.alert('访问出错，请重试');
        }
    });
}

function showWorksInfo(id) {
    layer.closeAll();
    $.ajax({
        type: "POST",
        url: "ajax.php?act=workInfo",
        data: {
            orderid: id
        },
        dataType: "json",
        success: function(data) {
            if ($("#con").length < 1) {
                return false;
            }
            if (data.works > 0) {
                $("#con").html('');
                $("#work_orderid").val(id);
                $("#work_title").html('订单ID' + id + '与网站客服的沟通记录');
                $.each(data.data, function(index, res) {
                    $("#con").append('<div class="clearfloat"><div class="author-name"><small class="chat-date">' + res.addtime + '</small></div><div class="' + (res.isadmin == 0 ? 'right' : 'left') + '">' + (res.isadmin == 0 ? '<div class="chat-message" style="color:white;">' + res.content + '</div><div class="chat-avatars"><a href="javascript:void(0)"><img width="35" class="qqlogo" src="' + res.myimg + '" alt="头像"></a></div>' : '<div class="chat-avatars"><a href="javascript:void(0)"><img width="35" class="qqlogo" src="https://imgcache.qq.com/open_proj/proj_qcloud_v2/mc_2014/work-order/css/img/custom-service-avatar.svg" alt="头像"></a></div><div class="chat-message">' + res.content + '</div>') + '</div></div>')
                })
                if (undefined != data.ok && data.ok == 1) {
                    $("#work_ok").show();
                    $("#closeWorkInfo").show();
                    $("#huifuWork").hide();
                    $("#work_ok").html(data.info);
                    workBackCronZt.cron.stop();
                    workBackCronZt.zt = false;
                } else {
                    $("#work_ok").hide();
                    $("#closeWorkInfo").hide();
                    $("#huifuWork").show();
                }
            }
        },
        error: function() {}
    });
}

function work(id, type) {
    layer.closeAll();
    if (type == 1) {
        showWorks(id);
        return false;
    } else {
        setTimeout(function() {
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
        url: "ajax.php?act=workBack",
        data: {
            orderid: id,
            content: content
        },
        dataType: "json",
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg("回复成功");
            } else if (data.code == -2) {
                orderid = id;
                var index = layer.alert(data.msg, {
                    btn: ['现在登录', '现在注册', '取消操作'],
                    yes: function() {
                        $("#cmLoginModal").modal('show');
                        $('#tousu').modal("hide");
                        layer.close(index);
                    },
                    btn2: function() {
                        $("#cmRegModal").modal('show');
                        $('#tousu').modal("hide");
                        layer.close(index);
                    },
                    btn3: function() {
                        layer.close(index);
                    }
                });
            } else {
                layer.alert(data.msg);
            }
        },
        error: function() {
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
        url: "ajax.php?act=orderWork",
        data: {
            orderid: order_id,
            type: type,
            qq: qq,
            content: content
        },
        dataType: "json",
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                $('#tousu').modal("hide");
                var index = layer.alert('售后申请提交成功，耐心等待回复~<br>是否现在打开会话详情', {
                    btn: ['立即查看', '还是不了'],
                    yes: function() {
                        showWorks(order_id);
                    },
                    btn2: function() {
                        layer.close(index);
                    }
                });
            } else if (data.code == -2) {
                is_orderWork = true;
                is_showWork = false;
                orderid = order_id;
                var index = layer.alert(data.msg, {
                    btn: ['现在登录', '现在注册', '取消操作'],
                    yes: function() {
                        $("#cmLoginModal").modal('show');
                        $('#tousu').modal("hide");
                        layer.close(index);
                    },
                    btn2: function() {
                        $("#cmRegModal").modal('show');
                        $('#tousu').modal("hide");
                        layer.close(index);
                    },
                    btn3: function() {
                        layer.close(index);
                    }
                });
            } else {
                layer.alert(data.msg);
            }
        },
        error: function() {
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
        url: './ajax.php?act=login',
        data: 'user=' + username + '&pass=' + password,
        dataType: 'json',
        success: function(data) {
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
        error: function(data) {
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
    var ii = layer.load(0, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: './ajax.php?act=reg',
        data: 'user=' + username + '&pass=' + password + '&qq=' + qq,
        dataType: 'json',
        success: function(data) {
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
                captcha = layer.open({
                    type: 1,
                    title: '注册验证-请完成拼图',
                    skin: 'layui-layer-rim',
                    area: ['320px', '100px'],
                    content: '<div id="captcha"><div id="captcha_text">正在加载验证码</div><div id="captcha_wait"><div class="loading"><div class="loading-dot"></div><div class="loading-dot"></div><div class="loading-dot"></div><div class="loading-dot"></div></div></div></div>'
                });
                $.ajax({
                    url: "?act=captcha&t=" + (new Date()).getTime(),
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
            } else {
                layer.alert(data.msg);
            }
        },
        error: function(data) {
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
        url: "ajax.php?act=order",
        data: {
            id: id,
            skey: skey
        },
        dataType: 'json',
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                var item = '<table class="table table-condensed table-hover">';
                item += '<tr><td colspan="6" style="text-align:center"><b>订单基本信息</b></td></tr><tr><td class="info" style="min-width:80px">订单编号</td><td colspan="5">' + id + '</td></tr><tr><td class="info" style="min-width:80px">商品名称</td><td colspan="5">' + data.name + '</td></tr><tr><td class="info" style="min-width:80px">订单金额</td><td colspan="5">' + data.money + '元</td></tr><tr><td class="info" style="min-width:80px">购买时间</td><td colspan="5">' + data.date + '</td></tr><tr><td class="info" style="min-width:80px">下单信息</td><td colspan="5">' + data.inputs + '</td><tr><td class="info" style="min-width:80px">订单状态</td><td colspan="5">' + orderStatus(data.status, data.is_curl) + '</td></tr>';
                if (data.status == 1 && data.show_endtime && data.endtime != "") {
                    item += '<tr><tr><td class="info" style="min-width:80px">完成时间</td><td colspan="5">' + data.endtime + '</td><tr>';
                } else if (data.show_usetime && data.show_usetime == 1 && data.usetime != "") {
                    item += '<tr><tr><td class="info" style="min-width:80px">处理耗时</td><td colspan="5"><span style="background-color:#42a1ff;padding:4px 6px;border-radius:5px" id="order_usetime">0小时0分0秒</span></td><tr>';
                }
                if (data.kminfo) {
                    item += '<tr><td colspan="6" style="text-align:center"><b>以下是你的卡密信息</b></td><tr><td colspan="6">' + data.kminfo + '</td></tr>';
                } else {
                    if (data.list && data.list.order_state) {
                        item += '<tr><td colspan="6" style="text-align:center"><b>订单实时状态(软件提供仅供参考)</b></td><tr><td class="warning">下单数量</td><td>' + data.list.num + '</td><td class="warning">下单时间</td><td colspan="3">' + data.list.add_time + '</td></tr><tr><td class="warning">初始数量</td><td>' + data.list.start_num + '</td><td class="warning">当前数量</td><td>' + data.list.now_num + '</td><td class="warning">订单状态</td><td><font color=blue>' + data.list.order_state + '</font></td></tr>';
                    }
                    if (data.result) {
                        item += '<tr><td colspan="6" style="text-align:center"><b>处理结果</b></td><tr><td colspan="6">' + data.result + '</td></tr>';
                    }
                }
                if (data.complain) {
                    if (data.works) {
                        item += '<tr style="padding-right: 0;"><td colspan="6"><a class="mdui-btn mdui-btn-block mdui-color-pink mdui-ripple" onclick="showWorks(' + id + ');">查看沟通记录</a></td></tr>';
                    } else {
                        if (!data.works) data.works = 0;
                        item += '<tr style="padding-right: 0;"><td colspan="6"><a class="mdui-btn mdui-btn-block mdui-color-pink mdui-ripple" onclick="work(' + id + ',' + (data.works.length > 0 ? 1 : 0) + ');">申请订单售后</a></td></tr>';
                    }
                }
                if (data.show_desc && data.desc) {
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
                        success: function(layero, index) {
                            if (data.show_usetime && data.usetime && data.usetime > 0) {
                                getUseTime(data.usetime, "#order_usetime");
                            }
                            //重定义高度
                            let lh = $(layero).height();
                            let wh = $(window).height();
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
var runUseTime = function() {
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
var handlerEmbed = function(captchaObj) {
    captchaObj.appendTo('#captcha');
    captchaObj.onReady(function() {
        $("#captcha_wait").hide();
    }).onSuccess(function() {
        var result = captchaObj.getValidate();
        if (!result) {
            return alert('请完成验证');
        }
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=pay",
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
            success: function(data) {
                layer.close(ii);
                if (data.code >= 0) {
                    $('#alert_frame').hide();
                    if ($('#inputname').html() == '你的邮箱') {
                        $.cookie('email', inputvalue);
                    }
                    layer.alert(data.msg, {
                        end: function() {
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

function cancel(id) {
    layer.closeAll();
    $.ajax({
        type: "POST",
        url: "./ajax.php?act=cancel",
        data: {
            orderid: id,
            hashsalt: hashsalt
        },
        dataType: 'json',
        async: true,
        success: function(data) {
            if (data.code == 0) {} else {
                layer.closeAll();
            }
        },
        error: function(data) {
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
            url: "./ajax.php?act=getshareid",
            data: {
                url: url,
                hashsalt: hashsalt
            },
            dataType: 'json',
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#inputvalue').val(data.authorid);
                    if (typeof(data.videoid) != "undefined" && $('#inputvalue2').length > 0) $('#inputvalue2').val(data.videoid);
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
            url: "./ajax.php?act=getshareurl",
            data: {
                url: url,
                hashsalt: hashsalt
            },
            dataType: 'json',
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#inputvalue').val(data.shareurl);
                    if (typeof(data.videoid) != "undefined" && $('#inputvalue2').length > 0) $('#inputvalue2').val(data.videoid);
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
    if ($("#title2").length > 1 && $("#title2").html() == "") {
        title2 = $("#inputname2").html();
    }
    var value = $("#inputvalue").val();
    var name = $('#tid option:selected').html();
    var shareurl = $("#inputvalue").attr('shareurl');
    if (typeof(shareurl) == 'undefined' || shareurl != '!shareurl') {
        inputFilter(value);
    }
    if (typeof(name) != 'undefined' && name != 'null') {} else {
        name = '';
    }
    if (title == '手心ID' || title == '块sんǒuID' || title == '快sんǒuID' || title == '手心作品链接' || title == '手心视频链接' || title == '快手ID' || title == '快手作品链接' || title == '快手视频链接' || title == '快手ＩＤ' || title == '快手用户ID') {
        if (title2 != null && (title2 == "作品ID" || title2 == "作品ＩＤ")) {
            var ksurl = /(http:\/\/[a-zA-Z0-9_\.\-\_\/]+|https:\/\/[a-zA-Z0-9_\.\-\_\/]+)/;
            if (ksurl.test(value)) {
                var match = ksurl.exec(value);
                if (match != null) {
                    $("#value").val(match[0]);
                }
            }
        } else if (value != '' && value.indexOf('http') >= 0) {
            getkuaishouid();
        }
    } else if (title == '歌曲ID' || title == '歌曲ＩＤ' || title == '全民K歌歌曲链接' || title == '歌曲链接' || title == 'K歌歌曲链接') {
        if (value.indexOf("s=") == (-1)) {
            if (value.length != 12 && value.length != 16) {
                layer.msg('请输入正确的K歌作品链接，会自动获取哦！');
                return false;
            }
        } else if (value != '') {
            getsongid();
        }
    } else if (title == '火山作品链接' || title == '火山ID' || title == '火山ＩＤ' || title == '火山作品ID' || title == '火山视频链接' || title == '火山视频ID' || title == '火山视频ＩＤ') {
        if (value.indexOf("http") !== (-1) || value.indexOf("/s/") !== (-1)) {
            gethuoshanid();
        } else {
            var regx = /^[0-9]{17,22}$/;
            if (value.length != 19 || !regx.test(value)) {
                layer.alert('请输入正确的火山视频链接，会自动获取哦！');
                return false;
            }
        }
    } else if (title == '绿洲ID' || title == '绿洲作品ID' || title == '绿洲作品链接') {
        if (value != '' && value.indexOf('http') >= 0) {
            getlvzhouid();
        }
    } else if (title == '抖音ID' || title == '抖音作品ID' || title == '抖音视频ID' || title == '抖音ＩＤ' || title == '抖音主页ID' || title == '抖音作品链接' || title == '抖音视频链接' || title.indexOf('抖喑') >= 0 || title.indexOf('dy') >= 0 || title.indexOf('DY') >= 0) {
        if (value != '' && value.indexOf('http') >= 0) {
            getdouyinid();
        }
    } else if (title == '抖音主页ID' || title == '小抖主页ID' || title == '抖音主页链接' || title == '抖音主页链接' || title == '小抖主页链接' || title == 'DY主页ID' || title == 'DY主页链接') {
        if (value != '' && value.indexOf('http') >= 0) {
            getDouyinUserId();
        }
    } else if (title == '微视ID' || title == '微视作品ID' || title == '微视作品ID' || title == '微视作品链接' || title == '微视ＩＤ') {
        if (value != '' && value.indexOf('http') >= 0) {
            getweishiid();
        }
    } else if (title == '微视主页链接' || title == '微视主页ID') {
        if (value != '' && value.indexOf('http') >= 0) {
            getwsUserid();
        }
    } else if (title == '头条ID' || title == '头条ＩＤ') {
        if (value != '' && value.indexOf('http') >= 0) {
            gettoutiaoid();
        }
    } else if (title.indexOf('小红书作品ID') >= 0 || title.indexOf('小红书作品ＩＤ') >= 0 || title.indexOf('小红书ID') >= 0 || title.indexOf('小红书ＩＤ') >= 0 || title.indexOf('小红本ID') >= 0) {
        if (value != '' && value.indexOf('http') >= 0) {
            getxiaohongshuid();
        }
    } else if (name.indexOf('红书') >= 0 || name.indexOf('红本') >= 0 || name.indexOf('小红薯') >= 0) {
        if (title.indexOf('ID') >= 0 || title.indexOf('ＩＤ') >= 0 || title.indexOf('链接') >= 0 || title.indexOf('作品') >= 0) {
            if (value != '' && value.indexOf('http') >= 0) {
                getxiaohongshuid();
            }
        }
    } else if (title == '皮皮虾ID' || title == '皮皮虾作品ID' || title == '皮皮虾作品链接') {
        if (value != '' && value.indexOf('http') >= 0) {
            getpipixia();
        }
    } else if (title == '美拍ID' || title == '美拍ＩＤ' || title == '美拍作品ID' || title == '美拍作品ID' || title == '美拍视频ID' || title == '美拍作品链接') {
        if (value != '' && value.indexOf('http') >= 0) {
            getmeipaiid();
        }
    } else if (title == '哔哩哔哩视频ID' || title == '哔哩哔哩ID' || title == '哔哩视频ID' || title == '哔哩视频链接' || title == '哔哩作品链接') {
        if (value != '' && value.indexOf('http') >= 0) {
            getbiliid();
        }
    } else if (title == '哔哩主页链接' || title == '哔哩用户链接' || title == '哔哩用户ID') {
        if (value != '' && value.indexOf('http') >= 0) {
            getBiliUserId();
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
        var gettype = $('#inputvalue').attr("gettype");
        if ('undefined' != typeof(gettype) && gettype == 'shareid') {
            console.log("检测到获取作品ID");
            if (value != '' && value.indexOf('http') >= 0) {
                getShareID();
            }
        } else if ('undefined' != typeof(gettype) && gettype == 'shareurl') {
            console.log("检测到转换到链接");
            if (value != '' && value.indexOf('http') >= 0) {
                getShareUrl();
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
        }, function() {
            window.location.href = './user/login.php';
        }, function() {
            window.location.href = './user/reg.php';
        }, function() {
            layer.close(confirmobj);
        });
        return false;
    }
}

function openCart() {
    window.location.href = './?mod=cart';
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
$(document).ready(function() {
    $("#submit_buy").click(function() {
        var tid = $("#tid").val();
        if (typeof(tid) == 'undefined' || tid == 0 || tid == null || ($("#display_tool").length > 0 && $("#display_tool").attr('display') == "none")) {
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
            url: "ajax.php?act=pay",
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
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    if ($('#inputname').html() == '你的邮箱') {
                        $.cookie('email', inputvalue);
                    }
                    window.location.href = './?mod=order&orderid=' + data.trade_no;
                } else if (data.code == 1) {
                    $('#alert_frame').hide();
                    if ($('#inputname').html() == '你的邮箱') {
                        $.cookie('email', inputvalue);
                    }
                    layer.alert(data.msg, {
                        end: function() {
                            window.location.href = '?buyok=1';
                        }
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
                        url: "ajax.php?act=captcha&t=" + (new Date()).getTime(),
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
                } else if (data.code == 3) {
                    layer.alert(data.msg, {
                        closeBtn: false
                    }, function() {
                        window.location.reload();
                    });
                } else if (data.code == 4) {
                    var confirmobj = layer.confirm('请登录后再购买，是否现在登录？', {
                        btn: ['登录', '注册', '取消']
                    }, function() {
                        window.location.href = './user/login.php';
                    }, function() {
                        window.location.href = './user/reg.php';
                    }, function() {
                        layer.close(confirmobj);
                    });
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function(req, err, data) {
                layer.close(ii);
                layer.alert('服务器错误，请稍后重试：' + data);
            }
        });
    });
    $("#num_add").click(function() {
        var i = parseInt($("#num").val());
        if ($("#need").val() == '') {
            layer.alert('请先选择商品');
            return false;
        }
        var multi = $('#tid').attr('multi');
        var count = parseInt($('#tid').attr('count'));
        if (multi == '0') {
            layer.alert('该商品不支持选择数量');
            return false;
        }
        i++;
        $("#num").val(i);
        var price = parseFloat($('#tid').attr('price'));
        var prices = $('#tid').attr('prices');
        price = price * i;
        if (typeof(prices) != 'undefined' && prices != '' && prices != 'null' && typeof(isLogin2) != 'undefined' && isLogin2 == 1) {
            price = price - getFinalPrice(price, prices, i);
        }
        count = count * i;
        count = count * getAllTimes() * getAllCount();
        if (count > 1) $('#need').val('￥' + price.toFixed(2) + "元 ➠ " + count + "个");
        else $('#need').val('￥' + price.toFixed(2) + "元");
    });
    $("#num_min").click(function() {
        var i = parseInt($("#num").val());
        if (i <= 1) {
            layer.msg('最低下单一份哦！');
            return false;
        }
        if ($("#need").val() == '') {
            layer.alert('请先选择商品');
            return false;
        }
        var multi = $('#tid').attr('multi');
        var count = parseInt($('#tid').attr('count'));
        if (multi == '0') {
            layer.alert('该商品不支持选择数量');
            return false;
        }
        i--;
        if (i <= 0) i = 1;
        $("#num").val(i);
        var price = parseFloat($('#tid').attr('price'));
        var prices = $('#tid').attr('prices');
        price = price * i;
        if (typeof(prices) != 'undefined' && prices != '' && prices != 'null' && typeof(isLogin2) != 'undefined' && isLogin2 == 1) {
            price = price - getFinalPrice(price, prices, i);
        }
        count = count * i;
        count = count * getAllTimes() * getAllCount();
        if (count > 1) $('#need').val('￥' + price.toFixed(2) + "元 ➠ " + count + "个");
        else $('#need').val('￥' + price.toFixed(2) + "元");
    });
    $("#num").keyup(function() {
        var i = parseInt($("#num").val());
        if (isNaN(i)) return false;
        var price = parseFloat($('#tid').attr('price'));
        var count = parseInt($('#tid').attr('count'));
        var prices = $('#tid').attr('prices');
        if (i < 1) {
            $("#num").val(1);
            i = 1;
        }
        price = price * i;
        if (typeof(prices) != 'undefined' && prices != '' && prices != 'null' && typeof(isLogin2) != 'undefined' && isLogin2 == 1) {
            price = price - getFinalPrice(price, prices, i);
        }
        count = count * i;
        count = count * getAllTimes() * getAllCount();
        if (count > 1) $('#need').val('￥' + price.toFixed(2) + "元 ➠ " + count + "个");
        else $('#need').val('￥' + price.toFixed(2) + "元");
    });

    getPoint();
    
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
                success: function(data) {
                    if (data.code == 0) {
                        var lay = layer.open({
                            title: ['靓仔靓女你好', 'text-align:center;'],
                            content: '好友邀你领取免费会员名片赞等福利！',
                            btn: ['我要领取', '残忍拒绝'],
                            yes: function() {
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
                                    success: function(data) {
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
                            btn2: function() {
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
            success: function(dom, index) {
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
            url: "ajax.php?act=captcha&t=" + (new Date()).getTime(),
            type: "get",
            dataType: "json",
            success: function(data) {
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
    var postTkey = function(captchaObj) {
        captchaObj.appendTo('#captcha');
        captchaObj.onReady(function() {
            $("#wait").hide();
        }).onSuccess(function() {
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
                success: function(data) {
                    layer.closeAll();
                    if (data.code == 0) {
                        $.cookie(t, t, 24 * 60 * 60 * 30);
                        if (data.invite_jump && data.invite_jump == 1) {
                            layer.msg("正在跳转到领取页面...");
                            setTimeout(function() {
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
});