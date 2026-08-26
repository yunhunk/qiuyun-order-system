"use strict";
var kakayunObj = {},
    shangzhanObj = {};
var changeEvent = false,
    yunbaoSelect = "",
    kkySelect = "",
    kysSelect = "",
    kyxSelect = "",
    szwSelect = "",
    typeSelect = "",
    pageLoad = true;
var chenmObj = {
    pridList: {},
    isChange: false,
    specs_id: 0,
    tid: 0,
    action: 'add',
    init: function () {
        var act = $("#act").val();
        chenmObj.action = act;
        chenmObj.isChange = true;
        if ($('head').length > 0) {
            $('head').append('<style type="text/css">.input-group-addon {padding: 6px 8px;}.layui-layer-ico5 { background-position: -149px 0;}</style>');
        } else {
            $('body').prepend('<style>.input-group-addon {padding: 6px 8px;}.layui-layer-ico5 { background-position: -149px 0;}</style>');
        }
        $.ajax({
            type: "POST",
            url: "ajax.php?act=getPridList",
            dataType: "json",
            success: function (data) {
                if (data.code == 0) {
                    //这里不用this指向和that变量代替
                    chenmObj.pridList = data.data;
                    chenmObj.initForm();
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.msg("服务器错误初始化失败，请刷新试试！" + data);
                return false;
            }
        });
    },
    //初始化表单
    initForm: function () {
        if ($("#tid").length > 0) {
            var tid = $("#tid").val();
            chenmObj.tid = tid;
        }
        var prid = parseInt($("select[name='prid']").val());
        if ('number' == typeof (prid) && prid == 0) {
            //当使用规格模板时才初始化加载规格选项
            var specs_id = parseInt($("select[name='specs_id']").val());
            chenmObj.specs_id = specs_id;
            if (parseInt(chenmObj.tid) > 0) {
                chenmObj.input(chenmObj.tid);
            }
        }
        //绑定事件
        chenmObj.bindEvent();
    },
    bindEvent: function () {
        $("#price1_box input[name='price1']").bind('blur', function () {
            chenmObj.setOptionPrice(this);
        });
        $("#price").bind('blur', function () {
            chenmObj.setOptionPrice(this);
        });
    },
    addOption: function (act) {
        chenmObj.action = act;
        var index = parseInt($("#" + act + "_index").val());
        var num = parseInt($("#" + act + "_num").val());
        var html = this.getOptionHtml(act, {}, index, num);
        $("#" + act + "_options").append(html);
        $("#" + act + "_num").val(num + 1);
        $("#" + act + "_index").val(index + 1);
    },
    getOptionHtml: function (act, option, index, num) {
        chenmObj.action = act;
        act = typeof (option.title) == "string" ? act : 'add';
        option.id = typeof (option.id) != "undefined" ? option.id : '0';
        option.title = typeof (option.title) != "undefined" ? option.title : '';
        option.value = typeof (option.value) != "undefined" ? option.value : '';
        option.stock = typeof (option.stock) != "undefined" ? option.stock : '999';
        option.icon = typeof (option.icon) != "undefined" ? option.icon : '';
        option.prid = typeof (option.prid) != "undefined" ? option.prid : '0';
        option.price1 = typeof (option.price1) != "undefined" ? option.price1 : '';
        var html = `<tr id="${act}_option${index}">
            <td>
                <input type="hidden" sign="stock_id" name="options[${num}][stock_id]" value="${option.id}"/>
                <input type="text" sign="title" name="options[${num}][title]" value="${option.title}" class="form-control input-sm"/>
            </td>
            <td><input type="text" sign="value" onblur="chenmObj.checkValue(this,${index})" name="options[${num}][value]" value="${option.value}" class="form-control input-sm" placeholder=""/></td>
            <td><input type="text" sign="price1" name="options[${num}][price1]" value="${option.price1}" class="options_price1 form-control input-sm" placeholder=""/></td>
            <td><input type="text" sign="stock"  name="options[${num}][stock]" value="${option.stock}" class="form-control input-sm" placeholder=""/></td>
            <td><div class="input-group"><input type="file" name="icon_file" onchange="chenmObj.iconUpload(this)" parentElem="#${act}_option${index}" class="hide"/><input type="text" name="options[${num}][icon]" value="${option.icon}"  onfocus="chenmObj.iconFocus(this)" onblur="chenmObj.iconBlur(this)" class="form-control input-sm option_icon" placeholder=""/><a onclick="chenmObj.iconClick(this)" parentElem="#${act}_option${index}" class="input-group-addon" title="点击上传该版本图片"><i class="fa fa-cloud-upload"></i></a><a onclick="chenmObj.iconView(this)" parentElem="#${act}_option${index}" class="input-group-addon" title="点击预览该版本图片"><i class="fa fa-file-image-o"></i></a></div></td>
            <td><select class="form-control" sign="prid" name="options[${num}][prid]" default="${option.prid}">${this.getPridHtml(option.prid)}</select></td>
            <td><a class="btn btn-warning btn-xs" onclick="chenmObj.delOption('${act}', ${index})">删除</a></td>
            </tr>`;
        return html;
    },
    iconFocus: function (el) {
        //$(el).css("width", "220px");
    },
    iconBlur: function (el) {
        $(el).css("width", "100%");
    },
    iconView: function (el) {
        var parentElem = $(el).attr('parentElem');
        var shopimg = $(parentElem + " input[class*='option_icon']").val();
        if (shopimg == '') {
            layer.msg("请先上传图片", {
                icon: 5,
                shade: 0.01,
                time: 1000
            });
            return;
        }
        if (shopimg.indexOf('http') == -1) shopimg = '../' + shopimg;
        layer.open({
            type: 1,
            area: ['360px', '400px'],
            title: '规格选项图片查看',
            shade: 0.3,
            anim: 1,
            shadeClose: true,
            content: '<center><img width="300px" src="' + shopimg + '"></center>'
        });
    },
    iconClick: function (el) {
        var parentElem = $(el).attr('parentElem');
        $(parentElem + " input[name='icon_file']").trigger("click");
    },
    iconUpload: function (el) {
        var parentElem = $(el).attr('parentElem');
        var iconElem = $(parentElem + " input[class*='option_icon']");
        var fileObj = $(el)[0].files[0];
        if (typeof (fileObj) == "undefined" || fileObj.size <= 0) {
            return layer.msg('文件对象错误上传失败，请反馈到工单并等待更新！');
        }
        var formData = new FormData();
        formData.append("do", "upload");
        formData.append("type", "specs");
        formData.append("file", fileObj);
        formData.append("tid", 0);
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            url: "./ajax.php?act=uploadimg",
            data: formData,
            type: "POST",
            dataType: "json",
            cache: false,
            processData: false,
            contentType: false,
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg('上传图标成功');
                    $(iconElem).val(data.url);
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg('服务器错误');
                return false;
            }
        })
    },
    getPridHtml: function (prid) {
        if (typeof (this.pridList) == 'object' || typeof (this.pridList) == 'array') {
            var html = '<option value="0">不加价(会没利润)</option>';
            $.each(this.pridList, function (i, $res) {
                //html += '<option value="' + $res.id + '" kind="' + $res.kind + '" p_2="' + $res.p_2 + '" p_1="' + $res.p_1 + '" p_0="' + $res.p_0 + '" >' + $res.name + '(+' + $res.p_2 + '元|+' + $res.p_1 + '元|+' + $res.p_0 + '元)</option>';
                if ($res.kind == 2) {
                    html += '<option ' + (prid == $res.id ? 'selected="selected"' : '') + ' value="' + $res.id + '">' + $res.name + '(+' + $res.p_2 + '%|+' + $res.p_1 + '%|+' + $res.p_0 + '%)</option>';
                } else {
                    html += '<option ' + (prid == $res.id ? 'selected="selected"' : '') + ' value="' + $res.id + '">' + $res.name + '(+' + $res.p_2 + '元|+' + $res.p_1 + '元|+' + $res.p_0 + '元)</option>';
                }
            });
        } else {
            console.log("加价模板为空！");
            var html = '<option value="0" selected="selected">不加价(会没利润)</option>';
        }
        return html;
    },
    checkValue: function (el, index) {
        var is_curl = parseInt($("select[name='is_curl']").val());
        $("#price1_tips").html('当前配置下，此处得是1份的成本价格，规格选项的成本价系统会自动计算！');
        if ($(el).length > 0 && is_curl == 2) {
            var value = parseInt($(el).val());
            var minnum = parseInt($('#value').attr('min'));
            if (typeof (minnum) != 'undefined' && value < minnum) {
                $(el).val(minnum);
                layer.msg('数量不能低于最低下单数量' + minnum);
            }
            var price1 = getFloat($("#price").val(), 6);
            if (typeof (price1) != "number" || $("#price").val() == "") {
                var price1 = getFloat($("#price1_box input[name='price1']").val(), 6);
            }
            cost = getFloat(price1 * value, 6);
            console.log(value, price1, cost, typeof (cost), chenmObj.action);
            if ('number' == typeof (cost)) {
                $("#" + chenmObj.action + "_option" + index + " input[sign='price1']").val(cost);
            }
        }
        $(el).css("width", "auto");
    },
    setOptionPrice: function (el) {
        var is_curl = parseInt($("select[name='is_curl']").val());
        $("#price1_tips").html('当前配置下，此处得是1份的成本价格，规格选项的成本价系统会自动计算！');
        if ($(el).length > 0 && is_curl == 2) {
            var price1 = getFloat($(el).val(), 6);
            if ('number' == typeof (price1) && price1 > 0) {
                var items = $("#" + chenmObj.action + "_options tr");
                $.each(items, function (i, item) {
                    var value = parseInt($(items[i]).find("input[sign='value']").val());
                    if (value < 1 || 'number' != typeof (value)) {
                        value = 1;
                        $(items[i]).find("input[sign='value']").val(value);
                    }
                    var cost = getFloat(price1 * value, 6);
                    console.log(value);
                    console.log(cost);
                    if ('number' == typeof (cost)) {
                        $(items[i]).find("input[sign='price1']").val(cost);
                    }
                });
            }
        }
    },
    delOption: function (act, index) {
        $("#" + act + "_option" + index).remove();
    },
    input: function (tid) {
        var that = this;
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=getSpecsInfo",
            dataType: 'json',
            data: {
                tid: tid
            },
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    if (data.data.length > 0) {
                        that.loadOptions(data.data);
                    } else {
                        that.input2(chenmObj.specs_id);
                    }
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (ret) {
                console.log(ret);
                layer.close(ii);
                layer.alert("服务器请求超时，请稍后再试！");
            }
        });
    },
    input2: function (specs_id) {
        var that = this;
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=getSpecsInfo",
            dataType: 'json',
            data: {
                specs_id: specs_id
            },
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    that.loadOptions(data.data);
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (ret) {
                console.log(ret);
                layer.close(ii);
                layer.alert("服务器请求超时，请稍后再试！");
            }
        });
    },
    loadOptions: function (options) {
        var html = '';
        var that = this;
        var num = 1,
            index = 0;
        if (typeof options == "object" || typeof options == "array") {
            $.each(options, function (i, item) {
                num++;
                index++;
                html += that.getOptionHtml('edit', item, i, i);
            });
        }
        if (html == "") {
            html = getOptionHtml('edit', {}, 0, 1);
        }
        console.log("多规格商品价值完毕，共" + num + "个选项");
        $("#edit_num").val(num);
        $("#edit_index").val(index);
        $("#edit_options").html(html);
        chenmObj.setOptionPrice();
        $("#option1").show() && $("#prid0").hide();
        //绑定事件
        chenmObj.bindEvent();
    },
    shopAdd: function () {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        var tid = $("#tid").val();
        $.ajax({
            type: "POST",
            url: "?act=add_submit&tid=" + tid,
            dataType: "json",
            data: $("#addForm").serialize(),
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    chenmObj.tid = data.tid;
                    chenmObj.alert(data.msg);
                } else {
                    chenmObj.alert({
                        content: data.msg,
                        icon: 5
                    });
                }
            },
            error: function (data) {
                layer.close(ii);
                chenmObj.alert({
                    content: '服务器错误',
                    icon: 5
                });
                return false;
            }
        });
    },
    shopEdit: function () {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        var tid = $("#tid").val();
        $.ajax({
            type: "POST",
            url: "?act=edit_submit&tid=" + tid,
            dataType: "json",
            data: $("#editForm").serialize(),
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    chenmObj.alert(data.msg);
                } else {
                    chenmObj.alert({
                        content: data.msg,
                        icon: 5
                    });
                }
            },
            error: function (data) {
                layer.close(ii);
                chenmObj.alert({
                    content: '服务器错误',
                    icon: 5
                });
                return false;
            }
        });
    },
    msg: function (options) {
        layer.msg(options);
    },
    alert: function (options) {
        if (typeof (options) === 'string') {
            var action = $("#action").val();
            if (action == 'add') {
                var obj = {
                    title: '操作成功',
                    content: options,
                    icon: 1,
                    btn: ['重新修改', '继续添加', '返回上一页'],
                    yes: function (index) {
                        window.location.href = './shopedit.php?my=edit&tid=' + chenmObj.tid;
                    },
                    btn2: function () {
                        window.history.reload();
                    },
                    btn3: function () {
                        var backurl = $("#backurl").length > 0 ? $("#backurl").val() : '';
                        if (backurl !== "" && backurl.indexOf("tid=" + chenmObj.tid) < 0) {
                            window.location.href = backurl;
                        } else {
                            window.location.href = './shoplist.php';
                        }
                    }
                };
            } else {
                var obj = {
                    title: '操作成功',
                    content: options,
                    icon: 1,
                    btn: ['重新修改', '返回上一页', '返回列表'],
                    yes: function (index) {
                        window.location.href = './shopedit.php?my=edit&tid=' + chenmObj.tid;
                    },
                    btn2: function () {
                        window.history.go(-1);
                    },
                    btn3: function () {
                        var backurl = $("#backurl").length > 0 ? $("#backurl").val() : '';
                        if (backurl !== "" && backurl.indexOf("tid=" + chenmObj.tid) < 0) {
                            window.location.href = backurl;
                        } else {
                            window.location.href = './shoplist.php';
                        }
                    }
                };
            }
        } else {
            var obj = options;
        }
        layer.open(obj);
    }
};

function changeinput(str) {
    $("input[name='input']").val(str);
}

function changeinputs(str) {
    $("input[name='inputs']").val(str);
}

function getFloat(number, n) {
    n = n ? parseInt(n) : 2;
    if (n <= 0) return Math.ceil(number);
    number = Math.round(number * Math.pow(10, n)) / Math.pow(10, n);
    return number;
}

function changeNum() {
    var num = parseInt($("#value").val());
    var price = parseFloat($("#price").val());
    var price1;
    if (price != "null" && typeof (price) != "undefined" && price > 0 && NaN != price) {} else {
        price1 = parseFloat($("input[name='price1']").val());
        if (price1 > 0) {
            price = getFloat(price1 / num, 6);
            $("#price").val(price);
        } else {
            return false;
        }
    }
    var prid = parseInt($("select[name='prid']").val());
    var specs_id = parseInt($("select[name='specs_id']").val());
    if (prid == 0 && specs_id > 0) {
        $("input[name='price1']").val(getFloat(price, 6));
        setPrice1Info();
    } else {
        var min = parseInt($("#value").attr('min'));
        var max = parseInt($("#value").attr('max'));
        if (min < 1) {
            min = 1;
        }
        if (min == max) {
            num = min;
        } else if (num < min) {
            if (num < min) $("#value").val(min);
            num = min;
            $("#value").val(min);
        } else if (num > max && max > 0) {
            if (max == min) {
                $("select[name='multi']").val(0);
            } else {
                $("select[name='multi']").val(1);
            }
            num = max;
        } else {
            $("select[name='multi']").val(1);
        }
        var minnum = num >= min ? 1 : Math.floor(min / num);
        var maxnum = max > 0 ? Math.floor(max / num) : 0;
        if (minnum != NaN) $("input[name='min']").val(minnum);
        if (maxnum != NaN) $("input[name='max']").val(maxnum);
        $("#value").val(num);
        $("select[name='multi']").change();
        $("input[name='price1']").val(getFloat(num * price, 6));
        getPridPrice();
    }
}

function fileSelect() {
    $("#file").trigger("click");
}

function fileView() {
    var shopimg = $("#shopimg").val();
    if (shopimg == '') {
        layer.alert("请先上传图片，才能预览");
        return;
    }
    if (shopimg.indexOf('http') == -1) shopimg = '../' + shopimg;
    layer.open({
        type: 1,
        area: ['360px', '400px'],
        title: '商品图片查看',
        shade: 0.3,
        anim: 1,
        shadeClose: true,
        content: '<center><img width="300px" src="' + shopimg + '"></center>'
    });
}

function fileUpload() {
    var tid = $("#tid").val();
    var fileObj = $("#file")[0].files[0];
    if (typeof (fileObj) == "undefined" || fileObj.size <= 0) {
        return;
    }
    var formData = new FormData();
    formData.append("do", "upload");
    formData.append("type", "shop");
    formData.append("file", fileObj);
    formData.append("tid", tid);
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        url: "ajax.php?act=uploadimg",
        data: formData,
        type: "POST",
        dataType: "json",
        cache: false,
        processData: false,
        contentType: false,
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg('上传图片成功');
                $("#shopimg").val(data.url);
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

function Addstr(id, str) {
    $("#" + id).val($("#" + id).val() + str);
}

function getPridPrice() {
    $("#prid1").show();
    $("#prid0").show();
    var price1 = getFloat($("input[name='price1']").val(), 6);
    var prid = $("select[name='prid'] option:selected").val();
    var value = $("input[name='value']").val();
    if (price1 > 0 && prid > 0) {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=getPridPrice",
            data: {
                price1: price1,
                prid: prid
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    if (data.data.price > 0) $("input[name='price']").val(data.data.price);
                    if (data.data.cost > 0) $("input[name='cost']").val(data.data.cost);
                    if (data.data.cost2 > 0) $("input[name='cost2']").val(data.data.cost2);
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg('服务器错误获取模板加价失败。请稍后再试');
                return false;
            }
        });
    }
}

function getinput() {
    var inputnam1 = $("select[id='set_select'] option:selected").attr("inputname1");
    var inputnam2 = $("select[id='set_select'] option:selected").attr("inputname2");
    $("input[name='input']").val(inputnam1);
    $("input[name='inputs']").val(inputnam2);
}

function kyxmoney() {
    var shequ = $("select[name=shequ]").val();
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: "GET",
        url: "ajax.php?act=kyxmoney&shequ=" + shequ,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.alert("余额：" + data.msg + "元", {
                    title: "查询成功"
                });
                return true;
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

function kyxlogin() {
    var shequ = $("select[name=shequ]").val();
    var ii = layer.msg("最新的卡易信商品目录获取中...", {
        icon: 16,
        time: 999999
    });
    $.ajax({
        type: "GET",
        url: "ajax.php?act=kyxlogin&shequ=" + shequ,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            layer.alert(data.msg);
        },
        error: function (data) {
            layer.msg('服务器错误');
            return false;
        }
    });
}

function kyxjump() {
    var url = $("#goodsclass1 option:selected").attr("value");
    if (url == undefined || !url) return;
    url = url.split("_");
    if (url[1] != "") {
        window.location.href = url[1];
    } else {
        layer.msg("分类目标地址不存在或为空！");
    }
}

function addkyxelement(arr) {
    var content = "<option>请先选择分类</option>"; //初始化
    for (var a = 0; a < arr.length; a++) {
        content += "<option value=\"" + arr[a]["id"] + "_" + arr[a]["url"] + "\">" + arr[a]["name"] + "</option>";
    }
    $("#goodsclass1").empty();
    $("#goodsclass1").append(content);
    $("#goodsclass1").select2({
        placeholder: '请选择商品分类',
        language: 'zh-CN'
    });
}

function setShangzhanClass(data) {
    var content = "<option>请先选择一级目录</option>"; //初始化
    shangzhanObj = {};
    $.each(data, function (i, group) {
        shangzhanObj[group.id] = group.children;
        content += '<option value="' + group.id + '">' + group.title + '"</option>';
    });
    $("#goodsclass1").empty();
    $("#goodsclass1").append(content);
    $("#goodsclass1").select2({
        placeholder: '请选择一级目录',
        language: 'zh-CN'
    });
}

function setKkyClass(data) {
    var content = "<option>请先选择分类</option>"; //初始化
    kakayunObj = {};
    $.each(data, function (i, group) {
        kakayunObj[group.class.groupid] = group.goods;
        content += '<option value="' + group.class.groupid + '">' + group.class.groupname + '"</option>';
    });
    $("#goodsclass1").empty();
    $("#goodsclass1").append(content);
    $("#goodsclass1").select2({
        placeholder: '请选择一级目录',
        language: 'zh-CN'
    });
}

function setPrice1Info() {
    var goodsid = parseInt($("#goodslist").val());
    var g = (typeof (goodsid)).toLowerCase();
    if (g == 'nan' || g == 'null' || goodsid < 1) {
        return false;
    }
    var price1 = getFloat($("#price").val(), 6);
    $("input[name='price1']").val(price1);
    chenmObj.setOptionPrice("#price");
}

function getGoodsParam(type, goodsid) {
    if (type == 'jiuwu') {
        var shequ = $("select[name='shequ']").val();
        if (!goodsid) {
            var goodsid = $("#goodslist option:selected").val();
        }
        var goodstype = $("#goodslist option:selected").attr('goodstype');
        var minnum = $("#goodslist option:selected").attr('minnum');
        var maxnum = $("#goodslist option:selected").attr('maxnum');
        var unit = $("#goodslist option:selected").attr('unit');
        var close = $("#goodslist option:selected").attr('close');
        var shopimg = $("#goodslist option:selected").attr('shopimg');
        var price = $("#goodslist option:selected").attr('price');
        var name = $("#goodslist option:selected").html();
        $("input[name='goods_id']").val(goodsid);
        $("input[name='goods_type']").val(goodstype);
        $("input[name='shopimg']").val(shopimg);
        $("#price").val(price);
        if ($("#value").val() == '' || $("#value").val() < minnum || $("#value").val() > maxnum) $("#value").val(minnum);
        $("#value").attr('min', minnum);
        $("#value").attr('max', maxnum);
        if ($("input[name='name']").val() == '') $("input[name='name']").val(name);
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=getGoodsParam",
            data: {
                shequ: shequ,
                goodsid: goodsid
            },
            dataType: 'json',
            success: function (res) {
                layer.close(ii);
                if (res.code == 0) {
                    var url = res.url;
                    var data = res.data;
                    $("input[name='goods_param']").val(data.param);
                    if (data.inputs && $("input[name='input']").val() == "") {
                        var inputsArr = data.inputs.split("|");
                        var input = "",
                            inputs = "";
                        $.each(inputsArr, function (i, v) {
                            if (i == 0) {
                                input = v;
                            } else if (i == 1) {
                                inputs = v;
                            } else {
                                inputs += "|" + v;
                            }
                        });
                        $("input[name='input']").val(input);
                        $("input[name='inputs']").val(inputs);
                    }

                    $("input[name='goods_param']").val(data.param);
                    if (typeof data.desc == 'string') $("textarea[name='desc']").val(data.desc);
                    var url = $("select[name='shequ'] option:selected").attr('url') || '';
                    $("#GoodsInfo").html('<b>社区商品售价：</b>' + price + '<br/><b>最小下单数量：</b>' + minnum + '<br/><b>最大下单数量：</b>' + maxnum + '<br/><b>单位：</b>' + unit + '<br/><b>上架状态：</b>' + (close == 0 ? '<span color=green>上架中</span>' : '<span color=red>已下架</span>'));
                    $("#GoodsInfo").slideDown();
                    changeNum();
                    editorChange();
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg('服务器错误，请稍后再试');
                return false;
            }
        });
    } else if (type == "shikonyun") {
        var shequ = $("select[name='shequ']").val();
        var goodsid = $("#goodslist option:selected").val();
        var shopimg = $("#goodslist option:selected").attr('shopimg');
        var price = $("#goodslist option:selected").attr('price');
        var min = $("#goodslist option:selected").attr('min');
        var max = $("#goodslist option:selected").attr('max');
        var desc = $("#goodslist option:selected").attr('desc');
        var param = $("#goodslist option:selected").attr('param');
        var input = $("#goodslist option:selected").attr('input');
        var inputs = $("#goodslist option:selected").attr('inputs');
        $("input[name='goods_param']").val(param);
        $("input[name='input']").val(input);
        if (inputs != "") $("input[name='inputs']").val(inputs);
        var status = $("#goodslist option:selected").attr('active');
        var name = $("#goodslist option:selected").html();
        $("input[name='goods_id']").val(goodsid);
        if ($("input[name='name']").val() == "") $("input[name='name']").val(name);
        if ($("input[name='shopimg']").val() == "") $("input[name='shopimg']").val(shopimg);
        $("textarea[name='desc']").val(unescape(desc));
        $("#price").val(price);
        $("#value").val(1);
        $("#value").attr('min', min);
        $("#value").attr('max', max);
        var url = $("select[name='shequ'] option:selected").attr('url') || '';
        $("#GoodsInfo").html('<b>商品名称：</b>' + name + '&nbsp;<a style="color:blue" href="' + url + 'home/order/' + goodsid + '" target="_blank" rel="noreferrrer">查看商品</a><br/><b>商品简介：</b>' + unescape(desc) + '<br/><b>商品进价：</b>' + price + '<br/><b>最小下单数量：</b>' + min + '<br/><b>最大下单数量：</b>' + max + '<br/><b>上架状态：</b>' + (status == 1 ? '<span color=green>上架中</span>' : '<span color=red>已下架</span>'));
        $("#GoodsInfo").slideDown();
        changeNum();
        editorChange();
    } else if (type == "chengzi") {
        var name = $("#goodslist option:selected").html();
        var price = $("#goodslist option:selected").attr('price');
        var min = $("#goodslist option:selected").attr('min');
        var max = $("#goodslist option:selected").attr('max');
        var active = $("#goodslist option:selected").attr('active');
        var inputs = $("#goodslist option:selected").attr('inputs');
        var param = $("#goodslist option:selected").attr('param');
        $("input[name='input']").val('下单链接');
        $("input[name='goods_id']").val(goodsid);
        $("#price").val(price);
        $("#value").val(1);
        $("#value").attr('min', min);
        $("#value").attr('max', max);
        if ("" != inputs) {
            $("input[name='inputs']").val(inputs);
        }
        if ("" != param) {
            $("input[name='goods_param']").val(param);
        }
        $("#GoodsInfo").html('<b>商品名称：</b>' + name + '<br/><b>商品进价：</b>' + price + '<br/><b>最小下单数量：</b>' + min + '<br/><b>最大下单数量：</b>' + max + '<br/><b>上架状态：</b>' + (active == 1 ? '<span style="color:green">上架中</span>' : '<span style="color:red">已下架</span>'));
        $("#GoodsInfo").slideDown();
        changeNum();
    } else if (type == "shangzhan") {
        var shequ = $("select[name='shequ']").val();
        if (!goodsid) {
            var goodsid = $("#goodslist option:selected").val();
        }
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=getGoodsParam",
            data: {
                shequ: shequ,
                goodsid: goodsid
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    var data = data.data;
                    var name = data.name;
                    var price = data.price;
                    var type = data.type;
                    $("select[name='goods_type']").val(type);
                    $("input[name='goods_id']").val(goodsid);
                    if (typeof (data.input) == 'string') $("input[name='input']").val(data.input);
                    if (typeof (data.inputs) == 'string') $("input[name='inputs']").val(data.inputs);
                    var desc = typeof (data.desc) == 'string' ? data.desc : '';
                    if (desc !== "") $("textarea[name='desc']").val(desc);
                    var alert = typeof (data.alert) == 'string' ? data.alert : '';
                    if (alert !== "") $("textarea[name='alert']").val(alert);
                    $("#price").val(price);
                    $("#value").val(1);
                    $("#value").attr('min', data.min);
                    $("#value").attr('max', data.max);
                    $("#GoodsInfo").html('<b>商品名称：</b>' + name + '&nbsp;<br/><b>商品简介：</b>' + desc + '<br/><b>商品进价：</b>' + price + '<br/><b>最小下单数量：</b>' + data.min + '<br/><b>最大下单数量：</b>' + data.max + '<br/><b>上架状态：</b>' + (data.active == 1 ? '<span color=green>上架中</span>' : '<span color=red>已下架</span>') + '<br/><b>商品类型：</b>' + (type == 0 ? '<span color=red>人工代充</span>' : '<span color=green>自动发卡</span>'));
                    $("#GoodsInfo").slideDown();
                    changeNum();
                    editorChange();
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg('服务器错误，请稍后再试');
                return false;
            }
        });
    } else if (type == 'zhike') {
        var shequ = $("select[name='shequ']").val();
        if (!goodsid) {
            var goodsid = $("#goodslist option:selected").val();
        }
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=getGoodsParam",
            data: {
                shequ: shequ,
                goodsid: goodsid
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    var arr = data.data;
                    var name = arr.name;
                    var price = arr.price;
                    $("input[name='goods_id']").val(goodsid);
                    if (typeof arr.params == 'string') $("input[name='goods_param']").val(arr.params);
                    if (typeof arr.input == 'string') $("input[name='input']").val(arr.input);
                    if (typeof arr.inputs == 'string') $("input[name='inputs']").val(arr.inputs);
                    if (typeof arr.goodsType != 'undefined') $("select[name='goods_type']").val('' + arr.goodsType);
                    var desc = typeof (arr.desc) == 'string' ? arr.desc : '';
                    if (desc !== "") $("textarea[name='desc']").val(desc);
                    $("#price").val(price);
                    $("#value").val(1);
                    $("#value").attr('min', arr.min);
                    $("#value").attr('max', arr.max);
                    $("#GoodsInfo").html('<b>商品名称：</b>' + name + '&nbsp;<br/><b>商品简介：</b>' + desc + '<br/><b>商品进价：</b>' + price + '<br/><b>最小下单数量：</b>' + arr.min + '<br/><b>最大下单数量：</b>' + arr.max + '<br/><b>上架状态：</b>' + (arr.active == 1 ? '<span color=green>上架中</span>' : '<span color=red>已下架</span>') + '<br/>');
                    $("#GoodsInfo").slideDown();
                    changeNum();
                    editorChange();
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg('服务器错误，请稍后再试');
                return false;
            }
        });
    } else if (type == "kakayun") {
        var shequ = $("select[name='shequ']").val();
        if (!goodsid) {
            var goodsid = $("#goodslist option:selected").val();
        }
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=getGoodsParam",
            data: {
                shequ: shequ,
                goodsid: goodsid
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    if (data.data.attach) {
                        var inputsArr = data.data.attach;
                        var input = "",
                            inputs = "";
                        $.each(inputsArr, function (i, v) {
                            if (i == 0) {
                                input = v.title;
                            } else if (i == 1) {
                                inputs = v.title;
                            } else {
                                inputs += "|" + v.title;
                            }
                        });
                        $("input[name='input']").val(input);
                        $("input[name='inputs']").val(inputs);
                    }
                    var goodsInfo = data.data.goodsdetails;
                    var name = goodsInfo.goodsname;
                    var shopimg = goodsInfo.imgurl;
                    var goodstype = goodsInfo.goodstype;
                    var showstock = goodsInfo.showstock;
                    var stock = goodsInfo.stock;
                    var price = data.data.price.data.goodsprice;
                    var minnum = goodsInfo.buyminnum;
                    var maxnum = goodsInfo.buymaxnum;
                    var unit = goodsInfo.unit;
                    var desc = typeof (goodsInfo.details) == 'string' ? goodsInfo.details : '';
                    var alert = typeof (goodsInfo.msgboxtip) == 'string' ? goodsInfo.msgboxtip : '';
                    var status = goodsInfo.goodsstatus;
                    $("input[name='goods_id']").val(goodsid);
                    if ($("input[name='name']").val() == "") $("input[name='name']").val(name);
                    if ($("input[name='shopimg']").val() == "") $("input[name='shopimg']").val(shopimg);
                    //if($("textarea[name='desc']").val()=="")
                    $("textarea[name='desc']").val(desc);
                    if ("" != alert) {
                        $("textarea[name='alert']").val(alert);
                    }
                    $("#price").val(price);
                    $("#value").val(1);
                    $("#value").attr('min', minnum);
                    $("#value").attr('max', maxnum);
                    $("select[name='goods_type']").val(goodstype);
                    $("#GoodsInfo").html('<b>商品名称：</b>' + name + '&nbsp;<a style="color:blue" href="' + data.data.shopurl + '" target="_blank" rel="noreferrrer">查看商品</a><br/><b>商品进价：</b>' + price + '<br/><b>最小下单数量：</b>' + minnum + '<br/><b>最大下单数量：</b>' + maxnum + '<br/><b>单位：</b>' + unit + '<br/><b>上架状态：</b>' + (status == 1 ? '<span style="color:green">上架中</span>' : '<span style="color:red">已下架</span>') + '<br/><b>商品类型：</b>' + (goodstype == 1 ? '<span style="color:#f45726">人工发货</span>' : '<span style="color:green">自动发卡</span>') + '<br/><b>库存状态：</b>' + (showstock == 1 ? (stock > 0 ? '<span style="color:green">' + stock + '个</span>' : '<span style="color:red">库存不足</span>') : '<span style="color:green">人工发货</span>') + '<br/><b>商品简介：</b>' + desc);
                    $("#GoodsInfo").slideDown();
                    changeNum();
                    editorChange();
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg('服务器错误，请稍后再试');
                return false;
            }
        });
    } else if (type == 'yile') {
        var shequ = $("select[name='shequ']").val();
        if (!goodsid) {
            var goodsid = $("#goodslist option:selected").val();
        }
        var name = $("#goodslist option:selected").html();
        $("input[name='goods_id']").val(goodsid);
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=getGoodsParam",
            data: {
                shequ: shequ,
                goodsid: goodsid
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    var url = data.url;
                    var data = data.data;
                    console.log(data);
                    if ($("input[name='shopimg']").val() == "") {
                        $("input[name='shopimg']").val(data.image);
                    }
                    var name = data.name;
                    var paramname = data.paramname;
                    var inputs = '';
                    $.each(paramname.split('|'), function (i, v) {
                        if (i == 0) {
                            $("input[name='input']").val(v);
                        } else {
                            if (v == 'QQ空间说说ID') v = '说说ID';
                            inputs += '|' + v;
                        }
                    });
                    if ($("input[name='inputs']").val() == '') $("input[name='inputs']").val(inputs.substr(1));
                    $("#price").val(data.price);
                    if ($("#value").val() == '' || $("#value").val() < data.limit_min || $("#value").val() > data.limit_max) $("#value").val(data.limit_min);
                    $("#value").attr('min', data.limit_min);
                    $("#value").attr('max', data.limit_max);
                    if ($("input[name='name']").val() == '') $("input[name='name']").val(name);
                    //if($("textarea[name='desc']").val()=="")
                    $("textarea[name='desc']").val(data.desc);
                    var url = $("select[name='shequ'] option:selected").attr('url') || '';
                    $("#GoodsInfo").html('<b>可下单状态：</b>' + (data.close == 1 ? '<font color="red">已关闭下单</font>' : '<font color="green">正常下单中</font>') + '<br/><b>商品名称：</b>' + name + '&nbsp;<a style="color:blue" href="' + url + 'home/order/' + goodsid + '" target="_blank" rel="noreferrrer">查看商品</a><br/><b>商品简介：</b>' + data.desc + '<br/><b>社区商品售价：</b>' + data.price + ' 元<br/><b>最小下单数量：</b>' + data.limit_min + '<br/><b>最大下单数量：</b>' + data.limit_max);
                    $("#GoodsInfo").slideDown();
                    changeNum();
                    editorChange();
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg('服务器错误，请稍后再试');
                return false;
            }
        });
    } else if (type == 'caihon' || type == 'chenmeng') {
        var shequ = $("select[name='shequ']").val();
        if (!goodsid) {
            var goodsid = $("#goodslist option:selected").val();
        }
        var name = $("#goodslist option:selected").html();
        $("input[name='goods_id']").val(goodsid);
        var cid = $("#goodslist option:selected").attr('cid') || '0';
        $("#value").val(1);
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=getGoodsParam",
            data: {
                shequ: shequ,
                goodsid: goodsid
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    var url = $("select[name='shequ'] option:selected").html();
                    url = url.split("（")[0];
                    var data = data.data;
                    if ($("input[name='shopimg']").val() == "") $("input[name='shopimg']").val(data.shopimg);
                    if ($("input[name='input']").val() == '') $("input[name='input']").val(data.input);
                    if ($("input[name='inputs']").val() == '') $("input[name='inputs']").val(data.inputs);
                    $("#price").val(data.price);
                    $("#value").val('1');
                    $("#value").attr('min', data.min);
                    $("#value").attr('max', data.max);
                    if ($("input[name='name']").val() == '') $("input[name='name']").val(name);
                    //if($("textarea[name='desc']").val()=="")
                    $("textarea[name='desc']").val(data.desc || '');
                    //if($("textarea[name='alert']").val()=="")
                    $("textarea[name='alert']").val(data.alert || '');

                    // 卡密商品
                    if ('undefined' !== typeof data.isfaka && data.isfaka == 1) {
                        $('[name=goods_type]').val('1');
                    }
                    else {
                        $('[name=goods_type]').val('0');
                    }

                    var url = $("select[name='shequ'] option:selected").attr('url') || '';
                    $("#GoodsInfo").html('<b>商品名称：</b>' + name + '&nbsp;<a style="color:blue" href="' + url + '?cid=' + data.cid + '&tid=' + goodsid + '" target="_blank">查看商品</a><br/><b>商品简介：</b>' + data.desc + '<br/><b>提示内容：</b>' + data.alert + '<br/><b>商品售价：</b>' + data.price + ' 元<br/><b>最小数量：</b>' + data.min + '<br/><b>最大数量：</b>' + data.max + '<br/><b>上架状态：</b>' + (data.close == 1 ? '<span style="color:red">已下架<span>' : '<span style="color:green">上架中<span>'));
                    $("#GoodsInfo").slideDown();
                    changeNum();
                    editorChange();
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg('服务器错误，请稍后再试');
                return false;
            }
        });
    } else if (type == "kayixin") {
        var shequ = $("select[name=shequ]").val();
        var url = $("#goodslist option:selected").attr("url");
        $.ajax({
            type: "POST",
            url: "ajax.php?act=getkyxgoods",
            data: {
                shequ: shequ,
                url: url
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    $("input[name=goods_param]").val(data.url);
                    $("input[name=price1]").val(data.price);
                    $("input[name=input]").val(data.input);
                    $("input[name=inputs]").val(data.inputs);
                    if ($("input[name='name']").val() == "") {
                        $("input[name='name']").val(data.name)
                    }
                    changeNum();
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.msg('服务器错误');
                return false;
            }
        });
    } else {
        var shequ = $("select[name='shequ']").val();
        if (!goodsid) {
            var goodsid = $("#goodslist option:selected").val();
        }
        var name = $("#goodslist option:selected").html();
        $("input[name='goods_id']").val(goodsid);
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=getGoodsParam",
            data: {
                shequ: shequ,
                goodsid: goodsid
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    var url = data.url;
                    var data = data.data;


                    if (data.input && 'string' === typeof data.input) {
                        $("input[name='input']").val(data.input);
                    }

                    if (data.inputs && 'string' === typeof data.inputs) {
                        var inputsArr = data.inputs.split("|");
                        var input = "",
                            inputs = "";
                        $.each(inputsArr, function (i, v) {
                            if (i == 0 && !data.input) {
                                input = v;
                            } else {
                                if (inputs == "") {
                                    inputs = v;
                                } else {
                                    inputs += "|" + v;
                                }
                            }
                        });

                        if (!data.input && input && (input + '').toLowerCase() != 'null') {
                            $("input[name='input']").val(input);
                        }

                        if (inputs && (inputs + '').toLowerCase() != 'null') {

                            $("input[name='inputs']").val(inputs);
                        }
                    }

                    if (data.param && typeof data.param == 'string') {
                        $("input[name='goods_param']").val(data.param);
                        $("#goods_param").css({
                            'display': 'inherit'
                        });
                    } else if (data.params && typeof data.params == 'string') {
                        $("input[name='goods_param']").val(data.params);
                        $("#goods_param").css({
                            'display': 'inherit'
                        });
                    }


                    if (data.name && typeof data.name == 'string') {
                        var name = data.name;
                    } else if (data.title && typeof data.title == 'string') {
                        var name = data.title;
                    } else if (data.goodsname && typeof data.goodsname == 'string') {
                        var name = data.goodsname;
                    }

                    if ($("input[name='name']").val() == '') {
                        $("input[name='name']").val(name)
                    }

                    if (typeof data.desc == 'string') $("textarea[name='desc']").val(data.desc);
                    if (typeof data.alert == 'string') $("textarea[name='alert']").val(data.alert);
                    if (typeof data.price != 'undefined') $("#price").val(data.price);
                    if (typeof data.goodstype != 'undefined') {
                        if ($("select[name='goods_type']").length > 0) {
                            if (typeof data.goodstype == 'number' && data.goodstype < 2) {
                                //直冲和卡密 返回0 是人工/代充 1是卡密
                                if (data.goodstype == 1) {
                                    $("select[name='goods_type']").val(1);
                                } else {
                                    $("select[name='goods_type']").val(0);
                                }
                            } else {
                                //如果类型是数组
                                if (typeof data.goodstype == 'object') {
                                    $("select[name='goods_type']").empty();
                                    var label, value, html;
                                    $.each(data.goodstype, function (i, v) {
                                        if (typeof i == 'string') {
                                            label = i;
                                        } else {
                                            label = typeof v.label == 'string' ? v.label : (v.name ? v.name : '');
                                        }
                                        if (typeof i == 'string') {
                                            value = v;
                                        } else {
                                            value = typeof v.data == 'string' ? v.data : (v.value ? v.value : '');
                                        }
                                        html = '<option value="' + value + '">' + label + '</option>';
                                        $("select[name='goods_type']").append(html);
                                    });
                                }
                            }
                        } else {
                            $("input[name='goods_type']").val(data.goodstype);
                        }
                    }
                    if (typeof data.shopimg != 'undefined') $("input[name='shopimg']").val(data.shopimg);
                    // var url = $("select[name='shequ'] option:selected").attr('url') || '';
                    var html = '<b>商品名称：</b>' + name + '&nbsp;';
                    if (data.minnum && typeof data.minnum == 'number') {
                        html += '<br/><b>最小下单数量：</b>' + data.minnum + '';
                        if ($("#value").val() == '' || $("#value").val() < data.minnum) {
                            $("#value").val(data.minnum);
                        }
                        $("#value").attr('min', data.minnum);
                    } else if (data.min && typeof data.min == 'number') {
                        html += '<br/><b>最小下单数量：</b>' + data.min + '';
                        if ($("#value").val() == '' || $("#value").val() < data.min) {
                            $("#value").val(data.min);
                        }
                        $("#value").attr('min', data.min);
                    }

                    if (data.maxnum && typeof data.maxnum == 'number') {
                        html += '<br/><b>最大下单数量：</b>' + data.maxnum + '';
                        if ($("#value").val() > data.maxnum) {
                            $("#value").val(data.maxnum);
                        }
                        $("#value").attr('max', data.maxnum);
                    }
                    else if (data.max && typeof data.max == 'number') {
                        html += '<br/><b>最大下单数量：</b>' + data.max + '';
                        if ($("#value").val() > data.max) {
                            $("#value").val(data.max);
                        }
                        $("#value").attr('max', data.max);
                    }

                    $("select[name='stock_open']").val(0);
                    $("#option_stock_num").hide();
                    $("input[name='stock']").val(0);
                    if (data.stock || data.goodsstock) {
                        // 处理库存
                        var stock = data.stock || data.goodsstock;
                        if (!isNaN(stock)) {
                            $("select[name='stock_open']").val(1);
                            $("#option_stock_num").show();
                            $("input[name='stock']").val(parseInt(stock));
                            html += '<br/><b>剩余库存：</b>' + stock + '份';
                        }
                    }
                    if (typeof data.unit == 'string') {
                        html += '<br/><b>单位：</b>' + data.unit + '';
                    }
                    if (typeof data.close != 'undefined') {
                        html += '<br/><b>上架状态：</b>' + (data.close == 0 ? '<span color=green>上架中</span>' : '<span color=red>已下架</span>') + '';
                    } else if (typeof data.active != 'undefined') {
                        html += '<br/><b>上架状态：</b>' + (data.active == 1 ? '<span color=green>上架中</span>' : '<span color=red>已下架</span>') + '';
                    }
                    $("#GoodsInfo").html(html);
                    $("#GoodsInfo").slideDown();
                    changeNum();
                    editorChange();
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg('服务器错误，请稍后再试');
                return false;
            }
        });
        // var goodsid = $("#goodslist option:selected").val();
        // var shopimg = $("#goodslist option:selected").attr('shopimg');
        // if(typeof (shopimg) != "undefined")$("input[name='shopimg']").val(shopimg);
        // $("input[name='goods_id']").val(goodsid);
        // $("#GoodsInfo").hide();
        // $("#price").val('');
    }
    if (!$('select[name="shequ"]').is(':hidden')) $('#goodslist').select2({
        placeholder: '请选择对接商品',
        language: 'zh-CN'
    });
}
$(document).ready(function () {
    $("select[name='prid']").on('change', function () {
        var prid = parseInt($(this).val());
        if (prid > 0) {
            $("#prid_tips").hide();
            $("#price1_tips").html('当已设置加价模板时成本价被修改后鼠标离开输入框可自动改价');
            $("#option1").hide();
            $("#option0").hide();
            getPridPrice();
        } else {
            $("#option0").show();
            $("select[name='specs_id']").change();
        }
    });
    //库存配置
    $("select[name='stock_open']").on('change', function () {
        if (parseInt($(this).val()) > 0) {
            $("#option_stock_num").show();
        } else {
            $("#option_stock_num").hide();
        }
    });
    $("select[name='specs_id']").on('change', function () {
        if (chenmObj.isChange == false) {
            return true;
        }
        var specs_id = parseInt($(this).val());
        console.log("specs_id：" + specs_id);
        if (specs_id > 0) {
            $("#option_stock").hide();
            $("#option1").show();
            var action = $("#action").val();
            if (action == 'add') {
                chenmObj.input2(specs_id);
            } else {
                if (specs_id == chenmObj.specs_id) {
                    var tid = parseInt($("#tid").val());
                    chenmObj.input(tid);
                } else {
                    chenmObj.specs_id = specs_id;
                    chenmObj.input2(specs_id);
                }
            }
        } else {
            $("#option_stock").show();
            $("#option1").hide();
        }
    });
    $("#getclass").click(function () {
        var shequ = $("select[name='shequ']").val();
        if (shequ == '') {
            layer.alert('请先选择一个对接网站');
            return false;
        }
        $('#goodslist').empty();
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=getGoodsclass",
            data: {
                shequ: shequ
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    var type = $("select[name='shequ'] option:selected").attr("type");
                    if (type == "6") {
                        $("#kyxjump").css("display", "inline-block");
                        addkyxelement(data.data);
                    } else if (type == "22") {
                        setShangzhanClass(data.data);
                    } else if (type == "18") {
                        setKkyClass(data.data);
                    } else {
                        layer.alert('该货源站平台类型不支持选择分类！');
                    }
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg('服务器错误。请稍后再试');
                return false;
            }
        });
    });
    $("input[name='price1']").blur(function () {
        var prid = parseInt($("select[name='prid']").val());
        if (prid > 0) {
            getPridPrice();
        } else {
            var specs_id = $("select[name='specs_id']").val();
            if (specs_id > 0) {
                chenmObj.setOptionPrice("#price1_box input[name='price1']");
            }
        }
    });
    $("select[name='multi']").change(function () {
        if ($(this).val() == 1) {
            $("#multi0").show();
        } else {
            $("#multi0").hide();
        }
    });
    $("select[name='is_curl']").change(function () {
        if ($(this).val() == 1) {
            $("#curl_display1").removeAttr("style");
            $("#curl_display2").css("display", "none");
        } else if ($(this).val() == 2) {
            $("#curl_display1").css("display", "none");
            $("#curl_display2").removeAttr("style");
        } else {
            $("#curl_display1").css("display", "none");
            $("#curl_display2").css("display", "none");
        }
        if (!$('select[name="shequ"]').is(':hidden')) $('#goodslist').select2({
            placeholder: '请选择对接商品',
            language: 'zh-CN'
        });
    });
    $("select[name='shequ']").change(function () {
        var type = $("select[name='shequ'] option:selected").attr("type");
        var alias = $("select[name='shequ'] option:selected").attr("alias");
        $("#goods_id label").html("商品ID（goods_id）:");
        $("#goods_id_tips").html("").css("display", "none");
        if (type == 4) {
            $("#goods_type").css("display", "none");
            $("#goods_param").removeAttr("style");
            $("#goods_card_display").removeAttr("style");
            $("#goods_cardpass_display").removeAttr("style");
        } else if (type == 1 || type == 3 || type == 5 || type == 11 || type == 12) {
            $("#goods_type").css("display", "none");
            $("#goods_param").css("display", "none");
        } else if (type == 9 || type == 10) {
            $("#goods_type").css("display", "none");
            $("#goods_param").css("display", "none");
        } else if (type >= 6) {
            $("#goods_type").css("display", "none");
            $("#goods_param").removeAttr("style");
        } else {
            $("#goods_type").removeAttr("style");
            $("#goods_param").removeAttr("style");
        }
        if (type >= 6 && type <= 8) {
            $("#show_value").removeAttr("style");
            $("#goods_id").css("display", "none");
            $("#show_goodslist").css("display", "none");
            $("#goods_param_name").html("下单页面地址：");
            if ($("input[name='goods_param']").val().indexOf('http') < 0) $("input[name='goods_param']").val("");
        } else if (type == 9) {
            $("#show_value").removeAttr("style");
            $("#goods_id").removeAttr("style");
            $("#goods_param").removeAttr("style");
            $("#show_goodslist").css("display", "none");
            $("#goods_id_tips").html("注意！<span style='color:red'>卡商接口地址和网站地址不一样</span>，如无法获取商品参数和对接，请检查货源列表的网站地是否是<span style='color:red'>http://www.kashangwl.com/</span>").removeAttr("style");
        } else if (type == 10) {
            $("#show_value").removeAttr("style");
            $("#goods_id").removeAttr("style");
            $("#show_goodslist").css("display", "none");
            $("#goods_param_name").html("下单页面地址：");
        } else if (type == 12 || type == 13) {
            $("#show_value").removeAttr("style");
            $("#goods_id").removeAttr("style");
            $("#goods_type").css("display", "none");
            $("#show_goodslist").removeAttr("style");
            $("#show_goodslist_search").removeAttr("style");
            $("#goods_param").css("display", "none");
        } else if (type == 16) {
            $("#goods_id").removeAttr("style");
            $("#goods_type").css("display", "none");
            $("#goods_param").css("display", "none");
            $("#show_goodslist").css("display", "none");
        } else if (type == 20) {
            //
        } else {
            $("#show_value").removeAttr("style");
            $("#goods_id").removeAttr("style");
            $("#show_goodslist").removeAttr("style");
            $("#goods_param_name").html("参数名：");
        }
        if (type == 15) {
            $("#show_value").removeAttr("style");
            $("#goods_type").removeAttr("style");
            if (yunbaoSelect == "") {
                var val = $("input[name='goods_type']").val();
                var select = `<label>商品类型:</label><br>
                           <select class="form-control" name="goods_type" default="${val}">
                           <option value="102">直冲</option>
                           <option value="101">卡密</option>
                           </select>
                           `;
                yunbaoSelect = select;
                $("#goods_type").html(select);
            } else {
                var select = yunbaoSelect;
                $("#goods_type").html(select);
            }
            $("#goods_id").css("display", "none");
            $("#show_goodslist").css("display", "none");
            $("#goods_param_name").html("下单页面地址：");
            $("select[name='goods_type']").val($("select[name='goods_type']").attr("default") || 102);
        } else if (type == 18) {
            //卡卡云
            $("#show_classlist").removeAttr("style");
            $("#show_goodslist").removeAttr("style");
            $("#getGoods_class").show();
            $("#getGoods").css("display", "none");
            $("#goods_id").removeAttr("style");
            $("#show_value").removeAttr("style");
            $("#goods_type").removeAttr("style");
            $("#goods_param").css("display", "none");
            if (kkySelect == "") {
                var val = $("input[name='goods_type']").val();
                kkySelect = `<label>商品类型:</label><br>
                           <select class="form-control" name="goods_type" default="${val}">
                           <option value="1">代充</option>
                           <option value="0">卡密</option>
                           </select>
                           `;
                $("#goods_type").html(kkySelect);
                $("input[name='goods_type']").val(val);
            } else {
                $("#goods_type").html(kkySelect);
            }
            return;
        } else if (type == 19) {
            //卡易速
            $("#goods_id").removeAttr("style");
            $("#goods_type").removeAttr("style");
            $("#show_value").removeAttr("style");
            $("#goods_param_name").html("商品ID：");
            $("#show_goodsclass1").css("display", "none");
            $("#show_classlist").removeAttr("style");
            //$("#getGoods_class").css("display", "run-in");
            $("#show_goodslist").removeAttr("style");
            $("#goods_param").css("display", "none");
            $("#goodsclasstips").css("display", "none");
            $("#kyxmoney").css("display", "none");
            $("#kyxjump").css("display", "none");
            $("input[name='goods_param']").attr("placeholder", "请查看对接平台商品，获取商品ID");
            if (kysSelect == "") {
                var val = $("input[name='goods_type']").val();
                kysSelect = `<label>商品类型:</label><br>
                           <select class="form-control" name="goods_type" default="${val}">
                           <option value="1">本地商品(代充)</option>
                           <option value="4">本地商品(卡密)</option>
                           <option value="2">一键通商品(代充)</option>
                           <option value="5">一键通商品(卡密)</option>
                           <option value="3">官方商品(代充)</option>
                           <option value="6">官方商品(卡密)</option>
                           </select>
                           `;
                $("#goods_type").html(kysSelect);
                $("input[name='goods_type']").val(val);
            } else {
                $("#goods_type").html(kysSelect);
            }
            return false;
        } else if (type == 22) {
            //商战网
            $("#show_goodsclass1").removeAttr("style");
            $("#show_classlist").removeAttr("style");
            $("#show_goodslist").removeAttr("style");
            $("#goods_id").removeAttr("style");
            $("#show_value").removeAttr("style");
            $("#goods_type").removeAttr("style");
            $("#goods_param").css("display", "none");
            $("#goodsclasstips").css("display", "none");
            $("#getGoods_class").css("display", "none");
            $("#kyxmoney").css("display", "none");
            $("#kyxjump").css("display", "none");
            $("#goods_card_display").css("display", "none");
            $("#goods_cardpass_display").css("display", "none");
            $("#goods_id_tips").html('该平台支持输入商品ID，光标移开可自动获取商品详情').removeAttr("style");
            if (szwSelect == "") {
                var val = $("input[name='goods_type']").val();
                szwSelect = '<label>商品类型:</label><br>' + '<select class="form-control" name="goods_type" default="' + val + '">' + '<option value="0">人工代充</option>' + '<option value="1">卡密发货</option>' + '</select>';
                $("#goods_type").html(szwSelect);
                $("input[name='goods_type']").val(val);
            } else {
                $("#goods_type").html(szwSelect);
            }
            return false;
        } else if (type == 23) {
            //橙子平台
            $("#show_goodslist").removeAttr("style");
            $("#goods_id").removeAttr("style");
            $("#show_value").removeAttr("style");
            $("#goods_type").css("display", "none");
            $("#goods_param").removeAttr("style");
            $("#getGoods").show();
            return false;
        } else if (type == 24) {
            $("#show_goodslist").css("display", "none");
            $("#goods_id").css("display", "none");
            $("#show_value").removeAttr("style");
            $("#goods_type").css("display", "none");
            $("#goods_param").removeAttr("style");
            $("#goods_param_name").html("商品详情链接：");
            $("#goods_param_tips").html("输入商品详情页面链接地址");
            $("#getGoods").hide();
            return false;
        } else if (type == 25 || type == 26) {
            //直客
            $("#show_goodsclass1").css("display", "none");
            $("#show_classlist").removeAttr("style");
            $("#show_goodslist").removeAttr("style");
            $("#getGoods_class").removeAttr("style");
            $("#goodsclasstips").removeAttr("style");
            $("#goods_id").removeAttr("style");
            $("#goods_id label").html("商品编号SN（goods_id）:");
            $("#goods_param").removeAttr("style");
            $("#goods_param_name").html("对接参数：");
            $("#goods_param_tips").html("上方填写商品编号SN后自动获取！对接卡密商品请留空");
            $("#goods_id_tips").html("通过访问网站商品，在页面链接获取商品编号SN（红色部分就是）如：http://xx.zz.cn/shop/goods/detail?sn=<span style='color:red'>211117234200222D</span>").removeAttr("style");
            $("#show_value").removeAttr("style");
            $("#goods_type").removeAttr("style");
            $("#kyxmoney").css("display", "none");
            $("#kyxjump").css("display", "none");
            $("#goods_card_display").css("display", "none");
            $("#goods_cardpass_display").css("display", "none");
            if (szwSelect == "") {
                var val = $("input[name='goods_type']").val();
                szwSelect = '<label>商品类型:</label><br>' + '<select class="form-control" name="goods_type" default="' + val + '">' + '<option value="1">代充</option>' + '<option value="2">卡密</option>' + '</select>';
                $("#goods_type").html(szwSelect);
                $("input[name='goods_type']").val(val);
            } else {
                $("#goods_type").html(szwSelect);
            }
            return false;
            return false;
        } else if (type == 27) {
            $("#show_goodslist").css("display", "none");
            $("#goods_id").removeAttr("style");
            $("#show_value").removeAttr("style");
            $("#goods_type").css("display", "none");
            $("#goods_param").css("display", "none");
            return false;
        } else if (type == 6) {
            $("#value").attr("min", '1');
            $("#value").attr("max", '');
            $("#value").val('1');
            $("#goods_type").removeAttr("style");
            $("#kayixin_tips").removeAttr("style");
            if (kyxSelect == "") {
                var val = $("input[name='goods_type']").val();
                if (val != "2") {
                    val = "1";
                }
                kyxSelect = `<label>商品类型:</label><br>
                     <select class="form-control" name="goods_type" default="${val}">
                     <option value="1">代充</option>
                     <option value="2">卡密</option>
                     </select>
                           `;
                $("#goods_type").html(kyxSelect);
                $("input[name='goods_type']").val(val);
            } else {
                $("#goods_type").html(kyxSelect);
            }
        } else if (alias != "") {
            $("#show_goodslist").removeAttr("style");
            $("#getGoods").removeAttr("style");
            $("#goods_id").removeAttr("style");
            $("#show_value").removeAttr("style");
            $("#goods_type").removeAttr("style");
            $("#goods_param").removeAttr("style");
            if (typeSelect == "") {
                var val = $("input[name='goods_type']").val();
                if (val != "1") {
                    val = "0";
                }
                typeSelect = `<label>商品类型:</label><br>
                     <select class="form-control" name="goods_type" default="${val}">
                     <option value="1">卡密</option>
                     <option value="0">代充</option>
                     </select>
                           `;
                $("#goods_type").html(typeSelect);
                $("input[name='goods_type']").val(val);
            } else {
                $("#goods_type").html(typeSelect);
            }
            return false;
        } else {
            var val = $("input[name='goods_type']").val();
            if (typeof (val) != "undefined" && val != "") {
                var select = `<label>类型ID（goods_type）:</label><br>
                       <input type="text" class="form-control" name="goods_type" value="${val}">
                       `;
                $("#goods_type").html(select);
            } else {
                var select = `<label>类型ID（goods_type）:</label><br>
                       <input type="text" class="form-control" name="goods_type" value="">
                       `;
                $("#goods_type").html(select);
            }
            $("#kayixin_tips").css("display", "none");
        }
        if (type == 6) {
            $("#show_goodsclass1").removeAttr("style");
            $("#goods_id").css("display", "none");
            $("#goods_param").removeAttr("style");
            $("#show_goodslist").removeAttr("style");
        } else {
            if (type == 22) {
                $("#show_goodsclass1").removeAttr("style");
            } else {
                $("#show_goodsclass1").css("display", "none");
            }
        }
        if (type == 4) {
            $("#show_goodslist").css("display", "none");
        } else {
            $("#goods_card_display").css("display", "none");
            $("#goods_cardpass_display").css("display", "none");
        }
        if (type != 22 && type != 18 && type != 9 && type != 15 && type != 16) {
            $("#getGoods").show();
        } else {
            $("#getGoods").css("display", "none");
        }
        $("#show_classlist").hide();
        if (!$('select[name="shequ"]').is(':hidden')) $('#goodslist').select2({
            placeholder: '请选择对接商品',
            language: 'zh-CN'
        });
        $("#GoodsInfo").hide();
    });
    $("#getGoods_class").click(function () {
        var shequ = $("select[name='shequ']").val();
        var type = $("select[name=shequ] option:selected").attr("type");
        if (shequ == '') {
            layer.alert('请先选择一个对接网站');
            return false;
        }
        $('#classlist_select').empty();
        $('#goodslist').empty();
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        console.log("社区类型：" + type);
        $.ajax({
            type: "POST",
            url: "ajax.php?act=getGoodsclass",
            data: {
                shequ: shequ
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#getGoods').attr('type', data.type);
                    if (type == '19') {
                        //卡易速
                        $('#classlist_select').append('<option value="-1">请选择商品目录</option>');
                        $('#classlist_select').append('<option value="-2">---开头的是子目录</option>');
                        var oid = '';
                        $.each(data.data, function (i, item) {
                            if (item['source_type'] == 2) {
                                oid = ' oid="' + item['oid'] + '"';
                            }
                            $('#classlist_select').append('<option value="' + item['id'] + '" source_type="' + item['source_type'] + '" ' + oid + '>【' + item['id'] + '】' + item['name'] + '</option>');
                            if (item['child'].length > 0) {
                                $.each(item['child'], function (i, child) {
                                    if (child['source_type'] == 2) {
                                        oid = ' oid="' + child['oid'] + '"';
                                    }
                                    $('#classlist_select').append('<option value="' + child['id'] + '" source_type="' + child['source_type'] + '" ' + oid + '>---【' + child['id'] + '】' + child['name'] + '</option>');
                                });
                            }
                        });
                    } else {
                        //其他
                        $('#classlist_select').append('<option value="-1">请选择商品分类</option>');
                        $.each(data.data, function (i, item) {
                            if ('undefined' != typeof item.cid) {
                                var cid = item.cid;
                            } else if ('undefined' != typeof item.groupid) {
                                var cid = item.groupid;
                            } else {
                                var cid = 'undefined' != typeof item.id ? item['id'] : '';
                            }

                            if (cid) {
                                $('#classlist_select').append('<option value="' + cid + '">' + item['name'] + '[' + cid + ('undefined' != typeof item.count ? ' => 商品共' + item.count + '个' : '') + ']</option>');
                                if ('undefined' != typeof item.child && item.child.length > 0) {
                                    $.each(item['child'], function (i2, child) {
                                        $('#classlist_select').append('<option value="' + child['id'] + '">|-' + child['name'] + '[' + child['id'] + ('undefined' != typeof child.count ? ' => 商品共' + child.count + '个' : '') + ']</option>');
                                    });
                                }
                            } else {
                                return false;
                            }
                        });

                    }
                    $('#classlist_select').select2({
                        placeholder: '请选择对接分类',
                        language: 'zh-CN'
                    });
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg('服务器错误。请稍后再试');
                return false;
            }
        });
    });
    $("#goodsclass1").change(function () {
        var groupid = $('#goodsclass1 option:selected').val();
        if (groupid > 0 && "object" != typeof shangzhanObj[groupid]) {
            return layer.alert("该目录下无二级目录，换一个再试试");
        }
        $('#classlist_select').empty();
        $('#classlist_select').append('<option value="-1">请选择二级目录</option>');
        var arr = shangzhanObj[groupid];
        $.each(arr, function (i, item) {
            $('#classlist_select').append('<option value="' + item.id + '">' + item.title + '"</option>');
        });
        $('#classlist_select').select2({
            placeholder: '请选择二级目录',
            language: 'zh-CN'
        });
        $('#classlist_select').val('-1');
    });
    $("#classlist_select").change(function () {
        var type = $("select[name=shequ] option:selected").attr("type");
        if (type == '22') {
            var id = parseInt($(this).val());
            if (id > 0) {
                $("#getGoods").click();
            }
            //$("#getGoods_class").css('display', 'none');
            //$("#getGoods").css('display', 'inherit');
        } else {
            //其他插件
            var id = parseInt($(this).val());
            if (id > 0 || id == -1) {
                $("#getGoods").click();
            }
            //$("#getGoods_class").css('display', 'none');
            //$("#getGoods").css('display', 'inherit');
        }
        return true;
    });

    $(document).on('click', '.nextPage', function () {
        // 下一页
        var el = $("select[id='goodslist']");
        console.log(el.attr('page'));
        el.attr('page', el.attr('page') ? parseInt(el.attr('page')) + 1 : 2);
        $("#getGoods").trigger('click');
    });
    $(document).on('click', '.lastPage', function () {
        // 上一页
        var el = $("select[id='goodslist']");
        el.attr('page', el.attr('page') ? parseInt(el.attr('page')) - 1 : 1);
        $("#getGoods").trigger('click');
    });
    $(document).on('click', '#getGoods', function () {
        var shequ = $("select[name='shequ']").val();
        var type = $("select[name=shequ] option:selected").attr("type");
        if (shequ == '') {
            layer.alert('请先选择一个对接网站');
            return false;
        }
        $('#goodslist').empty();
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        if (type == 6) {
            var dirId = $("#goodsclass1").val();
            dirId = dirId.split("=")[1];
            $.ajax({
                type: "POST",
                url: "ajax.php?act=getkxyGoodslist",
                data: {
                    shequ: shequ,
                    dirId: dirId
                },
                dataType: 'json',
                success: function (data) {
                    layer.close(ii);
                    if (data.code == 0) {
                        $('#getGoods').attr('type', data.type);
                        var content = '<option value="-1">点我搜索选择商品</option>';
                        var arr = data.data;
                        for (var a = 0; a < arr.length; a++) {
                            content += "<option value=\"" + arr[a]["id"] + "\" " + "url=\"" + arr[a]["url"] + "\" >" + arr[a]["name"] + "</option>";
                        }
                        $("#goodslist").empty();
                        $("#goodslist").append(content);
                        $('#goodslist').select2({
                            placeholder: '请选择对接商品',
                            language: 'zh-CN'
                        });
                        if (typeof ($("#goodslist").attr('default')) != 'undefined') {
                            $('#goodslist').val($("#goodslist").attr('default'));
                            if ($('#goodslist').val() != null) $("#goodslist").change();
                        } else {
                            $('#goodslist').val('-1');
                        }
                        layer.alert(data.msg);
                    } else {
                        layer.alert(data.msg);
                    }
                    return;
                },
                error: function (data) {
                    layer.close(ii);
                    layer.msg('服务器错误。请稍后再试');
                    return false;
                }
            });
            return false;
        }

        if (type == 22) {
            var category_id = parseInt($("#classlist_select option:selected").val());
            if (typeof category_id == 'number') {
                var pData = {
                    shequ: shequ,
                    category_id: category_id,
                };
            } else {
                layer.alert('该二级目录不存在，请换一个或重新获取一级目录试试');
                return false;
            }
        } else if (type == 19) {
            //卡易速
            var category_id = parseInt($("#classlist_select").val());
            var source_type = parseInt($("#classlist_select").attr('source_type'));
            var oid = $("#classlist_select").attr('oid') || '';
            var pData = {
                shequ: shequ,
                category_id: category_id,
                source_type: source_type,
                oid: oid
            };
        } else {
            var cid = $("#classlist_select").val();
            var pData = {
                shequ: shequ,
                cid: cid
            };
        }
        var page = $("select[id='goodslist']").attr('page') || 1;
        $.ajax({
            type: "POST",
            url: "ajax.php?act=getGoodsList",
            data: Object.assign(pData, {
                page: page
            }),
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    $('#getGoods').attr('type', data.type);
                    $('#goodslist').append('<option value="-1">点我搜索/选择商品</option>');
                    if (page > 1) {
                        $(".lastPage").removeAttr('style');
                    }

                    if (data.page && data.page === true) {
                        if (data.allPage) {
                            layer.msg('当前是第' + page + '页数据，共有' + data.allPage + '页');
                        } else {
                            layer.msg('当前是第' + page + '页数据，不是最后一页哦~');
                        }
                        $(".nextPage").removeAttr('style');
                    } else {
                        if (page > 1) {
                            layer.msg('当前是最后一页，没有更多了~');
                        }
                        if (page == 1) {
                            $(".lastPage").css("display", 'none');
                        }
                        $(".nextPage").css("display", 'none');
                    }
                    $.each(data.data, function (i, item) {
                        if (data.type == 'jiuwu') {
                            $('#goodslist').append('<option value="' + item.id + '" goodstype="' + item.type + '" shopimg="' + item.shopimg + '" minnum="' + item.minnum + '" maxnum="' + item.maxnum + '" price="' + item.price + '" unit="' + item.unit + '" close="' + item.close + '">' + item.name + '</option>');
                        } else if (data.type == 'yile' && item.close != 1) {
                            $('#goodslist').append('<option value="' + item.id + '">' + item.name + '</option>');
                        } else if (data.type == 'xingmo') {
                            $('#goodslist').append('<option value="' + item.id + '" shopimg="' + item.shopimg + '">' + item.name + '</option>');
                        } else if (data.type == 'jumeng') {
                            $('#goodslist').append('<option value="' + item.id + '" shopimg="' + item.shopimg + '" minnum="' + item.minnum + '" maxnum="' + item.maxnum + '" price="' + item.price + '">' + item.name + '</option>');
                        } else if (data.type == 'caihon' || data.type == 'chenmeng') {
                            $('#goodslist').append('<option value="' + item.tid + '" cid="' + item.cid + '" shopimg="' + item.shopimg + '" price="' + item.price + '">' + item.name + '</option>');
                        } else if (data.type == 'shikonyun') {
                            $('#goodslist').append('<option value="' + item.id + '" desc="' + escape(item.desc) + '" shopimg="' + item.shopimg + '" min="' + item.minnum + '" max="' + item.maxnum + '" price="' + item.price + '" param="' + item.param + '" input="' + item.input + '" inputs="' + item.inputs + '" active="' + item.active + '">' + item.name + '</option>');
                        } else if (data.type == 'shangzhan') {
                            if ('undefined' != typeof item.title) {
                                $('#goodslist').append('<option value="' + item.id + '">' + item.title + '</option>');
                            } else {
                                $('#goodslist').append('<option value="' + item.id + '">' + item.name + '</option>');
                            }
                        } else if (data.type == 'kayisu') {
                            $('#goodslist').append('<option value="' + item.id + '" source_type="' + item.source_type + '">' + item.name + '</option>');
                        } else if (data.type == 'chengzi') {
                            $('#goodslist').append('<option value="' + item.tid + '" active="' + item.active + '" min="' + item.min + '" max="' + item.max + '" price="' + item.price + '" inputs="' + item.inputs + '" param="' + item.param + '">' + (item.active == 1 ? '[上架中]' : '[已下架]') + item.name + '</option>');
                        } else if (data.type == 'kakayun') {
                            if ('undefined' != typeof item.price) {
                                $('#goodslist').append('<option value="' + item.id + '" price="' + item.price + '" type="' + item.type + '" stock="' + item.stock + '" active="' + item.active + '">' + item.name + '</option>');
                            } else {
                                $('#goodslist').append('<option value="' + item.id + '">' + item.name + '</option>');
                            }
                        } else {
                            $('#goodslist').append('<option value="' + item.id + '">' + item.name + '</option>');
                        }
                    });
                    $('#goodslist').select2({
                        placeholder: '请选择对接商品',
                        language: 'zh-CN'
                    });
                    if (typeof ($("#goodslist").attr('default')) != 'undefined') {
                        $('#goodslist').val($("#goodslist").attr('default'));
                        if ($('#goodslist').val() != null) $("#goodslist").change();
                    } else {
                        $('#goodslist').val('-1');
                    }
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg('服务器错误。请稍后再试');
                return false;
            }
        });
    });
    $("#goodslist").change(function () {
        var type = $('#getGoods').attr('type');
        var goodsid = $("#goodslist option:selected").val();
        if (goodsid == "" || goodsid == 0 || goodsid == null || goodsid == undefined) return;
        if (goodsid == '-1') {
            layer.msg("该条数据不是商品，请重新选择！");
            return false;
        }
        // console.log("[goodslist:change]社区类型：" + type);
        getGoodsParam(type, goodsid);
    });
    $("input[name='goods_id']").blur(function () {
        var type = $("select[name='shequ'] option:selected").attr("type");
        var alias = $("select[name='shequ'] option:selected").attr("alias");
        if (type == 9) {
            var shequ = $("select[name='shequ']").val();
            var goodsid = $(this).val();
            if (goodsid == '' || goodsid == 0 || typeof (goodsid) == undefined) return;
            var ii = layer.load(2, {
                shade: [0.1, '#fff']
            });
            $.ajax({
                type: "POST",
                url: "ajax.php?act=getGoodsParam",
                data: {
                    shequ: shequ,
                    goodsid: goodsid
                },
                dataType: 'json',
                success: function (data) {
                    layer.close(ii);
                    if (data.code == 0) {
                        var input = "",
                            inputs = "",
                            params = "";
                        if (!data.data.input) data.data.input = "QQ号码";
                        if ($("input[name='name']").val() == "") $("input[name='name']").val(data.data.name);
                        input = data.data.input;
                        if ($("input[name='input']").val() == "") $("input[name='input']").val(input);
                        if (!!data.data.inputs && data.data.inputs.length > 0) {
                            inputs = data.data.inputs;
                            params = input + "|" + inputs;
                        }
                        if ($("input[name='inputs']").val() == "") $("input[name='inputs']").val(inputs);
                        $("input[name='goods_param']").val(params);
                        var limit_min = data.data.valid_purchasing_quantity.split("-")[0];
                        var limit_max = data.data.valid_purchasing_quantity.split("-")[1];
                        $("#price").val(data.data.price);
                        $("#value").attr("min", limit_min);
                        $("#value").attr("max", limit_max);
                        $("input[name='value']").val(limit_min);
                        $("input[name='price1']").val(getFloat(data.data.price * limit_min, 6));
                        var url = $("select[name='shequ'] option:selected").html();
                        url = url.split("（")[0];
                        $("#GoodsInfo").html('<b>商品名称：</b>' + data.data.name + '&nbsp;<a style="color:black" href="' + url + 'buy/' + goodsid + '" target="_blank" rel="noreferrrer">进入下单页面</a><br/><b>商品售价：</b>' + data.data.price + ' 元<br/><b>最小下单数量：</b>' + limit_min + '<br/><b>最大下单数量：</b>' + limit_max);
                        $("#GoodsInfo").slideDown();
                        changeNum();
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
        var goodsid = $(this).val();
        if (!goodsid) {
            return;
        }

        if (type == 12) {
            getGoodsParam('caihon', goodsid);
        } else if (type == 13) {
            getGoodsParam('chenmeng', goodsid);
        } else if (type == 22) {
            getGoodsParam('shangzhan', goodsid);
        } else if (type == 18) {
            getGoodsParam('kakayun', goodsid);
        } else if (type == 19) {
            getGoodsParam('kayisu', goodsid);
        } else if (type == 25 || type == 26) {
            getGoodsParam('zhike', goodsid);
        } else if (type == 1) {
            getGoodsParam('yile', goodsid);
        } else if (alias != '') {
            getGoodsParam('extend', goodsid);
        }
        return;
    });
    var items = $("select[default]");
    for (var i = 0; i < items.length; i++) {
        $(items[i]).val($(items[i]).attr("default") || 0);
    }
    chenmObj.init();
    $("select[name='shequ']").change();
    $("select[name='multi']").change();
    $("select[name='prid']").change();
    $("select[name='specs_id']").change();
    $("select[name='stock_open']").change();
    $("#specs_tips").html('当绑定使用规格后上方的加价模板将无效').show();
});