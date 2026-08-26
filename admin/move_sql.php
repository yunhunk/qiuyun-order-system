<?php

$sql_build = 1006;

$sql_config = array(
    'site'      => "
DROP TABLE IF EXISTS `cmy_site`;
create table IF NOT EXISTS `cmy_site` (
  `zid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `upzid` int(11) unsigned NOT NULL DEFAULT '0',
  `power` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `mid` INT(11) DEFAULT '0' COMMENT '密价ID',
  `siteurl` varchar(64) NULL DEFAULT '',
  `siteurl2` varchar(255) NULL DEFAULT '',
  `user` varchar(20) NOT NULL,
  `pwd` varchar(32) NOT NULL,
  `salt` varchar(32) NULL DEFAULT NULL COMMENT '密码盐',
  `money` decimal(10,5) NOT NULL DEFAULT '0.00000' COMMENT '账户余额',
  `money_other` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '其他',
  `point` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '提成余额',
  `pay_type` int(1) NOT NULL DEFAULT '0' COMMENT '提现支付方式',
  `pay_account` varchar(50) DEFAULT NULL COMMENT '提现账户',
  `pay_name` varchar(50) DEFAULT NULL COMMENT '提现收款名',
  `qq` varchar(12) DEFAULT NULL,
  `sitename` varchar(80) DEFAULT NULL,
  `title` varchar(80) DEFAULT NULL,
  `keywords` text NULL,
  `description` text NULL,
  `addtime` datetime DEFAULT NULL,
  `kaurl` varchar(50) DEFAULT NULL,
  `kfqq` varchar(500) DEFAULT '' COMMENT '客服QQ',
  `kfwx` varchar(500) DEFAULT ''  COMMENT '客服微信',
  `musicurl` varchar(500) DEFAULT ''  COMMENT '音乐链接',
  `appurl` text NULL,
  `app_task_id` varchar(20) DEFAULT NULL,
  `app_weburl` varchar(64) DEFAULT NULL,
  `app_d_num` int(11) DEFAULT '0',
  `app_d_now` int(11) DEFAULT '0',
  `app_d_lastday` int(11) DEFAULT '0',
  `anounce` text NULL,
  `bottom` text NULL,
  `modal` text NULL,
  `alert` text NULL,
  `class` text NULL,
  `hidezid` text NULL,
  `price` longtext NULL,
  `skimg` text NULL,
  `logo` text NULL,
  `ktfz_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ktfz_price2` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ktfz_siteurl` text NULL,
  `email` varchar(200) DEFAULT ''  COMMENT '邮箱',
  `tel` varchar(11) DEFAULT NULL   COMMENT '手机号',
  `utype` int(1) NOT NULL DEFAULT '0',
  `lasttime` datetime DEFAULT NULL,
  `loginIp` varchar(255) DEFAULT NULL,
  `endtime` datetime DEFAULT NULL  COMMENT '到期时间',
  `template` varchar(10) DEFAULT NULL  COMMENT '模板',
  `msgread` varchar(255) DEFAULT NULL  COMMENT '未读',
  `status` tinyint(2) NOT NULL DEFAULT '0'  COMMENT '状态',
  `regular` tinyint(2) NOT NULL DEFAULT '0'  COMMENT '支付结果',
  `is_priced` tinyint(2) NOT NULL DEFAULT '0' COMMENT '价格同步主站',
  `master_open` tinyint(2) NOT NULL DEFAULT '0'  COMMENT '供货权限',
  `master_price` tinyint(2) NOT NULL DEFAULT '0'  COMMENT '供货押金',
  `closebz` text NULL,
  `iprice` text NULL,
  `createtime` bigint(10) NOT NULL DEFAULT '0'  COMMENT '创建时间',
  `updatetime` bigint(10) NOT NULL DEFAULT '0'  COMMENT '更新时间',
  PRIMARY KEY (`zid`),
  index `user` (`user`),
  index `siteurl` (`siteurl`),
  index `siteurl2` (`siteurl2`),
  index `qq` (`qq`),
  index `status` (`status`),
  index `master_price` (`master_price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000;
  ",
    'pay'       => "
  DROP TABLE IF EXISTS `pre_pay`;
CREATE TABLE `pre_pay` (
  `trade_no` varchar(64) NOT NULL,
  `type` varchar(20) NULL,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `tid` int(11) NOT NULL,
  `input` text NULL,
  `inputattr` varchar(255) DEFAULT '',
  `num` int(11) unsigned NOT NULL DEFAULT '1',
  `stock_id` int(11)  NULL DEFAULT '0',
  `addtime` datetime NULL,
  `endtime` datetime NULL,
  `name` varchar(64) NULL,
  `money` varchar(32) NULL,
  `ip` varchar(20) NULL,
  `userid` varchar(32) DEFAULT NULL,
  `inviteid` int(11) unsigned DEFAULT NULL,
  `siteurl` varchar(64) DEFAULT NULL,
  `epay_url` varchar(64) DEFAULT NULL,
  `epay_pid` varchar(64) DEFAULT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `call_ret` text NULL COMMENT '支付结果',
  `kid` int(11) unsigned NULL DEFAULT '0',
  PRIMARY KEY (`trade_no`),
  index trade_no (`trade_no`),
  index type (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci;
  ",
    'faka'      => "
DROP TABLE IF EXISTS `cmy_faka`;
create table IF NOT EXISTS `cmy_faka` (
  `kid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '卡密所属站点',
  `tid` int(11) unsigned NOT NULL,
  `km` varchar(255) DEFAULT NULL,
  `pw` varchar(255) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  `usetime` datetime DEFAULT NULL,
  `orderid` int(11) unsigned NOT NULL DEFAULT '0',
  `status` int(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`kid`),
  index `status` (`status`),
  index `orderid` (`orderid`),
  index `tid` (`tid`),
  index `zid` (`zid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci;
  ",
    'orders'    => "
DROP TABLE IF EXISTS `cmy_orders`;
create table IF NOT EXISTS `cmy_orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(11) unsigned NOT NULL,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `sid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '供货商ID',
  `type` varchar(64) NOT NULL DEFAULT '' COMMENT '支付方式',
  `input` varchar(700) NULL DEFAULT NULL,
  `input2` varchar(800) NULL,
  `input3` varchar(320) NULL,
  `input4` varchar(320) NULL,
  `input5` varchar(320) NULL,
  `inputattr` varchar(255) DEFAULT '',
  `stock_id` int(11) NULL DEFAULT '0',
  `bz`     text NULL,
  `exporder` varchar(64) DEFAULT '',
  `value` int(11) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `refund` int(2) NOT NULL DEFAULT '0' COMMENT '退款状态 1已退款',
  `refund_price` decimal(10,2) NOT NULL COMMENT '退款金额',
  `djzt` tinyint(2) NOT NULL DEFAULT '0',
  `djorder` varchar(32) DEFAULT NULL,
  `url` varchar(64) DEFAULT NULL,
  `result` text NULL,
  `uptime` int(11) DEFAULT NULL  COMMENT '订单同步时间',
  `userid` varchar(32) DEFAULT NULL,
  `payorder` varchar(64) DEFAULT NULL,
  `cartorder` varchar(64) DEFAULT NULL,
  `money` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cost` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '分站购价',
  `price1` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '成本价',
  `price2` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '供货商成本价',
  `addtime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  index `zid` (`zid`),
  index `input` (`input`),
  index `payorder` (`payorder`),
  index `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci;
  ",
    'price'     => "
  DROP TABLE IF EXISTS `pre_price`;
CREATE TABLE `pre_price`(
  `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '0',
  `kind` tinyint(2) NOT NULL DEFAULT '0' COMMENT '1 累加 2 百分比',
  `name` varchar(255) NOT NULL,
  `p_0` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '1 累加 2 百分比',
  `p_1` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '1 累加 2 百分比',
  `p_2` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '1 累加 2 百分比',
  `addtime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci;
  ",
    'tools'     => "

DROP TABLE IF EXISTS `cmy_tools`;
create table IF NOT EXISTS `cmy_tools` (
  `tid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '所属站点ID',
  `cid` int(11) unsigned NOT NULL DEFAULT '0' ,
  `cids` varchar(500) DEFAULT NULL,
  `sort` int(11) NULL DEFAULT '1',
  `deposit` decimal(10,2) NULL DEFAULT '0.00' COMMENT '押金',
  `name` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `value` int(11) unsigned NOT NULL DEFAULT '0',
  `stock_open` int(1)  NOT NULL DEFAULT '0' COMMENT '库存开关',
  `stock` int(11) NOT NULL DEFAULT '0' COMMENT '库存',
  `stock_time` bigint(10) NOT NULL DEFAULT '0' COMMENT '库存更新时间',
  `sale` int(11) NOT NULL DEFAULT '0' COMMENT '销量',
  `unit` varchar(128) NULL DEFAULT '' COMMENT '单位',
  `prid` int(11) NOT NULL DEFAULT '0' COMMENT '价格模板',
  `price1` decimal(10,4) NULL DEFAULT '0.0000',
  `price2` decimal(10,4) NULL DEFAULT '0.0000' COMMENT '供货商成本',
  `priceold` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '供货商记忆价格',
  `price` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `cost` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `cost2` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `specs_id` int(11)  NULL DEFAULT '0',
  `attr_id` int(11)  NULL DEFAULT '0',
  `prices` longtext NULL DEFAULT NULL,
  `discount` decimal(5,4) NULL DEFAULT '1.0000' COMMENT '折扣价 如0.7',
  `input` varchar(250) NOT NULL,
  `inputs` varchar(255) DEFAULT NULL,
  `desc` text NULL,
  `alert` text NULL,
  `result` text NULL COMMENT '处理信息',
  `result_succ` text NULL COMMENT '下单成功预置信息',
  `shopimg` text NULL COMMENT '商品图片',
  `validate` tinyint(2) NOT NULL DEFAULT '0',
  `permission` tinyint(2) NOT NULL DEFAULT '0',
  `card` varchar(255)  NULL DEFAULT NULL,
  `card_pass` varchar(255)  NULL DEFAULT NULL,
  `min` int(11) NOT NULL DEFAULT '1',
  `max` int(11) NOT NULL DEFAULT '0',
  `is_curl` tinyint(2) NOT NULL DEFAULT '0',
  `is_email` tinyint(2) NOT NULL DEFAULT '1',
  `is_rank` tinyint(2) NOT NULL DEFAULT '1',
  `pay_alipay` tinyint(2) NOT NULL DEFAULT '1',
  `pay_qqpay` tinyint(2) NOT NULL DEFAULT '1',
  `pay_wxpay` tinyint(2) NOT NULL DEFAULT '1',
  `pay_rmb` tinyint(2) NOT NULL DEFAULT '1',
  `curl` varchar(255) DEFAULT NULL,
  `repeat` tinyint(2) NOT NULL DEFAULT '0',
  `multi` tinyint(2) NOT NULL DEFAULT '0',
  `check` tinyint(2) NOT NULL DEFAULT '0',
  `check_val` varchar(255) DEFAULT '0',
  `shequ` int(3) NOT NULL DEFAULT '0',
  `goods_id` varchar(16) NULL DEFAULT '',
  `goods_type` int(11) NOT NULL DEFAULT '0',
  `goods_param` varchar(200) DEFAULT NULL,
  `goods_form` varchar(500) DEFAULT NULL,
  `close` tinyint(2) NOT NULL DEFAULT '0' COMMENT '隐藏状态',
  `close_login` tinyint(1) NOT NULL DEFAULT '0',
  `close_alert` text NULL,
  `mesh_list` longtext NULL,
  `condition` tinyint(2) NOT NULL DEFAULT '0' COMMENT '供货审核状态',
  `condition_msg` varchar(500) NULL DEFAULT '' COMMENT '审核备注',
  `active` tinyint(2) NOT NULL DEFAULT '0' COMMENT '上架状态',
  `addtime` datetime  NULL DEFAULT NULL,
  `updatetime` datetime  NULL DEFAULT NULL,
  `cardstime` bigint(10)  DEFAULT '0' COMMENT '上次加卡时间',
  `notifytime` bigint(10)  DEFAULT '0' COMMENT '上次通知加卡时间',
  `like_up` int(11) NOT NULL DEFAULT '0' COMMENT '点赞-顶数量',
  `like_down` int(11) NOT NULL DEFAULT '0' COMMENT '点赞-踩数量',
  `uptime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`),
  INDEX `tid` (`tid`),
  INDEX `cid` (`cid`),
  INDEX `sort` (`sort`),
  INDEX `sale` (`sale`),
  INDEX `stock` (`stock`),
  INDEX `close` (`close`),
  INDEX `deposit`(`deposit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci  COMMENT '商品列表';
  ",
    'class'     => "
DROP TABLE IF EXISTS `pre_class`;
CREATE TABLE `pre_class` (
  `cid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `upcid` int(11) unsigned DEFAULT '0',
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `sort` int(11) NOT NULL DEFAULT '10',
  `name` varchar(255) NOT NULL,
  `shopimg` text NULL,
  `alert` text NULL,
  `hidepays` VARCHAR(255) DEFAULT '',
  `islogin` tinyint(2) NOT NULL DEFAULT '0',
  `active` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  index sort (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci;
  ",
    'shequ'     => "
  DROP TABLE IF EXISTS `pre_shequ`;
CREATE TABLE `pre_shequ` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` text NULL,
  `paypwd` text NULL,
  `alias` varchar(32) NULL COMMENT '别名 扩展用',
  `paytype` tinyint(1) NOT NULL DEFAULT '0',
  `type` tinyint(3) NOT NULL DEFAULT '0',
  `proxy` tinyint(2) DEFAULT '0' COMMENT '代理',
  `method` INT(11) DEFAULT '0' COMMENT '方法',
  `orderstatus` tinyint(2) NOT NULL DEFAULT '1',
  `ssl` tinyint(2) NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci;
  ",
    'qiandao'   => "
  DROP TABLE IF EXISTS `pre_qiandao`;
CREATE TABLE `pre_qiandao`(
  `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `qq` VARCHAR(20) DEFAULT NULL,
  `reward` decimal(10,2) NOT NULL DEFAULT '0.00',
  `date` date DEFAULT NULL,
  `time` datetime NOT NULL,
  `continue` int(11) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  index id (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci;
  ",
    'message'   => "
  DROP TABLE IF EXISTS `pre_message`;
CREATE TABLE `pre_message`(
  `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `cid` int(11) NOT NULL DEFAULT '1',
  `type` int(2) NOT NULL DEFAULT '0',
  `title` VARCHAR(128) NOT NULL,
  `author` VARCHAR(128) NOT NULL,
  `seotitle` VARCHAR(255) DEFAULT NULL,
  `keywords` VARCHAR(255) NOT NULL,
  `seokeywords` VARCHAR(255) DEFAULT NULL,
  `seodescription` text NULL,
  `content` text NULL,
  `color` VARCHAR(20) DEFAULT NULL,
  `count` int(11) unsigned NOT NULL DEFAULT 0,
  `top` tinyint(2) NOT NULL DEFAULT 0,
  `sort` int(11) unsigned NOT NULL DEFAULT 0,
  `active` tinyint(2) NOT NULL DEFAULT 0,
  `addtime` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  index id(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci;
  ",
    'workorder' => "
DROP TABLE IF EXISTS `cmy_workorder`;
create table IF NOT EXISTS `cmy_workorder`(
  `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '订单用户ID',
  `sid` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '供货商ID',
  `type` int(1) unsigned NOT NULL DEFAULT '0',
  `orderid` int(11) unsigned NOT NULL DEFAULT '0',
  `qq` varchar(100) NULL DEFAULT NULL,
  `name` text NULL,
  `content` text NULL,
  `piclist` text NULL,
  `addtime` datetime NOT NULL,
  `endtime` datetime NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT 0,
  `ts` text NULL,
  PRIMARY KEY (`id`),
  KEY zid (`zid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci;
  ",
    'cart'      => "
  DROP TABLE IF EXISTS `pre_cart`;
CREATE TABLE `pre_cart` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` varchar(32) NOT NULL,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `tid` int(11) NOT NULL,
  `input` text NULL,
  `inputattr` varchar(255) DEFAULT '',
  `stock_id` int(11) NULL DEFAULT '0',
  `num` int(11) unsigned NOT NULL DEFAULT '1',
  `money` varchar(32) NULL,
  `addtime` datetime NULL,
  `endtime` datetime NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY userid (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci;
  ",
    'points'    => "
  DROP TABLE IF EXISTS `pre_points`;
CREATE TABLE `pre_points` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '0',
  `action` varchar(255) NOT NULL,
  `point` decimal(10,5) NOT NULL DEFAULT '0.00',
  `bz` varchar(1024) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  `orderid` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  index id (`id`),
  index action (`action`),
  index addtime (`addtime`),
  index action_addtime (`action`, `addtime`),
  index zid_action_addtime (`zid`, `action`, `addtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci;
  ",
    'kms'       => "
  DROP TABLE IF EXISTS `pre_kms`;
CREATE TABLE `pre_kms` (
  `kid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(11) unsigned NOT NULL,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `km` varchar(255) NOT NULL,
  `value` int(11) unsigned NOT NULL DEFAULT '0',
  `addtime` timestamp NULL DEFAULT NULL,
  `user` varchar(20) NOT NULL DEFAULT '0',
  `usetime` timestamp NULL DEFAULT NULL,
  `money` varchar(32) DEFAULT NULL,
  `orderid` int(11) unsigned NULL DEFAULT '0',
  PRIMARY KEY (`kid`),
  index kid (`kid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci;
  ",
    'tixian'    => "
  DROP TABLE IF EXISTS `pre_tixian`;
CREATE TABLE `pre_tixian` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL,
  `type` int(2) NOT NULL DEFAULT '0',
  `subtype` int(2) NOT NULL DEFAULT '0',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00',
  `realmoney` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pay_type` int(2) NOT NULL DEFAULT '0',
  `pay_account` varchar(50) NOT NULL,
  `pay_name` varchar(50) NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `addtime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  index id (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci;
  ",
    'logs'      => "
  DROP TABLE IF EXISTS `pre_logs`;
CREATE TABLE `pre_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `action` varchar(32) NOT NULL DEFAULT '货源对接',
  `param` varchar(255) NOT NULL DEFAULT '',
  `result` longtext NULL,
  `addtime` datetime DEFAULT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `orderid` int(11)  DEFAULT null,
  PRIMARY KEY (`id`),
  index id (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci;
  ",

    'gift'      => "
  DROP TABLE IF EXISTS `pre_gift`;
CREATE TABLE `pre_gift` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` text NULL,
  `tid` int(11) unsigned NOT NULL,
  `rate` int(3) NOT NULL,
  `ok` tinyint(1) NOT NULL DEFAULT 0,
  `not` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci;
  ",
    'giftlog'   => "
  DROP TABLE IF EXISTS `pre_giftlog`;
CREATE TABLE `pre_giftlog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT 0,
  `tid` int(11) unsigned NOT NULL,
  `gid` int(11) unsigned NOT NULL,
  `userid` varchar(32) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `addtime` datetime DEFAULT NULL,
  `tradeno` varchar(32) DEFAULT NULL,
  `input` varchar(64) DEFAULT NULL,
  `status` tinyint(2) NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci;
  ",

);
