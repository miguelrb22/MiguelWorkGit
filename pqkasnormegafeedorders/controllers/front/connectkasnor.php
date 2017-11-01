<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of connectkasnor
 *
 * @author dani
 */
class PqkasnormegafeedordersConnectkasnorModuleFrontController extends ModuleFrontController
{

    protected $data = array();

    public function __construct()
    {

        parent::__construct();

    }


    public function init()
    {

        $data = Tools::getValue("data");

        $data = json_decode($data, true);

        $this->data = $data;

        if (_PS_VERSION_ > "1.7.0") {

            $this->setTemplate('module:pqkasnormegafeedorders/views/templates/front/result.tpl');
        }

        parent::init();
    }


    public function postProcess()
    {


        $email = $this->data["email"];
        $address = $this->data["address"];
        $products = $this->data["products"];

        if (isset($email) && !empty($email) && isset($address) && !empty($address) && isset($products) && !empty($products)) {

            $customer = $this->getCustomerByEmail($email);

            if (Validate::isLoadedObject($customer)) {

                $_address = $this->getAdressByAlias($email . $address["id"]);

                if ($_address == null) {

                    $_address = new AddressCore();
                    $_address->id_customer = $customer->id;
                    $_address->alias = $email . $address["id"];
                    $_address->country = $address["country"];
                    $_address->id_country = $address["id_country"];
                    $_address->id_state = $address["id_state"];
                    $_address->other = $address["other"];
                    $_address->lastname = $address["lastname"];
                    $_address->firstname = $address["firstname"];
                    $_address->address1 = $address["address1"];
                    $_address->address2 = $address["address2"];
                    $_address->postcode = $address["postcode"];
                    $_address->city = $address["city"];
                    $_address->phone = $address["phone"];
                    $_address->phone_mobile = $address["phone_mobile"];
                    $_address->dni = $address["dni"];
                    $_address->
                    $_address->save();

                }

                if (Validate::isLoadedObject($_address)) {


                    //Creamos un nuevo carro
                    $cart = new Cart();
                    //Le ponemos los datos
                    $cart->id_currency = (new Currency(Configuration::get('PS_CURRENCY_DEFAULT')))->id;
                    $cart->id_shop = Context::getContext()->shop->id;
                    $cart->id_shop_group = Context::getContext()->shop->id_shop_group;
                    $cart->id_customer = $customer->id;
                    $cart->id_address_delivery = $_address->id;
                    $cart->id_address_invoice = $_address->id;


                    if ($cart->id_customer) {
                        $customer = new Customer($customer->id);
                        $cart->secure_key = $customer->secure_key;
                    }
                    //Guardamos el carro
                    $cart->save();

                    foreach ($products as $product) {

                        $reference = $product["reference"];
                        $quantity = $product["quantity"];
                        //$reference = str_replace("KAS","",$reference); // TODO DESCOMENTAR EN PRODUCCION

                        $_product = $this->getProductByReference($reference);

                        if ($_product != null) {

                           $cart->updateQty($quantity, $_product->id);

                        }
                    }

                    //TODO BERTO ESTO HACE FALTA?
                    //Añadimos vales descuento
                    /*$context = Context::getContext()->cloneContext();
                    $context->cart = $cart;
                    Cache::clean('getContextualValue_*');
                    CartRule::autoAddToCart($context);*/

                    Context::getContext()->cart = $cart;
                    //Validamos el pedido
                    $payment_module = new LoaderOrder();
                    $payment_module->validateOrder(
                        (int)$cart->id, 2,
                        $cart->getOrderTotal(true, Cart::BOTH), $payment_module->displayName, 'Loader Order', array(), null, false, $cart->secure_key
                    );

                }
            }
        }
    }

    /**
     * Devuelve el usuario que pertenece el email y si esta en el grupo de kasnor
     * @param $email
     * @return array|false|mysqli_result|null|PDOStatement|resource
     */
    public function getCustomerByEmail($email)
    {

        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . 'customer`
                WHERE `email` = \'' . pSQL($email) . '\'
                AND `id_default_group` = ' . pSQL(Configuration::get("KASNORMEGAFEEDORDER_USER_GROUP")) . '            
                ';

        $user = Db::getInstance()->executeS($sql);

        if (isset($user) && !empty($user)) {

            return new Customer($user[0]['id_customer']);
        }
        return null;
    }


    /**
     * Devuelve una direccion por su alias
     * @param $alias
     * @return array|false|mysqli_result|null|PDOStatement|resource
     */
    public function getAdressByAlias($alias)
    {

        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . 'address`
                WHERE `alias` = \'' . pSQL($alias) . '\'
                ';

        $address = Db::getInstance()->executeS($sql);

        if (isset($address) && !empty($address)) {

            return new Address($address[0]['id_address']);
        }
        return null;
    }

    public function getProductByReference($reference)
    {


        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . 'product`
                WHERE `reference` = \'' . pSQL($reference) . '\'
                ';

        $product = Db::getInstance()->executeS($sql);

        if (isset($product) && !empty($product)) {

            return new Product($product[0]['id_product']);
        }
        return null;
    }


    public function getCountryByIso($iso){

        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . 'country`
                WHERE `iso_code` = \'' . pSQL($iso) . '\'
                ';

        $country = Db::getInstance()->executeS($sql);

        if (isset($country) && !empty($country)) {

            return new Country($country[0]['id_country']);
        }
        return null;
    }

    public function getStateByIso($id_country, $iso){

        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . 'state`
                WHERE `iso_code` = \'' . pSQL($iso) . '\'
                AND `id_country` = ' . pSQL($id_country) . '
                ';

        $state = Db::getInstance()->executeS($sql);

        if (isset($state) && !empty($state)) {

            return new State($state[0]['id_state']);
        }
        return null;

    }
}

class LoaderOrder extends PaymentModule
{
    public $active = 1;
    public $name = 'loader_order';

    public function __construct()
    {
        $this->displayName = $this->l('Loader order');
    }
}