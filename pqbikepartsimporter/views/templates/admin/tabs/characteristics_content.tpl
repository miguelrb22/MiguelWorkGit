<hr>

{foreach from=$pq_bkp_feature_value_date item=bkp_feature}
    <h2>{ucfirst($bkp_feature['name'])}</h2>
    <br>
    <div class="row">

        <div class="col-lg-5">

            <select class="bkp_selection" class="form-control" data-bkp-feature="{$bkp_feature['data'][0]['id_feature']}">

                <option value="0"> No sincronizar</option>
                <option value="1"> Son categorías</option>
                <option value="2"> Son un grupo de caracteristicas</option>

            </select>
        </div>

    </div>
    {$data = $bkp_feature['data']}
    <div class="row" id="bkp_selection_char_{$data[0]['id_feature']}" style="display: none;">

        <br>
        <h4>Caracteristicas</h4>

        {foreach from=$data item=bkp_feature}
            <div class="row">
                <br>
                <div class="col-lg-5 col-xs-12" style="margin-left: 20px;">

                    <span style="font-size: 15px"> {ucfirst($bkp_feature['value_desc'])} </span>

                </div>
                <div class="col-lg-2 hidden-xs">
                    <span> <i class="icon-arrow-right"></i> </span>

                </div>
                <div class="col-lg-4 col-xs-12">

                    <select class="form-control">

                        {foreach from=$pq_bkp_features item=feature}
                            <option value="{$feature['id_feature']}"> {ucfirst($feature['name'])} </option>
                        {/foreach}
                    </select>
                </div>
            </div>
        {/foreach}

    </div>
    <div class="row" id="bkp_selection_cat_{$data[0]['id_feature']}" style="display: none;">

        <br>
        <h5>Categorías</h5>

        {foreach from=$data item=bkp_feature}
            <div class="row">
                <br>
                <div class="col-lg-5 col-xs-12" style="margin-left: 20px;">

                    <span style="font-size: 15px"> {ucfirst($bkp_feature['value_desc'])} </span>

                </div>
                <div class="col-lg-2 hidden-xs">
                    <span> <i class="icon-arrow-right"></i> </span>

                </div>
                <div class="col-lg-4 col-xs-12">

                    <select class="form-control">

                        {foreach from=$prestashop_categories item=pc}
                            <option value="{$pc['id_category']}">{$pc['name']}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        {/foreach}

    </div>
    <hr>
{/foreach}

<script type="text/javascript">

    $('.bkp_selection').on('change', function () {

        var data = ($(this).data('bkp-feature'));
        var value = this.value;

        switch (value) {

            case '0':
                $('#bkp_selection_char_'+data).hide();
                $('#bkp_selection_cat_'+data).hide();
                break;

            case '1':
                $('#bkp_selection_char_'+data).hide();
                $('#bkp_selection_cat_'+data).show();

                break;

            case '2':
                $('#bkp_selection_char_'+data).show();
                $('#bkp_selection_cat_'+data).hide();
                break;
        }

    });

</script>