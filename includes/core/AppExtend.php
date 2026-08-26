<?php
namespace core;

/**
 * App客户端生成系统
 */
class AppExtend
{
    private $url        = 'http://api.izuoquan.com/';
    private $key        = '';
    private $weburl     = '';
    private $task_id    = '';
    private $project_id = '';
    private $args       = '';

    //分站ID
    private $zid = 1;

    public function __construct($config = [])
    {
        global $userrow, $isLogin2, $isLogin;
        if ($isLogin !== 1) {
            if ($isLogin2 !== 1) {
                throw new \Exception("未登录，无权限");
                return false;
            } elseif ($userrow['power'] < 1) {
                throw new \Exception("不是分站，无权限");
                return false;
            }
        }
        $this->key = $config['key'];
        $this->zid = $userrow['zid'];
        //$this->project_id = $config['app_create_type'];
        $this->project_id = 11;
        $this->weburl     = $config['url'];

        return true;
    }

    /**
     * 添加App任务
     * @param [type]  $name       app名称
     * @param integer $icon       app图标ID
     * @param integer $background app背景ID
     */
    public function add($name, $icon = 1, $background = 1, $weburl = '')
    {
        global $conf, $DB, $userrow;
        $url = $this->url . 'tasks?key=' . $this->key;
        if ($this->project_id == 11) {
            $args = '{"url": "' . $weburl . '", "theme": "#1c97f5"}';
        } else {
            $args = '{"url": "' . $weburl . '"}';
        }
        $post = [
            'project_id' => $this->project_id,
            'args'       => $args,
            'package'    => 'com.chenmwl.client' . $this->zid,
            'name'       => $name,
            'icon'       => $icon,
            'background' => $background,
        ];
        $text   = $this->curl($url, http_build_query($post), 30);
        $result = json_decode($text, true);
        if (is_array($result) && isset($result['message']) || isset($result['code'])) {
            if ($result['code'] == 0 && $result['data']['id']) {
                $userrow['app_task_id'] = $result['data']['id'];
                $DB->query("UPDATE `pre_site` set `app_task_id`= ?,`app_weburl`= ? where `zid`= ?", array($result['data']['id'], $weburl, $this->zid));
            }
            $result['msg'] = $result['message'];
            return $result;
        }
        $text = mb_substr($text, 10, 200);
        return ['code' => -1, 'msg' => '添加任务失败，' . $text];
    }

    /**
     * 上传应用文件
     */
    public function upload($filepath, $type)
    {
        global $conf, $DB;
        $url  = $this->url . 'files?key=' . $this->key;
        $post = [
            'file' => curl_file_create($filepath, "application/octet-stream", $type . time() . '.png'),
        ];
        $ch = curl_init($url);

        //设置SSL不验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        //设置模拟数据
        //curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36");
        $text     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (empty($text)) {
            if ($httpCode !== 200 && !preg_match('/^3[\d]{2}$/', $httpCode) && $httpCode !== 408) {
                $text = '[' . $httpCode . ']' . curl_error($ch);
            } else {
                $text = '[' . $httpCode . ']APP系统内部出错，请联系网站客服处理';
            }
        }
        curl_close($ch);
        //@addWebLog('App接口调用', "上传应用文件\n" . $text . "\n" . $url . "\n" . json_encode($post), 'AppExtend', 1);
        $result = json_decode($text, true);
        if (is_array($result) && isset($result['message']) || isset($result['code'])) {
            $result['msg'] = $result['message'];
            return $result;
        }
        $text = mb_substr($text, 10, 200);
        return ['code' => -1, 'msg' => '上传失败，' . $text];
    }

    /**
     * 查询App任务进度
     */
    public function query($weburl)
    {
        global $conf, $userrow, $DB;
        $url   = $this->url . 'tasks/query';
        $query = [
            'key'            => $this->key,
            'id'             => $userrow['app_task_id'],
            'project_id'     => $this->project_id,
            'is_icon_base64' => false,
            'url'            => $weburl,
        ];
        $text = $this->curl($url . '?' . http_build_query($query), 0, 30);
        //@addWebLog('App接口调用', "查询App任务进度\n" . $text, 'AppExtend', 1);
        $result = json_decode($text, true);
        if (is_array($result) && isset($result['message']) || isset($result['code'])) {
            if ($result['code'] == 0) {
                $data = [];
                if (isset($result['data'][0])) {
                    $data           = $result['data'][0];
                    $data['weburl'] = $userrow['app_weburl'];
                    if ($data['status'] == 1) {
                        if ($data['lanzou_url']) {
                            $DB->query("UPDATE `pre_site` set `appurl`= ? where `zid`= ?", array($data['lanzou_url'], $weburl, $this->zid));
                        } else {
                            $DB->query("UPDATE `pre_site` set `appurl`= ? where `zid`= ?", array($data['download_url'], $weburl, $this->zid));
                        }
                    } elseif ($data['status'] == -1 && $userrow['app_task_id'] > 0) {
                        //退款处理
                        $row1 = $DB->get_row("SELECT * FROM `pre_points` WHERE `orderid`= ? limit 1", [$userrow['app_task_id']]);
                        if (!$row1) {
                            if ($userrow['power'] == 2) {
                                $price = sprintf('%.2f', $conf['app_price']);
                            } else {
                                $price = sprintf('%.2f', $conf['app_price2']);
                            }
                            if ($price > 0) {
                                $DB->exec("UPDATE `pre_site` set `money`=`money` + ? where `zid`= ?", [$price, $this->zid]);
                                addPointLogs($this->zid, $price, '退款', '生成APP失败，当前余额' . ($userrow['money'] + $price) . '元', $userrow['app_task_id']);
                            }
                        }

                    }
                    if ($conf['dwz_api'] > 0) {
                        $data['download_url'] = getUrlDwz($data['download_url']);
                        $data['android_url']  = getUrlDwz($data['android_url']);
                    }
                    unset($data['pay_method']);
                    unset($data['pay_money']);
                    unset($data['project_name']);
                    $result = [
                        'code' => 0,
                        'msg'  => $result['message'],
                        'data' => $data,
                    ];
                } else {
                    $result = [
                        'code' => -1,
                        'msg'  => '当前生成任务不存在',
                        'data' => [],
                    ];
                }

            } else {
                $result = [
                    'code' => -1,
                    'msg'  => $result['message'],
                    'data' => [],
                ];
            }
            return $result;
        }
        $text = mb_substr($text, 10, 200);
        return ['code' => -1, 'msg' => '查询进度失败，' . $text];

    }

    /**
     * 查询账户信息
     */
    public function userinfo()
    {
        $url  = $this->url . 'users/' . $this->key;
        $text = $this->curl($url, 0, 30);
        //@addWebLog('App接口调用', "查询账户信息\n" . $text, 'AppExtend', 1);
        $result = json_decode($text, true);
        if (is_array($result) && isset($result['message']) || isset($result['code'])) {
            $result['msg'] = $result['message'];
            return $result;
        }
        $text = mb_substr($text, 10, 200);
        return ['code' => -1, 'msg' => '查询进度失败，' . $text];

    }

    /**
     * 模拟访问
     */
    private function curl($url, $post = 0, $timeout = 30)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $httpheader[] = "Accept: */*";
        $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
        $httpheader[] = "Content-Type:application/x-www-form-urlencoded";
        $httpheader[] = "X-Requested-With:XMLHttpRequest";
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);

        //允许重定向
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //重定向最多3次
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (empty($ret)) {
            if ($httpCode !== 200 && !preg_match('/^3[\d]{2}$/', $httpCode) && $httpCode !== 408) {
                $ret = '[' . $httpCode . ']' . curl_error($ch);
            } else {
                $ret = '[' . $httpCode . ']该网站内部出错，未返回任何内容';
            }
        }
        curl_close($ch);
        return $ret;
    }
}
