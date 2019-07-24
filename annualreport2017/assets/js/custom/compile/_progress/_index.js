(function ($) {
    $.fn.progressOnScroll = function (options) {
        // This is the easiest way to have default options.
        var settings = $.extend({
            //backgroundColor: "#f00",
            //height: '10px',
            //position: 'fixed'
        }, options);
        var dot = $('.progress-dot');
        var mySelector = this.selector;
        var _is_vertical = false;
        if($(this).hasClass('vertical'))
            _is_vertical = true;
        this.each(function () {
            $(window).scroll(function () {
                var offsettop = parseInt($(this).scrollTop());
                var parentHeight = parseInt($('body, html').height() - $(window).height());
                var vscrollwidth = offsettop / parentHeight * 100;
                if(_is_vertical) {
                    $(mySelector).css({height: vscrollwidth + '%'});
                    setDotColor(dot, $(mySelector), vscrollwidth);
                }
                else
                    $(mySelector).css({width: vscrollwidth + '%'});
                
            });
            //$(mySelector).css({
            //    backgroundColor: settings.backgroundColor,
            //    height: settings.height,
            //    position: settings.position
            //});
        });
        return this;
    };
}(jQuery));

function setDotColor(dot, bar, percent) {
    if (percent < 38) {
        bar.css('background-color', '#0073cf');
        dot.css('background-color', '#00338d');
        return;
    }
    if (percent < 55) {
        bar.css('background-color', '#867a24');
        dot.css('background-color', '#bab37f');
        return;
    }
    if (percent < 65) {
        bar.css('background-color', '#c79900');
        dot.css('background-color', '#e3cd84');
        return;
    }
    if (percent < 75) {
        bar.css('background-color', '#bb650e');
        dot.css('background-color', '#cf9a64');
        return;
    }
    if (percent < 90) {
        bar.css('background-color', '#782327');
        dot.css('background-color', '#a96164');
        return;
    }
    if (percent < 101) {
        bar.css('background-color', '#0d61b2');
        dot.css('background-color', '#0d61b2');
        return;
    }
}

jQuery(document).ready(function($){
    $("#progress-bar").progressOnScroll();
});
