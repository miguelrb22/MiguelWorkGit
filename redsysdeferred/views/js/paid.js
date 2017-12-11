/**
 * Created by migue on 03/02/2017.
 */

$( document ).ready(function() {
    var a = $(".pagado");

    $(a).each(function() {

        var aux = $(this).html();
        var aux2 = aux.match(/\d+/);

        if(aux2 == 0){
            $(this).html("<span style='color:red; font-weight: bold'>No pagado</span>");
        }else if (aux2 == 1){
            $(this).html("<span style='color:green; font-weight: bold'>Pagado</span>");
        }
    });

    var a = $(".isdrop");

    $(a).each(function() {

        var aux = $(this).html();
        var aux2 = aux.match(/\d+/);

        if(aux2 == 0){
            $(this).html("<span style='color:red; font-weight: bold'>No</span>");
        }else if (aux2 == 1){
            $(this).html("<span style='color:green; font-weight: bold'>Sí</span>");
        }
    });
});