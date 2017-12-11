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
/* SSL Management */
$useSSL = true;


include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include_once(dirname(__FILE__).'/redsysdeferred.php');

$errors = array();

/* Configuration array */
$config = Configuration::getMultiple(
    array(
        'REDSYS_DEF_MONEDA',
        'REDSYS_DEF_MONEDA2',
        'REDSYS_DEF_MONEDA3',
    )
);

if (Tools::isSubmit('submitForm')) {
    /* Validate fields and redirect to TPV form */
    if (Tools::getValue('amount') == '') {
        $errors[] = Tools::displayError('You must define the payment amount');
    } elseif (!preg_match('/^\d+(?:\.\d{0,2})$/', Tools::getValue('amount'))) {
        $errors[] = Tools::displayError('Payment amount format is not valid');
    } elseif (Tools::getValue('amount') == 0 || Tools::getValue('amount') == '0.00') {
        $errors[] = Tools::displayError('Payment amount can not be 0');
    }

    if (Tools::getValue('name') == '') {
        $errors[] = Tools::displayError('You must define a name');
    }

    if (Tools::getValue('description') == '') {
        $errors[] = Tools::displayError('You must define a description');
    }

    if (Tools::strlen(Tools::getValue('description')) > 125) {
        $errors[] = Tools::displayError('Description is too long');
    }

    if (Tools::getValue('currency_payment') != Currency::getIdByIsoCodeNum($config['REDSYS_DEF_MONEDA'])
        && Tools::getValue('currency_payment') != Currency::getIdByIsoCodeNum($config['REDSYS_DEF_MONEDA2'])
        && Tools::getValue('currency_payment') != Currency::getIdByIsoCodeNum($config['REDSYS_DEF_MONEDA3'])) {
        $errors[] = Tools::displayError('Currency is not valid');
    }

    if (!count($errors)) {

        $redsysdeferred = new RedsysDeferred();
        return $redsysdeferred->redirectTPV();
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

$smarty->assign(array(
    'PS_SHOP_NAME'  => Configuration::get('PS_SHOP_NAME'),
    '_errors'       => $errors,
    'currencies'    => $currencies,
    'mail_customer' => Configuration::get('REDSYS_DEF_MAIL_CUSTOMER'),
    'amount'        => Tools::getValue('a'),
    'currency_url'  => Tools::getValue('c'),
    'name'          => Tools::getValue('n'),
    'description'   => Tools::getValue('d'),
    'mail'          => Tools::getValue('m'),
));

$smarty->display(dirname(__FILE__).'/views/templates/front/payment.tpl');

include(dirname(__FILE__).'/../../footer.php');