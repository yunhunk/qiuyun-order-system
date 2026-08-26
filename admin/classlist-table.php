<?php
/**
 * 分类管理
 **/
include "../includes/common.php";
checkLogin();

checkAuthority('class');

$my = isset($_GET['my']) ? $_GET['my'] : null;
if ($my == 'classimg') {
    $numrows = $DB->count("SELECT count(*) from `pre_class`");
    echo '
        <form name="classlist" id="classlist">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead><tr><th>分类名称</th><th style="min-width:220px">图片URL</th></tr></thead>
            <tbody><form id="classlist">
  ';
    $rs = $DB->query('SELECT * FROM `pre_class` WHERE upcid is null OR upcid=0 order by sort asc');
    while ($res = $DB->fetch($rs)) {
        echo '<tr><td>' . $res['name'] . '</td><td><div class="input-group"><input type="file" id="file' . $res['cid'] . '" onchange="fileUpload(' . $res['cid'] . ')" style="display:none;"/><input type="text" class="form-control input-sm" name="img' . $res['cid'] . '" value="' . $res['shopimg'] . '" placeholder="填写图片URL" required><span class="input-group-btn"><a href="javascript:fileSelect(' . $res['cid'] . ')" class="btn btn-success btn-sm" title="上传图片"><i class="glyphicon glyphicon-upload"></i></a><a href="javascript:getImage(' . $res['cid'] . ')" class="btn btn-info btn-sm" title="自动获取图片"><i class="glyphicon glyphicon-search"></i></a><a href="javascript:fileView(' . $res['cid'] . ')" class="btn btn-warning btn-sm" title="查看图片"><i class="glyphicon glyphicon-picture"></i></a></span></div></td></tr>';
        $subClass = $DB->count("SELECT count(*) FROM `pre_class` WHERE upcid='" . $res['cid'] . "' order by sort asc");
        if ($subClass > 0) {
            $rs2 = $DB->query("SELECT * FROM `pre_class` WHERE active=1 and upcid='" . $res['cid'] . "' order by sort asc");
            while ($res2 = $DB->fetch($rs2)) {
                echo '<tr><td>----' . $res2['name'] . '</td><td><div class="input-group"><input type="file" id="file' . $res2['cid'] . '" onchange="fileUpload(' . $res2['cid'] . ')" style="display:none;"/><input type="text" class="form-control input-sm" name="img' . $res2['cid'] . '" value="' . $res2['shopimg'] . '" placeholder="填写图片URL" required><span class="input-group-btn"><a href="javascript:fileSelect(' . $res2['cid'] . ')" class="btn btn-success btn-sm" title="上传图片"><i class="glyphicon glyphicon-upload"></i></a><a href="javascript:getImage(' . $res2['cid'] . ')" class="btn btn-info btn-sm" title="自动获取图片"><i class="glyphicon glyphicon-search"></i></a><a href="javascript:fileView(' . $res2['cid'] . ')" class="btn btn-warning btn-sm" title="查看图片"><i class="glyphicon glyphicon-picture"></i></a></span></div></td></tr>';
            }
        }
    }
    echo '</form><tr><td></td><td><span class="btn btn-primary btn-sm btn-block" onclick="saveAllImages()">保存全部</span></td></tr>';
    echo '          </tbody>
          </table>
        </div>
      </form>
      <div class="panel-footer">
      <span class="glyphicon glyphicon-info-sign"></span>当前图片仅适用于“mall”首页模板
      </div>

  ';
} elseif ($my == 'sub_class') {
    $numrows = $DB->count("SELECT count(*) from `pre_class` WHERE upcid='" . intval($_GET['cid']) . "'");
    $row     = $DB->get_row('select * from `pre_class` where cid=\'' . intval($_GET['cid']) . '\' limit 1');
    echo '
        <form name="classlist" id="classlist">
        <input type="hidden" name="upcid" value="' . intval($_GET['cid']) . '"/>
        <input type="hidden" name="type" value="2"/>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead><tr><th>分类ID</th><th>排序操作</th><th style="min-width:150px">名称（' . $numrows . '）</th><th>操作</th></tr>
            <tr><td>-</td><td><span class="btn btn-primary btn-sm" onclick="saveAll()">保存全部</span></td><td><input type="text" class="form-control input-sm" name="name" placeholder="分类名称" required></td><td colspan="3"><span class="btn btn-success btn-sm" onclick="addClass()"><span class="glyphicon glyphicon-plus"></span> 添加子分类</span>&nbsp;&nbsp;<a href="./classlist.php?my=classimg" class="btn btn-info btn-sm">修改分类图片</a></td></tr>
            </thead>
            <tbody><form id="classlist">
  ';
    $rs = $DB->query('SELECT * FROM `pre_class` WHERE upcid=\'' . intval($_GET['cid']) . '\' order by sort asc');
    while ($res = $DB->fetch($rs)) {
        echo '<tr cid="' . $res['cid'] . '"><td>' . $res['cid'] . '</td><td>
              <a class="btn btn-xs sort_btn" title="移到顶部" onclick="sort(' . $res['cid'] . ',0)"><i class="fa fa-long-arrow-up"></i></a><a class="btn btn-xs sort_btn" title="移到上一行" onclick="sort(' . $res['cid'] . ',1)"><i class="fa fa-chevron-circle-up"></i></a><a class="btn btn-xs sort_btn" title="移到下一行" onclick="sort(' . $res['cid'] . ',2)"><i class="fa fa-chevron-circle-down"></i></a><a class="btn btn-xs sort_btn" title="移到底部" onclick="sort(' . $res['cid'] . ',3)"><i class="fa fa-long-arrow-down"></i></a>
              </td><td><input type="text" class="form-control input-sm" id="name' . $res['cid'] . '" value="' . $res['name'] . '" placeholder="分类名称" required></td><td><span class="btn btn-primary btn-sm" onclick="editClass(' . $res['cid'] . ')">修改</span>&nbsp;' . ($res['active'] == 1 ? '<span class="btn btn-sm btn-success" onclick="setActive(' . $res['cid'] . ',0)">显示</span>' : '<span class="btn btn-sm btn-warning" onclick="setActive(' . $res['cid'] . ',1)">隐藏</span>') . '&nbsp;<a href="./shoplist.php?cid=' . $res['cid'] . '" class="btn btn-info btn-sm">商品</a>&nbsp;<span class="btn btn-sm btn-danger" onclick="delClass(' . $res['cid'] . ')">删除</span>&nbsp;<div class="dropdown" style="display:inline-block;"><a class="btn btn-default btn-sm dropdown-toggle" id="dropdownMenu' . $res['cid'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">更多 <span class="caret"></span></a><ul class="dropdown-menu dropdown-menu-right animated fadeInRight" role="menu" aria-labelledby="dropdownMenu' . $res['cid'] . '"><li role="presentation"><a role="menuitem" tabindex="999" onclick="setHidePays(' . $res['cid'] . ')">设置禁用付款方式</a></li><li role="presentation"><a  role="menuitem" tabindex="999" onclick="setBlockCitys(' . $res['cid'] . ')">设置禁售地区</a></li><li role="presentation"><a  role="menuitem" tabindex="999" onclick="setLoginShow(' . $res['cid'] . ')">设置登录后可见</a></li></ul></div></td></tr>';
    }
    echo '          </tbody>
          </table>
        </div>
        </form>
  ';
} else {

    $numrows = $DB->count("SELECT count(*) from `pre_class`");
    echo '<form name="classlist" id="classlist">
      <input type="hidden" name="type" value="1"/>
      <input type="hidden" id="sortValue" value=""/>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>分类ID</th><th>排序操作</th><th style="min-width:150px">名称（' . $numrows . '）</th><th>商品</th><th>子分类</th><th>登录可见</th><th>操作</th></tr>
          <tr><td>-</td><td>——</td><td><input type="text" class="form-control input-sm" name="name" placeholder="分类名称" required></td><td>——</td><td>——</td><td>——</td><td colspan="3"><span class="btn btn-success btn-sm" onclick="addClass()"><span class="glyphicon glyphicon-plus"></span> 添加分类</span>&nbsp;&nbsp;<a href="./classlist.php?my=classimg" class="btn btn-info btn-sm">修改分类图片</a></td></tr>
          </thead>
          <tbody id="tbodylist">';
    $rs = $DB->query("SELECT * FROM `pre_class` where `upcid` IS NULL OR upcid<1 order by sort asc");
    while ($res = $DB->fetch($rs)) {
        $count1 = (int) $DB->count("SELECT count(*) from `pre_tools` where cid= ?", array($res['cid']));
        $count2 = (int) $DB->count("SELECT count(*) from `pre_class` where upcid= ?", array($res['cid']));
        echo '<tr cid="' . $res['cid'] . '"><td><input class="inp-cmckb-xs cid_' . $res['cid'] . '" name="checkedList[]" id="cid_' . $res['cid'] . '" type="checkbox" value="' . $res['cid'] . '" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="cid_' . $res['cid'] . '"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span style="padding-left: 5px;padding-top: 2px;">' . $res['cid'] . '</span></label></td><td>
  <a class="btn btn-xs sort_btn" title="移到顶部" onclick="sort(' . $res['cid'] . ',0)"><i class="fa fa-long-arrow-up"></i></a><a class="btn btn-xs sort_btn" title="移到上一行" onclick="sort(' . $res['cid'] . ',1)"><i class="fa fa-chevron-circle-up"></i></a><a class="btn btn-xs sort_btn" title="移到下一行" onclick="sort(' . $res['cid'] . ',2)"><i class="fa fa-chevron-circle-down"></i></a><a class="btn btn-xs sort_btn" title="移到底部" onclick="sort(' . $res['cid'] . ',3)"><i class="fa fa-long-arrow-down"></i></a> <a class="btn btn-xs sort_btn sortable" title="按住拖动以排序"><i class="fa fa-arrows fa-2x"></i></a>
  </td>
    <td><input type="text" class="form-control input-sm" name="classNameList[' . $res['cid'] . ']" value="' . $res['name'] . '" placeholder="分类名称" required></td>
    <td><a href="./shoplist.php?cid=' . $res['cid'] . '">' . ($count1 > 0 ? '<span style="color:red">' . $count1 . '个</span>' : '<span style="color:green">无</span>') . '</a></td>
    <td><a href="./classlist.php?my=sub_class&cid=' . $res['cid'] . '">' . ($count2 > 0 ? '<span style="color:red">' . $count2 . '个</span>' : '<span style="color:green">无</span>') . '</a></td>
    <td>' . ($res['islogin'] == 1 ? '<span class="btn btn-sm btn-success" onclick="setLoginShow(' . $res['cid'] . ')">开启</span>' : '<span class="btn btn-sm btn-warning" onclick="setLoginShow(' . $res['cid'] . ',1)">关闭</span>') . '</td>
    <td><span class="btn btn-primary btn-sm" onclick="editClass(' . $res['cid'] . ')">修改</span>&nbsp;' . ($res['active'] == 1 ? '<span class="btn btn-sm btn-success" onclick="setActive(' . $res['cid'] . ',0)">显示</span>' : '<span class="btn btn-sm btn-warning" onclick="setActive(' . $res['cid'] . ',1)">隐藏</span>') . '&nbsp;<a href="./shoplist.php?cid=' . $res['cid'] . '" class="btn btn-info btn-sm">商品</a>&nbsp;<a href="./classlist.php?my=sub_class&cid=' . $res['cid'] . '" class="btn btn-primary btn-sm">子分类</a>&nbsp;<span class="btn btn-sm btn-danger" onclick="delClass(' . $res['cid'] . ')">删除</span>&nbsp;<div class="dropdown" style="display:inline-block;"><a class="btn btn-default btn-sm dropdown-toggle" id="dropdownMenu' . $res['cid'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">更多 <span class="caret"></span></a><ul class="dropdown-menu dropdown-menu-right animated fadeInRight" role="menu" aria-labelledby="dropdownMenu' . $res['cid'] . '"><li role="presentation"><a role="menuitem" tabindex="999" onclick="setHidePays(' . $res['cid'] . ')">设置禁用付款方式</a></li><li role="presentation"><a  role="menuitem" tabindex="999" onclick="setBlockCitys(' . $res['cid'] . ')">设置禁售地区</a></li><li role="presentation"><a  role="menuitem" tabindex="999" onclick="setLoginShow(' . $res['cid'] . ')">设置登录后可见</a></li></ul></div></td></tr>';
    }

    echo ' </tbody>
        </table>
        <p><br><br></p>
      </div>
      <!--SVG Sprites-->
      <svg class="inline-svg">
        <symbol id="checkSvg" viewbox="0 0 12 10">
          <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
        </symbol>
      </svg>';
    if ($is_mb == false) {
        echo '<footer class="navbar-fixed-bottom">
            <div class="paging-navbar">';
    } else {
        echo '<footer>
            <div style="width: 100%;display:block; background-color: #fff;  border-color: 1px 2px #cfdadd;">';
    }
    echo <<<'html2'
            <div class="form-inline" style="margin-top:3px">
            批量操作：<input name="chkAll1" type="checkbox" onclick="check1()" value="checkbox">&nbsp;反选&nbsp;
            <select name="checkAction"><option selected>操作分类</option><option value="1">批量显示选中</option><option value="2">批量隐藏选中</option><option value="4">批量设置登录可见</option><option value="5">批量取消登录可见</option><option value="3">批量修改所有</option><option value="10">批量删除选中</option></select>&nbsp;
            <button type="button" onclick="operation()" class="btn btn-primary">执行操作</button>
            &nbsp;<span onclick="javascript:listTable()" data-tip="点击刷新分类列表" class="btn btn-success" title="刷新分类列表">刷新分类</span>
            </div>
        </footer>
    </form>
html2;

}
echo '
<script type="text/javascript" src="' . $cdnserver . 'assets/public/stable/Sortable.js?' . $jsver . '"></script>
<script>
runTip();
var byId = function(id) {
    return window.document.getElementById(id);
}
function scrollW(direction, scrollSpeed, top) {
    direction = direction || "TOP";
    scrollSpeed = scrollSpeed || 20;
    if (direction == "TOP") {
        $(document.body).scrollTop(top - scrollSpeed);
    } else {
        $(document.body).scrollTop(top + scrollSpeed);
    }
}

$(document).ready(function() {
    var Sortable = window.Sortable;
    Sortable.create(byId("tbodylist"), {
        group: "words",
        handle: ".sortable",
        animation: 150,
        scroll: true,
        scrollSensitivity: 30,
        scrollSpeed: 10,
        onAdd: function(evt) {
            console.log("onAdd.foo:", [evt.item, evt.from]);
        },
        onUpdate: function(evt) {
            console.log("onUpdate.foo:", [evt.item, evt.from]);
        },
        onRemove: function(evt) {
            //console.log("onRemove.foo:", [evt.item, evt.from]);
        },
        onStart: function(evt) {
            console.log("onStart.foo:", [evt.item, evt.from]);
        },
        onSort: function(ui) {
            console.log("onSort.foo:", [ui.item, ui.from]);
            //插件自带的用不了，只能自己写个类似的，将就用着
            var scrollTop   = $(window.document).scrollTop();//文档卷去的高
            var elOffsetTop = $(ui.item).offset().top;//元素距离文档顶部的高
            var height1     = elOffsetTop-scrollTop;//元素距离窗口顶部的高
            var bodyHeight  = $(window.document).height();//文档的高
            if(scrollTop>=0 && height1<=80){
                //计算是否靠近屏幕顶部边缘
                scrollW("TOP", 80, scrollTop);
                console.log("已靠近屏幕顶部边缘："+height1);
            }
            else{
                //计算是否靠近屏幕底部部边缘
                var countheight = $(window).height() + scrollTop;
                var height2 = scrollTop + $(window).height() -elOffsetTop;
                if(height2 <= 150 && bodyHeight > countheight){
                    console.log("已靠近屏幕底部边缘："+height2);
                    scrollW("BOTTOM", 80, scrollTop);
                }
            }
        },
        onEnd: function(ui) {
            //console.log("onEnd.foo:", $(ui.item));
            if ($("#sortValue").val() == $(ui.item).prev().attr("cid")) return false;
            var classArr = [];
            var items = $(".table tbody tr");
            for (i = 0; i < items.length; i++) {
                classArr.push($(items[i]).attr("cid"));
            }
            var ii = layer.load(2, {
                shade: [0.1, \'#fff\']
            });
            $.ajax({
                type: \'POST\',
                url: \'ajax.php?act=setClassSortN\',
                dataType: \'json\',
                data: {
                    cids: classArr
                },
                success: function(data) {
                    layer.close(ii);
                },
                error: function(data) {
                    layer.close(ii);
                    layer.msg(\'服务器错误，修改排序失败\');
                    return false;
                }
            });
        },
        fallbackOnBody: true,
        fallbackTolerance: 30,
        fallbackOffset: {
            x: 40,
            y: 30
        }
    });

});
</script>
';
