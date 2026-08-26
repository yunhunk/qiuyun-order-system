<?php
if (!defined('ROOT') || !defined('SYSTEM_ROOT')) {
    die("请在后台设置文件引入使用！");
}
$id  = input('get.id', 1);
$row = $DB->get_row("SELECT * FROM `pre_plugin` where `id` = :id", [':id' => $id]);
if (!$row) {
    showmsg('该插件不存在！', 3);
}

$rule = include 'set_rule.php';

echo '
<div class="col-md-12 center-block" style="float: none;">
<div class="block">
<div class="block-title">
  <h3 class="panel-title">插件设置</h3>
  ';
echo '
</div>
<div class="">
  <form action="./set.php?mod=save_n" method="post" class="form-horizontal" role="form">
      <input type="hidden" name="do" value="submit"/>
      <input type="hidden" name="action" value="' . $row['name'] . '"/>
      <div class="alert alert-info">
        提示：加强版会对网站标题和关键词等都加密，开启加强版会直接影响SEO网站标题收录
      </div>
      <div class="form-group">
        <label class="col-sm-2 control-label">是否开启加强版加密</label>
        <div class="col-sm-10"><select class="form-control" name="plugin_tvs_type" default="';
echo $conf['plugin_tvs_type'] > 0 ? '1' : '0';
echo '"><option value="0">关闭(默认)</option><option value="1">开启</option>
        </select>
        </div>
      </div><br/>
      <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
          <input type="submit" name="submit" value="修改" class="btn btn-primary form-control"/><br/>
        </div>
      </div><br/>
      <div class="form-group">
        <div class="col-sm-offset-1 col-sm-11">
          <a href="./plugin.php?mod=install"><<返回插件列表</a>
        </div>
      </div>
    </div>
</form>
  </div>
</div>

';
