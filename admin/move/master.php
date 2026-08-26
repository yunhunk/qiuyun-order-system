<?php

include '../../includes/common.php';
$title = '站点数据迁移小工具V1.1.1';
checkLogin();

$dbqz   = isset($_GET['dbqz']) && $_GET['dbqz'] ? trim($_GET['dbqz']) : 'shua';
$offset = isset($_GET['offset']) && $_GET['offset'] ? intval($_GET['offset']) : 0;
$limit  = isset($_GET['limit']) && $_GET['limit'] ? intval($_GET['limit']) : 1000;
$reset  = isset($_GET['reset']) && $_GET['reset'] ? intval($_GET['reset']) : 0;
if ($offset == 0 && $reset == 1) {
    $resetsql = "DROP TABLE IF EXISTS `pre_master`;
create table IF NOT EXISTS `pre_master` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000;";
    $DB->exec($resetsql);
}

$DB->setDbqzList();

$count = intval($DB->count("SELECT count(*) FROM `{$dbqz}_supplier`"));
$ok    = 0;
if ($count > 0) {

    $rs = $DB->select("SELECT `sid`,`addtime`,`lasttime`,user,pwd,email,phone,rmb,qq,pay_type,pay_account,pay_name,qq,wx,`status` FROM `{$dbqz}_supplier` where 1 limit {$offset},{$limit}");
    if ($rs) {
        foreach ($rs as $key => $value) {
            $value['zid'] = $value['sid'];
            if ($reset == 1) {
                // 转移余额
                $value['income'] = $value['rmb'];
            }
            $value['tel']         = $value['phone'];
            $value['wechat']      = $value['wx'];
            $value['createtime']  = strtotime($value['addtime']);
            $value['lasttime']    = $value['lasttime'];
            $value['endtime']     = date('Y-m-d H:i:s', strtotime('+20 year', time()));
            $value['updatetime']  = time();
            $value['master_open'] = $value['status'] == 1 ? 1 : 0;
            unset($value['sid'], $value['rmb'], $value['phone'], $value['wx']);

            if ($reset == 1) {
                $sql = \core\Db::name('master')->insert($value);
            } else {
                $sql = \core\Db::name('master')->where(['zid' => $value['zid']])->update($value);
            }

            // print_r($value);
            // print_r(\core\Db::getLastSql());
            // die;

            if ($sql !== false) {
                $ok++;
            }
        }
    }
    echo "查询到{$count}数据，成功迁移{$ok}条数据";
} else {
    echo "查询到{$count}数据，无需迁移";
}
?>
<?php if ($count > 0 && $count >= $offset): ?>
<h4>3.5秒后开始迁移第<?php echo ($offset + $limit) ?>到<?php echo ($offset + $limit + $limit) ?>的数据</h4>
<script type="text/javascript">
setTimeout(function () {
    window.location.href='?reset=<?php echo $reset; ?>&dbqz=<?php echo $dbqz; ?>&offset=<?php echo ($offset + $limit); ?>&limit=<?php echo $limit; ?>';
}, 3500);
</script>
<?php else: ?>
数据迁移完成
<?php endif;?>
