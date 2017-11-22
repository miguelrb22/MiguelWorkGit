<!-- Module content -->

<div id="modulecontent" class="clearfix">
        <!-- Nav tabs -->
        <div class="col-lg-2" style="position:fixed">
                <div class="list-group">
                        <a href="#general" class="list-group-item active" data-toggle="tab"><i class="icon-indent"></i> {l s='General' mod='pqbikepartsImporter'}</a>
                        <a href="#categories" class="list-group-item " data-toggle="tab"><i class="icon-book"></i> {l s='Relacion Categorías' mod='pqbikepartsImporter'}</a>
                        <a href="#characteristics" class="list-group-item " data-toggle="tab"><i class="icon-book"></i> {l s='Relación Características' mod='pqbikepartsImporter'}</a>
                </div>
        </div>
        <!-- Tab panes -->
        <div class="tab-content col-lg-9" style="position:relative;left:19%">

                <div class="tab-pane active" id="general">
                        {include file="./tabs/general.tpl"}
                </div>

                <div class="tab-pane " id="categories">
                        {include file="./tabs/categories.tpl"}
                </div>

                <div class="tab-pane " id="characteristics">
                        {include file="./tabs/characteristics.tpl"}
                </div>

        </div>


</div>

