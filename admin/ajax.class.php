<?php

/**
 * name   后台请求接口
 * author  秋云
 */
define('AJAX_VERSION', '2.3.4');

$goodLibMethods = ['class_sort', 'setClassSort'];
if (in_array($_GET['act'], $goodLibMethods)) {
    $loadGoodLib = true;
}

include "../includes/common.php";
checkLogin();

$act = isset($_GET['act']) ? input('get.act', 1) : null;

@header('Content-Type: application/json; charset=UTF-8');

switch ($act) {
    case 'class_get':
        checkAuthority('super');
        $page   = input('get.page', 1) > 0 ? intval(input('get.page', 1)) : 1;
        $limit  = input('get.limit', 1) > 0 ? intval(input('get.limit', 1)) : 30;
        $offset = $page > 1 ? $page * $limit : 0;
        $data   = [];
        $count  = $DB->count("SELECT count(*) FROM `pre_class` ORDER BY `sort` ASC,`cid` ASC");
        //$rs     = $DB->select("SELECT * FROM `pre_class` ORDER BY `sort` ASC,`cid` ASC LIMIT {$offset},{$limit}");
        $rs = $DB->select("SELECT * FROM `pre_class` ORDER BY `sort` ASC,`cid` ASC");
        if (is_array($rs)) {
            $data = $rs;
            foreach ($data as $key => $res) {
                $count1            = $DB->count("SELECT count(*) from `pre_tools` where cid= ?", array($res['cid']));
                $res['is_upclass'] = intval($res['upcid']) == 0 ? 1 : 0;
                $res['shops']      = intval($count1);
                if ($res['is_upclass'] == 1) {
                    // 获取所以子分类商品数量
                    $shops        = $DB->count("SELECT count(*) from `pre_tools` where `cid` IN (select `cid` from cmy_class where upcid = '{$res['cid']}')");
                    $res['shops'] = $res['shops'] + intval($shops);
                }
                $res['upcid'] = intval($res['upcid']);
                $data[$key]   = $res;
            }
        }
        $result = ['code' => 0, 'msg' => 'succ', 'data' => $data, 'count' => $count];
        exit(json_encode($result));
        break;
    case 'class_add':
        checkAuthority('super');
        $name  = input('post.name', 1);
        $upcid = intval(input('post.upcid', 1));
        if (empty($name)) {
            $result = ['code' => -1, 'msg' => '分类名称不能为空！', 'data' => []];
        } else {
            $sortRow = $DB->get_row("SELECT * FROM `pre_class` ORDER BY sort desc limit 1");
            $sort    = $sortRow['sort'] + 1;
            $sql     = "INSERT INTO `pre_class` (`upcid`,`name`,`sort`,`zid`,`active`) VALUES (:upcid,:name,:sort,:zid,:active)";
            $data    = [
                ':upcid'  => $upcid,
                ':name'   => $name,
                ':sort'   => $sort,
                ':zid'    => 1,
                ':active' => 1,
            ];
            if ($cid = $DB->insert($sql, $data)) {
                $result = ['code' => 0, 'msg' => 'succ', 'data' => ['cid' => $cid]];
            } else {
                $result = ['code' => -1, 'msg' => '添加失败，' . $DB->error(), 'data' => []];
            }
        }
        exit(json_encode($result));
        break;
    case 'class_edit':
        checkAuthority('super');
        $cid = intval(input('post.cid', 1));
        $row = $DB->get_row("SELECT * FROM `pre_class` where `cid`=:cid", [':cid' => $cid]);
        if (!$row) {
            $result = ['code' => -1, 'msg' => '该记录不存在！', 'data' => []];
        } else {
            $name = input('post.name', 1);
            if (empty($name)) {
                $result = ['code' => -1, 'msg' => '分类名称不能为空！', 'data' => []];
            } else {
                $sql  = "UPDATE  `pre_class` SET `name`=:name where `cid`=:cid";
                $data = [
                    ':name' => $name,
                    ':cid'  => $cid,
                ];
                if (false !== $DB->update($sql, $data)) {
                    $result = ['code' => 0, 'msg' => 'succ', 'data' => ['cid' => $cid, 'time' => time()]];
                } else {
                    $result = ['code' => -1, 'msg' => '操作失败，' . $DB->error(), 'data' => []];
                }
            }
        }
        exit(json_encode($result));
        break;
    case 'class_shopimg_saveall':
        checkAuthority('super');
        $shopimgArr = input('post.shopimg', 1);
        $count      = count($shopimgArr);
        $ok         = 0;
        foreach ($shopimgArr as $cid => $shopimg) {
            $shopimg = addslashes($shopimg);
            $data    = [
                ':shopimg' => $shopimg,
                ':cid'     => $cid,
            ];
            $sql = "UPDATE `pre_class` SET `shopimg`=:shopimg where cid=:cid ";
            if (false !== $DB->update($sql, $data)) {
                $ok++;
            }
        }
        $result = [
            'code' => 0,
            'msg'  => '共' . $count . '个分类图片，成功' . $ok . '个',
            'data' => ['count' => $count, 'time' => time()],
        ];
        exit(json_encode($result));
        break;
    case 'class_move':
        checkAuthority('super');
        $checkedList = input('post.checkedList', 1);
        $upcid       = input('post.upcid', 1);
        if ($upcid == -1) {
            exit('{"code":-1,"msg":"无效操作，请选择有效目标上级分类"}');
        }

        if ($upcid) {
            $row = $DB->get_row("SELECT * FROM `pre_class` where `cid`=:cid", [':cid' => $upcid]);
            if (!$row) {
                exit('{"code":-1,"msg":"目标上级分类不存在"}');
            } elseif ($row['upcid'] > 0) {
                exit('{"code":-1,"msg":"目标上级分类只能是一级分类或空分类"}');
            }
        }
        $count = count($checkedList);
        $ok    = 0;
        foreach ($checkedList as $key => $cid) {
            $data = [
                ':upcid' => $upcid,
                ':cid'   => $cid,
            ];
            $sql = "UPDATE `pre_class` SET `upcid`=:upcid where cid=:cid ";
            if (false !== $DB->update($sql, $data)) {
                $ok++;
            }
        }
        $result = [
            'code' => 0,
            'msg'  => '共' . $count . '个分类需要移动，成功' . $ok . '个',
            'data' => ['count' => $count, 'time' => time()],
        ];
        exit(json_encode($result));
        break;
    case 'class_change':
        checkAuthority('class');
        $aid         = (int) input('post.checkAction');
        $checkedList = input('post.checkedList', 1);
        if ($aid == 1) {
            $type = "显示选中";
        } elseif ($aid == 2) {
            $type = "隐藏选中";
        } elseif ($aid == 3) {
            $type = "保存所有";
        } elseif ($aid == 10) {
            $type = "删除选中";
        } elseif ($aid == 4) {
            $type = "设置登录可见";
        } elseif ($aid == 5) {
            $type = "取消登录可见";
        } else {
            exit('{"code":-1,"msg":"无效操作"}');
        }
        if ($aid == 3) {
            $classNameList = input('post.classNameList', 1);
            foreach ($classNameList as $cid => $name) {
                $name = addslashes($name);
                $data = [
                    ':name' => $name,
                    ':cid'  => $cid,
                ];
                $sql = "UPDATE `pre_class` SET `name`=:name where cid=:cid limit 1";
                if ($DB->query($sql, $data)) {
                    $i++;
                }
            }
        } else {
            foreach ($checkedList as $key => $cid) {
                if ($cid < 1) {
                    continue;
                }
                if ($aid == 1) {
                    $DB->query("UPDATE `pre_class` SET `active`='1' where `cid`=:cid limit 1", [':cid' => $cid]);
                } elseif ($aid == 2) {
                    $DB->query("UPDATE `pre_class` SET `active`='0' where `cid`=:cid limit 1", [':cid' => $cid]);
                } elseif ($aid == 4) {
                    $DB->query("UPDATE `pre_class` SET `islogin`='1' where `cid`=:cid limit 1", [':cid' => $cid]);
                } elseif ($aid == 5) {
                    $DB->query("UPDATE `pre_class` SET `islogin`='0' where `cid`=:cid limit 1", [':cid' => $cid]);
                } elseif ($aid == 10) {
                    $DB->query("DELETE FROM `pre_class` where `cid`=:cid", [':cid' => $cid]);
                    $DB->query("DELETE FROM `pre_tools` where `cid`=:cid", [':cid' => $cid]);
                }
                $i++;
            }
        }
        $result = [
            'code' => 0,
            'msg'  => '成功' . $type . $i . '个分类',
            'data' => ['cid' => $cid, 'time' => time()],
        ];
        exit(json_encode($result));
        break;
    case 'class_status':
        checkAuthority('super');
        $cid    = intval(input('post.cid', 1));
        $active = intval(input('post.active', 1));
        $sql    = "UPDATE `pre_class` SET `active`=:active where `cid`=:cid";
        $data   = [
            ':active' => $active,
            ':cid'    => $cid,
        ];
        if (false !== $DB->update($sql, $data)) {
            $result = ['code' => 0, 'msg' => 'succ', 'data' => ['cid' => $cid, 'time' => time()]];
        } else {
            $result = ['code' => -1, 'msg' => '操作失败，' . $DB->error(), 'data' => []];
        }
        exit(json_encode($result));
    case 'class_sort':
        $cid  = intval(input('post.cid', 1));
        $type = intval(input('post.type'));
        if (setClassSort($cid, $type)) {
            exit('{"code":0,"msg":"succ"}');
        } else {
            exit('{"code":-1,"msg":"失败"}');
        }
    case 'class_sort_set':
        $cid  = input('post.cid', 1);
        $sort = input('post.sort', 1);
        $sql  = "UPDATE `pre_class` SET `sort`=:sort where `cid`=:cid";
        $data = [
            ':sort' => $sort,
            ':cid'  => $cid,
        ];
        if (false !== $DB->update($sql, $data)) {
            $result = ['code' => 0, 'msg' => 'succ', 'data' => $data];
        } else {
            $result = ['code' => -1, 'msg' => '操作失败，' . $DB->error(), 'data' => []];
        }
        exit(json_encode($result));
    case 'class_islogin_set':
        $cid     = input('post.cid', 1);
        $islogin = input('post.islogin', 1);
        $sql     = "UPDATE `pre_class` SET `islogin`=:islogin where `cid`=:cid";
        $data    = [
            ':islogin' => $islogin,
            ':cid'     => $cid,
        ];
        if (false !== $DB->update($sql, $data)) {
            $result = ['code' => 0, 'msg' => 'succ', 'data' => $data];
        } else {
            $result = ['code' => -1, 'msg' => '操作失败，' . $DB->error(), 'data' => []];
        }
        exit(json_encode($result));
    case 'class_del':
        $cid  = input('post.cid', 1);
        $type = intval(input('post.type', 1));
        $row  = $DB->get_row("SELECT * FROM `pre_class` where `cid`=:cid", [':cid' => $cid]);
        if (!$row) {
            $result = ['code' => -1, 'msg' => '该记录不存在！', 'data' => []];
        } else {
            $sql = "DELETE FROM `pre_class` where `cid`=:cid";
            if ($DB->query($sql, [':cid' => $cid])) {
                if ($type == 2) {
                    //删除该分类下商品
                    $DB->query("DELETE FROM `pre_tools` where `cid`=:cid", [':cid' => $cid]);
                }

                if ($type == 3) {
                    //删除该分类下商品和子分类
                    $rs = $DB->select("SELECT * FROM `pre_class` where `upcid`=:cid", [':cid' => $cid]);
                    if ($rs) {
                        foreach ($rs as $key => $item) {
                            $DB->query("DELETE FROM `pre_tools` where `cid`=:cid", [':cid' => $item['cid']]);
                        }
                    }
                    $DB->query("DELETE FROM `pre_class` where `upcid`=:upcid", [':upcid' => $cid]);
                    $DB->query("DELETE FROM `pre_tools` where `cid`=:cid", [':cid' => $cid]);
                }
                $result = ['code' => 0, 'msg' => 'succ', 'data' => ['cid' => $cid, 'time' => time()]];
            } else {
                $result = ['code' => -1, 'msg' => '操作失败，' . $DB->error(), 'data' => []];
            }
        }
        exit(json_encode($result));

    default:
        exit('{"code":-4,"msg":"No Act","act":"' . $act . '","version":"' . AJAX_VERSION . '"}');
        break;
}
