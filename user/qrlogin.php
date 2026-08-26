<?php
/**
 * QQ登录类优化版 By 星河
 */
include_once '../includes/common.php';
header('Content-type: application/json');
class qq_qrlogin
{
    public function getqrpic()
    {
        $url = 'https://ptlogin.qq.com/ptqrshow?appid=549000912&e=2&l=M&s=4&d=72&v=4&t=0.' . time() . '&daid=5';
        $arr = $this->get_curl($url, 0, 0, 0, 1, 0, 0, 1);
        $arr['header'];
        preg_match('/qrsig=(.*?);/', $arr['header'], $match);
        if ($qrsig = $match[1]) {
            exit('{"saveOK":0,"qrsig":"' . $qrsig . '","data":"' . base64_encode($arr['body']) . '"}');
        } else {
            exit('{"saveOK":1,"msg":"二维码获取失败"}');
        }
    }
    public function qqlogin()
    {
        $uin   = null;
        $sig   = null;
        $qrsig = empty($_GET['qrsig']) ? exit('{"saveOK":-1,"msg":"qrsig不能为空"}') : htmlspecialchars($_GET['qrsig']);
        //$url='http://ptlogin.qq.com/ptqrlogin?u1=http%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone&ptqrtoken='.$this->getqrtoken($qrsig).'&ptredirect=0&h=1&t=1&g=1&from_ui=1&ptlang=2052&action=0-0-'.time().'0000&js_ver=10194&js_type=1&login_sig='.$sig.'&pt_uistyle=40&aid=549000912&daid=5&';
        $url = 'https://ptlogin.qq.com/ptqrlogin?u1=http%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone&ptqrtoken=' . $this->getqrtoken($qrsig) . '&ptredirect=0&h=1&t=1&g=1&from_ui=1&ptlang=2052&action=0-0-' . time() . '0000&js_ver=10194&js_type=1&login_sig=' . $sig . '&pt_uistyle=40&aid=549000912&daid=5&'; //新版
        $ret = $this->get_curl($url, 0, $url, 'qrsig=' . $qrsig . '; ', 1);
        if (preg_match("/ptuiCB\('(.*?)'\)/", $ret, $arr)) {
            $r = explode("','", str_replace("', '", "','", $arr[1]));
            if ($r[0] == 0) {
                preg_match_all('/Set-Cookie: (.*?)/iU', $ret, $cookiearr);
                $cookie = "";
                foreach ($cookiearr[1] as $value) {
                    $value = str_replace(array("\r\n", "\n", "\r"), "", $value);
                    $cookie .= substr($value, 0, stripos($value, ';') + 1);
                }

                preg_match('/uin=o(\d+)/', $ret, $uin);
                $uin = ltrim($uin[1], '0');
                preg_match('/sid=(.?);/', $ret, $sid);
                preg_match('/skey=@(.{9});/', $ret, $skey);
                preg_match('/superkey=(.*?);/', $ret, $superkey);
                $data       = stripos($r[2], 'http') !== false ? $this->get_curl($r[2], 0, 0, 0, 1) : '111';
                $cookieArr2 = [];
                $g_tk       = null;
                if ($data) {
                    if (preg_match("/p_skey=(.*?);/", $data, $matchs1)) {
                        $pskey = $matchs1[1];
                    } else {
                        if (preg_match("/pskey=(.*?);/", $data, $matchs2)) {
                            $pskey = $matchs2[1];
                        } else {
                            $pskey = '获取失败，请扫码重新登录或换QQ再试[' . $r[2] . ']';
                            $this->setLog($uin, $data);
                        }
                    }

                    if (preg_match("/pt4_token=(.*?);/", $data, $match3)) {
                        $pt4_token = $match3[1];
                    } else {
                        $pt4_token = '获取失败，请扫码重新登录或换QQ再试';
                    }

                    preg_match_all('/Set-Cookie: (.*?)/iU', $data, $cookieArr2);
                    $this->setLog($uin, $data . json_encode($cookieArr2[1]));
                } else {
                    $pskey = '获取为空，请扫码重新登录或换QQ再试[' . $r[2] . ']';
                }

                if ($skey[1]) {
                    if ($skey[1]) {
                        $g_tk = $this->getTk('@' . $skey[1]);
                    }

                    $this->writeCookie($cookie . 'g_tk=' . $g_tk . ';skey=@' . $skey[1] . ';pt4_token=' . $pt4_token . ';p_skey=' . $pskey . ';', $g_tk, $uin);
                    $_SESSION['findpwd_qq'] = $uin;
                    exit('ptuiCB("0","' . $uin . '","' . $sid[1] . '","@' . $skey[1] . '","' . $pskey . '","' . $superkey[1] . '","' . urlencode($r[5]) . '","' . $g_tk . '");');
                } else {
                    exit('ptuiCB("6","' . $uin . '","登录成功，获取相关信息失败！' . $r[2] . '","' . $ret . '","' . $cookie . '");');
                }
            } elseif ($r[0] == 65) {
                exit('ptuiCB("1","' . $uin . '","二维码已失效。");');
            } elseif ($r[0] == 66) {
                exit('ptuiCB("2","' . $uin . '","二维码未失效。");');
            } elseif ($r[0] == 67) {
                exit('ptuiCB("3","' . $uin . '","正在验证二维码。");');
            } else {
                exit('ptuiCB("6","' . $uin . '","' . str_replace('"', '\'', $r[4]) . '");');
            }
        } else {
            $ret = str_ireplace(["\r\n", "\r", "\n"], '', $ret);
            $arr = ['saveOK' => 6, 'msg' => $ret];
            exit(json_encode($arr));
        }
    }

    public function setLog($uin, $msg)
    {
        if (!is_dir(__DIR__ . '/temp')) {
            @mkdir(__DIR__ . '/temp');
        }
        return file_put_contents(__DIR__ . '/temp/P_skey_res_' . $uin . '.txt', $msg);
    }

    private function getqrtoken($qrsig)
    {
        $len  = strlen($qrsig);
        $hash = 0;
        for ($i = 0; $i < $len; $i++) {
            $hash += (($hash << 5) & 2147483647) + ord($qrsig[$i]) & 2147483647;
            $hash &= 2147483647;
        }
        return $hash & 2147483647;
    }
    private function get_curl($url, $post = 0, $referer = 0, $cookie = 0, $header = 0, $ua = 0, $nobaody = 0, $split = 0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $httpheader[] = "Accept:*/*";
        $httpheader[] = "Accept-Encoding:gzip,deflate,sdch";
        $httpheader[] = "Accept-Language:zh-CN,zh;q=0.8";
        $httpheader[] = "Connection:keep-alive";
        $httpheader[] = "Upgrade-Insecure-Requests:1";
        $httpheader[] = "sec-ch-ua-platform: Windows";

        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        if ($header) {
            curl_setopt($ch, CURLOPT_HEADER, true);
        }
        if ($cookie) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        if ($referer) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }

        if ($ua) {
            curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        } else {
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 UBrowser/6.2.4098.3 Safari/537.36');
        }

        if ($nobaody) {
            curl_setopt($ch, CURLOPT_NOBODY, 1);
        }

        //允许重定向
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //重定向最多4次
        curl_setopt($ch, CURLOPT_MAXREDIRS, 6);

        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($ch);
        if ($split) {
            $headerSize    = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header        = substr($ret, 0, $headerSize);
            $body          = substr($ret, $headerSize);
            $ret           = array();
            $ret['header'] = $header;
            $ret['body']   = $body;
        }
        curl_close($ch);
        return $ret;
    }

    /**
     * 根据p_skey计算g_tk
     */
    private function getTk($skey)
    {
        // skey 是QQ空间登录授权以后cookie里的skey
        $hash = 5381;
        for ($i = 0; $i < strlen($skey); $i++) {
            $hash += ($hash << 5) + $this->charCodeAt(substr($skey, $i, 1));
        }
        return $hash & 0x7fffffff;
    }

    /**
     * 取出某字符Unicode值
     */
    private function charCodeAt($str, $index = null)
    {
        if (!is_null($index) && $index >= 0) {
            $char = mb_substr($str, intval($index), 1, 'UTF-8');
        } else {
            $char = $str;
        }

        if ($char !== '' && mb_check_encoding($char, 'UTF-8')) {
            $ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
            return hexdec(bin2hex($ret));
        } else {
            return null;
        }
    }

    /**
     * 临时储存ck
     */
    private function writeCookie($cookies, $g_tk, $uin)
    {
        global $DB, $date;
        $row = $DB->get_row("SELECT * FROM `pre_cookies` WHERE `uin`=:uin limit 1", [':uin' => $uin]);
        if ($row) {
            $sql_data = array(
                ':id'      => $row['id'],
                ':cookies' => $cookies,
                ':g_tk1'   => $g_tk,
                ':error'   => 0,
                ':num'     => 0,
                ':uptime'  => time(),
            );
            $sql = "UPDATE `pre_cookies` SET `cookies`=:cookies,`g_tk1`=:g_tk1,`error`=:error,`num`=:num,`uptime`=:uptime WHERE `id`=:id";
        } else {
            $sql_data = array(
                ':uin'     => $uin,
                ':cookies' => $cookies,
                ':g_tk1'   => $g_tk,
                ':error'   => 0,
                ':num'     => 0,
                ':addtime' => $date,
                ':uptime'  => time(),
            );
            $sql = "INSERT INTO `pre_cookies` (`uin`,`cookies`,`g_tk1`,`status`,`error`,`num`,`addtime`,`uptime`) VALUES (:uin, :cookies, :g_tk1, '1', :error, :num, :addtime, :uptime)";
        }

        return $DB->query($sql, $sql_data);
    }
}

if (strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
    exit('{"saveOK":-1}');
}

$login = new qq_qrlogin();
if ($_GET['do'] == 'qqlogin') {
    $login->qqlogin();
}
if ($_GET['do'] == 'getqrpic') {
    $login->getqrpic();
}
