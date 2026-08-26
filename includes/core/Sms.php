<?php
namespace core;

/**
 * 短信验证码类
 */
class Sms extends \app\traits\AbstractDrive
{

    /**
     * 验证码有效时长
     * @var int
     */
    protected static $expire = 120;

    /**
     * 最大允许检测的次数
     * @var int
     */
    protected static $maxCheckNums = 10;

    /**
     * 错误信息
     * @var string
     */
    public static $error = '';

    /**
     * 驱动插件
     *
     * @var string
     */
    private $device = '';

    private $connect = '';

    private $smsRow = '';

    private $resultData = [];

    private $_error;

    /**
     * 构造函数
     *
     * @param integer $plugin_id 插件ID 留空自动获取
     */
    public function __construct($plugin_id = 0)
    {
        global $conf;

        // $this->log = new \core\Log(1, 3, 'Sms');

        if ($plugin_id) {
            $this->smsRow = Db::name('plugin')->find(['id' => $plugin_id]);
        } else {
            $this->smsRow = Db::name('plugin')->where([
                'type'    => 'sms',
                'dirname' => $conf['sms_device'],
                'status'  => 1,
            ])->find();
        }

        if (!$this->smsRow) {
            throw new \Exception("配置短信接口平台不存在，无法使用");
            $this->setError('配置短信接口平台不存在，无法使用');
        }

        $this->device = $this->smsRow['dirname'];

        $device = ucfirst($this->smsRow['dirname']) . 'Plugin';

        $path = __DIR__ . '/sms/' . $this->smsRow['dirname'] . '/' . ucfirst($this->smsRow['dirname']) . 'Plugin.php';

        if (!file_exists($path)) {
            throw new \Exception("配置短信接口平台异常不存在，无法使用");
            $this->setError('配置短信接口平台异常不存在，无法使用');
        }

        if (!class_exists('\\' . $device, false)) {
            include $path;
        }

        $this->setData($conf, 'site');
        try {
            $this->connect = new $device($this->smsRow);
            return true;
        } catch (\Throwable $th) {
            //throw $th;
            throw new \Exception("系统加载短信驱动失败，" . $th->getMessage());
        }
    }

    /**
     * 获取最后一次手机发送的数据
     *
     * @param   int    $mobile 手机号
     * @param   string $event  事件
     * @return  Sms
     */
    public static function get($mobile, $event = 'default')
    {

        $sms = Db::name('sms_tpl')->
            where(['mobile' => $mobile, 'event' => $event])
            ->order('id', 'DESC')
            ->find();
        return $sms ? $sms : null;
    }

    /**
     * 设置变量用数据
     *
     * @param array $data 数据值
     * @param string $type 数据类型，主要如下： 订单数据 order 商品数据 goods 站点数据 site 用户数据 user
     * @return Sms
     */
    public function setData($data = [], $type = 'default')
    {

        if (is_array($data)) {
            $this->data[$type] = isset($this->data[$type]) && is_array($this->data[$type]) ? array_merge($this->data[$type], $data) : $data;
        } else {
            $this->data[$type][] = $data;
        }
        return $this;
    }

    /**
     * 获取邮件插件列表
     *
     * @return void
     */
    public static function getPluginList($refresh = false)
    {
        if ($refresh == true) {
            $path = __DIR__ . '/sms/';
            if (is_dir($path)) {
                $dirs = scandir($path);
                if ($dirs) {
                    foreach ($dirs as $key => $value) {
                        if ($value == '.' || $value == '..') {
                            continue;
                        }
                        $load = static::load($value);
                        if (is_array($load) && isset($load['name']) && isset($load['alias'])) {
                            $row = Db::name('plugin')->get(['name' => $load['name'], 'type' => 'sms', 'dirname' => $load['alias']]);
                            if ($row) {
                                Db::name('plugin')->where([
                                    'id' => $row['id'],
                                ])->update([
                                    'desc'    => $load['description'] ?? '',
                                    'author'  => $load['author'] ?? '',
                                    'version' => $load['version'] ?? '1.0.0',
                                    'build'   => $load['build'] ?? 1000,
                                    'link'    => $load['link'],
                                    'type'    => 'sms',
                                ]);
                            } else {
                                Db::name('plugin')->insert([
                                    'plugin_id' => 0,
                                    'name'      => $load['name'],
                                    'type'      => 'sms',
                                    'dirname'   => $load['alias'],
                                    'desc'      => $load['description'] ?? '',
                                    'author'    => $load['author'] ?? '',
                                    'version'   => $load['version'] ?? '1.0.0',
                                    'build'     => $load['build'] ?? 1000,
                                    'link'      => $load['link'],
                                ]);
                            }
                        }
                    }
                }
            }
        }
        return Db::name('plugin')->select(['type' => 'mails']);
    }

    /**
     * 加载插件配置
     *
     * @return void
     */
    public static function load($plugin = '')
    {
        $path   = __DIR__ . '/sms/' . $plugin . '/' . ucfirst($plugin) . 'Plugin.php';
        $device = ucfirst($plugin) . 'Plugin';
        if (file_exists($path)) {

            if (!class_exists('\\' . $device, false)) {
                include $path;
            }

            if (!class_exists('\\' . $device, false)) {
                return false;
            }
            if (method_exists($device, 'getPluginConfig')) {
                return $device::getPluginConfig();
            }
            return false;
        }
        return false;
    }

    /**
     * 加载插件类
     *
     * @param string $plugin 插件ID名
     * @param array $pluginRow 插件信息 不传则返回类名 传了则使用该参数自动实例化
     * @return object|string
     */
    public static function loadClass($plugin = '', $pluginRow = null)
    {
        $path   = __DIR__ . '/sms/' . $plugin . '/' . ucfirst($plugin) . 'Plugin.php';
        $device = ucfirst($plugin) . 'Plugin';
        if (file_exists($path)) {

            if (!class_exists('\\' . $device, false)) {
                include $path;
            }

            if (!class_exists('\\' . $device, false)) {
                return false;
            }

            if ($pluginRow) {
                return new $device($pluginRow);
            }
            return $device;
        }
        return false;
    }

    /**
     * 加载插件配置自定义列表
     *
     * @return void
     */
    public static function loadForConfigList($plugin = '')
    {
        $load = static::load($plugin);
        if ($load) {
            $device = ucfirst($plugin) . 'Plugin';
            if (method_exists($device, 'getFormList')) {
                return $device::getFormList();
            }
            return false;
        }
        return false;
    }

    /**
     * 发送验证码
     *
     * @param   int    $mobile 手机号
     * @param   int    $code   验证码,为空时将自动生成4位数字
     * @param   string $event  事件
     * @return  boolean
     */
    public function send($mobile, $code = null, $event = 'default', $content = '')
    {
        global $clientip;
        if ($event != 'push') {
            $code = is_null($code) ? mt_rand(1000, 9999) : $code;
        } else {
            //如果为通知类型, 则不提供验证码
            $code = null;
        }

        $templateRow = $this->getTemplate($event);

        if ($templateRow) {
            // 获取发送模板id
            $template_id = $templateRow['template_id'] ?: 0;
            $ip          = $clientip;

            $this->setData(['code' => $code, 'time' => intval(ceil(self::$expire / 60)), 'event' => $event, 'template_id' => $template_id, 'ip' => $ip], 'default');
            // 自动获取对应模板发送内容
            if (!$content) {
                $content = $templateRow['content'];
            }

            $content_complete = $this->getTemplateParamContent($content);

            $sms = Db::name('sms')->insert(['uid' => $this->data['default']['user_id'] ?? 1, 'event' => $event, 'device' => $this->device, 'content' => $content_complete, 'mobile' => $mobile, 'code' => $code, 'ip' => $ip, 'createtime' => time()]);

            // 获取自定义参数数据
            $templateParam = $this->getTemplateParam($content);
            // 组装参数
            $params = [
                'template_id'   => $template_id,
                'type'          => $templateRow['type'],
                'mobile'        => $mobile,
                'content'       => $content ? $content : $templateRow['content'],
                'templateParam' => $templateParam,
                'config'        => json_decode($this->smsRow['config'], true),
            ];

            // 提交发送请求
            if ($templateRow['type'] == 'global') {
                // 国际短信
                if (method_exists($this->connect, 'sendGlobalSms')) {
                    $result = $this->connect->sendCnSms($params);
                } elseif (method_exists($this->connect, 'sendSms')) {
                    $result = $this->connect->sendSms($params);
                } elseif (method_exists($this->connect, 'send')) {
                    $result = $this->connect->send($params);
                } else {
                    $result = '接口[' . $this->device . ']开发异常，发送失败';
                }
            } else {
                // 国内短信
                if (method_exists($this->connect, 'sendCnSms')) {
                    $result = $this->connect->sendCnSms($params);
                } elseif (method_exists($this->connect, 'sendSms')) {
                    $result = $this->connect->sendSms($params);
                } elseif (method_exists($this->connect, 'send')) {
                    $result = $this->connect->send($params);
                } else {
                    $result = '接口[' . $this->device . ']开发异常，发送失败';
                }
            }
        } else {
            $result = '接口[' . $this->device . ']无相应发送模板';
        }

        // 解析发送结果
        if (is_array($result)) {
            $result['templateRow'] = $templateRow ?? null;
            $this->setResultData($result);
            if (isset($result['status'])) {
                if (in_array($result['status'], [1, 200, 'ok', 'success', 'succ', '成功'])) {
                    $result = true;
                } else {
                    $result = $result['msg'] ?? ($result['info'] ?? $result['message']);
                }
            } else if (isset($result['code'])) {
                if (in_array($result['code'], [1, 200])) {
                    $result = true;
                } else {
                    $result = $result['msg'] ?? ($result['info'] ?? $result['message']);
                }
            }

            if (isset($sms['id'])) {
                $this->save($sms['id'], 'result', $result === true ? '发送成功' : $result);
            }
        } else {
            $result = '发送失败，' . (string) $result;
        }

        if ($sms) {
            $this->save($sms['id'], 'result', $result === true ? '发送成功' : $result);
        }

        return $result === true ? $result : $result . '[' . $this->device . ']';
    }

    /**
     *  获取发送内容
     *
     * @return string
     */
    public function getTemplateContent($event = 'send_code')
    {

        $row = Db::name('sms_tpl')->get(['event' => $event, 'device' => $this->device]);
        if ($row) {
            if (method_exists($this->connect, 'parseTemplateContent')) {
                return $this->connect->parseTemplateContent($row['content']);
            } else {
                return $this->connect->getTemplateParamContent($row['content']);
            }

        }
        return '';
    }

    /**
     * 处理替换模板的参数内容
     *
     * @param string $content
     * @return string
     */
    public function getTemplateParamContent($content = '')
    {
        $templateParam = $this->getTemplateParam($content);
        foreach ($templateParam as $key => $val) {
            $content = str_replace(
                [
                    '${' . $key . '}',
                    '{' . $key . '}',
                    '[' . $key . ']',
                ],
                $val,
                $content
            );
        }
        return $content;
    }

    /**
     *  获取发送模板
     *
     * @return string
     */
    public function getTemplateId($event = 'send_code')
    {
        $row = Db::name('sms_tpl')->get(['event' => $event, 'device' => $this->device]);
        if ($row) {
            return $row['template_id'];
        }
        return 0;
    }

    /**
     *  获取发送模板
     *
     * @return string
     */
    public function getTemplate($event = 'send_code')
    {
        $row = Db::name('sms_tpl')->get(['event' => $event, 'device' => $this->device]);
        if ($row) {
            return $row;
        }
        return null;
    }

    /**
     * 检测是否有可用接口
     *
     * @return bool
     */
    public static function checkIsRun()
    {
        global $conf;
        $sms_device = $conf['sms_device'];
        $smsRow     = Db::name('plugin')->where(['dirname' => $sms_device, 'status' => 1])->get();
        if ($smsRow) {
            $config = json_decode($smsRow['config'] ?: '', true);
            if (is_array($config)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 发送通知
     *
     * @param   mixed  $mobile   手机号,多个以,分隔
     * @param   string $msg      消息内容
     * @param   string $template 消息模板
     * @return  boolean
     */
    public static function notice($mobile, $msg = '', $template = null)
    {
        $params = [
            'mobile'   => $mobile,
            'msg'      => $msg,
            'template' => $template,
        ];
        $content = self::getContent($template, $msg);
        $result  = static::send($mobile, null, 'default', $content);
        return $result ? true : false;
    }

    /**
     * 解析模板内容
     *
     * @param string $template
     * @param string $msg
     * @return void
     */
    public static function getContent($template = 'default', $msg = '')
    {

        return $msg;
    }

    /**
     * 校验验证码
     *
     * @param   int    $mobile 手机号
     * @param   int    $code   验证码
     * @param   string $event  事件
     * @return  bool|string
     */
    public function check($mobile, $code, $event = 'default')
    {

        $key = 'sms_check_' . ($event ?: 'default') . '_' . md5($mobile . SYS_KEY);

        if (isset($_COOKIE[$key]) && $_COOKIE[$key] == $mobile) {
            return true;
        }

        if (!$code) {
            return '短信验证码不能为空';
        }

        $time = time() - self::$expire;

        $sms = Db::name('sms')->where(['mobile' => $mobile, 'code' => $code, 'event' => $event])
            ->order('id', 'DESC')->find();
        if ($sms) {
            if ($sms['createtime'] > $time && $sms['times'] <= self::$maxCheckNums) {
                $this->save($sms['id'], $sms['times'] + 1);
                setcookie($key, $mobile, time() + 1200);
                // 保存session
                return true;
            } else {
                // 过期则清空该手机验证码
                self::flush($mobile, $event);
                return '短信验证码超时';
            }
        } else {
            return '短信验证码错误或已失效';
        }
    }

    /**
     * 清空指定手机号验证码
     *
     * @param   int    $mobile 手机号
     * @param   string $event  事件
     */
    public static function flush($mobile, $event = 'default')
    {
        return Db::name('sms')->where(['mobile' => $mobile, 'event' => $event])->delete();
    }

    /**
     * 保存指定字段的数据
     * @param  integer $id    数据ID
     * @param  string  $key   字段名
     * @param  string  $value 字段值
     * @return boolean
     */
    public function save($id = 0, $key = '', $value = '')
    {
        global $DB;
        return $DB->query("UPDATE `pre_sms` SET `{$key}`='{$value}'  WHERE `id`='{$id}'");
    }

    /**
     * 设置短信发送原始数据
     *
     * @param array $data
     * @return Sms
     */
    public function setResultData($data = [])
    {
        $this->resultData = $data;
    }

    /**
     * 获取短信发送原始数据
     *
     */
    public function getResultData()
    {
        return $this->resultData;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }

    private function setError($error)
    {
        $this->_error = $error;
    }
}
