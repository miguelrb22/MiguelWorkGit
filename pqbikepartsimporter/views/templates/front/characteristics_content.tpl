<hr>

{foreach from=$pq_bkp_feature_value_date item=bkp_feature}
    <h2>{ucfirst($bkp_feature['name'])}</h2>
    <br>
    <div class="row">

        <div class="col-lg-5">

            <select class="bkp_selection" class="form-control"
                    data-bkp-feature="{$bkp_feature['data'][0]['id_feature']}"
                    name="type_feature_{$bkp_feature['data'][0]['id_feature']}">

                <option value="0" {if $bkp_feature['data'][0]['type'] == 0} selected {/if}> No sincronizar</option>
                <option value="1" {if $bkp_feature['data'][0]['type'] == 1} selected {/if}> Son categorías</option>
                <option value="2" {if $bkp_feature['data'][0]['type'] == 2} selected {/if}> Son un grupo de
                    caracteristicas
                </option>

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

                    <select class="form-control final_bkp_assigment" data-feature="{$bkp_feature['id_feature']}" data-feature-value="{$bkp_feature['id_value']}" name="feature_value_for_char_{$bkp_feature['id_feature']}_{$bkp_feature['id_value']}">

                        {foreach from=$pq_bkp_features item=feature}
                            <option value="{$feature['id_feature']}" {if $feature['id_feature'] == $bkp_feature['relation_bkp_feature_feature']} selected {/if}> {ucfirst($feature['name'])} </option>
                        {/foreach}
                    </select>
                </div>
            </div>
        {/foreach}

    </div>
    <div class="row" id="bkp_selection_cat_{$data[0]['id_feature']}" style="display: none;">

        <br>
        <h4>Categorías</h4>

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

                    <select class="form-control final_bkp_assigment" data-feature="{$bkp_feature['id_feature']}" data-feature-value="{$bkp_feature['id_value']}" name="feature_value_for_cat_{$bkp_feature['id_feature']}_{$bkp_feature['id_value']}">

                        {foreach from=$prestashop_categories item=pc}
                            <option value="{$pc['id_category']}" {if $pc['id_category'] == $bkp_feature['relation_bkp_feature_category']} selected {/if}>{$pc['name']}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        {/foreach}

    </div>
    <hr>
{/foreach}
