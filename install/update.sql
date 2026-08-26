ALTER TABLE `pre_tools`
ADD `mesh_list` longtext DEFAULT NULL;

ALTER TABLE `pre_tools`
ADD `is_rank` tinyint(2) NOT NULL DEFAULT '1';

ALTER TABLE `pre_tools`
ADD `check` tinyint(2) NOT NULL DEFAULT '0';

ALTER TABLE `pre_shequ`
ADD `orderstatus` tinyint(2) NOT NULL DEFAULT '1';

ALTER TABLE `pre_site`
ADD `loginIp` varchar(255) NULL DEFAULT NULL;

ALTER TABLE `pre_tixian`
ADD `type` int(2) NOT NULL DEFAULT '1';

ALTER TABLE `pre_class`
ADD `alert` text  DEFAULT NULL;

ALTER TABLE `pre_pay`
ADD `kid` int(11) unsigned NULL DEFAULT '0';

ALTER TABLE `pre_tools`
ADD `cids` varchar(500) DEFAULT NULL;

ALTER TABLE `pre_orders`
ADD `cartorder` varchar(32) DEFAULT NULL;

ALTER TABLE `pre_faka`
ADD `status` int(2) unsigned NOT NULL DEFAULT '0';