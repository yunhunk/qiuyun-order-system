<?php
if (!defined('ROOT') || !defined('SYSTEM_ROOT')) {
    die("请在后台设置文件引入使用！");
}

$cbHtml = <<<cbHtml
<a class="hd-time-limited" href="<?php echo $cdnserver?>user/reg.php"></a>
<a target="_self" class="feedback graHover" id="sign_daily" style="background-color: #ffd900;color:#383838;width:26px;" href="?cid=2">福利</a>
<a target="_self" class="feedback graHover" style="background-color: #AF3A9F;color:#fff;width:26px;" href="?cid=分类ID">Q钻</a>
<a target="_self" class="feedback graHover" style="background-color: #1e6be3;color:#fff;width:26px;" href="?cid=分类ID">Q赞</a>
<a target="_self" class="feedback graHover" style="background-color: #FF3399;color:#fff;width:26px;" href="?cid=分类ID">人气</a>
<a target="_self" class="feedback graHover" style="background-color: black;color:#fff;width:26px;" href="?cid=分类ID">说说</a>
<a target="_self" class="feedback graHover" style="background-color: #ffa500;color:#fff;width:26px;" href="?cid=分类ID">代挂</a>
<a target="_self" class="feedback graHover" style="background-color: #3cbdfa;color:#fff;width:26px;" href="<?php echo $cdnserver?>user/reg.php">分站搭建</a>
<a target="_self" class="feedback graHover" style="background-color: #06C17E;color:#fff;width:26px;" href="?cid=分类ID">网课</a>
<a target="_self" class="feedback graHover" style="background-color: #FF3399;color:#fff;width:26px;" href="?cid=分类ID">影视</a>
cbHtml;

if (empty($conf['template_purpleYear_right'])) {
    $conf['template_purpleYear_right'] = $cbHtml;
}

if ($_GET['act'] == 'reset_right') {
    saveSetting('template_purpleYear_right', $cbHtml);
    $ad = $CACHE->clear();
    if ($ad) {
        showmsg('侧边自定义内容重置成功！', 1);
    } else {
        showmsg('侧边自定义内容重置失败！<br/>' . $DB->error(), 4);
    }
}

echo '
<div class="block">
<div class="block-title"><h3 class="panel-title">FAKA模板设置</h3></div>
<div class="">
  <form action="./set.php?mod=save_n" method="post" class="form-horizontal" role="form">
      <div class="alert alert-info">
         当前界面的所有功能设置仅针对当前模板{' . $conf['template'] . '}有效
      </div>
      <input type="hidden" name="do" value="submit"/>
      <input type="hidden" name="action" value="purpleYear模板"/>
      <div class="form-group">
        <label class="col-sm-2 control-label">首页顶部新年风格</label>
        <div class="col-sm-10"><select class="form-control" name="template_purpleYear_topui" default="';
echo $conf['template_purpleYear_topui'] == 2 ? '2' : '1';
echo '"><option value="1">牛年大吉(默认)</option><option value="2">新年快乐</option>
        </select>
        </div>
      </div><br/>
      <div class="form-group">
        <label class="col-sm-2 control-label">首页背景风格</label>
        <div class="col-sm-10"><select class="form-control" name="template_purpleYear_bgui" default="';
echo $conf['template_purpleYear_bgui'] == 1 ? '1' : '0';
echo '"><option value="0">紫色渐变(默认)</option><option value="1">系统设置</option>
        </select>
        </div>
      </div><br/>
      <div class="form-group">
        <label class="col-sm-2 control-label">鼠标点击特效</label>
        <div class="col-sm-10"><select class="form-control" name="template_purpleYear_hover" default="';
echo isset($conf['template_purpleYear_hover']) && $conf['template_purpleYear_hover'] == 0 ? '0' : '1';
echo '"><option value="1">开启特效(默认)</option><option value="0">关闭特效</option>
        </select>
        </div>
      </div><br/>
      <div class="form-group">
        <label class="col-sm-2 control-label">侧边推荐内容</label>
        <div class="col-sm-10">
        <textarea style="min-height: 80px;height: 80px;" placeholder="" class="form-control" name="template_purpleYear_right" rows="4">';
echo htmlspecialchars($conf['template_purpleYear_right']);
echo '</textarea>
          <pre>支持自定义推荐商品和其他按钮。<a href="?mod=template_set&act=reset_right">重置内容</a></pre>
        </div>
      </div><br/>
       <div class="form-group">
        <label class="col-sm-2 control-label">底部友情链接</label>
        <div class="col-sm-10">
        <textarea style="min-height: 80px;height: 80px;" placeholder="" class="form-control" name="template_purpleYear_friendlink" rows="3">';
if (empty($conf['template_purpleYear_friendlink'])) {
    $conf['template_purpleYear_friendlink'] = '<a rel="nofollow" href="/" title="QQ**" target="_blank">QQ**</a>&nbsp;|&nbsp;
<a rel="nofollow" href="/" title="QQ**" target="_blank">QQ**</a>
';
}
echo htmlspecialchars($conf['template_purpleYear_friendlink']);
echo '</textarea>
          <pre>底部友情链接代码，留空将自动显示默认的</pre>
        </div>
      </div><br/>
      <div class="form-group">
        <label class="col-sm-2 control-label">博客站显示新文章</label>
        <div class="col-sm-10"><select class="form-control" name="template_purpleYear_blog" default="';
echo $conf['template_purpleYear_blog'] == 1 ? '1' : '0';
echo '"><option value="0">关闭显示(默认)</option><option value="1">开启显示</option>
        </select>
        <pre>网站底部显示博客站最新文章，需在子目录已安装Z-blog，并在下方配置数据库账号密码</pre>
        </div>
      </div><br/>
      <div class="form-group">
        <label class="col-sm-2 control-label">博客站安装子目录</label>
        <div class="col-sm-10"><input class="form-control" name="template_purpleYear_blog_dbname" value="';
if (empty($conf['template_purpleYear_blog_dir'])) {
    $conf['template_purpleYear_blog_dir'] = 'blog';
}
echo $conf['template_purpleYear_blog_dir'];
echo '"/>
        <pre>星河云商城Plus程序目录下的博客安装目录，例如blog！填写错误最新文章将无法打开</pre>
        </div>
      </div><br/>
      <div class="form-group">
        <label class="col-sm-2 control-label">博客站是否伪静态</label>
        <div class="col-sm-10"><select class="form-control" name="template_purpleYear_blog_static" default="';
echo $conf['template_purpleYear_blog_static'] == 1 ? '1' : '0';
echo '"><option value="0">未开启(默认)</option><option value="1">已开启</option>
        </select>
        <pre>博客站如果已经配置好了伪静态就选择【已开启】，反之选择【未开启】！</pre>
        </div>
      </div><br/>
      <div class="form-group">
        <label class="col-sm-2 control-label">博客站数据库前缀</label>
        <div class="col-sm-10">
        <input class="form-control" name="template_purpleYear_blog_dbqz" value="';
if (empty($conf['template_purpleYear_blog_dbqz'])) {
    $conf['template_purpleYear_blog_dbqz'] = 'zbp';
}
echo $conf['template_purpleYear_blog_dbqz'];
echo '"/><pre>Z-Blog的数据库前缀默认是zbp。</pre>
        </div>
      </div><br/>
      <div class="form-group">
        <label class="col-sm-2 control-label">博客站数据库名称</label>
        <div class="col-sm-10">
        <input class="form-control" name="template_purpleYear_blog_dbname" value="';
echo $conf['template_purpleYear_blog_dbname'];
echo '"/>
        </div>
      </div><br/>
      <div class="form-group">
        <label class="col-sm-2 control-label">博客站数据库账号</label>
        <div class="col-sm-10">
        <input class="form-control" name="template_purpleYear_blog_dbuser" value="';
echo $conf['template_purpleYear_blog_dbuser'];
echo '"/>
        </div>
      </div><br/>
      <div class="form-group">
        <label class="col-sm-2 control-label">博客站数据库密码</label>
        <div class="col-sm-10">
        <input class="form-control" name="template_purpleYear_blog_dbpwd" value="';
echo $conf['template_purpleYear_blog_dbpwd'];
echo '"/>
        </div>
      </div><br/>

    <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary form-control"/><br/>
    </div>
</form>
  </div>
</div>

';
