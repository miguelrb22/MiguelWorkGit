<form class="defaultForm form-horizontal"
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
                <div class="col-lg-4">

                    <span style="font-size: 15px"> {$bkp_category['bkp_name']} </span>

                </div>


                <div class="col-lg-1">
                    <span> <i class="icon-arrow-right"></i> </span>

                </div>

                <div class="col-lg-4">

                    <select class="form-control" name="bkpcategory_{$bkp_category['id']}">

                        {foreach from=$prestashop_categories item=pc}
                            <option value="{$pc['id_category']}" {if $bkp_category['id_category'] == $pc['id_category']} selected {/if}>{$pc['name']}</option>
                        {/foreach}
                    </select>
                </div>

                <div class="col-lg-1">
                    <span style="margin: auto;  text-align: center;"> <i class="icon-arrow-right"></i> </span>

                </div>

                <div class="col-lg-2">

                    <select class="form-control" name="bkpcategory_tax_{$bkp_category['id']}">

                        {foreach from=$taxes item=tax}
                            <option value="{$tax['id_tax']}">{$tax['name']}</option>
                        {/foreach}
                    </select>
                </div>


            </div>
            <hr>

            {foreachelse}

            <span>{l s='Without data. Please, configure cron. For first time, you can click the next button.' mod='pqbikepartsImporter'}</span>

            <br>
            <br>


            <a class="btn btn-warning" href="{$bkp_cron_categories}"> Execute </a>


        {/foreach}

        <div class="panel-footer">
            <button type="submit" value="1" name="submitBKPCategoryAsociation"
                    class="btn btn-default pull-right">
                <i class="process-icon-save"></i> Asociar
            </button>
        </div>

    </div>

</form>
