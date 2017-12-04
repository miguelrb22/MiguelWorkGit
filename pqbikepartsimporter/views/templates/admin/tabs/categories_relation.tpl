<form id="module_form" class="defaultForm form-horizontal"
      action="http://www.prestashop.local/prestashop/administration/index.php?controller=AdminModules&amp;configure=pqbikepartsimporter&amp;tab_module=administration&amp;module_name=pqbikepartsimporter&amp;token=28465943f6e3bbe63b3e2937b14c5185"
      method="post" enctype="multipart/form-data" novalidate="">

    <input type="hidden" name="submitBKPGeneralSettings" value="1">

    <div class="panel" id="fieldset_0">

        <div class="panel-heading">
            <i class="icon-cogs"></i> Settings
        </div>

        <div class="form-wrapper"></div>

        {foreach from=$bkp_categories item=bkp_category}
            <div class="row">
                <div class="col-lg-5">

                    <span style="font-size: 15px"> {$bkp_category['bkp_name']} </span>

                </div >


                <div class="col-lg-2">
                    <span> <i class="icon-arrow-right"></i> </span>

                </div>

                <div class="col-lg-5">

                    <select id="bkp_category" class="chosen form-control" name="bkp_category_{$bkp_category['id']}">

                        {foreach from=$prestashop_categories item=pc}
                            <option value="{$pc['id_category']}">{$pc['name']}</option>
                        {/foreach}
                    </select>
                </div>


            </div>
            <hr>
        {/foreach}

        <div class="panel-footer">
            <button type="submit" value="1" id="module_form_submit_btn" name="submitBKPGeneralSettings"
                    class="btn btn-default pull-right">
                <i class="process-icon-save"></i> Guardar
            </button>
        </div>

    </div>

</form>
