<?php
$debug     = false;
$webConfig = [];
if (file_exists(dirname(__DIR__) . '/includes/config.php')) {
    $webConfig = include dirname(__DIR__) . '/includes/config.php';
}

register_shutdown_function('callError');

if ($_SERVER['HTTP_HOST'] == '123.zimowl.cn') {
    error_reporting(E_ALL);
} else {
    error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);
}

define('IN_CRONLITE', true);
define('IN_OTHER', true);
define('CACHE_FILE', 0);
define('SYSTEM_ROOT', dirname(__FILE__) . '/');
define('ROOT', dirname(SYSTEM_ROOT) . '/');
date_default_timezone_set('Asia/Shanghai');

$date          = date("Y-m-d H:i:s");
$password_hash = '!@#%!s!0';
if (array_key_exists('HTTPS', $_SERVER) && ($_SERVER['HTTPS'] === 1 || $_SERVER['HTTPS'] === 'on') || array_key_exists('SERVER_PORT', $_SERVER) && $_SERVER['SERVER_PORT'] === 443) {
    define('HTTPS_ROOT', true);
}
!defined('HTTPS_ROOT') && define('HTTPS_ROOT', false);
if (function_exists("set_time_limit")) {
    @set_time_limit(0);
}
if (function_exists("ignore_user_abort")) {
    @ignore_user_abort(true);
}
include_once ROOT . "includes/autoloader.php";
Autoloader::register();

/**
 * 错误处理
 */
function callError()
{
    if (isset($_GET['act']) && $_GET['act'] == 'updateNew') {
        //正在更新时忽略错误
        return true;
    }
    if ($error = error_get_last()) {
        $type    = $error['type'];
        $message = str_replace(ROOT, '', $error['message']);
        $message = str_replace(dirname(ROOT), '', $message);
        $trace   = '';
        if (stripos($message, 'trace:')) {
            $arr   = explode('trace:', $message);
            $msg   = str_ireplace('trace:', '', $arr[0]);
            $trace = $arr[1];
        } else {
            $msg = $message;
            if (function_exists('debug_backtrace')) {
                $trace_arr = debug_backtrace();
                if (is_array($trace_arr)) {
                    foreach ($trace_arr as $key => $row) {
                        if (isset($row['file'])) {
                            $trace .= str_replace(ROOT, '', $row['file']) . '[' . $row['line'] . ']：' . $row['function'];
                        } else {
                            $trace .= str_replace(ROOT, '', is_array($row) ? json_encode($row, 256) : $row);
                        }
                    }
                }
            } elseif (function_exists('error_get_last')) {
                $trace = str_replace(ROOT, '', (string) error_get_last());
            }
        }

        $file = str_replace(ROOT, '', $error['file']);
        $file = str_replace(dirname(ROOT), '', $file);
        $line = $error['line'];

        if (in_array($type, [E_ERROR])) {
            $LOG = new \core\Log(1, 60, 'Error');
            $msg = "{$file}[{$line}]<br/>{$msg}";
            if ($trace) {
                $msg .= "<br/><br/>调用堆栈：{$trace}";
            }

            if (is_object($LOG) && method_exists($LOG, 'add')) {
                $LOG->add('错误日志', $msg . PHP_EOL);
            }
            if (IS_AJAX) {
                exit(json_encode(['code' => -1, 'msg' => $file . "[" . $line . "]<br/>" . $msg]));
            }
            $msg .= "<br/><br/>参考建议：如更新系统、安装插件后出错可能文件缺少导致，可尝试更换php版本、下载更新版覆盖解决";
            if (in_array($type, [E_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
                showErrPage($msg);
            }
        }
    }
}

include ROOT . 'includes/function.php';

$scriptpath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$sitepath   = substr($scriptpath, 0, strrpos($scriptpath, '/'));
$siteurl    = (HTTPS_ROOT ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $sitepath . '/';

if (file_exists(ROOT . 'includes/360safe/360webscan.php')) {
    include_once ROOT . 'includes/360safe/360webscan.php';
}

require ROOT . 'dbconfig.php';
//连接数据库
try {
    $DB = new \core\PdoReger($dbconfig);
} catch (\Exception $e) {
    if (strpos($e->getMessage(), '1045') !== false) {
        sysmsg("系统错误：数据库连接失败，请检查数据库信息账号密码等是否正确！");
    }
    sysmsg("系统错误：" . $e->getMessage());
}

$CACHE = new \core\Cache();
$conf  = $CACHE->read();
if (!is_array($conf) || empty($conf['version'])) {
    $conf = $CACHE->update();
}

if (!isset($conf['syskey']) || empty($conf['syskey'])) {
    $conf['syskey'] = md5(time() . mt_srand(11111, 99999));
    $DB->query("REPLACE INTO `pre_config` SET v= ?,k='syskey'", [$conf['syskey']]);
}

function x_real_ip()
{
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all("#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s", $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] as $xip) {
            if (!preg_match("/^(10|172\.16|127|192\.168)\./", $xip)) {
                $ip = $xip;
            }
        }
    } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } else {
        if ((isset($_SERVER['HTTP_X_REAL_IP']) && preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/", $_SERVER['HTTP_X_REAL_IP']))) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }
    }
    return $ip;
}

$cookiesid = $_COOKIE['mysid'];
if ($cookiesid == "" || !preg_match('/^[0-9a-z]{32}$/i', $cookiesid)) {
    $cookiesid = md5(uniqid(mt_rand(), 1) . time() . x_real_ip());
    setcookie('mysid', $cookiesid, time() + 86400 * 30, '/');
}

define('SYS_KEY', $conf['syskey']);

include ROOT . 'includes/authcode.php';

define('authcode', $authcode);
define('DIST_ID', hexdec($distid));
define('CHECKNOTIFY', $conf['pay_checkNotify'] == 1 ? '1' : '0');

include ROOT . 'includes/lib.class.php';
include ROOT . 'includes/func.class.php';
if (is_file(ROOT . 'includes/member2.php')) {
    include ROOT . 'includes/member2.php';
}
include ROOT . 'includes/ajax.class.php';
if (!$authcode) {
    exit(0);
}

if ($conf['cdnpublic'] == (0 - 1)) {
    $cdnpublic = $conf['cdnpublic_url'];
} elseif ($conf['cdnpublic'] == 1) {
    $cdnpublic = '//lib.baomitu.com/';
} elseif ($conf['cdnpublic'] == 2) {
    $cdnpublic = '//cdn.bootcss.com/';
} elseif ($conf['cdnpublic'] == 3) {
    $cdnpublic = '//cdn.staticfile.org/';
} else {
    $cdnpublic = '../assets/public/';
}

$clientip   = @real_ip();
$payapi     = @payApi();
$codepayapi = @codePayApi();

/**
 * 添加系统日志
 * @param string $action 日志类型
 * @param string $msg    日志内容
 * @param string $name   日志名
 */
if (!function_exists('addWebLog')) {
    function addWebLog($action, $msg = '', $name = 'Pay')
    {
        global $DB;
        $weburl  = addslashes($_SERVER['HTTP_HOST']);
        $siterow = $DB->get_row("SELECT * from `pre_site` where `siteurl2`='" . $weburl . "' or `siteurl2`='" . $weburl . "' limit 1");
        $Log     = new \core\Log(is_array($siterow) ? $siterow['zid'] : 1, 15, $name);
        $Log->add($action, $msg);
        return;
    }
}

if (!function_exists('orderIsCreate')) {
    /**
     * 验证订单是否已生成
     * @param string $payorder   支付订单号
     * @param  integer $tid      商品ID
     */
    function orderIsCreate($payorder = '', $tid = 0)
    {
        global $DB;
        if (empty($payorder)) {
            return true;
        }
        if ($tid > 0) {
            return $DB->count("SELECT count(*) FROM `pre_orders` WHERE `payorder`=:payorder limit 1", [':payorder' => $payorder]) > 0;
        }
        return true;
    }
}

/**
 * 弹窗
 */
function showalert($msg, $status, $out_trade_no = null, $tid = 0)
{
    global $DB, $conf, $zid, $userrow, $isLogin2, $cookiesid;
    if ($tid == -1 && $conf['template'] == 'mall') {
        $link = '../?act=my';
    } elseif ($tid == -1) {
        $link = '../user/';
    } elseif ($tid == -2) {
        $link = '../user/regok.php?orderid=' . $out_trade_no;
    } elseif ($tid == -4) {
        $link = '../sup/#/finance/chonzhi';
    } else {
        $tool = $DB->get_row("SELECT * FROM cmy_tools WHERE `tid`= ? limit 1", [$tid]);
        if ($tool['is_curl'] == 4) {
            $row = $DB->get_row("SELECT * FROM cmy_orders WHERE `payorder`=:payorder limit 1", [':payorder' => $out_trade_no]);
            if (is_array($row)) {
                $link = '../?mod=faka&id=' . $row['id'] . '&skey=' . getOrderSkey($row, 'get');
            } else {
                if (strstr($row['type'], 'rmb') !== false) {
                    $link = '../user/shop.php?act=query&buyok=1';
                } else {
                    $link = '../?act=query&buyok=1';
                }
            }
        } else {
            $srow = $DB->get_row("SELECT type FROM cmy_pay WHERE trade_no=:trade_no limit 1", [':trade_no' => $out_trade_no]);
            if (strstr($srow['type'], 'rmb') !== false) {
                $link = '../user/shop.php?act=query&buyok=1';
            } else {
                $link = '../?act=query&buyok=1';
            }
        }
    }
    echo '<meta charset="utf-8"/><script>alert("' . $msg . '");window.location.href="' . $link . '";</script>';
}
