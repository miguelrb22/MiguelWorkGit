<?php
/**
 * @author Miguel Ruiz
 */


require_once(_PS_MODULE_DIR_ . '/pqkasnormegafeedorders/exceptions/customexceptions.php');
require_once(_PS_MODULE_DIR_ . '/pqkasnormegafeedorders/lib/loghelper.php');
require_once(_PS_MODULE_DIR_ . '/pqkasnormegafeedorders/classes/PQKasnorMegaFeedOrderUrl.php');


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

        try {

            $email = $this->data["email"];
            $address = $this->data["address"];
            $products = $this->data["products"];

            if (isset($email) && !empty($email) && isset($address) && !empty($address) && isset($products) && !empty($products)) {

                $customer = $this->getCustomerByEmail($email);

                if (Validate::isLoadedObject($customer)) {

                    $_address = $this->getAdressByAlias($email . $address["id"]);

                    if (!Validate::isLoadedObject($_address)) {

                        $iso_country = $address["iso_country"];

                        $iso_state = $address["iso_state"];

                        $country = $this->getCountryByIso($iso_country);

                        $state = $this->getStateByIso($address["id_country"], $iso_state);

                        if (!Validate::isLoadedObject($country)) throw new ISOCountryNotFoundException("Error");

                        if (!Validate::isLoadedObject($state)) throw new ISOStateNotFoundException("Error");


                        $_address = new Address();
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


                        Context::getContext()->cart = $cart;

                        //TODO BERTO ESTO HACE FALTA?
                        //Añadimos vales descuento
                        /*$context = Context::getContext()->cloneContext();
                        $context->cart = $cart;
                        Cache::clean('getContextualValue_*');
                        CartRule::autoAddToCart($context);*/

                        //Validamos el pedido
                        $payment_module = new LoaderOrder();
                        $orderCreated = $payment_module->validateOrder(
                            (int)$cart->id, 14,
                            $cart->getOrderTotal(true, Cart::BOTH), $payment_module->displayName, 'Loader Order', array(), null, false, $cart->secure_key
                        );


                        //si el pedido se crea correctamente
                        if ($orderCreated) {

                            $order = $this->orderByCart($cart->id);

                            if(isset($order)){

                                //1- Se genera la url de pago
                                $payment_url = $this->generatePaymentUrl($order);

                                $customer = new Customer($order->id_customer);

                                //2- Se envia un email
                                $mailed =  Mail::Send($this->context->language->id, 'dropshipping_confirmation', Mail::l('New Order Created', $this->context->language->id), array('{url}' => $payment_url), $email, $customer->firstname, null, "Kasnor", null, null, _PS_MAIL_DIR_, false, $this->context->shop->id);

                                // si se ha enviado el email se inserta la url con su id order
                                if($mailed){

                                    $order_url = new PQKasnorMegaFeedOrderUrl();
                                    $order_url->url = $payment_url;
                                    $order_url->id_order = $order->id;
                                    $order_url->save();

                                }
                            }
                        }


                    } else {

                        throw new KasnorCustomerNotAllowedException("Error");
                    }
                } else {

                    throw new KasnorCustomerNotAllowedException("Error");
                }
            }

        } catch (AddressNotValidException $e) {

            LogHelper::Log("Error", "El usuario {$email} ha entrado con un pedido, pero la direccion no se encuentra o no se ha podido crear " . json_encode($address));

        } catch (KasnorCustomerNotAllowedException $e) {

            LogHelper::Log("Error", "El usuario {$email} ha entrado con un pedido, pero este usuario no se encuentra como cliente Kasnor", $e->getPrevious());

        } catch (ISOCountryNotFoundException $e) {

            LogHelper::Log("Error", "El usuario {$email} ha intentado obtener el ISO del pais  {$iso_country} y este no se ha encontrado", $e->getPrevious());

        } catch (ISOStateNotFoundException $e) {

            LogHelper::Log("Error", "El usuario {$email} ha intentado obtener el ISO del estado {$iso_state} y este no se ha encontrado");

        } catch (Exception $e) {

            LogHelper::Log("Error", $e->getMessage());
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


    public function getCountryByIso($iso)
    {

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

    public function getStateByIso($id_country, $iso)
    {

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

    /**
     * Devuelve el orden del carrito
     * @param $cart
     * @return null|Order
     */
    public function orderByCart($cart)
    {

        $order = Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'orders` WHERE `id_cart` = ' . (int)$cart);

        if (isset($order) && !empty($order)) {

            return new Order($order['id_order']);
        }

        return null;

    }

    private function generatePaymentUrl($order)
    {

        $customer = new Customer($order->id_customer);
        $prefix = _PS_BASE_URL_. __PS_BASE_URI__."module/redsysdeferred/payment?";
        $a = "a=" . $order->total_paid_tax_incl;
        $c = "&c=" . $order->id_currency;
        $n = "&n=" . $customer->firstname;
        $d = "&d=" . "Dropshipping";
        $m = "&m=" . $customer->email;
        $token = "&z=" . sha1(uniqid('kasnor'))."-" . $order->id;

        $url = $prefix.$a.$c.$n.$d.$m.$token;

        return $url;
    }
}

class LoaderOrder extends PaymentModule
{
    public $active = 1;
    public $name = 'dropshipping_order';

    public function __construct()
    {
        $this->displayName = $this->l('Dropshipping order');
    }
}