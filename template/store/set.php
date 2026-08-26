<?php
if ($isLogin !== 1 || !function_exists('checkLogin')) {
    @header("Location:" . $weburl);
    exit;
}

include 'config.php';

echo '
<div class="block">
<div class="block-title"><h3 class="panel-title">' . $conf['template'] . '模板设置</h3></div>
<div class="">
  <form action="./set.php?mod=save_n" method="post" class="form-horizontal" role="form">
      <div class="alert alert-info">
         此处的所有功能设置仅针对当前模板{' . $conf['template'] . '}有效
      </div>
      <input type="hidden" name="do" value="submit"/>
      <input type="hidden" name="action" value="store模板设置"/>
      ';
if (isset($template_settings)) {
    echo createFormList($template_settings);
}
echo '
    <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary form-control"/><br/>
    </div>
</form>
  </div>
</div>
';
