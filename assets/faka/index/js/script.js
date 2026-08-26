"use strict";

$(".mobile-options").on('click', function() {
    $(".navbar-container .nav-right").slideToggle('slow');
});

$(document).ready(function(){
    $(".header-notification").click(function(){
        $(this).find(".show-notification").slideToggle(200);
        $(this).toggleClass('active');
    });
});
$(document).on("click", function(event){
    var $trigger = $(".header-notification");
    if($trigger !== event.target && !$trigger.has(event.target).length){
        $(".show-notification").slideUp(300);
        $(".header-notification").removeClass('active');
    }
});

// /*chatbar js end*/
$(function() {
    $('[data-toggle="tooltip"]').tooltip()
})

// toggle full screen
function toggleFullScreen() {
    var a = $(window).height() - 10;

    if (!document.fullscreenElement && // alternative standard method
        !document.mozFullScreenElement && !document.webkitFullscreenElement) { // current working methods
        if (document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen();
        } else if (document.documentElement.mozRequestFullScreen) {
            document.documentElement.mozRequestFullScreen();
        } else if (document.documentElement.webkitRequestFullscreen) {
            document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
        }
    } else {
        if (document.cancelFullScreen) {
            document.cancelFullScreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitCancelFullScreen) {
            document.webkitCancelFullScreen();
        }
    }
}