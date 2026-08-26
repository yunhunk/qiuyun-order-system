<?php
/* *
 * MD5
 * 详细：MD5加密
 * 版本：3.3
 * 日期：2012-07-19
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 */

/**
 * 签名字符串
 * @param $prestr 需要签名的字符串
 * @param $key 私钥
 * return 签名结果
 */
function md5Sign($prestr, $key)
{
    $prestr = trim($prestr) . trim($key);
    return md5($prestr);
}

/**
 * 验证签名
 * @param $prestr 需要签名的字符串
 * @param $sign 签名结果
 * @param $key 私钥
 * return 签名结果
 */
function md5Verify($prestr, $sign, $key)
{
    global $webConfig;
    $prestr = trim($prestr) . trim($key);
    $mysgin = md5($prestr);

    if (isset($webConfig['debug']) && $webConfig['debug'] == 1 && function_exists('addWebLog')) {
        @addWebLog('签名验证调试', '$prestr：' . $prestr . '；$sign：' . $sign . '；$key：' . $key . '；$mysgin：' . $mysgin, 'Pay');
    }

    if ($mysgin == trim($sign)) {
        return true;
    } else {
        return false;
    }
}
