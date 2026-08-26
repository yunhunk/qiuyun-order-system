ALTER TABLE `cmy_tools` 
    ADD  `like_up` int(11) NOT NULL DEFAULT '0' COMMENT '点赞-顶数量' after `uptime`;
ALTER TABLE `cmy_tools` 
    ADD `like_down` int(11) NOT NULL DEFAULT '0' COMMENT '点赞-踩数量' after `like_up`;

ALTER TABLE `cmy_tools` 
    ADD `deposit` decimal(10,2) NULL DEFAULT '0.00' COMMENT '押金' after `sort`;

ALTER TABLE `cmy_tools` 
    ADD `price2` decimal(10,4) NULL DEFAULT '0.0000' COMMENT '供货商成本' after `price1`;

ALTER TABLE `cmy_tools` 
    ADD `priceold` decimal(10,4) NULL DEFAULT '0.0000' COMMENT '供货商记忆价格' after `price2`;

ALTER TABLE `cmy_orders` 
    ADD `price2` decimal(10,4) NULL DEFAULT '0.0000' COMMENT '供货商成本' after `price1`;

ALTER TABLE `cmy_orders` 
    ADD `sid` int(11) NOT NULL DEFAULT '0' COMMENT '供货商ID' after `tid`;

ALTER TABLE `cmy_tools` 
    ADD `condition` tinyint(2) NOT NULL DEFAULT '0' COMMENT '供货审核状态' after `close`;

ALTER TABLE `cmy_tools` 
    ADD `condition_msg` varchar(500) NULL DEFAULT '' COMMENT '审核备注' after `condition`;

UPDATE `cmy_tools`  SET `condition`='1' WHERE 1;

ALTER TABLE `cmy_faka` 
    ADD `zid` int(11) NOT NULL DEFAULT '1' COMMENT '供货ID' after `tid`;

ALTER TABLE `cmy_faka` ADD index `zid_tid` (`zid`, `tid`);
ALTER TABLE `cmy_faka` ADD index `tid_orderid` (`tid`, `orderid`);

-- 修改插件字段
ALTER TABLE `cmy_plugin` DROP `type`;

ALTER TABLE `cmy_plugin` ADD `type` VARCHAR(20) NULL DEFAULT '' COMMENT '插件类型' after `id`;

ALTER TABLE `cmy_plugin` ADD `link` varchar(200) DEFAULT '' COMMENT '作者链接' after `build`;

ALTER TABLE `cmy_plugin` ADD `config` text NULL COMMENT '插件自定义配置' after `link`;

ALTER TABLE `cmy_tixian` ADD `note` text NULL COMMENT '备注' after `status`;

ALTER TABLE `cmy_workorder` 
    ADD `sid` int(11) NOT NULL DEFAULT '0' COMMENT '供货商ID' after `id`;

-- ----------------------------
-- pre_sms_tpl 支付多接口通道
-- ----------------------------
CREATE TABLE `cmy_channel` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mode` varchar(20) DEFAULT '' COMMENT '使用分区 all index site',
  `type` varchar(20) NOT NULL DEFAULT 'alipay' COMMENT '支付方式 qqpay wxpay alipay',
  `plugin` varchar(30) NOT NULL DEFAULT 'epay' COMMENT '通道ID名',
  `name` varchar(30) NOT NULL,
  `rate` decimal(5,2) NOT NULL DEFAULT '100.00',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `appurl` varchar(255) DEFAULT NULL,
  `appid` varchar(255) DEFAULT NULL,
  `appkey` text DEFAULT NULL,
  `appsecret` text DEFAULT NULL,
  `appmchid` varchar(255) DEFAULT NULL,
  `apptype` varchar(50) DEFAULT NULL,
  `daytop` int(10) DEFAULT 0  COMMENT '每天上限',
  `daystatus` int(1) DEFAULT 0,
  `min` varchar(12) NULL DEFAULT NULL COMMENT '自动启用金额',
  `paymin` varchar(12) NULL DEFAULT NULL,
  `paymax` varchar(12) NULL DEFAULT NULL,
  `appwxmp` int(11) NULL DEFAULT NULL,
  `appwxa` int(11) NULL DEFAULT NULL,
  `appswitch` tinyint(4) NULL DEFAULT NULL,
  `beizhu` varchar(500) NULL DEFAULT '' COMMENT '备注',
  `addtime` datetime NULL COMMENT '添加时间',
  `updatetime` datetime NULL COMMENT '更新时间',
 PRIMARY KEY (`id`),
 INDEX `type` (`type`),
 INDEX `status` (`status`),
 INDEX `min` (`min`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `cmy_menu`;
CREATE TABLE IF NOT EXISTS `cmy_menu`(
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `upid` int(11) unsigned NULL DEFAULT '0' COMMENT '父菜单 ID',
  `name` varchar(32) NOT NULL DEFAULT '菜单名称' COMMENT '菜单名称',
  `url` varchar(128) NULL DEFAULT '' COMMENT '菜单Url',
  `wherein` text DEFAULT NULL COMMENT '选中条件 name、name.mod',
  `icon` varchar(64) NULL DEFAULT '' COMMENT 'fa图标class类名',
  `iconnav` varchar(64) NULL DEFAULT '' COMMENT '父菜单右侧 图标地址',
  `sort` int(11) NULL DEFAULT '0' COMMENT '排序',
  `status` int(2) NULL default '1'  COMMENT '1显示 2隐藏',
  `label` int(2) NULL default '1'  COMMENT '分区标签 1是 0否',
  `keywords` text default NULL COMMENT '关键词，多个用英文,隔开 检索菜单用', 
  `fixed` int(2) NULL default '0'  COMMENT '固定 1是 0否',
  `noedit` int(2) NULL default '0'  COMMENT '不可修改 1是 0否',
  `isadd` int(2) NULL default '0'  COMMENT '是否自定义新增 1是 0否',
  `addtime` int(11) NOT NULL DEFAULT '1636908915',
  `updatetime` int(11) NOT NULL DEFAULT '1636908915',
  PRIMARY KEY (`id`),
  index upid (`upid`),
  index sort (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci COMMENT='后台菜单列表';

INSERT INTO `cmy_menu` VALUES (1,  0, '后台主页', '', '', '', '', 0, '1', 1, '', 1, 1, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (2,  0, '后台首页', './', '', 'fa fa-home', '', 1, '1', 0, '', 1, 1, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (5,  0, '数据管理', '', '', '', '', 4, '1', 1, '', 0, 1, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (6,  0, '订单管理', './list.php', 'list', 'fa fa-list', '', 5, '1', 0, '', 0, 1, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (7,  0, '运维管理', 'auto', 'auto', 'fa fa-comments', '', 6, '1', 0, '', 0, 1, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (8,  7, '工单列表', './workorder.php', 'workorder', '', '', 7, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (9,  7, '工单设置', './set.php?mod=workorder', 'set.workorder', '', '', 8, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (10, 7, '员工系统', './account.php', 'account', '', '', 9, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (11, 7, '数据清理', './clean.php', 'clean', '', '', 10, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (12, 7, '检测更新', './update.php', 'update', '', '', 11, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
-- INSERT INTO `cmy_menu` VALUES (13, 7, 'Bug建议', './set.php?mod=bugWork', 'set.bugWork', '', '', 12, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (17,  0, '明细管理', 'auto', 'auto', 'fa fa-calendar', '', 16, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (18,  17, '分站明细', './record.php', 'record', '', '', 17, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (19,  17, '支付记录', './paylist.php', 'paylist', '', '', 18, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (20,  17, '登录日志', './fzlogs.php', 'fzlogs', '', '', 19, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (22,  0, '商品管理', 'auto', 'auto', 'fa fa-shopping-cart', '', 21, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (23,  22, '批量上架', './shophelper.php', 'shophelper', '', '', 22, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (24,  22, '分类列表', './classlist.php', 'classlist2,classlist', '', '', 23, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (25,  22, '商品列表', './shoplist.php', 'shoplist,shopedit.add,shopedit.edit', '', '', 24, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (26,  22, '卡密列表', './kamilist.php', 'kamilist', '', '', 25, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (27,  22, '加价模板', './price.php', 'price', '', '', 26, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (28,  22, '价格监控', './set.php?mod=pricejk', 'set.pricejk,price.prjklogs', '', '', 27, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (30,  22, '密价管理', './superPrice.php', 'superPrice', '', '', 29, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (32,  22, '商品推荐', './codelogs.php', 'codelogs', '', '', 31, '0', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (34,  0, '虚拟商品', 'auto', 'auto', 'fa fa-th', '', 33, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (35,  34, '库存管理', './fakalist.php', 'fakalist', '', '', 34, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (36,  34, '添加卡密', './fakakms.php?my=add', 'fakakms.add', '', '', 35, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (37,  34, '发信模板', './set.php?mod=mailmodel', 'set.mailmodel', '', '', 36, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (38,  0, '代理管理', 'auto', 'auto', 'fa fa-sitemap', '', 37, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (39,  38, '代理列表', './sitelist.php', 'sitelist,sitelist.add,sitelist.add2,sitelist.edit', '', '', 38, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (40,  38, '用户列表', './userlist.php', 'userlist,userlist.add,userlist.edit', '', '', 39, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (41,  38, '充值返利', './set.php?mod=recharge_set', 'set.recharge_set,set.recharge_n', '', '', 40, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (42,  38, '提现管理', './tixian.php', 'tixian', '', '', 41, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (43,  38, '分站设置', './set.php?mod=fenzhan', 'set.fenzhan,set.fenzhan_n', '', '', 42, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (44,  38, '提现设置', './set.php?mod=tixian', 'set.tixian,set.tixian_n', '', '', 43, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (45,  0, '数据排行', 'auto', 'auto', 'fa fa-flag', '', 44, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (46,  45, '排行奖励设置', './set.php?mod=rank', 'set.rank', '', '', 45, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (47,  45, '商品金额排行', './rank.php?mod=shop', 'rank.shop', '', '', 46, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (48,  45, '域名订单排行', './rank.php?mod=site', 'rank.site', '', '', 47, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (49,  45, '分站充值排行', './rank.php?mod=reg', 'rank.reg', '', '', 48, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (50,  45, '分站余额排行', './rank.php?mod=rmb', 'rank.rmb', '', '', 49, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (51,  45, '分站销售排行', './rank.php?mod=money', 'rank.money', '', '', 50, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (52,  45, '分站订单排行', './rank.php?mod=order', 'rank.order', '', '', 51, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (53,  45, '分站提成排行', './rank.php?mod=point', 'rank.point', '', '', 52, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (54,  45, '分站消费排行', './rank.php?mod=buy', 'rank.buy', '', '', 53, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (55,  0, '文章管理', './message.php', 'message,message.add,message.edit', 'fa fa-paper-plane', '', 54, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (56,  0, '监控管理', 'auto', 'auto', 'fa fa-retweet', '', 55, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (57,  0, '短信邮件', '', '', '', '', 56, '1', 1, '', 1, 1, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (58,  0, '短信设置', 'auto', 'auto', 'fa fa-industry', '', 57, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (59,  58, '短信接口', './sms.api.php', 'sms.api', '', '', 58, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (60,  58, '短信模板', './sms.template.php', 'sms.template', '', '', 59, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (61,  58, '发送记录', './sms.log.php', 'sms.log', '', '', 60, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (63,  0, '邮件设置', 'auto', 'auto', 'fa fa-cutlery', '', 62, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (64,  63, '邮件接口', './mails.api.php', 'mails.api', '', '', 63, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (65,  63, '邮件模板', './mails.template.php', 'mails.template', '', '', 64, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (66,  63, '发送记录', './mails.log.php', 'mails.log', '', '', 65, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (68,  0, '货源对接', '', '', '', '', 67, '1', 1, '', 1, 1, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (69,  0, '进货对接', 'auto', 'auto', 'fa fa-cubes', '', 68, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (70,  69, '货源对接列表', './shequlist.php', 'set.pricejk,shequlist,shequlist.add,shequlist.edit', '', '', 69, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (71,  69, '价格监控设置', './set.php?mod=pricejk', 'set.pricejk', '', '', 70, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (72,  69, '对接日志列表', './logs.php', 'logs', '', '', 71, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (73,  69, '商品克隆功能', './clone.php', 'clone', '', '', 72, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (74,  0, '系统功能', '', '', '', '', 73, '1', 1, '', 1, 1, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (75,  0, '主站设置', 'auto', 'auto', 'fa fa-cog', '', 74, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (76,  75, '系统在线更新', './update.php', 'update', '', '', 75, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (77,  75, '后台资料设置', './set.php?mod=user', 'set.user', '', '', 76, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (78,  75, '网站基本信息', './set.php?mod=info', 'set.info', '', '', 77, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (79,  75, '订单相关设置', './set.php?mod=order', 'set.order', '', '', 78, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (80,  75, '网站核心设置', './set.php?mod=site', 'set.site', '', '', 79, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (81,  75, '文件储存设置', './set.php?mod=uploadFile', 'set.uploadFile', '', '', 80, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (82,  75, '免费商品设置', './set.php?mod=freeSet', 'set.freeSet', '', '', 81, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (83,  75, '短信验证配置', './set.php?mod=sms', 'set.sms', '', '', 82, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (84,  75, '支付接口设置', './set.php?mod=pay', 'set.pay', '', '', 83, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (86,  75, '推荐公告设置', './tjset.php', 'tjset.tjgg_n,tjset', '', '', 85, '0', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (87,  75, '网站公告设置', './set.php?mod=gonggao', 'set.gonggao', '', '', 86, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (88,  75, '邮箱验证配置', './set.php?mod=ems', 'set.ems', '', '', 87, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (89,  75, '首页模板设置', './set.php?mod=template', 'set.template,set.template_set', '', '', 88, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (90,  75, 'LOGO和背景', './set.php?mod=logo', 'set.logo,set.upbgimg', '', '', 89, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (91,  0, '附加功能', 'auto', 'auto', 'fa fa-calendar', '', 90, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (92,  91, '邀人推广功能', './set.php?mod=invite', 'set.invite', '', '', 91, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (93,  91, '抽奖功能设置', './set.php?mod=choujiang', 'set.choujiang', '', '', 92, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (97,  91, '签到系统设置', './set.php?mod=qiandao', 'set.qiandao', '', '', 96, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (98,  91, '防洪接口设置', './set.php?mod=dwz', 'set.dwz,set.dwz_n', '', '', 97, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (99,  91, '旧站数据转移', './move.php', 'move', '', '', 98, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (100,  0, '其他', '', '', '', '', 99, '1', 1, '', 1, 1, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (102,  0, '退出后台', './login.php?logout', 'cron', 'fa fa-power-off', '', 101, '1', 0, '', 0, 1, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (104,  56, '监控设置', './set.php?mod=cron_set', 'set.cron_set', '', '', 103, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (105,  56, '监控任务', './cron.php', 'cron', '', '', 104, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (106,  17, '商品日志', './shoplog.php', 'shoplog', '', '', 21, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (107,  75, 'api对接设置', './set.php?mod=api_set', 'set.api_set', '', '', 90, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (108,  69, '网站对接设置', './set.php?mod=api_set', 'set.api_set', '', '', 69, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (109,  0, '供货管理', 'auto', 'auto', 'fa fa-cutlery', '', 30, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (110,  109, '供货系统设置', './master.set.php', 'master.set', '', '', 63, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (111,  109, '供货商品列表', './master.shop.php', 'master.shop', '', '', 63, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (112,  109, '审核商品列表', './master.check.php', 'master.check', '', '', 64, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (113,  109, '供货商户列表', './master.user.php', 'master.user', '', '', 64, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (114,  109, '商户收支明细', './master.record.php', 'master.record', '', '', 64, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
INSERT INTO `cmy_menu` VALUES (115,  109, '商户提现管理', './master.tixian.php', 'master.tixian', '', '', 64, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);
-- INSERT INTO `cmy_menu` VALUES (116,  109, '供货排行统计', './master.rank.php', 'master.rank', '', '', 64, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (116,  0, '库存告急', './shopstock.php', 'shopstock', 'fa fa-bell', '', 91, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (117,  91, '修复工具', './check.php', 'check', 'fa fa-check-square-o', '', 3, '1', 0, '', 0, 1, 0, 1636908915, 1636908915);

INSERT INTO `cmy_menu` VALUES (120,  0, '商品动态', './toolsmessage.php', 'toolsmessage', 'fa fa-bell', '', 55, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

INSERT INTO `cmy_config` VALUES ('master_open', '1');
INSERT INTO `cmy_config` VALUES ('master_price', '100');
INSERT INTO `cmy_config` VALUES ('master_vip_goods', '1');
INSERT INTO `cmy_config` VALUES ('master_vip_price', '300');
INSERT INTO `cmy_config` VALUES ('master_nostock_close_day', '3');
INSERT INTO `cmy_config` VALUES ('master_kucun_notify_open', '1');
INSERT INTO `cmy_config` VALUES ('master_kucun_notify_time', '6');
INSERT INTO `cmy_config` VALUES ('master_notify_workorder_email', '1');
INSERT INTO `cmy_config` VALUES ('master_notify_tixian', '0');
INSERT INTO `cmy_config` VALUES ('master_tousu_rmb_remove', '1');
INSERT INTO `cmy_config` VALUES ('master_login_type', '1');
INSERT INTO `cmy_config` VALUES ('master_register_type', '1');
INSERT INTO `cmy_config` VALUES ('master_deposit_sort', '0');
INSERT INTO `cmy_config` VALUES ('ems_check_change_email', '1');
INSERT INTO `cmy_config` VALUES ('ems_check_change_info', '1');
INSERT INTO `cmy_config` VALUES ('ems_check_change_pwd', '1');
INSERT INTO `cmy_config` VALUES ('ems_check_findpwd', '1');
INSERT INTO `cmy_config` VALUES ('ems_check_login', '1');
INSERT INTO `cmy_config` VALUES ('ems_check_register', '1');
INSERT INTO `cmy_config` VALUES ('master_notify_goods_email', '0');

create table IF NOT EXISTS `cmy_master` (
  `zid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `upzid` int(11) unsigned NOT NULL DEFAULT '0',
  `power` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `user` varchar(20) NOT NULL,
  `pwd` varchar(32) NOT NULL,  
  `point` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '提成余额',
  `income` decimal(12,2) NOT NULL DEFAULT '0.00'  COMMENT '供货收入',
  `pay_type` int(1) NOT NULL DEFAULT '0' COMMENT '提现支付方式',
  `pay_account` varchar(50) DEFAULT NULL COMMENT '提现账户',
  `pay_name` varchar(50) DEFAULT NULL COMMENT '提现收款名',
  `qq` varchar(12) DEFAULT NULL,
  `sitename` varchar(80) DEFAULT NULL,
  `title` varchar(80) DEFAULT NULL,
  `keywords` text NULL,
  `description` text NULL,
  `addtime` datetime DEFAULT NULL,
  `price` longtext NULL,
  `skimg` text NULL,
  `logo` text NULL,
  `email` varchar(200) DEFAULT ''  COMMENT '邮箱',
  `tel` varchar(11) DEFAULT NULL   COMMENT '手机号',
  `wechat` varchar(11) DEFAULT NULL   COMMENT '微信',
  `utype` int(1) NOT NULL DEFAULT '0',
  `lasttime` datetime DEFAULT NULL,
  `loginIp` varchar(255) DEFAULT NULL,
  `endtime` datetime DEFAULT NULL  COMMENT '到期时间',
  `msgread` varchar(255) DEFAULT NULL  COMMENT '未读',
  `status` tinyint(2) NOT NULL DEFAULT '0'  COMMENT '状态',
  `is_priced` tinyint(2) NOT NULL DEFAULT '0' COMMENT '价格同步主站',
  `master_open` tinyint(2) NOT NULL DEFAULT '0'  COMMENT '供货权限',
  `master_price` decimal(12,2) NOT NULL DEFAULT '0'  COMMENT '供货押金',
  `closebz` text NULL,
  `iprice` text NULL,
  `createtime` bigint(10) NOT NULL DEFAULT '0'  COMMENT '创建时间',
  `updatetime` bigint(10) NOT NULL DEFAULT '0'  COMMENT '更新时间',
  PRIMARY KEY (`zid`),
  index `user` (`user`),
  index `qq` (`qq`),
  index `status` (`status`),
  index `master_price` (`master_price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000;

-- ----------------------------
-- pre_master_tixian 供货商提现管理
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cmy_master_tixian` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL,
  `money` decimal(10,2) NOT NULL DEFAULT '0.00',
  `realmoney` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pay_type` int(2) NOT NULL DEFAULT '0',
  `pay_account` varchar(50) NOT NULL,
  `pay_name` varchar(50) NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `addtime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  `note` text NULL COMMENT '提现说明',
  `batch` varchar(20) DEFAULT '', 
  PRIMARY KEY (`id`),
  index id (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='供货商提现管理';

-- ----------------------------
-- pre_master_points 供货商收支明细
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cmy_master_points` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='供货商收支明细';

-- ----------------------------
-- pre_ems 系统邮件日志
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cmy_ems` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `device` varchar(64) DEFAULT 'smsbao' COMMENT '驱动插件',
  `event` varchar(30) DEFAULT '' COMMENT '事件',
  `email` varchar(100) DEFAULT '' COMMENT '邮箱',
  `code` varchar(10) DEFAULT '' COMMENT '验证码',
  `content` TEXT NULL DEFAULT NULL COMMENT '邮件内容',
  `times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '验证次数',
  `result` text COMMENT '发送结果',
  `expire` int(10) unsigned NOT NULL DEFAULT '180' COMMENT '过期时间',
  `ip` varchar(30) DEFAULT '' COMMENT 'IP',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `event` (`event`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='邮件日志';

-- ----------------------------
-- pre_ems_tpl 系统邮件模板
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cmy_ems_tpl` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(128) DEFAULT 'smsbao' COMMENT '模板名称',
  `event` varchar(128) DEFAULT '' COMMENT '事件名',
  `subject` varchar(500) COMMENT '邮件标题',
  `body` text COMMENT '邮件内容',
  `remark` varchar(400) COMMENT '备注', 
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '启用状态 1启用0禁用',
  `createtime` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  INDEX `event` (`event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统邮件模板';

INSERT INTO `cmy_ems_tpl` (`id`, `name`, `event`, `subject`, `body`, `remark`, `status`, `createtime`) VALUES
(1, '验证码', 'send_code', '您有新的验证码 - {site_sitename}', '<meta charset="UTF-8">  <meta http-equiv="X-UA-Compatible" content="IE=edge">  <meta name="viewport" content="width=device-width, initial-scale=1.0">  <title>Document</title>  <style>    li{list-style: none;}    a{text-decoration: none;}    body{margin: 0;}    .box{      background-color: #EBEBEB;      height: 100%;    }    .logo_top {padding: 20px 0;}    .logo_top img{      display: block;      width: auto;      margin: 0 auto;    }    .card{      width: 650px;      margin: 0 auto;      background-color: white;      font-size: 0.8rem;      line-height: 22px;      padding: 40px 50px;      box-sizing: border-box;    }    .contimg{      text-align: center;    }    button{      background-color: #F75697;      padding: 8px 16px;      border-radius: 6px;      outline: none;      color: white;      border: 0;    }    .lvst{      color: #57AC80;    }    .banquan{      display: flex;      justify-content: center;      flex-wrap: nowrap;      color: #B7B8B9;      font-size: 0.4rem;      padding: 20px 0;      margin: 0;      padding-left: 0;    }    .banquan li span{      display: inline-block;      padding: 0 8px;    }    @media (max-width: 650px){      .card{        padding: 5% 5%;      }      .logo_top img,.contimg img{width: 280px;}      .box{height: auto;}      .card{width: auto;}    }    @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}}  </style><div class="box"><div class="logo_top"><img src="{site_logo}" alt=""/></div><div class="card"><h1 style="text-align: center;">你有新的验证码<br/></h1><br/><strong>尊敬的用户</strong><span style="background-color: rgb(235, 235, 235); font-size: 14px;">{user_username}</span></div><div class="card"><span style="font-size: 1.35rem;">您的验证码是</span><font size="10">{code}</font><span style="font-size: 1.65rem;">, 5 分钟内有效。如非本人操作请忽略</span></div><div class="card"><span style="font-size: 0.8rem;"> </span><span style="font-size: 0.8rem; display: inline-block; width: 100%; text-align: right;"><span style="font-size: 16px;">{site_sitename}</span><br/></span></div><div class="card"><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;">{time}</span></div><ul class="banquan"><li>{site_company}<br/></li></ul></div>', '', 1, 1688565175),
(2, '找回密码', 'findpwd', '您正在找回密码 - {site_sitename}', '<meta charset="UTF-8">\n<meta http-equiv="X-UA-Compatible" content="IE=edge">\n<meta name="viewport" content="width=device-width, initial-scale=1.0">\n<title>Document</title>\n<style>\n    li {\n        list-style: none;\n    }\n\n    a {\n        text-decoration: none;\n    }\n\n    html,\n    body {\n        margin: 0 !important;\n        font-size: 16px !important;\n    }\n\n    .box {\n        background-color: #f1f1f1;\n        height: 100%;\n        padding-bottom: 135px;\n    }\n\n    .logo_top {\n        padding: 20px 0;\n    }\n\n    .logo_top img {\n        display: block;\n        width: auto;\n        margin: 0 auto;\n    }\n\n    .card {\n        width: 650px;\n        margin: 0 auto;\n        background-color: white;\n        font-size: 0.8rem;\n        line-height: 22px;\n        padding: 40px 50px;\n        box-sizing: border-box;\n    }\n\n    .card-footer {\n        display: flex;\n        justify-content: space-between;\n    }\n\n    .card-footer span.left {\n        font-size: 1.05rem;\n        display: inline-block;\n        width: 100%;\n        text-align: left;\n    }\n\n    .card-footer span.right {\n        font-size: 1.05rem;\n        display: inline-block;\n        width: 100%;\n        text-align: right;\n    }\n\n    .contimg {\n        text-align: center;\n    }\n\n    button {\n        background-color: #F75697;\n        padding: 8px 16px;\n        border-radius: 6px;\n        outline: none;\n        color: white;\n        border: 0;\n    }\n\n    .lvst {\n        color: #57AC80;\n    }\n\n    .banquan {\n        display: flex;\n        justify-content: center;\n        flex-wrap: nowrap;\n        color: #B7B8B9;\n        font-size: 0.75rem;\n        padding: 20px 0;\n        margin: 0;\n        padding-left: 0;\n    }\n\n    .banquan li span {\n        display: inline-block;\n        padding: 0 8px;\n    }\n\n    @media (max-width: 650px) {\n        .card {\n            padding: 5% 5%;\n        }\n\n        .logo_top img,\n        .contimg img {\n            width: 280px;\n        }\n\n        .box {\n            height: auto;\n        }\n\n        .card {\n            width: auto;\n        }\n    }\n\n    @media (max-width: 280px) {\n\n        .logo_top img,\n        .contimg img {\n            width: 100%;\n        }\n    }\n</style>\n<div class="box">\n    <div class="logo_top"><img src="{site_logo}" alt=""/></div>\n    <div class="card">\n        <h1 style="text-align: center;">你有新的验证码<br/></h1></div><div class="card"><span style="font-size: 1.65rem;">您正在找回密码, 您的验证码是</span><span style="font-size: 0.8rem;">\n        </span><font size="10">{code}</font><span style="font-size: 1.65rem;">, 5 分钟内有效。如非本人操作请忽略</span><br/></div>\n    <div class="card card-footer">\n        <span class="left">{site_sitename}</span>\n        <span class="right">{time}</span>\n    </div>\n    <ul class="banquan">\n        <li>{site_company}<br/></li>\n    </ul>\n</div>', '', 1, 1688630239),
(3, '注册账号', 'register', '您正在注册账号-{site_sitename}', '<meta charset="UTF-8">\n<meta http-equiv="X-UA-Compatible" content="IE=edge">\n<meta name="viewport" content="width=device-width, initial-scale=1.0">\n<title>Document</title>\n<style>\n    li {\n        list-style: none;\n    }\n\n    a {\n        text-decoration: none;\n    }\n\n    html,\n    body {\n        margin: 0 !important;\n        font-size: 16px !important;\n    }\n\n    .box {\n        background-color: #f1f1f1;\n        height: 100%;\n        padding-bottom: 135px;\n    }\n\n    .logo_top {\n        padding: 20px 0;\n    }\n\n    .logo_top img {\n        display: block;\n        width: auto;\n        margin: 0 auto;\n    }\n\n    .card {\n        width: 650px;\n        margin: 0 auto;\n        background-color: white;\n        font-size: 0.8rem;\n        line-height: 22px;\n        padding: 40px 50px;\n        box-sizing: border-box;\n    }\n\n    .card-footer {\n        display: flex;\n        justify-content: space-between;\n    }\n\n    .card-footer span.left {\n        font-size: 1.05rem;\n        display: inline-block;\n        width: 100%;\n        text-align: left;\n    }\n\n    .card-footer span.right {\n        font-size: 1.05rem;\n        display: inline-block;\n        width: 100%;\n        text-align: right;\n    }\n\n    .contimg {\n        text-align: center;\n    }\n\n    button {\n        background-color: #F75697;\n        padding: 8px 16px;\n        border-radius: 6px;\n        outline: none;\n        color: white;\n        border: 0;\n    }\n\n    .lvst {\n        color: #57AC80;\n    }\n\n    .banquan {\n        display: flex;\n        justify-content: center;\n        flex-wrap: nowrap;\n        color: #B7B8B9;\n        font-size: 0.75rem;\n        padding: 20px 0;\n        margin: 0;\n        padding-left: 0;\n    }\n\n    .banquan li span {\n        display: inline-block;\n        padding: 0 8px;\n    }\n\n    @media (max-width: 650px) {\n        .card {\n            padding: 5% 5%;\n        }\n\n        .logo_top img,\n        .contimg img {\n            width: 280px;\n        }\n\n        .box {\n            height: auto;\n        }\n\n        .card {\n            width: auto;\n        }\n    }\n\n    @media (max-width: 280px) {\n\n        .logo_top img,\n        .contimg img {\n            width: 100%;\n        }\n    }\n</style>\n<div class="box">\n    <div class="logo_top"><img src="{site_logo}" alt=""/></div>\n    <div class="card">\n        <h1 style="text-align: center;">你有新的验证码<br/></h1><br/></div>\n    <div class="card"><span style="font-size: 1.35rem;">您的验证码是</span>\n        <font size="10">{code}</font><span style="font-size: 1.65rem;">, 5 分钟内有效。如非本人操作请忽略</span>\n    </div>\n    <div class="card card-footer">\n        <span class="left">{site_sitename}</span>\n        <span class="right">{time}</span>\n    </div>\n    <ul class="banquan">\n        <li>{site_company}<br/></li>\n    </ul>\n</div>', NULL, 1, 1688641579),
(4, '登录账号', 'login', '您正在登录账号-{site_sitename}', '<meta charset="UTF-8">\n<meta http-equiv="X-UA-Compatible" content="IE=edge">\n<meta name="viewport" content="width=device-width, initial-scale=1.0">\n<title>Document</title>\n<style>\n    li {\n        list-style: none;\n    }\n\n    a {\n        text-decoration: none;\n    }\n\n    html,\n    body {\n        margin: 0 !important;\n        font-size: 16px !important;\n    }\n\n    .box {\n        background-color: #f1f1f1;\n        height: 100%;\n        padding-bottom: 135px;\n    }\n\n    .logo_top {\n        padding: 20px 0;\n    }\n\n    .logo_top img {\n        display: block;\n        width: auto;\n        margin: 0 auto;\n    }\n\n    .card {\n        width: 650px;\n        margin: 0 auto;\n        background-color: white;\n        font-size: 0.8rem;\n        line-height: 22px;\n        padding: 40px 50px;\n        box-sizing: border-box;\n    }\n\n    .card-footer {\n        display: flex;\n        justify-content: space-between;\n    }\n\n    .card-footer span.left {\n        font-size: 1.05rem;\n        display: inline-block;\n        width: 100%;\n        text-align: left;\n    }\n\n    .card-footer span.right {\n        font-size: 1.05rem;\n        display: inline-block;\n        width: 100%;\n        text-align: right;\n    }\n\n    .contimg {\n        text-align: center;\n    }\n\n    button {\n        background-color: #F75697;\n        padding: 8px 16px;\n        border-radius: 6px;\n        outline: none;\n        color: white;\n        border: 0;\n    }\n\n    .lvst {\n        color: #57AC80;\n    }\n\n    .banquan {\n        display: flex;\n        justify-content: center;\n        flex-wrap: nowrap;\n        color: #B7B8B9;\n        font-size: 0.75rem;\n        padding: 20px 0;\n        margin: 0;\n        padding-left: 0;\n    }\n\n    .banquan li span {\n        display: inline-block;\n        padding: 0 8px;\n    }\n\n    @media (max-width: 650px) {\n        .card {\n            padding: 5% 5%;\n        }\n\n        .logo_top img,\n        .contimg img {\n            width: 280px;\n        }\n\n        .box {\n            height: auto;\n        }\n\n        .card {\n            width: auto;\n        }\n    }\n\n    @media (max-width: 280px) {\n\n        .logo_top img,\n        .contimg img {\n            width: 100%;\n        }\n    }\n</style>\n<div class="box">\n    <div class="logo_top"><img src="{site_logo}" alt=""/></div>\n    <div class="card">\n        <h1 style="text-align: center;">你有新的验证码<br/></h1><br/></div>\n    <div class="card"><span style="font-size: 1.65rem;">您正在登录账号, 您的验证码是</span>\n        <font size="10">{code}</font><span style="font-size: 1.65rem;">, 5 分钟内有效。如非本人操作请忽略</span>\n    </div>\n    <div class="card card-footer">\n        <span class="left">{site_sitename}</span>\n        <span class="right">{time}</span>\n    </div>\n    <ul class="banquan">\n        <li>{site_company}<br/></li>\n    </ul>\n</div>', NULL, 1, 1688641618),
(5, '修改手机号', 'change_mobile', '您正在修改手机号-{site_sitename}', '<meta charset="UTF-8">\n<meta http-equiv="X-UA-Compatible" content="IE=edge">\n<meta name="viewport" content="width=device-width, initial-scale=1.0">\n<title>Document</title>\n<style>\n    li {\n        list-style: none;\n    }\n\n    a {\n        text-decoration: none;\n    }\n\n    html,\n    body {\n        margin: 0 !important;\n        font-size: 16px !important;\n    }\n\n    .box {\n        background-color: #f1f1f1;\n        height: 100%;\n        padding-bottom: 135px;\n    }\n\n    .logo_top {\n        padding: 20px 0;\n    }\n\n    .logo_top img {\n        display: block;\n        width: auto;\n        margin: 0 auto;\n    }\n\n    .card {\n        width: 650px;\n        margin: 0 auto;\n        background-color: white;\n        font-size: 0.8rem;\n        line-height: 22px;\n        padding: 40px 50px;\n        box-sizing: border-box;\n    }\n\n    .card-footer {\n        display: flex;\n        justify-content: space-between;\n    }\n\n    .card-footer span.left {\n        font-size: 1.05rem;\n        display: inline-block;\n        width: 100%;\n        text-align: left;\n    }\n\n    .card-footer span.right {\n        font-size: 1.05rem;\n        display: inline-block;\n        width: 100%;\n        text-align: right;\n    }\n\n    .contimg {\n        text-align: center;\n    }\n\n    button {\n        background-color: #F75697;\n        padding: 8px 16px;\n        border-radius: 6px;\n        outline: none;\n        color: white;\n        border: 0;\n    }\n\n    .lvst {\n        color: #57AC80;\n    }\n\n    .banquan {\n        display: flex;\n        justify-content: center;\n        flex-wrap: nowrap;\n        color: #B7B8B9;\n        font-size: 0.75rem;\n        padding: 20px 0;\n        margin: 0;\n        padding-left: 0;\n    }\n\n    .banquan li span {\n        display: inline-block;\n        padding: 0 8px;\n    }\n\n    @media (max-width: 650px) {\n        .card {\n            padding: 5% 5%;\n        }\n\n        .logo_top img,\n        .contimg img {\n            width: 280px;\n        }\n\n        .box {\n            height: auto;\n        }\n\n        .card {\n            width: auto;\n        }\n    }\n\n    @media (max-width: 280px) {\n\n        .logo_top img,\n        .contimg img {\n            width: 100%;\n        }\n    }\n</style>\n<div class="box">\n    <div class="logo_top"><img src="{site_logo}" alt=""/></div>\n    <div class="card">\n        <h1 style="text-align: center;">你有新的验证码<br/></h1><br/></div>\n    <div class="card"><span style="font-size: 1.65rem;">您正在修改手机号, 您的验证码是</span>\n        <font size="10">{code}</font><span style="font-size: 1.65rem;">, 5 分钟内有效。如非本人操作请忽略</span>\n    </div>\n    <div class="card card-footer">\n        <span class="left">{site_sitename}</span>\n        <span class="right">{time}</span>\n    </div>\n    <ul class="banquan">\n        <li>{site_company}<br/></li>\n    </ul>\n</div>', NULL, 1, 1688641638),
(6, '修改邮箱', 'change_email', '您正在修改邮箱-{site_sitename}', '<meta charset="UTF-8">\n<meta http-equiv="X-UA-Compatible" content="IE=edge">\n<meta name="viewport" content="width=device-width, initial-scale=1.0">\n<title>Document</title>\n<style>\n    li {\n        list-style: none;\n    }\n\n    a {\n        text-decoration: none;\n    }\n\n    html,\n    body {\n        margin: 0 !important;\n        font-size: 16px !important;\n    }\n\n    .box {\n        background-color: #f1f1f1;\n        height: 100%;\n        padding-bottom: 135px;\n    }\n\n    .logo_top {\n        padding: 20px 0;\n    }\n\n    .logo_top img {\n        display: block;\n        width: auto;\n        margin: 0 auto;\n    }\n\n    .card {\n        width: 650px;\n        margin: 0 auto;\n        background-color: white;\n        font-size: 0.8rem;\n        line-height: 22px;\n        padding: 40px 50px;\n        box-sizing: border-box;\n    }\n\n    .card-footer {\n        display: flex;\n        justify-content: space-between;\n    }\n\n    .card-footer span.left {\n        font-size: 1.05rem;\n        display: inline-block;\n        width: 100%;\n        text-align: left;\n    }\n\n    .card-footer span.right {\n        font-size: 1.05rem;\n        display: inline-block;\n        width: 100%;\n        text-align: right;\n    }\n\n    .contimg {\n        text-align: center;\n    }\n\n    button {\n        background-color: #F75697;\n        padding: 8px 16px;\n        border-radius: 6px;\n        outline: none;\n        color: white;\n        border: 0;\n    }\n\n    .lvst {\n        color: #57AC80;\n    }\n\n    .banquan {\n        display: flex;\n        justify-content: center;\n        flex-wrap: nowrap;\n        color: #B7B8B9;\n        font-size: 0.75rem;\n        padding: 20px 0;\n        margin: 0;\n        padding-left: 0;\n    }\n\n    .banquan li span {\n        display: inline-block;\n        padding: 0 8px;\n    }\n\n    @media (max-width: 650px) {\n        .card {\n            padding: 5% 5%;\n        }\n\n        .logo_top img,\n        .contimg img {\n            width: 280px;\n        }\n\n        .box {\n            height: auto;\n        }\n\n        .card {\n            width: auto;\n        }\n    }\n\n    @media (max-width: 280px) {\n\n        .logo_top img,\n        .contimg img {\n            width: 100%;\n        }\n    }\n</style>\n<div class="box">\n    <div class="logo_top"><img src="{site_logo}" alt=""/></div>\n    <div class="card">\n        <h1 style="text-align: center;">你有新的验证码<br/></h1><br/></div>\n    <div class="card"><span style="font-size: 1.65rem;">您正在修改邮箱, 您的验证码是</span>\n        <font size="10">{code}</font><span style="font-size: 1.65rem;">, 5 分钟内有效。如非本人操作请忽略</span>\n    </div>\n    <div class="card card-footer">\n        <span class="left">{site_sitename}</span>\n        <span class="right">{time}</span>\n    </div>\n    <ul class="banquan">\n        <li>{site_company}<br/></li>\n    </ul>\n</div>', NULL, 1, 1688641663),
(7, '下单成功', 'buyorder', '安全提醒！您已下单成功-{site_sitename}', '<meta charset="UTF-8">\n<meta http-equiv="X-UA-Compatible" content="IE=edge">\n<meta name="viewport" content="width=device-width, initial-scale=1.0">\n<title>Document</title>\n<style>\n    li {\n        list-style: none;\n    }\n\n    a {\n        text-decoration: none;\n    }\n\n    html,\n    body {\n        margin: 0 !important;\n        font-size: 16px !important;\n    }\n\n    .box {\n        background-color: #f1f1f1;\n        height: 100%;\n        padding-bottom: 135px;\n    }\n\n    .logo_top {\n        padding: 20px 0;\n    }\n\n    .logo_top img {\n        display: block;\n        width: auto;\n        margin: 0 auto;\n    }\n\n    .card {\n        width: 650px;\n        margin: 0 auto;\n        background-color: white;\n        font-size: 0.8rem;\n        line-height: 22px;\n        padding: 40px 50px;\n        box-sizing: border-box;\n    }\n\n    .card-footer {\n        display: flex;\n        justify-content: space-between;\n    }\n\n    .card-footer span.left {\n        font-size: 1.05rem;\n        display: inline-block;\n        width: 100%;\n        text-align: left;\n    }\n\n    .card-footer span.right {\n        font-size: 1.05rem;\n        display: inline-block;\n        width: 100%;\n        text-align: right;\n    }\n\n    .contimg {\n        text-align: center;\n    }\n\n    button {\n        background-color: #F75697;\n        padding: 8px 16px;\n        border-radius: 6px;\n        outline: none;\n        color: white;\n        border: 0;\n    }\n\n    .lvst {\n        color: #57AC80;\n    }\n\n    .banquan {\n        display: flex;\n        justify-content: center;\n        flex-wrap: nowrap;\n        color: #B7B8B9;\n        font-size: 0.75rem;\n        padding: 20px 0;\n        margin: 0;\n        padding-left: 0;\n    }\n\n    .banquan li span {\n        display: inline-block;\n        padding: 0 8px;\n    }\n\n    @media (max-width: 650px) {\n        .card {\n            padding: 5% 5%;\n        }\n\n        .logo_top img,\n        .contimg img {\n            width: 280px;\n        }\n\n        .box {\n            height: auto;\n        }\n\n        .card {\n            width: auto;\n        }\n    }\n\n    @media (max-width: 280px) {\n\n        .logo_top img,\n        .contimg img {\n            width: 100%;\n        }\n    }\n</style>\n<div class="box">\n    <div class="logo_top"><img src="{site_logo}" alt=""/></div>\n    <div class="card">\n        <h2 style="text-align: center;">下单成功通知</h2><br/></div>\n    <div class="card"><span style="font-size: 0.8rem;">您购买的商品</span><span style="background-color: rgb(241, 241, 241); font-size: 14px;">{order_name}</span><span style="font-size: 0.8rem;">已下单成功, 将尽快为你处理订单, 请耐心等待~</span></div>\n    <div class="card card-footer">\n        <span class="left">{site_sitename}</span>\n        <span class="right">{time}</span>\n    </div>\n    <ul class="banquan">\n        <li>{site_company}<br/></li>\n    </ul>\n</div>', NULL, 1, 1688641740),
(8, '修改密码', 'change_pwd', '您正在修改密码-{site_sitename}', '<meta charset="UTF-8">\n<meta http-equiv="X-UA-Compatible" content="IE=edge">\n<meta name="viewport" content="width=device-width, initial-scale=1.0">\n<title>Document</title>\n<style>\n    li {\n        list-style: none;\n    }\n\n    a {\n        text-decoration: none;\n    }\n\n    html,\n    body {\n        margin: 0 !important;\n        font-size: 16px !important;\n    }\n\n    .box {\n        background-color: #f1f1f1;\n        height: 100%;\n        padding-bottom: 135px;\n    }\n\n    .logo_top {\n        padding: 20px 0;\n    }\n\n    .logo_top img {\n        display: block;\n        width: auto;\n        margin: 0 auto;\n    }\n\n    .card {\n        width: 650px;\n        margin: 0 auto;\n        background-color: white;\n        font-size: 0.8rem;\n        line-height: 22px;\n        padding: 40px 50px;\n        box-sizing: border-box;\n    }\n\n    .card-footer {\n        display: flex;\n        justify-content: space-between;\n    }\n\n    .card-footer span.left {\n        font-size: 1.05rem;\n        display: inline-block;\n        width: 100%;\n        text-align: left;\n    }\n\n    .card-footer span.right {\n        font-size: 1.05rem;\n        display: inline-block;\n        width: 100%;\n        text-align: right;\n    }\n\n    .contimg {\n        text-align: center;\n    }\n\n    button {\n        background-color: #F75697;\n        padding: 8px 16px;\n        border-radius: 6px;\n        outline: none;\n        color: white;\n        border: 0;\n    }\n\n    .lvst {\n        color: #57AC80;\n    }\n\n    .banquan {\n        display: flex;\n        justify-content: center;\n        flex-wrap: nowrap;\n        color: #B7B8B9;\n        font-size: 0.75rem;\n        padding: 20px 0;\n        margin: 0;\n        padding-left: 0;\n    }\n\n    .banquan li span {\n        display: inline-block;\n        padding: 0 8px;\n    }\n\n    @media (max-width: 650px) {\n        .card {\n            padding: 5% 5%;\n        }\n\n        .logo_top img,\n        .contimg img {\n            width: 280px;\n        }\n\n        .box {\n            height: auto;\n        }\n\n        .card {\n            width: auto;\n        }\n    }\n\n    @media (max-width: 280px) {\n\n        .logo_top img,\n        .contimg img {\n            width: 100%;\n        }\n    }\n</style>\n<div class="box">\n    <div class="logo_top"><img src="{site_logo}" alt=""/></div>\n    <div class="card">\n        <h1 style="text-align: center;">你有新的验证码<br/></h1><br/></div>\n    <div class="card"><span style="font-size: 1.65rem;">您正在修改密码, 您的验证码是</span>\n        <font size="10">{code}</font><span style="font-size: 1.65rem;">, 5 分钟内有效。如非本人操作请忽略</span>\n    </div>\n    <div class="card card-footer">\n        <span class="left">{site_sitename}</span>\n        <span class="right">{time}</span>\n    </div>\n    <ul class="banquan">\n        <li>{site_company}<br/></li>\n    </ul>\n</div>', NULL, 1, 1688641876),
(9, '修改资料', 'change_info', '您正在修改资料-{site_sitename}', '<meta charset="UTF-8">\n<meta http-equiv="X-UA-Compatible" content="IE=edge">\n<meta name="viewport" content="width=device-width, initial-scale=1.0">\n<title>Document</title>\n<style>\n    li {\n        list-style: none;\n    }\n\n    a {\n        text-decoration: none;\n    }\n\n    html,\n    body {\n        margin: 0 !important;\n        font-size: 16px !important;\n    }\n\n    .box {\n        background-color: #f1f1f1;\n        height: 100%;\n        padding-bottom: 135px;\n    }\n\n    .logo_top {\n        padding: 20px 0;\n    }\n\n    .logo_top img {\n        display: block;\n        width: auto;\n        margin: 0 auto;\n    }\n\n    .card {\n        width: 650px;\n        margin: 0 auto;\n        background-color: white;\n        font-size: 0.8rem;\n        line-height: 22px;\n        padding: 40px 50px;\n        box-sizing: border-box;\n    }\n\n    .card-footer {\n        display: flex;\n        justify-content: space-between;\n    }\n\n    .card-footer span.left {\n        font-size: 1.05rem;\n        display: inline-block;\n        width: 100%;\n        text-align: left;\n    }\n\n    .card-footer span.right {\n        font-size: 1.05rem;\n        display: inline-block;\n        width: 100%;\n        text-align: right;\n    }\n\n    .contimg {\n        text-align: center;\n    }\n\n    button {\n        background-color: #F75697;\n        padding: 8px 16px;\n        border-radius: 6px;\n        outline: none;\n        color: white;\n        border: 0;\n    }\n\n    .lvst {\n        color: #57AC80;\n    }\n\n    .banquan {\n        display: flex;\n        justify-content: center;\n        flex-wrap: nowrap;\n        color: #B7B8B9;\n        font-size: 0.75rem;\n        padding: 20px 0;\n        margin: 0;\n        padding-left: 0;\n    }\n\n    .banquan li span {\n        display: inline-block;\n        padding: 0 8px;\n    }\n\n    @media (max-width: 650px) {\n        .card {\n            padding: 5% 5%;\n        }\n\n        .logo_top img,\n        .contimg img {\n            width: 280px;\n        }\n\n        .box {\n            height: auto;\n        }\n\n        .card {\n            width: auto;\n        }\n    }\n\n    @media (max-width: 280px) {\n\n        .logo_top img,\n        .contimg img {\n            width: 100%;\n        }\n    }\n</style>\n<div class="box">\n    <div class="logo_top"><img src="{site_logo}" alt=""/></div>\n    <div class="card">\n        <h1 style="text-align: center;">你有新的验证码<br/></h1><br/></div>\n    <div class="card"><span style="font-size: 1.65rem;">您正在修改资料, 您的验证码是</span>\n        <font size="10">{code}</font><span style="font-size: 1.65rem;">, 5 分钟内有效。如非本人操作请忽略</span>\n    </div>\n    <div class="card card-footer">\n        <span class="left">{site_sitename}</span>\n        <span class="right">{time}</span>\n    </div>\n    <ul class="banquan">\n        <li>{site_company}<br/></li>\n    </ul>\n</div>', NULL, 1, 1688641876),
(10, '安全验证', 'safecheck', '您正在进行重要操作-{site_sitename}', '<meta charset="UTF-8">\n<meta http-equiv="X-UA-Compatible" content="IE=edge">\n<meta name="viewport" content="width=device-width, initial-scale=1.0">\n<title>Document</title>\n<style>\n    li {\n        list-style: none;\n    }\n\n    a {\n        text-decoration: none;\n    }\n\n    html,\n    body {\n        margin: 0 !important;\n        font-size: 16px !important;\n    }\n\n    .box {\n        background-color: #f1f1f1;\n        height: 100%;\n        padding-bottom: 135px;\n    }\n\n    .logo_top {\n        padding: 20px 0;\n    }\n\n    .logo_top img {\n        display: block;\n        width: auto;\n        margin: 0 auto;\n    }\n\n    .card {\n        width: 650px;\n        margin: 0 auto;\n        background-color: white;\n        font-size: 0.8rem;\n        line-height: 22px;\n        padding: 40px 50px;\n        box-sizing: border-box;\n    }\n\n    .card-footer {\n        display: flex;\n        justify-content: space-between;\n    }\n\n    .card-footer span.left {\n        font-size: 1.05rem;\n        display: inline-block;\n        width: 100%;\n        text-align: left;\n    }\n\n    .card-footer span.right {\n        font-size: 1.05rem;\n        display: inline-block;\n        width: 100%;\n        text-align: right;\n    }\n\n    .contimg {\n        text-align: center;\n    }\n\n    button {\n        background-color: #F75697;\n        padding: 8px 16px;\n        border-radius: 6px;\n        outline: none;\n        color: white;\n        border: 0;\n    }\n\n    .lvst {\n        color: #57AC80;\n    }\n\n    .banquan {\n        display: flex;\n        justify-content: center;\n        flex-wrap: nowrap;\n        color: #B7B8B9;\n        font-size: 0.75rem;\n        padding: 20px 0;\n        margin: 0;\n        padding-left: 0;\n    }\n\n    .banquan li span {\n        display: inline-block;\n        padding: 0 8px;\n    }\n\n    @media (max-width: 650px) {\n        .card {\n            padding: 5% 5%;\n        }\n\n        .logo_top img,\n        .contimg img {\n            width: 280px;\n        }\n\n        .box {\n            height: auto;\n        }\n\n        .card {\n            width: auto;\n        }\n    }\n\n    @media (max-width: 280px) {\n\n        .logo_top img,\n        .contimg img {\n            width: 100%;\n        }\n    }\n</style>\n<div class="box">\n    <div class="logo_top"><img src="{site_logo}" alt=""/></div>\n    <div class="card">\n        <h1 style="text-align: center;">你有新的验证码<br/></h1><br/></div>\n    <div class="card"><span style="font-size: 1.65rem;">您正在进行重要操作, 您的验证码是</span>\n        <font size="10">{code}</font><span style="font-size: 1.65rem;">, 5 分钟内有效。如非本人操作请忽略</span>\n    </div>\n    <div class="card card-footer">\n        <span class="left">{site_sitename}</span>\n        <span class="right">{time}</span>\n    </div>\n    <ul class="banquan">\n        <li>{site_company}<br/></li>\n    </ul>\n</div>', NULL, 1, 1688641876);

-- ----------------------------
-- pre_sms 系统短信日志
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cmy_sms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `device` varchar(64) DEFAULT 'smsbao' COMMENT '驱动插件',
  `event` varchar(30) DEFAULT '' COMMENT '事件',
  `mobile` varchar(20) DEFAULT '' COMMENT '手机号',
  `content` text COMMENT '发送内容',
  `code` varchar(10) DEFAULT '' COMMENT '验证码',
  `times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '验证次数',
  `result` text COMMENT '发送结果',
  `ip` varchar(30) DEFAULT '' COMMENT 'IP',
  `createtime` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `event` (`event`),
  KEY `mobile` (`mobile`),
  KEY `code` (`code`),
  KEY `times` (`times`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='短信日志';

-- ----------------------------
-- pre_sms_tpl 系统短信模板
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cmy_sms_tpl` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `device` varchar(64) DEFAULT 'smsbao' COMMENT '驱动插件',
  `device_id` int(11) unsigned NOT NULL COMMENT '插件ID',
  `template_id` varchar(64) NULL DEFAULT NULL COMMENT '模板ID',
  `name` varchar(128) DEFAULT '默认模板' COMMENT '模板名称',
  `event` varchar(128) DEFAULT '' COMMENT '事件名',
  `type` varchar(32) NULL DEFAULT 'cn' COMMENT '类型 cn国内global国际',
  `content` text COMMENT '发送模板',
  `remark` varchar(400) COMMENT '发送模板', 
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '启用状态 1启用0禁用',
  `condition` tinyint(2) NOT NULL DEFAULT '0' COMMENT '审核状态 1通过0待提交2失败3审核中', 
  `fail_msg` text NULL DEFAULT NULL COMMENT '审核失败原因',
  `createtime` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  INDEX `device` (`device`),
  INDEX `event` (`event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统短信模板';

-- ----------------------------
-- pre_tools_message 商品动态文章
-- ----------------------------
create table IF NOT EXISTS `cmy_tools_message`(
  `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NULL DEFAULT '',
  `content` longtext DEFAULT NULL COMMENT '日志详情',
  `time` date DEFAULT NULL COMMENT '日期', 
  `addtime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '商品动态文章';

INSERT INTO `cmy_menu` VALUES (120,  0, '商品动态', './toolsmessage.php', 'toolsmessage', 'fa fa-bell', '', 55, '1', 0, '', 0, 0, 0, 1636908915, 1636908915);

ALTER TABLE `cmy_site` 
    ADD `kfwx` int(11) NOT NULL DEFAULT '0' COMMENT '客服链接' AFTER `qq`;

ALTER TABLE `cmy_site` 
    ADD `musicurl` int(11) NOT NULL DEFAULT '0' COMMENT '站点音乐链接' AFTER `kfwx`;

ALTER TABLE `cmy_pay` 
    ADD `channel_id` int(11) unsigned DEFAULT '0' COMMENT '支付接口ID' AFTER `status`;