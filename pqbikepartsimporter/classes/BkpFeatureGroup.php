<?php

class BkpFeatureGroup extends ObjectModel
{
    public $bkp_key;
    public $id_category;
    public $type;
    public $id_feature;

    /*
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'bkp_feature_group',
        'primary' => 'id',
        'fields' => array(
            'bkp_key' => array('type' => self::TYPE_STRING, 'required' => true),
            'id_category' => array('type' => self::TYPE_INT, ),
            'type' => array('type' => self::TYPE_INT, 'required' => true),
            'id_feature' => array('type' => self::TYPE_INT, ),
        )
    );
}

?>
