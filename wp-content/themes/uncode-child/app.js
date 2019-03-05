(function($) {
  $( document ).ready(function() {
    $('.team-members-section #board').remove();
    $('.team-members-section div[data-id="staff"]').find('.t-entry-visual').remove();
    $('.team-members-section .tab-content a').each(function(index, anchorTag) {
      var $anchorTag = $(anchorTag);
      if ($anchorTag.html().trim() === "") {
        $anchorTag.remove();
      }
    });

    if ($(window).width() <= 959 ) {
      truncateAllBlogTitles();
      $( document ).ajaxComplete(truncateAllBlogTitles);
    }
    
    addFirstLevelItemOnDropdownMenuNav();
    addSpinnerElementAbovePagination();
  });

  function truncateAllBlogTitles() {
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

  function addFirstLevelItemOnDropdownMenuNav () {
    var $firstLevelNavItems = $('.menu-primary-inner > li');
    $firstLevelNavItems.each(function(index, item) {
      var itemText = $(item).children('a.dropdown-toggle').text();
      var itemLink = $(item).children('a.dropdown-toggle').attr('href');
      var $dropMenu = $(item).find('.drop-menu');

      if ($dropMenu.length) {
        var liElementClasses = 'menu-item menu-item-type-custom menu-item-object-custom';
        $dropMenu.prepend('<li class="'+ liElementClasses +'"><a title="'+ itemText +'" href="'+ itemLink +'">'+ itemText +'</a></li>');
        $(item).children('a.dropdown-toggle').removeAttr('href');
      }
    });
  }

  function addSpinnerElementAbovePagination() {
    $('.isotope-pagination').after('<div class="lds-ring"><div></div><div></div><div></div><div></div></div>');
  }
})(jQuery)