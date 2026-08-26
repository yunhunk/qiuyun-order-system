"use strict";
var checkboxlist = '',
    modelList = '',
    pridList = '',
    idlist = '',
    classListSelect = '',
    pageLoad = true;

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


function readPwd(el) {
    if ($(el).length > 0) {
        var pwd = $(el).attr('pwd');
        layer.alert('用户密码：' + pwd);
    } else {
        layer.alert('页面JS脚本异常，请提交工单反馈！');
    }
}

function showRecharge(zid) {
    $("input[name='zid']").val(zid);
    $('#modal-money').modal('show');
}

function setPriced(zid, is_priced) {
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=setPriced&zid=' + zid + '&is_priced=' + is_priced,
        dataType: 'json',
        success: function (data) {
            layer.msg('切换成功');
            window.location.reload();
        },
        error: function (data) {
            layer.msg('服务器错误');
            return false;
        }
    });
}

function selectMid(mid) {
    var items = $("input[name='superPirceId']");
    var isCheck = false;
    for (i = 0; i < items.length; i++) {
        if ($(items[i]).val() != mid && items[i].checked == true) {
            $(items[i]).click();
        }
        if ($(items[i]).val() == mid && items[i].checked == true) {
            isCheck = true;
        }
    }
    if (isCheck) {
        $("input[name='m-mid']").val(mid);
    } else {
        $("input[name='m-mid']").val('0');
    }
}

function setIpirce() {
    var zid = $('input[name=\'m-zid\']').val();
    window.location.href = './superPrice.php?zid=' + zid;
}

function showSuperList(zid, mid) {
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=superPriceList',
        dataType: 'json',
        success: function (data) {
            if (data.code == 0) {
                $('#modal-superPrice-title').html('密价设置（ZID：' + zid + '）');
                $('#superPriceList').html(data.list);
                $('input[name=\'m-mid\']').val(mid);
                $('input[name=\'m-zid\']').val(zid);
                if (mid > 0) $('#mid' + mid).click();
                $('#modal-superPrice').modal('show');
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

function setSuperPrice() {
    var mid = $("input[name='m-mid']").val();
    var zid = $("input[name='m-zid']").val();
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: "POST",
        url: "ajax.php?act=setSuperPrice",
        dataType: 'json',
        data: {
            mid: mid,
            zid: zid
        },
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg(data.msg, {
                    end: function () {
                        window.location.reload();
                    }
                });
            } else {
                layer.alert(data.msg);
            }
        },
        error: function (ret) {
            layer.close(ii);
            layer.alert("服务器请求超时，请稍后再试！" + ret);
        }
    });
}

function setActive(zid, active) {
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=setSite&zid=' + zid + '&active=' + active,
        dataType: 'json',
        success: function (data) {
            layer.msg('切换成功');
            window.location.reload();
        },
        error: function (data) {
            layer.msg('服务器错误');
            return false;
        }
    });
}

function setSuper(zid) {
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=setSuper&zid=' + zid,
        dataType: 'json',
        success: function (data) {
            layer.msg('切换成功');
            window.location.reload();
        },
        error: function (data) {
            layer.msg('服务器错误');
            return false;
        }
    });
}

function listTable() {
    setTimeout(function () {
        window.location.reload();
    }, 100)
}

function setEndtime(zid) {
    layer.prompt({
        title: '需要延时多少个月',
        value: '12',
        formType: 0
    }, function (text, index) {
        $.ajax({
            type: 'POST',
            url: 'ajax.php?act=setEndtime',
            data: {
                zid: zid,
                month: text
            },
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
    });
}

function rePrice(zid) {
    layer.alert('是否重置该站商品设定价格?', {
        btn: ['确定', '取消'],
        yes: function () {
            $.ajax({
                type: 'GET',
                url: 'ajax.php?act=rePrice&zid=' + zid,
                dataType: 'json',
                success: function (data) {
                    layer.msg('操作成功！');
                    window.location.reload();
                },
                error: function (data) {
                    layer.layer('服务器错误');
                    return false;
                }
            });
        }
    });
}

function setRegular(zid) {
    layer.alert('是否切换该站站点余额固定状态?<br>提示：固定后该站每次下单后余额将会重置为0！', {
        btn: ['确定', '取消'],
        yes: function () {
            $.ajax({
                type: 'GET',
                url: 'ajax.php?act=setRegular&zid=' + zid,
                dataType: 'json',
                success: function (data) {
                    layer.msg('操作成功！');
                    window.location.reload();
                },
                error: function (data) {
                    layer.layer('服务器错误');
                    return false;
                }
            });
        }
    });
}

function setAppUrl(zid, AppUrl) {
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=setAppUrl&zid=' + zid + '&AppUrl=' + AppUrl,
        dataType: 'json',
        success: function (data) {
            if (data.code == 0) {
                layer.msg('保存成功！');
                window.location.reload();
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

function selectRender() {
    var items = $("select[default]");
    for (var i = 0; i < items.length; i++) {
        $(items[i]).val($(items[i]).attr("default") || 0);
    }
}

function operation(aid) {
    var area = [$(window).width() > 640 ? '540px' : '95%', 'auto'];

    if (aid == 2 || aid == 3) {
        // 批量替换分站域名(选中分站)
        var laybox = layer.open({
            type: 1,
            area: area,
            title: aid == 2 ? '批量替换分站域名(选中分站)' : '批量替换分站域名(全部分站)',
            content: '<div class="panel-body"><div class="form-group"><div class="input-group"><div class="input-group-addon">被替换域名关键字</div><input id="rep0" class="form-control"/></div></div><div class="form-group"><div class="input-group"><div class="input-group-addon">替换后域名内容</div><input id="rep1" class="form-control"/></div></div></div>',
            btn: ['提交操作', '取消操作'],
            yes: function (index, layero) {
                var rep0 = $("#rep0").val();
                var rep1 = $("#rep1").val();
                var ii = layer.load(2, {
                    shade: [0.1, '#fff']
                });
                $.ajax({
                    type: 'POST',
                    url: 'ajax.php?act=site_operation',
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
                            layer.alert(data.msg, {
                                yes: function () {
                                    listTable();
                                }
                            });
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
    else if (aid == 1) {
        // 批量替换分站域名(选中分站)
        var laybox = layer.open({
            type: 1,
            area: area,
            title: '批量替换分站名称(所有分站)',
            content: '<div class="panel-body"><div class="form-group"><div class="input-group"><div class="input-group-addon">被替换名称关键字</div><input id="rep0" class="form-control"/></div></div><div class="form-group"><div class="input-group"><div class="input-group-addon">替换后名称内容</div><input id="rep1" class="form-control"/></div></div></div>',
            btn: ['提交操作', '取消操作'],
            yes: function (index, layero) {
                var rep0 = $("#rep0").val();
                var rep1 = $("#rep1").val();
                var ii = layer.load(2, {
                    shade: [0.1, '#fff']
                });
                $.ajax({
                    type: 'POST',
                    url: 'ajax.php?act=site_operation',
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
                            layer.alert(data.msg, {
                                yes: function () {
                                    listTable();
                                }
                            });
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
        url: 'ajax.php?act=site_operation',
        data: {
            checkbox: checkboxlist,
            aid: aid,
        },
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.alert(data.msg, {
                    yes: function () {
                        listTable();
                    }
                });
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

function tips() {
    layer.tips('请输入分站ID、分站地址、分站用户名、分站QQ等', '#kw', {
        tips: 1,
        time: 8000
    });
}

$(document).ready(function () {
    $(document).on('click', '#recharge', function (event) {
        event.preventDefault();
        /* Act on the event */
        var zid = $("input[name='zid']").val();
        var actdo = $("select[name='do']").val();
        var action = $("#action option:selected").val();
        var money = $("input[name='money']").val();
        var bz = $("input[name='bz']").val();
        if (money == '') {
            layer.alert('请输入金额');
            return false;
        }
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "ajax.php?act=siteRecharge",
            data: {
                zid: zid,
                actdo: actdo,
                action: action,
                money: money,
                bz: bz
            },
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg('修改余额成功');
                    window.location.reload();
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

    selectRender();
});