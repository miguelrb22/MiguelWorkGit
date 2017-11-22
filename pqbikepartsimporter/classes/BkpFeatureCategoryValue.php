<?php

class BkpFeatureCategoryValue extends ObjectModel
{
    public $id_bkp_category;
    public $value_key;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'bkp_feature_category_value',
        'primary' => 'id_feature_group',
        'fields' => array(
            'id_bkp_category' => array('type' => self::TYPE_INT, 'required' => true),
            'value_key' => array('type' => self::TYPE_STRING, ),
        )
    );
}

?>