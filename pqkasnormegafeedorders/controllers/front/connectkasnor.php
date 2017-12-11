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
            $external_reference = $this->data["reference"];

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
                        $_address->id_country = $country->id;
                        $_address->id_state = $state->id;
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
                            $reference = str_replace("KAS","",$reference); // TODO DESCOMENTAR EN PRODUCCION

                            $_product = $this->getProductByReference($reference);

                            if ($_product != null) {

                               $aux = $cart->updateQty($quantity, $_product['product'], $_product['ipa']);
                               if($aux == false){

                                  $lang = $this->context->language->id;
                                  $product_ws = new Product($_product['product']);
                                  throw new NotStockException("{$product_ws->name[$lang]} {$product_ws->reference}");

                               }

                            }
                        }

                        $this->context->cart = $cart;

                        $this->context->country = new Country($_address->id_country);

                        //Context::getContext()->cart = $cart;

                        //Context::getContext()->country = new Country($_address->id_country);


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

                                $virtual_product = true;

                               $product_var_tpl_list = array();

                               foreach ($this->context->cart->getProducts() as $product) {
                                   $price = Product::getPriceStatic((int)$product['id_product'], false, ($product['id_product_attribute'] ? (int)$product['id_product_attribute'] : null), 6, null, false, true, $product['cart_quantity'], false, (int)$order->id_customer, (int)$order->id_cart, (int)$order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
                                   $price_wt = Product::getPriceStatic((int)$product['id_product'], true, ($product['id_product_attribute'] ? (int)$product['id_product_attribute'] : null), 2, null, false, true, $product['cart_quantity'], false, (int)$order->id_customer, (int)$order->id_cart, (int)$order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});

                                   $product_price = Product::getTaxCalculationMethod() == PS_TAX_EXC ? Tools::ps_round($price, 2) : $price_wt;

                                   $product_var_tpl = array(
                                       'reference' => $product['reference'],
                                       'name' => $product['name'].(isset($product['attributes']) ? ' - '.$product['attributes'] : ''),
                                       'unit_price' => Tools::displayPrice($product_price, $this->context->currency, false),
                                       'price' => Tools::displayPrice($product_price * $product['quantity'], $this->context->currency, false),
                                       'quantity' => $product['quantity'],
                                       'customization' => array()
                                   );

                                   $customized_datas = Product::getAllCustomizedDatas((int)$order->id_cart);
                                   if (isset($customized_datas[$product['id_product']][$product['id_product_attribute']])) {
                                       $product_var_tpl['customization'] = array();
                                       foreach ($customized_datas[$product['id_product']][$product['id_product_attribute']][$order->id_address_delivery] as $customization) {
                                           $customization_text = '';
                                           if (isset($customization['datas'][Product::CUSTOMIZE_TEXTFIELD])) {
                                               foreach ($customization['datas'][Product::CUSTOMIZE_TEXTFIELD] as $text) {
                                                   $customization_text .= $text['name'].': '.$text['value'].'<br />';
                                               }
                                           }

                                           if (isset($customization['datas'][Product::CUSTOMIZE_FILE])) {
                                               $customization_text .= sprintf(Tools::displayError('%d image(s)'), count($customization['datas'][Product::CUSTOMIZE_FILE])).'<br />';
                                           }

                                           $customization_quantity = (int)$product['customization_quantity'];

                                           $product_var_tpl['customization'][] = array(
                                               'customization_text' => $customization_text,
                                               'customization_quantity' => $customization_quantity,
                                               'quantity' => Tools::displayPrice($customization_quantity * $product_price, $this->context->currency, false)
                                           );
                                       }
                                   }

                                   $product_var_tpl_list[] = $product_var_tpl;
                                   // Check if is not a virutal product for the displaying of shipping
                                   if (!$product['is_virtual']) {
                                       $virtual_product &= false;
                                   }
                               } // end foreach ($products)

                                 $product_list_txt = '';
                                 $product_list_html = '';
                                 if (count($product_var_tpl_list) > 0) {
                                     $product_list_txt = $this->getEmailTemplateContent('order_conf_product_list.txt', Mail::TYPE_TEXT, $product_var_tpl_list);
                                     $product_list_html = $this->getEmailTemplateContent('order_conf_product_list.tpl', Mail::TYPE_HTML, $product_var_tpl_list);
                                 }



                                $template_vars = array (

                                    '{reference}' => $external_reference,
                                    '{firstname}' => $customer->firstname,
                                    '{lastname}' => $customer->lastname,
                                    '{url}' => $payment_url,
                                    '{order_name}' => $order->getUniqReference(),
                                    '{date}' => Tools::displayDate(date('Y-m-d H:i:s'), null, 1),
                                    '{products}' => $product_list_html,
                                    '{total_shipping}' => Tools::displayPrice($order->total_shipping, $this->context->currency, false),
                                    '{total_wrapping}' => Tools::displayPrice($order->total_wrapping, $this->context->currency, false),
                                    '{total_discounts}' => Tools::displayPrice($order->total_discounts, $this->context->currency, false),
                                    '{total_paid}' => Tools::displayPrice($order->total_paid, $this->context->currency, false),
                                    '{total_products}' => Tools::displayPrice(Product::getTaxCalculationMethod() == PS_TAX_EXC ? $order->total_products : $order->total_products_wt, $this->context->currency, false),
                                    '{total_tax_paid}' => Tools::displayPrice(($order->total_products_wt - $order->total_products) + ($order->total_shipping_tax_incl - $order->total_shipping_tax_excl), $this->context->currency, false)

                                );
                                //2- Se envia un email
                                $mailed = Mail::Send($this->context->language->id, 'dropshipping_confirmation', Mail::l('New Dropshipping Order Created', $this->context->language->id), $template_vars, $email, $customer->firstname, 'info@kasnor.com', 'KASNOR', null, null, _PS_MODULE_DIR_ . '/pqkasnormegafeedorders/views/templates/mails/');
                                // si se ha enviado el email se inserta la url con su id order
                                if($mailed){

                                    $order_url = new PQKasnorMegaFeedOrderUrl();
                                    $order_url->url = $payment_url;
                                    $order_url->id_order = $order->id;
                                    $order_url->save();

                                }

                                LogHelper::Log("Order Created", "{$order->reference} for user {$email}");

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

        } catch (NotStockException $e) {

            LogHelper::Log("Error", "El usuario {$email} ha entrado con un pedido, pero el producto {$e->getMessage()} no se ha podido añadir");

        } catch (KasnorCustomerNotAllowedException $e) {

            LogHelper::Log("Error", "El usuario {$email} ha entrado con un pedido, pero este usuario no se encuentra como cliente Kasnor", $e->getPrevious());

        } catch (ISOCountryNotFoundException $e) {

            LogHelper::Log("Error", "El usuario {$email} ha intentado obtener el ISO del pais  {$iso_country} y este no se ha encontrado", $e->getPrevious());

        } catch (ISOStateNotFoundException $e) {

            LogHelper::Log("Error", "El usuario {$email} ha intentado obtener el ISO del estado {$iso_state} y este no se ha encontrado");

        } catch (Exception $e) {

            LogHelper::Log("Error", $e->getMessage(). " usuario {$email}");
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

        $result = array();
        $result['product'] = null;
        $result['ipa'] = null;

        //primero se busca por combinacion
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'product_attribute` WHERE `reference` = \'' . pSQL($reference) . '\'';

        $product = Db::getInstance()->executeS($sql);

        //si se ha encontrado el producto se devuelve
        if (isset($product) && !empty($product)) {

          $result['product'] = $product[0]['id_product'];
          $result['ipa'] =  $product[0]['id_product_attribute'];
          return $result;
        }

        //si no se busca por producto

        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'product` WHERE `reference` = \'' . pSQL($reference) . '\'';
        $product = Db::getInstance()->executeS($sql);

        //si se ha encontrado el producto se devuelve
        if (isset($product) && !empty($product)) {

            $result['product'] =  $product[0]['id_product'];
            return $result;

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
        $prefix = _PS_BASE_URL_. __PS_BASE_URI__."controller/redsysdeferred/payment?";
        $a = "a=" . $order->total_paid_tax_incl;
        $c = "&c=" . $order->id_currency;
        $n = "&n=" . $customer->firstname;
        $d = "&d=" . "Dropshipping";
        $m = "&m=" . $customer->email;
        $token = "&z=" . sha1(uniqid('kasnor'))."-" . $order->id;

        $url = $prefix.$a.$c.$n.$d.$m.$token;

        return $url;
    }

    protected function getEmailTemplateContent($template_name, $mail_type, $var)
   {
       $email_configuration = Configuration::get('PS_MAIL_TYPE');
       if ($email_configuration != $mail_type && $email_configuration != Mail::TYPE_BOTH) {
           return '';
       }
       $theme_template_path = _PS_THEME_DIR_.'mails'.DIRECTORY_SEPARATOR.$this->context->language->iso_code.DIRECTORY_SEPARATOR.$template_name;
       $default_mail_template_path = _PS_MAIL_DIR_.$this->context->language->iso_code.DIRECTORY_SEPARATOR.$template_name;
       if (Tools::file_exists_cache($theme_template_path)) {
           $default_mail_template_path = $theme_template_path;
       }
       if (Tools::file_exists_cache($default_mail_template_path)) {
           $this->context->smarty->assign('list', $var);
           return $this->context->smarty->fetch($default_mail_template_path);
       }
       return '';
   }
}

class LoaderOrder extends PaymentModule
{
    public $active = 1;
    public $name = 'dropshipping_order';

    public function __construct()
    {
        $this->displayName = $this->l('Dropshipping order');
        parent::__construct();
    }
}
