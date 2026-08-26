<?php
function get_curl($url, $post = 0, $referer = 0, $cookie = 0, $header = 0, $ua = 0, $nobaody = 0)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $httpheader[] = "Accept: */*";

    $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
    $httpheader[] = "Content-Type:application/x-www-form-urlencoded; charset=UTF-8";
    $httpheader[] = "X-Requested-With:XMLHttpRequest";
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);

    if ($header) {
        curl_setopt($ch, CURLOPT_HEADER, true);
    } else {
        // 是否抓取头文件的信息
        curl_setopt($ch, CURLOPT_HEADER, false);
    }

    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }

    if ($referer) {
        if ($referer == 1) {
            curl_setopt($ch, CURLOPT_REFERER, 'http://m.qzone.com/infocenter?g_f=');
        } else {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
    }

    if ($ua) {
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    } else {
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.82 Safari/537.36');
    }
    if ($nobaody) {
        curl_setopt($ch, CURLOPT_NOBODY, 1);
    }
    //强制协议为1.0
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    //强制使用IPV4协议解析域名  新加新加
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    //允许重定向
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    //重定向最多4次
    curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
    curl_setopt($ch, CURLOPT_ENCODING, "gzip");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $ret      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (empty($ret)) {
        if ($httpCode !== 200 && !preg_match('/^3[\d]{2}$/', $httpCode) && $httpCode !== 408) {
            $ret = '[' . $httpCode . ']' . curl_error($ch);
        } else {
            $ret = '[' . $httpCode . ']该网站内部出错，未返回任何内容';
        }
    }
    curl_close($ch);
    return $ret;
}

if (!function_exists('createFormList')) {
    function createFormList($options = [], $key = null)
    {
        $html = '';
        if (isset($options['name']) && $key !== null) {
            $html = createFormHtml($options, $key);
        } else {
            if (is_array($options)) {
                foreach ($options as $key => $item) {
                    $html .= createFormHtml($item, $key);
                }
            }
        }
        return $html;
    }
}

if (!function_exists('createFormHtml')) {
    function createFormHtml($item = [], $key = null)
    {
        global $conf;
        $html = '';
        if ($item['type'] == 'textarea') {
            $html = '<div class="form-group"><label class="col-sm-2 control-label">' . $item['name'] . '</label><div class="col-sm-10"><textarea class="form-control" name="' . $key . '" rows="4">';
            $html .= $conf[$key];
            $html .= '</textarea>';
            if ($item['tips']) {
                $html .= '<small>' . $item['tips'] . '</small>';
            }
            $html .= '</div></div><br/>';
        } elseif ($item['type'] == 'select') {
            $html = '<div class="form-group"><label class="col-sm-2 control-label">' . $item['name'] . '</label><div class="col-sm-10"><select class="form-control" name="' . $key . '" default="';
            $html .= $conf[$key];
            $html .= '">';
            foreach ($item['options'] as $key => $name) {
                $html .= '<option value="' . $key . '">' . $name . '</option>';
            }
            $html .= '</select>';
            if ($item['tips']) {
                $html .= '<small>' . $item['tips'] . '</small>';
            }
            $html .= '</div></div><br/>';
        } else {
            $html = '<div class="form-group"><label class="col-sm-2 control-label">' . $item['name'] . '</label><div class="col-sm-10"><input class="form-control" name="' . $key . '" value="';
            $html .= $conf[$key];
            $html .= '"/>';
            if ($item['tips']) {
                $html .= '<small>' . $item['tips'] . '</small>';
            }
            $html .= '</div></div><br/>';
        }
        return $html;
    }
}

/**
 * 排序 从数值大到小 或 从字符长度高到低
 * @param  array  $arr  要排序的数组
 * @param  string $type 排序方式 size 或 length
 * @return array
 */
if (!function_exists('sortByDesc')) {
    function sortByDesc($arr, $type = 'size')
    {
        $count = count($arr);
        for ($i = 0; $i < $count; $i++) {
            for ($x = $i + 1; $x < $count; $x++) {
                if ($type == 'length' && strlen($arr[$i]) == strlen($arr[$x]) && $arr[$i] < $arr[$x]) {
                    $temp    = $arr[$i];
                    $arr[$i] = $arr[$x];
                    $arr[$x] = $temp;
                } elseif ($type == 'size' && $arr[$i] < $arr[$x] || $type == 'length' && strlen($arr[$i]) < strlen($arr[$x])) {
                    $temp    = $arr[$i];
                    $arr[$i] = $arr[$x];
                    $arr[$x] = $temp;
                }
            }
        }
        return $arr;
    }
}

if (!function_exists('writeLogs')) {
    function writeLogs($msg, $fileName = 'djlogs.txt')
    {
        $dirname = ROOT . "includes/logs/";
        if (!is_dir($dirname)) {
            @mkdir($dirname);
        }

        $filepath = $dirname . $fileName;

        if (filesize($filepath) > 1024 * 1024) {
            //超出1MB大小时删除，避免占用过多时间
            @unlink($filepath);
        }
        $fp = fopen($filepath, "a");
        flock($fp, LOCK_EX);
        fwrite($fp, "-------------------------------\n" . date("Y-m-d H:i:s") . "\n" . $msg . "\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

/**
 * 添加价格监控日志
 *
 * @param string $content 日志内容
 */
function addPricejkLogs($content = '')
{
    global $DB, $date;
    return $DB->insert("INSERT INTO `pre_tools_log_pricejk` (`content`,`addtime`) VALUES (:content,:addtime)", [
        ':content' => $content,
        ':addtime' => $date,
    ]);
}

/**
 * 添加系统日志
 * @param string $action 日志类型
 * @param string $msg    日志内容
 * @param string $name   日志名
 */
if (!function_exists('addWebLog')) {
    function addWebLog($action, $msg = '', $name = 'System', $zid = null)
    {
        global $DB, $userrow, $siterow;
        if ($zid == null) {
            if (is_array($userrow) && $userrow['zid']) {
                $zid = $userrow['zid'];
            } else {
                $zid = isset($siterow['zid']) ? $siterow['zid'] : 1;
            }
        }
        $Log = new \core\Log($zid, 15, $name);
        return $Log->add($action, $msg);
    }
}

if (!function_exists('addDebugLog')) {
    /**
     * 添加调试日志
     */
    function addDebugLog($action = '', $msg = null, $name = 'System', $zid = null)
    {
        global $webConfig;
        if (is_array($webConfig) && $webConfig['debug']) {
            return addWebLog($action, $msg, $name, $zid);
        }
    }
}

if (!function_exists('webLog_error')) {
    /**
     * 添加系统错误日志
     * @param string $action 日志类型
     * @param string $msg    日志内容
     */
    function webLog_error($action, $msg = '')
    {
        return addWebLog($action, $msg, 'Error', 1);
    }
}

if (!function_exists('webLog_pay')) {
    /**
     * 添加系统支付日志
     * @param string $action 日志类型
     * @param string $msg    日志内容
     */
    function webLog_pay($action, $msg = '')
    {
        return addWebLog($action, $msg, 'Pay', 1);
    }
}

if (!function_exists('webLog_fenzhan')) {
    /**
     * 添加系统分站日志
     * @param string $action 日志类型
     * @param string $msg    日志内容
     */
    function webLog_fenzhan($action, $msg = '')
    {
        return addWebLog($action, $msg, 'Fenzhan');
    }
}

if (!function_exists('webLog_order')) {
    /**
     * 添加系统订单日志
     * @param string $action 日志类型
     * @param string $msg    日志内容
     */
    function webLog_order($action, $msg = '')
    {
        return addWebLog($action, $msg, 'Order');
    }
}

if (!function_exists('webLog_admin')) {
    /**
     * 添加系统后台日志
     * @param string $action 日志类型
     * @param string $msg    日志内容
     */
    function webLog_admin($action, $msg = '')
    {
        return addWebLog($action, $msg, 'Admin');
    }
}
if (!function_exists('getFileName')) {
    function getFileName($filePath = '')
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
}
if (!function_exists('checkTableExists')) {
    function checkTableExists($table)
    {
        global $DB;
        $sql = "select count(TABLE_NAME) from INFORMATION_SCHEMA.TABLES where TABLE_NAME='" . addslashes($table) . "'";
        if ($DB->count($sql) > 0) {
            return true;
        } else {
            return false;
        }
    }
}
if (!function_exists('checkColumnExists')) {
    function checkColumnExists($table, $column)
    {
        global $DB;
        $sql = "select count(COLUMN_NAME) from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = '" . addslashes($table) . "' and COLUMN_NAME = '" . addslashes($column) . "'";
        if ($DB->count($sql) > 0) {
            return true;
        } else {
            return false;
        }
    }
}
if (!function_exists('isChinaIp')) {
    /**
     * 检测ip是否为国内
     * @param  string  $ip ip地址
     */
    function isChinaIp($ip = '')
    {
        if ($ip == '') {
            return false;
        }

        $apis = [
            'http://whois.pconline.com.cn/ipJson.jsp?ip={ip}&json=true',
            'http://ip-api.com/json/{ip}?lang=zh-CN',
        ];
        foreach ($apis as $key => $api) {
            $api = str_replace('{ip}', $ip, $api);
            $ret = cm_curl($api, 5);
            $ret = '{' . getSubstr($ret, '{', '}') . '}';
            if (is_array($arr = json_decode($ret, true))) {
                if ($key == 0 && isset($arr['addr']) && isChinaProvince($arr['addr'])) {
                    return true;
                } elseif ($key == 1 && (stripos($arr['country'], 'China') !== false || stripos($arr['countryCode'], 'cn') !== false)) {
                    return true;
                }
            }
        }
        return false;
    }

}

/**
 * 重置某商品排序 新上架商品用
 *
 * @return void
 */
/**
 * 重置某商品排序 新上架商品用
 *
 * @param  int $tid 商品ID
 * @param string $type 排序规则 top 置顶 bottom 置底
 * @return void
 */
function resetGoodsSort($tid = 0, $type = null)
{
    global $DB, $conf;
    return;
}

/**
 * 生成当前的商品上架日志
 *
 * @param string $date  日期 date格式
 * @param integer  $type
 * @return void
 */
function createToolsLogs($date = null, $type = 0)
{
    global $date;
    if (is_null($date)) {
        $time = date('Y-m-d');
    }
    $content = createToolsLogsHtml($date, $type);

    \core\Db::name('tools_message')->insert([
        'time'    => $time,
        'name'    => $time . '新增商品',
        'content' => $content,
        'addtime' => $date,
    ]);
}

/**
 * 获取指定日期的商品上架日志代码
 *
 * @param string $date 日期 date格式
 * @param integer $condition 审核状态
 * @return string
 */
function createToolsLogsHtml($time = null, $condition = 1)
{
    if (is_null($time)) {
        $time = date('Y-m-d');
    }

    $html = '';
    $list = \core\Db::name('tools')->where([
        'addtime'   => ['>=', $time . ' 00:00:00'],
        'condition' => ['=', $condition],
    ])->select();
    foreach ($list as $key => $value) {
        $html .= "新上架 " . $value['name'] . " " . $value['price'] . "元<br/>\n";
    }

    return $html;
}

if (!function_exists('isChinaProvince')) {
    function isChinaProvince($str)
    {
        $preg = '北京|天津|河北|山西|内蒙古|辽宁|吉林|黑龙江|上海|江苏|浙江|安徽|福建|江西|山东|河南|湖北|湖南|广东|广西|海南|重庆|四川|贵州|云南|西藏|陕西|甘肃|青海|宁夏|新疆|台湾|香港|澳门|海外';
        return preg_match('/' . $preg . '/', $str);
    }
}

function get_background_image($value = '')
{
    global $conf, $CACHE;
    $background_image = '';
    if ($conf['ui_bing'] > 0) {
        if ($conf['ui_bing'] == 1 && date("Ymd") == $conf['ui_bing_date']) {
            $background_image = $conf['ui_backgroundurl'];
            if (checkmobile() == true) {
                $background_image = str_replace('1920x1080', '768x1366', $background_image);
            }
        } else {
            if (time() - $conf['ui_bing_time'] < 180) {
                $background_image = $conf['ui_backgroundurl'];
            } else {
                $url       = 'http://cn.bing.com/HPImageArchive.aspx?format=js&idx=0&n=1';
                $bing_data = file_get_contents($url);
                $bing_arr  = json_decode($bing_data, true);
                if (!empty($bing_arr['images'][0]['url'])) {
                    $background_image = '//cn.bing.com' . $bing_arr['images'][0]['url'];
                    // saveSetting('ui_backgroundurl', $background_image);
                    if ($conf['ui_bing'] == 1) {
                        saveSetting('ui_bing_date', date("Ymd"));
                        $CACHE->clear();
                    } else {
                        saveSetting('ui_bing_time', time());
                    }
                    if (checkmobile() == true) {
                        $background_image = str_replace('1920x1080', '768x1366', $background_image);
                    }
                }
            }
        }
        $conf['ui_background'] = 3;
    } else {
        if ($conf['ui_bing_img'] != "") {
            if (strpos($conf['ui_bing_img'], 'http') !== false) {
                $background_image = $conf['ui_bing_img'];
            } else {
                if (checkIsAdminOrUser()) {
                    $background_image = '../' . $conf['ui_bing_img'];
                } else {
                    $background_image = './' . $conf['ui_bing_img'];
                }
            }
        } else {
            if (checkIsAdminOrUser()) {
                $background_image = '../assets/img/bj.png';
            } else {
                $background_image = parse_image('assets/img/bj.png');
            }
        }
    }
    return $background_image;
}

if (!function_exists('session_set')) {
    function session_set($value, $exp = 60)
    {
        global $DB, $cookiesid;
        //清理过期session
        if (is_object($DB) && method_exists($DB, 'query')) {
            $DB->query("DELETE FROM `pre_session` WHERE `exp`<:exp", [':exp' => time() - $exp * 2]);
            //更新当前客户连接session
            $row = $DB->get_row("SELECT * FROM `pre_session` WHERE `k`= ?", [$cookiesid]);
            if (is_array($row)) {
                $sql = "UPDATE `pre_session` SET `v`=:value,`exp`=:exp WHERE `id`='{$row['id']}'";
                return @$DB->query($sql, [
                    ':value' => $value,
                    ':exp'   => time() + $exp,
                ]);
            } else {
                $sql = "INSERT INTO `pre_session` (`k`,`v`,`exp`) VALUES (:k, :v, :exp)";
                return @$DB->query($sql, [
                    ':k'   => $cookiesid,
                    ':v'   => $value,
                    ':exp' => time() + $exp,
                ]);
            }
        }
        return false;
    }
}

if (!function_exists('session_get')) {
    function session_get()
    {
        global $DB, $cookiesid;
        if (is_object($DB) && method_exists($DB, 'get_row')) {
            $row = $DB->get_row("SELECT v,exp FROM `pre_session` WHERE `k`= ?", [$cookiesid]);
            if (is_array($row) && $row['exp'] > time()) {
                return $row['v'];
            }
            return '';
        }
        return '';
    }
}

if (!function_exists('addsalt_create')) {
    function addsalt_create()
    {

        $addsalt = session_get();
        if (empty($addsalt) || isset($_GET['mod']) && $_GET['mod'] != 'index') {
            $addsalt = md5(rand(1111, 9999) . x_real_ip() . time());
            session_set($addsalt, 600);
        }
        $x          = new \core\HieroGlyphy();
        $addsalt_js = $x->hieroglyphyString($addsalt);
        return $addsalt_js;
    }
}

function getFakaInput()
{
    global $conf;
    $faka_input = intval($conf['faka_input']);
    if ($faka_input == 4) {
        $name = "hide";
    } elseif ($faka_input == (-1)) {
        if ($conf['faka_input_index']) {
            $name = $conf['faka_input_index'];
        } else {
            $name = "你的邮箱";
        }
    } else {
        if ($faka_input == 1) {
            $name = "QQ邮箱";
        } elseif ($faka_input == 2) {
            $name = "手机号码";
        } elseif ($faka_input == 3) {
            $name = "你的ＱＱ";
        } else {
            $name = "你的邮箱";
        }
    }

    if (!$name) {
        $name = "你的邮箱";
    }
    return $name;
}

if (!function_exists('input')) {
    function input($key = '', $retag = 0, $addslashes = 1)
    {
        return getParams($key, $addslashes, $retag);
    }
}

function getParams($key = '', $addslashes = 1, $retag = 0)
{
    if (empty($key)) {
        return '';
    }

    $method = null;
    $params = '';
    if (strpos($key, '.') !== false && $arr = explode('.', $key)) {
        $methods = ['GET', 'POST'];
        if (array_key_exists(1, $arr) && in_array(strtoupper($arr[0]), $methods)) {
            $method = strtoupper($arr[0]);
            $params = $arr[1];
        }
    }

    if (!is_null($method)) {
        if ($method === "GET") {
            if (!empty($params)) {
                $value = $_GET[$params];
            } else {
                $value = $_GET;
            }
        } elseif ($method === "POST") {
            if (!empty($params)) {
                $value = $_POST[$params];
            } else {
                $value = $_POST;
            }
        } else {
            return '';
        }

    } else {
        if (isset($_GET[$key])) {
            $value = $_GET[$key];
        } elseif (isset($_POST[$key])) {
            $value = $_POST[$key];
        } else {
            return '';
        }
    }

    if ($addslashes && function_exists('daddslashes')) {
        $value = daddslashes($value);
    } else {
        $value = filter_escape($value, $addslashes);
    }

    if ($retag) {
        $value = filter_tags($value);
    }

    return filter_trim($value);
}

function filter_trim($_var)
{
    if (is_array($_var)) {
        foreach ($_var as $key => $val) {
            if (is_array($val)) {
                $_var[$key] = filter_trim($val);
            } else {
                $_var[$key] = trim($val);
            }
        }
    } else {
        $_var = trim($_var);
    }
    return $_var;
}

function filter_escape($_var, $addslashes = 1)
{
    $ad = stripos(ini_get('magic_quotes_gpc'), 'on') !== false;
    if (!$ad || $addslashes) {
        if (is_array($_var)) {
            foreach ($_var as $key => $val) {
                if (is_array($val)) {
                    $_var[$key] = filter_escape($val);
                } else {
                    $_var[$key] = trim(addslashes($val));
                }
            }
        } else {
            $_var = trim(addslashes($_var));
        }
    }

    return $_var;
}

function filter_tags($_var)
{
    if (is_array($_var)) {
        foreach ($_var as $key => $val) {
            if (is_array($val)) {
                $_var[$key] = filter_tags($val);
            } else {
                $_var[$key] = trim(strip_tags($val));
            }
        }
    } else {
        $_var = trim(strip_tags($_var));
    }
    return $_var;
}

if (!function_exists('getOrderSkey')) {
    function getOrderSkey($row, $type = 'check')
    {
        global $conf, $isLogin2, $userrow, $cookiesid;
        if ($conf['query_checkcookie'] == 1) {
            if ($type == 'get') {
                if ($isLogin2 === 1 && is_array($userrow) && isset($userrow['zid'])) {
                    $cookiesid = $userrow['zid'];
                }
                return md5($row['id'] . SYS_KEY . $cookiesid . $row['id']);
            } else {
                //check
                return md5($row['id'] . SYS_KEY . $row['userid'] . $row['id']);
            }
        } else {
            if ($isLogin2 == 1) {
                return md5($row['id'] . SYS_KEY . $userrow['zid'] . $row['id']);
            } else {
                return md5($row['id'] . SYS_KEY . $row['userid'] . $row['id']);
            }
        }
    }
}

if (!function_exists('parse_image')) {
    /**
     * 格式化图片url
     * @param  string $url url
     */
    function parse_image($url = '')
    {
        global $weburl;
        $url = trim($url);
        if (substr($url, 0, 4) == 'http' || substr($url, 0, 2) == '//') {
            return $url;
        } else {
            $weburl = str_replace('user/', '', $weburl);
            if (substr($url, 0, 1) == '/') {
                $url = ltrim($url, '/');
            }
            $url = $weburl . $url;
            return $url;
        }
    }
}

if (!function_exists('parse_site_url')) {
    /**
     * 格式化分站url
     * @param  string $url url
     */
    function parse_site_url($url = '', $protocol = true)
    {
        global $sitepath;
        // 处理子级目录情况
        $addpath = trim(str_replace('user', '', $sitepath), '/');
        if ($addpath && preg_match('/^[\w\/]+$/', $addpath) == 1) {
            if (!$protocol) {
                return $url . '/' . $addpath;
            }
            return (HTTPS_ROOT ? 'https://' : 'http://') . $url . '/' . $addpath . '/';
        }

        if (!$protocol) {
            return $url;
        }
        return (HTTPS_ROOT ? 'https://' : 'http://') . $url . '/';

    }
}

/**
 * 快捷获取数组指定键的成员 可避免php框架的严格模式下报错
 * 如 $arr = ['name'=>'测试','data'=> ['a'=>'555','b'=>'6565']]; 可以 array_get('data.b', $arr);
 * @param  string|number $key     key 支持无限级获取
 * @param  array         $array   数组
 * @param  string        $default 默认值
 */
function array_get($key = null, $array = [], $default = '')
{
    if (is_array($array)) {
        if (is_null($key)) {
            $key = '0';
        }
        if (strpos($key, '.') > 0) {
            $temp   = explode('.', $key, 2);
            $key2   = $temp[0];
            $array2 = array_has($key2, $array) ? $array[$key2] : $default;
            if (is_array($array2)) {
                return array_get($temp[1], $array2);
            }
            return $default;
        }
        return array_has($key, $array) ? $array[$key] : $default;
    }
    return $default;
}

/**
 * 快捷检测数组指定键是否存在 支持 array_has('key0.key1.name',$array)
 * @param  string $key   key
 * @return [type]        数组
 */
function array_has($key = null, $array = [])
{
    if (is_array($array) && !is_null($key)) {
        if (strpos($key, '.') > 0) {
            $temp   = explode('.', $key, 2);
            $array2 = array_has($temp[0], $array) ? $array[$temp[0]] : false;
            if ($array2 && $temp[1]) {
                return array_has($temp[1], $array2);
            }
            return false;
        } else {
            return array_key_exists($key, $array);
        }
    }
    return false;
}

/**
 * 获取配置
 *
 * @param string $name 键名
 * @param mixed  $default 默认值
 * @return mixed
 */
function conf($name = '', $default = null)
{
    global $conf;

    if (is_string($name) && isset($conf[$name])) {
        return $conf[$name];
    }
    return $default;
}

/**
 * 格式化资源文件地址
 *
 * @param [type] $url
 * @param boolean $domain
 * @return void
 */
function cdnurl($url = null, $domain = true)
{
    if ($url) {
        $url = trim($url);
        $url = str_replace('{weburl}', '', $url);
        if (!preg_match('/^(http|https|\/\/):/', $url)) {
            $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
            $url   = preg_match($regex, $url) ? $url : rtrim(WEB_URL, '/') . '/' . ltrim($url, '/');
            if ($domain && !preg_match('/^(http|https|\/\/):\/\//', $url)) {
                $url = rtrim(WEB_URL, '/') . '/' . ltrim($url, '/');
            }
        }
    }
    return $url;
}

if (!function_exists('strFilter')) {
    function strFilter($value = '')
    {
        global $conf;
        if (!isset($conf['filter_words_preg']) || $conf['filter_words_preg'] == '') {
            $conf['filter_words_preg'] = '5Luj5Yi3fOS7o+WIt+e9kXznp5LnoI185Yi3fOiHquWKqeS4i+WNlXzliLfotZ585b+r5omLfOaKlumfs3zljaHnm598UVHpkrt86LWefOWUsHzotIp85Yi36ZK7fOWIt+ermXzlj5HljaF854K55Y2hfOWNoemSu3zovoXliql85ZCN54mH6LWefOepuumXtOS6uuawlHznqbrpl7Torr/lrqJ86K+06K+06LWefOWIt+WNlXzlvIDpkrt86ZK75Y2hfOWIt+S8muWRmHzlpJbmjIJ86I+g6I+cfOmAj+inhnzoh6rnnoR86aOe5aSpfOenkuaKonzlkozlubN8UFVCRw==';
        }
        $preg = '/' . base64_decode($conf['filter_words_preg']) . '/';
        return preg_replace($preg, '', $value);
    }
}

/**
 * 输出成功json数据并退出执行
 * @param  string  $msg  提示信息
 * @param  integer $code 状态码 默认 -1
 * @param  array   $data 数据
 */
function json($msg = '', $code = -1, $data = [])
{
    @header('Content-Type: text/json; charset=UTF-8');

    $result = [
        'code'      => $code,
        'msg'       => $msg,
        'total'     => is_array($data) ? count($data) : 0,
        'data'      => is_array($data) ? $data : [$data],
        'timestamp' => time(),
    ];
    die(json_encode($result, JSON_UNESCAPED_UNICODE));
}

/**
 * 输出成功json数据并退出执行
 * @param  string $msg     提示信息
 * @param  array  $data    数据
 * @param  array  $console 调试信息
 * @return string
 */
function json_success($msg = '', $data = [], $console = [])
{
    @header('Content-Type: text/json; charset=UTF-8');
    $result = [
        'code'    => 0,
        'msg'     => $msg,
        'total'   => count($data),
        'data'    => is_array($data) ? $data : [$data],
        'console' => $console,
    ];
    die(json_encode($result, JSON_UNESCAPED_UNICODE));
}

/**
 * 输出错误json数据并退出执行
 * @param  string $msg     提示信息
 * @param  array  $data    数据
 * @param  array  $console 调试信息
 * @return string
 */
function json_error($msg = '', $data = [], $console = [])
{
    @header('Content-Type: text/json; charset=UTF-8');
    $result = [
        'code'    => -1,
        'msg'     => $msg,
        'total'   => count($data),
        'data'    => is_array($data) ? $data : [$data],
        'console' => $console,
    ];
    die(json_encode($result, JSON_UNESCAPED_UNICODE));
}

/**
 * 添加价格变动日志
 *
 * @param integer $tid
 * @param string $name
 * @param float $price
 * @param float $after
 * @param string $action
 * @param string $desc
 * @return void
 */
function addToolLogs($tid = 0, $name = '', $price = 0.00, $after = 0.00, $action = '价格变动', $desc = '')
{
    global $DB;
    $sql  = "INSERT INTO `pre_tools_log` (`tid`,`name`,`before`,`after`,`action`,`desc`,`addtime`) VALUES (:tid,:name,:before,:after,:action,:desc,:addtime)";
    $data = [
        ':tid'     => $tid,
        ':name'    => $name,
        ':before'  => $price,
        ':after'   => $after,
        ':action'  => $action,
        ':desc'    => $desc,
        ':addtime' => time(),
    ];
    if ($DB->exec($sql, $data) !== false) {
        return true;
    }
    return $DB->error();
}

/**
 * 获取商品动态文章 默认从新到旧
 *
 * @param integer $day  近几天的
 * @param integer $page 第第几页
 * @return array
 */
function getToolMessage($day = 5, $page = 1, $order = 'DESC')
{

    global $DB, $conf;
    $total = \core\Db::name('tools_message')->count();

    if ($day > 0) {
        $limit = $day;
    } else {
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;
    }
    $page   = $page ? intval($page) : ($_GET['page'] ? intval($_GET['page']) : 5);
    $pages  = ceil($total / $limit);
    $offset = $page > 1 ? $limit * ($page - 1) : 0;
    if (!$order || !in_array(strtoupper($order), ['DESC', 'ASC'])) {
        $order = 'DESC';
    }

    $rows = [];

    if ($conf['tool_log_type'] == 2) {
        $rows = $DB->select("SELECT * FROM pre_tools_message  ORDER BY id " . $order . ' limit 5');
    } else {
        for ($i = 0; $i < $limit; $i++) {
            $thtime  = strtotime(date('Y-m-d', strtotime('-' . ($i + $offset) . ' day')) . ' 00:00:00');
            $endtime = strtotime(date('Y-m-d', $thtime) . ' 23:59:59');
            $time    = date('Y-m-d', strtotime('-' . ($i + $offset) . ' day'));

            // var_dump("SELECT * FROM pre_tools_message where `time`={$time} ORDER BY id " . $order);
            $item = $DB->find("SELECT * FROM pre_tools_message where `time`='{$time}' ORDER BY id " . $order);
            if (!$item && $conf['tool_log_type'] == 1) {
                // // 未找到则自动查找自动日志
                // $item = [
                //     'time'    => date('Y-m-d', strtotime('-' . $i . ' day')),
                //     'endtime' => date('Y-m-d', $thtime) . ' 23:59:59',
                //     'list'    => $DB->select("SELECT * FROM pre_tools_log where `desc` like '%新上架商品%' and `addtime`>={$thtime} AND `addtime`<={$endtime} ORDER BY id " . $order . ' limit 5'),
                //     'content' => '',
                // ];

                // if ($item['list']) {
                //     $item['list'] = parse_unique($item['list'], 'tid');
                //     foreach ($item['list'] as $key => &$value) {
                //         $value['time'] = date('Y-m-d H:i:s', $value['addtime']);
                //     }
                // }
            } else {
                $item['time']    = date('Y-m-d', strtotime('-' . $i . ' day'));
                $item['endtime'] = date('Y-m-d', $thtime) . ' 23:59:59';
                $item['list']    = [];
                $item['content'] = $item['content'] ? $item['content'] : "";
            }

            if (count($item['list']) > 0 || $item['content']) {
                $rows[] = $item;
            }
        }
    }

    // die;
    $result = [
        'code'  => 0,
        'msg'   => '成功',
        'total' => $total,
        'page'  => $pages,
        'pages' => $pages,
        'data'  => $rows,
        'limit' => $limit,
    ];
    return $result;
}

/**
 * 去除二维数组重复数据
 *
 * @param array $array
 * @param null|string $field
 * @return array
 */
function parse_unique($array = [], $field = null)
{
    if (!$field) {
        return array_unique($array);
    } else {
        $newArr  = [];
        $tempArr = [];
        foreach ($array as $key => $value) {
            if (isset($value[$field]) && !in_array($value[$field], $tempArr)) {
                $newArr[]  = $value;
                $tempArr[] = $value[$field];
            }
        }
        return $newArr;
    }
}

function writeNotifyLogs($msg)
{
    $type    = isset($_GET['type']) && !empty($_GET['type']) ? input('get.type') : 'other';
    $dirpath = ROOT . 'other/logs/';
    if (!is_dir($dirpath)) {
        @mkdir($dirpath, 0755);
    }
    $filename = $type . 'Logs_' . md5(SYS_KEY . '_chenmMall') . '.txt';
    $filepath = $dirpath . $filename;
    if (filesize($filepath) > 1024 * 1024) {
        //超出1MB大小时删除，避免占用过多时间
        @unlink($filepath);
    }
    if (file_put_contents($filepath, "测试写入")) {
        return file_put_contents($filepath, "-------------------------------\n" . date("Y-m-d H:i:s") . "\n" . $msg . "\n");
    }
    // $fp = fopen($filepath, "a");
    // flock($fp, LOCK_EX);
    // fwrite($fp, "-------------------------------\n" . date("Y-m-d H:i:s") . "\n" . $msg . "\n");
    // flock($fp, LOCK_UN);
    // fclose($fp);
    return false;
}

function getIp()
{
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
        $ip = getenv("HTTP_CLIENT_IP");
    } else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    } else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
        $ip = getenv("REMOTE_ADDR");
    } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = "unknown";
    }

    return ($ip);
}

function real_ip()
{
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] as $xip) {
            if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                $ip = $xip;
                break;
            }
        }
    } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (isset($_SERVER['HTTP_X_REAL_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    return $ip;
}

function get_ip_city($ip)
{
    $text = get_curl('http://sp0.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?query=' . $ip . '&co=&resource_id=6006');
    $json = json_decode($text, true);
    if (is_array($json) && $json['status'] == 0) {
        if ($json['data']['location']) {
            $location = $json['data']['location'];
        } elseif ($json['data'][0]['location']) {
            $location = $json['data'][0]['location'];
        } else {
            $location = '';
        }
    } else {
        $url  = 'http://whois.pconline.com.cn/ipJson.jsp?json=true&ip=';
        $text = get_curl($url . $ip);
        $text = mb_convert_encoding($text, "UTF-8", "GB2312");
        $json = json_decode($text, true);
        if ($json['city']) {
            $location = $json['pro'] . $json['city'];
        } else {
            $location = $json['addr'] ? $json['addr'] : $json['pro'];
        }
    }
    return ip_city_str($location);
}

function ip_city_str($str)
{
    return str_replace(array('省', '市'), '', $str);
}

function daddslashes($string, $force = 0, $strip = false)
{
    !defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', preg_match('/on/i', ini_get('magic_quotes_gpc')) == 1);
    if (!MAGIC_QUOTES_GPC || $force) {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = daddslashes($val, $force, $strip);
            }
        } else {
            $string = addslashes($strip ? stripslashes($string) : $string);
        }
    }
    return $string;
}

function strexists($string, $find)
{
    return !(strpos($string, $find) === false);
}

function dstrpos($string, $arr)
{
    if (empty($string)) {
        return false;
    }

    foreach ((array) $arr as $v) {
        if (strpos($string, $v) !== false) {
            return true;
        }
    }
    return false;
}

function checkmobile()
{
    $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $ualist    = array('android', 'midp', 'nokia', 'mobile', 'iphone', 'ipod', 'blackberry', 'windows phone');
    if ((dstrpos($useragent, $ualist) || strexists($_SERVER['HTTP_ACCEPT'], "VND.WAP") || strexists($_SERVER['HTTP_VIA'], "wap"))) {
        return true;
    } else {
        return false;
    }
}

function checkEmail($input)
{
    if (preg_match('/^([\w\-\_]+)@([\w\-]+)\.([\w]+)$/', $input) && strlen($input) <= 60) {
        return true;
    }
    return false;
}

/**
 * 取中间文本
 * @param string $str
 * @param string  $leftStr
 * @param string $rightStr
 */
function getSubstr($str = '', $leftStr = '', $rightStr = '')
{
    $left  = strpos($str, $leftStr);
    $right = strpos($str, $rightStr, $left);
    if ($left < 0 or $right < $left) {
        return '';
    }
    return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
}

function random($length, $numeric = 0)
{
    $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    $hash = '';
    $max  = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed[mt_rand(0, $max)];
    }
    return $hash;
}

function randomNumer($length)
{
    $hash = '';
    for ($i = 0; $i < $length; $i++) {
        $hash .= mt_rand(0, 9);
    }
    return $hash;
}

function get_rand($proArr)
{
    $result = "";
    $proSum = array_sum($proArr);
    foreach ($proArr as $key => $proCur) {
        $randNum = mt_rand(1, $proSum);
        if ($randNum <= $proCur && $proCur > 0) {
            $result = $key;
            break;
        }
        $proSum -= $proCur;
    }
    unset($proArr);
    return $result;
}

if (!function_exists('guolv')) {
    /**
     * 过滤脚本标签
     */
    function guolv($str)
    {
        if ($str) {
            $key = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');

            $key2   = ['<script(.*?)>', "<script", '<link', '<link(.*?)>', '<iframe', '<head(.*?)>', '<applet', "<meta(.*?)>", "<meta", "<javascript(.*?)>", "<javascript", "<vbscript(.*?)>", "<vbscript", "<base", "<title", "<embed", "object", "xml", "<xml", "<\?php", "<\?", "<\?=", "<%", "<%="];
            $key    = array_merge($key, $key2);
            $newStr = preg_replace('/' . implode('|', $key) . '/i', '', $str);
            if ($newStr) {
                return $newStr;
            }
            return preg_replace('/' . implode('|', $key2) . '/i', '', $str);
        }
        return '';
    }
}

if (!function_exists('xss_filter')) {
    /**
     * 过滤xss
     * @param  string   $value      被过滤字符串
     * @param  bool     $html_tage  是否剥离html标签
     * @return string
     */
    function xss_filter($value, $html_tage = true)
    {
        if ($html_tage) {
            return strip_tags(htmlspecialchars(guolv($value)));
        }
        return guolv($value);
    }
}

function getInputsBtn($input, $inputs)
{
    $InputsBtn = array();

    if (!empty($input)) {
        if (stripos($input, "&") !== false) {
            $input = explode("&", $input)[0];
        }
        $InputsBtn[] = $input;
    }

    if ($inputs == "") {
        return $InputsBtn;
    }

    if (!is_array($inputs)) {
        $inputs = explode('|', $inputs);
    }

    for ($i = 0; $i < count($inputs); $i++) {
        if (stripos($inputs[$i], "&") !== false) {
            $inputs[$i] = explode("&", $inputs[$i])[0];
        }

        if (stripos($input, "{") !== false) {
            $input = explode("{", $input)[0];
        }

        if (stripos($input, "[") !== false) {
            $input = explode("[", $input)[0];
        }

        $InputsBtn[] = $inputs[$i];
    }

    return $InputsBtn;
}

if (!function_exists('isSsl')) {
    function isSsl()
    {
        if (defined('HTTPS_ROOT')) {
            return HTTPS_ROOT;
        }
        $HTTPS = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : '';
        if (preg_match('/1|on/', $HTTPS) || $_SERVER['SERVER_PORT'] == 443) {
            return true;
        }
        return false;
    }
}

if (!function_exists('getHostUrl')) {
    /**
     * 获取当前页面url
     * @param  boolean $all 是否获取完整地址
     * @return string
     */
    function getHostUrl($all = false)
    {
        $protocol = isSsl() ? 'https' : 'http';
        $url      = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        if ($all && isset($_SERVER['QUERY_STRING'])) {
            $url .= '?' . $_SERVER['QUERY_STRING'];
        }
        return $url;
    }
}

function getSelect($selectstr, $val = '')
{
    if (stripos($selectstr, '{') !== false) {
        $selectstr = explode('{', $selectstr)[1];
        $selectstr = explode('}', $selectstr)[0];
    }

    if (stripos($selectstr, '[') !== false) {
        $selectstr = explode('[', $selectstr)[1];
        $selectstr = explode(']', $selectstr)[0];
    }

    $arr    = explode(",", $selectstr);
    $select = "";
    foreach ($arr as $str) {
        if (stripos($str, ':') !== false) {
            $k = explode(":", $str)[0];
            $v = explode(":", $str)[0];
        } else {
            $k = $str;
            $v = $str;
        }
        $select .= '<option value="' . $k . '" ' . ($val == $v && $val != "" ? ' selected="selected"' : '') . '>' . $v . '</option>';
    }
    return $select;
}

/**
 * 系统提示
 *
 * @param string $content
 * @param integer $type  提示等级 1 success 2 info 3 warning 4 danger
 * @param boolean|string $back 是否返回上一页
 * @param boolean $head 是否增加css样式head
 * @return void
 */
function showmsg($content = '未知的异常', $type = 4, $back = false, $head = false)
{
    global $cdnpublic, $cdnserver, $conf;
    switch ($type) {
        case 1:
            $panel = "success";
            break;
        case 2:
            $panel = "info";
            break;
        case 3:
            $panel = "warning";
            break;
        case 4:
            $panel = "danger";
            break;
    }

    if ($head) {
        if (empty($cdnpublic)) {
            $cdnpublic = '//lib.baomitu.com/';
        }

        if (empty($cdnserver)) {
            $cdnserver = '/';
        }

        echo '<!DOCTYPE html>
            <html xmlns="http://www.w3.org/1999/xhtml" lang="zh-cn">
            <head>
              <meta charset="utf-8"/>
              <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
              <title>提示信息 - ' . $conf['sitename'] . '</title>
              <link href="' . $cdnpublic . 'twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
              <link href="' . $cdnpublic . 'font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
              <link rel="stylesheet" href="' . $cdnserver . 'assets/simple/css/main.css">
              <link rel="stylesheet" href="' . $cdnserver . 'assets/simple/css/oneui.css">
              <script src="' . $cdnpublic . 'jquery/1.12.4/jquery.min.js"></script>
<!--[if lt IE 9]>
                  <script src="' . $cdnpublic . 'html5shiv/3.7.3/html5shiv.min.js"></script>
                  <script src="' . $cdnpublic . 'respond.js/1.4.2/respond.min.js"></script>
              <![endif]-->
<div class="col-sm-12 col-md-8 col-lg-7 center-block" style="float: none;padding-top:55px;">
    ';
    }

    echo '<div class="panel panel-' . $panel . '">
        <div class="panel-heading">
            <h3 class="panel-title">提示信息</h3>
        </div>
        <div class="panel-body">';
    echo $content;

    if ($back && is_string($back)) {
        echo '
            <hr /><a href="' . $back . '">
                << 返回上一页</a>';
    } else {
        echo '<hr /><a href="javascript:history.back(-1)">
                        << 返回上一页</a>';
    }

    echo '
        </div>
    </div>';
    exit;
}

function getIpAddress($ip = '')
{

    global $clientip;
    $ip = $ip ?: $clientip;
    if (!$ip) {
        return 'IP地址为空';
    }

    $url  = 'http://whois.pconline.com.cn/ipJson.jsp?json=true&ip=' . $ip;
    $city = curl_get($url, 30);
    // $city = mb_convert_encoding($city, "UTF-8", "GB2312");
    $city     = json_decode($city, true);
    $location = null;

    if ($city) {
        if (isset($city['city']) && isset($city['pro'])) {
            $location = ip_city_str($city['pro']) . ip_city_str($city['city']);
        } elseif (isset($city['addr']) && $city['addr']) {
            $location = $city['addr'];
        } else {
            $location = ip_city_str($city['pro'] ?? '');
        }
    }

    if ($location) {
        // 数据库保存
        return $location;
    } else {
        return '未知';
    }
}

function check_china()
{
    return false;
    $ip = gethostbyname('check.88qf.net');
    if ($ip == '192.168.0.1') {
        return true;
    } else {
        return false;
    }
}

if (function_exists('getDiscount')) {
    function getDiscount($price, $discount)
    {
        global $conf;
        if ($conf['discount_open'] == 1) {
            if ($price > 0 && $discount > 0) {
                return sprintf('%.6f', $price * $discount);
            } elseif ($price > 0 && $conf['discount'] > 0) {
                return sprintf('%.6f', $price * $conf['discount']);
            }
        }
        return $price;
    }
}

function getRandUrl($min, $max)
{
    global $DB, $date, $conf;
    if ($conf['zz_fenzhan_siteurl'] != "") {
        $conf['zz_fenzhan_domain'] = $conf['zz_fenzhan_siteurl'];
    }

    if (empty($conf['zz_fenzhan_domain'])) {
        return '';
    }

    if (strstr($conf['zz_fenzhan_domain'], ',') !== false) {
        $hz   = explode(',', $conf['zz_fenzhan_domain']);
        $urls = [];
        foreach ($hz as $url) {
            $urls[] = $url;
        }
        $num   = count($urls);
        $domin = $urls[mt_rand(0, $num - 1)];
    } else {
        $domin = $conf['zz_fenzhan_domain'];
    }
    $url = getRandQz($min, $max, $domin);
    return $url;
}

function getRandQz($min, $max, $domin)
{
    global $DB, $date, $conf;
    $len    = mt_rand($min, $max);
    $string = "123456789abcdefghijklmopqrstuvwxyz";
    $maxlen = strlen($string) - 1;
    $hash   = '';
    for ($i = 0; $i < $len; $i++) {$hash .= substr($string, mt_rand(0, $maxlen), 1);}
    $url = $hash . '.' . $domin;if ($DB->
        get_row("SELECT * FROM cmy_site WHERE siteurl='" . $url . "' or siteurl='" . $url . "' limit 1") ||
        in_array($url, explode(',', $conf['fenzhan_remain']))) {
        return getRandQz($min, $max, $domin);
    }
    return $url;
}

function getDaifuSign($param, $key)
{
    $signPars = "";
    ksort($param);
    foreach ($param as $k => $v) {
        if ("sign" != $k && "" != $v) {
            $signPars .= $k . "=" . $v . "&";
        }
    }
    $signPars = trim($signPars, '&');
    $signPars .= $key;
    $sign = md5($signPars);
    return $sign;
}

function yile_getSign($param, $key)
{
    $signPars = "";
    ksort($param);
    reset($param);
    foreach ($param as $k => $v) {
        if ("sign" != $k && "" != $v) {
            $signPars .= $k . "=" . $v . "&";
        }
    }
    $signPars = trim($signPars, '&');
    $signPars .= $key;
    $sign = md5($signPars);
    return $sign;
}

function kashang_getSign($param, $key)
{
    $signPars = "";
    ksort($param);
    foreach ($param as $k => $v) {
        if ("sign" != $k && "" != $v) {
            $signPars .= $k . $v;
        }
    }
    $signPars = $key . $signPars;
    $sign     = md5($signPars);
    return $sign;
}

function getServerIp()
{
    $url  = 'http://members.3322.org/dyndns/getip';
    $url2 = 'https://www.bt.cn/Api/getIpAddress';
    if ($data = get_curl($url2)) {
        return $data;
    } else {
        $data = get_curl($url);
        return $data;
    }
}

function exitmsg($code, $msg)
{
    exit(json_encode((array("code" => $code, "msg" => $msg))));
}

function filtertext($str)
{
    $str = trim($str);
    $str = preg_replace("/\t/", "", $str);
    $str = preg_replace("/\r\n/", "", $str);
    $str = preg_replace("/\r/", "", $str);
    $str = preg_replace("/\n/", "", $str);
    $str = preg_replace("/ /", "", $str);
    $str = preg_replace("/ /", "", $str);
    return $str;
}

/**
 * 简单的验证内容是否指定常见数值类型
 * @param  string $value  验证内容
 * @param  string $method 验证类型 qq QQ|mobile 手机号|money 金额|email 邮箱|username 用户名|password 密码 | domain 域名(不含中文)| url 完整网址(不含中文)
 * @return [type]         [description]
 */
function validateData($value = '', string $method = 'money')
{
    $value = !$value || !is_string($value) ? '' : $value;
    switch ($method) {
        case 'email':
            return preg_match('/^[\w\-\.]+@[\w\-\.]+\.[\w]{2,6}$/', $value) == 1;
            break;
        case 'mobile':
            return preg_match('/^1[0-9]{10}$/', $value) == 1;
            break;
        case 'money':
            return preg_match('/^[0-9\.]+$/', $value) == 1;
            break;
        //用户名 支持邮箱用户名
        case 'username':
            return preg_match('/^[\w\-\.@]{5,20}$/', $value) == 1;
            break;
        //密码 支持常见特殊符号
        case 'password':
            return preg_match('/^[\w\-\.@!#%\$\^\*]{5,32}$/', $value) == 1;
            break;
        //域名(不含中文)
        case 'domain':
            return preg_match('/^[\w\-\.]+\.[\w\:]+$/', $value) == 1;
            break;
        //网址(不含中文)
        case 'url':
            return preg_match('/^(http|https):\/\/[\w\-\.]+\.[\w\:]+(.*?)$/', $value) == 1;
            break;
        //过滤高发shell
        default:
            $preg = '/exec|shell_exec|ssh2_shell|preg_|system|chr\(|call_user_|select\s|update\s/i';
            return preg_match($preg, $value) != 1;
            break;
    }
}

function paserEncodeStr($string, $len = 3)
{
    return substr($string, 0, $len) . '***' . substr($string, -abs($len));
}
