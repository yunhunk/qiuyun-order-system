function listTable(query) {
    var url = window.document.location.href.toString();
    var queryString = url.split("?")[1];
    query = query || queryString;
    layer.closeAll();
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'GET',
        url: 'classlist-table.php?' + query,
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

function check1() {
    var checkedList = document.getElementsByName('checkedList[]');
    for (var i = 0; i < checkedList.length; i++) {
        if (checkedList[i].checked == true) {
            checkedList[i].checked = false;
        } else {
            checkedList[i].checked = true;
        }
    }
}

function setActive(cid, active) {
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=setClass&cid=' + cid + '&active=' + active,
        dataType: 'json',
        success: function(data) {
            listTable();
        },
        error: function(data) {
            layer.msg('服务器错误');
            return false;
        }
    });
}

function sort(cid, sort) {
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=setClassSort&cid=' + cid + '&sort=' + sort,
        dataType: 'json',
        success: function(data) {
            listTable();
        },
        error: function(data) {
            layer.msg('服务器错误');
            return false;
        }
    });
}

function getImage(cid) {
    layer.confirm('是否从该分类下的商品图片获取一张作为分类图片？', {
        btn: ['确定', '取消'] //按钮
    }, function() {
        $.ajax({
            type: 'GET',
            url: 'ajax.php?act=getClassImage&cid=' + cid,
            dataType: 'json',
            success: function(data) {
                if (data.code == 0) {
                    layer.msg('获取图片成功');
                    $("input[name='img" + cid + "']").val(data.url);
                } else {
                    layer.alert('该分类下商品都没有图片');
                }
            },
            error: function(data) {
                layer.msg('服务器错误');
                return false;
            }
        });
    });
}

function addClass() {
    var type = $("input[name='type']").val();
    var name = $("input[name='name']").val();
    var upcid = 0;
    if ($("input[name='upcid']").length > 0) {
        upcid = $("input[name='upcid']").val();
    }
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=addClass',
        data: {
            name: name,
            type: type,
            upcid: upcid
        },
        dataType: 'json',
        success: function(data) {
            if (data.code == 0) {
                layer.msg('添加成功');
                listTable();
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

function operation() {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=class_change',
        data: $('form#classlist').serialize(),
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
            layer.msg('操作请求超时，请刷新页面后再试！');
            listTable();
        }
    });
    return false;
}

function editClass(cid) {
    if ($("#name" + cid).length > 0) {
        var name = $("#name" + cid).val();
    } else {
        var name = $("input[name='classNameList[" + cid + "]']").val();
    }
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=editClass&cid=' + cid,
        data: {
            name: name
        },
        dataType: 'json',
        success: function(data) {
            if (data.code == 0) {
                layer.msg('修改成功');
                listTable();
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

function delClass(cid) {
    var confirmobj = layer.confirm('你确实要删除此分类和分类下全部商品吗？', {
        btn: ['确定', '取消']
    }, function() {
        $.ajax({
            type: 'GET',
            url: 'ajax.php?act=delClass&cid=' + cid,
            dataType: 'json',
            success: function(data) {
                if (data.code == 0) {
                    layer.msg('删除成功');
                    listTable();
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function(data) {
                layer.msg('服务器错误');
                return false;
            }
        });
    }, function() {
        layer.close(confirmobj);
    });
}

function saveAll() {
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=editClassAll',
        data: $('#classlist').serialize(),
        dataType: 'json',
        success: function(data) {
            alert('保存成功！');
            listTable();
        },
        error: function(data) {
            layer.msg('服务器错误');
            return false;
        }
    });
}

function saveAllImages() {
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=editClassImages',
        data: $('#classlist').serialize(),
        dataType: 'json',
        success: function(data) {
            alert('保存成功！');
            window.location.reload();
        },
        error: function(data) {
            layer.msg('服务器错误');
            return false;
        }
    });
}

function fileSelect(cid) {
    $("#file" + cid).trigger("click");
}

function fileView(cid) {
    var shopimg = $("input[name='img" + cid + "']").val();
    if (shopimg == '') {
        layer.alert("请先上传图片，才能预览");
        return;
    }
    if (shopimg.indexOf('http') == -1) shopimg = '../' + shopimg;
    layer.open({
        type: 1,
        area: ['360px', '400px'],
        title: '分类图片查看',
        shade: 0.3,
        anim: 1,
        shadeClose: true,
        content: '<center><img width="300px" src="' + shopimg + '"></center>'
    });
}

function fileUpload(cid) {
    var fileObj = $("#file" + cid)[0].files[0];
    if (typeof(fileObj) == "undefined" || fileObj.size <= 0) {
        return;
    }
    var formData = new FormData();
    formData.append("do", "upload");
    formData.append("type", "class");
    formData.append("file", fileObj);
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
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg('上传图片成功');
                $("input[name='img" + cid + "']").val(data.url);
            } else {
                layer.alert(data.msg);
            }
        },
        error: function(data) {
            layer.msg('服务器错误');
            return false;
        }
    })
}

function setHidePays(cid) {
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=getHidePays',
        data: {
            cid: cid
        },
        dataType: 'json',
        success: function(data) {
            if (data.code == 0) {
                layer.open({
                    area: ['360px'],
                    title: '设置此分类商品禁用支付方式',
                    content: '<div class="form-group"><input class="inp-cmckb-xs" id="qqpay' + cid + '" type="checkbox" ' + ($.inArray("qqpay", data.data) > -1 ? 'checked value="1"' : ' value="0"') + ' style="display: none;"/><label class="cmckb-xs" for="qqpay' + cid + '"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>禁用QQ钱包</span></label><br><input class="inp-cmckb-xs" id="alipay' + cid + '" type="checkbox" ' + ($.inArray("alipay", data.data) > -1 ? 'checked value="1"' : ' value="0"') + ' style="display: none;"/><label class="cmckb-xs" for="alipay' + cid + '"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>禁用支付宝</span></label><br><input class="inp-cmckb-xs" id="wxpay' + cid + '" type="checkbox" ' + ($.inArray("wxpay", data.data) > -1 ? 'checked value="1"' : ' value="0"') + ' style="display: none;"/><label class="cmckb-xs" for="wxpay' + cid + '"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>禁用微信支付</span></label><br><input class="inp-cmckb-xs" id="rmb' + cid + '" type="checkbox" ' + ($.inArray("rmb", data.data) > -1 ? 'checked value="1"' : ' value="0"') + ' style="display: none;"/><label class="cmckb-xs" for="rmb' + cid + '"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>禁用余额支付</span></label><!--SVG Sprites--><svg class="inline-svg"><symbol id="checkSvg" viewbox="0 0 12 10"><polyline points="1.5 6 4.5 9 10.5 1"></polyline> </symbol></svg><br/><span style="color:red">注意：该设置仅对当前分类有效，不会对子分类有效</span></div>',
                    btn: ["确定", "取消"],
                    yes: function() {
                        var paytype = [];
                        if ($("#qqpay" + cid)[0].checked == true) paytype.push("qqpay");
                        if ($("#alipay" + cid)[0].checked == true) paytype.push("alipay");
                        if ($("#wxpay" + cid)[0].checked == true) paytype.push("wxpay");
                        if ($("#rmb" + cid)[0].checked == true) paytype.push("rmb");
                        $.ajax({
                            type: 'POST',
                            url: 'ajax.php?act=setHidePays',
                            data: {
                                cid: cid,
                                paytype: paytype
                            },
                            dataType: 'json',
                            success: function(data) {
                                if (data.code == 0) {
                                    layer.msg(data.msg, {
                                        icon: 1
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

function setLoginShow(cid) {
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=setLoginShow',
        data: {
            cid: cid
        },
        dataType: 'json',
        success: function(data) {
            if (data.code == 0) {
                layer.msg(data.msg, {
                    icon: 1
                });
                listTable();
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

function setBlockCitys(cid) {
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=getBlockCitys',
        data: {
            cid: cid
        },
        dataType: 'json',
        success: function(data) {
            if (data.code == 0) {
                if (!data.content) data.content = "";
                layer.open({
                    area: ['360px'],
                    title: '设置禁售地区(多个城市用,分隔)',
                    content: '<div class="form-group"><textarea class="form-control" name="blockcity" placeholder="示例：北京市,广东省深圳市" rows="5">' + data.content + '</textarea><small>如设置某地区无效果，请直接填城市名称，如四川省成都市，可只填成都提高准确率</small></div>',
                    btn: ["确定", "取消"],
                    yes: function() {
                        var content = $("textarea[name='blockcity']").val();
                        $.ajax({
                            type: 'POST',
                            url: 'ajax.php?act=setBlockCitys',
                            data: {
                                cid: cid,
                                blockcity: content
                            },
                            dataType: 'json',
                            success: function(data) {
                                if (data.code == 0) {
                                    layer.msg(data.msg, {
                                        icon: 1
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
$(document).ready(function() {
    listTable();
})