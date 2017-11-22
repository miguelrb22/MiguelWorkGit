<?php

class BkpProduct extends ObjectModel
{
    public $id_bkp_reference;
    public $id_product;

    /*
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'bkp_product',
        'primary' => 'id',
        'fields' => array(
            'id_bkp_reference' => array('type' => self::TYPE_STRING, 'required' => true),
            'id_product' => array('type' => self::TYPE_INT, 'required' => true ),
        )
    );
}

?>
