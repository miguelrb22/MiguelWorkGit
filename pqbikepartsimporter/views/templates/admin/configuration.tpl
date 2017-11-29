<!-- Module content -->

<div id="modulecontent" class="clearfix">
        <!-- Nav tabs -->
        <div class="col-lg-2" style="position:fixed">
                <div class="list-group">
                        <a href="#general" class="list-group-item active" data-toggle="tab"><i class="icon-indent"></i> {l s='General' mod='pqbikepartsImporter'}</a>
                        <a href="#categories" class="list-group-item " data-toggle="tab"><i class="icon-book"></i> {l s='Relacion Categorías' mod='pqbikepartsImporter'}</a>
                        <a href="#characteristics" class="list-group-item " data-toggle="tab"><i class="icon-book"></i> {l s='Relación Características' mod='pqbikepartsImporter'}</a>
                        <a href="#logout" class="list-group-item " data-toggle="tab"><i class="icon-power-off"></i> {l s='Logout' mod='pqbikepartsImporter'}</a>
                </div>
        </div>
        <!-- Tab panes -->
        <div class="tab-content col-lg-9" style="position:relative;left:19%">

                <div class="tab-pane active" id="general">
                    {$pq_bike_form1}
                </div>

                <div class="tab-pane " id="categories">
                    {$pq_bike_form2}

                </div>

                <div class="tab-pane " id="characteristics">
                    {$pq_bike_form3}
                </div>

                <div class="tab-pane " id="logout">
                    {include file = './logout.tpl' }
                </div>

        </div>


</div>

