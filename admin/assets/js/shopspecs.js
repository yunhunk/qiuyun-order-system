//商品规格方法 By 星河软件工作室
"use strict";
var chenmObj = {
    pridList: {},
    init: function () {
        $.ajax({
            type: "POST",
            url: "./ajax.php?act=getPridList",
            dataType: "json",
            success: function (data) {
                if (data.code == 0) {
                    //这里不用this指向和that变量代替，避免数据可能不会保存
                    chenmObj.pridList = data.data;
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
    addOption: function (act) {
        var index = parseInt($("#" + act + "_index").val());
        var num = parseInt($("#" + act + "_num").val());
        var html = this.getOptionHtml(act, {}, index, num);
        $("#" + act + "_options").append(html);
        $("#" + act + "_num").val(num + 1);
        $("#" + act + "_index").val(index + 1);
    },
    getOptionHtml: function (act, option, index, num) {
        act = typeof (option.title) == "string" ? act : 'add';
        option.title = typeof (option.title) != "undefined" ? option.title : '';
        option.value = typeof (option.value) != "undefined" ? option.value : '';
        option.stock = typeof (option.stock) != "undefined" ? option.stock : '999';
        option.icon = typeof (option.icon) != "undefined" ? option.icon : '';
        option.prid = typeof (option.prid) != "undefined" ? option.prid : '0';
        var html = `<tr id="${act}_option${index}">
            <td><input type="text"  name="options[${num}][title]" value="${option.title}" class="form-control input-sm"/></td>
            <td><input type="text"  name="options[${num}][value]" value="${option.value}" class="form-control input-sm" placeholder=""/></td>
            <td><input type="text"  name="options[${num}][stock]" value="${option.stock}" class="form-control input-sm" placeholder=""/></td>
            <td><div class="input-group"><input type="file" name="icon_file" onchange="chenmObj.iconUpload(this)" parentElem="#${act}_option${index}" class="hide"/><input type="text" name="options[${num}][icon]" value="${option.icon}" class="form-control input-sm option_icon" placeholder=""/><a onclick="chenmObj.iconClick(this)" parentElem="#${act}_option${index}" class="input-group-addon" title="点击上传该版本图片"><i class="fa fa-cloud-upload"></i></a><a onclick="chenmObj.iconView(this)" parentElem="#${act}_option${index}" class="input-group-addon" title="点击预览该版本图片"><i class="fa fa-file-image-o"></i></a></div></td>
            <td><select class="form-control" name="options[${num}][prid]" default="${option.prid}">${this.getPridHtml(option.prid)}</select></td>
            <td><a class="btn btn-warning btn-xs" onclick="chenmObj.delOption('${act}', ${index})">删除</a></td>
            </tr>`;
        return html;
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
                layer.msg('服务器错误，请稍后再试');
                return false;
            }
        })
    },
    getPridHtml: function (prid) {
        var html = '<option value="0">不加价或使用商品设置</option>';
        if (typeof (this.pridList) == 'object' || typeof (this.pridList) == 'array') {
            $.each(this.pridList, function (i, $res) {
                //html += '<option value="' + $res.id + '" kind="' + $res.kind + '" p_2="' + $res.p_2 + '" p_1="' + $res.p_1 + '" p_0="' + $res.p_0 + '" >' + $res.name + '(+' + $res.p_2 + '元|+' + $res.p_1 + '元|+' + $res.p_0 + '元)</option>';
                if ($res.kind == 2) {
                    html += '<option ' + (prid == $res.id ? 'selected="selected"' : '') + ' value="' + $res.id + '">' + $res.name + '(+' + $res.p_2 + '%|+' + $res.p_1 + '%|+' + $res.p_0 + '%)</option>';
                } else {
                    html += '<option ' + (prid == $res.id ? 'selected="selected"' : '') + ' value="' + $res.id + '">' + $res.name + '(+' + $res.p_2 + '元|+' + $res.p_1 + '元|+' + $res.p_0 + '元)</option>';
                }
            });
        }
        return html;
    },
    delOption: function (act, index) {
        $("#" + act + "_option" + index).remove();
    },
    add: function () {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "?act=add",
            dataType: "json",
            data: $("#addForm").serialize(),
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg("添加成功", {
                        time: 500,
                        end: function () {
                            window.location.reload();
                        }
                    });
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg("服务器错误");
                return false;
            }
        });
    },
    input: function (id) {
        var that = this;
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "?act=input",
            dataType: 'json',
            data: {
                id: id
            },
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    $("#edit_id").val(id);
                    $("#editForm input[name='name']").val(data.data.name);
                    $("#editForm input[name='title']").val(data.data.title);
                    //$("#editForm select[name='curl']").val(data.data.curl);
                    //$("#editForm select[name='type']").val(data.data.type);
                    $("#editForm select[name='attach']").val(data.data.attach);
                    $("#editForm input[name='remark']").val(data.data.remark);
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
    loadOptions: function (data) {
        var html = '';
        var that = this;
        var num = 0,
            index = 0;
        if (typeof data.options == "object" || typeof data.options == "array") {
            $.each(data.options, function (i, item) {
                num++;
                index++;
                html += that.getOptionHtml('edit', item, i, i);
            });
        }
        if (html == '') {
            html = this.getOptionHtml('edit', {}, 0, 0);
        }
        $("#edit_num").val(num);
        $("#edit_index").val(index);
        $("#edit_options").html(html);
        $("#edit_model").modal("show") && console.log("规格选项编辑=>" + data.id);
    },
    edit: function () {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "?act=edit",
            dataType: "json",
            data: $("#editForm").serialize(),
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg("修改成功", {
                        time: 500,
                        end: function () {
                            window.location.reload();
                        }
                    });
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg("服务器错误");
                return false;
            }
        });
    },
    setStatus: function (status, id) {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "?act=setStatus",
            dataType: "json",
            data: {
                status: status,
                id: id
            },
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    window.location.reload();
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg("服务器错误");
                return false;
            }
        });
    },
    del: function (id) {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "?act=del",
            dataType: "json",
            data: {
                id: id
            },
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    window.location.reload();
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg("服务器错误");
                return false;
            }
        });
    }
};
chenmObj.init();