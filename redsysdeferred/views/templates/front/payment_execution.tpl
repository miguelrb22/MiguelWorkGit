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

<div class="warning" style="text-align:center">
    {l s='Transferring to payment gateway, please wait.' mod='redsysdeferred'}
</div>

<form action="{$urltpv_comercio|escape:'htmlall':'UTF-8'}" method="post" id="iupay_form" class="hidden" accept-charset="ISO-8859-1">
    <input type="hidden" name="Ds_SignatureVersion" value="{$signatureVersion|escape:'htmlall':'UTF-8'}" />
    <input type="hidden" name="Ds_MerchantParameters" value="{$parameter_iupay|escape:'htmlall':'UTF-8'}" />
    <input type="hidden" name="Ds_Signature" value="{$signature|escape:'htmlall':'UTF-8'}" />
</form>
<script>
    document.getElementById("redsysForm").submit();
</script>
