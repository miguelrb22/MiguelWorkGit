<?php

class BkpCategory extends ObjectModel
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
            'bkp_name' => array('type' => self::TYPE_STRING, ),
            'id_category' => array('type' => self::TYPE_INT, ),
            'id_tax_rule' => array('type' => self::TYPE_INT,),
        )
    );
}

?>