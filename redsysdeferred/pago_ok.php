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

/**
 * @deprecated 1.5.0 This file is deprecated, use moduleFrontController instead
 */

/* SSL Management */
$useSSL = true;

require('../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
require_once(dirname(__FILE__).'/../../init.php');

$errors = array();

$smarty->display(dirname(__FILE__).'/views/templates/front/paymentok.tpl');

include(dirname(__FILE__).'/../../footer.php');
