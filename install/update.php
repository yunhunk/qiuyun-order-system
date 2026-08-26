<?php
/**
 * 秋云商城数据库自动工具
 */

include '../includes/common.php';
if (isset($_GET['goto']) && $_GET['goto']) {
    $goto = $_GET['goto'];
} else {
    if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) {
        $goto = $_SERVER['HTTP_REFERER'];
    } else {
        $goto = 'javascript:history.go(-1);';
    }
}

$build_new = input('get.build', 1);

if (!function_exists('getUpdateFileList')) {
    function getUpdateFileList($build = 2028)
    {
        $arr   = [];
        $files = scandir(__DIR__ . '/');
        if (is_array($files)) {
            foreach ($files as $key => $filename) {
                if (preg_match('/^update_([\d]+)\.sql$/', $filename, $match) && ($match[1] > $build && $match[1] < 2000)) {
                    $arr[] = __DIR__ . '/' . $filename;
                }
            }
        }
        return $arr;
    }
}

if (is_object($DB)) {
    if ($DB->query("SELECT 1") !== false) {
        $file = ROOT . 'includes/version.php';
        if (is_file($file)) {
            $json = include $file;
            if (is_array($json)) {
                $build_new = $json['build_core'];
                if ($conf['SQLVERSION'] < $json['build_sql']) {
                    $arr   = getUpdateFileList($conf['version']);
                    $ok    = 0;
                    $err   = 0;
                    $num   = 0;
                    $error = '';
                    foreach ($arr as $key => $filePath) {
                        if (is_file($filePath)) {
                            $sql      = file_get_contents($filePath);
                            $sqls     = explode(';', $sql);
                            $filename = str_replace(__DIR__ . '/', '', $filePath);
                            preg_match('/[\d]+/', $filename, $m);
                            $build = $m[0];
                            if (count($sqls) > 0) {
                                $error .= "---------文件[" . $filename . "]错误日志---------<br/>\n<br/>\n";
                                foreach ($sqls as $key => $value) {
                                    $value = trim($value);
                                    if ($value) {
                                        $num++;
                                        if ($DB->query($value)) {
                                            $ok++;
                                        } else {
                                            $err++;
                                            $error .= "<br/>\n|===第" . ($key + 1) . "行执行失败<br/>\n|---错误信息：" . $DB->error() . "<br/>\n|---错误语句：" . $value . "<br/>\n";
                                        }
                                    }
                                }
                                $error .= "<br/>\n---------文件[" . str_replace(__DIR__ . '/', '', $filePath) . "]错误日志 END---------<br/>\n";
                                saveSetting('version', $build);
                            }
                        }
                    }

                    saveSetting('SQLVERSION', $json['build_sql']);
                    if ($build_new) {
                        saveSetting('version', $build_new);
                    }

                    $msg = "秋云商城提示您：数据库更新到Build " . $json['build_sql'] . " 成功！<br/>更新说明：共检测到" . count($arr) . "个更新文件，执行{$num}条语句，成功{$ok}条！<br/><br/><a href='" . $goto . "'>点此回到上一页</a><br/><br/";
                } else {
                    $msg = "当前数据库已是最新版本V" . $json['version'] . "（build " . $json['build_core'] . "） 无需更新！<br/>如有新版本请下载更新包，然后上传覆盖到当前站点后再试可自动更新~<br/><br/><a href='" . $goto . "'>点此回到上一页</a><br/>";
                }

            } else {
                $msg = "更新数据库失败，本地程序版本文件不完整或已损坏，请下载更新包覆盖再试<br/><br/><a href='" . $goto . "'>点此回到上一页</a>";
            }
        } else {
            $msg = "更新数据库失败，未检测到本地程序版本文件[version.php]，请下载更新包覆盖再试<br/><br/><a href='" . $goto . "'>点此回到上一页</a>";
        }
        saveSetting('sqlupdatetime', time() + 43200);
    } else {
        $msg = "程序未完整安装，数据库链接失败！<br/><br/><a href='" . $goto . "'>点此回到上一页</a>";
    }

} else {
    $msg = '程序未完整安装，请先<a href="./install.php">访问此处</a>重新安装';
}

@header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
	<title>自动更新数据库中 - 秋云商城</title>
	<style type="text/css">html,body{font-size: 16px;}</style>
</head>
<body>
	<?php syshow($msg);?>
</body>
</html>