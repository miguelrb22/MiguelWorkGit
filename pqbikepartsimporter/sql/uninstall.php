<?php

$sql = array();

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'bkp_category`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'bkp_feature_category_value`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'bkp_product`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'bkp_feature_group`';




foreach ($sql as $query)
    if (Db::getInstance()->execute($query) == false) return false;