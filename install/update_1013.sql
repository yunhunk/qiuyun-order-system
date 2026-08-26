ALTER TABLE `pre_pay` 
    ADD `channel_id` int(11) unsigned DEFAULT '0' COMMENT '支付接口ID' AFTER `status`;

ALTER TABLE `pre_master` 
    CHANGE `master_price` `master_price` DECIMAL(12,2) NOT NULL DEFAULT '0.00' COMMENT '供货押金';


    