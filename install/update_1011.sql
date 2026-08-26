
ALTER TABLE `pre_master` 
    CHANGE `master_price` `master_price` DECIMAL(12,2) NOT NULL DEFAULT '0.00' COMMENT '供货押金';

ALTER TABLE `pre_site` 
    ADD `kfwx` int(11) NOT NULL DEFAULT '0' COMMENT '客服链接' AFTER `qq`;

ALTER TABLE `pre_site` 
    ADD `musicurl` int(11) NOT NULL DEFAULT '0' COMMENT '站点音乐链接' AFTER `kfwx`;

-- ----------------------------
-- pre_sms_tpl 支付多接口通道
-- ----------------------------
CREATE TABLE `pre_channel` (
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
