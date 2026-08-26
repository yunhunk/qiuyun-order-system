
<?php

/**
 * 供货商品管理
 **/

use core\Db;

include "../includes/common.php";
$title = '供货商品管理';
checkLogin();

$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
if ($act == 'delete') {
    $id  = intval(input('id', 1));
    $sql = "DELETE from `pre_tools` where `tid`='" . $id . "'";
    if ($DB->exec($sql) !== false) {
        $result = array('code' => 0, 'msg' => "删除商品ID => " . $id . "成功！");
    } else {
        $result = array('code' => -1, 'msg' => '删除商品失败, ' . $DB->error());
    }
    exit(json_encode($result));
} elseif ($act == 'info') {
    $tid = intval(input('tid', 1));
    $row = Db::name('tools')->find(['tid' => $tid]);
    if ($row) {
        $result = array('code' => 0, 'msg' => "成功", 'data' => $row);
    } else {
        $result = array('code' => -1, 'msg' => '该商品不存在 => ' . $tid);
    }
    exit(json_encode($result));
} elseif ($act == 'onSave') {
    $tid = intval(input('tid', 1));
    $row = Db::name('tools')->find(['tid' => $tid]);
    if ($row) {
        $post = input('post.');
        unset($post['tid']);
        $update = Db::name('tools')->where(['tid' => $tid])->update($post);
        if ($update !== false) {
            $result = array('code' => 0, 'msg' => "成功", 'data' => $row);
        } else {
            $result = array('code' => -1, 'msg' => '保存失败, 数据库错误=>' . $DB->error());
        }

    } else {
        $result = array('code' => -1, 'msg' => '该商品不存在 => ' . $tid);
    }
    exit(json_encode($result));
} elseif ($act == 'change') {
    $tid    = intval(input('tid', 1));
    $active = intval(input('active', 1));
    $sql    = Db::name('tools')->where(['tid' => $tid])->update(['active' => $active]);
    if ($sql !== false) {
        $result = array('code' => 0, 'msg' => "切换成功！");
    } else {
        $result = array('code' => -1, 'msg' => '切换失败, ' . $DB->error());
    }
    exit(json_encode($result));
}

include './head.php';

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
            $select .= '<option value="' . $res2['cid'] . '">|--' . $res2['name'] . '</option>';
        }
    }
}

$rs          = $DB->query("SELECT * FROM pre_price order by id asc");
$p_arr       = array();
$p_rows      = array();
$i           = 0;
$priceselect = "<option value=\"0\" kind=\"0\">0_不选择模板</option>";
while ($res = $DB->fetch($rs)) {
    switch ($res["kind"]) {
        case 1:
            $priceselect .= '<option value="' . $res["id"] . '" kind="' . $res["kind"] . '" p_2="' . $res["p_2"] . '"  p_1="' . $res["p_1"] . '" p_0="' . $res["p_0"] . '">' . $res["name"] . '（+' . $res["p_0"] . '元|+' . $res["p_1"] . '元|+' . $res["p_2"] . '元）</option>';
            break;
        default:
            $priceselect .= '<option value="' . $res["id"] . '" kind="' . $res["kind"] . '" p_2="' . $res["p_2"] . '"  p_1="' . $res["p_1"] . '" p_0="' . $res["p_0"] . '">' . $res["name"] . '（+' . $res["p_0"] . '%|+' . $res["p_1"] . '%|+' . $res["p_2"] . '%）</option>';
            break;
    }
    $p_arr[$i]          = $res;
    $p_rows[$res["id"]] = $res;
    $i++;
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

?>

<div class="col-xs-12 col-sm-12 col-lg-12 center-block" style="float: none;padding-top:10px;">
    <!-- 模板编辑/添加-->
   <div class="modal fade" id="save_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">供货商品审核</h4>
            </div>
            <div class="modal-body">
					<form id="form-save">
                        <input type="hidden" id="tid" name="tid" value=""/>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">商品名称:</div>
                                <input class="form-control" value="" name="name">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">输入框标题:</div>
                                <input class="form-control" value="" name="input">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">更多标题:</div>
                                <input class="form-control" value="" name="inputs">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">加价模板:</div>
                                <select class="form-control" name="prid">
                                    <?php echo $priceselect; ?>
                                </select>
                            </div>
                        </div>
                            <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">成本价格:</div>
                                <input type="text" name="price1" value="" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <table class="table table-striped table-bordered table-condensed" id="prid0">
                            <tbody>
                            <tr align="center"><td>*销售价格</td><td>普及版价格</td><td>专业版价格</td></tr>
                            <tr>
                            <td><input type="text" name="price" value="10.00" class="form-control input-sm"></td>
                            <td><input type="text" name="cost" value="10.00" class="form-control input-sm" placeholder="不填写则同步销售价格"></td>
                            <td><input type="text" name="cost2" value="10.00" class="form-control input-sm" placeholder="不填写则同步普及版价格"></td>
                            </tr>
                            </tbody></table>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">商品介绍:</div>
                                <div class="" id="editorBox"></div>
                                <textarea class="form-control hide textDom" name="desc" rows="3" placeholder=""></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">提示内容:</div>
                                <textarea class="form-control" name="alert" rows="2" placeholder="当选择该商品时自动显示, 支持HTML代码"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">显示数量选择框:</div>
                                <select class="form-control" name="multi">
                                    <option value="1">1_是</option>
                                    <option value="0">0_否</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">允许重复下单:</div>
                                <select class="form-control" name="repeat">
                                    <option value="1">1_是</option>
                                    <option value="0">0_否</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">审核失败备注:</div>
                                <textarea class="form-control" name="condition_msg" rows="4" placeholder="审核失败备注"></textarea>
                            </div>
                        </div>
                    </form>
					<div class="form-group">
					    <div style="width: 100%; display: flex;justify-content: space-between;">
                            <div style="width: 49%;"><a data-condition="1" class="btn btn-primary btn-block onSaveTool">通过审核</a></div>
                            <div style="width: 49%;"><a class="btn btn-danger btn-block onSaveTool" data-condition="2" data-dismiss="modal">驳回审核</a></div>
                        </div>
					</div>
            </div>
        </div>
    </div>
</div>
    <!-- 模板编辑 end -->

<?php
if (isset($_GET['zid']) && $_GET['zid']) {
    $sql  = ' `zid`>0 AND `is_curl`!=2 and zid=' . input('zid');
    $link = '&zid=' . input('zid');
} elseif (isset($_GET['kw']) && $_GET['kw']) {
    $kw   = input('kw', 1);
    $sql  = ' `zid`>0 AND `is_curl`!=2 and (zid=\'' . $kw . '\' OR name LIKE \'%' . $kw . '%\')';
    $link = '&kw=' . $kw;
} else {
    $sql = " `zid`>0 AND `is_curl`!=2 AND `condition` != 1";
}

$numrows  = $DB->count("SELECT count(*) from `cmy_tools` WHERE " . $sql);
$pagesize = 30;
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
?>
 <div class="block">
    <div class="block-title">
      <h3 class="panel-title"><?php echo $title; ?></h3>
    </div>
       <div class="alert alert-info">系统共有 <b><?php echo $numrows; ?></b> 个商品待审核<br/>
        </div>
        <form onsubmit="return searchItem()" method="GET" class="form-inline">
            <div class="form-group">
                <input type="text" class="form-control" name="kw" placeholder="请输入商品名称、供货商ID">
            </div>
            <button type="submit" class="btn btn-success">搜索</button>&nbsp;
            <a href="./master.check.php" class="btn btn-default" title="刷新商品列表"><i class="fa fa-refresh"></i></a>
        </form>
        <div class="table-responsive">
        <table class="table table-striped" style="word-break: break-all;margin-bottom: 135px;">
          <thead><th>TID</th><th>所属供货商</th><th>商品名称</th><th>售卖价格</th><th>所属分类</th><th>操作</th></thead>
          <tbody>
<?php

$rs = $DB->query("SELECT * FROM `cmy_tools` WHERE {$sql} order by tid desc limit $offset,$pagesize");

while ($res = $DB->fetch($rs)) {

    $userInfo = Db::name('master')->get(['zid' => $res['zid']]);
    ?>
    <tr id="tr_<?php echo $res['tid']; ?>">
        <td><div class="checkbox-inline checkbox-md"><input class="" type="checkbox" name="checkbox[]" id="list1" value="<?php echo $res['tid']; ?>"><b><?php echo $res['tid']; ?></b></div></td>
        <td><?php echo $res['zid']; ?>(<?php echo $userInfo ? $userInfo['user'] : '商户不存在'; ?>)</td>
        <td><?php echo $res['name']; ?></td>
        <td><?php echo $res['price']; ?></td>
        <td><a href="?cid=<?php echo $res['cid']; ?>"><?php echo $pre_class[$res['cid']]; ?></a> </td>
        <td>
            <span id="check_ok" title="通过" data-tid="<?php echo $res['tid']; ?>" class="btn btn-success btn-xs">通过</span>
            <span id="check_ok" title="驳回" data-tid="<?php echo $res['tid']; ?>" class="btn btn-warning btn-xs">驳回</span>
            <span id="onDel" title="删除"  data-id="<?php echo $res['tid']; ?>" class="btn btn-danger btn-xs">删除</span>
        </td>
    </tr>
<?php
}

echo ' </tbody>
        </table>
      </div>';

if (!checkmobile()) {
    echo '<style>.fllist{}</style>';
    echo '<footer class="navbar-fixed-bottom">
             <div class="paging-navbar">';
} else {
    echo '<div style="display:block;height:50px;"></div>';
    echo '<style>.fllist{max-width:130px}</style>';
    echo '<footer class="navbar-fixed-bottom">
            <div class="paging-navbar"><div style="padding:5px 8px;">';
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
                <div class="form-group">
                    <div class="input-group">
                        <select name="aid" class="form-control">
                            <option selected="">
                                批量操作
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
                            <option value="16">
                                >设置加价模板
                            </option>
                            <option value="5">
                                >删除选中
                            </option>
                            <option value="6">
                                >复制选中
                            </option>
                        </select>
                        <div class="input-group-addon btn btn-info btn-sm"><a onclick="change()" >执行</a></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <select name="cid" class="form-control" class="">
                            <option selected>将选定商品移动到</option>
                            ' . $select . '
                        </select>
                        <div class="input-group-addon btn btn-primary btn-sm">
                            <a onclick="move()">确定移动</a>
                        </div>
                    </div>
                </div>
        </div>
        </div>
        </div>
        </footer>
        ';

#分页
$pageList = new \core\Page($numrows, $pagesize, 0, $link);
echo $pageList->showPage();

echo '</div>';

echo '<script src="' . $cdnserver . 'assets/public/wangEditor/3.1.1/wangEditor.js?' . $jsver . '" type="text/javascript"></script>
	  <script src="' . $cdnserver . 'assets/public/wangEditor/3.1.1/plugin.js?' . $jsver . '" type="text/javascript"></script>
	  ';

echo '
    <script src="' . $cdnserver . 'assets/public/wangEditor/3.1.1/wangEditor.js?' . $jsver . '" type="text/javascript"></script>
    <script src="' . $cdnserver . 'assets/public/wangEditor/3.1.1/plugin.js?' . $jsver . '" type="text/javascript"></script>
    ';
echo '
<script type="text/javascript">
';
echo '
	var $is_mb = \'' . ($is_mb === true ? '1' : '0') . '\';
	var E = window.wangEditor ? window.wangEditor : null;
	var editor2,editor3,content,content2;
	';
echo '
function editorInit() {
	if ($(\'.textDom\').length>0 && "function" === typeof window.wangEditor) {
	    if ($(\'.textDom\').length > 0) {
			content = $(\'.textDom\');
		}
		$("#editorBox").show();
		editor2 = new E(\'#editorBox\');
		if(typeof boxType != "undefined"){
			$(\'#editorBox\').css({\'display\':\'block\',\'width\':\'100%\',\'float\':\'none\'});
		}
		editor2.customConfig.uploadImgServer = \'upload.php\';
		editor2.customConfig.uploadImgTimeout = 60 * 1000;
		editor2.customConfig.uploadFileName = \'filedata\';
		editor2.customConfig.mobile = $is_mb;
		editor2.customConfig.uploadImgHooks = {
		    success: function (xhr, editor, result) {
		    	   if (result.errno==0) {
		             layer.msg("图片插入成功");
		    	   }
		    	   else{
		    	   	    if (!result.msg) {
			             	result.msg="上传图片失败！请检查";
			    	    }
			    	    layer.alert(result.msg);
		    	   }
		    },
		    timeout: function (xhr, editor) {
		        layer.alert("图片上传超时，如文件过大请尝试剪切或压缩到较小后再试！");
		    },
		    fail: function (xhr, editor, result) {
				 if("object" == typeof result && "undefined" !=typeof result.msg){
				 	layer.alert(result.msg);
				 }
				 else{
				 	layer.alert("上传失败，请在浏览器按F12切换到network截图【上传请求的结果】在工单反馈");
				 }
		    }
		}
		editor2.customConfig.onchange = function (newHtml) {
		    if (typeof content =="object") {
		    	!content.val(""+newHtml) && layer.msg("编辑内容同步失败，请截图并提供详细描述提交工单反馈");
		    	return;
		    }
		    layer.msg("编辑内容同步失败，请截图并提供详细描述提交工单反馈");
		    return;
		}
		editor2.customConfig.onblur = function (newHtml) {
		    // 检测无用html内容
            if (editor2.txt.isEmptyHtml(newHtml)) {
                newHtml = "";
                editor2.txt.html("");
                editor2.txt.change();
            }
		    return;
		}

		if(typeof(editorMenu)=="object" && editorMenu instanceof Array){
			editor2.customConfig.menus = editorMenu;
		}
		editor2.create();
		if (content.val()!="") {
			editor2.txt.html(content.val());
		}
		else{
			editor2.txt.html("");
		}

		E.viewSource.init(editor2);

	}

	//商品页面附加富文本
	if ($(\'.textDom2\').length>0 && "function" === typeof window.wangEditor) {
		if ($(\'.textDom2\').length>1) {
			content2 = $(\'.textDom2\')[0];
		}
		else{
			content2 = $(\'.textDom2\');
		}

		editor3 = new E(\'#editorBox2\');
		if(typeof boxType != "undefined"){
			$(\'#editorBox2\').css({\'display\':\'block\',\'width\':\'100%\',\'float\':\'none\'});
		}
		editor3.customConfig.uploadImgServer = \'upload.php\';
		editor3.customConfig.uploadImgTimeout = 60 * 1000;
		editor3.customConfig.uploadFileName = \'filedata\';
		editor3.customConfig.mobile = $is_mb;
		editor3.customConfig.uploadImgHooks = {
		    success: function (xhr, editor, result) {
		    	   if (result.errno==0) {
		             layer.msg("图片插入成功");
		    	   }
		    	   else{
		    	   	   if (!result.msg) {
				            result.msg="上传图片失败！请检查";
				    	   }
				    	   layer.alert(result.msg);
		    	   }
		    },
		    timeout: function (xhr, editor) {
		        layer.alert("图片上传超时，如文件过大请尝试剪切或压缩到较小后再试！");
		    },
		    fail: function (xhr, editor, result) {
		        layer.alert("图片插入返回状态异常，请反馈作者处理！<br>返回信息：" + result);
		    }
		}
		if(typeof(editorMenu)=="object" && editorMenu instanceof Array){
			editor3.customConfig.menus = editorMenu;
		}
		editor3.customConfig.onchange = function (newHtml) {
		    if (typeof content2 =="object") {
		    	!content2.val(""+newHtml) && layer.msg("编辑内容同步失败，请截图并提供详细描述提交工单反馈");
		    	return;
		    }
		    layer.msg("编辑内容同步失败，请截图并提供详细描述提交工单反馈");
		    return;
		}
		editor3.customConfig.onblur = function (newHtml) {
		    // 检测无用html内容
            if (editor3.txt.isEmptyHtml(newHtml)) {
                newHtml = "";
                editor3.txt.html("");
                editor3.change();
            }
		    return;
		}
		editor3.create();
		if (content2.val()!="") {
			editor3.txt.html(content2.val());
		}
		else{
			editor3.txt.html("");
		}


		E.viewSource.init(editor3);

	}

	if( "function" !== typeof window.wangEditor ){
		if ($(\'.textDom\').length > 0) {
			if("function" == typeof($(\'.textDom\').removeClass)){
				$(\'.textDom\').removeClass("hide").addClass("form-control").attr("rows", "6").show();
			}
			else{
				$(\'.textDom\').attr("class", "form-control textDom").attr("rows", "6").show();
			}
		}

		if ($(\'.textDom2\').length > 0) {
			if("function" == typeof($(\'.textDom2\').removeClass)){
				$(\'.textDom2\').removeClass("hide").addClass("form-control").attr("rows", "6").show();
			}
			else{
				$(\'.textDom2\').attr("class", "form-control textDom2").attr("rows", "6").show();
			}
		}
	}

}
';
echo '
window.onload=function(){
	var items = $("select[default]");
	for (i = 0; i < items.length; i++) {
	    $(items[i]).val($(items[i]).attr("default")||0);
	    if(typeof(changeEvent)=="undefined" || changeEvent!==false){
	    	$(items[i]).change();
	    }
	};
    editorInit();
}
';

echo '
function editorChange(){
	if(typeof(editor2) =="object" && typeof(editor2.txt.html) =="function"){
		//将商品简介同步到表单
		var text = $(content).val();
		if(text != ""){
        	editor2.txt.html(text, false);
        }
	}

	if(typeof editor3 =="object" && typeof editor3.txt.html =="function"){
		//将提示内容同步到表单
		var text = $(content2).val();
        if(text != ""){
        	editor3.txt.html(text, false);
        }
	}
}
$(document).on("click", ".emptyText", function(event) {
    event.preventDefault();
    /* Act on the event */
    $(content).val("");
    editor2.txt.html("", false);
});
$(document).on("click", ".emptyText2", function(event) {
    event.preventDefault();
    /* Act on the event */
    $(content2).val("");
    editor3.txt.html("", false);
});
</script>
';
?>

<script>
"use strict";

var pridList = '';

function getPridList() {
    if ("" != pridList) {
        return pridList;
    } else {
        $.ajax({
            type: 'POST',
            url: 'ajax.php?act=getPridListSelect',
            dataType: 'html',
            success: function (data) {
                pridList = data;
            },
            error: function (data) {
                layer.msg('获取加价模板列表请求超时，请稍后再试！');
            }
        });
        return pridList;
    }
}

function move() {
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=shop_move',
        data: $('#form1').serialize(),
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                listTable();
                layer.alert(data.msg);
            } else {
                layer.alert(data.msg);
            }
        },
        error: function (data) {
            layer.msg('请求超时');
            listTable();
        }
    });
    return false;
}


function change() {
    var aid = parseInt($('select[name=aid]').val());
    var area = [$(window).width() > 640 ? '540px' : '95%', 'auto'];
    if (aid == 10) {
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: 'GET',
            url: 'ajax.php?act=getAllPrice',
            dataType: 'json',
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.open({
                        type: 1,
                        area: area,
                        title: '修改加价模板',
                        content: data.data
                    });
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function(data) {
                layer.msg('服务器错误');
                return false;
            }
        });
        return false;
    }
    if (aid == 16 && $("input[name='prid']").val() == '') {
        var laybox = layer.open({
            type: 1,
            area: area,
            title: '批量设置加价模板',
            content: '<div class="panel-body"><div class="form-group"><div class="input-group"><div class="input-group-addon">请选择模板</div><select class="form-control" id="p_prid">' + pridList + '</select></div></div></div>',
            btn: ['确定批量', '取消操作'],
            yes: function(index, layero) {
                var prid = $("#p_prid").val();
                $("input[name='prid']").val(prid);
                change();
            },
            btn2: function() {
                layer.close(laybox);
            }
        });
        return false;
    }
    $("input[name='shopimg_ok']").val('false');
    $("input[name='input_ok']").val('false');
    $("input[name='pay_status']").val('false');
    var ii = layer.load(2, {
        shade: [0.1, '#fff']
    });
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=shop_change',
        data: $('#form1').serialize(),
        dataType: 'json',
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                listTable();
                layer.alert(data.msg);
            } else {
                layer.alert(data.msg);
            }
        },
        error: function(data) {
            layer.msg('请求超时');
            listTable();
        }
    });
    return false;
}


$(document).on('click', '#check_ok',  function () {
    var tid = $(this).data('tid');
    var active = $(this).data('active');
    var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type : 'GET',
        url : '?act=info&tid='+tid,
        dataType : 'json',
        success : function(res) {
            layer.close(ii);
            if (res.code==0) {
                $("#tid").val(tid);
                $("#save_modal").modal('show');
                $('#save_modal').on('shown.bs.modal', function () {
                    // 写入数据
                    $("[name=name]").val(res.data.name);
                    $("[name=price1]").val(res.data.price1);
                    $("[name=cid]").attr('default', res.data.cid);
                    $("[name=price]").val(res.data.price);
                    $("[name=cost]").val(res.data.cost);
                    $("[name=cost2]").val(res.data.cost2);
                    $("[name=prices]").val(res.data.prices);
                    $("[name=input]").val(res.data.input);
                    $("[name=inputs]").val(res.data.inputs);
                    $("[name=alert]").val(res.data.alert);
                    $("[name=desc]").val(res.data.desc);
                    $("[name=multi]").attr('default', res.data.multi);
                    $("[name=repeat]").attr('default', res.data.repeat);
                    $("[name=prid]").attr('default', res.data.prid);
                    selectRender();
                    setTimeout(editorInit, 100);
                });
                $('#save_modal').on('hidden.bs.modal', function () {
                    // 还原
                    $("[name=body]").val('');
                    editorChange();
                })
            }else{
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

$(document).on('click', '#change',  function () {
    var tid = $(this).data('tid');
    var active = $(this).data('active');
    var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type : 'GET',
        url : '?act=change&tid='+tid+'&active='+active,
        dataType : 'json',
        success : function(data) {
            layer.close(ii);
            window.location.reload();
        },
        error:function(data){
            layer.close(ii);
            layer.msg('服务器错误');
            return false;
        }
    });
});


$(document).on('click', '#onDel', function () {
    // 保存插件配置
    var id = $(this).data('id');
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "?act=delete",
        data: {id: id},
        dataType: 'json',
        success: function (res) {
            layer.close(ii);
            if (res.code == 0) {
                layer.msg(res.msg);
                $("#tr_"+ id).remove();
            } else {
                layer.alert(res.msg);
            }
            return;
        },
        error: function (data) {
            layer.close(ii);
            layer.msg('服务器错误。请稍后再试');
            return false;
        }
    });
});

$(document).on('click', '.onSaveTool', function () {
    // 保存插件配置
    var postArr =  $("#form-save").serializeArray();
    var condition =  $(this).data('condition');
    var post = {};
    $.each(postArr, function (indexInArray, valueOfElement) {
        post[valueOfElement.name] = valueOfElement.value
    });
    post['condition'] = condition;
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "?act=onSave",
        data: post,
        dataType: 'json',
        success: function (res) {
            layer.close(ii);
            if (res.code == 0) {
                layer.msg(res.msg);
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                layer.alert(res.msg);
            }
            return;
        },
        error: function (data) {
            layer.close(ii);
            layer.msg('服务器错误。请稍后再试');
            return false;
        }
    });
});


function changePrice() {
    var price1 = getFloat($("[name=price1]").val(), 2);
    var prid = $("[name=prid]").val();
    if (prid > 0 ) {
        if (price1 == 0) {
            $("[name=price]").val(price1);
            $("[name=cost]").val(price1);
            $("[name=cost2]").val(price1);
            return true;
        }
        var kind = parseFloat($("[name=prid] option:selected").attr('kind'), 0);
        var p_2 = parseFloat($("[name=prid] option:selected").attr('p_2'), 2);
        var p_1 = parseFloat($("[name=prid] option:selected").attr('p_1'), 2);
        var p_0 = parseFloat($("[name=prid] option:selected").attr('p_0'), 2);
        $("[name=price]").val(getFloat(kind == 2 ? price1 + p_0 * price1 / 100 : price1 + p_0, 2));
        $("[name=cost]").val(getFloat(kind == 2 ? price1 + p_1 * price1 / 100 : price1 + p_1, 2));
        $("[name=cost2]").val(getFloat(kind == 2 ? price1 + p_2 * price1 / 100 : price1 + p_2, 2));
    }
}

$(document).on('change','[name=price1]', function () {
    changePrice();
});

$(document).on('change','[name=prid]', function () {
    changePrice();
});

function getFloat(number, n) {
    n = n ? parseInt(n) : 0;
    if (n <= 0) return Math.round(number);
    number = Math.round(number * Math.pow(10, n)) / Math.pow(10, n);
    return number;
}

function selectRender(){
    var items = $("select[default]");
    for (var i = 0; i < items.length; i++) {
        $(items[i]).val($(items[i]).attr("default") || 0);
    }
}

$(document).ready(function () {
    getPridList();
});
</script>

<?php include 'footer.php';?>