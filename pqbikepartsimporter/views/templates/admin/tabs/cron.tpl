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
            <div class="col col-lg-1"><a class="btn btn-warning" href="{$bkp_cron_categories}"> Execute </a></div>
            <div class="col col-lg-9" style="margin-top: 5px"><span style="vertical-align: middle"> {$bkp_cron_categories_nr}</span></div>
        </div>
        <br>

        <div class="row">
            <div class="col col-lg-1"><a class="btn btn-warning" href="{$bkp_cron_charasteristics}"> Execute </a></div>
            <div class="col col-lg-9" style="margin-top: 5px"><span> {$bkp_cron_charasteristics_nr} </span></div>
        </div>


    </div>


</div>

