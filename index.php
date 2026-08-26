<?php
if (version_compare(PHP_VERSION, '7.0.0', '<')) {
    die('当前服务器环境php版本小于7.0, 请使用7.0~8.0的版本以保障稳定性');
}

$is_defend = true;

include "./includes/common.php";

if ($conf['index_run'] != "" && $conf['index_run'] != 1) {
    if ($conf['index_run_alert'] == '') {
        echo "<br/><br/><br/><center><h4>网站维护升级中，请耐心等待恢复</h4></center>";
    } else {
        echo "<br/><br/><br/><center><h4>" . $conf['index_run_alert'] . "</h4></center>";
    }
    exit;
} elseif ($conf['index_regbuy'] == 1 && $isLogin2 !== 1) {
    header("Location:" . $weburl . "user/login.php");
    exit;
}

@header('Content-Type: text/html; charset=UTF-8');
if ($conf['fenzhan_page_404'] == 1 && $is_fenzhan == false) {
    if (!empty($conf['fenzhan_remain']) && !in_array($_SERVER['HTTP_HOST'], explode(',', $conf['fenzhan_remain']))) {
        include ROOT . 'template/default/404.html';
        exit;
    }
}

if (isset($_GET['i']) && !empty($_GET['i'])) {
    $key = input('i', 1);
    $row = $DB->get_row("SELECT * from pre_invite where `key`=:key limit 1", [':key' => $key]);
    if ($row) {
        if ($_COOKIE['invite_id'] != $row['id']) {
            setcookie("invite_id", $row['id'], time() + 86400);
        }
    }
}
$qq = isset($_GET['qq']) ? strip_tags($_GET['qq']) : null;

if (!function_exists('session_get') && !function_exists('addsalt_create')) {
    showErrPage('缓存异常导致服务器环境缺失，请清理缓存或取消使用CDN再试!');
}

$addsalt_js = addsalt_create();

//加载插件首页配置
@hook('all', 'extend_index');

//模板输出前
@hook('view_before');

//输出模版视图
$mod      = isset($_GET['mod']) ? $_GET['mod'] : 'index';
$loadfile = \core\Template::load($mod);
if ($loadfile !== true) {
    //加载模板插件处理
    if (ob_start()) {
        include $loadfile;
        $viewHtml = ob_get_contents();
        ob_clean();
        if (trim($viewHtml) == "") {
            showErrPage('程序模板文件不完整或缺失，请检查是否上传完整');
            die;
        }

        if ($conf['index_html']) {
            //将底部全局自定义代码追加到body标签内
            if (preg_match('/<(\s+\/|\s+\/\s+|\/\s+|\/)body(.*?)>/i', $viewHtml, $match)) {
                $viewHtml = str_replace($match[0], $conf['index_html'] . '</body>', $viewHtml);
            } else {
                $viewHtml = $viewHtml . $conf['index_html'] . '</body></html>';
            }
        }
        //处理IE兼容问题
        $IeTips = TEMPLATE_ROOT . '/default/IeTips.html';
        if (file_exists($IeTips)) {
            if (preg_match('/<(\s+\/|\s+\/\s+|\/\s+|\/)head(.*?)>/i', $viewHtml, $match2)) {
                $viewHtml = str_replace($match2[0], file_get_contents($IeTips) . '</head>', $viewHtml);
            } elseif (preg_match('/<body(.*?)>/i', $viewHtml, $match2)) {
                $viewHtml = str_replace($match2[0], file_get_contents($IeTips) . '<body>', $viewHtml);
            }
        }
        @hook('view_show', $viewHtml);
        ob_end_flush();
    } else {
        @webLog_error('错误日志', 'OB缓存启用失败，视图输出插件加载终止！');
        if (filesize($loadfile) < 1024) {
            showErrPage('程序模板文件不完整或缺失，请检查是否上传完整');
            die;
        }
        include $loadfile;
        echo $conf['index_html'];
    }
}
//模板输出后
@hook('view_after');

//findp13139d9351313313133
//findp13139d9351313313133
