{**
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
*}

<div class="redsysdeferred_ko">
    <h1>{l s='Your credit card payment could not be accomplished' mod='redsysdeferred'}</h1>
    <p>
        {l s='We are sorry, but your payment has not been successfully accomplished. Please try again or contact us.' mod='redsysdeferred'}
    </p>
    <br />
    <p>
        <a href="{$link->getModuleLink('redsysdeferred', 'payment')|escape:'htmlall':'UTF-8'}" alt="{l s='Return to payment page' mod='redsysdeferred'}" title="{l s='Return to payment page' mod='redsysdeferred'}" class="button_large">{l s='Return to payment page' mod='redsysdeferred'}</a>
    </p>
</div>