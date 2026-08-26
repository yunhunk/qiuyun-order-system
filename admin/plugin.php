<?php
/**
 * 插件市场
 **/
include "../includes/common.php";
checkLogin();
checkAuthority('super');

$mod = isset($_GET['mod']) ? input('get.mod') : null;
if ($mod == 'set') {
    $id = input('get.id', 1);

    $row = $DB->get_row("SELECT * FROM `pre_plugin` where `id`=:id", [':id' => $id]);
    if (!$row) {
        showmsg('该插件数据不存在！', 3);
    }
    $path = PLUGIN_ROOT . $row['dirname'] . '/set.php';
    if (!file_exists($path)) {
        include './head.php';
        showmsg('该插件不支持或不需要配置操作！', 3);
    }

    $title = '插件设置';
    if (!IS_AJAX) {
        include './head.php';
    }

    include $path;
} elseif ($mod == 'save_n') {
    include './head.php';
    if (!$_POST['submit'] || !$_POST['do'] == 'submit') {
        showmsg('请通过页面保存提交数据！<br/>' . $DB->error(), 4);
    } else {
        foreach ($_POST as $key => $value) {
            if ($key != 'submit') {
                if ($act !== "gonggao" && !in_array($key, $key_white_list)) {
                    //剥去html标签和标签样式
                    $value = input('post.' . $key, 1);
                }
                saveSetting($key, $value);
            }
        }
    }
    $ad = $CACHE->clear();
    if ($ad) {
        showmsg('插件市场配置修改成功！', 1);
    } else {
        showmsg('插件市场配置修改失败，' . $DB->error(), 4);
    }
} elseif ($mod == 'plugin_bind') {
    $title = '我的插件';
    include './head.php';
    if (!function_exists('getPluginAuthType')) {
        function getPluginAuthType($authtype = 0)
        {
            if ($authtype == 2) {
                return '域名授权';
            } else if ($authtype == 1) {
                return '账号授权';
            } else {
                return '程序授权';
            }
        }
    }
    echo '
    <div class="col-md-12 center-block" style="float: none;padding-top:12px;">
        <div class="block">
            <div class="block-title">
            <h3 class="panel-title">' . $title . '</h3>
              <span style="margin-left:10px;display:inline-block">
                 <a href="./plugin.php" class="btn btn-primary btn-xs">回到插件市场</a>
              </span>
            </div>
            <div class="">
                <div class="alert alert-info">
                    1.已经购买过的插件都可以在此修改换绑授权<br/>
                    2.如果这里连不上授权站无法正常操作，请联系系统商处理，换绑一次5元(不限数量)<br/>
                    3.如果是账号授权的插件，换到他人账号后将无法撤销，请谨慎操作！
                </div>
                <div class="table-responsive">
                <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>
                                    名称
                                </th>
                                <th>
                                    绑定类型
                                </th>
                                <th>
                                    绑定对象
                                </th>
                                <th>
                                    状态
                                </th>
                                <th>
                                    操作
                                </th>
                            </tr>
                        </thead>
                        <tbody>';

    //获取授权插件列表
    if (function_exists('getPluginAuthList')) {
        $result = getPluginAuthList();
        if ($result['code'] != 0) {
            echo '<tr><td colspan="5">获取云端插件列表失败，' . $result['msg'] . '</td></tr>';
        } else {
            foreach ($result['data'] as $key => $res) {
                echo '<tr><td>' . $res['name'] . '</td><td>' . $res['authtypename'] . '</td><td>' . $res['authvalue'] . '</td><td>' . ($res['status'] == 1 ? '<span class="btn btn-success btn-xs">正常</span>' : '<span class="btn btn-success btn-xs">禁用</span>') . '</td><td><a data-id="' . $res['id'] . '" data-name="' . $res['name'] . '" data-authtype="' . $res['authtype'] . '" data-alias="' . $res['alias'] . '" data-token="' . $res['token'] . '" class="btn btn-primary btn-xs updateBind">改绑</a></td></tr>';
            }
        }
    } else {
        echo '<tr><td colspan="5">系统核心文件被损坏或不是最新，请先更新到最新版或更换php版本再试</td></tr>';
    }
    echo '</tbody>
        </table>
            </div>
            </div>
        </div>
    </div>
    <script>var noload=true;</script>
    <script src="./assets/js/plugin.js?' . $jsver . '"></script>
    ';
} elseif ($mod == 'plugin_set') {
    $title = '插件市场设置';
    include './head.php';

    echo '
    <div class="col-md-12 center-block" style="float: none;padding-top:12px;">
    <div class="block">
    <div class="block-title">
    <h3 class="panel-title">' . $title . '</h3>
      <span style="margin-left:10px;display:inline-block">
         <a href="./plugin.php" class="btn btn-primary btn-xs">回到插件市场</a>
      </span>
    </div>
    <div class="">
      <form action="?mod=save_n" method="post" class="form-horizontal" role="form">
        <input type="hidden" name="do" value="submit"/>
        <div class="col-sm-12 col-md-10 col-lg-10" style="float:none;margin:auto">
        <div class="form-group">
            <div class="input-group">
            <div class="input-group-addon">云端授权平台账号</div>
            <input class="form-control" type="text" placeholder="授权站平台账号" name="plugin_user" value="';
    echo $conf['plugin_user'];
    echo '">
            </div>
            <small style="color:red">授权平台账号是授权站注册的账号，将会跟插件等各种重要信息各种绑定，请谨慎填写，不要遗失</small>
        </div>
        <div class="form-group">
        <div class="input-group">
        <div class="input-group-addon">云端授权平台密码</div>
        <input class="form-control" type="text" placeholder="不修改请留空" name="plugin_pwd" value="';
    //echo $conf['plugin_pwd'];
    echo '">
        </div>
        </div>
        <div class="form-group">
          <div class="col-sm-12"><input type="submit" name="submit" value="修改" class="btn btn-primary form-control"/><br/>
        </div>
    </form>
      </div>
    </div>
    </div>
    </div>
    ';

} elseif ($mod == 'install') {
    $title = '已装插件';
    include './head.php';

    if (!function_exists('display_status')) {
        function display_status($id, $status)
        {
            if ($status == 1) {
                return '<a class="btn btn-success btn-xs" onclick="chenmObj.setActive(' . $id . ',0)">已启用</a>';
            } else {
                return '<a class="btn btn-gray btn-xs" onclick="chenmObj.setActive(' . $id . ',1)">已关闭</font>';
            }
        }
    }

    echo '
    <div class="col-md-12 center-block" style="float: none;">
    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top: 10px;">
            <div class="block">
                <div class="block-title">
                    <h2>已装插件</h2>
                </div>
                <div class="table-responsive">
                    <div class="alert alert-info">
                    注意一：模板无需启用，可在这里更新！模板的自定义设置在“主站设置、首页模板设置”中选中设置后刷新即可看到<br/>
                    注意二：如刚安装的插件，点“配置”进去空白没有任何提示，多半是你的服务器主机太垃圾导致的!! (主机商为了节约成本和提高性能什么的，搞了各种加速、缓存、CDN等导致部分文件缺失、报错无法正常使用)
                    </div>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>
                                    配置
                                </th>
                                <th style="max-width: 60px;">
                                    -
                                </th>
                                <th>
                                    名称
                                </th>
                                <th>
                                    版本
                                </th>
                                <th>
                                    作者
                                </th>
                                <th>
                                    状态
                                </th>
                                <th>
                                    操作
                                </th>
                            </tr>
                        </thead>
                        <tbody>';
    $sql      = ' 1';
    $numrows  = $DB->count("SELECT count(*) from `pre_plugin` WHERE {$sql}");
    $pagesize = 30;
    $pages    = ceil($numrows / $pagesize);
    $page     = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset   = $pagesize * ($page - 1);

    //获取插件列表
    $rs = \core\Plugin::getList($sql, $offset, $pagesize);
    if (false !== $rs) {
        foreach ($rs as $key => $res) {
            echo '<tr><td><a href="./plugin.php?mod=set&id=' . $res['id'] . '" class="btn btn-info btn-xs">配置</a></td><td><b>' . $res['id'] . '</b></td><td>' . $res['name'] . '</td><td>' . $res['version'] . '（build ' . $res['build'] . '）</td><td>' . $res['author'] . '</td><td>' . display_status($res['id'], $res['status']) . '</td><td><a onclick="chenmObj.update(' . $res['id'] . ')" class="btn btn-primary btn-xs">更新</a>&nbsp;<a onclick="chenmObj.unInstall(' . $res['id'] . ')" class="btn btn-danger btn-xs">卸载</a>&nbsp;</td></tr>';
        }
    }
    echo '</tbody>
        </table>
      </div>';

    #分页
    $pageList = new \core\Page($numrows, $pagesize, 0, $link);
    echo $pageList->showPage();
    #分页
    echo '
            </div>
        </div>
    </div>
    <script>var noload=true;</script>
    <script src="./assets/js/plugin.js?' . $jsver . '"></script>
    ';

} else {
    $title = '插件市场';
    include './head.php';
    echo '
    <div class="col-md-12 center-block" style="float: none;">
    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top: 10px;">
            <div class="block">
                <div class="block-title">
                    <ul class="nav nav-tabs" data-toggle="tabs">
                        <li type="all" style="min-width: 80px;" align="center" class="active"><a href="#" data-toggle="tab" onclick="chenmObj.load(\'all\')"><span><i class="fa fa-bars hidden-xs"></i> 所有</span></a></li>
                        <li type="template" style="min-width: 80px;" align="center" class=""><a href="#" data-toggle="tab" onclick="chenmObj.load(\'template\')"><span><i class="fa fa-picture-o hidden-xs"></i> 模板</span></a></li>
                        <li type="plugin" style="min-width: 80px;" align="center" class=""><a href="#" data-toggle="tab"  onclick="chenmObj.load(\'plugin\')"><span><i class="fa fa-server hidden-xs"></i> 插件</span></a></li>
                        <li type="free" style="min-width: 80px;" align="center" class=""><a href="#" data-toggle="tab" onclick="chenmObj.load(\'free\')"><span><i class="fa fa-gift hidden-xs"></i> 免费</span></a></li>
                        <li type="shequ" style="min-width: 80px;" align="center" class=""><a href="#" data-toggle="tab" onclick="chenmObj.load(\'shequ\')"><span><i class="fa fa-hourglass-half hidden-xs"></i> 对接</span></a></li>
                        <li type="fuwu" style="min-width: 80px;" align="center" class=""><a href="#" data-toggle="tab" onclick="chenmObj.load(\'fuwu\')"><span><i class="fa fa-user-circle-o hidden-xs"></i> 服务</span></a></li>
                        <li style="min-width: 80px;" align="center" class=""><a href="?mod=plugin_set"><span><i class="fa fa-cog hidden-xs"></i> 设置</span></a></li>
                        <li style="min-width: 80px;" align="center" class=""><a href="?mod=plugin_bind"><span><i class="fa fa-cogs hidden-xs"></i> 换绑</span></a></li>
                    </ul>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">搜索</div>
                    <input type="text" id="kw" value="" class="form-control" required="">
                    <a class="input-group-addon" id="search"><i class="fa fa-search"></i></a>
                </div>
                <div id="pcTable" class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width: 60px;">
                                    -
                                </th>
                                <th style="width: 15%;">
                                    名称
                                </th>
                                <th>
                                    类型
                                </th>
                                <th style="width: 40%;">
                                    详情
                                </th>
                                <th>
                                    作者
                                </th>
                                <th>
                                    绑定类型
                                </th>
                                <th>
                                    最新版本
                                </th>
                                <th>
                                    是否购买
                                </th>
                                <th>
                                    价格
                                </th>
                                <th>
                                    操作
                                </th>
                            </tr>
                        </thead>
                        <tbody id="pluginlist">
                            <tr><td colspan="6" class="text-center"><h3>数据正在加载中~</h3></td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="mobileTable" class="row" style="display:none">

                </div>
            </div>
        </div>
    </div>
    <script src="./assets/js/plugin.js?' . $jsver . '"></script>
    ';
}
$url = $_SERVER['HTTP_HOST'] . ($_SERVER['SERVER_PORT'] != 80 ? ':' . $_SERVER['SERVER_PORT'] : '');
echo '<input type="hidden" id="authcode" name="authcode" value="' . $authcode . '">';
echo '<input type="hidden" id="url" name="url" value="' . $url . '">';
echo <<<html
    <div id="template_service" style="display:none">
        <div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top:10px ">
            <div class="block">
                <div class="">
                    <div class="form-group">
                        <label>服务类型：</label><br/>
                        <input type="text" id="input1" value="" class="form-control"/>
                        <pre>目前支持的服务类型如下：迁移数据、网站维护、定制模板二种</pre>
                    </div>
                    <div class="form-group">
                        <label>联系方式：</label><br/>
                        <input type="text" id="input2" value="" class="form-control"/>
                        <pre>可以是QQ、手机号、微信等</pre>
                    </div>
                    <div class="form-group">
                        <label>需求内容详细说明:</label><br>
                        <textarea type="text" id="input3" rows="3" class="form-control"></textarea>
                        <pre>比如是迁移数据、网站维护的情况，提供服务器资料(含主机地址，账号、密码、宝塔资料等)；如果是定制，请大概说明要定制的模板示例站点或模板详细图片提供参考，如可以做会联系您，不可以将会退款</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="template_reg" style="display:none">
        <div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top:10px ">
            <div class="block">
                <form>
                    <div class="form-group">
                        <label>登录用户名：</label><br/>
                        <input type="text" id="user" value="" class="form-control"/>
                        <small>支持【字母、数字、小数点、@、-】任意组合，长度6~20个字符</small>
                    </div>
                    <div class="form-group">
                        <label>登录密码：</label><br/>
                        <input type="text" id="pass" value="" class="form-control"/>
                        <small>支持【字母、数字、小数点、@、-】任意组合，长度8~24个字符</small>
                    </div>
                    <div class="form-group">
                        <label>联系QQ：</label><br/>
                        <input type="text" id="qq" value="" class="form-control"/>
                    </div>
                    <div class="form-group">
                        <label>联系邮箱：</label><br/>
                        <input type="text" id="email" value="" class="form-control"/>
                        <small>格式如：xxxx@qq.com、xxxx@163.com</small>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="template_recharge" style="display:none">
        <div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top:10px ">
            <div class="block">
                <form>
                    <div class="form-group">
                        <label>充值金额：</label><br/>
                        <input type="text" id="money" value="" class="form-control"/>
                        <small style="color:red;">单次最低充值不小于5元，如点充值没反应，可以重新登录换个节点试试</small>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="template_updatebind" style="display:none">
        <div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top:10px ">
            <div class="block">
                <form>
                    <div class="form-group">
                        <label>插件名称：</label><br/>
                        <input type="text" id="name" value="" class="form-control" disabled/>
                    </div>
                    <div class="form-group">
                        <label>授权类型：</label><br/>
                        <input type="text" id="authtypename" value="" class="form-control" disabled/>
                    </div>
                    <div class="form-group">
                        <label>新的授权：</label><br/>
                        <input type="text" id="authvalue" value="" class="form-control"/>
                        <small style="color:red;" id="authtips">填写新的授权码，同一个授权码的授权均可使用</small>
                    </div>
                </form>
            </div>
        </div>
    </div>
html;

include_once 'footer.php';
