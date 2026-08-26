"use strict";
$(document).ready(function() {
    listTable();
    setTimeout(function(argument) {
        getModelList();
    }, 1500);
})
var checkList = [] || new Array();
var modelList = '';

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

function getVals(strType = false) {
    var checkbox = document.getElementsByName('checkbox[]');
    var str = "";
    checkList = [];
    for (var i = 0; i < checkbox.length; i++) {
        if (checkbox[i].checked) {
            if (strType) {
                if (str == "") {
                    str = checkbox[i].value
                } else {
                    str = str + "|" + checkbox[i].value;
                }
            } else {
                checkList.push(checkbox[i].value);
            }
        }
    }
    if (strType) {
        return str;
    }
}

function getModelList() {
    if ("" != modelList) {
        return modelList;
    } else {
        $.ajax({
            type: 'POST',
            url: 'ajax.php?act=getModelList',
            data: {},
            dataType: 'html',
            success: function(data) {
                modelList = data;
            },
            error: function(data) {
                layer.msg('获取快捷回复列表请求超时，请稍后再试！');
            }
        });
        return modelList;
    }
}

function change() {
    if ($("select[name='status']").val() == 2 && $("input[name='content']").val() == '') {
        var laybox = layer.alert('<div class="form-group"><div class="input-group"><div class="input-group-addon">回复内容</div><textarea type="text" cols="25" rows="6" id="gongdanval" name="content"></textarea></div></div><div class="form-group"><label>快捷模板:</label><div class="form-group">' + modelList + '</div></div>', {
            btn: ["确定回复", "取消操作"],
            yes: function(index, layero) {
                var text = $("#gongdanval").val();
                $("input[name='content']").val(text);
                $("select[name='status']").val(2);
                change();
            },
            btn2: function() {
                layer.close(laybox);
            }
        });
        return false;
    }
    var content = $("input[name='content']").val();
    var status = $("select[name='status']").val();
    var checkbox = getVals(true);
    if (checkbox == "") {
        return layer.alert("你未勾选任何工单");
    }
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=workorder_change',
        data: {
            checkbox: checkbox,
            status: status,
            content: content
        },
        dataType: 'json',
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                listTable();
                layer.alert(data.msg);
            } else {
                layer.alert(data.msg);
            }
        },
        error: function(data) {
            layer.close(ii);
            layer.msg('请求超时，请稍后再试！');
            listTable();
        }
    });
    return false;
}

function escape2Html(str) {
    var arrEntities = {
        "lt": "<",
        "gt": ">",
        "nbsp": " ",
        "amp": "&",
        "quot": '"'
    };
    return str.replace(/&(lt|gt|nbsp|amp|quot);/ig, function(all, t) {
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
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                $("#gongdanval").val(escape2Html(data.reply));
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

function listTable(query) {
    var url = window.document.location.href.toString();
    var queryString = url.split("?")[1];
    query = query || queryString;
    if (query == 'start' || query == undefined) {
        query = '';
        history.replaceState({}, null, './workorder.php');
    } else if (query != undefined) {
        history.replaceState({}, null, './workorder.php?' + query);
    }
    layer.closeAll();
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'GET',
        url: 'workorder-table.php?' + query,
        dataType: 'html',
        cache: false,
        success: function(data) {
            layer.close(ii);
            $("#listTable").html(data)
        },
        error: function(data) {
            layer.msg('服务器错误');
            return false;
        }
    });
}

function orderItem(id) {
    layer.closeAll();
    var title = 'ID:' + id + ' 工单详情';
    var url = './workorder-item.php?my=view&id=' + id;
    var area = [$(window).width() > 800 ? '800px' : '90%', $(window).height() > 600 ? '600px' : '90%'];
    var options = {
        type: 2,
        title: title,
        shadeClose: true,
        shade: false,
        maxmin: true,
        moveOut: true,
        area: area,
        content: url,
        zIndex: layer.zIndex,
        success: function(layero, index) {
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
        cancel: function() {
            listTable()
        }
    }
    if ($(window).width() < 480 || (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream && top.$("body").size() > 0)) {
        options.area = [top.$("body").width() + "px", top.$("body").height() + "px"];
        options.offset = [top.$("body").scrollTop() + "px", "0px"];
    }
    layer.open(options);
}

function delworkorder(id) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'GET',
        url: '?my=del&id=' + id,
        dataType: 'json',
        cache: false,
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg(data.msg);
                $("#tr_" + id).fadeOut();
            } else {
                layer.alert(data.msg)
            }
        },
        error: function(data) {
            layer.close(ii);
            return layer.msg('服务器错误');
        }
    });
}

function setActive(id, status) {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'GET',
        url: '?my=setActive&id=' + id + '&status=' + status,
        dataType: 'json',
        cache: false,
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg(data.msg);
                listTable();
            } else {
                layer.alert(data.msg)
            }
        },
        error: function(data) {
            layer.close(ii);
            return layer.msg('服务器错误');
        }
    });
}