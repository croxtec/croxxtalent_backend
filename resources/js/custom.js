$(function() {
    $(window).on("scroll", function() {
        if($(window).scrollTop() > 50) {
            $(".header-contain").addClass("header-bg");
        } else {
            //remove the background property so it comes transparent again (defined in your css)
           $(".header-contain").removeClass("header-bg");
        }
    });
    $('show-mobile').on('click', function() {
        $(".hide-menu").addClass("hide");
        $(".show-menu").addClass("show");
    })
});

$(function() {
    $('.show-mobile').on('click', function() {
        $(".show-menu").toggle();
    });
    $('.hide-mobile').on('click', function() {
        $(".show-menu").toggle();
    });
});