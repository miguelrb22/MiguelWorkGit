<?php
/**
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}


$pq_lib_path = dirname(__FILE__).'/lib/PqKasnormegafeedLib.php';
if (!file_exists($pq_lib_path)) {
    exit;
}
require_once $pq_lib_path;

class Kasnormegafeed extends Module
{
    protected $config_form = false;

    protected $url = "http://localhost/prestashop6/es/module/pqkasnormegafeedorders/connectkasnor";


    public function __construct()
    {
        $this->name = 'kasnormegafeed';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Prestaquality';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Kasnor Mega Feed');
        $this->description = $this->l('Module to synchronize Kasnor products in your store');

        $this->confirmUninstall = $this->l('Are you sure?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
         try {
            $correct = parent::install();

            if($correct)
            {
                 $correct = $correct &&
                    $this->registerHook('actionValidateOrder');
            }

            return $correct;
         } catch (Exception $ex)
         {
              PqKasnormegafeedLib::logException($ex);
         }
    
    }
    
    public function uninstall()
    {

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {

        //ddd($this->context->link->getModuleLink('kasnormegafeed','update',array()));

        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitKasnormegafeedModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }


     /**
     * @param $params
     * evento para cuando se valida un pedido
     */
    public function hookActionValidateOrder($params)
    {
        //VARIABLES PARA ENVIAR A KASNOR
        $reference = "";
        $quantity = "";
        $email = Configuration::get('KASNORMEGAFEED_EMAIL_USER');
        $address = "" ;
        $json_array = array();


        // COMPROBAMOS QUE NO SEA NULL EL CARRITO
        if (!empty($params["cart"])) {

            //RECORREMOS TODOS LOS PRODUCTOS DEL CARRITO
            $index = 0;


            $json_array['email'] = $email;

            $address = new Address((int)$params["cart"]->id_address_delivery);

            $country = new Country($address->id_country);
            $iso_country = $country->iso_code;

            $state = new State($address->id_state);
            $iso_state = $state->iso_code;

            $json_array['address']['id'] = $address->id;
            $json_array['address']['country'] = $address->country;
            $json_array['address']['id_country'] = $address->id_country;
            $json_array['address']['id_state'] = $address->id_state;
            $json_array['address']['iso_country'] = $iso_country;
            $json_array['address']['iso_state'] = $iso_state;
            $json_array['address']['other'] = $address->other;
            $json_array['address']['lastname'] = $address->lastname;
            $json_array['address']['firstname'] = $address->firstname;
            $json_array['address']['address1'] = $address->address1;
            $json_array['address']['address2'] = $address->address2;
            $json_array['address']['postcode'] = $address->postcode;
            $json_array['address']['city'] = $address->city;
            $json_array['address']['phone'] = $address->phone;
            $json_array['address']['phone_mobile'] = $address->phone_mobile;
            $json_array['address']['dni'] = $address->dni;



            foreach($params["cart"]->getProducts() as $value)
            {
                $reference = $value['reference'];
               
                if(strpos($reference,'KAS') !== false)
                {
                    $quantity = $value['cart_quantity'];

                    //AÑADIMOS A EN UN ARRAY TODOS LOS DATOS NECESARIO PARA ENVIAR

                    $product = array('reference' => $reference, 'quantity' => $quantity);
                    $json_array['products'][] = $product;

                }

            }


            //ddd(json_encode($json_array));

            $result = $this->kasnorOrderRequest(json_encode($json_array));

        }
        return false;

    }
    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitKasnormegafeedModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {

        $categories = array();

        $categories_aux = Category::getAllCategoriesName($this->context->language->id);


        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'desc' => $this->l('URL for update products in your store'),
                        'name' => 'KASNORMEGAFEED_URL_PRODUCT',
                        'label' => $this->l('URL Products'),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'name' => 'KASNORMEGAFEED_URL_STOCK',
                        'desc' => $this->l('URL for update stocks in your store'),
                        'label' => $this->l('URL Stocks'),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'name' => 'KASNORMEGAFEED_EMAIL_USER',
                        'desc' => $this->l('Email of the customer for send order to kasnor'),
                        'label' => $this->l('Email customer'),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'select',
                        'name' => 'KASNORMEGAFEED_CATEGORY_DEFAULT',
                        'desc' => $this->l('Default category for Kasnor Prodycts'),
                        'label' => $this->l('Kasnor Category'),
                        'options' => array(
                            'query' => $categories_aux,                           // $options contains the data itself.
                            'id' => 'id_category',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
                            'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'KASNORMEGAFEED_URL_PRODUCT' => Configuration::get('KASNORMEGAFEED_URL_PRODUCT', ''),
            'KASNORMEGAFEED_URL_STOCK' => Configuration::get('KASNORMEGAFEED_URL_STOCK', ''),
            'KASNORMEGAFEED_EMAIL_USER' => Configuration::get('KASNORMEGAFEED_EMAIL_USER', ''),
            'KASNORMEGAFEED_CATEGORY_DEFAULT' => Configuration::get('KASNORMEGAFEED_CATEGORY_DEFAULT', ''),

        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }


    /**
     * Llama a kasnor para crear el pedido
     * @param $url
     * @param $order
     * @return mixed
     */
    private function kasnorOrderRequest($order){

        //http://localhost/prestashop6/es/module/pqkasnormegafeedorders/connectkasnor?data={%22email%22:%22miguel@prestquality.com%22,%22address%22:{%22id%22:5,%22country%22:%22Espa\u00f1a%22,%22id_country%22:%226%22,%22id_state%22:%22316%22,%22iso_country%22:%22ES%22,%22iso_state%22:%22ES-A%22,%22other%22:%22%22,%22lastname%22:%22Ruiz%22,%22firstname%22:%22Miguek%22,%22address1%22:%22qdwqd%22,%22address2%22:%22%22,%22postcode%22:%2203370%22,%22city%22:%22Redovan%22,%22phone%22:%22666666666%22,%22phone_mobile%22:%22666666666%22,%22dni%22:%2248642143H%22},%22products%22:[{%22reference%22:%22KAS8023820%22,%22quantity%22:%221%22}]}        //http://www.prestashop.local/prestashop/es/module/pqkasnormegafeedorders/connectkasnor?data={%22email%22:%22miguel@prestaquality.com%22,%22address%22:{%22id%22:5,%22country%22:%22Espa%C3%B1a%22,%22lastname%22:%22Ruiz%22,%22firstname%22:%22Miguek%22,%22address1%22:%22qdwqd%22,%22address2%22:%22%22,%22postcode%22:%2203370%22,%22city%22:%22Redovan%22,%22phone%22:%22666666666%22,%22phone_mobile%22:%22666666666%22,%22dni%22:%2248642143H%22},%22products%22:[{%22reference%22:%22KAS000478%22,%22quantity%22:%221%22}]}
        $data = array("order" => $order);

        $ch = curl_init( $this->url );
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec( $ch );

        curl_close($ch);

        return $response;
    }


}
