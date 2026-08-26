<?php
require 'inc.php';

checkPaySec();

@header('Content-Type: text/html; charset=UTF-8');

$trade_no = daddslashes($_GET['trade_no']);
if ($conf['alipay_api'] != 3) {
    exit('当前支付接口未开启');
}

$row = $DB->get_row("SELECT * FROM cmy_pay WHERE trade_no=:trade_no limit 1", [':trade_no' => $trade_no]);
if (!$row) {
    exit('该订单号不存在，请返回来源地重新发起请求！');
}

if ($row['tid'] > 0) {
//自定义支付名称
    $tool = $DB->get_row("SELECT * FROM cmy_tools WHERE tid=:tid limit 1", [':tid' => $row['tid']]);
    if ($tool && $tool['title'] != "") {
        $row['name'] = $tool['title'];
    }
}

if (empty($conf['alipay_private_key']) || empty($conf['alipay_public_key']) || empty($conf['alipay_app_id'])) {
    sysmsg('当面付支付未配置好，请联系站长qq' . $conf['zzqq'] . '重新配置！');
}

require_once SYSTEM_ROOT . "f2fpay/model/builder/AlipayTradePrecreateContentBuilder.php";
require_once SYSTEM_ROOT . "f2fpay/AlipayTradeService.php";

// 创建请求builder，设置请求参数
$qrPayRequestBuilder = new AlipayTradePrecreateContentBuilder();
$qrPayRequestBuilder->setOutTradeNo($trade_no);
$qrPayRequestBuilder->setTotalAmount($row['money']);
$qrPayRequestBuilder->setSubject($row['name']);

$notify_url = $qrPayRequestBuilder->notify_url;

// 调用qrPay方法获取当面付应答
$qrPay = new AlipayTradeService($config);

try {
    $qrPayResult = $qrPay->qrPay($qrPayRequestBuilder);
} catch (Exception $e) {
    sysmsg('支付二维码请求错误：' . $e->getMessage());
}

//    根据状态值进行业务处理
$status   = $qrPayResult->getTradeStatus();
$response = $qrPayResult->getResponse();
if ($status == 'SUCCESS') {
    $code_url = $response->qr_code;
} elseif ($status == 'FAILED') {
    sysmsg('支付宝创建订单二维码失败！[' . $response->sub_code . ']' . $response->sub_msg);
} else {
    sysmsg('系统异常，状态未知！');
}

if ($qrPay->private_key != $conf['alipay_private_key']) {
    sysmsg('商户验证失败，请返回网站重新提交订单发起支付<br><span style="display:none">' . $qrPay->private_key . '----------' . $conf['alipay_private_key'] . '</span><br><a href="/">返回到首页</a>');
}

echo '<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Language" content="zh-cn">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
<meta name="renderer" content="webkit">
<title>支付宝扫码支付 - ' . $conf['sitename'] . '</title>
<link href="assets/css/alipay_pay.css?r=1.05&v=' . $conf['version'] . '" rel="stylesheet">
<style>
.qr-tips{
    margin-top: 10px;
}
</style>
</head>
<body>
<div class="body">
<h1 class="mod-title">
<span class="ico-wechat"></span><span class="text">支付宝扫码支付</span>
</h1>
<div class="mod-ct">
<div class="order">
</div>
<div class="amount">￥' . $row['money'] . '</div>
<div class="qr-image" id="qrcode">
</div>
<div class="qr-tips">
<h2 style="color:red;font-size:1.8rem;font-size:18px;">付款后务必返回该页面</h2>
</div>

<div class="detail" id="orderDetail">
<dl class="detail-ct" style="display: none;">
<dt>购买物品</dt>
<dd id="productName">' . $row['name'] . '</dd>
<dt>商户订单号</dt>
<dd id="billId">' . $row['trade_no'] . '</dd>
<dt>创建时间</dt>
<dd id="createTime">' . $row['addtime'] . '</dd>
</dl>';

echo <<<'Html'
<a href="javascript:void(0)" class="arrow"><i class="ico-arrow"></i></a>
</div>
<div class="tip">
<span class="dec dec-left"></span>
<span class="dec dec-right"></span>
<div class="ico-scan"></div>
<div class="tip-text">
<p>请使用支付宝扫一扫</p>
<p>扫描二维码完成支付</p>
</div>
</div>
<div class="tip-text">
</div>
<p class="download-asking"><a id="getShop" href="javascript:;" onclick="loadmsg();">付款已完成点我</a></p>
</div>
<div class="foot">
<div class="inner">
<div id="J_downloadInteraction" class="download-interaction download-interaction-opening">
    <div class="inner-interaction">
        <p class="download-opening">正在打开支付宝<span class="download-opening-1">.</span><span class="download-opening-2">.</span><span class="download-opening-3">.</span></p>
        <p class="download-asking">如果没有打开支付宝，<a id="J_downloadBtn" href="javascript:;" onclick="openAli();">请点此重新唤起</a></p>
</div>
</div>
</div>
</div>
<script src="//cdn.staticfile.org/jquery/1.12.4/jquery.min.js"></script>
<script src="//cdn.staticfile.org/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
Html;

echo '
<script>
    var code_url = \'' . $code_url . '\';
    var o = 0;
    $(\'#qrcode\').qrcode({
        text: code_url,
        width: 230,
        height: 230,
        foreground: "#000000",
        background: "#ffffff",
        typeNumber: -1
    });
    // 订单详情
    $(\'#orderDetail .arrow\').click(function (event) {
        if ($(\'#orderDetail\').hasClass(\'detail-open\')) {
            $(\'#orderDetail .detail-ct\').slideUp(500, function () {
                $(\'#orderDetail\').removeClass(\'detail-open\');
            });
        } else {
            $(\'#orderDetail .detail-ct\').slideDown(500, function () {
                $(\'#orderDetail\').addClass(\'detail-open\');
            });
        }
    });
    // 检查是否支付完成
    function loadmsg() {
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "getshop.php",
            timeout: 10000, //ajax请求超时时间10s
            data: {type: "alipay", trade_no: "' . $row['trade_no'] . '"}, //post数据
            success: function (data, textStatus) {
                //从服务器得到数据，显示数据并继续查询
                if (data.code == 1) {
                    setTimeout(function(){
                        window.location.href = data.backurl;
                    }, 2000);

                    //if (confirm("您已支付完成，需要跳转到订单页面吗？")) {
                        // window.location.href=data.backurl;
                    //} else {
                        // 用户取消
                    //}
                }else{
                    setTimeout("loadmsg()", 4000);
                }
            },
            //Ajax请求超时，继续查询
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                if (textStatus == "timeout") {
                    setTimeout("loadmsg()", 2500);
                } else { //异常
                    setTimeout("loadmsg()", 15000);
                }
            }
        });
    }

    function alijswap(qrcode) {
        var isflag = false;
        if (isflag) {
            return
        };
        isflag = true;
        url = \'alipayqr://platformapi/startapp?saId=10000007&clientVersion=3.7.0.0718&qrcode=\' + qrcode;
        location[\'href\'] = url;
        flag = typeof(flag) == \'undefined\' ? \'\' : flag;
        setTimeout(function() {
            if (typeof flag !== \'string\') {
                flag = \'\'
            };
            if (flag && typeof flag === \'string\') {
                location[\'href\'] = flag
            }
        }, 2000);
        setTimeout(function() {
            isflag = false;
            if(o < 4){
                o=o+1;
                alijswap(qrcode);
            }
        }, 1500);
    }

    function openAli(){
        alijswap(code_url);
    }

    window.onload = function(){
        setTimeout(function(){
            o=1;
            openAli();
        }, 1000);
        setTimeout(function(){
            loadmsg()
        }, 2000);
    }
</script>
</body>
</html>
';
