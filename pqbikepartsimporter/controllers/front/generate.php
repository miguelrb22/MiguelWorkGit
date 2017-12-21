<?php
/**
 * Created by PhpStorm.
 * User: migue
 * Date: 21/12/2017
 * Time: 9:41
 */

require_once(_PS_MODULE_DIR_ . 'pqbikepartsimporter/classes/BkpCategory.php');
require_once(_PS_MODULE_DIR_ . 'pqbikepartsimporter/classes/BkpFeature.php');
require_once(_PS_MODULE_DIR_ . 'pqbikepartsimporter/classes/BkpFeatureValue.php');

class PqBikepartsImporterGenerateModuleFrontController extends ModuleFrontController
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

        $prestashop_categories = Category::getAllCategoriesName();


        $prestashop_categories[] = array("id_category" => 0, "name" => $this->module->l('No sincronizar'));

        $all = BkpCategory::getAll();

        $id = Tools::getValue('general_category',$all[0]['id']);

        $data = BkpCategory::getCategoryFeatureValueData($id);

        $features = Feature::getFeatures($this->context->language->id);

        $this->context->smarty->assign(array(

            'pq_bkp_feature_value_date' => $data,
            'pq_bkp_features' => $features,
            'bkp_categories' => $all,
            'prestashop_categories' => $prestashop_categories,
        ));

        $tpl = 'characteristics_content.tpl';
        $this->setTemplate($tpl);

        $html = $this->context->smarty->fetch($this->template);

        echo $html;
        die();

    }


}