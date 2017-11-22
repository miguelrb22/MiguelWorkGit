<?php

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bkp_category` (
    `id` int NOT NULL AUTO_INCREMENT,
    `bkp_key` varchar(255) NOT NULL,
	`bkp_name` varchar(255),
	`id_category` int default null,
	`id_tax_rule` int,
    PRIMARY KEY  (`id`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bkp_feature_category_value` (
    `id_feature_group` int NOT NULL,
	`id_bkp_category` int NOT NULL,
	`value_key` varchar(255),
    PRIMARY KEY  (`id_feature_group`, `id_bkp_category`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bkp_product` (
    `id` int NOT NULL AUTO_INCREMENT,
	`id_bkp_reference` varchar(255) NOT NULL,
	`id_product` int NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';


$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bkp_feature_group` (
    `id` int NOT NULL AUTO_INCREMENT,
	`bkp_key` varchar(255) NOT NULL,
	`id_category` int NULL,
	`type` int NOT NULL,
	`id_feature` int NULL,
    PRIMARY KEY  (`id`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';


foreach ($sql as $query)
    if (Db::getInstance()->execute($query) == false) return false;
