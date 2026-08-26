"use strict";
var pageLoad = true,
    isUpdate = true,
    layerIndex = null,
    postOptions = null,
    width;

function loadInfo(data) {
    var area = [$(window).width() > 640 ? '480px' : '95%', 'auto'];
    if (data.data.now >= data.data.count) {
        isUpdate = false;
        var opions = {
            title: '更新完成',
            area: area,
            btnAlign: 'c',
            btn: '好的我知道了',
        }
        layer.alert(data.msg, opions);
    } else {
        var width = Math.floor(data.data.now / data.data.count * 100);
        var progress = '<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="60" ' + 'aria-valuemin="0" aria-valuemax="100" style="width: ' + width + '%;">' + '<span class="sr-only">' + width + '% 完成</span>' + width + '%' + '</div>' + '</div>';
        if (layerIndex === null) {
            var content = '<div class="text-center" style="padding: 10px;">';
            content += '已更新：<span id="progress_now">' + data.data.now + '</span> / 总数量：<span id="progress_count">' + data.data.count + '</span> /   预计剩余时间：<span id="progress_needtime">' + data.data.needtime + '</span>';
            content += '<div class="progress progress-striped active" id="progress">';
            content += progress;
            content += '</div>';
            var opions = {
                type: 1,
                title: '正在更新中，请不要关闭本页面',
                skin: 'layui-layer-rim',
                area: area,
                btnAlign: 'c',
                content: content,
                btn: ['取消更新'],
                yes: function(layo, index) {
                    layer.msg('取消成功，稍后将停止更新！');
                    isUpdate = false;
                }
            }
            layerIndex = layer.open(opions);
        } else {
            $("#progress").html(progress);
            $("#progress_now").html(data.data.now);
            $("#progress_count").html(data.data.count);
            $("#progress_needtime").html(data.data.needtime);
        }
    }
    setTimeout(function() {
        if (isUpdate == true) {
            data.data.file = [];
            updateNew({
                data: data.data
            });
        } else {
            layer.close(layerIndex);
        }
    }, 300);
}
// loadInfo({
//     code: 0,
//     msg: '更新完成！',
//     data: {
//         now: 35,
//         count: 168
//     }
// });
function updateNew(options) {
    postOptions = options;
    options.data.list_succ = []
    options.data.list_warn = []
    $.ajax({
        type: 'POST',
        url: '?act=updateNew',
        data: options,
        dataType: 'json',
        success: function(data) {
            if (data.code >= 0) {
                loadInfo(data);
            } else if (data.code == -2) {
                layer.alert(data.msg, {
                    btn: ["好的我知道了", "继续尝试更新"],
                    btn2: function() {
                        updateNew({
                            data: data.data
                        });
                    }
                });
            } else {
                layer.alert(data.msg);
            }
        },
        error: function() {
            layer.alert('当前服务器不支持在线更新，请下载更新包手动上传覆盖更新！', {
                btn: ["好的我知道了"],
            });
        }
    });
}

function update() {
    isUpdate = true;
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'GET',
        url: '?act=update',
        data: {
            all_update: 0
        },
        dataType: 'json',
        timeout: 15 * 60 * 1000,
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                updateNew({
                    data: {
                        all_update: 0
                    }
                });
            } else if (data.code == 3) {
                layer.alert(data.msg, {
                    btn: ["好的谢谢", "强制更新"],
                    btn2: function() {
                        updateNew({
                            data: {
                                all_update: 1
                            }
                        });
                    }
                });
            } else if (data.code == 2) {
                $("#update-msg").html(data.msg);
            } else {
                layer.alert(data.msg);
            }
        },
        error: function(data) {
            layer.close(ii);
            layer.msg('服务器错误或请求超时');
            return false;
        }
    });
}
$(document).on('click', '.updateSubmit', function(event) {
    event.preventDefault();
    /* Act on the event */
    update();
});