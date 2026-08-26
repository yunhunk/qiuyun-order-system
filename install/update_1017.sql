ALTER TABLE `cmy_orders` 
    CHANGE `uptime` `uptime` INT(11) NULL DEFAULT '0' COMMENT '订单同步时间';