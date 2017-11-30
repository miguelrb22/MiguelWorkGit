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
        require_once (_PS_MODULE_DIR_.'/pqbikepartsimporter/helper/loghelper.php');
        require_once (_PS_MODULE_DIR_.'/pqbikepartsimporter/lib/bikepartswebserviceclient.php');
    }

    public function init()
    {
        parent::init();

        $this->action = trim(strip_tags(Tools::getValue('action')));
    }

    public function postProcess(){


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
    public function categoriesSynchronizeBPMethod(){

        if($this->isLoggedIn()) {
            $url = BikePartsWebServiceClient::buildURLforCategory($this->key, $this->pass);
            $response = BikePartsWebServiceClient::requestXML($url);
            LogHelper::Log("Info", "Sincronizando categorias...");

            $categories = ($response['data']);
            dump($this->xml2array($categories));
            die();
        }

    }

    //http://localhost/prestashop6/es/module/pqbikepartsimporter/cron?action=products
    public function productsSynchronizeBPMethod(){

        LogHelper::Log("Info", "Sincronizando productos...");

    }

    public function isLoggedIn()
    {
        $this->key = Configuration::get('BKP_KEY');
        $this->pass = Configuration::get('BKP_PASS');

        if (isset($this->key) && isset($this->pass) && !empty($this->key) && !empty($this->pass)) {

            return true;
        }

        return false;
    }

    public function xml2array ( $xmlObject)
    {
        return json_decode(json_encode((array) ($xmlObject)), 1);

    }
}