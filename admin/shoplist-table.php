<?php
/**
 * 商品列表
 **/
include "../includes/common.php";
checkLogin();

function display_shoptype($row)
{
    if ($row['is_curl'] == 1 || $row['is_curl'] == 2) {
        if ($row['is_curl'] == 2) {
            $shequ = db()->get_row("SELECT * FROM `pre_shequ` WHERE id='{$row['shequ']}'");
            return '<a href="./shequlist.php?my=edit&id=' . $row['shequ'] . '" class="btn-warning btn-xs" data-tip="已对接到【' . $shequ['url'] . '】 点击编辑该货源站">对接</a>';
        } else {
            return '<span class="btn-warning btn-xs" data-tip="模拟POST到【' . $row['curl'] . '】">对接</span>';
        }
    } elseif ($row['is_curl'] == 4) {
        return '<a href="./fakakms.php?tid=' . $row['tid'] . '" title="点击管理该商品卡密库存" class="btn-success btn-xs">卡密</a>';
    } else {
        return '<span class="btn-info btn-xs">自营</span>';
    }
}

function display_stock($row = [])
{
    global $DB;
    if ($row['is_curl'] == 4) {
        if ($row['stock_time'] <= time() || $row['stock'] <= 0) {
            $stock = $DB->count("SELECT count(kid) FROM `pre_faka` WHERE tid='{$row['tid']}' AND `orderid`<1");
            // 2天更新一次
            $stock_time = time() + (86400 * 2);
            $DB->exec("UPDATE `pre_tools` SET `stock`='{$stock}',`stock_time`='{$stock_time}' WHERE `tid`='" . $row['tid'] . "'");
        } else {
            $stock = $row['stock'];
        }

        if ($stock > 10) {
            $color = 'green';
        } elseif ($stock > 5) {
            $color = 'blue';
        } elseif ($stock > 0) {
            $color = '#ff9800';
        } else {
            $color = 'red';
        }
        return '<span style="color:' . $color . ';"><a style="color:' . $color . ';" data-tip="点击给商品加卡" href="./fakakms.php?my=add&tid=' . $row['tid'] . '">' . $stock . '<span>';
    } else {
        $stock_open = $row['stock_open'];
        $stock      = $row['stock'];
        if ($stock_open == 1) {
            if ($stock > 10) {
                return '<font color="green">' . $stock . '<font>';
            } elseif ($stock > 3) {
                return '<font color="blue">' . $stock . '<font>';
            } elseif ($stock > 0) {
                return '<font color="yellow">' . $stock . '<font>';
            } else {
                return '<font color="red">' . $stock . '<font>';
            }
        }
    }

    return '<font color="blue">-<font>';
}

function display_price($row, $prow, $price1 = null)
{
    if ($price1 !== null) {
        if ($row['prid'] > 0) {
            if (is_array($prow) && array_key_exists('kind', $prow)) {
                $p['price'] = sprintf('%.2f', $prow['kind'] == 2 ? $price1 + $price1 * $prow['p_0'] / 100 : $price1 + $prow['p_0']);
                $p['cost']  = sprintf('%.2f', $prow['kind'] == 2 ? $price1 + $price1 * $prow['p_1'] / 100 : $price1 + $prow['p_1']);
                $p['cost2'] = sprintf('%.2f', $prow['kind'] == 2 ? $price1 + $price1 * $prow['p_2'] / 100 : $price1 + $prow['p_2']);
            } else {
                return '<span onclick="getPrice(' . $row['tid'] . ')">' . $row['price'] . '&nbsp;|&nbsp;' . $row['cost'] . '&nbsp;|&nbsp;' . $row['cost2'] . '&nbsp;|&nbsp;<i class="fa fa-exclamation-triangle" title="该加价模板不存在！" style="color:red"><i></span>';
            }

        } else {
            $p['price'] = $row['price'];
            $p['cost']  = $row['cost'];
            $p['cost2'] = $row['cost2'];
        }
        return '<span onclick="getPrice(' . $row['tid'] . ')">' . $p['price'] . '&nbsp;|&nbsp;' . $p['cost'] . '&nbsp;|&nbsp;' . $p['cost2'] . '&nbsp;|&nbsp;' . $price1 . '</span>';
    } else {
        return '<span onclick="getPrice(' . $row['tid'] . ')">' . $row['price'] . '&nbsp;|&nbsp;' . $row['cost'] . '&nbsp;|&nbsp;' . $row['cost2'] . '&nbsp;|&nbsp;<span data-tip="未设定成本价格或成本价为0"><i class="fa fa-exclamation-triangle" style="color:red"></i>成本为0</span></span>';
    }
}

function display_repeat($tid, $repeat)
{
    if ($repeat == 1) {
        return '<span class="btn btn-xs btn-success" data-tip="当前已开启重复下单，点击可关闭" onclick="setRepeat(' . $tid . ',0)">开启</span>';
    } else {
        return '<span class="btn btn-xs btn-warning" data-tip="当前已关闭重复下单，点击可开启" onclick="setRepeat(' . $tid . ',1)">禁止</span>';
    }
}

function getIlink($tids)
{
    return '';
    if ($tids) {
        $arr = explode(",", $tids);
        return '<span class="icon-ilink" title="已与商品ID[' . $arr[0] . ']关联，可同步基本信息">' . $arr[0] . '</span>';
    }
    return '<span class="icon-ilink-warn" title="未关联，可与其他商品关联">无</span>';
}

$rs           = $DB->query("SELECT * FROM pre_class WHERE upcid is null OR upcid=0 order by sort asc");
$select       = '<option value="0">未分类</option>';
$pre_class[0] = '未分类';
while ($res = $DB->fetch($rs)) {
    $pre_class[$res['cid']] = $res['name'];
    $select .= '<option value="' . $res['cid'] . '">' . $res['name'] . '</option>';
    $subClass = $DB->count("SELECT count(*) FROM pre_class WHERE upcid='" . $res['cid'] . "' order by sort asc");
    if ($subClass > 0) {
        $rs2 = $DB->query("SELECT * FROM pre_class WHERE active=1 and upcid='" . $res['cid'] . "' order by sort asc");
        while ($res2 = $DB->fetch($rs2)) {
            $pre_class[$res2['cid']] = $res2['name'];
            $select .= '<option value="' . $res2['cid'] . '">----' . $res2['name'] . '</option>';
        }
    }
}

$rs          = $DB->query("SELECT * FROM pre_price order by id asc");
$p_arr       = array();
$p_rows      = array();
$i           = 0;
$priceselect = "<option value=\"0\" kind=\"0\">0_不选择模板</option>";
while ($res = $DB->fetch($rs)) {
    $priceselect .= '<option value="' . $res["id"] . '" kind="' . $res["kind"] . '">' . $res["name"] . '（' . $res["p_0"] . '|' . $res["p_1"] . '|' . $res["p_2"] . '）</option>';
    $p_arr[$i]          = $res;
    $p_rows[$res["id"]] = $res;
    $i++;
}

if (isset($_GET['prid'])) {
    $prid    = intval($_GET['prid']);
    $sql     = " prid='$prid'";
    $numrows = $DB->count("SELECT count(*) from pre_tools where " . $sql);
    $con     = '加价模板 ' . $price_class[$prid] . ' 共有 <b>' . $numrows . '</b> 个商品';
    $link    = '&prid=' . $prid;
    $is_page = true;
} elseif (isset($_GET['shequ'])) {
    $shequ_id = intval(input('get.shequ'));
    $sql      = " shequ='{$shequ_id}' AND is_curl=2";
    $shequ    = $DB->get_row("SELECT * FROM pre_shequ WHERE id='" . $shequ_id . "' limit 1");
    //die(json_encode($shequ));
    $numrows = $DB->count("SELECT count(*) from pre_tools where " . $sql);
    $con     = '包含 社区 <b>' . $shequ['name'] . '（' . $shequ['url'] . '）' . '</b> 的共有 <b>' . $numrows . '</b> 个商品';
    $link    = '&shequ=' . $shequ_id;
    $is_page = true;
} elseif (isset($_GET['tid'])) {
    $tid     = trim(daddslashes($_GET['tid']));
    $sql     = " tid='{$tid}'";
    $numrows = $DB->count("SELECT count(*) from pre_tools where " . $sql);
    $con     = '包含 商品ID <b>' . $kw . '</b> 的共有 <b>' . $numrows . '</b> 个商品';
    $link    = '&tid=' . $tid;
    $is_page = true;
} else {
    $is_page = true;
    $sql     = [];
    $link    = '';
    if (isset($_GET['kw']) && $_GET['kw'] != '') {
        $kw    = trim(daddslashes($_GET['kw']));
        $sql[] = " (tid='{$kw}' OR name LIKE '%$kw%') ";
        $link .= '&kw=' . $kw;
        $is_page = true;
    }
    $cid = 0;
    if (isset($_GET['cid']) && $_GET['cid'] != '') {
        $cid = trim(daddslashes($_GET['cid']));
        if ($cid > -1) {
            $sql[] = " `cid`='{$cid}' ";
            $link .= '&cid=' . $cid;
            $is_page = false;
        }
    }

    if (isset($_GET['active']) && $_GET['active'] != '') {
        $active = intval(daddslashes($_GET['active']));
        if ($active > -1 && in_array($active, [0, 1])) {
            $sql[] = " `active`='{$active}' ";
            $link .= '&active=' . $active;
            $is_page = false;
        }
    }

    if (isset($_GET['is_curl']) && $_GET['is_curl'] != '') {
        $is_curl = intval(daddslashes($_GET['is_curl']));
        if ($is_curl > -1 && in_array($is_curl, [0, 1, 2, 3, 4, 5])) {
            $sql[] = " `is_curl`='{$is_curl}' ";
            $link .= '&is_curl=' . $is_curl;
            $is_page = false;
        }
    }

    if (isset($_GET['close']) && $_GET['close'] != '') {
        $close = intval(daddslashes($_GET['close']));
        if ($close > -1 && in_array($close, [0, 1])) {
            $sql[] = " `close`='{$close}' ";
            $link .= '&close=' . $close;
            $is_page = false;
        }
    }

    if (!$sql) {
        $sql = "1";
    } else {
        $sql = trim(implode(' AND ', $sql));
    }

    // die($sql);

    $numrows = $DB->count("SELECT count(*) from pre_tools where " . $sql);

    $count1 = $DB->count("SELECT count(*) from pre_tools where `active`=1 AND " . $sql);
    $count2 = $DB->count("SELECT count(*) from pre_tools where `active`=0 AND " . $sql);
    $count3 = $DB->count("SELECT count(*) from pre_tools where `is_email`=1 AND " . $sql);

    if ($sql == "1") {
        $con = '系统共有 <b>' . $numrows . '</b> 个商品, 上架中' . $count1 . '个商品, 已下架' . $count2 . '个商品, ' . $count3 . '个商品开启下单邮件提醒';
    } else {
        $con = '当前条件下共有 <b>' . $numrows . '</b> 个商品 , 上架中' . $count1 . '个商品, 已下架' . $count2 . '个商品, ' . $count3 . '个商品开启下单邮件提醒';
    }
}

echo '
    <style>
    .icon-ilink{
        color:#fff;
        background-color: #27c24c;
        padding: 2px 3px;
        font-size: 1.0rem;
    }
    .icon-ilink-warn{
        color:#fff;
        background-color: #f2cb13;
        padding: 2px 3px;
        font-size: 0.6rem;
    }
    </style>
    <form name="form1" id="form1">
      <input type="hidden" id="sortValue" value=""/>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th style="width: 75px;">Tid</th><th style="width: 200px;">商品名称</th><th style="width: 100px;">所属供货商</th><th style="width: 100px;">销量</th><th>库存</th><th style="width: 10%;">表单属性</th><th>设定价格&nbsp;<span data-tip="设置的价格，显示顺序为：销售|专业|旗舰|成本" class="btn btn-xs btn-info"><i class="fa fa-info"></i></span></th><th>所属分类</th><th>加价模板</th><th class="' . ($_GET['cid'] > 0 ? '' : 'hide') . '">商品排序</th>' . ($cid > 0 ? '<th>手动排序</th>' : '') . '<th>状态</th><th>操作</th></tr></thead>
          <tbody id="tbodylist">';

if ($cid == 0) {
    $orderBy = "tid DESC";
} else {
    $orderBy = $conf['goods_sort_type'] == 1 ? "sort ASC" : "sort DESC";
}

if ($is_page === true) {
    $pagesize = $conf['index_pagesize'] ? $conf['index_pagesize'] : 30;
    $pages    = intval($numrows / $pagesize);
    if ($numrows % $pagesize) {
        $pages++;
    }
    if (isset($_GET['page'])) {
        $page = intval($_GET['page']);
    } else {
        $page = 1;
    }
    $offset = $pagesize * ($page - 1);
    $rs     = $DB->query("SELECT * FROM pre_tools WHERE {$sql} order by {$orderBy} limit $offset,$pagesize");
} else {
    $pagesize = 500;
    $pages    = intval($numrows / $pagesize);
    if ($numrows % $pagesize) {
        $pages++;
    }
    if (isset($_GET['page'])) {
        $page = intval($_GET['page']);
    } else {
        $page = 1;
    }

    $offset = $pagesize * ($page - 1);
    $rs     = $DB->query("SELECT * FROM pre_tools WHERE {$sql} order by {$orderBy} limit $offset,$pagesize");
}

while ($res = $DB->fetch($rs)) {
    echo '<tr tid="' . $res['tid'] . '"><td><input type="checkbox" name="checkbox[]" id="list1" value="' . $res['tid'] . '" onClick="unselectall1()">&nbsp;&nbsp;' . $res['tid'] . '&nbsp;' . getIlink($res['mesh_list']) . '</td><td>' . display_shoptype($res) . '&nbsp;&nbsp;<a href="javascript:show(' . $res['tid'] . ')" style="color:#000">' . $res['name'] . '</a></td>
    <td>' . ($res['zid'] > 0 ? '<a href="./master.user.php?zid=' . $res['zid'] . '">' . $res['zid'] . '</a>' : '主站商品') . '</td>
    <td>' . $res['sale'] . '份</td>
    <td>' . display_stock($res) . '</td>
    <td>' . $res['input'] . (!empty($res['inputs']) ? '<br>' . $res['inputs'] : null) . '</td>
    <td>' . display_price($res, $p_rows[$res['prid']], $res['price1'] > 0 ? $res['price1'] : null) . '</td>';
    echo '
    <td><a href="./shoplist.php?cid=' . $res['cid'] . '" class="btn btn-info btn-xs">' . (isset($pre_class[$res['cid']]) ? $pre_class[$res['cid']] : '分类不存在') . '</a></td>
    <td><select class="form-control" onchange="setPrid(' . $res['tid'] . ',this.value)" id="prid_' . $res['tid'] . '" default="' . $res['prid'] . '"><option value="0" kind="0">0_自定义加价</option>';
    foreach ($p_arr as $value) {
        if ($value["kind"] == 2) {
            echo "<option value=\"" . $value["id"] . "\" kind=\"" . $value["kind"] . "\">" . $value["name"] . "(+" . $value['p_2'] . "%|+" . $value['p_1'] . "%|+" . $value['p_0'] . "%)</option>";
        } else {
            echo "<option value=\"" . $value["id"] . "\" kind=\"" . $value["kind"] . "\">" . $value["name"] . "(+" . $value['p_2'] . "元|+" . $value['p_1'] . "元|+" . $value['p_0'] . "元)</option>";
        }
    }
    //<span class="btn btn-xs btn-primary" onclick="setBatch(' . $res['tid'] . ')" title="点击以将本商品基本信息同步到其他关联商品">同步</span>&nbsp;<a onclick="related(' . $res['tid'] . ')" href="#" class="btn btn-info btn-xs">关联</a>&nbsp;
    echo '</select></td><td class="' . ($_GET['cid'] > 0 ? '' : 'hide') . '"><a class="btn btn-xs sort_btn" title="移到顶部" onclick="sort(' . $res['cid'] . ',' . $res['tid'] . ',0)"><i class="fa fa-long-arrow-up"></i></a><a class="btn btn-xs sort_btn" title="移到上一行" onclick="sort(' . $res['cid'] . ',' . $res['tid'] . ',1)"><i class="fa fa-chevron-circle-up"></i></a><a class="btn btn-xs sort_btn" title="移到下一行" onclick="sort(' . $res['cid'] . ',' . $res['tid'] . ',2)"><i class="fa fa-chevron-circle-down"></i></a><a class="btn btn-xs sort_btn" title="移到底部" onclick="sort(' . $res['cid'] . ',' . $res['tid'] . ',3)"><i class="fa fa-long-arrow-down"></i></a>' . ($is_mb == false ? ' <a class="btn btn-xs sort_btn sortable" title="按住拖动以排序"><i class="fa fa-arrows"></i></a>' : ' <a class="btn btn-xs sort_btn sortable" title="按住拖动以排序"><i class="fa fa-arrows fa-2x"></i></a>') . '</td>';
    if ($cid > 0) {
        echo '<td><form class="form-inline"><div class="input-group">
   <input type="text" style="width:50px" class="form-control" name="sort" value="' . $res['sort'] . '" placeholder="请输入排序">
  <span data-tid="' . $res['tid'] . '" class="input-group-addon input-group-append input-append editSort">修改</span></div></div></td>';
    }
    echo '
   <td>' . ($res['active'] == 0 ? '<span class="btn btn-xs btn-warning" onclick="setActive(' . $res['tid'] . ',1)">已下架</span>' : '<span class="btn btn-xs btn-success" onclick="setActive(' . $res['tid'] . ',0)">上架中</span>') . '&nbsp;' . ($res['close'] == 0 ? '<span class="btn btn-xs btn-success" onclick="setClose(' . $res['tid'] . ',1)">显示</span>' : '<span class="btn btn-xs btn-warning" onclick="setClose(' . $res['tid'] . ',0)">隐藏</span>') . '</td><td><a href="./shopedit.php?my=edit&tid=' . $res['tid'] . '" class="btn btn-info btn-xs">编辑</a>&nbsp;<a href="./list.php?tid=' . $res['tid'] . '" class="btn btn-warning btn-xs">订单</a>&nbsp;<span href="./shopedit.php?my=delete&tid=' . $res['tid'] . '" class="btn btn-xs btn-danger" onclick="delTool(' . $res['tid'] . ')">删除</span></td></tr>';
}

echo '
          </tbody>
        </table>
        <p><br><br></p>
</div>

        ';

if (!checkmobile()) {
    echo '<style>.fllist{}</style>';
    echo '<footer class="navbar-fixed-bottom">
             <div class="paging-navbar">';
} else {
    echo '<div style="display:block;height:50px;"></div>';
    echo '<style>.fllist{max-width:130px}</style>';
    echo '<footer>
            <div class="" style="width: 98%;display:block;background-color: #fff;border-color: 1px 2px #cfdadd;position: fixed;bottom: 0;left: 15px;"><div style="padding:5px 8px;">';
}
echo '<div class="form-inline" style="margin-bottom:6px;">
                <input type="hidden" name="prid"/>
                <input type="hidden" name="check_val"/>
                <input type="hidden" name="hide_val"/>
                <input type="hidden" name="pay_val"/>
                <input type="hidden" name="result"/>
                <input type="hidden" name="pay_alipay" value="0"/>
                <input type="hidden" name="pay_wxpay" value="0"/>
                <input type="hidden" name="pay_qqpay" value="0"/>
                <input type="hidden" name="pay_rmb" value="0"/>
                <input type="hidden" name="pay_status" value="false"/>
                <input type="hidden" name="shopimg_ok" value="false"/>
                <input type="hidden" name="shopimg" value=""/>
                <input type="hidden" name="input_ok" value="false"/>
                <input type="hidden" name="input" value=""/>
                <input type="hidden" name="inputs" value=""/>
                <input type="hidden" name="desc" value=""/>
                <input type="hidden" name="desc_ok" value="false"/>
                <input type="hidden" name="alert" value=""/>
                <input type="hidden" name="alert_ok" value="false"/>
                <input name="chkAll1" type="checkbox" id="chkAll1" onClick="this.value=check1(this.form.list1)" value="checkbox">&nbsp;反选&nbsp;
                <select name="aid">
                    <option selected="">
                        批量操作
                    </option>
                    <option class="hide" value="10">
                        >改加价模板
                    </option>
                    <option value="1">
                        >改为显示
                    </option>
                    <option value="2">
                        >改为隐藏
                    </option>
                    <option value="3">
                        >改为上架中
                    </option>
                    <option value="4">
                        >改为已下架
                    </option>
                    <option value="17">
                        >改为显示并上架
                    </option>
                    <option value="18">
                        >改为隐藏并下架
                    </option>
                    <option value="13">
                        >设置下架原因
                    </option>
                    <option value="8">
                        >开启计入排行
                    </option>
                    <option value="9">
                        >取消计入排行
                    </option>
                    <option value="25">
                        >开启邮件通知
                    </option>
                    <option value="26">
                        >关闭邮件通知
                    </option>
                    <option value="29">
                        >开启库存控制
                    </option>
                    <option value="30">
                        >关闭库存控制
                    </option>
                    <option value="12">
                        >设置预置处理信息
                    </option>
                    <option value="16">
                        >设置加价模板
                    </option>
                    <option value="20">
                        >设置支付方式
                    </option>
                    <option value="21">
                        >设置商品图片
                    </option>
                    <option value="22">
                        >设置输入框标题
                    </option>
                    <option value="23">
                        >设置商品简介
                    </option>
                    <option value="24">
                        >设置提示内容
                    </option>
                    <option value="19">
                        >设置支付名称
                    </option>
                    <option value="5">
                        >删除选中
                    </option>
                    <option value="6">
                        >复制选中
                    </option>
                </select>
                &nbsp;
                <a onclick="change()" class="btn btn-info btn-sm">执行</a>&nbsp;&nbsp;
                <select name="cid" class="fllist"><option selected>将选定商品移动到</option>' . $select . '</select>&nbsp;
                <a onclick="move()" class="btn btn-primary btn-sm">确定移动</a>&nbsp;&nbsp;
                <a onclick="setClass()" class="btn btn-success btn-sm">设置分类</a>
              </div>

        ';
//分页
$pageList = new \core\Page($numrows, $pagesize, 1, $link);
echo $pageList->showPage();

echo '
</div>
</div>
            </div>

            </div>
        </footer>
</div>
</form>';
echo '

<script type="text/javascript" src="' . $cdnserver . 'assets/public/stable/Sortable.js?' . $jsver . '"></script>
<script>
runTip();
$("#blocktitle").html(\'' . $con . '\');
';
if ($_GET['cid']) {
    echo '
    var byId = function(id) {
        return window.document.getElementById(id);
    }
    function scrollW(direction, scrollSpeed, top){
        direction   = direction || "TOP";
        scrollSpeed = scrollSpeed || 20;
        if(direction=="TOP"){
            $(document.body).scrollTop(top - scrollSpeed);
        }
        else{
            $(document.body).scrollTop(top + scrollSpeed);
        }
    }

    $(document).ready(function(){
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
                console.log("onEnd.foo:", $(ui.item));
                var lastcid = $(ui.item).prev().attr("tid");
                if(lastcid=="undefined"||lastcid=="null"||lastcid==""||$("#sortValue").val()==lastcid)return false;
                var tidArr=[];
                var items=$(".table tbody tr");
                for (i = 0; i < items.length; i++) {
                    tidArr.push($(items[i]).attr("tid"));
                }
                var ii = layer.load(2, {shade:[0.1,\'#fff\']});
                $.ajax({
                  type : \'POST\',
                  url : \'ajax.php?act=setToolSortN\',
                  dataType : \'json\',
                  data : {tids:tidArr},
                  success : function(data) {
                     layer.close(ii);
                  },
                  error:function(data){
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
    });';
}

echo '
</script>
';
