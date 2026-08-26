<?php
// +----------------------------------------------------------------------
// | Author: 星河
// +----------------------------------------------------------------------
// | Date: 2020/05/26
// +----------------------------------------------------------------------
namespace core;

use core\Card;

/**
 * 商品综合处理类
 */
class InfoControler extends Card
{
    /**
     * 初始化
     * @return InfoControler
     */
    public function __construct()
    {
        return true;
    }

    /**
     * 模拟访问函数
     * @url 请求地址
     * @post POST数据
     * @referer 来源地址
     * @cookie 请求缓存
     * @proxy 是否启用代理
     * @timeout 超时时间 默认60秒
     * @header 是否返回header内容
     * @nobaody 是否不返回body内容
     */

    public function get_curl($url, $post = 0, $referer = 0, $cookie = 0, $proxy = 0, $timeout = 15, $header = 0, $nobaody = 0)
    {
        global $conf;
        $server_hash = md5($_SERVER["SERVER_SOFTWARE"] . $_SERVER["SERVER_ADDR"]);
        $ch          = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if ($proxy && $server_hash == $conf["server_hash"] && function_exists('getProxyIp')) {
            curl_setopt($ch, CURLOPT_PROXY, getProxyIp() . ":6759");
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, "chenmds:123456");
        } elseif ($server_hash == $conf["server_hash"] && $conf["proxy"] == 2 && $proxy) {
            if (!empty($conf["proxy_host"]) && !empty($conf["proxy_port"])) {
                curl_setopt($ch, CURLOPT_PROXY, $conf["proxy_host"] . ":" . $conf["proxy_port"]);
            }

            if (!empty($conf["proxy_user"]) && !empty($conf["proxy_pwd"])) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $conf["proxy_user"] . ":" . $conf["proxy_pwd"]);
            }
        }

        $httpheader[] = "Accept: */*";
        $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
        $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
        $httpheader[] = "Connection: close";
        $httpheader[] = "Content-Type:application/x-www-form-urlencoded; charset=UTF-8";

        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);

        if ($header) {
            curl_setopt($ch, CURLOPT_HEADER, true);
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
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 UBrowser/6.2.4098.3 Safari/537.36');

        if ($nobaody) {
            curl_setopt($ch, CURLOPT_NOBODY, 1);
        }

        //强制协议为1.0
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        //强制使用IPV4协议解析域名  新加新加
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //允许重定向
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //重定向最多3次
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        $ret = curl_exec($ch);
        if (trim($ret) == '') {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode != 200 && !preg_match('/^3[\d]{2}$/', $httpCode) && $httpCode !== 408) {
                $error = curl_error($ch);
                if (strpos($error, 'time')) {
                    $ret = '[' . $httpCode . ']站点访问请求超时502';
                } else {
                    $ret = '[' . $httpCode . ']' . $error;
                }
            } else {
                $ret = '[' . $httpCode . ']该站点存在服务器请求拦截或数据拦截，未返回任何内容';
            }
        }
        curl_close($ch);
        return $ret;
    }

    public function chenm_curl($url, $post = 0, $referer = 0, $cookie = 0, $outHeader = 0, $inputHeader = array(), $nobaody = 0, $proxy = 0)
    {
        //返回CURL句柄
        global $conf;
        $server_hash = md5($_SERVER["SERVER_SOFTWARE"] . $_SERVER["SERVER_ADDR"]);
        $ch          = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if ($server_hash == $conf["server_hash"] && $conf["proxy"] == 1 && $proxy) {
            curl_setopt($ch, CURLOPT_PROXY, "121.42.140.77:6759");
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, "chenmds:123456");
        } elseif ($server_hash == $conf["server_hash"] && $conf["proxy"] == 2 && $proxy) {
            if (!empty($conf["proxy_host"]) && !empty($conf["proxy_port"])) {
                curl_setopt($ch, CURLOPT_PROXY, $conf["proxy_host"] . ":" . $conf["proxy_port"]);
            }

            if (!empty($conf["proxy_user"]) && !empty($conf["proxy_pwd"])) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $conf["proxy_user"] . ":" . $conf["proxy_pwd"]);
            }
        }

        $httpheader[] = "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*;q=0.8";
        $httpheader[] = "Accept-Encoding: gzip, deflate";
        $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
        $httpheader[] = "Connection: close";
        $httpheader[] = "Content-Type:application/x-www-form-urlencoded";
        $httpheader[] = "X-Requested-With:XMLHttpRequest";

        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        if (count($inputHeader) > 0) {
            $httpheader = array_merge($httpheader, $inputHeader);
        } else {
            exit(json_encode($httpheader));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);

        if ($outHeader) {
            curl_setopt($ch, CURLOPT_HEADER, true);
        }

        if ($cookie) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }

        if ($referer) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }

        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 UBrowser/6.2.4098.3 Safari/537.36');

        if ($nobaody) {
            curl_setopt($ch, CURLOPT_NOBODY, 1);
        }
        //允许重定向
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //重定向最多3次
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        return $ch;
    }

    public function writeLogs($msg, $filename = 'allLogs.txt')
    {
        $dirname = ROOT . "includes/core/logs/";
        if (!is_dir($dirname)) {
            @mkdir($dirname);
        }

        $filepath = $dirname . $filename;
        $fp       = fopen($filepath, "a");
        flock($fp, LOCK_EX);
        fwrite($fp, "-------------------------------\n" . date("Y-m-d H:i:s") . "\n" . $msg . "\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * 同系统价格监控
     *
     * @param array $shequ
     * @param string $cids
     * @return array
     */
    public function pricejk_this($shequ = [], $cids = '')
    {
        global $DB, $conf;
        $success  = 0;
        $warnlist = '';

        $rs = [];
        if ($cids != '') {
            $rs = $DB->select("SELECT * FROM cmy_tools WHERE shequ='{$shequ['id']}' AND `is_curl`=2 and cid IN ({$cids})");
        }

        if ($rs && count($rs) > 0) {
            $url           = $shequ['url'] . 'api.php?act=goodslist';
            $breakMaxPrice = $conf['cron_maxprice'] ? $conf['cron_maxprice'] : '1888';
            $password      = strSafeEnCode($shequ['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
            $data          = [
                'user' => $shequ['username'],
                'pass' => $password,
            ];
            $result = $this->get_curl($url, http_build_query($data), 0, 0, 0, 20);
            $json   = json_decode($result, true);
            if (is_array($json)) {
                if ($json['code'] == 0) {
                    $list = [];
                    foreach ($json['data'] as $row) {
                        $list[$row['tid']] = $row;
                    }

                    $num = count($rs);
                    foreach ($rs as $key => $res2) {
                        if ($res2['price'] >= $breakMaxPrice) {
                            continue;
                        }

                        if (array_key_exists($res2['goods_id'], $list)) {
                            if ($res2['value'] < 1) {
                                $res2['value'] = 1;
                            }
                            $price1     = floatval($list[$res2['goods_id']]['price'] * $res2['value']);
                            $active     = intval($list[$res2['goods_id']]['active']);
                            $min        = intval($list[$res2['goods_id']]['min']);
                            $max        = intval($list[$res2['goods_id']]['max']);
                            $desc       = $list[$res2['goods_id']]['desc'];
                            $desc       = addslashes(stripslashes($desc));
                            $shopimg    = addslashes($list[$res2['goods_id']]['shopimg']);
                            $stock      = intval($list[$res2['goods_id']]['stock']);
                            $stock_open = $list[$res2['goods_id']]['stock_open'];

                            $this->setToolActive($active, $res2['tid']);

                            $this->setToolPrice($price1, $res2);

                            if ($res2['max'] > $max) {
                                $sql = ",`max`='" . $max . "'";
                            }
                            if ($conf['corn_desc'] == 1 && $desc) {
                                $sql = ",`desc`='" . $desc . "'";
                            }

                            if ($conf['corn_desc'] == 1 && $desc) {
                                $sql = ",`desc`='" . $desc . "'";
                            }

                            if ($conf['corn_stock'] == 1) {
                                if ($stock == null) {
                                    $stock_open = 0;
                                }
                                $sql .= ",`stock_open`='" . $stock_open . "',`stock`='" . intval($stock) . "'";
                            }

                            if ($conf['corn_shopimg'] == 1 && $shopimg) {
                                if (stripos($shopimg, 'http') === false) {
                                    $shopimg = $shequ['url'] . trim($shopimg, '/');
                                }
                                $sql .= ",`shopimg`='" . $shopimg . "'";
                            }
                            $sql = "UPDATE `pre_tools` set `price1`='" . $price1 . "'{$sql} where tid='" . $res2['tid'] . "'";
                            addWebLog('价格同步日志', '系统：' . getShequTypeName($shequ['type']) . '；执行语句：' . $sql, 'Cron');
                            $DB->query($sql);
                            $success++;
                        } else {
                            $success++;
                            //商品不存在，直接下架
                            $this->setToolActive(0, $res2['tid']);
                        }
                    }
                    return array('code' => 0, "msg" => '共' . $num . '个商品，成功更新' . $success . '个', "success" => $success, "warnlist" => $warnlist);
                } else {
                    $msg = isset($json['message']) ? $json['message'] : $json['msg'];
                    return array('code' => -1, "msg" => '商品数据查询失败，' . $msg, "success" => 0);
                }
            } else {
                if (mb_strlen($result) > 500) {
                    $result = substr($result, 50, 1000);
                }
                return array('code' => -1, "msg" => '获取商品数据解析失败！返回：' . htmlspecialchars($result), "success" => 0);
            }
        } else {
            return array('code' => -1, "msg" => '对接该货源站的本地商品数据为空或查询失败', "success" => 0);
        }
    }

    public function pricejk_caihon($shequ, $cids = '')
    {
        global $DB, $conf;
        $success  = 0;
        $warnlist = '';

        $rs = [];
        if ($cids != '') {
            $rs = $DB->select("SELECT * FROM cmy_tools WHERE shequ='{$shequ['id']}' AND is_curl=2 and cid IN ({$cids})");
        }

        if ($rs && count($rs) > 0) {
            $url           = $shequ['url'] . 'api.php?act=goodslist';
            $breakMaxPrice = $conf['cron_maxprice'] ? $conf['cron_maxprice'] : '1888';
            $data          = array(
                'user' => $shequ['username'],
                'pass' => strSafeEnCode($shequ['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1'),
            );
            $result = $this->get_curl($url, http_build_query($data), 0, 0, $shequ['proxy'], 10);
            $json   = json_decode($result, true);
            if (is_array($json)) {
                if ($json['code'] == 0) {
                    $list = [];
                    foreach ($json['data'] as $row) {
                        $list['' . $row['tid']] = $row;
                    }

                    $num = count($rs);
                    foreach ($rs as $key => $res2) {
                        if ($res2['price'] >= $breakMaxPrice) {
                            continue;
                        }

                        if (array_key_exists($res2['goods_id'], $list)) {
                            if ($res2['value'] < 1) {
                                $res2['value'] = 1;
                            }
                            $item         = $list[$res2['goods_id']];
                            $price1       = floatval($item['price'] * $res2['value']);
                            $goods_status = intval($item['close']);
                            $min          = intval($item['min']);
                            $max          = intval($item['max']);
                            $stock        = isset($item['stock']) ? intval($item['stock']) : null;
                            $shopimg      = addslashes($item['shopimg']);
                            $desc         = $item['desc'];
                            $desc         = addslashes(stripslashes($desc));
                            if ($max > 0) {
                                $max = floor($max / $res2['value']);
                            }

                            if ($conf['cron_status_sync'] != 1) {
                                $this->setToolActive($goods_status == 1 ? 0 : 1, $res2['tid']);
                            }

                            if ($price1 > 0) {
                                $this->setToolPrice($price1, $res2);
                            }

                            $sql = "`price1`='{$price1}'";

                            if ($res2['max'] != $max) {
                                $sql .= ",`max`='" . $max . "'";
                            }

                            if ($conf['corn_desc'] == 1 && $desc) {
                                $sql .= ",`desc`='" . $desc . "'";
                            }
                            if ($conf['corn_desc'] == 1 && $stock != null) {
                                $sql .= ",`stock_open`='1', `stock`='" . intval($stock) . "'";
                            }

                            if ($conf['corn_shopimg'] == 1 && $shopimg) {
                                if (stripos($shopimg, 'http') === false) {
                                    $shopimg = $shequ['url'] . trim($shopimg, '/');
                                }
                                $sql .= ",`shopimg`='" . $shopimg . "'";
                            }
                            $sql = "UPDATE `pre_tools` SET {$sql} where tid='" . $res2['tid'] . "'";
                            addWebLog('价格同步日志', '系统：' . getShequTypeName($shequ['type']) . '；执行语句：' . $sql, 'Cron');
                            $success++;
                            $DB->query($sql);
                        } else {
                            //商品不存在，下架
                            $success++;
                            $this->setToolActive(0, $res2['tid']);
                        }
                    }
                    return array('code' => 0, "msg" => '共' . $num . '个商品，成功更新' . $success . '个', "success" => $success, "warnlist" => $warnlist);
                } else {
                    $msg = isset($json['message']) ? $json['message'] : $json['msg'];
                    return array('code' => -1, "msg" => '商品数据查询失败，' . $msg, "success" => 0);
                }
            } else {
                if (mb_strlen($result) > 500) {
                    $result = substr($result, 50, 1000);
                }
                return array('code' => -1, "msg" => '获取商品数据解析失败！返回：' . htmlspecialchars($result), "success" => 0);
            }
        } else {
            return array('code' => -1, "msg" => '对接该货源站的本地商品数据为空或查询失败', "success" => 0);
        }
    }
    /**
     * 优云宝价格监控
     * @param  [int] $shequid 社区
     * @param  [int] $cid     分类ID
     * @return [array]        同步结果信息
     */
    public function pricejk_youyunbao($shequ, $cids = '')
    {
        global $DB, $CACHE, $conf;
        $success  = 0;
        $warnlist = '';

        if ($shequ['type'] != 15) {
            return array('code' => -1, "msg" => '该货源站的网站类型不是【优云宝】，无法执行更新', "success" => 0);
        }
        $password = strSafeEnCode($shequ['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
        $paypwd   = strSafeEnCode($shequ['paypwd'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
        if (empty($paypwd)) {
            return array('code' => -1, "msg" => '该货源站未填写二级密码或对接密码，无法进行执行更新价格！', "success" => 0);
        }
        $rs = [];
        if ($cids != '') {
            $rs = $DB->select("SELECT * FROM cmy_tools WHERE shequ='{$shequ['id']}' AND is_curl=2 and cid IN ({$cids})");
        }

        if ($rs && count($rs) > 0) {
            $url           = $shequ["url"] . "home/api";
            $breakMaxPrice = $conf['cron_maxprice'] ? $conf['cron_maxprice'] : '1888';
            $token         = md5('user=' . $shequ['username'] . '&&pass=' . md5($paypwd));
            $post          = [
                'user'  => $shequ['username'],
                'pass'  => md5($password),
                'code'  => '1013',
                'token' => $token,
                'spid'  => '1',
            ];
            $arr = $CACHE->read($shequ['url'] . '.tools');
            if (is_array($arr)) {
                $json = @json_decode($arr['v'], true);
            }
            if (!isset($json) || !is_array($json)) {
                $result = $this->get_curl($url, http_build_query($post), 0, 0, $shequ['proxy'], 10);
                $json   = json_decode($result, true);
                if (is_array($json)) {
                    $CACHE->save($shequ["url"] . '.tools', $result, time() + 300, 'string');
                }
            }
            if (is_array($json)) {
                if ($json['code'] == 0) {
                    $list = [];
                    foreach ($json['data'] as $row) {
                        $list['' . $row['id']] = $row;
                    }

                    $num = count($rs);
                    foreach ($rs as $key => $res2) {
                        if ($res2['price'] >= $breakMaxPrice) {
                            continue;
                        }
                        $goods_id = 0;
                        if (is_numeric($res2['goods_param'])) {
                            $goods_id = $res2['goods_param'];
                        } elseif (preg_match('/p\/id\/([\d]+)/', $res2['goods_param'], $match)) {
                            $goods_id = $match[1];
                        }

                        if (array_key_exists($goods_id, $list)) {
                            if ($res2['value'] < 1) {
                                $res2['value'] = 1;
                            }

                            $price1 = floatval($list[$goods_id]['money'] * $res2['value']);

                            if (isset($price1) && $price1 > 0) {
                                $this->setToolPrice($price1, $res2);
                            }
                            $success++;
                        } else {
                            //商品不存在，下架
                            $success++;
                            $this->setToolActive(0, $res2['tid']);
                        }
                    }
                    return array('code' => 0, "msg" => '共' . $num . '个商品，成功更新' . $success . '个', "success" => $success, "warnlist" => $warnlist);
                } else {
                    $msg = isset($json['message']) ? $json['message'] : $json['msg'];
                    return array('code' => -1, "msg" => '商品数据查询失败，' . $msg, "success" => 0);
                }
            } else {
                $result = substr($result, 50, 1000);
                return array('code' => -1, "msg" => '获取商品数据解析失败！返回：' . $result, "success" => 0);
            }
        } else {
            return array('code' => -1, "msg" => '对接该货源站的本地商品数据为空或查询失败', "success" => 0);
        }
    }

    public function pricejk_kayixin($shequ, $cids = '')
    {
    }

    public function pricejk_jiuwu($shequ, $cids = '')
    {
        global $DB, $conf;
        $success           = 0;
        $warn              = 0;
        $shequ['password'] = strSafeEnCode($shequ['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
        $url               = $shequ['url'] . 'index.php?m=home&c=api&a=user_get_goods_lists_details&Api_UserName=' . $shequ['username'] . '&Api_UserMd5Pass=' . md5($shequ['password']);
        $list              = $this->get_curl($url, 0, 0, 0, $shequ['proxy'], 10);
        $json              = json_decode($list, true);
        if (is_array($json)) {
            if ($json['status'] == true || $json['status'] == 1) {
                $breakMaxPrice = $conf['cron_maxprice'] ? $conf['cron_maxprice'] : '1888';
                $price_arr     = array();
                if (!is_array($json['user_goods_lists_details'])) {
                    $json['user_goods_lists_details'] = [];
                }
                foreach ($json['user_goods_lists_details'] as $row) {
                    $price_arr[$row['id']]['price']        = isset($row['user_unitprice']) && $row['user_unitprice'] > 0 ? $row['user_unitprice'] : $row['goods_unitprice'];
                    $price_arr[$row['id']]['goods_status'] = $row['goods_status'];
                    $price_arr[$row['id']]['min']          = $row['minbuynum_0'];
                    $price_arr[$row['id']]['max']          = $row['maxbuynum_0'];
                }

                if ($cids != '') {
                    $rs = $DB->select("SELECT * FROM cmy_tools WHERE shequ='{$shequ['id']}' AND is_curl=2 and cid IN ({$cids})");
                }

                $rows = [];
                if ($rs && count($rs) > 0) {
                    $rows = $rs;
                }

                foreach ($rows as $res2) {
                    if ($breakMaxPrice > 0 && $res2['price'] >= $breakMaxPrice) {
                        continue;
                    }

                    if ($res2['value'] < 1) {
                        $res2['value'] = 1;
                    }

                    $price1 = $price_arr[$res2['goods_id']]['price'] * $res2['value'];

                    if ($conf['cron_status_sync'] != 1) {

                        if (isset($price_arr[$res2['goods_id']]) && isset($price_arr[$res2['goods_id']]['goods_status'])) {
                            $goods_status = intval($price_arr[$res2['goods_id']]['goods_status']);
                        } else {
                            require_once SYSTEM_ROOT . 'ajax.class.php';
                            $srow['id']      = '0';
                            $srow['djorder'] = '';
                            $srow['input']   = '123456';
                            $result          = $this->newExtend($shequ)->doOrder($srow, $shequ, $res2, 1);
                            if (strpos($result, '禁止下单') !== false) {
                                $goods_status = 1;
                            } else {
                                $goods_status = 0;
                            }
                        }
                        $this->setToolActive($goods_status == 1 ? 0 : 1, $res2['tid']);
                    }

                    $max = intval($price_arr[$res2['goods_id']]['max']);
                    $max = floor($max / $res2['value']);

                    if (isset($price1) && $price1 > 0) {
                        $this->setToolPrice($price1, $res2);
                    }
                    $DB->query("UPDATE `pre_tools` set `price1`='" . $price1 . "',`max`='" . $max . "' where tid='" . $res2['tid'] . "'");
                    $success++;
                }
                return array('code' => 0, "msg" => '共成功更新' . $success . '个商品', "success" => $success);
            } else {
                return array('code' => -1, "msg" => '获取商品列表失败，' . $json['info'], "success" => 0);
            }
        } else {
            if (stripos($list, '密码') !== false) {
                return array('code' => -1, "msg" => "API账号或密码错误", "success" => 0);
            } else {
                $list = str_replace(array("\r\n", "\r", "\n"), "", $list);
                $list = substr($list, 50, 1000);
                return array('code' => -1, "msg" => "数据解析失败，" . $list, "success" => 0);
            }
        }
    }

    /**
     * 橙子平台价格同步
     * @param  [int] $shequid 社区ID
     * @param  [int] $cid     分类ID
     * @return [array]        同步结果信息
     */
    public function pricejk_chengzi($shequ, $cids = '')
    {
        global $DB, $conf;
        $success  = 0;
        $warnlist = '';

        $token = strSafeEnCode($shequ['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
        $data  = array(
            'key' => $token,
        );
        $url  = $shequ['url'] . "api/index/products";
        $text = $this->get_curl($url, http_build_query($data), 0, 0, $shequ['proxy'], 10);
        $json = json_decode($text, true);
        if (is_array($json)) {
            if ($json['code'] == 1) {
                $breakMaxPrice = $conf['cron_maxprice'] ? $conf['cron_maxprice'] : '1888';
                if (!is_array($json['data'])) {
                    $json['data'] = [];
                }

                $price_arr = [];
                foreach ($json['data'] as $row) {
                    $price_arr[$row['pid']]['price']        = $row['price'];
                    $price_arr[$row['pid']]['goods_status'] = $row['maintain'] == 1 ? '1' : '0';
                    $price_arr[$row['pid']]['min']          = $row['min'];
                    $price_arr[$row['pid']]['max']          = $row['max'];
                }
                $rs = [];
                if ($cids != '') {
                    $rs = $DB->select("SELECT * FROM cmy_tools WHERE shequ='{$shequ['id']}' AND is_curl=2 and cid IN ({$cids})");
                }

                foreach ($rs as $key => $res2) {
                    if ($res2['price'] >= $breakMaxPrice) {
                        continue;
                    }

                    if ($res2['value'] < 1) {
                        $res2['value'] = 1;
                    }

                    $price1       = sprintf('%.2f', $price_arr[$res2['goods_id']]['price'] * $res2['value']);
                    $goods_status = $price_arr[$res2['goods_id']]['goods_status'];
                    $max          = floor($price_arr[$res2['goods_id']]['max'] / $res2['value']);
                    if (isset($price1) && $price1 > 0) {
                        $this->setToolPrice($price1, $res2);
                    }
                    $this->setToolActive($goods_status, $res2['tid']);

                    if ($max) {
                        $DB->query("UPDATE `pre_tools` set `price1`='" . $price1 . "',`max`='" . $max . "' where tid='" . $res2['tid'] . "'");
                    } else {
                        $DB->query("UPDATE `pre_tools` set `price1`='" . $price1 . "' where tid='" . $res2['tid'] . "'");
                    }
                    $success++;
                }
                return array('code' => 0, "msg" => '共成功更新' . $success . '个商品', "success" => $success);
            } else {
                return array('code' => -1, "msg" => $json['msg'], "success" => 0);
            }
        } else {
            return array('code' => -1, "msg" => $text, "success" => 0);
        }
    }

    /**
     * 卡卡云平台价格同步
     * @param  [int] $shequid 社区ID
     * @param  [int] $cid     分类ID
     * @return [array]        同步结果信息
     */
    public function pricejk_kakayun($shequ, $cids = '')
    {
        global $DB, $conf;
        $success  = 0;
        $warnlist = '';

        $data['userid'] = $shequ['username'];
        $key            = strSafeEnCode($shequ['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
        $post           = http_build_query($data) . '&sign=' . yile_getSign($data, $key);
        $url            = $shequ['url'] . 'dockapi/index/getallgoods.html';
        $text           = $this->get_curl($url, $post, 0, 0, $shequ['proxy'], 10);
        $json           = json_decode($text, true);
        if (is_array($json)) {
            if ($json['code'] == 1) {
                $breakMaxPrice = $conf['cron_maxprice'] ? $conf['cron_maxprice'] : '1888';
                if (!is_array($json['data'])) {
                    $json['data'] = [];
                }
                $goodsList = [];
                $price_arr = [];
                foreach ($json['data'] as $value) {
                    $goodsList = array_merge($goodsList, $value['goods']);
                }

                foreach ($goodsList as $row) {
                    $price_arr[$row['id']]['price']        = $row['goodsprice'] / 100; //卡卡云给的价格翻了100倍
                    $price_arr[$row['id']]['goods_status'] = $row['goodsstatus'];
                    $price_arr[$row['id']]['goodstype']    = $row['goodstype'];
                    $price_arr[$row['id']]['showstock']    = $row['showstock'];
                    $price_arr[$row['id']]['stock']        = $row['stock'];
                }

                //$this->writeLogs("卡卡云价格返回\n".json_encode($price_arr), 'priceJk.txt');
                $rs = [];
                if ($cids != '') {
                    $rs = $DB->select("SELECT * FROM cmy_tools WHERE shequ='{$shequ['id']}' AND is_curl=2 and cid IN ({$cids})");
                }

                $message = '';

                foreach ($rs as $key => $res2) {
                    if ($res2['price'] >= $breakMaxPrice) {
                        continue;
                    }

                    if (array_key_exists($res2['goods_id'], $price_arr)) {
                        if ($res2['value'] < 1) {
                            $res2['value'] = 1;
                        }
                        $price1       = sprintf('%.2f', $price_arr[$res2['goods_id']]['price'] * $res2['value']);
                        $goodstype    = $price_arr[$res2['goods_id']]['goodstype'];
                        $stock        = null;
                        $goods_status = intval($price_arr[$res2['goods_id']]['goods_status']);
                        if ($goods_status == 1) {
                            //**商品 或手工
                            if ($res2['goods_type'] == 0 || $goodstype == 1) {
                                if ($price_arr[$res2['goods_id']]['stock'] > 0) {
                                    $goods_status = 1;
                                    $stock        = $price_arr[$res2['goods_id']]['stock'];
                                } else {
                                    $goods_status = 0;
                                    $stock        = 0;
                                }
                            }
                        }

                        $this->setToolActive($goods_status, $res2['tid']);
                        $sql = "`price1`='{$price1}'";
                        if (isset($price1) && $price1 > 0) {
                            $this->setToolPrice($price1, $res2);
                        }
                        $message .= "商品[" . $res2['tid'] . "]更新成功，状态【" . $goods_status . "|" . $price_arr[$res2['goods_id']]['stock'] . "】\n<br>";
                        if ($stock != null) {
                            $sql .= ",`stock_open`='1', `stock`='" . intval($stock) . "'";
                        }

                        $success++;
                        $sql = "UPDATE `pre_tools` SET {$sql} where tid='" . $res2['tid'] . "'";
                        addWebLog('价格同步日志', '系统：' . getShequTypeName($shequ['type']) . '；执行语句：' . $sql, 'Cron');
                        $DB->query($sql);
                    } else {
                        //商品不存在
                        $message .= "商品[" . $res2['tid'] . "]对接业务不存在，已自动下架\n<br>";
                        $this->setToolActive(0, $res2['tid']);
                        $success++;
                    }
                }
                return array('code' => 0, "msg" => $message, "success" => $success);
            } else {
                return array('code' => -1, "msg" => $json['msg'], "success" => 0);
            }
        } else {
            return array('code' => -1, "msg" => $text, "success" => 0);
        }
    }

    /**
     * 返回对接插件实例
     *
     * @param array $config
     * @return object
     */
    private function newExtend($config = [])
    {
        global $DB;
        if (isset($config['alias']) && $config['alias']) {
            $extendname = str_replace('\\', '/', ucfirst($config['alias']));
        } else {
            $extendname = null;
            //自动从插件列表匹配
            $list = extend_get_list();
            foreach ($list as $key => $item) {
                if ($item['type'] == $config['type']) {
                    $extendname = str_replace('\\', '/', $item['alias']);
                    $id         = $config['id'];
                    $DB->exec("UPDATE `pre_shequ` SET `alias`='{$extendname}' where `id`='{$id}'");
                    $extendname = ucfirst($extendname);
                    break;
                }
            }
        }

        if ($extendname) {
            $file = ROOT . 'includes/core/extend/' . trim($extendname, '/') . '/' . $extendname . '.php';
            if (file_exists($file)) {
                include_once $file;
                if (class_exists($extendname, false)) {
                    return new $extendname($config);
                }
            }
        }
        throw new \Exception("未找到对应的对接插件[" . $extendname . "]", 1);
    }

    /**
     * 直客网价格同步
     * @param  [int] $shequid 社区ID
     * @param  [int] $cid     分类ID
     * @return [array]        同步结果信息
     */
    public function pricejk_zhike($shequ, $cids = '')
    {
        global $DB, $conf;
        $result         = [];
        $result['code'] = -1;

        $success       = 0;
        $warnlist      = '';
        $breakMaxPrice = $conf['cron_maxprice'] ? $conf['cron_maxprice'] : '1888';
        $crontime      = isset($shequ['crontime']) && $shequ['crontime'] >= 30 ? $shequ['crontime'] : 400;
        $uptime        = time() - $crontime;
        $rs            = [];
        if ($cids != '') {
            $rs = $DB->select("SELECT * FROM cmy_tools WHERE shequ='{$shequ['id']}' AND `uptime`<='{$uptime}' AND is_curl=2 and cid IN ({$cids})");
        }

        $shequ['password'] = strSafeEnCode($shequ['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
        try {
            $myApi = $this->newExtend($shequ);
        } catch (\Exception $e) {
            return ['code' => -1, "msg" => '更新价格失败，' . $e->getMessage(), "success" => 0];
        }

        $max_num    = 1000;
        $max_time   = 30;
        $start_time = time();
        $num        = 0;
        // $count      = count($rs);
        foreach ($rs as $key => $res2) {
            if (empty($res2['goods_id'])) {
                continue;
            }

            if ($res2['price'] >= $breakMaxPrice) {
                continue;
            }

            $num++;

            if ($num >= $max_num) {
                break;
            }

            if (time() - $start_time >= $max_time) {
                break;
            }

            $json = $myApi->getGoodsParams($shequ, $res2['goods_id']);
            if ($json['code'] == 0) {

                if ($res2['value'] < 1) {
                    $res2['value'] = 1;
                }

                $price1 = $json['data']['price'] * $res2['value'];
                $min    = $json['data']['min'] > 0 ? $json['data']['min'] : 1;
                $max    = $json['data']['max'] > 0 ? $json['data']['max'] : 0;
                $max    = floor($max / $res2['value']);

                $stock     = intval($json['data']['goodsStock']);
                $goodsType = intval($json['data']['goodsType']);
                $desc      = addslashes(stripslashes($json['data']['goodsDetail']));

                //同步价格
                if (isset($price1)) {
                    $this->setToolPrice($price1, $res2);
                } else {
                    $json['data']['active'] = 0;
                }
                //同步上下架
                $this->setToolActive($json['data']['active'], $res2['tid']);
                $sql = "`price1`='{$price1}',`uptime`='" . time() . "'";
                if ($max != $res2['max']) {
                    $sql .= ",`max` = '{$max}'";
                }
                if ($goodsType == 2) {
                    $sql .= ",`stock_open` = '1',`stock` = '{$stock}'";
                }

                if ($conf['corn_desc'] == 1 && $desc) {
                    $sql .= ",`desc` = '{$desc}'";
                }
                $sql = "UPDATE `pre_tools` set {$sql} where tid='" . $res2['tid'] . "'";
                addWebLog('价格同步日志', '系统：' . getShequTypeName($shequ['type']) . '；执行语句：' . $sql, 'Cron');
                $DB->query($sql);
                $success++;
            } else {
                if (isset($json['data']) && $json['data']['code'] == 921 || stripos($json['msg'], '无效') !== false) {
                    //同步上下架
                    $this->setToolActive(0, $res2['tid']);
                    $warnlist .= "\n<br/>商品" . $res2['name'] . "[Tid:" . $res2['goods_id'] . "]已失效，自动下架";
                } else {
                    $warnlist .= "\n<br/>商品" . $res2['name'] . "[Tid:" . $res2['goods_id'] . "]价格更新失败，" . $json['msg'];
                }
            }
        }

        $result['code']     = 0;
        $msg                = '共需执行' . $num . '个，成功更新' . $success . '个商品（为避免超时和保证所有商品同步到位，某商品每次检测后间隔' . $crontime . '秒后才会再次检测）';
        $result['msg']      = $msg;
        $result['success']  = $success;
        $result['warnlist'] = $warnlist;
        return $result;
    }

    /**
     * 商战网价格同步
     * @param  [int] $shequid 社区ID
     * @param  [int] $cid     分类ID
     * @return [array]        同步结果信息
     */
    public function pricejk_shangzhan($shequ, $cids = '')
    {
        global $DB, $conf;
        $success  = 0;
        $warnlist = '';

        $breakMaxPrice = $conf['cron_maxprice'] ? $conf['cron_maxprice'] : '1888';

        $token = strSafeEnCode($shequ['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');

        $uptime = time() - 360;
        $rs     = [];
        if ($cids != '') {
            $rs = $DB->select("SELECT * FROM cmy_tools WHERE shequ='{$shequ['id']}' AND `uptime`<='{$uptime}' AND is_curl=2 and cid IN ({$cids})");
        }

        $max_num    = 600;
        $max_time   = 31;
        $start_time = time();
        $num        = 0;
        foreach ($rs as $key => $res2) {
            if (empty($res2['goods_id'])) {
                continue;
            }

            if ($res2['price'] >= $breakMaxPrice) {
                continue;
            }

            $num++;

            if ($num >= $max_num) {
                break;
            }

            if (time() - $start_time >= $max_time) {
                break;
            }

            $_p = array(
                'customerid' => $shequ['username'],
                'id'         => $res2['goods_id'],
            );
            $_p['sign'] = md5($_p['customerid'] . $res2['goods_id'] . $token);
            $url        = $shequ['url'] . "api.php/Client/goodsInfo";
            $result     = $this->get_curl($url, http_build_query($_p), 0, 0, $shequ['proxy'], 5);
            $json       = @json_decode($result, true);
            if (is_array($json)) {

                if ($json['code'] == 1000) {
                    if ($res2['value'] < 1) {
                        $res2['value'] = 1;
                    }

                    $price1 = $json['data']['price'] * $res2['value'];
                    $desc   = $json['data']['info'];
                    $max    = $json['data']['quantity'];
                    $max    = floor($max / $res2['value']);

                    $this->setToolPrice($price1, $res2);
                    if ($conf['cron_status_sync'] != 1) {
                        //同步上下架
                        if ($json['data']['type'] == 1 && $json['data']['stock_state'] == 3) {
                            $active = 1;
                        } else {
                            $active = $json['data']['supply_state'] == 1 ? 1 : 0;
                        }
                        $this->setToolActive($active, $res2['tid']);
                    }
                    $sql = "`uptime`='" . time() . "'";
                    if ($res2['max'] > $max) {
                        $sql .= ",`max`='" . $max . "'";
                    }
                    if ($conf['corn_desc'] == 1 && $desc) {
                        $desc = addslashes(stripslashes($desc));
                        $sql  = ",`desc`='" . $desc . "'";
                    }
                    $sql = "UPDATE `pre_tools` set {$sql} where tid='" . $res2['tid'] . "'";
                    addWebLog('价格同步日志', '系统：' . getShequTypeName($shequ['type']) . '；执行语句：' . $sql, 'Cron');
                    $DB->query($sql);
                    $success++;
                } else {
                    $warnlist = "价格更新失败，" . $this->getShangzhanCode($json['code'], 'JK');
                }
            } else {
                $warnlist = "商品" . $res2['name'] . "[GID:" . $res2['goods_id'] . "]的详情数据解析失败，" . $result;
            }
        }
        return [
            'code'     => 0,
            "msg"      => '共需执行' . $num . '个，成功更新' . $success . '个商品（为避免超时，某商品每次检测后间隔6分钟后才会再次检测）',
            "success"  => $success,
            "warnlist" => $warnlist,
        ];
    }

    private function getShangzhanCode($resultcode, $type = 'buyorder')
    {
        $status_arr = array(
            '403'  => "禁止访问",
            '404'  => "请求方法不存在",
            '1000' => $type == 'buyorder' ? "下单成功" : '查询成功',
            '1001' => "请求参数不合法",
            '1003' => "签名错误",
            '1004' => "访问频繁，两次间隔不能低于10秒钟",
            '1005' => "请求方式错误",
            '1006' => "客户编号不存在",
            '1007' => "客户编号已经被禁用",
            '1008' => "未开通供货接口功能",
            '1009' => "未开通进货接口功能",
            '1010' => $type == 'buyorder' ? "下单失败" : '查询无数据',
            '2001' => "系统未知错误",
        );

        return !empty($status_arr[$resultcode]) ? $status_arr[$resultcode] : '返回状态码未知，' . $resultcode;
    }

    /**
     * 时空云价格同步
     * @param  [int] $shequid 社区ID
     * @param  [int] $cid     分类ID
     * @return [array]        同步结果信息
     */
    public function pricejk_shikonyun($shequ, $cids = '')
    {
        global $DB, $conf;
        $success  = 0;
        $warnlist = '';

        $breakMaxPrice = $conf['cron_maxprice'] ? $conf['cron_maxprice'] : '1888';

        try {
            $shequ['password'] = strSafeEnCode($shequ['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
            $ShikyApi          = $this->newExtend($shequ);
        } catch (\Exception $e) {
            return array('code' => 0, "msg" => '共成功更新0个商品', "success" => 0, "warnlist" => $e->getMessage());
        }

        $rs = [];
        if ($cids != '') {
            $rs = $DB->select("SELECT * FROM cmy_tools WHERE shequ='{$shequ['id']}' AND is_curl=2 and cid IN ({$cids})");
        }

        foreach ($rs as $key => $res2) {

            if ($res2['price'] >= $breakMaxPrice) {
                continue;
            }

            $result = $ShikyApi->getGoodsParams($shequ, $res2['goods_id']);
            if ($result['code'] == 0) {
                if ($res2['value'] < 1) {
                    $res2['value'] = 1;
                }

                $price1 = $result['data']['price'] * $res2['value'];

                $min = $result['data']['minnum'];
                $max = $result['data']['maxnum'];
                $max = floor($max / $res2['value']);

                if (isset($price1) && $price1 > 0) {
                    $this->setToolPrice($price1, $res2);
                    $DB->query("UPDATE `pre_tools` SET `price1`='{$price1}',`max`='{$max}' where `tid`='" . $res2['tid'] . "'");
                }

                if ($conf['cron_status_sync'] != 1) {
                    //同步上下架
                    $active = isset($result['data']['active']) ? $result['data']['active'] : !$result['data']['close'];
                    $this->setToolActive($active, $res2['tid']);
                }
                $success++;
            } else {
                $warnlist = "价格更新失败，" . $result['msg'];
            }
        }
        return array('code' => 0, "msg" => '共成功更新' . $success . '个商品', "success" => $success, "warnlist" => $warnlist);
    }

    /**
     * 亿樂价格同步
     * @param  [int] $shequid 社区ID
     * @param  [int] $cid     分类ID
     * @return [array]        同步结果信息
     */
    public function pricejk_yile($shequ, $cids = '')
    {
        global $DB, $conf;
        $success  = 0;
        $warnlist = '';

        $shequ["url"]  = yile_url_parse($shequ);
        $url           = $shequ["url"] . "api/goods/info";
        $breakMaxPrice = $conf['cron_maxprice'] ? $conf['cron_maxprice'] : '1888';
        $data          = array(
            'api_token' => $shequ['username'],
        );

        //$uptime = time() - 15;
        $rs = [];
        if ($cids != '') {
            $rs = $DB->select("SELECT * FROM cmy_tools WHERE shequ='{$shequ['id']}' AND is_curl=2 and cid IN ({$cids})");
        }

        $max_num    = 100;
        $max_time   = 31;
        $start_time = time();
        $num        = 0;
        foreach ($rs as $key => $res2) {
            if (empty($res2['goods_id'])) {
                continue;
            }

            if ($res2['price'] >= $breakMaxPrice) {
                continue;
            }

            $num++;

            if ($num >= $max_num) {
                break;
            }

            if (time() - $start_time >= $max_time) {
                break;
            }

            $data['timestamp'] = time();
            $data['gid']       = $res2['goods_id'];
            $post              = http_build_query($data) . '&sign=' . yile_getSign($data, $key);
            //$list=get_curl($url,$post,0,0,0,0,0,1);
            $list = $this->get_curl($url, $post, 0, 0, $shequ['proxy'], 10);

            $json = json_decode($list, true);
            if (is_array($json)) {
                if ($json['status'] == 0) {
                    if ($res2['value'] < 1) {
                        $res2['value'] = 1;
                    }

                    $price1  = $json['data']['price'] * $res2['value'];
                    $close   = intval($json['data']['close']);
                    $desc    = $json['data']['desc'];
                    $min     = $json['data']['limit_min'];
                    $max     = $json['data']['limit_max'];
                    $shopimg = $json['data']['image'];
                    $max     = floor($max / $res2['value']);

                    $this->setToolActive($close == 1 ? 0 : 1, $res2['tid']);

                    $this->setToolPrice($price1, $res2);

                    $sql = "`uptime`='" . time() . "'";

                    if ($res2['max'] > $max) {
                        $sql = ",`max`='" . $max . "'";
                    }
                    if ($conf['corn_desc'] == 1 && $desc) {
                        $desc = addslashes(stripslashes($desc));
                        $sql  = ",`desc`='" . $desc . "'";
                    }

                    if ($conf['corn_shopimg'] == 1 && $shopimg) {
                        if (stripos($shopimg, 'http') === false) {
                            $shopimg = $shequ['url'] . trim($shopimg, '/');
                        }
                        $sql .= ",`shopimg`='" . $shopimg . "'";
                    }
                    $DB->query("UPDATE `pre_tools` SET `price1`='" . $price1 . "'{$sql} where tid='" . $res2['tid'] . "'");
                    $success++;
                } else {
                    $message = $json['message'];
                    if (preg_match('/不存在|商品|删除/', $message)) {
                        $this->setToolActive(0, $res2['tid']);
                    }
                    $warnlist = "价格更新失败，" . $json['message'];
                }
            } else {
                if (empty($list)) {
                    $list = "网站打开失败或超时无法正常访问";
                }
                $warnlist = "价格文本解析失败，" . $list;
            }
        }

        return array('code' => 0, "msg" => '共成功更新' . $success . '个商品', "success" => $success, "warnlist" => $warnlist);
    }

    public function pricejk_kashang($shequ, $cids = '')
    {
        global $DB, $date, $conf, $date;
        $success  = 0;
        $warnlist = '';

        $url           = $shequ['url'] . 'api/product';
        $breakMaxPrice = $conf['cron_maxprice'] ? $conf['cron_maxprice'] : '1888';
        $data          = array(
            'customer_id' => $shequ['username'],
        );
        $uptime = time() - (isset($shequ['crontime']) && $shequ['crontime'] >= 10 ? $shequ['crontime'] : 60);
        $rs     = [];
        if ($cids != '') {
            $rs = $DB->select("SELECT * FROM cmy_tools WHERE shequ='{$shequ['id']}' AND `uptime`<='{$uptime}' AND is_curl=2 and cid IN ({$cids})");
        }

        $max_num    = 600;
        $max_time   = 31;
        $start_time = time();
        $num        = 0;
        foreach ($rs as $key => $res2) {
            if (empty($res2['goods_id'])) {
                continue;
            }

            if ($res2['price'] >= $breakMaxPrice) {
                continue;
            }

            $num++;

            if ($num >= $max_num) {
                break;
            }

            if (time() - $start_time >= $max_time) {
                break;
            }

            $data['timestamp']  = time();
            $data['product_id'] = $res2['goods_id'];

            $post = http_build_query($data) . '&sign=' . kashang_getSign($data, strSafeEnCode($shequ['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1'));
            $list = $this->get_curl($url, $post, 0, 0, $shequ['proxy'], 10);
            //$list=get_curl($url,$post);
            $json = json_decode($list, true);
            if (is_array($json)) {
                if ($json['code'] == 'ok') {
                    if ($res2['value'] < 1) {
                        $res2['value'] = 1;
                    }
                    if (time() - $res2['uptime'] < 120) {
                        continue;
                    }

                    $price1       = $json['data']['price'] * $res2['value'];
                    $goods_status = $json['data']['supply_state'];
                    $min          = explode("-", $json['data']['valid_purchasing_quantity'])[0];
                    $max          = explode("-", $json['data']['valid_purchasing_quantity'])[1];
                    $max          = floor($max / $res2['value']);
                    $active       = $goods_status == 2 || $goods_status == 3 ? 0 : 1;

                    $this->setToolActive($active, $res2['tid']);

                    $this->setToolPrice($price1, $res2);

                    $sql = ",`uptime`='" . time() . "'";
                    if ($res2['max'] > $max) {
                        $sql = ",`max`='" . $max . "'";
                    }

                    $DB->query("UPDATE `pre_tools` SET `price1`='" . $price1 . "'{$sql} where tid='" . $res2['tid'] . "'");
                    $success++;
                } else {
                    $warnlist = addslashes($json['message']);
                }
            } else {
                if (empty($list)) {
                    $list = '网站返回空，请检查网站是否正常打开，或尝试关闭代理服务器再试';
                }
                $warnlist = "价格文本解析失败，" . $list;
            }
        }

        return array('code' => 0, "msg" => '共成功更新' . $success . '个商品（为避免超时，某商品每次检测后间隔6分钟后才会再次检测）', "success" => $success, "warnlist" => $warnlist);
    }

    public function orderjk_yunbao($row, $config)
    {
        global $DB, $date;
        if (empty($row['djorder'])) {
            return array('code' => -1, "msg" => "缺少对接订单号!");
        }

        $url = $config["url"] . "home/api";

        $password = strSafeEnCode($config['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
        $token    = md5('user=' . $config['username'] . '&&pass=' . md5($password));
        $post     = 'user=' . $config['username'] . '&pass=' . md5($password);
        $post .= '&order=' . $row['djorder'] . '&code=1010&token=' . $token;
        $result = get_curl($url, $post);
        $json   = json_decode($result, true);
        if (is_array($json)) {
            if ($json['code'] == '8888') {
                $Arr = [
                    '1' => '未付款',
                    '2' => '已付款',
                    '3' => '未发货',
                    '4' => '已发货',
                    '5' => '已退款',
                    '6' => '已取消',
                ];

                $ret['code'] = 0;
                if (array_key_exists($json['stduy'], $Arr)) {
                    $message = $Arr[$json['stduy']];
                } else {
                    $message = '未知状态，' . $result;
                }

                if ($json['stduy'] == '4') {
                    if (is_array($json['kami']) && count($json['kami']) > 0) {
                        $message = "卡密信息如下，请参考商品说明使用<br>\r\n";
                        foreach ($json['kami'] as $key => $value) {
                            if (!empty($value['code'])) {
                                $message .= $value['code'] . "<br>\r\n";
                            }
                        }
                        $message = trim($message, "<br>\r\n");
                    } else {
                        $message = '充值已到账，如有疑问请联系客服';
                    }
                    $DB->query("UPDATE cmy_orders set bz='" . $message . "',result='" . $message . "',status=1,endtime='" . $date . "' where id='" . $row['id'] . "'");
                } elseif ($json['stduy'] > 4) {
                    $ret['msg'] = '订单充值异常，请联系网站客服处理';
                    $DB->query("UPDATE cmy_orders set bz='" . $ret['msg'] . "',result='" . $ret['msg'] . "',status=3,endtime='" . $date . "' where id='" . $row['id'] . "'");
                } else {
                    $ret['msg'] = '等待发货.';
                }
            } else {
                $ret['code'] = -1;
                if ($json['code'] == '50001') {
                    $ret['msg']   = "查询无记录";
                    $ret['order'] = $row['id'];
                } else {
                    $ret['msg'] = $json['text'];
                }
            }
            return $ret;
        } else {
            $ret = array('code' => -1, "msg" => "查询" . $row['id'] . "的订单详情失败，返回：<br>" . $result);
            return $ret;
        }
    }

    public function orderjk_kashang($row, $config)
    {
        global $DB, $date;
        if (empty($row['djorder'])) {
            return array('code' => -1, "msg" => "缺少对接订单号!");
        }
        $tool = $DB->get_row("SELECT * FROM cmy_tools WHERE tid='" . $row['tid'] . "' limit 1");
        $url  = $config['url'] . "api/order";
        $time = time();
        $data = array(
            'customer_id' => $config['username'],
            'order_id'    => $row['djorder'],
            'timestamp'   => $time,
        );

        $post   = http_build_query($data) . "&sign=" . kashang_getSign($data, strSafeEnCode($config['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1'));
        $result = shequ_get_curl($url, $post, 0, 0, 0, 0, 0, 1);
        //$result=get_curl($url,$post);
        $json = json_decode($result, true);
        if (array_key_exists('code', $json)) {
            if ($json['code'] == 'ok') {
                $dataInfo = $json['data'];
                if ($dataInfo['state'] == 100) {
                    $bz = "卡商等待发货，" . $dataInfo['recharge_info'];
                    # $DB->query("UPDATE cmy_orders set bz='".$bz."',status=2,endtime='".$date."' where id='".$row['id']."'");
                } elseif ($dataInfo['state'] == 200) {
                    if ($dataInfo['product_type'] == 1) {
                        $bz     = "卡商已发货，" . $dataInfo['recharge_info'];
                        $result = $tool['result_succ'] ? $tool['result_succ'] : "订单已完成，如有疑问联系平台客服哦~";
                    } elseif ($dataInfo['product_type'] == 2 || count($dataInfo['cards']) > 0) {
                        $ret    = $this->getCardData($row, $config, $dataInfo['cards']);
                        $result = '';
                        if (!empty($ret['kmdata'])) {
                            $result  = "卡密信息如下，请参考商品介绍使用：<br>\r\n" . $ret["kmdata"];
                            $sqldata = [$result, 3, $date, 1, $row['id']];
                        } else {
                            $result  = '请联系客服查看卡密信息';
                            $bz      = "卡商已发货，卡密失败失败：<br>\n" . json_encode($dataInfo['cards']);
                            $sqldata = [$result, 1, $date, 2, $row['id']];
                            log_result(getShequTypeName($config["type"]) . "对接", $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；', '卡密识别失败，' . $ret['msg'], 1, $row['id']);
                        }

                        $DB->query("UPDATE cmy_orders set result= ?,`djzt`= ?,endtime= ?,status= ? where id= ?", $sqldata);
                    } elseif ($dataInfo['product_type'] == 4) {
                        $recharge_info = $dataInfo['recharge_info'];
                        preg_match("/订单编号\:([A-Za-z0-9]+)/", $recharge_info, $msg);
                        $recharge_info = str_replace($msg[0], '', $recharge_info);
                        $arr           = array('/qq([0-9]+)/', '/QQ([0-9]+)/', '/扣扣([0-9]+)/', '/企鹅([0-9]+)/', '/微信([A-Za-z0-9]+)/', '/vx([A-Za-z0-9]+)/');
                        foreach ($arr as $value) {
                            $msg2 = [];
                            if (preg_match($value, $recharge_info, $msg2)) {
                                $recharge_info = str_ireplace($msg2[0], '', $recharge_info);
                            }
                        }

                        $result = "订单已完成，租号内容：" . $recharge_info;
                        $bz     = "订单已发货，租号内容：" . $recharge_info;
                    } else {
                        $result = $tool['result'];
                        $bz     = '选号订单，暂无法处理：' . $tool['result'];
                    }
                    $DB->query("UPDATE cmy_orders set bz='" . $bz . "',result='" . $result . "',status=1,endtime='" . $date . "' where id='" . $row['id'] . "'");
                } elseif ($dataInfo['state'] == 500) {
                    $bz = "卡商已退款，请审核！参考原因：" . $dataInfo['recharge_info'];
                    $DB->query("UPDATE cmy_orders set bz='" . $bz . "',status=0,endtime='" . $date . "' where id='" . $row['id'] . "'");
                }

                $ret = array('code' => 0, "msg" => $bz);
            } else {
                $ret = array('code' => -1, "msg" => $json['message'], "data" => null);
            }
        } else {
            $ret = array('code' => -1, "msg" => "打开网址[" . $config['url'] . "]失败，请检查货源地址是否正确", "data" => $result);
        }
        return $ret;
    }

    /**
     * 插件价格监控
     */
    public function pricejk_extend($config = [], $cids = '')
    {
        if (!isset($config['alias']) || !isset($config['username'])) {
            return '插件配置信息不完整';
        }
        $alias          = isset($config['alias']) ? $config['alias'] : null;
        $result['code'] = -1;
        if ($config['password']) {
            $config['password'] = strSafeEnCode($config['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
        }
        if ($config['paypwd']) {
            $config['paypwd'] = strSafeEnCode($config['paypwd'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
        }
        try {
            $obj = $this->newExtend($config);
            if (!method_exists($obj, 'pricejk')) {
                throw new \Exception("该对接插件无更新价格功能");
            }
            return $obj->pricejk($config, $cids);
        } catch (\Throwable $th) {
            if ($alias && is_dir(ROOT . 'includes/core/extend/' . $alias)) {
                //尝试加载对接插件的函数库
                $this->extend_function($alias);
                $funcName = 'pricejk_' . strtolower($alias);
                if (function_exists($funcName)) {
                    $result = $funcName($config, $cids);
                } else {
                    $result['msg'] = '同步商品信息失败，该对接插件不支持';
                }
            } else {
                $result['msg'] = '同步商品信息失败，该对接插件不存在、被卸载、删除等';
            }
        }
        return $result;
    }

    public function query_this($row, $tool)
    {
        global $DB;

        $result['code']    = -1;
        $list['num']       = $row['value'];
        $list['start_num'] = 0;
        $list['now_num']   = $row['value'];
        $list['add_time']  = $row['addtime'];
        if ($tool['is_curl'] == 4) {
            $sqlData = [':tid' => $row['tid'], ':orderid' => $row['id']];
            $rs      = $DB->query("SELECT * FROM cmy_faka WHERE tid=:tid AND orderid= :orderid ORDER BY kid ASC", $sqlData);
            $kmdata  = [];
            while ($res = $DB->fetch($rs)) {
                if (!empty($res['pw'])) {
                    $kmdata[] = array('card' => $res['km'], 'pass' => $res['pw']);
                } else {
                    $kmdata[] = array('card' => $res['km']);
                }
            }

            $list['num']         = $row['value'];
            $list['start_num']   = 0;
            $list['now_num']     = $row['value'];
            $list['add_time']    = $row['addtime'];
            $list['order_state'] = '已完成';
            $list['isfaka']      = 1;
            $list['kmdata']      = $kmdata;
            $row['status']       = 1;
        } else {
            $list['num']         = $row['value'];
            $list['start_num']   = 0;
            $list['now_num']     = $row['value'];
            $list['add_time']    = $row['addtime'];
            $order_state         = ['1' => '已完成', '2' => '处理中', '3' => '异常', '4' => '已退款', '0' => '待处理'];
            $list['order_state'] = $order_state[$row['status']];
            $list['result']      = $row['result'];
        }

        if (is_array($list)) {
            $result['code']    = 0;
            $result['message'] = "查询成功，订单状态【" . $list['order_state'] . "】；状态码【" . $row['status'] . "】";
            $result['data']    = $list;
        } else {
            $result['message'] = '获取数据失败';
        }

        return $result;
    }

    public function query_extend($row, $config)
    {
        if ($config['password']) {
            $config['password'] = strSafeEnCode($config['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
        }

        if ($config['paypwd']) {
            $config['paypwd'] = strSafeEnCode($config['paypwd'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
        }

        try {
            $obj = $this->newExtend($config);
            if (method_exists($obj, 'query')) {
                return $obj->query($row, $config);
            }

            $alias          = isset($config['alias']) ? ucfirst($config['alias']) : null;
            $result['code'] = -1;
            if ($alias && is_dir(ROOT . 'includes/core/extend/' . $alias)) {
                //尝试加载对接插件的函数
                $this->extend_function($alias);
                $funcName = 'query_' . strtolower($alias);
                if (function_exists($funcName)) {
                    $result = $funcName($row, $config);
                } else {
                    //尝试加载对接插件的处理类
                    $this->extend_autoload("\\core\\extend\\{$alias}\\{$alias}");
                    if (class_exists($alias)) {
                        try {
                            $myApi = new $alias($config, $config['password'], $config['paypwd'], $config['ssl']);
                            if (!method_exists($myApi, 'query')) {
                                $result['msg'] = '查询失败，该对接插件不支持查询';
                            } else {
                                $result = $myApi->query($row);
                            }
                        } catch (\Exception $e) {
                            $result['msg'] = '查询失败，' . $e->getMessage();
                        }
                    } else {
                        $result['msg'] = '查询失败，该对接插件不支持';
                    }
                }
            } else {
                $result['msg'] = '查询失败，该对接插件查询订单功能可能不存在、异常等';
            }
            return $result;
        } catch (\Throwable $th) {
            return ['code' => -1, 'msg' => '插件运行错误，' . $th->getMessage()];
        }
    }

    /**
     * 加载扩展插件的类库
     */
    private function extend_autoload($class)
    {
        $class = str_replace('\\', '/', $class);
        $file  = ROOT . 'includes/' . trim($class, '/') . '.php';
        if (file_exists($file)) {
            include_once $file;
        }
    }
    /**
     * 加载扩展插件的函数库
     */
    private function extend_function($extendname)
    {
        $extendname = str_replace('\\', '/', $extendname);
        $file       = ROOT . 'includes/core/extend/' . trim($extendname, '/') . '/function.php';
        if (file_exists($file)) {
            include_once $file;
        }
    }

    /**
     *  挂客宝订单退款
     * @param  [array] $row    下单数据信息
     * @param  [array] $config 对接平台信息
     * @return [array]         查询结果
     */
    public function refund_guakebao($row, $config)
    {
        global $DB, $date;
        $url    = $config['url'] . "AppApi.ashx";
        $apikey = strSafeEnCode($config['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
        $post   = array(
            'action'                     => 'busi_apply_refund_order',
            'write_response_do_add_root' => true,
            'return_exception_info'      => true,
            'id'                         => $row['djorder'],
            'api_key'                    => $apikey,
        );
        $text = $this->get_curl($url, http_build_query($post), 0, 0, $config['proxy'], 30);
        if (stripos($text, '<result_code>') !== false) {
            $result_code = getXmlVal($text, 'result_code');
            if ($result_code == '0') {
                $result = ['code' => 0, 'msg' => getXmlVal($text, 'result_message')];
            } else {
                $result = ['code' => -1, 'msg' => getXmlVal($text, 'result_message')];
            }
        } else {
            $result = ['code' => -1, 'msg' => '网站返回数据解析失败，' . $text];
        }
        return $result;
    }

    /**
     *  挂客宝订单查询
     * @param  [array] $row    下单数据信息
     * @param  [array] $config 对接平台信息
     * @return [array]         查询结果
     */
    public function query_guakebao($row, $config)
    {
        global $DB, $date;
        $url    = $config['url'] . "AppApi.ashx";
        $apikey = strSafeEnCode($config['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
        $post   = array(
            'action'                     => 'busi_load_order_list',
            'write_response_do_add_root' => true,
            'user_id'                    => $config['username'],
            'ids'                        => $row['djorder'],
            'api_key'                    => $apikey,
        );

        $text = $this->get_curl($url, http_build_query($post), 0, 0, $config['proxy'], 30);
        //$json = xmlToArray($result);
        //$json = json_decode($text, true);
        if (stripos($text, '<result_code>') !== false) {
            $result_code = getXmlVal($text, 'result_code');
            if ($result_code == '0') {
                $order_active = getXmlVal($text, 'result_list.result.busi_status_id');
                $status_arr   = array(
                    '1' => '下单（未确认）',
                    '2' => '提交成功',
                    '3' => '进行中',
                    '4' => '暂停',
                    '5' => '已完成',
                    '6' => '管理员退款',
                    '7' => '已撤单',
                    '8' => '已退款',
                    '9' => '申请退款中',
                );
                $order_state = $status_arr[$order_active];
                if (empty($order_state)) {
                    $order_state = '待处理';
                }
                $data = array(
                    'orderid'     => $row['djorder'],
                    'num'         => getXmlVal($text, 'result_list.result.target_val_1'),
                    'add_time'    => $row['addtime'],
                    'start_num'   => getXmlVal($text, 'result_list.result.start_val_1'),
                    'now_num'     => getXmlVal($text, 'result_list.result.current_val_1'),
                    'order_money' => getXmlVal($text, 'result_list.result.cost'),
                    'order_state' => $order_state,
                    'shopUrl'     => '',
                );
                if ($row['status'] == 2) {
                    $sql = "UPDATE `pre_orders` SET `result`=:result,`status`=:status where `id`=:id";
                    if ($order_active == 5) {
                        $sql_data = array(
                            ':result' => '订单进度已完成，如有疑问联系平台客服哦',
                            ':status' => 1,
                            ':id'     => $row['id'],
                        );
                    } else {
                        if (in_array($order_active, ['6', '8', '9', '4'])) {
                            $sql_data = array(
                                ':result' => '订单出现异常，请联系客服处理',
                                ':status' => 3,
                                ':id'     => $row['id'],
                            );
                        } else {
                            $sql_data = array(
                                ':result' => '订单正在排队处理中，耐心等待~',
                                ':status' => 2,
                                ':id'     => $row['id'],
                            );
                        }
                    }
                    $DB->exec($sql, $sql_data);
                }
                $result = ['code' => 0, 'msg' => '订单查询成功，状态已同步', 'data' => $data];
            } else {
                $result = ['code' => -1, 'msg' => getXmlVal($text, 'result_message')];
            }
        } else {
            $result = ['code' => -1, 'msg' => '网站返回数据解析失败，' . $text];
        }
        return $result;
    }

    /**
     *  橙子平台订单退款
     * @param  [array] $row    下单数据信息
     * @param  [array] $config 对接平台信息
     * @return [array]         查询结果
     */
    public function refund_chengzi($row, $config)
    {
        global $DB, $date;
        $url = $config['url'] . "api/index/refund";
        $arr = array(
            "trade" => $row['djorder'],
        );
        $text = $this->get_curl($url, http_build_query($arr), 0, 0, $config['proxy'], 30);
        //@writeLogs("社区" . $config['url'] . "订单退款返回：\n" . $text); //调试日志
        $json = json_decode($text, true);
        if (is_array($json)) {
            if ($json['code'] == '1') {
                $result = ['code' => 0, 'msg' => $json['info']];
            } else {
                $result = ['code' => -1, 'msg' => $json['info']];
            }
        } else {
            $result = ['code' => -1, 'msg' => '网站返回数据解析失败，' . $text];
        }
        return $result;
    }

    /**
     *  橙子平台订单查询
     * @param  [array] $row    下单数据信息
     * @param  [array] $config 对接平台信息
     * @return [array]         查询结果
     */
    public function query_chengzi($row, $config)
    {
        global $DB, $date;

        $url = $config['url'] . "api/index/query";
        $arr = array(
            "trade" => $row['djorder'],
        );
        $text = $this->get_curl($url, http_build_query($arr), 0, 0, $config['proxy'], 30);
        //@writeLogs("查询社区" . $config['url'] . "订单详情返回：\n" . $text); //调试日志
        $json = json_decode($text, true);
        if (is_array($json)) {
            if ($json['code'] == '1') {
                $status_arr = array(
                    '1' => '执行中',
                    '2' => '已完成',
                    '5' => '状态异常',
                );
                $order_state = $status_arr[$json['data']['status']];
                if (empty($order_state)) {
                    $order_state = '待处理';
                }
                $now_num  = isset($json['data']['finish_num']) ? $json['data']['finish_num'] : $json['data']['now_num'];
                $add_time = isset($json['data']['create_at']) ? $json['data']['create_at'] : $json['data']['create_time'];
                $data     = array(
                    'orderid'     => $row['djorder'],
                    'num'         => $json['data']['num'],
                    'add_time'    => $add_time,
                    'start_num'   => $json['data']['start_num'],
                    'now_num'     => $now_num,
                    'order_money' => $json['data']['amount'],
                    'order_state' => $order_state,
                    'shopUrl'     => '',
                );
                $sql = "UPDATE `pre_orders` SET `result`=:result,`status`=:status where `id`=:id";
                if ($json['data']['status'] == 2) {
                    $sql_data = array(
                        ':result' => '订单进度已完成，如有疑问联系平台客服哦',
                        ':status' => 1,
                        ':id'     => $row['id'],
                    );
                } else {
                    if (in_array($json['data']['status'], ['5'])) {
                        $sql_data = array(
                            ':result' => '订单出现异常，请联系客服处理',
                            ':status' => 3,
                            ':id'     => $row['id'],
                        );
                    } else {
                        $sql_data = array(
                            ':result' => '订单正在排队处理中，耐心等待~',
                            ':status' => 2,
                            ':id'     => $row['id'],
                        );
                    }
                }
                $DB->exec($sql, $sql_data);
                $result = ['code' => 0, 'msg' => '订单查询成功，状态已同步', 'data' => $data];
            } else {
                $result = ['code' => -1, 'msg' => $json['msg']];
            }
        } else {
            $result = ['code' => -1, 'msg' => '网站返回数据解析失败，' . $text];
        }
        return $result;
    }

    public function query_jiuliu($row, $config)
    {

        global $DB, $date;

        $tool = $DB->get_row("SELECT * from cmy_tools where tid='" . $row['tid'] . "' limit 1"); //获取商品信息

        if (!$tool['card'] || !$tool['card_pass']) {
            $ret = array('code' => -1, "msg" => "对接商品缺少卡号或卡密", "data" => null);
            return $ret;
        }

        if (!$row['djorder']) {
            $ret = array('code' => -1, "msg" => "缺少对接订单号，查询失败！", "data" => null);
            return $ret;
        }

        $url = $config["url"] . "index.php?m=Api&c=User&a=Getorderinfo";

        $post = "card=" . $tool["card"] . "&pass=" . $tool["card_pass"] . "&goodsid=" . $tool['goods_id'] . "&orderid=" . $row['djorder'];
        $data = get_curl($url . '&' . $post);
        $arr  = json_decode($data, true);
        if (is_array($arr)) {
            if ($arr['status'] == true && isset($arr['orderid'])) {
                $ret                      = array();
                $ret['code']              = 0;
                $ret['msg']               = "succ";
                $ret['data']['orderid']   = $row['djorder'];
                $ret['data']['start_num'] = $arr['startnum'];
                $ret['data']['now_num']   = $arr['nownum'];
                $ret['data']['add_time']  = $arr['starttime'];
                $state_arr                = explode("u", $arr['state']);
                $order_state              = '';
                foreach ($state_arr as $value) {
                    if ($value == "") {
                        continue;
                    }

                    $order_state .= "\\u" . $value;
                }
                $order_state                = trim($order_state, '\\u');
                $order_state                = preg_replace_callback('/\\\([0-9a-f]{4})/i', 'unicode_decode', $order_state);
                $ret['data']['order_state'] = $order_state;
                return $ret;
            }

            $ret['code'] = -1;
            $ret['msg']  = $arr["info"];
            return $ret;
        }

        if (preg_match("/<p\\sclass=\"error\">(.*?)<\\/p>/", $data, $msg)) {
            $ret = array('code' => -1, "msg" => $msg[1], "data" => $data);
        } else {
            $data = mb_substr($data, 0, 100);
            $ret  = array('code' => -1, "msg" => "查询" . $row['id'] . "的九流订单详情失败，请稍后重试！", "data" => $data);
        }

        return $ret;
    }

    private function kayisu_getCookie($config)
    {
        global $CACHE;
        $cookie_name = substr(md5($config["url"] . $config["username"]), 0, 12);
        $row         = $CACHE->read($cookie_name);
        if (empty($row['v']) || time() > $row['expire']) {
            $cookies = $this->kayisu_login($config);
            if (strpos($cookies, "失败") === false) {
                $CACHE->save($cookie_name, $cookies, time() + 120);
            }
            return $cookies;
        } else {
            return $row['v'];
        }
    }

    private function kayisu_login($config)
    {
        $url          = $config['url'] . "user/login";
        $user         = $config['username'];
        $pwd          = strSafeEnCode($config['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
        $post         = "username=" . $user . "&password=" . $pwd . "&r=" . time();
        $header[]     = "Host:" . $config['url'];
        $header[]     = "Origin:http://" . $config['url'];
        $header[]     = "Upgrade-Insecure-Requests:1";
        $referer      = $config['url'] . "login.html";
        $handle       = $this->chenm_curl($url, $post, $referer, 0, 1, $header, 0, $config['proxy']);
        $result       = curl_exec($handle);
        $httpCode     = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $headerString = curl_getinfo($handle, CURLINFO_HEADER_OUT);
        curl_close($handle);
        if ($httpCode == 200) {
            $data2 = "{" . getSubstr($result, "{", "}") . "}";
            $Json  = json_decode($data2, true);
            if (is_array($Json)) {
                if ($Json['status'] == '1' && stripos($result, 'front_user_auth_sign') !== false) {
                    $cookies = "";
                    preg_match_all("/Set-Cookie:(.*);/im", $result, $matchs);
                    foreach ($matchs[1] as $val) {
                        $cookies .= trim($val) . "; ";
                    }
                    return $cookies;
                } else {
                    return '尝试登录失败，' . $Json['info'];
                }
            } else {
                return '尝试登录失败，' . $result;
            }
        } else {
            $this->writeLogs("自动登录卡易速失败：\n" . substr($result, 0, 3000), $config['url'] . '.txt');
            return '对接站访问失败，[' . $httpCode . ']：' . $result;
        }
    }

    /**
     * @author  [chenm]
     * @param   [row]  array 商城订单信息数组
     */

    public function query_express($row)
    {
        global $conf;
        if (empty($conf['express_appcode'])) {
            return ['code' => -1, 'msg' => '未配置好接口信息，查询物流信息失败', 'data' => []];
        } elseif (empty($row['exporder'])) {
            return ['code' => -1, 'msg' => '缺少物流订单号', 'data' => []];
        }

        $host      = "https://kdwlcxf.market.alicloudapi.com";
        $path      = "/kdwlcx";
        $method    = "GET";
        $appcode   = $conf['express_appcode'];
        $headers   = [];
        $headers[] = "Authorization:APPCODE " . $appcode;
        //$headers[] = "Content-Type:application/x-www-form-urlencoded; charset=UTF-8";
        $querys = "no=" . $row['exporder'];
        if (!empty($conf['express_com'])) {
            $querys .= "&type=" . $conf['express_com'];
        }
        $bodys = "";
        $url   = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_HEADER, false);

        if (1 == strpos("$" . $host, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $result   = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($httpCode != '200') {
            $json['status'] = -4;
            $json['msg']    = '查询失败，' . $this->query_expressGetHttpCode($httpCode);
            $result         = 'httpCode：' . $httpCode;
        } else {
            $json = json_decode($result, true);
        }

        if (is_array($json)) {
            if ($json['status'] == '0') {
                //查询到对应物流并通讯成功
                $ret['code']           = 0;
                $ret['msg']            = 'ok';
                $ret['data']           = $json['result'];
                $ret['data']['status'] = $this->query_expressGetSignStatus($json['result']['deliverystatus']);
            } else if ($json['status'] == '-4') {
                $ret['code']   = -1;
                $ret['msg']    = $json['msg'];
                $ret['result'] = $result;
            } else {
                $ret['code']   = -1;
                $ret['msg']    = $this->query_expressGetStatus($json['status']);
                $ret['result'] = $result;
            }
            return $ret;
        } else {
            $ret['code']   = -1;
            $ret['msg']    = '快递信息查询结果集数据异常，解析失败';
            $ret['result'] = $result;
            return $ret;
        }
    }

    /**
     * @author  [chenm]
     * @param   [status]  int 快递状态
     */

    private function query_expressGetSignStatus($status)
    {
        switch ($status) {
            case '0':
                return '快递收件(揽件)';
                break;
            case '1':
                return '在途中';
                break;
            case '2':
                return '正在派件';
                break;
            case '3':
                return '已签收';
                break;
            case '4':
                return '派送失败';
                break;
            case '5':
                return '疑难件';
                break;
            case '6':
                return '退件签收';
                break;
            default:
                return '未知，请自咨询客服[' . $status . ']';
                break;
        }
    }

    /**
     * @author  [chenm]
     * @param   [status]  int 快递状态
     */

    private function query_expressGetStatus($status)
    {
        switch ($status) {
            case '0':
                return '正常查询';
                break;
            case '201':
                return '快递单号错误';
                break;
            case '203':
                return '快递公司不存在';
                break;
            case '204':
                return '快递公司识别失败';
                break;
            case '205':
                return '没有信息';
                break;
            case '207':
                return '该单号被限制，错误单号';
                break;
            default:
                return '未知状态';
                break;
        }
    }

    /**
     * @author  [chenm]
     * @param   [code]  int 网关状态码
     */

    private function query_expressGetHttpCode($code)
    {
        switch ($code) {
            case '200':
                return '正常';
                break;
            case '400':
                return 'URL无效';
                break;
            case '401':
                return 'appCode错误';
                break;
            case '403':
                return '次数用完';
                break;
            case '500':
                return 'API网管错误';
                break;
            default:
                return '其他状态';
                break;
        }
    }

    private function query_expressDefault($param)
    {
        global $conf;
        return ['code' => -1, 'msg' => '接口异常，已弃用', 'data' => []];

        if (empty($conf['express_com'])) {
            return ['code' => -1, 'msg' => '缺少默认快递编码信息，请联系平台客服核对', 'data' => []];
        }

        $key      = $conf['express_key']; //客户授权key
        $customer = $conf['express_customer']; //查询公司编号

        $param['com'] = $conf['express_com'];

        $post_data             = array();
        $post_data["customer"] = $customer;

        $post_data["param"] = json_encode($param);
        $sign               = md5($post_data["param"] . $key . $post_data["customer"]);
        $post_data["sign"]  = strtoupper($sign);

        $url = 'http://poll.kuaidi100.com/poll/query.do'; //实时查询请求地址

        $result      = get_curl($url, http_build_query($post_data));
        $result      = str_replace("\"", '"', $result);
        $json        = json_decode($result, true);
        $ret         = [];
        $ret['code'] = -1;
        if (is_array($json) && $json['status'] == '200') {
            //查询到物流并通讯成功
            $ret['code']    = 0;
            $ret['msg']     = 'succ';
            $ret['ischeck'] = $json['ischeck'];
            $ret['com']     = $json['com'];
            $ret['status']  = $this->query_expressGetStatus($json['state']);
            $ret['data']    = $json['data'];
            return $ret;
        } else {
            $ret['result'] = $json;
            $ret['msg']    = $json['message'];
            $ret['data']   = $json['data'];
            $ret['status'] = $this->query_expressGetStatus($json['state']);
        }
        return $ret;
    }
}
