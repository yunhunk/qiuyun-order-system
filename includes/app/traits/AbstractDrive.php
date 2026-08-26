<?php

namespace app\traits;

/**
 * 驱动模型基类
 */
abstract class AbstractDrive
{

    public $type = 'sms';

    public $data = [
        'default' => [],
        'site'    => [],
        'order'   => [],
        'user'    => [],
    ];

    /**
     * 从公共变量数据里获取指定变量数据
     *
     * @return string
     */
    public function getDriveVars($var, $default = '')
    {
        if (isset($this->data[$var])) {
            return is_array($this->data[$var])
            ? json_encode($this->data[$var])
            : $this->data[$var];
        }

        if (strpos($var, '.') !== false) {
            return array_get($var, $this->data, $default);
        }

        return $default;
    }

    /**
     * 获取发送变量参数列表
     *
     * @return array
     */
    public function getTemplateParam($content = '')
    {
        $vars = [];
        // 从发送内容中匹配变量
        if (preg_match_all('/\{([\w\.]+)\}/', $content, $match1)) {
            $vars = $match1[1];
        } elseif (preg_match_all('/\[([\w\.]+)\]/', $content, $match1)) {
            $vars = $match1[1];
        }

        $list = [];
        // 匹配变量数据
        foreach ($vars as $key => $value) {
            if (strpos($value, '.')) {
                $item = explode('.', $value, 2);
            } else if (strpos($value, '_')) {
                $item = explode('_', $value, 2);
            }

            if (isset($item) && count($item) == 2) {
                $arr = $item;
            } else {
                $arr = [$value];
            }

            if (count($arr) == 2) {
                $list[$arr[0]] = $this->getDriveVars($arr[0] . '.' . $arr[1]);
            } else {
                $list[$arr[0]] = $this->getDriveVars('default.' . $arr[0]);
            }
        }

        // 合并自定义变量数据和默认变量数据
        $defaultVars = [
            'time'            => $this->getDriveVars('sys.time', date('Y-m-d H:i:s')),
            'sendtime'        => date('Y-m-d H:i:s'),
            'nowtime'         => date('Y-m-d H:i:s'),
            'sitename'        => conf('sitename'),
            'username'        => $this->getDriveVars('user.username'),
            'mobile'          => $this->getDriveVars('user.mobile'),
            'email'           => $this->getDriveVars('user.email'),
            'logintime'       => $this->getDriveVars('user.logintime'),
            'loginip'         => $this->getDriveVars('user.loginip'),
            'qq'              => $this->getDriveVars('user.qq'),
            'wechat'          => $this->getDriveVars('user.wechat'),
            'sitename'        => $this->getDriveVars('site.sitename'),
            'title'           => $this->getDriveVars('site.title'),
            'company'         => $this->getDriveVars('site.company'),
            'order_id'        => $this->getDriveVars('order.order_id'),
            'order_total'     => $this->getDriveVars('order.order_total'),
            'order_num'       => $this->getDriveVars('order.num'),
            'order_num_total' => $this->getDriveVars('order.num_total'),
            'admin_nickname'  => $this->getDriveVars('admin.nickname'),
            'admin_logintime' => $this->getDriveVars('admin.logintime'),
            'admin_loginip'   => $this->getDriveVars('admin.loginip'),
            'ip'              => $this->getDriveVars('default.loginip'),
            'event'           => $this->getDriveVars('default.event'),
        ];

        foreach ($defaultVars as $key => $value) {
            if (!isset($list[$key]) || !$list[$key]) {
                $list[$key] = $value;
            }
        }
        return $list;
    }

    /**
     *  获取邮件模板变量列表
     *
     * @return array
     */
    public static function getEmailTemplateVarsList()
    {
        return [
            '{site_sitename}'   => '系统名称',
            '{site_logo}'       => '网站Logo',
            '{site_company}'    => '公司名称',
            '{site_title}'      => '网站标题',
            '{time}'            => '当前时间',
            '{code}'            => '验证码',
            '{ip}'              => '发送IP',
            '{user_username}'   => '会员账号',
            '{user_nickname}'   => '会员昵称',
            '{user_mobile}'     => '会员手机',
            '{user_email}'      => '会员邮箱',
            '{user_qq}'         => '会员Q Q',
            '{order_id}'        => '订单编号',
            '{order_total}'     => '订单金额',
            '{order_num}'       => '订单份数',
            '{order_num_total}' => '订单金额总计',
            '{order_name}'      => '订单商品名称',
        ];
    }

    /**
     *  解析邮件模板变量
     *
     * @return array
     */
    public function parseEmailTemplateVarsList($content = '')
    {

        $list = [
            '{site_sitename}'   => $this->getDriveVars('site.sitename'),
            '{site_logo}'       => cdnurl($this->getDriveVars('site.logo'), true),
            '{site_company}'    => $this->getDriveVars('site.company'),
            '{site_title}'      => $this->getDriveVars('site.title'),
            '{time}'            => $this->getDriveVars('sys.time', date('Y-m-d H:i:s')),
            '{code}'            => $this->getDriveVars('default.code'),
            '{ip}'              => $this->getDriveVars('default.ip'),
            '{user_username}'   => $this->getDriveVars('user.username'),
            '{user_nickname}'   => $this->getDriveVars('user.nickname'),
            '{user_mobile}'     => $this->getDriveVars('user.mobile'),
            '{user_email}'      => $this->getDriveVars('user.email'),
            '{user_qq}'         => $this->getDriveVars('user.qq'),
            '{order_id}'        => $this->getDriveVars('orde.id'),
            '{order_total}'     => $this->getDriveVars('orde.total'),
            '{order_num}'       => $this->getDriveVars('orde.num'),
            '{order_num_total}' => $this->getDriveVars('orde.num_total'),
            '{order_name}'      => $this->getDriveVars('orde.name'),
        ];
        return str_replace(array_keys($list), array_values($list), $content);
    }
}
