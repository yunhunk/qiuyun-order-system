<?php
namespace core;

class Log
{
    private $name;
    private $date;
    private $dir;
    private $file;
    private $dirname;
    private $path;
    private $saveDay     = 15;
    private $writeParams = true;

    /**
     * @param [integer]  $_zid     所属站点ID
     * @param [integer]  $_saveDay 保留最近几天的
     */
    public function __construct($_zid = 1, $_saveDay = 15, $_name = 'Fenzan')
    {
        global $conf;
        $this->date = date("Ymd");
        $this->name = $_name;
        $this->zid  = $_zid;
        if ($_saveDay > 0) {
            $this->saveDay = $_saveDay;
        }
        $this->dirname = ROOT . "includes/logs/" . $this->name . "/";
        $this->setLogDir();
        $this->delDir($this->dirname);
        if ($this->isOldVersion(['Fz_', '^\d{8}$'])) {
            $this->delOldLog(['Fz_', '^\d{8}$']);
        }
        return true;
    }

    public function setWriteParams($v = false)
    {
        $this->writeParams = $v;
    }

    /**
     * 写入日志文件
     * @param  string $action 类型
     * @param  string $msg   内容 可空
     */
    public function writeLog($action, $msg)
    {
        $txt = "----------------\n" . date("Y-m-d H:i:s") . "\n类型：" . $action . "\n内容：" . $msg . "\n";
        $fp  = fopen($this->path, "a");
        if ($fp) {
            flock($fp, LOCK_EX);
            fwrite($fp, $txt);
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    /**
     * 添加自动日志
     * @param  string $action 类型
     * @param  string|object|array $msg    内容 可空
     */
    public function add($action, $msg = [])
    {
        $runPath = $this->getRunPath();
        if (!$this->checkWhite($runPath)) {
            $txt = "-----" . date("Y-m-d H:i:s") . "-----\n类型：" . $action;

            if (is_object($msg) || is_array($msg)) {
                $msg = json_encode($msg, 256);
            }

            if ($msg) {
                $txt .= "\n内容：" . $msg;
            }

            if ($this->writeParams) {
                $txt .= "\nPOST:" . json_encode($_POST) . "\nGET:" . json_encode($_GET) . "\nrunPath:" . $runPath;
            }
            $txt .= "\n";
            $fp = fopen($this->path, "a");
            if ($fp) {
                flock($fp, LOCK_EX);
                fwrite($fp, $txt);
                flock($fp, LOCK_UN);
                fclose($fp);
            }
        }
    }

    private function checkWhite($runPath)
    {
        if (preg_match('/^\/\?cid=[\d]+&tid=[\d]+$/', $runPath)) {
            return true;
        } elseif (preg_match('/^\/ajax\.php\?act=getcount$/', $runPath)) {
            return true;
        }
        return false;
    }

    private function getRunPath()
    {
        if (!empty($_SERVER["REQUEST_URI"])) {
            $scriptName = $_SERVER["REQUEST_URI"];
        } else {
            $scriptName = $_SERVER["PHP_SELF"];
        }
        $s = stripos($scriptName, '?');
        if ($s > 0) {
            $scriptName = substr($scriptName, 0, $s);
        }
        return $scriptName;
    }

    /**
     * 设置日志文件目录
     * @param  [type] $dir 文件夹路径
     */
    private function setLogDir()
    {
        $this->dir = $this->dirname;
        if (!is_dir(dirname($this->dir))) {
            @mkdir(dirname($this->dir));
        }
        if (!is_dir($this->dir)) {
            @mkdir($this->dir);
        }
        $this->dir = rtrim($this->dir, '/') . '/' . $this->date;
        if (!is_dir($this->dir)) {
            @mkdir($this->dir);
        }
        $this->dir = rtrim($this->dir, '/') . '/' . $this->zid;
        if (!is_dir($this->dir)) {
            @mkdir($this->dir);
        }
        $this->setLogFile();
        return true;
    }

    /**
     * 设置日志文件名称
     */
    private function setLogFile()
    {
        $h = date("H");
        $i = date("i");
        if ($i > 30) {
            $e    = $h + 1;
            $file = $h . ':30~' . $e . ':00_' . substr(md5($this->zid . ' 11111111' . $h), 0, 12) . '.txt';
        } else {
            $file = $h . ':00~' . $h . ':30_' . substr(md5($this->zid . ' 11111111' . $h), 0, 12) . '.txt';
        }
        $this->file = $file;
        $this->path = $this->dir . '/' . $file;
        return true;
    }

    /**
     * 删除旧版日志
     */
    private function isOldVersion($version = [])
    {
        global $conf;
        if (!isset($conf['extend_log_version']) || $conf['extend_log_version'] != '2.1.0') {
            $files = @scandir(ROOT . "includes/logs/");
            $ok    = false;
            if ($files && count($files) > 2) {
                foreach ($files as $key => $filename) {
                    if ($filename === "." || $filename === "..") {
                        continue;
                    }
                    if ($this->checkInArr($filename, $version)) {
                        $ok = true;
                        break;
                    }
                }
            }
            saveSetting('extend_log_version', '2.1.0');
            return $ok;
        }
        return false;
    }

    /**
     * 删除旧版日志
     */
    private function checkInArr($filename, $version = [])
    {
        $in = false;
        if (is_array($version)) {
            foreach ($version as $key => $value) {
                if (preg_match('/' . $value . '/', $filename)) {
                    $in = true;
                    break;
                }
            }
        }
        return $in;
    }

    /**
     * 删除旧版日志
     */
    private function delOldLog($version = [])
    {
        global $CACHE;
        $dir   = ROOT . "includes/logs/";
        $files = @scandir($dir);
        if (count($files) > 2) {
            foreach ($files as $key => $filename) {
                if ($filename === "." || $filename === "..") {
                    continue;
                }
                if ($this->checkInArr($filename, $version)) {
                    if (is_dir($dir . $filename)) {
                        $this->delFile($dir . $filename . '/');
                        @rmdir($dir);
                    }
                }
            }
        }
        saveSetting('extend_log_version', '2.1.0');
        $CACHE->clear();
    }

    /**
     * 递归删除过期日志文件
     * @param  [type] $dir 文件夹路径
     */
    private function delDir($dir)
    {
        $dir = rtrim($dir, '/') . '/';
        if (!is_dir($dir)) {
            return;
        }
        $files = scandir($dir);
        $time  = date("Ymd");
        if (count($files) > 0) {
            foreach ($files as $filename) {
                if ($filename === "." || $filename === "..") {
                    continue;
                }
                if (is_dir($dir . $filename)) {
                    $dirTime = intval($filename);
                    $dirDay  = floor($time - $dirTime);
                    if ($dirDay > $this->saveDay && $this->saveDay > 0) {
                        $this->delFile($dir . $filename . '/');
                        @rmdir($dir);
                    }
                }
            }
        }
        return true;
    }

    /**
     * 递归删除目录文件
     * @param  string $dir 文件夹路径
     */
    private function delFile($dir)
    {
        $dir   = rtrim($dir, '/') . '/';
        $files = scandir($dir);
        if (count($files) > 2) {
            foreach ($files as $filename) {
                if ($filename === "." || $filename === "..") {
                    continue;
                }
                if (is_dir($dir . $filename)) {
                    //递归删除
                    $this->delFile($dir . $filename . '/');
                } else {
                    //删除文件
                    @unlink($dir . $filename);
                }
            }
        }
        //最后删除当前目录
        @rmdir($dir);
        return true;
    }
}
