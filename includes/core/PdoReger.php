<?php
namespace core;

use core\File;

//调试模式 在需要时开启可写入日志到本地文件
define('pdo_debug', 1);
define('pdo_escape_debug', 0);
// 调试日志写入模式 0 时间戳  1 运行脚本文件名 2 表名
define('pdo_debug_type', 1);
if (extension_loaded('pdo_mysql')) {
    define('pdo_type', 'mysql');
} else {
    die("DB_ERROR:服务器环境未成功开启pdo安全数据库扩展，请在对应的php版本的php.ini开启\n宝塔面板：请在软件商店搜索安装postgresql程序并进入安装好版本\n虚拟主机：请联系服务器商配置好再试");
}

class PdoReger
{
    private $handle;
    private $type;
    private $host;
    private $dbname;
    private $user;
    private $password;
    private $port;
    private $dsn;
    private $_sql;
    protected $options;
    private $startTime;
    private $endTime;
    private $stmt  = null;
    private $error = [];
    /** @var \PDO  */
    public $pdo;
    private $dbqz         = 'pre_';
    private $dbqz_list    = ['pre_', 'pre_', 'dsw_', 'shua_', 'cmds_'];
    private $dbqz_replace = true;

    private $webConfig    = [];
    private $exception_on = false;
    private $logFileRand  = '';

    private $dbTransaction = false;

    public function __construct($options = [])
    {
        $this->webConfig = [];
        if (file_exists(__DIR__ . 'config.php')) {
            $this->webConfig = @include __DIR__ . 'config.php';
        }

        if (empty($options['dbuser']) || empty($options['dbhost']) || empty($options['dbpwd'])) {
            die('错误：数据库账号密码配置信息不完整，请删除【install/install.lock】后重新安装！');
        }

        $this->dbname   = !empty($options['dbname']) ? $options['dbname'] : $options['dbuser'];
        $this->user     = $options['dbuser'];
        $this->password = $options['dbpwd'];
        $this->port     = $options['dbport'];
        $this->type     = pdo_type;
        $this->host     = $options['dbhost'];
        $this->dbqz     = $options['dbqz'];
        $this->dsn      = "{$this->type}:host={$this->host};port={$this->port};dbname={$this->dbname}";
        $this->options  = [
            \PDO::ATTR_CASE             => \PDO::CASE_NATURAL, //设置数据库字段保持不变
            \PDO::ATTR_EMULATE_PREPARES => false, //关闭pdo模拟功能
            \PDO::ATTR_PERSISTENT       => true, //开启持久链接
        ];

        try {
            //实例化pdo对象
            $this->pdo = new \PDO($this->dsn, $this->user, $this->password, $this->options);
            $this->pdo->exec("set sql_mode = ''");
            $this->pdo->exec("set names 'utf8mb4'");
            $this->delLogs();
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->exception_on = true;
            //模拟句柄
            $this->handle      = time() . rand(11111, 99999);
            $this->logFileRand = sha1($this->random(32) . mt_rand(11111, 99999));
            //echo '连接成功';
            $this->clearLog();
            return $this->pdo;
        } catch (\PDOException $e) {
            //抛出异常
            throw new \Exception($e->getMessage());
            return false;
        }
    }

    /**
     * 清理数据库日志
     */
    private function clearLog()
    {
        $dirName = __DIR__ . "/sqlLog/";
        if (is_dir($dirName)) {
            $arr = scandir($dirName);
            $d   = date("Ymd");
            foreach ($arr as $key => $name) {
                if ($name == '.' || $name == '..') {
                    continue;
                }
                if ($d - $name > 2 && stripos($dirName . $name, 'sqlLog') !== false) {
                    File::delFiles($dirName . $name);
                }
            }
        }
    }

    /**
     * 插入数据
     *
     * @param string $sql sql语句
     * @param array $data 绑定参数数据
     * @param array $_param
     * @return array
     */
    public function insert(string $sql = '', array $data = [], array $_param = [])
    {
        if ($this->exec($sql, $data)) {
            return $this->pdo->lastInsertId();
        } else {
            return false;
        }
    }

    //查询数据，成功返回数据数组
    public function select($sql, $_data = array())
    {

        $stmt = $this->query($sql, $_data);
        if ($stmt) {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        return false;
    }

    //更新数据
    public function update($_sql = '', $_data = array())
    {
        $stmt = $this->exec($_sql, $_data);
        return $stmt;
    }

    /**
     * sum查询
     *
     * @param string $sql
     * @param array $_data
     * @return void
     */
    public function sum($sql = '', $_data = array())
    {
        $stmt = $this->query($sql, $_data);
        if ($stmt) {
            $row = $stmt->fetch(\PDO::FETCH_NUM);
            return isset($row[0]) ? floatval($row[0]) : 0;
        }
        return false;
    }

    /**
     * value查询
     *
     * @param string $sql
     * @param array $_data
     * @return void
     */
    public function value($sql = '', $_data = array())
    {

        $stmt = $this->query($sql, $_data);
        if ($stmt) {
            $row = $stmt->fetch(\PDO::FETCH_NUM);
            return isset($row[0]) ? $row[0] : 0;
        }
        return false;
    }

    //更新、插入、删除数据
    public function exec($_sql, $_data = array())
    {
        $this->startTime = $this->msectime();

        $sql = $_sql = $this->preHandle($_sql);
        try {
            if (count($_data) > 0) {
                $sql = $this->exec_escape($_sql, $_data);
                return $this->pdo->exec($sql);
            } else {
                return $this->pdo->exec($_sql);
            }
        } catch (\PDOException $e) {
            $this->endTime = $this->msectime();
            $this->writeLogs("SQL[" . $sql . "] DATA[" . $_data . "] Error[" . $e->getMessage() . "]");
            $this->setErrror($e->getMessage());
            return false;
        } catch (\Throwable $th) {
            $this->endTime = $this->msectime();
            $this->writeLogs("SQL[" . $sql . "] DATA[" . $_data . "] Error[" . $th->getMessage() . "]");
            $this->setErrror($th->getMessage());
            return false;
        }
    }

    public function checkKeyIsStr($_data)
    {
        foreach ($_data as $key => $value) {
            if (is_numeric($key)) {
                return false;
            }
        }
        return true;
    }

    public function preHandle($sql)
    {
        if ($this->dbqz_replace === true) {
            return str_replace($this->dbqz_list, $this->dbqz . '_', $sql);
        }
        return $sql;
    }

    /**
     * 设置是否替换前缀
     *
     * @param boolean $dbqz
     * @return void
     */
    public function setDbqz($dbqz = true)
    {
        $this->dbqz_replace = $dbqz;
    }

    /**
     * 设置是否替换前缀列表
     *
     * @param boolean $dbqz
     * @return void
     */
    public function setDbqzList($dbqz_list = ['pre_', 'pre_', 'cmy_'])
    {
        $this->dbqz_list = $dbqz_list;
    }

    public function msectime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return sprintf('%.0f', (floatval($usec) + floatval($sec)) * 1000);
    }

    //执行查询
    public function query($sql, $data = array(), $_param = array())
    {
        $sql        = $this->preHandle($sql);
        $this->_sql = $sql;

        //mysql下SQL函数NOW()可能会导致服务器500的问题处理
        if (stripos($sql, 'NOW()') !== false) {
            $date = date("Y-m-d H:i:s");
            $date = "'" . $date . "'";
            $sql  = str_ireplace('NOW()', $date, $sql);
        }
        $this->startTime = $this->msectime();
        try {
            if (is_array($data) && count($data) > 0) {
                //创建pdo预处理对象
                $stmt = $this->pdo->prepare($sql);
                if ($stmt) {
                    //转义处理
                    $data = $this->escape($data);
                    $stmt->execute($data);
                }
                \PDO::PARAM_STR;
                $this->endTime = $this->msectime();
                $this->writeLogs("SQL[" . $sql . "]");
                return $stmt;
            } else {
                //执行查询方法
                $stmt          = $this->pdo->query($sql);
                $this->endTime = $this->msectime();
                $this->writeLogs("SQL[" . $sql . "]");
                return $stmt;
            }
        } catch (\PDOException $e) {
            $this->endTime = $this->msectime();
            $this->writeLogs("SQL[" . $sql . "] Error[" . $e->getMessage() . "]");
            $this->setErrror($e->getMessage());
            return false;
        } catch (\Throwable $th) {
            $this->endTime = $this->msectime();
            $this->writeLogs("SQL[" . $sql . "] Error[" . $th->getMessage() . "]");
            $this->setErrror($th->getMessage());
            return false;
        }
    }

    public function escape($data = [], $_param = array())
    {
        $preg = '/version\(\)|where\s|delete\s|update\s|select\s|information_schema|user\(\)|union|database\(|`|concat\(|into\s|values\(|values(\s|\s+)\(|extractvalue\(|updatexml\(|\sschema|connection_id\s|exp\(|sleep\(|load_file|benchmark|file_put_contents\(|urldecode\(|system|substring\(|substr\(|fopen\(|popen\(|phpinfo\(|alter\s|scandir\(|shell_exec\(|eval\(|assert\(|execute\(|set\s|bin\(|ascii\(|concat_ws\(|group_concat\(|left\sjoin|datadir|greatest|from\s|exists/i';

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (preg_match_all($preg, $value, $matches)) {
                    if (isset($matches[0]) && count($matches[0]) > 0) {
                        foreach ($matches[0] as $k => $v) {
                            $value = str_ireplace($v, '', $value);
                        }
                    }
                }
                $value      = addcslashes(stripcslashes($value), "'");
                $data[$key] = $value;
            }
            return $data;
        } else {
            if (preg_match_all($preg, $data, $matches)) {
                if (isset($matches[0]) && count($matches[0]) > 0) {
                    foreach ($matches[0] as $k => $v) {
                        $data = str_ireplace($v, '', $data);
                    }
                }
            }
            return addcslashes(stripcslashes($data), "'");
        }
    }

    //自定义安全替换处理函数 支持?占位符和:占位符
    public function exec_escape($_sql, $_data = array())
    {
        $preg = '/version\(\)|where\s|delete\s|update\s|select\s|information_schema|user\(\)|union|database\(|`|concat\(|into\s|values\(|values(\s|\s+)\(|extractvalue\(|updatexml\(|\sschema|connection_id\s|exp\(|sleep\(|load_file|benchmark|file_put_contents\(|urldecode\(|system|substring\(|substr\(|fopen\(|popen\(|phpinfo\(|alter\s|scandir\(|shell_exec\(|eval\(|assert\(|execute\(|set\s|bin\(|ascii\(|concat_ws\(|group_concat\(|left\sjoin|datadir|greatest|from\s|exists/i';

        if (preg_match('/:[a-zA-Z0-9_]+/', $_sql) > 0) {
            preg_match_all('/:[a-zA-Z0-9_]+/', $_sql, $matches);
            if (isset($matches[0]) && count($matches[0]) > 0) {
                $fields = $matches[0];
                //$fields = $this->sortField($matches[0]);
                foreach ($fields as $i => $key) {
                    if (array_key_exists($key, $_data)) {
                        $string = $_data[$key];
                        //过滤SQL注入语句
                        $string = preg_replace($preg, '', $string);
                        $_sql   = $this->sqlReplace($_sql, $key, $string);
                    }
                }
            }

            $this->writeLogs("\n替换后的SQL：" . $_sql);
            return $_sql;
        } else {
            $arr = explode('?', $_sql);
            // $this->writeLogs("\n?占位符分割数据：" . json_encode($arr));
            $sql   = '';
            $count = count($arr);
            if (count($_data) == $count - 1) {
                foreach ($arr as $index => $value) {
                    if (isset($_data[$index])) {
                        //处理占位符有对应数据时 ?占位符是SQL语句的最后一个有效字符时需注意
                        $string = $_data[$index];
                        //过滤SQL注入语句
                        $string = preg_replace($preg, '', $string);
                        if (strpos(strtolower($value), 'limit') !== false) {
                            //处理LIMIT前
                            $sql .= $value . intval($string);
                        } elseif (trim($value) == ',' && strpos($arr[$index - 1], 'limit') !== false) {
                            //处理LIMIT后
                            $sql .= $value . intval($string);
                        } else {
                            $sql .= $value . "'" . addcslashes(stripcslashes($string), "'") . "'";
                        }
                    } else {
                        $sql .= $value;
                    }
                }
                $this->writeLogs("\n替换后的SQL[" . $sql . "]");
            } else {
                $this->writeLogs("\n错误，占位符数量和数据数量不一致！SQL[" . $sql . "]，Data[" . $_data . "]");
            }
            return $sql;
        }
    }

    private function sqlReplace($sql = '', $key = '', $value = '')
    {
        // $this->writeLogs("替换断点：" . $sql . "\nkey：" . $key . "\nvalue：" . $value);
        $arr = explode($key, $sql, 2);
        if (count($arr) == 2) {
            if ($this->isLimitBefore($arr[0]) === true || $this->isLimitAfter($arr[0]) === true) {
                return $arr[0] . intval(addslashes($value)) . $arr[1];
            } else {
                return $arr[0] . "'" . addcslashes(stripslashes($value), "'") . "'" . $arr[1];
            }
        }
        return $sql;
    }

    /**
     * 当前占位符前面的sql语句是否需要limit参数1
     * @param  string  $sql SQL语句
     * @param  string  $key 占位符
     * @return boolean
     */
    private function isLimitBefore($sql = '', $key = '')
    {
        if (empty($sql)) {
            return false;
        }
        if (empty($key)) {
            return preg_match('/limit$/i', trim($sql)) > 0;
        } else {
            $arr = explode($key, $sql, 2);
            if (count($arr) == 2) {
                $str = trim($arr[0]);
                return preg_match('/limit$/i', $str) > 0;
            }
        }
        return false;
    }

    /**
     * 当前占位符前面的sql语句是否需要limit参数2
     * @param  string  $sql SQL语句
     * @param  string  $key 占位符
     * @return boolean
     */
    private function isLimitAfter($sql = '', $key = '')
    {
        if (empty($sql)) {
            return false;
        }
        if (empty($key)) {
            return preg_match('/limit(\s|\s+)([\d]+),$/i', trim($sql)) > 0;
        } else {
            $arr = explode($key, $sql, 2);
            if (count($arr) == 2) {
                $str = trim($arr[0]);
                return preg_match('/limit(\s|\s+)([\d]+),$/i', $str) > 0;
            }
        }
        return false;
    }

    //占位符排序
    public function sortField($arr)
    {
        for ($i = 0; $i < count($arr); $i++) {
            for ($x = $i + 1; $x < count($arr); $x++) {
                if (strlen($arr[$i]) < strlen($arr[$x])) {
                    $str_temp = $arr[$i];
                    $arr[$i]  = $arr[$x];
                    $arr[$x]  = $str_temp;
                }
            }
        }
        return $arr;
    }

    public function sortInStrlen($a, $b)
    {
        return strlen($a) < strlen($b);
    }

    public function random($length, $numeric = 0)
    {
        $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
        $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
        $hash = '';
        $max  = strlen($seed) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $seed[mt_rand(0, $max)];
        }
        return $hash;
    }

    //写入日志
    public function writeLogs($msg)
    {
        if (pdo_debug || pdo_escape_debug) {
            $dirName = __DIR__ . "/sqlLog";
            if (!is_dir($dirName)) {
                @mkdir($dirName);
            }
            $dirName .= '/' . date("Ymd");
            if (!is_dir($dirName)) {
                @mkdir($dirName);
            }

            $i = ceil(date("i") / 10);

            $times = $this->endTime - $this->startTime;
            if ($times > 1000) {
                $time = sprintf('%.3f', $times / 1000) . 's';
            } else {
                $time = $times . 'ms';
            }

            if ($times >= 10000) {
                $dirName .= '/error';
            } elseif ($times >= 5000) {
                $dirName .= '/danger';
            } elseif ($times >= 1000) {
                $dirName .= '/warn';
            } elseif ($times >= 500) {
                $dirName .= '/info';
            } else {
                $dirName .= '/succ';
            }

            if (!is_dir($dirName)) {
                @mkdir($dirName);
            }

            if (pdo_debug_type == 1) {
                // 脚本文件名
                if (defined('SYS_KEY')) {
                    $filename = $dirName . '/' . ($times > 300 ? 'danger_' : 'succ_') . $this->getRunPathFileName() . '_' . date('H') . "_" . $i . "_" . $this->logFileRand . ".txt";
                } else {
                    $filename = $dirName . '/' . ($times > 300 ? 'danger_' : 'succ_') . $this->getRunPathFileName() . '_' . date('H') . "_" . $i . "_cacheRun" . "_" . $this->logFileRand . ".txt";
                }
            } else {
                if (defined('SYS_KEY')) {
                    $filename = $dirName . '/' . 'sql_' . date('H') . "_" . $i . "_" . $this->logFileRand . ".txt";
                } else {
                    $filename = $dirName . '/' . 'sql_' . date('H') . "_" . $i . "_cacheRun" . "_" . $this->logFileRand . ".txt";
                }
            }

            $fp = fopen($filename, "a");
            if ($fp) {
                flock($fp, LOCK_EX);
                fwrite($fp, "\n[" . date("Y-m-d H:i:s") . "] Handle <" . $this->handle . "> " . $msg . "; Time[" . $time . "]");
                flock($fp, LOCK_UN);
                fclose($fp);
            }
        }
        return true;
    }

    public function getRunPathFileName($filePath = '')
    {
        if (empty($filePath)) {
            $filePath = $_SERVER["REQUEST_URI"];
        }
        $fileName = str_replace('/', '_', $filePath);
        $fileName = str_replace(':', '', $fileName);
        $fileName = substr($fileName, 0, strripos($fileName, '?'));
        return $fileName;
    }

    //新增日志
    public function addLogs($sql, $data = null, $error = null)
    {
        if (is_array($data)) {
            $data = json_encode($data);
        }
        if (is_null($error)) {
            $error = $this->error();
            if (!$error) {
                $error = 'unknown';
            }
        }
        $msg     = "Error" . $error . ";\n[Sql: " . $sql . "]; [Data: " . $data . "]";
        $dirName = __DIR__ . "/sqlLog";
        if (!is_dir($dirName)) {
            @mkdir($dirName);
        }
        $dirName .= '/' . date("Ymd");
        if (!is_dir($dirName)) {
            @mkdir($dirName);
        }
        $i = ceil(date("i") / 10);

        $times = $this->endTime - $this->startTime;

        if ($times >= 10000) {
            $dirName .= '/error';
        } elseif ($times >= 5000) {
            $dirName .= '/danger';
        } elseif ($times >= 1000) {
            $dirName .= '/warn';
        } elseif ($times >= 500) {
            $dirName .= '/info';
        } else {
            $dirName .= '/succ';
        }

        if (!is_dir($dirName)) {
            @mkdir($dirName);
        }

        if (pdo_debug_type == 1) {
            // 脚本文件名
            if (defined('SYS_KEY')) {
                $filename = $dirName . '/' . ($times > 300 ? 'danger_' : 'succ_') . $this->getRunPathFileName() . '_' . date('H') . "_" . $i . "_" . $this->logFileRand . ".txt";
            } else {
                $filename = $dirName . '/' . ($times > 300 ? 'danger_' : 'succ_') . $this->getRunPathFileName() . '_' . date('H') . "_" . $i . "_cacheRun" . "_" . $this->logFileRand . ".txt";
            }
        } else {
            if (defined('SYS_KEY')) {
                $filename = $dirName . '/' . 'sql_' . date('H') . "_" . $i . "_" . $this->logFileRand . ".txt";
            } else {
                $filename = $dirName . '/' . 'sql_' . date('H') . "_" . $i . "_cacheRun" . "_" . $this->logFileRand . ".txt";
            }
        }

        $fp = fopen($filename, "a");
        flock($fp, LOCK_EX);
        fwrite($fp, "\n---------------------------\nTime：" . date("Y-m-d H:i:s") . "\n" . $msg . "\n");
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }

    //删除日志
    public function delLogs()
    {
        $this->clearLog();
    }

    //删除日志
    private function delLogFile($dir)
    {
        if (is_dir($dir)) {
            $files = opendir($dir);
            if (is_array($files) && count($files)) {
                foreach ($files as $key => $filename) {
                    if ($filename == '.' || $filename == '..') {
                        continue;
                    }
                    if (is_dir($dir . '/' . $filename)) {
                        $this->delLogFile($dir . '/' . $filename);
                    } else {
                        @unlink($dir . '/' . $filename);
                    }
                }
            }
        }
    }

    /**
     * 错误信息
     */
    public function error()
    {
        if (count($this->error) > 0) {
            $error = count($this->error) > 1 ? "<br/>\n" : "";
            foreach ($this->error as $key => $value) {
                $error .= "#{$key}" . $value . "<br/>\n";
            }
            return "数据库错误=> " . $error;
        } else {
            return "数据库错误=> " . '[' . $this->pdo->errorInfo()[0] . ']' . $this->pdo->errorInfo()[1] . $this->pdo->errorInfo()[2];
        }
    }

    /**
     * 获取错误信息
     */
    public function getError()
    {
        return $this->error();
    }

    /**
     * 获取一行
     */
    public function get_row($sql, $data = array(), $_param = array())
    {
        if ($stmt = $this->query($sql, $data, $_param)) {
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }
        return false;
    }

    /**
     * 获取一行
     */
    public function getRow($sql, $data = array(), $_param = array())
    {
        if ($stmt = $this->query($sql, $data, $_param)) {
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }
        return false;
    }

    /**
     * 获取一行
     */
    public function find($sql, $data = array(), $_param = array())
    {
        if ($stmt = $this->query($sql, $data, $_param)) {
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }
        return false;
    }

    /**
     * 获取一行
     */
    public function get($sql, $data = array(), $_param = array())
    {
        if ($stmt = $this->query($sql, $data, $_param)) {
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }
        return false;
    }

    /**
     * 查询总和
     */
    public function count($sql, $data = array(), $_param = array())
    {

        if (count($data) > 0) {
            if (stripos($sql, 'count(*)') === false && stripos($sql, 'sum(') === false) {
                $sql_c = getSubstr(strtolower($sql), 'select', 'from');
                $sql   = str_ireplace($sql_c, ' count(*) ', $sql);
            }
        }

        if ($stmt = $this->query($sql, $data)) {
            $rows = $stmt->fetch(\PDO::FETCH_NUM);
            return isset($rows[0]) ? $rows[0] : 0;
        } else {
            return false;
        }
    }

    /**
     * 获取下一行
     */
    public function fetch($stmt)
    {
        //执行操作
        if (!$stmt) {
            return false;
        }
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * 根据数据集获取全部
     */
    public function fetchAll($stmt)
    {
        //执行操作
        if (!$stmt) {
            return false;
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    //获取字段值
    public function get_column($sql, $data = array(), $_param = array())
    {
        if ($stmt = $this->query($sql, $data, $_param)) {
            return $stmt->fetchColumn();
        } else {
            return false;
        }
    }

    //获取字段值
    public function getColumn($sql, $data = array(), $_param = array())
    {
        if ($stmt = $this->query($sql, $data, $_param)) {
            return $stmt->fetchColumn();
        } else {
            return false;
        }
    }

    //获取字段值
    public function column($sql, $data = array(), $_param = array())
    {
        if ($stmt = $this->query($sql, $data, $_param)) {
            return $stmt->fetchColumn();
        } else {
            return false;
        }
    }

    public function affected()
    {
        return 0;
    }

    public function close()
    {
        //关闭\PDO链接
        return $this->pdo = null;

    }
    public function setErrror($value)
    {
        $this->error[] = $value;
        return $this;
    }

    /**
     * 开始事务
     *
     */
    public function transaction()
    {
        return true;
        if ($this->dbTransaction == true) {
            return;
        }

        $session_id = session_id();
        $key        = trim($_SERVER['REQUEST_URI']);
        $key        = $key ? $key : '200';
        $key        = str_replace('/', '_', $key);
        if (!$session_id) {
            $session_id = md5($_SERVER['REQUEST_URI']);
        }

        $file = __DIR__ . '/db/transaction_' . $session_id . '.txt';

        if (!is_dir(dirname($file))) {
            @mkdir(dirname($file));
        }

        if (is_file($file) && file_get_contents($file) == $key || $this->dbTransaction == true) {
            return;
        }

        $this->dbTransaction = true;

        file_put_contents($file, $key);
        // $this->query("BEGIN");
    }

    /**
     * 提交事务
     *
     * @return void
     */
    public function commit()
    {
        return true;

        $session_id = session_id();

        $key = trim($_SERVER['REQUEST_URI']);
        $key = $key ? $key : '200';
        $key = str_replace('/', '_', $key);

        if (!$session_id) {
            $session_id = md5($_SERVER['REQUEST_URI']);
        }

        $file = __DIR__ . '/db/transaction_' . $session_id . '.txt';

        if (!is_file($file) || file_get_contents($file) != $key) {
            return;
        }

        $this->dbTransaction = false;
        // $this->query("COMMIT");

        @unlink($file);

    }

    /**
     * 回滚事务
     *
     * @return void
     */
    public function rollback()
    {
        return true;

        $session_id = session_id();

        $key = trim($_SERVER['REQUEST_URI']);
        $key = $key ? $key : '200';
        $key = str_replace('/', '_', $key);

        if (!$session_id) {
            $session_id = md5($_SERVER['REQUEST_URI']);
        }

        $file = __DIR__ . '/db/transaction_' . $session_id . '.txt';

        if (!is_file($file) || file_get_contents($file) != $key) {
            return;
        }

        // $this->pdo->query("ROLLBACK");

        $this->dbTransaction = false;
        @unlink($file);
    }
}
