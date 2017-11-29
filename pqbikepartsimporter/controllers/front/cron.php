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

    public function __construct()
    {
        parent::__construct();
        require_once (_PS_MODULE_DIR_.'/pqbikepartsimporter/helper/loghelper.php');
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

            $this->module->logException($ex);

        }

    }

//http://localhost/prestashop6/es/module/pqbikepartsimporter/cron?action=categories
    public function categoriesSynchronizeBPMethod(){

        LogHelper::Log("Info", "Sincronizando categorias...");

    }

    //http://localhost/prestashop6/es/module/pqbikepartsimporter/cron?action=products
    public function productsSynchronizeBPMethod(){

        LogHelper::Log("Info", "Sincronizando productos...");

    }
}