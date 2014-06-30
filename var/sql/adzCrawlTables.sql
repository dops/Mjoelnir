CREATE TABLE IF NOT EXISTS `adzlocal_reporting`.`crawl_order` (
`crawl_order_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`type` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`active` BOOLEAN NOT NULL ,
`valid_from` INT UNSIGNED NOT NULL ,
`valid_to` INT UNSIGNED NOT NULL ,
`interval` ENUM(  'minutely',  'hourly',  'daily',  'weekly',  'monthly',  'yearly' ) NOT NULL ,
`time_insert` INT UNSIGNED NOT NULL ,
`time_update` INT UNSIGNED NULL DEFAULT NULL ,
`time_execution` INT UNSIGNED NULL DEFAULT NULL
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE  `crawl_order` ADD  `execution_state` ENUM(  'open',  'requesting',  'done' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'open' AFTER  `interval` ,
ADD INDEX (  `execution_state` );
ALTER TABLE  `crawl_order` ADD INDEX (  `active` );
ALTER TABLE  `crawl_order` ADD INDEX (  `type` );
ALTER TABLE  `crawl_order` CHANGE  `execution_state`  `execution_state` ENUM(  'open',  'requesting',  'done',  'failed' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'open';

CREATE TABLE IF NOT EXISTS `crawl_order_parameter` (
  `crawl_order_parameter_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `crawl_order_id` int(10) unsigned NOT NULL,
  `key` varchar(30) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`crawl_order_parameter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
ALTER TABLE  `crawl_order_parameter` ADD INDEX (  `crawl_order_id` );
ALTER TABLE  `crawl_order_parameter` ADD INDEX (  `key` );
ALTER TABLE  `crawl_order_parameter` ADD INDEX (  `value` );
ALTER TABLE  `crawl_order_parameter` ADD FOREIGN KEY (  `crawl_order_id` ) REFERENCES  `adzlocal_reporting`.`crawl_order` (
`crawl_order_id`
) ON DELETE CASCADE ON UPDATE CASCADE ;