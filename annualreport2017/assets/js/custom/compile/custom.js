require('./_custom_animate_waypoint');
// require('./_maps/_init');
require('./_progress/_index');

jQuery(document).ready(function(){
    $('#im-global-map .inner-panel-panel-heading a.tab').on('click', function(e){
      $('#im-global-map .inner-panel-panel-heading a.tab').not(this).each(function () {
        var el = $(this);
        var id = el.attr('href');
        $(id).removeClass('in');
        el.addClass('collapsed');
        el.attr('aria-expaned', 'false');
      });
      if ($(this).hasClass('collapsed')) {
        $('html').animate({
          scrollTop: $("#im-global-map").offset().top
        }, 600);
      }
    });

    $('.scroll_to').localScroll();

    $('form').submit(function(e){
      if (!$('#GDPR').is(':checked')) {
        e.preventDefault();
        $('#GDPRError').show();
        return false;
      }
      $('#GDPRError').hide();
      return;
    });

    $('#GDPR').on('change', function(e) {
      if (!$(this).is(':checked')) {
        $('#GDPRError').show();
        return;
      }
      $('#GDPRError').hide();
      return;
    });
});