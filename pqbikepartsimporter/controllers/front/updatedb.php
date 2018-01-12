<?php
/**
 * Created by PhpStorm.
 * User: migue
 * Date: 21/12/2017
 * Time: 9:41
 */


die();
require_once(_PS_MODULE_DIR_ . 'pqbikepartsimporter/classes/BkpFeatureValue.php');
require_once(_PS_MODULE_DIR_ . 'pqbikepartsimporter/classes/BkpFeature.php');


class PqBikepartsImporterUpdatedbModuleFrontController extends ModuleFrontController
{


    public function __construct()
    {

        parent::__construct();

    }

    public function init()
    {
        parent::init();

    }

    public function postProcess()
    {


        $value = Tools::getValue('value');
        $id_feature = Tools::getValue('id_feature');
        $type = Tools::getValue('type');
        $id_value = Tools::getValue('id_value');

        BkpFeatureValue::setDataFeatureValue($id_feature, $id_value, $type, $value);

        $feature = new BkpFeature($id_feature);
        $feature->type = $type;
        $feature->save();

        die();

    }


}