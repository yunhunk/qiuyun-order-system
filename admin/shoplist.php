<?php
/**
 * 商品管理 By 秋云
 */
include '../includes/common.php';
checkLogin();
$title = '商品管理';
$act   = isset($_GET['act']) ? $_GET['act'] : null;

checkFileSize();

checkAuthority('shops');

function name_func($cid)
{
    global $DB;
    $res = $DB->get_row("SELECT * FROM cmy_class WHERE cid='{$cid}'");
    return $res['name'];
}

if ($act == 'editPrice') {
    $tid  = intval($_POST['tid']);
    $rows = $DB->get_row("SELECT * from cmy_tools where tid= ? limit 1", [$tid]);
    if (!$rows) {
        exit('{"code":-1,"msg":"商品不存在"}');
    }

    $price  = $_POST['price_s'];
    $cost   = $_POST['cost_s'];
    $cost2  = $_POST['cost2_s'];
    $price1 = $_POST['price1'];
    $prid   = intval($_POST['prid']);
    if ($DB->query("UPDATE `pre_tools` SET `price1`='" . $price1 . "',`price`='" . $price . "',`cost`='" . $cost . "',`cost2`='" . $cost2 . "',`prid`='" . $prid . "' WHERE `tid`='{$tid}'")) {
        exit('{"code":0,"msg":"succ"}');
    } else {
        exit('{"code":-1,"msg":"修改商品失败！' . $DB->error() . '"}');
    }

} else if ($act == 'setP_cover') {
    $tid     = $_GET['tid'];
    $p_cover = $_GET['p_cover'];
    $row     = $DB->query("UPDATE cmy_tools set p_cover='{$p_cover}' where tid='{$tid}'");
    if ($row) {
        exit('{"code":0,"msg":"操作成功！"}');
    } else {
        exit('{"code":-1,"msg":"操作失败！"}');
    }
} elseif ($act == 'setSort') {
    //一键排序
    $tid  = $_GET['tid'];
    $sort = $_GET['sort'];
    $row  = $DB->query("UPDATE cmy_tools set sort='{$sort}' where tid='{$tid}'");
    if ($row) {
        exit('{"code":0,"msg":"操作成功！"}');
    } else {
        exit('{"code":-1,"msg":"操作失败！"}');
    }
} elseif ($act == "setClass") {
    $tidlist = $_POST['tidlist'];
    $fidlist = $_POST['cidlist'];
    $tidArr  = explode('|', $tidlist);
    $fidArr  = explode('|', $fidlist);
    $num     = 0;
    $succ    = 0;
    $no      = 0;
    $num     = count($tidArr);
    foreach ($tidArr as $tid) {
        $row = $DB->query("UPDATE `pre_tools` set `cids` ='" . (implode(',', $fidArr)) . "' where `tid`='{$tid}'");
        if ($row) {
            $succ++;
        } else {
            $no++;
        }
    }
    $result = array('code' => 0, 'msg' => '共' . $num . '个商品设置分类目录，成功' . $succ . '个，失败' . $no . '个');
    exit(json_encode($result));
} elseif ($act == "getClass") {
    $rsfl = $DB->query("SELECT * FROM cmy_class where upcid is null OR upcid=0 order by sort asc");
    $row  = $CACHE->read('classTable');
    if ($row['expire'] >= time() && $row['v']) {
        $list = $row['v'];
    } else {
        $list = '';
        while ($resfl = $DB->fetch($rsfl)) {
            $list .= '<tr><td><input type="checkbox" name="classbox[]" value="' . $resfl['cid'] . '"/>&nbsp;' . $resfl['cid'] . '</td><td>' . $resfl['name'] . '</td></tr>';
            $subClass = $DB->count("SELECT count(*) FROM cmy_class WHERE upcid='" . $res['cid'] . "' order by sort asc");
            if ($subClass > 0) {
                $rs2 = $DB->query("SELECT * FROM cmy_class WHERE active=1 and upcid='" . $resfl['cid'] . "' order by sort asc");
                while ($res2 = $DB->fetch($rs2)) {
                    $list .= '<tr><td><input type="checkbox" name="classbox[]" value="' . $res2['cid'] . '"/>&nbsp;' . $res2['cid'] . '</td><td>----' . $res2['name'] . '</td></tr>';
                }
            }
        }
        $CACHE->save('classTable', $list, time() + 120);
    }

    $result = array('code' => 0, 'list' => $list);
    exit(json_encode($result));
} elseif ($act == "readClass") {
    $tid = intval($_POST['tid']);
    $row = $DB->get_row("SELECT * from cmy_tools where tid='$tid' limit 1");
    if ($row) {
        $cidArr = explode(',', $row['cids']);
        $rs     = $DB->query("SELECT * FROM cmy_class where upcid is null OR upcid=0 order by sort asc");
        $list   = '';
        foreach ($rs as $res) {
            if (in_array($res['cid'], $cidArr) || $res['cid'] == $row['cid']) {
                $checked = 'checked="checked"';
            } else {
                $checked = '';
            }
            $list .= '<tr><td><input type="checkbox" name="classbox[]" ' . $checked . ' value="' . $res['cid'] . '"/>&nbsp;' . $res['cid'] . '</td><td>' . $res['name'] . '</td></tr>';
            $subClass = $DB->count("SELECT count(*) FROM cmy_class WHERE upcid='" . $res['cid'] . "' order by sort asc");
            if ($subClass > 0) {
                $rs2 = $DB->query("SELECT * FROM cmy_class WHERE active=1 and upcid='" . $res['cid'] . "' order by sort asc");
                while ($res2 = $DB->fetch($rs2)) {
                    if (in_array($res['cid'], $cidArr) || $res2['cid'] == $row['cid']) {
                        $checked = 'checked="checked"';
                    } else {
                        $checked = '';
                    }
                    $list .= '<tr><td><input type="checkbox" name="classbox[]" ' . $checked . ' value="' . $res2['cid'] . '"/>&nbsp;' . $res2['cid'] . '</td><td>----' . $res2['name'] . '</td></tr>';
                }
            }

        }
        $result = array('code' => 0, 'msg' => 'ok', 'list' => $list);
    } else {
        $result = array('code' => -1, 'msg' => '该商品不存在！', 'tid' => $tid);
    }
    exit(json_encode($result));
} else if ($act == 'setPrid') {
    $tid  = $_POST['tid'];
    $prid = trim($_POST['prid']);
    $row  = $DB->get_row("SELECT * from cmy_price where id='{$prid}' limit 1");
    $tool = $DB->get_row("SELECT * from cmy_tools where tid='{$tid}' limit 1");
    if ($row || $prid < 1) {
        $sql = $DB->query("update `pre_tools` set `prid`='" . $prid . "' where tid='" . $tid . "'");
        if ($sql) {
            if ($prid > 0 && isset($tool['price1']) && $tool['price1'] > 0) {
                $data['price'] = $tool['price'];
                $data['cost']  = $tool['cost'];
                $data['cost2'] = $tool['cost2'];
                $price1        = $tool['price1'];
                if ($row['p_0'] > 0 && $price1 > 0) {
                    $data['price'] = $row['kind'] == 2 ? round($price1 + $price1 * $row['p_0'] / 100, 2) : round($row['p_0'] + $price1, 2);
                }

                if ($row['p_1'] > 0 && $price1 > 0) {
                    $data['cost'] = $row['kind'] == 2 ? round($price1 + $price1 * $row['p_1'] / 100, 2) : round($row['p_1'] + $price1, 2);
                }

                if ($row['p_2'] > 0 && $price1 > 0) {
                    $data['cost2'] = $row['kind'] == 2 ? round($price1 + $price1 * $row['p_2'] / 100, 2) : round($row['p_2'] + $price1, 2);
                }

                $data['price1'] = $price1;
                $sql2           = $DB->query("update `pre_tools` set `price`=" . $data['price'] . ",`cost`=" . $data['cost'] . ",`cost2`=" . $data['cost2'] . " where tid='" . $tid . "'");
                if ($sql2) {
                    $result = array("code" => 0, "msg" => "修改模板并设置价格成功", "data" => $data);
                } else {
                    $result = array("code" => -1, "msg" => "修改加价模板成功，更新价格失败！" . $DB->error());
                }
            } else {
                $result = array("code" => 0, "msg" => "修改模板成功");
            }
        } else {
            $result = array("code" => -1, "msg" => "修改加价模板失败！" . $DB->error());
        }
    } else {
        $result = array("code" => -1, "msg" => "该加价模板不存在或已被删除");
    }
    exit(json_encode($result));
} elseif ($act == "tools") {
    $tid = input('get.tid', 1);
    if ($tid > 0) {
        $tool = $DB->get_row("SELECT cid FROM cmy_tools WHERE tid = ?", array($tid));
        $cid  = $tool['cid'];
        $rs   = $DB->query("SELECT * FROM cmy_tools WHERE cid= ? order by sort asc", array($cid));
    } else {
        $cid = (int) $_GET['cid'];
        if ($cid == 0) {
            $rs = $DB->query("SELECT * FROM cmy_tools WHERE 1 order by sort asc");
        } else {
            $rs = $DB->query("SELECT * FROM cmy_tools WHERE cid='" . $cid . "' order by sort asc");
        }
    }

    if (!$rs) {
        $result = array('code' => -1, 'msg' => "该分类下无商品！", 'cid' => $cid);
        exit(json_encode($result));
    }

    $data = "";
    while ($res = $DB->fetch($rs)) {
        $data .= '<tr><td><input type="checkbox" name="checkbox2[]" id="list2" value="' . $res['tid'] . '" onclick="unselect()">&nbsp;' . $res['tid'] . '</td><td>' . $res['name'] . '</td><td>' . $res['price'] . '</td><td>' . name_func($res['cid']) . '</td></tr>';
    }
    $result = array('code' => 0, 'msg' => "succ", 'data' => $data, 'cid' => $cid);
    exit(json_encode($result));
} elseif ($act == "setRelated") {
    $tid  = input('post.tid', 1);
    $list = input('post.list', 1);
    $list = $tid . ',' . $list;
    $tids = explode(',', $list);
    $ok   = 0;
    $num  = 0;
    foreach ($tids as $tid) {
        $num++;
        if ($DB->query("update `pre_tools` set `mesh_list`='" . $list . "' where tid='" . $tid . "'")) {
            $ok++;
        }
    }

    if ($ok > 0) {
        $result = array('code' => 0, 'msg' => "成功操作" . $ok . "个，共" . $num . "个");
    } else {
        $result = array('code' => -1, 'msg' => "成功操作" . $ok . "个，共" . $num . "个" . $DB->error());
    }
    exit(json_encode($result));
} else if ($act == 'refreshPrice') {
    $cid = input('get.cid', 1);
    if (!$cid) {
        $result = array("code" => -1, "msg" => "分类不存在或为空！");
        exit(json_encode($result));
    }
    $success    = 0;
    $is_need    = 0;
    $resultList = "";
    $pricejk    = new \core\InfoControler();
    $rs         = $DB->query("SELECT * FROM cmy_shequ WHERE type IN (0,1,2,6,9) ORDER BY id ASC");
    while ($res = $DB->fetch($rs)) {
        if ($cid != (0 - 1)) {
            $tcount = $DB->count("SELECT count(*) FROM cmy_tools WHERE is_curl=2 AND shequ= ? AND cid= ?", [$res['id'], $cid]);
        } else {
            $tcount = $DB->count("SELECT count(*) FROM cmy_tools WHERE is_curl=2 AND shequ=? ", [$res['id']]);
        }

        if ($tcount > 0 && $res['username'] && $res['password']) {
            $is_need += $tcount;
            if ($res['type'] == 0 || $res['type'] == 2) {
                $results = $pricejk->pricejk_jiuwu($res['id'], $cid);
                if (is_array($results)) {
                    if ($results['code'] == 0) {
                        $success += $results['success'];
                        saveSetting('pricejk_status', 'ok');
                    } else {
                        saveSetting('pricejk_status', $results['msg'] . '（' . $results['warnlist'] . '）');
                        $resultList .= '对接站点地址' . $res['url'] . '：' . $results['msg'] . '（' . $results['warnlist'] . '）' . '<br/>';
                    }
                } else {
                    saveSetting('pricejk_status', $results);
                    $resultList .= '对接站点地址' . $res['url'] . '：' . $results['msg'] . '（' . $results['warnlist'] . '）' . '<br/>';
                }
            } elseif ($res['type'] == 6 && method_exists($pricejk, 'pricejk_kayixin')) {
                $results = $pricejk->pricejk_kayixin($res['id'], $cid);
                if (is_array($results)) {
                    if ($results['code'] == 0) {
                        $success += $results['success'];
                        saveSetting('pricejk_status', 'ok');
                    } else {
                        saveSetting('pricejk_status', $results['msg'] . '（' . $results['warnlist'] . '）');
                        $resultList .= "对接站点：" . $shequ['url'] . "：" . $results['msg'] . "（" . $results['warnlist'] . "）\r\n<br>";
                    }
                } else {
                    saveSetting('pricejk_status', $results);
                    $resultList .= '对接站点：' . $shequ['url'] . '：（' . $results . '）\r\n<br>';
                }
            } elseif ($res['type'] == 1) {
                $results = $pricejk->pricejk_yile($res['id'], $cid);
                if (is_array($results)) {
                    if ($results['code'] == 0) {
                        $success += $results['success'];
                        saveSetting('pricejk_status', 'ok');
                    } else {
                        saveSetting('pricejk_status', $results['msg'] . '（' . $results['warnlist'] . '）');
                        $resultList .= '对接站点地址' . $res['url'] . '：' . $results['msg'] . '（' . $results['warnlist'] . '）' . '<br/>';
                    }
                } else {
                    saveSetting('pricejk_status', $results);
                    $resultList .= '对接站点地址' . $res['url'] . '：' . $results['msg'] . '（' . $results['warnlist'] . '）' . '<br/>';
                }
            } elseif ($res['type'] == 9) {
                $results = $pricejk->pricejk_kashang($res['id'], $cid);
                if (is_array($results)) {
                    if ($results['code'] == 0) {
                        $success += $results['success'];
                        saveSetting('pricejk_status', 'ok');
                    } else {
                        saveSetting('pricejk_status', $results['msg'] . '（' . $results['warnlist'] . '）');
                        $resultList .= '对接站点地址' . $res['url'] . '：' . $results['msg'] . '（' . $results['warnlist'] . '）' . '<br/>';
                    }
                } else {
                    saveSetting('pricejk_status', $results);
                    $resultList .= '对接站点地址' . $res['url'] . '：' . $results['msg'] . '（' . $results['warnlist'] . '）' . '<br/>';
                }
            }
        }
    }

    if ($is_need > 0) {
        if ($success > 0) {
            $result = array('code' => 0, "msg" => '成功更新' . $success . '个商品！' . ($resultList != "" ? '其中更新失败的原因:' . $resultList : ''));
        } else {
            $result = array('code' => -1, "msg" => '更新失败，共执行' . $is_need . '个商品，原因如下：<br>' . $resultList);
        }
    } else {
        $result = array('code' => -1, "msg" => '没有需要更新的对接商品');
    }

    exit(json_encode($result));
}

include './head.php';

$rs           = $DB->query("SELECT * FROM cmy_class WHERE upcid is null OR upcid=0 order by sort asc");
$select       = '<option value="0">未分类</option>';
$cmy_class[0] = '未分类';
while ($res = $DB->fetch($rs)) {
    $cmy_class[$res['cid']] = $res['name'];
    $select .= '<option value="' . $res['cid'] . '">' . $res['name'] . '</option>';
    $subClass = $DB->count("SELECT count(*) FROM cmy_class WHERE upcid='" . $res['cid'] . "' order by sort asc");
    if ($subClass > 0) {
        $rs2 = $DB->query("SELECT * FROM cmy_class WHERE active=1 and upcid='" . $res['cid'] . "' order by sort asc");
        while ($res2 = $DB->fetch($rs2)) {
            $cmy_class[$res2['cid']] = $res2['name'];
            $select .= '<option value="' . $res2['cid'] . '">----' . $res2['name'] . '</option>';
        }
    }
}

echo '<div class="col-xs-12 col-md-12 center-block" style="float: none;margin:0;padding:0;">
';
$my = (isset($_GET['my']) ? $_GET['my'] : null);
if ($my == 'qk2') {
    $sql = 'TRUNCATE TABLE `pre_tools`';
    if ($DB->query($sql)) {
        exit('<script language=\'javascript\'>alert(\'清空成功！\');window.location.href=\'shoplist.php\';</script>');
    } else {
        exit('<script language=\'javascript\'>alert(\'清空失败！' . $DB->error() . '\');history.go(-1);</script>');
    }
} else {
    $cid = intval($_GET['cid'] ? $_GET['cid'] : 0 - 1);
    echo <<<'MODAL'
      <!------商品功能使用帮助 ------->
   <div class="modal fade" id="help_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">商品功能使用帮助</h4>
            </div>
            <div class="modal-body">
                <h4 style="color:red">一.关联功能</h4>
                <p>本功能方便快速上架和修改多个商品！此功能可用于将多个商品关联到一起，先把商品关联后才能使用同步功能！请仔细查看下方说明功能</p>
                <p></p>
                <h4 style="color:red">二.同步功能</h4>
                <p>1.主要功能：此功能可用于将已经关联商品的通用设置属性同步过来（也就是都有的商品属性），不会同步数量和价格等属内容可能不相同的属性！<br>
                2.属性包括：对接社区、对接参数、下单动作、重复下单、商品简介、弹窗说明、预置处理信息、输入框标题（包括更多）、商品图片、加价模板<br>
                3.使用说明：同步时是可以单独某个商品的属性同步到其他关联的商品，所以并不是随便在某个商品后面点同步！例如已经关联的商品如【**100个】、【**1000个】，你修改了【**100个】的属性，此时在商品【**100个】后面点击同步即可把修改后的内容同步到其他商品
                </p>
                <p></p>

            </div>
        </div>
    </div>
</div>
    <!------商品功能使用帮助 END-------->
      <!------商品设置多分类 ------->
   <div class="modal fade" id="editCids_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">批量设置多分类</h4>
            </div>
            <div class="modal-body">
                    <center>
                    <br/>当前选中商品ID：<span id="editCids_tid"><span><br/></center>
                    <div class="table-responsive" style="height:460px;max-height:600px;overflow: auto;">
                        <table class="table table-striped">
                        <thead>
                        <tr><th>cid</th><th>分类名称</th></tr>
                        </thead>
                        <tbody id="editCids_list">
                        </tbody>
                        </table>
                    </div>
                    <div class="form-group">
                        <a onclick="setShopCids($('#editCids_tid').html())" class="btn btn-primary btn-block">提交设置</a>
                        <br>
                        <a class="btn btn-danger btn-block" id="submit" data-dismiss="modal">关闭页面</a>
                    </div>
            </div>
        </div>
    </div>
</div>
    <!------商品设置多分类 END-------->
    <!------商品设置多分类 ------->
   <div class="modal fade" id="readCids_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">批量设置多分类</h4>
            </div>
            <div class="modal-body">
                    <center>当前选中商品ID：<span id="readCids_tid"><span><br/></center>
                    <div class="table-responsive" style="height:460px;max-height:600px;overflow: auto;">
                        <table class="table table-striped">
                        <thead>
                        <tr><th>cid</th><th>分类名称</th></tr>
                        </thead>
                        <tbody id="readCids_list">
                        </tbody>
                        </table>
                    </div>
                    <div class="form-group">
                        <a onclick="setShopCids($('#readCids_tid').html())" class="btn btn-primary btn-block">提交设置</a>
                        <br>
                        <a class="btn btn-danger btn-block" id="submit" data-dismiss="modal">关闭页面</a>
                    </div>
            </div>
        </div>
    </div>
</div>
    <!------商品设置多分类 END-------->

MODAL;

    $cid = input('cid');
    if ($cid == '') {
        $cid = -1;
    }

    $is_curl = input('is_curl');
    if ($is_curl == '') {
        $is_curl = -1;
    } else {
        $is_curl = intval($is_curl);
    }

    $active = input('active');
    if ($active == '') {
        $active = -1;
    } else {
        $active = intval($active);
    }

    $close = input('close');
    if ($close == '') {
        $close = -1;
    } else {
        $close = intval($close);
    }

    echo '
    <div class="col-sm-12 col-md-12  col-lg-12 center-block" style="float: none;padding-top:10px ">
    <div class="block">
<div class="block-title clearfix">
<h2 id="blocktitle"></h2>
</div>';

    if ($conf['cronkey']) {
        if ($_GET['cid'] > 0) {
            $cronHtml = '<a target="_blank" class="btn btn-danger" href="' . $weburl . 'cron/priceCron.php?act=priceCron&key=' . $conf['cronkey'] . '&cid=' . intval($_GET['cid']) . '&test=1">同步价格</a>';
        } else {
            $cronHtml = '<a target="_blank" class="btn btn-danger" href="' . $weburl . 'cron/priceCron.php?act=priceCron&key=' . $conf['cronkey'] . '&test=1">同步价格</a>';
        }
    } else {
        $cronHtml = '<a target="_blank" class="btn btn-danger" href="' . $weburl . 'cron/priceCron.php?act=priceCron&key=' . $conf['cronkey'] . '&test=1" data-tip="未设置监控密钥，无法同步价格">无法同步</a>';
    }

    echo ' <form onsubmit="return searchItem()" method="GET" class="form-inline">
 <a id="add" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;添加商品</a>&nbsp;
 <div class="form-group">
   <select class="form-control" name="toolcid" default="' . $cid . '" id="toolcid">
  <option value="-1">所有分类</option>
   ' . $select . '
   </select>
 </div>
<div class="form-group">
   <input type="text" class="form-control" name="kw" placeholder="请输入商品名称">
</div>
<div class="form-group">
    <select class="form-control" name="is_curl" default="' . $is_curl . '" id="is_curl" placeholder="选择商品类型">
        <option value="-1">所有商品类型</option><option value="0">无</option><option value="2">自动提交到进货站点</option><option value="1">自定义访问URL/POST</option><option value="4">卡密商品自动发货</option><option value="3">自动发送提醒邮件</option><option value="5">直接显示内容</option>
   </select>
 </div>
 <div class="form-group">
    <select class="form-control" name="active" default="' . $active . '" id="active" placeholder="选择上架状态">
        <option value="-1">所有状态</option>
        <option value="1">上架中</option>
        <option value="0">已下架</option>
    </select>
 </div>
 <div class="form-group">
    <select class="form-control" name="close" default="' . $close . '" id="close" placeholder="选择隐藏状态">
        <option value="-1">所有状态</option>
        <option value="0">显示中</option>
        <option value="1">已隐藏</option>
    </select>
 </div>
 <button type="submit" class="btn btn-info">搜索</button>&nbsp;
 <a href="./shoplist.php" class="btn btn-warning">重置</a>&nbsp;
 <div class="btn-group" role="group">
    <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">批量操作 <span class="caret"></span></button>
    <ul class="dropdown-menu">
        <li><a href="javascript:operation(1)">批量替换商品名称(选中商品)</a></li>
        <li><a href="javascript:operation(2)">批量替换商品名称(全部商品)</a></li>
    </ul>
</div>
 ' . $cronHtml . '
<button type="button" class="btn btn-warning dropdown-toggle hide" data-toggle="dropdown">更多<span class="caret"></span></button>&nbsp;
<ul class="dropdown-menu dropdown-menu-right animated fadeInRight hide">
    <li class="dropdown-item"><a href="./set.php?mod=pricejk" class="dropdown-menu-right animated fadeInRight" role="menu" title="价格监控">商品价格监控</a></li>
</ul>
 <a href="javascript:listTable(\'start\')" class="btn btn-default" title="刷新商品列表"><i class="fa fa-refresh"></i></a>
</form>
<p></p>

<div id="listTable"></div>
';
}
echo '    </div>
';
if (!isset($_GET['cid'])) {
    echo '<font color="grey">提示：查看单个分类的商品列表可进行商品排序操作';
}
echo "
</div>
<script src=\"./assets/public/layer/3.4.0/layer.js\"></script>
<script src=\"./assets/js/shoplist.js?" . $jsver . "\"></script>
<script type=\"text/javascript\">
if(typeof pageLoad == \"undefined\" || pageLoad!==true){
    layer.alert(\"缺少静态js文件，请前往【修复工具】修复后再试！\");
}
</script>
";
include_once 'footer.php';
