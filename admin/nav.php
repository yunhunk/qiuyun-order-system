<?php
/**
 * 菜单管理
 */
include '../includes/common.php';
checkLogin();
$title = '菜单管理';

include './head.php';
?>
<link rel="stylesheet" type="text/css" href="<?php echo $cdnpublic ?>layui/2.5.7/css/layui.css">
<style type="text/css">
.layui-btn+.layui-btn{
	margin-left: 5px;
}
.btn+.btn{
	margin-left: 5px;
}
</style>
<div class="col-sm-12 col-md-12  col-lg-12 center-block" style="float: none;padding-top:10px ">
<div class="block">
    <div class="block-title">
    <h2><?php echo $title ?></h2> &nbsp;<span id="reload" data-tip="点击刷新分类列表" class="btn btn-success btn-xs" title="刷新分类列表">刷新列表</span>
    </div>
    <div style="margin-bottom: 15px;">
	    <div id="toolbar" style="display: none">
   			<a class="layui-btn layui-btn-xs" id="add" lay-event="add">添加父菜单</a>
   			<a class="btn btn-success btn-xs" id="show" lay-event="show">显示选中</a>
   			<a class="btn btn-danger btn-xs" id="hide" lay-event="hide">隐藏选中</a>
   		</div>
        <table class="layui-hidden" id="treeTable" lay-filter="treeTable"></table>
        <div id="laypage" style="">

   		</div>
	</div>
</div>
<div id="add-template" style="display: none">
	<div class="panel">
	 	<div class="panel-body">
	 		<div class="alert alert-info">
	 			支持添加快捷外链和内部快捷链接
	 		</div>
			<div class="form-group">
			    <div class="input-group">
			        <div class="input-group-addon">菜单标题</div>
			        <input type="text" id="name" class="form-control" placeholder="菜单标题">
			    </div>
			</div>
			<div class="form-group">
			    <div class="input-group">
			        <div class="input-group-addon">菜单链接</div>
			        <input type="text" id="url" class="form-control" placeholder="菜单链接">
			    </div>
			</div>
			<div class="form-group">
			    <div class="input-group">
			        <div class="input-group-addon">菜单图标样式名称</div>
			        <input type="text" id="icon" class="form-control" placeholder="fa fa-bars 可留空">
			    </div>
			</div>
	    </div>
	</div>
</div>
<div id="addsub-template" style="display: none">
	<div class="panel">
	 	<div class="panel-body">
	 	  	  <div class="form-group">
			    <div class="input-group">
			        <div class="input-group-addon">菜单标题</div>
			        <input type="text" id="name" class="form-control" placeholder="菜单标题">
			    </div>
			</div>
			<div class="form-group">
			    <div class="input-group">
			        <div class="input-group-addon">菜单链接</div>
			        <input type="text" id="url" class="form-control" placeholder="菜单链接">
			    </div>
			</div>

			<div class="form-group">
			    <div class="input-group">
			        <div class="input-group-addon">菜单图标样式名称</div>
			        <input type="text" id="icon" class="form-control" placeholder="fa fa-bars">
			    </div>
			</div>
			<div class="form-group">
			    <div class="input-group">
			        <div class="input-group-addon">选中条件</div>
			        <input type="text" id="wherein" class="form-control" placeholder="参考其他内置菜单">
			    </div>
			</div>
			<div class="form-group">
			    <div class="input-group">
			        <div class="input-group-addon">检索关键词</div>
			        <textarea id="keywords" class="form-control" rows="3" placeholder="检索关键词"></textarea>
			        <small>可通过搜索关键词快速找到某个页面。多个用英文,隔开</small>
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
			        <div class="input-group-addon">菜单标题</div>
			        <input type="text" id="name" class="form-control" placeholder="菜单标题">
			    </div>
			</div>
			<div class="form-group">
			    <div class="input-group">
			        <div class="input-group-addon">菜单链接</div>
			        <input type="text" id="url" class="form-control" placeholder="菜单链接">
			    </div>
			</div>
			<div class="form-group">
			    <div class="input-group">
			        <div class="input-group-addon">选中条件</div>
			        <input type="text" id="wherein" class="form-control" placeholder="参考其他内置菜单">
			    </div>
			</div>
			<div class="form-group">
			    <div class="input-group">
			        <div class="input-group-addon">检索关键词</div>
			        <textarea id="keywords" class="form-control" rows="3" placeholder="检索关键词"></textarea>
			        <small>可通过搜索关键词快速找到某个页面。多个用英文,隔开</small>
			    </div>
			</div>
	 	  </div>
	</div>
</div>
<script type="text/javascript" src="<?php echo $cdnpublic ?>layui/2.5.7/layui.all.js"></script>
<script src="./assets/js/nav.js?<?php echo $jsver ?>"></script>