<form class="defaultForm form-horizontal"
      action="{$bkpsubmiturl}"
      method="post" enctype="multipart/form-data" novalidate="">

    <input type="hidden" name="submitBKPCaracteristicsAsociation" value="1">

    <div class="panel" id="fieldset_0">

        <div class="panel-heading">
            <i class="icon-exchange"></i> {l s='ASSOCIATION (CATEGORY BIKEPARTS -> PRESTASHOP CATEGORY)' mod='pqbikepartsImporter'}
            <br>
        </div>

        <div class="form-wrapper">


            {if count($bkp_categories)}

            <div class="row">

                <div class="col-lg-6">

                    <h4> Configure associations for...</h4>
                </div>

            </div>

            <div class="row">

                <div class="col-lg-5">

                    <select id="general_category_select" class="form-control" name="general_category">
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

            {else}
                <div class="row">
                    <div class="col-lg-12">
                        <span style="font-weight: bold; font-size: 14px; text-align: center; margin: 0 auto;">{l s='Without categories. Please, configure cron. You have more information in crons tab' mod='pqbikepartsImporter'}</span>
                    </div>

                </div>
                <br>
                <br>
            {/if}


        </div>


        <div class="panel-footer">
            <!--<button type="submit" value="1" name="submitBKPCaracteristicsAsociation"
                    class="btn btn-default pull-right">
                <i class="process-icon-save"></i> Asociar
            </button> -->
        </div>

    </div>

</form>

<script type="text/javascript">

    $(document).on('change', '.bkp_selection', function () {

        var data = ($(this).data('bkp-feature'));
        var value = this.value;

        switch (value) {

            case '0':
                $('#bkp_selection_char_' + data).hide();
                $('#bkp_selection_cat_' + data).hide();
                break;

            case '1':
                $('#bkp_selection_char_' + data).hide();
                $('#bkp_selection_cat_' + data).show();

                break;

            case '2':
                $('#bkp_selection_char_' + data).show();
                $('#bkp_selection_cat_' + data).hide();
                break;
        }

    });


    $(document).on('change', '.final_bkp_assigment', function () {

        var value = this.value;
        var id_feature = $(this).data('feature');
        var id_value = $(this).data('feature-value');
        var type = $('select[name=type_feature_' + id_feature + ']').val();

        $.ajax({
            type: "POST",
            url: "{$bkp_cron_updatedb}",
            data: {literal}{value: value, id_feature: id_feature, id_value: id_value, type: type}{/literal},
            success: function (data) {
                console.log('%c Feature ' + id_feature + ' update feature value ' + id_value + ' to ' + value, 'background: #222; color: #bada55');
            },
            error: function () {

            }
        });

    });


    $('#general_category_select').on('change', function () {

        var value = this.value;

        $.ajax({
            type: "POST",
            url: "{$bkp_cron_generate}",
            data: {literal}{general_category: value}{/literal},
            success: function (data) {

                $('.bkp_characteristics_layout').html(data);
                reloadCategoryViewMap();
            },
            error: function () {

                alert("Error loading data at this moment");
            }
        });

    });

    $(document).ready(function () {

        $(".bkp_selection").each(function (index) {

            var data = ($(this).data('bkp-feature'));
            var value = this.value;

            switch (value) {

                case '0':
                    $('#bkp_selection_char_' + data).hide();
                    $('#bkp_selection_cat_' + data).hide();
                    break;

                case '1':
                    $('#bkp_selection_char_' + data).hide();
                    $('#bkp_selection_cat_' + data).show();

                    break;

                case '2':
                    $('#bkp_selection_char_' + data).show();
                    $('#bkp_selection_cat_' + data).hide();
                    break;
            }
        });
    });


    function reloadCategoryViewMap() {

        $(".bkp_selection").each(function (index) {

            var data = ($(this).data('bkp-feature'));
            var value = this.value;

            switch (value) {

                case '0':
                    $('#bkp_selection_char_' + data).hide();
                    $('#bkp_selection_cat_' + data).hide();
                    break;

                case '1':
                    $('#bkp_selection_char_' + data).hide();
                    $('#bkp_selection_cat_' + data).show();

                    break;

                case '2':
                    $('#bkp_selection_char_' + data).show();
                    $('#bkp_selection_cat_' + data).hide();
                    break;
            }
        });

    }

</script>
