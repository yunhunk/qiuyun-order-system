<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

$is_mobile = checkmobile();
$action    = $mod;

if ($mod == 'article') {
    if (!empty($row['keywords'])) {
        $conf['keywords'] = $row['keywords'];
    }
    if (!empty($row['seodescription'])) {
        $conf['description'] = $row['seodescription'];
    }
}
?>
<!DOCTYPE html>
    <html lang="cn-Zh">
    <head>
        <meta charset="utf-8" />
        <title><?php if ($mod == 'index') {echo $conf['sitename'] . ' - ' . $conf['title'];} else {echo $title . ' - ' . $conf['sitename'];}?></title>
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
        <meta name="keywords" content="<?php echo $conf['keywords']; ?>">
        <meta name="description" content="<?php echo $conf['description']; ?>">
        <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $cdnserver ?>assets/template/hyper/css/app.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $cdnserver ?>assets/template/hyper/css/style.css" rel="stylesheet" type="text/css" />
        <script src="<?php echo $cdnpublic ?>jquery/1.12.4/jquery.min.js"></script>
        <style type="text/css">
            ul.side-nav>li.active{
                background: #ffffff38;
            }
            ul.collapse>li.active{
                background: #7f79e888;
            }
            select.form-control {
                -webkit-appearance: none;
                outline: none;
            }
            #alert_frame img{
                max-width: 100%;
            }
        </style>
        <?php
//加载插件代码
hook('head');
?>
    </head>
    <body class="boxed-layout sidebar-enable" data-keep-enlarged="true">
        <div class="loading">
            <div id="loader"></div>
        </div>
        <div class="modal fade" id="kfModel">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="text-center my-3">

                          <a onclick="_AIHECONG('showChat')">联系客服</a>

                            <h3>售后客服</h3>
                        </div>
                        <table class="table table-hover table-bordered">
                            <tbody class="text-center">
                                <tr>
                                    <th>ＱＱ客服</th>
                                    <th>客服名称</th>
                                    <th>在线时间</th>
                                </tr>
<?php
foreach ($kfInfo as $key => $v) {
    echo '<tr>
        <td><a target="_blank" href="https://wpa.qq.com/msgrd?v=3&uin=' . $v['qq'] . '&site=qq&menu=yes" class="align-middle"><img class="align-middle" border="0" src="https://wpa.qq.com/pa?p=2:' . $v['qq'] . ':52" alt="点击这里给我发消息" title="点击这里给我发消息"> ' . $v['qq'] . '</a></td>
        <td style="vertical-align: middle !important;">' . $v['name'] . '</td>
        <td style="vertical-align: middle !important;">' . $conf['on_line'] . '</td>
    </tr>';
}
?>
                        </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">关闭</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="myModal">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="text-center my-3">
                            <h3>商品通知</h3>
                        </div>
                        <?php echo $conf['modal'] ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">关闭</button>
                    </div>
                </div>
            </div>
        </div>
        <?php if (!empty($conf['appurl'])): ?>
        <div id="appDown" class="modal fade" data-backdrop="static">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content modal-filled bg-success">
                    <div class="modal-body p-4">
                        <div class="text-center">
                            <i class="fa fa-cloud-download-alt h1"></i>
                            <h4 class="mt-1">下载APP，下单更便捷！</h4>
                            <p class="mt-1"><img src="https://www.liantu.com/api.php?text=<?php echo $conf['appurl']; ?>&bg=0acf97&fg=006a58&w=300&el=l" width="100%" alt="扫码下载APP"></p>
                            <p class="text-reset">注：不建议使用微信扫码，微信扫码请选择在浏览器打开！</p>
                            <div class="mt-1">
                                <a href="<?php echo $conf['appurl']; ?>" target="_blank" class="btn btn-warning text-white">直接下载</a>
                                <button type="button" class="btn btn-light" data-dismiss="modal">关闭</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif;?>
        <div class="wrapper shadow-none bg-white" <?php if (!$is_mobile) {
    echo 'style="min-height: 56rem;"';
}
?>>
            <?php if (!$is_mobile): ?>
            <div class="left-side-menu">
                <div class="slimscroll-menu" id="left-side-menu-container">
                    <!-- LOGO -->
                    <a href="./" class="logo text-center">
                        <span class="logo-lg">
                            <img src="<?php echo $logo ?>" alt="LOGO" height="45">
                        </span>
                        <span class="logo-sm">
                            <img src="<?php echo $cdnserver ?>assets/template/hyper/img/zanLogo.png" alt="LOGO" height="50">
                        </span>
                    </a>
                    <!-- 侧栏 -->
                    <ul class="metismenu side-nav">
                        <li class="side-nav-title side-nav-item">功能菜单</li>

                        <li class="side-nav-item">
                            <a href="./" _action="index" class="side-nav-link">
                                <i class="fa fa-home"></i>
                                <span> 网站首页 </span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="./?mod=query" _action="query" class="side-nav-link">
                                <i class="fa fa-search"></i>
                                <span> 查询订单 </span>
                            </a>
                        </li>
                        <?php if ($conf['fenzhan_buy'] == 1): ?>
                        <li class="side-nav-item">
                            <a href="./?mod=site" _action="site" class="side-nav-link">
                                <i class="fa fa-users"></i>
                                <span> 成为代理 </span>
                            </a>
                        </li>
                        <?php endif;?>
                        <?php if ($conf['gift_open'] == 1 || $conf['invite_tid']): ?>
                        <li class="side-nav-item">
                            <a href="javascript: void(0);" class="side-nav-link">
                                <i class="fa fa-gift"></i>
                                <span> 免费福利 </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-second-level collapse">
                                <?php if ($conf['gift_open'] == 1): ?>
                                <li>
                                    <a href="./?mod=gift" _action="gift">每日抽奖</a>
                                </li>
                                <?php endif;?>
                                <?php if ($conf['invite_tid']): ?>
                                <li>
                                    <a href="./?mod=invite" _action="invite">邀请有礼</a>
                                </li>
                                <?php endif;?>
                            </ul>
                        </li>
                        <?php endif;?>
                        <?php if ($conf['index_article'] == 1): ?>
                        <li class="side-nav-item">
                            <a href="<?php echo article_url() ?>" _action="<?php echo $mod == 'articlelist' ? 'articlelist' : 'article'; ?>" class="side-nav-link">
                                <i class="fa fa-file-alt"></i>
                                <span> 文章教程 </span>
                            </a>
                        </li>
                        <?php endif;?>
                        <?php if (!empty($conf['appurl'])): ?>
                         <li class="side-nav-item">
                            <a href="#appDown" data-toggle="modal" class="side-nav-link">
                                <i class="fa fa-cloud-download-alt"></i>
                                <span> APP下载 </span>
                                <img src="<?php echo $cdnserver ?>assets/template/hyper/img/hot.gif" style="max-height: 100%;"/>
                            </a>
                        </li>
                        <?php endif;?>

                         <li class="side-nav-title side-nav-item mt-1">关于本站</li>

                        <li class="side-nav-item">
                            <a href="#kfModel" data-toggle="modal" class="side-nav-link">
                                <i class="fa fa-qq"></i>
                                <span> 联系客服 </span>
                            </a>
                        </li>
                      <li class="side-nav-item">
                            <a href="./?mod=faq" _action="faq" class="side-nav-link">
                            <i class="fa fa-bell" aria-hidden="true"></i>
                            <span> 免责声明 </span>
                            </a>
                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>
            </div>
            <?php endif;?>
            <div class="content-page">
                <div class="content position-relative">
                    <div class="navbar-custom">
                        <?php if ($islogin2 == 1): ?>
                        <ul class="list-unstyled topbar-right-menu float-right mb-0">
                            <li class="dropdown notification-list">
                                <a class="nav-link dropdown-toggle nav-user arrow-none mr-0 p-0" data-toggle="dropdown" href="javascript:void(0);" style="padding: 17px 5px 17px 57px!important;">
                                    <span class="account-user-avatar">
                                        <img src="<?php echo ($islogin2 == 1) ? '//q2.qlogo.cn/headimg_dl?bs=qq&dst_uin=' . $userrow['qq'] . '&src_uin=' . $userrow['qq'] . '&fid=' . $userrow['qq'] . '&spec=100&url_enc=0&referer=bu_interface&term_type=PC' : 'assets/img/user.png' ?>" class="rounded-circle">
                                    </span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated topbar-dropdown-menu profile-dropdown">
                                    <div class=" dropdown-header noti-title">
                                        <h6 class="text-overflow m-0">Welcome !</h6>
                                    </div>
                                    <a href="<?php echo $cdnserver ?>user/" target="_blank" class="dropdown-item notify-item">
                                        <i class="fa fa-user mr-1 fa-fw"></i>
                                        <span>用户中心</span>
                                    </a>
                                    <a href="<?php echo $cdnserver ?>user/uset.php?mod=user" target="_blank" class="dropdown-item notify-item">
                                        <i class="fa fa-user-edit mr-1 fa-fw"></i>
                                        <span>我的资料</span>
                                    </a>
                                    <a href="<?php echo $cdnserver ?>user/qiandao.php" target="_blank" class="dropdown-item notify-item">
                                        <i class="fa fa-calendar-check mr-1 fa-fw"></i>
                                        <span>每日签到</span>
                                    </a>
                                    <a href="<?php echo $cdnserver ?>user/login.php?logout" target="_blank" class="dropdown-item notify-item">
                                        <i class="fa fa-sign-out-alt mr-1 fa-fw"></i>
                                        <span>退出登录</span>
                                    </a>
                                </div>
                            </li>
                        </ul>
                        <?php else: ?>
                            <div class="fc-button-group topbar-right-menu float-right mb-0" style="padding: calc(32px / 2) 0;">
                                <a href="<?php echo $cdnserver ?>user/login.php" pjax="no" class="btn btn-primary btn-sm mr-1">登录</a>
                                <a href="<?php echo $cdnserver ?>user/reg.php" pjax="no" class="btn btn-light btn-sm">注册</a>
                            </div>
                        <?php endif;?>
                        <?php if (!$is_mobile): ?>
                            <button class="button-menu-mobile open-left disable-btn">
                                <i class="fa fa-bars"></i>
                            </button>
                        <?php else: ?>
                            <a href="./" class="logo d-inline-block ml-2" style="line-height: 70px;">
                                <img src="<?php echo $logo ?>" height="30">
                            </a>
                        <?php endif;?>
                    </div>
                    <?php
//加载插件代码
hook('header');
?>
<section id="pjax-container">