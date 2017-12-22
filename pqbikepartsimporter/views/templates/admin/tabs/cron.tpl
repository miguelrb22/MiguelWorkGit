<div class="panel" id="fieldset_0">
    <div class="panel-heading">
        <i class="icon-exchange"></i> {l s='Cron information' mod='pqbikepartsImporter'}
        <br>
    </div>
    <div class="row">
        <div class="col col-lg-12"><span style="font-weight: bold; font-size: 12px">Here you can see the urls that you must configure in your server as scheduled tasks to synchronize categories, characteristics and products. Additionally you can execute them directly using the available button.</span>
        </div>
    </div>
    <br>
    <div class="form-wrapper">
        <div class="row">
            <div class="col col-lg-2"><a style="width: 190px; font-weight: bold" class="btn btn-warning" href="{$bkp_cron_categories}"> Execute cron categories</a></div>
            <div class="col col-lg-9" style="margin-top: 5px"><span style="vertical-align: middle"> {$bkp_cron_categories_nr}</span></div>
        </div>
        <br>

        <div class="row">
            <div class="col col-lg-2"><a style="width: 190px; font-weight: bold" class="btn btn-success" href="{$bkp_cron_charasteristics}"> Execute cron characteristics</a></div>
            <div class="col col-lg-9" style="margin-top: 5px"><span> {$bkp_cron_charasteristics_nr} </span></div>
        </div>
        <br>
        <div class="row">
            <div class="col col-lg-2"><a style="width: 190px; font-weight: bold" class="btn btn-info" href="{$bkp_cron_products}"> Execute cron products </a></div>
            <div class="col col-lg-9" style="margin-top: 5px"><span> {$bkp_cron_products_nr} </span></div>
        </div>
    </div>
</div>

