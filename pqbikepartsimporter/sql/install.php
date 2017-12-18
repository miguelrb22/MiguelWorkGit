<?php

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bkp_category` (
    `id` int NOT NULL AUTO_INCREMENT,
    `bkp_key` varchar(255) NOT NULL,
	`bkp_name` varchar(255) NOT NULL,
	`id_category` int default 0,
	`id_tax_rule` int default 0,
    PRIMARY KEY  (`id`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bkp_feature_value` (
    `id` int NOT NULL AUTO_INCREMENT,
    `id_bkp_feature` int NOT NULL,
	`value_key` varchar(255),
	`value_desc` varchar(255),
    PRIMARY KEY  (`id`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bkp_product` (
    `id` int NOT NULL AUTO_INCREMENT,
	`bkp_reference` varchar(255) NOT NULL,
	`id_product` int NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';


$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bkp_feature` (
    `id` int NOT NULL AUTO_INCREMENT,
	`id_bkp_category` int NOT NULL,
	`feature_key` varchar(255) NOT NULL,
	`feature_value` varchar(255) NOT NULL,
	`type` tinyint(1) default 0,
	`id_category` int default 0,
	`id_feature` int default 0,
    PRIMARY KEY  (`id`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';


foreach ($sql as $query)
    if (Db::getInstance()->execute($query) == false) return false;
