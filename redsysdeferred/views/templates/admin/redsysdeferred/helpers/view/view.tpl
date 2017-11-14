<style type="text/css">
    .nobootstrap {
        min-width: 0 !important;
        padding: 60px 30px 0 !important;
    }

    #redsysdeferred select {
        height: 23px;
        vertical-align: middle;
    }
    #redsysdeferred label {
        clear: both;
    }
    fieldset input{

        margin: 5px;
    }

    body{
        background-color: white;
    }
</style>


<div id="tabs">

    <div id="tabs-2" class="tab-content nobootstrap">
        <form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post">
            <fieldset>
                <legend><img src="../img/admin/cog.gif" />{l s='Link generator' mod='redsysdeferred'}</legend>
                <table id="generator" border="0" width="100%" cellpadding="0" cellspacing="0" id="form" style="font-size:12px">
                    <tr>
                        <td width="340" style="height: 25px;">{l s='Amount' mod='redsysdeferred'}</td>
                        <td>
                            <input type="text" size="25" maxlength="20" name="amount" style="width: 150px;" />
                        </td>
                    </tr>
                    {if $currencies|@count > 1}
                        <tr>
                            <td width="340" style="height: 25px;">{l s='Currency' mod='redsysdeferred'}</td>
                            <td>
                                <select name="currency" style="height:24px">
                                    <option value="" ></option>
                                    {foreach from=$currencies item=currency}
                                        <option value="{$currency['id_currency']|intval}" >{$currency['name']|escape:'htmlall':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            </td>
                        </tr>
                    {/if}
                    <tr>
                        <td width="340" style="height: 25px;">{l s='Name' mod='redsysdeferred'}</td>
                        <td>
                            <input type="text" size="25" name="name" style="width: 150px;" />
                        </td>
                    </tr>
                    <tr>
                        <td width="340" style="height: 25px;">{l s='Description' mod='redsysdeferred'}</td>
                        <td>
                            <input type="text" size="25" name="description" style="width: 150px;" />
                        </td>
                    </tr>
                    <tr>
                        <td width="340" style="height: 25px;">{l s='Mail address' mod='redsysdeferred'}</td>
                        <td>
                            <input type="text" size="25" name="mail" style="width: 150px;" />
                        </td>
                    </tr>
                    <tr>
                        <td width="340" style="height: 25px;"><strong>{l s='Link to share' mod='redsysdeferred'}</strong></td>
                        <td>
                            <a id="generated-link-anchor" target="_blank"><span id="generated-link" /></a>
                        </td>
                    </tr>
                </table>
            </fieldset>

        </form>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        console.log( "ready!" );
    });

    $('#generator input, #generator select').bind("keyup change", function() {
        var base_link = "{$base_link|escape:'htmlall':'UTF-8'}";
        var link = '';
        link += ($("#generator input[name='amount']") && $("#generator input[name='amount']").val() != '') ? "a="+$("#generator input[name='amount']").val()+"&" : "";
        {if $currencies|@count > 1}
        link += ($("#generator select[name='currency']") && $("#generator select[name='currency']").val() != '') ? "c="+$("#generator select[name='currency']").val()+"&" : "";
        {/if}
        link += ($("#generator input[name='name']") && $("#generator input[name='name']").val() != '') ? "n="+$("#generator input[name='name']").val()+"&" : "";
        link += ($("#generator input[name='description']") && $("#generator input[name='description']").val() != '') ? "d="+$("#generator input[name='description']").val()+"&" : "";
        link += ($("#generator input[name='mail']") && $("#generator input[name='mail']").val() != '') ? "m="+$("#generator input[name='mail']").val()+"&" : "";
        link += ($("#generator input[name='mail']").val() != '' || $("#generator input[name='name']").val() != '' || $("#generator input[name='description']").val() != '' || $("#generator input[name='amount']").val() != '') ? "z="+md5(new Date().getTime())+"&" : "";


        if (link != '') {
            $('#generated-link').text(base_link+'?'+link.slice(0, -1));
            $("#generated-link-anchor").attr("href", base_link+'?'+link.slice(0, -1));
        }
        else {
            $('#generated-link').text('');
            $("#generated-link-anchor").attr("href", "");
        }
    })
</script>