<?php
/**
* Redsys Deferred - Get payments without orders.
*
* This product is licensed for one customer to use in one installation. Site developer has the
* right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade module to newer
* versions in the future.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2015 PrestaShop SA
*  @license   See above
*/

/*
 * Call this controller with BASE_DIR/index.php?fc=module&module=redsysdeferred&controller=payment
 */

class RedsysDeferredPaymentModuleFrontController extends ModuleFrontController
{


    private $id_order = 0; //url de la fila en la bbdd

    public function initContent()
    {
        $errors = array();
        $this->display_column_left = false;
        $this->display_column_right = false;

        parent::initcontent();
        $this->id_order = $this->SaveHistoric();

        // Array config con los datos de configuración
        $config = Configuration::getMultiple(
            array(
                'REDSYS_DEF_MONEDA',
                'REDSYS_DEF_MONEDA2',
                'REDSYS_DEF_MONEDA3',
                'REDSYS_DEF_MAIL_CUSTOMER',
            )
        );

        if (Tools::isSubmit('submitForm')) {
            //Validate fields and redirect to TPV form

            if (Tools::getValue('amount') == '') {
                $errors[] = $this->module->l('You must define the payment amount', 'payment');
            } elseif (!preg_match('/^\d+(?:\.\d{0,2})$/', Tools::getValue('amount'))) {
                $errors[] = $this->module->l('Payment amount format is not valid', 'payment');
            } elseif (Tools::getValue('amount') == 0 || Tools::getValue('amount') == '0.00') {
                $errors[] = $this->module->l('Payment amount can not be 0', 'payment');
            }

            if (!Tools::getValue('name')) {
                $errors[] = $this->module->l('Name is required', 'payment');
            }

            if (!Tools::getValue('description')) {
                $errors[] = $this->module->l('Description is required', 'payment');
            } elseif (Tools::strlen(Tools::getValue('description')) > 125) {
                $errors[] = $this->module->l('Description is too long', 'payment');
            }

            if (Tools::getValue('currency_payment') != Currency::getIdByIsoCodeNum($config['REDSYS_DEF_MONEDA'])
                && Tools::getValue('currency_payment') != Currency::getIdByIsoCodeNum($config['REDSYS_DEF_MONEDA2'])
                && Tools::getValue('currency_payment') != Currency::getIdByIsoCodeNum($config['REDSYS_DEF_MONEDA3'])) {
                $errors[] = $this->module->l('Currency is not valid', 'payment');
            }

            if ($config['REDSYS_DEF_MAIL_CUSTOMER']) {
                if (!Tools::getValue('mail_address')) {
                    $errors[] = $this->module->l('Mail address is required', 'payment');
                } elseif (Tools::getValue('mail_address') && !Validate::isEmail(Tools::getValue('mail_address'))) {
                    $errors[] = $this->module->l('Mail address is not valid', 'payment');
                }
            }

            if (!count($errors)) {

                return $this->module->redirectTPV($this->id_order);
            }
        }

        $currencies = array();
        if (!empty($config['REDSYS_DEF_MONEDA'])) {
            $currencies[] = new Currency(Currency::getIdByIsoCodeNum($config['REDSYS_DEF_MONEDA']));
        }

        if (!empty($config['REDSYS_DEF_MONEDA2'])) {
            $currencies[] = new Currency(Currency::getIdByIsoCodeNum($config['REDSYS_DEF_MONEDA2']));
        }

        if (!empty($config['REDSYS_DEF_MONEDA3'])) {
            $currencies[] = new Currency(Currency::getIdByIsoCodeNum($config['REDSYS_DEF_MONEDA3']));
        }

        $this->context->smarty->assign(array(
            '_errors'       => $errors,
            'currencies'    => $currencies,
            'mail_customer' => $config['REDSYS_DEF_MAIL_CUSTOMER'],
            'amount'        => Tools::getValue('a'),
            'currency_url'  => Tools::getValue('c'),
            'name'          => Tools::getValue('n'),
            'description'   => Tools::getValue('d'),
            'mail'          => Tools::getValue('m'),
            'token'         => Tools::getValue('z'),
        ));

        return $this->setTemplate('payment.tpl');
    }

    /**
     * Guardar una nueva url
     * @return int
     */
    private function SaveHistoric(){

        require_once ($this->module->loadClass()."/classes/RedsysdeferredHistoricUrl.php");
        $hitoric = new RedsysdeferredHistoricUrl();
        return $hitoric->Insert($_SERVER["REQUEST_URI"]);
    }
    
}
