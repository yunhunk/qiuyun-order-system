//星河多规格属性表单通用处理 V1.0
var serverPath = 'string' === typeof serverPath ? serverPath : './';
var queryVal = null,
    orderid = null,
    is_showWork = false,
    is_orderWork = false,
    orderPage = 1,
    filename = '',
    inputDisabled = false;
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
window.layer = layer;
var chnemMall = {
    options: {},
    inputDisabled: false,
    shopIndex: 0,
    attrIndex: 0,
    specs_id: 0,
    init: function (tid) {
        if (parseInt(tid) < 1) {
            return;
        }
        chnemMall.bindEvent();
        chnemMall.getPoint();
        var specs_id = parseInt($("#specs_id").val());
        if (specs_id > 0) {
            chnemMall.specs_id = specs_id;
            $.ajax({
                type: "POST",
                url: serverPath + "ajax.php?act=getSpecsInfo",
                dataType: "json",
                data: {
                    tid: tid
                },
                success: function (data) {
                    if (data.code == 0) {
                        //这里不能用this和that，否则数据不会保存
                        chnemMall.options = data.data;
                        console.log(chnemMall.options);
                        chnemMall.shopClick($('.shopOptions a:eq(0)'));
                        chnemMall.attrClick($('.attrOptions a:eq(0)'));
                    }
                },
                error: function (data) {
                    layer.msg("出现错误，联系网站客服处理！" + data);
                    return false;
                }
            });
        } else {
            chnemMall.specs_id = 0;
            this.setInfo();
        }
    },
    isObject: function ($var) {
        return $var != 'null' && (typeof ($var) == 'object' || typeof ($var) == 'array');
    },
    //属性版本选中
    attrClick: function (that) {
        chnemMall.attrIndex = $(that).attr('index');
        $('.attrOptions a').removeClass('active');
        $(that).addClass('active');
        var val = $(that).attr('value');
        $("#inputattr").val(val);
    },
    //商品选中
    shopClick: function (that) {
        chnemMall.shopIndex = $(that).attr('index');
        $('.shopOptions a').removeClass('active');
        $(that).addClass('active');
        this.setInfo();
    },
    //绑定事件
    bindEvent: function () {
        //份数减少
        $("#num_min").bind('click', function () {
            var i = parseInt($("#num").val());
            if (i <= 1) {
                layer.msg('最低下单一份哦！');
            }
            var multi = $('#tid').attr('multi');
            if (multi == '0') {
                layer.alert('该商品不支持选择数量');
                return false;
            }
            i--;
            if (i <= 0) i = 1;
            $("#num").val(i);
            chnemMall.setInfo();
        });
        //份数增加
        $("#num_add").bind('click', function () {
            var i = parseInt($("#num").val());
            var multi = $('#tid').attr('multi');
            if (multi == '0') {
                layer.alert('该商品不支持选择数量');
                return false;
            }
            i++;
            $("#num").val(i);
            chnemMall.setInfo();
        });
        //份数光标移开
        $("#num").bind('blur', function () {
            var i = parseInt($("#num").val());
            if (i <= 0) i = 1;
            $("#num").val(i);
            chnemMall.setInfo();
        });
        //输入框光标移开
        $("#inputvalue").bind('blur', function () {
            chnemMall.checkInput();
        });
        //查看商品大图
        $("#shop_icon").bind('click', function () {
            chnemMall.shopView();
        });
    },
    getFinalPrice: function (price, prices, num) {
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
    },
    //设置商品信息
    setInfo: function () {
        if (chnemMall.specs_id > 0) {
            this.setIcon();
            this.setStock();
        } else {
            if ($("#shop_value").length > 0) {
                var num = parseInt($("#num").val());
                if (num < 1) {
                    num = 1;
                }
                var value = parseInt($("#value").val());
                if (value < 1) {
                    value = 1;
                }
                var count = num * value;
                console.log('扩展前', count);
                count = count * this.getAllTimes() * this.getAllCount();
                console.log('扩展后', count);
                $("#count").val(count);
                $("#shop_value").show();
            }
        }
        this.setPrice();
    },
    //设置库存
    setStock: function () {
        $("#shop_stock").html(chnemMall.options[chnemMall.shopIndex].stock);
    },
    //设置价格
    setPrice: function () {
        var num = parseInt($("#num").val());
        if (num < 1) {
            num = 1;
        }
        var prices = $("#tid").attr("prices");
        //console.log(chnemMall.options[index].price);
        if (chnemMall.specs_id > 0) {
            var price = this.getFloat(chnemMall.options[chnemMall.shopIndex].price * num, 2);
            if (chnemMall.shopIndex == 0) {
                $("#cprice").html(price);
            }
            $("#stock_id").val(chnemMall.options[chnemMall.shopIndex].id);
        } else {
            var price = this.getFloat($("#price").val() * num, 2);
        }
        if (typeof (prices) == 'string' && prices != '' && prices != 'null') {
            price = price - chnemMall.getFinalPrice(price, prices, num);
        }
        $("#shop_price").html(price);
        $("#newNeed").html(price);
    },
    //设置图标
    setIcon: function () {
        var icon = chnemMall.options[chnemMall.shopIndex].icon;
        if (typeof (icon) == 'string') {
            if ("" != icon && "/" != icon) {
                if (icon.indexOf('http') == -1) icon = '/' + icon;
            } else {
                icon = '/assets/img/Product/default.png';
            }
            $("#shop_icon").attr('src', icon);
        }
        console.log('图标[' + chnemMall.shopIndex + ']：' + icon);
    },
    //查看图片
    shopView: function () {
        var options = chnemMall.options;
        var images = new Array();
        $.each(options, function (key, item) {
            var img = item.icon;
            if ("string" === typeof (img) && "" !== img && "/" !== img) {
                if (img.indexOf('http') == -1) img = '/' + img;
            } else {
                img = '/assets/img/Product/default.png';
            }
            images[key] = {
                "alt": "Image" + (key + 1),
                "pid": key, //图片id
                "src": img, //原图地址
                "thumb": img //缩略图地址
            };
        });
        var photos = {
            "title": "商品大图查看",
            "id": 1,
            "start": chnemMall.shopIndex,
            "data": images
        };
        layer.photos({
            photos: photos,
            anim: 1,
            shift: 1
        });
    },
    //转浮点数
    getFloat: function (number, n) {
        n = n ? parseInt(n) : 2;
        if (n <= 0) return Math.ceil(number);
        number = Math.round(number * Math.pow(10, n)) / Math.pow(10, n);
        return number;
    },
    //商品表单初始化
    getPoint: function () {
        //var tid = $('#tid').val();
        var multi = $('#tid').attr('multi');
        var count = $('#tid').attr('count');
        //重复下单
        if (multi == 1) {
            $('#shop_num').show();
        } else {
            $('#shop_num').hide();
        }
        $('#inputsname').html("");
        var inputname = $('#tid').attr('input').toString();
        if (typeof (inputname) == 'string' || "" == inputname || "null" == inputname) {
            var input = $('#tid').attr('inputname');
            if ('undefined' != typeof (input) && 'null' != input) {
                inputname = input;
            }
        }
        var price = $('#price').val();
        if (inputname == 'hide') {
            $('#inputsname').append('<input type="hidden" name="inputvalue" id="inputvalue" value="' + $.cookie('mysid') + '"/>');
        } else if ("string" == typeof (inputname) && "" != inputname) {
            var placeholder = "";
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
            var extendEventBlur = '';
            var extendEventAttr = '';
            //输入框价格变量
            if (inputname.indexOf('[int:') > 0) {
                extendEventBlur = 'chnemMall.numChange(this);';
                var str1 = inputname.split('[int:')[1];
                inputname = inputname.split('[int:')[0];
                extendEventAttr = ' price="' + price + '" extendBlurAttr="' + str1.split(']')[0] + '"';
            }
            if (inputname.indexOf("&") !== (-1)) {
                placeholder = inputname.split('&')[1];
                inputname = inputname.split('&')[0];
            } else {
                placeholder = '输入' + inputname;
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
                $('#inputsname').append('<div class="input-box"><p class="tit" id="inputname">' + inputname + '</p><div class="input-form"><select name="inputvalue" id="inputvalue" placeholder="' + placeholder + '" class="input-form-control">' + addstr + '</select></div></div>');
            } else if (extendEventBlur == '' && inputname.indexOf('[!shareurl]') < 0 && inputname.indexOf('[') > 0 && inputname.indexOf(']') > 0) {
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
                $('#inputsname').append('<div class="input-box"><p class="tit" id="inputname">' + inputname + '</p><div class="input-form"><select name="inputvalue" id="inputvalue" placeholder="' + placeholder + '" ' + gettype + ' class="input-form-control">' + addstr + '</select></div></div>');
            } else {
                $('#inputname').html(inputname);
                var addstr = '';
                if (inputname.indexOf('[!shareurl]') >= 0) {
                    inputname = inputname.replace('[!shareurl]', '');
                    addstr = 'shareurl="!shareurl"';
                }
                $('#inputsname').append('<div class="input-box"><p class="tit" id="inputname">' + inputname + '</p><div class="input-form"><input type="text" name="inputvalue" id="inputvalue" ' + addstr + ' ' + gettype + ' placeholder="' + placeholder + '" value="' + ($_GET['qq'] ? $_GET['qq'] : '') + '" class="input-form-control input" required onblur="chnemMall.checkInput();' + extendEventBlur + '" ' + extendEventAttr + '/></div></div>');
            }
        } else {
            $('#inputsname').append('<div class="input-box"><p class="tit" id="inputname">下单ＱＱ</p><div class="input-form"><input type="text" name="inputvalue" id="inputvalue" placeholder="输入下单QQ" value="' + ($_GET['qq'] ? $_GET['qq'] : '') + '" class="input-form-control" required onblur="chnemMall.checkInput()"/></div></div>');
        }
        var inputsname = $("#tid").attr('inputs');
        if (typeof (inputsname) == 'string' || "" == inputsname || "null" == inputsname) {
            var inputs = $('#tid').attr('inputsname');
            if ('undefined' != typeof (inputs) && 'null' != inputs) {
                inputsname = inputs;
            }
        }
        if ("string" == typeof (inputsname) && "" != inputsname) {
            $.each(inputsname.split('|'), function (i, value) {
                value = value.replace('[!shareurl]', '');
                value = value.replace('[shareurl]', '');
                value = value.replace('[shareid]', '');
                value = value.replace('[zpid]', '');
                var extendEventBlur = '';
                var extendEventAttr = '';
                //输入框价格变量
                if (value.indexOf('[int:') > 0) {
                    extendEventBlur = 'chnemMall.numChange(this);';
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
                    $('#inputsname').append('<div class="input-box"><p class="tit" id="inputname' + (i + 2) + '">' + selectname + '</p><div class="input-form"><select type="text" name="inputvalue' + (i + 2) + '" id="inputvalue' + (i + 2) + '" class="input-form-control" >' + addstr + '</select></div></div>');
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
                    $('#inputsname').append('<div class="input-box"><p class="tit" id="inputname' + (i + 2) + '">' + selectname + '</p><div class="input-form"><select type="text" name="inputvalue' + (i + 2) + '" id="inputvalue' + (i + 2) + '" class="input-form-control" >' + addstr + '</select></div></div>');
                } else {
                    if (value == '说说ID' || value == '说说ＩＤ') var addstr = '<div class="input-btn input-btn-info onclick" onclick="chnemMall.inputMethod.get_shuoshuo(\'inputvalue' + (i + 2) + '\',$(\'#inputvalue\').val())">自动获取</div>';
                    else if (value == '日志ID' || value == '日志ＩＤ') var addstr = '<div class="input-btn input-btn-info onclick" onclick="chnemMall.inputMethod.get_rizhi(\'inputvalue' + (i + 2) + '\',$(\'#inputvalue\').val())">自动获取</div>';
                    else if (value == '收货人地址' || value == '收货地址') var addstr = '<div class="input-btn input-btn-info onclick" onclick="chnemMall.inputMethod.inputAddress(\'inputvalue' + (i + 2) + '\')">填写地址</div>';
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
                    $('#inputsname').append('<div class="input-box"><p class="tit" id="inputname' + (i + 2) + '">' + ibtn + '</p><div class="input-form"><input type="text" name="inputvalue' + (i + 2) + '" placeholder="' + placeholder + '" id="inputvalue' + (i + 2) + '" value="" class="input-form-control input" onblur="' + extendEventBlur + '" ' + extendEventAttr + ' required/>' + addstr + '</div></div>');
                }
            });
        }
        //数据识别
        var attach = $('#tid').attr('attach');
        if (attach == 'kuaishou') {
            $('#inputvalue').attr("placeholder", "在此输入作品链接 可自动获取");
        } else if (attach == 'qmkg') {
            $('#inputvalue').attr("placeholder", "在此输入歌曲的分享链接 可自动获取");
        } else if (attach == 'huoshan') {
            $('#inputvalue').attr("placeholder", "在此输入火山视频的链接 可自动获取");
        } else if (attach == 'douyin') {
            $('#inputvalue').attr("placeholder", "在此输入作品分享链接 可自动获取");
        } else if (attach == 'toutiao') {
            $('#inputvalue').attr("placeholder", "在此输入头条链接 可自动获取");
        } else if (attach == 'meipai') {
            $('#inputvalue').attr("placeholder", "在此输入视频链接 可自动获取");
        } else if (attach == 'shuoshuo' || $("#inputname2").html() == '说说ID' || $("#inputname").html() == '说说ＩＤ') {
            $('#inputvalue').attr("placeholder", "在此输入QQ号码 点击自动获取");
            $('#inputvalue2').attr("placeholder", "填写QQ号码后点击→ ");
            $("#inputvalue2").attr('disabled', true);
        } else if (attach == 'bili') {
            $('#inputvalue').attr("placeholder", "在此输入视频链接 可自动获取");
        } else if (attach == 'zuiyou') {
            $('#inputvalue').attr("placeholder", "在此输入最右帖子链接 可自动获取");
        } else if (attach == 'qmvideo') {
            $('#inputvalue').attr("placeholder", "在此输入全民小视频链接 可自动获取");
        } else if (attach == 'meitu') {
            $('#inputvalue').attr("placeholder", "在此输入美图作品链接 可自动获取");
        } else {
            var inputname = $('#tid').attr('input');
            if (typeof inputname === 'string' && inputname != "" && inputname != "null") {
                if (inputname.indexOf('[shareid]') >= 0) {
                    $('#inputvalue').attr("placeholder", '粘贴分享链接，自动获取用户ID');
                    $('#inputvalue').attr('gettype', 'shareid');
                } else if (inputname.indexOf('[shareurl]') >= 0) {
                    $('#inputvalue').attr("placeholder", '粘贴分享链接，自动格式化链接');
                    $('#inputvalue').attr('gettype', 'shareurl');
                } else if (inputname.indexOf('[zpid]') >= 0) {
                    $('#inputvalue').attr("placeholder", '粘贴分享链接,自动获取作品ID');
                    $('#inputvalue').attr('gettype', 'zpid');
                } else {
                    $('#inputvalue').attr('gettype', '');
                }
            }
            var inputsname = $('#tid').attr('inputs');
            if (typeof inputsname === 'string' && inputsname != "" && inputsname != "null") {
                if (inputname.indexOf('[zpid]') >= 0) {
                    $('#inputvalue').attr('gettype', 'zpid');
                    $('#inputvalue2').attr('gettype', 'zpid');
                } else {
                    $('#inputvalue').attr('gettype', '');
                }
            }
        }
        if ($('#tid').attr('isfaka') == 1) {
            $('#inputvalue').attr("placeholder", "用于接收卡密以及查询订单使用");
            $.ajax({
                type: "POST",
                url: serverPath + "ajax.php?act=getleftcount",
                data: {
                    tid: $('#tid option:selected').val()
                },
                dataType: 'json',
                success: function (data) {
                    $('#shop_stock').val(data.count)
                }
            });
            if ($.cookie('email')) $('#inputvalue').val($.cookie('email'));
        }
        var alert = $('#tid').attr('alert');
        if (typeof (alert) == 'string' && alert != '' && alert != 'null') {
            var ii = layer.alert('' + unescape(alert) + '', {
                btn: ['我知道了'],
                title: '商品注意事项'
            }, function () {
                layer.close(ii);
            });
        }
    },
    //表单过滤
    inputFilter: function (inputvalue) {
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
    },
    numChange: function (el) {
        var value = parseInt($(el).val());
        var rule = $(el).attr('extendBlurAttr');
        if (!isNaN(rule) && value % rule == 0) {
            var num = chnemMall.getAllTimes();
            var cost = $(el).attr('price');
            var price = cost * num;
            $('#price').val(price.toFixed(2));
        } else {
            var tips = (rule * 1) + '、' + (rule * 2) + '、' + (rule * 4);
            layer.alert('此项内容必须是' + rule + '的整倍数，如' + tips + '等！');
        }
        chnemMall.setInfo();
    },
    getTimes: function (el) {
        var value = parseInt($(el).val());
        var rule = $(el).attr('extendBlurAttr');
        if (!!rule && !isNaN(rule) && value >= rule && value % rule == 0) {
            var num = parseInt(value / rule);
            return num;
        }
        return 1;
    },
    getAllTimes: function () {
        var els = $('.input');
        var count = 1;
        for (var i = 0; i < els.length; i++) {
            count = count * chnemMall.getTimes(els[i]);
        }
        //console.log('共' + count + '倍');
        return count;
    },
    getAllCount: function () {
        var els = $('.input');
        var count = 1;
        for (var i = 0; i < els.length; i++) {
            var rule = parseInt($(els[i]).attr('extendBlurAttr'));
            console.log('基数', rule);
            if (!isNaN(rule) && rule > 0) {
                count = count * rule;
            }
        }
        return count;
    },
    //表单验证
    checkInput: function () {
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
        var name = $('#tid').attr('shopname');
        var attach = $("#tid").attr('attach');
        var shareurl = $("#inputvalue").attr('shareurl');
        var gettype = $("#inputvalue").attr('gettype');
        if (shareurl != '!shareurl' && gettype != '!shareurl') {
            this.inputFilter(value);
        }
        if (typeof (name) != 'undefined' && name != 'null') {} else {
            name = '';
        }
        if (attach == 'kuaishou' || attach == 'kuaishou_uid') {
            if (value != '' && value.indexOf('http') >= 0) {
                var ksurl = /(http:\/\/[a-zA-Z0-9_\.\-\_\/]+|https:\/\/[a-zA-Z0-9_\.\-\_\/]+)/;
                if (ksurl.test(value)) {
                    var match = ksurl.exec(value);
                    if (match != null) {
                        $("#value").val(match[0]);
                    }
                }
                chnemMall.inputMethod.getkuaishouid();
            }
        } else if (attach == 'qmkg') {
            if (value.indexOf("s=") == (-1)) {
                if (value.length != 12 && value.length != 16) {
                    layer.msg('请输入正确的K歌作品链接，会自动获取哦！');
                    return false;
                }
            } else if (value != '') {
                chnemMall.inputMethod.getsongid();
            }
        } else if (attach == 'huoshan') {
            if (value.indexOf("http") !== (-1) || value.indexOf("/s/") !== (-1)) {
                chnemMall.inputMethod.gethuoshanid();
            } else {
                var regx = /^[0-9]{17,22}$/;
                if (value.length != 19 || !regx.test(value)) {
                    layer.alert('请输入正确的视频链接，会自动获取哦！');
                    return false;
                }
            }
        } else if (attach == 'lvzhou') {
            if (value != '' && value.indexOf('http') >= 0) {
                chnemMall.inputMethod.getlvzhouid();
            }
        } else if (attach == 'douyin') {
            if (value != '' && value.indexOf('http') >= 0) {
                chnemMall.inputMethod.getdouyinid();
            }
        } else if (attach == 'douyin_uid') {
            if (value != '' && value.indexOf('http') >= 0) {
                chnemMall.inputMethod.getDouyinUserId();
            }
        } else if (attach == 'weishi') {
            if (value != '' && value.indexOf('http') >= 0) {
                chnemMall.inputMethod.getweishiid();
            }
        } else if (attach == 'weishi_uid') {
            if (value != '' && value.indexOf('http') >= 0) {
                chnemMall.inputMethod.getwsUserid();
            }
        } else if (attach == 'toutiao') {
            if (value != '' && value.indexOf('http') >= 0) {
                chnemMall.inputMethod.gettoutiaoid();
            }
        } else if (attach == 'xhs') {
            if (value != '' && value.indexOf('http') >= 0) {
                chnemMall.inputMethod.getxiaohongshuid();
            }
        } else if (attach == 'pipix') {
            if (value != '' && value.indexOf('http') >= 0) {
                chnemMall.inputMethod.getpipixia();
            }
        } else if (attach == 'meipai') {
            if (value != '' && value.indexOf('http') >= 0) {
                chnemMall.inputMethod.getmeipaiid();
            }
        } else if (attach == 'bili') {
            if (value != '' && value.indexOf('http') >= 0) {
                chnemMall.inputMethod.getbiliid();
            }
        } else if (attach == 'bili_uid') {
            if (value != '' && value.indexOf('http') >= 0) {
                chnemMall.inputMethod.getBiliUserId();
            }
        } else if (attach == 'zuiyou') {
            if (value != '' && value.indexOf('http') >= 0) {
                chnemMall.inputMethod.getzuiyouid();
            }
        } else if (attach == 'qmvideo') {
            if (value != '' && value.indexOf('http') >= 0) {
                chnemMall.inputMethod.getquanminid();
            }
        } else if (attach == 'meitu') {
            if (value != '' && value.indexOf('http') >= 0) {
                chnemMall.inputMethod.getmeituid();
            }
        } else {
            var gettype = $("#inputvalue").attr('gettype');
            if (typeof gettype == 'string' && gettype != "") {
                if (gettype == 'shareid' && value.indexOf('http') >= 0) {
                    chnemMall.getShareId();
                } else if (gettype == 'zpid' && value.indexOf('http') >= 0) {
                    chnemMall.getZpId();
                } else if (gettype == 'shareurl') {
                    chnemMall.inputFilter(value);
                }
            } else if ($("#inputvalue2").length > 0) {
                var gettype = $("#inputvalue2").attr('gettype');
                if (typeof gettype == 'string' && gettype != "") {
                    if (gettype == 'zpid' && value2.indexOf('http') >= 0) {
                        chnemMall.getZpId();
                    }
                }
            }
        }
    },
    getShareUrl: function () {
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
    },
    getShareId: function () {
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
    },
    getZpId: function () {
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
    },
    //加入购物车
    addCart: function () {
        var tid = $("#ddid").length > 0 ? $("#ddid").val() : $("#tid").val();
        if (tid < 1) {
            layer.alert('请选择正确的商品面值版本！');
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
        chnemMall.checkInput();
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
                inputattr: $("#inputattr").length > 0 ? $("#inputattr").val() : '',
                stock_id: $("#stock_id").val(),
                num: $("#num").val(),
                hashsalt: hashsalt
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg("加入购物车成功");
                    if ($("input[id='cart_count']").length > 0) {
                        $("input[id='cart_count']").val(data.cart_count);
                    }
                    if ($("*[id='cart_count']").length > 0) {
                        $("*[id='cart_count']").text(data.cart_count);
                    }
                } else if (data.code == 4) {
                    layer.alert(data.msg, {
                        end: function () {
                            window.location.href = '/?act=login';
                        }
                    });
                } else {
                    layer.alert(data.msg);
                }
            }
        });
    },
    //免费商品验证
    handlerEmbed: function (captchaObj) {
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
    },
    //提交订单
    buyOrder: function () {
        var tid = $("#ddid").length > 0 ? $("#ddid").val() : $("#tid").val();
        if (tid < 1) {
            layer.alert('请选择正确的商品面值版本！');
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

        var code = $("#code").val();

        chnemMall.checkInput();
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
                inputattr: $("#inputattr").length > 0 ? $("#inputattr").val() : '',
                stock_id: $("#stock_id").val(),
                num: $("#num").val(),
                hashsalt: hashsalt,
                code: code
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    //window.location.href = '/?mod=pay.php?type=' + type + '&trade_no=' + trade_no;
                    if ($('#inputname').html() == '你的邮箱') {
                        $.cookie('email', inputvalue);
                    }
                    var paymsg = '';
                    if (data.pay_alipay > 0) {
                        paymsg += '<button class="input-btn input-btn-default input-btn-block" onclick="chnemMall.dopay(\'alipay\',\'' + data.trade_no + '\')" style="margin-top:10px;"><img width="20" src="/assets/icon/alipay.ico" class="logo">支付宝</button>';
                    }
                    if (data.pay_qqpay > 0) {
                        paymsg += '<button class="input-btn input-btn-default input-btn-block" onclick="chnemMall.dopay(\'qqpay\',\'' + data.trade_no + '\')" style="margin-top:10px;"><img width="20" src="/assets/icon/qqpay.ico" class="logo">QQ钱包</button>';
                    }
                    if (data.pay_wxpay > 0) {
                        paymsg += '<button class="input-btn input-btn-default input-btn-block" onclick="chnemMall.dopay(\'wxpay\',\'' + data.trade_no + '\')" style="margin-top:10px;"><img width="20" src="/assets/icon/wechat.ico" class="logo">微信支付</button>';
                    }
                    if (data.pay_rmb > 0) {
                        paymsg += '<button class="input-btn input-btn-success input-btn-block" onclick="chnemMall.dopay(\'rmb\',\'' + data.trade_no + '\')">使用余额支付（剩' + data.user_rmb + '元）</button>';
                    }
                    layer.alert('<center><h2 style="color:red">￥' + data.need + '元</h2><hr class="input-btn-hr">' + paymsg + '<hr class="input-btn-hr"><a class="input-btn input-btn-danger input-btn-block" onclick="chnemMall.cancel(\'' + data.trade_no + '\')">取消订单</a></center>', {
                        btn: [],
                        title: '提交订单成功',
                        closeBtn: false
                    });
                } else if (data.code == 1) {
                    $('#alert_frame').hide();
                    if ($('#inputname').html() == '你的邮箱') {
                        $.cookie('email', inputvalue);
                    }
                    layer.alert('领取成功！', function () {
                        window.location.href = '?buyok=1';
                    });
                } else if (data.code == 2 || data.code == 4) {
                    if (data.code == 2) {
                        layer.open({
                            type: 1,
                            title: '完成验证',
                            skin: 'layui-layer-rim',
                            area: ['320px', '100px'],
                            content: '<div id="captcha"><div id="captcha_text">正在加载验证码</div><div id="captcha_wait"><div class="loading"><div class="loading-dot"></div><div class="loading-dot"></div><div class="loading-dot"></div><div class="loading-dot"></div></div></div></div>',
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
                                            }, chnemMall.handlerEmbed);
                                        }
                                    });
                                });
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
                        window.location.href = '/?act=login';
                    }, function () {
                        window.location.href = '/?act=reg';
                    }, function () {
                        layer.close(confirmobj);
                    });
                } else {
                    layer.alert(data.msg);
                }
            }
        });
    },
    dopay: function (type, orderid) {
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
                                window.location.href = '?buyok=1';
                            } else if (data.code == 2) {
                                alert(data.msg);
                                window.location.href = '?mod=faka&id=' + data.orderid + '&skey=' + data.skey;
                            } else if (data.code == -2) {
                                alert(data.msg);
                                window.location.href = '?buyok=1';
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
            }
        });
        //window.location.href = '/mod=pay.php?type=' + type + '&trade_no=' + orderid;
    },
    cancel: function (id) {
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
    },
    inputMethod: {
        inputAddress: function (el) {
            if ('number' == typeof (isLogin2) && $("#address_list").length > 0) {
                $("#addr_el").val(el);
                $("#address_list").modal('show');
            } else {
                if (typeof ($("body").distpicker) !== 'function') {
                    return layer.alert("省市区支持库文件未引入！请联系平台客服处理");
                }
                var html = '<div class="panel-body"><div id="AddressContent" data-toggle="distpicker"><div class="size2_1">' + '<div class="input-box"><div class="input-form"><div class="input-btn">选择省份</div><select class="input-form-control" id="province"><option>省份加载中</option></select></div></div>' + ' <div class="input-box"><div class="input-form"><div class="input-btn">选择市区</div><select class="input-form-control" id="city"><option>市区加载中</option></select></div></div>' + ' <div class="input-box"><div class="input-form"><div class="input-btn">选择县镇</div><select class="input-form-control" id="district"><option>县镇加载中</option></select></div></div>' + '<div class="input-box"><div class="input-form"><div class="input-btn">详细街道</div><input id="street" class="input-form-control" type="text" value=""/></div></div>' + '</div></div></div><script>var item=$("#AddressContent")||!layer.alert("出错了，加载省份时出现问题！");item.distpicker({ province: "-- 请选择省份 --", city: "-- 请选择市 --", district: "-- 请选择区 --"});</script>';
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
                        $('#' + el).val(address);
                        //console.log('地址：'+address);
                        layer.closeAll();
                    }
                });
            }
        },
        get_shuoshuo: function (id, uin, km, page) {
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
                                $('#show_shuoshuo').html('<div class="input-box"><div class="input-form"><div class="input-btn input-btn-info onclick" title="上一页" onclick="inputMethod.get_shuoshuo(\'' + id + '\',$(\'#km_inputvalue\').val(),' + km + ',' + lastpage + ')"><i class="fa fa-chevron-left"></i></div><select id="shuoid" class="input-form-control" onchange="inputMethod.set_shuoshuo(\'' + id + '\');">' + addstr + '</select><div class="input-btn input-btn-info onclick" title="下一页" onclick="inputMethod.get_shuoshuo(\'' + id + '\',$(\'#km_inputvalue\').val(),' + km + ',' + nextpage + ')"><i class="fa fa-chevron-right"></i></div></div></div>');
                            } else {
                                $('#show_shuoshuo').html('<div class="input-box"><div class="input-form"><div class="input-btn input-btn-info onclick" title="上一页" onclick="inputMethod.get_shuoshuo(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + lastpage + ')"><i class="fa fa-chevron-left"></i></div><select id="shuoid" class="input-form-control" onchange="inputMethod.set_shuoshuo(\'' + id + '\');">' + addstr + '</select><div class="input-btn input-btn-info onclick" title="下一页" onclick="inputMethod.get_shuoshuo(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + nextpage + ')"><i class="fa fa-chevron-right"></i></div></div></div>');
                            }
                        } else {
                            if (km == 1) {
                                $('#km_inputsname').append('<div class="size2_1" id="show_shuoshuo"><div class="input-box"><div class="input-form"><div class="input-btn input-btn-info onclick" title="上一页" onclick="inputMethod.get_shuoshuo(\'' + id + '\',$(\'#km_inputvalue\').val(),' + km + ',' + lastpage + ')"><i class="fa fa-chevron-left"></i></div><select id="shuoid" class="input-form-control" onchange="inputMethod.set_shuoshuo(\'' + id + '\');">' + addstr + '</select><div class="input-btn input-btn-info onclick" title="下一页" onclick="inputMethod.get_shuoshuo(\'' + id + '\',$(\'#km_inputvalue\').val(),' + km + ',' + nextpage + ')"><i class="fa fa-chevron-right"></i></div></div></div></div>');
                            } else {
                                $('#inputsname').append('<div class="size2_1" id="show_shuoshuo"><div class="input-box"><div class="input-form"><div class="input-btn input-btn-info onclick" title="上一页" onclick="inputMethod.get_shuoshuo(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + lastpage + ')"><i class="fa fa-chevron-left"></i></div><select id="shuoid" class="input-form-control" onchange="inputMethod.set_shuoshuo(\'' + id + '\');">' + addstr + '</select><div class="input-btn input-btn-info onclick" title="下一页" onclick="inputMethod.get_shuoshuo(\'' + id + '\',$(\'#inputvalue\').val(),' + km + ',' + nextpage + ')"><i class="fa fa-chevron-right"></i></div></div></div></div>');
                            }
                        }
                        inputMethod.set_shuoshuo(id);
                    } else {
                        layer.alert(data.msg);
                    }
                }
            });
        },
        set_shuoshuo: function (id) {
            var shuoid = $('#shuoid').val();
            $('#' + id).val(shuoid);
        },
        getCourseList: function (el) {
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
                                $('#inputsname').append('<div class="size2_1" id="show_CourseList"><div class="input-box"><div class="input-form"><select id="courseList" class="input-form-control" onchange="inputMethod.set_course(this.value,\'' + el + '\');">' + addstr + '</select></div></div></div>');
                            }
                            $("#courseList").val(coursename);
                            inputMethod.set_course(coursename, el);
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
        },
        set_course: function (coursename, el) {
            $(el).val(coursename);
        },
        getkuaishouid: function () {
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
                            if (this.inputDisabled) $('#inputvalue').attr('disabled', true);
                            if ($('#inputvalue2').length > 0) {
                                $('#inputvalue2').val(data.videoid);
                                if (this.inputDisabled) $('#inputvalue2').attr('disabled', true);
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
                    if (this.inputDisabled) $('#inputvalue2').attr('disabled', true);
                }
            }
        },
        get_kuaishou: function (id, ksid) {
            chnemMall.inputMethod.getkuaishouid();
        },
        gethuoshanid: function () {
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
                            if (chnemMall.inputDisabled) $('#inputvalue').attr('disabled', true);
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
                    if (chnemMall.inputDisabled) $('#inputvalue').attr('disabled', true);
                    layer.msg('视频ID获取成功！下单即可');
                } catch (e) {
                    layer.alert('请输入正确的链接！');
                    return false;
                }
            }
        },
        getlvzhouid: function () {
            var songurl = $("#inputvalue").val();
            if (songurl == '') {
                layer.alert('请确保每项不能为空！');
                return false;
            }
            try {
                var songid = songurl.split('sid=')[1];
                $('#inputvalue').val(songid);
                if (chnemMall.inputDisabled) $('#inputvalue').attr('disabled', true);
                layer.msg('ID获取成功！提交下单即可');
            } catch (e) {
                layer.alert('请输入正确的链接！');
                return false;
            }
        },
        getdouyinid: function () {
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
                            if (chnemMall.inputDisabled) $('#inputvalue').attr('disabled', true);
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
                    if (chnemMall.inputDisabled) $('#inputvalue').attr('disabled', true);
                    layer.msg('ID获取成功！提交下单即可');
                } catch (e) {
                    layer.alert('请输入正确的链接！');
                    return false;
                }
                $('#inputvalue').val(songid);
            }
        },
        getDouyinUserId: function () {
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
                            if (chnemMall.inputDisabled) $('#inputvalue').attr('disabled', true);
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
                    if (chnemMall.inputDisabled) $('#inputvalue').attr('disabled', true);
                    layer.msg('主页ID获取成功！提交下单即可');
                } catch (e) {
                    layer.alert('请输入正确的主页链接！');
                    return false;
                }
                $('#inputvalue').val(songid);
            }
        },
        gettoutiaoid: function () {
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
                if (chnemMall.inputDisabled) $('#inputvalue').attr('disabled', true);
                layer.msg('ID获取成功！提交下单即可');
            } catch (e) {
                layer.alert('请输入正确的链接！');
                return false;
            }
            $('#inputvalue').val(songid);
        },
        getweishiid: function () {
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
                if (chnemMall.inputDisabled) $('#inputvalue').attr('disabled', true);
                layer.msg('ID获取成功！提交下单即可');
            } catch (e) {
                layer.alert('请输入正确的链接！');
                return false;
            }
            $('#inputvalue').val(songid);
        },
        getwsUserid: function () {
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
                if (chnemMall.inputDisabled) $('#inputvalue').attr('disabled', true);
                layer.msg('ID获取成功！提交下单即可');
            } catch (e) {
                layer.alert('请输入正确的主页分享链接！');
                return false;
            }
        },
        getpipixia: function () {
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
                            if (chnemMall.inputDisabled) $('#inputvalue').attr('disabled', true);
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
                    if (chnemMall.inputDisabled) $('#inputvalue').attr('disabled', true);
                } catch (e) {
                    layer.alert('请输入正确的作品链接！');
                    return false;
                }
            }
        },
        getxiaohongshuid: function () {
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
                            if (chnemMall.inputDisabled) $('#inputvalue').attr('disabled', true);
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
                    if (chnemMall.inputDisabled) $('#inputvalue').attr('disabled', true);
                } catch (e) {
                    layer.alert('请输入正确的链接！');
                    return false;
                }
            }
        },
        getbiliid: function () {
            var songurl = $("#inputvalue").val();
            if (songurl == '') {
                layer.alert('请确保每项不能为空！');
                return false;
            }
            var reg = new RegExp('(https|http):\\/\\/([\\w_\\-\\.]+)\\/([\\w_\\-]+)', 'i');
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
                            if (chnemMall.inputDisabled) $('#inputvalue').attr('disabled', true);
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
                    if (chnemMall.inputDisabled) $('#inputvalue').attr('disabled', true);
                } catch (e) {
                    layer.alert('请输入正确的视频链接！');
                    return false;
                }
            }
        },
        getBiliUserId: function () {
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
                    if (chnemMall.inputDisabled) $('#inputvalue').attr('disabled', true);
                } else {
                    layer.alert('请输入正确的主页链接！');
                    return false;
                }
            } catch (e) {
                layer.alert('请输入正确的主页链接！');
                return false;
            }
        },
        getzuiyouid: function () {
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
        },
        getmeipaiid: function () {
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
        },
        getquanminid: function () {
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
        },
        getmeituid: function () {
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
    },
    queryOrder: function (type, content, page) {
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
                    var status, orderid_1, x = 0;
                    $('#list').append('<tr><td colspan="6"><font color="red">温馨提示：订单超过24小时仍待处理请联系客服哦~</font></td></tr>');
                    $.each(data.data, function (i, item) {
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
                        chnemMall.showOrder(orderid_1.id, orderid_1.skey);
                    } else if (orderid != null && data.data['order_' + orderid] && (is_showWork == true || is_orderWork == true)) {
                        chnemMall.showOrder(orderid, data.data['order_' + orderid].skey);
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
    },
    showOrder: function (id, skey) {
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
                    if (data.status == 1 && data.show_endtime && data.endtime != "") {
                        item += '<tr><tr><td class="info" style="min-width:80px">完成时间</td><td colspan="5">' + data.endtime + '</td><tr>';
                    } else if (data.show_usetime && data.show_usetime == 1 && data.usetime != "") {
                        item += '<tr><tr><td class="info" style="min-width:80px">处理耗时</td><td colspan="5"><span style="background-color:#42a1ff;padding:4px 6px;border-radius:5px" id="order_usetime">0小时0分0秒</span></td><tr>';
                    }
                    if (typeof (data.kminfo) == "string" && data.kminfo) {
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
                    if (data.complain) {
                        if (data.works) {
                            item += '<tr style="padding-right: 0;"><td colspan="6"><a class="mdui-btn mdui-btn-block mdui-color-pink mdui-ripple" onclick="showWorks(' + id + ');">查看沟通记录</a></td></tr>';
                        } else {
                            if (!data.works) data.works = 0;
                            item += '<tr style="padding-right: 0;"><td colspan="6"><a class="mdui-btn mdui-btn-block mdui-color-pink mdui-ripple" onclick="work(' + id + ',' + (data.works.length > 0 ? 1 : 0) + ');">申请订单售后</a></td></tr>';
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
    },
    getUseTime: function ($times, el) {
        var runUseTime = function () {
            if ($(el).length < 1) {
                clearInterval(chnemMall.userTimeObj);
                return false;
            } else {
                $(el).html(chnemMall.getTimeToDay($timestamp));
                $timestamp = $timestamp + 1;
                return true;
            }
        };
        $timestamp = $times;
        chnemMall.userTimeObj = setInterval(runUseTime, 1000);
    },
    getTimeToDay: function ($timestamp) {
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
    },
    work: function (id, type) {
        layer.closeAll();
        if (type == 1) {
            chnemMall.showWorks(id);
            return false;
        } else {
            setTimeout(function () {
                $("#tousu_id").val(id);
                $('#tousu').modal("show");
            }, 500);
        }
    },
    showWorks: function (id) {
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
}
var tid = $("#tid").val();
if (typeof (tid) == 'string') {
    chnemMall.init(tid);
} else {
    layer.alert("商品信息出现错误，联系网站客服处理！");
}
var inputMethod = chnemMall.inputMethod;

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
                get[j[0]] = j[1] != "" ? j[1] : '1';
            } else {
                get[i] = '1';
            }
        });
    }
    var q = queryStr.split("&");
    q.forEach(function (i, index) {
        if (i.indexOf('=') >= 0) {
            var j = i.split("=");
            get[j[0]] = j[1] != "" ? j[1] : '1';
        } else {
            get[i] = '1';
        }
    });
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

$(document).ready(function () {
    //修复订单详情的商品详情可能会太宽超出屏幕
    var cssHtml = '<style type="text/css">.table tr td img{max-width: 100%;}</style>';
    if ($("head").length > 0) {
        $("head").append(cssHtml);
    } else {
        $("body").append(cssHtml);
    }
});