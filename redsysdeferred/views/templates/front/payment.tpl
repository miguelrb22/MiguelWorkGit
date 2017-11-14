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

<script type="text/javascript">
{literal}
function validateForm() {
    amount = document.getElementById('amount').value;
    amount = parseFloat(Math.round(amount.replace(",",".") * 100) / 100).toFixed(2);
    var regex  = /^\d+(?:\.\d{0,2})$/;
    if (!regex.test(amount)) {
        {/literal}alert("{l s='Invalid amount' mod='redsysdeferred'}");{literal}
        $('#submitForm').show();
        return false;
    }
    if (amount == 0.00) {
        {/literal}alert("{l s='Amount can not be 0' mod='redsysdeferred'}");{literal}
        $('#submitForm').show();
        return false;
    }
    document.getElementById('amount').value = amount;

    {/literal}{if $mail_customer}{literal}
    mail = document.getElementById('mail_address').value;
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (!re.test(mail)) {
        {/literal}alert("{l s='Mail address is not valid' mod='redsysdeferred'}");{literal}
        $('#submitForm').show();
        return false;
    }
    {/literal}{/if}{literal}

    return true;
}
{/literal}
</script>

<div class="redsysdeferred">
    <h1 class="page-heading">{l s='Payment to' mod='redsysdeferred'} {$PS_SHOP_NAME|escape:'htmlall':'UTF-8'}</h1>

    {if $_errors|@count > 0}
    <div class="alert error alert-danger">
        <p>{l s='There are errors:' mod='redsysdeferred'}</p>
        <ol>
            {foreach from=$_errors item=error}
                <li>{$error|escape:'htmlall':'UTF-8'}</li>
            {/foreach}
        </ol>
    </div>
    {/if}

    <h4>{l s='Make a payment' mod='redsysdeferred'}</h4>

    <form method="post" accept-charset="ISO-8859-1" onsubmit="return validateForm();">
        <fieldset>
            <p class="text">
                <label for="amount">{l s='Amount' mod='redsysdeferred'} <sup>*</sup></label>
                <input type="text" name="amount" id="amount" value="{if isset($smarty.post.amount)}{$smarty.post.amount|escape:'htmlall':'UTF-8'}{elseif isset($amount)}{$amount|escape:'htmlall':'UTF-8'}{/if}" size="10" {if isset($amount) && $amount != ''}readonly{/if}/>
                {if $currencies|@count > 1}
                <select name="currency_payment" style="height:24px" {if isset($currency) && $currency}disabled{/if}>
                    {foreach from=$currencies item=currency}
                        <option value="{$currency->id|intval}" {if (isset($smarty.post.currency_payment) && $smarty.post.currency_payment) || isset($currency_url) && $currency->id == $currency_url['id']}selected{/if}>{$currency->sign|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
                {else}
                    &nbsp;{$currencies[0]->sign|escape:'htmlall':'UTF-8'}
                    <input type="hidden" name="currency_payment" value="{$currencies[0]->id|intval}" />
                {/if}
            </p>
            <p class="text">
                <label for="name">{l s='Name' mod='redsysdeferred'} <sup>*</sup></label>
                <input type="text" name="name" value="{if isset($smarty.post.name)}{$smarty.post.name|escape:'htmlall':'UTF-8'}{elseif isset($name)}{$name|escape:'htmlall':'UTF-8'}{/if}" maxlength="125" size="40" />
            </p>
            {if $mail_customer}
            <p class="text">
                <label for="name">{l s='Mail address' mod='redsysdeferred'} <sup>*</sup></label>
                <input type="text" name="mail_address" id="mail_address" value="{if isset($smarty.post.mail_address)}{$smarty.post.mail_address|escape:'htmlall':'UTF-8'}{elseif isset($mail)}{$mail|escape:'htmlall':'UTF-8'}{/if}" maxlength="125" size="40" />
            </p>
            {/if}

            {if $token}
                <input type="hidden" name="token_payment" value="{$token}" />

            {/if}
            <p class="text">
                <label for="description">{l s='Payment description' mod='redsysdeferred'} <sup>*</sup></label>
                <input type="text" name="description" value="{if isset($smarty.post.description)}{$smarty.post.description|escape:'htmlall':'UTF-8'}{elseif isset($description)}{$description|escape:'htmlall':'UTF-8'}{/if}" maxlength="125" size="40" />
            </p>
            <p class="submit">
                <input type="submit" name="submitForm" id="submitForm" value="{l s='Submit' mod='redsysdeferred'}" class="button_large exclusive" onclick="$(this).hide();">
            </p>
        </fieldset>
    </form>
</div>
