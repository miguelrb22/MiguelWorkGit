<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BikePartsWebServiceClient
 *
 * @author Daniel Perez
 * @version 1.0
 *
 */

require_once (_PS_MODULE_DIR_.'/pqbikepartsimporter/helper/loghelper.php');
require_once (_PS_MODULE_DIR_.'/pqbikepartsimporter/lib/bikepartswebserviceclient.php');

class BikePartsWebServiceClient
{

    /**
     * @param null $url
     * @param null $type
     * @return array|null
     */
    public static function requestXML($url = null, $type = null)
    {

        $result = array('response_info' => '', 'data' => null);

        if ($url == null || (empty($url))) return null;

        //realizamos la petición mediante la url
        $data = self::getResponseXML($url);


        $processstatus = (string)$data->xpath('processstatus')[0];


        $response_info = self::manageErrors($processstatus);

        //Comporbaciones de que la llamada es correcta
        if ($response_info == null) {

            $result['response_info'] = "unknown error";

        } else if ($response_info == "successful") {

            $result['response_info'] = $response_info;
            $result['data'] = $data;

        } else {

            $result['response_info'] = $response_info;
        }

        return $result;

    }

    /**
     * Función que devuelve el número de páginas
     * @param type $data
     * @return type
     */
    public static function getPages($data)
    {

        if ($data != null && !empty($data))
            if (!empty($data->xpath('ofpages')))
                return (string)$data->xpath('ofpages')[0];

    }

    /**
     * Construye la url para la categorías según id, en caso de null nos devuleve todas las categorías
     * @param type $user
     * @param type $password
     * @param type $category
     */
    public static function buildURLforCategory($user, $password, $category = null, $page = null, $perpage = null, $hqpic = null, $showfeature = null, $featuresearch = null)
    {
        $url = "";

        //Comprobamos que usuario no son nulos
        if ($user == null || empty($user))
            return null;
        //Comprobamos que password no son nulos
        if ($password == null || empty($password))
            return null;

        //formamus la url para devolver la url para obtener todas la categorías
        if ($category == null || empty($category)) {
            $url = "http://b2b.bike-parts.de/xml/?loginid=" . $user . "&password=" . $password . "&processtype=searchcatalog&searchpattern=*&searchpattern=*";

        } else {
            // para una categoría
            $url = "http://b2b.bike-parts.de/xml/?loginid=" . $user . "&password=" . $password . "&processtype=searchcatalog&searchpattern=*&category=" . $category;
        }

        //incorporamos hdpics
        if ($hqpic != null && !empty($hqpic)) {
            $url = $url . "&hqpic=" . $hqpic;
        }

        //incorporamos hdpics
        if ($showfeature != null && !empty($showfeature)) {
            $url = $url . "&showfeature=" . $showfeature;
        }

        //incorporamos cantidad por página
        if ($perpage != null && !empty($perpage)) {
            $url = $url . "&pagesize=" . $perpage;
        }

        //incorporamos el número de página
        if ($page != null && !empty($page)) {
            $url = $url . "&page=" . $page;
        }

        if ($featuresearch != null && !empty($featuresearch)) {
            $url = $url . "&featuresearch=" . $featuresearch;
        }
        return $url;
    }

    /**
     * Devuelve la url para producto según una categoría
     * @param type $user
     * @param type $password
     * @param type $category
     * @return string
     */
    public static function buildURLForProductsByCategory($user, $password, $category, $page = null, $perpage = null, $hqpic = null, $showfeature = null, $featuresearch = null)
    {

        $url = self::buildURLforCategory($user, $password, $category, $page, $perpage, $hqpic, $showfeature, $featuresearch);

        return $url;
    }

    /**
     * Devuelve la url para todos los productos
     * @param type $user
     * @param type $password
     * @return type
     */
    public static function buildURLForAllProducts($user, $password, $page = null, $perpage = null, $hqpic = null, $showfeature = null, $featuresearch = null)
    {

        $url = self::buildURLforCategory($user, $password, null, $page, $perpage, $hqpic, $showfeature, $featuresearch);


        return $url;
    }

    /**
     * Función que nos devuelve la respuesta XML
     * @param type $url
     * @return type
     */
    private static function getResponseXML($url)
    {

        if (($response_xml_data = file_get_contents($url)) === false) {
            return null;
        } else {
            libxml_use_internal_errors(true);
            $data = simplexml_load_string($response_xml_data);
            if (!$data) {
                return null;
            }
        }
        return $data;
    }

    //Procesamos y devolvermos los datos según el typo
    public static function getData($type, $data)
    {
        $solution = array();
        //Comprueba que el tipo sea el correcto
        if ($type == null || empty($type)) {
            return "bad type";
        }

        //Este switch hace que procese la información según el tipo
        switch ($type) {
            case "categories":
                $solution = self::getCategories($data);
                break;
            case "category":
                $solution = self::getCategory($data);
                break;
            case "products":
                $solution = self::getProducts($data);
                break;
            default :

                break;
        }

        return $solution;
    }

    private function getCategory($data)
    {

        $path = dirname(__FILE__) . '/PqBikeImportLib.php';
        require_once $path;

        PqBikeImportLib::loadClass("model/PqBikeCategory");
        PqBikeImportLib::loadClass("model/PqBikeProduct");
        PqBikeImportLib::loadClass("model/PqBikeFeature");
        PqBikeImportLib::loadClass("model/PqBikeFeaturevalue");

        $features_in_array = array();
        $category_final = new PqBikeCategory();
        $products_in_array = array();

        foreach ($data->children() as $key => $obj) {
            if ($key == "filter") {

                $category = new PqBikeCategory();
                $category->key = (string)$obj->filterkey;
                $category->count = (string)$obj->filtercount;
                $category->desc = (string)$obj->filterdesc;
                $category->alter = (string)$obj->filteralter;
                $category->alterdesc = (string)$obj->filteralterdesc;

                $category_final = $category;
            }

            if ($key == "feature") {

                $feature_value_in_array = array();
                foreach ($obj->children() as $key_featurevaule => $featurevalue) {
                    if ($key_featurevaule == "featurevalue") {
                        $feature_value_processed = new PqBikeFeaturevalue();
                        $feature_value_processed->valuekey = (string)$featurevalue->valuekey;
                        $feature_value_processed->valuedesc = (string)$featurevalue->valuedesc;

                        $feature_value_in_array[] = $feature_value_processed;
                    }
                }
                $feature_processed = new PqBikeFeature();
                $feature_processed->featurekey = (string)$obj->featurekey;
                $feature_processed->featurekeydesc = (string)$obj->featurekeydesc;
                $feature_processed->featurevalue = $feature_value_in_array;

                $features_in_array[] = $feature_processed;

            }
            if ($key == "item") {
                $product = new PqBikeProduct();

                $product->number = (string)$obj->number;
                $product->unitprice = (string)$obj->unitprice;
                $product->recommendedretailprice = (string)$obj->recommendedretailprice;
                $product->description1 = (string)$obj->description1;
                $product->description2 = (string)$obj->description2;
                $product->availablestatus = (string)$obj->availablestatus;
                $product->availablestatusdesc = (string)$obj->availablestatusdesc;
                $product->supplieritemnumber = (string)$obj->supplieritemnumber;
                $product->tax = (string)$obj->tax;
                $product->ean = (string)$obj->ean;
                $product->manufacturerean = (string)$obj->manufacturerean;
                $product->customstariffnumber = (string)$obj->customstariffnumber;
                $product->supplier = (string)$obj->supplier;
                $product->categorykey = (string)$obj->categorykey;
                $product->infourl = (string)$obj->infourl;
                $product->pictureurl = (string)$obj->pictureurl;

                $products_in_array[] = $product;

            }
        }
        $category_final->features = $features_in_array;
        $category_final->products = $products_in_array;

        return $category_final;
    }

    /**
     * Devuelve las categorias
     * @param type $data
     * @return type
     */
    private function getCategories($data)
    {

        $path = dirname(__FILE__) . '/PqBikeImportLib.php';
        require_once $path;

        PqBikeImportLib::loadClass("model/PqBikeCategory");


        $categories = array();
        foreach ($data->xpath("filter") as $key => $obj) {


            $category = new PqBikeCategory();
            $category->key = (string)$obj->filterkey;
            $category->count = (string)$obj->filtercount;
            $category->desc = (string)$obj->filterdesc;
            $category->alter = (string)$obj->filteralter;
            $category->alterdesc = (string)$obj->filteralterdesc;
            $categories[] = $category;


        }

        return $categories;
    }


    /*
   * @return array
   */
    public static function getFeaturesByCategory($key){

        //    public static function buildURLForProductsByCategory($user, $password, $category, $page = null, $perpage = null, $hqpic = null, $showfeature = null, $featuresearch = null)

        require_once (_PS_MODULE_DIR_.'/pqbikepartsimporter/classes/model/PqBikeCategory.php');

        $collection = array();

        $auth = BikePartsWebServiceClient::checkLogin();

        if($auth['logged']) {

            $url = BikePartsWebServiceClient::buildURLforCategory($auth['key'], $auth['pass'], $key, null, null, true, true, true);

            $response = BikePartsWebServiceClient::requestXML($url);
            LogHelper::Log("Info", "Descargando categorias...");


            $data =   BikePartsWebServiceClient::xml2array(($response['data']));
            $collection = $data['feature'];

        }
        return $collection;
    }

    /*
     * @return array
     */
    public static function getCategoriesV2(){


        require_once (_PS_MODULE_DIR_.'/pqbikepartsimporter/classes/model/PqBikeCategory.php');

        $collection = array();

        $auth = BikePartsWebServiceClient::checkLogin();

        if($auth['logged']) {

            $url = BikePartsWebServiceClient::buildURLforCategory($auth['key'], $auth['pass']);
            $response = BikePartsWebServiceClient::requestXML($url);
            LogHelper::Log("Info", "Descargando categorias...");

            $data =   BikePartsWebServiceClient::xml2array(($response['data']))['filter'];


            $i = 0;
            foreach ($data as $item){

                if($i == 0){ $i++; continue; }

                try {

                    $category = new PqBikeCategory();
                    $category->key = $item['filterkey'];
                    $category->count = $item['filtercount'];
                    $category->desc = $item['filterdesc'];
                    $collection[] = $category;

                }catch (Exception $e){

                    LogHelper::LogException("Error", $e->getMessage());

                }

            }

        }

        return $collection;
    }

    /**
     * Devuelve los productos
     * @param type $data
     * @return type
     */
    private function getProducts($data)
    {
        $path = dirname(__FILE__) . '/PqBikeImportLib.php';
        require_once $path;

        PqBikeImportLib::loadClass("model/PqBikeProduct");
        PqBikeImportLib::loadClass("model/PqBikeFeature");
        PqBikeImportLib::loadClass("model/PqBikeFeaturevalue");

        $products = array();

        foreach ($data->xpath("item") as $key => $obj) {


            $product = new PqBikeProduct();

            $product->number = (string)$obj->number;
            $product->unitprice = (string)$obj->unitprice;
            $product->recommendedretailprice = (string)$obj->recommendedretailprice;
            $product->description1 = (string)$obj->description1;
            $product->description2 = (string)$obj->description2;
            $product->availablestatus = (string)$obj->availablestatus;
            $product->availablestatusdesc = (string)$obj->availablestatusdesc;
            $product->supplieritemnumber = (string)$obj->supplieritemnumber;
            $product->tax = (string)$obj->tax;
            $product->ean = (string)$obj->ean;
            $product->manufacturerean = (string)$obj->manufacturerean;
            $product->customstariffnumber = (string)$obj->customstariffnumber;
            $product->supplier = (string)$obj->supplier;
            $product->categorykey = (string)$obj->categorykey;

            $features_in_array = array();

            foreach ($obj->xpath("feature") as $key_feature => $feature) {

                $feature_value_in_array = array();
                foreach ($feature->children() as $key_featurevaule => $featurevalue) {
                    if ($key_featurevaule == "featurevalue") {
                        $feature_value_processed = new PqBikeFeaturevalue();
                        $feature_value_processed->valuekey = (string)$featurevalue->valuekey;
                        $feature_value_processed->valuedesc = (string)$featurevalue->valuedesc;

                        $feature_value_in_array[] = $feature_value_processed;
                    }
                }
                $feature_processed = new PqBikeFeature();
                $feature_processed->featurekey = (string)$feature->featurekey;
                $feature_processed->featurekeydesc = (string)$feature->featurekeydesc;
                $feature_processed->featurevalue = $feature_value_in_array;

                $features_in_array[] = $feature_processed;


            }

            $product->features = $features_in_array;

            $product->infourl = (string)$obj->infourl;
            $product->pictureurl = (string)$obj->pictureurl;

            $products[] = $product;


        }

        return $products;
    }

    /**
     * Gestión de errores
     * @param type $xml_response
     * @return boolean|string
     */
    private static function manageErrors($processstatus = null)
    {
        switch ($processstatus) {
            case 0 :// successfull
                return "successful";
                break;
            case 10001://error fallo login                  
                return "login failed";
                break;
            case 10002: //Tipo de desconocido
                return "unknown processtype";
                break;
            case 10003:
                return "wrong profile password";
                break;
            case 10004:
                return "account is closed";
                break;
            case 10005:
                return "account is disabled";
                break;
            case 10006:
                return "this account needs a TAN";
                break;
            case 10007:
                return "orders disabled for this account";
                break;
            case 10101:
                return "no valid items";
                break;
            case 10102:
                return "unknown category";
                break;
            case 10103:
                return "unknown documenttype";
                break;
            case 10104:
                return "unknown documentstyle";
                break;
            case 10301:
                return "missing basketname";
                break;
            case 10302:
                return "no items in bakset";
                break;
            case 10401:
                return "no customer filter in account";
                break;
            case 10402:
                return "customer not found";
                break;
            case 10403:
                return "customer does not match to salesperson";
                break;
            case 10501:
                return "deliveryaddress ist not complete";
                break;
            case 10502:
                return "deliverycountrycode ist not defined or not allowed";
                break;
            default :
                return "unknow error";
                break;
        }

    }

    public function checkLogin()
    {
        $auth = array();

        $auth['key'] = Configuration::get('BKP_KEY');
        $auth['pass'] = Configuration::get('BKP_PASS');
        $auth['logged'] = false;

        if (isset($auth['key']) && isset( $auth['pass']) && !empty($auth['key']) && !empty( $auth['pass']))
            $auth['logged'] = true;

        return $auth;
    }

    public static function xml2array ($xmlObject)
    {
        return json_decode(json_encode((array) ($xmlObject)), 1);

    }
}