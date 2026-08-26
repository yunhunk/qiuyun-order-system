<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

$hotshop_list = explode(",", $conf['hotshop_list']);
?>
<link rel="stylesheet" href="<?php echo $cdnserver ?>assets/css/head.css?<?php echo $jsver ?>">
<link rel="stylesheet" href="<?php echo $cdnserver ?>assets/css/common.css?<?php echo $jsver ?>">
<script type="text/javascript">var online = [];online[0]=0;online[1]=0;</script>

<?php if ($conf['hotshop_open'] == 1 && count($hotshop_list) > 0) {?>
<div class="modal fade" align="left" id="sptj" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
<div class="modal-dialog">
<div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(120deg, #0174DF 30%, #DF01D7 70%);">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"></span><span class="sr-only">Close</span></button>
            <center><h4 class="modal-title" id="myModalLabel"><b><font color="#fff">商品推荐</font></b></h4></center>
    </div>
    <br/>
    <?php
if ($conf['hotshop_open'] == 1 && $conf['hotshop_list'] != "") {
    $hotshop_list = explode(",", $conf['hotshop_list']);
    $num          = count($hotshop_list);
    for ($i = 0; $i < $num; $i++) {
        $tool    = $DB->get_row("select * from pre_tools where tid= ? limit 1", array($hotshop_list[$i]));
        $shopimg = $tool['shopimg'];
        if (empty($shopimg)) {
            $shopimg = "<?php echo $cdnserver ?>assets/img/Product/default.png";
        }
        $price = round($tool['price'], 2);
        echo '<div class="col-xs-6 col-sm-3 col-md-4 layui-anim layui-anim-scaleSpring" data-anim="layui-anim-upbit">
              <a href="?cid=' . $tool['cid'] . '&tid=' . $tool['tid'] . '">
              <div class="thumbnail" style="height:138px;">
                <center style="margin-top:5%;">
                 <img src="' . $shopimg . '" width="30" height="30" style="border-radius: 10px">
                 <hr class="layui-bg-blue" style="width:80%">' . $tool['name'] . '
                 <br/>
                 <font color="red">[￥' . $price . '元 ]</font><br/>
                 点击购买
                </center>
        </div>
        </a>
        </div>
            ';
    }
}
    ?>
    <table class="table table-bordered table-striped">
    <tbody>
    <tr>
    </tr>
    </tbody>
    </table>
    <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
    </div>
    </div>
    </div>
</div>
<?php }?>

<div class="modal fade" id="cmLoginModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">在线登录</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon">登录账号</div>
                        <input type="text" id="username" class="form-control" placeholder="请填写登录账号">
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon">登录密码</div>
                        <input type="password" id="password" class="form-control" placeholder="请填写登录密码">
                    </div>
                    <small>忘记密码？<a href="<?php echo $cdnserver ?>user/findpwd.php">点我</a>(找回后记得返回本页面哦)</small>
                </div>
                <br/>
                <div class="form-group">
                    <a class="btn btn-info btn-block" id="login" onclick="cm_login()">确定登录</a><br/>
                    <a class="btn btn-success btn-rounded" data-dismiss="modal" onclick="$('#cmRegModal').modal('show')">现在注册</a>
                    <a id="siterowmit" class="btn btn-primary btn-rounded" data-dismiss="modal" style="float:right;">取消登录</a>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="cmRegModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">注册新用户</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon">登录账号</div>
                        <input type="text" id="reg_username" class="form-control" placeholder="请填写登录账号">
                    </div>
                    <small>登录账号可以使用QQ，微信号，手机号等</small>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon">登录密码</div>
                        <input type="password" id="reg_password" class="form-control" placeholder="请填写登录密码">
                    </div>
                    <small>登录密码建议不要跟账号相同，可以使用字母数字和下划线等任意组合</small>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon">联系QQ</div>
                        <input type="text" id="reg_qq" class="form-control" placeholder="请填写联系QQ">
                    </div>
                    <small>填写的你QQ号码，方便联系和咨询我们</small>
                </div>
                <br/>
                <div class="form-group">
                    <a class="btn btn-info btn-block" id="reg" onclick="cm_reg()">立即注册</a><br/>
                    <a class="btn btn-success btn-rounded" data-dismiss="modal" onclick="$(\'#cmLoginModal\').modal(\'show\')">已有账号</a>
                    <a id="siterowmit" class="btn btn-primary btn-rounded" data-dismiss="modal" style="float:right;">取消注册</a>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="lqq" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin">
        <div class="modal-content">
            <div class="block block-themed block-transparent remove-margin-b">
                <div class="block-header bg-primary-dark">
                    <ul class="block-options">
                        <li>
                            <button data-dismiss="modal" type="button"><i class="si si-close"></i></button>
                        </li>
                    </ul>
                    <h4 class="block-title">免费拉圈圈99+</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        免费拉取圈圈标签赞 99+ ，不是100%成功哦！
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">请输入QQ</div>
                            <input type="text" name="qq" id="qq4" value="" class="form-control" required/>
                        </div>
                    </div>
                    <input type="submit" id="submit_lqq" class="btn btn-primary btn-block" value="立即提交">
                    <div id="result3" class="form-group text-center" style="display:none;"></div>
                    <br/>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-default" type="button" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>



<div align="left" aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="/userjs" role="dialog" style="display: none;" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(120deg, #FE2EF7 10%, #71D7A2 90%);">
                <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
                    ×
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    版本介绍
                </h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-borderless table-vcenter">
                        <thead>
                            <tr>
                                <th style="width: 100px;">
                                    功能
                                </th>
                                <th class="text-center" style="width: 20px;">
                                    专业版/旗舰版
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="active">
                                <td>
                                    独立网站/专属后台
                                </td>
                                <td class="text-center">
                                    <span class="btn btn-effect-ripple btn-xs btn-success" style="overflow: hidden; position: relative;">
                                        <i class="fa fa-check">
                                        </i>
                                    </span>
                                    <span class="btn btn-effect-ripple btn-xs btn-success" style="overflow: hidden; position: relative;">
                                        <i class="fa fa-check">
                                        </i>
                                    </span>
                                </td>
                            </tr>
                            <tr class="">
                                <td>
                                    低价拿货/调整价格
                                </td>
                                <td class="text-center">
                                    <span class="btn btn-effect-ripple btn-xs btn-success" style="overflow: hidden; position: relative;">
                                        <i class="fa fa-check">
                                        </i>
                                    </span>
                                    <span class="btn btn-effect-ripple btn-xs btn-success" style="overflow: hidden; position: relative;">
                                        <i class="fa fa-check">
                                        </i>
                                    </span>
                                </td>
                            </tr>
                            <tr class="info">
                                <td>
                                    搭建分站/管理分站
                                </td>
                                <td class="text-center">
                                    <span class="btn btn-effect-ripple btn-xs btn-danger" style="overflow: hidden; position: relative;">
                                        <i class="fa fa-close">
                                        </i>
                                    </span>
                                    <span class="btn btn-effect-ripple btn-xs btn-success" style="overflow: hidden; position: relative;">
                                        <i class="fa fa-check">
                                        </i>
                                    </span>
                                </td>
                            </tr>
                            <tr class="">
                                <td>
                                    超低密价/高额提成
                                </td>
                                <td class="text-center">
                                    <span class="btn btn-effect-ripple btn-xs btn-danger" style="overflow: hidden; position: relative;">
                                        <i class="fa fa-close">
                                        </i>
                                    </span>
                                    <span class="btn btn-effect-ripple btn-xs btn-success" style="overflow: hidden; position: relative;">
                                        <i class="fa fa-check">
                                        </i>
                                    </span>
                                </td>
                            </tr>
                            <tr class="danger">
                                <td>
                                    赠送APP
                                </td>
                                <td class="text-center">
                                    <span class="btn btn-effect-ripple btn-xs btn-danger" style="overflow: hidden; position: relative;">
                                        <i class="fa fa-close">
                                        </i>
                                    </span>
                                    <span class="btn btn-effect-ripple btn-xs btn-success" style="overflow: hidden; position: relative;">
                                        <i class="fa fa-check">
                                        </i>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal" type="button">
                    关闭
                </button>
            </div>
        </div>
    </div>
</div>



<div align="left" aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="qqdzz" role="dialog" style="display: none;" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="list-group-item reed" style="background:linear-gradient(120deg, #5ED1D7 10%, #71D7A2 90%);">
                <button class="close" data-dismiss="modal" type="button">
                    <span aria-hidden="true">
                        x
                    </span>
                    <span class="sr-only">
                        Close
                    </span>
                </button>
                <center>
                    <h4 class="modal-title" id="myModalLabel">
                        <b>
                            <font color="#fff">
                                数量要求
                            </font>
                        </b>
                    </h4>
                </center>
            </div>
            <br/>
            <div class="modal-body">
                <center>
                    <p class="bg-primary" style="background-color:#424242;padding: 10px;">
                        球球粉丝
                        <br/>
                        固定数量:100,200,400,800,
                        <br/>
                        1000,2000,4000,8000,10000,20000
                    </p>
                    <p class="bg-primary" style="background-color:#FF6666;padding: 10px;">
                        球球爱心
                        <br/>
                        固定数量:1000,2000,4000,
                        <br/>
                        8000,10000,20000,40000,80000
                    </p>
                </center>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">
                        我知道了
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>



<div align="left" aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="qmkg" role="dialog" style="display: none;" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="list-group-item reed" style="background:linear-gradient(120deg, #DF01A5 10%, #FF0080 90%);">
                <button class="close" data-dismiss="modal" type="button">
                    <span aria-hidden="true">
                    </span>
                    <span class="sr-only">
                        Close
                    </span>
                </button>
                <center>
                    <h4 class="modal-title" id="myModalLabel">
                        <b>
                            <font color="#fff">
                                经验上限表
                            </font>
                        </b>
                    </h4>
                </center>
            </div>
            <div class="modal-body">
                <center>
                    <p class="bg-primary" style="background-color:#424242;padding: 10px;">
                        0-6级： 每天可获得1000点经验
                    </p>
                    <p class="bg-primary" style="background-color:#FF6666;padding: 10px;">
                        7-9级： 每天可获得1500点经验
                    </p>
                    <p class="bg-primary" style="background-color:#0404B4;padding: 10px;">
                        10-12级：每天可获得3500点经验
                    </p>
                    <p class="bg-primary" style="background-color:#FF8000;padding: 10px;">
                        13-15级：每天可获得26000点经验
                    </p>
                    <p class="bg-primary" style="background-color:#04B431;padding: 10px;">
                        16-18级：每天可获得45000点经验
                    </p>
                </center>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">
                        我知道了
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>




<div class="modal fade" align="left" id="zlsm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">

   <div class="modal-dialog">

    <div class="modal-content">

         <div class="list-group-item reed" style="background:linear-gradient(120deg, #0000FF 10%, #FE2EF7 90%);">

        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"></span><span class="sr-only">Close</span></button>

    <center><h4 class="modal-title" id="myModalLabel"><b><font color="#fff">钻类介绍</font></b></h4></center>

      </div>

      <br/>

  <div class="modal-body">

<p class="bg-primary" style="background-color:#04B45F;padding: 10px;">

问题：什么是质保期，理论永久是什么？</p>

 <p class="bg-primary" style="background-color: #04B45F; padding: 10px;">

质保：理论永久，每个人用的时间都不一样，质保期就像家电的保修期一样，有问题可以联系客服处理哦！</p>     </div>

      <div class="modal-footer">

      <button type="button" class="btn btn-default" data-dismiss="modal">我知道了</button>

     </div>

   </div>

  </div>

 </div>




<div class="modal fade" align="left" id="cxsm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">
              <span aria-hidden="true">
                &times;
              </span>
              <span class="sr-only">
                Close
              </span>
            </button>
            <h4 class="modal-title" id="myModalLabel">
              我该如何查单？
            </h4>
          </div>
          <li class="list-group-item">
            <font color="green">
              例如您购买商品时需要填邮箱账号，这种需要请输入您的完整邮箱账号
            </font>
          </li>
          <li class="list-group-item">
            <b>
              <font color="blue">
                如果付款后，查单账号输入正确还是没有订单，可能是漏单了！联系客服提供付款截图处理
              </font>
            </b>
          </li>
          <li class="list-group-item">
            例如您购买的是爱奇艺会员，输入下单的手机号即可查询订单
          </li>
          <li class="list-group-item">
            例如您购买的是需要收货的商品，需要输入下单时第一个填写的收货人或者手机号即可
          </li>
          <li class="list-group-item">
            <font color="red">
              如果您不知道下单账号是什么，可以不填写，直接点击查询，则会根据浏览器缓存查询
            </font>
          </li>
          <li class="list-group-item">
            <font color="red">
              最后我们建议注册个账号登录后下单，避免查不到订单
            </font>
          </li>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">
              关闭
            </button>
          </div>
        </div>
    </div>
</div>



        <div class="modal fade" align="left" id="kefu" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                  <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  <span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">常见问题</h4></div>
              <div class="modal-body">
                <div class="tab-pane fade in" id="faq">
                  <div class="panel-group" id="accordion">
                    <div class="panel panel-info">
                      <a class="cm-kfqy-warning collapsed" data-toggle="collapse" data-parent="#accordion" href="#1">
                      <b>为什么下单了没有处理呢？</b>
                      </a>
                      <div id="1" class="panel-collapse collapse" style="height: 0px;">
                        <div class="panel-body">由于本站80%以上的业务订单采用软件全自动处理，下单自动记录订单并排队处理，若订单超过6小时仍然显示待处理请联系客服！（注：是6小时显示未处理联系客服，不是6小时未到账联系客服！）<br/>如果你购买的是钻类商品，一般说明上会写到账时间，点播和爆卡的比较慢，大概2~5天都很正常，官方的较快，当天到账<br/>如果你购买的是其他手工商品，说明会写操作说明或到账时间，请参照说明并如实操作，才能尽快给你完成订单哦~<br/>
                        如果超过7天以上，请咨询客服是否出现维护等情况，可根据情况退单处理~</div></div>
                    </div>
                    <div class="panel panel-warning">
                      <a class="cm-kfqy collapsed" data-toggle="collapse" data-parent="#accordion" href="#2" class="collapsed">
                        <b>QQ空间业务好久没开刷？</b>
                      </a>
                      <div id="2" class="panel-collapse collapse" style="height: 0px;">
                        <div class="panel-body">1.空间权限设为所有人可见
                          <br/>2.空间被单封请勿下单（这种是QQ好友才能进去，而且好友是看不出来异常的，当陌生人进去就提示空间维护）
                          <br/>3.空间最好有2、3条说说</div></div>
                    </div>
                    <div class="panel panel-warning">
                      <a class="cm-kfqy" data-toggle="collapse" data-parent="#accordion" href="#7" class="collapsed">
                        <b>全名k歌这些业务好久没开刷？</b>
                      </a>
                      <div id="7" class="panel-collapse collapse" style="height: 0px;">
                        <div class="panel-body">1.下单前先确认输的信息是否正确！
                          <br/>2.请检查作品是否违规或者出现审核等无法正常观看的情况<br/>
                        3.请检查下单时的作品链接是否正确，超过24小时未开始联系客服处理</div></div>
                    </div>
                    <div class="panel panel-warning">
                      <a class="cm-kfqy" data-toggle="collapse" data-parent="#accordion" href="#11" class="collapsed">
                        <b>虚拟商品没收到卡密？</b>
                      </a>
                      <div id="11" class="panel-collapse collapse" style="height: 0px;">
                        <div class="panel-body">下单必须填写正确的邮箱号！
                          <br/>邮箱用于接收卡密以及查询订单
                          <br/>购买完请进入查单页面查询（点击详情）
                          <br/>点击详情后会弹出卡密信息</div></div>
                    </div>
                    <div class="panel panel-warning">
                      <a class="cm-kfqy" data-toggle="collapse" data-parent="#accordion" href="#13" class="collapsed">
                        <b>理论永久钻好久没到账？</b>
                      </a>
                      <div id="13" class="panel-collapse collapse" style="height: 0px;">
                        <div class="panel-body">1.本身自已开通该业务请勿下单！！！
                          <br/>2.质保25天/稳定开单
                          <br/>3.期间请不要修改密码！
                          <br/>4.关闭设备所和网页登陆保护</div></div>
                    </div>
                    <br/>
                    <center>若以上没有帮助到您 - 请联系客服处理！</center></div>
                    <table class="table table-bordered" style="text-align:center">
                      <tbody>
                        <tr height="25" style="font-size: 13px;">
                          <td style="width: 35%;">客服列表</td>
                          <td style="width: 35%;">联系方式</td>
                          <td style="width: 30%;">操作</td></tr>
<?php
foreach ($kfInfo as $row) {
    if (empty($row['name'])) {
        $row['name'] = '在线客服';
    }

    echo '<tr height="25" style="font-size: 13px;">
          <td>
            <img src="//q4.qlogo.cn/headimg_dl?dst_uin=' . $row['qq'] . '&spec=100" style="width:18%;border-radius:50%; overflow:hidden;">&nbsp;' . $row['name'] . '
            </td>
          <td>' . $row['qq'] . '</td>
          <td>
          <a href="http://wpa.qq.com/msgrd?v=3&uin=' . $row['qq'] . '&site=qq&menu=yes" target="_blank" class="btn btn-success btn-xs" onclick="return confirm(\'有事请直奔主题！\');">联系</a>
            </td>
          </tr>';
}
?>
                      </tbody>
                    </table>
                    <p style="color: red">注意：联系客服后直接说明清楚问题并发相关下单账号，等待客服回复哦</p>

              </div>
            </div>
          </div>
        </div>
      </div>

        <div class="modal fade" align="left" id="about" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                  <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  <span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">常见问题</h4></div>
              <div class="modal-body">
                <div class="tab-pane fade in" id="faq">
                  <div class="panel-group" id="accordion">
                    <div class="panel panel-info">
                      <a class="cm-kfqy-warning collapsed" data-toggle="collapse" data-parent="#accordion" href="#1">
                      <b>为什么下单了没有处理呢？</b>
                      </a>
                      <div id="1" class="panel-collapse collapse" style="height: 0px;">
                        <div class="panel-body">部分自动发货商品是自动处理的，但需要排队需要等待处理<br/>
                        部分手动商品需要手动处理，速度会慢一点，如果订单长时间未更新状态，请联系客服
                        <br/>
                        如果订单状态未更新超过3天以上，请咨询客服是否出现维护等情况，可根据情况退单处理~</div></div>
                    </div>
                    <div class="panel panel-warning">
                      <a class="cm-kfqy collapsed" data-toggle="collapse" data-parent="#accordion" href="#2" class="collapsed">
                        <b>如何申请售后</b>
                      </a>
                      <div id="2" class="panel-collapse collapse" style="height: 0px;">
                        <div class="panel-body">1.你可以在查单处理查询到对应的订单，点击[详情]即可申请售后工单
                          <br/>2.如果无法申请请联系下方的客服列表处理
                          </div>
                        </div>
                    </div>
                    <table class="table table-bordered" style="text-align:center">
                      <tbody>
                        <tr height="25" style="font-size: 13px;">
                          <td style="width: 35%;">客服列表</td>
                          <td style="width: 35%;">联系方式</td>
                          <td style="width: 30%;">操作</td></tr>
                        </tr>

<?php
foreach ($kfInfo as $row) {
    if (empty($row['name'])) {
        $row['name'] = '在线客服';
    }

    echo '
    <tr height="25" style="font-size: 13px;">
    <td>
      <img src="//q4.qlogo.cn/headimg_dl?dst_uin=' . $row['qq'] . '&spec=100" style="width:18%;border-radius:50%; overflow:hidden;">&nbsp;' . $row['name'] . '</td>
    <td>' . $row['qq'] . '</td>
    <td>
    <a href="http://wpa.qq.com/msgrd?v=3&uin=' . $row['qq'] . '&site=qq&menu=yes" target="_blank" class="btn btn-success btn-xs" onclick="return confirm(\'有事请直奔主题！\');">联系</a>
    </td>
    </tr>';
}
?>
                      </tbody>
                    </table>
                  <center>若以上没有帮助到您 - 请联系客服处理！</center></div>
              </div>
            </div>
          </div>
        </div>
      </div>


<div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="numAlert" role="dialog" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
                    ×
                </button>
                <h4 class="modal-title">
                    “下单份数”是什么意思？
                </h4>
            </div>
            <div class="modal-body">
                <center>
                    <font color="red">
                        商品的面值×份数=下单数量（份数默认为1）
                    </font>
                    <hr/>
                    例如您购买：10元话费
                    <br/>
                    下单份数选3，就是总共会获得30元话费
                    <hr/>
                    例如您购买：1双鞋子
                    <br/>
                    下单份数选3，就是总共会获得3双鞋子
                    <hr/>
                    <font color="red">
                      以此类推 本站其他商品都是如此
                    </font>
                    <br/>
                    <br/>
                </center>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-default btn-rounded" data-dismiss="modal" type="button">
                    明白了
                </button>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="work" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="work_title"></h5>
            </div>
            <div class="modal-body">
          <div class="card bg-secondary shadow border-0">
          <div class="card-body talkBox">
              <div class="px-lg-4" id="con">
              </div>
              <div id="work_ok" style="display: none"></div>
          </div>
          <div class="panel panel-body" style="padding: 10px 0;margin-bottom: 0px;">
                <input type="hidden" id="work_orderid" value="">
                <div id="huifuWork" class="col-xs-12 text-center" style="margin: 5px auto; padding-right: 0px;padding-left: 0px;">
                  <div class="form-group">
                     <textarea id="work_content" class="form-control" rows="4" placeholder="请认真填写该订单遇到的真实问题，填写越清楚真实，解决的就越快！"></textarea>

                  </div><br/>
                  <a class="btn btn-success btn-block btn-sm" onclick="workBack()">提交回复内容</a><br/>
                  <a class="btn btn-danger btn-block btn-sm" onclick="closeWorkCall()" data-dismiss="modal">关闭售后窗口</a>
                </div>
                <div id="closeWorkInfo" class="col-xs-12 text-center" style="margin: 5px auto;padding:4px 8;display: none;">
                  <a class="btn btn-danger btn-block btn-sm" data-dismiss="modal">关闭售后窗口</a>
                </div>
          </div>
          </div>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="tousu" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="myModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="tousu_title">在线申请售后</h4>
            </div>
            <div class="modal-body">
                  <div class="form-group">
                      <div class="input-group">
                          <div class="input-group-addon">订单编号</div>
                          <input id="tousu_id"  class="form-control" type="number" value="" readonly="readonly"/>
                      </div>
                  </div>
                  <div class="form-group">
                      <div class="input-group">
                          <div class="input-group-addon">售后类型</div>
                          <select class="form-control" id="tousu_type">
                              <option value="1">48小时以上还未到账(补单)</option>
                              <option value="2">卡密错误</option>
                              <option value="3">充值没到账</option>
                              <option value="4">订单中途改了密码</option>
                              <option value="0">其他问题</option>
                          </select>
                      </div>
                  </div>
                  <div class="form-group">
                      <div class="input-group">
                          <div class="input-group-addon">问题描述</div>
                          <textarea id="tousu_content" class="form-control" rows="4" placeholder="请认真填写该订单遇到的问题，填写越清楚，解决的就越快！"></textarea>
                      </div>
                  </div>
                  <div class="form-group">
                      <div class="input-group">
                          <div class="input-group-addon">联系方式(可选)</div>
                          <input id="tousu_qq"  class="form-control" type="text" placeholder="QQ号、手机号、微信号" value=""/>
                      </div>
                  </div>
            </div>
            <div class="modal-footer">
                <a class="btn btn-success btn-block" onclick="tousuOrder()" id="tousu_btn">提交售后申请</a><br/>
                <a class="btn btn-warning btn-block" data-dismiss="modal" aria-hidden="true">取消售后申请</a>
            </div>
        </div>
    </div>
</div>
<div aria-hidden="true" class="modal fade" id="disclaimer" role="dialog" style="display: none;" tabindex="100">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="block block-themed block-transparent remove-margin-b">
                <div class="block-header bg-primary-dark">
                    <ul class="block-options">
                    </ul>
                    免责声明
                </div>
                <div class="modal-body">
                    <b>
                        1.由于网民反应自己发布的作品没有点赞跟评论觉得很尴尬,不好意思再次发布,我们平台因此诞生,若贵公司觉得我们干扰公司自身的发展请联系删除,谢谢！
                        <br/>
                        <br/>
                        2.本平台所属资源源于网络他人制作，均为他人一手申请、并非上传资料、或早年收藏，绝非非法途径获得的黑号。
                        <br/>
                        <br/>
                        3.所列商品若侵犯到您的权益，请立即联系我们删除。本平台不负任何相关责任。
                        <br/>
                        <br/>
                        4.依照所属购买平台业务规则限制、冻结或终止您的号码使用，可能会给您造成一定的损失，该损失由买家自行承担，本平台不承担任何责任。
                        <br/>
                        <br/>
                        5.平台部分商品如遇限制 可能存在 掉量 少量 等等一系列 能补尽量补 或者不支持补,下单默认同意本条 本店不负任何相关责任。
                        <br/>
                        <br/>
                        切勿使用本平台用于违法犯罪行为，一经发现，我们将配合相关部门坚决打击到底。
                        <br/>
                        <br/>
                        内容版权纠纷声明：
                        <br/>
                        如若本平台信息有侵犯到您的知识产权或任何利益，请发送邮件到
                        <?php echo $conf['zz_zzqq'] ?>@qq.com删除，本站将第一时间删除并下架相关内容，感谢您的理解
                    </b>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-default" data-dismiss="modal" type="button">
                    关闭
                </button>
            </div>
        </div>
    </div>
</div>

<!--版本介绍-->
<div class="modal fade" align="left" id="userjs" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
		<div class="modal-header">
			<h4 class="modal-title" id="myModalLabel">版本介绍</h4>
		</div>
		<div class="block">
            <div class="table-responsive">
                <table class="table table-borderless table-vcenter">
                    <thead>
                        <tr>
                            <th style="width: 100px;">功能</th>
                            <th class="text-center" style="width: 20px;">普及版/专业版</th>
                        </tr>
                    </thead>
					<tbody>
						<tr class="active">
                            <td>专属卡密平台</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
                        <tr class="">
                            <td>三种在线支付接口</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
						<tr class="success">
                            <td>专属网站域名</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
						<tr class="">
                            <td>賺取用户提成</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>

						<tr class="">
                            <td>设置商品价格</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
                        	<tr class="info">
                            <td>賺取下级分战提成</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-danger"><i class="fa fa-close"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
						<tr class="warning">
                            <td>设置下级分战商品价格</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-danger"><i class="fa fa-close"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
						<tr class="">
                            <td>搭建下级分战</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-danger"><i class="fa fa-close"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
						<tr class="danger">
                            <td>赠送专属精致APP</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-danger"><i class="fa fa-close"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
                    </tbody>
                </table>
            </div>
				<center style="color: #b2b2b2;"><small><em>* 自己的能力决定着你的收入！</em></small></center>
        </div>
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
		</div>
    </div>
  </div>
</div>
<!--版本介绍-->

