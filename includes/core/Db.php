<?php

namespace core;

/**
 * Db静态链式快捷类 By 星河
 */
class Db
{
    private static $instance = null;

    public $pdo = null;

    private $tablename       = '';
    private static $sql      = '';
    private $where           = null;
    private $order           = null;
    private $limit           = null;
    private $error           = '';
    private $data            = [];
    private $lock            = null;
    private $fields          = '';
    private static $dbstrans = false;

    public function __construct()
    {
        global $DB;
        $this->pdo = $DB;
        $this->pdo->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public static function error()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        if (self::$instance->error) {
            return self::$instance->error;
        }
        return self::$instance->pdo->error();
    }

    /**
     *
     * @param string $name
     * @return Db
     */
    public static function name($name)
    {

        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        include ROOT . 'dbconfig.php';

        // 自动追加前缀
        if ($dbconfig && isset($dbconfig['dbqz'])) {
            if (strpos($name, $dbconfig['dbqz'] . '_') === false) {
                $name = $dbconfig['dbqz'] . '_' . $name;
            }
            unset($dbconfig);
        }
        self::$instance->tablename = $name;

        // 重置数据
        self::$instance->where  = null;
        self::$instance->order  = null;
        self::$instance->limit  = null;
        self::$instance->error  = '';
        self::$instance->data   = [];
        self::$instance->fields = null;

        return self::$instance;
    }

    public static function query($sql)
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance->pdo->query($sql);
    }

    /**
     * 设置排序
     * @param  string $order
     */
    public function order($order = '')
    {
        $this->order = $order;
        return $this;
    }

    /**
     * 设置范围
     * @param  string $limit
     */
    public function limit($limit = '')
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * 设置or条件的Where子句
     * @param  [type] $data 条件数据
     * @return object
     */
    public function whereOr($data = null)
    {
        return $this->where($data, 'OR');
    }

    /**
     * 设置查询字段
     *
     * @param string|array $fields
     * @return Db
     */
    public function field($fields = '*')
    {
        $this->fields = is_array($fields) ? implode(',', $fields) : $fields;
        return $this;
    }

    /**
     * 设置锁
     *
     * @param boolean $lock
     * @return Db
     */
    public function lock($lock = false)
    {
        $this->lock = $lock;

        return $this;
    }

    /**
     * 设置Where子句
     * @param  [type] $data 条件数据  支持格式 ['name'=>'名称'] 或  ['name' => ['>', 50] ] 或  ['name' => ['between', [50,80] ]]
     * @param  string $type 条件类型 AND OR
     * @return Db
     */
    public function where($data = null, $type = 'AND')
    {
        if (strtoupper(trim($type)) == 'OR') {
            $type = 'OR';
        } else {
            $type = "AND";
        }
        if (is_array($data)) {
            $where = '';
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $w = strtoupper(trim($value[0]));
                    if (is_array($value[1])) {
                        $v = daddslashes($value[1]);
                    } else {
                        $v = addcslashes(stripslashes($value[1]), "'");
                    }

                    if (in_array($w, ['>', '<', '>=', '<=', '<>', '!=', '=', 'LIKE'])) {
                        if ($w == '!=') {
                            $w = '<>';
                        }
                        $where .= " `{$key}` {$w} '{$v}' {$type}";
                    } elseif (in_array($w, ['BETWEEN', 'NOT BETWEEN'])) {
                        if (isset($value[2]) && !is_array($value[2])) {
                            // ['id' => ['between', 50, 80]]
                            if (is_numeric($v) && is_numeric($value[2])) {
                                $where .= " `{$key}` {$w} {$v} AND {$value[2]} {$type}";
                            } else {
                                $where .= " `{$key}` {$w} '{$v}' AND '{$value[2]}' {$type}";
                            }
                        } else {
                            // ['id' => ['between', [ 50, 80]]]
                            // ['id' => ['between', '50,80']]
                            if (is_array($v)) {
                                $v = implode(',', $v);
                            }
                            $v = explode(',', $v, 2);
                            if (count($v) == 2) {
                                if (is_numeric($v[0])) {
                                    $where .= " `{$key}` {$w} {$v[0]} AND {$v[1]} {$type}";
                                } else {
                                    $where .= " `{$key}` {$w} '{$v[0]}' AND '{$v[1]}' {$type}";
                                }
                            }
                        }
                    } elseif (in_array($w, ['IN', 'NOT IN'])) {
                        //  ['id' => ['IN', [50, 80]]]
                        //  ['id' => ['IN', '50, 80, 90']]
                        if (is_array($v)) {
                            $v = array_map(function ($item) {
                                return is_numeric($item) ? $item : "'" . $item . "'";
                            }, $v);
                            $v = implode(',', $v);
                        }
                        $where .= " `{$key}` {$w} ({$v}) {$type}";
                    } else {
                        if (in_array($w, ['IS NULL', 'IS NOT NULL'])) {
                            $where .= " `{$key}` {$w} {$type}";
                        } else {
                            $where .= " `{$key}`='{$v}' {$type}";
                        }
                    }
                } else {
                    $value = addcslashes(stripslashes($value), "'");
                    $where .= " `{$key}`='{$value}' {$type}";
                }
            }
            if ($this->where) {
                $this->where .= " " . $type . " " . trim($where, $type);
            } else {
                $this->where = ' WHERE ' . trim($where, $type);
            }
        } else {
            if (trim($data)) {
                if ($this->where) {
                    $this->where .= " " . $type . " ( " . trim($data) . " )";
                } else {
                    $this->where = ' WHERE ' . trim($data);
                }
            }
        }
        return $this;
    }

    /**
     * 获取某行 比较复杂的条件可通过where方法设置
     * @param  array  $data 快捷设置AND条件数据
     */
    public function find($data = [])
    {
        $sql = $this->createSql('query', $data);
        return $this->execute('get_row', $sql);
    }

    /**
     * 获取某行 比较复杂的条件可通过where方法设置
     * @param  array  $data 快捷设置AND条件数据
     */
    public function get($data = [])
    {
        $sql = $this->createSql('query', $data);
        return $this->execute('get_row', $sql);
    }

    public function insert($data = [])
    {
        $sql = $this->createSql('insert', $data);
        return $this->execute('insert', $sql);
    }

    /**
     * 查询数据列表
     *
     * @param array $data
     * @return array|false
     */
    public function select()
    {
        $sql = $this->createSql('query');
        return $this->execute('select', $sql);
    }

    /**
     * 查询总行数 查询条件请使用where方法设置
     *
     * @param string $fields
     * @return int|false
     */
    public function count($fields = '*')
    {
        $sql = $this->createSql('count', $fields);
        return $this->execute('count', $sql);
    }

    /**
     * 查询总数 查询条件请使用where方法设置
     *
     * @param string $fields
     * @return int|false
     */
    public function sum($fields = '*')
    {
        $sql = $this->createSql('sum', $fields);
        return $this->execute('sum', $sql);
    }

    /**
     * column查询
     *
     * @param string $fields
     * @return array|false
     */
    public function column($fields = '*')
    {
        $sql = $this->createSql('column', $fields);
        return $this->execute('column', $sql);
    }

    /**
     * 更新数据
     *
     * @param array $data
     * @return int|false
     */
    public function update($data = [])
    {
        $sql  = $this->createSql('update', $data);
        $exec = $this->execute('update', $sql);
        return $exec > 0 ? $exec : $exec !== false;
    }

    /**
     * 删除数据
     *
     * @param array $data
     * @return int|false
     */
    public function delete($data = [])
    {
        $sql = $this->createSql('delete', $data);
        return $this->execute('delete', $sql);
    }

    /**
     * 获取上次执行SQL语句
     *
     * @return string
     */
    public static function getLastSql()
    {
        return static::$sql;
    }

    private function execute($method, $sql)
    {
        $method = strtolower($method);
        if ($method == 'update' || $method == 'delete') {
            $method = 'exec';
        }
        if (method_exists($this->pdo, $method)) {
            try {
                $result = $this->pdo->$method($sql);
                return $result;
            } catch (\PDOException $e) {
                $this->error = $e->getMessage();
                return false;
            }
        } else {
            throw new \Exception('DB CLASS has no method set：' . $method);
            return false;
        }
    }

    /**
     * 生成SQL语句
     * @param  string $type SQL类型 query insert update delete
     * @param  mixed  $data SQL数据
     * @return string
     */
    public function createSql($type = 'query', $data = [])
    {
        if (!$this->tablename) {
            throw new \Exception("tablename is not set");
            return '';
        }
        $type = strtolower(trim($type));
        switch ($type) {
            case 'update':
                $sql  = "UPDATE `{$this->tablename}` SET ";
                $sql2 = '';
                if ($this->where) {
                    $where = $this->where;
                    foreach ($data as $key => $value) {
                        $sql2 .= "`{$key}`='{$value}',";
                    }
                } else {
                    $field = $this->getkeyField($data);
                    if ($field) {
                        $where = " WHERE `{$field}`='" . ($data[$field] ?? '') . "'";
                    } else {
                        $where = " WHERE 1";
                    }
                    foreach ($data as $key => $value) {
                        if ($key != $field) {
                            $sql2 .= "`{$key}`='{$value}',";
                        }
                    }
                }
                $sql2 = trim($sql2, ',');
                $sql  = $sql . $sql2 . $where;
                break;
            case 'insert':
                $sql  = 'INSERT INTO `' . $this->tablename . '` ';
                $sql2 = '(';
                $sql3 = ' VALUES (';
                foreach ($data as $key => $value) {
                    $value = addcslashes(stripslashes($value), "'");
                    $sql2 .= "`{$key}`,";
                    $sql3 .= "'{$value}',";
                }
                $sql2 = trim($sql2, ',') . ")";
                $sql3 = trim($sql3, ',') . ")";
                $sql  = $sql . $sql2 . $sql3;
                break;
            case 'delete':
                $sql = 'DELETE FROM `' . $this->tablename . '` ';
                if ($this->where) {
                    $sql = $sql . $this->where;
                } else {
                    $where = ' WHERE ';
                    if (count($data) > 0) {
                        foreach ($data as $key => $value) {
                            $value = addcslashes(stripslashes($value), "'");
                            $where .= " `{$key}`='{$value}' AND";
                        }
                        $where = trim($where, 'AND');
                    } else {
                        $where = "1";
                    }
                    $sql = $sql . $where;
                }
                break;
            // 查询总数
            case 'count':
                // 查询条件字段
                if (is_string($data) && $data && trim($data) != '*') {
                    $fields = "`{$data}`";
                } else {
                    $fields = '*';
                }
                $sql = 'SELECT count(' . $fields . ') FROM `' . $this->tablename . '` ';

                if ($this->where) {
                    $where = $this->where;
                } else {
                    $where = ' WHERE 1';
                }
                $sql = $sql . $where;
                break;
            // 查询总值
            case 'sum':
                // 查询条件字段
                if (is_string($data) && $data) {
                    // 检查字段是否符合标准，不符合则是复杂表达式, 直接代入
                    if (preg_match('/^[a-zA-z]+[A-Za-z0-9]+$/', $data) == 0) {
                        $fields = "{$data}";
                    } else {
                        $fields = trim($data) != '*' ? "`{$data}`" : "*";
                    }
                } else {
                    $fields = '*';
                }
                $sql = 'SELECT sum(' . $fields . ') FROM `' . $this->tablename . '` ';

                if ($this->where) {
                    $where = $this->where;
                } else {
                    $where = ' WHERE 1';
                }
                $sql = $sql . $where;
                break;
            // 查询某个值
            case 'value':

                if (is_string($data) && $data) {
                    // 检查字段是否符合标准，不符合则是复杂表达式, 直接代入
                    if (preg_match('/^[a-zA-z]+[A-Za-z0-9]+$/', $data) == 0) {
                        $fields = "{$data}";
                    } else {
                        $fields = trim($data) != '*' ? "`{$data}`" : "*";
                    }
                } else {
                    $fields = '*';
                }

                $sql = 'SELECT ' . $fields . ' FROM `' . $this->tablename . '` ';

                if ($this->where) {
                    $where = $this->where;
                } else {
                    $where = ' WHERE 1';
                }
                $sql = $sql . $where;
                break;
            // 查询某个值
            case 'column':

                if (is_string($data) && $data) {
                    // 检查字段是否符合标准，不符合则是复杂表达式, 直接代入
                    if (preg_match('/^[a-zA-z]+[A-Za-z0-9]+$/', $data) == 0) {
                        $fields = "{$data}";
                    } else {
                        $fields = trim($data) != '*' ? "`{$data}`" : "*";
                    }
                } else {
                    $fields = '*';
                }

                if ($data) {
                    $sql = 'SELECT ' . $fields . ' FROM `' . $this->tablename . '` ';
                } else {
                    $sql = 'SELECT * FROM `' . $this->tablename . '` ';
                }

                if ($this->where) {
                    $where = $this->where;
                } else {
                    $where = ' WHERE 1';
                }
                $sql = $sql . $where;
                break;
            default:
                $sql = 'SELECT ' . ($this->fields ? $this->fields : '*') . ' FROM `' . $this->tablename . '` ';
                if ($this->where) {
                    $where = $this->where;
                } else {
                    $where = ' WHERE ';
                    if (count($data) > 0) {
                        foreach ($data as $key => $value) {
                            $value = addcslashes(stripslashes($value), "'");
                            $where .= " `{$key}`='{$value}' AND";
                        }
                        $where = trim($where, 'AND');
                    } else {
                        $where = " WHERE 1";
                    }
                }
                $sql = $sql . $where;

                break;
        }

        // 设置排序
        if ($sql && $this->order) {
            $sql .= ' ORDER BY ' . trim($this->order);
        }

        // 设置条目
        if ($sql && $this->limit) {
            $sql .= ' LIMIT ' . trim($this->limit);
        }

        // 设置锁
        if ($sql && $this->lock) {
            $sql .= ' for update';
        }

        //释放本次执行的SQL拼接数据，保证下次SQL语句不出错
        $this->where     = '';
        $this->data      = [];
        $this->order     = '';
        $this->limit     = '';
        $this->tablename = '';
        //echo $sql . "\n";
        //die($sql);

        self::$sql = $sql;
        return $sql;
    }

    /**
     * 开始事务
     *
     */
    public static function transaction()
    {

        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        self::$instance->pdo->transaction();
    }

    /**
     * 提交事务
     *
     */
    public static function commit()
    {

        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        self::$instance->pdo->commit();
    }

    /**
     * 提交事务
     *
     */
    public static function rollback()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        self::$instance->pdo->rollback();
    }

    /**
     * 获取主键字段
     * @param  array  $data SQL数据
     * @return string
     */
    private function getkeyField($data = [])
    {
        $sql = "SELECT COLUMN_NAME FROM
    INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME= '{$this->tablename}' AND COLUMN_KEY = 'PRI'";
        $rs = $this->pdo->get_row($sql);
        if (is_array($rs) && preg_match('/field=([\w\_]+)&/i', $rs[0], $match)) {
            return $match[1];
        }

        $list  = ['id', 'kid', 'pid', 'tid', 'sid'];
        $field = '';
        foreach ($data as $key => $value) {
            if (isset($data[$key])) {
                $field = $key;
                break;
            }
        }
        return $field;
    }

    /**
     * 关闭数据库
     */
    public function close()
    {
        if (method_exists($this->pdo, 'close')) {
            $this->pdo->close();
        }
        $this->pdo = null;
        return;
    }
}
