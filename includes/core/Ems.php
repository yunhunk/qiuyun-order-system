<?php
namespace core;

use Exception;

/**
 * 邮箱验证码类
 */
class Ems extends \app\traits\AbstractDrive
{

    /**
     * 验证码有效时长
     * @var int
     */
    protected static $expire = 1200;

    /**
     * 最大允许检测的次数
     * @var int
     */
    protected static $maxCheckNums = 10;

    /**
     * 驱动插件
     *
     * @var string
     */
    private $device = '';

    private $connect = '';

    private $emsRow = '';

    private $code = null;

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
            $this->emsRow = Db::name('plugin')->where([
                'id' => $plugin_id,
            ])->find();

        } else {
            $this->emsRow = Db::name('plugin')->where([
                'type'    => 'mails',
                'dirname' => $conf['ems_device'],
                'status'  => 1,
            ])->find();

        }

        if (!$this->emsRow) {
            throw new \Exception("[501]配置邮件接口平台不存在，无法使用");
            $this->setError('[501]配置邮件接口平台不存在，无法使用');
        }

        $this->device = $this->emsRow['dirname'];

        $device = ucfirst($this->emsRow['dirname']) . 'Plugin';
        $path   = __DIR__ . '/mails/' . $this->emsRow['dirname'] . '/' . ucfirst($this->emsRow['dirname']) . 'Plugin.php';

        if (!file_exists($path)) {
            throw new \Exception("[502]配置邮件接口平台不存在，无法使用");
            $this->setError('[502]配置邮件接口平台不存在，无法使用');
        }

        if (!class_exists('\\' . $device, false)) {
            include $path;
        }

        try {
            include_once $path;

            if (!class_exists('\\' . $device, false)) {
                throw new Exception("系统配置邮件接口异常，加载邮件驱动失败 =>" . $device);
            }

            $this->connect = new $device($this->emsRow);
            return true;
        } catch (\Throwable $th) {
            //throw $th;
            throw new Exception($th->getMessage());
        }
    }

    /**
     * 检测是否有可用接口
     *
     * @return bool
     */
    public static function checkIsRun()
    {
        global $conf;
        $ems_device = $conf['ems_device'];
        $emsRow     = Db::name('plugin')->where(['dirname' => $ems_device, 'status' => 1])->get();
        if ($emsRow) {
            $config = json_decode($emsRow['config'] ?: '', true);
            if (is_array($config)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取邮件插件列表
     *
     * @return void
     */
    public static function getPluginList($refresh = false)
    {
        if ($refresh == true) {
            $path = __DIR__ . '/mails/';
            if (is_dir($path)) {
                $dirs = scandir($path);
                if ($dirs) {

                    foreach ($dirs as $key => $value) {
                        if ($value == '.' || $value == '..') {
                            continue;
                        }
                        $load = static::load($value);
                        if (is_array($load) && isset($load['name']) && isset($load['alias'])) {
                            $row = Db::name('plugin')->get(['name' => $load['name'], 'type' => 'mails', 'dirname' => $load['alias']]);
                            if ($row) {
                                Db::name('plugin')->where([
                                    'id' => $row['id'],
                                ])->update([
                                    'desc'    => $load['description'],
                                    'author'  => $load['author'],
                                    'version' => $load['version'],
                                    'build'   => $load['build'],
                                    'link'    => $load['link'],
                                ]);
                            } else {
                                $insert = Db::name('plugin')->insert([
                                    'plugin_id' => 0,
                                    'name'      => $load['name'],
                                    'type'      => 'mails',
                                    'dirname'   => $load['alias'],
                                    'desc'      => $load['description'],
                                    'author'    => $load['author'],
                                    'version'   => $load['version'],
                                    'build'     => $load['build'],
                                    'link'      => $load['link'],
                                    'status'    => $load['status'] ?? 1,
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
        $path   = __DIR__ . '/mails/' . $plugin . '/' . ucfirst($plugin) . 'Plugin.php';
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
     * @return void
     */
    public static function loadClass($plugin = '', $pluginRow = null)
    {
        $path   = __DIR__ . '/mails/' . $plugin . '/' . ucfirst($plugin) . 'Plugin.php';
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
     * @return array|false
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
     * 设置变量用数据
     *
     * @param array $data 数据值
     * @param string $type 数据类型，主要如下： 订单数据 order 商品数据 goods 站点数据 site 用户数据 user
     * @return Sms
     */
    public function setData($data = [], $type = 'default')
    {
        if (is_array($data)) {
            $this->data[$type] = $data;
        } else {
            $this->data[$type][] = $data;
        }
        return $this;
    }

    /**
     * 获取最后一次邮箱发送的数据
     *
     * @param   int    $email 邮箱
     * @param   string $event 事件
     * @return  Ems
     */
    public static function get($email, $event = 'send_code')
    {

        $ems = Db::name('ems')->where(['email' => $email, 'event' => $event])
            ->order('id', 'DESC')
            ->find();
        return $ems ? $ems : null;
    }

    /**
     * 发送验证码
     *
     * @param   int         $email 邮箱
     * @param   int         $code  验证码,为空时将自动生成4位数字
     * @param   string      $event 事件
     * @return  string|true
     */
    public function send($email, $code = null, $event = 'send_code', $uid = 0)
    {
        global $clientip, $conf;
        $this->code  = $this->randnumber(6);
        $templateRow = $this->getTemplate($event);
        if ($templateRow) {

            $this->setData($conf, 'site');

            $subject = $this->parseEmailTemplateVarsList($templateRow['subject']);
            $this->setData(['code' => $this->code, 'subject' => $subject, 'ip' => $clientip]);
            $body = $this->parseEmailTemplateVarsList($templateRow['body']);
            return $this->sendEmail($email, $subject, $body, $event, $uid);
        } else {
            return '接口[' . $this->device . ']无相应发送模板';
        }
    }

    /**
     * 生成随机数
     *
     * @param integer $len 随时数长度
     * @return integer
     */
    public function randnumber($len = 4)
    {
        global $clientip;
        $time  = time();
        $randx = intval(substr($time, 10 - $len, $len));
        $randy = str_replace('.', '', $clientip);
        $randy = mb_substr($randy, strlen($randy . '') - $len, $len);
        if ($time % 7 == 0) {
            $number = mt_rand(111111, $randx + 10);
        } elseif ($time % 6 == 0) {
            $number = mt_rand(111111, $randx + 199999);
        } elseif ($time % 4 == 0) {
            $number = mt_rand(111111, $randx + 916546);
        } else {
            if ($randy > $randx) {
                $number = mt_rand($randx, $randy);
            } else {
                $number = mt_rand($randy, $randx);
            }
        }

        if (($strlen = strlen($number . '')) > $len) {
            return substr($number . '', $strlen - $len, $len);
        }
        return $number;
    }

    /**
     *  获取发送内容
     *
     * @return string
     */
    public function getTemplateContent($event = 'send_code')
    {

        $row = Db::name('ems_tpl')->get(['event' => $event, 'device' => $this->device]);
        if ($row) {
            return $this->parseEmailTemplateVarsList($row['content']);
        }
        return '';
    }

    /**
     * 获取系统html模板
     *
     * @param string $title
     * @param string $body
     * @return string
     */
    public function getParseTplHtml($title = '', $body = '')
    {
        return '<meta charset="UTF-8">  <meta http-equiv="X-UA-Compatible" content="IE=edge">  <meta name="viewport" content="width=device-width, initial-scale=1.0">  <title>Document</title>  <style> h1,h2,h3,h4,h5,span{margin:0;padding:0;    line-height: 100%;}   li{list-style: none;}    a{text-decoration: none;}    body{margin: 0;}    .box{      background-color: #EBEBEB;    height: 100%;    }    .logo_top {padding: 20px 0; width:175px;  margin: 0 auto;}    .logo_top img{      display: block;      width: 100%;      margin: 0 auto;    }    .card{      width: 650px;      margin: 0 auto;      background-color: white;      font-size: 2.1rem;      line-height: 35px;      padding: 40px 50px;      box-sizing: border-box;    }    .contimg{      text-align: center;    }    button{      background-color: #F75697;      padding: 8px 26px;      border-radius: 6px;      outline: none;      color: white;      border: 0;    }    .lvst{      color: #57AC80;    }    .banquan{      display: flex;      justify-content: center;      flex-wrap: nowrap;      color: #B7B8B9;      font-size: 0.4rem;      padding: 20px 0;      margin: 0;      padding-left: 0;    }    .banquan li span{      display: inline-block;      padding: 0 8px;    }    @media (max-width: 650px){      .card{        padding: 5% 5%;      }      .logo_top img,.contimg img{width: 280px;}      .box{height: auto;}      .card{width: auto;}    }    @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}}  </style><div class="box"><div class="logo_top"><img src="' . cdnurl(conf('logo'), true) . '" alt=""/></div><div class="card"><h2 style="text-align: center; font-size: 2.45rem;">' . $title . '<br/></h2><br/></div><div class="card">' . $body . '</div><div class="card"><span style="font-size: 2.1rem;"> </span></div><div class="card" style="margin-top: 0px;display: flex;padding: 40px 25px;"><span style="margin: 0;font-size: 1.7rem;text-align: left;width: 100%;">' . conf('sitename') . '</span><span style="margin: 0;font-size: 1.7rem;text-align: left;width: 100%;">' . date('Y-m-d H:i:s') . '</span></div></div>';
    }

    /**
     * 发送自定义邮件
     *
     * @param  string $toEmail 接收邮箱
     * @param  string $title   邮件标题
     * @param  string $msg     邮件正文
     * @param  string $event   事件类型 可空
     * @return string|true
     */
    public function sendEmail($toEmail, $title = '', $body = '', $event = 'default', $uid = 0)
    {
        global $clientip, $conf;
        if (!is_string($toEmail) || empty($toEmail)) {
            return '目标邮箱为空，发送失败';
        } elseif (!is_string($body) || empty($body)) {
            return '邮件内容为空，发送失败';
        }

        $this->setData($conf, 'site');

        // 提交发送请求
        try {

            if (preg_match('/<meta|<title/i', $body) !== 1) {
                $body = $this->getParseTplHtml($title, $body);
            }

            $ems_id = Db::name('ems')->insert(['event' => $event, 'uid' => $uid, 'device' => $this->device, 'content' => $body, 'email' => $toEmail, 'code' => $this->code, 'ip' => $clientip, 'createtime' => time()]);

            // 组装参数
            $params = [
                'email'   => $toEmail,
                'subject' => $title,
                'content' => $this->parseEmailTemplateVarsList($body),
                'config'  => json_decode($this->emsRow['config'], true),
            ];

            if (method_exists($this->connect, 'sendCnEms')) {
                $result = $this->connect->sendCnSms($params);
            } elseif (method_exists($this->connect, 'sendCode')) {
                $result = $this->connect->sendCode($params);
            } elseif (method_exists($this->connect, 'sendEms')) {
                $result = $this->connect->sendSms($params);
            } elseif (method_exists($this->connect, 'send')) {
                $result = $this->connect->send($params);
            } else {
                $result = '接口[' . $this->device . ']开发异常，发送失败';
            }

            // 解析发送结果
            if (is_array($result)) {
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
            } else {
                $params['content'] = '为方便显示, 发送内容已清除';
                $result            = '发送失败，' . (string) $result . '  参数 => ' . json_encode($params, 256);
            }

            if ($result !== true) {
                $params['content'] = '为方便显示, 发送内容已清除';
                $result            = $result . '  参数 => ' . json_encode($params, 256);
            }

            $this->save($ems_id, 'result', $result === true ? '发送成功' : $result);
            return $result === true ? $result : $result . '[' . $this->device . ']';
        } catch (\Throwable $th) {
            return '系统执行错误, ' . $th->getMessage() . '[' . $this->device . ']';
        }
    }

    /**
     *  获取发送模板Id
     *
     * @return string
     */
    public function getTemplateId($event = 'send_code')
    {
        $row = Db::name('ems_tpl')->get(['event' => $event]);
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
        $row = Db::name('ems_tpl')->get(['event' => $event]);
        if ($row) {
            return $row;
        }
        return null;
    }

    /**
     * 发送通知
     *
     * @param   mixed  $email    邮箱,多个以,分隔
     * @param   string $title    邮件标题
     * @param   string $msg      消息内容
     * @return  boolean
     */
    public static function notice($email, $title = '', $msg = '')
    {

        $static  = new static();
        $success = 0;
        if (strpos($email, ',') > 0) {
            $list = explode(',', $email);
            foreach ($list as $key => $value) {
                if ($static->sendEmail($value, $title, $msg) === true) {
                    $success++;
                }
            }
        } else {
            if ($static->sendEmail($email, $title, $msg) === true) {
                $success++;
            }
        }
        return $success > 0;
    }

    /**
     * 校验验证码
     *
     * @param   int    $email 邮箱
     * @param   int    $code  验证码
     * @param   string $event 事件
     * @return  boolean
     */
    public function check($email, $code, $event = 'default')
    {
        global $DB, $conf;

        $key = 'ems_check_' . ($event ?: 'default') . '_' . md5($email . SYS_KEY);

        if (isset($_COOKIE[$key]) && intval($_COOKIE[$key]) >= time()) {
            return true;
        }

        if (!$code) {
            return '邮件验证码不能为空';
        }

        $expire = $conf['mail_expire'] > 0 ? $conf['mail_expire'] * 60 : 3 * 60;
        $time   = time() - $expire;

        $ems = Db::name('ems')->where(['email' => $email, 'code' => $code, 'event' => $event])
            ->order('id DESC')->find();
        if ($ems) {
            if ($ems['createtime'] > $time && $ems['times'] <= self::$maxCheckNums) {
                $correct = $code == $ems['code'];
                // 判断验证码次数，保证只能成功验证一次避免被恶意利用
                if ($ems['times'] < 1) {
                    if (!$correct) {
                        return '验证码不正确！';
                    } else {
                        // 成功后五分钟有效
                        setcookie($key, '' . (time() + 600), time() + 600);
                        // 验证码失效
                        $ems['times'] = $ems['times'] + 1;
                        $this->save($ems['id'], 'times', $ems['times']);
                        return true;
                    }
                } else {
                    return '邮件验证码已失效！';
                }
            } else {
                // 过期则清空该邮箱验证码
                // self::flush($email, $event);
                return '验证码已过期！';
            }
        } else {
            return '邮件验证码已过期！';
        }
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
        return $DB->query("UPDATE `pre_ems` SET `{$key}`='{$value}'  WHERE `id`='{$id}'");
    }

    /**
     * 清空指定邮箱验证码
     *
     * @param   int    $email 邮箱
     * @param   string $event 事件
     * @return  boolean
     */
    public static function flush($email, $event = 'default')
    {
        global $DB;
        return $DB->query("DELETE  FROM `pre_ems` WHERE `email`='{$email}' and `event`='{$event}'");
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
