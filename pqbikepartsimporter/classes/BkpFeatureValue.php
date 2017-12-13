<?php

class BkpFeatureValue extends ObjectModel
{
    public $id_bkp_feature;
    public $value_key;
    public $value_desc;

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
        )
    );
}
