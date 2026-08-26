"use strict";
var checkList = [] || new Array(),
    modelList = '',
    pageLoad = true;

function getModelList() {
    if ("" != modelList) {
        return modelList;
    } else {
        $.ajax({
            type: 'POST',
            url: './ajax.php?act=getModelList',
            dataType: 'html',
            success: function (data) {
                modelList = data;
            },
            error: function (data) {
                //layer.msg('获取快捷回复列表请求超时，请稍后再试！');
            }
        });
        return modelList;
    }
}

function check1(field) {
    var checkbox = field || document.getElementsByName('checkbox[]');
    for (var i = 0; i < checkbox.length; i++) {
        if (checkbox[i].checked === false) {
            checkbox[i].checked = true;
        } else {
            checkbox[i].checked = false;
        }
    }
}

function getVals() {
    var checkbox = document.getElementsByName('checkbox[]');
    checkList = [];
    for (var i = 0; i < checkbox.length; i++) {
        if (checkbox[i].checked) {
            checkList.push(checkbox[i].value);
        }
    }
    console.log(checkList);
}

function listTable(query) {
    var url = window.document.location.href.toString();
    var queryString = url.split("?")[1];
    query = query || queryString;
    if (query == 'start' || query == undefined) {
        query = '';
        history.replaceState({}, null, './list.php');
    } else if (query != undefined) {
        history.replaceState({}, null, './list.php?' + query);
    }
    layer.closeAll();
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'GET',
        url: 'list-table.php?' + query,
        dataType: 'html',
        cache: false,
        success: function (data) {
            layer.close(ii);
            $("#listTable").html(data)
        },
        error: function (data) {
            layer.msg('服务器错误');
            return false;
        }
    });
}

function searchOrder() {
    var kw = $("input[name='kw']").val();
    var type = $("select[name='type']").val();
    var status = $("select[name='status']").val();
    listTable('kw=' + kw + '&type=' + type + '&status=' + status);
    return false;
}

function orderFail(status, is_djorder) {
    getVals();
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=operation',
        data: {
            status: status,
            checkbox: checkList,
            is_djorder: is_djorder
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
            layer.close(ii);
            layer.msg('请求超时');
            listTable();
        }
    });
}

function operation() {
    var status = $("select[name='checkStatus']").val();
    var result_all = $("#result_all").val();
    if (result_all == "" && status == 7) {
        var area = [$(window).width() > 640 ? '480px' : '95%', 'auto'];
        var laybox = layer.alert('<div class="form-group"><div class="input-group"><div class="input-group-addon">处理信息</div><textarea class="result" type="text" cols="25" rows="6" id="result_val" placeholder="批量处理信息，可留空"></textarea></div></div><div class="form-group"><label>快捷模板:</label> <div class="form-group">' + modelList + '</div></div>', {
            title: '批量写处理订单状态和修改信息',
            area: area,
            btn: ["确定批量", "取消操作"],
            yes: function (index, layero) {
                var text = $("#result_val").val();
                if (text == "") {
                    text = "空";
                }
                $("#result_all").val(text);
                operation();
            },
            btn2: function () {
                layer.close(laybox);
            }
        });
        return false;
    }
    if (status == 5) {
        layer.open({
            title: "温馨提示",
            content: "如果订单中有已对接过的订单是否强制补单？",
            btn: ["正常补单", "强制补单", "取消补单"],
            yes: function () {
                orderFail(status, "0");
            },
            btn2: function () {
                orderFail(status, "1");
            },
            btn3: function (index) {
                layer.close(index);
            }
        });
        return false;
    }
    if (result_all == "空") {
        result_all = "";
    }
    getVals();
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=operation',
        data: {
            status: status,
            checkbox: checkList,
            result_all: result_all
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
            layer.close(ii);
            layer.msg('请求超时');
            listTable();
        }
    });
    return false;
}

function showStatus(id) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=showStatus&id=' + id,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                var item = data.data;
                var area = [$(window).width() > 640 ? '480px' : '95%', 'auto'];
                layer.open({
                    type: 1,
                    title: '订单进度查询',
                    area: area,
                    skin: 'layui-layer-rim',
                    content: '以下数据来自' + data.domain + '<br/><table class="table"><tr><td class="warning">订单ID</td><td>' + item.orderid + '</td><td class="warning">订单状态</td><td><font color=blue>' + item.order_state + '</font></td></tr><tr><td class="warning">下单数量</td><td>' + item.num + '</td><td class="warning">下单时间</td><td>' + item.add_time + '</td></tr><tr><td class="warning">初始数量</td><td>' + item.start_num + '</td><td class="warning">当前数量</td><td>' + item.now_num + '</td></tr></table>'
                    //<tr><td colspan="6"><a target="_blank" class="btn btn-success btn-block" href="' + item.shopUrl + '">到社区商品</a></td></tr>
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

function djOrder(id) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=djOrder&id=' + id,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg(data.msg);
                listTable();
            } else {
                layer.alert(data.msg, {
                    area: [$(window).width() > 640 ? '480px' : '95%', 'auto']
                });
            }
        },
        error: function (data) {
            layer.msg('服务器错误');
            return false;
        }
    });
}

function reShop(id) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=reShop&id=' + id,
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

function showOrder(id) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=order&id=' + id,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                var area = [$(window).width() > 640 ? '480px' : '95%', 'auto'];
                layer.open({
                    type: 1,
                    area: area,
                    title: '订单详情',
                    skin: 'layui-layer-rim',
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
}

function inputOrder(id) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    var area = [$(window).width() > 640 ? '480px' : '95%', 'auto'];
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=order2&id=' + id,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.open({
                    type: 1,
                    area: area,
                    title: '修改数据',
                    skin: 'layui-layer-rim',
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
}

function inputNum(id) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=order3&id=' + id,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                var area = [$(window).width() > 640 ? '480px' : '95%', 'auto'];
                layer.open({
                    type: 1,
                    area: area,
                    title: '修改份数',
                    skin: 'layui-layer-rim',
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
}

function refund(id) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=getmoney',
        data: {
            id: id
        },
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                var area = [$(window).width() > 640 ? '480px' : '95%', 'auto'];
                layer.prompt({
                    area: area,
                    title: '填写退款金额',
                    value: data.money,
                    formType: 0
                }, function (text, index) {
                    var ii = layer.load(2, {
                        shade: [0.1, '#fff']
                    });
                    $.ajax({
                        type: 'POST',
                        url: 'ajax.php?act=refund',
                        data: {
                            id: id,
                            money: text
                        },
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
                            layer.msg('服务器错误');
                            return false;
                        }
                    });
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

function setResult(id, title) {
    var title = title || '异常原因';
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=result',
        data: {
            id: id
        },
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.prompt({
                    title: '填写' + title,
                    value: data.result,
                    formType: 2
                }, function (text, index) {
                    var ii = layer.load(2, {
                        shade: [0.1, '#fff']
                    });
                    $.ajax({
                        type: 'POST',
                        url: 'ajax.php?act=setresult',
                        data: {
                            id: id,
                            result: text
                        },
                        dataType: 'json',
                        success: function (data) {
                            layer.close(ii);
                            if (data.code == 0) {
                                layer.msg('填写' + title + '成功');
                            } else {
                                layer.alert(data.msg);
                            }
                        },
                        error: function (data) {
                            layer.msg('服务器错误');
                            return false;
                        }
                    });
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

function saveOrder(id) {
    var inputvalue = $("#inputvalue").val();
    if (inputvalue == "" || $("#inputvalue2").val() == "" || $("#inputvalue3").val() == "" || $("#inputvalue4").val() == "" || $("#inputvalue5").val() == "") {
        layer.alert("请确保每项不能为空！");
        return false;
    }
    if ($("#inputname").html() == "下单ＱＱ" && (inputvalue.length < 5 || inputvalue.length > 11)) {
        layer.alert("请输入正确的QQ号！");
        return false;
    }
    $("#save").val("Loading");
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: "POST",
        url: "ajax.php?act=editOrder",
        data: {
            id: id,
            inputvalue: inputvalue,
            inputvalue2: $("#inputvalue2").val(),
            inputvalue3: $("#inputvalue3").val(),
            inputvalue4: $("#inputvalue4").val(),
            inputvalue5: $("#inputvalue5").val(),
            value: $("#value").val(),
            djorder: $("#djorder").val(),
            exporder: $("#exporder").val(),
            inputattr: $("#inputattr").length > 0 ? $("#inputattr").val() : ""
        },
        dataType: "json",
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg('保存成功！');
                setTimeout(function () {
                    listTable();
                }, 1000)
            } else {
                layer.alert(data.msg);
            }
            $('#save').val('保存');
        }
    });
}

function saveOrderNum(id) {
    var num = $("#num").val();
    if (num == "") {
        layer.alert("请确保每项不能为空！");
        return false;
    }
    $("#save").val("Loading");
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "ajax.php?act=editOrderNum",
        data: {
            id: id,
            num: num
        },
        dataType: "json",
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg("保存成功！");
                listTable();
            } else {
                layer.alert(data.msg);
            }
            $("#save").val("保存");
        }
    });
}

function get_tkbz(id, status) {
    var ii = layer.load(2);
    $.ajax({
        type: "GET",
        url: "ajax.php?act=orderInfo",
        data: {
            id: id,
        },
        dataType: "json",
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                var area = [$(window).width() > 640 ? '480px' : '95%', 'auto'];
                var addstr = '';
                if (data.row['zid'] > 1) {
                    addstr = '<div class="form-group"><div class="input-group"><div class="input-group-addon">退款到账方式</div><select class="form-control" id="refund_fenzhan_type" default="1"><option value="1">退到分站' + data.row['zid'] + '的余额（分站不可提现）</option><option value="0">退到分站' + data.row['zid'] + '的提成（分站可提现）</option><option value="-1" selected>我手动退款给客户，同时扣除分站的提成</option></select></div><small style="color:red">注意，如果扣除分站相应提成时提成余额不足，将自动扣除余额！如果分站余额也不足时操作会把分站余额变成负数</small></div>';
                }
                var options = {
                    title: '订单退款操作',
                    area: area,
                    skin: 'layui-layer-rim',
                    content: '<div class="panel-body"><div class="form-group"><div class="input-group"><div class="input-group-addon">退款原因填写</div><textarea type="text" cols="25" rows="6" class="form-control result"></textarea></div></div>' + addstr + '<div class="form-group"><div class="input-group"><div class="input-group-addon">退款金额选择</div><select class="form-control" id="refund_type" onchange="setShow(this.value)"><option value="0">0_全额退款</option><option value="1" >1_按天退款</option><option value="2" >2_按数量退款</option><option value="3" >3_按比例退款</option></select></div></div><div class="form-group" id="zbts" style="display:none"><div class="input-group"><div class="input-group-addon" id="zb_title">质保天数</div><input type="text" name="num" id="num" value="25" class="form-control"/></div></div><div class="form-group"><label>快捷模板:</label> <div class="form-group">' + modelList + '</div></div></div>',
                    btn: ['确认提交', '取消操作'],
                    success: function () {
                        var items = $("select[default]");
                        for (var i = 0; i < items.length; i++) {
                            $(items[i]).val($(items[i]).attr("default") || 0);
                        }
                    },
                    yes: function () {
                        var ii = layer.load(2, {
                            shade: [0.1, '#fff']
                        });
                        var tkbz = $(".result").val();
                        var refund_type = $("#refund_type").val();
                        var num = $("#num").val() ? $("#num").val() : 25;
                        var postData = {
                            id: id,
                            num: num,
                            status: status,
                            tkbz: tkbz,
                            refund_type: refund_type,
                            refund_fenzhan_type: refund_fenzhan_type,
                        };
                        if ($("#refund_fenzhan_type").length > 0) {
                            postData = Object.assign(postData, {
                                refund_fenzhan_type: $("#refund_fenzhan_type option:selected").val()
                            });
                        }
                        $.ajax({
                            type: 'POST',
                            url: './ajax.php?act=setResult',
                            data: postData,
                            dataType: 'json',
                            success: function (ret) {
                                layer.close(ii);
                                if (ret['code'] != 200) {
                                    layer.open({
                                        title: '操作结果',
                                        content: ret['msg'] ? ret['msg'] : '操作失败',
                                        btn: ['我知道了'],
                                        yes: function () {
                                            window.location.reload();
                                        }
                                    });
                                }
                            },
                            error: function (data) {
                                layer.close(ii);
                                alert('服务器错误');
                                return false;
                            }
                        });
                    },
                    btn2: function () {
                        layer.closeAll();
                        listTable();
                        return false;
                    }
                };
                layer.open(options);
            } else {
                layer.alert(data.msg);
            }
        }
    });
}

function setShow(value) {
    if (value == 1) {
        $("#zbts").css('display', 'none');
        $("#zb_title").html('质保天数');
        $("#num").val('25');
        $("#zbts").slideDown();
    } else if (value == 2) {
        $("#zbts").css('display', 'none');
        $("#zb_title").html('已到账数量');
        $("#num").val('');
        $("#zbts").slideDown();
    } else if (value == 3) {
        $("#zbts").css('display', 'none');
        $("#zb_title").html('退款百分比');
        $("#num").val('10');
        $("#zbts").slideDown();
    } else {
        $("#zbts").slideUp();
    }
}

function escape2Html(str) {
    var arrEntities = {
        "lt": "<",
        "gt": ">",
        "nbsp": " ",
        "amp": "&",
        "quot": '"'
    };
    return str.replace(/&(lt|gt|nbsp|amp|quot);/ig, function (all, t) {
        return arrEntities[t];
    });
}

function setReply(id, obj) {
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
                $('.result').val(escape2Html(data.reply));
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

function setInfo(id, status) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    var area = [$(window).width() > 640 ? '480px' : '95%', 'auto'];
    $.ajax({
        type: "POST",
        url: "ajax.php?act=orderInfo&id=" + id,
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                if (status == 9) {
                    var titlename = '填写待退款原因';
                } else if (status == 3) {
                    var titlename = '填写异常原因';
                } else {
                    var titlename = '修改订单处理信息';
                }
                if (data.row.result == null) data.row.result = "";
                layer.open({
                    title: titlename,
                    area: area,
                    skin: 'layui-layer-rim',
                    content: '<div class="form-group"><div class="input-group"><div class="input-group-addon">订单处理信息</div><textarea type="text" cols="25" rows="6" id="result_' + id + '" class="form-control">' + data.row.result + '</textarea></div></div><div class="form-group"><label>快捷模板:</label> <div class="form-group">' + modelList + '</div></div>',
                    btn: ['确认提交', '取消操作'],
                    yes: function () {
                        var ii = layer.load(2, {
                            shade: [0.1, '#fff']
                        });
                        var result = $("#result_" + id).val();
                        $("#info" + id).val(result);
                        $.ajax({
                            type: "POST",
                            url: "ajax.php?act=setResult",
                            data: "id=" + id + "&result=" + result + "&status=" + status,
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
                                layer.msg('服务器错误');
                                return false;
                            }
                        });
                    }
                });
            } else {
                layer.alert(data.msg);
            }
        },
        error: function (data) {
            layer.close(ii);
            layer.alert('服务器错误，请稍后重试');
            return false;
        }
    });
}

function setStatus(id, status) {
    if (status == 9) {
        get_tkbz(id, status);
        return false;
    } else if (status == 3 || status == 10 || status == 1) {
        setInfo(id, status);
        return false;
    } else if (status == 6) {
        refund(id);
        return false;
    } else {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=setResult",
            data: "id=" + id + "&status=" + status,
            dataType: 'json',
            success: function (ret) {
                layer.close(ii);
                if (ret['code'] != 0) {
                    layer.alert(ret['msg'] ? ret['msg'] : '操作失败');
                } else {
                    listTable();
                }
            },
            error: function (data) {
                alert('服务器错误');
                return false;
            }
        });
    }
}

function tips() {
    layer.tips('支持下单数据、商品名称、订单号等模糊搜索！如：爱奇艺', '#search', {
        tips: 1,
        time: 8000
    });
}
$(document).ready(function () {
    listTable();
    setTimeout(function () {
        getModelList()
    }, 1000);
})