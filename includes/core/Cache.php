<?php
namespace core;

class Cache
{
    public function read($key = 'config')
    {
        global $DB;
        $row = $DB->get_row("SELECT `v`,`expire`,`type` FROM cmy_cache WHERE k= ? limit 1", [$key]);

        if (!is_array($row) || $row['expire'] > 0 && $row['expire'] < time() || empty($row['v'])) {
            $this->clear($key);
            if ($key != 'config') {
                return null;
            }
            return $this->update();
        }

        if ($row['type'] == 'string') {
            return $row;
        } elseif ($row['type'] == 'array') {
            $config = json_decode($row['v'], true);
        } else {
            $config = unserialize($row['v']);
        }

        if (is_array($config)) {
            if ($key == 'config') {
                $temp = [];
                foreach ($config as $index => $value) {
                    $value          = $this->_stripslashes($value);
                    $value          = trim(str_replace('[rand]', '' . rand(1, 999), $value));
                    $config[$index] = $config['zz_' . $index] = $value;
                }
            } else {
                $config = $this->decodeData($config, $key);
            }
            return $config;
        }
        return $row['v'];
    }

    public function decodeData($config, $key = '')
    {
        $temp = [];
        foreach ($config as $index => $value) {
            if (is_array($value)) {
                $value = $this->decodeData($value);
            } else {
                $value = $this->_stripslashes($value);
            }
            $temp[$index] = $value;
        }
        return $temp;
    }

    private function _stripslashes($string)
    {
        $string = str_replace("\'", "'", $string);
        $string = str_replace("\/\/", "//", $string);
        $string = str_replace('\"', '"', $string);
        //return $string;
        return stripslashes($string);
    }

    public function save($key = 'config', $value, $expire = 600, $type = 'array')
    {
        global $DB;
        if (is_array($value)) {
            if ($type == 'array') {
                $value = json_encode($value);
            } else {
                $value = serialize($value);
            }
        }

        if ($expire > 0 && $expire < time()) {
            if ($expire > 86400) {
                $expire = 86400;
            }
            $expire = time() + $expire;
        }

        if ($expire > 0) {
            $sql = "REPLACE INTO cmy_cache (`k`,`v`,`expire`,`type`) VALUES ('" . $key . "', '" . addslashes($value) . "', '" . $expire . "', '" . $type . "')";
        } else {
            $sql = "REPLACE INTO cmy_cache (`k`,`v`,`type`) VALUES ('" . $key . "', '" . addslashes($value) . "', '" . $type . "')";
        }
        if ($DB->query($sql)) {
            return true;
        }
        return $DB->error();

    }
    public function pre_fetch()
    {
        global $_CACHE;
        $_CACHE = array();
        $cache  = $this->read('config');
        $_CACHE = array_merge(@unserialize($cache), $_COOKIE);
        if (empty($_CACHE['version']) || $_GET['clearcache']) {
            $_CACHE = $this->update();
        }

        return $_CACHE;
    }

    public function saveSetting($k, $v)
    {
        global $DB;
        $v = addslashes($v);
        return $DB->query("REPLACE INTO cmy_config SET v= ?,k= ?", [$v, $k]);
    }
    public function update()
    {
        global $DB;
        $data = $temp = array();
        $rs   = $DB->query('SELECT * FROM cmy_config');
        if ($rs) {
            while ($item = $DB->fetch($rs)) {
                $temp[$item['k']] = addslashes($item['v']);
                $data[$item['k']] = $this->_stripslashes($item['v']);
            }
        }
        $this->save('config', $temp, time() + 1800);
        foreach ($data as $key => $value) {
            if (strpos($key, 'zz_') === false) {
                $data['zz_' . $key] = $value;
            }
        }
        return $data;
    }

    public function config($key = null, $value = null)
    {
        global $DB;
        // if (!is_null($key) && !is_null($value)) {
        //     return $this->saveSetting($key, $value);
        // }
        $DB->query("DELETE FROM cmy_config WHERE `k`= 'update_sql' OR `k`= 'updateData'");
        $data = array();
        $rs   = $DB->query('SELECT * FROM cmy_config');
        if ($rs) {
            while ($item = $DB->fetch($rs)) {
                $data['zz_' . $item['k']] = $data[$item['k']] = $this->_stripslashes($item['v']);
            }
        }
        return $data;
    }

    public function get()
    {
        global $DB;
        $data = $temp = array();
        $rs   = $DB->query('SELECT * FROM cmy_config');
        if ($rs) {
            while ($item = $DB->fetch($rs)) {
                $data['zz_' . $item['k']] = $data[$item['k']] = $this->_stripslashes($item['v']);
            }
        }
        return $data;
    }

    public function clear($key = 'config')
    {
        global $DB;
        return $DB->query("UPDATE cmy_cache SET v='' where k=?", [$key]);
    }
}
