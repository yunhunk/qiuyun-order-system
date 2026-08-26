<?php
//webscan_error();
//引用配置文件
require_once 'webscan_cache.php';
$siteConfig = [];
if (file_exists(dirname(__DIR__) . '/config.php')) {
    $siteConfig = include dirname(__DIR__) . '/config.php';
}

!defined('ROOT') && define('ROOT', dirname(dirname(__DIR__)));

//get拦截规则 优化版 By 星河
$getfilter = "\\<.+javascript:window\\[.{1}\\\\x|<.*=(&#\\d+?;?)+?>|<.*(data|src)=data:text\\/html.*>|\\b(alert\\(|confirm\\(|expression\\(|prompt\\(|benchmark\\s*?\(.*\)|sleep\\s*?\(.*\)|\\b(group_)?concat[\\s\\/\\*]*?\\([^\\)]+?\\)|\bcase[\\s\/\*]*?when[\\s\/\*]*?\([^\)]+?\)|load_file\\s*?\\()|<[a-z]+?\\b[^>]*?\\bon([a-z]{4,})\\s*?=|^\\+\\/v(8|9)|<.+(javascript|vbscript|expression|applet|meta|xml|blink\\(|link\\(|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)|\\s+(onabort|onactivate|onafterprint|onafterupdate|onbeforeactivate|onbeforecopy|onbeforecut|onbeforedeactivate|onbeforeeditfocus|onbeforepaste|onbeforeprint|onbeforeunload|onbeforeupdate|onblur|onbounce|oncellchange|onchange|onclick|oncontextmenu|oncontrolselect|oncopy|oncut|ondataavailable|ondatasetchanged|ondatasetcomplete|ondblclick|ondeactivate|ondrag|ondragend|ondragenter|ondragleave|ondragover|ondragstart|ondrop|onerror|onerrorupdate|onfilterchange|onfinish|onfocus|onfocusin|onfocusout|onhelp|onkeydown|onkeypress|onkeyup|onlayoutcomplete|onload|onlosecapture|onmousedown|onmouseenter|onmouseleave|onmousemove|onmouseout|onmouseover|onmouseup|onmousewheel|onmove|onmoveend|onmovestart|onpaste|onpropertychange|onreadystatechange|onreset|onresize|onresizeend|onresizestart|onrowenter|onrowexit|onrowsdelete|onrowsinserted|onscroll|onselect|onselectionchange|onselectstart|onstart|onstop|onsubmit|onunload)|\\b(and|or)\\b\\s*?([\\(\\)'\"\\d]+?=[\\(\\)'\"\\d]+?|[\\(\\)'\"a-zA-Z]+?=[\\(\\)'\"a-zA-Z]+?|>|<|\\s+?[\\w]+?\\s+?\\bin\\b\\s*?\(|\\blike\\b\\s+?[\"'])|\\/\\*.*\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT\\s*(\(.+\)\\s*|@{1,2}.+?\\s*|\\s+?.+?|(`|'|\").*?(`|'|\")\\s*)|UPDATE\\s*(\(.+\)\\s*|@{1,2}.+?\\s*|\\s+?.+?|(`|'|\").*?(`|'|\")\\s*)SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE)@{0,2}(\\(.+\\)|\\s+?.+?\\s+?|(`|'|\").*?(`|'|\"))FROM(\\(.+\\)|\\s+?.+?|(`|'|\").*?(`|'|\"))|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)|<.*(iframe|frame|style|embed|object|frameset|meta|xml)|copy\\(|eval|mkdir|assert|rename|chmod|dirname\\(|fputs\\(|file_put_contents|exit|readfile|fflush|fopen\\(|fgetc|fgetcsv|fgetss|file\\(|fwrite\\(|fread\\(|link\\(|linkinfo|pathinfo|realpath|touch|^exec$|
system|chroot|getcwd|scandir|chgrp|chown|shell_exec|pcntl_exec|ini_alter|ini_restore|readlink\\(|popepassthru|imap_open|passthru|curl_multi_exec|escapeshellcmd|escapeshellarg|insert\\s|select\\s|information_schema|union\\s|database|concat|connection_id|group_concat|update\\s|`|create_function|call_user_func|unlink\\(|delete\\s|phpinfo|preg_replace|popen|proc_open|ini_get|ini_set|parse_str|extract|mb_parse_str|import_request_variables|glob\\(|get_defined_vars|get_defined_constants|get_defined_functions|get_included_files|proc_get_status|openlog|syslog|dl\\(|chr\\(";
//post拦截规则 优化版 By 星河
$postfilter = "<.*=(&#\\d+?;?)+?>|<.*data=data:text\\/html.*>|\\b(alert\\(|confirm\\(|expression\\(|prompt\\(|benchmark\\s*?\(.*\)|sleep\\s*?\(.*\)|\\b(group_)?concat[\\s\\/\\*]*?\\([^\\)]+?\\)|\bcase[\\s\/\*]*?when[\\s\/\*]*?\([^\)]+?\)|load_file\\s*?\\()|<.+(javascript|vbscript|expression|applet|meta|xml|blink\\(|link\\(|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)|\\s+(onabort|onactivate|onafterprint|onafterupdate|onbeforeactivate|onbeforecopy|onbeforecut|onbeforedeactivate|onbeforeeditfocus|onbeforepaste|onbeforeprint|onbeforeunload|onbeforeupdate|onblur|onbounce|oncellchange|onchange|onclick|oncontextmenu|oncontrolselect|oncopy|oncut|ondataavailable|ondatasetchanged|ondatasetcomplete|ondblclick|ondeactivate|ondrag|ondragend|ondragenter|ondragleave|ondragover|ondragstart|ondrop|onerror|onerrorupdate|onfilterchange|onfinish|onfocus|onfocusin|onfocusout|onhelp|onkeydown|onkeypress|onkeyup|onlayoutcomplete|onload|onlosecapture|onmousedown|onmouseenter|onmouseleave|onmousemove|onmouseout|onmouseover|onmouseup|onmousewheel|onmove|onmoveend|onmovestart|onpaste|onpropertychange|onreadystatechange|onreset|onresize|onresizeend|onresizestart|onrowenter|onrowexit|onrowsdelete|onrowsinserted|onscroll|onselect|onselectionchange|onselectstart|onstart|onstop|onsubmit|onunload)|<[^>]*?\\b(onerror|onmousemove|onload|onclick|onmouseover)\\b|\\b(and|or)\\b\\s*?([\\(\\)'\"\\d]+?=[\\(\\)'\"\\d]+?|[\\(\\)'\"a-zA-Z]+?=[\\(\\)'\"a-zA-Z]+?|>|<|\\s+?[\\w]+?\\s+?\\bin\\b\\s*?\(|\\blike\\b\\s+?[\"'])|\\/\\*.*\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT\\s*(\(.+\)\\s*|@{1,2}.+?\\s*|\\s+?.+?|(`|'|\").*?(`|'|\")\\s*)|UPDATE\\s*(\(.+\)\\s*|@{1,2}.+?\\s*|\\s+?.+?|(`|'|\").*?(`|'|\")\\s*)SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE)(\\(.+\\)|\\s+?.+?\\s+?|(`|'|\").*?(`|'|\"))FROM(\\(.+\\)|\\s+?.+?|(`|'|\").*?(`|'|\"))|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)|<.*(iframe|frame|style|embed|object|frameset|meta|xml)|copy\\(|eval|mkdir|assert|rename|chmod|dirname\\(|fputs\\(|file_put_contents|exit|readfile|fflush|fopen\\(|fgetc|fgetcsv|fgetss|file\\(|fwrite\\(|fread\\(|link\\(|linkinfo|pathinfo|realpath|touch|^exec$|
system|chroot|getcwd|scandir|chgrp|chown|shell_exec|pcntl_exec|ini_alter|ini_restore|readlink\\(|popepassthru|imap_open|passthru|curl_multi_exec|escapeshellcmd|escapeshellarg|insert\\s|select\\s|information_schema|union\\s|database|concat|connection_id|group_concat|update\\s|`|create_function|call_user_func|unlink\\(|delete\\s|phpinfo|preg_replace|popen|proc_open|ini_get|ini_set|parse_str|extract|mb_parse_str|import_request_variables|glob\\(|get_defined_vars|get_defined_constants|get_defined_functions|get_included_files|proc_get_status|openlog|syslog|dl\\(|chr\\(";
//cookie拦截规则
$cookiefilter = "benchmark\\s*?\(.*\)|sleep\\s*?\(.*\)|load_file\\s*?\\(|\\b(and|or)\\b\\s*?([\\(\\)'\"\\d]+?=[\\(\\)'\"\\d]+?|[\\(\\)'\"a-zA-Z]+?=[\\(\\)'\"a-zA-Z]+?|>|<|\\s+?[\\w]+?\\s+?\\bin\\b\\s*?\(|\\blike\\b\\s+?[\"'])|\\/\\*.*\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT\\s*(\(.+\)\\s*|@{1,2}.+?\\s*|\\s+?.+?|(`|'|\").*?(`|'|\")\\s*)|UPDATE\\s*(\(.+\)\\s*|@{1,2}.+?\\s*|\\s+?.+?|(`|'|\").*?(`|'|\")\\s*)SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE)@{0,2}(\\(.+\\)|\\s+?.+?\\s+?|(`|'|\").*?(`|'|\"))FROM(\\(.+\\)|\\s+?.+?|(`|'|\").*?(`|'|\"))|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
//referer获取
$webscan_referer = empty($_SERVER['HTTP_REFERER']) ? array() : array('HTTP_REFERER' => $_SERVER['HTTP_REFERER']);

//基础拦截规则
$mustfilter = "\\bEXEC\\b|UNION.+?SELECT\\s*(\(.+\)\\s*|@{1,2}.+?\\s*|\\s+?.+?|(`|'|\").*?(`|'|\")\\s*)|UPDATE\\s*(\(.+\)\\s*|@{1,2}.+?\\s*|\\s+?.+?|(`|'|\").*?(`|'|\")\\s*)SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE)@{0,2}(\\(.+\\)|\\s+?.+?\\s+?|(`|'|\").*?(`|'|\"))FROM(\\(.+\\)|\\s+?.+?|(`|'|\").*?(`|'|\"))|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)|<.*(iframe|frame|style|embed|object|frameset|meta|xml)|copy\\(|eval|mkdir|assert|rename|chmod|dirname\\(|fputs\\(|file_put_contents|exit|readfile|fflush|fopen\\(|fgetc|fgetcsv|fgetss|file\\(|fwrite\\(|fread\\(|link\\(|linkinfo|pathinfo|realpath|touch|exec|
system|chroot|getcwd|scandir|chgrp|chown|shell_exec|symlink|ini_alter|ini_restore|readlink\\(|popepassthru|imap_open|passthru|curl_multi_exec|escapeshellcmd|escapeshellarg|insert\\s|select\\s|information_schema|union\\s|database|concat|connection_id|group_concat|update\\s|`|create_function|call_user_func_|unlink\\(|delete\\s|phpinfo\\(|preg_replace|popen|proc_open|ini_get|ini_set|parse_str|extract|mb_parse_str|import_request_variables|glob\\(|get_defined_vars|get_defined_constants|get_defined_functions|get_included_files|proc_get_status|openlog|syslog|apache_setenv|pcntl_([\\w]+)|dl\\(|chr\\(";

/**
 *   关闭用户错误提示
 */
function webscan_error()
{
    if (ini_get('display_errors')) {
        ini_set('display_errors', '0');
    }
}

/**
 *  数据统计回传
 */
function webscan_slog($logs)
{
    if (!class_exists('\core\Log')) {
        include_once ROOT . 'includes/core/Log.php';
    }

    $log = null;
    if (class_exists('\core\Log')) {
        $log = new \core\Log(1, 15, '360safe');
    }
    if (is_object($log)) {
        $msg = is_array($logs) ? json_encode($logs) : $logs;
        $log->add('拦截日志', $msg);
    }
    //日志记录
    return true;
}

/**
 *  参数拆分
 */
function webscan_escape($val)
{
    static $str;
    $arr = array('|', ',', '.', ';', '-', '=', '<', '>', '/', '^', '$', '#');
    if (!webscan_isWhite($arr, $val)) {
        $str = str_replace($arr, '', $val);
    } else {
        $str = $val;
    }
    return $str;
}

function webscan_isWhite($arr, $val)
{
    if (is_array($arr)) {
        foreach ($arr as $key => $value) {
            $arr2 = explode($value, $val);
            if (count($arr2) >= 4) {
                return false;
            }
        }
    }
    return true;
}
function webscan_getFileName($filePath = '')
{
    if (empty($filePath)) {
        $filePath = $_SERVER["REQUEST_URI"];
    }

    $fileName = substr($filePath, strrpos($_SERVER["REQUEST_URI"], "/") + 1, strlen($_SERVER["REQUEST_URI"]) - strrpos($_SERVER["REQUEST_URI"], "/") - 1);
    if (stripos($fileName, '?') !== false) {
        $fileName = substr($fileName, 0, stripos($fileName, "?") - 1);
    }
    return $fileName;
}

/**
 *  防护提示页
 */
function webscan_pape($str = '')
{
    global $siteConfig;
    if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
        $str  = !empty($str) ? "监测到非法字符：" . $str : '提交内容包含危险字符';
        $file = webscan_getFileName();
        if ($file === "shopedit.php" && isset($_GET['act']) && $_GET['act'] == 'edit_submit') {
            $msg = $str . "<br/>请检查商品简介或提示内容并查看源代码是否包含！";
        } else {
            $msg = $str . "，已被程序拦截！<br>您可以规范内容后再尝试提交<br>如误报请联系网站管理员处理";
        }

        $result = ['code' => -1, "msg" => $msg];
        die(json_encode($result, JSON_UNESCAPED_UNICODE));
    }
    @header("HTTP/1.1 403 Forbidden");
    $pape = <<<HTML
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8"/>
<title>输入内容存在危险字符，安全起见，已被本站拦截</title>
<style>
body, h1, h2, p,dl,dd,dt{margin: 0;padding: 0;font: 12px/1.5 微软雅黑,tahoma,arial;}
body{background:#efefef;}
h1, h2, h3, h4, h5, h6 {font-size: 100%;cursor:default;}
ul, ol {list-style: none outside none;}
a {text-decoration: none;color:#447BC4}
a:hover {text-decoration: underline;}
.ip-attack{width:600px; margin:200px auto 0;}
.ip-attack dl{ background:#fff; padding:30px; border-radius:10px;border: 1px solid #CDCDCD;-webkit-box-shadow: 0 0 8px #CDCDCD;-moz-box-shadow: 0 0 8px #cdcdcd;box-shadow: 0 0 8px #CDCDCD;}
.ip-attack dt{text-align:center;}
.ip-attack dd{font-size:16px; color:#333; text-align:center;}
.tips{text-align:center; font-size:14px; line-height:50px; color:#999;}
</style>
</head>
<body>
<div class="ip-attack">
<dl>
<dt><img  src='http://p2.qhimg.com/t016dd70ac04d942b1b.png' /></dt>
HTML;
    if (isset($siteConfig) && $siteConfig['debug'] && $str != "") {
        $pape .= '<h5>' . $str . '</h5>';
    }
    $pape .= <<<HTML2
<dt><a href="javascript:history.go(-1)">返回上一页</a></dt>
</dl>
</div>
</body>
</html>
HTML2;
    die($pape);
}

/**
 *  攻击检查拦截
 */
function webscan_StopAttack($StrFiltKey, $StrFiltValue, $ArrFiltReq, $method)
{
    $StrFiltValue = webscan_escape($StrFiltValue);
    if (preg_match("/" . $ArrFiltReq . "/is", $StrFiltValue, $ma1) === 1) {
        webscan_slog(array('ip' => $_SERVER["REMOTE_ADDR"], 'time' => strftime("%Y-%m-%d %H:%M:%S"), 'page' => $_SERVER["PHP_SELF"], 'method' => $method, 'req' => $ArrFiltReq, 'rkey' => $StrFiltKey, 'rdata' => $StrFiltValue, 'rvalue' => $ma1[0], 'user_agent' => $_SERVER['HTTP_USER_AGENT'], 'request_url' => $_SERVER["REQUEST_URI"]));
        exit(webscan_pape($method . '： [' . $StrFiltValue . '] => ' . $ma1[0]));
    }
    if (preg_match("/" . $ArrFiltReq . "/is", $StrFiltKey, $ma2) === 1) {
        webscan_slog(array('ip' => $_SERVER["REMOTE_ADDR"], 'time' => strftime("%Y-%m-%d %H:%M:%S"), 'page' => $_SERVER["PHP_SELF"], 'method' => $method, 'req' => $ArrFiltReq, 'rkey' => $StrFiltKey, 'rdata' => $StrFiltKey, 'rvalue' => $ma2[0], 'user_agent' => $_SERVER['HTTP_USER_AGENT'], 'request_url' => $_SERVER["REQUEST_URI"]));
        exit(webscan_pape($method . '： ' . $StrFiltKey . '] => ' . $ma2[0]));
    }
}

function preg_string($find = '', $value = '')
{
    return $value && preg_match('/' . $find . '/i', $value) == 1;
}

/**
 *  拦截目录白名单
 */
function webscan_white($webscan_white_name, $webscan_white_url = array())
{
    $url_path  = $_SERVER['SCRIPT_NAME'];
    $url_query = $_SERVER['QUERY_STRING'];

    if (preg_match("/" . $webscan_white_name . "/is", $url_path) == 1 && !empty($webscan_white_name)) {
        return true;
    }

    foreach ($webscan_white_url as $filname => $value) {
        if (preg_string($filname, $url_path)) {
            if (is_array($value)) {
                foreach ($value as $queryString) {
                    if (!empty($queryString) && preg_string($queryString, $url_query)) {
                        return true;
                    }
                }
            } else {
                if (!empty($value) && preg_string($value, $url_query)) {
                    return true;
                }
            }
        }
    }
    return false;
}

if ($webscan_switch && !webscan_white($webscan_white_directory, $webscan_white_url)) {
    if ($webscan_get) {
        foreach ($_GET as $key => $value) {
            if (is_array($webscan_white_keys) && !in_array($key, $webscan_white_keys)) {
                webscan_StopAttack($key, $value, $getfilter, "GET");
            } else {
                webscan_StopAttack($key, $value, $mustfilter, "GET");
            }
        }
    }
    if ($webscan_post) {
        foreach ($_POST as $key => $value) {
            if (is_array($webscan_white_keys) && !in_array($key, $webscan_white_keys)) {
                webscan_StopAttack($key, $value, $postfilter, "POST");
            } else {
                webscan_StopAttack($key, $value, $mustfilter, "POST");
            }
        }
    }
    if ($webscan_cookie) {
        foreach ($_COOKIE as $key => $value) {
            webscan_StopAttack($key, $value, $cookiefilter, "COOKIE");
        }
    }
    if ($webscan_referre) {
        foreach ($webscan_referer as $key => $value) {
            webscan_StopAttack($key, $value, $postfilter, "REFERRER");
        }
    }
}

unset($key);
unset($value);
unset($siteConfig);
