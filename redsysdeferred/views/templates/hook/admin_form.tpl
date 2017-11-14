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

<style type="text/css">
    .nobootstrap {
        min-width: 0 !important;
        padding: 100px 30px 0 !important;
    }
    .nobootstrap .margin-form {
        font-size: 0.9em !important;
    }

    .company {
        border: 1px solid black;
        background-color: #2A2A2A;
        color: #FFF;
        overflow: hidden;
        padding: 20px;
        margin: 15px 0;
    }
    .company a{
        color: white;
        font-weight: bold;
    }
    .company ul {
        margin: 6px 0 12px;
        padding-left: 40px;
        list-style-type: disc;
    }
    .company ul li {
        color: #FFF;
    }
    .company .logo {
        padding-bottom: 10px;
    }
    #redsysdeferred select {
        height: 23px;
        vertical-align: middle;
    }
    #redsysdeferred label {
        clear: both;
    }
    #content .alert {
        width: auto;
    }
</style>

<h2>{$displayName|escape:'htmlall':'UTF-8'} - {l s='Configuration' mod='redsysdeferred'}</h2>

<div class="company">
    <div class="logo">
        <img src="{$rdPath|escape:'htmlall':'UTF-8'}views/img/logo_idnovate.png" title="idnovate.com" alt="idnovate.com" />
    </div>
    <div class="content">
        {l s='We offer you free assistance to install and set up the module. If you have any problem you can contact us at' mod='redsysdeferred'} <a href="http://addons.prestashop.com/contact-community.php?id_product=9104" target="_blank" title="{l s='Contact' mod='redsysdeferred'}">http://addons.prestashop.com/contact-community.php?id_product=9104</a>
    </div>
</div>

{if isset($errors) && $errors|@count > 0}
    <div class="bootstrap">
        <div class="module_confirmation alert error alert-warning">
            <h4>{l s='There is/are error/s:' mod='redsysdeferred'}</h4>
            <ol>
                {foreach from=$errors item=error}
                    <li>{$error|escape:'htmlall':'UTF-8'}</li>
                {/foreach}
            </ol>
        </div>
    </div>
{/if}

{if isset($success) && $success == true}
    <div class="bootstrap">
        <div class="module_confirmation conf confirm alert alert-success">{l s='Settings updated' mod='redsysdeferred'}</div>
    </div>
{/if}

<div style="clear: both"></div>

<div id="tabs">
    {*
    <ul>
        <!--<li>
            <a href="#tabs-1">{l s='Payments' mod='redsysdeferred'}</a>
        </li>-->
        <li>
            <a href="#tabs-2">{l s='Module configuration' mod='redsysdeferred'}</a>
        </li>
    </ul>
    *}
    {*<!--<div id="tabs-1">
        <form action="{$smarty.server.REQUEST_URI}" method="post" id="payment_order">
            <fieldset>
                <span class="legent"><img src="../img/admin/contact.gif" />{l s='Virtual POS configuration' mod='redsysdeferred'}</span>
                <table border="0" width="100%" cellpadding="0" cellspacing="4" id="form" style="font-size:12px;margin-bottom:3px">
                    <tr><td colspan="2">
                        <fieldset>
                            <legend>{l s='Create payment order' mod='redsysdeferred'}</legend>
                            <table border="0" width="100%" cellpadding="2" cellspacing="3" style="font-size:12px">
                                <tr>
                                    <td style="height: 25px;">{l s='Alias' mod='redsysdeferred'}</td>
                                    <td>
                                        <input type="text" name="alias" value="{$alias|escape:'htmlall':'UTF-8'}" style="width: 200px;" />
                                    </td>
                                </tr>
                                <tr>
                                    <td style="height: 25px;">{l s='Payment amount' mod='redsysdeferred'}</td>
                                    <td>
                                        <input type="text" name="payment_amount" value="{$payment_amount|escape:'htmlall':'UTF-8'}" style="width: 200px;" />
                                        <select style="width:130px" name="payment_currency" style="width: 80px;"><option value=""></option>
                                            {foreach $currencies as $currency}
                                            <option value="{$currency.iso_code_num}">{$currency.name}</option>
                                            {/foreach}
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <input type="submit" name="addPayment" value="Add" class="button" />
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
                    </tr>
                </table>
            </fieldset>
        </form>
    </div>-->*}
    <div id="tabs-2" class="tab-content">
        <form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post">
            <fieldset>
                <legend>
                    <img src="../img/admin/contact.gif" />{l s='Virtual POS configuration' mod='redsysdeferred'}
                </legend>
                <table border="0" width="100%" cellpadding="0" cellspacing="4" id="form" style="font-size:12px;margin-bottom:3px">
                    <tr>
                        <td colspan="2">
                            <fieldset>
                                <legend>{l s='Commerce configuration' mod='redsysdeferred'} 1</legend>
                                <table border="0" width="100%" cellpadding="2" cellspacing="3" style="font-size:12px">
                                    <tr>
                                        <td style="height: 25px;">
                                            {l s='Redsys environment' mod='redsysdeferred'}
                                        </td>
                                        <td><select style="width:150px" name="urltpv">
                                                <option value=""></option>
                                                <option value="1" {if isset($entorno) && $entorno == 1}selected{/if}>{l s='Real Redsys' mod='redsysdeferred'}</option>
                                                <option value="2" {if isset($entorno) && $entorno == 2}selected{/if}>{l s='Testing Redsys' mod='redsysdeferred'}</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="height: 25px;">
                                            {l s='Commerce name' mod='redsysdeferred'}
                                        </td>
                                        <td>
                                            <input type="text" name="nombre" value="{$nombre|escape:'htmlall':'UTF-8'}" style="width: 150px;" />
                                        </td>
                                        <td style="height: 25px;">
                                            {l s='Commerce number (FUC)' mod='redsysdeferred'}
                                        </td>
                                        <td>
                                            <input type="text" maxlength="9" name="codigo" value="{$codigo|escape:'htmlall':'UTF-8'}" style="width: 150px;" />
                                        </td>
                                        <td style="height: 25px;">
                                            {l s='Secret Key encryption' mod='redsysdeferred'}
                                        </td>
                                        <td>
                                            <input type="text" size="25" maxlength="32" name="clave" value="{$clave|escape:'htmlall':'UTF-8'}" style="width: 150px;" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="height: 25px;">
                                            {l s='Terminal number' mod='redsysdeferred'}
                                        </td>
                                        <td>
                                            <input type="text" style="width:40px" maxlength="3" name="terminal" value="{$terminal|escape:'htmlall':'UTF-8'}" style="width: 80px;" />
                                        </td>
                                        <td style="height: 25px;">
                                            {l s='Currency' mod='redsysdeferred'}
                                        </td>
                                        <td>
                                            <select style="width:130px" name="moneda" style="width: 80px;">
                                                <option value=""></option>
                                                {foreach $currencies as $currency}
                                                    <option value="{$currency.iso_code_num|intval}" {if isset($moneda) and $moneda==$currency.iso_code_num}selected{/if}>{$currency.name|escape:'htmlall':'UTF-8'}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <fieldset>
                                <legend>{l s='Commerce configuration' mod='redsysdeferred'} 2</legend>
                                <table border="0" width="100%" cellpadding="2" cellspacing="3" style="font-size:12px">
                                    <tr>
                                        <td style="height: 25px;">
                                            {l s='Redsys environment' mod='redsysdeferred'}
                                        </td>
                                        <td><select style="width:150px" name="urltpv2">
                                                <option value=""></option>
                                                <option value="1" {if isset($entorno2) && $entorno2 == 1}selected{/if}>{l s='Real Redsys' mod='redsysdeferred'}</option>
                                                <option value="2" {if isset($entorno2) && $entorno2 == 2}selected{/if}>{l s='Testing Redsys' mod='redsysdeferred'}</option>
                                                <option value="3" {if isset($entorno2) && $entorno2 == 3}selected{/if}>{l s='Real Sermepa' mod='redsysdeferred'}</option>
                                                <option value="4" {if isset($entorno2) && $entorno2 == 4}selected{/if}>{l s='Testing Sermepa' mod='redsysdeferred'}</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="height: 25px;">
                                            {l s='Commerce name' mod='redsysdeferred'}
                                        </td>
                                        <td>
                                            <input type="text" name="nombre2" value="{$nombre2|escape:'htmlall':'UTF-8'}" style="width: 150px;" />
                                        </td>
                                        <td style="height: 25px;">
                                            {l s='Commerce number (FUC)' mod='redsysdeferred'}
                                        </td>
                                        <td>
                                            <input type="text" maxlength="9" name="codigo2" value="{$codigo2|escape:'htmlall':'UTF-8'}" style="width: 150px;" /
                                            >
                                        </td>
                                        <td style="height: 25px;">
                                            {l s='Secret Key encryption' mod='redsysdeferred'}
                                        </td>
                                        <td>
                                            <input type="text" size="25" maxlength="32" name="clave2" value="{$clave2|escape:'htmlall':'UTF-8'}" style="width: 150px;" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="height: 25px;">
                                            {l s='Terminal number' mod='redsysdeferred'}
                                        </td>
                                        <td>
                                            <input type="text" style="width:40px" maxlength="3" name="terminal2" value="{$terminal2|escape:'htmlall':'UTF-8'}" style="width: 80px;" />
                                        </td>
                                        <td style="height: 25px;">
                                            {l s='Currency' mod='redsysdeferred'}
                                        </td>
                                        <td>
                                            <select style="width:130px" name="moneda2" style="width: 80px;">
                                                <option value=""></option>
                                                {foreach $currencies as $currency}
                                                    <option value="{$currency.iso_code_num|intval}" {if isset($moneda2) and $moneda2==$currency.iso_code_num}selected{/if}>{$currency.name|escape:'htmlall':'UTF-8'}</option>
                                                {/foreach}
                                                }
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <fieldset>
                                <legend>{l s='Commerce configuration' mod='redsysdeferred'} 3</legend>
                                <table border="0" width="100%" cellpadding="2" cellspacing="3" style="font-size:12px">
                                    <tr>
                                        <td style="height: 25px;">
                                            {l s='Redsys environment' mod='redsysdeferred'}
                                        </td>
                                        <td><select style="width:150px" name="urltpv3">
                                                <option value=""></option>
                                                <option value="1" {if isset($entorno3) && $entorno3 == 1}selected{/if}>{l s='Real Redsys' mod='redsysdeferred'}</option>
                                                <option value="2" {if isset($entorno3) && $entorno3 == 2}selected{/if}>{l s='Testing Redsys' mod='redsysdeferred'}</option>
                                                <option value="3" {if isset($entorno3) && $entorno3 == 3}selected{/if}>{l s='Real Sermepa' mod='redsysdeferred'}</option>
                                                <option value="4" {if isset($entorno3) && $entorno3 == 4}selected{/if}>{l s='Testing Sermepa' mod='redsysdeferred'}</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="height: 25px;">
                                            {l s='Commerce name' mod='redsysdeferred'}
                                        </td>
                                        <td>
                                            <input type="text" name="nombre3" value="{$nombre3|escape:'htmlall':'UTF-8'}" style="width: 150px;" />
                                        </td>
                                        <td style="height: 25px;">
                                            {l s='Commerce number (FUC)' mod='redsysdeferred'}
                                        </td>
                                        <td>
                                            <input type="text" maxlength="9" name="codigo3" value="{$codigo3|escape:'htmlall':'UTF-8'}" style="width: 150px;" /
                                            >
                                        </td>
                                        <td style="height: 25px;">
                                            {l s='Secret Key encryption' mod='redsysdeferred'}
                                        </td>
                                        <td>
                                            <input type="text" size="25" maxlength="32" name="clave3" value="{$clave3|escape:'htmlall':'UTF-8'}" style="width: 150px;" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="height: 25px;">
                                            {l s='Terminal number' mod='redsysdeferred'}
                                        </td>
                                        <td>
                                            <input type="text" style="width:40px" maxlength="3" name="terminal3" value="{$terminal3|escape:'htmlall':'UTF-8'}" style="width: 80px;" />
                                        </td>
                                        <td style="height: 25px;">
                                            {l s='Currency' mod='redsysdeferred'}
                                        </td>
                                        <td>
                                            <select style="width:130px" name="moneda3" style="width: 80px;">
                                                <option value=""></option>
                                                {foreach $currencies as $currency}
                                                    <option value="{$currency.iso_code_num|intval}" {if isset($moneda3) and $moneda3==$currency.iso_code_num}selected{/if}>{$currency.name|escape:'htmlall':'UTF-8'}</option>
                                                {/foreach}
                                                }
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </fieldset>
            <br>
            <fieldset>
                <legend><img src="../img/admin/cog.gif" />{l s='Customization' mod='redsysdeferred'}</legend>
                <table border="0" width="100%" cellpadding="0" cellspacing="0" id="form" style="font-size:12px">
                    <tr>
                        <td width="340" style="height: 25px;">{l s='URL validation with SSL' mod='redsysdeferred'}</td>
                        <td>
                            <input type="radio" name="ssl" id="ssl" value="1" {if isset($ssl) && $ssl == 1}checked{/if} />
                            <img src="../img/admin/enabled.gif" alt="{l s='Enabled' mod='redsysdeferred'}" title="{l s='Enabled' mod='redsysdeferred'}" />
                            <input type="radio" name="ssl" id="ssl" value="0" {if isset($ssl) && $ssl == 0}checked{/if} />
                            <img src="../img/admin/disabled.gif" alt="{l s='Disabled' mod='redsysdeferred'}" title="{l s='Disabled' mod='redsysdeferred'}" />
                        </td>
                    </tr>
                    <tr>
                        <td width="340" style="height: 25px;">{l s='Enable languages in POS (if disabled, default POS language)' mod='redsysdeferred'}</td>
                        <td>
                            <input type="radio" name="idiomas_estado" id="idiomas_estado" value="1" {if isset($idiomas_estado) && $idiomas_estado == 1}checked{/if} />
                            <img src="../img/admin/enabled.gif" alt="{l s='Enabled' mod='redsysdeferred'}" title="{l s='Enabled' mod='redsysdeferred'}" />
                            <input type="radio" name="idiomas_estado" id="idiomas_estado" value="0" {if isset($idiomas_estado) && $idiomas_estado == 0}checked{/if} />
                            <img src="../img/admin/disabled.gif" alt="{l s='Disabled' mod='redsysdeferred'}" title="{l s='Disabled' mod='redsysdeferred'}" />
                        </td>
                    </tr>
                    <tr>
                        <td width="340" style="height: 25px;">{l s='Send mail to customer' mod='redsysdeferred'}</td>
                        <td>
                            <input type="radio" name="mail_customer" id="mail_customer" value="1" {if isset($mail_customer) && $mail_customer == 1}checked{/if} />
                            <img src="../img/admin/enabled.gif" alt="{l s='Enabled' mod='redsysdeferred'}" title="{l s='Enabled' mod='redsysdeferred'}" />
                            <input type="radio" name="mail_customer" id="mail_customer" value="0" {if isset($mail_customer) && $mail_customer == 0}checked{/if} />
                            <img src="../img/admin/disabled.gif" alt="{l s='Disabled' mod='redsysdeferred'}" title="{l s='Disabled' mod='redsysdeferred'}" />
                        </td>
                    </tr>
                </table>
            </fieldset>
            <br>
            <input class="button" name="saveConfig" value="{l s='Save configuration' mod='redsysdeferred'}" type="submit" />
        </form>
    </div>
</div>
