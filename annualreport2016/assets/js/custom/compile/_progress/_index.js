(function ($) {
    $.fn.progressOnScroll = function (options) {
        // This is the easiest way to have default options.
        var settings = $.extend({
            //backgroundColor: "#f00",
            //height: '10px',
            //position: 'fixed'
        }, options);
        var mySelector = this.selector;
        var _is_vertical = false;
        if($(this).hasClass('vertical'))
            _is_vertical = true;
        this.each(function () {
            $(window).scroll(function () {
                var offsettop = parseInt($(this).scrollTop());
                var parentHeight = parseInt($('body, html').height() - $(window).height());
                var vscrollwidth = offsettop / parentHeight * 100;
                if(_is_vertical)
                    $(mySelector).css({height: vscrollwidth + '%'});
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


jQuery(document).ready(function($){
    $("#progress-bar").progressOnScroll();
});
