<?php

/**
 * 商品编辑
 * By 秋云
 */
include '../includes/common.php';
$title = '商品管理';
checkLogin();

checkFileSize();

checkAuthority('shops');

$act = isset($_GET['act']) ? input('get.act', 1) : null;
if ($act == 'add_submit') {
    $cid         = input('cid');
    $name        = strFilter(input('name', 1));
    $prices      = input('prices');
    $price       = sprintf('%.2f', input('post.price'));
    $cost        = sprintf('%.2f', input('post.cost'));
    $cost2       = sprintf('%.2f', input('post.cost2'));
    $price1      = sprintf('%.6f', input('post.price1'));
    $sale        = intval(input('post.sale'));
    $unit        = input('post.unit');
    $min         = input('post.min');
    $max         = input('post.max');
    $prid        = intval(input('post.prid'));
    $specs_id    = intval(input('post.specs_id'));
    $attr_id     = intval(input('post.attr_id'));
    $input       = strFilter(input('post.input', 1));
    $inputs      = strFilter(input('post.inputs', 1));
    $desc        = strFilter(input('post.desc'));
    $alert       = strFilter(input('post.alert'));
    $shopimg     = input('post.shopimg');
    $value       = input('post.value');
    $stock_open  = intval(input('post.stock_open', 1));
    $stock       = intval(input('post.stock', 1));
    $multi       = input('post.multi');
    $validate    = input('post.validate');
    $is_curl     = input('post.is_curl');
    $curl        = input('post.curl');
    $shequ       = intval(input('post.shequ'));
    $close_login = intval(input('post.close_login'));
    $goods_id    = input('post.goods_id', 1);
    $goods_type  = input('post.goods_type');
    $goods_param = ($is_curl == 1 ? input('post.curl_post', 1) : input('post.goods_param', 1));
    $repeat      = input('post.repeat');
    $result      = strFilter(input('post.result'));
    $is_rank     = intval(input('post.is_rank'));
    $is_email    = intval(input('post.is_email'));
    $check       = input('post.check');
    $check_val   = input('post.check_val');
    $pay_alipay  = (int) $_POST['pay_alipay'] == 1 ? $_POST['pay_alipay'] : '0';
    $pay_qqpay   = (int) $_POST['pay_qqpay'] == 1 ? $_POST['pay_qqpay'] : '0';
    $pay_wxpay   = (int) $_POST['pay_wxpay'] == 1 ? $_POST['pay_wxpay'] : '0';
    $pay_rmb     = (int) $_POST['pay_rmb'] == 1 ? $_POST['pay_rmb'] : '0';
    $title       = input('title');

    if ($prid == -1) {
        $prid = 0;
    }

    // if ($price == null && $prid == 0 && strpos($name, '免费') === false) {
    //     exit(json_encode(['code' => -1, 'msg' => '添加错误，出售价格不能为空！']));
    // }

    if (floatval($cost) == 0) {
        $cost = $price;
    }

    if (floatval($cost2) == 0) {
        $cost2 = $cost;
    }
    try {
        $prow = $DB->get_row("SELECT * FROM cmy_price WHERE id= ? limit 1", [$prid]);

        if ($prid > 0 && !$prow) {
            exit(json_encode(['code' => -1, 'msg' => '该加价模板不存在！']));
        } elseif ($prow['kind'] == 2 && $price1 <= 0) {
            exit(json_encode(['code' => -1, 'msg' => '使用百分比加价模板时成本价格不能为0！']));
        }

        if ($name == null) {
            exit(json_encode(['code' => -1, 'msg' => '添加错误，商品名称不能为空！']));
        } else if ($price != null && $price1 == null && floatval($cost) >= floatval($price)) {
            exit(json_encode(['code' => -1, 'msg' => '出售价格不能小于普及分站价格，或不能等于普及分站价格！']));
        } else {
            $msg = '';
            if ($price == 0 && $prid == 0 && $specs_id == 0) {
                $msg = '<br><span style="color:red">注意：当前商品价格为0，且未配置规格，可能会亏本！</span>';
            }

            if ($price == 0 && $prid < 1 && $specs_id == 0) {
                $msg .= '<br><span style="color:red">注意：当前商品价格为0，且没有配置加价模板！</span>';
                if ($multi == 1 && $specs_id == 0) {
                    $msg .= '<br><span style="color:red">注意：当前商品价格为0，且开启了显示数量选择框，可能会导致被撸！</span>';
                }

                if ($repeat == 1 && $specs_id == 0) {
                    $msg .= '<br><span style="color:red">注意：当前商品价格为0，且开启了重复下单，可能会导致被撸！</span>';
                }
            }

            if ($stock_open == 1 && $stock < 1) {
                $msg .= '<br><span style="color:red">注意：当前已开启库存控制，且库存数量为0！</span>';
            }

            if ($stock_open == 0) {
                $stock = 0;
            }

            if (($is_curl == 2 && !$shequ)) {
                exit(json_encode(['code' => -1, 'msg' => '请选择对接社区！']));
            } else {
                $options = [];
                if ($specs_id > 0) {
                    $options = input('post.options');
                    if (count($options) == 0) {
                        $result = [
                            'code' => -1,
                            'msg'  => '规格选项不能为空！',
                        ];
                        exit(json_encode($result));
                    }
                }

                $sql = 'INSERT INTO `pre_tools` (`zid`,`cid`,`condition`,`name`,`title`,`price1`,`prid`,`sale`,`unit`,`specs_id`,`attr_id`,`price`,`cost`,`cost2`,`min`,`max`,`input`,`inputs`,`desc`,`alert`,`shopimg`,`value`,`stock_open`,`stock`,`stock_time`,`is_curl`,`curl`,`shequ`,`goods_id`,`goods_type`,`goods_param`,`repeat`,`result`,`multi`,`validate`,`is_rank`,`is_email`,`check_val`,`pay_alipay`,`pay_qqpay`,`pay_wxpay`,`pay_rmb`,`sort`,`prices`,`active`,`addtime`,`close_login`) VALUES (\'0\',\'' . $cid . '\',\'1\',\'' . $name . '\',\'' . $title . '\',\'' . $price1 . '\',\'' . $prid . '\',\'' . $sale . '\',\'' . $unit . '\',\'' . $specs_id . '\',\'' . $attr_id . '\',\'' . $price . '\',\'' . $cost . '\',\'' . $cost2 . '\',\'' . $min . '\',\'' . $max . '\',\'' . $input . '\',\'' . $inputs . '\',\'' . $desc . '\',\'' . $alert . '\',\'' . $shopimg . '\',\'' . $value . '\',\'' . $stock_open . '\',\'' . $stock . '\',\'' . time() . '\',\'' . $is_curl . '\',\'' . $curl . '\',\'' . $shequ . '\',\'' . $goods_id . '\',\'' . $goods_type . '\',\'' . $goods_param . '\',\'' . $repeat . '\',\'' . $result . '\',\'' . $multi . '\',\'' . $validate . '\',\'' . $is_rank . '\',\'' . $is_email . '\',\'' . $check_val . '\',\'' . $pay_alipay . '\',\'' . $pay_qqpay . '\',\'' . $pay_wxpay . '\',\'' . $pay_rmb . '\',\'' . $sort . '\',\'' . $prices . '\',\'1\',\'' . $date . '\',\'' . $close_login . '\')';
                if ($tid = $DB->insert($sql)) {
                    if ($specs_id > 0) {
                        $DB->query("DELETE FROM `pre_stock` WHERE tid='" . $tid . "'");
                        foreach ($options as $key => $option) {
                            $DB->query("INSERT INTO `pre_stock` (`tid`,`stock`,`icon`,`specs_id`,`prid`,`price1`,`title`,`value`,`remark`,`status`,`uptime`) VALUES ('" . $tid . "','" . $option['stock'] . "','" . $option['icon'] . "','" . $specs_id . "','" . $option['prid'] . "','" . $option['price1'] . "','" . $option['title'] . "','" . $option['value'] . "','" . $option['remark'] . "','1','" . $date . "')");
                        }
                    }

                    // 商品置顶
                    resetGoodsSort($tid);

                    addToolLogs($tid, $name, $price, 0, '上架', '新上架商品');

                    $result = [
                        'code' => 0,
                        'msg'  => '添加商品成功！' . $msg,
                        'tid'  => $tid,
                    ];

                } else {
                    $result = [
                        'code' => -1,
                        'msg'  => '添加商品失败！' . $DB->error(),
                    ];
                }
                exit(json_encode($result));
            }
        }
    } catch (\Exception $th) {
        $result = [
            'code' => -1,
            'msg'  => '添加商品失败，' . $th->getMessage(),
        ];
        exit(json_encode($result));
    }
} elseif ($act == 'edit_submit') {
    $tid  = input('get.tid', 1);
    $rows = $DB->get_row('SELECT * FROM cmy_tools WHERE tid=\'' . $tid . '\' LIMIT 1');
    if (!$rows) {
        exit(json_encode(['code' => -1, 'msg' => '当前记录不存在！']));
    }
    $cid         = input('cid');
    $name        = strFilter(input('name', 1));
    $prices      = input('prices');
    $price       = sprintf('%.2f', input('post.price'));
    $cost        = sprintf('%.2f', input('post.cost'));
    $cost2       = sprintf('%.2f', input('post.cost2'));
    $price1      = sprintf('%.6f', input('post.price1'));
    $sale        = intval(input('post.sale'));
    $unit        = input('post.unit', 1);
    $min         = input('post.min');
    $max         = input('post.max');
    $prid        = intval(input('post.prid'));
    $specs_id    = intval(input('post.specs_id'));
    $attr_id     = intval(input('post.attr_id'));
    $input       = strFilter(input('post.input', 1));
    $inputs      = strFilter(input('post.inputs', 1));
    $desc        = strFilter(input('post.desc'));
    $alert       = strFilter(input('post.alert'));
    $shopimg     = input('post.shopimg');
    $value       = input('post.value');
    $close_login = intval(input('post.close_login'));
    $stock_open  = intval(input('post.stock_open', 1));
    $stock       = intval(input('post.stock', 1));
    $multi       = input('post.multi');
    $validate    = input('post.validate');
    $is_curl     = input('post.is_curl');
    $curl        = input('post.curl');
    $shequ       = input('post.shequ');
    $goods_id    = input('post.goods_id');
    $goods_type  = input('post.goods_type');
    $goods_param = $is_curl == 1 ? input('post.curl_post', 1) : input('post.goods_param', 1);
    $repeat      = intval($_POST['repeat']);
    $result      = strFilter(input('result'));
    $is_rank     = intval($_POST['is_rank']);
    $is_email    = intval(input('post.is_email'));
    $check_val   = input('check_val');
    $title       = trim(input('title'));
    $pay_alipay  = intval($_POST['pay_alipay']) == 1 ? input('post.pay_alipay', 1) : '0';
    $pay_qqpay   = intval($_POST['pay_qqpay']) == 1 ? input('post.pay_qqpay', 1) : '0';
    $pay_wxpay   = intval($_POST['pay_wxpay']) == 1 ? input('post.pay_wxpay', 1) : '0';
    $pay_rmb     = intval($_POST['pay_rmb']) == 1 ? input('post.pay_rmb', 1) : '0';

    if ($prid == -1) {
        $prid = 0;
    }

    // if ($price == null && $prid == 0 && strpos($name, '免费') === false) {
    //     exit(json_encode(['code' => -1, 'msg' => '添加错误，出售价格不能为空！']));
    // }

    if (floatval($cost) == 0) {
        $cost = $price;
    }

    if (floatval($cost2) == 0) {
        $cost2 = $cost;
    }

    try {
        $prow = $DB->get_row("SELECT * FROM cmy_price WHERE id= ? limit 1", [$prid]);

        if ($prid > 0 && !$prow) {
            exit(json_encode(['code' => -1, 'msg' => '该加价模板不存在！']));
        } elseif ($prow['kind'] == 2 && $price1 <= 0) {
            exit(json_encode(['code' => -1, 'msg' => '使用百分比加价模板时成本价格不能为0！']));
        }

        if ($name == null) {
            exit(json_encode(['code' => -1, 'msg' => '保存错误，商品名称或成本价格不能为空！']));
        } else {
            if (($is_curl == 2 && !$shequ)) {
                exit(json_encode(['code' => -1, 'msg' => '请选择对接社区！']));
            } else {
                $msg = '';
                if ($price == 0 && $prid == 0 && $specs_id == 0) {
                    $msg = '<br><span style="color:red">注意：当前商品价格为0，且未配置规格，可能会亏本！</span>';
                }

                if ($price == 0 && $prid < 1 && $specs_id == 0) {
                    $msg .= '<br><span style="color:red">注意：当前商品价格为0，且没有配置加价模板！</span>';
                    if ($multi == 1 && $specs_id == 0) {
                        $msg .= '<br><span style="color:red">注意：当前商品价格为0，且开启了显示数量选择框，可能会导致被撸！</span>';
                    }

                    if ($repeat == 1 && $specs_id == 0) {
                        $msg .= '<br><span style="color:red">注意：当前商品价格为0，且开启了重复下单，可能会导致被撸！</span>';
                    }
                }

                if ($stock_open == 1 && $stock < 1) {
                    $msg .= '<br><span style="color:red">注意：当前已开启库存控制，且库存数量为0！</span>';
                }

                if ($stock_open == 0) {
                    $stock = 0;
                }

                if ($specs_id > 0) {
                    $options = input('post.options');
                    if (count($options)) {
                        $data_ids = [];
                        foreach ($options as $key => $option) {
                            $stock = $DB->get_row('SELECT * FROM `pre_stock` WHERE `id`= :id', [':id' => $option['stock_id']]);
                            if ($option['stock_id'] > 0 && $stock) {
                                $data_ids[] = $option['stock_id'];
                                $DB->query("UPDATE `pre_stock` SET `stock`='" . $option['stock'] . "',`icon`='" . $option['icon'] . "',`prid`='" . $option['prid'] . "',`price1`='" . $option['price1'] . "',`title`='" . $option['title'] . "',`value`='" . $option['value'] . "',`remark`='" . $option['remark'] . "',`uptime`='" . $date . "' WHERE id='" . $option['stock_id'] . "'");
                            } else {
                                $stock_id = $DB->insert("INSERT INTO `pre_stock` (`tid`,`stock`,`icon`,`specs_id`,`prid`,`price1`,`title`,`value`,`remark`,`status`,`uptime`) VALUES ('" . $tid . "','" . $option['stock'] . "','" . $option['icon'] . "','" . $specs_id . "','" . $option['prid'] . "','" . $option['price1'] . "','" . $option['title'] . "','" . $option['value'] . "','" . $option['remark'] . "','1','" . $date . "')");
                                if ($stock_id) {
                                    $data_ids[] = $stock_id;
                                }
                            }
                        }
                        $data_ids_str = implode(',', $data_ids);
                        $DB->query("DELETE FROM `pre_stock` WHERE `tid`='{$tid}' AND `id` not in ({$data_ids_str})");
                    } else {
                        $result = [
                            'code' => -1,
                            'msg'  => '规格选项不能为空！',
                        ];
                        exit(json_encode($result));
                    }
                }

                $sql = "UPDATE `pre_tools` SET `cid`='" . $cid . "',`name`='" . $name . "',`condition`='1',`title`='" . $title . "',`price`='" . $price . "',`cost`='" . $cost . "',`cost2`='" . $cost2 . "',`price1`='" . $price1 . "',`min`='" . $min . "',`max`='" . $max . "',`prid`='" . $prid . "',`sale`='" . $sale . "',`unit`='" . $unit . "',`specs_id`='" . $specs_id . "',`attr_id`='" . $attr_id . "',`cost`='" . $cost . "',`cost2`='" . $cost2 . "',`input`='" . $input . "',`inputs`='" . $inputs . "',`desc`='" . $desc . "',`alert`='" . $alert . "',`shopimg`='" . $shopimg . "',`value`='" . $value . "',`stock_open`='" . $stock_open . "',`stock`='" . $stock . "',`is_curl`='" . $is_curl . "',`curl`='" . $curl . "',`shequ`='" . $shequ . "',`goods_id`='" . $goods_id . "',`goods_type`='" . $goods_type . "',`goods_param`='" . $goods_param . "',`repeat`='" . $repeat . "',`result`='" . $result . "',`multi`='" . $multi . "',`validate`='" . $validate . "',`is_rank`='" . $is_rank . "',`is_email`='" . $is_email . "',`check_val`='" . $check_val . "',`pay_alipay`='" . $pay_alipay . "',`pay_qqpay`='" . $pay_qqpay . "',`pay_wxpay`='" . $pay_wxpay . "',`pay_rmb`='" . $pay_rmb . "',`prices`='" . $prices . "',`close_login`='" . $close_login . "' WHERE `tid`='" . $tid . "'";
                if ($DB->query($sql)) {
                    $result = [
                        'code' => 0,
                        'msg'  => '修改商品成功！' . $msg,
                        'tid'  => $tid,
                    ];
                } else {
                    $result = [
                        'code' => -1,
                        'msg'  => '修改商品失败，' . $DB->error(),
                        'tid'  => $tid,
                        'sql'  => $sql,
                    ];
                }
                exit(json_encode($result));
            }
        }
    } catch (\Exception $th) {
        $result = [
            'code' => -1,
            'msg'  => '修改商品失败，' . $th->getMessage(),
        ];
        exit(json_encode($result));
    }
}

include './head.php';

$editor_load = true;

echo '
<link href="../assets/public/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
<script src="../assets/public/select2/4.0.13/js/select2.js"></script>
<style>
.table thead > tr > th{font-size: 14px;padding-top: 10px;padding-bottom: 10px;}
.select2-selection.select2-selection--single {
  height: 32px;
}
#GoodsInfo img{
    max-width:100%;
    display: block;
}
.select2-container--default.select2-selection--single {
  padding: 5px;
}
.modal{
    z-index: 999999;
}
.btn-link{
    color:#2440b3;
}
</style>
<div class="modal fade" align="left" id="inputabout" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">输入框标题说明</h4>
      </div>
      <div class="modal-body">
    使用以下输入框标题可实现特殊的转换功能<br/>
    <b>链接去掉中文转换</b>：默认自动去中文，只支持第一个输入框！如果不需要去掉(如砍价口令)，请在按钮标题后面加[!shareurl]，如：砍价口令[!shareurl]<br/>
    <hr style=" margin: 8px;">
    <b>倍数下单加价扩展功能（不能与规格一起使用，不是每个模板都支持！注意初级阶段暂不会更新，不保证没有bug，如遇到bug请关闭使用！）</b><br/>
    <b>支持多个输入框同时使用，1个输入框只能有1个</b>
    ：仅支持输入框内容是数字的情况，输入框格式：<a href="javascript:changeinput(\'购买天数[int:1]\')">购买天数[int:1]</a>、<a href="javascript:changeinput(\'每天数量[int:100]\')">每天数量[int:100]</a><br/>
    <b>控制价格翻倍扩展说明</b>：购买天数[int:1]，其中1是基数，当用户输入内容是该值的<span style="color:red;">{整倍数}</span>时，总价格就根据<span style="color:red;">{整倍数}</span>翻倍<br/>
    <hr style=" margin: 8px;">
    <b>自动获取链接url</b>：请在输入框标题后面加[shareurl]，如：<a href="javascript:changeinput(\'作品链接[shareurl]\')">作品链接[shareurl]</a><br/>
    <b>自动获取用户ID或主页ID</b>：请在输入框标题后面加[shareid]，如：<a href="javascript:changeinput(\'主页链接[shareid]\')">主页链接[shareid]</a><br/>
    <b>自动获取作品ID</b>：请在输入框标题后面加[zpid]，如：<a href="javascript:changeinput(\'分享链接[zpid]\')">分享链接[zpid]</a><br/>
    <hr style=" margin: 8px;">
    <b>显示下拉选择框</b>：在名称后面加{选择1,选择2}，例如：<a href="javascript:changeinput(\'分类名{普通,音乐,宠物}\')">分类名{普通,音乐,宠物}</a>
    <br/>
    <b>显示提示语</b>：在名称后面加&提示语，例如：<a href="javascript:changeinput(\'下单QQ&输入下单QQ\')">下单QQ&输入下单QQ</a>
    </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" align="left" id="inputsabout" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">更多输入框标题说明</h4>
      </div>
      <div class="modal-body">
    使用以下输入框标题可实现特殊功能<br/>
    <hr style=" margin: 8px;">
    <b>控制价格翻倍扩展使用（不能与规格一起使用）</b><br/>
    <b>支持多个输入框同时使用，1个输入框只能有1个</b>
    ：仅支持输入框内容是数字的情况，输入框格式：<a href="javascript:changeinputs(\'购买天数[int:1]\')">购买天数[int:1]</a>、<a href="javascript:changeinputs(\'每天数量[int:100]\')">每天数量[int:100]</a><br/>
    <b>控制价格翻倍扩展说明</b>：购买天数[int:1]，其中1是基数，当用户输入内容是该值的<span style="color:red;">{整倍数}</span>时，总价格就根据<span style="color:red;">{整倍数}</span>翻倍<br/>
    <hr style=" margin: 8px;">
    <b>获取空间说说列表</b>：<a href="javascript:changeinputs(\'说说ID\')">说说ID</a>、<a href="javascript:changeinputs(\'说说ＩＤ\')">说说ＩＤ</a><br/>
    <b>自动获取作品ID</b>：请在更多输入框标题后面加[zpid]，如：作品链接[zpid]<br/>
    <b>收货地址获取</b>：<a href="javascript:changeinputs(\'收货地址\')">收货地址</a>、<a href="javascript:changeinputs(\'收货人地址\')">收货人地址</a><br/><hr/>
    <b>显示选择框</b>：在名称后面加{选择1,选择2}，例如：<a href="javascript:changeinputs(\'分类名{普通,音乐,宠物}\')">分类名{普通,音乐,宠物}</a>
    <br/>
    <b>显示提示语</b>：在名称后面加&提示语，例如：<a href="javascript:changeinputs(\'下单QQ&输入下单QQ\')">下单QQ&输入下单QQ</a>
    </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
';
$rs           = $DB->query("SELECT * FROM cmy_class WHERE upcid is null OR upcid<1 order by sort asc");
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

$rs          = $DB->query('SELECT * FROM cmy_shequ order by id DESC');
$shequselect = '';
while ($res = $DB->fetch($rs)) {
    $shequselect .= '<option value="' . $res['id'] . '" type="' . ($res['alias'] && $res['type'] == '' ? 'extend' : $res['type']) . '" alias="' . $res['alias'] . '" url="' . $res['url'] . '">[' . getShequTypeName($res['type']) . '] ' . $res['url'] . '（' . $res['name'] . '）</option>';
}

$rs          = $DB->query('SELECT * FROM cmy_price order by id desc');
$priceselect = '<option value="0">不加价</option>';
while ($res = $DB->fetch($rs)) {
    if ($res['kind'] == 1) {
        $kind  = '元';
        $color = 'red';
        $type  = '累加价';
    } else {
        $kind  = '%';
        $color = 'green';
        $type  = '百分比';
    }
    $priceselect .= '<option value="' . $res['id'] . '" kind="' . $res['kind'] . '" p_2="' . $res['p_2'] . '" p_1="' . $res['p_1'] . '" p_0="' . $res['p_0'] . '" style="color:' . $color . '">[' . $type . ']&nbsp;' . $res['name'] . '(+' . $res['p_0'] . $kind . '|+' . $res['p_1'] . $kind . '|+' . $res['p_2'] . $kind . ')</option>';
}

$rs2         = $DB->query('SELECT * FROM `pre_specs` order by id desc');
$specsSelect = '<option value="0">不绑定规格</option>';
while ($res = $DB->fetch($rs2)) {
    $specsSelect .= '<option value="' . $res['id'] . '">' . $res['name'] . '</option>';
}

$rs3             = $DB->query('SELECT * FROM `pre_attribute` order by id desc');
$attributeSelect = '<option value="0">不绑定属性</option>';
while ($res = $DB->fetch($rs3)) {
    $attributeSelect .= '<option value="' . $res['id'] . '">' . $res['name'] . '</option>';
}

$my = (isset($_GET['my']) ? $_GET['my'] : null);
if ($my == 'add') {
    if (isset($_GET['cid']) && !empty($_GET['cid'])) {
        $cid       = input('get.cid');
        $row_class = $DB->get_row('SELECT * from `pre_class` where cid=\'' . $cid . '\' limit 1');
    }
    echo '
  <form id="addForm" action="./shopedit.php?my=add_submit" method="POST">
  <div class="col-sm-12 col-md-12  col-lg-12 center-block" style="float: none;padding-top:10px ">
  <div class="col-sm-12 col-md-6">
<div class="block">
<div class="block-title"><h3 class="panel-title">商品类型与对接设置</h3></div>
<input type="hidden" name="backurl" value="';
    echo $_SERVER['HTTP_REFERER'];
    echo '"/>
<div class="">
<div class="form-group">
<label>购买成功后的动作:</label><br>
<select class="form-control" name="is_curl"><option value="0">0_无</option><option value="2">自动提交到进货站点</option><option value="1">自定义访问URL/POST</option><option value="4">卡密商品自动发货</option><option value="3">自动发送提醒邮件</option><option value="5">直接显示内容</option></select>
</div>
<div id="curl_display1" style="display:none;">
<div class="form-group">
<label>* URL:<span style="color:red">（必填）</span></label><br>
<input type="text" class="form-control" name="curl" id="curl" value="">
<font color="green">此处URL必须先填要访问的url地址和GET数据，如https://www.test.cn/?value=[input]&value2=[input2]&num=[num]</font>
</div>
<br/>
<div class="form-group">
<label>POST:</label><br>
<input type="text" class="form-control" name="curl_post" id="curl_post" value="" placeholder="无POST内容请留空">
<font color="green">无POST内容请留空，POST格式{键值对}：a=123&b=456<br/>变量代码：<br/>
<a href="#" onclick="Addstr(\'curl\',\'[input]\');return false">[input]</a>&nbsp;第一个输入框内容<br/>
<a href="#" onclick="Addstr(\'curl\',\'[input2]\');return false">[input2]</a>&nbsp;第二个输入框内容<br/>
<a href="#" onclick="Addstr(\'curl\',\'[input3]\');return false">[input3]</a>&nbsp;第三个输入框内容<br/>
<a href="#" onclick="Addstr(\'curl\',\'[input4]\');return false">[input4]</a>&nbsp;第四个输入框内容<br/>
<a href="#" onclick="Addstr(\'curl\',\'[name]\');return false">[name]</a>&nbsp;商品名称<br/>
<a href="#" onclick="Addstr(\'curl\',\'[price]\');return false">[price]</a>&nbsp;商品单价<br/>
<a href="#" onclick="Addstr(\'curl\',\'[money]\');return false">[money]</a>&nbsp;订单金额<br/>
<a href="#" onclick="Addstr(\'curl\',\'[num]\');return false">[num]</a>&nbsp;下单数量<br/>
<a href="#" onclick="Addstr(\'curl\',\'[time]\');return false">[time]</a>&nbsp;当前时间戳<br/></font>
</div>
<br/>
</div>
<div id="curl_display2" style="display:none;">
<div class="form-group">
<label>选择对接网站:</label>&nbsp;(<a href="shequlist.php" target="_blank">管理</a>)<br>
<select class="form-control" name="shequ">';
    echo $shequselect;
    echo '</select>
    <small style="color:red">如果获取失败或提示网站打开失败，请尝试切换该对接站代理服务器开关</small>
</div>
<div class="form-group" id="show_goodsclass1" style="display:none;">
  <label>选择一级目录:</label>  <span class="btn btn-xs btn-info" id="kyxmoney"  onclick="kyxmoney()">查余额</span> <span class="btn btn-xs btn-info" id="kyxjump" onclick="kyxjump()">到目录</span> <br>
  <div class="input-group">
  <select class="form-control" id="goodsclass1"></select>
  <span class="input-group-addon btn btn-success" id="getclass">获取分类</span>
  </div>
  <small style="color:red" id="goodsclasstips">如分类过多或对方服务器出现卡顿，会获取比较慢！如果获取失败请重新获取几次</small>
</div>
<div class="form-group" id="show_classlist" style="display:none;">
  <label>选择商品分类:</label>
  <div class="input-group">
  <select class="form-control" id="classlist_select"></select>
  <span class="input-group-addon btn btn-success" id="getGoods_class">一键获取</span>
  </div>
</div>
<div class="form-group" id="show_goodslist">
    <label>选择对接商品:</label><br>
    <div class="input-group">
        <div class="input-group-addon lastPage" title="上一页" style="display:none">
        <i class="fa fa-chevron-left"></i>
        </div>
        <select class="form-control" id="goodslist"></select>
        <span class="input-group-addon btn btn-success" id="getGoods">获取</span>
        <div class="input-group-addon nextPage" title="下一页" style="display:none">
        <i class="fa fa-chevron-right"></i>
        </div>
    </div>
    <small id="kayixin_tips" style="color:red">如商品过多或对方服务器出现卡顿，会获取比较慢<br/>商品太多时，建议你直接填写商品ID可直接获取参数</small>
</div>
<div class="form-group" id="goods_id">
<label>商品ID（goods_id）:</label><br>
<input type="text" class="form-control" name="goods_id" value="">
<small id="goods_id_tips" style="display:none;"></small>
</div>
<div class="form-group" id="goods_type">
<label>类型ID（goods_type）:</label><br>
<input type="text" class="form-control" name="goods_type" value="">
</div>
<div class="form-group" id="goods_param">
<label id="goods_param_name">参数名:</label><br>
<input type="text" class="form-control" name="goods_param" value="">
<pre><font color="green" id="goods_param_tips">对接那边下单输入框的参数，多个参数请用|隔开（社区和卡商可自动获取）；如果是对接KM或云宝站点，请直接填写下单页面地址</font></pre>
</div>
<div class="form-group" id="goods_card_display" style="display:none">
<label id="goods_card">卡号(九流用):</label><br>
<input type="text" class="form-control" name="goods_card" value="">
<pre><font color="green">九流社区的商品卡号，填写错误无法对接</font></pre>
</div>
<div class="form-group" id="goods_cardpass_display" style="display:none">
<label id="goods_cardpass">卡号密码(九流用):</label><br>
<input type="text" class="form-control" name="goods_cardpass" value="">
<pre><font color="green">九流社区的商品卡号密码，填写错误无法对接</font></pre>
</div>
</div>
<div class="form-group" id="show_value">
<label>默认数量信息:</label><br>
<input type="text" class="form-control" id="value" name="value" value="1" placeholder="用于对接社区使用或导出时显示" onBlur="changeNum()">
<input type="hidden" id="price" value="">
</div>
<div class="form-group" id="show_result">
<label>直接显示内容:</label><br>
<textarea class="form-control" name="result" rows="5" placeholder="下单成功直接显示的处理信息。支持html代码（可留空）"></textarea>
<small><font color="green">下单成功直接显示的处理信息。支持html代码（可留空）</font></small>
</div>
<div id="GoodsInfo" class="alert alert-info" style="display:none;"></div>
</div>
<div class="form-group">
<label>下单后发送邮件提醒:</label><br>
<select class="form-control" name="is_email"><option value="0">关闭</option><option value="1">开启</option></select>
 <pre><font color="red">具体是否发送邮件通知以【<a href="./set.php?mod=order">主站设置->订单相关设置->下单后邮件通知我</a>】为准</font></pre>
</div>
</div>
</div>
<div class="col-sm-12 col-md-6">
<div class="block">
<div class="block-title"><h3 class="panel-title">商品基本信息设置</h3></div>
<div class="">
<div class="form-group">
<label>*商品分类:</label>&nbsp;(<a href="./classlist.php" target="_blank">管理</a>)<br>
<select name="cid" class="form-control" default="';
    echo $_GET['cid'];
    echo '">';
    echo $select;
    echo '</select>
</div>
<div class="form-group">
<label>*商品名称:</label><br>
<input type="text" class="form-control" name="name" value="" required>
</div>
<div class="form-group">
<label>支付名称:（发起支付时自定义名称）</label><br>
<input type="text" class="form-control" name="title" value="" placeholder="不需要自定义请留空">
</div>
<div class="form-group">
<label>已售销量:</label><br>
<input type="text" class="form-control" name="sale" placeholder="不需要自定义请留空" value="">
</div>
<div class="form-group">
<label>单位:</label><br>
<input type="text" class="form-control" name="unit" placeholder="默认为空" value="">
</div>
<div class="form-group">
<label>加价模板:</label>&nbsp;(<a href="./price.php" target="_blank">管理</a>)<br>
<select name="prid" id="prid" class="form-control">';
    echo $priceselect;
    echo '</select>
    <pre id="prid_tips" style="display:none"></pre>
</div>
<div class="form-group" id="price1_box">
<label>*成本价格:</label><br>
<input type="text" class="form-control" name="price1" value="">
<pre>当已设置加价模板时成本价被修改后鼠标离开输入框可自动改价</pre>
</div>
<table class="table table-striped table-bordered table-condensed" id="prid0">
<tbody>
<tr align="center"><td>*销售价格</td><td>专业版价格</td><td>旗舰版价格</td></tr>
<tr>
<td><input type="text" name="price" value="" class="form-control input-sm" placeholder="不能为空"/></td>
<td><input type="text" name="cost" value="" class="form-control input-sm" placeholder="不填写则同步销售价格"/></td>
<td><input type="text" name="cost2" value="" class="form-control input-sm" placeholder="不填写则同步普及版价格"/></td>
</tr>
</table>
<div class="form-group" id="option1" style="display:none">
<label>规格选项:</label><br>
<table class="table table-striped table-bordered table-condensed">
  <tbody>
    <input type="hidden" id="edit_num" value="1"/>
    <input type="hidden" id="edit_index" value="1"/>
    <tr><td colspan="7">规格选项 一行一个，成本价务必正确填写！其中数量是对接第三方平台用的&nbsp;&nbsp;<a class="btn btn-danger btn-sm" onclick="chenmObj.addOption(\'edit\')"><i class="fa fa-plus"></i>&nbsp;添加一个</a></td></tr>
    <tr align="center"><td>标题</td><td>数量</td><td>成本价</td><td>库存</td><td>图片</td><td>加价模板</td><td>操作</td></tr>
    <tbody id="edit_options">
    </tbody>
  </table>
</div>
<div class="form-group">
<label>批发价格满减设置:</label><br>
<input type="text" class="form-control" name="prices" placeholder="不懂请勿填写">
<pre><font color="green">填写格式：购满x个|订单总额减少x元,购满x个|订单总额减少x元 例如10|0.1,20|0.3,30|0.5</font></pre>
</div>
<div id="option_stock">
    <div class="form-group">
    <label>库存控制:</label><br>
    <select class="form-control" name="stock_open" default="0"><option value="0">0_关闭</option><option value="1">1_开启</option></select>
    <pre>开启后即可控制该商品库存（<span style="color:red">该配置与规格选项中的库存、卡密商品库存无法混用</span>）</pre>
    </div>
    <div class="form-group" id="option_stock_num" style="display:none">
    <label>库存数量:</label><br>
    <input type="text" class="form-control" name="stock" placeholder="99">
    <pre>客户付款后订单成功生成时，库存数量会自动减少</pre>
    </div>
</div>
<table class="table table-striped table-bordered table-condensed text-center" id="pay_type">
<tbody>
';
    if (is_array($row_class) && isset($row_class['hidepays']) && $row_class['hidepays']) {
        $hidepays = explode(',', $row_class['hidepays']);
        echo '<tr align="left"><td colspan="4"><span style="color:red">提示：当前所属分类已设置禁用支付方式[ ' . implode('、', $hidepays) . ' ]，此处对应的设置将会无效</span></td></tr>';
    }

    echo '
<tr align="center"><td>登录可见<span data-tip="开启后游客或代理下单必须登录后才能看到该商品" class="fa fa-question-circle fa-fw"></span></td><td>QQ钱包</td><td>微信支付</td><td>支付宝</td><td>余额支付</td></tr>
<tr>
<td>
<input type="checkbox" class="cmckb cmckb-flat" onclick="if(this.value==\'1\'){this.value=\'0\';}else{this.value=\'1\';};" name="close_login" id="close_login" value="0">
<label class="cmckb-btn" for="close_login"></label>
</td>
<td>
<input type="checkbox" class="cmckb cmckb-flat" checked onclick="if(this.value==\'1\'){this.value=\'0\';}else{this.value=\'1\';};" name="pay_qqpay" id="pay_qqpay" value="1">
<label class="cmckb-btn" for="pay_qqpay"></label>
</td>
<td>
<input type="checkbox" class="cmckb cmckb-flat" checked onclick="if(this.value==\'1\'){this.value=\'0\';}else{this.value=\'1\';};" name="pay_wxpay" id="pay_wxpay" value="1">
<label class="cmckb-btn" for="pay_wxpay"></label>
</td>
<td>
<input type="checkbox" class="cmckb cmckb-flat" checked onclick="if(this.value==\'1\'){this.value=\'0\';}else{this.value=\'1\';};" name="pay_alipay" id="pay_alipay" value="1">
<label class="cmckb-btn" for="pay_alipay"></label>
</td>
<td>
<input type="checkbox" class="cmckb cmckb-flat" checked onclick="if(this.value==\'1\'){this.value=\'0\';}else{this.value=\'1\';};" name="pay_rmb" id="pay_rmb" value="1">
<label class="cmckb-btn" for="pay_rmb"></label>
</td>
</tr>
</table>
<div class="form-group">
<label>第一个输入框标题:</label><br>
<div class="input-group">
<input type="text" class="form-control" name="input" value="" placeholder="留空默认为“下单ＱＱ”">
<span class="input-group-btn"><a href="#inputabout" data-toggle="modal" class="btn btn-info" title="说明">一键填写</a></span>
</div>
<pre><font color="green">如需实现自动转换功能，请点击‘一键填写’按钮！！<br/>显示选择框，在名称后面加{选择1,选择2}，例如：分类名{普通,音乐,宠物}<br/>显示提示语，在名称后面加&提示语，例如：下单QQ&输入下单QQ</font></pre>
</div>
<div class="form-group">
<label>更多输入框标题:</label><br>
<div class="input-group">
<input type="text" class="form-control" name="inputs" value="" placeholder="留空则不显示更多输入框">
<span class="input-group-btn"><a href="#inputsabout" data-toggle="modal" class="btn btn-info" title="说明">一键填写</a></span>
</div>
<pre><font color="green">如需实现获取ID等功能，请点击‘一键填写’按钮！！<br/>多个输入框请用|隔开(不能超过4个)<br/>显示选择框，在名称后面加{选择1,选择2}，例如：分类名{普通,音乐,宠物}<br/>显示提示语，在名称后面加&提示语，例如：下单QQ&输入下单QQ</font><br/><span style="color:red">注意：本系统暂不支持倍数下单加价，请不要当成社区/供货批发系统使用</span></pre>
</div>
<div class="form-group">
<label>商品简介:</label>(没有请留空) <a href="javascript:;" class="emptyText btn-link">清空内容</a><br>
<div class="" id="editorBox"></div>
<textarea class="form-control hide textDom" name="desc" rows="3" placeholder="当选择该商品时自动显示，支持HTML代码"></textarea>
</div>
<div class="form-group">
<label>提示内容:</label>(没有请留空) <a href="javascript:;" class="emptyText2 btn-link">清空内容</a><br>
<div class="" id="editorBox2"></div>
<textarea class="form-control hide textDom2" name="alert" rows="3" placeholder="当选择该商品时自动弹出该内容，支持HTML代码"></textarea>
</div>
<div class="form-group">
<label>商品图片:</label><br>
<input type="file" id="file" onchange="fileUpload()" style="display:none;"/>
<div class="input-group">
<input type="text" class="form-control" id="shopimg" name="shopimg" value="" placeholder="填写图片URL，没有请留空"><span class="input-group-btn"><a href="javascript:fileSelect()" class="btn btn-success" title="上传图片"><i class="glyphicon glyphicon-upload"></i></a><a href="javascript:fileView()" class="btn btn-warning" title="查看图片"><i class="glyphicon glyphicon-picture"></i></a></span>
</div>
</div>
<div class="form-group">
<label>*显示数量选择框:</label><br>
<select class="form-control" name="multi" default="1"><option value="1">1_是</option><option value="0">0_否</option></select>
</div>
<table class="table table-striped table-bordered table-condensed" id="multi0" style="display:none;">
<tbody>
<tr align="center"><td>最小下单数量</td><td>最大下单数量</td></tr>
<tr>
<td><input type="text" name="min" value="1" class="form-control input-sm" placeholder="留空则默认为1"/></td>
<td><input type="text" name="max" value="0" class="form-control input-sm" placeholder="留空则不限数量"/></td>
</tr>
</table>
<div class="form-group">
<label>*允许重复下单:</label><br>
<select class="form-control" name="repeat"><option value="1">1_是</option><option value="0">0_否</option></select>
</div>
<div class="form-group">
<label>计入排行销售额:</label><br>
<select class="form-control" name="is_rank" default="1">
<option value="1">1_是</option><option value="0">0_否</option></select>
<pre>选择计入排行销售额即可作为排行统计依据，如果不计入分站将不会获得对应奖励，例如QQ钻等利润较低的商品可以不计入</pre>
</div>
<a onclick="chenmObj.shopAdd()" class="btn btn-primary btn-block">确定添加</a>
<br/><a href="';
    echo $_SERVER['HTTP_REFERER'] != '' ? $_SERVER['HTTP_REFERER'] : './shoplist.php';
    echo '">>>返回商品列表</a>
</div></div>
</div>
</form>
';
} else {
    if ($my == 'edit') {
        $tid = intval($_GET['tid']);
        $row = $DB->get_row('select * from cmy_tools where tid=\'' . $tid . '\' limit 1');
        if (isset($row['cid']) && !empty($row['cid'])) {
            $row_class = $DB->get_row('SELECT * from `pre_class` where cid=\'' . $row['cid'] . '\' limit 1');
        }
        echo '
<form id="editForm" action="./shopedit.php?my=edit_submit&tid=';
        echo $tid;
        echo '" method="POST">
        <div class="col-sm-12 col-md-6">
<div class="block">
<div class="block-title"><h3 class="panel-title">商品类型与对接设置</h3></div>
<div class="">
<input type="hidden" id="tid" value="';
        echo intval(input('get.tid'));
        echo '"/>
<input type="hidden" id="action" value="edit"/>
<input type="hidden" id="backurl" value="';
        echo $_SERVER['HTTP_REFERER'];
        echo '"/>
<div class="form-group">
<label>购买成功后的动作:</label><br>
<select class="form-control" name="is_curl" default="';
        echo $row['is_curl'];
        echo '"><option value="0">0_无</option><option value="2">自动提交到进货站点</option><option value="1">自定义访问URL/POST</option><option value="4">卡密商品自动发货</option><option value="3">自动发送提醒邮件</option><option value="5">直接显示内容</option></select>
</div>
<div id="curl_display1" style="';
        echo ($row['is_curl'] != 1 ? 'display:none;' : null);
        echo '">
<div class="form-group">
<label>* URL:<span style="color:red">（必填）</span></label><br>
<input type="text" class="form-control" name="curl" id="curl" value="';
        echo $row['curl'];
        echo '">

</div>
<font color="green">此处URL必须先填要访问的url地址和GET数据，如https://www.test.cn/?value=[input]&value2=[input2]&num=[num]</font>
<br/>
<div class="form-group">
<label>POST:</label><br>
<input type="text" class="form-control" name="curl_post" id="curl_post" value="';
        echo $row['goods_param'];
        echo '" placeholder="无POST内容请留空">
</div>
<font color="green">无POST内容请留空，POST格式{键值对}：a=123&b=456<br/>变量代码：<br/>
<a href="#" onclick="Addstr(\'curl\',\'[input]\');return false">[input]</a>&nbsp;第一个输入框内容<br/>
<a href="#" onclick="Addstr(\'curl\',\'[input2]\');return false">[input2]</a>&nbsp;第二个输入框内容<br/>
<a href="#" onclick="Addstr(\'curl\',\'[input3]\');return false">[input3]</a>&nbsp;第三个输入框内容<br/>
<a href="#" onclick="Addstr(\'curl\',\'[input4]\');return false">[input4]</a>&nbsp;第四个输入框内容<br/>
<a href="#" onclick="Addstr(\'curl\',\'[name]\');return false">[name]</a>&nbsp;商品名称<br/>
<a href="#" onclick="Addstr(\'curl\',\'[price]\');return false">[price]</a>&nbsp;商品单价<br/>
<a href="#" onclick="Addstr(\'curl\',\'[money]\');return false">[money]</a>&nbsp;订单金额<br/>
<a href="#" onclick="Addstr(\'curl\',\'[num]\');return false">[num]</a>&nbsp;下单数量<br/>
<a href="#" onclick="Addstr(\'curl\',\'[time]\');return false">[time]</a>&nbsp;当前时间戳<br/></font>
<br/>
</div>
<div id="curl_display2" style="';
        echo ($row['is_curl'] != 2 ? 'display:none;' : null);
        echo '">
<div class="form-group">
<label>选择对接网站:&nbsp;(<a href="./shequlist.php" target="_blank">管理</a>)</label><br>
<select class="form-control" name="shequ" default="';
        echo $row['shequ'];
        echo '">';
        echo $shequselect;
        echo '</select>
        <small style="color:red">如果获取失败或提示网站打开失败，请尝试切换该对接站代理服务器开关</small>
</div>
<div class="form-group" id="show_goodsclass1" style="display:none;">
  <label>选择一级目录:</label>  <span class="btn btn-xs btn-info" id="kyxmoney" onclick="kyxmoney()">查余额</span>  <span class="btn btn-xs btn-info" id="kyxjump" onclick="kyxjump()">到目录</span> <br>
  <div class="input-group">
  <select class="form-control" id="goodsclass1"></select>
  <span class="input-group-addon btn btn-success" id="getclass">获取分类</span>
  </div>
  <small style="color:red" id="goodsclasstips">如分类过多或对方服务器出现卡顿，会获取比较慢！如果获取失败请重新获取几次</small>
</div>
<div class="form-group" id="show_classlist" style="display:none;">
  <label>选择商品分类:</label>
  <div class="input-group">
      <select class="form-control" id="classlist_select"></select>
      <span class="input-group-addon btn btn-success" id="getGoods_class">一键获取</span>
  </div>
</div>
<div class="form-group" id="show_goodslist">
    <label>选择对接商品:</label><br>
    <div class="input-group">
        <div class="input-group-addon lastPage" title="上一页" style="display:none">
                <i class="fa fa-chevron-left"></i>
        </div>
        <select class="form-control" id="goodslist" default="';
        echo $row['goods_id'];
        echo '"></select>
        <span class="input-group-addon btn btn-success" id="getGoods">获取</span>
        <div class="input-group-addon nextPage" title="下一页" style="display:none">
            <i class="fa fa-chevron-right"></i>
        </div>
    </div>
  <small id="kayixin_tips" style="color:red">如商品过多或对方服务器出现卡顿，会获取比较慢<br/>商品太多时，建议你直接填写商品ID可直接获取参数</small>
</div>
<div class="form-group" id="goods_id">
<label>商品ID（goods_id）:</label><br>
<input type="text" class="form-control" name="goods_id" value="';
        echo $row['goods_id'];
        echo '">
        <small id="goods_id_tips" style="display:none;"></small>
</div>
<div class="form-group" id="goods_type">
<label>类型ID（goods_type）:</label><br>
<input type="text" class="form-control" name="goods_type" value="';
        echo $row['goods_type'];
        echo '">
</div>
<div class="form-group" id="goods_param">
<label id="goods_param_name">参数名:</label><br>
<input type="text" class="form-control" name="goods_param" value="';
        echo $row['goods_param'];
        echo '">
<pre><font color="green" id="goods_param_tips">对接那边下单输入框的参数，多个参数请用|隔开（社区和卡商可自动获取）；如果是对接KM和云宝，请直接填写下单页面地址</font></pre>
</div>
<div class="form-group" id="goods_card_display" style="display:none">
<label id="goods_card">卡号(九流用):</label><br>
<input type="text" class="form-control" name="goods_card" value="';
        echo $row['goods_card'];
        echo '">
<pre><font color="green">九流社区的商品卡号，填写错误无法对接</font></pre>
</div>
<div class="form-group" id="goods_cardpass_display" style="display:none">
<label id="goods_cardpass">卡号密码(九流用):</label><br>
<input type="text" class="form-control" name="goods_cardpass" value="';
        echo $row['goods_cardpass'];
        echo '">
<pre><font color="green">九流社区的商品卡号密码，填写错误无法对接</font></pre>
</div>
</div>
<div class="form-group" id="show_value">
<label>默认数量信息:</label><br>
<input type="text" class="form-control" id="value" name="value"  value="';
        echo $row['value'];
        echo '" placeholder="用于对接社区使用或导出时显示" onBlur="changeNum()">
<input type="hidden" id="price" value="">
</div>
<div class="form-group" id="show_result">
<label>直接显示内容:</label><br>
<textarea class="form-control" name="result" rows="5" placeholder="下单成功直接显示的处理信息。支持html代码（可留空）">';
        echo htmlspecialchars($row['result']);
        echo '</textarea>
<small><font color="green">下单成功直接显示的处理信息。支持html代码（可留空）</font></small>
</div>
<div id="GoodsInfo" class="alert alert-info" style="display:none;"></div>
</div>
<div class="form-group">
<label>下单后发送邮件提醒:</label><br>
<select class="form-control" name="is_email" default="';
        echo $row['is_email'];
        echo '"><option value="0">关闭</option><option value="1">开启</option></select>
 <pre><font color="red">具体是否发送邮件通知以【<a href="./set.php?mod=order">主站设置->订单相关设置->下单后邮件通知我</a>】为准</font></pre>
</div>
</div>
</div>
<div class="col-sm-12 col-md-6">
<div class="block">
<div class="block-title"><h3 class="panel-title">商品基本信息设置</h3></div>
<div class="">
<div class="form-group">
<label>商品分类:</label>&nbsp;(<a href="./classlist.php" target="_blank">管理</a>)<br>
<select name="cid" class="form-control" default="';
        echo $row['cid'];
        echo '">';
        echo $select;
        echo '</select>
</div>
<div class="form-group">
<label>商品名称:</label><br>
<input type="text" class="form-control" name="name" value="';
        echo $row['name'];
        echo '" required>
</div>
<div class="form-group">
<label>支付名称:（发起支付时自定义名称）</label><br>
<input type="text" class="form-control" name="title" placeholder="不需要自定义请留空" value="';
        echo $row['title'];
        echo '">
</div>
<div class="form-group">
<label>已售销量:</label><br>
<input type="text" class="form-control" name="sale" placeholder="不需要自定义请留空" value="';
        echo $row['sale'];
        echo '">
</div>
<div class="form-group">
<label>单位:</label><br>
<input type="text" class="form-control" name="unit" placeholder="默认为空" value="';
        echo $row['unit'];
        echo '">
</div>
<div class="form-group">
<label>加价模板:</label>&nbsp;(<a href="./price.php" target="_blank">管理</a>)<br>
<select name="prid" class="form-control" default="';
        echo $row['prid'];
        echo '">';
        echo $priceselect;
        echo '</select>
        <pre id="prid_tips" style="display:none"></pre>
</div>
<div class="form-group" id="price1_box">
<label>*成本价格:</label><br>
<input type="text" class="form-control" name="price1" value="';
        echo $row['price1'];
        echo '">
    <pre id="price1_tips">当已设置加价模板时成本价被修改后鼠标离开输入框可自动改价</pre>
</div>
<table class="table table-striped table-bordered table-condensed" id="prid0">
<tbody>
<tr align="center"><td>*销售价格</td><td>专业版价格</td><td>旗舰版价格</td></tr>
<tr>
<td><input type="text" name="price" value="';
        echo $row['price'];
        echo '" class="form-control input-sm"/></td>
<td><input type="text" name="cost" value="';
        echo $row['cost'];
        echo '" class="form-control input-sm" placeholder="不填写则同步销售价格"/></td>
<td><input type="text" name="cost2" value="';
        echo $row['cost2'];
        echo '" class="form-control input-sm" placeholder="不填写则同步普及版价格"/></td>
</tr>
</table>
<div class="form-group" id="option1" style="display:none">
<label>规格选项:</label><br>
<table class="table table-striped table-bordered table-condensed">
  <tbody>
    <input type="hidden" id="edit_num" value="1"/>
    <input type="hidden" id="edit_index" value="1"/>
    <tr><td colspan="7">规格选项 一行一个，成本价务必正确填写！其中数量是对接第三方平台用的&nbsp;&nbsp;<a class="btn btn-danger btn-sm" onclick="chenmObj.addOption(\'edit\')"><i class="fa fa-plus"></i>&nbsp;添加一个</a></td></tr>
    <tr align="center"><td>标题</td><td>数量</td><td>成本价</td><td>库存</td><td>图片</td><td>加价模板</td><td>操作</td></tr>
    <tbody id="edit_options">
    </tbody>
  </table>
</div>
<div class="form-group">
<label>批发价格优惠设置:</label><br>
<input type="text" class="form-control" name="prices" value="';
        echo $row['prices'];
        echo '">
<pre><font color="green">填写格式：购满x个|订单总额减少x元,购满x个|订单总额减少x元 例如10|0.1,20|0.3,30|0.5</font></pre>
</div>
<div id="option_stock">
    <div class="form-group">
    <label>库存控制:</label><br>
    <select class="form-control" name="stock_open" default="';
        echo $row['stock_open'] == 1 ? '1' : '0';
        echo '"><option value="0">0_关闭</option><option value="1">1_开启</option></select>
    <pre>开启后即可控制该商品库存（<span style="color:red">该配置与规格选项中的库存、卡密商品库存无法混用</span>）</pre>
    </div>
    <div class="form-group" id="option_stock_num" style="display:none">
    <label>库存数量:</label><br>
    <input type="text" class="form-control" name="stock" value="';
        echo $row['stock'] > 0 ? $row['stock'] : '';
        echo '" placeholder="库存数量 如：99">
        <pre>客户付款后订单成功生成时，库存数量会自动减少</pre>
    </div>
</div>
<table class="table table-striped table-bordered table-condensed text-center" id="pay_type">
<tbody>
';
        if (is_array($row_class) && !empty($row_class['hidepays'])) {
            $hidepays = explode(',', $row_class['hidepays']);
            echo '<tr align="left"><td colspan="4"><span style="color:red">提示：当前所属分类已设置禁用支付方式[ ' . implode('、', $hidepays) . ' ]，此处对应的设置将会无效</span></td></tr>';
        }

        echo '
<tr align="center"><td>登录可见<span data-tip="开启后游客或代理下单必须登录后才能看到该商品" class="fa fa-question-circle fa-fw"></span></td><td>QQ钱包</td><td>微信支付</td><td>支付宝</td><td>余额支付</td></tr>
<tr>
<td>
<input type="checkbox" class="cmckb cmckb-flat" onclick="if(this.value==\'1\'){this.value=\'0\';}else{this.value=\'1\';};" name="close_login" id="close_login" ';
        echo $row['close_login'] == 1 ? 'checked="checked" value="1"' : 'value="0"';
        echo '>
<label class="cmckb-btn" for="close_login"></label>
</td>
<td>
<input type="checkbox" class="cmckb cmckb-flat" onclick="if(this.value==\'1\'){this.value=\'0\';}else{this.value=\'1\';};" name="pay_qqpay" id="pay_qqpay" ';
        echo $row['pay_qqpay'] == 1 ? 'checked="checked" value="1"' : 'value="0"';
        echo '>
<label class="cmckb-btn" for="pay_qqpay"></label>
</td>
<td>
<input type="checkbox" class="cmckb cmckb-flat" onclick="if(this.value==\'1\'){this.value=\'0\';}else{this.value=\'1\';};" name="pay_wxpay" id="pay_wxpay" ';
        echo $row['pay_wxpay'] == 1 ? 'checked="checked" value="1"' : 'value="0"';
        echo '>
<label class="cmckb-btn" for="pay_wxpay"></label>
</td>
<td>
<input type="checkbox" class="cmckb cmckb-flat" onclick="if(this.value==\'1\'){this.value=\'0\';}else{this.value=\'1\';};" name="pay_alipay" id="pay_alipay" ';
        echo $row['pay_alipay'] == 1 ? 'checked="checked" value="1"' : 'value="0"';
        echo '>
<label class="cmckb-btn" for="pay_alipay"></label>
</td>
<td>
<input type="checkbox" class="cmckb cmckb-flat" onclick="if(this.value==\'1\'){this.value=\'0\';}else{this.value=\'1\';};" name="pay_rmb" id="pay_rmb" ';
        echo $row['pay_rmb'] == 1 ? 'checked="checked" value="1"' : 'value="0"';
        echo '>
<label class="cmckb-btn" for="pay_rmb"></label>
</td>
</tr>
</table>
<div class="form-group">
<label>第一个输入框标题:</label><br>
<div class="input-group">
<input type="text" class="form-control" name="input" value="';
        echo $row['input'];
        echo '" placeholder="留空默认为“下单ＱＱ”">
<span class="input-group-btn"><a href="#inputabout" data-toggle="modal" class="btn btn-info" title="说明">一键填写</a></span>
</div>
<pre><font color="green">显示选择框，在名称后面加{选择1,选择2}，例如：分类名{普通,音乐,宠物}<br/>显示提示语，在名称后面加&提示语，例如：下单QQ&输入下单QQ</font></pre>
</div>
<div class="form-group">
<label>更多输入框标题:</label><br>
<div class="input-group">
<input type="text" class="form-control" name="inputs" value="';
        echo $row['inputs'];
        echo '" placeholder="留空则不显示更多输入框">
<span class="input-group-btn"><a href="#inputsabout" data-toggle="modal" class="btn btn-info" title="说明">一键填写</a></span>
</div>
<pre><font color="green">多个输入框请用|隔开(不能超过4个)<br/>显示选择框，在名称后面加{选择1,选择2}，例如：分类名{普通,音乐,宠物}<br/>显示提示语，在名称后面加&提示语，例如：下单QQ&输入下单QQ</font><br/><span style="color:red">注意：本系统暂不支持倍数下单加价，请不要当成社区/供货批发系统使用</span></pre>
</div>
<div class="form-group">
<label>商品简介:</label>(没有请留空) <a href="javascript:;" class="emptyText btn-link">清空内容</a><br>
<div class="" id="editorBox"></div>
<textarea class="form-control hide textDom" name="desc" rows="3" placeholder="当选择该商品时自动显示，支持HTML代码">';
        echo htmlspecialchars($row['desc']);
        echo '</textarea>
</div>
<div class="form-group">
<label>提示内容:</label>(没有请留空) <a href="javascript:;" class="emptyText2 btn-link">清空内容</a><br>
<div class="" id="editorBox2"></div>
<textarea class="form-control hide textDom2" name="alert" rows="3" placeholder="当选择该商品时自动弹出该内容，支持HTML代码">';
        echo htmlspecialchars($row['alert']);
        echo '</textarea>
</div>
<div class="form-group">
<label>商品图片:</label><br>
<input type="file" id="file" onchange="fileUpload()" style="display:none;"/>
<div class="input-group">
<input type="text" class="form-control" id="shopimg" name="shopimg" value="';
        echo $row['shopimg'];
        echo '" placeholder="填写图片URL，没有请留空"><span class="input-group-btn"><a href="javascript:fileSelect()" class="btn btn-success" title="上传图片"><i class="glyphicon glyphicon-upload"></i></a><a href="javascript:fileView()" class="btn btn-warning" title="查看图片"><i class="glyphicon glyphicon-picture"></i></a></span>
</div>
</div>
<div class="form-group">
<label>显示数量选择框:</label><br>
<select class="form-control" name="multi" default="';
        echo $row['multi'];
        echo '"><option value="1">1_是</option><option value="0">0_否</option></select>
</div>
<table class="table table-striped table-bordered table-condensed" id="multi0" style="display:none;">
<tbody>
<tr align="center"><td>最小下单数量</td><td>最大下单数量</td></tr>
<tr>
<td><input type="text" name="min" class="form-control input-sm" value="';
        echo $row['min'];
        echo '" placeholder="留空则默认为1"/></td>
<td><input type="text" name="max" class="form-control input-sm" value="';
        echo $row['max'];
        echo '" placeholder="留空则不限数量"/></td>
</tr>
</table>

<div class="form-group">
<label>允许重复下单:</label><br>
<select class="form-control" name="repeat" default="';
        echo $row['repeat'];
        echo '"><option value="0">0_否</option><option value="1">1_是</option></select>
</div>
<div class="form-group">
<label>计入排行销售额:</label><br>
<select class="form-control" name="is_rank" default="';
        echo intval($row['is_rank']);
        echo '"><option value="1">1_是</option><option value="0">0_否</option></select>
<pre>选择计入排行销售额即可作为排行统计依据，如果不计入分站将不会获得对应奖励，例如QQ钻等利润较低的商品可以不计入</pre>
</div>
<a onclick="chenmObj.shopEdit()" class="btn btn-primary btn-block">确定修改</a>
<br/><a href="';
        echo $_SERVER['HTTP_REFERER'] != '' ? $_SERVER['HTTP_REFERER'] : './shoplist.php';
        echo '">>>返回商品列表</a>
</div></div>
</div>
</form>
';
    } else {

        if ($my == 'delete') {
            $tid = $_GET['tid'];
            $sql = 'DELETE FROM cmy_tools WHERE tid=\'' . $tid . '\' limit 1';
            if ($DB->query($sql)) {
                showmsg('删除成功！<br/><br/><a href="' . $_SERVER['HTTP_REFERER'] . '">>>返回商品列表</a>', 1);
            } else {
                showmsg('删除失败！' . $DB->error(), 4);
            }
        }
    }
}

echo '
      <div id="prid1" style="display:none;"></div>
      <script src="./assets/js/shopedit.js' . $jsver . '"></script>
      <script type="text/javascript">
        if(typeof pageLoad == "undefined" || pageLoad!==true){
            layer.alert("缺少静态js文件，疑似更新下载不完整或被误删除，请前往【修复工具】尝试一键修复后再试！");
        }
        runTip();
        </script>
      ';
include_once 'footer.php';
