<?php
if(!defined('IN_CRONLITE'))exit();

$tid=intval($_GET['tid']);
$tool=$DB->getRow("SELECT * FROM pre_tools WHERE tid='$tid' LIMIT 1");
if(!$tool || $tool['active']!=1)sysmsg('当前商品不存在');
if(isset($_GET['skey']) && $_GET['skey']==md5($tid.SYS_KEY.$tid))$skey_check=true;
else $skey_check=false;

$price_obj = new \lib\Price($userrow['zid'],$userrow);
$price_obj->setToolInfo($tool['tid'],$tool);
$price=$price_obj->getToolPrice($tool['tid']);
if($price===false)sysmsg('商品价格信息不存在');

// 获取商品库存
if($tool['is_curl'] == 4){
    $count = $DB->getColumn("SELECT count(*) FROM pre_faka WHERE tid='{$tool['tid']}' and orderid=0");
    $stock = $count;
    $stockText = '库存'.$stock.'张';
}else{
    $stock = $tool['stock'];
    $stockText = $stock === null ? '无限' : '库存'.$stock.'张';
}

?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
    <title><?php echo $tool['name']?> - <?php echo $conf['sitename']?></title>
    <meta name="keywords" content="<?php echo $conf['keywords']?>">
    <meta name="description" content="<?php echo $conf['description']?>">
    <link href="<?php echo $cdnpublic?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="<?php echo $cdnpublic?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <link href="<?php echo $cdnserver?>assets/css/common.css?ver=<?php echo VERSION ?>" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: "Microsoft YaHei", sans-serif;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 15px;
        }
        .product-header {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .product-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }
        .product-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .product-price {
            color: #4e8cff;
            font-size: 24px;
            font-weight: bold;
        }
        .product-stock {
            color: #4CAF50;
            font-size: 14px;
        }
        .product-desc {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .desc-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .desc-title:before {
            content: "";
            width: 3px;
            height: 16px;
            background: #4e8cff;
            margin-right: 8px;
            border-radius: 2px;
        }
        .order-form {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .form-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .form-title:before {
            content: "";
            width: 3px;
            height: 16px;
            background: #4e8cff;
            margin-right: 8px;
            border-radius: 2px;
        }
        .form-control {
            border: 1px solid #ddd;
            box-shadow: none;
            height: 40px;
        }
        .form-control:focus {
            border-color: #4e8cff;
            box-shadow: none;
        }
        .submit-btn {
            background: #4e8cff;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
            margin-top: 15px;
        }
        .back-btn {
            display: inline-block;
            padding: 8px 15px;
            background: #f8f9fa;
            color: #666;
            border-radius: 5px;
            text-decoration: none;
            margin-bottom: 15px;
        }
        .back-btn:hover {
            background: #e9ecef;
            text-decoration: none;
            color: #333;
        }
        /* 支付弹窗样式 */
        .pay-modal {
            padding: 20px;
            max-width: 400px;
            margin: 0 auto;
        }
        .pay-header {
            text-align: center;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 20px;
        }
        .pay-header:after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: #4e8cff;
            border-radius: 3px;
        }
        .pay-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .pay-amount {
            font-size: 28px;
            color: #4e8cff;
            font-weight: bold;
            margin: 15px 0;
        }
        .pay-amount small {
            font-size: 14px;
            color: #666;
        }
        .pay-info {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #666;
        }
        .pay-info p {
            margin: 5px 0;
        }
        .pay-channel-title {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
            text-align: center;
            position: relative;
        }
        .pay-channel-title:before,
        .pay-channel-title:after {
            content: "";
            position: absolute;
            top: 50%;
            width: 60px;
            height: 1px;
            background: #eee;
        }
        .pay-channel-title:before {
            left: 20px;
        }
        .pay-channel-title:after {
            right: 20px;
        }
        .pay-channels .btn {
            margin-bottom: 10px;
            padding: 12px;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .pay-channels .btn i {
            margin-right: 8px;
            font-size: 20px;
        }
        .pay-channels .btn-alipay {
            background: #027AFF;
            color: #fff;
            border: none;
        }
        .pay-channels .btn-wxpay {
            background: #09BB07;
            color: #fff;
            border: none;
        }
        .pay-channels .btn-qqpay {
            background: #12B7F5;
            color: #fff;
            border: none;
        }
        .pay-channels .btn-balance {
            background: #FF9800;
            color: #fff;
            border: none;
        }
        .pay-channels .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="./" class="back-btn"><i class="fa fa-angle-left"></i> 返回列表</a>
        
        <div class="product-header">
            <div class="product-title"><?php echo $tool['name']?></div>
            <div class="product-info">
                <div class="product-price">¥<?php echo $price?></div>
                <div class="product-stock"><?php echo $stockText?></div>
            </div>
        </div>

        <?php if($tool['desc']){?>
        <div class="product-desc">
            <div class="desc-title">商品简介</div>
            <div class="desc-content"><?php echo $tool['desc']?></div>
        </div>
        <?php }?>

        <div class="order-form">
            <div class="form-title">填写信息</div>
            <form id="submit-form" onsubmit="return false;">
                <input type="hidden" name="tid" id="tid" value="<?php echo $tid?>">
                <input type="hidden" name="skey" id="skey" value="<?php echo $_GET['skey']?>">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-user"></i></div>
                        <input type="text" name="inputvalue" id="inputvalue" class="form-control" placeholder="请输入联系方式" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-shopping-cart"></i></div>
                        <input type="number" name="num" id="num" class="form-control" value="1" placeholder="请输入购买数量" required>
                    </div>
                </div>
                <?php if($conf['captcha_open']==1){?>
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-shield"></i></div>
                        <input type="text" name="code" id="code" class="form-control" placeholder="请输入验证码" required>
                        <span class="input-group-addon" style="padding: 0">
                            <img src="./code.php?r=<?php echo time();?>" height="43" onclick="this.src='./code.php?r='+Math.random();" title="点击更换验证码">
                        </span>
                    </div>
                </div>
                <?php }?>
                <button type="button" class="submit-btn" id="submit_buy">立即购买</button>
            </form>
        </div>
    </div>

    <script src="<?php echo $cdnpublic?>jquery/3.4.1/jquery.min.js"></script>
    <script src="<?php echo $cdnpublic?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="<?php echo $cdnpublic?>layer/2.3/layer.js"></script>
    <script>
    var hashsalt=<?php echo $addsalt_js?>;
    $(document).ready(function(){
        $("#submit_buy").click(function(){
            var tid = $("#tid").val();
            var inputvalue = $("#inputvalue").val();
            var num = $("#num").val();
            <?php if($conf['captcha_open']==1){?>
            var code = $("#code").val();
            if(code==''){layer.alert('验证码不能为空！');return false;}
            <?php }?>
            if(inputvalue=='' || tid=='' || num==''){layer.alert('请确保每项不能为空！');return false;}
            if(num>1000){layer.alert('每次只能下单1000个！');return false;}
            var data = {
                type: "buy",
                tid: tid,
                inputvalue: inputvalue,
                num: num,
                <?php if($conf['captcha_open']==1){?>code: code,<?php }?>
                hashsalt: hashsalt
            };
            var ii = layer.load(2, {shade:[0.1,'#fff']});
            $.ajax({
                type : "POST",
                url : "ajax.php?act=pay",
                data : data,
                dataType : 'json',
                success : function(data) {
                    layer.close(ii);
                    if(data.code == 0){
                        var paymsg = '';
                        if(data.pay_alipay>0){
                            paymsg+='<button class="btn btn-alipay btn-block" onclick="window.location.href=\'other/submit.php?type=alipay&orderid='+data.trade_no+'&redirect_url='+encodeURIComponent('./?mod=query&orderid='+data.trade_no)+'\'"><i class="fa fa-credit-card"></i>支付宝付款</button>';
                        }
                        if(data.pay_wxpay>0){
                            paymsg+='<button class="btn btn-wxpay btn-block" onclick="window.location.href=\'other/submit.php?type=wxpay&orderid='+data.trade_no+'&redirect_url='+encodeURIComponent('./?mod=query&orderid='+data.trade_no)+'\'"><i class="fa fa-wechat"></i>微信支付</button>';
                        }
                        if(data.pay_qqpay>0){
                            paymsg+='<button class="btn btn-qqpay btn-block" onclick="window.location.href=\'other/submit.php?type=qqpay&orderid='+data.trade_no+'&redirect_url='+encodeURIComponent('./?mod=query&orderid='+data.trade_no)+'\'"><i class="fa fa-qq"></i>QQ钱包付款</button>';
                        }
                        if(data.pay_rmb>0){
                            paymsg+='<button class="btn btn-balance btn-block" onclick="dopay(\'rmb\',\''+data.trade_no+'\')"><i class="fa fa-wallet"></i>余额支付（剩'+data.user_rmb+'元）</button>';
                        }
                        layer.open({
                            type: 1,
                            title: false,
                            closeBtn: true,
                            shadeClose: true,
                            skin: 'layui-layer-molv',
                            area: ['420px', 'auto'],
                            content: '<div class="pay-modal">' +
                                '<div class="pay-header">' +
                                '<div class="pay-title">订单提交成功</div>' +
                                '<div class="pay-amount">￥<span>'+data.need+'</span><small>元</small></div>' +
                                '</div>' +
                                '<div class="pay-info">' +
                                '<p><span>订单号：</span>'+data.trade_no+'</p>' +
                                (data.paymsg ? '<p>'+data.paymsg+'</p>' : '') +
                                '</div>' +
                                '<div class="pay-channel-title">请选择支付方式</div>' +
                                '<div class="pay-channels">' +
                                paymsg +
                                '</div>' +
                                '</div>'
                        });
                    }else if(data.code == 1){
                        layer.alert(data.msg,{icon:1},function(){window.location.href='?buyok=1'});
                    }else if(data.code == 4){
                        var confirmobj = layer.confirm('请登录后再购买，是否现在登录？', {
                          btn: ['登录','注册','取消']
                        }, function(){
                            window.location.href='./user/login.php';
                        }, function(){
                            window.location.href='./user/reg.php';
                        }, function(){
                            layer.close(confirmobj);
                        });
                    }else{
                        layer.alert(data.msg);
                    }
                },
                error:function(data){
                    layer.close(ii);
                    layer.msg('服务器错误');
                }
            });
        });
    });

    function dopay(type,orderid){
        if(type == 'rmb'){
            var ii = layer.msg('正在提交订单请稍候...', {icon: 16,shade: 0.5,time: 15000});
            $.ajax({
                type : "POST",
                url : "ajax.php?act=payrmb",
                data : {orderid: orderid},
                dataType : 'json',
                success : function(data) {
                    layer.close(ii);
                    if(data.code == 1){
                        alert(data.msg);
                        window.location.href='./?mod=query&orderid='+orderid;
                    }else if(data.code == -2){
                        alert(data.msg);
                        window.location.href='./?mod=query&orderid='+orderid;
                    }else if(data.code == -3){
                        var confirmobj = layer.confirm('你的余额不足，请充值！', {
                          btn: ['立即充值','取消']
                        }, function(){
                            window.location.href='./user/recharge.php';
                        }, function(){
                            layer.close(confirmobj);
                        });
                    }else if(data.code == -4){
                        var confirmobj = layer.confirm('你还未登录，是否现在登录？', {
                          btn: ['登录','注册','取消']
                        }, function(){
                            window.location.href='./user/login.php';
                        }, function(){
                            window.location.href='./user/reg.php';
                        }, function(){
                            layer.close(confirmobj);
                        });
                    }else{
                        layer.alert(data.msg);
                    }
                } 
            });
        }else{
            window.location.href='other/submit.php?type='+type+'&orderid='+orderid;
        }
    }
    </script>
</body>
</html> 