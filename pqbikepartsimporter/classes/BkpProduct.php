<?php

class BkpProduct extends ObjectModel
{
    public $bkp_reference;
    public $id_product;

    /*
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'bkp_product',
        'primary' => 'id',
        'fields' => array(
            'bkp_reference' => array('type' => self::TYPE_STRING, 'required' => true),
            'id_product' => array('type' => self::TYPE_INT, 'required' => true ),
        )
    );

    /**
     * Devuelve la información del producto por referencia bikeparts
     */
    public static function getDataByReference($ref){
        $query = new dbQuery();
        $query->select('*')->from(self::$definition['table'])->where('bkp_reference = \''.pSQL($ref).'\'');

        return db::getInstance()->getRow($query);
    }

    public static function getDataByIdProduct($id_product){
        $query = new dbQuery();
        $query->select('*')->from(self::$definition['table'])->where('id_product = \''.pSQL($id_product).'\'');

        return db::getInstance()->getRow($query);
    }
}

?>
