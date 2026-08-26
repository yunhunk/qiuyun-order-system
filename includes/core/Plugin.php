<?php
namespace core;

!defined('DS') && define('DS', DIRECTORY_SEPARATOR);

/**
 * name   插件处理类
 * author 星河
 */

class Plugin
{
    private $plugin_id;

    //当前已支持的模板 此处用于记录方便查看无实际效果，可在服务端插件列表里面修改
    private static $plugin_template = ['simple', 'purpleYear', 'yunshang', 'zuan', 'oneui', 'nifty', 'hyper', 'default', 'cyui', 'appui', 'comic', 'colorful', 'classic', 'chenmeng', 'aswpay', 'qiuqiu'];

    //当前已支持的位置 未更新不准确
    private static $plugin_action = ['all', 'head', 'header', 'bottom', 'footer', 'login_bottom', 'reg_bottom'];

    /**
     * @param integer  $id  插件ID
     */
    public function __construct($id = 0)
    {
        $this->plugin_id = $id;
        return true;
    }

    /**
     * 安装
     */
    public static function install($id, $dirname)
    {
        //执行安装
        if (function_exists('plugin_install')) {
            return plugin_install($id, $dirname);
        }
        return ['code' => -1, 'msg' => '当前程序文件不完整，请先下载更新包覆盖或重新安装后再试！'];
    }

    /**
     * 刷新插件列表
     */
    public static function getList($sql, $offset, $pagesize)
    {
        global $DB;
        $dir = dirname(__DIR__) . DS . 'plugin' . DS;
        $arr = scandir($dir);
        if ($arr && count($arr)) {
            foreach ($arr as $key => $value) {
                if ($value == '.' || $value == '..') {
                    continue;
                }
                $file = $dir . $value . DS . 'config_sql.php';
                if (file_exists($file)) {
                    self::check($file);
                }
            }
        }
        return $DB->select("SELECT * FROM `pre_plugin` WHERE {$sql} order by id desc limit $offset,$pagesize");
    }

    public static function check($path = '')
    {
        global $DB, $date;

        $config = include $path;
        if (is_array($config)) {
            $dirname = addslashes($config['dirname']);
            $row     = $DB->get_row("SELECT * FROM `pre_plugin` where `dirname`='{$dirname}' limit 1");
            if (!$row) {
                $config['addtime']    = $date;
                $config['updatetime'] = $date;
                $config['type']       = $config['type'] == 'plugin' ? 0 : 1;
                $config['status']     = 0;
                $sql                  = "INSERT INTO `pre_plugin` ";
                $sql_keys             = '(';
                $sql_data             = ' VALUES (';
                foreach ($config as $key => $value) {
                    $key   = addcslashes($key, ',');
                    $value = addcslashes($value, ',');
                    $sql_keys .= "`{$key}`,";
                    $sql_data .= "'{$value}',";
                }
                $sql_keys = rtrim($sql_keys, ',') . ")";
                $sql_data = rtrim($sql_data, ',') . ")";
                $sql      = $sql . $sql_keys . $sql_data;
                if ($DB->insert($sql)) {
                    return true;
                }
                return $DB->error();
            }
        }
        return true;
    }

    /**
     * 绑定授权平台账号
     */
    public function binduser($user, $pwd)
    {
        //执行安装
        if (function_exists('plugin_binduser')) {
            return plugin_binduser($user, $pwd);
        }
        return ['code' => -1, 'msg' => '当前程序文件不完整，请先下载更新包覆盖或重新安装后再试！'];
    }

    /**
     * 更新
     */
    public static function update($id, $plugin_id, $dirname)
    {
        //执行安装
        if (function_exists('plugin_update')) {
            return plugin_update($id, $plugin_id, $dirname);
        }
        return ['code' => -1, 'msg' => '当前程序文件不完整，请先下载更新包覆盖或重新安装后再试！'];
    }

    /**
     * 卸载
     */
    public static function unInstall($id, $dirname)
    {
        global $DB;
        //通用插件
        $plugin_dir = ROOT . 'includes/plugin/' . $dirname;
        //对接插件
        $plugin_dir2 = ROOT . 'includes/core/extend/' . $dirname;
        $sql         = "DELETE FROM `pre_plugin` WHERE `id`=:id";
        $data        = [
            ':id' => $id,
        ];

        try {
            \core\File::delFiles($plugin_dir);
            \core\File::delFiles($plugin_dir2);
            $DB->query($sql, $data);
            return ['code' => 0, 'msg' => 'succ'];
        } catch (\PDOException $e) {
            return ['code' => -1, 'msg' => '数据库操作失败，' . $e->getMessage()];
        } catch (\Exception $e) {
            return ['code' => -1, 'msg' => '操作失败，' . $e->getMessage()];
        }
    }
    /**
     * 加载插件
     * @param  string    $action   钩子函数名称
     * @param  string    $options  传递参数
     * @param  function  $callFunc 回调函数
     */
    public static function load($action = '', $options = 'index', $callFunc = '')
    {
        global $DB;

        $count  = 0;
        $rs     = $DB->select("SELECT * FROM `pre_plugin` where `status`=1");
        $output = false;

        if ($action == 'view_show') {
            $output = true;
        }
        //禁止自动加载，避免报错
        !defined('CLASS_AUTOLOAD') && define('CLASS_AUTOLOAD', false);
        if (is_array($rs)) {
            $count = count($rs);
            foreach ($rs as $key => $row) {
                $fileDir = self::checkPath(ROOT . 'includes/plugin/' . $row['dirname']);
                if (file_exists($fileDir . '/index.php')) {
                    include_once self::checkPath($fileDir . '/index.php');
                    $classname = $row['dirname'];
                    try {
                        //实例化插件类
                        if (class_exists($classname)) {
                            $obj = new $classname($row);
                            if ($options === null || empty($options)) {
                                $options = 'index';
                            }

                            if (method_exists($obj, $action)) {
                                //调用与当前钩子名称一致的插件方法
                                if ($action == 'view_show') {
                                    // 保证 $output只被处理一次
                                    if ($output == true) {
                                        @$obj->$action($options, $output, $callFunc);
                                    }
                                } else {
                                    @$obj->$action($options, $callFunc);
                                }
                            } elseif (method_exists($obj, 'run')) {
                                //调用插件入口方法
                                @$obj->run($action, $options, $callFunc);
                            }
                        }
                    } catch (\Exception $e) {
                        @webLog_error('错误日志', $e->getMessage());
                        //入口文件不是类 就引入对应文件
                        $runfile = is_string($action) ? self::checkPath($fileDir . '/' . $action . '.php') : null;
                        if ($options !== 'index' && file_exists($runfile)) {
                            include_once $runfile;
                        }
                    }
                }
            }
        }

        //如果钩子<view_show>执行后缓冲区没有内容就直接输出模板代码
        if ($action == 'view_show' && $output == true) {
            $output = false;
            echo $options;
        }
        return;
    }
    /**
     * 处理文件路径的目录分隔符兼容
     */
    private static function checkPath($path)
    {
        return str_replace('/', DS, $path);
    }

    /**
     * 检测是否已输出视图
     *
     * @return bool
     */
    private static function checkViewShow($viewshow)
    {
        return isset($_COOKIE['viewshow']) && $_COOKIE['viewshow'] > $viewshow;
    }

}

function doAction($action, $func)
{
    //
}
