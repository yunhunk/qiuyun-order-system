var serverPath = 'string' === typeof serverPath ? serverPath : './';
var $_GET = (function () {
    var url = window.document.location.href.toString();
    var u = url.split("?");
    if (typeof (u[1]) == "string") {
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
$(document).ready(function () {
    if ($("head").length > 0) {
        $("head").append('<style>.layui-layer-btn{display:box;display:-moz-box;display:-webkit-box;width:100%;height: auto;line-height:50px;}.layui-layer-btn a{display:block;-moz-box-flex:1;box-flex:1;-webkit-box-flex:1;font-size:14px;cursor:pointer;margin:5px 6px 0;padding:2px 18px;height: auto;text-align: center;}</style>')
    } else {
        $("body").append('<style>.layui-layer-btn{display:box;display:-moz-box;display:-webkit-box;width:100%;height: auto;line-height:50px;}.layui-layer-btn a{display:block;-moz-box-flex:1;box-flex:1;-webkit-box-flex:1;font-size:14px;cursor:pointer;margin:5px 6px 0;padding:2px 18px;height: auto;text-align: center;}</style>')

    }
    //修复订单详情的商品详情可能会太宽超出屏幕
    var cssHtml = '<style type="text/css">.table tr td img{max-width: 100%;}</style>';
    if ($("head").length > 0) {
        $("head").append(cssHtml);
    } else {
        $("body").append(cssHtml);
    }
});

$("#submitSeach").on('click', function () {
    var content = $("#content").val();
    if ("" == content) {
        layer.msg("请输入搜索关键词~");
        return false;
    }
    var url = '/?act=seach&kw=' + content;
    window.location.href = url;
});
$("#submitQuery").on('click', function () {
    var qq = $("#qq3").val();
    queryOrder2(qq, 1);
});
if ($_GET['buyok'] || $_GET['chadan'] || $_GET['query']) {
    $("#submitQuery").click();
}

function show_more(el, order_id) {
    $(el).hide();
    $("#" + order_id + " .separate").hide();
    $("#" + order_id + " .close_more").show();
    $("#" + order_id + " .order-more").show();
}

function close_more(el, order_id) {
    $(el).hide();
    $("#" + order_id + " .order-more").hide();
    $("#" + order_id + " .show_more").show();
    $("#" + order_id + " .separate").show();
}

function queryOrder2(content, page) {
    var page = page || 1;
    $('#qq3').val(content);
    if ($("#tab-query").length > 0) {
        $("#tab-query").tab('show');
    }
    if (typeof (isModal) != undefined) {
        isModal = false;
    }
    $('#submitQuery').val('Loading');
    $('#result2').hide();
    $('#list').html('');
    $.ajax({
        type: "POST",
        url: serverPath + "ajax.php?act=query",
        data: {
            qq: content,
            page: page
        },
        dataType: 'json',
        success: function (data) {
            if (data.code == 0) {
                queryVal = content;
                orderPage = page;
                if (typeof ($_GET['buyok']) != 'undefined' && $_GET['buyok'] == '1') {
                    setHistory('buyok=1&query=' + content + '&page=' + page);
                } else {
                    setHistory('query=' + content + '&page=' + page);
                }
                var status, orderid_1, x = 0;
                $('#list').append('<tr><td colspan="6"><font color="red">温馨提示：订单超过24小时仍待处理请联系客服哦~</font></td></tr>');
                $.each(data.data, function (i, item) {
                    if (x == 0) orderid_1 = item;
                    if (!item.is_curl) item.is_curl = 0;
                    status = orderStatus(item.status, item.is_curl);
                    var str = '<tr id="' + item.id + '">' + '<td class="order">' + '<div class="order-info">' + '<p>商品名称：<span>' + item.name + '</span></p>' + '<p>下单账号：<span>' + item.input + '</span></p>' + '        <p class="separate"> ······</p>' + '    </div>' + '    <div class="order-more">' + '       <p>订单时间：<span>' + item.addtime + '</span></p>' + '       <p>订单份数：<span>' + item.value + '</span></p>' + '       <p>订单金额：<span>' + item.money + '元</span></p>' + '   </div>' + '</td>' + '<td class="operation">' + '      <p>' + '          <a onclick="show_more(this, \'' + item.id + '\')" class="show_more btn btn-info btn-xs">展开更多</a>' + '          <a onclick="close_more(this, \'' + item.id + '\')" class="close_more btn btn-danger btn-xs">收缩更多</a>' + '      </p>' + '      <p>' + '         <a onclick="showOrder(' + item.id + ',\'' + item.skey + '\')" class="btn btn-primary btn-xs">查看详情</a>' + '      </p>';
                    if (Number(item.status) != 1 && Number(item.status) != 4 && Number(item.status) != 10 && Number(item.status) != 3) {
                        str += '<p><a onclick="cuidan(' + item.id + ',' + item.status + ')" title="催单" class="btn btn-warning  warning btn-xs" style="margin:3px">催单</a></p>';
                    }
                    if (Number(item.status) == 3) {
                        str += '<p><a onclick="inputOrder(\'' + item.id + '\')" title="补单" class="btn btn-primary btn-xs">补单</a></p>';
                    }
                    str += "</td></tr>";
                    $('#list').append(str);
                    if (item.result != null) {
                        if (item.status == 3) {
                            $('#list').append('<tr><td colspan=2><font color="red">异常原因：' + item.result + '</font></td></tr>');
                        }
                    }
                    x++;
                });
                var addstr = '';
                if (data.islast == true) addstr += '<button class="btn btn-primary btn-xs pull-left" onclick="queryOrder2(\'' + data.content + '\',' + (data.page - 1) + ')">上一页</button>';
                if (data.isnext == true) addstr += '<button class="btn btn-primary btn-xs pull-right" onclick="queryOrder2(\'' + data.content + '\',' + (data.page + 1) + ')">下一页</button>';
                $('#list').append('<tr><td colspan=6>' + addstr + '</td></tr>');
                $("#result2").slideDown();
                if ($_GET['buyok'] && isObject(orderid_1) && orderid_1.id) {
                    showOrder(orderid_1.id, orderid_1.skey);
                } else if (orderid != null && data.data['order_' + orderid] && (is_showWork == true || is_orderWork == true)) {
                    showOrder(orderid, data.data['order_' + orderid].skey);
                } else {
                    if (x == 0) {
                        layer.alert("未查询到相关订单记录！<br>请输入下单时填写的QQ、账号、链接等试试~<br>或点击查单处右侧的感叹号按钮获取查询帮助");
                    }
                }
            } else {
                layer.alert(data.msg);
            }
            $('#submitQuery').val('立即查询');
        }
    });
}

function isObject(v) {
    return typeof (v) == 'object' || typeof (v) == 'array' ? true : false;
}