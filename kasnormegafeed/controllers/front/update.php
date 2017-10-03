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

    private $category_tree = array();

    private $category_tree_branch = array();

    /**
     * Init
     */
    public function init()
    {
        $this->products_url = Configuration::get('KASNORMEGAFEED_URL_PRODUCT');

        $this->stocks_url = Configuration::get('KASNORMEGAFEED_URL_STOCKS');

        $this->languages = Language::getLanguages();

        $parent = Configuration::get("KASNORMEGAFEED_CATEGORY_DEFAULT", 0);

        if ((empty($parent) || $parent == 0)) throw new Exception("No hay categoría por defecto configurada");

        $this->upTree($parent); // iniciarlizar arbol de categorias


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

        if (!isset($datas) || empty($datas) || $datas == false) return false;

        $parent = Configuration::get("KASNORMEGAFEED_CATEGORY_DEFAULT", 0);

        if ((empty($parent) || $parent == 0)) throw new Exception("No hay categoría por defecto configurada");

        foreach ($datas as $data) {

            $parent = Configuration::get("KASNORMEGAFEED_CATEGORY_DEFAULT", 0); // hace falta reiniciarlo cada vez

            $product = new Product($data["id_product"]);

            //si no existe
            if (!Validate::isLoadedObject($product)) {

                $categories_aux = $data["default_category"];

                if (empty($categories_aux)) continue;

                $categories = array_map('trim', (explode('>', $categories_aux)));


                foreach ($categories as $category) {

                    $id_category = $this->getOrCreateCategoryByName($parent, $category);
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
    private function getOrCreateCategoryByName($parent, $name)
    {

        $name = utf8_encode($name);

        //hijos del padre
        $childs = $this->category_tree[$parent]['childs'];

        //si no existe el hijo lo creo y lo añado al array de hijos
        if (!in_array($name, $childs)) {

            $id = $this->createCategory($name, $parent);

            $this->category_tree[$parent]['childs'][$id] = $name;

            return $id;
        } //si existe devuelvo el id del hijo
        else {

            return (array_search($name, $childs = $this->category_tree[$parent]['childs']));
        }

    }


    /**
     * Crea una nueva categoria
     * @param $name
     * @param $parent
     * @return mixed
     */
    public function createCategory($name, $parent)
    {

        $languages_name = array();

        $links_rewrite = array();

        foreach ($this->languages as $language) {
            $languages_name[$language['id_lang']] = $name;
        }

        foreach ($this->languages as $language) {

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


    /**
     * Inicializa el array de categorias ya creadas
     * @param $parent
     */
    public function upTree($parent)
    {
        $childrens = $this->getChildren($parent, Context::getContext()->language->id);

        $category = new Category($parent, Context::getContext()->language->id);

        $this->category_tree[$category->id]['name'] = $category->name;

        foreach ($childrens as $children) {

            $this->category_tree[$category->id]['childs'][$children['id_category']] = $children['name'];

            $this->upTree($children['id_category']);

        }
    }


    /**
     * Obtiene las categorias que son hijas del padre pasado
     * @param $id_parent
     * @param $id_lang
     * @param bool $active
     * @param bool $id_shop
     * @return array|false|mysqli_result|null|PDOStatement|resource
     */
    public function getChildren($id_parent, $id_lang, $active = true, $id_shop = false)
    {
        if (!Validate::isBool($active)) {
            die(Tools::displayError());
        }

        $query = 'SELECT c.`id_category`, cl.`name`, cl.`link_rewrite`, category_shop.`id_shop`
			FROM `' . _DB_PREFIX_ . 'category` c
			LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (c.`id_category` = cl.`id_category`' . Shop::addSqlRestrictionOnLang('cl') . ')
			' . Shop::addSqlAssociation('category', 'c') . '
			WHERE `id_lang` = ' . (int)$id_lang . '
			AND c.`id_parent` = ' . (int)$id_parent . '
			' . ($active ? 'AND `active` = 1' : '') . '
			GROUP BY c.`id_category`
			ORDER BY category_shop.`position` ASC';
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        return $result;

    }


}