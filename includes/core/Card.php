<?php

namespace core;

/**
 * Name   卡密信息综合处理类
 * Author 星河
 * Time   2021-01-08 14:47
 */

class Card
{

    public function __construct()
    {

        return true;
    }

    /**
     * 解析json
     *
     * @param string $string 解析字符串
     * @return array|null    返回解析结果
     */
    public function json_decode($string = '')
    {
        $text  = trim(str_replace('\\"', '"', $string), "\xEF\xBB\xBF");
        $array = json_decode($text, true);
        if (is_array($array)) {
            return $array;
        }
        return json_decode('{' . $this->getSubstr($text, '{', '}') . '}', true);
    }

    /**
     * 截取字符串
     *
     * @param string $str
     * @param string $leftStr
     * @param string $rightStr
     * @return string
     */
    public function getSubstr($str = '', $leftStr = '', $rightStr = '')
    {
        $left  = strpos($str, $leftStr);
        $right = strrpos($str, $rightStr, $left);
        if ($left < 0 || $right < $left) {
            return '';
        }
        return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
    }

    /**
     * 获取下单数据
     *
     * @param array $row
     * @return array
     */
    public function parseInputData(array $row = [])
    {
        if (!is_array($row) || !array_key_exists('input', $row)) {
            return [];
        }
        $input   = [];
        $input[] = isset($row["input"]) ? $row["input"] : '';
        if (isset($row["input2"]) && $row["input2"]) {
            $input[] = $row["input2"];
        }

        if (isset($row["input3"]) && $row["input3"]) {
            $input[] = $row["input3"];
        }

        if (isset($row["input4"]) && $row["input4"]) {
            $input[] = $row["input4"];
        }

        if (isset($row["input5"]) && $row["input5"]) {
            $input[] = $row["input5"];
        }
        return $input;
    }

    /**
     * 设置商品上下架
     * @param [type] $active 状态
     * @param [type] $tid    商品ID
     */
    public function setToolActive($active, $tid)
    {
        global $DB, $conf;

        if ($conf['cron_status_sync'] != 1) {
            if ($conf['cron_status_close'] == 1) {
                $sql      = "UPDATE `pre_tools` set `active`=:active,`close`=:close where tid=:tid";
                $sql_data = array(
                    ':active' => $active,
                    ':close'  => $active ? 0 : 1,
                    ':tid'    => $tid,
                );
            } else {
                $sql      = "UPDATE `pre_tools` set `active`=:active where tid=:tid";
                $sql_data = array(
                    ':active' => $active ? 1 : 0,
                    ':tid'    => $tid,
                );
            }
            return $DB->query($sql, $sql_data);
        }
        return;
    }

    /**
     * 设置商品价格
     * @param [type] $price1 成本
     * @param [type] $tool   商品
     */
    public function setToolPrice($price1, $tool)
    {
        global $DB, $date, $conf;
        if ($price1 - 0 == 0 && $tool['cost2'] > 0) {
            return;
        }
        if ($conf['pricejk_edit'] == 0 && $price1 >= $tool['cost2'] || $conf['pricejk_edit'] == 1) {
            if ($tool['prid'] > 0) {
                $prow = $DB->get_row("SELECT * from cmy_price where id='" . $tool['prid'] . "' limit 1");
            }

            $update = false;
            if ($tool['prid'] > 0 && isset($prow['p_0'])) {
                $p_kind = $prow['kind'];
                $p_0    = $prow['p_0'];
                $p_1    = $prow['p_1'];
                $p_2    = $prow['p_2'];
                $update = true;
            } elseif ($conf['tool_price_open'] == 1 && $tool['price'] != 0) {
                $p_kind = $conf['tool_price_kind'];
                $p_0    = $conf['tool_price_0'];
                $p_1    = $conf['tool_price_1'];
                $p_2    = $conf['tool_price_2'];
                $update = true;
            }

            if ($update) {
                $price = sprintf('%.2f', $p_kind == 1 ? $p_0 + $price1 : $price1 + $price1 * $p_0 / 100);
                $cost  = sprintf('%.2f', $p_kind == 1 ? $p_1 + $price1 : $price1 + $price1 * $p_1 / 100);
                $cost2 = sprintf('%.2f', $p_kind == 1 ? $p_2 + $price1 : $price1 + $price1 * $p_2 / 100);
                if ($price >= $price1 && $price > 0) {
                    $sqlData = [
                        ':price1' => $price1,
                        ':price'  => $price,
                        ':cost'   => $cost,
                        ':cost2'  => $cost2,
                        ':uptime' => time(),
                        ':tid'    => $tool['tid'],
                    ];
                    if ($price - $tool['price'] != 0) {
                        $sql = $this->addToolLogs($tool, $price, '价格变动', '变动后信息 => 出售价:' . $price . ';专业价:' . $cost . ';旗舰价:' . $cost2 . ';成本价:' . $price1);
                        if ($sql !== true) {
                            die('错误：' . $sql);
                        }
                    }
                    return $DB->query("UPDATE `pre_tools` set `price1`=:price1,`price`=:price,`cost`=:cost,`cost2`=:cost2,`uptime`=:uptime where tid=:tid", $sqlData);
                } else {
                    //throw new \Exception("经过计算后出售价格小于成本价，或出售价格为0！");
                }
            } else {
                //throw new \Exception("商品 " . $tool['name'] . " (" . $tool['tid'] . ")加价模板[" . $tool['prid'] . "]不存在或未配置！");
            }
        } else {
            return $DB->query("UPDATE `pre_tools` set `price1`= ? where tid= ?", [$price1, $tool['tid']]);
        }
        return true;
    }

    /**
     * 添加商品日志
     * @param array   $tool
     * @param float   $after
     * @param string  $action
     * @param string  $desc
     */
    public function addToolLogs($tool = [], $after = 0.00, $action = '价格变动', $desc = '')
    {
        global $DB;
        $sql  = "INSERT INTO `pre_tools_log` (`tid`,`name`,`before`,`after`,`action`,`desc`,`addtime`) VALUES (:tid,:name,:before,:after,:action,:desc,:addtime)";
        $data = [
            ':tid'     => $tool['tid'],
            ':name'    => $tool['name'],
            ':before'  => $tool['price'],
            ':after'   => $after,
            ':action'  => $action,
            ':desc'    => $desc,
            ':addtime' => time(),
        ];
        if ($DB->exec($sql, $data)) {
            return true;
        }
        return $DB->error();
    }

    public function getCardData($row, $config, $kmArr)
    {
        global $DB;
        $count = $DB->count("SELECT count(*) FROM `pre_faka` WHERE `orderid`= ?", [$row['id']]);
        if ($count > 0) {
            return $this->getCards($row['id']);
        }
        $km = $this->fillerCard($config, $kmArr);
        return $this->addCardOrder($row['id'], $row['tid'], $km);
    }

    public function addCardOrder($orderid, $tid, $kmdata)
    {
        global $DB, $date;

        if (count($kmdata) > 0) {
            $sql    = "INSERT INTO `pre_faka` (`tid`, `km`, `pw`, `addtime`, `usetime`, `orderid`, `status`) VALUES ";
            $sql2   = '';
            $kmlist = "";
            foreach ($kmdata as $key => $row) {
                if (!empty($row['card']) && !empty($row['pass'])) {
                    $kahao = daddslashes($row['card']);
                    $kami  = daddslashes($row['pass']);
                    $kmlist .= $kahao . "----" . $kami . "<br>\r\n";
                    $sql2 .= "('" . $tid . "','" . $kahao . "','" . $kami . "','" . $date . "','" . $date . "','" . $orderid . "','0'),";
                } else {
                    if (!empty($row['pass'])) {
                        $kahao = daddslashes($row['pass']);
                        $kmlist .= $kahao . "<br>\r\n";
                        $sql2 .= "('" . $tid . "','" . $kahao . "','','" . $date . "','" . $date . "','" . $orderid . "','0'),";
                    } else {
                        if (!empty($row['card'])) {
                            $kahao = daddslashes($row['card']);
                            $kmlist .= $kahao . "<br>\r\n";
                            $sql2 .= "('" . $tid . "','" . $kahao . "','','" . $date . "','" . $date . "','" . $orderid . "','0'),";
                        }
                    }
                }
            }

            if ($sql2 !== '') {
                $sql = $sql . trim($sql2, ',');
                if ($DB->insert($sql)) {
                    return ['code' => 0, 'msg' => '导入成功', 'kmdata' => $kmlist];
                } else {
                    return ['code' => -1, 'msg' => '导入失败，' . $DB->error(), 'kmdata' => ''];
                }
            } else {
                return ['code' => -1, 'msg' => '卡密为空或json格式不正确，' . json_encode($kmdata), 'kmdata' => ''];
            }
        }

        return ['code' => -1, 'msg' => '卡密为空或格式不是正确的数组', 'kmdata' => ''];
    }

    private function getCards($orderid)
    {
        global $DB;
        $rs     = $DB->query("SELECT * FROM `pre_faka` WHERE `orderid`= ?", [$orderid]);
        $kmlist = '';
        if ($rs) {
            $list = $rs->fetchAll();
            foreach ($list as $row) {
                if (!empty($row['pw']) && !empty($row['km'])) {
                    $kmlist .= $row['km'] . "----" . $row['pw'] . "<br>\r\n";
                } else {
                    if (!empty($row['pw'])) {
                        $kmlist .= $row['pw'] . "<br>\r\n";
                    } else {
                        $kmlist .= $row['km'] . "<br>\r\n";
                    }
                }
            }
        }

        return ['code' => 0, 'msg' => '导入成功', 'kmdata' => $kmlist];
    }

    private function fillerCard($config, $kmArr)
    {
        if (!is_array($kmArr)) {
            //卡密信息不是数组，直接返回空数组
            return [];
        }
        $km = [];
        if ($config['type'] == 30) {
            //卡易信新版
            foreach ($kmArr as $item) {
                $km[] = [
                    'card' => $item['number'] . '',
                    'pass' => $item['pwd'],
                ];
            }
        } elseif ($config['type'] == 9) {
            //卡商
            foreach ($kmArr as $item) {
                $km[] = [
                    'card' => $item['no'] . '',
                    'pass' => $item['password'],
                ];
            }
        } elseif ($config['type'] == 22 || $config['type'] == 19) {
            //商战网、卡易速
            foreach ($kmArr as $item) {
                $km[] = [
                    'card' => $item['card_no'] . '',
                    'pass' => $item['card_password'],
                ];
            }
        } elseif ($config['type'] == 12 || $config['type'] == 13) {
            //星河、彩虹
            return $kmArr;
        } elseif ($config['type'] == 1) {
            //亿樂
            foreach ($kmArr as $value) {
                $km[]['card'] = $value;
            }
        } elseif ($config['type'] == 16) {
            //视多
            foreach ($kmArr as $item) {
                if (!empty($item['kano'])) {
                    $km[]['card'] = $item['kano'];
                }
            }
        } elseif ($config['type'] == 18) {
            //卡卡云
            foreach ($kmArr as $card) {
                if (!empty($card)) {
                    $km[]['card'] = $card;
                }
            }
        } elseif ($config['type'] == 15) {
            //优云宝
            foreach ($kmArr as $card) {
                if (gettype($card) == 'string') {
                    $km[]['card'] = $card;
                } elseif (gettype($card) == 'array') {
                    $km[]['card'] = $card['code'];
                } else {
                    $km[]['card'] = (string) $card;
                }
            }
        } elseif ($config['type'] == 21) {
            //时空云
            foreach ($kmArr as $item) {
                $km[] = [
                    'card' => $item['card'],
                    'pass' => $item['pass'],
                ];
            }
        } elseif ($config['type'] == 25) {
            //直客
            foreach ($kmArr as $key => $item) {
                if (isset($item['account'])) {
                    $km[] = [
                        'card' => $item['account'],
                        'pass' => $item['password'],
                    ];
                } else {
                    $km[] = [
                        'card' => $$item,
                        'pass' => '',
                    ];
                }
            }
        } else {
            //其他
            foreach ($kmArr as $key => $item) {
                if (isset($item['card_no'])) {
                    $km[] = [
                        'card' => $item['card_no'],
                        'pass' => $item['card_password'],
                    ];
                } elseif (isset($item['card'])) {
                    $km[] = [
                        'card' => $item['card'],
                        'pass' => $item['pass'],
                    ];
                } elseif (isset($item['km']) && isset($item['pw'])) {
                    $km[] = [
                        'card' => $item['km'],
                        'pass' => $item['pw'],
                    ];
                } elseif (isset($item['km'])) {
                    $km[] = [
                        'card' => $item['km'],
                        'pass' => '',
                    ];
                } elseif (isset($item[0])) {
                    $km[] = [
                        'card' => $item[0],
                        'pass' => $item[1],
                    ];
                } else {
                    $km[] = [
                        'card' => is_array($item) ? json_encode($item) : $item,
                        'pass' => '',
                    ];
                }
            }
        }
        if (count($km) > 0) {
            return $km;
        }
        return $kmArr;
    }
}
