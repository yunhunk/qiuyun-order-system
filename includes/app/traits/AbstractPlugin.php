<?php

namespace app\traits;

/**
 * 插件abstract基类
 */
abstract class AbstractPlugin
{
    /**
     * 请求管理类
     *
     * @var \think\Request
     */
    public $request;

    /**
     * Log日志类
     *
     * @var \app\common\util\Log
     */
    public $log;

    /**
     * 响应输出基础类
     *
     * @var \think\Response
     */
    public $response;

    public static $error = '';

    public $config = [];

    /**
     * Http-Body发送方式
     *
     * @var string
     */
    public $http_body_type = 'query';

    /**
     * 对接站点ID 建议必传
     *
     */
    public static $site_id;

    /**
     * 对接站点信息 建议必传
     *
     */
    public static $site_row;

    private $ctlen = 0;

    public function toArray($value)
    {
        if (is_object($value)) {
            return json_decode(json_encode($value), true);
        }
        return $value;
    }

    /**
     * 检测是否存在某方法
     *
     * @param string $method  方法名
     * @return bool
     *
     */
    public function hasMethod($method)
    {
        if (method_exists($this, $method . 'Method')) {
            return true;
        } elseif (method_exists($this, $method)) {
            return true;
        } else {
            return false;
        }
    }
}
