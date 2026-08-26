<?php

if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__) . '/');
}

/**
 *
 * 自动载入函数
 */
class Autoloader
{
    /**
     * 向PHP注册在自动载入函数
     */
    public static function register()
    {
        spl_autoload_register(array(new self, 'autoload'));
    }

    /**
     * 根据类名载入所在文件
     */
    public static function autoload($className)
    {
        if (class_exists($className)) {
            return;
        }

        $className = ltrim($className, '\\');
        // DIRECTORY_SEPARATOR：目录分隔符，linux上就是’/’    windows上是’\’
        $filePath = __DIR__ . DIRECTORY_SEPARATOR . $className;
        $filePath = str_replace('\\', DIRECTORY_SEPARATOR, $filePath) . '.php';
        if (file_exists($filePath)) {
            require_once $filePath;
            return;
            //                if(method_exists($className, "init")) {
            //                    call_user_func(array($className, "init"), $params);
            //                }
        } else {
            $filePath = __DIR__ . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . $className;
            $filePath = str_replace('\\', DIRECTORY_SEPARATOR, $filePath) . '.php';
            if (file_exists($filePath)) {
                require_once $filePath;
                return;
            } else {
                if (!defined('CLASS_AUTOLOAD') || CLASS_AUTOLOAD) {
                    $stacksInfo = self::getStacks();
                    if (strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest") {
                        $result = ['code' => -1, "msg" => "致命错误：无法加载" . $filePath . "<br/>" . $stacksInfo];
                        die(json_encode($result, JSON_UNESCAPED_UNICODE));
                    }
                    throw new \Exception("无法加载" . $filePath . "<br/>" . $stacksInfo);
                }
            }
        }
    }

    public static function getStacks()
    {
        $stacks = (new \Exception())->getTrace();
        $info   = '调用堆栈[Stacks Info]：<br/>';

        foreach ($stacks as $key => $value) {

            $file = $value['file'] ? str_replace(ROOT, '', $value['file']) : 'NULL';
            $info .= "\r\r\r\r|-#" . $key . " file[" . $file . "]; function[" . $value['function'] . "]; class[" . $value['class'] . "]; line[" . $value['line'] . "]<br/>";
        }
        return $info;
    }
}
