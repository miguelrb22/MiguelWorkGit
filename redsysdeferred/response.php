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

require_once(dirname(__FILE__).'../../../config/config.inc.php');
if (!class_exists("RedsysAPIR")) {
    require_once(dirname(__FILE__).'/apiRedsys/apiRedsysFinal.php');
}
if (!class_exists("RedsysdeferredHistoricUrl")) {
    require_once(dirname(__FILE__)."/classes/RedsysdeferredHistoricUrl.php");
}

if (version_compare(_PS_VERSION_, '1.5', '>=')) {
    if (empty(Context::getContext()->link)) {
        Context::getContext()->link = new Link();
    }
}

if (!empty($_POST)) {
    $miObj = new RedsysAPIR();

    $version = Tools::getValue("Ds_SignatureVersion");
    $datos = Tools::getValue("Ds_MerchantParameters");
    $signatureRecibida = Tools::getValue("Ds_Signature");

    $decodec = $miObj->decodeMerchantParameters($datos);

    $config = Configuration::getMultiple(
        array(
            'REDSYS_DEF_CLAVE',
            'REDSYS_DEF_CLAVE2',
            'REDSYS_DEF_CLAVE3'
        )
    );

    foreach ($config as $key) {
        $firma = $miObj->createMerchantSignatureNotif($key, $datos);
        if ($firma === $signatureRecibida) {
            break;
        }
    }

    $pedido = (int)$miObj->getParameter("Ds_Order");
    $amount = $miObj->getParameter("Ds_Amount");
    $moneda = $miObj->getParameter('Ds_Currency');
    $description = explode('~', urldecode($miObj->getParameter('Ds_MerchantData')));

    /* Signatures match */
    if ($firma === $signatureRecibida) {
        $response = (int)$miObj->getParameter("Ds_Response");

        if ($response < 101) {

            $hitoric = new RedsysdeferredHistoricUrl();
            $hitoric->setPaidOut($pedido);
			
            if (Configuration::get('REDSYS_DEF_MAIL_CUSTOMER')) {
                sendConfirmMailCustomer($amount, $moneda, $description, $miObj);
            }
            sendConfirmMail($amount, $moneda, $description, $miObj);

            

        } else {
            if (Configuration::get('REDSYS_DEF_MAIL_CUSTOMER')) {
                sendErrorMailCustomer($amount, $moneda, $description);
            }

            sendErrorMail($amount, $moneda, $description, $miObj);
        }
    }
} else {
    die('Bad request');
}

function sendConfirmMailCustomer($amount, $moneda, $description, $miObj)
{
    $currency = new Currency(Currency::getIdByIsoCodeNum($moneda));
    $mailsVars = array(
        '{amount}' => number_format($amount / 100, 2, '.', ''),
        '{currency}' => $currency->sign,
        '{name}' => $description[1],
        '{description}' => $description[2],
        '{data}' => $miObj->vars_pay['Ds_AuthorisationCode'],
    );

    Mail::Send(
        (int)Configuration::get('PS_LANG_DEFAULT'),
        'customer_ok',
        Mail::l('Payment accepted'),
        $mailsVars,
        $description[0],
        null,
        null,
        null,
        null,
        null,
        dirname(__FILE__).'/mails/'
    );

    return true;
}

function sendConfirmMail($amount, $moneda, $description, $miObj)
{
    $data = '';
    foreach ($miObj->vars_pay as $a => $b) {
        $data .= Mail::l('Field').': '.$a.' '.Mail::l('Value').': '.$b.'<br />';
    }

    $currency = new Currency(Currency::getIdByIsoCodeNum($moneda));

    $mailsVars = array(
        '{amount}' => number_format($amount / 100, 2, '.', ''),
        '{currency}' => $currency->sign,
        '{data}' => $data,
        '{name}' => $description[1],
        '{mail}' => $description[0],
        '{description}' => $description[2]
    );

    Mail::Send(
        (int)Configuration::get('PS_LANG_DEFAULT'),
        'payment_redsys',
        Mail::l('Payment accepted'),
        $mailsVars,
        Configuration::get('PS_SHOP_EMAIL'),
        null,
        null,
        null,
        null,
        null,
        dirname(__FILE__).'/mails/'
    );

    return true;
}

function sendErrorMailCustomer($amount, $moneda, $description)
{
    $currency = new Currency(Currency::getIdByIsoCodeNum($moneda));

    $mailsVars = array(
        '{amount}' => number_format($amount / 100, 2, '.', ''),
        '{currency}' => $currency->sign,
        '{name}' => $description[1],
        '{description}' => $description[2],
    );

    Mail::Send(
        (int)Configuration::get('PS_LANG_DEFAULT'),
        'customer_ko',
        Mail::l('Payment error'),
        $mailsVars,
        $description[0],
        null,
        null,
        null,
        null,
        null,
        dirname(__FILE__).'/mails/'
    );

    return true;
}

function sendErrorMail($amount, $moneda, $description, $miObj)
{
    $data = '';
    foreach ($miObj->vars_pay as $a => $b) {
        $data .= Mail::l('Field').': '.$a.' '.Mail::l('Value').': '.$b.'<br />';
    }

    $currency = new Currency(Currency::getIdByIsoCodeNum($moneda));

    $mailsVars = array(
        '{amount}' => number_format($amount / 100, 2, '.', ''),
        '{currency}' => $currency->sign,
        '{data}' => $data,
        '{name}' => $description[1],
        '{mail}' => $description[0],
        '{description}' => $description[2]
    );

    Mail::Send(
        (int)Configuration::get('PS_LANG_DEFAULT'),
        'error',
        Mail::l('Payment error'),
        $mailsVars,
        Configuration::get('PS_SHOP_EMAIL'),
        null,
        null,
        null,
        null,
        null,
        dirname(__FILE__).'/mails/'
    );

    return true;
}
