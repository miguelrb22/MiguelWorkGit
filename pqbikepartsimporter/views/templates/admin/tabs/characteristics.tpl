<form id="module_form" class="defaultForm form-horizontal"
      action="{$bkpsubmiturl}"
      method="post" enctype="multipart/form-data" novalidate="">

    <input type="hidden" name="submitBKPCaracteristicsAsociation" value="1">

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


        </div>


        <div class="panel-footer">
            <button type="submit" value="1" id="module_form_submit_btn" name="submitBKPCaracteristicsAsociation"
                    class="btn btn-default pull-right">
                <i class="process-icon-save"></i> Asociar
            </button>
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

        alert(value);

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
