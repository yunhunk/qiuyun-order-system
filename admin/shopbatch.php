<?php

/**
 * 批量上架新版
 */

$loadGoodLib = true;
include '../includes/common.php';
$title = '批量上架新版';
$build = 1000;
checkLogin();
checkAuthority('super');

$act = isset($_GET['act']) ? input('get.act') : null;

if ($act == 'shoplist') {
    $shequid = intval(input('shequid'));
    // -1 全部 0 已下架 1 上架中
    $active = intval(input('active'));
    // -1 全部 0 未对接 1 已对接
    $have = intval(input('have'));
    // -1 全部 0 今天 1 昨天
    $time = intval(input('time'));
    if (!$shequid) {
        json_error('对接站点不能为空');
    }

    $cid = intval(input('cid'));

    $pagesize = intval(input('pagesize'));
    if (!$pagesize) {
        $pagesize = 100;
    }

    $search = input('search');

    $shequ = $DB->get_row("SELECT * from `pre_shequ` where id=:shequid limit 1", [':shequid' => $shequid]);
    if ($shequ) {
        if ($shequ['type'] == 21 || $shequ['type'] == 13 || $shequ['type'] == 12) {
            $result = getGoods_extend($shequ, $cid);
            if ($result['code'] == 0) {
                $data = [];
                foreach ($result['data'] as $key => $value) {
                    if (count($data) >= $pagesize) {
                        break;
                    }

                    $add = 0;
                    if ($active > -1) {
                        if ($active == 0) {
                            $add = isset($value['close']) && $value['close'] == 1 ? 1 : 0;
                        } elseif ($active == 1) {
                            $add = isset($value['close']) && $value['close'] == 0 ? 1 : 0;
                        }
                    } else {
                        $add = 1;
                    }

                    // if ($time > -1 ) {
                    //     if ($time == 0) {
                    //         // 今天
                    //         $add = isset($value['close']) && $value['close'] == 1 ? 1 : 0;
                    //     } elseif ($time == 1) {
                    //         // 昨天
                    //         $add = isset($value['close']) && $value['close'] == 0 ? 1 : 0;
                    //     }
                    // } else {
                    //     $add = 1;
                    // }

                    $value['have'] = \core\Db::name('tools')->find(['goods_id' => $value['tid'], 'shequ' => $shequid]) ? 1 : 0;

                    if ($have > -1) {
                        if ($have == 0) {
                            $add = !$value['have'] && $add == 1 ? 1 : 0;
                        } elseif ($have == 1 && $value['have']) {
                            $add = $value['have'] && $add == 1 ? 1 : 0;
                        }
                    } else {
                        $add = 1;
                    }

                    // 搜索关键词
                    if ($search && $add == 1) {
                        $add = stripos($value['name'], $search) === false ? 0 : 1;
                    }

                    if ($add == 1) {
                        $data[] = $value;
                    }
                }
                json_success('成功', $data, ['post' => input('post.'), 'result' => $result]);
            } else {
                json_error("查询商品列表失败，" . $result['msg']);
            }
        } else {
            json_error('该对接站点不支持批量');
        }
    } else {
        json_error('该对接站点不存在');
    }
} elseif ($act == 'class') {
    $shequid = input('shequid');

    if (!$shequid) {
        json_error('对接站点不能为空');
    }
    $shequ = $DB->get_row("SELECT * from `pre_shequ` where id=:shequid limit 1", [':shequid' => $shequid]);
    if ($shequ) {
        if ($shequ['type'] == 21 || $shequ['type'] == 13 || $shequ['type'] == 12) {
            $result = getGoodsCategory_extend($shequ);
            if ($result['code'] == 0) {
                json_success('成功', $result['data']);
            } else {
                json_error("查询分类失败，" . $result['msg']);
            }
        } else {
            json_error('该对接站点不支持批量');
        }
    } else {
        json_error('该对接站点不存在');
    }
} elseif ($act == 'batch') {
    $shequid = input('shequid');

    if (!$shequid) {
        json_error('对接站点不能为空');
    }

    $cid = intval(input('localcid'));
    if (!$cid) {
        json_error('本地分类不能为空');
    }

    $classrow = $DB->get_row("SELECT * from cmy_class where cid=:cid limit 1", [':cid' => $cid]);
    if (!$classrow) {
        json_error('本地分类不存在=>' . $cid);
    }

    $tids = input('tids');
    if (!$tids) {
        json_error('对接的商品列表不能为空');
    }

    $pagesize = intval(input('pagesize'));
    if (!$pagesize) {
        $pagesize = 100;
    }

    $shequ = $DB->get_row("SELECT * from cmy_shequ where id=:shequid limit 1", [':shequid' => $shequid]);
    if (!$shequ) {
        json_error('站点不存在');
    }

    $prid = intval(input('prid'));
    if (!$prid) {
        json_error('加价模板不能为空');
    }

    $prow = $DB->get_row("SELECT * from cmy_price where id=:prid limit 1", [':prid' => $prid]);
    if (!$prow && $prid > 0) {
        json_error('加价模板不存在 => ' . $prid);
    }

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

    $toolsData = input('post.toolsData');

    $have = intval(input('have'));

    $count = count($tids);
    $ok    = 0;
    foreach ($tids as $goods_id) {

        if ($have == 0 && \core\Db::name('tools')->find(['goods_id' => $goods_id, 'shequ' => $shequid])) {
            // 过滤已经对接的
            $error2 .= '对接商品ID[' . $goods_id . '] => 已经对接过，无需对接<br>';
            continue;
        }

        if ($shequ['type'] == 1) {
            $result = getGoodsParams_extend($shequ, $goods_id);
            if ($result['code'] == 0) {
                $tool            = $result['data'];
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
                $error2 .= '对接商品ID[' . $goods_id . '] => 获取商品详情失败，' . $result['msg'] . '<br>';
                continue;
            }
        } elseif ($shequ['type'] == 0 || $shequ['type'] == 2) {
            $result = getGoodsParams_extend($shequ, $goods_id);
            if ($result['code'] == 0) {
                $tool                = $result['data'];
                $tool['desc']        = $result['desc'];
                $tool['goods_param'] = $result['param'];
                $tool['input']       = explode('|', $result['inputs'])[0];
                $inputs              = '';
                if (stripos($result['inputs'], "|") !== false) {
                    $inputs = str_replace($tool['input'] . "|", '', $result['inputs']);
                }
                $tool['inputs'] = $inputs;
            } else {
                $error2 .= '对接商品ID[' . $goods_id . '] => 获取商品详情失败，' . $result['msg'] . '<br>';
                continue;
            }
        } elseif ($shequ['type'] == 12 || $shequ['type'] == 13) {
            $tool = isset($toolsData['item' . $goods_id]) ? $toolsData['item' . $goods_id] : null;
            if (!$tool || !isset($tool['price']) || !isset($tool['tid'])) {
                $result = getGoodsParams_extend($shequ, $goods_id);
                if ($result['code'] == 0) {
                    $tool            = $result['data'];
                    $tool['price']   = $result['data']['price'];
                    $tool['desc']    = $result['data']['desc'];
                    $tool['stock']   = is_null($result['data']['stock']) ? 999999 : $result['data']['stock'];
                    $tool['input']   = $result['data']['input'];
                    $tool['inputs']  = $result['data']['inputs'];
                    $tool['value']   = $result['data']['value'];
                    $tool['minnum']  = $result['data']['min'];
                    $tool['maxnum']  = $result['data']['max'];
                    $tool['shopimg'] = isset($result['data']['shopimg']) ? $result['data']['shopimg'] : '';
                } else {
                    $error2 .= '对接商品ID[' . $goods_id . '] => 获取商品详情失败，' . $result['msg'] . '<br>';
                    continue;
                }
            }
        } else {
            $result = getGoodsParams_extend($shequ, $goods_id);
            if ($result['code'] == 0) {
                $tool            = $result['data'];
                $tool['price']   = isset($tool['price']) ? $tool['price'] : $tool['money'];
                $tool['shopimg'] = isset($result['data']['shopimg']) ? $result['data']['shopimg'] : '';
            } else {
                $error2 .= '对接商品ID[' . $goods_id . '] => 获取商品详情失败，' . $result['msg'] . '<br>';
                continue;
            }
        }

        if (!$tool) {
            $error2 .= '对接商品ID[' . $goods_id . '] => 获取商品详情失败，未成功匹配到商品详情<br>';
            continue;
        }

        if (isset($tool['maxnum']) && ($tool['maxnum'] > 1 || $tool['maxnum'] == 0)) {
            $multi = 1;
        } else {
            $multi          = 0;
            $tool['maxnum'] = 1;
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
            //     $error2 .= '对接商品ID[' . $goods_id . '] => 上架数量不是当前商品对接数量的整倍数【】<br>';
            //     continue;
            // }

            $num = $value * $tool['value'];
            // 当对接商品标题是 100个 最大10份
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

        $name = $tool['name'];

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

        $multi = 1;
        $sql   = "INSERT INTO `pre_tools` (`zid`,`condition`,`cid`,`prid`,`name`,`price1`,`price`,`cost`,`cost2`,`prices`,`input`,`inputs`,`value`,`desc`,`shopimg`,`min`,`max`,`is_curl`,`is_rank`,`repeat`,`multi`,`validate`,`shequ`,`goods_id`,`goods_type`,`goods_param`,`close`,`active`,`addtime`) values ('0','1','" . $cid . "','" . $prid . "','" . $name . "','" . $price1 . "','" . $price . "','" . $cost . "','" . $cost2 . "','','" . addslashes($tool['input']) . "','" . addslashes($tool['inputs']) . "','" . $value . "','" . addslashes($tool['desc']) . "','" . $tool['shopimg'] . "','" . $minnum . "','" . $maxnum . "','2','1','1','" . $multi . "','0','" . $shequid . "','" . $goods_id . "','" . addslashes($tool['goods_type']) . "','" . addslashes($tool['goods_param']) . "','0','" . intval($tool['active']) . "','" . $date . "')";
        $tid   = $DB->insert($sql);
        if ($tid) {
            // 商品置顶
            resetGoodsSort($tid);
            $ok++;
        } else {
            $error2 .= '上架第' . $key . '个商品时数据库错误: ' . $DB->error() . '<br>';
        }
    }

    json_success('共' . $count . '个商品, 成功上架' . $ok . '个商品到分类' . $classrow['name'] . '! 错误信息:<br/>' . ($error2 ? $error2 : '无错误信息'));
}

$shequSelect = '';
$rs          = $DB->select("SELECT * FROM `pre_shequ` where `type` in (0,1,2,12,13,25,18)");

if ($rs) {
    foreach ($rs as $key => $value) {
        $shequSelect .= '<option value="' . $value['id'] . '" type="' . $value['type'] . '">[' . getShequTypeName($value['type']) . ']' . $value['name'] . ' ' . $value['url'] . '</option>';
    }
}
$localcid    = 0;
$rs2         = $DB->select("SELECT * FROM pre_class WHERE upcid is null OR upcid<1 order by sort asc");
$classSelect = '<option value="0">未分类</option>';
// $cmy_class[0] = '未分类';
foreach ($rs2 as $key => $res) {
    $localcid == 0 && $localcid = $res['cid'];
    $cmy_class[$res['cid']]     = $res['name'];
    $classSelect .= '<option value="' . $res['cid'] . '" name="' . $res2['name'] . '">' . $res['name'] . '</option>';
    $subClass = $DB->count("SELECT count(*) FROM pre_class WHERE `upcid`='" . $res['cid'] . "' order by sort asc");
    if ($subClass > 0) {
        $subRs = $DB->select("SELECT * FROM pre_class WHERE `upcid`='" . $res['cid'] . "' order by sort asc");
        if ($subRs) {
            foreach ($subRs as $key2 => $item) {
                // $cmy_class[$res2['cid']] = $res2['name'];
                $classSelect .= '<option value="' . $item['cid'] . '" name="' . $item['name'] . '">|----' . $item['name'] . '</option>';
            }
        }
    }
}

$priceSelect = '';
$prid        = 0;
$rs3         = $DB->select("SELECT * FROM `pre_price` where 1");
if ($rs3) {
    foreach ($rs3 as $key => $res) {
        if ($res['kind'] == 1) {
            $kind  = '元';
            $color = 'red';
            $type  = '累加价';
        } else {
            $kind  = '%';
            $color = 'green';
            $type  = '百分比';
        }
        $prid == 0 && $prid = $res['id'];
        $priceSelect .= '<option value="' . $res['id'] . '" kind="' . $res['kind'] . '" p_2="' . $res['p_2'] . '" p_1="' . $res['p_1'] . '" p_0="' . $res['p_0'] . '" style="color:' . $color . '">[' . $type . ']&nbsp;' . $res['name'] . '(+' . $res['p_0'] . $kind . '|+' . $res['p_1'] . $kind . '|+' . $res['p_2'] . $kind . ')</option>';
    }
}
include './head.php';
?>
<style>
#list{
    overflow: auto;
    height: calc(100% - 200px);
}
</style>
<div class="row" style="padding: 5px;">
    <div class="col-sm-12 col-md-4 col-lg-3">
        <div class="block">
             <div class="block-title">
                <h3 class="panel-title">商品筛选</h3>
            </div>
            <div class="">
            <form id="form-set3" role="form">
                <div class="input-group">
                    <div class="input-group-addon">对接站点</div>
                    <select class="form-control" name="shequid" default="">
                        <?php echo $shequSelect; ?>
                    </select>
                </div>
                <br/>
                <!-- <div class="input-group">
                    <div class="input-group-addon">时间范围</div>
                    <select class="form-control" name="time" default="-1">
                        <option value="-1">不限时间</option>
                        <option value="0">今日上架</option>
                        <option value="1">昨日上架</option>
                    </select>
                </div>
                <br/> -->
                <div class="input-group">
                    <div class="input-group-addon">商品过滤</div>
                    <select class="form-control" name="have" default="0">
                        <option value="0">未对接</option>
                        <option value="1">已对接</option>
                        <option value="-1">全部</option>
                    </select>
                    <select class="form-control" name="active" default="-1">
                        <option value="-1">全部</option>
                        <option value="1">上架中</option>
                        <option value="0">已下架</option>
                    </select>
                </div>
                <br/>
                <div class="input-group">
                    <div class="input-group-addon">选择分类</div>
                    <select class="form-control" name="cid" default="">
                        <option value="-1">请先选择对接站点</option>
                    </select>
                </div>
                <br/>
                <div class="input-group">
                    <div class="input-group-addon">显示数量</div>
                    <select class="form-control" name="pagesize" default="30">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                        <option value="80">80</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="300">300</option>
                        <option value="500">500</option>
                        <option value="1000">1000</option>
                    </select>
                </div>
                <br/>
                <div class="input-group">
                    <div class="input-group-addon">搜索内容</div>
                    <input name="search" value="" type="text" placeholder="搜索内容" class="form-control">
                </div>
                <br/>
                <div class="btn-group btn-group-justified form-group">
                    <a id="clear" type="button" class="btn btn-warning btn-block" >清空</a>
                    <a id="query" type="button" class="btn btn-primary btn-block">查询</a>
                </div>
            </form>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-8 col-lg-9">
        <div class="block">
          <div class="block-title">
            <h3 class="panel-title">批量对接</h3>
        </div>
        <div class="block-body" style="padding-bottom: 25px;">
            <div id="mytable" style="display: none;">
                <form method="GET" class="form-inline">
                    <button id="batch" type="button" class="btn btn-xs btn-primary"><i class="fa fa-plus"></i>&nbsp;批量对接</button>&nbsp;
                    <button id="selectedHaveNo" type="button" class="btn btn-xs btn-success">&nbsp;选中未对接</button>&nbsp;
                    <button id="selectedHave" type="button" class="btn btn-xs btn-warning">&nbsp;选中已对接</button>&nbsp;
                </form>
                <br/>
                <alert class="alert alert-info" >
                    <span  style="color: red;">当前分类共有<span id="num">0</span>个商品</span>
                </alert>
                <br/>
                <table class="table table-bordered" style="table-layout: fixed;">
                    <!--SVG Sprites-->
                    <svg class="inline-svg">
                    <symbol id="checkSvg" viewbox="0 0 12 10">
                        <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                    </symbol>
                    </svg>
                    <br>
                    <thead>
                        <tr><th width="12%"><input class="inp-cmckb-xs" id="SelectGoodsAll" type="checkbox" onclick="SelectGoodsAll(this)" style="display: none;"/>
                            <label class="cmckb-xs" for="SelectGoodsAll"><span>
                                <svg width="12px" height="10px">
                                <use xlink:href="#checkSvg"></use>
                            </svg>
                            </span><span>&nbsp;ID</span></label></th><th width="40%">商品名称</th><th width="15%">对接分类</th><th>成本价</th><th>上架状态</th><th>对接状态</th><th>操作</th></tr>
                    </thead>
                    <tbody id="list">

                    </tbody>
                </table>
            </div>
            <alert id="alert" class="alert alert-warning" >
                请先选择对接站点和对接分类
            </alert>
        </div>
    </div>

</div>
</div>
</div>
<div id="batchTpl" style="display: none;">
    <div class="input-group">
        <div class="input-group-addon">本地分类</div>
        <select class="form-control" name="localcid" default="<?php echo $localcid; ?>">
            <?php echo $classSelect; ?>
        </select>
    </div>
    <br/>
    <div class="input-group">
        <div class="input-group-addon">加价模板</div>
        <select class="form-control" name="prid" default="<?php echo $prid; ?>">
            <?php echo $priceSelect; ?>
        </select>
    </div>
    <br/>
</div>
<script>
var goodsListAll = [];
$(document).on('click', '#selectedHave', function () {
    var elTids =  $('input[name^="tid"]');
    if (elTids) {
        $.each(elTids, function (indexInArray, valueOfElement) {
            let tid = $(valueOfElement).val();
            if (goodsListAll['item' + tid] && goodsListAll['item' + tid].have == 1) {
                $(valueOfElement).prop('checked', true); ;
            }else{
                $(valueOfElement).prop('checked', false);
            }
        });
    }
});

$(document).on('click', '#selectedHaveNo', function () {
    var elTids =  $('input[name^="tid"]');
    if (elTids) {
        $.each(elTids, function (indexInArray, valueOfElement) {
            let tid = $(valueOfElement).val();
            if (goodsListAll['item' + tid] && goodsListAll['item' + tid].have == 0) {
                $(valueOfElement).prop('checked', true); ;
            }
            else{
                $(valueOfElement).prop('checked', false);
            }
        });
    }
});


$(document).on('click', '#batch', function () {
    var elTids =  $('input[name^="tid"]');
    var tids =  [];
    console.log('elTids', elTids);
    if (!elTids || elTids.length==0){
        layer.msg('未选中需要对接的商品');
        return;
    }

    $.each(elTids, function (indexInArray, valueOfElement) {
        // console.log([valueOfElement], valueOfElement.checked );
        if (valueOfElement.checked ===true) {
            tids.push($(valueOfElement).val());
        }
    });
    // console.log('tids', tids);
    if (!tids || tids.length==0){
        layer.msg('未选中需要对接的商品');
        return;
    }

    var lay1 = layer.open({
        type: 1,
        title: '批量对接(已选' + tids.length + '个商品)',
        anim: 3,
        area: ['380px', 'auto'],
        scrollbar: false,
        btnAlign: 'c',
        content: '<div id="mybatch" class="col-sm-12" style="float: none;padding-top:10px;overflow: auto;"><div class="panel">' + $("#batchTpl").html() +'</div></div>',
        btn: ['确定上架', '取消操作'],
        yes: function () {
            var type = $("[name=shequid] option:selected").attr('type');
            // 大于10个时
            if (tids.length > 300 || (type !=12  && type != 13) ) {
                layer.alert('该系统不支持批量上架或商品较多, 将在1秒后逐个上架以保证成功');
                setTimeout(() => {
                    $.each(tids, function (index, tid) {
                        submitBatch(tid, index + 1,tids.length);
                    });
                    layer.close(lay1);
                }, 1000);
            }
            else{
                // 批量上架
                var shequid = $("[name=shequid] option:selected").val();
                var localcid = $("#mybatch [name=localcid] option:selected").val();
                var prid = $("#mybatch [name=prid] option:selected").val();
                var ii = layer.load(2, {shade:[0.1,'#fff'] });
                console.log('goodsListAll', goodsListAll);
                $.ajax({
                    type : 'POST',
                    url : '?act=batch',
                    data: {
                        shequid: shequid,
                        localcid: localcid,
                        tids: tids,
                        prid: prid,
                        toolsData: goodsListAll
                    },
                    dataType : 'json',
                    success : function(res) {
                        layer.close(lay1);
                        layer.close(ii);
                        if (res.code==0) {
                            layer.msg(res.msg, {icon: 6, });
                        }
                        else{
                            layer.alert(res.msg, {icon: 5});
                        }
                    },
                    error:function(data){
                        layer.close(ii);
                        layer.msg('服务器错误');
                        return false;
                    }
                });
            }
        },
    });
});

$(document).on('click', '.batchonce', function () {
    var tid = $(this).data('tid');
    var lay1 = layer.open({
        type: 1,
        title: '批量对接(已选1个商品)',
        anim: 3,
        area: ['380px', 'auto'],
        scrollbar: false,
        btnAlign: 'c',
        content: '<div id="mybatch" class="col-sm-12" style="float: none;padding-top:10px;overflow: auto;"><div class="panel">' + $("#batchTpl").html() +'</div></div>',
        btn: ['确定上架', '取消操作'],
        yes: function () {
            submitBatch(tid, 1, 1);
        },
    });
});

function submitBatch(tid, num, all){
    var shequid = $("[name=shequid] option:selected").val();
    var localcid = $("#mybatch [name=localcid] option:selected").val();
    var prid = $("#mybatch [name=prid] option:selected").val();
    var classname = $("#mybatch [name=localcid] option:selected").attr('name');
    var ii = layer.load(2, {shade:[0.1,'#fff'], 'content':'正在上架第'+ num +'个到本地分类' + classname});
    $.ajax({
        type : 'POST',
        url : '?act=batch',
        data: {
            shequid: shequid,
            localcid: localcid,
            tids: [tid],
            prid: prid,
            toolsData: [],
        },
        dataType : 'json',
        async: false,
        success : function(res) {
            layer.close(ii);
            if (res.code==0) {
                if (all==1) {
                    layer.closeAll();
                    layer.msg(res.msg, {icon: 6, });
                }
                else{
                    return true;
                }
            }
            else{
                layer.alert(res.msg);
                return false
            }
        },
        error:function(data){
            layer.close(ii);
            layer.msg('上架第'+ num +'个商品到本地失败, 服务器错误');
            return false;
        }
    });
}

$(document).on('change', '[name=shequid]',  function () {
    var shequid = $("[name=shequid] option:selected").val();
    if (!shequid || shequid<0) {
        return;
    }
    var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type : 'GET',
        url : '?act=class&shequid='+shequid,
        dataType : 'json',
        success : function(res) {
            layer.close(ii);
            if (res.code==0) {
                $("[name=cid]").empty();
                $("[name=cid]").append('<option value="0">全部商品</option>');
                $.each(res.data, function (i, item) {
                    $("[name=cid]").append('<option value="'+ item.cid + '" name="'+ item.name + '">'+ item.name + '</option>');
                });
            }
            else{
                layer.alert(res.msg);
            }
        },
        error:function(data){
            layer.close(ii);
            layer.msg('服务器错误');
            return false;
        }
    });
});

$(document).on('click', '#query', function () {
    var shequid = $("[name=shequid] option:selected").val();
    var active = $("[name=active] option:selected").val();
    var cid = $("[name=cid] option:selected").val();
    var classname = $("[name=cid] option:selected").attr('name');
    var have = $("[name=have] option:selected").val();
    var time = $("[name=time] option:selected").val();
    var pagesize = $("[name=pagesize] option:selected").val();
    var search = $("[name=search]").val();
    var ii = layer.load(2, {shade: [0.1, '#fff']});
    $.ajax({
        type: 'POST',
        url: '?act=shoplist',
        data: {
            shequid: shequid,
            active: active,
            have: have,
            time: time,
            pagesize: pagesize,
            cid: cid,
            search: search,
        },
        dataType: 'json',
        success: function (res) {
            layer.close(ii);
            if (res.code == 0) {
                $("#list").html('');
                goodsListAll = []
                $.each(res.data, function (i, item) {
                    goodsListAll['item'+item.tid] = item
                    $("#list").append('<tr><td><input class="inp-cmckb-xs class_' + cid + '" name="tid[]" id="tid' + item.tid + '" type="checkbox" value="' + item.tid + '" onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="tid' + item.tid + '"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;' + item.tid + '</span></label></td><td>'
                    + item.name +'</td><td>'
                    +'对接分类: '+ (classname?classname:'全部商品') +'</td><td>'
                    + item.price +'</td><td>'
                    + (item.close ==0?'<span class="btn btn-xs btn-success">上架中</span>':'<span class="btn btn-xs btn-warning">已下架</span>')
                    +'</td><td>'
                    + (item.have ==1?'<span class="btn btn-xs btn-success">已对接</span>':'<span class="btn btn-xs btn-warning">未对接</span>') +'</td>'
                    +'<td><button data-tid="' +  item.tid + '"  data-cid="' + cid + '" type="button" class="btn  btn-xs btn-primary batchonce"><i class="fa fa-plus"></i>&nbsp;对接</button>&nbsp;</td></tr>');
                });

                if (res.data.length==0) {
                    $("#list").append('<tr><td colspan="7" class="text-center">该条件未查询到商品</td>');
                }

                $("#num").html(res.data.length);

                $("#mytable").show();
                $("#alert").hide();
            }
            else {
                $("#mytable").hide();
                $("#alert").html(res.msg).show();
            }
        },
        error: function (data) {
            layer.close(ii);
            layer.msg('服务器错误');
            return false;
        }
    });
});

$(document).on('click', '#clear', function () {
    $("#list").html('');
    $("#mytable").hide();
    $("#alert").html('请先选择对接站点和对接分类').show();
    $("[name=shequid]").val(0);
    $("[name=active]").val(-1);
    $("[name=cid]").val(-1);
    $("[name=have]").val(0);
    $("[name=pagesize]").val(30);
    $("[name=search]").val('');
});



function selectRender(){
    var items = $("select[default]");
    for (var i = 0; i < items.length; i++) {
        $(items[i]).val($(items[i]).attr("default") || 0);
    }
}

function SelectGoodsAll(chkAll) {
        var cid = $("[name=cid] option:selected").val();
        var items = $('.class_'+ cid);
        for (i = 0; i < items.length; i++) {
            if (items[i].id.indexOf("tid") != -1 && items[i].type == "checkbox") {
                items[i].checked = chkAll.checked;
            }
        }
        return true;
    }

$(document).ready(function () {
    selectRender();
});
</script>

<?php include './footer.php';?>