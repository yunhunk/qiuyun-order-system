<?php

return [
    [
        'label'   => '顶想云通用API授权码',
        'field'   => 'token',
        'type'    => 'string',
        'tips'    => '顶想云API授权码, 短信控制台: <a href="https://console.topthink.com/sms" target="_blank">https://console.topthink.com/sms</a><br/><span style="color:red;">token获取地址(看我看我): <a href="https://console.topthink.com/user/token" target="_blank">https://console.topthink.com/user/token</a></span>',
        'encrypt' => 1,
    ],
    [
        'label'   => '顶想云短信签名ID',
        'field'   => 'signId',
        'type'    => 'string',
        'tips'    => '顶想云短信签名ID, 创建/获取地址: <a href=" https://console.topthink.com/sms/sign" target="_blank"> https://console.topthink.com/sms/sign</a>',
        'encrypt' => 1,
    ],
];
