// window.sr = ScrollReveal({ reset: true });
(function($) {
    jQuery(document).ready(function( $ ) {
    /**
     * @example : <div class="add-wm-animate-css" data-wm-animate-class="flash" data-wm-animate-mobile-width="768" data-wm-animate-show-mobile="false" data-wm-animate-offset="80%"></div>
     *
     * @link https://github.com/daneden/animate.css/
     * @link http://imakewebthings.com/waypoints/
     * All elements acted on will need the following data fields filled out
     * wmAnimateClass : (required) the animate css class to use
     * wmAnimateMobileWidth : (optional) the width that describes the mobile screen
     * wmAnimateShowMobile : (optional) to show animation in mobile or not
     * wmAnimateDelay : (optional) an amount in miliseconds to delay animation
     * wmAnimateOffset : (optional) see: http://imakewebthings.com/waypoints/api/offset-option/
     *  A number offset represents the number of pixels from the top of the viewport where the handler should trigger.
     *
     *  Number Offset: The default, 0, means the handler triggers when the top of the element hits the top of the viewport.
     *  If we set this to 25, it will trigger when the top of the element is 25px from the top of the window.
     *
     *  Out of sight: Number offsets can be negative. An offset of -25 triggers the handler when the top of the element is 25px above the top of the viewport, out of sight.
     *
     *  Percentage Offset: A percentage offset refers to a percentage of the window's height. An offet of '50%'
     *  will trigger when the top of the element is 50% of the way from the top of the window, or simply put, hits the middle of the window.
     *
     *  Just like number offsets, percentages can be negative. Also like number offsets,
     *  negatives indicate the top of the element is a certain percentage of the window height beyond the top of the viewport.
     *
     *  by default it is set to 80 %
     */
     $('.add-wm-animate-css').waypoint({
          handler: function(direction) {
            //adding delay
            var _self = this;
            var _delay = null;
            var _show_mobile = true;
            var _mobile_width = 0;
            if($(_self.element).data('wmAnimateClass'))
            {
                var _class = $(_self.element).data('wmAnimateClass');

                if($(_self.element).data('wmAnimateMobileWidth'))
                {
                    var _val = parseInt(($(_self.element).data('wmAnimateMobileWidth')));
                    if(_val && _val > 0)
                        _mobile_width = _val;
                }
                if(_mobile_width > 0)
                {
                    if(($(_self.element).data('wmAnimateShowMobile') == false) && (((window.innerWidth > 0) ? window.innerWidth : screen.width ) <=  _mobile_width))
                    {
                        _show_mobile = false;
                    }
                }
                if($(_self.element).data('wmAnimateDelay'))
                    _delay = $(_self.element).data('wmAnimateDelay');
                if(_show_mobile)
                {
                    if(direction == 'up')
                    {
                        if($(_self.element).hasClass('animated'))
                            $(_self.element)
                                .removeClass('animated');
                        if($(_self.element).hasClass(_class))
                            $(_self.element)
                                .removeClass(_class);
                    }
                    if(direction == 'down')
                    {
                        if(!$(_self.element).hasClass(_class))
                        {
                            if(_delay)
                            {
                                setTimeout(function(){
                                    $(_self.element)
                                        .addClass(_class);
                                },_delay);
                            }
                            else
                            {
                                $(_self.element)
                                    .addClass(_class);
                            }
                        }
                    }

                    if(!$(_self.element).hasClass('animated'))
                    {
                        $(_self.element)
                            .addClass('animated');
                    }
                }
            }
          },
          offset: '80%'
         //this causes big bug
         //offset: function()
         //{
         //    var _self = this;
         //    var _element = $(_self.element);
         //    var _distance = _element.data('wmAnimateOffset');
         //    if(_distance)
         //    {
         //        console.warn(_distance);
         //        return _distance;
         //    }
         //    return '80%';
         //}
        });

        /**
         * Use any of these animations for the
         * wmAnimateClass data attribute :
         *
         * bounce
         * flash
         * pulse
         * rubberBand
         * shake
         * headShake
         * swing
         * tada
         * wobble
         * jello
         * bounceIn
         * bounceInDown
         * bounceInLeft
         * bounceInRight
         * bounceInUp
         * bounceOut
         * bounceOutDown
         * bounceOutLeft
         * bounceOutRight
         * bounceOutUp
         * fadeIn
         * fadeInDown
         * fadeInDownBig
         * fadeInLeft
         * fadeInLeftBig
         * fadeInRight
         * fadeInRightBig
         * fadeInUp
         * fadeInUpBig
         * fadeOut
         * fadeOutDown
         * fadeOutDownBig
         * fadeOutLeft
         * fadeOutLeftBig
         * fadeOutRight
         * fadeOutRightBig
         * fadeOutUp
         * fadeOutUpBig
         * flipInX
         * flipInY
         * flipOutX
         * flipOutY
         * lightSpeedIn
         * lightSpeedOut
         * rotateIn
         * rotateInDownLeft
         * rotateInDownRight
         * rotateInUpLeft
         * rotateInUpRight
         * rotateOut
         * rotateOutDownLeft
         * rotateOutDownRight
         * rotateOutUpLeft
         * rotateOutUpRight
         * hinge
         * jackInTheBox
         * rollIn
         * rollOut
         * zoomIn
         * zoomInDown
         * zoomInLeft
         * zoomInRight
         * zoomInUp
         * zoomOut
         * zoomOutDown
         * zoomOutLeft
         * zoomOutRight
         * zoomOutUp
         * slideInDown
         * slideInLeft
         * slideInRight
         * slideInUp
         * slideOutDown
         * slideOutLeft
         * slideOutRight
         * slideOutUp
         **/

    });

})(jQuery);
