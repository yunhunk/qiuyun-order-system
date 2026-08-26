<?php

/**
 *生成验证码
 * @param string $id
 * @param array  $config
 * @return \think\captcha\Captcha
 */
function captcha_doimg($id = '', $config = [])
{
    $captcha = new \think\captcha\Captcha($config);
    return $captcha->entry($id);
}

/**
 * @param string $url
 * @return string
 */
function captcha_src($url = '')
{
    global $weburl;
    if ($url) {
        $url = 'user/code.php';
    }
    return $weburl . $url . '?r=' . time();
}

/**
 * @param $id
 * @return mixed
 */
function captcha_img($url = '')
{
    return '<img src="' . captcha_src($url) . '" alt="captcha" />';
}

/**
 * @param        $value
 * @param string $id
 * @param array  $config
 * @return bool
 */
function captcha_check($value, $id = 'user')
{
    $captcha = new \think\captcha\Captcha();
    return $captcha->check($value, $id);
}
