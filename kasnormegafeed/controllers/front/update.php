<?php

error_reporting(-1);
ini_set('display_errors', 1);
set_time_limit(0);

/**
 * Created by PhpStorm.
 * User: Miguel (Prestaquality)
 * Date: 30/08/2017
 * Time: 13:37
 */
class KasnorMegaFeedUpdateModuleFrontController extends ModuleFrontController
{


    const PRODUCT = "products";

    const STOCKS = "stocks";

    private $products_path = _PS_MODULE_DIR_ . "kasnormegafeed/files/products.csv";

    private $stocks_path = _PS_MODULE_DIR_ . "kasnormegafeed/files/stocks.csv";

    private $products_url;

    private $stocks_url;

    private $languages;

    /**
     * Init
     */
    public function init()
    {
        $this->products_url = Configuration::get('KASNORMEGAFEED_URL_PRODUCT');
        $this->stocks_url = Configuration::get('KASNORMEGAFEED_URL_STOCKS');
        $this->languages = Language::getLanguages();

        parent::init();
    }

    /**
     * Metodo que se ejecuta despues de la inicializacion. Comprueba si existe el metodo solicitado
     * y lo ejecuta si es el caso.
     */
    public function postProcess()
    {

        $action = Tools::getValue("action");

        if ($action == KasnorMegaFeedUpdateModuleFrontController::PRODUCT) {

            $this->processProducts();

        } else if ($action == KasnorMegaFeedUpdateModuleFrontController::STOCKS) {

            $this->processStocks();

        } else {

            throw new Exception("No suitable method");
        }

    }


    /**
     * Insertar nuevos productos (Actualización de productos)
     */
    private function processProducts()
    {

        $result = $this->updateFile(KasnorMegaFeedUpdateModuleFrontController::PRODUCT);

        if (!$result) return false;

        $datas = $this->renderFile($this->updateFile(KasnorMegaFeedUpdateModuleFrontController::PRODUCT));

        if(!isset($datas) || empty($datas) || $datas == false) return false;

        $i = 0;


        foreach ($datas as $data){

            $parent = Configuration::get("KASNORMEGAFEED_CATEGORY_DEFAULT",0);
            if((empty($parent) || $parent == 0)) throw new Exception("No hay categoría por defecto configurada");

            $i++;

            if($i == 6) die();
            $product = new Product($data["id_product"]);

            //si no existe
            if(!Validate::isLoadedObject($product)) {

                $categories_aux = $data["default_category"];

                if(empty($categories_aux)) continue;

                $categories = array_map('trim',(explode('>',$categories_aux)));


                foreach ($categories as $category) {

                    $id_category = $this->getCategoryByName($parent, $category);
                    $parent = $id_category;

                }

            }

        }

    }

    /**
     * Actualizar stocks
     */
    private function processStocks()
    {

        $result = $this->updateFile(KasnorMegaFeedUpdateModuleFrontController::STOCKS);

        if (!$result) return false;

        $data = $this->renderFile($this->updateFile(KasnorMegaFeedUpdateModuleFrontController::STOCKS));

    }

    private function updateFile($type)
    {

        if ($type == KasnorMegaFeedUpdateModuleFrontController::PRODUCT) {

            $file = Tools::file_get_contents($this->products_url);

            if ($file != false) {
                file_put_contents($this->products_path, $file);
                return true;
            } else {
                return false;
            }

        } else if ($type == KasnorMegaFeedUpdateModuleFrontController::STOCKS) {

            $file = Tools::file_get_contents($this->stocks_url);

            if ($file != false) {
                file_put_contents($this->stocks_path, $file);
                return true;
            } else {
                return false;
            }

        } else {

            return false;
        }

    }

    /**
     * Combierte un archivo csv a un array facilmente accesible
     * @param $type
     * @return array|bool
     */
    private function renderFile($type)
    {

        if ($type == KasnorMegaFeedUpdateModuleFrontController::PRODUCT) {

            $file = $this->products_path;

        } else if ($type == KasnorMegaFeedUpdateModuleFrontController::STOCKS) {

            $file = $this->stocks_path;

        } else {

            return false;
        }


        $fila = 1;
        $result = array();
        $keys = array();

        if (($gestor = fopen($file, "r")) !== FALSE) {

            while (($datos = fgetcsv($gestor, 0, ";")) !== FALSE) {

                $numero = count($datos);
                $array = array();
                for ($c = 0; $c < $numero; $c++) {
                    $array[] = $datos[$c];
                }

                if ($fila == 1) {

                    $keys = $array;

                } else {

                    $combine = array_combine($keys, $array);

                    if ($combine != false)
                        $result[] = $combine;

                }

                $fila++;

            }
            fclose($gestor);

            return $result;

        } else return false;

    }

    /**
     * Devuelve el id de la categoría dado el nombre y si no existe la crea
     * @param $parent categoria base de kasnor
     * @param $name nombre de la categoria a buscar
     * @return id_category
     */
    private function getCategoryByName($parent, $name) {


        $name = utf8_encode($name);
        //obtengo todas las cateogi
        $categories_aux = Category::getChildren($parent,Context::getContext()->language->id);



        if(!isset($categories_aux) || empty($categories_aux)){

            $languages_name = array();
            $links_rewrite = array();

            foreach ($this->languages as $language){
                $languages_name[$language['id_lang']] = $name;
            }

            foreach ($this->languages as $language){
                $links_rewrite[$language['id_lang']] = Tools::link_rewrite($name);
            }

            $category = new CategoryCore();
            $category->id_parent = $parent;
            $category->name = $languages_name;
            $category->active = true;
            $category->link_rewrite = $links_rewrite;
            $category->save();

            return $category->id;
        }

        //busco la solicitada por nombre
        $result = array_filter($categories_aux, function($category) use ($name){
            return  $category["name"] == $name;
        });


        var_dump($result);

        $result = reset($result);




        //si no existe creo una categoria nueva
        if(!isset($result) || empty($result)) {

            $languages_name = array();
            $links_rewrite = array();

            foreach ($this->languages as $language){
                $languages_name[$language['id_lang']] = $name;
            }

            foreach ($this->languages as $language){
                $links_rewrite[$language['id_lang']] = Tools::link_rewrite($name);
            }

            $category = new CategoryCore();
            $category->id_parent = $parent;
            $category->name = $languages_name;
            $category->active = true;
            $category->link_rewrite = $links_rewrite;
            $category->save();

            return $category->id;


        }

        return $result["id_category"];

    }


}