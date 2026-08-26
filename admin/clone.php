<?php
include '../includes/common.php';
$title = '克隆站点工具V1.2.7';
checkLogin();

checkAuthority('super');

function getSuData($item, $cid, $site_type)
{
    if ($site_type == 2) {
        return array();
    }

    $data = array();
    $n    = count($item);
    for ($i = 0; $i < $n; $i++) {
        $v = $item[$i];
        if ($v['upcid'] && $v['upcid'] == $cid) {
            $data[] = $v;
        }
    }
    return $data;
}

function saveTempData($name = null, $value = '')
{

    $dir = ROOT . 'cache/';
    if (!file_exists($dir)) {
        @mkdir($dir);
    }
    $dir = $dir . 'temp/';
    if (!file_exists($dir)) {
        @mkdir($dir);
    }

    if (!$name) {
        $name = 'cacheFile';
    }

    if (is_array($value)) {
        $value = @serialize($value);
    }
    return file_put_contents($dir . $name . '.temp', $value);
}

function getTempData($name = null)
{
    $dir = ROOT . 'cache/temp/';
    if (!$name) {
        $name = 'cacheFile';
    }
    return file_get_contents($dir . $name . '.temp');
}

/**
 * 备份数据库到本地
 */
function backupDb()
{
    global $DB, $date;
    $backupDir = rtrim(__DIR__, '/') . '/backup';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755) or showmsg('创建目录[' . $backupDir . ']失败', 3);
    }
    $backupDir = $backupDir . '/database';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755) or showmsg('创建目录[' . $backupDir . ']失败', 3);
    }
    $backupFile = 'backup-' . date('Y-m-d H:i:s') . '.sql';
    $backupPath = $backupDir . '/' . $backupFile;

    $sqls   = '';
    $tables = array(
        'cmy_tools',
        'cmy_price',
        'cmy_shequ',
        'cmy_class',
    );
    foreach ($tables as $key => $table) {
        $sql       = '';
        $sql_data  = '';
        $sql_table = getDbTableSql($table);
        $count     = getDbTableDataNum($table);
        //如果表存在
        if ($sql_table && $count > 0) {
            //生成数据写入sql
            $rs = $DB->select("SELECT * from `{$table}`");
            if (is_array($rs)) {
                $keys = [];
                if (isset($rs[0])) {
                    $keys = array_keys($rs[0]);
                }
                foreach ($rs as $key => $row) {
                    $sql_data .= getDbTableDataSql($table, $row, $keys) . "\r\n";
                }
            }
        }
        $sql = $sql_table . "\r\n" . $sql_data;
        $sqls .= $sql . "\r\n\r\n";
    }
    return file_put_contents($backupPath, $sqls);
}

/**
 * 处理特殊字符
 */
function replaceCharacter($s)
{
    $s = str_replace('/**', '/ **', $s);
    $s = str_replace(array("\r", "\n"), "", $s);
    $s = str_replace('\"', '"', $s);
    return $s;
}
/**
 * 生成某数据行的写入语句
 */
function getDbTableDataSql($tablename, $row, $keys)
{
    $sql = "INSERT INTO `{$tablename}` (";
    $sql = $sql . "`" . implode('`,`', $keys) . "`) VALUES (";
    foreach ($keys as $key => $value) {
        if (isset($row[$value]) && $row[$value]) {
            $sql .= "'" . replaceCharacter(daddslashes($row[$value])) . "',";
        } else {
            $sql .= "'',";
        }
    }
    $sql = trim($sql, ',');
    $sql .= ");";
    return $sql;
}

/**
 * 获取当前表数据总行数
 */
function getDbTableDataNum($tablename)
{
    global $DB;
    return intval($DB->count("SELECT count(*) from `{$tablename}`"));
}

/**
 * 生成数据表sql语句
 */
function getDbTableSql($tablename)
{
    global $DB;

    $sql = '';
    $rs  = $DB->select("show columns from `{$tablename}`");
    if (is_array($rs)) {
        $sql = DbTableDoSql($tablename, $rs);
    }
    return $sql;
}

/**
 * 解析数据表并生成sql语句
 */
function DbTableDoSql($tablename, $data)
{
    $sql_key = '';
    $sql     = 'DROP TABLE IF EXISTS `' . $tablename . '`;';
    $sql .= "\r\n";
    $sql .= 'CREATE TABLE `' . $tablename . '` (';
    $sql .= "\r\n\t";
    foreach ($data as $key => $row) {
        $sql .= '`' . $row['Field'] . '` ' . $row['Type'];

        if (strtoupper($row['Null']) == 'YES') {
            $sql .= ' NULL';
        } else {
            $sql .= ' NOT NULL';
        }

        if (isset($row['Default']) && $row['Default']) {
            $sql .= ' DEFAULT \'' . $row['Default'] . '\'';
        }

        if (isset($row['Extra']) && $row['Extra']) {
            $sql .= ' ' . strtoupper($row['Extra']);
        }

        if (isset($row['Key']) && $row['Key'] == "PRI") {
            $sql_key = ' PRIMARY KEY (`' . $row['Field'] . '`)';
        }
        $sql .= ",\r\n\t";
    }
    $sql .= $sql_key;
    $sql = trim($sql, ',');
    $sql .= "\r\n";
    $sql .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci;';
    return $sql;
}

function getDefaultPrid()
{
    //默认加价模板
    global $DB, $date;
    $data = [
        ':name' => '系统默认模板',
    ];
    $sql = "SELECT * FROM `pre_price` WHERE `name`=:name limit 1";
    $row = $DB->get_row($sql, $data);
    if (is_array($row)) {
        return $row['id'];
    } else {
        $sql = "INSERT INTO `pre_price` (`zid`,`kind`,`name`,`p_0`,`p_1`,`p_2`,`addtime`) values ('1','2','系统默认模板','30','25','20','" . $date . "')";
        $id  = $DB->insert($sql);
        if ($id) {
            //$DB->query("commit");
            return $id;
        }
        return 0;
    }
}

function getShequ($item, $shequ)
{
    global $DB, $date;

    foreach ($item as $key => $v) {
        if ($v['id'] == $shequ) {

            preg_match('/([\w\-\.]+)\.([\w\:\/]+)/', $v['url'], $match);

            $v['url'] = 'http://' . ltrim($match[0], '/') . '/';
            $row      = $DB->get_row("SELECT * FROM cmy_shequ WHERE `url`='" . trim($v['url']) . "' limit 1");
            if ($row) {
                return $row['id'];
            } else {
                $sql     = "INSERT INTO `pre_shequ` (`url`,`type`,`status`) values ('" . $v['url'] . "','" . $v['type'] . "','1')";
                $shequid = $DB->insert($sql);
                if ($shequid) {
                    return $shequid;
                }
                return -1;
            }
        }
    }
    return 0;
}

function setToolPrice($tid, $prid, $price1)
{
    global $DB, $date;
    $row = $DB->get_row("SELECT * FROM `pre_price` WHERE `id`=:id limit 1", [':id' => $prid]);
    if (is_array($row)) {
        $sql  = "UPDATE `pre_tools` set `prid`=:prid,`price`=:price,`cost`=:cost,`cost2`=:cost2 where `tid`=:tid";
        $data = [
            ':prid'  => $prid,
            ':price' => sprintf('%.2f', ($row['kind'] == 1 ? $row['p_0'] + $price1 : $row['p_0'] * $price1 / 100 + $price1)),
            ':cost'  => sprintf('%.2f', ($row['kind'] == 1 ? $row['p_1'] + $price1 : $row['p_1'] * $price1 / 100 + $price1)),
            ':cost2' => sprintf('%.2f', ($row['kind'] == 1 ? $row['p_2'] + $price1 : $row['p_2'] * $price1 / 100 + $price1)),
        ];
        $DB->query($sql, $data);
    } else {
        $DB->query("UPDATE `pre_tools` set `prid`='" . $prid . "' where tid='" . $tid . "'");
    }
}

function getPrid($item, $prid, $site_type)
{
    global $DB, $date;
    $n = count($item);
    for ($i = 0; $i < $n; $i++) {
        $v = $item[$i];
        if ($v['id'] == $prid) {
            $data = [];
            $row  = $DB->get_row("SELECT * FROM `pre_price` WHERE `name`=:name limit 1", [':name' => $v['name']]);
            if ($row && isset($row['id'])) {
                return $row['id'];
            } else {
                $name = $v['name'];
                if ($site_type == 2) {
                    //彩虹
                    if ($v['kind'] == 1) {
                        $kind = '1';
                        $p_0  = sprintf('%.2f', $v['p_0']);
                        $p_1  = sprintf('%.2f', $v['p_1']);
                        $p_2  = sprintf('%.2f', $v['p_2']);
                    } else {
                        $kind = '2';
                        $p_0  = sprintf('%.0f', $v['p_0'] > 1 ? ($v['p_0'] - 1) * 100 : '10');
                        $p_1  = sprintf('%.0f', $v['p_1'] > 1 ? ($v['p_1'] - 1) * 100 : '15');
                        $p_2  = sprintf('%.0f', $v['p_2'] > 1 ? ($v['p_2'] - 1) * 100 : '20');
                    }
                } else {
                    $kind = addslashes($v['kind']);
                    $p_0  = addslashes($v['p_0']);
                    $p_1  = addslashes($v['p_1']);
                    $p_2  = addslashes($v['p_2']);
                }
                $sql = "INSERT INTO `pre_price` (`zid`,`kind`,`name`,`p_0`,`p_1`,`p_2`,`addtime`) values ('1','" . $kind . "','" . $name . "','" . $p_0 . "','" . $p_1 . "','" . $p_2 . "','" . $date . "')";
                $id  = $DB->insert($sql);
                if ($id) {
                    return $id;
                }
                return 0;
            }

        }
    }
    return 0;
}

function getTools($arr, $cid)
{
    if (!is_array($arr)) {
        return [];
    }
    $arr2 = [];
    foreach ($arr as $tool) {
        if ($tool['cid'] == $cid && $cid > 0) {
            $arr2[] = $tool;
        }
    }
    return $arr2;
}

function display_info($tool)
{
    $arr           = [];
    $arr['active'] = $tool['active'] == 1 ? '<span class="btn btn-xs btn-success">上架中</span>' : '<span class="btn btn-xs btn-warning">已下架</span>';
    $arr['close']  = $tool['close'] == 0 ? '<span class="btn btn-xs btn-success">显示</span>' : '<span class="btn btn-xs btn-warning">隐藏</span>';
    return $arr['active'] . '&nbsp;' . $arr['close'];
}

include './head.php';

echo '<div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top: 10px">';

$mod = isset($_GET['mod']) ? daddslashes($_GET['mod']) : null;
if ($mod == "clone") {

    $url       = input('post.url', 1);
    $key       = input('post.key', 1);
    $type      = (int) input('post.type');
    $site_type = (int) input('post.site_type');

    if (empty($url)) {
        showmsg("克隆地址不能为空！", 4);
    } elseif (!preg_match("/^(http|https):\/\/([\w\-\.]+)\.([\w\/:]+)/", $url)) {
        showmsg("克隆地址格式不正确！", 4);
    } else if (empty($key)) {
        showmsg("克隆密钥不能为空！", 4);
    }

    $data = get_curl($url . 'api.php?act=clone&key=' . $key);
    $json = json_decode($data, true);
    if (is_array($json)) {
        if ($json['code'] == 1) {
            $backupDb = backupDb();
            if ($backupDb) {
                $tips = '自动备份[社区、商品、分类、模板]数据成功！';
            } else {
                $tips = '自动备份[社区、商品、分类、模板]数据失败！';
            }
            if ($type == 0) {
                //全部克隆
                $ok = 0;
                if ($site_type == 1) {
                    $type_class   = (int) input('post.type_class');
                    $type_tools   = (int) input('post.type_tools');
                    $type_prid    = (int) input('post.type_prid');
                    $type_shequ   = (int) input('post.type_shequ');
                    $type_message = (int) input('post.type_message');
                    $DB->exec("DELETE FROM cmy_class WHERE 1");
                    $DB->exec("DELETE FROM cmy_tools WHERE 1");
                    if ($type_tools == 1) {
                        $type_class = 1;
                    }
                    if ($type_prid == 1) {
                        $DB->exec("DELETE FROM cmy_price WHERE 1");
                    }
                    if ($type_message == 1) {
                        $DB->exec("DELETE FROM cmy_message WHERE 1");
                    }
                } else {
                    $DB->exec("DELETE FROM cmy_class WHERE 1");
                    $DB->exec("DELETE FROM cmy_tools WHERE 1");
                    $type_class = (int) input('post.type_class');
                    $type_tools = (int) input('post.type_tools');
                    if ($type_tools == 1) {
                        $type_class = 1;
                    }
                    $type_prid = (int) input('post.type_prid');
                    if ($type_prid == 1) {
                        $DB->exec("DELETE FROM cmy_price WHERE 1");
                    }
                    $type_shequ   = (int) input('post.type_shequ');
                    $type_message = 0;
                }

                if ($type_class == 0 && $type_tools == 0 && $type_prid == 0 && $type_shequ == 0) {
                    showmsg('您还未勾选任何需要克隆的数据，至少勾选一个！', 3);
                }

                $error     = array();
                $count1    = count($json['class']);
                $count2    = count($json['tools']);
                $count3    = count($json['shequ']);
                $count4    = count($json['price']);
                $count5    = isset($json['message']) ? count($json['message']) : 0;
                $count1_ok = 0;
                $count2_ok = 0;
                $count3_ok = 0;
                $count4_ok = 0;
                $count5_ok = 0;
                $msg       = '克隆操作已执行完毕！<br/>';

                if ($type_class == 1) {
                    if (is_array($json['class']) && $count1 > 0) {
                        for ($i = 0; $i < $count1; $i++) {
                            $v = $json['class'][$i];
                            if (isset($v['upcid']) && $v['upcid'] > 0) {
                                continue;
                            }

                            if ($site_type == 1) {
                                //同系统
                                $sql = "INSERT INTO `pre_class` (`sort`,`name`,`shopimg`,`alert`,`islogin`,`active`) values ('" . $v['sort'] . "','" . $v['name'] . "','" . $v['shopimg'] . "','" . $v['alert'] . "','" . $v['islogin'] . "','" . $v['active'] . "')";
                            } else {
                                //彩虹
                                $sql = "INSERT INTO `pre_class` (`sort`,`name`,`shopimg`,`alert`,`islogin`,`active`) values ('" . $v['sort'] . "','" . $v['name'] . "','" . $v['shopimg'] . "','','0','" . $v['active'] . "')";
                            }

                            $cid = $DB->insert($sql);
                            if ($cid > 0) {
                                $count1_ok++;
                                $ok += 1;
                                if ($site_type == 1) {
                                    $subItem    = getSuData($json['class'], $v['cid'], $site_type);
                                    $subData    = [];
                                    $subItemNum = count($subItem);
                                }

                                if ($site_type == 1 && is_array($subItem) && count($subItem) > 0) {
                                    for ($x = 0; $x < $subItemNum; $x++) {
                                        $v2   = $subItem[$x];
                                        $sql2 = "INSERT INTO `pre_class` (`upcid`,`sort`,`name`,`shopimg`,`alert`,`active`) values ('" . $cid . "','" . $v2['sort'] . "','" . $v2['name'] . "','" . $v2['shopimg'] . "','" . $v2['alert'] . "','" . $v2['active'] . "')";

                                        $subcid = $DB->insert($sql2);
                                        if ($subcid < 1) {
                                            $error['class'][$v2['cid']] = $DB->error();
                                        } else {
                                            $count1_ok++;
                                            if ($type_tools == 1 && $json['tools'] && $count2 > 0) {
                                                for ($y = 0; $y < $count2; $y++) {
                                                    if ($json['tools'][$y]['cid'] == $v2['cid'] && $tool = $json['tools'][$y]) {
                                                        if ($tool['value'] < 1) {
                                                            $tool['value'] = 1;
                                                        }

                                                        $sql3 = "INSERT INTO `pre_tools` (`cid`,`name`,`sort`,`price`,`cost`,`cost2`,`price1`,`prices`,`input`,`inputs`,`value`,`desc`,`alert`,`result`,`shopimg`,`min`,`max`,`is_curl`,`is_rank`,`curl`,`repeat`,`multi`,`validate`,`check_val`,`shequ`,`goods_id`,`goods_type`,`goods_param`,`close`,`active`,`close_alert`) values ('" . $subcid . "','" . $tool['name'] . "','" . $tool['sort'] . "','" . $tool['price'] . "','" . $tool['cost'] . "','" . $tool['cost2'] . "','" . $tool['price1'] . "','" . $tool['prices'] . "','" . $tool['input'] . "','" . $tool['inputs'] . "','" . $tool['value'] . "','" . addslashes($tool['desc']) . "','" . addslashes($tool['alert']) . "','" . addslashes($tool['result']) . "','" . $tool['shopimg'] . "','" . $tool['min'] . "','" . $tool['max'] . "','" . $tool['is_curl'] . "','" . $tool['is_rank'] . "','" . $tool['curl'] . "','" . $tool['repeat'] . "','" . $tool['multi'] . "','" . $tool['validate'] . "','" . $tool['check_val'] . "','" . $tool['shequ'] . "','" . $tool['goods_id'] . "','" . $tool['goods_type'] . "','" . $tool['goods_param'] . "','" . $tool['close'] . "','" . $tool['active'] . "','" . addslashes($tool['close_alert']) . "')";
                                                        $tid  = $DB->insert($sql3);
                                                        if ($tid > 0) {
                                                            $count2_ok++;
                                                            $ok += 1;
                                                            //处理对接站
                                                            $shequ_id = $type_shequ == 1 ? getShequ($json['shequ'], $tool['shequ']) : 0;
                                                            if ($shequ_id) {
                                                                $count3_ok += 1;
                                                                $DB->query("UPDATE `pre_tools` set `shequ`='" . $shequ_id . "' where tid='" . $tid . "'");
                                                            }
                                                            //处理加价模板
                                                            if ($type_prid == 1 && $count4 > 0) {
                                                                $prid = getPrid($json['price'], $tool['prid'], $site_type);
                                                            }

                                                            if ($prid) {
                                                                $count4_ok += 1;
                                                            } else {
                                                                $prid = getDefaultPrid();
                                                            }

                                                            $DB->query("UPDATE `pre_tools` set `prid`='" . $prid . "' where tid='" . $tid . "'");

                                                            $DB->query("UPDATE `pre_tools` set `sort`='" . (isset($tool['sort']) ? $tool['sort'] : $tid) . "' where tid='" . $tid . "'");
                                                        } else {
                                                            $error['tools'][$tool['tid']] = $DB->error();
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    if ($type_tools == 1 && $json['tools'] && $count2 > 0) {
                                        for ($z = 0; $z < $count2; $z++) {
                                            $tool = $json['tools'][$z];
                                            if ($tool['cid'] == $v['cid']) {
                                                if ($tool['value'] < 1) {
                                                    $tool['value'] = 1;
                                                }

                                                if ($site_type == 1) {
                                                    $price1 = $tool['price1'];
                                                    //同系统
                                                    $sql2 = "INSERT INTO `pre_tools` (`cid`,`name`,`sort`,`price`,`cost`,`cost2`,`price1`,`prices`,`input`,`inputs`,`value`,`desc`,`alert`,`result`,`shopimg`,`min`,`max`,`is_curl`,`is_rank`,`curl`,`repeat`,`multi`,`validate`,`check_val`,`shequ`,`goods_id`,`goods_type`,`goods_param`,`close`,`active`,`close_alert`) values ('" . $cid . "','" . $tool['name'] . "','" . $tool['sort'] . "','" . $tool['price'] . "','" . $tool['cost'] . "','" . $tool['cost2'] . "','" . $tool['price1'] . "','" . $tool['prices'] . "','" . $tool['input'] . "','" . $tool['inputs'] . "','" . $tool['value'] . "','" . addslashes($tool['desc']) . "','" . addslashes($tool['alert']) . "','" . addslashes($tool['result']) . "','" . $tool['shopimg'] . "','" . $tool['min'] . "','" . $tool['max'] . "','" . $tool['is_curl'] . "','" . $tool['is_rank'] . "','" . $tool['curl'] . "','" . $tool['repeat'] . "','" . $tool['multi'] . "','" . $tool['validate'] . "','" . $tool['check_val'] . "','" . $tool['shequ'] . "','" . $tool['goods_id'] . "','" . $tool['goods_type'] . "','" . $tool['goods_param'] . "','" . $tool['close'] . "','" . $tool['active'] . "','" . addslashes($tool['close_alert']) . "')";
                                                } else {
                                                    //彩虹
                                                    $price1 = $tool['price'];
                                                    $price  = sprintf('%.2f', $price1 + $price1 * 20 / 100);
                                                    $cost   = sprintf('%.2f', $price1 + $price1 * 15 / 100);
                                                    $cost2  = sprintf('%.2f', $price1 + $price1 * 10 / 100);
                                                    if (stripos($tool['goods_param'], '#') !== false) {
                                                        $arr                 = explode('#', $tool['goods_param']);
                                                        $tool['goods_id']    = $arr[0];
                                                        $tool['goods_param'] = $arr[1];
                                                    }
                                                    $sql2 = "INSERT INTO `pre_tools` (`cid`,`name`,`sort`,`price`,`cost`,`cost2`,`price1`,`prices`,`input`,`inputs`,`value`,`desc`,`alert`,`shopimg`,`min`,`max`,`is_curl`,`is_rank`,`curl`,`repeat`,`multi`,`validate`,`check_val`,`shequ`,`goods_id`,`goods_type`,`goods_param`,`close`,`active`) values ('" . $cid . "','" . $tool['name'] . "','" . $tool['sort'] . "','" . $price . "','" . $cost . "','" . $cost2 . "','" . $price1 . "','" . $tool['prices'] . "','" . $tool['input'] . "','" . $tool['inputs'] . "','" . $tool['value'] . "','" . addslashes($tool['desc']) . "','" . addslashes($tool['alert']) . "','" . $tool['shopimg'] . "','" . $tool['min'] . "','" . $tool['max'] . "','" . $tool['is_curl'] . "','1','" . $tool['curl'] . "','" . $tool['repeat'] . "','" . $tool['multi'] . "','" . $tool['validate'] . "','0','" . $tool['shequ'] . "','" . $tool['goods_id'] . "','" . $tool['goods_type'] . "','" . $tool['goods_param'] . "','" . $tool['close'] . "','" . $tool['active'] . "')";
                                                }
                                                $tid = $DB->insert($sql2);
                                                if ($tid > 0) {
                                                    $count2_ok++;
                                                    $ok += 1;
                                                    //处理对接站
                                                    $shequ_id = getShequ($json['shequ'], $tool['shequ']);
                                                    if ($shequ_id) {
                                                        $count3_ok += 1;
                                                        $DB->query("UPDATE `pre_tools` set `shequ`='{$shequ_id}' where tid='{$tid}'");
                                                    }

                                                    //处理加价模板
                                                    if ($type_prid == 1 && $count4 > 0) {
                                                        $prid = getPrid($json['price'], $tool['prid'], $site_type);
                                                    }

                                                    if ($prid) {
                                                        $count4_ok += 1;
                                                    } else {
                                                        $prid = getDefaultPrid();
                                                    }

                                                    setToolPrice($tid, $prid, $price1);
                                                    $sort = isset($tool['sort']) ? $tool['sort'] : $tid;
                                                    $DB->query("UPDATE `pre_tools` set `sort`='{$sort}' where tid='{$tid}'");
                                                } else {
                                                    $error['tools'][$tool['tid']] = $DB->error();
                                                }
                                            }
                                        }
                                    }
                                }
                            } else {
                                $error['class'][$v['cid']] = $DB->error();
                            }
                        }

                        $msg .= '分类数据：共' . $count1 . '条，成功增加' . $count1_ok . '条数据<br/>';
                        if ($type_tools) {
                            $msg .= '商品数据：共' . $count2 . '条，成功增加' . $count2_ok . '条数据<br/>';
                        }
                        if ($type_shequ) {
                            $msg .= '社区列表：共' . $count3 . '条，成功增加' . $count3 . '条数据<br/>';
                        }

                        if ($type_prid) {
                            $msg .= '加价模板：共' . $count4 . '条，成功增加' . $count4 . '条数据<br/>';
                        }
                    } else {
                        $msg .= '分类数据：无数据或数据解析失败！<br/>';
                        if ($type_tools) {
                            $msg .= '商品数据：无数据或数据解析失败！<br/>';
                        }
                    }
                }

                if ($type_tools == 0 || $type_class == 0) {
                    if ($type_shequ == 1 && $count3 > 0) {
                        $count3_ok = 0;
                        foreach ($json['shequ'] as $item) {
                            $sql = "INSERT INTO `pre_shequ` (`url`,`type`,`status`) values ('" . $item['url'] . "','" . $item['type'] . "','1')";
                            if ($DB->insert($sql)) {
                                $count3_ok++;
                                $ok++;
                            }

                        }
                        $msg .= '社区列表：共' . $count3 . '条，成功增加' . $count3_ok . '条数据<br/>';
                    }

                    if ($type_prid == 1 && $count4 > 0) {
                        $count4_ok = 0;
                        foreach ($json['price'] as $item) {
                            if ($site_type == 2) {
                                if ($item['kind'] == 1) {
                                    $kind = '1';
                                    $p_0  = $item['p_0'];
                                    $p_1  = $item['p_1'];
                                    $p_2  = $item['p_2'];
                                } else {
                                    $kind = '2';
                                    $p_0  = $item['p_0'] > 1 ? ($item['p_0'] - 1) * 100 : '10';
                                    $p_1  = $item['p_1'] > 1 ? ($item['p_1'] - 1) * 100 : '15';
                                    $p_2  = $item['p_2'] > 1 ? ($item['p_2'] - 1) * 100 : '20';
                                }
                            } else {
                                $kind = $item['kind'];
                                $p_0  = $item['p_0'];
                                $p_1  = $item['p_1'];
                                $p_2  = $item['p_2'];
                            }

                            $sql = "INSERT INTO `pre_price` (`kind`,`name`,`p_0`,`p_1`,`p_2`,`addtime`) values ('" . $kind . "','" . $item['name'] . "','" . $p_0 . "','" . $p_1 . "','" . $p_2 . "','" . $date . "')";
                            if ($DB->insert($sql)) {
                                $count4_ok++;
                                $ok++;
                            }

                        }
                        $msg .= '加价模板：共' . $count4 . '条，成功增加' . $count4_ok . '条数据<br/>';
                    }

                }

                if ($site_type == 1 && $type_message == 1 && isset($json['message']) && $count5 > 0) {
                    foreach ($json['message'] as $v) {
                        $sqlData = [':zid' => $v['zid'], ':cid' => $v['cid'], ':type' => $v['type'], ':title' => $v['title'], ':author' => $v['author'], ':seotitle' => $v['seotitle'], ':seokeywords' => $v['seokeywords'], ':seodescription' => $v['seodescription'], ':content' => $v['content'], ':color' => $v['color'], ':count' => $v['count'], ':sort' => $v['sort'], ':active' => $v['active'], ':addtime' => $v['addtime']];
                        $sql     = "INSERT INTO `pre_message` (`zid`,`cid`,`type`,`title`,`author`,`seotitle`,`seokeywords`,`seodescription`,`content`,`color`,`count`,`sort`,`active`,`addtime`) values (:zid,:cid,:type,:title,:author,:seotitle,:seokeywords,:seodescription,:content,:color,:count,:sort,:active,:addtime)";
                        if ($DB->insert($sql, $sqlData)) {
                            $count5_ok++;
                            $ok++;
                        } else {
                            $error['message'][$v['id']] = $DB->error();
                        }

                    }
                    $msg .= '文章数据：共' . $count5 . '条，成功增加' . $count5_ok . '条数据<br/>';
                }

                $err   = '';
                $names = ['prid' => '加价模板', 'shequ' => '货源站点', 'tools' => '商品上架', 'class' => '分类上架'];
                foreach ($error as $key => $item) {
                    $err .= $names[$key] . "错误日志：<br>";
                    foreach ($item as $index => $value) {
                        $err .= "ID 【" . $index . "】写入失败，" . $value . "<br>";
                    }
                }
                if (!empty($err)) {
                    $msg = $msg . "<br>" . $err;
                }
                if (isset($tips) && $tips) {
                    $msg = $tips . "<br>" . $msg;
                }
                showmsg($msg, 1);
            } else {
                //增量克隆
                $arr1     = $json['class'];
                $arr2     = $json['tools'];
                $arrClass = [];
                foreach ($arr1 as $group) {
                    if (!is_array($group) || !is_numeric($group['cid'])) {
                        continue;
                    }
                    $info          = [];
                    $info['goods'] = getTools($arr2, $group['cid']);
                    $info['group'] = $group;
                    $arrClass[]    = $info;
                }

                saveTempData('shequList', $json['shequ']);

                $shoplist   = '';
                $pclassList = [];
                $ptoolsList = [];
                foreach ($arrClass as $item) {
                    $pclassList[$item['group']['cid']] = $item['group'];
                    $cid                               = $item['group']['cid'];
                    $shoplist .= '
                <a class="panel-title" data-toggle="collapse" data-parent="#class" href="#class_' . $cid . '"><div class="list-group-item list-group-item-success">
                <span class="pull-right"><i class="fa fa-chevron-down"></i></span>&nbsp;' . $item['group']['name'] . '</div></a>';
                    $shoplist .= '<div id="class_' . $cid . '" class="panel-collapse collapse">
                <table class="table table-bordered" style="table-layout: fixed;">
                <tbody>
                <tr class="hide"><td><input class="inp-cmckb-xs" name="group[]" id="cate_' . $cid . '" value="' . $cid . '" type="checkbox" style="display: none;"/></td></tr>
                <tr><td><input class="inp-cmckb-xs" id="SelectAlltlist_' . $cid . '" value="' . $cid . '" type="checkbox" onclick="SelectAll(' . $cid . ',this)" style="display: none;"/>
                    <label class="cmckb-xs" for="SelectAlltlist_' . $cid . '"><span>
                      <svg width="12px" height="10px">
                        <use xlink:href="#checkSvg"></use>
                      </svg>
                    </span><span>全选&nbsp;TID</span></label></td><td>商品名称</td><td>价格</td><td>状态</td></tr>
                ';
                    $tablist = '';
                    if (is_array($item['goods'])) {
                        foreach ($item['goods'] as $tool) {
                            $ptoolsList[$tool['tid']] = $tool;
                            $tablist .= '<tr><td><input class="inp-cmckb-xs class_' . $cid . '" name="tids[' . $cid . '][]" id="tid' . $tool['tid'] . '" onchange="setCate(' . $cid . ')" type="checkbox" value="' . $tool['tid'] . '" style="display:none;"/><label class="cmckb-xs" for="tid' . $tool['tid'] . '"><span><svg width="12px" height="10px"><use xlink:href=" #checkSvg"></use></svg></span><span>&nbsp;' . $tool['tid'] . '</span></label></td><td>' . $tool['name'] . '</td><td>' . $tool['price'] . '</td><td>' . display_info($tool) . '</td></tr>';
                        }
                    }
                    if (empty($tablist)) {
                        $tablist .= '<tr><td><input class="inp-cmckb-xs" type="checkbox"  onclick="//setVal(this)" style="display: none;"/><label class="cmckb-xs" for="tid' . $tool['tid'] . '"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>&nbsp;—</span></label></td><td>当前条件下商品为空</td><td>——</td></tr>';
                    }
                    $shoplist .= $tablist . '</tbody></table></div>';
                }

                echo '
          <form action="?mod=clone_add" method="POST" class="form-horizontal" role="form">
          <div class="block">
            <div class="block-title"><h3 class="panel-title">请选择要克隆的商品数据</h3></div>
            <div class="">
                    <div class="row col-xs-12 col-lg-11" style="float:none;margin:0 auto;">
                        ' . $shoplist . '
                        <!--SVG Sprites-->
                        <svg class="inline-svg">
                          <symbol id="checkSvg" viewbox="0 0 12 10">
                            <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                          </symbol>
                        </svg>
                        <br>
                    </div>
                    <div class="row col-xs-12 col-lg-11" style="float:none;margin:0 auto;">
                        <input type="submit" name="submit" class="btn btn-primary btn-block" value="确定克隆"/>
                    </div>
                    <p><br></p>
                </form>
            </div>
            </div>
            ';
                echo <<<'jsjb'
                <script type="text/javascript">
                var checkedZt = false;
                function SelectAllOther(type, chkAll){
                    var items = $('.'+type);
                    for (i = 0; i < items.length; i++) {
                        if (items[i].type == "checkbox") {
                            items[i].checked = chkAll.checked;
                        }
                    }
                    return true;
                }

                function SelectAll(cid, chkAll) {
                    var items = $('.class_'+cid);
                    var num = 0
                    for (i = 0; i < items.length; i++) {
                        if (items[i].id.indexOf("tid") != -1 && items[i].type == "checkbox") {
                            items[i].checked = chkAll.checked;
                            if(chkAll.checked){
                                num++;
                            }
                        }
                    }

                    if(num > 0){
                        $("#cate_" + cid).attr("checked", true);
                    }
                    else{
                        $("#cate_" + cid).attr("checked", false);
                    }

                    if(num == items.length){
                        $(chkAll).attr("checked", "checked");
                        $(chkAll).attr("checked", true);
                    }
                    else{
                        console.log("false");
                        $(chkAll).attr("checked", false);
                    }
                    return true;
                }

                function setCate(cid){
                    var items = $('.class_'+cid);
                    var num = 0
                    for (i = 0; i < items.length; i++) {
                        if (items[i].id.indexOf("tid") != -1 && items[i].type == "checkbox") {
                            if(items[i].checked){
                                num++;
                            }
                        }
                    }
                    if(num > 0){
                        $("#cate_" + cid).attr("checked", true);
                    }
                    else{
                        $("#cate_" + cid).attr("checked", false);
                    }
                    if(num == items.length){
                        $("#SelectAlltlist_"+cid).attr("checked", "checked");
                        $("#SelectAlltlist_"+cid).attr("checked", true);
                    }
                    else{
                        $("#SelectAlltlist_"+cid).attr("checked", false);
                    }
                }

                function SelectAllClass() {
                    var items = $('.clist');
                    for (i = 0; i < items.length; i++) {
                        if (items[i].id.indexOf("cid") != -1 && items[i].type == "checkbox") {
                            items[i].checked = !checkedZt;
                        }
                    }
                    checkedZt = !checkedZt;
                }
                </script>
jsjb;
                saveTempData('pclassList', $pclassList);
                saveTempData('ptoolsList', $ptoolsList);
                saveTempData('site_type', $site_type);
            }

        } elseif ($json['code'] == (0 - 4)) {
            showmsg("克隆密钥错误", 4);
        } else {
            if (isset($json['msg'])) {
                showmsg("克隆失败，" . $json['msg'], 4);
            } elseif (isset($json['message'])) {
                showmsg("克隆失败，" . $json['message'], 4);
            } else {
                showmsg("对接网站返回内容解析失败，" . $data, 4);
            }
        }
    } elseif (substr(bin2hex($data), 0, 6) == 'efbbbf') {
        showmsg("对方网站的源码被用记事本改过，请先在对方网站清理BOM头部", 4);
    } else {
        showmsg("对方网站无法连接或存在金盾或云锁等防火墙！返回文本：" . $data, 4);
    }

} elseif ($mod == "clone_add") {
    $site_type = intval(getTempData('site_type'));
    $groups    = input("post.group", 1);
    $tids      = input("post.tids", 1);

    $ptoolsList = @unserialize(getTempData('ptoolsList'));
    $pclassList = @unserialize(getTempData('pclassList'));
    $shequList  = @unserialize(getTempData('shequList'));
    $count1     = count($groups);
    $count2     = 0;
    $succ1      = 0;
    $succ2      = 0;
    $error1     = '';
    $error2     = '';

    foreach ($groups as $groupId) {
        $cateRow = $pclassList[$groupId];
        if (empty($cateRow['name'])) {
            $error1 .= '[' . $groupId . ']分类导入失败，分类名称为空！<br>';
        } else {
            $row = $DB->get_row("SELECT * from cmy_class where `name`=:name limit 1", [':name' => $cateRow['name']]);
            if (!$row) {
                $cid = $DB->insert("INSERT INTO `pre_class` (`name`,`islogin`,`active`) VALUES ('" . $cateRow['name'] . "','0','1')");
                if (!$cid) {
                    $error1 .= '分类【' . $cateRow['name'] . '[' . $groupId . ']】导入失败，错误：' . $DB->error() . '<br>';
                    continue;
                } else {
                    $succ1++;
                }
            } else {
                $cid = $row['cid'];
                $succ1++;
            }

            $tool_list = $tids[$groupId];
            foreach ($tool_list as $key => $tid) {
                $count2++;
                // if (stripos($str, $groupId . '_') === false) {
                //     continue;
                // }
                // $tid  = intval(str_ireplace($groupId . '_', '', $str));
                $tool = $ptoolsList[$tid];
                if (!is_array($tool) || !isset($tool['name'])) {
                    $error1 .= '分类【' . $cateRow['name'] . '】下的商品[' . $tid . ']的信息不完整，导入失败！<br>';
                    continue;
                }

                $prid = getDefaultPrid();
                if ($site_type == 1) {
                    //同系统
                    $price1 = floatval($tool['price1']);
                    $sql    = "INSERT INTO `pre_tools` (`cid`,`name`,`price`,`cost`,`cost2`,`price1`,`prid`,`prices`,`input`,`inputs`,`value`,`desc`,`alert`,`result`,`shopimg`,`min`,`max`,`is_curl`,`goods_id`,`goods_type`,`goods_param`,`is_rank`,`curl`,`repeat`,`multi`,`validate`,`check_val`,`close`,`active`,`close_alert`) values ('" . $cid . "','" . addslashes($tool['name']) . "','" . floatval($tool['price']) . "','" . floatval($tool['cost']) . "','" . floatval($tool['cost2']) . "','" . $price1 . "','0','" . addslashes($tool['prices']) . "','" . addslashes($tool['input']) . "','" . addslashes($tool['inputs']) . "','" . $tool['value'] . "','" . addslashes($tool['desc']) . "','" . addslashes($tool['alert']) . "','" . addslashes($tool['result']) . "','" . addslashes($tool['shopimg']) . "','" . intval($tool['min']) . "','" . intval($tool['max']) . "','" . intval($tool['is_curl']) . "','" . addslashes($tool['goods_id']) . "','" . addslashes($tool['goods_type']) . "','" . addslashes($tool['goods_param']) . "','" . intval($tool['is_rank']) . "','" . addslashes($tool['curl']) . "','" . intval($tool['repeat']) . "','" . intval($tool['multi']) . "','" . intval($tool['validate']) . "','" . addslashes($tool['check_val']) . "','" . intval($tool['close']) . "','" . intval($tool['active']) . "','" . addslashes($tool['close_alert']) . "')";
                } else {
                    $price1         = floatval($tool['price']);
                    $tool['active'] = $tool['close'] == 1 ? 0 : 1;
                    $tool['value']  = $tool['value'] > 0 ? intval($tool['value']) : 1;
                    //彩虹系统
                    $sql = "INSERT INTO `pre_tools` (`cid`,`name`,`price1`,`prid`,`prices`,`input`,`inputs`,`value`,`desc`,`alert`,`shopimg`,`min`,`max`,`is_curl`,`goods_id`,`goods_type`,`goods_param`,`is_rank`,`curl`,`repeat`,`multi`,`validate`,`close`,`active`) values ('" . $cid . "','" . addslashes($tool['name']) . "','" . $price1 . "','" . $prid . "','" . addslashes($tool['prices']) . "','" . addslashes($tool['input']) . "','" . addslashes($tool['inputs']) . "','" . $tool['value'] . "','" . addslashes($tool['desc']) . "','" . addslashes($tool['alert']) . "','" . addslashes($tool['shopimg']) . "','" . intval($tool['min']) . "','" . intval($tool['max']) . "','" . intval($tool['is_curl']) . "','" . addslashes($tool['goods_id']) . "','" . addslashes($tool['goods_type']) . "','" . addslashes($tool['goods_param']) . "','1','" . addslashes($tool['curl']) . "','" . intval($tool['repeat']) . "','" . intval($tool['multi']) . "','" . intval($tool['validate']) . "','" . intval($tool['close']) . "','" . intval($tool['active']) . "')";
                }

                $intotid = $DB->insert($sql);

                if (!$intotid) {
                    $error2 .= '分类【' . $cateRow['name'] . '[' . $groupId . ']】所属商品[' . $tid . ']的导入失败，' . $DB->error() . '<br>';
                    continue;
                } else {
                    $succ2++;
                    //处理价格
                    if ($site_type != 1) {
                        setToolPrice($intotid, $prid, $price1);
                    }

                    //处理对接站
                    $shequ_id = getShequ($shequList, $tool['shequ']);
                    if ($shequ_id) {
                        $DB->query("UPDATE `pre_tools` SET `shequ`='{$shequ_id}' where `tid`='{$intotid}'");
                    }
                }
            }
        }

    }
    $msg = "增量克隆成功，克隆站点类型[" . ($site_type == 1 ? '同系统' : '彩虹系统') . "]！" . ($site_type != 1 ? "<br/><span style='color:red'>注意：对接站点的数据由于是其他系统的不一样，需自行修改一下网站类型和其他资料</span>" : "") . "<br/><br/>详细导入日志如下：";
    $msg .= "<br>克隆分类，共" . $count1 . "个，成功" . $succ1 . "个，SQL成功执行" . $succ1 . "句。";
    if (!empty($error1)) {
        $msg .= "<br>失败信息：" . $error1;
    }
    $msg .= "<br>克隆商品，共" . $count2 . "个，成功" . $succ2 . "个，SQL成功执行" . $succ2 . "句。";
    if (!empty($error1)) {
        $msg .= "<br>失败信息：" . $error2;
    }
    showmsg($msg, 1);
} else {
    echo '
      <form action="?mod=clone" method="POST" class="form-horizontal" role="form">
      <div class="block">
        <div class="block-title">
            <h3 class="panel-title">' . $title . '</h3>
            <span>
                <a href="./backup.php" class="btn btn-primary btn-xs">备份管理</a>
            </span>
        </div>
        <div class="">
        <div class="alert alert-info">
            欢迎使用星河云商城Plus商品货源克隆功能 ๑*◡*๑<br>
            使用此功能可一键克隆目标站点的分类、商品及社区对接数据（除社区账号密码外），方便站长快速丰富网站内容。<br>
            <span style="color:red;">注意1：使用全量克隆将会清空本站所有商品和分类数据，请谨慎操作！</span><br>
            <span style="color:red;">注意2：由于彩虹系统的对接网站类型和本系统不一样，克隆后社区类型需要您自己再改一下！</span>
        </div>
            <div class="alert">
                <div class="form-group">
                    <div class="input-group"><div class="input-group-addon">站点URL</div>
                    <input type="text" name="url" value="" class="form-control" placeholder="http://www.qq.com/" required/>
                    <div class="input-group-addon" onclick="checkurl()"><small>检测连通性</small></div>
                </div></div>
                <div class="form-group">
                    <div class="input-group"><div class="input-group-addon">克隆密钥</div>
                    <input type="text" name="key" value="" class="form-control" placeholder="联系目标站点取得" required/>
                </div></div>
                <div class="form-group">
                    <div class="input-group"><div class="input-group-addon">站点类型</div>
                        <select class="form-control" name="site_type" id="site_type" onchange="setDispay()" value="1">
                        <option value="1">同系统</option>
                        <option value="2">彩虹系统</option>
                        </select>
                    </div>
                    <small style="color:red;">注意：选错了会导致数据异常或不完整，请认真选择！</small>
                </div>
                <div class="form-group">
                    <div class="input-group"><div class="input-group-addon">克隆方式</div>
                    <select class="form-control" onchange="setOptions(this.value)" name="type" id="type" value="0">
                        <option value="0">全量克隆（会清空本站原有的商品数据）</option>
                        <option value="1">增量克隆（不会改变原有数据）</option>
                    </select>
                    <small>其中增量克隆只能完美克隆分类和商品，对应的加价模板和社区信息无法克隆过来</small>
                </div></div>
                <div class="form-group" id="clone_options" style="display:none;">
                    <table class="table table-striped table-bordered table-condensed text-center" id="pay_type">
                    <tbody>
                    <tr align="center"><td colspan="4">请选择哪些数据要克隆</td></tr>
                    <tr align="center"><td>商品分类</td><td>商品列表</td><td>社区列表</td><td>加价模板</td><td id="message_head" style="display:none;">文章列表</td></tr>
                    <tr>
                    <td>
                    <input class="inp-cmckb-xs" name="type_class" id="type_class" value="0" type="checkbox" onclick="if(this.checked){this.value=\'1\';}else{this.value=\'0\';};" style="display: none;"/>
                        <label class="cmckb-xs" for="type_class"><span>
                          <svg width="12px" height="10px">
                            <use xlink:href="#checkSvg"></use>
                          </svg>
                        </span>
                    </td>
                    <td>
                    <input class="inp-cmckb-xs" name="type_tools" id="type_tools" value="0" type="checkbox" onclick="if(this.checked){this.value=\'1\';}else{this.value=\'0\';};" style="display: none;"/>
                        <label class="cmckb-xs" for="type_tools"><span>
                          <svg width="12px" height="10px">
                            <use xlink:href="#checkSvg"></use>
                          </svg>
                        </span>
                    </td>
                    <td>
                    <input class="inp-cmckb-xs" name="type_shequ" id="type_shequ" value="0" type="checkbox" onclick="if(this.checked){this.value=\'1\';}else{this.value=\'0\';};" style="display: none;"/>
                        <label class="cmckb-xs" for="type_shequ"><span>
                          <svg width="12px" height="10px">
                            <use xlink:href="#checkSvg"></use>
                          </svg>
                        </span>
                    </td>
                    <td>
                    <input class="inp-cmckb-xs" name="type_prid" id="type_prid" value="0" type="checkbox" onclick="if(this.checked){this.value=\'1\';}else{this.value=\'0\';};" style="display: none;"/>
                        <label class="cmckb-xs" for="type_prid"><span>
                          <svg width="12px" height="10px">
                            <use xlink:href="#checkSvg"></use>
                          </svg>
                        </span>
                    </td>
                    <td id="message_body" style="display:none;">
                    <input class="inp-cmckb-xs" name="type_message" id="type_message" value="0" type="checkbox" onclick="if(this.checked){this.value=\'1\';}else{this.value=\'0\';};" style="display: none;"/>
                        <label class="cmckb-xs" for="type_message"><span>
                          <svg width="12px" height="10px">
                            <use xlink:href="#checkSvg"></use>
                          </svg>
                        </span>
                    </td>
                    </tr>
                    <tr align="center"><td colspan="4">注意：如果要克隆商品列表，必须勾选分类！否则数据会出问题</td></tr>
                    </table>
                </div>
            </div>
            <!--SVG Sprites-->
            <svg class="inline-svg">
              <symbol id="checkSvg" viewbox="0 0 12 10">
                <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
              </symbol>
              &nbsp;克隆此项
            </svg>
            <p><input type="submit" name="submit" onclick="this.value=\'克隆中..耐心等待\'" class="btn btn-primary btn-block" value="开始克隆"/></p>
        </div>
        <div class="panel-footer">
          <span class="glyphicon glyphicon-info-sign"></span> 本站克隆密钥<a href="./set.php?mod=cloneset">点此获取</a>
        </div>
      </div>
    </div>
  </div>';
    echo '
<script src="//cdn.staticfile.org/layer/2.3/layer.js"></script>
<script>
function setOptions(val){
    var site_type = $("#site_type").val();
    if(val==1){
        $("#clone_options").hide();
    }
    else{
        $("#clone_options").show();
        if(site_type==1){
            $("#message_head").show();
            $("#message_body").show();
        }
        else{
            $("#message_head").hide();
            $("#message_body").hide();
        }
    }
}

function setSelected(){
    var items = $("input[type=\'checkbox\']");
    for (i = 0; i < items.length; i++) {
        if(items[i].value==1){
            items[i].checked=true;
        }
        else{
            items[i].checked=false;
        }
    }

    setTimeout(function(){
        setSelected()
    }, 2000);
}

function setDispay(){
    var val = $("#type").val();
    setOptions(val);
}

function checkurl(){
    var url = $("input[name=\'url\']").val();
    if(url.indexOf(\'http\')>=0 && url.substr(-1) == \'/\'){
        var ii = layer.load(2, {shade:[0.1,\'#fff\']});
        $.ajax({
            type : "POST",
            url : "ajax.php?act=checkclone",
            data : {url:url},
            dataType : \'json\',
            success : function(data) {
                layer.close(ii);
                if(data.code == 1){
                    layer.msg(\'连通性良好，可以克隆\');
                }else if(data.code == 2){
                    layer.alert(\'无法自己克隆自己\');
                }else if(data.code == 3){
                    layer.alert(\'对方网站的源码被用记事本改过，请先在对方网站清理BOM头部\');
                }else{
                    layer.alert(\'对方网站无法连接或存在金盾或云锁等防火墙\');
                }
            } ,
            error:function(data){
                layer.close(ii);
                layer.msg(\'目标URL连接超时\');
                return false;
            }
        });
    }else{
        layer.alert(\'URL必须以 http:// 开头，以 / 结尾\');
    }
}
setDispay();
setSelected();
</script>';
}

include 'footer.php';
