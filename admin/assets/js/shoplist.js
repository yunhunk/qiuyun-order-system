"use strict";
var checkboxlist = '',
    modelList = '',
    pridList = '',
    idlist = '',
    classListSelect = '',
    pageLoad = true;
var changeEvent = false;
$(document).ready(function () {
    listTable();
    getPridList();
    getModelList();
    getClassList();
})

function refreshPrice(cid) {
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: 'GET',
        url: '?act=refreshPrice&cid=' + cid,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg(data.msg);
                listTable();
            } else {
                layer.alert(data.msg);
            }
        },
        error: function (data) {
            layer.close(ii);
            layer.msg('服务器错误');
            return false;
        }
    });
}

function check1(field) {
    var checkbox = document.getElementsByName('checkbox[]');
    checkboxlist = '';
    for (var i = 0; i < checkbox.length; i++) {
        if (checkbox[i].checked == true) {
            checkbox[i].checked = false;
        } else {
            checkbox[i].checked = true;
            if (checkboxlist == '') {
                checkboxlist = '' + checkbox[i].value;
            } else {
                checkboxlist = ',' + checkbox[i].value;
            }
        }
    }
}

function getClassList() {
    if ("" != classListSelect) {
        return classListSelect;
    } else {
        $.ajax({
            type: 'POST',
            url: 'ajax.php?act=getClassListSelect',
            dataType: 'html',
            success: function (data) {
                classListSelect = data;
            },
            error: function (data) {
                layer.msg('获取加价模板列表请求超时，请稍后再试！');
            }
        });
        return pridList;
    }
}

function getPridList() {
    if ("" != pridList) {
        return pridList;
    } else {
        $.ajax({
            type: 'POST',
            url: 'ajax.php?act=getPridListSelect',
            dataType: 'html',
            success: function (data) {
                pridList = data;
            },
            error: function (data) {
                layer.msg('获取加价模板列表请求超时，请稍后再试！');
            }
        });
        return pridList;
    }
}

function getModelList() {
    if ("" != modelList) {
        return modelList;
    } else {
        $.ajax({
            type: 'POST',
            url: 'ajax.php?act=getModelList',
            dataType: 'html',
            success: function (data) {
                modelList = data;
            },
            error: function (data) {
                layer.msg('获取快捷回复列表请求超时，请稍后再试！');
            }
        });
        return modelList;
    }
}

function unselectall1(that) {
    checkboxlist = getVals() || '';
}

function getVals() {
    var checkbox = document.getElementsByName('checkbox[]');
    idlist = '';
    //console.log(checkbox);
    for (var i = 0; i < checkbox.length; i++) {
        if (checkbox[i].checked) {
            if (idlist == '') {
                idlist = '' + checkbox[i].value;
            } else {
                idlist = idlist + ',' + checkbox[i].value;
            }
        }
    }
    console.log(idlist);
    return idlist;
}
var relatedList = '';

function unselect(that) {
    relatedList = getRelatedList() || '';
}

function getRelatedList() {
    var checkbox = document.getElementsByName('checkbox2[]');
    idlist = '';
    //console.log(checkbox);
    for (var i = 0; i < checkbox.length; i++) {
        if (checkbox[i].checked) {
            if (idlist == '') {
                idlist = '' + checkbox[i].value;
            } else {
                idlist = idlist + ',' + checkbox[i].value;
            }
        }
    }
    console.log(idlist);
    return idlist;
}

function listTable(query) {
    var url = window.document.location.href.toString();
    var queryString = url.split("?")[1];
    query = query || queryString;
    if (query == 'start' || query == undefined) {
        query = '';
        history.replaceState({}, null, './shoplist.php');
    } else if (query != undefined) {
        history.replaceState({}, null, './shoplist.php?' + query);
    }
    layer.closeAll();
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'GET',
        url: 'shoplist-table.php?' + query,
        dataType: 'html',
        cache: false,
        success: function (data) {
            layer.close(ii);
            $("#listTable").html(data);
            setSelectVal()
        },
        error: function (data) {
            layer.msg('服务器错误');
            return false;
        }
    });
}

function show(tid) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=getTool&tid=' + tid,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                var item = '<table class="table table-condensed table-hover">';
                item += '<tr><td colspan="6" style="text-align:center"><b>商品详情</b></td></tr><tr><td class="info">商品ID</td><td colspan="5">' + data.data.tid + '</td></tr><tr><td class="info">商品名称</td><td colspan="5">' + data.data.name + '</td></tr><tr><td class="info">商品链接</td><td colspan="5"><a href="' + data.data.link + '" target="_blank">' + data.data.link + '</a></td></tr>';
                item += '</table>';
                layer.open({
                    type: 1,
                    title: '商品详情',
                    skin: 'layui-layer-rim',
                    content: item
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

function getCheckedTid() {
    var checkbok1 = document.getElementsByName('checkbox[]');
    var list = '';
    for (var i = 0; i < checkbok1.length; i++) {
        if (checkbok1[i].checked == true) {
            if (list == "") {
                list = '' + checkbok1[i].value;
            } else {
                list += '|' + checkbok1[i].value;
            }
        }
    }
    console.log(list);
    return list;
}

function setClass() {
    var tidlist = getCheckedTid();
    if (tidlist == "") {
        return layer.alert('你当前未选中任何商品！');
    }
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        url: "?act=getClass",
        type: 'GET',
        dataType: "json",
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                $("#editCids_model").modal("show");
                $("#editCids_tid").html(tidlist);
                $("#editCids_list").html(data.list);
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
}

function readClass(tid) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        url: "?act=readClass",
        type: 'POST',
        data: 'tid=' + tid,
        dataType: "json",
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                var tidlist = tid;
                $("#readCids_model").modal("show");
                $("#readCids_tid").html(tid);
                $("#readCids_list").html(data.list);
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
}

function setShopCids(tidlist) {
    var checkbok2 = document.getElementsByName('classbox[]');
    var cidlist = '';
    for (var i = 0; i < checkbok2.length; i++) {
        if (checkbok2[i].checked == true) {
            if (cidlist == "") {
                cidlist = '' + checkbok2[i].value;
            } else {
                cidlist += '|' + checkbok2[i].value;
            }
        }
    }
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        url: "?act=setClass",
        type: 'POST',
        data: 'tidlist=' + tidlist + "&cidlist=" + cidlist,
        dataType: "json",
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg(data.msg);
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
}

function editAllPrice() {
    var prid = $("select[name='prid_n']").val();
    $("input[name='prid']").val(prid);
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=editAllPrice',
        data: $('#form1').serialize(),
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                listTable();
                layer.alert(data.msg);
            } else {
                layer.alert(data.msg);
            }
        },
        error: function (data) {
            layer.msg('请求超时');
            listTable();
        }
    });
}

function setPayActive(el) {
    if (el.value == '1') {
        $(el).val('0');
    } else {
        $(el).val('1');
    }
}


function operation(aid) {
    var area = [$(window).width() > 640 ? '540px' : '95%', 'auto'];

    if (aid == 1 || aid == 2) {
        // 批量替换商品名称(选中商品)
        var laybox = layer.open({
            type: 1,
            area: area,
            title: aid == 1 ? '批量替换商品名称(选中商品)' : '批量替换商品名称(全部商品)',
            content: '<div class="panel-body"><div class="form-group"><div class="input-group"><div class="input-group-addon">被替换关键字</div><input id="rep0" class="form-control"/></div></div><div class="form-group"><div class="input-group"><div class="input-group-addon">替换后内容</div><input id="rep1" class="form-control"/></div></div></div>',
            btn: ['提交操作', '取消操作'],
            yes: function (index, layero) {
                var rep0 = $("#rep0").val();
                var rep1 = $("#rep1").val();
                var ii = layer.load(2, {
                    shade: [0.1, '#fff']
                });
                $.ajax({
                    type: 'POST',
                    url: 'ajax.php?act=shop_operation',
                    data: {
                        checkbox: checkboxlist,
                        aid: aid,
                        rep0: rep0,
                        rep1: rep1
                    },
                    dataType: 'json',
                    success: function (data) {
                        layer.close(ii);
                        if (data.code == 0) {
                            listTable();
                            layer.alert(data.msg);
                        } else {
                            layer.alert(data.msg);
                        }
                    },
                    error: function (data) {
                        layer.msg('请求超时');
                        listTable();
                    }
                });
            },
            btn2: function () {
                layer.close(laybox);
            }
        });
        return false;
    }
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=shop_operation',
        data: {
            checkbox: checkboxlist,
            aid: aid,
        },
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                listTable();
                layer.alert(data.msg);
            } else {
                layer.alert(data.msg);
            }
        },
        error: function (data) {
            layer.msg('请求超时');
            listTable();
        }
    });
    return false;
}


function change() {
    var aid = parseInt($('select[name=aid]').val());
    var area = [$(window).width() > 640 ? '540px' : '95%', 'auto'];
    if (aid == 10) {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: 'GET',
            url: 'ajax.php?act=getAllPrice',
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.open({
                        type: 1,
                        area: area,
                        title: '修改加价模板',
                        content: data.data
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
        return false;
    }
    if (aid == 7 && $("input[name='check_val']").val() == '') {
        var laybox = layer.open({
            type: 1,
            area: area,
            title: '批量设置Q钻自带检测',
            content: "<div class=\"panel-body\"><div class='form-group'><div class='input-group'><div class='input-group-addon'>选择Q钻类型</div><select class='form-control' id='check_val' default=''><option value='-1'>请选择QQ钻类型</option><option value='0'>关闭Q钻自带检测</option><option value='超级会员'>超级会员</option><option value='QQ会员'>QQ会员</option><option value='腾讯视频VIP'>腾讯视频VIP</option><option value='豪华黄钻'>豪华黄钻</option><option value='黄钻贵族'>黄钻贵族</option><option value='绿钻豪华版'>绿钻豪华版</option><option value='绿钻'>绿钻</option><option value='蓝钻'>蓝钻</option><option value='紫钻'>紫钻</option><option value='红钻'>红钻</option></select></div></div></div>",
            btn: ['确定批量', '取消操作'],
            yes: function (index, layero) {
                var text = $('#check_val').val();
                $('input[name=check_val]').val(text);
                change();
            },
            btn2: function () {
                layer.close(laybox);
            }
        });
        return false;
    } else if (aid == 12 && $("input[name='result']").val() == '') {
        var laybox = layer.open({
            type: 1,
            area: area,
            title: '批量预置处理信息',
            content: '<div class="panel-body"><div class="form-group"><div class="input-group"><div class="input-group-addon">批量预置处理信息</div><textarea type="text" cols="25" rows="6" id="p_result" class="form-control"></textarea></div></div><div class="form-group"><label>快捷模板:</label> <div class="form-group">' + modelList + '</div></div></div>',
            btn: ['确定批量', '取消操作'],
            yes: function (index, layero) {
                var text = $("#p_result").val();
                $("input[name='result']").val(text);
                change();
            },
            btn2: function () {
                layer.close(laybox);
            }
        });
        return false;
    } else if (aid == 13 && $("input[name='hide_val']").val() == '') {
        var laybox = layer.open({
            type: 1,
            area: area,
            title: '批量设置下架原因',
            content: '<div class="panel-body"><div class="form-group"><div class="input-group"><div class="input-group-addon">批量设置下架原因</div><textarea type="text" cols="25" rows="6" id="hide_result" class="result"></textarea></div></div><div class="form-group"><label>快捷模板:</label> <div class="form-group">' + modelList + '</div></div></div>',
            btn: ['确定批量', '取消操作'],
            yes: function (index, layero) {
                var text = $("#hide_result").val();
                $("input[name='hide_val']").val(text);
                change();
            },
            btn2: function () {
                layer.close(laybox);
            }
        });
        return false;
    } else if (aid == 16 && $("input[name='prid']").val() == '') {
        var laybox = layer.open({
            type: 1,
            area: area,
            title: '批量设置加价模板',
            content: '<div class="panel-body"><div class="form-group"><div class="input-group"><div class="input-group-addon">请选择模板</div><select class="form-control" id="p_prid">' + pridList + '</select></div></div></div>',
            btn: ['确定批量', '取消操作'],
            yes: function (index, layero) {
                var prid = $("#p_prid").val();
                $("input[name='prid']").val(prid);
                change();
            },
            btn2: function () {
                layer.close(laybox);
            }
        });
        return false;
    } else if (aid == 19 && $("input[name='pay_val']").val() == '') {
        var laybox = layer.open({
            type: 1,
            area: area,
            title: '批量设置支付名称',
            content: '<div class="panel-body"><div class="form-group"><div class="input-group"><div class="input-group-addon">填写支付名称</div><input type="text" class="form-control" id="p_pay"/ placeholder="可留空，留空时清空"></div></div></div>',
            btn: ['确定批量', '取消操作'],
            yes: function (index, layero) {
                var pay_name = $("#p_pay").val();
                if (pay_name == "") pay_name = "null";
                $("input[name='pay_val']").val(pay_name);
                change();
            },
            btn2: function () {
                layer.close(laybox);
            }
        });
        return false;
    } else if (aid == 20 && $("input[name='pay_status']").val() == 'false') {
        var html = '<div class="panel-body"><div class="form-group"><table class="table table-striped table-bordered table-condensed text-center"id="pay_type"><tbody><tr align="center"><td>QQ钱包</td><td>微信支付</td><td>支付宝</td><td>余额支付</td></tr><tr><td><input type="checkbox" class="cmckb cmckb-flat" checked onclick="setPayActive(this)" id="pay_qqpay" value="1"><label class="cmckb-btn" for="pay_qqpay"></label></td><td><input type="checkbox" class="cmckb cmckb-flat" checked onclick="setPayActive(this)" id="pay_wxpay" value="1"><label class="cmckb-btn"for="pay_wxpay"></label></td><td><input type="checkbox" class="cmckb cmckb-flat" checked onclick="setPayActive(this)" id="pay_alipay" value="1"><label class="cmckb-btn"for="pay_alipay"></label></td><td><input type="checkbox" class="cmckb cmckb-flat" checked onclick="setPayActive(this)" id="pay_rmb" value="1"><label class="cmckb-btn"for="pay_rmb"></label></td></tr></table></div></div>';
        var laybox = layer.open({
            type: 1,
            area: area,
            title: '批量设置支付方式',
            content: html,
            btn: ['确定批量', '取消操作'],
            yes: function (index, layero) {
                $("input[name='pay_status']").val('true');
                $("input[name='pay_alipay']").val($("#pay_alipay").val());
                $("input[name='pay_qqpay']").val($("#pay_qqpay").val());
                $("input[name='pay_wxpay']").val($("#pay_wxpay").val());
                $("input[name='pay_rmb']").val($("#pay_rmb").val());
                setTimeout(function () {
                    change();
                }, 200);
            },
            btn2: function () {
                layer.close(laybox);
            }
        });
        return false;
    } else if (aid == 21 && $("input[name='shopimg_ok']").val() == 'false') {
        var laybox = layer.open({
            type: 1,
            area: area,
            title: '批量设置商品图片',
            content: '<div class="panel-body"><div class="form-group"><div class="input-group"><div class="input-group-addon">图片链接</div><input type="text" id="shopimg" class="form-control"/></div></div></div>',
            btn: ['确定批量', '取消操作'],
            yes: function (index, layero) {
                $("input[name='shopimg_ok']").val('true');
                $("input[name='shopimg']").val($("#shopimg").val());
                change();
            },
            btn2: function () {
                layer.close(laybox);
            }
        });
        return false;
    } else if (aid == 22 && $("input[name='input_ok']").val() == 'false') {
        var laybox = layer.open({
            type: 1,
            area: area,
            title: '批量设置输入框标题',
            content: '<div class="panel-body"><div class="form-group"><div class="input-group"><div class="input-group-addon">输入框标题</div><input type="text" id="input" class="form-control"/></div></div><div class="form-group"><div class="input-group"><div class="input-group-addon">更多标题</div><input type="text" id="inputs" class="form-control"/></div></div></div>',
            btn: ['确定批量', '取消操作'],
            yes: function (index, layero) {
                $("input[name='input_ok']").val('true');
                $("input[name='input']").val($("#input").val());
                $("input[name='inputs']").val($("#inputs").val());
                change();
            },
            btn2: function () {
                layer.close(laybox);
            }
        });
        return false;
    } else if (aid == 23 && $("input[name='desc_ok']").val() == 'false') {
        var laybox = layer.open({
            type: 1,
            area: area,
            title: '批量设置商品简介',
            content: '<div class="panel-body"><div class="form-group"><div class="input-group"><div class="input-group-addon">商品简介</div><textarea id="desc" class="form-control" rows="6"></textarea></div></div></div>',
            btn: ['确定批量', '取消操作'],
            yes: function (index, layero) {
                $("input[name='desc_ok']").val('true');
                $("input[name='desc']").val($("#desc").val());
                change();
            },
            btn2: function () {
                layer.close(laybox);
            }
        });
        return false;
    } else if (aid == 24 && $("input[name='alert_ok']").val() == 'false') {
        var laybox = layer.open({
            type: 1,
            area: area,
            title: '批量设置提示内容',
            content: '<div class="panel-body"><div class="form-group"><div class="input-group"><div class="input-group-addon">提示内容</div><textarea id="alert" class="form-control" rows="6"></textarea></div></div></div>',
            btn: ['提交操作', '取消操作'],
            yes: function (index, layero) {
                $("input[name='alert_ok']").val('true');
                $("input[name='alert']").val($("#alert").val());
                change();
            },
            btn2: function () {
                layer.close(laybox);
            }
        });
        return false;
    }
    $("input[name='shopimg_ok']").val('false');
    $("input[name='input_ok']").val('false');
    $("input[name='pay_status']").val('false');
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=shop_change',
        data: $('#form1').serialize(),
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                listTable();
                layer.alert(data.msg);
            } else {
                layer.alert(data.msg);
            }
        },
        error: function (data) {
            layer.msg('请求超时');
            listTable();
        }
    });
    return false;
}

function setReply(id, obj = '') {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: 'model.php?my=getReply',
        dataType: 'json',
        data: {
            id: id
        },
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                if (obj == '') {
                    obj = '.result';
                }
                $(obj).val(escape2Html(data.reply));
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

function escape2Html(str) {
    var arrEntities = {
        'lt': '<',
        'gt': '>',
        'nbsp': ' ',
        'amp': '&',
        'quot': '"'
    };
    return str.replace(/&(lt|gt|nbsp|amp|quot);/ig, function (all, t) {
        return arrEntities[t];
    });
}

function move() {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=shop_move',
        data: $('#form1').serialize(),
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                listTable();
                layer.alert(data.msg);
            } else {
                layer.alert(data.msg);
            }
        },
        error: function (data) {
            layer.msg('请求超时');
            listTable();
        }
    });
    return false;
}





function setActive(tid, active) {
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=setTools&tid=' + tid + '&active=' + active,
        dataType: 'json',
        success: function (data) {
            listTable();
        },
        error: function (data) {
            layer.msg('服务器错误');
            return false;
        }
    });
}

function setClose(tid, close) {
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=setTools&tid=' + tid + '&close=' + close,
        dataType: 'json',
        success: function (data) {
            listTable();
        },
        error: function (data) {
            layer.msg('服务器错误');
            return false;
        }
    });
}

function setRepeat(tid, repeat) {
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=setRepeat&tid=' + tid + '&repeat=' + repeat,
        dataType: 'json',
        success: function (data) {
            listTable();
        },
        error: function (data) {
            layer.msg('服务器错误');
            return false;
        }
    });
}

function delTool(tid) {
    var confirmobj = layer.confirm('你确实要删除此商品吗？', {
        btn: ['确定', '取消']
    }, function () {
        $.ajax({
            type: 'GET',
            url: 'ajax.php?act=delTool&tid=' + tid,
            dataType: 'json',
            success: function (data) {
                if (data.code == 0) {
                    layer.msg('删除成功');
                    listTable();
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.msg('服务器错误');
                return false;
            }
        });
    }, function () {
        layer.close(confirmobj);
    });
}

function sort(cid, tid, sort) {
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=setToolSort&cid=' + cid + '&tid=' + tid + '&sort=' + sort,
        dataType: 'json',
        success: function (data) {
            listTable();
        },
        error: function (data) {
            layer.msg('服务器错误');
            return false;
        }
    });
}

function getPrice(tid) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=getPrice&tid=' + tid,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.open({
                    type: 1,
                    title: '修改商品价格',
                    skin: 'layui-layer-rim',
                    content: data.data,
                    success: function () {
                        setSelectVal();
                        setTimeout(function () {
                            changePrice(tid);
                        }, 500);
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

function getFloat(number, n) {
    n = n ? parseInt(n) : 0;
    if (n <= 0) return Math.round(number);
    number = Math.round(number * Math.pow(10, n)) / Math.pow(10, n);
    return number;
}

function changePrice(tid) {
    var price1 = getFloat($("#edit_price1_" + tid).val(), 2);
    var prid = $("#edit_prid_" + tid).val();
    if (prid == '0') {
        $("#price_s").attr("disabled", false);
        $("#cost_s").attr("disabled", false);
        $("#cost2_s").attr("disabled", false);
        $("#price").attr("disabled", true);
        console.log("当前prid：" + prid);
    } else {
        $("#price_s").attr("disabled", true);
        $("#cost_s").attr("disabled", true);
        $("#cost2_s").attr("disabled", true);
        $("#price").attr("disabled", false);
        if (price1 == 0) {
            $("#price_s").val(price1);
            $("#cost_s").val(price1);
            $("#cost2_s").val(price1);
            return true;
        }
        var kind = parseFloat($("#edit_prid_" + tid + " option:selected").attr('kind'), 0);
        var p_2 = parseFloat($("#edit_prid_" + tid + " option:selected").attr('p_2'), 2);
        var p_1 = parseFloat($("#edit_prid_" + tid + " option:selected").attr('p_1'), 2);
        var p_0 = parseFloat($("#edit_prid_" + tid + " option:selected").attr('p_0'), 2);
        console.log("成本：" + price1, "方式：" + kind, "p_2：" + p_2, "p_1：" + p_1, "p_0：" + p_0);
        $("#price_s").val(getFloat(kind == 2 ? price1 + p_0 * price1 / 100 : price1 + p_0, 2));
        $("#cost_s").val(getFloat(kind == 2 ? price1 + p_1 * price1 / 100 : price1 + p_1, 2));
        $("#cost2_s").val(getFloat(kind == 2 ? price1 + p_2 * price1 / 100 : price1 + p_2, 2));
    }
}

function setSelectVal() {
    var items = $("select[default]");
    for (i = 0; i < items.length; i++) {
        $(items[i]).val($(items[i]).attr("default") || 0);
    }
}

function related(tid) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'GET',
        url: '?act=tools&cid=0',
        data: {
            tid: tid
        },
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                var lay1 = layer.open({
                    type: 1,
                    title: '设置关联商品',
                    anim: 3,
                    area: ['80%', '70%'],
                    scrollbar: false,
                    btnAlign: 'c',
                    content: '<div class="col-sm-12 col-md-10 center-block" style="float: none;padding-top:15px;max-height:600px;overflow: auto;"><div class="panel panel-body"><div class="form-inline">分类查看：<div class="form-group"><select class="form-control"onchange="tools(this.value)" name="cid" default="' + data.cid + '" required>' + classListSelect + '</select></div></div><br><div class="table-responsive"><table class="table table-striped"><thead><tr><th>商品ID</th><th>商品名称</th><th>商品价格</th><th>所属分类</th><th class="text-center">操作</th></tr></thead><tbody id="toollist">' + data.data + '</tbody></table></div></div></div>',
                    btn: ['关联选中', '关闭页面'],
                    yes: function () {
                        setRelated(tid);
                    },
                    success: function () {
                        setSelectVal();
                    }
                });
            } else {
                layer.alert(data.msg);
            }
        },
        error: function (data) {
            layer.close(ii);
            layer.msg('服务器错误');
            return false;
        }
    });
}

function tools(cid) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: "GET",
        url: "?act=tools",
        data: {
            cid: cid
        },
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                $('#toollist').html(data.data);
            } else {
                layer.alert(data.msg);
            }
        }
    });
}

function setRelated(tid) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: '?act=setRelated',
        data: 'tid=' + tid + '&list=' + relatedList,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg(data.msg);
                setTimeout(function () {
                    listTable();
                }, 800);
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

function setBatch(tid) {
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=setBatch&tid=' + tid,
        dataType: 'json',
        success: function (data) {
            if (data.code == 0) {
                layer.msg(data.msg);
                listTable();
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

function setSort(tid, sort) {
    $.ajax({
        type: 'GET',
        url: '?act=setSort&tid=' + tid + '&sort=' + sort,
        dataType: 'json',
        success: function (data) {
            if (data.code == 0) {
                layer.msg(data.msg);
                listTable();
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

function setP_cover(tid, p_cover) {
    $.ajax({
        type: 'GET',
        url: '?act=setP_cover&tid=' + tid + '&p_cover=' + p_cover,
        dataType: 'json',
        success: function (data) {
            if (data.code == 0) {
                layer.msg(data.msg);
                if (p_cover == 1) {
                    $("#p_cover_" + tid).removeClass("btn-warning");
                    $("#p_cover_" + tid).addClass("btn-success");
                    $("#p_cover_" + tid).html("自适应加价");
                    $("#p_cover_" + tid).attr("onclick", "setP_cover(" + tid + ",0)");
                } else {
                    $("#p_cover_" + tid).removeClass("btn-success");
                    $("#p_cover_" + tid).addClass("btn-warning");
                    $("#p_cover_" + tid).html("自定义加价");
                    $("#p_cover_" + tid).attr("onclick", "setP_cover(" + tid + ",1)");
                }
            } else {
                layer.alert(data.msg);
            }
        },
        error: function (data) {
            layer.msg('服务器错误');
            return false;
        }
    });
};

function editPrice(tid) {
    var price1 = $("#edit_price1_" + tid).val();
    var name = $("#edit_name_" + tid).val();
    var value = $("#edit_value_" + tid).val();
    var prid = $("#edit_prid_" + tid).val();
    var price_s = $("#price_s").val();
    var cost_s = $("#cost_s").val();
    var cost2_s = $("#cost2_s").val();
    if (parseInt(prid) > 0 && price_s == '' || parseInt(prid) == 0 && price_s == '') {
        layer.alert('商品售价不能为空！');
        return false;
    }
    if (price1 == '') {
        layer.alert('商品成本价不能为空！');
        return false;
    }
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: "POST",
        url: "ajax.php?act=editPrice",
        data: {
            tid: tid,
            price1: price1,
            prid: prid,
            name: name,
            value: value,
            price_s: price_s,
            cost_s: cost_s,
            cost2_s: cost2_s
        },
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg('保存成功！');
                listTable();
            } else {
                layer.alert(data.msg);
            }
        }
    });
}

function setPrid(tid, prid) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: '?act=setPrid',
        data: {
            tid: tid,
            prid: prid
        },
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg(data.msg);
                setTimeout(function () {
                    listTable();
                }, 1000);
            } else {
                layer.alert(data.msg);
            }
        },
        error: function (data) {
            layer.msg('服务器错误');
            return false;
        }
    });
};

function searchItem() {
    var kw = $("input[name='kw']").val();
    var cid = $("[name='toolcid']").val();
    var active = $("[name='active']").val();
    var is_curl = $("[name='is_curl']").val();
    var close = $("[name='close']").val();
    $("[name='is_curl']").attr('default', is_curl);
    $("[name='toolcid']").attr('default', cid);
    $("[name='active']").attr('default', active);
    $("[name='close']").attr('default', close);
    listTable('kw=' + kw + '&cid=' + cid + '&active=' + active + '&is_curl=' + is_curl + '&close=' + close);
    return false;
}

// 监听自动搜索
$(document).ready(function () {
    $(document).on('change', "input[name='kw']", function () {
        searchItem();
    });
    $(document).on('change', "[name='toolcid']", function () {
        searchItem();
    });
    $(document).on('change', "[name='active']", function () {
        searchItem();
    });
    $(document).on('change', "[name='is_curl']", function () {
        searchItem();
    });
    $(document).on('change', "[name='close']", function () {
        searchItem();
    });

    $(document).on('click', "#add", function () {
        var cid = parseInt($("[name=toolcid]").attr('default'));
        if (cid < 1 || isNaN(cid)) {
            cid = 0;
        }
        window.location.href = './shopedit.php?my=add&cid=' + cid;
    });

    $(document).on('click', ".editSort", function () {
        var tid = $(this).data('tid');
        var find = $("tr[tid=" + tid + "]").find('input[name=sort]');

        if (!find) {
            return;
        }
        var sort = find.val();
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=goods_sort",
            data: {
                tid: tid,
                sort: sort,
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg('修改排序成功！', {
                        end: function () {listTable();},
                        time: 800
                    });

                } else {
                    layer.alert(data.msg);
                }
            }
        });
    });

});