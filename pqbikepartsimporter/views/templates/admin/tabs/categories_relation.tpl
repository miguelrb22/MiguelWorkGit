<form id="module_form" class="defaultForm form-horizontal"
      action="{$bkpsubmiturl}"
      method="post" enctype="multipart/form-data" novalidate="">

    <input type="hidden" name="submitBKPCategoryAsociation" value="1">

    <div class="panel" id="fieldset_0">

        <div class="panel-heading">
            <i class="icon-exchange"></i> {l s='ASSOCIATION (CATEGORY BIKEPARTS -> PRESTASHOP CATEGORY)' mod='pqbikepartsImporter'}
            <br>
        </div>

        <div class="form-wrapper"></div>

        {foreach from=$bkp_categories item=bkp_category}
            <div class="row">
                <div class="col-lg-5">

                    <span style="font-size: 15px"> {$bkp_category['bkp_name']} </span>

                </div>


                <div class="col-lg-2">
                    <span> <i class="icon-arrow-right"></i> </span>

                </div>

                <div class="col-lg-4">

                    <select id="bkp_category" class="form-control" name="bkpcategory_{$bkp_category['id']}">

                        {foreach from=$prestashop_categories item=pc}
                            <option value="{$pc['id_category']}" {if $bkp_category['id_category'] == $pc['id_category']} selected {/if}>{$pc['name']}</option>
                        {/foreach}
                    </select>
                </div>


            </div>
            <hr>
        {/foreach}

        <div class="panel-footer">
            <button type="submit" value="1" id="module_form_submit_btn" name="submitBKPCategoryAsociation"
                    class="btn btn-default pull-right">
                <i class="process-icon-save"></i> Asociar
            </button>
        </div>

    </div>

</form>
