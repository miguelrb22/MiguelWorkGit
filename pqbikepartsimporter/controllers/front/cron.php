<?php

/**
 * Created by PhpStorm.
 * User: migue
 * Date: 29/11/2017
 * Time: 12:09
 */
class PqBikepartsImporterCronModuleFrontController extends ModuleFrontController
{

    private $action;
    private $key;
    private $pass;

    public function __construct()
    {
        parent::__construct();
        require_once(_PS_MODULE_DIR_ . '/pqbikepartsimporter/helper/loghelper.php');
        require_once(_PS_MODULE_DIR_ . '/pqbikepartsimporter/lib/bikepartswebserviceclient.php');
        require_once(_PS_MODULE_DIR_ . '/pqbikepartsimporter/classes/BkpFeature.php');
        require_once(_PS_MODULE_DIR_ . '/pqbikepartsimporter/classes/BkpFeatureValue.php');
    }

    public function init()
    {
        parent::init();

        $this->action = trim(strip_tags(Tools::getValue('action')));
    }

    public function postProcess()
    {

        try {

            $meth = $this->action . 'SynchronizeBPMethod';

            if (method_exists($this, $meth)) {

                $this->$meth();

            } else {

                throw new Exception("Method not Exists");
            }

        } catch (Exception $ex) {

            LogHelper::LogException($ex);

        }

    }

//http://localhost/prestashop6/es/module/pqbikepartsimporter/cron?action=categories
//http://www.prestashop.local/prestashop/es/module/pqbikepartsimporter/cron?action=categories
    public function categoriesSynchronizeBPMethod()
    {

        require_once(_PS_MODULE_DIR_ . 'pqbikepartsimporter/classes/BkpCategory.php');

        try {

            $data = BikePartsWebServiceClient::getCategoriesV2();
            foreach ($data as $model) {

                $BkpCategory = BkpCategory::getInstanceByKey($model->key);

                if (Validate::isLoadedObject($BkpCategory)) {

                    $BkpCategory->bkp_name = $model->desc;
                    $BkpCategory->save();

                } else {

                    $BkpCategory = new BkpCategory();
                    $BkpCategory->bkp_key = $model->key;
                    $BkpCategory->bkp_name = $model->desc;
                    $BkpCategory->save();
                }
            }

        } catch (Exception $ex) {
            LogHelper::LogException($ex->getMessage());
        }

        if (Tools::isSubmit("redirect")) {

            Tools::redirect($_SERVER['HTTP_REFERER']);
        }

        die("finish categories load");
    }

    //http://www.prestashop.local/prestashop/es/module/pqbikepartsimporter/cron?action=charasteristics

    /**
     *
     */
    public function charasteristicsSynchronizeBPMethod()
    {

        try {

            require_once(_PS_MODULE_DIR_ . 'pqbikepartsimporter/classes/BkpCategory.php');

            $categories = BkpCategory::getAll();

            foreach ($categories as $category) {

                $bkp_category_id = $category['id'];

                $features = BikePartsWebServiceClient::getFeaturesByCategory($category['bkp_key']);

                if (!isset($features['featurekey'])) {

                    foreach ($features as $feature) {

                        $Objectfeature = $this->createFeature($bkp_category_id, $feature);
                        $BkpFeatureId = $Objectfeature->id;
                        $this->setFeatureValues($feature['featurevalue'], $BkpFeatureId);

                    }

                } else {


                    $Objectfeature = $this->createFeature($bkp_category_id, $features);
                    $BkpFeatureId = $Objectfeature->id;
                    $this->setFeatureValues($features['featurevalue'], $BkpFeatureId);

                }

            }

            die("finish characteristics load");

        } catch (Exception $e) {

            ddd($e->getMessage());
            die();
        }

    }

    private function createFeature($bkp_category_id, $feature){

        $Objectfeature = BkpFeature::getByCategory($bkp_category_id, $feature['featurekey']);

        if (!Validate::isLoadedObject($Objectfeature)) {

            $Objectfeature->feature_key = $feature['featurekey'];
            $Objectfeature->feature_value = $feature['featurekeydesc'];
            $Objectfeature->id_bkp_category = $bkp_category_id;
            $Objectfeature->save();
        }

        return $Objectfeature;

    }

    /**
     * Crea los Value de cada Feature
     * @param $feature_values
     * @param $BkpFeatureId
     */
    private function setFeatureValues($feature_values, $BkpFeatureId)
    {
        if (!isset($feature_values['valuekey'])) {

            foreach ($feature_values as $value) {

                $ObjectValue = $this->createFeatureValue($BkpFeatureId, $value);
            }

        } else {

            $ObjectValue = $this->createFeatureValue($BkpFeatureId, $feature_values);
        }

    }

    /**
     * Crea o instancia un Value de una Feature
     * @param $BkpFeatureId
     * @param $value
     * @return BkpFeatureValue
     */
    private function createFeatureValue($BkpFeatureId, $value){

        $ObjectValue = BkpFeatureValue::getValueByFeature($BkpFeatureId, $value['valuekey']);

        if (!Validate::isLoadedObject($ObjectValue)) {

            $ObjectValue->id_bkp_feature = $BkpFeatureId;
            $ObjectValue->value_key = $value['valuekey'];
            $ObjectValue->value_desc = $value['valuedesc'];
            $ObjectValue->save();
        }

        return $ObjectValue;
    }

    //http://localhost/prestashop6/es/module/pqbikepartsimporter/cron?action=products
    public function productsSynchronizeBPMethod()
    {

        LogHelper::Log("Info", "Sincronizando productos...");

    }


}