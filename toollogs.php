<?php
$nosession = false;
$is_defend = true;
include __DIR__ . '/includes/common.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>商品动态 | <?php echo $conf['sitename'] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver ?>assets/css/install.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnpublic ?>layui/2.9.3/css/layui.min.css"/>
    <style>
    p{white-space:pre-line}.buttonGroup{text-align:center;margin:13px 0}
    .text-overflow{
        max-width: 560px;
        overflow-x: auto; /* 允许水平滚动条出现 */
        overflow-y: hidden; /* 禁止垂直滚动条 */
        white-space: nowrap; /* 禁止文本换行 */
        display: inline-block;
    }
    </style>

</head>
<body>
<div class="layui-container">
    <h1 class="install-h1">商品动态</h1>
    <div class="buttonGroup">
        <div class="layui-btn-group">
            <a class="layui-btn layui-btn-sm" href="./"><i class="layui-icon layui-icon-return"></i> 返回首页</a>
            <a class="layui-btn layui-btn-sm" href="./user"><i class="layui-icon layui-icon-username"></i> 用户后台</a>
            <?php if ($conf['master_open'] == 1) {?>
                <a class="layui-btn layui-btn-sm" href="./sup"><i class="layui-icon layui-icon-username"></i> 供货商后台</a>
            <?php }?>
        </div>
    </div>
    <blockquote class="layui-elem-quote">
        <div class="layui-timeline">
            <div id="toolLog-flow"></div>
        </div>
    </blockquote>
</div>
<script src="<?php echo $cdnpublic ?>layui/2.9.3/layui.min.js"></script>
<script>
    var type = 'group';

    layui.use(function () {
        const $ = layui.$, util = layui.util, flow = layui.flow;
        flow.load({
            elem: '#toolLog-flow',
            scrollElem: '#toolLog-flow',
            end: `我也是有底线的 ~_~`,
            done: function (page, next) {
                setTimeout(function () {
                    $.ajax({
                        type: "GET",
                        url: "ajax.php?act=" +(type=='group'?"toollogsgroup":"toollogs")+  "&page=" + page,
                        dataType: 'json',
                        success: function (res) {
                            let html = ``;
                            if (res.code == 0) {
                                for (let i = 0; i < res.data.length; i++) {
                                    if (type=='group') {
                                        var success=0,error =0;
                                        var content ='';
                                        if ('string'== typeof res.data[i].content && 'undefined' != typeof res.data[i].content && res.data[i].content && res.data[i].content != 'null') {
                                            content = res.data[i].content;
                                        }
                                        else if ('object'== typeof res.data[i].list) {
                                            $.each(res.data[i].list, function (indexInArray, valueOfElement) {
                                                if (valueOfElement.action.indexOf('下架') > -1) {
                                                    error++;
                                                }else{
                                                    success++;
                                                }
                                                 content+=`<div style="display: flex;
        justify-content: space-between;">
            <div style="display:inline-block">
                ` + (valueOfElement.action.indexOf('下架') > -1 ? `<span class="layui-btn layui-bg-orange layui-btn-xs">下架</span>`:`
                <span class="layui-btn layui-btn-xs">上架</span>`)+ `
                <div class="text-overflow">${valueOfElement.name}</div>
            </div>
            <div class="layui-hide-sm layui-hide-xs layui-show-md-inline layui-show-lg-inline layui-show-xl-inline">
                <div class="layui-btn-group">
                    <span class="layui-btn layui-bg-orange layui-btn-xs">最新</span>
                    <span class="layui-btn layui-bg-red layui-btn-xs">${valueOfElement.after}元</span>
                </div>
                <div class="layui-btn-group">
                    <span class="layui-btn layui-btn-xs">历史</span>
                    <span class="layui-btn layui-bg-blue layui-btn-xs">${valueOfElement.before}元</span>
                </div>
            </div>
        </div>`;

                                            });
                                        }

                                        if (content=='') {
                                            content ='这一天暂无变动记录哦~';
                                        }

                                        html += `
                       <div class="layui-timeline-item">
    <i class="layui-icon layui-timeline-axis"></i>
    <div class="layui-timeline-content layui-text">
      <h3 class="layui-timeline-title" style="color:red">${res.data[i].time}`+ ( !res.data[i].id ? `[上架${success}个商品, 下架${error}个商品]`:'[实时更新]') +`</h3>
        ${content}
    </div>
  </div>
                       `;
                                    }else{
                                        html += `
                       <div class="layui-timeline-item">
    <i class="layui-icon layui-timeline-axis"></i>
    <div class="layui-timeline-content layui-text">
      <h3 class="layui-timeline-title" style="color:red">${res.data[i].time}</h3>
      <div style="display: flex;
    justify-content: space-between;">
        <div style="display:inline-block">
            ` + (res.data[i].action.indexOf('下架') > -1 ? `<span class="layui-btn layui-bg-orange layui-btn-xs">下架</span>`:`
            <span class="layui-btn layui-btn-xs">上架</span>`)+ `
            ${res.data[i].name}
        </div>
        <div class="layui-hide-sm layui-hide-xs layui-show-md-inline layui-show-lg-inline layui-show-xl-inline">
            <div class="layui-btn-group">
                <span class="layui-btn layui-bg-orange layui-btn-xs">最新</span>
                <span class="layui-btn layui-bg-red layui-btn-xs">${res.data[i].after}元</span>
            </div>
            <div class="layui-btn-group">
                <span class="layui-btn layui-btn-xs">历史</span>
                <span class="layui-btn layui-bg-blue layui-btn-xs">${res.data[i].before}元</span>
            </div>
        </div>
    </div>
    </div>
  </div>
                       `;
                                    }

                                }
                                next(html, page < res.page);
                            }
                        }
                    })
                }, 100);
            }
        });
    })
</script>
</body>
</html>