<?php
require '../includes/common.php';
if ($isLogin2 == 1) {} else {
    $goto = @getHostUrl();

    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

$mod = isset($_GET['mod']) ? $_GET['mod'] : null;
if ($mod == 'user_n') {
    $qq          = input('post.qq', 1);
    $pay_type    = intval(input('post.pay_type', 1));
    $pay_account = input('post.pay_account', 1);
    $pay_name    = input('post.pay_name', 1);
    $pwd         = input('post.pwd', 1);
    $tel         = input('post.tel', 1);
    $code        = input('post.code', 1);

    $LOG->writeLog('修改资料', 'POST数据：' . json_encode($_POST));

    if ($conf['cloud_open'] == 1 && $conf['fenzhan_tel'] == 1) {
        $sendCodeStatus = false;
        $sendCodeType   = '';

        if (checkMessage('editcard') && $userrow['tel'] != "") {
            $sendCodeStatus = true;
            $sendCodeType   = '修改资料';
        } elseif (checkMessage('edittel') && $tel != $userrow['tel']) {
            $sendCodeStatus = true;
            $sendCodeType   = '修改手机';
        } elseif (checkMessage('editpass') && !empty($pwd)) {
            $sendCodeStatus = true;
            $sendCodeType   = '修改密码';
        }

        if ($sendCodeStatus === true) {
            if (!$_SESSION['code_userid']) {
                $result = array('code' => -1, 'msg' => '请先发送验证码再操作！', 'type' => $sendCodeType);
            } elseif (empty($code)) {
                $result = array('code' => -1, 'msg' => '验证码不能为空！', 'type' => $sendCodeType);
            } else {
                $user_token = $_COOKIE["user_token"] ? $_COOKIE["user_token"] : $_SESSION['user_token'];
                $userid     = md5($user_token . '11111111' . $code);
                if ($_SESSION['code_userid'] != $userid) {
                    $result = array('code' => -1, 'msg' => '验证码错误或已过期！', 'data' => array('user_token' => $user_token, 'code_userid' => $_SESSION['code_userid'], 'code_userid' => $_SESSION['code_userid'], 'userid' => $userid));
                } else {
                    $logrow = $DB->get_row("select * from cmy_codelog where `tel`= ? and `code`= ? order by id desc limit 1", array($tel, $code));
                    if (!$logrow) {
                        $result = array('code' => -1, 'msg' => '该验证码不正确！', 'type' => $sendCodeType);
                    } else {
                        if ($logrow['status'] == 1) {
                            $result = array('code' => -1, 'msg' => '该验证码已失效！', 'type' => $sendCodeType);
                        } elseif ((time() - strtotime($logrow['addtime'])) > (10 * 60)) {
                            $result = array('code' => -1, 'msg' => '该验证码已过期！', 'type' => $sendCodeType);
                        } else {
                            $DB->query("UPDATE `pre_codelog` set `status`='1' where id= ? limit 1", array($logrow['id']));
                        }
                        unset($_SESSION['code_userid']);
                    }
                }
            }
        }

        if (isset($result) && is_array($result)) {
            exit(json_encode($result));
        }
    }

    if (!empty($pwd) && !preg_match('/^[a-zA-Z0-9\.\_\-\@]{6,16}$/', $pwd)) {
        $result = array('code' => -1, 'msg' => '密码只能为6~16位的英文数字下划线小数点！');
    } elseif (!preg_match('/^[1-9]{1}[0-9]{5,10}$/', $qq)) {
        $result = array('code' => -1, 'msg' => 'QQ格式不正确！');
    } else {
        $sql      = "UPDATE `pre_site` set qq=:qq,pay_type=:pay_type,pay_account=:pay_account,pay_name=:pay_name,tel=:tel where `zid`=:zid";
        $sql_data = array(
            ':qq'          => $qq,
            ':pay_type'    => $pay_type,
            ':pay_account' => $pay_account,
            ':pay_name'    => $pay_name,
            ':tel'         => $tel,
            ':zid'         => $userrow['zid'],
        );
        if ($DB->query($sql, $sql_data)) {
            if (!empty($pwd)) {
                $sql_data = array(
                    ':pwd' => $pwd,
                    ':zid' => $userrow['zid'],
                );
                $DB->query("UPDATE cmy_site set pwd=:pwd where `zid`=:zid", $sql_data);
            }
            $result = ['code' => 0, 'msg' => '资料修改成功！'];
        } else {
            $result = ['code' => 0, '资料修改失败，' . $DB->error()];
        }
    }
    exit(json_encode($result));
}
$title = '网站设置';
include 'head.php';
if ($conf['fenzhan_cost2'] <= 0) {
    $conf['fenzhan_cost2'] = $conf['fenzhan_price2'];
}
?>
<div class="wrapper">
<div class="col-sm-12">
<?php
if ($mod == 'user') {
    $select = '';
    if ($conf['fenzhan_tixian_pays']) {
        $arr = explode(',', $conf['fenzhan_tixian_pays']);
        if (in_array(1, $arr)) {
            $select .= '<option value="0">支付宝</option>';
        }
        if (in_array(2, $arr)) {
            $select .= '<option value="1">微信</option>';
        }
        if (in_array(3, $arr)) {
            $select .= '<option value="2">QQ钱包</option>';
        }
    } else {
        $select = '
        <option value="0">支付宝</option>
        <option value="1">微信</option>
        <option value="2">QQ钱包</option>
        ';
    }
    ?>
<div class="block">
    <div class="block-title"><h3 class="panel-title">用户资料设置</h3></div>
<div class="">
  <form class="form-horizontal" role="form">
    <div class="form-group">
      <label class="col-sm-2 control-label">绑定ＱＱ:</label>
      <div class="col-sm-10">
        <input type="text" name="qq" value="<?php echo $userrow['qq']; ?>" class="form-control" placeholder="用于联系与找回密码"/>
         <small>用于联系与找回密码，网站首页头像显示</small>
      </div>
    </div><br>
    <div class="form-group">
      <label class="col-sm-2 control-label">提现方式:</label>
      <div class="col-sm-10">
      <select class="form-control" name="pay_type" default="<?php echo $userrow['pay_type'] ?>">
        <?php echo $select; ?>
      </select>
        <span class="help-block m-b-none">选择的提现支付方式务必和上传的收款图对应才行！</span>
          </div>
   </div><br>
      <div class="form-group">
      <label class="col-sm-2 control-label">提现账号:</label>
      <div class="col-sm-10">
      <input type="text" name="pay_account" value="<?php echo $userrow['pay_account']; ?>" class="form-control"/>
    <span class="help-block m-b-none">提现方式选的是什么就填什么账号</span>
          </div>
    </div><br>
    <div class="form-group">
      <label class="col-sm-2 control-label">提现姓名:</label>
      <div class="col-sm-10">
      <input type="text" name="pay_name" value="<?php echo $userrow['pay_name']; ?>" class="form-control"/>
        <span class="help-block m-b-none">填写你的真实姓名或者网名</span>
        </div>
    </div><br>
    <?php if ($conf['fenzhan_tel'] == 1) {?>
    <div class="form-group">
        <label class="col-sm-2 control-label">绑定手机号:</label>
        <div class="col-sm-10">
            <div class="input-group">
            <input type="text" name="tel" value="<?php echo $userrow['tel']; ?>" class="form-control" placeholder="用于联系与找回密码"/>
            <a onclick="userSendCode()" id="userSendCode" class="input-group-addon btn btn-primary btn-sm">发送验证码</a>
            </div>
          <small>用于找回密码和短信安全验证等</small>
        </div>
    </div><br>
    <div class="form-group" id="code_display" style="display:none">
      <label class="col-sm-2 control-label">填写验证码</label>
      <div class="col-sm-10">
        <input type="text" name="code" value="" class="form-control" placeholder="填写验证码"/>
      </div>
    </div><br/>
    <?php }?>
    <div class="form-group">
      <label class="col-sm-2 control-label">重置密码:</label>
      <div class="col-sm-10">
      <input type="text" name="pwd" value="" class="form-control" placeholder="不修改请留空"/>
      <small>不修改请留空</small>
      </div>
   </div><br>
    <div class="form-group">
      <a id="submit_save" class="btn btn-primary form-control">修改</a>
    </div>
  </form>
  </div>
</div>
</div>
<script>
var items = $("select[default]");
for (i = 0; i < items.length; i++) {
    $(items[i]).val($(items[i]).attr("default")||0);
}
$("#submit_save").click(function (){
    var ii = layer.load(2, {shade:[0.1,"#fff"]});
    $.ajax({
      type : "POST",
      url : "?mod=user_n",
      data : $("form").serialize(),
      dataType : "json",
      success : function(data) {
        layer.close(ii);
        if(data.code == 0){
          layer.msg(data.msg);
        }else{
          layer.alert(data.msg);
        }
      }
    });
});

function userSendCode(){
    var tel = $("input[name='tel']").val();
    if("" == tel){
      return layer.alert("手机号不能为空！");
    }
    var ii = layer.load(2, {shade:[0.1,"#fff"]});
    $.ajax({
      type : "POST",
      url : "ajax.php?act=sendCode",
      data : "tel="+tel,
      dataType : "json",
      success : function(data) {
        layer.close(ii);
        if(data.code == 0){
          $("#code_display").show();
          $("#userSendCode").html("已发送");
          setTimeout(function(){
            $("#userSendCode").html("发送验证码");
          },1500);
          layer.msg(data.msg);
        }else{
          layer.alert(data.msg);
        }
      }
    });
}
</script>
<?php
} elseif ($mod == 'site_n' && $userrow['power'] > 0) {
    $sitename    = xss_filter(strFilter(input('post.sitename', 1)));
    $title       = xss_filter(strFilter(input('post.title', 1)));
    $keywords    = xss_filter(strFilter(input('post.keywords', 1)));
    $description = xss_filter(strFilter(input('post.description', 1)));
    $kfwx        = xss_filter(strFilter(input('post.kfwx', 1)));
    $musicurl    = xss_filter(strFilter(input('post.musicurl', 1)));
    $sql         = "";
    if ($conf['fenzhan_gonggao_type'] == 2 && $conf['fenzhan_gonggao_open'] == 1) {
        $anounce = xss_filter(strFilter(input('post.anounce')), false);
        $modal   = xss_filter(strFilter(input('post.modal')), false);
        $bottom  = strFilter(input('post.bottom'));
        $alert   = xss_filter(strFilter(input('post.alert')), false);
        $sql     = ",anounce='" . $anounce . "',modal='" . $modal . "',bottom='" . $bottom . "',alert='" . $alert . "'";
    } else if ($conf['fenzhan_gonggao_open'] == 1) {
        $anounce = xss_filter(strFilter(input('post.anounce', 1)));
        $modal   = xss_filter(strFilter(input('post.modal', 1)));
        $bottom  = xss_filter(strFilter(input('post.bottom', 1)));
        $alert   = xss_filter(strFilter(input('post.alert', 1)));
        $sql     = ",anounce='" . $anounce . "',modal='" . $modal . "',bottom='" . $bottom . "',alert='" . $alert . "'";
    }

    $ktfz_price   = floatval(input('post.ktfz_price', 1));
    $ktfz_price2  = floatval(input('post.ktfz_price2', 1));
    $ktfz_siteurl = strFilter(input('post.ktfz_siteurl', 1));
    $template     = isset($_POST['template']) ? daddslashes(strip_tags($_POST['template'])) : null;
    if ($sitename == null) {
        showmsg('请确保各项不能为空', 3);
    } else {
        if (!empty($template) && (!preg_match('/^[a-zA-Z0-9]+$/', $template) || \core\Template::exists($template) == false)) {
            showmsg('该模板首页文件不存在！', 3);
        }

        if ($userrow['power'] == 2) {
            if (!is_numeric($ktfz_price) || !preg_match('/^[0-9.]+$/', $ktfz_price)) {
                showmsg('专业分站价格输入不规范', 3);
            }

            if (!is_numeric($ktfz_price2) || !preg_match('/^[0-9.]+$/', $ktfz_price2)) {
                showmsg('旗舰分站价格输入不规范', 3);
                //exit("<script language='javascript'>alert('旗舰分站价格输入不规范');history.go(-1);</script>");
            }

            if ($ktfz_price < $conf['zz_fenzhan_cost']) {
                showmsg('专业分站价格不能低于成本价', 3);
                //exit("<script language='javascript'>alert('专业分站价格不能低于成本价');history.go(-1);</script>");
            }

            if ($ktfz_price2 < $conf['zz_fenzhan_cost2']) {
                showmsg('旗舰分站价格不能低于成本价', 3);
                //exit("<script language='javascript'>alert('旗舰分站价格不能低于成本价');history.go(-1);</script>");
            }

            if ($ktfz_price2 < $ktfz_price) {
                showmsg('旗舰分站价格不能低于专业分站价格', 3);
                // exit("<script language='javascript'>alert('旗舰分站价格不能低于专业分站价格');history.go(-1);</script>");
            }

            $sds = $DB->query("UPDATE cmy_site SET `sitename`= ?,`kfwx`= ?, `musicurl`=?, title= ?,keywords= ?,`description`= ?,kaurl= ?,ktfz_price= ?,ktfz_price2= ?,ktfz_siteurl= ?,template= ?,anounce= ?,modal= ?,bottom= ?,alert= ? where `zid`='{$userrow['zid']}'", array($sitename, $kfwx, $musicurl, $title, $keywords, $description, $kaurl, $ktfz_price, $ktfz_price2, $ktfz_siteurl, $template, $anounce, $modal, $bottom, $alert), array('money'));
        } elseif ($userrow['power'] == 1) {
            if (!is_numeric($ktfz_price) || !preg_match('/^[0-9.]+$/', $ktfz_price)) {
                showmsg('专业分站价格输入不规范', 3);
                // exit("<script language='javascript'>alert('专业分站价格输入不规范');history.go(-1);</script>");
            }

            if ($ktfz_price < $conf['fenzhan_cost']) {
                showmsg('专业分站价格不能低于成本价', 3);
                // exit("<script language='javascript'>alert('专业分站价格不能低于成本价');history.go(-1);</script>");
            }

            if ($ktfz_price > $conf['fenzhan_price2']) {
                showmsg('专业分站价格不能高于旗舰版价格', 3);
                // exit("<script language='javascript'>alert('专业分站价格不能高于旗舰版价格');history.go(-1);</script>");
            }

            $sds = $DB->query("UPDATE cmy_site set sitename= ?, `kfwx`= ?, `musicurl`=?, title= ?,keywords= ?,description= ?,kaurl= ?,ktfz_price= ?,ktfz_siteurl= ?,template= ?,anounce= ?,modal= ?,bottom= ?,alert= ? where `zid`='{$userrow['zid']}'", array($sitename, $kfwx, $musicurl, $title, $keywords, $description, $kaurl, $ktfz_price, $ktfz_siteurl, $template, $anounce, $modal, $bottom, $alert), array('money'));
        } else {
            $sds = $DB->query("UPDATE cmy_site set sitename= ?, `kfwx`= ?, `musicurl`=?, title= ?,keywords= ?,description= ?,template= ?,anounce= ?,modal= ?,bottom= ?,alert= ? where `zid`='{$userrow['zid']}'", array($sitename, $kfwx, $musicurl, $title, $keywords, $description, $template, $anounce, $modal, $bottom, $alert), array('money'));
        }

        if ($sds) {
            showmsg('修改保存成功！', 1);
            // exit("<script language='javascript'>alert('修改保存成功！');history.go(-1);</script>");
        } else {
            showmsg('修改保存失败，' . $DB->error(), 4);
            // exit("<script language='javascript'>alert('修改保存失败:" . $DB->error() . "');history.go(-1);</script>");
        }
    }
} elseif ($mod == 'site' && $userrow['power'] > 0) {
    $mblist = \core\Template::getList();
    $cost   = getAddSiteCost(1);
    $cost2  = getAddSiteCost(2);
    ?>
<div class="block">
    <div class="block-title"><h3 class="panel-title">网站信息设置</h3></div>
<div class="">
  <form action="./uset.php?mod=site_n" method="post" class="form-horizontal" role="form">
    <div class="form-group">
        <label class="col-sm-2 control-label">网站名称:</label>
        <div class="col-sm-10">
        <input type="text" name="sitename" value="<?php echo $userrow['sitename'] ? $userrow['sitename'] : $conf['sitename']; ?>" class="form-control" required/>
          </div>
   </div>
      <div class="form-group">
      <label class="col-sm-2 control-label">标题栏后缀</label>
            <div class="col-sm-10">
            <input type="text" name="title" value="<?php echo $userrow['title'] ? $userrow['title'] : $conf['title']; ?>" class="form-control"/>
            </div>
      </div>
      <div class="form-group">
      <label class="col-sm-2 control-label">关键字</label>
      <div class="col-sm-10">
      <input type="text" name="keywords" value="<?php echo $userrow['keywords'] ? $userrow['keywords'] : $conf['keywords']; ?>" class="form-control"/>
       </div>
      </div>
      <div class="form-group">
      <label class="col-sm-2 control-label">网站描述</label>
      <div class="col-sm-10">
        <input type="text" name="description" value="<?php echo $userrow['description'] ? $userrow['description'] : $conf['description']; ?>" class="form-control"/>
       </div>
      </div>
      <?if ($conf['fenzhan_gonggao_open'] == 1) {?>
          <div class="form-group">
          <label class="col-sm-2 control-label">首页公告</label>
          <div class="col-sm-10">
          <textarea class="form-control" name="anounce" rows="3"><?php echo htmlspecialchars($userrow['anounce']); ?></textarea>
              </div>
           </div>
          <div class="form-group">
          <label class="col-sm-2 control-label">首页弹出公告</label>
          <div class="col-sm-10">
          <textarea class="form-control" name="modal" rows="3"><?php echo htmlspecialchars($userrow['modal']); ?></textarea>
              </div>
          </div>
          <!--<div class="form-group">-->
          <!--<label class="col-sm-2 control-label">首页底部排版</label><div class="col-sm-10">-->
          <!--<textarea class="form-control" name="bottom" rows="5"><?php echo htmlspecialchars($userrow['bottom']); ?></textarea>-->
          <!--     </div>-->
          <!--</div>-->
          <div class="form-group">
          <label class="col-sm-2 control-label">在线下单提示</label><div class="col-sm-10">
          <textarea class="form-control" name="alert" rows="3"><?php echo htmlspecialchars($userrow['alert']); ?></textarea>
         </div>
          </div>
    <?php }?>

    <?php if ($userrow['power'] >= 1) {?>
        <div class="form-group">
        <label class="col-sm-2 control-label">专业分站价格</label><div class="col-sm-10">
        <input type="text" name="ktfz_price" value="<?php echo $userrow['ktfz_price'] >= $conf['fenzhan_cost'] ? $userrow['ktfz_price'] : $cost; ?>" class="form-control"/><small>前台自助开通分站的价格，不能低于成本价<?php echo $cost ?>元，不能高于旗舰版价<?php echo $cost2 ?>元</small>
           </div>
        </div>
        <?php if ($userrow['power'] >= 2) {?>
        <div class="form-group">
        <label class="col-sm-2 control-label">旗舰分站价格</label><div class="col-sm-10">
        <input type="text" name="ktfz_price2" value="<?php echo $userrow['ktfz_price2'] >= $conf['fenzhan_cost2'] ? $userrow['ktfz_price2'] : $cost2; ?>" class="form-control"/>
        <small>前台自助开通分站的价格，不能低于成本价<?php echo $cost2 ?>元</small>
           </div>
        </div>
        <?php }?>
        <?php if ($conf['fenzhan_domain_diy'] == 1) {?>
        <div class="form-group">
          <label class="col-sm-2 control-label">分站可选择域名</label><div class="col-sm-10">
          <input type="text" name="ktfz_siteurl" value="<?php echo $userrow['ktfz_siteurl']; ?>" class="form-control"/><small>默认使用主站域名，没有请留空，不要乱填写！多个域名用,隔开！</small>
         </div>
        </div>
        <?php }?>
    <?php }?>
    <?php if ($conf['fenzhan_template'] == 1):
    ?>
    <div class="form-group">
      <label class="col-sm-2 control-label">首页模板设置</label><div class="col-sm-10">
      <select class="form-control" name="template">
      <?php foreach ($mblist as $row) {
        echo '<option value="' . $row . '" ' . ($userrow['template'] == $row ? 'selected' : null) . '>' . $row . '</option>';
    }
    ?>
      </select>

     </div>


      </div>
    <?php endif;?>
    <div class="form-group">
        <label class="col-sm-2 control-label">首页客服链接</label>
        <div class="col-sm-10">
            <input type="text" name="kfwx" value="<?php echo $userrow['kfwx']; ?>" placeholder="个人无售后能力不建议更换 (留空则不跳转)" class="form-control"/>

        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">首页音乐链接</label>
        <div class="col-sm-10">
            <input type="text" name="musicurl" value="<?php echo $userrow['musicurl']; ?>" placeholder="填写音乐外链" class="form-control"/>
        </div>
    </div>
    <?php if ($conf['fenzhan_editd'] > 0) {?>


    <div class="form-group">
      <label class="col-sm-2 control-label">本站域名</label><br>
      <div class="input-group col-sm-10">
        <input type="text" name="siteurl" value="<?php echo $userrow['siteurl']; ?>" class="form-control" disabled/>
        <div class="input-group-addon"><a href="changeurl.php">自助更换域名</a></div>
      </div>
    </div>
    <?php }?>
    <div class="form-group">
        <input type="submit" name="submit" value="修改" class="btn btn-primary form-control"/>
     </div>
   </form>
 </div>
<div class="panel-footer">
<span class="glyphicon glyphicon-info-sign"></span>
实用工具：<a href="uset.php?mod=copygg">一键复制其他站点排版</a>｜<a href="http://www.w3school.com.cn/tiy/t.asp?f=html_basic" target="_blank" rel="noreferrer">HTML在线测试</a>｜<a href="http://pic.xiaojianjian.net/" target="_blank" rel="noreferrer">图床</a>｜<a href="http://music.88qf.net/" target="_blank" rel="noreferrer">音乐外链</a>
</div>
</div>
<?php
} elseif ($mod == 'copygg_n' && $_POST['do'] == 'submit' && $userrow['power'] > 0) {
    $url     = $_POST['url'];
    $content = $_POST['content'];
    $url_arr = parse_url($url);
    if ($url_arr['host'] == $_SERVER['HTTP_HOST']) {
        showmsg('无法自己复制自己', 3);
    }

    $data = get_curl($url . 'api.php?act=siteinfo');
    $arr  = json_decode($data, true);
    if (array_key_exists('sitename', $arr)) {
        if (in_array('anounce', $content)) {
            $anounce = addslashes(str_replace($arr['kfqq'], $userrow['qq'], $arr['anounce']));
        } else {
            $anounce = addslashes($userrow['anounce']);
        }

        if (in_array('modal', $content)) {
            $modal = addslashes(str_replace($arr['kfqq'], $userrow['qq'], $arr['modal']));
        } else {
            $modal = addslashes($userrow['modal']);
        }

        if (in_array('bottom', $content)) {
            $bottom = addslashes(str_replace($arr['kfqq'], $userrow['qq'], $arr['bottom']));
        } else {
            $bottom = addslashes($userrow['bottom']);
        }
        $sds = $DB->query("UPDATE `pre_site` set anounce='$anounce',modal='$modal',bottom='$bottom' where `zid`='{$userrow['zid']}'");
        if ($sds) {
            showmsg('修改保存成功！', 1);
        } else {
            showmsg('修改保存失败，' . $DB->error(), 1);
        }
    } else {
        showmsg('获取数据失败，对方网站无法连接或非网站。', 4);
    }
} elseif ($mod == 'copygg') {
    ?>
<div class="panel panel-default">
<div class="panel-heading font-bold" style="background-color: #9999CC;color: white;">一键复制其他站点排版</h3></div>
<div class="">
  <form action="./uset.php?mod=copygg_n" method="post" class="form-horizontal" role="form"><input type="hidden" name="do" value="submit"/>
    <div class="form-group">
      <label class="col-sm-2 control-label">站点URL</label>
      <input type="text" name="url" value="" class="form-control" placeholder="http://www.qq.com/" required/>
    </div><br/>
    <div class="form-group">
      <label class="col-sm-2 control-label">复制内容：</label><br>
      <label class="col-sm-2 control-label"><input name="content[]" type="checkbox" value="anounce" checked/> 首页公告</label>
      <div class="col-sm-10"><label class="col-sm-2 control-label"><input name="content[]" type="checkbox" value="modal" checked/> 弹出公告</label>
      <div class="col-sm-10"><label class="col-sm-2 control-label"><input name="content[]" type="checkbox" value="bottom" checked/> 底部排版</label>
    </div>
    <input type="submit" name="submit" value="修改" class="btn btn-primary form-control"/>
  </form>
</div>
</div>
<p>你好</p>
<?php
} elseif ($mod == 'logo' && $userrow['power'] > 0) {
    if (intval($conf['fenzhan_logo_open']) !== 1) {
        showmsg('系统未开启logo上传功能', 3);
    } else {
        echo '<div class="block">
        <div class="block-title"><h3 class="panel-title">更改首页LOGO</h3></div>
    <div class="">提示：部分模板不显示logo图片，是正常现象！<br/>';
        if ($_POST['s'] == 1) {
            $extension = explode('.', $_FILES['logo']['name']);
            if (($length = count($extension)) > 1) {
                $ext = strtolower($extension[$length - 1]);
            }

            $uploaded_size    = $_FILES['logo']['size'];
            $uploaded_tmp     = $_FILES['logo']['tmp_name'];
            $uploaded_type    = $_FILES['logo']['type'];
            $uploaded_maxsize = $conf['fenzhan_imglimit'] >= 1024 ? $conf['fenzhan_imglimit'] * 1024 : 1024 * 1024;

            if ($uploaded_size > $uploaded_maxsize) {
                $LOG->writeLog('上传Logo', '上传logo失败，图片文件过大');
                echo "图片文件过大，请重新选择上传较小的文件试试<br>不要用直接截图出来的图片，可以试试先发送给别人，然后点保存再发新的那个图片！";
            } elseif ($ext == 'png' || $ext == 'gif' || $ext == 'jpg' || $ext == 'jpeg') {

                $logoPath = 'logo_' . $userrow['zid'] . '_' . substr(MD5(time() . rand(11, 9999)), 0, 16) . '.png';

                $data = uploadFile_fenzhan('logo', $logoPath, 'logo');
                if ($data['code'] == 0) {
                    $userrow['logo'] = $data['path'];
                    if ($DB->query("UPDATE cmy_site set `logo`=:logo where `zid`=:zid", [":logo" => $userrow['logo'], ":zid" => $userrow['zid']])) {
                        echo "成功上传文件!";
                    }
                    echo "成功上传文件!";
                } else {
                    $LOG->writeLog('上传Logo', '上传文件失败，文件信息：' . json_encode($_FILES['logo']) . '；失败原因：' . $data['msg']);
                    echo $data['msg'] . '，请联系站长' . $conf['zzqq'] . '处理';
                }
            } else {
                echo "文件格式不支持，请更换一个试试";
            }
        }

        if ($userrow['logo']) {
            if (stripos($userrow['logo'], '//') !== false) {
                $logo = $userrow['logo'];
            } elseif (file_exists(ROOT . $userrow['logo'])) {
                $logo = '../' . $userrow['logo'];
            } else {
                $logo = '../assets/img/logo.png';
            }
        } else {
            $logo = '../assets/img/logo.png';
        }

        echo '<form action="uset.php?mod=logo" method="POST" enctype="multipart/form-data"><label for="file"></label><input type="file" name="logo" id="logo" /><input type="hidden" name="s" value="1" /><br><input type="submit" class="btn btn-primary form-control" value="确认上传" /></form><br>现在的图片：<br><img src="' . $logo . '" style="max-width:30%">';
        echo '</div></div>';

    }

}?>
    </div>
</div>
<?php include 'footer.php'?>