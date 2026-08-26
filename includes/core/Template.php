<?php
/**
 * Name   模板缓存渲染类
 * Author 星河
 * Time   2020-09-28 16:51
 */
namespace core;

class Template
{
    private static $cacheDir   = ROOT . 'cache/template/';
    private static $fileExtend = 'html';
    private $zid               = '1';
    private $is_loading        = false;
    public $is_cache           = true;
    private $name              = 'index';
    public $template           = 'default';
    public $templateFile       = TEMPLATE_ROOT . 'default/index.php';
    private $filePath          = '';
    private $cacheContent      = '';
    private function _init()
    {
        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
            $this->is_loading = true;
        } elseif (isset($_GET["cid"]) || isset($_GET["tid"])) {
            $this->is_loading = true;
        }

        $actList = ['query', 'faka', 'search', 'chadan', 'gift'];
        $act     = isset($_GET["act"]) ? strip_tags(addslashes($_GET["act"])) : null;
        if (isset($_GET["act"]) && in_array($act, $actList)) {
            $this->is_loading = true;
        } elseif (isset($_GET["buyok"])) {
            $this->is_loading = true;
        } elseif (isset($_GET["mod"]) && in_array($_GET["mod"], ['cart', 'faka', 'invite', 'cart'])) {
            $this->is_loading = true;
        }
    }

    public static function getList()
    {
        global $conf;
        $dir        = TEMPLATE_ROOT;
        $dirArray[] = null;
        if (false != ($handle = opendir($dir))) {
            $i = 0;
            while (false !== ($file = readdir($handle))) {
                if ($conf['template_default_off'] == 1 && $file == 'default') {
                    continue;
                }

                if ($file != "." && $file != ".." && !strpos($file, ".")) {
                    $dirArray[$i] = $file;
                    $i++;
                }
            }
            closedir($handle);
        }
        return $dirArray;
    }

    public function getCacheFile($name)
    {
        global $DB, $conf, $siterow, $is_fenzhan;
        //$this->zid      = $is_fenzhan === true ? $siterow['zid'] : '1';
        if ($name !== "") {
            $this->name = $name;
        }
        $this->setTemplateFile();

        if ($conf['template_cache'] == 1 && !$this->is_loading) {
            $this->is_cache = true;
            // exit($this->name . $this->template);
            $row = $DB->get_row("SELECT * FROM `pre_template` WHERE name= ? and `template`= ?", [$this->name, $this->template]);
            if ($row && is_array($row)) {
                //exit(json_encode($row));
                if ($row['expires'] > time() && file_exists(self::$cacheDir . $row['filepath'])) {
                    return self::$cacheDir . $row['filepath'];
                } else {
                    @unlink(self::$cacheDir . $row['filepath']);
                    $ret = $this->setContent('update');
                }
            } else {
                $ret = $this->setContent('add');
            }

            if ($ret['code'] == 0 && file_exists($this->filePath)) {
                return $this->filePath;
            } else {
                $this->is_cache = false;
                return $this->templateFile;
            }
        } else {
            $this->is_cache = false;
            return $this->templateFile;
        }
    }

    private function setContent($_actionType = 'update')
    {
        global $conf, $siterow, $DB, $date, $cdnpublic, $cdnserver, $clientip;
        global $kfInfo, $isLogin2, $jsver, $addsalt_js, $is_mb, $addsalt;
        global $logo, $is_defend, $background_image, $repeat;
        $file = $this->templateFile;
        if (file_exists($file)) {
            //expires 过期时间，单位：秒。 无更改情况下建议60分钟左右
            $expires  = $conf['template_expires'] >= 1 ? $conf['template_expires'] : '3'; //单位：分钟
            $filename = md5($template . time()) . '.' . self::$fileExtend;
            if (!is_dir(self::$cacheDir)) {
                @mkdir(self::$cacheDir, 0755, true) or $this->showErrPage("创建模板引用目录时失败！");
            }
            $this->filePath = self::$cacheDir . $filename;
            ob_start();
            include_once $file;
            $pageData = ob_get_contents(); //获得页面内容
            ob_end_clean();
            if ($pageData !== "") {
                $pageData = $this->htmlReplace($pageData);
                $pageData = "<!--星河云PlusExpires： " . $date . " + " . $expires . " minute-->\n" . $pageData;
                $ret      = @file_put_contents($this->filePath, $pageData);
                if ($ret) {
                    $name     = $this->name;
                    $template = $this->template;
                    $expires  = $expires * 60 + time();
                    if ($_actionType === 'update') {
                        $sql     = "UPDATE `pre_template` set `filepath`=:filepath,`expires`=:expires,`uptime`=:uptime WHERE `name`=:name and `template`=:template";
                        $sqlData = [':filepath' => $filename, ':expires' => $expires, ':uptime' => $date, ':name' => $name, ':template' => $template];
                    } else {
                        $sql     = "INSERT INTO `pre_template` (`name`,`template`,`filepath`,`expires`,`uptime`) VALUES (:name, :template, :filepath, :expires, :uptime)";
                        $sqlData = [':name' => $name, ':template' => $template, ':filepath' => $filename, ':expires' => $expires, ':uptime' => $date];
                    }
                    if ($DB->query($sql, $sqlData)) {
                        return ['code' => 0, 'msg' => 'succ'];
                    } else {
                        @unlink($this->filePath);
                        return ['code' => -1, 'msg' => '保存缓存文件路径失败，' . $DB->error()];
                    }
                }
                return ['code' => -1, 'msg' => '写入缓存内容失败，请检查是否有写入权限：' . $this->filePath];
            } else {
                return ['code' => -1, 'msg' => '缓存内容为空，可能获取失败或模板文件不存在'];
            }
        } else {
            $this->showErrPage(); //模板不存在，输出错误页面
        }
    }

    public function viewReplace($pageHtml)
    {
        global $conf, $siterow, $addsalt_js, $addsalt, $is_fenzhan;
        if ($is_fenzhan === true) {
            $sitename    = $siterow['sitename'];
            $title       = $siterow['title'];
            $keywords    = $siterow['keywords'];
            $description = $siterow['description'];
        } else {
            $sitename    = $conf['sitename'];
            $title       = $conf['title'];
            $keywords    = $conf['keywords'];
            $description = $conf['description'];
        }
        if (stripos($pageHtml, '[sitename]') === false) {
            $this->showErrPage('当前模板内容异常或不支持缓存，请尝试关闭缓存功能后再试！');
        }

        $fileArr = ['index', 'buy', 'query', 'product'];
        if (in_array($this->name, $fileArr)) {
            $pageHtml = str_replace("[sitename]", $sitename, $pageHtml);
            $pageHtml = str_replace("[title]", $title, $pageHtml);
            $pageHtml = str_replace("[keywords]", $keywords, $pageHtml);
            $pageHtml = str_replace("[description]", $description, $pageHtml);
        } else {
            $pageHtml = str_replace("[sitename]", $sitename, $pageHtml);
            $pageHtml = str_replace("[keywords]", $keywords, $pageHtml);
            $pageHtml = str_replace("[description]", $description, $pageHtml);
        }
        $pageHtml = str_replace("[hashsalt]", 'hashsalt=' . $addsalt_js . ';// hashsalt', $pageHtml);
        $pageHtml = str_replace("[addsalt]", 'hashsalt=' . $addsalt . '&', $pageHtml);
        return $pageHtml;
    }

    private function htmlReplace($pageHtml)
    {
        //$pageHtml = str_ireplace(["\n", "\t", "  "], "", $pageHtml);//去除换行可能会引起js单行注释问题
        //$pageHtml = str_ireplace(["\t", "  "], "", $pageHtml);
        $fileArr = ['index', 'buy', 'query', 'product'];
        if (in_array($this->name, $fileArr)) {
            $pageHtml = str_ireplace($this->getSubstr($pageHtml, '<title>', '</title>'), "[sitename] - [title]", $pageHtml);
            $pageHtml = str_ireplace($this->getSubstr($pageHtml, 'name="keywords" content="', '">'), "[keywords]", $pageHtml);
            $pageHtml = str_ireplace($this->getSubstr($pageHtml, 'name="description" content="', '">'), "[description]", $pageHtml);
        } else {
            $pageHtml = str_ireplace($this->getSubstr($pageHtml, '<title>', '-'), "[sitename] ", $pageHtml);
            $pageHtml = str_ireplace($this->getSubstr($pageHtml, 'name="keywords" content="', '">'), "[keywords]", $pageHtml);
            $pageHtml = str_ireplace($this->getSubstr($pageHtml, 'name="description" content="', '">'), "[description]", $pageHtml);
        }

        $pageHtml = preg_replace("/hashsalt=[\s\(\)\{\}\[\]\+\!]+;/", "[hashsalt]", $pageHtml);
        $pageHtml = preg_replace("/hashsalt=[\w]+&/", "[addsalt]", $pageHtml);
        return $pageHtml;
    }

    private function getSubstr($str, $leftStr, $rightStr)
    {
        $left  = strpos($str, $leftStr);
        $right = strpos($str, $rightStr, $left);
        if ($left < 0 or $right < $left) {
            return '';
        }

        return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
    }

    public static function checkUserTpl($name)
    {
        global $conf;
        if ($conf['template_layout_off'] != 1) {
            if ($name == 'login' && $conf['template_layout_login_off'] == 1) {
                return false;
            }
            if ($name == 'reg' && $conf['template_layout_reg_off'] == 1) {
                return false;
            }
            if ($name == 'findpwd' && $conf['template_layout_findpwd_off'] == 1) {
                return false;
            }

            $Template = new Template();
            $Template->_init();
            $Template->setTemplateFile();
            $path = TEMPLATE_ROOT . $Template->template . '/user/' . $name . '.php';
            if (is_file($path)) {
                return $path;
            }
        }
        return false;
    }

    public static function load($name = 'index')
    {
        global $conf;
        $Template = new Template();
        $Template->_init();
        $loadfile = $Template->getCacheFile($name);

        if ($Template->is_cache === true) {
            $pageHtml = file_get_contents($loadfile);
            $pageHtml = $Template->viewReplace($pageHtml);
            echo $pageHtml;
            return true;
        } else {
            return $loadfile;
        }
    }

    public static function removeCache()
    {
        $fileList = scandir(self::$cacheDir);
        foreach ($fileList as $filename) {
            if (stripos($filename, '.' . self::$fileExtend) !== false) {
                @unlink(self::$cacheDir . $filename);
            }
        }
        return true;
    }

    /**
     * 是否需要验证是否真人
     */
    public static function isNeedCodeCaptcha($type = 'login')
    {
        global $conf;
        $Template = new Template();
        if ($Template->checkIsUserConsole()) {
            $Template->_init();
            $Template->setTemplateFile();
            $key = 'template_' . $Template->template . '_user_' . $type . '_captcha';
            if (isset($conf[$key])) {
                return $conf[$key] == 1;
            }
        }
        return $conf['captcha_open'] > 0 && $conf['captcha_open_' . $type] == 1;
    }

    /**
     * 是否用户后台
     * @return bool
     */
    private function checkIsUserConsole()
    {
        global $siteurl;
        if (strpos($siteurl, 'user/')) {
            return true;
        }
        return false;
    }

    private function setTemplateFile()
    {
        global $conf, $isLogin2, $weburl;
        $name = $this->name;
        if (!preg_match('/^[\w\-]+$/', $name)) {
            $this->showErrPage("模板异常[名称不支持特殊符号]，请联系站长{$conf['zzqq']}处理！");
        }

        $this->template = $this->getTemplate();

        if (in_array($this->template, ['mall', 'default', 'store', 'kkyone'])) {
            //该模板不支持缓存
            $this->is_loading = true;
        }

        $filename         = $this->template . '/' . $name . '.php';
        $filename_default = 'default/' . $name . '.php';

        if ($this->checkIsUserConsole()) {
            if ($conf['template_layout_off'] != 1) {
                $this->templateFile = TEMPLATE_ROOT . $filename;
            } else {
                $this->templateFile = TEMPLATE_ROOT . $filename_default;
            }
        } else {
            if (file_exists(TEMPLATE_ROOT . $filename)) {
                $this->templateFile = TEMPLATE_ROOT . $filename;
            } elseif (file_exists(TEMPLATE_ROOT . $filename_default)) {
                $this->template     = 'default';
                $this->templateFile = TEMPLATE_ROOT . $filename_default;
            } else {
                $query = [];
                foreach ($_GET as $key => $value) {
                    if ($key != 'mod' && $key != 'act') {
                        $query[$key] = $value;
                    }
                }
                @header("Location:" . rtrim($weburl, '/') . '?' . http_build_query($query));
                //$this->showErrPage('Template Error：File [' . $filename . '] Is not found');
            }
        }
    }

    public function getTemplate()
    {
        global $conf, $siterow, $is_fenzhan;
        if ($conf['template_fixed'] == 1) {
            if (checkmobile() && $this->checkMobileTemplate()) {
                $template = $conf['zz_template_mobile'] ? $conf['zz_template_mobile'] : 'default';
            } else {
                $template = $conf['zz_template'] ? $conf['zz_template'] : 'default';
            }
        } else {
            if ($is_fenzhan === true && $conf['fenzhan_template'] == 1) {
                if ($conf['template_default_off'] == 1 && $siterow['template'] == 'default') {
                    $template = $conf['template_default'] != "" ? $conf['template_default'] : $conf['template'];
                } else {
                    $template = $siterow['template'] != "" ? $siterow['template'] : 'default';
                    if (empty($template) || !file_exists(TEMPLATE_ROOT . $template . '/index.php')) {
                        $template = $conf['template_default'] ? $conf['template_default'] : 'default';
                    }
                }
            } else {
                if (checkmobile() && $this->checkMobileTemplate()) {
                    $template = $conf['template_mobile'] ? $conf['template_mobile'] : $conf['template'];
                } else {
                    $template = $conf['template'] ? $conf['template'] : ($conf['template_default'] ? $conf['template_default'] : 'default');
                }
            }
        }
        return $template;
    }

    private function checkMobileTemplate()
    {
        global $conf;
        return $conf['template_mobile_type'] == 0 && !empty($conf['zz_template_mobile']);
    }

    public static function exists($template)
    {
        $filename = TEMPLATE_ROOT . $template . '/index.php';
        if (file_exists($filename)) {
            return true;
        } else {
            return false;
        }
    }

    private function showErrPage($msg = '当前模板文件不存在！')
    {
        global $conf;
        @header("Content-Type:text/html; charset=UTF-8");
        $html = <<<'HTTML'
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>404页面错误！</title>
    <style>
        .container {
            width: 60%;
            margin: 10% auto 0;
            background-color: #f0f0f0;
            padding: 2% 5%;
            border-radius: 10px
        }

        ul {
            padding-left: 20px;
        }

            ul li {
                line-height: 2.3
            }

        a {
            color: #20a53a
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>404</h1>
        <h3>页面出错：
HTTML;
        $html .= $msg;
        $html .= <<<'HTTML2'
        </h3>
        <ul>
            <li>你可以联系网站管理员解决，联系QQ
HTTML2;
        $html .= $conf['zzqq'];
        $html .= <<<'HTTML3'
            </li>
            <li><a href="/user/login.php">用户登录</a>&nbsp;<a href="/user/reg.php">用户注册</a>&nbsp;</li>
        </ul>
    </div>
</body>
</html>
HTTML3;
        echo $html;
        die;
    }
}
