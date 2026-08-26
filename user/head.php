<?php
if ($conf['ui_user'] == 1) {
    $ui_user = array('bg-dark', 'bg-white-only', 'bg-dark');
} else {
    $ui_user = array('bg-success', 'bg-success', 'bg-light dker');
}

if ($conf['index_run'] != "" && $conf['index_run'] != 1) {
    if (empty($conf['index_run_alert'])) {
        $conf['index_run_alert'] = "网站维护升级中，请耐心等待恢复~";
    }
    echo "<br/><br/><br/><center><h4>" . $conf['index_run_alert'] . "</h4></center>";
    exit;
}
@header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8" />
  <title><?php echo $title ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <link href="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="../assets/cmui/css/main.css?<?php echo $jsver ?>">
  <link id="theme-link" rel="stylesheet" href="<?php echo $_COOKIE['optionThemeColor'] ? $_COOKIE['optionThemeColor'] : '../assets/appui/css/themes/amethyst-2.4.css'; ?>">
  <script src="<?php echo $cdnpublic ?>jquery/3.1.1/jquery.min.js"></script>
  <script src="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/user/css/animate.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/user/css/app.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo $cdnpublic ?>toastr.js/latest/toastr.min.css" type="text/css" />
  <script src="../assets/appui/js/plugins.js"></script>
  <script src="<?php echo $cdnserver ?>assets/user/js/app.js"></script>
  <script src="<?php echo $cdnpublic ?>layer/3.4.0/layer.js?<?php echo $jsver ?>"></script>
  <script src="<?php echo $cdnpublic ?>distpicker/2.0.3/distpicker.min.js"></script>
  <script src="<?php echo $cdnserver ?>assets/public/tooltip/tooltip.js?<?php echo $jsver ?>"></script>
  <!--[if lt IE 9]>
    <script src="<?php echo $cdnpublic ?>html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="<?php echo $cdnpublic ?>respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<style>
.bg-success {
  background-color: #00b386 !important; 
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
<body class="">
<?php if ($isLogin2 == 1) {
    if ($userrow['status'] == 0) {
        sysmsg('你的账号已被封禁！', true);exit;
    }
    if ($userrow['lasttime'] == null) {
        $DB->query("UPDATE `pre_site` set lasttime='{$date}' where zid='{$userrow['zid']}'");
    }
    ?>
<div class="app app-header-fixed  ">
  <header id="header" class="app-header navbar ng-scope" role="menu">
      <div class="navbar-header <?php echo $ui_user[0] ?>">
        <button class="pull-right visible-xs" ui-toggle="off-screen" target=".app-aside" ui-scroll="app">
          <i class="glyphicon glyphicon-align-justify"></i>
        </button>
        <a href="./" class="navbar-brand text-lt">
        <span>🚀</span>
          <span class="hidden-folded m-l-xs">分站管理中心</span>
        </a>
      </div>
<!--<i class="fa fa-child"></i>-->
      <div class="collapse pos-rlt navbar-collapse box-shadow <?php echo $ui_user[1] ?>">
        <!-- buttons -->
        <div class="nav navbar-nav hidden-xs">
          <a href="#" class="btn no-shadow navbar-btn" ui-toggle="app-aside-folded" target=".app">
            <i class="fa fa-dedent fa-fw text"> 菜单</i>
            <i class="fa fa-indent fa-fw text-active">菜单</i>
          </a>
        </div>
        <!-- / buttons -->

        <!-- nabar right -->
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">
            <a href="#" data-toggle="dropdown" class="dropdown-toggle clear" data-toggle="dropdown">
              <span class="thumb-sm avatar pull-right m-t-n-sm m-b-n-sm m-l-sm">
                <img src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $userrow['qq'] ?>&spec=100">
                <i class="on md b-white bottom"></i>
              </span>
              <span class="hidden-sm hidden-md"><?php echo $userrow['user'] ?></span> <b class="caret"></b>
            </a>
            <!-- dropdown -->
            <ul class="dropdown-menu animated fadeInRight w">
              <li>
                <a href="./">
                  <span>用户中心</span>
                </a>
              </li>
              <li>
                <a href="./uset.php?mod=user">
                  <span>修改资料</span>
                </a>
              </li>
             <li>
                <a href="../">
                  <span>返回首页</span>
                </a>
              </li>
              <li class="divider"></li>
              <li>
                <a ui-sref="access.signin" href="login.php?logout">退出登录</a>
              </li>
            </ul>
            <!-- / dropdown -->
          </li>
        </ul>
        <!-- / navbar right -->
      </div>
      <!-- / navbar collapse -->
  </header>
  <!-- / header -->
  <!-- aside -->
  <aside id="aside" class="app-aside hidden-xs <?php echo $ui_user[2] ?>">
      <div class="aside-wrap">
        <div class="navi-wrap">

          <!-- nav -->
          <nav ui-nav class="navi">
            <ul class="nav">
              <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                <span>导航</span>
              </li>
              <li class="<?php echo checkIfActive(',index') ?>">
                <a href="./">
                  <i class="fa fa-user"></i>
                  <span>用户中心</span>
                </a>
              </li>
              <li class="<?php echo checkIfActive('shop') ?>">
                <a href="./shop.php">
                  <i class="fa fa-cart-plus"></i>
                  <span>自助下单</span>
                </a>
              </li>
              <li>
                <a href="../">
                  <i class="fa fa-home"></i>
                  <span>回到首页</span>
                </a>
              </li>
              <?php if ($conf['app_open'] == 1 && $userrow['power'] > 0): ?>
              <li class="<?php echo checkIfActive('app') ?>">
                <a href="./app.php">
                  <i class="fa fa-star"></i>
                  <span>App下载</span>
                </a>
              </li>
              <?php endif?>
              <?php if ($conf['fenzhan_tg'] == 1) {?>
              <li class="<?php echo checkIfActive('tuiguang') ?>">
                <a href="./tuiguang.php">
                  <i class="fa fa-certificate"></i>
                  <span>推广功能</span>
                </a>
              </li>
              <?php }?>
              <?php if ($conf['workorder_open'] == 1) {?>
             <li class="<?php echo checkIfActive('workorder') ?>">
                <a href="./workorder.php">
                  <i class="fa fa-check-square-o"></i>
                  <span>我的工单</span>
                </a>
              </li>
               <?php }?>
          <?php if ($userrow['power'] > 0) {?>
          <li class="<?php echo checkIfActive('classlist,shoplist,sitelist,userlist') ?>">
            <a href class="auto">
              <span class="pull-right text-muted">
                <i class="fa fa-fw fa-angle-right text"></i>
                <i class="fa fa-fw fa-angle-down text-active"></i>
              </span>
              <i class="fa fa-codepen"></i>
              <span>商品管理</span>
            </a>
            <ul class="nav nav-sub dk">
              <li class="<?php echo checkIfActive('classlist') ?>">
                <a href="./classlist.php">
                  <span>分类管理</span>
                </a>
              </li>
              <li class="<?php echo checkIfActive('shoplist') ?>">
                <a href="./shoplist.php">
                  <span>价格设置</span>
                </a>
              </li>
              <?php if ($userrow['power'] == 2) {?>
              <li class="<?php echo checkIfActive('sitelist') ?>">
                <a href="./sitelist.php">
                  <span>分站列表</span>
                </a>
              </li><?php }?>
              <li class="<?php echo checkIfActive('userlist') ?>">
                <a href="./userlist.php">
                  <span>用户列表</span>
                </a>
              </li>
            </ul>
          </li>
        <?php }?>
        <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                <span>查询</span>
              </li>
              <li class="<?php echo checkIfActive('list') ?>">
                <a href="./list.php">
                  <i class="fa fa-list"></i>
                  <span>订单查询</span>
                </a>
              </li>
              <li class="<?php echo checkIfActive('record') ?>">
                <a href="./record.php">
                  <i class="fa fa-hashtag"></i>
                  <span>收支明细</span>
                </a>
              </li>
              <li class="<?php echo checkIfActive('toMoney') ?>">
                <a href="./toMoney.php">
                  <i class="fa fa-codiepie"></i>
                  <span>提成结转</span>
                </a>
              </li>
            <?php if ($userrow['power'] > 0) {?>
                  <li class="<?php echo checkIfActive('rank') ?>">
                   <a href="./rank.php">
                   <i class="fa fa-line-chart"></i>
                     <span>分站排行</span>
                   </a>
                  </li>
            <?php }?>
              <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                <span>其他</span>
              </li>
              <li class="<?php echo checkIfActive('uset.user,uset.site,uset.logo,uset.skimg') ?>">
                <a href class="auto">
                  <span class="pull-right text-muted">
                    <i class="fa fa-fw fa-angle-right text"></i>
                    <i class="fa fa-fw fa-angle-down text-active"></i>
                  </span>
                  <i class="fa fa-resistance"></i>
                  <span>系统设置</span>
                </a>
                <ul class="nav nav-sub dk">
                   <li class="<?php echo checkIfActive('uset.user') ?>">
                    <a href="./uset.php?mod=user">
                      <span>用户资料设置</span>
                    </a>
                  </li>
                <?php if ($userrow['power'] > 0) {?>
                    <li class="<?php echo checkIfActive('uset.site') ?>">
                      <a href="./uset.php?mod=site">
                        <span>网站信息设置</span>
                      </a>
                    </li>
                    <li class="<?php echo checkIfActive('changeurl') ?>">
                      <a href="./changeurl.php">
                        <span>网站域名更换</span>
                      </a>
                    </li>
                    <?php if (intval($conf['fenzhan_logo_open']) === 1) {?>
                    <li class="<?php echo checkIfActive('uset.logo') ?>">
                      <a href="./uset.php?mod=logo">
                        <span>网站Logo设置</span>
                      </a>
                    </li>
                    <?php }?>
                <?php }?>
                </ul>
              </li>
              <li class="<?php echo checkIfActive('message') ?>">
                <a href="./message.php">
                  <i class="fa fa-bullhorn"></i>
                  <span>消息通知</span>
                </a>
              </li>
              <?php if ($userrow['power'] > 0) {?>
              <li class="<?php echo checkIfActive('faq') ?>">
                <a href="./faq.php">
                  <i class="fa fa-exclamation-circle"></i>
                  <span>常见问题</span>
                </a>
              </li>
              <?php }?>
              <li>
                <a ui-sref="access.signin" href="login.php?logout">
                  <i class="fa fa-power-off"></i>
                  <span>退出登录</span>
                </a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
  </aside>
<div id="content" class="app-content" role="main">
    <div class="app-content-body ">
        <div class="bg-light lter b-b wrapper-sm ng-scope">
          <ul class="breadcrumb" style="padding: 0;margin: 0;">
            <li><i class="fa fa-home"></i><a href="./">管理中心</a></li>
            <li><?php echo $title ?></li>
          </ul>
        </div>
  <!-- / aside -->
<?php
if ($userrow['endtime'] < $date && $userrow['power'] > 0 && time() - strtotime($date) < 5) {
        $fileName = getFileName();
        if ($conf['zz_renew_open'] == 1 && stripos($fileName, "index.php") === false && $fileName != "") {
            if ($userrow['power'] == 2) {
                $price = $conf['fenzhan_cost2'];
            } else {
                $price = $conf['fenzhan_cost'];
            }

            $fenzhan_expiry = $conf['fenzhan_expiry'] > 0 ? $conf['fenzhan_expiry'] : 12;
            if (strtotime($userrow['endtime']) > time()) {
                $endtime = date("Y-m-d H:i:s", strtotime("+{$fenzhan_expiry} months", strtotime($userrow['endtime'])));
            } else {
                $endtime = date("Y-m-d H:i:s", strtotime("+{$fenzhan_expiry} months"));
            }

            if ($price > 0 && $price > $userrow['money']) {
                showmsg('您当前网站已到期，自动续费失败，请先充值续费或联系网站客服' . $conf['zzqq'] . '处理！<a href="./index.php#chongzhi">点我充值</a>', 3);
                exit;
            }
            $data = [
                ':endtime' => $endtime,
                ':price'   => $price,
                ':zid'     => $userrow['zid'],
            ];
            $sql = $DB->query("UPDATE `pre_site` set `endtime`=:endtime,`money`=`money`-:price where `zid`= :zid", $data);
            if ($sql) {
                addPointLogs($userrow['zid'], $price, '消费', '自动续期站点', '');
                exit('<body>站点到期已自动续费，请稍后..</body><script type="text/javascript">setTimeout(function(){window.location.reload();}, 3000);</script>');
            } else {
                showmsg('续费失败，' . $DB->error() . '<br>请联系站长' . $conf['zz_zzqq'] . '反馈解决！', 3);
                exit;
            }
        }
    }
}
?>
