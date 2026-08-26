<?php
/**
 * name   后台请求公共接口
 * author  秋云
 */
define('AJAX_VERSION', '2.7.6');
include "../includes/common.php";

$act = isset($_GET['act']) ? input('get.act', 1) : null;

@header('Content-Type: application/json; charset=UTF-8');

switch ($act) {
    case 'api_check':
        $id  = input('post.id', 1);
        $ok  = getAuthServerTimeout($id);
        $url = getAuthServerUrl($id);
        if ($ok) {
            if ($ok >= 650) {
                $type = '[卡顿]';
            } elseif ($ok >= 420) {
                $type = '[正常]';
            } else {
                $type = '[流畅]';
            }
            $result = ['code' => 0, 'msg' => '连接成功，耗时' . $ok . 'ms' . $type];
        } else {
            $result = ['code' => -1, 'msg' => '连接成功，耗时' . $ok . 'ms' . $type];
        }
        exit(json_encode($result));
        break;
    case 'getServerList':
        if (function_exists('getAuthServerList')) {
            $data = getAuthServerList();
            if (!is_array($AuthServerList)) {
                unlink(ROOT . 'includes/core/authServerListV3.txt');
                $data = getAuthServerList();
            }
            if (!is_array($data) || !isset($data)) {
                $data = [];
            }
            // exit(json_encode(['code' => 0, 'data' => $data], JSON_UNESCAPED_UNICODE));
            foreach ($data as $k => $v) {
                if ($k == 0) {
                    $option = '<option value="' . $v['id'] . '">' . $v['name'] . '</option>';
                } else {
                    $option = '<option value="' . $v['id'] . '">' . $v['name'] . '</option>';
                }
                $select .= $option;
            }
        } else {
            $select = '<option value="0">服务器主机环境异常或程序不完整，请重新安装或更换服务器</option>';
        }
        exit(json_encode(['code' => 0, 'data' => $select], JSON_UNESCAPED_UNICODE));
        break;
    //获取短信验证码
    case 'sms_send':
        if (input('mobile')) {
            $tel = input('mobile');
        } else {
            $tel = input('tel');
        }

        if (!$tel) {
            $tel = conf('adm_tel');
        }

        $event = input('event');
        if (!$event) {
            $event = 'safecheck';
        }

        if ($tel == "" || !validateData($tel, 'mobile')) {
            if ($event == 'login' || $event == 'findpwd') {
                $result = array("code" => -1, "msg" => "系统未配置有效管理员手机号");
            } else {
                $result = array("code" => -1, "msg" => "手机号不能为空！或格式错误！");
            }
        } else {
            try {
                $result = send_code($tel, null, $event);
                if ($result === true) {
                    $result = array('code' => 0, 'msg' => '发送成功');
                } else {
                    $result = array('code' => -1, 'msg' => '发送失败, ' . $result);
                }
            } catch (\Throwable $th) {
                $result = array('code' => -1, 'msg' => '发送失败, ' . $th->getMessage());
            }
        }
        exit(json_encode($result));
        break;
    //验证短信验证码
    case 'sms_check':
        if (input('mobile')) {
            $tel = input('mobile');
        } else {
            $tel = input('tel');
        }

        $code  = input('code');
        $event = input('event');

        if (!$event) {
            $event = 'safecheck';
        }

        if (!$tel) {
            $tel = conf('adm_tel');
        }

        if ($tel == "" || !validateData($tel, 'mobile')) {
            if ($event == 'login' || $event == 'findpwd') {
                $result = array("code" => -1, "msg" => "系统未配置有效管理员手机号");
            } else {
                $result = array("code" => -1, "msg" => "手机号不能为空！或格式错误！");
            }
        } elseif ($code == "") {
            $result = array("code" => -1, "msg" => "验证码不能为空！");
        } else {
            try {
                $sms    = new \core\Sms();
                $result = $sms->check($tel, $code, $event);
                if ($result === true) {
                    $result = array('code' => 0, 'msg' => '验证成功');
                } else {
                    $result = array('code' => -1, 'msg' => '验证失败, ' . $result);
                }
            } catch (\Throwable $th) {
                $result = array('code' => -1, 'msg' => $th->getMessage());
            }
        }
        exit(json_encode($result));
        break;
    //获取验证码
    case 'ems_send':
        $email = input('email');
        if (!$email) {
            $email = conf('adm_email');
        }

        $event = input('event');

        if (!$event) {
            $event = 'safecheck';
        }

        if ($email == "" || !validateData($email, 'email')) {
            if ($event == 'login' || $event == 'findpwd') {
                $result = array("code" => -1, "msg" => "系统未配置有效管理员邮箱", "email" => $email);
            } else {
                $result = array("code" => -1, "msg" => "邮箱不能为空或格式错误！", "email" => $email);
            }
        } else {
            $code = null;
            try {
                $sms    = new \core\Ems();
                $result = $sms->send($email, $code, $event);
                if ($result === true) {
                    $result = array('code' => 0, 'msg' => '发送到' . paserEncodeStr($email, 5) . '成功');
                } else {
                    $result = array('code' => -1, 'msg' => '发送失败, ' . $result);
                }
            } catch (\Throwable $th) {
                $result = array('code' => -1, 'msg' => $th->getMessage());
            }
        }
        exit(json_encode($result));
        break;
    //验证验证码
    case 'ems_check':
        $email = input('email');
        $event = input('event');
        $code  = input('code');
        if (!$event) {
            $event = 'safecheck';
        }

        if (!$email) {
            $email = conf('adm_email');
        }

        if ($email == "" || !validateData($email, 'email')) {
            if ($event == 'login' || $event == 'findpwd') {
                $result = array("code" => -1, "msg" => "系统未配置有效管理员邮箱");
            } else {
                $result = array("code" => -1, "msg" => "邮箱不能为空或格式错误！");
            }
        } elseif ($code == "") {
            $result = array("code" => -1, "msg" => "验证码不能为空！");
        } else {
            try {
                $sms    = new \core\Ems();
                $result = $sms->check($email, $code, $event);
                if ($result === true) {
                    $result = array('code' => 0, 'msg' => '验证成功');
                } else {
                    $result = array('code' => -1, 'msg' => '验证失败, ' . $result);
                }
            } catch (\Throwable $th) {
                $result = array('code' => -1, 'msg' => $th->getMessage());
            }
        }
        exit(json_encode($result));
        break;
    default:
        exit('{"code":-4,"msg":"No Act","version":"' . AJAX_VERSION . '"}');
        break;
}
