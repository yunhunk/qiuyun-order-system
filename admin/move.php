<?php

include '../includes/common.php';

$build = 1227;
$title = '数据迁移小工具V1.1.3（Build ' . $build . '）';
$query = '';
if (isset($_GET['debug']) && $_GET['debug'] == 'ok') {
    $query = '&debug=ok';
}

checkLogin();

checkAuthority('super');

$endlimit = 2000;

function getNeedTime($count, $ok, $limit, $lasttime = 0)
{

    $usetime = round(time() - $lasttime, 0);

    $time = (($count - $ok) / $limit) * (12.5 + $usetime);
    $time = ceil($time);
    if ($time > 60) {
        $s = $time % 60;
        $m = intval($time / 60);
        return "{$m}分{$s}秒";
    }
    return "{$time}秒";
}

function convertUTF8($str)
{
    if (empty($str)) {
        return '';
    }

    $bm = mb_detect_encoding($str, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'));
    if (in_array(strtoupper($bm), array("ASCII", "GB2312", "GBK", 'BIG5'))) {
        return iconv($bm, 'utf-8', $str);
    }
    return $str;
}

//准备初始化
function init($dbqz)
{
    global $DB, $conf, $CACHE;
    if (empty($conf['move_init']) || $conf['move_init'] == 0) {
        saveSetting('move_init', '1');
    }
    $CACHE->clear();
}

function filterData($tables = '')
{
    global $DB;
    if (strstr($tables, 'site')) {
        // $DB->query("UPDATE `{$tables}` SET price='',iprice='' where 1");
    }
    if (strstr($tables, 'tools')) {
        //$DB->query("UPDATE `{$tables}` SET `desc`='',`alert`='' where 1");
    }
}

//清空数据
function clear($offset, $tables)
{
    global $DB;
    if (file_exists(__DIR__ . '/move_sql.php')) {
        include_once __DIR__ . '/move_sql.php';
        if (!isset($sql_config)) {
            return '缺少依赖，[move_sql.php]文件异常！';
        }
        if ($offset == 0) {
            $arr = array_keys($sql_config);
            if (in_array($tables, $arr)) {
                $sql = $sql_config[$tables];
                $sql = str_replace(["\r", "\n", '""'], ['', '', "''"], $sql);
                return $DB->exec($sql) !== false ? '[' . $tables . ']表清空成功' : '[' . $tables . ']表清空失败，' . $DB->error();
            }
            return '[' . $tables . ']未检测到清空语句';
        }
        return '[' . $tables . ']无需清空';
    }
    return '缺少依赖，[move_sql.php]文件不存在！';
}

//检测是否具备迁移条件
function check($dbqz)
{
    global $DB;
    if (($count = $DB->count("select count(*) from `" . $dbqz . "_config`")) != false) {
        return $count;
    }
    return false;
}

//转移保持登录
function funcLogin()
{
    global $password_hash, $CACHE;
    $conf    = $CACHE->update();
    $session = md5($conf['adm_user'] . $conf['adm_pwd'] . $password_hash);
    $token   = authcode("{$conf['adm_user']}\t{$session}", 'ENCODE', SYS_KEY);
    setcookie("admin_token", $token, time() + 604800);
}

function keysMesh($sql, $table)
{
    if ($table == "pre_orders") {
        $sql = str_replace('`tradeno`', '`payorder`', $sql);
        $sql = str_replace('`paytype`', '`type`', $sql);
        $sql = str_replace('`input6`', '`bz`', $sql);
        $sql = str_replace('`cmoney`', '`cost`', $sql);
    }
    if ($table == "pre_site") {
        $sql = str_replace('domain', 'siteurl', $sql);
        $sql = str_replace('`rmb`', '`money`', $sql);
        $sql = str_replace('wait_msg', 'msgread', $sql);
        $sql = str_replace('bevynm', 'appurl', $sql);
        $sql = str_replace('phone', 'tel', $sql);
    }

    if ($table == "pre_tools") {
        $sql = str_replace('`msg`', '`desc`', $sql);
        $sql = str_replace('`cause`', '`close_alert`', $sql);
    }
    if ($table == "pre_pay") {
        $sql = str_replace('`domain', '`siteurl', $sql);
    }
    if ($table == "pre_taocan") {
        $sql = str_replace('`zzcost', '`price1', $sql);
    }
    if ($table == "pre_config") {
        $sql = str_replace('domain', 'siteurl', $sql);
    }
    return $sql;
}

function initLimit($tables)
{
    global $DB, $conf, $CACHE;
    if (stripos($tables, 'shua_') !== false) {
        $tables = str_replace('shua_', 'pre_', $tables);
    }

    if ($tables == "pre_site") {
        $endlimit = 600;
    } elseif ($tables == "pre_qiandao" || $tables == "pre_tixian" || $tables == "shua_price") {
        $endlimit = 15000;
    } elseif ($tables == "pre_pay" || $tables == "pre_points" || $tables == "pre_faka") {
        $endlimit = 10000;
    } elseif ($tables == "pre_orders") {
        $endlimit = 5000;
    } elseif ($tables == "pre_logs") {
        $endlimit = 3000;
    } elseif ($tables == "pre_tools") {
        $endlimit = 500;
    } else {
        $endlimit = 1000;
    }
    return $endlimit;
}

$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
if (!$_SESSION["move_index"]) {
    $_SESSION["move_index"] = 0;
}

$table_arr = array(
    'shua_config',
    'shua_class',
    'shua_cart',
    'shua_gift',
    'shua_giftlog',
    'shua_kms',
    'shua_faka',
    'shua_message',
    'shua_pay',
    'shua_points',
    'shua_price',
    'shua_shequ',
    'shua_site',
    'shua_logs',
    'shua_qiandao',
    'shua_tixian',
    'shua_tools',
    'shua_workorder',
    'shua_orders',
);

$tableName_arr = array(
    'shua_config'    => "主站配置",
    'shua_class'     => "分类列表",
    'shua_cart'      => "购物车记录",
    'shua_gift'      => "奖品列表",
    'shua_giftlog'   => "抽奖记录",
    'shua_kms'       => "卡密列表",
    'shua_faka'      => "库存列表",
    'shua_message'   => "文章列表",
    'shua_pay'       => "支付记录",
    'shua_points'    => "提成明细",
    'shua_price'     => "加价模板",
    'shua_shequ'     => "社区列表",
    'shua_site'      => "分站列表",
    'shua_logs'      => "对接日志",
    'shua_qiandao'   => "签到记录",
    'shua_tixian'    => "提现记录",
    'shua_tools'     => "商品列表",
    'shua_workorder' => "工单记录",
    'shua_orders'    => "订单列表",
);
if ($act == 'checkDb') {
    $dbqz = input("post.dbqz", 1);
    if (empty($dbqz)) {
        $result = ['code' => -1, 'msg' => '旧版数据库表前缀不能为空！'];
    } elseif ($dbqz == $dbconfig['dbqz']) {
        $result = ['code' => -1, 'msg' => '旧版数据库表前缀不能和当前系统一样'];
    } else {
        if ($DB->query("SELECT * FROM `{$dbqz}_config`")) {
            $result = ['code' => 0, 'msg' => '该数据库存在，可以转移~', 'data' => check($dbqz)];
        } else {
            $result = ['code' => -1, 'msg' => '该数据库不存在，请检查！'];
        }
    }
    exit(json_encode($result, JSON_UNESCAPED_UNICODE));
} elseif ($act == 'move') {
    if (function_exists("set_time_limit")) {
        @set_time_limit(0);
    }

    if (function_exists("ignore_user_abort")) {
        @ignore_user_abort(true);
    }

    $table   = $_POST['table'];
    $type    = intval($_POST['type']);
    $subtype = intval($_POST['subtype']);
    $dbqz    = input("post.dbqz", 1);
    $num     = input("post.num", 1);
    if (empty($dbqz)) {
        $dbqz = 'shua';
    }
    if ($dbqz == $dbconfig['dbqz']) {
        $result = array(
            'code' => -1,
            'msg'  => "旧版数据库表前缀不能和当前系统一样",
        );
        exit(json_encode($result));
    } elseif (check($dbqz) === false) {
        $result = array(
            'code' => -1,
            'msg'  => "未检测到彩虹/祥云系统的数据表，请检测是否使用的同一个数据库且未删除旧数据",
        );
        exit(json_encode($result));
    }

    //设置单次转移多少条
    if ($num == 0) {
        $endlimit = initLimit($table);
    } else {
        $endlimit = $num;
    }

    $limit = intval(isset($_POST['limit']) && is_numeric($_POST['limit']) ? $_POST['limit'] : '0');
    if ($subtype == 2) {
        if ($limit == 0 && $table == "all") {
            init($dbqz);
            $tableName              = $table_arr[0];
            $table                  = $tableName;
            $nextTable              = $table_arr[1];
            $_SESSION["move_index"] = 0;
        } else {
            $tableName = $table;
            $count     = $DB->count("SELECT count(*) from " . $tableName . " where 1");
            $tableName = $table;
            $nextTable = $table_arr[$_SESSION["move_index"]];
        }
    } else {
        $tableName = $table;
    }

    if ($table != 'all' && $_SESSION["move_index"] == count($table_arr) - 1) {
        $_SESSION["move_index"] = 0;
        $result                 = array(
            'code'  => 0,
            'msg'   => "所有数据转移工作执行完毕！（不保证已转移完整）",
            "info"  => "<br>执行完毕：所有数据转移工作执行完毕！（不保证已转移完整）",
            'table' => '',
        );
    } else {
        $succ = 0;
        $warn = 0;
        $num  = 0;
        $err  = '';

        $startTime = time();

        $DB->setDbqzList();

        filterData($tableName);

        $tables = str_replace($dbqz . '_', 'pre_', $tableName);

        $clearMsg = clear(($subtype != 2 && $limit <= 0 ? 0 : $limit), str_replace('pre_', '', $tables));

        addWebLog('数据转移调试0', '数据表重置：' . $clearMsg . ";", 'Move');

        $count = $DB->count("SELECT count(*) from " . $tableName . "");

        $queryTableSql = "SELECT count(*) from " . $tableName . "";
        // var_dump($count, $queryTableSql);
        // die;
        if ($subtype == 1) {
            if (isset($_COOKIE[$tables . '_LIMIT'])) {
                $limit = $_COOKIE[$tables . '_LIMIT'];
            }
        }

        if ($count < $endlimit) {
            $_SESSION["move_index"] = $_SESSION["move_index"] + 1;
            $limit                  = 0;
            $_sql                   = "SELECT * from `" . $tableName . "`";
            try {
                $rs   = $DB->select($_sql);
                $rows = [];
                if ($rs) {
                    $rows = $rs;
                }
            } catch (\PDOException $e) {
                $result = array(
                    'code'    => -1,
                    'msg'     => "转移" . $tableName_arr[$tableName] . "的数据时查询出现错误，" . $e->getMessage(),
                    "info"    => "<br>转移" . $tableName_arr[$tableName] . "的数据时查询出现错误，" . $e->getMessage(),
                    'table'   => $subtype == 2 ? $nextTable : $table,
                    'subtype' => $subtype ? $subtype : $_POST['subtype'],
                );
                exit(json_encode($result));
            }
        } else {
            if (intval($_POST['limit']) > 0) {
                $limit = intval($_POST['limit']);
            } else {
                $limit = 0;
            }

            $_sql = "SELECT * from `" . $tableName . "` limit " . $limit . "," . $endlimit;
            $rs   = $DB->select($_sql);
            $rows = [];
            if ($rs != false) {
                $rows = $rs;
            } else {
                $result = array(
                    'code'    => -1,
                    'msg'     => "转移" . $tableName_arr[$tableName] . "的数据时查询出现错误，" . $DB->getError(),
                    "info"    => "<br>转移" . $tableName_arr[$tableName] . "的数据时查询出现错误，" . $DB->getError(),
                    'table'   => $subtype == 2 ? $nextTable : $table,
                    'subtype' => $subtype ? $subtype : $_POST['subtype'],
                );
                exit(json_encode($result));
            }
        }

        $sql       = '';
        $sql_debug = '';
        $keys      = [];

        $result         = [];
        $result['code'] = 0;
        $result['msg']  = 'succ';

        addWebLog('数据转移调试1', 'SQL[' . $_sql . "];数量[" . count($rows) . "];总行[" . $count . "];数据表[" . $tables . "]", 'Move');

        try {
            if ($tables == 'pre_config') {
                foreach ($rows as $key => $res) {
                    if ($res['k'] == "version") {
                    } elseif ($res['k'] == "admin_user") {
                    } elseif ($res['k'] == "admin_pwd") {
                    } elseif ($res['k'] == "syskey") {
                    } elseif ($res['k'] == "cdnpublic_url") {
                    } elseif ($res['k'] == "domain") {
                        $num++;
                        $sql = "REPLACE INTO " . $tables . " SET v='" . $res['v'] . "',k='siteurl'";
                        if ($DB->query($sql)) {
                            $succ++;
                        } else {
                            $warn++;
                        }
                    } else {

                        $num++;
                        $sql = "REPLACE INTO " . $tables . " SET v='" . addslashes($res['v']) . "',k='" . $res['k'] . "'";
                        if ($DB->query($sql)) {
                            $succ++;
                        } else {
                            $warn++;
                        }
                    }
                }

            } else {

                if (false != $rs && $count > 0 && $_sql != "") {
                    $columns = array_keys($rows[0]);
                    foreach ($columns as $value) {
                        if (!checkColumnExists($tables, $value) && stripos($tables, '_config') === false) {
                            //字段不存在兼容处理
                            $DB->query("ALTER TABLE `" . $tables . "` ADD `" . $value . "` text DEFAULT NULL");
                        }
                    }

                    addWebLog('数据转移调试2', 'columns' . json_encode($columns), 'Move');

                    $sql_q = "INSERT INTO " . $tables;
                    if ($tables == 'pre_tools') {
                        $sql_q .= " (`" . implode("`,`", $columns) . "`,`price1`,`sale`) ";
                    } elseif ($tables == 'pre_class') {
                        $sql_q .= " (`" . implode("`,`", $columns) . "`,`upcid`) ";
                    } elseif ($tables == 'pre_orders') {
                        $sql_q .= " (`" . implode("`,`", $columns) . "`,`type`) ";
                    } else {
                        $sql_q .= " (`" . implode("`,`", $columns) . "`) ";
                    }

                    $sql_q = keysMesh($sql_q, $tables);

                    $sql_q .= " VALUES";

                    addWebLog('数据转移调试2.1', 'sql_q: ' . $sql_q, 'Move');

                    $vals = "";
                    $DB->query("SET global max_allowed_packet = 104857600");
                    $DB->query("BEGIN");
                    if ($tables == 'pre_site' || $tables == 'pre_logs') {
                        $keys = [];
                        if (count($rows) > 0) {
                            $keys = array_keys($rows[0]);
                        }

                        foreach ($rows as $i => $res) {
                            $num++;
                            $sql_h = "";
                            $val   = "";
                            foreach ($keys as $key => $value) {
                                //Null数据处理
                                if ($res[$value] == null || $res[$value] == 'null') {
                                    $val .= "'',";
                                } elseif ($tables == 'pre_site' && ($value == 'price' || $value == 'iprice')) {
                                    $val .= "'',";
                                } elseif ($tables == 'pre_site' && ($value == 'tel')) {
                                    //处理余额
                                    $val .= "'" . floatval($res['phone']) . "',";
                                } elseif ($tables == 'pre_site' && ($value == 'money' || $value == 'rmb')) {
                                    //处理余额
                                    $val .= "'" . floatval($res['rmb']) . "',";
                                } elseif ($tables == 'pre_site' && $value == 'siteurl') {
                                    //处理分站域名
                                    $val .= "'" . addslashes($res['domain']) . "',";
                                } elseif ($tables == 'pre_site' && $value == 'siteurl2') {
                                    //处理分站域名二
                                    $val .= "'" . addslashes($res['domain2']) . "',";
                                } elseif ($tables == 'pre_tools' && $value == 'condition') {
                                    //处理分站域名二
                                    $val .= "'" . addslashes($res['audit_status']) . "',";
                                } elseif ($tables == 'pre_master' && ($value == 'income' || $value == 'rmb')) {
                                    //处理供货商余额
                                    $val .= "'" . addslashes($res['rmb']) . "',";
                                } else {
                                    if ($tables == 'pre_site' && in_array(trim($value), ['anounce', 'bottom', 'modal', 'alert'])) {
                                        $val .= "'',";
                                    } else {
                                        $s = str_replace(["\r", "\n", "\t", "\'"], ['', '', '', ''], $res[$value]);
                                        $val .= "'" . addcslashes(guolv(convertUTF8($s)), "'") . "',";
                                    }
                                }
                            }

                            $sql_h = rtrim($val, ",");
                            $sql   = $sql_q . "(" . $sql_h . ")";

                            if (false !== $DB->exec($sql)) {
                                //提交
                                $succ = $succ + 1;
                            } else {
                                //回滚
                                if ($i == 0) {
                                    $sql_debug = $sql_q . "(" . $sql_h . "),";
                                } elseif ($i <= 5) {
                                    $sql_debug .= "(" . rtrim($val, ",") . "),";
                                }
                                $err .= '<br>错误：第' . $key . '行[' . $sql . ']，信息：' . $DB->getError();
                                $warn = $num + 1;
                            }
                        }

                        if ($succ == count($rows)) {
                            $DB->query("COMMIT");
                        } else {
                            $succ == 0;
                        }
                    } else {
                        $sql_vals = "";
                        $keys     = [];
                        if (count($rows) > 0) {
                            $keys = array_keys($rows[0]);
                        }

                        foreach ($rows as $i => $res) {
                            $num++;
                            $val = "";
                            if ($tables == 'pre_tools' && isset($res['price'])) {
                                if ($res['price'] >= 1) {
                                    $res['price1'] = $res['price'];
                                    $res['price']  = $res['price1'] + $res['price1'] * 30 / 100;
                                    $res['cost']   = $res['price1'] + $res['price1'] * 25 / 100;
                                    $res['cost2']  = $res['price1'] + $res['price1'] * 18 / 100;
                                } else {
                                    $res['price1'] = $res['price'];
                                    $res['price']  = $res['price1'] + 0.20;
                                    $res['cost']   = $res['price1'] + 0.15;
                                    $res['cost2']  = $res['price1'] + 0.12;
                                }
                            }

                            if ($tables == 'pre_price') {
                                if ($res['kind'] == 1) {
                                    $val = "'" . $res['id'] . "','" . $res['zid'] . "','1','" . addslashes($res['name']) . "','" . $res['p_0'] . "','" . $res['p_1'] . "','" . $res['p_2'] . "'";
                                } else {
                                    $res['p_0'] = $res['p_0'] > 1 ? ($res['p_0'] - 1) * 100 : '15';
                                    $res['p_1'] = $res['p_1'] > 1 ? ($res['p_1'] - 1) * 100 : '12';
                                    $res['p_2'] = $res['p_0'] > 1 ? ($res['p_2'] - 1) * 100 : '9';
                                    $val        = "'" . $res['id'] . "','" . $res['zid'] . "','2','" . addslashes($res['name']) . "','" . $res['p_0'] . "','" . $res['p_1'] . "','" . $res['p_2'] . "'";
                                }
                            } else {
                                foreach ($keys as $key => $value) {
                                    if ($tables == 'pre_workorder' && $value == "name") {
                                        $value = "content";
                                    }

                                    //Null数据处理
                                    if ($res[$value] == null || $res[$value] == 'null') {
                                        $val .= "'',";
                                    } elseif ($tables == 'pre_tools' && ($value == 'desc' || $value == 'alert')) {
                                        $s = str_replace(["\r", "\n", "\t", "\'", "\\"], ['', '', '', "'", ''], $res[$value]);
                                        $val .= "'" . addcslashes(guolv($s), "'") . "',";
                                    } elseif ($tables == 'pre_tools' && $value == 'sales') {
                                        $val .= "'0',";
                                    } else {
                                        $d = str_replace(["\'", "\\"], ["'", ''], $res[$value]);
                                        $d = addcslashes(guolv(convertUTF8($d)), "'");
                                        $val .= "'" . $d . "',";
                                    }
                                }
                                if ($tables == 'pre_class') {
                                    $val .= "'0',";
                                } elseif ($tables == 'pre_orders') {
                                    $val .= "'move',";
                                } elseif ($tables == 'pre_tools') {
                                    //兼容商品数据成本价
                                    if (isset($res['price1']) && $res['price1'] > 0) {
                                    } else {
                                        $res['price1'] = $res['price'];
                                    }
                                    $val .= "'" . floatval($res['price1']) . "',";
                                    $val .= "'" . intval($res['sales']) . "',";
                                }
                            }
                            $sql_vals .= "(" . rtrim($val, ",") . "),";
                            if ($i <= 30) {
                                $sql_debug .= "(" . rtrim($val, ",") . "),";
                            }
                        }
                        $sql_debug = $sql_q . rtrim($sql_debug, ',');
                        $sql       = $sql_q . rtrim($sql_vals, ',');
                        addWebLog('数据转移调试3', 'SQL[' . $sql_debug . "];", 'Move');
                        if ($DB->query($sql) !== false) {
                            //提交
                            $succ = $endlimit;
                        } else {
                            //回滚
                            //$err .= '<br>数据库错误，' . $DB->error() . '；[Sql:' . $sql . ']，：';
                            if (mb_strlen($sql) > 500) {
                                $sql = mb_substr($sql, 0, 500);
                            }
                            $err .= '<br>数据库错误，' . $DB->error();
                            $warn = $num + 1;
                        }
                    }

                    if ($succ > 0) {
                        //提交成功了的数据
                        $DB->query("COMMIT");
                    }
                } elseif ($_sql == "") {
                    $result = array(
                        'code' => -1,
                        'msg'  => '转移时遇到错误，请截图该信息并反馈到chenmxgg@163.com',
                    );
                    exit(json_encode($result));
                } else {
                    $err .= '[未查询到任何数据]';
                }
            }
        } catch (\Throwable $th) {
            $err            = '转移出现致命错误，' . $th->getMessage();
            $result['code'] = -1;
        }

        if ($count > 0) {
            if ($succ > 0 && $result['code'] == 0) {
                $ok = $limit + $succ;
                if ($count - ($limit + $endlimit) > 0) {
                    $offset = $limit + $endlimit;
                    setcookie($tables . '_LIMIT', $offset, time() + 7200);
                    $result = array(
                        'code'          => 0,
                        'msg'           => $tableName_arr[$tableName] . "的数据共" . $count . "条，已转移" . $ok . "条，本次成功转移" . $succ . "条数据！当前模式：" . ($subtype == 2 ? '转移所有数据' : '转移单独数据'),
                        "info"          => "<br>转移" . $tableName_arr[$tableName] . "：需转移" . $count . "条数据，已成功转移" . $ok . "条，本次成功转移" . $succ . "条数据，失败" . $warn . "条！当前表的数据转移完预计还需要" . getNeedTime($count, $ok, $endlimit, $startTime),
                        'table'         => $table,
                        'limit'         => $offset,
                        'count'         => $count,
                        'endlimit'      => $endlimit,
                        'err'           => $err,
                        'sql'           => $sql,
                        'subtype'       => $subtype ? $subtype : $_POST['subtype'],
                        'timeout'       => 10000,
                        'clearMsg'      => $clearMsg,
                        'sql'           => $sql_debug,
                        'rows'          => $endlimit <= 50 ? $rows : [$rows[0], $rows[1], $rows[2], $rows[3]],
                        'queryTableSql' => $queryTableSql,
                    );
                } else {
                    if ($subtype == 2) {
                        $_SESSION["move_index"] = $_SESSION["move_index"] + 1; //表索引+1
                        $result                 = array(
                            'code'          => 0,
                            'msg'           => $tableName_arr[$tableName] . "的数据共" . $count . "条，已转移" . $ok . "条，本次成功转移" . $succ . "条数据！当前模式：" . ($subtype == 2 ? '转移所有数据' : '转移单独数据'),
                            "info"          => "<br>转移" . $tableName_arr[$tableName] . "：需转移" . $count . "条数据，已成功转移" . $ok . "条，本次成功转移" . $succ . "条数据，失败" . $warn . "条！当前表的数据转移完预计还需要" . getNeedTime($count, $ok, $endlimit, $startTime),
                            'table'         => $nextTable,
                            'limit'         => 0,
                            'count'         => $count,
                            'err'           => $err,
                            'subtype'       => 2,
                            'clearMsg'      => $clearMsg,
                            'sql'           => $sql_debug,
                            'rows'          => $endlimit <= 50 ? $rows : [$rows[0], $rows[1], $rows[2], $rows[3]],
                            'queryTableSql' => $queryTableSql,
                        );
                    } else {
                        $_SESSION["move_index"] = 0; //表索引重置
                        $result                 = array(
                            'code'     => 0,
                            'clearMsg' => $clearMsg,
                            'msg'      => $tableName_arr[$tableName] . "的数据共" . $count . "条全部已转移完毕！",
                            'sql'      => $_GET['debug'] == 'ok' ? $sql : '',
                        );
                    }
                }
            } else {
                $ok = $limit - $endlimit + $succ;
                //出现错误
                $result = array(
                    'code'          => -1,
                    'msg'           => $tableName_arr[$tableName] . "的数据转移失败，共" . $count . "条！" . $err,
                    "info"          => "<br>转移" . $tableName_arr[$tableName] . "：转移出现错误，共" . $count . "条！错误信息：" . $err,
                    'table'         => $subtype == 2 ? $nextTable : $table,
                    'count'         => $count,
                    'err'           => $err,
                    'sql'           => $sql,
                    'endlimit'      => $endlimit,
                    'subtype'       => $subtype ? $subtype : $_POST['subtype'],
                    'clearMsg'      => $clearMsg,
                    'sql'           => $sql_debug,
                    'keys'          => $keys,
                    'rows'          => $endlimit <= 50 ? $rows : [$rows[0], $rows[1], $rows[2], $rows[3]],
                    'queryTableSql' => $queryTableSql,
                );
            }
        } else {
            $_SESSION["move_index"] = $_SESSION["move_index"] + 1;
            $result                 = array(
                'code'          => 0,
                'msg'           => $tableName_arr[$tableName] . "的数据转移完毕！",
                "info"          => "<br>转移" . $tableName_arr[$tableName] . "：本次需转移0条数据，无需转移！",
                'table'         => $subtype == 2 ? $nextTable : '',
                'subtype'       => $subtype ? $subtype : $_POST['subtype'],
                'queryTableSql' => $queryTableSql,
            );
        }
    }
    exit(json_encode($result));
} elseif ($act == "jump") {
    $tableName = $table != "" ? $table : $table_arr[$_SESSION["move_index"]];
    $result    = array(
        'code' => 0,
        'msg'  => "跳过当前表（" . $tableName_arr[$tableName] . "）成功",
    );
    $_SESSION["move_index"] = $_SESSION["move_index"] + 1;
    exit(json_encode($result));
}

include './head.php';

echo '<div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top: 10px">
      <div class="block">
        <div class="block-title"><h3 class="panel-title">' . $title . '</h3></div>
        <div class="">
        <div class="alert alert-info">
            <h4 class="text-center" style="color:red">如果出现没反应或乱跳转，请换浏览器试试</h4>
            1.使用此功能可将其他系统的数据一键迁移到本系统中<br>
            2.请注意,如果数据过多,时间将会将较长（每个数据将分开转移，建议单独转移必要的数据，例如：分类、商品、分站、**数据）<br>
            3.如果某次转移失败或需要单独转移，请刷新本页面重新选择要转移的数据提交转移操作<br>
            4.如果出现转移失败且严重影响使用的问题，请联系上级销售商帮你转移<br>
            5.经过客户反馈，当数据较多例如分站数量达到1万及以上时，请单独选择分站列表转移
            <p style="color:red">6.转移完成后，请进去货源对接列表将对接站的密码按照说明重新提交保存一遍才能正常对接哦</p>
            <p style="color:red">注意一：迁移前请保证新安装的数据库和之前系统的数据库是同一个</p>
            <p style="color:red">注意二：如果数据库不一致，请重新安装后再操作</p>
        </div>
        <form action="" method="POST" role="form">
            <div class="form-group" id="orderstatus">
                <label>要转移的数据:</label><br>
                <select class="form-control" name="table" default="all">
                    <option value="all">所有数据（从未转移过再选择）</option>';
foreach ($tableName_arr as $key => $value) {
    echo '<option value="' . $key . '" tableName="' . $value . '">' . $value . '</option>';
}

$options = '<option value="2">增量转移（仅转移新的数据）</option><option value="3">祥云系统</option>';
echo '</select>
            </div>
            <div class="form-group">
                <label>旧版数据表前缀:</label><br>
                <div class="input-group">
                    <input name="dbqz" type="text" class="form-control" value="shua"/>
                    <a class="input-group-addon" id="checkDatabase">检测数据库</a>
                </div>
                <small>旧版指之前用的系统（如彩虹），填写旧版系统数据库表前缀（在网站程序目录config.php里面），祥云和彩虹的默认是shua，如果不是请修改</small>
            </div>
            <div class="form-group">
                <label>当前数据表前缀:</label><br>
                <input name="" type="text" class="form-control" value="' . $dbconfig['dbqz'] . '" disabled/>
                <small>星河系统当前的数据表前缀，不需要填写</small>
            </div>
            <div class="form-group hide">
                <label>请选择转移模式:</label><br>
                <select class="form-control" name="type" default="2">
                    <option value="1" selected>全部转移</option>
                </select>
            </div>
            <div class="form-group">
                <label>请选择系统类型:</label><br>
                <select class="form-control" name="xtpt">
                    <option value="1" selected>彩虹/祥云自助下单系统</option>
                </select>
            </div>
            <div class="form-group">
                <label>请选择转移数量:</label><br>
                <select class="form-control" name="num">
                    <option value="0" selected>系统默认</option>
                    <option value="5">5条</option>
                    <option value="10">10条</option>
                    <option value="20">20条</option>
                    <option value="30">30条</option>
                    <option value="40">40条</option>
                    <option value="50">50条</option>
                    <option value="60">60条</option>
                    <option value="70">70条</option>
                    <option value="80">80条</option>
                    <option value="90">90条</option>
                    <option value="100">100条</option>
                    <option value="120">120条</option>
                    <option value="150">150条</option>
                    <option value="180">180条</option>
                    <option value="200">200条</option>
                    <option value="300">300条</option>
                    <option value="500">500条</option>
                    <option value="1000">1000条</option>
                    <option value="2000">2000条</option>
                    <option value="3000">3000条</option>
                    <option value="4000">4000条</option>
                    <option value="5000">5000条</option>
                    <option value="8000">8000条</option>
                    <option value="10000">10000条</option>
                    <option value="15000">15000条</option>
                    <option value="20000">20000条</option>
                    <option value="30000">30000条</option>
                    <option value="40000">40000条</option>
                    <option value="50000">50000条</option>
                    <option value="70000">70000条</option>
                    <option value="100000">10万条</option>
                    <option value="120000">12万条</option>
                    <option value="150000">15万条</option>
                    <option value="175000">17.5万条</option>
                    <option value="200000">20万条</option>
                </select>
                <small>如果系统默认的转移不完整或者失败，超时等情况可以选择其他数量，当多次失败时推荐选择较小的</small>
            </div>
            <p><button onclick="move({})" id="moveBtn" class="btn btn-primary form-control" >立即转移</button></p>
        </form>
        <div id="move-info" class="alert" style="background-color:  #F8F8FF;color: #0c69c6;">
            此处显示转移详情信息
        </div>
        </div>
      </div>
    </div>
  </div>';

echo '<script>
var quxiao,changeEvent=false;
$("select[name=\'xtpt\']").change(function(){
    if($(this).val() == 1){
        window.location.href="./move.php";
    }
    else if($(this).val() == 3){
        window.location.href="./move3.php";
    }
});
$(document).on("click", "#checkDatabase", function(event) {
    event.preventDefault();
    /* Act on the event */
    var dbqz = $("input[name=\"dbqz\"]").val();
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "?act=checkDb",
        dataType: "json",
        data: {
            dbqz: dbqz,
        },
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg(data.msg);
            } else {
                layer.alert(data.msg);
            }
        },
        error: function() {
            layer.close(ii);
            layer.alert("检测超时，可能服务器错误请稍后刷新页面再试！");
            return false;
        }
    });
});
$("select[name=\'xtpt\']").change(function(){
    if($(this).val() == 1){
        window.location.href="./move.php";
    }
    else if($(this).val() == 3){
        window.location.href="./move3.php";
    }
});
function move(data) {
    if (!data.table) {
        data.table = $("select[name=\'table\'] option:selected").val();
        data.type = $("select[name=\'type\'] option:selected").val();
        data.num = $("select[name=\'num\'] option:selected").val();
        data.dbqz = $("input[name=\'dbqz\'] option:selected").val();
        if (data.table=="all") {
           $("#move-info").html(\'开始转移所有数据..\');
           data.subtype = \'2\';
           data.table   = \'all\';
        }
        else{
           tableName = $("select[name=\'table\'] option:selected").attr(\'tableName\');
           $("#move-info").html(\'开始转移\'+tableName+\'..\');
           data.subtype =\'1\';
        }
    }

    $("#moveBtn").attr("disabled",true);

    if (!data.limit) {data.limit=0;}

    data.dbqz = $("input[name=\'dbqz\']").val();
    data.num = $("select[name=\'num\'] option:selected").val();

    $("#moveBtn").html("转移中~请不要再点！否则后果自负");

    var ii = layer.load(2, {shade:[0.1,\'#fff\']});
    $.ajax({
        type : \'POST\',
        url : \'?act=move' . $query . '\',
        dataType : \'json\',
        data : {
            table:data.table,
            type:data.type,
            limit:data.limit,
            subtype:data.subtype,
            dbqz:data.dbqz,
            num:data.num
        },
        success : function(data) {
            layer.close(ii);
            if(data.code == 0){
                layer.msg(data.msg);
                if(data.info){
                    $("#move-info").append(data.info);
                }
                else{
                     $("#move-info").append("<br>"+data.msg);
                }
                if(data.table && data.table!=""){
                    moveStatus=true;
                    setTimeout(function(){
                        move(data);
                    }, data.timeout?data.timeout:10000);
                }
                else{
                    $("#moveBtn").html("立即转移");
                    $("#moveBtn").attr("disabled",false);
                }
            }else{
                $("#moveBtn").attr("disabled",false);
                layer.alert(data.msg,{
                    btn:[\'关闭提示\',\'跳过此表\'],
                    btn2:function (){
                        $.ajax({
                            type : \'POST\',
                            url : \'?act=jump\',
                            dataType : \'json\',
                            data : {table:data.table,type:data.type,limit:data.limit},
                            success : function(data) {
                                if(data.code == 0){
                                    layer.msg(data.msg);
                                }else{
                                    layer.alert(data.msg);
                                }
                            },
                            error:function(data){
                                layer.msg(\'服务器错误\');
                                return false;
                            }
                        });

                    }
                });
            }
        },
        error:function(){
            layer.close(ii);
            $("#moveBtn").html("立即转移");
            $("#moveBtn").attr("disabled",false);
            var t=layer.alert("服务器出现超时错误，将在10秒后自动重试！",{
                btn:[\'立即重试\',\'取消重试\'],
                yes:function (){
                    move(data);
                },
                btn2:function (){
                    quxiao=true;
                }
            });
            setTimeout(function(){
                if(quxiao===true) return true;
                layer.close(t);
                move(data);
            }, 10000);
            return false;
        }
    });
}
';
echo "var build='{$build}'";
echo '
</script>';

include 'footer.php';
