(function($) {
  $( document ).ready(function() {
    $('.team-members-section #board').remove();
    $('.team-members-section div[data-id="staff"]').find('.t-entry-visual').remove();

    if ($(window).width() <=800 ) {
      truncateAllBlogTitleInMobile();
      $( document ).ajaxComplete(truncateAllBlogTitleInMobile);
    }    
  });

  function truncateAllBlogTitleInMobile() {
    var $postEntryTitles = $('.t-entry-title a');

    $postEntryTitles.each(function(index, titleLink) {
      var $title = $(titleLink);
      var titleText = $title.text();
      var lastThreeCharacters = titleText.substring(titleText.length - 3);

      if (titleText.length > 60 && lastThreeCharacters !== '...') {
        var truncatedText = titleText.substring(0, 60)
          .split(" ")
          .slice(0, -1)
          .join(" ") + "...";
        
        $title.text(truncatedText);
      }
    });
  }
})(jQuery)