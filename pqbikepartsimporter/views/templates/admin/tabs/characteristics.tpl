<form id="module_form" class="defaultForm form-horizontal"
      action="{$bkpsubmiturl}"
      method="post" enctype="multipart/form-data" novalidate="">

    <input type="hidden" name="submitBKPCategoryAsociation" value="1">




    <div class="panel" id="fieldset_0">

        <div class="panel-heading">
            <i class="icon-exchange"></i> {l s='ASSOCIATION (CATEGORY BIKEPARTS -> PRESTASHOP CATEGORY)' mod='pqbikepartsImporter'}
            <br>
        </div>

        <div class="form-wrapper">

            <div class="row">

                <div class="col-lg-6">

                    <h4> Configure associations for...</h4>
                </div>

            </div>

            <div class="row">

                <div class="col-lg-5">

                    <select id="bkp_category" class="form-control">
                        {foreach from=$bkp_categories item=bkp_category}
                            <option value="{$bkp_category['id']}">{$bkp_category['bkp_name']}</option>
                        {/foreach}
                    </select>
                </div>

            </div>


            <div class="row">

                <div class="col-lg-12 bkp_characteristics_layout">

                    {$characteristics_layout}

                </div>

            </div>


        </div>


        <div class="panel-footer">
            <button type="submit" value="1" id="module_form_submit_btn" name="submitBKPCategoryAsociation"
                    class="btn btn-default pull-right">
                <i class="process-icon-save"></i> Asociar
            </button>
        </div>

    </div>

</form>
