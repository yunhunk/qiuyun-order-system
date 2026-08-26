<?php

use core\Db;

$is_defend = false;

include "./includes/common.php";

$act      = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
$url      = daddslashes($_GET['url']);
$authcode = daddslashes($_GET['authcode']);

$price_obj = null;
@header('Content-Type: application/json; charset=UTF-8');

if (isset($_GET['pagesize']) || isset($_POST['page'])) {
    $page     = intval(isset($_POST['page']) ? input('post.page', 1) : input('get.page', 1));
    $pagesize = intval(isset($_POST['pagesize']) ? input('post.pagesize', 1) : input('get.pagesize', 1));
    if (!$page) {
        $page = 1;
    }
    $offset = 0;
    if ($page > 1) {
        $offset = ($page - 1) * $pagesize;
    }
}

if ($act == 'clone') {
    $key = daddslashes($_GET['key']);
    if (!$key) {
        exit('{"code":-5,"msg":"确保各项不能为空"}');
    }

    if ($key != md5($password_hash . md5(SYS_KEY) . $conf['apikey'])) {
        exit('{"code":-4,"msg":"克隆密钥错误"}');
    }

    // $rs    = $DB->query("SELECT * FROM pre_class order by cid asc");
    // $class = array();
    // while ($res = $DB->fetch($rs)) {
    //     $class[] = $res;
    // }

    $rs    = $DB->query("SELECT * FROM `pre_class` where (`upcid` is null OR `upcid`=0) order by sort asc");
    $class = array();
    if ($rs) {
        while ($res = $DB->fetch($rs)) {
            $res['count'] = Db::name('tools')->where("cid='" . $res['cid'] . "'")->count('tid');
            $subClass     = Db::name('class')->where("upcid='" . $res['cid'] . "'")->count();
            if ($subClass > 0) {
                $rs2 = Db::name('class')->where("upcid='" . $res['cid'] . "'")->order("sort ASC")->select();
                foreach ($rs2 as $key2 => $res2) {
                    $res2['count'] = Db::name('tools')->where("cid='" . $res2['cid'] . "'")->count('tid');
                    $class[]       = $res2;
                }
            } else {
                $class[] = $res;
            }
        }
    }

    $rs    = $DB->query("SELECT * FROM pre_tools order by tid asc");
    $tools = array();
    while ($res = $DB->fetch($rs)) {
        $tools[] = $res;
    }
    $rs    = $DB->query("SELECT id,url,type FROM pre_shequ order by id asc");
    $shequ = array();
    while ($res = $DB->fetch($rs)) {
        $shequ[] = $res;
    }
    $rs    = $DB->query("SELECT * FROM pre_price order by id asc");
    $price = array();
    while ($res = $DB->fetch($rs)) {
        $price[] = $res;
    }
    $rs      = $DB->query("SELECT * FROM pre_message order by id asc");
    $message = array();
    while ($res = $DB->fetch($rs)) {
        $message[] = $res;
    }
    $result = array("code" => 1, "class" => $class, "tools" => $tools, "shequ" => $shequ, "price" => $price, "message" => $message);
} elseif ($act == 'class' || $act == 'classlist') {
    $rs   = $DB->query("SELECT * FROM `pre_class` where (`upcid` is null OR `upcid`=0) order by sort asc");
    $data = array();
    while ($res = $DB->fetch($rs)) {
        $subClass = Db::name('class')->where(['upcid' => $res['cid']])->count();
        if ($subClass > 0) {
            $rs2 = Db::name('class')->where(['upcid' => $res['cid']])->order("sort ASC")->select();
            foreach ($rs2 as $key2 => $res2) {
                $res2['count'] = Db::name('tools')->where("cid='" . $res2['cid'] . "'")->count('tid');
                $data[]        = $res2;
            }
        } else {
            $res['subClass'] = $subClass;
            $res['count']    = Db::name('tools')->where("cid='" . $res['cid'] . "'")->count('tid');
            $data[]          = $res;
        }
    }
    $result = array("code" => 0, "msg" => "succ", "data" => $data, "count" => count($data));
    exit(json_encode($result, 256));
} elseif ($act == 'tools') {
    $key   = input('get.key', 1);
    $cid   = isset($_GET['cid']) ? intval(input('get.cid', 1)) : null;
    $limit = isset($_GET['limit']) ? intval(input('get.limit', 1)) : 50;
    if (!$key) {
        exit('{"code":-5,"msg":"确保各项不能为空"}');
    } elseif ($limit < 10) {
        exit('{"code":-5,"msg":"单次获取商品不能小于10个"}');
    } elseif ($key != $conf['apikey']) {
        exit('{"code":-4,"msg":"API对接密钥错误，请在后台设置密钥"}');
    }
    $sqls = '';
    if ($cid) {
        $sqls = " and cid='{$cid}'";
    }
    $rs = $DB->query("SELECT * FROM pre_tools WHERE active=1{$sqls} order by tid asc limit " . $limit);
    while ($res = $DB->fetch($rs)) {
        $data[] = array('tid' => $res['tid'], 'cid' => $res['cid'], 'name' => $res['name'], 'price' => $res['price']);
    }
    exit(json_encode($data));
} elseif ($act == 'orders') {
    $tid    = intval($_GET['tid']);
    $status = intval(daddslashes($_GET['status']));
    $key    = daddslashes($_GET['key']);
    $limit  = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    $format = isset($_GET['format']) ? daddslashes($_GET['format']) : 'json';
    if (!$key) {
        exit('{"code":-5,"msg":"确保各项不能为空"}');
    }

    if ($key !== $conf['apikey']) {
        exit('{"code":-4,"msg":"API对接密钥错误，请在后台设置密钥"}');
    }

    if ($tid) {
        $tool  = $DB->get_row("SELECT * FROM pre_tools WHERE `tid` =:tid and active = 1 LIMIT 1", [':tid' => $tid]);
        $input = !empty($tool['input']) ? $tool['input'] : '下单QQ';
        if (!$tool) {
            exit('{"code":-5,"msg":"商品ID不存在"}');
        }

        $sqls  = " and tid='$tid'";
        $value = $tool['value'] > 0 ? $tool['value'] : 1;
    }

    if (isset($_GET['status'])) {
        $sqls .= " and status='" . $status . "'";
    }

    $rs = $DB->query("SELECT * FROM pre_orders WHERE status=0{$sqls} order by id asc limit $limit");
    while ($res = $DB->fetch($rs)) {
        $row = array('id' => $res['id'], 'tid' => $res['tid'], 'input' => $res['input'], 'input2' => $res['input2'], 'input3' => $res['input3'], 'input4' => $res['input4'], 'input5' => $res['input5'], 'value' => $res['value'], 'status' => $res['status']);
        if ($tid) {
            $row['inputs'] = $tool['inputs'];
        }

        $data[] = $row;
        if ($_GET['sign'] == 1) {
            $DB->query("update `pre_orders` set status=1 where `id`='{$res['id']}'");
        }

    }
    if ($format == 'text') {
        $txt = '';
        foreach ($data as $row) {
            $txt .= $row['input'] . ($row['input2'] ? '----' . $row['input2'] : null) . ($row['input3'] ? '----' . $row['input3'] : null) . ($row['input4'] ? '----' . $row['input4'] : null) . ($row['input5'] ? '----' . $row['input5'] : null) . '----' . $row['value'] . "\r\n";
        }
        exit($txt);
    } else {
        //$result = array('code'=>0, 'msg'=>'succ','data'=>$data);
        exit(json_encode($data));
    }
} elseif ($act == 'change') {
    $id     = intval($_GET['id']);
    $key    = daddslashes($_GET['key']);
    $status = intval($_GET['zt']); //1:已完成,2:正在处理,3:异常,4:待处理
    if (!$id || !$key) {
        exit('{"code":-5,"msg":"确保各项不能为空"}');
    }

    if ($key != $conf['apikey']) {
        exit('{"code":-4,"msg":"API对接密钥错误，请在后台设置密钥"}');
    }

    $row = $DB->get_row("SELECT * FROM pre_orders WHERE id='$id' limit 1");
    if ($id = $row['id']) {
        $sql = "update `pre_orders` set `status`='$status' where `id`='{$id}' limit 1";
        if ($DB->query($sql)) {
            $result = array("code" => 1, "msg" => "修改成功", "id" => $id);
        } else {
            $result = array("code" => -2, "msg" => "修改失败", "id" => $id);
        }
    } else {
        $result = array("code" => -5, "msg" => "订单ID不存在");
    }
} elseif ($act == 'getQrCode') {
    $w        = intval(isset($_GET['w']) ? $_GET['w'] : 8);
    $text     = daddslashes($_GET['text']);
    $hashsalt = isset($_GET['hashsalt']) ? $_GET['hashsalt'] : null;
    if ($conf['verify_open'] == 1 && $hashsalt != session_get()) {
        exit('{"code":-1,"message":"验证失败","msg":"验证失败"}');
    }
    $QRcode = new \core\QRcode();
    $level  = 'L'; // 纠错级别：L、M、Q、H
    $size   = $w; //元素尺寸
    $margin = 1; //边距
    @header("Content-Type:image/png");
    $img = $QRcode->png($text, $outfile, $level, $size, $margin, false, 0xFFFFFF, 0x000000);
    die;
} elseif ($act == 'goodslistbycid') {
    if (isset($_POST['user']) && isset($_POST['pass'])) {
        $user    = input('user', 1);
        $pass    = input('pass', 1);
        $userrow = $DB->get_row("SELECT * FROM `pre_site` WHERE `user` = :user LIMIT 1", [':user' => $user]);
        if ($userrow && $userrow['user'] == $user && $userrow['pwd'] == $pass && $userrow['status'] == 1) {
            $isLogin2  = 1;
            $price_obj = new \core\Price($userrow['zid'], $userrow);
        } elseif ($userrow && $userrow['status'] == 0) {
            exit('{"code":-1,"message":"该账户已被封禁"}');
        } else {
            exit('{"code":-1,"message":"用户名或密码不正确"}');
        }
    }

    $cid = isset($_POST['cid']) ? intval(input('cid', 1)) : 0;
    if (isset($offset)) {
        $rs = $DB->select("SELECT * FROM `pre_tools` WHERE `cid`='{$cid}' ORDER BY `sort` ASC limit {$offset},{$pagesize}");
    } else {
        $rs = $DB->select("SELECT * FROM `pre_tools` WHERE `cid`='{$cid}' ORDER BY sort ASC");
    }

    $data = array();
    if (is_array($rs)) {
        foreach ($rs as $key => $res) {
            if (isset($price_obj)) {
                $price_obj->setToolInfo($res['tid'], $res);
                $price = $price_obj->getBuyPrice($res['tid']);
            } else {
                $price = $res['price'];
            }

            if ($res['is_curl'] == 4) {
                $isfaka       = 1;
                $res['input'] = getFakaInput();
                // 查询缓存6小时
                if ($res['stock'] <= 1 || $res['stock_time'] <= time()) {
                    $count      = intval($DB->count("SELECT count(*) FROM pre_faka WHERE `tid`='{$res['tid']}' AND orderid<=0"));
                    $stock_time = time() + (3600 * 6);
                    $DB->update("UPDATE `pre_tools` SET `stock`='{$count}',`stock_time`='{$stock_time}' WHERE `tid`='{$res['tid']}'");
                } else {
                    $count = intval($res['stock']);
                }
                $count             = intval($res['stock']);
                $res['stock_open'] = 1;
            } else {
                if ($res['stock_open'] == 1) {
                    $count = intval($res['stock']);
                } else {
                    //$count = 0;
                    $res['stock_open'] = 0;
                    $count             = null;
                    if ($res['stock'] > 0) {
                        $count             = $res['stock'];
                        $res['stock_open'] = 1;
                    }
                }
                $isfaka = 0;
            }
            $data[] = array('tid' => $res['tid'], 'cid' => $res['cid'], 'sort' => $res['sort'], 'name' => $res['name'], 'value' => $res['value'], 'price' => $price, 'input' => $res['input'], 'inputs' => $res['inputs'], 'desc' => $res['desc'], 'alert' => $res['alert'], 'shopimg' => $res['shopimg'], 'validate' => $res['validate'], 'valiserv' => 0, 'repeat' => $res['repeat'], 'multi' => $res['multi'], 'close' => $res['active'] == 1 ? 0 : 1, 'prices' => $res['prices'], 'min' => $res['min'], 'max' => $res['max'], 'sales' => 0, 'isfaka' => $isfaka, 'stock' => $count, 'stock' => $res['stock_open']);
        }
    }
    $result = array("code" => 0, "msg" => "succ", "data" => $data, "count" => count($data));
    exit(json_encode($result));
} elseif ($act == 'goodslist') {
    $result['code'] = 0;
    if (isset($_POST['cid'])) {
        $cid = intval(daddslashes($_POST['cid']));
    }
    $user = input('user', 1);
    $pass = input('pass', 1);
    if ($user && $pass) {
        $userrow = $DB->get_row("SELECT * FROM `pre_site` WHERE `user` = :user LIMIT 1", [':user' => $user]);
        if ($userrow && checkPwd($pass, $userrow['pwd'], $userrow['salt']) && $userrow['status'] == 1) {
            $isLogin2  = 1;
            $price_obj = new \core\Price($userrow['zid'], $userrow);
        } elseif ($userrow && $userrow['status'] == 0) {
            exit('{"code":-1,"message":"该账户已被封禁","msg":"该账户已被封禁"}');
        } else {
            exit('{"code":-1,"message":"用户名或密码不正确","msg":"用户名或密码不正确"}');
        }
    } else {
        $price_obj = new \core\Price(1);
    }

    if ($cid > 0) {
        $rs = $DB->query("SELECT * FROM `pre_tools` WHERE `cid` = '" . $cid . "'  ORDER BY `sort` ASC");
    } else {
        if (isset($offset)) {
            $rs = $DB->query("SELECT * FROM `pre_tools` ORDER BY `cid` ASC,`sort` ASC limit {$offset},{$pagesize}");
        } else {
            $rs = $DB->query("SELECT * FROM `pre_tools` ORDER BY `cid` ASC,`sort` ASC");
        }
    }

    if ($rs) {
        while ($res = $DB->fetch($rs)) {
            if ($isLogin2 == 1) {
                $price_obj->setToolInfo($res['tid'], $res);
                $price = $price_obj->getBuyPrice($res['tid']);
            } else {
                $price = $price_obj->getToolPrice($res['tid']);
            }
            if ($res['is_curl'] == 4) {
                // 查询缓存12小时
                // if ($res['stock'] <= 0 || $res['stock_time'] <= time()) {
                //     $count      = intval($DB->count("SELECT count(*) FROM pre_faka WHERE `tid`='{$res['tid']}' AND orderid<=0"));
                //     $stock_time = time() + (3600 * 12);
                //     $DB->update("UPDATE `pre_tools` SET `stock`='{$count}',`stock_time`='{$stock_time}' WHERE `tid`='{$res['tid']}'");
                // } else {
                //     $count = intval($res['stock']);
                // }
                $count             = intval($res['stock']);
                $isfaka            = 1;
                $res['input']      = getFakaInput();
                $res['stock_open'] = 1;
            } else {
                if ($res['stock_open'] == 1) {
                    $count = intval($res['stock']);
                } else {
                    //$count = 0;
                    $res['stock_open'] = 0;
                    $count             = null;
                    if ($res['stock'] > 0) {
                        $count             = $res['stock'];
                        $res['stock_open'] = 1;
                    }
                }
                $isfaka = 0;
            }
            $data[] = array('tid' => $res['tid'], 'cid' => $res['cid'], 'sort' => $res['sort'], 'name' => $res['name'], 'value' => $res['value'], 'price' => $price, 'prices' => $res['prices'], 'input' => $res['input'], 'inputs' => $res['inputs'], 'desc' => $res['desc'], 'alert' => $res['alert'], 'shopimg' => $res['shopimg'], 'min' => $res['min'], 'max' => $res['max'], 'close' => $res['active'] == 1 ? 0 : 1, 'active' => $res['active'], 'stock_time' => date('Y-m-d H:i:s', $res['stock_time']), 'isfaka' => $isfaka, 'stock' => $count, 'stock_open' => $res['stock_open']);
        }
        $result['msg']  = 'succ';
        $result['data'] = $data;
    } else {
        $result['code'] = -1;
        $result['msg']  = '获取商品列表失败，' . $DB->error();
    }
    exit(json_encode($result));
} elseif ($act == 'goodsdetails') {
    $result['code'] = 0;
    $result['data'] = [];
    if (isset($_POST['user']) && isset($_POST['pass'])) {
        $user    = input('post.user', 1);
        $pass    = input('post.pass', 1);
        $userrow = $DB->get_row("SELECT * FROM `pre_site` WHERE `user` = :user LIMIT 1", [':user' => $user]);
        if ($userrow && checkPwd($pass, $userrow['pwd'], $userrow['salt']) && $userrow['status'] == 1) {
            $isLogin2  = 1;
            $price_obj = new \core\Price($userrow['zid'], $userrow);
        } elseif ($userrow && $userrow['status'] == 0) {
            exit('{"code":-1,"message":"该账户已被封禁","msg":"该账户已被封禁"}');
        } else {
            exit('{"code":-1,"message":"用户名或密码不正确","msg":"用户名或密码不正确"}');
        }
    } else {
        $price_obj = new \core\Price(1);
    }

    if (isset($_POST['tid'])) {
        $tid  = intval(input('post.tid', 1));
        $tool = $DB->get_row("SELECT * FROM `pre_tools` WHERE `tid` = :tid LIMIT 1", [':tid' => $tid]);
        if ($tool) {
            if ($isLogin2 == 1) {
                $price_obj->setToolInfo($tid, $tool);
                $price = $price_obj->getBuyPrice($tid);
            } else {
                $price = $price_obj->getToolPrice($tid);
            }

            if ($tool['is_curl'] == 4) {
                // 查询缓存10分钟
                if ($tool['stock'] <= 1 || $tool['stock_time'] <= time()) {
                    $count      = intval($DB->count("SELECT count(*) FROM pre_faka WHERE `tid`='{$tool['tid']}' AND orderid<=0"));
                    $stock_time = time() + (600 * 1);
                    $DB->update("UPDATE `pre_tools` SET `stock`='{$count}',`stock_time`='{$stock_time}' WHERE `tid`='{$tool['tid']}'");
                } else {
                    $count = intval($tool['stock']);
                }
                // $count             = intval($res['stock']);
                $tool['input'] = getFakaInput();
                $isfaka        = 1;
            } else {
                if ($res['stock_open'] == 1) {
                    $count = intval($res['stock']);
                } else {
                    //$count = 0;
                    $res['stock_open'] == 0;
                    $count = null;
                }

                if ($res['is_curl'] == 2 && $res['goods_type'] == 1) {
                    // 卡密对接商品
                    $isfaka = 1;
                } else {
                    $isfaka = 0;
                }
            }
            $data           = array('tid' => $tool['tid'], 'cid' => $tool['cid'], 'sort' => $tool['sort'], 'name' => $tool['name'], 'value' => $tool['value'], 'price' => $price, 'prices' => $tool['prices'], 'input' => $tool['input'], 'inputs' => $tool['inputs'], 'desc' => $tool['desc'], 'alert' => $tool['alert'], 'shopimg' => $tool['shopimg'], 'repeat' => $tool['repeat'], 'is_curl' => $tool['is_curl'], 'multi' => $tool['multi'], 'min' => $tool['min'], 'max' => $tool['max'], 'close' => $tool['active'] == 1 ? 0 : 1, 'isfaka' => $isfaka, 'stock_time' => date('Y-m-d H:i:s', $res['stock_time']), 'stock' => $count, 'stock_open' => $res['stock_open']);
            $result['data'] = $data;
        } else {
            $result['code']    = -1;
            $result['msg']     = '该商品不存在！';
            $result['message'] = '该商品不存在！';
        }

    } else {
        if (isset($_POST['cid']) || isset($_GET['cid'])) {
            $cid = intval(input('cid', 1));
        }

        if ($cid > 0) {
            $rs = $DB->query("SELECT * FROM `pre_tools` WHERE `cid` = '" . $cid . "'  ORDER BY `sort` ASC");
        } else {
            $rs = $DB->query("SELECT * FROM `pre_tools` WHERE 1 ORDER BY `cid` ASC,`sort` ASC");
        }

        if ($rs) {
            while ($res = $DB->fetch($rs)) {
                if ($isLogin2 == 1) {
                    $price_obj->setToolInfo($res['tid'], $res);
                    $price = $price_obj->getBuyPrice($res['tid']);
                } else {
                    $price = $res['price'];
                }

                if ($res['is_curl'] == 4) {
                    // 查询缓存6小时
                    if ($res['stock'] <= 0 || $res['stock_time'] <= time()) {
                        $count      = intval($DB->get_column("SELECT count(*) FROM pre_faka WHERE tid= ? AND (orderid is null OR orderid<1 OR `usetime` is null)", [$res['tid']]));
                        $stock_time = time() + (3600 * 6);
                        $DB->update("UPDATE `pre_tools` SET `stock`='{$count}',`stock_time`='{$stock_time}' WHERE `tid`='{$res['tid']}'");
                    } else {
                        $count = intval($res['stock']);
                    }
                    $isfaka = 1;
                } else {
                    if ($res['stock_open'] != 1) {
                        $count = null;
                    } else {
                        $count = $res['stock'];
                    }
                    $isfaka = 0;
                }
                $data[] = array('tid' => $res['tid'], 'cid' => $res['cid'], 'sort' => $res['sort'], 'name' => $res['name'], 'value' => $res['value'], 'price' => $price, 'prices' => $res['prices'], 'input' => $res['input'], 'inputs' => $res['inputs'], 'desc' => $res['desc'], 'alert' => $res['alert'], 'shopimg' => $res['shopimg'], 'repeat' => $res['repeat'], 'multi' => $res['multi'], 'min' => $res['min'], 'max' => $res['max'], 'close' => $tool['active'] == 1 ? 0 : 1, 'active' => $res['active'], 'isfaka' => $isfaka, 'stock' => $count, 'stock_time' => date('Y-m-d H:i:s', $res['stock_time']));
            }
            $result['data'] = $data;
        } else {
            $result['code']    = -1;
            $result['msg']     = '获取商品列表失败，' . $DB->error();
            $result['message'] = '获取商品列表失败，' . $DB->error();
        }
    }

    exit(json_encode($result));
} elseif ($act == 'goodsinfo') {
    $tid = intval(input('tid', 1));
    if ($tid < 1) {
        $result['code'] = -1;
        $result['msg']  = '商品ID不能为空！';
        exit(json_encode($result));
    }

    $result['code'] = 0;
    if (isset($_POST['user']) && isset($_POST['pass'])) {
        $user    = input('user', 1);
        $pass    = input('pass', 1);
        $userrow = $DB->get_row("SELECT * FROM `pre_site` WHERE `user` = :user LIMIT 1", [':user' => $user]);
        if ($userrow && $userrow['user'] == $user && checkPwd($pass, $userrow['pwd'], $userrow['salt']) && $userrow['status'] == 1) {
            $isLogin2  = 1;
            $price_obj = new \core\Price($userrow['zid'], $userrow);
        } elseif ($userrow && $userrow['status'] == 0) {
            exit('{"code":-1,"msg":"该账户已被封禁"}');
        } else {
            exit('{"code":-1,"msg":"用户名或密码不正确"}');
        }
    }

    $row = $DB->get_row("SELECT * FROM `pre_tools` WHERE `tid` = '" . $tid . "' LIMIT 1");

    if ($row) {
        if ($isLogin2 == 1) {
            $price_obj->setToolInfo($row['tid'], $row);
            $price = $price_obj->getBuyPrice($row['tid']);
        } else {
            $price = $row['price'];
        }

        if ($row['is_curl'] == 4) {
            $isfaka = 1;
        } else {
            $isfaka = 0;
        }
        $result['data'] = array('tid' => $row['tid'], 'cid' => $row['cid'], 'name' => $row['name'], 'value' => $row['value'], 'price' => $price, 'prices' => $row['prices'], 'input' => $row['input'], 'inputs' => $row['inputs'], 'desc' => !empty($row['desc']) ? $row['desc'] : '', 'alert' => !empty($row['alert']) ? $row['alert'] : '', 'shopimg' => $row['shopimg'], 'repeat' => $row['repeat'], 'multi' => $row['multi'], 'min' => $row['min'], 'max' => $row['max'], 'close' => $row['close'], 'active' => $row['active'], 'isfaka' => $isfaka);
    } else {
        $result['code'] = -1;
        $result['msg']  = '获取商品列表失败，' . $DB->error();
    }
    exit(json_encode($result));
} elseif ($act == 'pay') {
    $result['code'] = -1;
    $tid            = intval(input('post.tid'));
    if (!$tid) {
        exit('{"code":-1,"message":"商品ID不能为空"}');
    }

    if ($conf['index_run'] != 1) {
        exit('{"code":-1,"message":"网站维护升级中，请稍后再下单！"}');
    }

    $user   = input('post.user', 1);
    $pass   = input('post.pass', 1);
    $input1 = input('post.input1');
    if (empty($input1)) {
        exit('{"code":-1,"message":"首个参数值不能为空"}');
    }

    $input2 = input('post.input2', 1);
    $input3 = input('post.input3', 1);
    $input4 = input('post.input4', 1);
    $input5 = input('post.input5', 1);
    $num    = isset($_POST['num']) && $_POST['num'] > 0 ? intval(input('num')) : 1;
    $tool   = $DB->get_row("SELECT * FROM `pre_tools` WHERE `tid` = ? LIMIT 1", [$tid]);
    if ($tool && $tool['active'] == 1) {
        $tid = (int) $tool['tid'];
        if ($tool['active'] != 1) {
            exit('{"code":-1,"message":"当前商品维护中，停止下单！"}');
        }

        $userrow = $DB->get_row("SELECT * FROM `pre_site` WHERE `user` = ? LIMIT 1", [$user]);
        if ($userrow && checkPwd($pass, $userrow['pwd'], $userrow['salt']) && $userrow['status'] == 1) {
            $result['code'] = 0;
            if (in_array($input1, explode("|", $conf['blacklist']))) {
                exit('{"code":-1,"message":"你的下单账号已被拉黑，无法下单！"}');
            }

            if ($tool['validate'] == 1 && is_numeric($input1)) {
                //if(validate_qzone($input1)==false) exit('{"code":-1,"message":"你的QQ空间设置了访问权限，无法下单！"}');
            }

            if ($tool['is_curl'] == 4) {
                if (!$isLogin2 && $conf['faka_input'] == 0 && !checkEmail($input1)) {
                    exit('{"code":-1,"message":"邮箱格式不正确"}');
                }
                $count = $DB->count("SELECT count(*) FROM pre_faka WHERE tid=:tid and orderid<1", [':tid' => $tid]);
                $nums  = ($tool['value'] > 0 ? $tool['value'] : 1) * $num;
                if ($count <= 0) {
                    exit('{"code":-1,"message":"该商品库存卡密不足，请联系对接站加卡！"}');
                }

                if ($nums > $count) {
                    exit('{"code":-1,"message":"你所购买的数量超过对接站库存数量！"}');
                }
            } elseif ($tool['stock_open'] == 1 && $tool['stock'] < 1) {
                exit('{"code":-1,"message":"该商品库存不足，无法下单！"}');
            }

            if ($tool['multi'] == 0 || $num < 1) {
                $num = 1;
            }

            if ($tool['multi'] == 1 && $tool['min'] > 0 && $num < $tool['min']) {
                exit('{"code":-1,"message":"当前商品最小下单数量为' . $tool['min'] . '"}');
            }

            if ($tool['multi'] == 1 && $tool['max'] > 0 && $num > $tool['max']) {
                exit('{"code":-1,"message":"当前商品最大下单数量为' . $tool['max'] . '"}');
            }

            $isLogin2  = 1;
            $price_obj = new \core\Price($userrow['zid'], $userrow);
            $price_obj->setToolInfo($tid, $tool);
            $price = $price_obj->getBuyPrice($tid);
            if (!empty($tool['prices'])) {
                //批发价
                $price = $price_obj->getFinalPrice($price, $num);
                if (!$price) {
                    exit('{"code":-1,"message":"当前商品批发价格优惠设置不正确"}');
                }
            }

            $need = $price * $num;
            if ($need == 0) {
                exit('{"code":-2,"message":"不支持免费商品对接"}');
            }

            if ($userrow['money'] < $need) {
                exit('{"code":-2,"message":"余额不足，购买此商品还差' . sprintf('%.5f', $need - $userrow['money']) . '元"}');
            }

            $trade_no = date("YmdHis") . rand(111, 999) . 'money';
            $input    = $input1 . ($input2 ? '|' . $input2 : null) . ($input3 ? '|' . $input3 : null) . ($input4 ? '|' . $input4 : null) . ($input5 ? '|' . $input5 : null);
            $sql      = "INSERT INTO `pre_pay` (`trade_no`,`type`,`zid`,`userid`,`tid`,`input`,`num`,`addtime`,`name`,`money`,`siteurl`,`ip`,`status`) VALUES (:trade_no,:type,:zid,:userid,:tid,:input,:num,:addtime,:name,:money,:siteurl,:ip,'0')";
            $param    = [
                ':trade_no' => $trade_no,
                ':type'     => 'rmb',
                ':zid'      => $userrow['zid'],
                ':userid'   => $userrow['zid'],
                ':tid'      => $tid,
                ':input'    => $input,
                ':num'      => $num,
                ':addtime'  => $date,
                ':name'     => $tool['name'],
                ':money'    => $need,
                ':siteurl'  => $clientip,
                ':ip'       => $clientip,
            ];
            if ($DB->query($sql, $param)) {
                if ($DB->query("UPDATE `pre_site` SET `money`=`money`- ? WHERE `zid`= ?", array($need, $userrow['zid']))) {
                    if ($DB->query("UPDATE `pre_pay` SET `status` = 1 WHERE `trade_no` = ?", array($trade_no))) {
                        require_once SYSTEM_ROOT . 'ajax.class.php';
                        $srow = $DB->get_row("SELECT * FROM `pre_pay` WHERE `trade_no` = ?", [$trade_no]);
                        if (is_array($srow)) {
                            $DB->transaction();
                            try {
                                $orderid = processOrderAll($srow);
                                addPointLogs($userrow['zid'], $need, '消费', '通过API接口购买 ' . $tool['name'] . ' X' . $num . '份！', $orderid);
                                $result['code'] = 0;

                                $result['message'] = 'success';
                                $result['orderid'] = $orderid;
                                $djzt              = $DB->get_column("SELECT djzt FROM pre_orders WHERE id = :orderid LIMIT 1", [':orderid' => $orderid]);
                                if ($djzt == 3) {
                                    $rs     = $DB->query("SELECT * FROM pre_faka WHERE tid=:tid AND orderid= :orderid ORDER BY kid ASC", [':tid' => $tid, ':orderid' => $orderid]);
                                    $kmdata = array();
                                    while ($res = $DB->fetch($rs)) {
                                        if (!empty($res['pw'])) {
                                            $kmdata[] = array('card' => $res['km'], 'pass' => $res['pw']);
                                        } else {
                                            $kmdata[] = array('card' => $res['km']);
                                        }
                                    }
                                    $result['faka']   = true;
                                    $result['kmdata'] = $kmdata;
                                }
                                $DB->commit();
                            } catch (\Exception $e) {
                                $DB->rollback();
                                $log = $debug = ' Debug[Code：' . $e->getCode() . '；Line：' . $e->getLine() . '；Traces：';
                                // $list = $e->getTrace();
                                // foreach ($list as $k => $v) {
                                //     if ($k > 0 && $k <= 4 && isset($v['file'])) {
                                //         $debug .= "#{$k} " . str_replace(ROOT, '', $v['file']) . ' => ' . $v['line'];
                                //     }
                                // }
                                // $debug .= ']';
                                if (stripos($e->getMessage(), 'SQLSTATE') !== false) {
                                    $message = '数据库错误，' . $e->getMessage();
                                } else {
                                    $message = $e->getMessage();
                                }
                                addWebLog('对接下单错误', '订单金额：' . $need . '元；代理（' . $userrow['zid'] . '）通过API接口购买 ' . $tool['name'] . ' 共' . $num . '份失败！' . $message . $log . $e->getTraceAsString() . ']', 'Api');
                                $result['message'] = '系统错误: ' . $message;
                            }
                        } else {
                            $result['message'] = '下单失败: 订单创建失败，' . $DB->error();
                        }
                    } else {
                        $result['message'] = '下单失败: 订单修改失败， ' . $DB->error();
                    }
                } else {
                    $result['message'] = '下单失败 : 订单扣款失败，' . $DB->error();
                }
            } else {
                $result['message'] = '下单失败 : 订单生成失败，' . $DB->error();
            }
        } elseif ($userrow && $userrow['status'] == 0) {
            $result['message'] = '该账户已被封禁';
        } else {
            $result['message'] = '用户名或密码不正确';
        }
    } else {
        $result['message'] = '商品ID不存在';
    }
    $result['msg'] = $result['message'];
    exit(json_encode($result, JSON_UNESCAPED_UNICODE));
} elseif ($act == 'query') {
    $qq     = input('qq', 1);
    $page   = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $status = isset($_POST['status']) ? intval($_POST['status']) : -1;

    $limit = 10;
    $start = $limit * ($page - 1);
    if (empty($qq)) {
        if ($status >= 0) {
            $query_sql  = "SELECT * FROM pre_orders WHERE `userid`=:uid AND `status`=:status order by id desc limit :pagestart, :pagesize";
            $query_data = array(
                ':uid'       => $isLogin2 === 1 ? $userrow['zid'] : $cookiesid,
                ':status'    => $status,
                ':pagestart' => $start,
                ':pagesize'  => $limit,
            );
            $query_data2 = $query_data;
            unset($query_data2[':pagestart']);
            unset($query_data2[':pagesize']);
            $total = $DB->count("SELECT count(*) FROM pre_orders WHERE `userid`=:uid AND `status`=:status", $query_data2);
        } else {
            $query_sql  = "SELECT * FROM pre_orders WHERE `userid`=:uid order by id desc limit :pagestart, :pagesize";
            $query_data = array(
                ':uid'       => $isLogin2 === 1 ? $userrow['zid'] : $cookiesid,
                ':pagestart' => $start,
                ':pagesize'  => $limit,
            );
            $query_data2 = $query_data;
            unset($query_data2[':pagestart']);
            unset($query_data2[':pagesize']);
            $total = $DB->count("SELECT count(*) FROM pre_orders WHERE `userid`=:uid", $query_data2);
        }

    } else {
        $query_data = [];
        if ($conf['query_checkcookie'] == 1) {
            $query_data[':uid'] = $isLogin2 === 1 ? $userrow['zid'] : $cookiesid;
        }

        if ($conf['query_orderid'] == 1) {
            //支持订单ID查询
            $query_data = array_merge($query_data, array(
                ':payorder'  => $qq,
                ':id'        => $qq,
                ':input'     => $qq,
                ':input2'    => $qq,
                ':input3'    => $qq,
                ':input4'    => $qq,
                ':input5'    => $qq,
                ':pagestart' => $start,
                ':pagesize'  => $limit,
            ));
            $sql_where = '(payorder=:payorder OR id=:id OR input=:input OR input2=:input2 OR input3=:input3 OR input4=:input4 OR input5=:input5)';
        } else {
            if (is_numeric($qq) && $qq < 10000) {
                json_error("请输入正确的查询条件！");
            }
            $query_data = array_merge($query_data, array(
                ':payorder'  => $qq,
                ':input'     => $qq,
                ':input2'    => $qq,
                ':input3'    => $qq,
                ':input4'    => $qq,
                ':input5'    => $qq,
                ':pagestart' => $start,
                ':pagesize'  => $limit,
            ));
            $sql_where = '(payorder=:payorder OR input=:input OR input2=:input2 OR input3=:input3 OR input4=:input4 OR input5=:input5)';
        }

        if ($status >= 0) {
            $sql_where .= ' AND `status`=:status';
            $query_data = array_merge($query_data, array(
                ':status' => $status,
            ));
        }

        $query_data2 = $query_data;
        unset($query_data2[':pagestart']);
        unset($query_data2[':pagesize']);
        if ($conf['query_checkcookie'] == 1) {
            //userid缓存安全验证
            $query_sql = "SELECT * FROM pre_orders WHERE `userid`=:uid AND {$sql_where} order by id desc limit :pagestart, :pagesize";
            $total     = $DB->count("SELECT count(*) FROM pre_orders WHERE `userid`=:uid AND {$sql_where}", $query_data2);
        } else {
            $query_sql = "SELECT * FROM pre_orders WHERE {$sql_where} order by id desc limit :pagestart, :pagesize";
            $total     = $DB->count("SELECT count(*) FROM pre_orders WHERE {$sql_where}", $query_data2);
        }
    }

    try {
        $rows = $DB->select($query_sql, $query_data);
        if (!is_array($rows)) {
            throw new \Exception($DB->error());
        }
        $data  = array();
        $count = 0;
        if (is_array($rows)) {
            $count = count($rows);
            foreach ($rows as $key => $res) {
                $tool   = $DB->get_row("SELECT * FROM pre_tools where tid=:tid limit 1", array(':tid' => $res['tid']));
                $skey   = getOrderSkey($res, 'get');
                $data[] = array('id' => $id, 'tid' => $res['tid'], 'input' => $res['input'], 'name' => is_array($tool) ? $tool['name'] : '该商品已被删除或不存在', 'value' => $res['value'], 'money' => $res['money'], 'addtime' => $res['addtime'], 'endtime' => $res['endtime'], 'result' => $res['result'], 'status' => $res['status'], 'is_curl' => $tool['is_curl'], 'skey' => $skey, 'payorder' => $res['payorder'], 'shopimg' => $res['shopimg']);
                if ($isLogin2 === 1 && $userrow['zid'] && $res['userid'] === $cookiesid) {
                    //将订单所属更新为当前已登陆用户
                    $DB->query("UPDATE `pre_orders` SET `userid`=:userid where id=:id", [':userid' => $userrow['zid'], ':id' => $res['id']]);
                }
            }
            if ($page > 1 && $count == 0) {
                exit('{"code":-1,"msg":"没有更多订单了"}');
            }
            $result = array("code" => 0, "msg" => "succ", "count" => $count, "total" => intval($total), "content" => $qq, "page" => $page, "isnext" => ($count == $limit ? true : false), "islast" => ($page > 1 ? true : false), "data" => $data);
        } else {
            $result = array("code" => -1, "msg" => "查询订单失败，" . $DB->error(), "content" => $qq, "page" => $page, "isnext" => false, "islast" => false, "data" => []);
        }
    } catch (\PDOException $e) {
        $result = array("code" => -1, "msg" => "查询订单失败，" . $e->getMessage(), "content" => $qq, "page" => $page, "isnext" => false, "islast" => false, "data" => []);
    }
    exit(json_encode($result));
} elseif ($act == 'search') {
    $result['code'] = -1;
    $id             = intval(input('get.id', 1));
    $skey           = input('get.skey', 1);
    if ($conf['api_check_skey'] == 1) {
        if (empty($skey) || strlen($skey) !== 32) {
            exit('{"code":-1,"msg":"skey参数不正确"}');
        }
    }

    $row     = $DB->get_row("SELECT * FROM `pre_orders` WHERE `id` = :id LIMIT 1", [':id' => $id]);
    $orderid = $id;
    if ($row) {
        if ($conf['api_check_skey'] == 1 && getOrderSkey($row) !== $skey) {
            exit('{"code":-2,"msg":"skey参数验证失败"}');
        }

        $tool          = $DB->get_row("select * from pre_tools where tid=:tid limit 1", [':tid' => $row['tid']]);
        $InfoControler = new \core\InfoControler();
        if ($tool['is_curl'] == 2) {
            $shequ = $DB->get_row("select * from pre_shequ where id=:id limit 1", [':id' => $tool['shequ']]);
            if ($shequ['type'] == 4) {
                $list = $InfoControler->query_jiuliu($row, $shequ);
            } elseif ($shequ['type'] == 23) {
                $list = $InfoControler->query_chengzi($row, $shequ);
            } elseif ($shequ['type'] == 24) {
                $list = $InfoControler->query_guakebao($row, $shequ);
            } else {
                $list = $InfoControler->query_extend($row, $shequ);
            }

            if ($list['order_state'] == '已完成' && $row['status'] == 2) {
                $DB->query("UPDATE `pre_orders` SET `status`=1 WHERE `id` = :id LIMIT 1", [':id' => $id]);
            }

            if (is_array($list)) {
                $list['code']    = 0;
                $list['message'] = 'success';
                $result          = $list;
            } else {
                $result['code']    = -1;
                $result['message'] = '获取数据失败';
                $result['data']    = $list;
            }
        } else {
            $result = $InfoControler->query_this($row, $tool);
        }
        $result['status'] = $row['status'];
    } else {
        $result['message'] = '订单不存在';
    }
    $result['msg'] = $result['message'];
} elseif ($act == 'siteinfo') {
    $count1 = $DB->count("SELECT count(*) from pre_orders");
    $count2 = $DB->count("SELECT count(*) from pre_orders where status>=1");
    $count3 = $DB->count("SELECT count(*) from pre_site");
    $result = array('sitename' => $conf['sitename'], 'kfqq' => $conf['qq'] ? $conf['qq'] : $conf['kfqq'], 'anounce' => $conf['anounce'], 'modal' => $conf['modal'], 'bottom' => $conf['bottom'], 'alert' => $conf['alert'], 'gg_search' => $conf['gg_search'], 'gg_panel' => $conf['gg_panel'], 'version' => VERSION, 'build' => $conf['build'], 'orders' => $count1, 'orders1' => $count2, 'sites' => $count3, 'appalert' => $conf['appalert']);
} elseif ($act == 'token') {
    // $key = isset($_GET['key'])?$_GET['key']:exit('No key');
    // $result=array('token'=>get_app_token($key),'time'=>time());
    $result = array("code" => -5, "msg" => "待开发");
} elseif ($act == 'getClass') {
    $classhide = explode(',', $siterow['class']);
    $rs        = $DB->query("SELECT * FROM `pre_class` where (`upcid` is null OR `upcid`=0) order by sort asc");
    $data      = array();
    while ($res = $DB->fetch($rs)) {
        $res['count'] = Db::name('tools')->where("cid='" . $res['cid'] . "'")->count('tid');
        $subClass     = Db::name('class')->where("upcid='" . $res['cid'] . "'")->count('id');
        if ($subClass > 0) {
            $rs2 = Db::name('class')->where("upcid='" . $res['cid'] . "'")->order("sort ASC")->select();
            foreach ($rs2 as $key2 => $res2) {
                if ($is_fenzhan && in_array($res['cid'], $classhide)) {
                    continue;
                }
                $res2['count'] = Db::name('tools')->where("cid='" . $res2['cid'] . "'")->count('tid');
                $data[]        = $res2;
            }
        } else {
            if ($is_fenzhan && in_array($res['cid'], $classhide)) {
                continue;
            }
            $data[] = $res;
        }
    }
    $result = array("code" => 0, "msg" => "succ", "data" => $data);
    exit(json_encode($result));
} elseif ($act == 'getTools') {
    $rs   = $DB->query("SELECT * FROM pre_tools order by tid asc");
    $data = array();
    while ($res = $DB->fetch($rs)) {
        if (isset($price_obj)) {
            $price_obj->setToolInfo($res['tid'], $res);
            if ($price_obj->getToolDel($res['tid']) == 1) {
                continue;
            }

            $price = $price_obj->getToolPrice($res['tid']);
        } else {
            $price = $res['price'];
        }

        if ($res['is_curl'] == 4) {
            $isfaka       = 1;
            $res['input'] = getFakaInput();
        } else {
            $isfaka = 0;
        }
        $data[$res['tid']] = array('cid' => $res['cid'], 'tid' => $res['tid'], 'sort' => $res['sort'], 'name' => $res['name'], 'price' => $price, 'status' => ($res['close'] == 1 ? 0 : 1), 'prid' => $res['prid']);
    }
    $result = array("code" => 0, "msg" => "succ", "data" => $data);
    exit(json_encode($result));
} elseif ($act == 'addRmb') {
    $rmb    = round(daddslashes($_POST['rmb']), 2);
    $zid    = (int) daddslashes($_POST['zid']);
    $apikey = trim(strip_tags(daddslashes($_POST['apikey'])));
    if (empty($conf['apikey'])) {
        exit(json_encode(array("code" => -1, "msg" => "未配置api密钥，请填写后再试！")));
    }

    if (md5($apikey) !== md5($conf['apikey'])) {
        echo json_encode(array("code" => -1, "msg" => "操作失败，api密钥错误！"));
        exit(0);
    } else {
        if ($rmb <= 0) {
            exit(json_encode(array("code" => -1, "msg" => "单笔加款金额不能小于0.01")));
        } else if ($zid < 1) {
            exit(json_encode(array("code" => -1, "msg" => "分站ZID不正确！")));
        } else {
            $site = $DB->get_row("select * from pre_site where zid= ? limit 1", array($zid));
            if ($site) {
                $sql = "update pre_site set `money`=`money`+ ? WHERE zid= ?";
                if ($DB->query($sql, array($rmb, $zid))) {
                    addPointLogs(1, $rmb, '充值', '通过API接口给代理（' . $zid . '）充值 ' . $rmb . '元成功');
                    $result = array("code" => 0, "msg" => "成功给分站" . $zid . "加款" . $rmb . "元，当前余额" . ($site['money'] + $rmb) . "元");
                } else {
                    addPointLogs(1, $rmb, '充值', '通过API接口给代理（' . $zid . '）充值 ' . $rmb . '元失败，' . $DB->error());
                    $result = array("code" => -1, "msg" => "加款失败！[0]" . $DB->error());
                }
            } else {
                $result = array("code" => -1, "msg" => "加款失败！当前分站不存在");
            }
            exit(json_encode($result));
        }
    }
} elseif ($act == 'removeRmb') {
    $rmb    = round(daddslashes($_POST['rmb']), 2);
    $zid    = (int) daddslashes($_POST['zid']);
    $apikey = trim(strip_tags(daddslashes($_POST['apikey'])));
    if (empty($conf['apikey'])) {
        exit(json_encode(array("code" => -1, "msg" => "未配置api密钥，请在主站后台的核心设置里面填写后再试！")));
    }

    if (md5($apikey) !== md5($conf['apikey'])) {
        echo json_encode(array("code" => -1, "msg" => "操作失败，api密钥错误！"));
        exit(0);
    } else {
        if ($rmb <= 0) {
            exit(json_encode(array("code" => -1, "msg" => "单笔扣款金额不能小于0.01")));
        } else if ($zid < 1) {
            exit(json_encode(array("code" => -1, "msg" => "分站ZID不正确！")));
        } else {
            $site = $DB->get_row("select * from pre_site where zid= ? limit 1", array($zid));
            if ($site) {
                $sql = "update pre_site set `money`=`money`- ? WHERE zid= ?";
                if ($DB->query($sql, array($rmb, $zid))) {
                    addPointLogs(1, $rmb, '扣款', '通过API接口给代理（' . $zid . '）扣除余额 ' . $rmb . '元');
                    $result = array("code" => 0, "msg" => "成功给分站" . $zid . "扣款" . $rmb . "元，当前余额" . ($site['money'] + $rmb) . "元");
                } else {
                    addPointLogs(1, $rmb, '扣款', '通过API接口给代理（' . $zid . '）扣除余额 ' . $rmb . '元');
                    $result = array("code" => -1, "msg" => "扣款失败！[1]" . $DB->error());
                }
            } else {
                $result = array("code" => -1, "msg" => "扣款失败！当前分站不存在");
            }
            exit(json_encode($result));
        }
    }
} elseif ($act == 'changeSite') {
    $status = (int) daddslashes($_POST['status']);
    $zid    = (int) daddslashes($_POST['zid']);
    $apikey = trim(daddslashes($_POST['apikey']));
    if (empty($conf['apikey'])) {
        exit(json_encode(array("code" => -1, "msg" => "未配置api密钥，请在主站后台的核心设置里面填写后再试！")));
    }

    if (md5($apikey) !== md5($conf['apikey'])) {
        exit(json_encode(array("code" => -1, "msg" => "操作失败，api密钥错误！")));
    } else {
        if ($zid < 1) {
            exit(json_encode(array("code" => -1, "msg" => "分站ZID不正确~")));
        } else {
            $site = $DB->get_row("select * from pre_site where zid= ? limit 1", array($zid));
            if ($site) {
                $sql = "update pre_site set status= ? WHERE zid= ?";
                if ($DB->query($sql, array($status, $zid))) {
                    $result = array("code" => 0, "msg" => ($status == 1 ? '开启' : '禁用') . "分站操作成功");
                } else {
                    $result = array("code" => -1, "msg" => "操作失败！[0]" . $DB->error());
                }
            } else {
                $result = array("code" => -1, "msg" => "操作失败！当前分站不存在~");
            }
            exit(json_encode($result));
        }
    }
} elseif ($act == 'getToolsAll') {
    $rs   = $DB->query("SELECT * FROM pre_tools order by sort asc");
    $data = array();
    while ($res = $DB->fetch($rs)) {
        if (isset($price_obj)) {
            $price_obj->setToolInfo($res['tid'], $res);
            $price = $price_obj->getToolPrice($res['tid']);
        } else {
            $price = $res['price'];
        }

        $data[] = array('cid' => $res['cid'], 'tid' => $res['tid'], 'name' => $res['name'], 'price' => $price, 'price1' => $res['price1'], 'status' => $res['active']);
    }

    $result = array("code" => 0, "msg" => "succ", "data" => $data);
    exit(json_encode($result));
} elseif ($act == 'getCount') {
    $thtime    = date("Y-m-d") . ' 00:00:00';
    $count1    = $DB->count("SELECT count(*) from pre_orders");
    $count2    = $DB->count("SELECT count(*) from pre_orders where status=1");
    $count3    = $DB->count("SELECT count(*) from pre_orders where status=0");
    $count4    = $DB->count("SELECT count(*) from pre_orders where addtime>='$thtime'");
    $count5    = $DB->count("SELECT sum(money) from pre_pay where tid!=-1 and addtime>='$thtime' and status=1");
    $strtotime = strtotime($conf['build']); //获取开始统计的日期的时间戳
    $now       = time(); //当前的时间戳
    $yxts      = ceil(($now - $strtotime) / 86400); //取相差值然后除于24小时(86400秒)

    $count6 = $DB->count("SELECT count(*) from pre_site");
    $count7 = $DB->count("SELECT count(*) from pre_site where addtime>='$thtime'");
    $count8 = $DB->count("SELECT sum(point) from pre_points where action='提成' and addtime>='$thtime'");

    $count11 = $DB->count("SELECT sum(realmoney) FROM `pre_tixian` WHERE `status` = 0");

    $count12 = $DB->count("SELECT sum(money) FROM `pre_pay` WHERE `type` = 'qqpay' AND `addtime` > '$thtime' AND `status` = 1");
    $count13 = $DB->count("SELECT sum(money) FROM `pre_pay` WHERE `type` = 'wxpay' AND `addtime` > '$thtime' AND `status` = 1");
    $count14 = $DB->count("SELECT sum(money) FROM `pre_pay` WHERE `type` = 'alipay' AND `addtime` > '$thtime' AND `status` = 1");

    $result = array("code" => 0, "yxts" => $yxts, "count1" => intval($count1), "count2" => intval($count2), "count3" => intval($count3), "count4" => intval($count4), "count5" => round($count5, 2), "count6" => intval($count6), "count7" => intval($count7), "count8" => round($count8, 2), "count9" => round($count9, 2), "count10" => round($count10, 2), "count11" => round($count11, 2), "count12" => round($count12, 2), "count13" => round($count13, 2), "count14" => round($count14, 2));
    exit(json_encode($result));
} else {
    $result = array("code" => -5, "msg" => "No Act!");
}
if (!isset($result['message'])) {
    $result['message'] = $result['msg'];
} else {
    $result['msg'] = $result['message'];
}
echo json_encode($result);
$DB->close();
