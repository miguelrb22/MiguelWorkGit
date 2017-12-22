<form class="defaultForm form-horizontal"
      action="{$bkpsubmiturl}"
      method="post" enctype="multipart/form-data" novalidate="">

    <input type="hidden" name="submitBKPCategoryAsociation" value="1">

    <div class="panel" id="fieldset_0">

        <div class="panel-heading">
            <i class="icon-exchange"></i> {l s='Category Relationship' mod='pqbikepartsImporter'}
            <br>
        </div>

        <div class="form-wrapper"></div>

        {foreach name=foo from=$bkp_categories item=bkp_category}

            {if $smarty.foreach.foo.first}
                <div class="row">
                    <div class="col-lg-4">
                        <h2>Bikeparts Category</h2>
                    </div>


                    <div class="col-lg-4">
                        <h2>Local Category</h2>
                    </div>

                    <div class="col-lg-4">
                        <h2>Tax rule</h2>

                    </div>
                </div>
                <hr>
            {/if}
            <div class="row">
                <div class="col-lg-3">

                    <span style="font-size: 15px"> {$bkp_category['bkp_name']} </span>

                </div>


                <div class="col-lg-1">
                    <span> <i class="icon-arrow-right"></i> </span>

                </div>

                <div class="col-lg-3">

                    <select class="form-control" name="bkpcategory_{$bkp_category['id']}">

                        {foreach from=$prestashop_categories item=pc}
                            <option value="{$pc['id_category']}" {if $bkp_category['id_category'] == $pc['id_category']} selected {/if}>{$pc['name']}</option>
                        {/foreach}
                    </select>
                </div>

                <div class="col-lg-1">
                    <CENTER><span style="margin: auto;  text-align: center;"> <i class="icon-arrow-right"></i> </span>
                    </CENTER>

                </div>

                <div class="col-lg-3">

                    <select class="form-control" name="bkp_category_tax_{$bkp_category['id']}">

                        <option value="0">Without taxes</option>

                        {foreach from=$taxes item=tax}
                            <option value="{$tax['id_tax']}" {if $tax['id_tax'] == $bkp_category['id_tax_rule']} selected {/if}>{$tax['name']}</option>
                        {/foreach}
                    </select>
                </div>


            </div>
            <hr>
            {foreachelse}

        <div class="row">
            <div class="col-lg-12">
                <span style="font-weight: bold; font-size: 14px; text-align: center; margin: 0 auto;">{l s='Without categories. Please, configure cron. You have more information in crons tab' mod='pqbikepartsImporter'}</span>
            </div>

        </div>
            <br>
            <br>

        {/foreach}

        <div class="panel-footer">
            <button type="submit" value="1" name="submitBKPCategoryAsociation"
                    class="btn btn-default pull-right">
                <i class="process-icon-save"></i> Asociar
            </button>
        </div>

    </div>

</form>
