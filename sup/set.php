<?php
/**
 * 余额提现
 **/

use core\Db;
use core\Ems;
use core\Sms;

include __DIR__ . "/common.php";

$title = '用户设置';

$act = isset($_GET['act']) ? $_GET['act'] : null;
if ($act == 'updateUserInfo') {
    // 基本信息
    $email  = input('email');
    $qq     = input('qq');
    $tel    = input('tel');
    $wechat = input('wechat');

    $rows = Db::name('master')->where(['tel' => $tel])->find();
    if ($rows && $rows['zid'] != $masterrow['zid']) {
        json('该手机号已经被绑定过');
    }

    // 邮箱验证
    if (Ems::checkIsRun() && (conf('ems_check_change_info') == 1 || $masterrow['email'] != $emai) && validateData($email, 'email')) {

        if ($masterrow['email'] != $email && validateData($masterrow['email'], 'email')) {
            $email = $masterrow['email'];
        }

        $event = 'change_info';
        $code  = input('code2');
        if (!$code) {
            json('邮箱验证码不能为空', 406);
        }

        $ems   = new Ems();
        $check = $ems->check($email, $code ?: null, $event);
        if ($check !== true) {
            json($check, 406);
        }
    }

    $update = Db::name('master')->where(['zid' => $masterrow['zid']])->update([
        'email'  => $email ? $email : $masterrow['email'],
        'tel'    => $tel ? $tel : $masterrow['tel'],
        'qq'     => $qq ? $qq : $masterrow['qq'],
        'wechat' => $wechat ? $wechat : $masterrow['wechat'],
    ]);
    if ($update !== false) {
        if (Ems::checkIsRun()) {
            $ems = new \core\Ems();
            $ems->sendEmail($masterrow['email'], '安全提醒！您已成功修改基本资料', '尊敬的供货商<b>' . $masterrow['user'] . '</b>, 您已成功账户基本资料!<br/>如非本人操作, 您的密码可能已经泄露, 请及时查看');
        }
        success('成功', [
            'zid'    => $masterrow['zid'],
            'email'  => $email,
            'qq'     => $qq,
            'wechat' => $wechat,
        ]);
    } else {
        error('更新收款资料失败,' . Db::error(), [
            'zid'    => $masterrow['zid'],
            'email'  => $email,
            'qq'     => $qq,
            'wechat' => $wechat,
        ]);
    }

} elseif ($act == 'updateTixianInfo') {
    // 提现
    $pay_type    = intval(input('pay_type'));
    $pay_account = input('pay_account', 1);
    $pay_name    = input('pay_name', 1);
    $skimg       = input('skimg', 1);

    if (!in_array($pay_type, [0, 1, 2])) {
        error('提现方式不正确, 必须是支付宝、微信、QQ钱包其中一种');
    }

    if (!$pay_account) {
        error('收款账户不能为空');
    }

    if (!$pay_name) {
        error('收款姓名不能为空');
    }

    if (!validateData($masterrow['email'], 'email')) {
        error('未绑定邮箱, 请先去“设置”绑定', []);
    }

    // if (!$skimg) {
    //     error('收款码不能为空');
    // }

    $event = 'change_info';

    if (conf('sms_open') == 1 && conf('sms_check_change_info') == 1 && validateData($masterrow['tel'], 'mobile')) {
        if (!$code) {
            error('验证码不能为空');
        }
        $sms   = new Sms();
        $check = $sms->check($masterrow['tel'], $code ?: null, $event);
        if ($check !== true) {
            json([
                'code' => 407,
                'msg'  => $check,
            ]);
        }
    }

    // 邮箱验证
    if (Ems::checkIsRun() && (conf('ems_check_change_info') == 1 || $masterrow['pay_account'] != $pay_account || $masterrow['skimg'] != $skimg) && validateData($email, 'email')) {

        if ($masterrow['email'] != $email && validateData($masterrow['email'], 'email')) {
            $email = $masterrow['email'];
        }

        $event = 'change_info';
        $code  = input('code2');
        if (!$code) {
            json('邮箱验证码不能为空', 406);
        }

        $ems   = new Ems();
        $check = $ems->check($email, $code ?: null, $event);
        if ($check !== true) {
            json($check, 406);
        }
    }

    $update = Db::name('master')->where(['zid' => $masterrow['zid']])->update([
        'pay_type'    => $pay_type,
        'pay_account' => $pay_account,
        'pay_name'    => $pay_name,
        'skimg'       => $skimg,
    ]);
    if ($update !== false) {

        if (Ems::checkIsRun()) {
            $ems = new \core\Ems();
            $ems->sendEmail($masterrow['email'], '安全提醒！您已成功修改收款信息', '尊敬的供货商<b>' . $masterrow['user'] . '</b>, 您已成功修改收款信息!<br/>如非本人操作,你的密码可能已经泄露, 请及时查看');
        }

        success('成功', [
            'zid'         => $masterrow['zid'],
            'pay_type'    => $pay_type,
            'pay_account' => $pay_account,
            'pay_name'    => $pay_name,
            'skimg'       => $skimg,
        ]);
    } else {
        error('更新收款资料失败,' . Db::error(), [
            'zid'         => $masterrow['zid'],
            'pay_type'    => $pay_type,
            'pay_account' => $pay_account,
            'pay_name'    => $pay_name,
            'skimg'       => $skimg,
        ]);
    }

} elseif ($act == 'updatePwdInfo') {
    // 修改密码
    $newPassword        = input('newPassword');
    $confirmNewPassword = input('confirmNewPassword');
    $userPassword       = input('userPassword');
    $code               = input('code');
    if (!$newPassword) {
        error('新密码不能为空');
    }

    if (!$confirmNewPassword) {
        error('重复密码不能为空');
    }

    if ($newPassword !== $confirmNewPassword) {
        error('重复密码和新密码不一致');
    }

    // if ($userPassword !== $masterrow['pwd']) {
    //     error('旧密码错误');
    // }

    if (!validateData($masterrow['email'], 'email')) {
        error('未绑定邮箱, 请先去“设置”绑定', []);
    }

    $event = 'change_pwd';

    // if (conf('sms_open') == 1 && conf('sms_check_changepwd') && validateData($masterrow['tel'], 'mobile')) {
    //     if (!$code) {
    //         error('验证码不能为空');
    //     }
    //     $sms   = new Sms();
    //     $check = $sms->check($masterrow['tel'], $code ?: null, $event);
    //     if ($check !== true) {
    //         json([
    //             'code' => 407,
    //             'msg'  => $check,
    //         ]);
    //     }
    // }

    // 邮箱验证
    if (Ems::checkIsRun() && (conf('ems_check_change_pwd') == 1 || conf('ems_check_change_info') == 1) && validateData($masterrow['email'], 'email')) {

        $code2 = input('code2');
        if (!$code2) {
            json('邮箱验证码不能为空', 406);
        }

        $ems   = new Ems();
        $check = $ems->check($masterrow['email'], $code2 ?: null, $event);
        if ($check !== true) {
            json($check, 406);
        }
    }

    $update = Db::name('master')->where(['zid' => $masterrow['zid']])->update([
        'pwd' => $newPassword,
    ]);

    if ($update !== false) {
        if (Ems::checkIsRun()) {
            $ems = new \core\Ems();
            $ems->sendEmail($masterrow['email'], '安全提醒！您已成功修改密码', '尊敬的供货商<b>' . $masterrow['user'] . '</b>, 您已成功修改密码!<br/>如非本人操作,你的密码可能已经泄露, 请及时查看');
        }
        success('重置密码成功');
    } else {
        error('修改密码失败,' . Db::error(), [
            'zid'      => $masterrow['zid'],
            'pay_type' => $pay_type,
        ]);
    }

}
