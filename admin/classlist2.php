<?php

/**
 * 分类管理新版
 */
include '../includes/common.php';
checkLogin();
if ($_GET['my'] == 'classimg') {
    $title = '修改分类图片新版';
} else {
    $title = '分类管理新版';
}

checkAuthority('class');

include './head.php';

$select = '<option value="-1">请选择目标分类</option><option value="0">移动为一级分类</option>';
$rs     = $DB->select("SELECT * from `pre_class` where (`upcid`=0 OR `upcid` IS NULL)");
if ($rs) {
    foreach ($rs as $key => $item) {
        $select .= '<option value="' . $item['cid'] . '">移动到【' . $item['name'] . '】</option>';
    }
}

?>
<link rel="stylesheet" type="text/css" href="<?php echo $cdnpublic ?>layui/2.5.7/css/layui.css">
<style type="text/css">
	.layui-btn+.layui-btn {
		margin-left: 5px;
	}

	.btn+.btn {
		margin-left: 5px;
	}

	.layui-table-cell {
		padding: 0 2px;
	}
</style>
<div class="col-sm-12 col-md-12  col-lg-12 center-block" style="float: none;padding-top:10px ">
	<div class="block">
		<div class="block-title">
			<h2><?php echo $title ?></h2>
		</div>
		<div style="height: 100%">
			<form id="classlist" onsubmit="return false;">
				<div id="toolbar" style="display: none">
					<div class="layui-form-item">
						<div class="form-inline" style="margin-top:3px">
							<div class="layui-inline">
								<!-- <label class="layui-form-label" style="width: auto;">批量操作：</label> -->
								<div class="layui-input-inline">
									<select name="checkAction" lay-filter="checkAction">
										<option selected>请选择批量操作</option>
										<option value="1">批量显示选中</option>
										<option value="2">批量隐藏选中</option>
										<option value="4">批量设置登录可见</option>
										<option value="5">批量取消登录可见</option>
										<option value="3">批量修改所有</option>
										<option value="10">批量删除选中</option>
									</select>
								</div>
								<a href="javascript:;" id="operation" lay-event="operation" class="btn btn-primary btn-xs" title="执行操作">执行操作</a>

							</div>
							<div class="layui-inline">
								<div class="layui-input-inline">
									<select name="upcid" lay-filter="moveUpcid">
										<?php echo $select; ?>
									</select>
								</div>
								<a href="javascript:;" id="move" lay-event="move" class="btn btn-primary btn-xs" title="执行操作">确定移动</a>
								<a href="javascript:;" id="reload" data-tip="点击刷新分类列表" lay-event="reload" class="btn btn-success btn-xs reload" title="刷新分类列表">刷新分类</a>
								<a href="javascript:;" id="add" lay-event="add" class="btn btn-info btn-xs" title="添加分类"><span class="glyphicon glyphicon-plus"></span>添加分类</a>
								<a href="javascript:;" id="classimg" lay-event="classimg" class="btn btn-danger btn-xs" title="添加分类">修改分类图片</a>
								<a href="./classlist.php" class="btn btn-info btn-xs" title="添加分类">旧版分类管理</a>
							</div>
						</div>
					</div>
				</div>
				<div id="toolbar2" style="display: none">
					<div class="layui-form-item">
						<div class="form-inline" style="margin-top:3px">
							<div class="layui-btn-group">
								<a href="javascript:;" id="saveall" lay-event="saveall" data-tip="点击保存全部" class="btn btn-primary btn-xs saveall" title="保存全部">保存全部</a>
								<a href="javascript:;" id="reload" lay-event="reload" data-tip="点击刷新分类列表" class="btn btn-success btn-xs reload" title="刷新分类列表">刷新列表</a>
								<a href="javascript:;" id="goback" lay-event="goback" class="btn btn-danger btn-xs" title="添加分类">回到分类</a>
							</div>
						</div>
					</div>
				</div>
				<table class="layui-hidden" id="treeTable" lay-data="{id: 'treeTable'}" lay-filter="treeTable"></table>
				<div id="laypage" style="display: none">

				</div>
			</form>
		</div>
	</div>
	<div id="add-template" style="display: none">
		<div class="panel">
			<div class="panel-body">
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">分类名称</div>
						<input type="text" id="name" class="form-control" placeholder="菜单标题">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="edit-template" style="display: none">
		<div class="panel">
			<div class="panel-body">
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">分类名称</div>
						<input type="text" id="name" class="form-control" placeholder="菜单标题">
					</div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript" src="<?php echo $cdnpublic ?>layui/2.5.7/layui.all.js"></script>
	<script src="./assets/js/classlist2.js?<?php echo $jsver ?>"></script>