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

class ModuleFrontController extends ModuleFrontControllerCore
{
    public function initContent()
    {
        if (Tools::isSubmit('module') && Tools::getValue('controller') == 'payment' && Tools::getValue('module') == 'redsysdeferred') {
            FrontController::initContent();
        } else {
            parent::initContent();
        }
    }
}
