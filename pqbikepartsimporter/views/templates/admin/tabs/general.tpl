<div class="tab-pane panel active" id="categories">
    <form  method="POST" >
    <h3><i class="icon-indent"></i> {l s='Categories' mod='pqbikepartsImporter'}</h3>
        <div class="form-group">
            Comisión:<br>
            <input type="text" name='BKP_COMMISSION'>
            <br>
            Tiempo de entrega adicional:<br>
            <input type="text" name='BKP_ADDITIONAL_TIME'>
            <br>
            Deshabilitado:<br>
            <select type="select" name='BKP_DEFAULT_STATUS'>
                <option value="yes">Yes</option>
                <option value="no">No</option>
            </select>
            <br><br>
            <input type="submit" name="saveBKPCategories" value="Submit">
        </div>
    </form>
</div>