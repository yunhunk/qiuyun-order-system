<?php
/**
 * 登录
 **/
$is_defend = true;
include "../includes/common.php";
$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
if ($act == 'login') {
    $user = daddslashes($_POST['user']);
    $pwd  = daddslashes($_POST['pwd']);
    $row  = $DB->get_row("SELECT * FROM pre_site WHERE user= ? limit 1", array($user));
    if ($row && $user == $row['user'] && MD5($pwd) == MD5($row['pwd'])) {
        $clientip = real_ip();
        if ($row['power'] > 0 && $_SERVER['HTTP_HOST'] != $row['siteurl'] && $_SERVER['HTTP_HOST'] != $row['siteurl2']) {
            $result = array('code' => 1, "msg" => "网址不匹配", "siteurl" => $row['siteurl']);
            exit(json_encode($result));
        } elseif ($row['status'] == 0 && $row['user'] == $user) {
            @header('Content-Type: text/html; charset=UTF-8');
            $result = array('code' => -1, "msg" => "当前分站已关闭，无法登陆！<br>关闭原因：" . ($row['closure'] != '' ? $row['closure'] : '站点运行异常临时封禁处理') . "<br>如有疑问，请联系站长QQ" . $conf['zzqq'] . "处理", "row" => $row);
            setcookie("user_token", "", time() - 604800, '/');
            exit(json_encode($result));
        } elseif ($conf['cloud_open'] == 1 && $conf['fenzhan_tel'] == 1) {
            if (($conf['cloud_open_all'] == 1 || $conf['cloud_open_adm_yidilogin'] == 1) && $row['tel'] != "" && $row['loginIp'] != "" && $row['loginIp'] != $clientip) {
                $telcode = daddslashes($_POST['telcode']);
                $userid  = md5($cookiesid . ' 11111111' . $telcode);
                if (empty($telcode)) {
                    $result = array('code' => -2, 'msg' => '验证码不能为空！');
                } elseif ($_SESSION['code_userid'] != $userid) {
                    $result = array('code' => -2, 'msg' => '异地登录，请先发送验证码！');
                } else {
                    if ($_SESSION['code_userid'] != $userid) {
                        $result = array('code' => -1, 'msg' => '验证码错误或已过期！', 'data' => null);
                    } else {
                        $logrow = $DB->get_row("select * from pre_codelog where `tel`= ? and `code`= ? order by id desc limit 1", array($row['tel'], $telcode));
                        if (!$logrow) {
                            $result = array('code' => -1, 'msg' => '该验证码不正确！');
                        } else {
                            if ($logrow['status'] == 1) {
                                $result = array('code' => -1, 'msg' => '该验证码已失效，请重新发送！');
                            } elseif ((time() - strtotime($logrow['addtime'])) > (10 * 60)) {
                                $result = array('code' => -1, 'msg' => '该验证码已过期！');
                            } else {
                                $DB->query("update `pre_codelog` set `status`='1' where id= ? limit 1", array($logrow['id']));
                            }
                            unset($_SESSION['code_userid']);
                        }

                    }
                }

                if (isset($result) && is_array($result)) {
                    exit(json_encode($result));
                }
            }
        }
        $session = md5($user . $pwd . $password_hash);
        $token   = authcode($row['zid'] . "\t" . $session, 'ENCODE', SYS_KEY);
        setcookie("user_token", $token, time() + 604800, '/');
        $DB->query("update `pre_site` set `loginIp`= ? where zid= ? limit 1", array($clientip, $row['zid']));
        fzlog_result($row['zid'], '分站登录', '用户名：' . $user . '；登录IP：' . $clientip, '分站登录成功', 1);
        @header('Content-Type: text/html; charset=UTF-8');
        exit('{"code":0,"msg":"succ"}');
    } else {
        @header('Content-Type: text/html; charset=UTF-8');
        fzlog_result($siterow['zid'] ? $siterow['zid'] : 1, '分站登录', '用户名：' . $user . '；登录IP：' . $clientip, '用户名或密码不正确！', 0);
        exit('{"code":-1,"msg":"用户名或密码不正确！"}');
    }
} elseif (isset($_GET['logout'])) {
    setcookie("user_token", "", time() - 604800, '/');
    @header('Content-Type: text/html; charset=UTF-8');
    exit("<script language='javascript'>alert('安全提醒！您已成功注销本次登陆！');window.location.href='./login.php';</script>");
} elseif ($isLogin2 == 1) {
    @header('Content-Type: text/html; charset=UTF-8');
    exit("<script language='javascript'>window.location.href='./';</script>");
}
$title = '用户登录';
include './head2.php';
?>
<img src="//cn.bing.com/az/hprichbg/rb/PenaNationalPalace_ZH-CN12058841312_1920x1080.jpg;" alt="Full Background" class="full-bg full-bg-bottom animation-pulseSlow" ondragstart="return false;" oncontextmenu="return false;">
<div class="col-xs-12 col-sm-10 col-md-8 col-lg-4 center-block " style="float: none;">
  <br /><br /><br /><br /><br /><br />
    <div class="widget" style="border-radius: 20px;box-shadow: 0 0 10px #999;">
    <div class="widget-content themed-background-flat text-center"  style="padding: 6px; position: relative; top: 10px;" >
<img  class="img-circle"src="http://q4.qlogo.cn/headimg_dl?dst_uin=<?=$conf['kfqq']?>&spec=100" alt="Avatar" alt="avatar" height="80" width="80" />
<br><p style="font-size: 24px;line-height: 30px;">站长登录</p>
    </div>

    <div class="block" style="border-radius: 20px;">
        <div class="block-title" style="background: #fff;padding:5px;">
            <a href="../" class="btn btn-effect-ripple btn-success btn-xs btn-block toggle-bordered enable-tooltip">返回首页</a>
        </div>
          <div role="form">
            <div class="input-group"><div class="input-group-addon"><span class="glyphicon glyphicon-user"></span></div>
              <input type="text" id="user" name="user" value="" class="form-control" required="required" placeholder="用户名"/>
            </div><br/>
            <div class="input-group"><div class="input-group-addon" ><span class="glyphicon glyphicon-lock"></span></div>
              <input type="password" id="pwd" name="pwd" class="form-control" required="required" placeholder="密码"/>
              <div class="input-group-addon btn btn-info btn-sm" id="btn"><a onclick="showPsw();" title="点击显示密码" style="color: #fff">显示</a></div>
            </div><br/>

            <?php if ($conf['captcha_open'] == 1 && $conf['captcha_open_login'] == 1 && $conf['captcha_key']) {?>
                <div id="captcha" style="display: none;">
                    <div id="captcha_text" style="text-align: center;">
                        正在加载验证码...
                    </div>
                    <div id="captcha_wait">
                        <div class="loading">
                            <div class="loading-dot"></div>
                            <div class="loading-dot"></div>
                            <div class="loading-dot"></div>
                            <div class="loading-dot"></div>
                        </div>
                    </div>
                </div>
                <div id="captchaform" style="display: none;"></div>
            <?php }?>

            <div class="input-group" id="display_code" style="display: none;">
                <div class="input-group-addon"><span class="fa fa-check-square"></span></div>
                <input type="text" id="telcode" name="telcode" class="form-control" required="required" placeholder="输入验证码"/>
                 <span class="input-group-addon btn btn-success btn-sm"><a id="sendCode" title="发送验证码">发送验证码</a></span>
            </div><br/>

            <div class="form-group" >
                <a onclick="login()" class="btn btn-primary btn-block" style="border-radius: 80px;">登录</a>
            </div>
			<hr>
			<div class="form-group">
			<a href="findpwd.php" class="btn btn-info btn-rounded"><i class="fa fa-unlock"></i>&nbsp;找回密码</a>
			<?php if ($conf['user_open'] == 1) {?>
			<a href="reg.php" class="btn btn-danger btn-rounded" style="float:right;"><i class="fa fa-user-plus"></i>&nbsp;注册账号</a>
			<?php } else {?>
			<a href="regsite.php" class="btn btn-danger btn-rounded" style="float:right;"><i class="fa fa-user-plus"></i>&nbsp;开通分站</a>
			<?php }?>
			</div>
          </div>
    </div>
  </div>
<script>
// 判断 input的type是password还是text.切换即可
var ele = {
    sp : document.getElementById('pwd'),
    btn : document.getElementById('btn'),
    showP : '<a onclick="showPsw();" title="点击显示密码" style="color: #fff">显示</a>',
    hideP : '<a  onclick="hidePsw();" title="点击隐藏密码" style="color: #fff">隐藏</a>'
}
function showPsw(){
    if(ele.sp.type==='password'){
        ele.sp.type = 'text';
        ele.btn.innerHTML = ele.hideP;
    }
}
function hidePsw(){
    if(ele.sp.type==='text'){
        ele.sp.type = 'password';
        ele.btn.innerHTML = ele.showP;
    }
}

var captchaInt = false;
var handlerEmbed = function (captchaObj) {
    captchaObj.appendTo('#captcha');
    captchaObj.onReady(function () {
        captchaInt = true;
        $('#captcha_text').hide();
        $("#captcha_wait").hide();
    }).onSuccess(function () {
        var result = captchaObj.getValidate();
        if (!result) {
            return alert('请完成验证');
        }
        $("#captchaform").html('<input type="hidden" name="geetest_challenge" value="'+result.geetest_challenge+'" /><input type="hidden" name="geetest_validate" value="'+result.geetest_validate+'" /><input type="hidden" name="geetest_seccode" value="'+result.geetest_seccode+'" />');
    });
};

function intGeetest() {
    $.getScript("//static.geetest.com/static/tools/gt.js");
    $.ajax({
        url: "/ajax.php?act=captcha&t=" + (new Date()).getTime(),
        type: "get",
        dataType: "json",
        success: function (data) {
            $('#captcha').show();
            $('#captcha_wait').show();
            initGeetest({
                gt: data.gt,
                challenge: data.challenge,
                new_captcha: data.new_captcha,
                product: "popup",
                width: "100%",
                offline: !data.success
            }, handlerEmbed);
        }
    });
}


function login(){
    var user=$("input[name=user]").val();
    var pwd=$("input[name=pwd]").val();
    var telcode=$("#telcode").val();
    if(user=='' || pwd==''){
        layer.alert("请确保登录账号密码不能为空");
        return false;
    }

    if ($("#captcha").length>0 && captchaInt==true) {
        var geetest_challenge = $("input[name='geetest_challenge']").val();
        var geetest_validate = $("input[name='geetest_validate']").val();
        var geetest_seccode = $("input[name='geetest_seccode']").val();
        if(geetest_challenge == undefined){
            intGeetest();
            return layer.alert('请先完成滑动验证！');
        }
        var adddata = {geetest_challenge:geetest_challenge, geetest_validate:geetest_validate, geetest_seccode:geetest_seccode};
    }

    var ii =layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type:"POST",
        url: "?act=login",
        dataType: "json",
        data:Object.assign({user:user,pwd:pwd,telcode:telcode}, adddata),
        success: function(data) {
            layer.close(ii);
            if(data.code==0){
                layer.msg('登录成功！正在进入后台..');
                setTimeout(function(){
                    window.location.href="./index.php";
                },800);
            }
            else if(data.code==1){
                window.location.href='http://'+data.siteurl+'/user/login.php';
            }
            else if(data.code == -2){
                layer.alert(data.msg);
                $("#display_code").show();
            }
            else{
               layer.alert(data.msg);
            }
        },
        error: function(e,r,d) {
            layer.close(ii);
            layer.alert('服务器错误，请稍后再试试，'+d+'！<br>多次出现请联系客服<?php echo $conf['zzqq'] ?>处理');
        }
    });
}

$("#sendCode").click(function (){
    var user=$("input[name=user]").val();
    var ii = layer.load(2, {shade:[0.1,"#fff"]});
    $.ajax({
      type : "POST",
      url : "?act=sendCode&type=4",
      data : 'user='+user,
      dataType : "json",
      success : function(data) {
        layer.close(ii);
        if(data.code == 0){
           $("#sendCode").html("已发送");
           $("#display_vcode").hide();

          setTimeout(function(){
            $("#sendCode").html("发送验证码");
          },1500);
          layer.msg(data.msg);
        }else{
          layer.alert(data.msg);
        }
      }
    });
});

window.onload=function(){
    if($("#captcha").length>0){
        intGeetest();
    }
};

</script>
<script src="<?php echo $cdnpublic ?>jquery/1.12.4/jquery.min.js"></script>
<script src="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
<!--泡泡-->
<div id="cover">
<script>
(function webpackUniversalModuleDefinition(a,b){if(typeof exports==="object"&&typeof module==="object"){module.exports=b()}else{if(typeof define==="function"&&define.amd){define([],b)}else{if(typeof exports==="object"){exports["POWERMODE"]=b()}else{a["POWERMODE"]=b()}}}})(this,function(){return(function(a){var b={};function c(e){if(b[e]){return b[e].exports}var d=b[e]={exports:{},id:e,loaded:false};a[e].call(d.exports,d,d.exports,c);d.loaded=true;return d.exports}c.m=a;c.c=b;c.p="";return c(0)})([function(c,g,b){var d=document.createElement("canvas");d.width=window.innerWidth;d.height=window.innerHeight;d.style.cssText="position:fixed;top:0;left:0;pointer-events:none;z-index:999999";window.addEventListener("resize",function(){d.width=window.innerWidth;d.height=window.innerHeight});document.body.appendChild(d);var a=d.getContext("2d");var n=[];var j=0;var k=120;var f=k;var p=false;o.shake=true;function l(r,q){return Math.random()*(q-r)+r}function m(r){if(o.colorful){var q=l(0,360);return"hsla("+l(q-10,q+10)+", 100%, "+l(50,80)+"%, "+1+")"}else{return window.getComputedStyle(r).color}}function e(){var t=document.activeElement;var v;if(t.tagName==="TEXTAREA"||(t.tagName==="INPUT"&&t.getAttribute("type")==="text")){var u=b(1)(t,t.selectionStart);v=t.getBoundingClientRect();return{x:u.left+v.left,y:u.top+v.top,color:m(t)}}var s=window.getSelection();if(s.rangeCount){var q=s.getRangeAt(0);var r=q.startContainer;if(r.nodeType===document.TEXT_NODE){r=r.parentNode}v=q.getBoundingClientRect();return{x:v.left,y:v.top,color:m(r)}}return{x:0,y:0,color:"transparent"}}function h(q,s,r){return{x:q,y:s,alpha:1,color:r,velocity:{x:-1+Math.random()*2,y:-3.5+Math.random()*2}}}function o(){var t=e();var s=5+Math.round(Math.random()*10);while(s--){n[j]=h(t.x,t.y,t.color);j=(j+1)%500}f=k;if(!p){requestAnimationFrame(i)}if(o.shake){var r=1+2*Math.random();var q=r*(Math.random()>0.5?-1:1);var u=r*(Math.random()>0.5?-1:1);document.body.style.marginLeft=q+"px";document.body.style.marginTop=u+"px";setTimeout(function(){document.body.style.marginLeft="";document.body.style.marginTop=""},75)}}o.colorful=false;function i(){if(f>0){requestAnimationFrame(i);f--;p=true}else{p=false}a.clearRect(0,0,d.width,d.height);for(var q=0;q<n.length;++q){var r=n[q];if(r.alpha<=0.1){continue}r.velocity.y+=0.075;r.x+=r.velocity.x;r.y+=r.velocity.y;r.alpha*=0.96;a.globalAlpha=r.alpha;a.fillStyle=r.color;a.fillRect(Math.round(r.x-1.5),Math.round(r.y-1.5),3,3)}}requestAnimationFrame(i);c.exports=o},function(b,a){(function(){var d=["direction","boxSizing","width","height","overflowX","overflowY","borderTopWidth","borderRightWidth","borderBottomWidth","borderLeftWidth","borderStyle","paddingTop","paddingRight","paddingBottom","paddingLeft","fontStyle","fontVariant","fontWeight","fontStretch","fontSize","fontSizeAdjust","lineHeight","fontFamily","textAlign","textTransform","textIndent","textDecoration","letterSpacing","wordSpacing","tabSize","MozTabSize"];var e=window.mozInnerScreenX!=null;function c(k,l,o){var h=o&&o.debug||false;if(h){var i=document.querySelector("#input-textarea-caret-position-mirror-div");if(i){i.parentNode.removeChild(i)}}var f=document.createElement("div");f.id="input-textarea-caret-position-mirror-div";document.body.appendChild(f);var g=f.style;var j=window.getComputedStyle?getComputedStyle(k):k.currentStyle;g.whiteSpace="pre-wrap";if(k.nodeName!=="INPUT"){g.wordWrap="break-word"}g.position="absolute";if(!h){g.visibility="hidden"}d.forEach(function(p){g[p]=j[p]});if(e){if(k.scrollHeight>parseInt(j.height)){g.overflowY="scroll"}}else{g.overflow="hidden"}f.textContent=k.value.substring(0,l);if(k.nodeName==="INPUT"){f.textContent=f.textContent.replace(/\s/g,"\u00a0")}var n=document.createElement("span");n.textContent=k.value.substring(l)||".";f.appendChild(n);var m={top:n.offsetTop+parseInt(j["borderTopWidth"]),left:n.offsetLeft+parseInt(j["borderLeftWidth"])};if(h){n.style.backgroundColor="#aaa"}else{document.body.removeChild(f)}return m}if(typeof b!="undefined"&&typeof b.exports!="undefined"){b.exports=c}else{window.getCaretCoordinates=c}}())}])});
POWERMODE.colorful=true;POWERMODE.shake=false;document.body.addEventListener("input",POWERMODE);
</script>
<!--泡泡-->
</body>
</html>