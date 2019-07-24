'use strict';

var $slider = $('.owl-carousel');
var owl;
var curr_slide = 0;
var curr_stop = 0;

function setWidths() {

  $('.caption, .header').css('width', $slider.css('width'));
  $('.captions').each(function() {
    $(this).css('width', $(this).find('.caption').length * $slider.width() + 'px');
  });

  $('.headers').each(function() {
    $(this).css('width', $(this).find('.header').length * $slider.width() + 'px');
  });

}

function adjustWidths() {

  var $cslide = $('.owl-item').eq(curr_slide - 1);
  var $headers = $cslide.find('.headers');
  var $captions = $cslide.find('.captions');
  var hdr_pos = 0;
  var cap_pos = 0;

  $('.caption, .header').css('width', $slider.css('width'));
  $('.captions').each(function() {
    $(this).css('width', $(this).find('.caption').length * $slider.width() + 'px');
  });

  $('.headers').each(function() {
    $(this).css('width', $(this).find('.header').length * $slider.width() + 'px');
  });

  hdr_pos = -((curr_stop - 1) * $headers.parent('.header-wrap').width()) + 'px';
  cap_pos = -((curr_stop - 1) * $captions.parent('.caption-wrap').width()) + 'px';
  $headers.css('left', cap_pos);
  $captions.css('left', cap_pos);

}

function toggleIndNav(slide, stop) {

  // toggle entire nav
  if ((slide == 3 && stop == 3) || slide > 3) {
    $('.ind-nav').addClass('active');
  } else if (slide <= 3) {
    $('.ind-nav').removeClass('active');
  }

  // toggle "home" nav button
  if (slide > 3) {
    $('.ind-nav .ind-intro').addClass('active');
  } else if (slide == 3 && stop < 3) {
    $('.ind-nav .ind-intro').removeClass('active');
  }

  if (slide > 13) {
    $('.ind-nav li').not('.ind-intro').removeClass('active');
  } else {
    $('.ind-nav li').not('.ind-intro, .active').addClass('active');
  }

  // toggle "current" nav state
  if (slide == 4) {
    $('.ind-nav .ind-1').addClass('current').siblings().removeClass('current');
  } else if (slide == 6 || slide == 7) {
    $('.ind-nav .ind-2').addClass('current').siblings().removeClass('current');
  } else if (slide == 8 || slide == 9) {
    $('.ind-nav .ind-3').addClass('current').siblings().removeClass('current');
  } else if (slide == 10 || slide == 11) {
    $('.ind-nav .ind-4').addClass('current').siblings().removeClass('current');
  } else if (slide == 12 || slide == 13) {
    $('.ind-nav .ind-5').addClass('current').siblings().removeClass('current');
  }

}

function toggleBG(slide, stop) {

  if (slide == 15 && stop == 8) {
    $('.owl-item').last().find('.slide').addClass('bg-fade');
  } else {
    $('.owl-item').last().find('.slide').removeClass('bg-fade');
  }

}

function jumpToStop(slide, stop) {

  var $cslide = $('.owl-item').eq(owl.currentItem);
  var $nslide = $('.owl-item').eq(slide - 1);
  var $nclip;
  var $headers = $nslide.find('.headers');
  var $captions = $nslide.find('.captions');
  var hdr_pos = 0;
  var cap_pos = 0;

  owl.jumpTo(slide - 1);

  // set new active video (swap), reset, & play
  if ($nslide.find('video, img.still').length) {

    if (!Modernizr.appleios) {
      $nclip = $nslide.find('video').removeClass('active').filter(function(){ return $(this).data('stop') == stop });  
    } else {
      $nclip = $nslide.find('img.still').removeClass('active').filter(function(){ return $(this).data('stop') == stop });
    }

    if (!Modernizr.appleios) $nclip.get(0).currentTime = 0;
    $nclip.addClass('active');
    $nclip.off('seeked');
    if (!Modernizr.appleios) $nclip.on('seeked', function(){ $nclip.get(0).play(); });
  }

  // slide captions & headers in as necessary for this stop & play video
  hdr_pos = -((stop - 1) * $headers.parent('.header-wrap').width()) + 'px';
  cap_pos = -((stop - 1) * $captions.parent('.caption-wrap').width()) + 'px';
  $headers.css('left', hdr_pos);
  $captions.css('left', cap_pos);

  if (slide >= $('.slide').length && stop >= $nslide.find('.clips').children().length){
    $('.owl-next').fadeOut(400);
  } else {
    $('.owl-next').fadeIn(400);
  }

  $('.owl-pagination .owl-page').removeClass('active').filter(function() {
    return $(this).data('slide') == slide && $(this).data('stop') == stop;
  }).addClass('active');

  curr_slide = slide;
  curr_stop = stop;

  toggleIndNav(slide, stop);
  toggleBG(slide, stop);

}

function nextStop() {

  var stop = 0;
  var slide = 0;
  var $cslide = $('.owl-item').eq(owl.currentItem);
  var $nslide;
  var $nclip;
  var $headers;
  var $captions;
  var hdr_pos = 0;
  var cap_pos = 0;

  // next stop?
  if (
      (!Modernizr.appleios && $cslide.find('video').length > 1 && $cslide.find('video:last-child').filter('.active').length == 0)
      || (Modernizr.appleios && $cslide.find('img.still').length > 1 && $cslide.find('img.still:last-child').filter('.active').length == 0)
    ) {

    // set new active video (swap), reset & play
    if ($cslide.find('video.active, img.still.active').length) {

      if (!Modernizr.appleios) {
        $nclip = $cslide.find('video.active').off('seeked').next();
      } else {
        $nclip = $cslide.find('img.still.active').off('seeked').next();
      }
      
    } else {
      if (!Modernizr.appleios) {
        $nclip = $cslide.find('video:first-child');
      } else {
        $nclip = $cslide.find('img.still:first-child');
      }
    }

    if (!Modernizr.appleios) {

      $nclip.get(0).currentTime = 0;

      $nclip.off('seeked');
      $nclip.on('seeked', function(){

        $nclip.get(0).pause();
        $cslide.find('video.active').removeClass('active');
        $nclip.addClass('active');
        $nclip.get(0).play();

      });

    } else {
      $cslide.find('img.still.active').removeClass('active');
      $nclip.addClass('active');
    }
    
    //$cslide.find('video.active').prev().get(0).currentTime = 0;

    // set stop pos & slide index for new video
    stop = $nclip.data('stop');
    slide = owl.currentItem + 1;

    // slide captions & headers in as necessary for this stop & play video
    $headers = $cslide.find('.headers');
    $captions = $cslide.find('.captions');
    hdr_pos = -((stop - 1) * $headers.parent('.header-wrap').width()) + 'px';
    cap_pos = -((stop - 1) * $captions.parent('.caption-wrap').width()) + 'px';
    $headers.css('left', hdr_pos);
    $captions.css('left', cap_pos);

    if (slide >= $('.slide').length && stop >= $cslide.find('video').length){
      $('.owl-next').fadeOut(400);
    }

  // or next slide?
  } else {
    stop = 1;
    slide = owl.currentItem + 2;

    $nslide = $('.owl-item').eq(slide - 1);

    $headers = $nslide.find('.headers');
    $captions = $nslide.find('.captions');
    $headers.css('left', hdr_pos);
    $captions.css('left', cap_pos);

    owl.next();

    if (!Modernizr.appleios) {

      if ($nslide.find('video').length) {
        $nslide.find('video').removeClass('active');
        $nslide.find('video:first-child').addClass('active');
        $nslide.find('video.active').get(0).currentTime = 0;
        $nslide.find('video.active').get(0).play();
      }

    } else {

      if ($nslide.find('img.still').length) {
        $nslide.find('img.still').removeClass('active');
        $nslide.find('img.still:first-child').addClass('active');
      }

    }
  }

  $('.owl-pagination .owl-page').removeClass('active').filter(function() {
    return $(this).data('slide') == slide && $(this).data('stop') == stop;
  }).addClass('active');

  curr_slide = slide;
  curr_stop = stop;

  toggleIndNav(slide, stop);
  toggleBG(slide, stop);

}

function prevStop() {

  var stop = 0;
  var slide = 0;
  var $cslide = $('.owl-item').eq(owl.currentItem);
  var $pslide;
  var $pclip;
  var $headers;
  var $captions;
  var hdr_pos = 0;
  var cap_pos = 0;

  // prev stop?
  if (
      (!Modernizr.appleios && $cslide.find('video').length > 1 && $cslide.find('video:first-child').filter('.active').length == 0)
      || (Modernizr.appleios && $cslide.find('img.still').length > 1 && $cslide.find('img.still:first-child').filter('.active').length == 0)
    ) {

    $headers = $cslide.find('.headers');
    $captions = $cslide.find('.captions');

    // set new active video (swap), reset & play
    if (!Modernizr.appleios) {
      $pclip = $cslide.find('video.active').removeClass('active').prev();
      $pclip.get(0).currentTime = 0;
      $pclip.addClass('active');
      $pclip.off('seeked');
      $pclip.on('seeked', function(){ $pclip.get(0).play(); });
    } else {
      $pclip = $cslide.find('img.still.active').removeClass('active').prev();
      $pclip.addClass('active');
    }

    // set stop pos & slide index for new video
    stop = $pclip.data('stop');
    slide = owl.currentItem + 1;

    // slide captions & headers in as necessary for this stop & play video
    hdr_pos = -((stop - 1) * $headers.parent('.header-wrap').width()) + 'px';
    cap_pos = -((stop - 1) * $captions.parent('.caption-wrap').width()) + 'px';
    $headers.css('left', hdr_pos);
    $captions.css('left', cap_pos);

    if (slide < $('.slide').length || stop < $cslide.find('video').length){
      $('.owl-next').fadeIn(400);
    }

  // prev slide
  } else {

    slide = owl.currentItem;

    $pslide = $('.owl-item').eq(slide - 1);

    if ($pslide.find('video:last-child').length) {
      stop = $pslide.find('video:last-child').data('stop');
    } else {
      stop = 1;
    }

    $headers = $pslide.find('.headers');
    $captions = $pslide.find('.captions');
    hdr_pos = -((stop - 1) * $headers.parent('.header-wrap').width()) + 'px';
    cap_pos = -((stop - 1) * $captions.parent('.caption-wrap').width()) + 'px';
    $headers.css('left', hdr_pos);
    $captions.css('left', cap_pos);

    owl.prev();

    if (!Modernizr.appleios) {
      $pslide.find('video').removeClass('active');
      $pslide.find('video').get(0).currentTime = 0;
      $pslide.find('video').last().addClass('active');
      $pslide.find('video.active').get(0).play();
    } else {
      $pslide.find('img.still').removeClass('active');
      $pslide.find('img.still').last().addClass('active');
    }

  }

  $('.owl-pagination .owl-page').removeClass('active').filter(function() {
    return $(this).data('slide') == slide && $(this).data('stop') == stop;
  }).addClass('active');

  curr_slide = slide;
  curr_stop = stop;

  toggleIndNav(slide, stop);
  toggleBG(slide, stop);

}

// end functions

$slider.owlCarousel({
  singleItem: true,
  autoPlay: false,
  pagination: false,
  touchDrag: false,
  mouseDrag: false,
  slideSpeed: 400,
  addClassActive: true,
  afterInit: function() {
    setWidths();
  },
  afterUpdate: function() {
    adjustWidths();
  },
  afterMove: function() {

    //curr = this.owl.currentItem;

    if (this.owl.currentItem == 0) {
      $('.banner, .owl-prev').fadeOut(400);
    } else {
      $('.banner, .owl-prev').fadeIn(400);
    }

  }
});

owl = $slider.data('owlCarousel');

$('.owl-prev').on('click tap', prevStop);

$('.owl-next').on('click tap', nextStop);

$('.owl-pagination .owl-page').on('click tap', function() {
  var slide = $(this).data('slide');
  var stop = $(this).data('stop');

  jumpToStop(slide, stop);
});

$('.ind-nav li').on('click tap', function() {
  var slide = $(this).data('slide');
  var stop = $(this).data('stop');

  jumpToStop(slide, stop);
});

$('.share-btn').on('click tap', function (e) {
  e.stopPropagation();
  $('.sharebox').fadeToggle(400);
});

$('*').not('.share-btn, .sharebox').on('click tap', function() {
  $('.sharebox').fadeOut(400);
});
