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

    private $attributes_tree = array();

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

        $parent = Configuration::get("KASNORMEGAFEED_CATEGORY_DEFAULT", 0);

        if ((empty($parent) || $parent == 0)) throw new Exception("No hay categoría por defecto configurada");


        if ($action == KasnorMegaFeedUpdateModuleFrontController::PRODUCT) {

            $this->upCategoryTree($parent); // inicializar arbol de categorias

            $this->upAttributesTree();

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

        //Se intenta actualizar el archivo descargandolo del servidor
        $result = $this->updateFile(KasnorMegaFeedUpdateModuleFrontController::PRODUCT);

        if (!$result) return false;

        //se convierte el archivo a un array de datos
        $datas = $this->renderFile($this->updateFile(KasnorMegaFeedUpdateModuleFrontController::PRODUCT));

        if (!isset($datas) || empty($datas) || $datas == false) return false;

        //categoria padre de kasnor
        $parent = Configuration::get("KASNORMEGAFEED_CATEGORY_DEFAULT", 0);

        if ((empty($parent) || $parent == 0)) throw new Exception("No hay categoría por defecto configurada");


        $i = 0;

        //recorremos los datos
        foreach ($datas as $data) {

            $i++;

            $reference = "KAS" . $data['reference'];

            $exist = $this->refInDatabase($reference);

            if (!$exist) {

                $parent = Configuration::get("KASNORMEGAFEED_CATEGORY_DEFAULT", 0); // hace falta reiniciarlo cada vez

                $categories_aux = $data["default_category"];

                if (empty($categories_aux)) continue;

                $categories = array_map('trim', (explode('>', $categories_aux)));


                foreach ($categories as $category) {

                    $id_category = $this->getOrCreateCategoryByName($parent, $category);

                    $parent = $id_category;

                }

                $this->createProduct($data, $parent);
            }

            if ($i > 40) {

                Search::indexation(1);

                die();

            }
        }

        Context::getContext()->shop->setContext(Shop::CONTEXT_SHOP, (int)Tools::getValue('id_shop'));

        Search::indexation(1);

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


    /**
     * Descarga el archivo nuevo de el servidor
     * @param $type
     * @return bool
     */
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

                    $array[] = utf8_encode($datos[$c]);

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
        $childs = @$this->category_tree[$parent]['childs'];

        //si no existe el hijo lo creo y lo añado al array de hijos
        if ($childs == null || !in_array($name, $childs)) {

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
    public function upCategoryTree($parent)
    {
        $childrens = $this->getChildren($parent, Context::getContext()->language->id);

        $category = new Category($parent, Context::getContext()->language->id);

        $this->category_tree[$category->id]['name'] = $category->name;

        foreach ($childrens as $children) {

            $this->category_tree[$category->id]['childs'][$children['id_category']] = $children['name'];

            $this->upCategoryTree($children['id_category']);

        }
    }

    /**
     * Inicializa el arbol de grupos y attributos
     */
    public function upAttributesTree()
    {

        $result = array();

        $groups = AttributeGroupCore::getAttributesGroups(Context::getContext()->language->id, Context::getContext()->shop->id);

        foreach ($groups as $group) {

            $result[$group['id_attribute_group']] = array("name" => $group['name']);

            $attributes = AttributeGroupCore::getAttributes(Context::getContext()->language->id, $group['id_attribute_group']);

            $result[$group['id_attribute_group']]['attributes'] = array();

            foreach ($attributes as $attribute) {

                $result[$group['id_attribute_group']]['attributes'][$attribute['id_attribute']] = $attribute['name'];
            }

        }

        $this->attributes_tree = $result;

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


    /**
     * Comprueba si existe o no una referencia en la BBDD
     * @param $ref
     * @return false|null|string
     */
    public function refInDatabase($ref)
    {
        $sql = 'SELECT id_product FROM ' . _DB_PREFIX_ . 'product WHERE reference = \'' . $ref . '\'';
        $id = Db::getInstance()->getValue($sql);
        return $id;
    }


    /**
     * @param $data
     * @param $parent
     */
    public function createProduct($data, $parent)
    {


        $shop = Context::getContext()->shop->id;
        $product = new ProductCore();

        $languages_name = array();

        $links_rewrite = array();

        $description_languages = array();

        $shot_description_languages = array();


        foreach ($this->languages as $language) {
            $languages_name[$language['id_lang']] = trim($data['name']);
        }

        foreach ($this->languages as $language) {

            $links_rewrite[$language['id_lang']] = Tools::link_rewrite($data['link_rewrite']);
        }

        foreach ($this->languages as $language) {
            $description_languages[$language['id_lang']] = $data['description'];;
        }

        foreach ($this->languages as $language) {

            $shot_description_languages[$language['id_lang']] = $data['description_short'];
        }

        $product->name = $languages_name;
        $product->link_rewrite = $links_rewrite;
        $product->id_category_default = $parent;
        $product->category = $parent;
        $product->price = $data['price'];
        $product->quantity = $data['quantity'];
        $product->wholesale_price = $data['wholesale_price'];
        $product->ean13 = $data['ean13'];
        $product->upc = $data['upc'];
        $product->width = $data['width'];
        $product->weight = $data['weight'];
        $product->height = $data['height'];
        $product->depth = $data['depth'];
        $product->id_shop_default = $shop;
        $product->description = $description_languages;
        $product->description_short = $shot_description_languages;
        $product->reference = "KAS" . $data['reference'];
        $product->visibility = $data['visibility'];

        $product->save();

        $images = array_map('trim', (explode(',', $data['images'])));

        $cover = true;

        foreach ($images as $image) {

            if (!empty($image)) {
                $this->setImage($product->id, array($shop), $image, $cover);
                $cover = false;
            }

        }

        $combinations = json_decode($data['combinations'], true);

        $combination_resume = array();

        dump($combinations);
        foreach ($combinations as $combination) {


            $group_name = $combination['group_name'];
            $group_id = $this->getOrCreateAttributeGroup($group_name, $combination['is_color_group'], $combination['group_type'], $combination['public_group_name']);

            $attribute_name = $combination['attribute_name'];
            $attribute_id = $this->getOrCreateAttribute($group_id, $attribute_name);


            //TODO
            $combination_resume[$group_id][] = $attribute_id;

        }


    }


    /**
     * Devuelve el id del grupo de atributos, si no existe lo cre y lo devuelve
     * @param $group_name
     * @param $is_color
     * @param $group_type
     * @param $public_name
     * @return int
     */
    public function getOrCreateAttributeGroup($group_name, $is_color, $group_type, $public_name)
    {

        $group = array_filter($this->attributes_tree, function ($aux) use ($group_name) {
            return $aux["name"] == $group_name;
        });


        $key = key($group);

        if (!isset($key) || empty($key)) {

            $languages_name = array();

            $languages_public_name = array();


            foreach ($this->languages as $language) {
                $languages_name[$language['id_lang']] = $group_name;
            }

            foreach ($this->languages as $language) {
                $languages_public_name[$language['id_lang']] = $public_name;
            }

            $atgroup = new AttributeGroup();
            $atgroup->name = $languages_name;
            $atgroup->is_color_group = $is_color;
            $atgroup->group_type = $group_type;
            $atgroup->public_name = $languages_public_name;
            $atgroup->save();


            $this->attributes_tree[$atgroup->id] = array("name" => $group_name, "attributes" => array());

            return $atgroup->id;

        }

        return $key;
    }

    /**
     * @param $group
     * @param $name
     */
    public function getOrCreateAttribute($group, $name, $color = 0, $default = 0){


        //atributos del grupo
        $group_in_tree = $this->attributes_tree[$group]['attributes'];


        $attribute = array_filter($group_in_tree, function ($aux) use ($name) {
            return $aux == $name;
        });

        $key = key($attribute);

        if (!isset($key) || empty($key)) {

            $languages_name = array();


            foreach ($this->languages as $language) {
                $languages_name[$language['id_lang']] = $name;
            }


            $atr = new Attribute();
            $atr->name = $languages_name;
            $atr->id_attribute_group = $group;
            $atr->color = $color;
            $atr->default = $default;
            $atr->save();

            $this->attributes_tree[$group]['attributes'][$atr->id] = $name;

            return $atr->id;

        }

        return $key;
    }

    /**
     * Asocia una imagen a un producto
     * @param $id_product
     * @param $shops
     * @param $url
     * @param $cover
     * @return null
     */
    function setImage($id_product, $shops, $url, $cover)
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
    function copyImg($id_entity, $id_image = null, $url, $entity = "products", $regenerate = true)
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
                $path = _PS_CAT_IMG_DIR_ . (int)$id_entity;
                break;
            case "manufacturers":
                $path = _PS_MANU_IMG_DIR_ . (int)$id_entity;
                break;
            case "suppliers":
                $path = _PS_SUPP_IMG_DIR_ . (int)$id_entity;
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
            ImageManager::resize($tmpfile, $path . ".jpg", null, null, "jpg", false, $error, $tgt_width, $tgt_height, 5,
                $src_width, $src_height);
            $images_types = ImageType::getImagesTypes($entity, true);
            if ($regenerate) {
                $previous_path = null;
                $path_infos = array();
                $path_infos[] = array($tgt_width, $tgt_height, $path . ".jpg");
                foreach ($images_types as $image_type) {
                    $tmpfile = get_best_path($image_type["width"], $image_type["height"], $path_infos);
                    if (ImageManager::resize($tmpfile, $path . "-" . stripslashes($image_type["name"]) . ".jpg", $image_type["width"],
                        $image_type["height"], "jpg", false, $error, $tgt_width, $tgt_height, 5,
                        $src_width, $src_height)
                    ) {
                        if ($tgt_width <= $src_width && $tgt_height <= $src_height) {
                            $path_infos[] = array($tgt_width, $tgt_height, $path . "-" . stripslashes($image_type["name"]) . ".jpg");
                        }
                        if ($entity == "products") {
                            if (is_file(_PS_TMP_IMG_DIR_ . "product_mini_" . (int)$id_entity . ".jpg")) {
                                unlink(_PS_TMP_IMG_DIR_ . "product_mini_" . (int)$id_entity . ".jpg");
                            }
                            if (is_file(_PS_TMP_IMG_DIR_ . "product_mini_" . (int)$id_entity . "_" . (int)Context::getContext()->shop->id . ".jpg")) {
                                unlink(_PS_TMP_IMG_DIR_ . "product_mini_" . (int)$id_entity . "_" . (int)Context::getContext()->shop->id . ".jpg");
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


    /**
     * @param $tgt_width
     * @param $tgt_height
     * @param $path_infos
     * @return string
     */
    function get_best_path($tgt_width, $tgt_height, $path_infos)
    {
        $path_infos = array_reverse($path_infos);
        $path = "";
        foreach ($path_infos as $path_info) {
            list($width, $height, $path) = $path_info;
            if ($width >= $tgt_width && $height >= $tgt_height) {
                return $path;
            }
        }
        return $path;
    }


}