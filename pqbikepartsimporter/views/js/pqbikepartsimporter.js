

$(document).ready(function() {


    $(".list-group-item").on('click', function () {
        var $el = $(this).parent().closest(".list-group").children(".active");
        if ($el.hasClass("active")) {
            target = $(this).find('i').attr('data-target');
            if (target !== undefined) {
                loadTable(target);
            }
            $el.removeClass("active");
            $(this).addClass("active");
        }
    });


});