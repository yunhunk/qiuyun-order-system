
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