<?php
namespace core;

/**
 * base64页面加密类
 */
class BsEncode
{
    private static $instance = null;

    private $plugin_tvs_type = 0;

    private $status = 0;

    public function __construct()
    {
        return $this;
    }

    public static function start()
    {
        global $webConfig;
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        if ($webConfig['admin']['pageEncode'] && self::$instance->checkExistBase64() && ob_start()) {
            self::$instance->status = 1;
        }
        return self::$instance;
    }

    /**
     * 检测浏览器是否支持
     */
    private function checkExistBase64()
    {

        if (isset($_COOKIE['browser_base64'])) {
            if (isset($_COOKIE['browser_base64']) && $_COOKIE['browser_base64'] == '1') {
                setcookie('browser_base64', 1, time() + 86400 * 7, '/');
                return true;
            } else {
                setcookie('browser_base64', 0, time() + 86400 * 7, '/');
                return false;
            }
        } else {
            echo <<<Html
<html><head><meta http-equiv="pragma" content="no-cache"><meta http-equiv="cache-control" content="no-cache"><meta http-equiv="content-type" content="text/html;charset=utf-8"><title>浏览器检测中</title>
            <script>console.log("chenmYun 检测系统");function setCookie(name,value){var exp = new Date();exp.setTime(exp.getTime() + 86400 * 7);document.cookie = name + "="+ escape (value).replace(/\+/g, '%2B') + ";expires=" + exp.toGMTString() + ";path=/";}function getCookie(name){var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");if(arr=document.cookie.match(reg))return unescape(arr[2]);else return null;}; if("undefined" !== typeof window.atob){setCookie('browser_base64',1);}else{setCookie('browser_base64', 0);};setTimeout(function(){window.location.reload();}, 1500)</script></head><body><br/><br/><h3 style="text-align:center;">浏览器检测中，请稍后...</h3><br/><noscript>该页面需要浏览器支持JavaScript才能正常显示！<br/>请尝试先在浏览器设置-安全-高级开启【JavaScript脚本】再刷新本页!</noscript></body></html>
Html;
            die;
        }
    }

    /**
     * 获取Body顶部代码
     */
    public function getHeadHtml($html)
    {
        global $conf;
        if ($this->plugin_tvs_type == 1) {
            return '
<!DOCTYPE html>
<html>
<head>
</head>
';
        } else {
            $arr = explode('<body', $html);
            if (stripos($arr[0], '<head') !== false) {
                return $arr[0];
            } else {
                preg_match('/<body([\w\"\'\-\s\=\;\:\!\(\)]+)>|<body>|<body(\s+)>/', $html, $match);
                if (isset($match[0]) && $match[0]) {
                    return substr($html, 0, stripos($html, $match[0])) . $match[0];
                } else {
                    return substr($html, 0, stripos($html, '<body>')) . '<body>';
                }
            }
        }
    }

    /**
     * 获取Body代码
     */
    public function getBodyHtml($html)
    {
        if ($this->plugin_tvs_type == 1) {
            $text = $html;
        } else {
            $arr = explode('<body', $html, 2);
            if (stripos($arr[1], '</body') !== false) {
                $arr2 = explode('</body', $arr[1], 2);
                $text = '<body ' . $arr2[0];
            } else {
                preg_match('/<body([\w\"\'\-\s\=]+)>|<body>|<body(\s+)>/', $html, $match);
                if (isset($match[0]) && $match[0]) {
                    $text = getSubstr($html, $match[0], '</body');
                } else {
                    $text = getSubstr($html, '<body>', '</body');
                }
            }
        }

        return $text;
    }

    /**
     * 获取Body底部代码
     */
    public function getFooterHtml($html)
    {
        if ($this->plugin_tvs_type == 1) {
            if (!stripos($html, '</body>')) {
                return '</body></html>';
            }
            return '';
        } else {
            $arr = explode('</body>', $html, 2);
            if ($arr[1]) {
                return '</body>' . $arr[1];
            }
            return substr($html, stripos($html, '</body'));
        }
    }

    /**
     * 视图输出
     */
    public static function outPutHtml()
    {
        global $webConfig;
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        if ($webConfig['admin']['pageEncode'] && self::$instance->status != 1) {
            return false;
        }
        
        $html = ob_get_contents();
        ob_end_clean();
        $head   = self::$instance->getHeadHtml($html);
        $body   = self::$instance->getBodyHtml($html);
        $body   = str_ireplace('TYPE html', '', $body);
        $footer = self::$instance->getFooterHtml($html);
        if ($head) {
            //echo str_replace('</head>', '', $head);
            echo $head;
            echo <<<Html
            <script> var html = utf8to16(window.atob('
Html;
            //echo str_replace(' ', '+', base64_encode($body));
            echo base64_encode($body);
            //echo base64_encode($body . base64_decode('PHNjcmlwdD5jb25zb2xlLmxvZygi6KeG5Zu+5Yqg5a+GIEJ5IOayieaipiA4NTcyODU3MTEiKTs8L3NjcmlwdD4='));
            echo "'";
            echo <<<Html
));function utf8to16(str){var out,i,len,c;var char2,char3;out="";len=str.length;i=0;while(i<len){c=str.charCodeAt(i++);switch(c>>4){case 0:case 1:case 2:case 3:case 4:case 5:case 6:case 7:out+=str.charAt(i-1);break;case 12:case 13:char2=str.charCodeAt(i++);out+=String.fromCharCode(((c&0x1F)<<6)|(char2&0x3F));break;case 14:char2=str.charCodeAt(i++);char3=str.charCodeAt(i++);out+=String.fromCharCode(((c&0x0F)<<12)|((char2&0x3F)<<6)|((char3&0x3F)<<0));break}}return out};document.write(html);</script>
Html;
            echo $footer;
        } else {
            echo $html;
        }
    }
}
