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
var editObj = null,
    ptable = null,
    treeGridObj = null,
    treeGrid = null,
    checkActionValue = -1,
    upcid = -1,
    tableId = 'treeTable'
var classTooler = {
    add: function(upcid) {
        upcid = upcid || 0;
        var area = [$(window).width() > 640 ? '480px' : '95%', 'auto'];
        layer.open({
            title: upcid > 0 ? '添加[' + upcid + ']的子分类' : '添加分类',
            area: area,
            content: '<div id="add-form">' + $("#add-template").html() + '</div>',
            btnAlign: 'c',
            btn: ['添加', '取消'],
            yes: function(lay, index) {
                var name = $("#add-form #name").val();
                var ii = layer.load(2, {
                    shade: [0.1, '#fff']
                });
                $.ajax({
                    type: 'POST',
                    url: './ajax.class.php?act=class_add',
                    data: {
                        name: name,
                        upcid: upcid
                    },
                    dataType: 'json',
                    success: function(data) {
                        layer.close(ii);
                        if (data.code == 0) {
                            layer.msg('操作成功~');
                            ptable();
                        } else {
                            layer.alert(data.msg);
                        }
                    },
                    error: function(data) {
                        layer.close(ii);
                        layer.msg('服务器错误');
                        return false;
                    }
                });
            }
        })
    },
    edit: function(row) {
        var area = [$(window).width() > 640 ? '480px' : '95%', 'auto'];
        layer.open({
            title: '修改',
            area: area,
            content: '<div id="edit-form">' + $("#edit-template").html() + '</div>',
            btnAlign: 'c',
            btn: ['修改', '取消'],
            success: function() {
                $("#edit-form #name").val(row.name);
            },
            yes: function(lay, index) {
                var name = $("#edit-form #name").val();
                var ii = layer.load(2, {
                    shade: [0.1, '#fff']
                });
                $.ajax({
                    type: 'POST',
                    url: './ajax.class.php?act=class_edit',
                    data: {
                        cid: row.cid,
                        name: name,
                    },
                    dataType: 'json',
                    success: function(data) {
                        layer.close(ii);
                        if (data.code == 0) {
                            layer.msg('操作成功~');
                            ptable();
                        } else {
                            layer.alert(data.msg);
                        }
                    },
                    error: function(data) {
                        layer.close(ii);
                        layer.msg('服务器错误');
                        return false;
                    }
                });
            }
        })
    },
    fileSelect: function(cid) {
        $("#file" + cid).trigger("click");
    },
    fileUpload: function(cid) {
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
            url: "./ajax.php?act=uploadimg",
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
                    $("input.shopimg" + cid).val(data.url);
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function(data) {
                layer.msg('服务器错误');
                return false;
            }
        })
    },
    getImage: function(cid) {
        layer.confirm('是否从该分类下的商品图片获取一张作为分类图片？', {
            btn: ['确定', '取消'] //按钮
        }, function() {
            $.ajax({
                type: 'GET',
                url: 'ajax.php?act=getClassImage&cid=' + cid,
                dataType: 'json',
                success: function(data) {
                    if (data.code == 0 && data.url) {
                        layer.msg('获取图片成功');
                        $("input.shopimg" + cid).val(data.url);
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
    },
    fileView: function(cid) {
        var shopimg = $("input.shopimg" + cid).val();
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
    },
    saveAll: function() {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: 'POST',
            url: 'ajax.class.php?act=class_shopimg_saveall',
            data: $('form#classlist').serialize(),
            dataType: 'json',
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg(data.msg, {
                        end: function() {
                            setTimeout(function() {
                                ptable();
                            }, 500)
                        }
                    });;
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
    },
    operation: function() {
        var checkStatus = treeGrid.checkStatus('treeTable');
        var ids = [];
        $.each(checkStatus.data, function(index, item) {
            ids.push(item.cid);
        });
        if (ids.length == 0) {
            return layer.msg('请先选择一个以上有效分类')
        }

        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: 'POST',
            url: 'ajax.class.php?act=class_change',
            data: {
                checkAction: checkActionValue,
                checkedList: ids,
            },
            dataType: 'json',
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg(data.msg);
                    setTimeout(function() {
                        ptable();
                    }, 800)
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
    },
    move: function() {
        var checkStatus = treeGrid.checkStatus('treeTable');
        var ids = [];
        $.each(checkStatus.data, function(index, item) {
            ids.push(item.cid);
        });
        if (ids.length == 0) {
            return layer.msg('请先选择一个以上有效分类')
        }

        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: 'POST',
            url: 'ajax.class.php?act=class_move',
            data: {
                checkedList: ids,
                upcid: upcid,
            },
            dataType: 'json',
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg(data.msg);
                    setTimeout(function() {
                        ptable();
                    }, 500);
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
    },
    setActive: function(obj, active) {
        var row = obj.data;
        var btnEl = $(".active" + row.cid + " a");
        var active = btnEl.data('active') * 1;
        console.log('active', active);
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: 'POST',
            url: './ajax.class.php?act=class_status',
            data: {
                cid: row.cid,
                active: active,
            },
            dataType: 'json',
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg('操作成功~');
                    if (active == 1) {
                        btnEl.data('active', '0');
                        btnEl.html('显示').attr('class', 'layui-btn layui-btn-normal layui-btn-xs');
                    } else {
                        btnEl.data('active', '1');
                        btnEl.html('隐藏').attr('class', 'btn btn-warning btn-xs');
                    }
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function(data) {
                layer.msg('服务器错误');
                return false;
            }
        });
    },
    del: function(obj) {
        var row = obj.data;
        layer.confirm('是否确认删除分类【' + obj.data.name + '】？<br/>确定：只删除该分类，不删除商品和子分类<br/>方式二：删除该分类下商品<br/>方式三：删除该分类下商品、子分类(含商品)', {
            btn: ['确定', '方式二', '方式三', '取消'], //按钮
            btn2: function() {
                var ii = layer.load(2, {
                    shade: [0.1, '#fff']
                });
                $.ajax({
                    type: 'POST',
                    url: './ajax.class.php?act=class_del',
                    data: {
                        cid: row.cid,
                        type: '2',
                    },
                    dataType: 'json',
                    success: function(data) {
                        layer.close(ii);
                        if (data.code == 0) {
                            layer.msg('操作成功~');
                            obj.del();
                        } else {
                            layer.alert(data.msg);
                        }
                    },
                    error: function(data) {
                        layer.msg('服务器错误');
                        return false;
                    }
                });
            },
            btn3: function() {
                var ii = layer.load(2, {
                    shade: [0.1, '#fff']
                });
                $.ajax({
                    type: 'POST',
                    url: './ajax.class.php?act=class_del',
                    data: {
                        cid: row.cid,
                        type: '3',
                    },
                    dataType: 'json',
                    success: function(data) {
                        layer.close(ii);
                        if (data.code == 0) {
                            layer.msg('操作成功~');
                            obj.del();
                        } else {
                            layer.alert(data.msg);
                        }
                    },
                    error: function(data) {
                        layer.msg('服务器错误');
                        return false;
                    }
                });
            },
        }, function() {
            var ii = layer.load(2, {
                shade: [0.1, '#fff']
            });
            $.ajax({
                type: 'POST',
                url: './ajax.class.php?act=class_del',
                data: {
                    cid: row.cid
                },
                dataType: 'json',
                success: function(data) {
                    layer.close(ii);
                    if (data.code == 0) {
                        layer.msg('操作成功~');
                        obj.del();
                    } else {
                        layer.alert(data.msg);
                    }
                },
                error: function(data) {
                    layer.msg('服务器错误');
                    return false;
                }
            });
        });
    },
    setBlockPays: function(row) {
        var cid = row.cid;
        $.ajax({
            type: 'POST',
            url: './ajax.php?act=getHidePays',
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
                                url: './ajax.php?act=setHidePays',
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
    },
    setBlockCitys: function(row) {
        var cid = row.cid;
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
}
$(document).ready(function($) {
    if ($_GET['my'] == 'classimg') {
        //分类图片
        layui.config({
            base: '../assets/public/layui/2.5.7/lay/modules/'
        }).extend({
            treeGrid: 'treeGrid'
        }).use(['jquery', 'treeGrid', 'layer', 'laypage'], function() {
            var $ = layui.jquery;
            treeGrid = layui.treeGrid; //很重要
            var layer = layui.layer;
            var laypage = layui.laypage;
            ptable = function() {
                return treeGrid.render({
                    id: tableId,
                    elem: '#' + tableId,
                    idField: 'cid',
                    url: './ajax.class.php?act=class_get',
                    cellMinWidth: 100,
                    toolbar: '#toolbar2',
                    treeId: 'cid', //树形id字段名称
                    treeUpId: 'upcid', //树形父id字段名称
                    treeShowName: 'cid', //以树形式显示的字段
                    page: {
                        theme: '#c00'
                    },
                    done: function(res, curr, count) {
                        //
                    },
                    cols: [
                        [{
                            field: 'cid',
                            title: 'CID',
                            width: 140,
                            hide: true,
                            templet: function(d) {
                                return d.cid;
                            }
                        }, {
                            field: 'name',
                            width: 260,
                            title: '分类名称',
                        }, {
                            title: '图片URL',
                            width: 520,
                            align: 'left',
                            colStyle: 'display: flex;min-width:240px;',
                            templet: function(d) {
                                if (d.shopimg == 'null' || d.shopimg == null) {
                                    d.shopimg = '';
                                }
                                var html = '',
                                    inputBox = '',
                                    fileBox = '',
                                    uploadBtn = '',
                                    getBtn = '',
                                    viewBtn = '';
                                //html = '<div class="layui-btn-group">';
                                fileBox = '<input type="file" id="file' + d.cid + '" onchange="classTooler.fileUpload(' + d.cid + ')" style="display:none;"/>';
                                inputBox = '<input type="text" placeholder="分类图片" class="layui-input shopimg' + d.cid + '" name="shopimg[' + d.cid + ']" value="' + d.shopimg + '">';
                                uploadBtn = '<a href="javascript:;"  data-cid="' + d.cid + '" lay-event="fileSelect" class="btn btn-success btn-sm" title="上传图片"><i class="glyphicon glyphicon-upload"></i></a>';
                                getBtn = '<a href="javascript:;" data-cid="' + d.cid + '" lay-event="getImage" class="btn btn-info btn-sm" title="自动获取图片"><i class="glyphicon glyphicon-search"></i></a>';
                                viewBtn = '<a href="javascript:;" data-cid="' + d.cid + '" lay-event="fileView" class="btn btn-warning btn-sm" title="查看图片"><i class="glyphicon glyphicon-picture"></i></a>';
                                //html = fileBox + '<div class="form-group">' + inputBox + html + uploadBtn + getBtn + viewBtn + '</span></div>';
                                html = fileBox + '' + inputBox + html + uploadBtn + getBtn + viewBtn + '</div></div>';
                                return html;
                            }
                        }]
                    ],
                    page: false
                });
            };
            treeGrid.on('tool(' + tableId + ')', function(obj) {
                var row = obj.data;
                var event = obj.event;
                if (obj.event === "fileSelect") { //上传图片
                    classTooler.fileSelect(row.cid);
                } else if (obj.event === "getImage") {
                    classTooler.getImage(row.cid);
                } else if (obj.event === "fileView") {
                    classTooler.fileView(row.cid);
                }
            });
            ptable();
        });
    } else {
        layui.config({
            base: '../assets/public/layui/2.5.7/lay/modules/'
        }).extend({
            treeGrid: 'treeGrid',
            dropdown: 'dropdown/dropdown'
        }).use(['jquery', 'treeGrid', 'layer', 'laypage', 'dropdown', 'form'], function() {
            var $ = layui.jquery;
            treeGrid = layui.treeGrid; //很重要
            var layer = layui.layer;
            var laypage = layui.laypage;
            var dropdown = layui.dropdown;
            var form = layui.form;
            form.on('select(checkAction)', function(data) {
                checkActionValue = data.value;
            });
            form.on('select(moveUpcid)', function(data) {
                upcid = data.value;
            });

            //console.log('dropdown', dropdown);
            var loadMenu = function() {
                dropdown.suite(".more", {
                    immed: true,
                    align: "right",
                    cssLink: "../assets/public/layui/2.5.7/lay/modules/dropdown/dropdown.css",
                    menus: [
                        [{
                            txt: "设置禁用付款方式",
                            event: "setBlockPays"
                        }, {
                            txt: "设置禁售地区",
                            event: "setBlockCitys"
                        }]
                    ],
                    onShow: function($maker, $down) {
                        //console.log('$maker', $maker);
                        $maker.find(".layui-icon").css('transition-duration', '300ms');
                        $maker.find(".layui-icon").css('transform', 'rotate(180deg)');
                        $maker.find("i").removeClass('layui-icon-triangle-r').addClass('layui-icon-triangle-d');
                    },
                    onHide: function($maker, $down) {
                        //console.log('$maker', $maker);
                        $maker.find(".layui-icon").css('transform', 'rotate(0)');
                        $maker.find("i").removeClass('layui-icon-triangle-d').addClass('layui-icon-triangle-r');
                    },
                    onItemClick: function(event, menu) {
                        //console.log(menu);
                    },
                    maxHeight: 200
                });
                // dropdown.render({
                //     elem: '.more', //可绑定在任意元素中，此处以上述按钮为例 
                //     data: [{
                //         title: '设置禁用付款方式',
                //         id: 100,
                //         href: '#'
                //     }, {
                //         title: '设置禁售地区',
                //         id: 101,
                //         href: 'https://www.layui.com/' //开启超链接
                //             ,
                //         target: '_blank' //新窗口方式打开
                //     }],
                //     id: 'more',
                //     //菜单被点击的事件
                //     click: function(obj) {
                //         console.log(obj);
                //         layer.msg('回调返回的参数已显示再控制台');
                //     }
                // });
            }
            ptable = function() {
                treeGridObj = treeGrid.render({
                    isOpenDefault: false, //默认隐藏子节点
                    checkChild: false, //父节点选中不同步选中子节点
                    id: tableId,
                    elem: '#' + tableId,
                    idField: 'cid',
                    url: './ajax.class.php?act=class_get',
                    cellMinWidth: 100,
                    toolbar: '#toolbar',
                    treeId: 'cid', //树形id字段名称
                    treeUpId: 'upcid', //树形父id字段名称
                    treeShowName: 'cid', //以树形式显示的字段
                    page: {
                        theme: '#c00'
                    },
                    done: function(res, curr, count) {
                        console.log('res.count', res.count);
                        laypage.render({
                            count: res.count,
                            elem: '#laypage',
                            layout: ['prev', 'page', 'next'],
                            limits: [10, 30, 50],
                            limit: 30,
                            curr: 1,
                            groups: 3,
                        });
                        loadMenu();
                        form.render();
                    },
                    cols: [
                        [{
                            type: 'checkbox'
                        }, {
                            field: 'cid',
                            width: 100,
                            title: 'CID',
                            templet: function(d) {
                                return '<input name="checkedList[' + d.cid + ']" class="cid' + d.cid + '" value="" type="hidden"/>' + d.cid;
                            }
                        }, {
                            field: 'name',
                            title: '分类名称',
                            width: 260,
                            templet: function(d) {
                                return '<input name="classNameList[' + d.cid + ']" class="form-control" value="' + d.name + '"/>';
                            }
                        }, {
                            field: 'upcid',
                            title: '类型',
                            align: "center",
                            width: 78,
                            templet: function(d) {
                                if (d.upcid > 0) {
                                    return '<span class="btn btn-xs btn-primary" data-cid="' + d.cid + '" data-islogin="0">二级分类</span>';
                                } else {
                                    return '<span class="btn btn-success btn-xs" data-cid="' + d.cid + '" data-islogin="1">一级分类</span>';
                                }
                            }
                        }, {
                            field: 'shops',
                            title: '商品数量',
                            width: 85,
                            align: "center",
                            templet: function(d) {
                                return d.shops + '个';
                            }
                        }, {
                            field: 'sort',
                            title: '排序',
                            colStyle: 'display: flex;min-width:170px;',
                            width: 220,
                            templet: function(d) {
                                if (d.fixed) {
                                    return '<input type="text" value="' + d.sort + '" class="form-control input-' + d.cid + '" style="max-width:70px;" "disabled">&nbsp;' + '<a class="btn btn-xs editSort" title="修改排序" data-tip="点击快速修改排序" data-cid="' + d.cid + '" style="line-height: 28px;"><i class="fa fa-pencil-square-o"></i></a>';
                                }
                                var html = '<input type="text" value="' + d.sort + '" class="form-control input-' + d.cid + '" style="max-width:70px;">&nbsp;';
                                html += '<a class="btn btn-xs editSort" title="修改排序" data-tip="点击快速修改排序" data-cid="' + d.cid + '" style="line-height: 28px;"><i class="fa fa-pencil-square-o"></i></a>';
                                html += '<a class="btn btn-xs sort" title="移到顶部" data-cid="' + d.cid + '" data-type="0" style="line-height: 28px;"><i class="fa fa-long-arrow-up"></i></a>';
                                html += '<a class="btn btn-xs sort" title="移到上一行" data-cid="' + d.cid + '" data-type="1" style="line-height: 28px;"><i class="fa fa-chevron-circle-up"></i></a>';
                                html += '<a class="btn btn-xs sort" title="移到下一行" data-cid="' + d.cid + '" data-type="2" style="line-height: 28px;"><i class="fa fa-chevron-circle-down"></i></a>';
                                html += '<a class="btn btn-xs sort" title="移到底部" data-cid="' + d.cid + '" data-type="3" style="line-height: 28px;"><i class="fa fa-long-arrow-down"></i></a>';
                                html += '';
                                return html;
                            }
                        }, {
                            field: 'islogin',
                            title: '登录可见',
                            width: 130,
                            templet: function(d) {
                                if (d.islogin == 1) {
                                    return '<span class="btn btn-xs btn-success setLoginShow" data-cid="' + d.cid + '" data-islogin="0">已开启</span>';
                                } else {
                                    return '<span class="btn btn-warning btn-xs setLoginShow" data-cid="' + d.cid + '" data-islogin="1">关闭中</span>';
                                }
                            }
                        }, {
                            title: '操作',
                            align: 'left',
                            width: 400,
                            templet: function(d) {
                                var addBtn = '',
                                    closeBtn = '',
                                    editBtn = '',
                                    delBtn = '',
                                    shopBtn = '',
                                    moreBtn = '';
                                var style = 'style="margin-left: 5px;"';
                                if (d.upcid == 0) {
                                    addBtn = '<a class="layui-btn layui-btn-xs" lay-event="addsub">添加子分类</a>';
                                }
                                editBtn = '<a class="btn btn-primary btn-xs" lay-event="edit" ' + style + '>修改</a>';
                                if (d.active == 1) {
                                    closeBtn = '<span class="active' + d.cid + '"><a class="layui-btn layui-btn-normal layui-btn-xs" data-active="0"  lay-event="setActive" ' + style + '>显示</a></span>';
                                } else {
                                    closeBtn = '<span class="active' + d.cid + '"><a class="btn btn-warning btn-xs" data-active="1" lay-event="setActive" ' + style + '>隐藏</a></span>';
                                }
                                shopBtn = '<a class="btn btn-info btn-xs" href="./shoplist.php?cid=' + d.cid + '" ' + style + '>商品</a>';
                                delBtn = '<a class="btn btn-danger btn-xs" lay-event="del" ' + style + '>删除</a>';
                                // moreBtn = '<div class="dropdown" style="display:inline-block;"><a class="btn btn-default btn-sm dropdown-toggle" id="dropdownMenu' + d.cid + '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">更多 <span class="caret"></span></a><ul class="dropdown-menu dropdown-menu-right animated fadeInRight" role="menu" aria-labelledby="dropdownMenu' + d.cid + '"><li role="presentation"><a role="menuitem" tabindex="999" onclick="setHidePays(' + d.cid + ')">设置禁用付款方式</a></li><li role="presentation"><a  role="menuitem" tabindex="999" onclick="setBlockCitys(' + d.cid + ')">设置禁售地区</a></li><li role="presentation"><a  role="menuitem" tabindex="999" onclick="setLoginShow(' + d.cid + ')">设置登录后可见</a></li></ul></div>';
                                moreBtn = '<button class="layui-btn layui-btn-primary layui-btn-xs more" ' + style + '>更多<i class="layui-icon layui-icon-triangle-r layui-font-12"></i></button>';
                                return addBtn + editBtn + closeBtn + shopBtn + delBtn + moreBtn;
                            }
                        }]
                    ],
                    page: false
                });
            };
            treeGrid.on('checkbox(' + tableId + ')', function(obj) {
                //console.log(obj); //当前行的一些常用操作集合
                //console.log(obj.checked); //当前是否选中状态
                if (obj.checked) {
                    $("input.cid" + obj.data.cid).val(obj.data.cid);
                } else {
                    $("input.cid" + obj.data.cid).val('');
                }
                //console.log(obj.data); //选中行的相关数据
                //console.log(obj.type); //如果触发的是全选，则为：all，如果触发的是单选，则为：one
            });
            treeGrid.on('tool(' + tableId + ')', function(obj) {
                var row = obj.data;
                var event = obj.event;
                //console.log('obj', obj);
                console.log('event', event);
                if (obj.event === "addsub") { //添加子分类
                    classTooler.add(row.cid);
                } else if (obj.event === "setActive") { //隐藏显示
                    classTooler.setActive(obj);
                } else if (obj.event === "edit") { //修改
                    classTooler.edit(row);
                } else if (obj.event === "del") { //删除
                    classTooler.del(obj);
                } else if (obj.event === "setBlockPays") { //设置禁用付款方式
                    classTooler.setBlockPays(row);
                } else if (obj.event === "setBlockCitys") { //设置禁用地区
                    classTooler.setBlockCitys(row);
                }
            });
            ptable();
        });
    }
});
$(document).on('click', '.sort', function(event) {
    event.preventDefault();
    /* Act on the event */
    var cid = $(this).data('cid');
    var type = $(this).data('type');
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: './ajax.class.php?act=class_sort',
        data: {
            cid: cid,
            type: type,
        },
        dataType: 'json',
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg('操作成功~');
                ptable();
            } else {
                layer.alert(data.msg);
            }
        },
        error: function(data) {
            layer.msg('服务器错误');
            return false;
        }
    });
});
$(document).on('click', '.editSort', function(event) {
    event.preventDefault();
    /* Act on the event */
    var cid = $(this).data('cid');
    var sort = $(".input-" + cid).val();
    console.log('sort', sort);
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: './ajax.class.php?act=class_sort_set',
        data: {
            cid: cid,
            sort: sort,
        },
        dataType: 'json',
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg('操作成功~');
                ptable();
            } else {
                layer.alert(data.msg);
            }
        },
        error: function(data) {
            layer.msg('服务器错误');
            return false;
        }
    });
});
$(document).on('click', '.setLoginShow', function(event) {
    event.preventDefault();
    /* Act on the event */
    var cid = $(this).data('cid');
    var islogin = $(this).data('islogin');
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: './ajax.class.php?act=class_islogin_set',
        data: {
            cid: cid,
            islogin: islogin,
        },
        dataType: 'json',
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg('操作成功~');
                ptable();
            } else {
                layer.alert(data.msg);
            }
        },
        error: function(data) {
            layer.msg('服务器错误');
            return false;
        }
    });
});
$(document).on('click', '#reload', function(event) {
    event.preventDefault();
    /* Act on the event */
    ptable();
});
$(document).on('click', '#classimg', function(event) {
    event.preventDefault();
    /* Act on the event */
    window.location.href = './classlist2.php?my=classimg';
});
$(document).on('click', '#goback', function(event) {
    event.preventDefault();
    /* Act on the event */
    window.location.href = './classlist2.php';
});
$(document).on('click', '#operation', function(event) {
    event.preventDefault();
    /* Act on the event */
    classTooler.operation();
});
$(document).on('click', '#move', function(event) {
    event.preventDefault();
    /* Act on the event */
    classTooler.move();
});
$(document).on('click', '#add', function(event) {
    event.preventDefault();
    /* Act on the event */
    classTooler.add();
});
$(document).on('click', '#saveall', function(event) {
    event.preventDefault();
    /* Act on the event */
    classTooler.saveAll();
});