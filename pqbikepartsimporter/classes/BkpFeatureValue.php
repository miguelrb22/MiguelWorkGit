<?php

class BkpFeatureValue extends ObjectModel
{
    public $id_bkp_feature;
    public $value_key;
    public $value_desc;
    public $id_category;
    public $id_feature;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'bkp_feature_value',
        'primary' => 'id',
        'fields' => array(
            'id_bkp_feature' => array('type' => self::TYPE_INT, 'required' => true),
            'value_key' => array('type' => self::TYPE_STRING),
            'value_desc' => array('type' => self::TYPE_STRING),
            'id_category' => array('type' => self::TYPE_INT),
            'id_feature' => array('type' => self::TYPE_INT)
        )
    );

    //SELECT * FROM `ps_bkp_category` c left join ps_bkp_feature f on (c.id = f.id_bkp_category) left join ps_bkp_feature_value v on( f.id = v.id_bkp_feature)

    public static function getValueByFeature($feature, $key){

        $db = Db::getInstance();

        $query = "SELECT id from ". _DB_PREFIX_ ."bkp_feature_value where id_bkp_feature = {$feature} and value_key = '{$key}'";

        $result = $db->getValue($query);

        if(isset($result)){

            return new BkpFeatureValue($result);
        }

        return new BkpFeatureValue();

    }

    public static function setDataFeatureValue($id_feature, $id_value, $type, $data){

        $aux = "";
        if($type == 1) $aux = "id_category";
        if($type == 2) $aux = "id_feature";

        $db = Db::getInstance();

        $query = "update ". _DB_PREFIX_ ."bkp_feature_value set {$aux} = $data where id_bkp_feature = {$id_feature} and id = {$id_value}";

        $result = $db->execute($query);

    }



}
