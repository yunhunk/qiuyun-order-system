<?php
if (!defined('ROOT') || !defined('SYSTEM_ROOT')) {
    die("请在后台设置文件引入使用！");
}

echo '
<div class="block">
<div class="block-title"><h3 class="panel-title">chenmeng模板设置</h3></div>
<div class="">
  <form action="./set.php?mod=save_n" method="post" class="form-horizontal" role="form">
      <div class="alert alert-info">
         此处的所有功能设置仅针对当前模板{' . $conf['template'] . '}有效
      </div>
      <input type="hidden" name="do" value="submit"/>
      <input type="hidden" name="action" value="MALL模板设置"/>
      <div class="form-group">
        <label class="col-sm-2 control-label">是否显示自定义按钮</label>
        <div class="col-sm-10"><select class="form-control" name="template_chenmeng_btn" default="';
echo $conf['template_chenmeng_btn'];
echo '"><option value="0">关闭</option><option value="1">开启</option>
        </select>
        <pre>开启后将在网站首页顶部显示一个自定义按钮</pre>
        </div>
      </div><br/>
      <div class="form-group">
        <label class="col-sm-2 control-label">自定义按钮类型</label>
        <div class="col-sm-10"><select class="form-control" name="template_chenmeng_btn_type" default="';
echo $conf['template_chenmeng_btn_type'] > 0 ? '1' : '0';
echo '"><option value="0">链接（默认）</option><option value="1">弹窗</option>
        </select>
        <pre>开启后将在网站首页顶部显示一个自定义按钮</pre>
        </div>
      </div><br/>
      <div class="form-group">
        <label class="col-sm-2 control-label">自定义按钮标题</label>
        <div class="col-sm-10"><input class="form-control" name="template_chenmeng_btn_title" value="';
echo $conf['template_chenmeng_btn_title'];
echo '"/>
        <pre>通常4个字左右，太长在手机端效果看起来会不协调</pre>
        </div>
      </div><br/>
       <div class="form-group">
        <label class="col-sm-2 control-label">自定义按钮内容</label>
        <div class="col-sm-10"><textarea style="min-height: 80px;height: 80px;" placeholder="" class="form-control" name="template_chenmeng_btn_content" rows="4">';
if ($conf['template_chenmeng_btn_type'] == 0) {
    echo $conf['template_chenmeng_btn_content'];
} else {
    echo htmlspecialchars($conf['template_chenmeng_btn_content']);
}
echo '</textarea>
        <pre>如果按钮类型是链接就填链接，如果是弹窗就填需要弹窗的内容，弹窗内容支持html代码</pre>
        </div>
      </div><br/>
    <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary form-control"/><br/>
    </div>
</form>
  </div>
</div>
';
