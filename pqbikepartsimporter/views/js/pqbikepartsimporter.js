

$(document).ready(function() {


    $('.numeric').keypress(function(event){

        if(event.which != 8 && isNaN(String.fromCharCode(event.which))){
            event.preventDefault(); //stop character from entering input
        }

    });

    $(".list-group .list-group-item").click(function(event){

        $(".list-group-item").each(function( index ) {
            $(this).removeClass("active");
        });

        $(this).addClass( "active" );

    });
});