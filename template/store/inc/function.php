<?php

if (!function_exists('getPayConf')) {
    function getPayConf($tool, $class = null)
    {
        global $conf, $DB;
        $conf['alipay_api'] = $conf['alipay_api'] > 0 ? $tool['pay_alipay'] : '0';
        $conf['wxpay_api']  = $conf['wxpay_api'] > 0 ? $tool['pay_wxpay'] : '0';
        $conf['qqpay_api']  = $conf['qqpay_api'] > 0 ? $tool['pay_qqpay'] : '0';
        $conf['rmbpay_api'] = $tool['pay_rmb'] == 1 ? 1 : 0;
        if (!is_array($class) || !isset($class['hidepays'])) {
            $class = @$DB->get_row("SELECT hidepays FROM `pre_class` where `cid`= ? ", [$tool['cid']]);
        }
        if (!empty($class['hidepays'])) {
            $hidepays = explode(',', $class['hidepays']);
            if (in_array('alipay', $hidepays)) {
                $conf['alipay_api'] = 0;
            }

            if (in_array('qqpay', $hidepays)) {
                $conf['wxpay_api'] = 0;
            }

            if (in_array('wxpay', $hidepays)) {
                $conf['qqpay_api'] = 0;
            }

            if (in_array('rmb', $hidepays)) {
                $conf['rmbpay_api'] = 0;
            }
        }
        return $conf;
    }
}

if (!function_exists('store_checkActive')) {
    function store_checkActive($page = 'index')
    {
        $mod = isset($_GET['mod']) ? input('get.mod') : 'index';
        if ($page === $mod) {
            return 'active';
        } else {
            $uri = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
            if ($page == 'home' && strpos($uri, 'user/') !== false) {
                return 'active';
            } else {
                return '';
            }
        }
    }
}
