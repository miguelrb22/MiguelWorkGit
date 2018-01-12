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
        require_once(_PS_MODULE_DIR_ . '/pqbikepartsimporter/classes/BkpProduct.php');
    }

    public function init()
    {
        parent::init();

        if(Tools::getValue("pqtoken") != Configuration::get('BKP_TOKEN')){

            return false;
        }

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
        die();

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
            LogHelper::LogException($ex);
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

            if (Tools::isSubmit("redirect")) {

                Tools::redirect($_SERVER['HTTP_REFERER']);
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
    {set_time_limit(0);
        LogHelper::Log("Info", "Sincronizando productos...");
        LogHelper::Log("Info", "Solicitamos las categorías a sincronizar");
        $this->languages = Language::getLanguages();
        $this->disabled_by_default = (bool)Configuration::get(PqBikepartsImporter::PQ_BKP_DEFAULT_STATUS_CK);
        $this->commision = Configuration::get(PqBikepartsImporter::PQ_BKP_COMMISSION_CK);

        require_once(_PS_MODULE_DIR_ . '/pqbikepartsimporter/classes/BkpCategory.php');
        require_once(_PS_MODULE_DIR_ . '/pqbikepartsimporter/classes/BkpProduct.php');
        $categories = BkpCategory::getCategoriesWithAssociation();



        foreach($categories as $category_base){
            //Comprobamos que la categoría exista
            if(!ObjectModel::existsInDatabase($category_base['id_category'],'category'))
                continue;

            //Solicitamos los productos de esta categoría
            $page = 1;
            $first_product_number = '';//Acumulamos el primer número, si se repite en primera posición, hemos terminado
            do{                
                LogHelper::Log("Info", "Categoria: ".$category_base['bkp_key']." pagina: ".$page);
                $products = BikePartsWebServiceClient::getProductsByCategory($category_base['bkp_key'],$page,50);
                $page++;
                if(!empty($products)){//si se repite el mismo producto en primera posición, hemos terminado
                    if($products[0]->number == $first_product_number)
                        break;

                    $first_product_number = $products[0]->number;
                }

                foreach($products as $product_base){
                    try {
                        $product_data = BkpProduct::getDataByReference($product_base->number);

                        if (empty($product_data)) {
                            //crea el producto
                            $id_prod = $this->importProduct($product_base, $category_base['id_category'],
                                $category_base['id_tax_rule']);
                            LogHelper::Log("Info", "Creamos producto... Id: " . $id_prod);
                            $this->saveBkpProduct($product_base->number, $id_prod);

                        } else {
                            //actualiza el producto
                            LogHelper::Log("Info", "Actualizamos producto... Id: " . $product_data['id_product']);
                            $this->updateProductData($product_base, $product_data['id_product']);
                        }

                    } catch (Exception $ex) {
                        LogHelper::LogException($ex);
                    }
                }
            }while(count($products>0));
        }


        die('done!');

    }


    private function saveBkpProduct($bkp_reference, $id_prod)
    {
        $obj_bkp_product = new BkpProduct();
        $obj_bkp_product->bkp_reference = $bkp_reference;
        $obj_bkp_product->id_product = $id_prod;
        $obj_bkp_product->save();
    }

    private function importProduct($data, $category, $tax_id){
        //crea producto
        $shop = Context::getContext()->shop->id;

        $product = new Product();

        $languages_name = array();
        $links_rewrite = array();
        $description_languages = array();

        $patron ='/^[^<>\/.=#{}]*$/u';

        foreach ($this->languages as $language) {
            $languages_name[$language['id_lang']] = trim( str_replace($patron, '',$data->description1)." ".str_replace($patron, '', $data->description2));
        }

        foreach ($this->languages as $language) {
            $links_rewrite[$language['id_lang']] = Tools::link_rewrite(trim($data->description1.$data->description2));

        }

        if(!empty($data->infourl)){

            //$description = Tools::file_get_contents($data->infourl);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $data->infourl);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $description = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if($httpCode == 200){
                foreach ($this->languages as $language) {
                    $description_languages[$language['id_lang']] = $description;
                }
            }

        }


        $product->name = $languages_name;
        $product->link_rewrite = $links_rewrite;
        $product->id_category_default = $category;
        $product->price = str_replace(',','.',$data->recommendedretailprice); //TODO Actualizacion
        $product->quantity = 0; 
        $product->wholesale_price = str_replace(',','.',$data->unitprice); //TODO Actualizacion
        $product->ean13 = $data->ean;
        $product->id_shop_default = $shop;
        $product->description = $description_languages;
        $product->visibility = 'both';
        $product->active = !$this->disabled_by_default;
        $product->id_tax_rules_group = $tax_id;
        $product->id_manufacturer = Manufacturer::getIdByName(trim($data->supplier));

        $product->save();
        //Tras guardar asignamos las categorías
        $product->updateCategories([$category]);

		//Y la parte generica
		$this->updateProductData($data,0,$product);
		
		//La imagen
        if (!empty($data->pictureurl)) {
            $this->setImage($product->id, array($shop), $data->pictureurl, true);
        }


        //asociaciones
        if(!empty($data->features)) {

            $obj_cat = BkpCategory::getInstanceByKey($data->categorykey);

            foreach ($data->features as $pqbikefeature) {

                $obj_feature = BkpFeature::getByCategory($obj_cat->id, $pqbikefeature->featurekey);
                //asocia categorias
                if ($obj_feature->type == 1) {
                    foreach ($pqbikefeature->featurevalue as $pqbikefeaturevalue) {
                        $categories = $product->getCategories();

                        $obj_feature_value = BkpFeatureValue::getValueByFeatureAndValueDesc($obj_feature->id, $pqbikefeaturevalue->valuedesc);

                        $id_cat = $obj_feature_value->id_category;
                        $categories[]= $id_cat;
                        $product->addToCategories($categories);
                    }
                }
                //asocia atributos
                if ($obj_feature->type == 2) {
                    foreach ($pqbikefeature->featurevalue as $pqbikefeaturevalue) {

                        $obj_feature_value = BkpFeatureValue::getValueByFeatureAndValueDesc($obj_feature->id, $pqbikefeaturevalue->valuedesc);
                        $id_feat = $obj_feature_value->id_feature;

                        $id_feature_value = (int)FeatureValue::addFeatureValueImport($id_feat, $pqbikefeaturevalue->valuedesc, $product->id, $this->context->language->id, true);
                        Product::addFeatureProductImport($product->id, $id_feat, $id_feature_value);
                    }
                }
            }
        }

        return $product->id;
    
    }

    private function updateProductData($data, $id_prod, $product = null){
        //actualiza el producto
        $shop = Context::getContext()->shop->id;
		if(empty($product))
			$product = new Product($id_prod);

        $product->price = str_replace(',','.',$data->recommendedretailprice); //TODO Actualizacion

        $product->wholesale_price = str_replace(',','.',$data->unitprice); //TODO Actualizacion

        //Calculamos el descuento en base a la comisión
        if($this->commision>0){
            $final_commision = (float)$product->price-(float)$product->wholesale_price*(1+$this->commision/100);
            if($final_commision>0){
                $commission_perc = $final_commision*100/(float)$product->price;

                $specific_price = SpecificPrice::getSpecificPrice($product->id, $shop, 0, 0, 0, 1, 0, 0, 0, 0);

                if (is_array($specific_price) && isset($specific_price['id_specific_price'])) {
                    $specific_price = new SpecificPrice((int)$specific_price['id_specific_price']);
                } else {
                    $specific_price = new SpecificPrice();
                }
                $specific_price->id_product = (int)$product->id;
                $specific_price->id_specific_price_rule = 0;
                $specific_price->id_shop = $shop;
                $specific_price->id_currency = 0;
                $specific_price->id_country = 0;
                $specific_price->id_group = 0;
                $specific_price->price = -1;
                $specific_price->id_customer = 0;
                $specific_price->from_quantity = 1;

                $specific_price->reduction = round($commission_perc/100,2);
                $specific_price->reduction_type = 'percentage';
                $specific_price->from =  '0000-00-00 00:00:00';
                $specific_price->to = '0000-00-00 00:00:00';
                $specific_price->save();
            }
        }


        //Establecemos disponibilidad
        if($data->availablestatus==0){
            StockAvailable::setProductOutOfStock($product->id,1);
            $this->setAvailability($product);
        }

        if($data->availablestatus==2){
            if(empty($data->expecteddeliverydate)){
                StockAvailable::setProductOutOfStock($product->id,0);
            }else{
                StockAvailable::setProductOutOfStock($product->id,1);
                $this->setAvailability($product, $data->expecteddeliverydate);
            }
        }

    }

    private function setAvailability($product, $expecteddeliverydate = 0){
        $tiempo_adicional = Configuration::get(pqbikepartsimporter::PQ_BKP_ADDITIONAL_TIME_CK);
        if($expecteddeliverydate == 0){
            $fecha = date("Y-m-d H:i:s");
        }else{
            $fecha = date($expecteddeliverydate);
        }

        $nuevafecha = strtotime ( '+'.$tiempo_adicional.' day' , strtotime ( $fecha ) ) ;
        $nuevafecha = date ( "Y-m-d H:i:s" , $nuevafecha );

        $string ="Delivery time: ".$nuevafecha;

        $available=array();
        foreach ($this->languages as $language) {
            $available[$language['id_lang']] = $string;
        }
        $product->available_later =  $available;
        $product->save();
    }

    private function setImage($id_product, $shops, $url, $cover)
    {
        $image = new Image();
        $image->id_product = $id_product;
        $image->position = Image::getHighestPosition($id_product) + 1;
        $image->cover = $cover;
        if (($image->validateFields(false, true)) === true &&
            ($image->validateFieldsLang(false, true)) === true && $image->add()
        ) {
            $image->associateTo($shops);
            if (!$this->copyImg($id_product, $image->id, $url, "products", false)) {
                $image->delete();
                return null;
            }
        }
        return $image->id;
    }

    /**
     * @param $id_entity
     * @param null $id_image
     * @param $url
     * @param string $entity
     * @param bool $regenerate
     * @return bool
     */
    private function copyImg($id_entity, $id_image = null, $url, $entity = "products", $regenerate = true)
    {
        $tmpfile = tempnam(_PS_TMP_IMG_DIR_, "ps_import");
        $watermark_types = explode(",", Configuration::get("WATERMARK_TYPES"));
        switch ($entity) {
            default:
            case "products":
                $image_obj = new Image($id_image);
                $path = $image_obj->getPathForCreation();
                break;
            case "categories":
                $path = _PS_CAT_IMG_DIR_ . (int) $id_entity;
                break;
            case "manufacturers":
                $path = _PS_MANU_IMG_DIR_ . (int) $id_entity;
                break;
            case "suppliers":
                $path = _PS_SUPP_IMG_DIR_ . (int) $id_entity;
                break;
        }
        $url = urldecode(trim($url));
        $parced_url = parse_url($url);
        if (isset($parced_url["path"])) {
            $uri = ltrim($parced_url["path"], "/");
            $parts = explode("/", $uri);
            foreach ($parts as &$part) {
                $part = rawurlencode($part);
            }
            unset($part);
            $parced_url["path"] = "/" . implode("/", $parts);
        }
        if (isset($parced_url["query"])) {
            $query_parts = array();
            parse_str($parced_url["query"], $query_parts);
            $parced_url["query"] = http_build_query($query_parts);
        }
        if (!function_exists("http_build_url")) {
            require_once(_PS_TOOL_DIR_ . "http_build_url/http_build_url.php");
        }
        $url = http_build_url("", $parced_url);
        $orig_tmpfile = $tmpfile;
        if (Tools::copy($url, $tmpfile)) {
            if (!ImageManager::checkImageMemoryLimit($tmpfile)) {
                @unlink($tmpfile);
                return false;
            }
            $tgt_width = $tgt_height = 0;
            $src_width = $src_height = 0;
            $error = 0;
            ImageManager::resize($tmpfile, $path . ".jpg", null, null, "jpg", false, $error, $tgt_width, $tgt_height, 5, $src_width, $src_height);
            $images_types = ImageType::getImagesTypes($entity, true);
            if ($regenerate) {
                $previous_path = null;
                $path_infos = array();
                $path_infos[] = array($tgt_width, $tgt_height, $path . ".jpg");
                foreach ($images_types as $image_type) {
                    $tmpfile = get_best_path($image_type["width"], $image_type["height"], $path_infos);
                    if (ImageManager::resize($tmpfile, $path . "-" . stripslashes($image_type["name"]) . ".jpg", $image_type["width"], $image_type["height"], "jpg", false, $error, $tgt_width, $tgt_height, 5, $src_width, $src_height)
                    ) {
                        if ($tgt_width <= $src_width && $tgt_height <= $src_height) {
                            $path_infos[] = array($tgt_width, $tgt_height, $path . "-" . stripslashes($image_type["name"]) . ".jpg");
                        }
                        if ($entity == "products") {
                            if (is_file(_PS_TMP_IMG_DIR_ . "product_mini_" . (int) $id_entity . ".jpg")) {
                                unlink(_PS_TMP_IMG_DIR_ . "product_mini_" . (int) $id_entity . ".jpg");
                            }
                            if (is_file(_PS_TMP_IMG_DIR_ . "product_mini_" . (int) $id_entity . "_" . (int) Context::getContext()->shop->id . ".jpg")) {
                                unlink(_PS_TMP_IMG_DIR_ . "product_mini_" . (int) $id_entity . "_" . (int) Context::getContext()->shop->id . ".jpg");
                            }
                        }
                    }
                    if (in_array($image_type["id_image_type"], $watermark_types)) {
                        Hook::exec("actionWatermark", array("id_image" => $id_image, "id_product" => $id_entity));
                    }
                }
            }
        } else {
            @unlink($orig_tmpfile);
            return false;
        }
        unlink($orig_tmpfile);
        return true;
    }


}