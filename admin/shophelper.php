<?php

/**
 * 批量上架
 */

$loadGoodLib = true;
include '../includes/common.php';
$title = '批量上架';
$build = 1002;
checkLogin();
checkAuthority('super');

include './head.php';

echo '
<style>
#progressbar{padding-left: 0px;}
</style>
<link rel="stylesheet" href="' . $cdnserver . 'assets/cmui/css/load.css?' . $jsver . '" type="text/css"/>';

$mod = isset($_GET['mod']) ? daddslashes($_GET['mod']) : null;

echo '<div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top: 10px">
      <div class="block">
        <div class="block-title">
        <h3 class="panel-title">' . $title . '</h3>
        </div>
        <div class="">
        <div class="card step-progress">
        ';

$step = intval(str_ireplace('step', '', $mod));

function getDefaultPrid()
{
    //默认加价模板
    global $DB, $date;
    $data = [
        ':name' => '系统默认模板',
    ];
    $sql = "SELECT * FROM `pre_price` WHERE `name`=:name limit 1";
    $row = $DB->get_row($sql, $data);
    if (is_array($row)) {
        return $row['id'];
    } else {
        $sql = "INSERT INTO `pre_price` (`zid`,`kind`,`name`,`p_0`,`p_1`,`p_2`,`addtime`) values ('1','2','系统默认模板','30','25','20','" . $date . "')";
        $id  = $DB->insert($sql);
        if ($id) {
            //$DB->query("commit");
            return $id;
        }
        return 0;
    }
}

function saveTempData($name = null, $value = '')
{

    $dir = ROOT . 'cache/';
    if (!file_exists($dir)) {
        @mkdir($dir);
    }
    $dir = $dir . 'temp/';
    if (!file_exists($dir)) {
        @mkdir($dir);
    }

    if (!$name) {
        $name = 'cacheFile';
    }

    if (is_array($value)) {
        $value = @serialize($value);
    }
    return file_put_contents($dir . $name . '.temp', $value);
}

function getTempData($name = null)
{
    $dir = ROOT . 'cache/temp/';
    if (!$name) {
        $name = 'cacheFile';
    }
    return file_get_contents($dir . $name . '.temp');
}

function paseGoodsParams($data = [], $type = 0)
{
    $params = [];
    $input  = '';
    $inputs = [];
    if ($type == 21) {
        $data = isset($data['data']) ? $data['data'] : $data;
        foreach ($data['post'] as $key => $value) {
            $params[] = $value['param'];
            if ($key == 0) {
                $input = $value['name'];
            } else {
                $inputs[] = $value['name'];
            }
        }
    }
    return [
        'goods_param' => implode('|', $params),
        'input'       => $input,
        'inputs'      => implode('|', $inputs),
    ];
}

echo '
    <div class="alert alert-info">
        说明:使用此功能可快速一键上架货源站商品，请根据步骤操作<br>
        <span style="color:red">
        支持系统：同系统、彩虹，九五、亿樂、时空云，其他系统后续更新<br>
        注意1：如果【分类名称】前面带有----，说明此分类是属于靠前分类中名称不带----的子分类（仅同系统有）<br>
        注意2：其中同系统、彩虹、九五、亿樂可支持自动填写商品简介<br>
        注意3：由于亿樂、九五等系统均没有分类接口，无法选择分类；其中亿樂由于接口限制每个商品都得单独获取一遍，批量上架商品较多时速度会很慢<br>
        提示：已安装的批量对接插件请在【已装插件】使用，如提示【该插件不支持或不需要配置操作】请重新安装插件
        </span>
    </div>
    <div class="step-slider">
        <ul id="progressbar">
            <li class="step1 ' . ($step >= 1 || $mod == '' ? 'active' : '') . '"><span>选择货源</span></li>
            <li class="step2 ' . ($step >= 2 ? 'active' : '') . '"><span>选择分类</span></li>
            <li class="step3 ' . ($step >= 3 ? 'active' : '') . '"><span>选择商品</span></li>
            <li class="step4 ' . ($step >= 4 ? 'active' : '') . '"><span>完成上架</span></li>
        </ul>
    </div>
    ';

function display_info($tool, $type = 13)
{
    $arr = [];
    if ($type == 13) {
        //同系统
        $arr['active'] = $tool['active'] == 1 ? '<span class="btn btn-xs btn-success">上架中</span>' : '<span class="btn btn-xs btn-warning">已下架</span>';
        $arr['close']  = $tool['close'] == 0 ? '<span class="btn btn-xs btn-success">显示</span>' : '<span class="btn btn-xs btn-warning">隐藏</span>';
    } else {
        //彩虹、时空云
        $arr['active'] = '<span class="btn btn-xs btn-success">上架中</span>';
        $arr['close']  = $tool['close'] == 1 ? '<span class="btn btn-xs btn-warning">禁售中</span>' : '<span class="btn btn-xs btn-success">未禁售</span>';
    }

    return $arr['active'] . '&nbsp;' . $arr['close'];
}

function display_group_info($group)
{
    return $group['active'] == 1 ? '<span class="btn btn-xs btn-success">上架中</span>' : '<span class="btn btn-xs btn-warning">已下架</span>';
}

if ($mod == 'step1' || $mod == '') {
    $shequselect = '';
    $shequselect .= '<option value="0">请选择货源站</option>';
    $rs = $DB->query("SELECT * FROM cmy_shequ WHERE 1 order by id desc");
    if ($rs) {
        $data = $DB->fetchAll($rs);
        foreach ($data as $row) {
            $shequselect .= '<option value="' . $row['id'] . '">' . $row['url'] . '（' . $row['name'] . '）</option>';
        }
    }

    $priceselect = "<option value=\"0\" kind=\"0\">0_使用默认加价</option>";
    $rs2         = $DB->query("SELECT * FROM cmy_price order by id asc");
    if ($rs2) {
        $data2 = $DB->fetchAll($rs2);
        foreach ($data2 as $row) {
            if ($row["kind"] == 2) {
                $priceselect .= "<option value=\"" . $row["id"] . "\" kind=\"" . $row["kind"] . "\">" . $row["name"] . "(+" . $row['p_2'] . "%|+" . $row['p_1'] . "%|+" . $row['p_0'] . "%)</option>";
            } else {
                $priceselect .= "<option value=\"" . $row["id"] . "\" kind=\"" . $row["kind"] . "\">" . $row["name"] . "(+" . $row['p_2'] . "元|+" . $row['p_1'] . "元|+" . $row['p_0'] . "元)</option>";
            }
        }
    }

    echo '<form action="?mod=step2" method="POST" role="form">

                <div class="form-group">
                    <div class="input-group"><div class="input-group-addon">选择货源站</div>
                    <select class="form-control" id="shequ" name="shequ">
                    ' . $shequselect . '
                    </select>
                </div></div>
                <div class="form-group">
                    <div class="input-group"><div class="input-group-addon">选择加价模板</div>
                    <select class="form-control" id="prid" name="prid">
                    ' . $priceselect . '
                    </select>
                </div></div>
                <p><input type="submit" name="submit" onclick="this.value=\'获取数据中..耐心等待\'" class="btn btn-primary form-control" value="开始上架"/></p>
            </form>
           ';
}

if ($mod == 'step2') {
    $shequid = (int) $_POST['shequ'];
    $prid    = (int) $_POST['prid'];
    unset($_SESSION['sprid']);
    $_SESSION['sprid'] = $prid;
    $shequ             = $DB->get_row("SELECT * from `pre_shequ` where id=:shequid limit 1", [':shequid' => $shequid]);
    if ($shequ) {
        if (empty($shequ['username']) || empty($shequ['password'])) {
            showmsg("该货源站账号或密码不完整，请填写后再试！", 4);
        } else {
            if ($shequ['type'] == 21 || $shequ['type'] == 13 || $shequ['type'] == 12) {
                if (function_exists('getGoodsCategory_extend')) {
                    $result = getGoodsCategory_extend($shequ);
                    if ($result['code'] == 0) {
                        $class = $result['data'];
                        if (is_array($class)) {
                            $pclassData = [];
                            foreach ($class as $row) {
                                $pclassData[$row['cid']] = $row;
                            }
                            saveTempData('pclassData', $pclassData);
                        }
                    } else {
                        showmsg("获取分类失败，" . $result['msg'], 4);
                    }
                } else {
                    showmsg("获取分类失败，请检查该站点程序版本是否为V2.8.3及以上", 3);
                }
            } elseif ($shequ['type'] == 1) {
                //亿樂
                $mod = 'step3';
                echo '<script type="text/javascript">var $el = $(".step3");$el.addClass("active");</script>';
            } elseif ($shequ['type'] == 0 || $shequ['type'] == 2) {
                //九五
                $mod = 'step3';
                echo '<script type="text/javascript">var $el = $(".step3");$el.addClass("active");</script>';
            } else {
                showmsg("该对接平台系统类型不支持批量上架，如已购买插件，请前往【插件管理->已装插件】使用！", 4);
            }
        }
    } else {
        showmsg("该货源站不存在或不完整，请确认后再试！[" . $shequid . "]", 4);
    }

    if ($mod == "step2") {
        $classList = '';
        foreach ($class as $row) {
            $classList .= '<div class="col-xs-12 col-md-6 col-lg-4">
            <input class="inp-cmckb-xs clist" name="cid[]" id="cid' . $row['cid'] . '" type="checkbox" value="' . $row['cid'] . '" onclick="//setVal(this)" style="display: none;"/>
            <label class="cmckb-xs" for="cid' . $row['cid'] . '"><span>
              <svg width="12px" height="10px">
                <use xlink:href="#checkSvg"></use>
              </svg>
            </span><span>' . $row['name'] . (isset($row['count']) ? '(' . $row['count'] . '个)' : '') . '</span></label>
            </div>';
        }

        echo '<form action="?mod=step3" method="POST" class="form-horizontal" role="form">
        <input type="hidden" name="shequ" value="' . $shequid . '"/>
        <div class="row col-xs-12 col-lg-11" style="float:none;margin:0 auto;">
            <div class="col-xs-12">
            <input class="inp-cmckb-xs" id="SelectAllclist" type="checkbox" onclick="SelectAllClass()" style="display: none;"/>
                <label class="cmckb-xs" for="SelectAllclist"><span>
                  <svg width="12px" height="10px">
                    <use xlink:href="#checkSvg"></use>
                  </svg>
                </span><span>全部选中</span></label>
            </div>
            ' . $classList . '
            <!--SVG Sprites-->
            <svg class="inline-svg">
              <symbol id="checkSvg" viewbox="0 0 12 10">
                <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
              </symbol>
            </svg>
            <br>
        </div>
        <div class="row col-xs-12 col-lg-11" style="float:none;margin:0 auto;">
            <div class="form-group" style="display:flex;">
              <a onclick="javascript:window.history.go(-1);" href="!#" class="btn btn-info" style="width:30%">上一步</a>
              &nbsp;&nbsp;
              <input type="submit" name="submit" class="btn btn-primary" style="flex:1" value="下一步"/>
            </div>
        </div>
        ';
    }
}

if ($mod == 'step3') {
    $shequid  = intval(input('shequ'));
    $shequ    = $DB->get_row("SELECT * from `pre_shequ` where `id`=:shequid limit 1", [':shequid' => $shequid]);
    $typeList = ['0', '2', '13', '12', '21', '1']; //对接平台支持的系统类型
    if ($shequ && in_array($shequ['type'], $typeList)) {
        $ptoolsData = [];
        if ($shequ['type'] == 13 || $shequ['type'] == 12) {
            $cids       = input('cid');
            $pclassData = (array) @unserialize(getTempData('pclassData'));
            $shoplist   = '';
            foreach ($cids as $cid) {
                $shoplist .= '
            <a class="panel-title" data-toggle="collapse" data-parent="#class" href="#class_' . $cid . '"><div class="list-group-item list-group-item-success">
            <span class="pull-right"><i class="fa fa-chevron-down"></i></span>&nbsp;' . $pclassData[$cid]['name'] . '</div></a>';
                $shoplist .= '<div id="class_' . $cid . '" class="panel-collapse collapse">
            <table class="table table-bordered" style="table-layout: fixed;">
            <tbody>
            <tr><td><input class="inp-cmckb-xs" id="SelectAlltlist_' . $cid . '" type="checkbox" onclick="SelectAll(' . $cid . ',this)" style="display: none;"/>
                <label class="cmckb-xs" for="SelectAlltlist_' . $cid . '"><span>
                  <svg width="12px" height="10px">
                    <use xlink:href="#checkSvg"></use>
                  </svg>
                </span><span>全选&nbsp;ID</span></label></td><td>商品名称</td><td>状态</td><td>系统已上架</td></tr>
            ';
                $tablist = '';
                $result  = getGoods_extend($shequ, $cid);
                if (is_array($result) && $result['code'] == 0) {
                    $arr1 = $result['data'];
                    foreach ($arr1 as $tool) {
                        $isOk = $DB->get_row("SELECT tid FROM `pre_tools` WHERE `name` = :name LIMIT 1", [':name' => $tool['name']]);

                        $ptoolsData[$tool['tid']] = $tool;
                        $tablist .= '<tr><td><input class="inp-cmckb-xs class_' . $cid . '" name="tid[]" id="tid' . $tool['tid'] . '" type="checkbox" value="' . $cid . '_' . $tool['tid'] . '" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="tid' . $tool['tid'] . '"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;' . $tool['tid'] . '</span></label></td><td>' . $tool['name'] . '</td><td>' . display_info($tool, $shequ['type']) . '</td><td>' . (is_array($isOk) ? '<span style="color:green">是</span>' : '<span style="color:red">否</span>') . '</td></tr>';
                    }
                    if (empty($tablist)) {
                        $tablist .= '<tr><td><input class="inp-cmckb-xs" type="checkbox"  onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="tid' . $tool['tid'] . '"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;—</span></label></td><td>当前条件下商品为空</td><td>——</td></tr>';
                    }
                } else {
                    $tablist = '<tr><td><input class="inp-cmckb-xs" type="checkbox"  onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="tid"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;—</span></label></td><td>' . $result['msg'] . '</td><td>——</td></tr>';
                }
                $shoplist .= $tablist . '</tbody></table></div>';
            }
        } elseif ($shequ['type'] == 21) {
            $cids       = getParams('cid');
            $pclassData = (array) @unserialize(getTempData('pclassData'));
            $shoplist   = '';
            foreach ($cids as $cid) {
                $shoplist .= '
            <a class="panel-title" data-toggle="collapse" data-parent="#class" href="#class_' . $cid . '"><div class="list-group-item list-group-item-success">
            <span class="pull-right"><i class="fa fa-chevron-down"></i></span>&nbsp;' . $pclassData[$cid]['name'] . '</div></a>';
                $shoplist .= '<div id="class_' . $cid . '" class="panel-collapse collapse">
            <table class="table table-bordered" style="table-layout: fixed;">
            <tbody>
            <tr><td><input class="inp-cmckb-xs" id="SelectAlltlist_' . $cid . '" type="checkbox" onclick="SelectAll(' . $cid . ',this)" style="display: none;"/>
                <label class="cmckb-xs" for="SelectAlltlist_' . $cid . '"><span>
                  <svg width="12px" height="10px">
                    <use xlink:href="#checkSvg"></use>
                  </svg>
                </span><span>全选&nbsp;ID</span></label></td><td>商品名称</td><td>状态</td><td>系统已上架</td></tr>
            ';
                $tablist = '';

                $result = getGoods_extend($shequ, $cid);
                if (is_array($result) && $result['code'] == 0) {
                    $arr1 = $result['data'];
                    foreach ($arr1 as $tool) {
                        $tool['tid']         = $tool['id'];
                        $tool['goods_param'] = $tool['param'];
                        $tool['goods_id']    = $tool['tid'];
                        $tool['min']         = $tool['minnum'];
                        $tool['max']         = $tool['maxnum'];
                        $tool['value']       = $tool['minnum'];
                        $tool['price1']      = floatval($tool['price']);
                        if ($tool['min'] > 1) {
                            $tool['max'] = intval($tool['max'] / $tool['min']);
                            if ($tool['max'] <= 0) {
                                $tool['max'] = null;
                            }
                            $tool['min'] = 1;
                        }
                        $isOk                     = $DB->get_row("SELECT tid FROM `pre_tools` WHERE `name` = :name LIMIT 1", [':name' => $tool['name']]);
                        $ptoolsData[$tool['tid']] = $tool;
                        $tablist .= '<tr><td><input class="inp-cmckb-xs class_' . $cid . '" name="tid[]" id="tid' . $tool['tid'] . '" type="checkbox" value="' . $cid . '_' . $tool['tid'] . '" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="tid' . $tool['tid'] . '"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;' . $tool['tid'] . '</span></label></td><td>' . $tool['name'] . '</td><td>' . display_info($tool, 12) . '</td><td>' . (is_array($isOk) ? '<span style="color:green">是</span>' : '<span style="color:red">否</span>') . '</td></tr>';
                    }

                    if (empty($tablist)) {
                        $tablist .= '<tr><td><input class="inp-cmckb-xs" type="checkbox"  onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="tid' . $tool['tid'] . '"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;—</span></label></td><td>当前条件下商品为空</td><td>——</td></tr>';
                    }
                } else {
                    $tablist = '<tr><td><input class="inp-cmckb-xs" type="checkbox"  onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="tid"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;—</span></label></td><td>' . $result['msg'] . '</td><td>——</td></tr>';
                }
                $shoplist .= $tablist . '</tbody></table></div>';
            }
        } else {
            if ($shequ['type'] == 1) {
                $result = getGoods_extend($shequ);
                $arr1   = [];
                foreach ($result['data'] as $item) {
                    $arr1[] = array(
                        'tid'    => $item['id'],
                        'name'   => $item['name'],
                        'active' => $item['close'] == 1 ? 0 : 1, //上下架
                        'close'  => 0, //默认显示
                    );
                }
            } elseif ($shequ['type'] == 0 || $shequ['type'] == 2) {
                $result = getGoods_extend($shequ);
                $arr1   = [];
                foreach ($result['data'] as $item) {
                    $arr1[] = array(
                        'tid'        => $item['id'],
                        'name'       => $item['name'],
                        'goods_type' => $item['type'],
                        'price'      => $item['price'],
                        'minnum'     => $item['minnum'],
                        'maxnum'     => $item['maxnum'],
                        'active'     => $item['close'] == 1 ? 0 : 1, //上下架
                        'close'      => 0, //默认显示
                    );
                }
            } elseif ($shequ['type'] == 12) {
                $result = getGoods_extend($shequ);
                $arr1   = [];
                foreach ($result['data'] as $item) {
                    $arr1[] = array(
                        'tid'        => $item['tid'],
                        'name'       => $item['name'],
                        'goods_type' => $item['type'],
                        'price'      => $item['price'],
                        'active'     => $item['close'] == 1 ? 0 : 1, //上下架
                        'close'      => $item['close'], //显示状态
                    );
                }
            } else {
                showmsg("该对接平台系统类型不支持批量上架，如已购买插件，请前往【插件管理->已装插件】使用！", 4);
            }

            $shoplist = '
                <a class="panel-title" data-toggle="collapse" data-parent="#shop" href="#shopbox"><div class="list-group-item list-group-item-success">
            <span class="pull-right"><i class="fa fa-chevron-down"></i></span>&nbsp;点我选择要上架的商品【共' . count($arr1) . '条】</div></a>
                <div id="shopbox" class="panel-collapse collapse">
                <table class="table table-bordered" style="table-layout: fixed;">
                    <tbody>
                    <tr><td><input class="inp-cmckb-xs" id="SelectAlltlist_' . $shequid . '" type="checkbox" onclick="SelectAllOther(\'shequ_' . $shequid . '\',this)" style="display: none;"/>
                    <label class="cmckb-xs" for="SelectAlltlist_' . $shequid . '"><span>
                      <svg width="12px" height="10px">
                        <use xlink:href="#checkSvg"></use>
                      </svg>
                    </span><span>全选&nbsp;ID</span></label></td><td>商品名称</td><td>状态</td><td>系统已上架</td></tr>
                    ';

            if (is_array($result) && $result['code'] == 0) {
                foreach ($arr1 as $tool) {
                    $isOk = $DB->get_row("SELECT tid FROM `pre_tools` WHERE `name` = :name LIMIT 1", [':name' => $tool['name']]);

                    $ptoolsData[$tool['tid']] = $tool;
                    $tablist .= '<tr><td><input class="inp-cmckb-xs shequ_' . $shequid . '" name="tid[]" id="tid' . $tool['tid'] . '" type="checkbox" value="' . $tool['tid'] . '" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="tid' . $tool['tid'] . '"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;' . $tool['tid'] . '</span></label></td><td>' . $tool['name'] . '</td><td>' . display_info($tool) . '</td><td>' . (is_array($isOk) ? '<span style="color:green">是</span>' : '<span style="color:red">否</span>') . '</td></tr>';
                }
                if (empty($tablist)) {
                    $tablist .= '<tr><td><input class="inp-cmckb-xs" type="checkbox"  onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="tid"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;—</span></label></td><td>当前条件下商品为空</td><td>——</td></tr>';
                }
            } else {
                $tablist = '<tr><td><input class="inp-cmckb-xs" type="checkbox"  onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="tid"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;—</span></label></td><td>' . $result['msg'] . '</td><td>——</td></tr>';
            }
            $shoplist .= $tablist . '</tbody></table></div>';

            //数量列表
            $numlist = '
                <a class="panel-title" data-toggle="collapse" data-parent="#num" href="#numbox"><div class="list-group-item list-group-item-success">
            <span class="pull-right"><i class="fa fa-chevron-down"></i></span>&nbsp;点我选择要上架的数量【共14条】</div></a>
                <div id="numbox" class="panel-collapse collapse">
                <table class="table table-bordered" style="table-layout: fixed;">
                    <tbody>
                    <tr><td colspan="3">说明1：表示要对接的份数，例：客户下单是1份，数量是10，对接时系统自动下单10份，以此类推</td></tr>
                    <tr><td colspan="3">说明2：部分对接商品名称可能不符合实际数量情况，系统无法完全识别，上架完后请手动调整</td></tr>
                    <tr><td colspan="3">说明3：如果所选上架商品的数量大于对接的最大数量或小于对接的最小数量，则此数量不上架！</td></tr>
                    <tr><td><input class="inp-cmckb-xs" id="SelectNum_' . $shequid . '" type="checkbox" onclick="SelectAllOther(\'num_' . $shequid . '\',this)" style="display: none;"/>
                    <label class="cmckb-xs" for="SelectNum_' . $shequid . '"><span>
                      <svg width="12px" height="10px">
                        <use xlink:href="#checkSvg"></use>
                      </svg>
                    </span><span>全选&nbsp;</span></label></td><td>数量类型</td><td>单位(可自定义)</td>
                    </tr>
                    <tr><td><input class="inp-cmckb-xs num_' . $shequid . '" name="shopnum[]" id="num_1" type="checkbox" value="1" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="num_1"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;1个</span></label></td><td>1</td><td><input class="inp-xs" name="shopunit[1]" type="text" value="个"/></td></tr>
                    <tr><td><input class="inp-cmckb-xs num_' . $shequid . '" name="shopnum[]" id="num_5" type="checkbox" value="5" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="num_1"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;5个</span></label></td><td>5</td><td><input class="inp-xs" name="shopunit[5]" type="text" value="个"/></td></tr>
                    <tr><td><input class="inp-cmckb-xs num_' . $shequid . '" name="shopnum[]" id="num_10" type="checkbox" value="10" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="num_10"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;10个</span></label></td><td>10</td><td><input class="inp-xs" name="shopunit[10]" type="text" value="个"/></td></tr>
                    <tr><td><input class="inp-cmckb-xs num_' . $shequid . '" name="shopnum[]" id="num_15" type="checkbox" value="15" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="num_15"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;15个</span></label></td><td>15</td><td><input class="inp-xs" name="shopunit[15]" type="text" value="个"/></td></tr>
                    <tr><td><input class="inp-cmckb-xs num_' . $shequid . '" name="shopnum[]" id="num_20" type="checkbox" value="20" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="num_20"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;20个</span></label></td><td>20</td><td><input class="inp-xs" name="shopunit[20]" type="text" value="个"/></td></tr>
                    <tr><td><input class="inp-cmckb-xs num_' . $shequid . '" name="shopnum[]" id="num_25" type="checkbox" value="25" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="num_25"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;25个</span></label></td><td>25</td><td><input class="inp-xs" name="shopunit[25]" type="text" value="个"/></td></tr>
                    <tr><td><input class="inp-cmckb-xs num_' . $shequid . '" name="shopnum[]" id="num_50" type="checkbox" value="50" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="num_50"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;50个</span></label></td><td>50</td><td><input class="inp-xs" name="shopunit[50]" type="text" value="个"/></td></tr>
                    <tr><td><input class="inp-cmckb-xs num_' . $shequid . '" name="shopnum[]" id="num_100" type="checkbox" value="100" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="num_100"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;100个</span></label></td><td>100</td><td><input class="inp-xs" name="shopunit[100]" type="text" value="个"/></td></tr>
                    <tr><td><input class="inp-cmckb-xs num_' . $shequid . '" name="shopnum[]" id="num_1000" type="checkbox" value="1000" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="num_1000"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;1000个</span></label></td><td>1000</td><td><input class="inp-xs" name="shopunit[1000]" type="text" value="个"/></td></tr>
                    <tr><td><input class="inp-cmckb-xs num_' . $shequid . '" name="shopnum[]" id="num_5000" type="checkbox" value="5000" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="num_5000"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;5000个</span></label></td><td>5000</td><td><input class="inp-xs" name="shopunit[5000]" type="text" value="个"/></td></tr>
                    <tr><td><input class="inp-cmckb-xs num_' . $shequid . '" name="shopnum[]" id="num_10000" type="checkbox" value="10000" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="num_10000"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;10000个</span></label></td><td>10000</td><td><input class="inp-xs" name="shopunit[10000]" type="text" value="个"/></td></tr>
                    <tr><td><input class="inp-cmckb-xs num_' . $shequid . '" name="shopnum[]" id="num_100000" type="checkbox" value="100000" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="num_100000"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;100000个</span></label></td><td>100000</td><td><input class="inp-xs" name="shopunit[100000]" type="text" value="个"/></td></tr>
                    <tr><td><input class="inp-cmckb-xs num_' . $shequid . '" name="shopnum[]" id="num_500000" type="checkbox" value="500000" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="num_500000"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;500000个</span></label></td><td>500000</td><td><input class="inp-xs" name="shopunit[500000]" type="text" value="个"/></td></tr>
                    <tr><td><input class="inp-cmckb-xs num_' . $shequid . '" name="shopnum[]" id="num_1000000" type="checkbox" value="1000000" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="num_1000000"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;1000000个</span></label></td><td>1000000</td><td><input class="inp-xs" name="shopunit[1000000]" type="text" value="个"/></td></tr>
                    </tbody></table></div>
                    ';

            //分类列表
            $priceselect = "<option value=\"0\" kind=\"0\"></option>";
            $rsfl        = $DB->query("SELECT * FROM cmy_class order by sort asc");
            $rows        = [];
            if ($rsfl) {
                $rows = $DB->fetchAll($rsfl);
            }

            $grouplist = '
                <a class="panel-title" data-toggle="collapse" data-parent="#fl" href="#flbox"><div class="list-group-item list-group-item-success">
            <span class="pull-right"><i class="fa fa-chevron-down"></i></span>&nbsp;点我选择要放置的分类【共' . count($rows) . '条】</div></a>
                <div id="flbox" class="panel-collapse collapse">
                <table class="table table-bordered" style="table-layout: fixed;">
                    <tbody>
                    <tr><td><input class="inp-cmckb-xs" id="SelectGroup_' . $shequid . '" type="checkbox" onclick="SelectAllOther(\'group_' . $shequid . '\',this)" style="display: none;"/>
                    <label class="cmckb-xs" for="SelectGroup_' . $shequid . '"><span>
                      <svg width="12px" height="10px">
                        <use xlink:href="#checkSvg"></use>
                    </svg>
                    </span><span>全选&nbsp;CID</span></label></td><td>分类名称</td><td>状态</td></tr>
                    ';
            foreach ($rows as $row) {
                $grouplist .= '<tr><td><input class="inp-cmckb-xs group_' . $shequid . '" name="group[]" id="cid' . $row['cid'] . '" type="checkbox" value="' . $row['cid'] . '" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="cid' . $row['cid'] . '"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;' . $row['cid'] . '</span></label></td><td>' . $row['name'] . '</td><td>' . display_group_info($row) . '</td></tr>';
            }

            if (count($rows) == 0) {
                $grouplist .= '<tr><td><input class="inp-cmckb-xs" type="checkbox" id="cid" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="cid"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;—</span></label></td><td>商品分类为空，请先创建分类后再试</td><td>——</td></tr>';
            }

            $grouplist .= '</tbody></table></div>';

            $optionlist = '
                <a class="panel-title" data-toggle="collapse" data-parent="#option" href="#optionbox"><div class="list-group-item list-group-item-success">
            <span class="pull-right"><i class="fa fa-chevron-down"></i></span>&nbsp;点我配置其他属性</div></a>
                <div id="optionbox" class="panel-collapse collapse">
                <table class="table table-bordered" style="table-layout: fixed;">
                    <tbody>
                    <tr><td>属性名称</td><td>属性值</td><td>说明</td></tr>
                    <tr><td>价格低于0时价格设置为0.01</td><td><input class="inp-xs option_' . $shequid . '" name="option[reset]" id="opt_reset" type="text" value="1"/></td><td>如填：1 ，1 是 0 否 ，留空表示否</td></tr>
                    <tr><td>多个数量后增加商品作为分割线</td><td><input class="inp-xs option_' . $shequid . '" name="option[division]" id="opt_division" type="text" value="---分割线---"/></td><td>如：---分割线--- ，留空表示不用分割线</td></tr>
                    <tr><td>是否不显示数量和单位</td><td><input class="inp-xs option_' . $shequid . '" name="option[show_unit]" id="opt_show_unit" type="text" value="1"/></td><td>如填：1 ，1 是 0 否 ，留空表示否</td></tr>
                    <tr><td>商品名称和数量连接分隔符</td><td><input class="inp-xs option_' . $shequid . '" name="option[separate]" id="opt_separate" type="text" value="-"/></td><td>如：-、->、| ，留空表示不用分隔连接符</td></tr>
                    <tr><td colspan="3">更多属性配置等待后续开发...</td></tr>
                    </tbody></table></div>
                    ';
        }
        saveTempData('ptoolsData', $ptoolsData);
    } elseif (!in_array($shequ['type'], $typeList)) {
        showmsg("该对接站系统类型不支持，请确认后再试！", 4);
    } else {
        showmsg("该货源站不存在或不完整，请确认后再试！", 4);
    }

    echo '<form action="?mod=step4" method="POST" class="form-horizontal" role="form">
        <input type="hidden" name="shequ" value="' . $shequid . '"/>
        <div class="row col-xs-12 col-lg-11" style="float:none;margin:0 auto;">
            ' . $shoplist . '
            ' . (isset($numlist) ? '<br>' . $numlist : null) . '
            ' . (isset($grouplist) ? '<br>' . $grouplist : null) . '
            ' . (isset($optionlist) ? '<br>' . $optionlist : null) . '
            <!--SVG Sprites-->
            <svg class="inline-svg">
              <symbol id="checkSvg" viewbox="0 0 12 10">
                <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
              </symbol>
            </svg>
            <br>
        </div>
        <div class="row col-xs-12 col-lg-11" style="float:none;margin:0 auto;">
            <div class="form-group" style="display:flex;">
              <a onclick="javascript:window.history.go(-1);" href="!#" class="btn btn-info" style="width:30%">上一步</a>
              &nbsp;&nbsp;
              <input type="submit" name="submit" class="btn btn-primary" style="flex:1" value="下一步"/>
            </div>
        </div>
        ';
} elseif ($mod == 'step4') {
    $shequid  = (int) $_POST['shequ'];
    $shequ    = $DB->get_row("SELECT * from cmy_shequ where id=:shequid limit 1", [':shequid' => $shequid]);
    $count1   = 0;
    $count2   = 0;
    $succ1    = 0;
    $succ2    = 0;
    $error1   = '';
    $error2   = '';
    $typeList = ['0', '2', '13', '12', '21', '1']; //对接平台支持的系统类型
    if ($shequ && in_array($shequ['type'], $typeList)) {
        $ptoolsData = (array) @unserialize(getTempData('ptoolsData'));

        $prid = $_SESSION['sprid'];
        $prow = $DB->get_row("SELECT * from cmy_price where id=:prid limit 1", [':prid' => $prid]);
        if ($prid < 1 || !$prow) {
            $prow   = getDefaultPrid();
            $p_kind = 2;
            $p_0    = 18;
            $p_1    = 15;
            $p_2    = 11;
        } else {
            $p_kind = $prow['kind'];
            $p_0    = $prow['p_0'];
            $p_1    = $prow['p_1'];
            $p_2    = $prow['p_2'];
        }

        if ($shequ['type'] == 13 || $shequ['type'] == 12 || $shequ['type'] == 21) {
            $is_group   = true;
            $pclassData = (array) @unserialize(getTempData('pclassData'));
            $tids       = input('tid');
            foreach ($tids as $key => $value) {
                $arr   = explode('_', $value);
                $scid  = $arr[0];
                $stid  = $arr[1];
                $cname = $pclassData[$scid]['name'];
                $cname = str_ireplace('----', '', $cname);
                $ctool = $ptoolsData[$stid];
                $row1  = $DB->get_row("SELECT * from cmy_class where name= ? limit 1", [$cname]);
                if ($row1) {
                    $cid = $row1['cid'];
                } else {
                    $cid = $DB->insert("INSERT INTO `pre_class` (`name`,`active`) VALUES( ?,'1')", [$cname]);
                    $count1++;
                    if ($cid) {
                        $succ1++;
                    } else {
                        $error1 .= $cname . '[' . $scid . ']=>' . $DB->error() . '<br>';
                    }
                }
                if ($cid > 0) {
                    if (!isset($ctool['close_alert'])) {
                        $ctool['close_alert'] = '';
                    }
                    if (!isset($ctool['result'])) {
                        $ctool['result'] = '';
                    }
                    if ($shequ['type'] == 12 || $shequ['type'] == 21) {
                        $price1 = $ctool['price'];
                        $price  = $p_kind == 2 ? sprintf('%.2f', $ctool['price'] + $ctool['price'] * $p_0 / 100) : sprintf('%.2f', $p_0 + $ctool['price']);
                        $cost   = $p_kind == 2 ? sprintf('%.2f', $ctool['price'] + $ctool['price'] * $p_1 / 100) : sprintf('%.2f', $p_1 + $ctool['price']);
                        $cost2  = $p_kind == 2 ? sprintf('%.2f', $ctool['price'] + $ctool['price'] * $p_2 / 100) : sprintf('%.2f', $p_2 + $ctool['price']);
                    } else {
                        $price1 = $ctool['price'];
                        if ($price == 0) {
                            $price = $p_kind == 2 ? sprintf('%.2f', $price1 + $price1 * $p_0 / 100) : sprintf('%.2f', $p_0 + $price1);
                        }
                        if ($cost == 0) {
                            $cost = $p_kind == 2 ? sprintf('%.2f', $price1 + $price1 * $p_1 / 100) : sprintf('%.2f', $p_1 + $price1);
                        }
                        if ($cost2 == 0) {
                            $cost2 = $p_kind == 2 ? sprintf('%.2f', $price1 + $price1 * $p_2 / 100) : sprintf('%.2f', $p_2 + $price1);
                        }
                    }

                    if (preg_match('/^http/i', trim($ctool['shopimg']))) {
                        $shopimg = trim($ctool['shopimg']);
                    } else {
                        $url     = shequ_url_parse($shequ);
                        $shopimg = $url . $ctool['shopimg'];
                    }

                    $shequid  = $ctool['shequ'] ? intval($ctool['shequ']) : $shequid;
                    $goods_id = $ctool['goods_id'] ? addslashes($ctool['goods_id']) : addslashes($stid);
                    $multi    = isset($ctool['max']) && $ctool['max'] > 0 ? 1 : intval($ctool['multi']);
                    $value    = $ctool['value'] ? intval($ctool['value']) : 1;
                    if (isset($ctool['min']) && $ctool['min'] > 0) {
                        $multi = 1;
                    }

                    $goods_param = $ctool['goods_param'] ? addslashes($ctool['goods_param']) : '';
                    $sql         = "INSERT INTO `pre_tools` (`cid`,`prid`,`name`,`price1`,`price`,`cost`,`cost2`,`prices`,`input`,`inputs`,`value`,`desc`,`alert`,`result`,`shopimg`,`min`,`max`,`is_curl`,`is_rank`,`repeat`,`multi`,`validate`,`shequ`,`goods_id`,`goods_param`,`close`,`sort`,`active`,`close_alert`) values ('" . intval($cid) . "','" . $prid . "','" . addslashes($ctool['name']) . "','" . $price1 . "','" . $price . "','" . $cost . "','" . $cost2 . "','" . addslashes($ctool['prices']) . "','" . addslashes($ctool['input']) . "','" . addslashes($ctool['inputs']) . "','" . $value . "','" . addslashes($ctool['desc']) . "','" . addslashes($ctool['alert']) . "','" . addslashes($ctool['result']) . "','" . $shopimg . "','" . intval($ctool['min']) . "','" . intval($ctool['max']) . "','2','1','1','" . $multi . "','" . intval($ctool['validate']) . "','" . $shequid . "','" . $goods_id . "','" . $goods_param . "','" . intval($ctool['close']) . "','" . intval($ctool['sort']) . "','" . intval($ctool['active']) . "','" . addslashes($ctool['close_alert']) . "')";
                    $count2++;
                    if ($tid = $DB->insert($sql)) {

                        //更新排序
                        if ($ctool['sort'] == 0) {
                            $DB->query("UPDATE `pre_tools` set `sort`='{$tid}' WHERE `tid`='{$tid}'");
                        }

                        // 商品置顶
                        resetGoodsSort($tid);
                        $succ2++;
                    } else {
                        $error2 .= $ctool['name'] . '[' . $ctool['tid'] . '] => ' . $DB->error() . '；SQL => ' . $sql . '<br>';
                    }
                }
            }
        } else {
            $is_group = false;
            $group    = input('post.group', 1);
            if (!is_array($group) || count($group) == 0) {
                showmsg("未选择要放置的商品分类，请返回重新选择后再操作！", 4);
            } else {
                $tools    = getParams('tid');
                $shopunit = getParams('shopunit');
                $shopnum  = getParams('shopnum');
                $option   = getParams('option');
                if (!is_array($tools)) {
                    showmsg("商品信息不完整或无法解析，请重试！多次出现反馈给开发者", 4);
                }

                if (!is_array($shopnum)) {
                    showmsg("要上架的数量信息不完整或无法解析，请重试！多次出现反馈给开发者", 4);
                }

                if (count($shopnum) == 0) {
                    $shopnum[] = '1';
                }

                foreach ($tools as $goods_id) {
                    $tool            = $ptoolsData[$goods_id];
                    $tool['shopimg'] = '';
                    //exit(json_encode($tool));
                    if ($shequ['type'] == 1) {
                        $result = getGoodsParams_extend($shequ, $goods_id);
                        if ($result['code'] == 0) {
                            $tool['price']   = $result['data']['price'];
                            $tool['desc']    = $result['data']['desc'];
                            $tool['minnum']  = $result['data']['limit_min'];
                            $tool['maxnum']  = $result['data']['limit_max'];
                            $tool['shopimg'] = isset($result['data']['image']) ? $result['data']['image'] : '';
                            $inputs          = '';
                            foreach ($result['data']['inputs'] as $key => $item) {
                                if ($key == 0) {
                                    continue;
                                }

                                $inputs .= $item[0] . '|';
                            }
                            $tool['inputs'] = trim($inputs, "|");
                        } else {
                            $error2 .= $tool['name'] . '[' . $goods_id . '] => 获取商品详情失败，' . $result['msg'] . '<br>';
                            continue;
                        }
                    } elseif ($shequ['type'] == 0 || $shequ['type'] == 2) {
                        $result = getGoodsParams_extend($shequ, $goods_id);
                        if ($result['code'] == 0) {
                            $tool['desc']        = $result['desc'];
                            $tool['goods_param'] = $result['param'];
                            $tool['input']       = explode('|', $result['inputs'])[0];
                            $inputs              = '';
                            if (stripos($result['inputs'], "|") !== false) {
                                $inputs = str_replace($tool['input'] . "|", '', $result['inputs']);
                            }
                            $tool['inputs'] = $inputs;
                        } else {
                            $error2 .= $tool['name'] . '[' . $goods_id . '] => 获取商品详情失败，' . $result['msg'] . '<br>';
                            continue;
                        }
                    } elseif ($shequ['type'] == 12) {
                        $result = getGoodsParams_extend($shequ, $goods_id);
                        if ($result['code'] == 0) {
                            $tool['price']   = $result['data']['price'];
                            $tool['desc']    = $result['data']['desc'];
                            $tool['input']   = $result['data']['input'];
                            $tool['inputs']  = $result['data']['inputs'];
                            $tool['value']   = $result['data']['value'];
                            $tool['minnum']  = $result['data']['min'];
                            $tool['maxnum']  = $result['data']['max'];
                            $tool['shopimg'] = isset($result['data']['shopimg']) ? $result['data']['shopimg'] : '';
                        } else {
                            $error2 .= $tool['name'] . '[' . $goods_id . '] => 获取商品详情失败，' . $result['msg'] . '<br>';
                            continue;
                        }
                    }

                    if (isset($tool['maxnum']) && $tool['maxnum'] > 1) {
                        $multi = 1;
                    } else {
                        $multi          = 0;
                        $tool['maxnum'] = 1;
                    }

                    $i = 1;

                    foreach ($shopnum as $value) {
                        if ($value < 1) {
                            $value = 1;
                        }
                        $count2++;
                        if ($tool['maxnum'] >= 1 && $value > $tool['maxnum']) {
                            $error2 .= $tool['name'] . '[' . $goods_id . '] => 上架数量' . $value . '大于最大数量' . $tool['maxnum'] . '<br>';
                            continue;
                        }

                        if ($tool['minnum'] > 1 && $value < $tool['minnum']) {
                            $error2 .= $tool['name'] . '[' . $goods_id . '] => 上架数量' . $value . '小于最小数量' . $tool['minnum'] . '<br>';
                            continue;
                        }

                        if ($shequ['type'] == 12) {
                            $minnum = 1;

                            if ($tool['minnum'] > 1) {
                                $minnum = $tool['minnum'];
                            }

                            $maxnum = 1;

                            if ($tool['maxnum'] >= 1) {
                                $maxnum = $tool['maxnum'];
                            }

                            // if ($value % $tool['value'] == 0) {
                            //     $value = floor($value / $tool['value']);
                            // } else {
                            //     $error2 .= $tool['name'] . '[' . $goods_id . '] => 上架数量不是当前商品对接数量的整倍数【】<br>';
                            //     continue;
                            // }

                            $num = $value * $tool['value'];
                            // 当对接商品标题是 100个 最大10份
                            // 而本地上架10份，最终则是 1000个 最大对接数量则是10/10

                            if ($num > $tool['value']) {
                                $maxnum = floor($maxnum / $value);
                            }
                        } else {
                            $minnum = 1;
                            if ($tool['minnum'] > 1) {
                                $minnum = floor($tool['minnum'] / $value);
                            }

                            $maxnum = 1;

                            if ($tool['maxnum'] > 1) {
                                $maxnum = floor($tool['maxnum'] / $value);
                            }
                        }

                        if ($option['show_unit'] == 1) {
                            $name = $tool['name'] . $option['separate'] . $value;
                            $unit = $shopunit[$value];
                            if ($unit !== "" && !is_numeric($unit)) {
                                $name = $name . $unit;
                            }
                        } else {
                            $name = $tool['name'];
                        }

                        $price1 = $tool['price'] * $value;

                        $price = sprintf('%.6f', $p_kind == 2 ? $price1 + $price1 * $p_0 / 100 : $p_0 + $price1);
                        $cost  = sprintf('%.6f', $p_kind == 2 ? $price1 + $price1 * $p_1 / 100 : $p_1 + $price1);
                        $cost2 = sprintf('%.6f', $p_kind == 2 ? $price1 + $price1 * $p_2 / 100 : $p_2 + $price1);

                        if ($price < 0.01 && $option['reset'] == 1) {
                            $price = 0.01;
                        }

                        if ($cost < 0.01 && $option['reset'] == 1) {
                            $cost = 0.01;
                        }

                        if ($cost2 < 0.01 && $option['reset'] == 1) {
                            $cost2 = 0.01;
                        }

                        $sql  = "INSERT INTO `pre_tools` (`cid`,`prid`,`name`,`price1`,`price`,`cost`,`cost2`,`prices`,`input`,`inputs`,`value`,`desc`,`shopimg`,`min`,`max`,`is_curl`,`is_rank`,`repeat`,`multi`,`validate`,`shequ`,`goods_id`,`goods_type`,`goods_param`,`close`,`active`) values (':cid','" . $prid . "','" . $name . "','" . $price1 . "','" . $price . "','" . $cost . "','" . $cost2 . "','','" . addslashes($tool['input']) . "','" . addslashes($tool['inputs']) . "','" . $value . "','" . addslashes($tool['desc']) . "','" . $tool['shopimg'] . "','" . $minnum . "','" . $maxnum . "','2','1','1','" . $multi . "','0','" . $shequid . "','" . $goods_id . "','" . addslashes($tool['goods_type']) . "','" . addslashes($tool['goods_param']) . "','0','" . intval($tool['active']) . "')";
                        $sql2 = '';
                        if ($i === count($shopnum) && !empty($option['division'])) {
                            $sql2 = "INSERT INTO `pre_tools` (`cid`,`name`,`price1`,`price`,`cost`,`cost2`,`close`,`active`) values (':cid','" . $option['division'] . "','888','8888','8888','8888','0','0')";
                        }

                        foreach ($group as $cid) {
                            $sql = str_replace(":cid", $cid, $sql);
                            if ($tid = $DB->insert($sql)) {
                                //更新排序
                                $DB->query("UPDATE `pre_tools` set `sort`='{$tid}' WHERE `tid`='{$tid}'");
                                if ($i === count($shopnum) && $sql2) {
                                    $sql2 = str_replace(":cid", $cid, $sql2);
                                    $tid  = $DB->insert($sql2);
                                    if ($tid) {
                                        //更新排序
                                        $DB->query("UPDATE `pre_tools` set `sort`='{$tid}' WHERE `tid`='{$tid}'");
                                        // 商品置顶
                                        resetGoodsSort($tid);
                                    }
                                }
                                $succ2++;
                            } else {
                                $error2 .= $name . '[' . $goods_id . '] => ' . $DB->error() . '；SQL => ' . $sql . '<br>';
                            }
                        }
                        $i++;
                    }
                }
            }
        }
    } elseif (!is_array($shequ)) {
        showmsg("该货源站不存在或不完整，请确认后再试！", 4);
    } else {
        showmsg("该对接站系统类型不支持，请确认后再试！", 4);
    }

    echo '<div class="row col-xs-12 col-lg-11 col-lg-9" style="float:none;margin:0 auto;">
          <h3 style="color:red;text-align:center">恭喜，您已完成批量上架！</h3>';

    if ($is_group) {
        echo '<p>分类：需上架' . $count1 . '个，成功' . $succ1 . '个（如果已上架一样的，将不计数）<p>
            <p>分类上架错误日志：' . $error1 . '<p>
            <hr style="height:2px"/>';
    }

    echo '<p>商品：需上架' . $count2 . '个，成功' . $succ2 . '个<p>
            <p>商品上架错误日志：' . $error2 . '<p>
            <br>
         </div>
          <p><a href="?mod=step1" class="btn btn-primary form-control">点我重新上架</a></p>
         ';
}

echo ' </div>
      </div>
    </div>
  </div>';
echo <<<'jsjb'
    <script>
    var checkedZt = false;
    function SelectAllOther(type, chkAll){
        var items = $('.'+type);
        for (i = 0; i < items.length; i++) {
            if (items[i].type == "checkbox") {
                items[i].checked = chkAll.checked;
            }
        }
        return true;
    }

    function SelectAll(cid,chkAll) {
        var items = $('.class_'+cid);
        for (i = 0; i < items.length; i++) {
            if (items[i].id.indexOf("tid") != -1 && items[i].type == "checkbox") {
                items[i].checked = chkAll.checked;
            }
        }
        return true;
    }

    function SelectAllClass() {
        var items = $('.clist');
        for (i = 0; i < items.length; i++) {
            if (items[i].id.indexOf("cid") != -1 && items[i].type == "checkbox") {
                items[i].checked = !checkedZt;
            }
        }
        checkedZt = !checkedZt;
    }
    </script>
jsjb;
echo '<script>var build="' . $build . '";</script>';
include_once 'footer.php';
