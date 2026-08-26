<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

if ($conf['qqjump'] == 2) {
    include_once 'jumpNew.php';
    die();
}

$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
if (strpos($useragent, 'iphone') !== false || strpos($useragent, 'ipad') !== false) {
    $alert = '<img src="//puep.qpic.cn/coral/Q3auHgzwzM4fgQ41VTF2rLrNvRzmibibqrjTFj5g2kzGyoQj3ViartAEQ/0" class="icon-safari" /> <span id="openm">\u0053\u0061\u0066\u0061\u0072\u0069\u6253\u5f00</span>';
} elseif (strpos($useragent, 'micromessenger') !== false) {
    $alert = '<img src="//puep.qpic.cn/coral/Q3auHgzwzM4fgQ41VTF2rLbNVmztN9ia6GPRJ0IFicucFTr4Pp8xzibsw/0" class="icon-safari" /> <span id="openm">\u6d4f\u89c8\u5668\u6253\u5f00</span>';
} else {
    $alert = '<img src="//puep.qpic.cn/coral/Q3auHgzwzM4fgQ41VTF2rOCTm6gtUeQKX7m84xg47iaVosibGckrP0JQ/0" class="icon-safari" /> <span id="openm">\u6d4f\u89c8\u5668\u6253\u5f00</span>';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="renderer"  content="webkit">
  	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title id="sitename">欢迎访问本站</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport"/>
    <style>
body,html{width:100%;height:100%}
*{margin:0;padding:0}
body{background-color:#fff}
.top-bar-guidance{font-size:15px;color:#fff;height:70%;line-height:1.8;padding-left:20px;padding-top:20px;background:url(//gw.alicdn.com/tfs/TB1eSZaNFXXXXb.XXXXXXXXXXXX-750-234.png) center top/contain no-repeat}
.top-bar-guidance .icon-safari{width:25px;height:25px;vertical-align:middle;margin:0 .2em}
.app-download-tip{margin:0 auto;width:290px;text-align:center;font-size:15px;color:#2466f4;background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAcAQMAAACak0ePAAAABlBMVEUAAAAdYfh+GakkAAAAAXRSTlMAQObYZgAAAA5JREFUCNdjwA8acEkAAAy4AIE4hQq/AAAAAElFTkSuQmCC) left center/auto 15px repeat-x}
.app-download-tip .guidance-desc{background-color:#fff;padding:0 5px}
.app-download-btn{display:block;width:214px;height:40px;line-height:40px;margin:18px auto 0 auto;text-align:center;font-size:18px;color:#2466f4;border-radius:20px;border:.5px #2466f4 solid;text-decoration:none}
    </style>
</head>
<body>
<div class="top-bar-guidance">
    <p id="p_msg"></p>
    <p id="p_msg2"></p>
</div>
<div class="app-download-tip">
    <span class="guidance-desc"></span>
</div>
<a data-clipboard-text="<?php echo $siteurl ?>" class="app-download-btn" id="J_BtnDowanloadApp"></a>
<a style="display: none;" href="" id="vurl" rel="noreferrer"></a>

<script src="//lib.baomitu.com/jquery/1.12.4/jquery.min.js"></script>
<script>
$("#sitename").html('\u8bf7\u4f7f\u7528\u6d4f\u89c8\u5668\u6253\u5f00');
$("#p_msg").html('\u53f3\u4e0a\u89d2<?php echo $alert ?>');
$("#p_msg2").html('\u53ef\u4ee5\u7ee7\u7eed\u6d4f\u89c8\u672c\u7ad9\u54e6\u007e');
$("#J_BtnDowanloadApp").html('\u70b9\u6b64\u7ee7\u7eed\u8bbf\u95ee');
$(".guidance-desc").html('\u60a8\u4e5f\u53ef\u4ee5\u590d\u5236\u672c\u7ad9\u7f51\u5740\uff0c\u5230\u5176\u5b83\u6d4f\u89c8\u5668\u6253\u5f00');
function openu(u){
	document.getElementById("vurl").href= u;
	document.getElementById("vurl").click();
}
var url = window.location.href;
	document.querySelector('body').addEventListener('touchmove', function (event) {
		event.preventDefault();
	});
	if(navigator.userAgent.indexOf("QQ/") > -1){
		openu("ucbrowser://"+url);
		openu("mttbrowser://url="+url);
		openu("baiduboxapp://browse?url="+url);
		openu("googlechrome://browse?url="+url);
		$("html").on("click",function(){
			openu("ucbrowser://"+url);
			openu("mttbrowser://url="+url);
			openu("baiduboxapp://browse?url="+url);
			openu("googlechrome://browse?url="+url);
		});
	}else if(navigator.userAgent.indexOf("MicroMessenger") > -1){
		if(navigator.userAgent.indexOf("Android") > -1){
			var iframe = document.createElement("iframe");
			iframe.style.display = "none";
			iframe.src = '?open=1';
			document.body.appendChild(iframe);
		}
	}
</script>
</body>
</html>