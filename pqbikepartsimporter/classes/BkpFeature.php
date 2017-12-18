<?php

class BkpFeature extends ObjectModel
{
    public $id_bkp_category;
    public $feature_key;
    public $feature_value;
    public $type;
    public $id_category;
    public $id_feature;

    /*
     * @see ObjectModel::$definition
     */
    public static $definition = array(

        'table' => 'bkp_feature',
        'primary' => 'id',
        'fields' => array(
            'id_bkp_category' => array('type' => self::TYPE_INT),
            'feature_key' => array('type' => self::TYPE_STRING, 'required' => true),
            'feature_value' => array('type' => self::TYPE_STRING, 'required' => true),
            'type' => array('type' => self::TYPE_INT),
            'id_category' => array('type' => self::TYPE_INT),
            'id_feature' => array('type' => self::TYPE_INT)
        )
    );

    public static function getByCategory($category_id, $feature){

        $db = Db::getInstance();

        $query = "SELECT id from ". _DB_PREFIX_ ."bkp_feature where id_bkp_category = {$category_id} and feature_key = '{$feature}'";

        $result = $db->getValue($query);

        if(isset($result)){

            return new BkpFeature($result);
        }

        return new BkpFeature();

    }
}

