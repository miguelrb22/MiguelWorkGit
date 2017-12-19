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

        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'bkp_category`';

        $all = Db::getInstance()->executeS($sql);

        return $all;

    }


    //$query = "SELECT * FROM `ps_bkp_category` c left join ps_bkp_feature f on (c.id = f.id_bkp_category) left join ps_bkp_feature_value v on( f.id = v.id_bkp_feature)";

    public static function getCategoryFeatureValueData($id_category = null){

        $db = Db::getInstance();

        $query = "SELECT c.id as id_category, c.bkp_key, c.bkp_name, c.id_category as relation_bkp_category_category, c.id_tax_rule, f.id as id_feature, f.feature_key, f.feature_value, f.type, v.id as id_value, v.id_bkp_feature, v.value_key, v.value_desc, v.id_category as relation_bkp_feature_category, v.id_feature as relation_bkp_feature_feature FROM `ps_bkp_category` c left join ps_bkp_feature f on (c.id = f.id_bkp_category) left join ps_bkp_feature_value v on( f.id = v.id_bkp_feature) where f.id is not null";

        if(!empty($id_category)){
            $query .= " and c.id = {$id_category}";
        }

        $result = $db->executeS($query);


        $groups = array();
        foreach ($result as $item) {
            $key = $item['id_feature'];
            if (!isset($groups[$key])) {
                $groups[$key] = array("data" => array($item), "name" => $item['feature_value']);

            } else {
                $groups[$key]['data'][] = $item;
            }
        }

        return $groups;


    }




}
