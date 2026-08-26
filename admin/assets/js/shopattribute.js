//商品属性方法 By 星河软件工作室
"use strict";
var chenmObj = {
    init: function () {
        //
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
        option.remark = typeof (option.remark) != "undefined" ? option.remark : '';
        var html = `<tr id="${act}_option${index}">
			<td><input type="text"  name="options[${num}][title]" value="${option.title}" class="form-control input-sm"/></td>
			<td><input type="text"  name="options[${num}][value]" value="${option.value}" class="form-control input-sm" placeholder=""/></td>
			<td><input type="text"  name="options[${num}][remark]" value="${option.remark}" class="form-control input-sm" placeholder=""/></td>
			<td><a class="btn btn-warning btn-xs" onclick="chenmObj.delOption('${act}', ${index})">删除</a></td>
			</tr>`;
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
                    //$("#editForm select[name='type']").val(data.data.type);
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
            html = `<tr id="edit_option0">
			<td><input type="text" placeholder="中号" name="options[0][title]" value="" class="form-control input-sm"/></td>
			<td><input type="text" placeholder="中号/M" name="options[0][value]" value="" class="form-control input-sm" placeholder=""/></td>
			<td><input type="text"  name="options[0][remark]" value="" class="form-control input-sm" placeholder=""/></td>
			<td><a class="btn btn-warning btn-xs" onclick="chenmObj.delOption('edit', 0)">删除</a></td>
			</tr>`;
        }
        $("#edit_num").val(num);
        $("#edit_index").val(index);
        $("#edit_options").html(html);
        $("#edit_model").modal("show") && console.log("属性选项编辑=>" + data.id);
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