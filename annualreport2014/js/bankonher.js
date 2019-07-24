
// Videos
var tag = document.createElement('script');
tag.src = "//www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

// make player array
var players = new Array();

function onYouTubeIframeAPIReady() {
players[0] = new YT.Player('player1', {
videoId: 'N5ExKtjGVPw'
});
players[1] = new YT.Player('player2', {
videoId: 'MZwndujGpCc'
});
players[2] = new YT.Player('player3', {
videoId: 'jhEprHVty-E'
});
players[3] = new YT.Player('player4', {
videoId: 'XQLGB9aPaLc'
});

players[4] = new YT.Player('player5', {
videoId: 'dCjOhvI-ft0'
});

players[5] = new YT.Player('player6', {
videoId: '43bcSvr_fNk'
});

players[6] = new YT.Player('player7', {
videoId: 'HbIrTwcbD0U'
});

players[7] = new YT.Player('player8', {
videoId: 'F1z6YxYDMFo'
});

}

$(window).resize(function() {
	header_size();
});

$(document).ready(function(){
// header Size
header_size();

// Carousels

$("#owl-process").owlCarousel({
	items : 5,
	navigation: true,
	pagination: false,
	itemsMobile:[479,1],
});

$("#owl-who").owlCarousel({
	items :3,
	navigation: true,
	pagination: false,
	itemsMobile:[1100,1],
});

$("#owl-work").owlCarousel({
	items :5,
	navigation: false,
	pagination: false,
	itemsMobile:[720,2.5],
});

$("#owl-footprint").owlCarousel({
	items :5,
	navigation: false,
	pagination: false,
	itemsMobile:[720,2.5],
});


$('.toggle-nav').click(function() {
      $('body').toggleClass('show-nav');
      return false;
});


$('#navigation a').click(function() {
      $('body').removeClass('show-nav');
});

// Flexslider
$('.flexslider').flexslider({
	animation: "slide",
	animationLoop: true,
	directionNav: true,
	controlNav: false,
	controlsContainer: '.flexinav',
});

// Map

$('.map-menu li').click(function(){
	$('.map-menu li.active, .map-details .active').removeClass('active');
	var image = $(this).attr('data-image');
	$('#mapimg').attr('src', image);
	$(this).addClass('active');
	$('.map-details [data-image="'+image+'"]').addClass('active');
});

//SCROLLING

$("a.scroll[href^='#']").on('click', function(e) {
	var baroffset = $('.bar').height();
	e.preventDefault();
	var hash = this.hash;
	$('html, body').animate({ scrollTop: $(this.hash).offset().top + baroffset}, 800, function(){window.location.hash = hash;});
	stopPlaying();
	if ($(this).hasClass('trigger')){
		$(this.hash + ' .section-toggle').delay(1900).click();
	}
});

// Errors
if(document.location.search.length){
	$(window).load(function() {
  $("html, body").animate({ scrollTop: $(document).height() }, 1000);
});
}

// Our owl-work
$('#owl-work .item').click(function(){
	$('#our-work .tab').removeClass('active');
	var tab = $(this).attr('data-tab');
	$('#owl-work .active').removeClass('active');
	$(this).addClass('active');
	$('#'+tab).addClass('active');
	stopPlaying();
});

// Our owl-work
$('#owl-footprint .item').click(function(){
	$('#global-footprint .footprint').removeClass('active');
	var tab = $(this).attr('data-tab');
	$('#owl-footprint .active').removeClass('active');
	$(this).addClass('active');
	$('#'+tab).addClass('active');
	stopPlaying();
});

//owl-financial
$('#owl-financial .item').click(function(){
	$('#financial-statements .financial').removeClass('active');
	var tab = $(this).attr('data-tab');
	$('#owl-financial .active').removeClass('active');
	$(this).addClass('active');
	$('#'+tab).addClass('active');
	stopPlaying();
});

// owl friends
$('#owl-friends .item').click(function(){
	$('#friends-statements .friends').removeClass('active');
	var tab = $(this).attr('data-tab');
	$('#owl-friends .active').removeClass('active');
	$(this).addClass('active');
	$('#'+tab).addClass('active');
	stopPlaying();
});

// Sections
$('.section-toggle').click(function(){
	$(this).parent().find('.section').slideToggle();
	$(this).toggleClass('open');
});

// Video toggleClass// Sections
$('.video-toggle').click(function(){
	$(this).parent().parent().find('.video-container').toggle();
	$(this).toggleClass('open');
	stopPlaying();
});

// Table Shifter
var table = 1;
$('#table-shifter').click(function(){
$('#activity-table td.col1,#activity-table td.col2,#activity-table td.col3,#activity-table td.col4,#activity-table td.col5,#activity-table td.colhide').hide();

if (table < 5){
	table = table + 1;
	}
else {
	table = 1;
}

$('#activity-table td.col'+table).show();

});


// Table Shifter 4 - friends activities

$('#table-shifter4').click(function(){
$('#friends-activity-table td.col1,#friends-activity-table td.col2,#friends-activity-table td.col3,#friends-activity-table td.col4,#friends-activity-table td.col5,#friends-activity-table td.colhide').hide();

if (table < 5){
	table = table + 1;
	}
else {
	table = 1;
}

$('#friends-activity-table td.col'+table).show();

});



// Table Shifter 2

var table2 = 1;
$('#table-shifter2').click(function(){
$('#financial-position-table td.col1').toggle();
$('#financial-position-table td.col2').toggle();

});

var table3 = 1;
$('#table-shifter3').click(function(){
$('#friends-position-table td.col1').toggle();
$('#friends-position-table td.col2').toggle();

});

// Twitter Shifter

var tweet = 1;
$('.twitter-change').click(function(){
$('.twrap').hide();

if (tweet < 6){
	tweet = tweet + 1;
	}
else {
	tweet = 1;
}

$('.twrap[data-twitter="twitter-'+tweet+'"]').show();

});


// Static header
$('.intro').bind('inview', function(event, isInView, visiblePartX, visiblePartY) {
  if (isInView) {
    // element is now visible in the viewport
  $('.bar').removeClass('sticky');
  } else {
   $('.bar').addClass('sticky');
  }
});



// Form Labels
 $(".pledge-form form :input").each(function(index, elem) {
    var eId = $(elem).attr("id");
    var label = null;
    if (eId && (label = $(elem).parents("form").find("label[for="+eId+"]")).length == 1) {
        $(elem).attr("placeholder", $(label).html());
        $(label).remove();
    }
 });

// Events
$('#our-work .owl-item .item').off('click.ga').on('click.ga', function() { _gaq.push(['_trackEvent','our-work', $(this).data('tab')]);});
$('#owl-footprint .owl-item .item').off('click.ga').on('click.ga', function() { _gaq.push(['_trackEvent','global-footprint', $(this).data('tab')]);});
$('#owl-financial .item').off('click.ga').on('click.ga', function() { _gaq.push(['_trackEvent','financial-statements', $(this).data('tab')]);});
$('#owl-friends .item').off('click.ga').on('click.ga', function() { _gaq.push(['_trackEvent','friends-statements', $(this).data('tab')]);});
$('a.section-toggle').off('click.ga').on('click.ga', function() { _gaq.push(['_trackEvent','section-dropdowns', $(this).parent().find('h2').text()]);});
$('#navigation a').off('click.ga').on('click.ga', function() { _gaq.push(['_trackEvent','navigation', $(this).text()]);});
$('.map-menu a').off('click.ga').on('click.ga', function() { _gaq.push(['_trackEvent','our-impact', $(this).text()]);});
$('.flex-direction-nav a').off('click.ga').on('click.ga', function() { _gaq.push(['_trackEvent','slideshow', $(this).text()]);});
$('a.blue').off('click.ga').on('click.ga', function() { _gaq.push(['_trackEvent','blog', $(this).attr('href')]);});



// end ready
});

// Home rotator Size
function header_size() {
var frame = $( window ).height();
	$('.header').css('height', frame);
}

// Stop players

function stopPlaying() {
	for (var i in players) {
        var player = players[i];
        player.pauseVideo();
    }
}