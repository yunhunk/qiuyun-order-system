var editObj = null,
    ptable = null,
    treeGrid = null,
    tableId = 'treeTable',
    options = null,
    tableObj = null;
var navTooler = {
    addsub: function(row) {
        var area = [$(window).width() > 640 ? '480px' : '95%', 'auto'];
        layer.open({
            title: '添加子菜单',
            area: area,
            content: '<div id="addsub-form">' + $("#addsub-template").html() + '</div>',
            btnAlign: 'c',
            btn: ['添加', '取消'],
            yes: function(lay, index) {
                var name = $("#addsub-form #name").val();
                var url = $("#addsub-form #url").val();
                var icon = $("#addsub-form #icon").val();
                var wherein = $("#addsub-form #wherein").val();
                var keywords = $("#addsub-form #keywords").val();
                var ii = layer.load(2, {
                    shade: [0.1, '#fff']
                });
                $.ajax({
                    type: 'POST',
                    url: './ajax.php?act=nav_addsub',
                    data: {
                        upid: row.id,
                        name: name,
                        url: url,
                        icon: icon,
                        wherein: wherein,
                        keywords: keywords,
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
    add: function() {
        var area = [$(window).width() > 640 ? '480px' : '95%', 'auto'];
        layer.open({
            title: '添加菜单',
            area: area,
            content: '<div id="add-form">' + $("#add-template").html() + '</div>',
            btnAlign: 'c',
            btn: ['添加', '取消'],
            yes: function(lay, index) {
                var name = $("#add-form #name").val();
                var url = $("#add-form #url").val();
                var icon = $("#add-form #icon").val();
                var ii = layer.load(2, {
                    shade: [0.1, '#fff']
                });
                $.ajax({
                    type: 'POST',
                    url: './ajax.php?act=nav_add',
                    data: {
                        name: name,
                        url: url,
                        icon: icon,
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
                $("#edit-form #url").val(row.url);
                $("#edit-form #wherein").val(row.wherein);
                $("#edit-form #keywords").val(row.keywords);
            },
            yes: function(lay, index) {
                var name = $("#edit-form #name").val();
                var url = $("#edit-form #url").val();
                var wherein = $("#edit-form #wherein").val();
                var keywords = $("#edit-form #keywords").val();
                var ii = layer.load(2, {
                    shade: [0.1, '#fff']
                });
                $.ajax({
                    type: 'POST',
                    url: './ajax.php?act=nav_edit',
                    data: {
                        id: row.id,
                        name: name,
                        url: url,
                        wherein: wherein,
                        keywords: keywords,
                    },
                    dataType: 'json',
                    success: function(data) {
                        layer.close(ii);
                        if (data.code == 0) {
                            layer.msg('操作成功~');
                            //ptable();
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
    setActiveAll: function(status) {
        status = status || 0;
        var checkStatus = treeGrid.checkStatus('treeTable');
        var listArr = [];
        $.each(checkStatus.data, function(index, item) {
            listArr.push({
                id: item.id,
                fixed: item.fixed,
                noedit: item.noedit,
            });
        });
        if (listArr.length < 1) {
            layer.alert('未勾选菜单，无效操作');
            return;
        }
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: 'POST',
            url: './ajax.php?act=nav_status_all',
            data: {
                list: listArr,
                status
            },
            dataType: 'json',
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg(data.msg);
                    ptable();
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
    setActive: function(row, status) {
        status = status || 0;
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: 'POST',
            url: './ajax.php?act=nav_status',
            data: {
                id: row.id,
                status: status,
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
    },
    del: function(row) {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: 'POST',
            url: './ajax.php?act=nav_del',
            data: {
                id: row.id
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
    },
}
layui.config({
    base: '../assets/public/layui/2.5.7/lay/modules/'
}).extend({
    treeGrid: 'treeGrid'
}).use(['jquery', 'treeGrid', 'layer', 'laypage'], function() {
    var $ = layui.jquery;
    treeGrid = layui.treeGrid; //很重要
    var layer = layui.layer;
    var laypage = layui.laypage;
    options = {
        id: tableId,
        elem: '#' + tableId,
        idField: 'id',
        cellMinWidth: 100,
        toolbar: '#toolbar',
        treeId: 'id', //树形id字段名称
        treeUpId: 'upid', //树形父id字段名称
        treeShowName: 'name', //以树形式显示的字段
        page: {
            theme: '#c00'
        },
        cols: [
            [{
                type: 'checkbox',
            }, {
                field: 'name',
                width: 170,
                title: '菜单标题',
            }, {
                field: 'url',
                width: 140,
                title: '菜单链接'
            }, {
                field: 'label',
                title: '类型',
                width: 90,
                templet: function(d) {
                    if (d.label == 1) {
                        return '<span class="btn btn-primary btn-xs">分区标签</span>';
                    } else if (d.isadd == 1) {
                        return '<span class="btn btn-warning btn-xs">自定义菜单</span>';
                    } else {
                        return '<span class="btn btn-success btn-xs">内置菜单</span>';
                    }
                }
            }, {
                field: 'icon',
                width: 190,
                title: 'font图标',
                templet: function(d) {
                    return '<i class="' + d.icon + '" style="color: #2196F3;">&nbsp;' + d.icon + '</i>';
                }
            }, {
                field: 'sort',
                title: '排序',
                colStyle: 'display: flex;min-width:170px;',
                class: '',
                width: 230,
                // width: 'auto',
                templet: function(d) {
                    if (d.fixed) {
                        return '<input type="text" value="' + d.sort + '" class="form-control input-' + d.id + '" style="max-width:45px;" "disabled">&nbsp;' + '<a class="btn btn-xs editSort" title="修改排序" data-tip="点击快速修改排序" data-id="' + d.id + '" style="line-height: 28px;"><i class="fa fa-pencil-square-o"></i></a>';
                    }
                    var html = '<input type="text" value="' + d.sort + '" class="form-control input-' + d.id + '" style="max-width:45px;">&nbsp;';
                    html += '<a class="btn btn-xs editSort" title="修改排序" data-tip="点击快速修改排序" data-id="' + d.id + '" style="line-height: 28px;"><i class="fa fa-pencil-square-o"></i></a>';
                    html += '<a class="btn btn-xs sort" title="移到顶部" data-id="' + d.id + '" data-type="0" style="line-height: 28px;"><i class="fa fa-long-arrow-up"></i></a>';
                    html += '<a class="btn btn-xs sort" title="移到上一行" data-id="' + d.id + '" data-type="1" style="line-height: 28px;"><i class="fa fa-chevron-circle-up"></i></a>';
                    html += '<a class="btn btn-xs sort" title="移到下一行" data-id="' + d.id + '" data-type="2" style="line-height: 28px;"><i class="fa fa-chevron-circle-down"></i></a>';
                    html += '<a class="btn btn-xs sort" title="移到底部" data-id="' + d.id + '" data-type="3" style="line-height: 28px;"><i class="fa fa-long-arrow-down"></i></a>';
                    html += '';
                    return html;
                }
            }, {
                field: 'status',
                title: '状态',
                sort: true,
                templet: function(d) {
                    if (d.status == 1) {
                        return '<span class="btn btn-success btn-xs">显示</span>';
                    } else {
                        return '<span class="btn btn-danger btn-xs">隐藏</span>';
                    }
                }
            }, {
                field: 'addtime',
                title: '添加/更新时间',
                width: 160,
                templet: function(d) {
                    var html = '';
                    html += d.addtime + '<br/>';
                    html += d.updatetime;
                    return html
                }
            }, {
                title: '操作',
                width: 'auto',
                align: 'left' /*toolbar: '#barDemo'*/ ,
                templet: function(d) {
                    var addBtn = '',
                        closeBtn = '',
                        editBtn = '',
                        delBtn = '';
                    if (d.noedit != 1) {
                        var addBtn = '';
                        var style = 'style="margin-left: 5px;"';
                        if (d.upid == 0) {
                            addBtn = '<a class="layui-btn layui-btn-xs" lay-event="addsub">添加子菜单</a>';
                            editBtn = '<a class="btn btn-primary btn-xs" lay-event="edit" ' + style + '>修改</a>';
                        } else {
                            editBtn = '<a class="btn btn-primary btn-xs" lay-event="edit">修改</a>';
                        }
                        if (d.status == 1) {
                            closeBtn = '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="close" ' + style + '>隐藏</a>';
                        } else {
                            closeBtn = '<a class="btn btn-warning btn-xs" lay-event="show" ' + style + '>显示</a>';
                        }
                        if (d.isadd == 1) {
                            delBtn = '<a class="btn btn-danger btn-xs" lay-event="del" ' + style + '>删除</a>';
                        }
                    }
                    return addBtn + editBtn + closeBtn + delBtn;
                }
            }]
        ],
    };
    ptable = function(curr, limit) {
        options.done = function(data) {
            laypage.render({
                count: data.count,
                elem: 'laypage',
                layout: ['count', 'prev', 'page', 'next', 'refresh', 'limit'],
                limits: [200, 500],
                limit: limit || 200,
                curr: 1,
                groups: 3,
                jump: function(obj, first) {
                    if (!first) {
                        console.log(obj.curr, obj.limit);
                        ptable(obj.curr, obj.limit);
                    }
                }
            });
        }
        if (tableObj == null) {
            options = Object.assign(options, {
                url: './ajax.php?act=nav_get',
                where: {
                    page: 1,
                    limit: 200
                },
            });
            tableObj = treeGrid.render(options);
        } else {
            console.log(curr, limit);
            tableObj.reload(options.id, {
                url: './ajax.php?act=nav_get',
                where: {
                    page: curr,
                    limit: limit
                },
            }, true);
        }
    };
    treeGrid.on('tool(' + tableId + ')', function(obj) {
        var row = obj.data;
        var event = obj.event;
        console.log(event);
        if (obj.event === "addsub") { //添加子菜单
            navTooler.addsub(row);
        } else if (obj.event === "close") { //隐藏
            navTooler.setActive(row, 0);
        } else if (obj.event === "show") { //显示
            navTooler.setActive(row, 1);
        } else if (obj.event === "edit") { //修改
            navTooler.edit(row);
        } else if (obj.event === "del") { //删除
            navTooler.del(row);
        }
    });
    ptable();
});
$(document).on('click', '.sort', function(event) {
    event.preventDefault();
    /* Act on the event */
    var id = $(this).data('id');
    var type = $(this).data('type');
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: './ajax.php?act=nav_sort',
        data: {
            id: id,
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
    var id = $(this).data('id');
    var sort = $(".input-" + id).val();
    console.log('sort', sort);
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: './ajax.php?act=nav_sort_set',
        data: {
            id: id,
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
$(document).on('click', '#add', function(event) {
    event.preventDefault();
    /* Act on the event */
    navTooler.add();
});
$(document).on('click', '#hide', function(event) {
    event.preventDefault();
    /* Act on the event */
    navTooler.setActiveAll(0);
});
$(document).on('click', '#show', function(event) {
    event.preventDefault();
    /* Act on the event */
    navTooler.setActiveAll(1);
});