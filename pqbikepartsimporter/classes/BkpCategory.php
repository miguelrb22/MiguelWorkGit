<?php

class BkpCategory extends ObjectModelCore
{
    public $bkp_key;
    public $bkp_name;
    public $id_category;
    public $id_tax_rule;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'bkp_category',
        'primary' => 'id',
        'fields' => array(
            'bkp_key' => array('type' => self::TYPE_STRING, 'required' => true),
            'bkp_name' => array('type' => self::TYPE_STRING,),
            'id_category' => array('type' => self::TYPE_INT,),
            'id_tax_rule' => array('type' => self::TYPE_INT,),
        )
    );

    /**
     * Devuelve una instancia de BkpCategory si existe la clave, si no devuelve nulo.
     * @param $key
     * @return Country|null
     */
    public static function getInstanceByKey($key)
    {

        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'bkp_category` WHERE `bkp_key` = \'' . pSQL($key) . '\'';

        $BkpCategory = Db::getInstance()->executeS($sql);

        if (isset($BkpCategory) && !empty($BkpCategory)) {

            return new BkpCategory($BkpCategory[0]['id']);
        }
        return null;
    }

    public static function getAll(){

        $sql = 'SELECT id, bkp_name FROM `' . _DB_PREFIX_ . 'bkp_category`';

        $all = Db::getInstance()->executeS($sql);

        return $all;



    }




}
