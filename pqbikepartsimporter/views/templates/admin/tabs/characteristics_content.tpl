<HR>

{foreach from=$pq_bkp_feature_value_date item=bkp_feature}
    <h2>{ucfirst($bkp_feature['name'])}</h2>
    <br>
    <div class="row">

        <div class="col-lg-5">

            <select id="bkp_category" class="form-control">

                <option value="0"> No sincronizar</option>
                <option value="1"> Son categorías</option>
                <option value="2"> Son un grupo de caracteristicas</option>

            </select>
        </div>

    </div>
    <div class="row">

        <br>


        <div class="col-lg-5">

            <select id="bkp_category" class="form-control">

                {foreach from=$pq_bkp_features item=feature}
                    <option value="{$feature['id_feature']}"> {ucfirst($feature['name'])} </option>
                {/foreach}

            </select>
        </div>

    </div>
    <div class="row">

        <br>

        {$data = $bkp_feature['data'] }
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

                    <select id="bkp_category" class="form-control">

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