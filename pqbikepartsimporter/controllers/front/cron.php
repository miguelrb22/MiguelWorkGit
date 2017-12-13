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

        if(Tools::isSubmit("redirect")){

            Tools::redirect($_SERVER['HTTP_REFERER']);
        }

        die("finish categories load");
    }

    //http://www.prestashop.local/prestashop/es/module/pqbikepartsimporter/cron?action=charasteristics
    public function charasteristicsSynchronizeBPMethod()
    {
        require_once(_PS_MODULE_DIR_ . 'pqbikepartsimporter/classes/BkpCategory.php');


        $categories = BkpCategory::getAll();


        foreach ($categories as $category) {

            $category_id = $category['id'];

            $features =  BikePartsWebServiceClient::getFeaturesByCategory($category['bkp_key']);

            foreach ($features as $feature) {

                dump($feature);
                die();
            }

        }

        die("finish characteristics load");

    }

    //http://localhost/prestashop6/es/module/pqbikepartsimporter/cron?action=products
    public function productsSynchronizeBPMethod()
    {

        LogHelper::Log("Info", "Sincronizando productos...");

    }


}