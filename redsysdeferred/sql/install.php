<?php
/**
 * Oct8ne Module
 *
 * @category  Prestashop
 * @category  Module
 * @author    Prestaquality.com
 * @copyright 2014 - 2015 Prestaquality
 * @license   Commercial license see license.txt
 * Support by mail  : info@prestaquality.com
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'redsysdeferred_historic_url` (
`id_historic` int(10) unsigned NOT NULL AUTO_INCREMENT,
`url` text NOT NULL,
`date_upd` datetime NOT NULL,
`paid` TINYINT(1) NOT NULL default 0,
`id_order` int(10) unsigned NOT NULL default 0,
`isdrop` TINYINT(1) NOT NULL default 0,
PRIMARY KEY  (`id_historic`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}