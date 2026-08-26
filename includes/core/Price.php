<?php
namespace core;

class Price
{
    private $zid;
    private $mid;
    private $priced; //分站价格设置开关
    private $power;
    public $upzid;
    public $uppower;
    public $uprow;
    private $user;
    private $endtime;
    private $super;
    private $price_array        = array(); //分站价格
    private $up_price_array     = array(); //分站上级价格
    private $iprice_array       = array(); //密价
    private $price_super        = array(); //密价列表
    private $price_stock        = array(); //商品单规格
    private $tool               = array(); //商品
    private $temp               = array(); //临时数据
    private static $price_rules = null;

    public function __construct($zid, $siterow = null)
    {
        global $isLogin2, $DB;

        if (!$siterow) {
            $siterow = $this->getSiteInfo($zid);
        }

        $this->endtime = $siterow['endtime'];

        if ($isLogin2 == 1) {
            $this->super = 1;
        }

        $this->zid    = $zid;
        $this->mid    = $siterow['mid'];
        $this->power  = $siterow['power'];
        $this->priced = $siterow['is_priced'];

        if ($siterow['power'] == 2) {
            $this->price_array    = @unserialize($siterow['price']);
            $this->up_price_array = null;
        } elseif ($siterow['power'] == 1) {
            $this->price_array = @unserialize($siterow['price']);
            if ($row = $DB->get_row("SELECT upzid,zid,price,power FROM cmy_site WHERE zid= ? and power=2 limit 1", [$siterow['upzid']])) {
                $this->up_price_array = @unserialize($row['price']);
                $this->upzid          = $row['zid'];
                $this->uppower        = $row['power'];
                $this->uprow          = $row;
            }
        } elseif ($siterow['power'] == 0) {
            $this->user        = true;
            $this->price_array = null;
            if ($data = $DB->get_row("SELECT upzid,zid,price,power FROM cmy_site WHERE zid= ? limit 1", array($siterow['upzid']))) {
                $this->up_price_array = @unserialize($data['price']);
                $this->upzid          = $data['zid'];
                $this->uppower        = $data['power'];
                $this->uprow          = $data;
            }
        }
        if (!empty($siterow['iprice'])) {
            $this->iprice_array = @json_decode($siterow['iprice'], true);
        }

        $this->updatePriceRules();
        if ($this->mid) {
            $this->updateSuperPrice();
        }
    }

    private function getAmount($kind, $price1, $value = 0)
    {
        if ($kind == 3) {
            // 固定价格
            return $this->priceFormat($value);
        }
        return $this->priceFormat($kind == 1 ? $price1 + $value : $value * $price1 / 100 + $price1);
    }

    public function setSuper($super = 0)
    {
        $this->super = $super;
        return;
    }

    public function getToolPrice($tid)
    {
        global $conf;
        if ($this->tool['tid'] != $tid) {
            $this->tool = $this->getToolInfo($tid);
        }

        //初始售价
        $price = $this->tool['price'];

        if ($this->zid > 1) {
            if ($this->power > 0 && $this->priced != 1) {
                if ($this->power == 2) {
                    $buyPrice = $this->getToolCost2($tid);
                } else {
                    $buyPrice = $this->getToolCost($tid);
                }

                $price1 = $this->tool['price1'];

                if ($buyPrice > 0 && $this->isSetPrice($tid)) {
                    //读取当前站点设置的售价
                    $price = $buyPrice * $this->getSetPrice($tid);
                    if ($price == 0 || $price <= $price1 && $price1 > 0) {
                        $price = $this->tool['price'];
                    }
                } else if ($this->checkUpSite($this->upzid) && $this->isUpSetPrice($tid)) {
                    //读取当前站点上级设置的售价
                    $upcost = $this->getSiteBuyPrice($tid, $this->upzid);
                    if ($upcost > 0) {
                        $num   = $this->getUpSetPrice($tid);
                        $price = $num * $upcost;
                        if ($price == 0 || $price <= $price1 && $price1 > 0) {
                            $price = $this->tool['price'];
                        }
                    }
                }
                return sprintf("%.2f", $price);
            }
        }

        if (function_exists('getDiscount')) {
            $price = getDiscount($price, $this->tool['discount']);
        }
        return $this->priceFormat($price);
    }

    /**
     * 价格是否设置倍数
     */
    private function isSetPrice($tid = 0, $lv = 0)
    {
        if ($lv == 2) {
            //设置的下级旗舰倍数
            $key = 'cost2';
        } elseif ($lv == 1) {
            //设置的下级专业倍数
            $key = 'cost';
        } else {
            //设置的售价倍数
            $key = 'price';
        }
        if (isset($this->price_array[$tid])) {
            return $this->price_array[$tid]['up'][$key] > 1;
        }
        return false;
    }

    /**
     * 验证上级是否存在
     */
    private function checkUpSite($upzid = 0, $zid = null)
    {
        if ($upzid > 0) {
            $row = $this->getSiteInfo($upzid);
            if ($zid) {
                $row2 = $this->getSiteInfo($zid);
                if (is_array($row) && $row['power'] > $row2['power']) {
                    //此处存疑 是否需要上级等级必须大于下级
                    return true;
                }
            } else {
                if (is_array($row) && $row['power'] > $this->power) {
                    //此处存疑 是否需要上级等级必须大于下级
                    return true;
                }
            }
            return false;
        }
        return false;
    }

    /**
     * 验证上级的上级是否存在
     */
    private function checkUp2UpSite($upzid = 0, $zid = null)
    {
        if ($upzid > 0) {
            $row = $this->getSiteInfo($upzid);
            if ($zid) {
                $row2 = $this->getSiteInfo($zid);
                if (is_array($row) && $row['power'] > $row2['power']) {
                    //此处存疑 是否需要上级等级必须大于下级
                    return true;
                }
            } else {
                if (is_array($row) && $row['power'] > $this->power) {
                    //此处存疑 是否需要上级等级必须大于下级
                    return true;
                }
            }
            return false;
        }
        return false;
    }

    /**
     * 获取设置倍数
     */
    private function getSetPrice($tid = 0, $lv = 0)
    {
        if ($lv == 1) {
            $key = 'cost';
        } else {
            $key = 'price';
        }
        if (isset($this->price_array[$tid]['up'][$key])) {
            return $this->price_array[$tid]['up'][$key];
        }
        return 1;
    }

    /**
     * 上级售价是否设置倍数
     */
    private function isUpSetPrice($tid = 0, $lv = 0, $zid = 0)
    {
        global $DB;
        if ($lv == 1) {
            //设置的下级专业倍数
            $key = 'cost';
        } else {
            //设置的售价倍数
            $key = 'price';
        }
        if ($zid > 0) {
            if (isset($this->temp['site'][$zid]) && is_array($this->temp['site'][$zid])) {
                $row = $this->temp['site'][$zid];
            } else {
                $row = $DB->get_row("SELECT * FROM cmy_site WHERE zid= ? limit 1", [$zid]);
            }
            if (is_array($row) && $row['price']) {
                if (!isset($this->temp['site'][$zid])) {
                    $this->temp['site'][$zid] = $row;
                }
                $arr = unserialize($row['price']);
                return is_array($arr) && isset($arr[$tid]) && $arr[$tid]['up'][$key] > 1;
            }
            return false;
        } else {
            if (is_array($this->up_price_array) && isset($this->up_price_array[$tid])) {
                return $this->up_price_array[$tid]['up'][$key] > 1;
            }
            return false;
        }
    }

    /**
     * 获取上级价格设置倍数
     */
    private function getUpSetPrice($tid = 0, $lv = 0, $zid = 0)
    {
        global $DB;
        if ($lv == 1) {
            $key = 'cost';
        } else {
            $key = 'price';
        }
        if ($zid > 0) {
            if (isset($this->temp['site'][$zid]) && is_array($this->temp['site'][$zid])) {
                $row = $this->temp['site'][$zid];
            } else {
                $row = $DB->get_row("SELECT * FROM cmy_site WHERE zid= ? limit 1", [$zid]);
            }
            if (is_array($row) && $row['price']) {
                if (!isset($this->temp['site'][$zid])) {
                    $this->temp['site'][$zid] = $row;
                }
                $arr = @unserialize($row['price']);
                return $arr[$tid]['up'][$key];
            }
        } else {
            if (isset($this->up_price_array[$tid]['up'][$key])) {
                return $this->up_price_array[$tid]['up'][$key];
            }
        }
        return 1;
    }

    public function getToolPrice1($tid)
    {
        global $conf;
        if ($this->tool['tid'] != $tid) {
            $this->tool = $this->getToolInfo($tid);
        }

        if ($this->tool['price1'] > 0) {
            $price1 = $this->tool['price1'];
        } else if ($this->tool['cost2'] > 0) {
            $price1 = $this->tool['cost2'];
        } else if ($this->tool['cost'] > 0) {
            $price1 = $this->tool['cost'];
        } else {
            $price1 = $this->tool['price'];
        }
        return $this->priceFormat($price1);
    }

    public function getSubPriceLv1($tid)
    {
        if ($this->tool['tid'] != $tid) {
            $this->tool = $this->getToolInfo($tid);
        }

        $cost = $this->tool['cost'] > 0 ? $this->tool['cost'] : $this->tool['price'];

        //如果当前是旗舰版且登录状态，就显示下级设置价格
        if ($this->power == 2 && $this->super == 1) {
            $cost2 = $this->getToolCost2($tid);
            if ($this->price_array[$tid]['up']['cost'] > 1 && $cost2 > 0) {
                $cost = $cost2 * $this->price_array[$tid]['up']['cost'];
            } else {
                if ($this->tool['cost'] > 0) {
                    $cost = $this->tool['cost'];
                } else {
                    $cost = $this->tool['price'];
                }
            }
            if (function_exists('getDiscount')) {
                $cost = getDiscount($cost, $this->tool['discount']);
            }
            return $this->priceFormat($cost);
        }
        return $this->priceFormat($cost);
    }

    public function getToolCost($tid, $noset = 0)
    {
        if ($this->tool['tid'] != $tid) {
            $this->tool = $this->getToolInfo($tid);
        }

        if ($noset) {
            return $this->priceFormat($this->tool['cost']);
        }

        //获取密价
        $cost = $this->getAlonePrice($tid);
        if (!is_null($cost)) {
            return $this->priceFormat($cost);
        }

        if ($this->tool['cost'] > 0) {
            $cost = $this->tool['cost'];
        } else {
            $cost = $this->tool['price'];
        }

        if ($this->power == 1 && $this->checkUpSite($this->upzid)) {
            //读取上级设置的专业价格
            if ($this->isUpSetPrice($tid, 1)) {
                $upcost = $this->getSiteBuyPrice($tid, $this->upzid);
                if ($upcost > 0) {
                    $cost2 = $upcost * $this->getUpSetPrice($tid, 1);
                    if ($cost2 > $upcost) {
                        return $this->priceFormat($cost2);
                    }
                }
            }
        }
        return $this->priceFormat($cost);
    }

    public function getToolCost2($tid, $noset = 0)
    {
        if ($this->tool['tid'] != $tid) {
            $this->tool = $this->getToolInfo($tid);
        }

        if ($noset) {
            return $this->priceFormat($this->tool['cost2']);
        }

        //获取密价
        $cost = $this->getAlonePrice($tid);
        if (!is_null($cost)) {
            return $this->priceFormat($cost);
        }

        if ($this->power == 2 && $this->tool['cost2'] > 0) {
            $cost = $this->tool['cost2'];
        } else {
            $cost = $this->tool['cost'];
        }
        return $this->priceFormat($cost);
    }

    public function getToolDel($tid)
    {
        if ($this->tool['tid'] != $tid) {
            $this->tool = $this->getToolInfo($tid);
        }

        return $this->price_array[$tid]['del'] == 1 ? 1 : 0;
    }

    /**
     * 是否被设置单独密价
     */
    private function isSetIPrice($tid = 0)
    {
        if ($this->super === 1) {
            return isset($this->iprice_array[$tid]) && is_array($this->iprice_array[$tid]);
        }
        return false;
    }

    /**
     * 是否被设置通用密价
     */
    private function isHasIPrice($tid = 0)
    {
        if ($this->super === 1 && $this->mid > 0) {
            return is_array($this->price_super) && $this->price_super['mid'];
        }
        return false;
    }

    /**
     * 是否设置密价
     * @param  integer $tid 商品ID
     */
    public function isHasAlonePrice($tid = 0)
    {
        return $this->isSetIPrice($tid) || $this->isHasIPrice($tid);
    }

    /**
     * 获取密价 未设置返回null
     * @param  integer $tid 商品ID
     */
    public function getAlonePrice($tid = 0)
    {

        //未登录直接返回null
        if ($this->super != 1) {
            return null;
        }

        if ($this->isHasIPrice($tid)) {
            //通用密价
            $price1 = $this->getToolPrice1($tid);
            if (isset($this->tool['price1'])) {
                if ($this->price_super['type'] == 1) {
                    $price = $price1 + $this->price_super['cost'];
                } else {
                    $price = $price1 + $this->price_super['cost'] / 100 * $price1;
                }
            } else {
                $this->mid = 0;
                $price     = $this->getBuyPrice($tid);
            }
            return $this->priceFormat($price);
        } elseif ($this->isSetIPrice($tid)) {
            //单独商品密价
            $price1 = $this->getToolPrice1($tid);
            if (isset($this->tool['price1']) && isset($this->iprice_array[$tid])) {
                $iprice = $this->iprice_array[$tid];
                $price  = $this->getAmount($iprice['kind'], $price1, $iprice['price']);
            } else {
                $this->iprice_array[$tid] = null;
                $price                    = $this->getBuyPrice($tid);
            }
            return $this->priceFormat($price);
        } else {
            return null;
        }
    }

    public function getBuyPrice($tid)
    {
        global $conf;
        if ($this->tool['tid'] != $tid) {
            $this->tool = $this->getToolInfo($tid);
        }

        $price1 = $this->getToolPrice1($tid);
        $price  = $this->tool['price'];

        //获取密价
        $buyPrice = $this->getAlonePrice($tid);
        if (!is_null($buyPrice)) {
            return $this->priceFormat($buyPrice);
        } else {
            if ($this->super == 1) {
                //登录状态下
                if ($this->power == 2) {
                    //旗舰站点价格
                    $price = $this->getToolCost2($tid);
                } elseif ($this->power == 1) {
                    //专业站点价格
                    $price = $this->getToolCost($tid);
                } else {
                    if ($conf['user_level'] == 1) {
                        //专业版购价
                        $price = $this->tool['cost'];
                    } elseif ($this->checkUpSite($this->upzid) && $this->isUpSetPrice($tid)) {
                        //上级销售价格
                        $cost = $this->getSiteBuyPrice($tid, $this->upzid);
                        if ($cost > 0) {
                            $price = $this->getUpSetPrice($tid) * $cost;
                        }
                    } elseif ($this->checkUpSite($this->upzid) && $this->checkUp2UpSite($this->uprow['upzid'], $this->upzid)) {
                        if ($this->isUpSetPrice($tid, 0, $this->uprow['upzid'])) {
                            //echo '上级的上级设置价格：OK' . "\n";
                            $cost = $this->getSiteBuyPrice($tid, $this->uprow['upzid']);
                            if ($cost > 0) {
                                $price = $this->getUpSetPrice($tid, 0, $this->uprow['upzid']) * $cost;
                            }
                        }
                    }
                }
            }
        }

        if (function_exists('getDiscount')) {
            $price = getDiscount($price, $this->tool['discount']);
        }
        return $this->priceFormat($price);
    }

    public function getSiteBuyPrice($tid, $zid = '')
    {

        if (empty($zid)) {
            $zid = $this->upzid;
        }

        if ($zid < 1) {
            return 0;
        }
        $price_obj = new Price($zid);
        if (isset($this->price_stock[$tid])) {
            $tool           = $price_obj->getToolInfo($tid);
            $tool['price1'] = $this->price_stock[$tid]['price1'];
            $tool['prid']   = $this->price_stock[$tid]['prid'];
            $price_obj->setToolInfo($tid, $tool);
        } else {
            $price_obj->setToolInfo($tid);
        }

        $price_obj->setSuper(1);
        $price = $price_obj->getBuyPrice($tid);

        if ($price > 0) {
            return $this->priceFormat($price);
        }
        return 0;
    }

    public function getBuyPoint($tid, $money, $num = 1, $upsite)
    {
        //获取订单佣金
        global $conf, $DB;
        if ($this->tool['tid'] != $tid) {
            $this->tool = $this->getToolInfo($tid);
        }

        $price = 0;

        if ($this->zid <= 1) {
            return 0;
        }
        if ($num < 1) {
            $num = 1;
        }

        // 分站提成
        if ($this->power == 2) {
            $cost  = $this->getToolCost2($tid);
            $price = $money - $cost * $num;
        } elseif ($this->power == 1) {
            $cost = $this->getToolCost($tid);
            if ($cost > 0) {
                $cost  = $cost * $num;
                $price = $money - $cost;
            } else {
                $price = 0;
            }
        } elseif ($this->power == 0) {
            $price = 0;
        }

        //上级分站提成
        if ($upsite == 1) {
            if ($this->uppower == 2 && $this->uppower > $this->power) {
                $cost2 = $this->getToolCost2($tid) * $num;
                $point = $money - $cost2 - $price;
            } elseif ($this->uppower == 1 && $this->uppower > $this->power && $this->upzid > 1) {
                //普通分站
                $cost  = $this->getSiteBuyPrice($tid);
                $cost2 = round($cost * $num, 5);
                if ($cost2 > 0) {
                    $point = $money - $cost2 - $price;
                } else {
                    $point = 0;
                }
            } else {
                $point = 0;
            }
        }

        if ($upsite == 1) {
            return round($point, 5);
        }

        //上级分站的上级分站提成
        if ($upsite == 2) {
            $cost2   = $this->getToolCost2($tid) * $num;
            $uppoint = 0;
            if ($cost2 > 0) {
                $uppoint = $money - $point - $price - $cost2;
            }
            return round($uppoint, 5);
        }

        return $this->priceFormat($price);
    }

    public function getFinalPrice($price, $num)
    {
        if ($this->tool['prices'] != "") {
            $prices  = explode(',', $this->tool['prices']);
            $prices2 = array();
            foreach ($prices as $item) {
                $arrs              = explode('|', $item);
                $prices2[$arrs[0]] = $arrs[1];
            }

            krsort($prices2);
            $discount = 0;
            foreach ($prices2 as $key => $value) {
                if ($num >= $key) {
                    $discount = $value;
                    break;
                }
            }

            $price = $price - $discount;
            if ($price <= 0) {
                return 0;
            }

        }
        return $this->priceFormat($price);
    }

    public function setPriceInfo($tid, $del, $price, $cost = 0)
    {
        global $DB;
        $this->price_array[$tid] = array();
        if ($price != $this->tool['price'] || $cost > 0 && $cost != $this->tool['cost'] || $del != $this->price_array[$tid]['del']) {
            $this->price_array[$tid]['price'] = $price;
            if ($this->power == 2) {
                $this->price_array[$tid]['cost'] = $cost;
            }

            $this->price_array[$tid]['del'] = $del;
        }
        $price_data = serialize($this->price_array);
        return $DB->query("update cmy_site set price= ? where zid= ?", array($price_data, $this->zid));
    }

    private function addPointRecord($zid, $point = 0, $action = '提成', $bz = null, $orderid)
    {
        global $DB, $date;
        $DB->query("INSERT INTO `pre_points` (`zid`, `action`, `point`, `bz`, `addtime`, `orderid`) VALUES ( ?, ?, ?, ?, ?, ?)", array($zid, $action, $point, $bz, $date, $orderid));
    }

    private function getSiteInfo($zid)
    {
        global $DB;
        $data       = $DB->get_row("SELECT * FROM cmy_site WHERE zid= ? limit 1", array($zid));
        $this->tool = $data;
        return $data;
    }

    public function setToolInfo($tid, $row = null)
    {

        if (is_array($row) && isset($row['price1'])) {
            if ($row['price'] > 0) {
                $price1 = $row['price1'] > 0 ? $row['price1'] : '0';
                $row2   = @$this->price_rules[$row['prid']];
                if ($row['prid'] > 0 && is_array($row2) && $row2['p_0'] >= $row2['p_1']) {
                    $row['cost2'] = $this->getAmount($row2['kind'], $price1, $row2['p_2']);
                    $row['cost']  = $this->getAmount($row2['kind'], $price1, $row2['p_1']);
                    $row['price'] = $this->getAmount($row2['kind'], $price1, $row2['p_0']);
                } else {
                    if ($row['specs_id'] > 0) {
                        //自动加价，避免亏本
                        $row['price'] = $this->getAmount(2, $price1, 15);
                        $row['cost']  = $this->getAmount(2, $price1, 12);
                        $row['cost2'] = $this->getAmount(2, $price1, 8);
                    }
                }
            } else {
                $row['price'] = 0;
                $row['cost']  = 0;
                $row['cost2'] = 0;
            }
        } else {
            $row = $this->getToolInfo($tid);
        }
        $this->tool = $row;
        return $row;
    }

    /**
     * @param Int 商品ID
     * @param Int 商品规格单独选项信息
     * @return Array
     */
    public function setStockInfo($tid, $stock_row)
    {
        if (!empty($tid)) {
            $tid = $this->tool['tid'];
        } elseif (!empty($stock_row['id'])) {
            return [];
        }

        $this->price_stock[$tid] = $stock_row;
        return $this->price_stock[$tid];
    }

    public function getToolInfo($tid, $cache = 0)
    {
        global $DB;

        if ($cache === 1 && is_array($this->tool) && $this->tool['tid'] == $tid) {
            return $this->tool;
        }
        $row = $DB->get_row("SELECT * from cmy_tools where tid= ? limit 1", [$tid]);

        if ($row['prid'] > 0 && $row['price1'] > 0) {
            $pridrow = $this->price_rules[$row['prid']];
            if (!is_array($pridrow)) {
                $this->updatePriceRules();
                $pridrow = $this->price_rules[$row['prid']];
            }

            if (is_array($pridrow)) {
                $tool         = $row;
                $row['price'] = $this->getAmount($pridrow['kind'], $row['price1'], $pridrow['p_0']);
                $row['cost']  = $this->getAmount($pridrow['kind'], $row['price1'], $pridrow['p_1']);
                $row['cost2'] = $this->getAmount($pridrow['kind'], $row['price1'], $pridrow['p_2']);

                if ($tool['price'] - $row['price'] != 0 || $tool['cost'] - $row['cost'] != 0 || $tool['cost2'] - $row['cost2'] != 0) {
                    // 重新设置价格
                    $data = [
                        ':price' => $row['price'],
                        ':cost'  => $row['cost'],
                        ':cost2' => $row['cost2'],
                        ':tid'   => $row['tid'],
                    ];
                    $DB->query("UPDATE `pre_tools` SET `price`=:price,`cost`=:cost,`cost2`=:cost2 WHERE tid = :tid limit 1", $data);
                }
            }
        }
        $this->tool = $row;
        return $row;
    }

    private function updatePriceRules()
    {
        global $DB, $CACHE, $conf;
        $this->price_rules = null;
        $price_rules       = $CACHE->read('pricerules');
        if (!is_array($this->price_rules) || !isset($this->price_rules[0]['kind'])) {
            $array = array();
            $rs    = $DB->query("SELECT * FROM cmy_price order by id asc");
            while ($res = $DB->fetch($rs)) {
                $array[$res['id']] = [
                    'kind' => $res['kind'],
                    'p_2'  => $res['p_2'],
                    'p_1'  => $res['p_1'],
                    'p_0'  => $res['p_0'],
                ];
            }
            $CACHE->save('pricerules', $array, time() + 21600);
            $CACHE->clear();
            $this->price_rules = $array;
        }
    }

    private function updateSuperPrice()
    {
        global $DB;
        $this->price_super = null;
        if ($this->mid) {
            $row = $DB->get_row("SELECT * FROM `pre_price_super` WHERE mid= ? limit 1", [$this->mid]);
            if ($row && is_array($row)) {
                $this->price_super = $row;
            }
        }
    }

    public function priceFormat($value)
    {
        global $conf;
        if ($conf['supply_float_limit'] < 2) {
            $conf['supply_float_limit'] = 2;
        } else {
            $conf['supply_float_limit'] = intval($conf['supply_float_limit']);
        }
        return sprintf('%.' . $conf['supply_float_limit'] . 'f', $value);
    }
}
