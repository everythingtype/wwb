#### 1.9.0: January 15, 2019
* Improvement: Complete rework of ignored words. Added many new words to 57 languages.
* Tweak: Added filter to exclude currently linked posts from get_related_posts() method (rp4wp_get_related_exclude_already_linked).
* Tweak: Removed 'fixed' class from related word meta box to prevent conflict with AFC.

#### 1.8.1: November 22, 2018
* Improvement: Add en_GB ignored words so we can properly support British English as well.
* Tweak: Fixed a label error in the backend when a link of a deleted custom post type still existed.

#### 1.8.0: November 14, 2018
* Feature: Added 'heading_text' attribute to [rp4wp] shortcode.
* Tweak: Added an improved way of normalizing characters.
* Tweak: Fixed an issue where the first image in the content was not found correctly.

#### 1.7.5: March 19, 2018
* Tweak: Make sure installer is run from options-general.php to prevent capability issues

#### 1.7.4: February 12, 2018
* Tweak: Removed hard removal of non a-z0-9 characters because this removes all non-latin chars causing issues for non-latin languages. Instead we're now using a specific blacklist of characters that needs to be removed. Also moved this to convert_characters so we apply this blacklist also to title,taxonomy,etc.
* Tweak: Made case lowering of words in cache UTF-8 compatible, solving an issue with non-latin characters.
* Tweak: Manually linked posts now maintain order on relink.
* Tweak: Added filter 'rp4wp_cache_word_amount' to filter amount of words added in cache (default=6).

#### 1.7.3: October 10, 2017
* Tweak: Fixed an issue where sticky posts were always included in related posts.

#### 1.7.2: October 4, 2017
* Tweak: Fixed an issue that caused mixed post types to not be correctly displayed on frontend.
* Tweak: Always deactivate local license on deactivation request.
* Tweak: Tweaked default CSS to force specific CSS rules.
* Tweak: Added clearer installation instructions to installation wizard step 1.
* Tweak: Updated various translations.

#### 1.7.1: September 7, 2017
* Tweak: Fixed an issue where category limitation didn't work correctly.

#### 1.7.0: September 7, 2017
* Feature: Added the option to set output template in widget.
* Feature: New related post is found for parents of posts that are put back to draft or deleted.
* Feature: Added WooCommerce price component.
* Feature: Added option to only find related posts in certain categories. See new 'categories' tab.
* Tweak: Rewrite of installation procedure so we can properly handle activation of plugin when free plugin is still active. 
* Tweak: Fixed jQuery lib include for HTTPS websites
* Tweak: Fixed incorrect matching of duplicate related content.
* Tweak: Fixed incorrect matching of non-published content.
* Tweak: Fixed an issue with related content count being incorrect on automated related post append.
* Tweak: UL class rp4wp-posts-list now has a default width of 100%.
* Tweak: Added 'rp4wp_max_post_age' filter.
* Tweak: Added 'rp4wp_excluded_ids' filter.
* Tweak: Plugin is now checking if required mbstring PHP extension is installed.
* Tweak: Fixed a conflict with other plugins that use the SweetAlert library.
* Tweak: Updated various translations.

#### 1.6.0: August 25, 2016
* Feature: Added Taxonomy component to Configurator.
* Feature: Added Post Author component to Configurator.
* Feature: Added Post Date component to Configurator.
* Feature: Added Read More component to Configurator.
* Feature: Added 'reset to default' option to weights.
* Tweak: Improved Content Matching Score algorithm. Better related content result.
* Tweak: Added post dates to manual linking screen.
* Tweak: Fixed Configurator Component Tool tips.
* Tweak: Added 'rp4wp_related_posts_list' filter to generate_related_posts_list().
* Tweak: Added 'rp4wp_custom_tax_weight' filter allowing you to filter custom category weight different per custom taxonomy and term.
* Tweak: German translation update. Props Silvan Hagen.

#### 1.5.9: June 24, 2016
* Tweak: Added 'rp4wp_thumbnail_use_inline_images' filter to prevent the use of inline images.
* Tweak: Fixed issue with search queries with multiple words in manual post linking

#### 1.5.8: May 12, 2016
* Tweak: Fixed a cross post-type caching bug.
* Tweak: Fixed a bug that caused the word caching progress bar to be incorrect.
* Tweak: Removed DateTime method chaining as PHP 5.2 doesn't support this.

#### 1.5.7: May 2, 2016
* Tweak: Always load template functions because some plugins execute shortcode/widget related code in admin.
* Tweak: Added widget-title class to h3 element in widget template.
* Tweak: Various performance optimizations, props [Danny van Kooten](https://github.com/dannyvankooten).
* Tweak: Fixed a bug that caused the search query to reset when navigating through pages on the manual linking page.
* Tweak: Properly escaping page request variable in manual linking screen now.
* Tweak: Added check if post type is installed before words from cache are cleared.
* Tweak: Various translations updates.

#### 1.5.6: January 22, 2016
* Tweak: Fixed a bug where thumbnails were not loaded when images in posts were not full size.

#### 1.5.5: January 11, 2016
* Tweak: Fixed a bug that caused too many related posts to be created when related posts were added prior publishing the post.

#### 1.5.4: December 30, 2015
* Tweak: Fixed a shortcode bug where the output would be placed on top of the post.

#### 1.5.3: November 25, 2015
* Tweak: Fixed an post exclusion related SQL error causing posts not to link.

#### 1.5.2: November 18, 2015
* Tweak: Fixed a bug where post exclude data got corrupted causing posts not to related properly.
* Tweak: Fixed a manual post linking bug that caused the post title to be missing, props [iCulture](iCulture.nl).
* Tweak: Also remove newly added options on uninstall when clean_on_uninstall is checked.

#### 1.5.1: November 12, 2015
* Tweak: Fixed a thumbnail bug when cross linking different post types.
* Tweak: Fixed a bug where extra added ignored words where not properly used.
* Tweak: Updated Dutch ignored words.

#### 1.5.0: October 28, 2015
* Feature: Thumbnail size can be set via options per post type.
* Feature: Use first image in content if no featured image is set.
* Feature: Added ability to set a placeholder image for if no image is found.
* Feature: Added ability to relink related posts from installer.
* Feature: Added ability to reinstall related posts from installer.
* Feature: Added joined-words feature. Allow multiple words to be parsed as one word.
* Feature: Added Brazilian Portuguese commonly used words.
* Feature: Added Czech commonly used words.
* Feature: Added Bulgarian commonly used words.
* Feature: Added Russian commonly used words.
* Feature: Added Swedish commonly used words.
* Feature: Added Spanish commonly used words.
* Feature: Added Norwegian Bokmål commonly used words.
* Tweak: Display Post Type in backend meta box.
* Tweak: Major install performance enhancements.
* Tweak: Added filter: rp4wp_get_children_link_args in RP4WP_Post_Link_Manager:get_children().
* Tweak: Added filter: rp4wp_get_children_child_args in RP4WP_Post_Link_Manager:get_children()
* Tweak: Strip all non letters or number characters from content.
* Tweak: Load Google API jQueryUI assets over HTTPS.
* Tweak: Fixed an issue with encoding non ASCII characters.
* Tweak: Improved trimming of punctuation in words.
* Tweak: Added new and improved CLI feedback to CLI commands.
* Tweak: CSS frontend tweaks to correct small align issues.

#### 1.4.3: September 1, 2015
* Tweak: Various license related improvements.

#### 1.4.2: July 29, 2015
* Tweak: Added id attribute to [rp4wp] shortcode.
* Tweak: Added limit attribute to [rp4wp] shortcode.
* Tweak: Added template attribute to [rp4wp] shortcode.
* Tweak: Added limit argument to rp4wp_children template function.
* Tweak: Set post type per link to already existing links for existing free links on activation.

#### 1.4.1: June 10, 2015
* Tweak: Fixed error caused when saving configurator, moved usort callback to separate method.
* Tweak: Made post age column filterable with filter 'rp4wp_post_age_column'.

#### 1.4.0: June 7, 2015
* Feature: Related Post Configurator. Full control on how your related posts are displayed.
* Feature: Installer is now displaying total number of posts todo and done.
* Feature: Added pagination to manual post link table.
* Feature: Widget now loads it's own template file.
* Feature: Added ability to exclude posts from being related.
* Feature: Added possibility to only link posts in past X days (option per post type).
* Feature: CLI - New command: install
* Feature: CLI - New command: cache
* Feature: CLI - New command: link
* Feature: CLI - New command: remove_related
* Tweak: Fixed a bug that caused first time site activation problems.
* Tweak: Fixed a bug that caused CSS not to be loaded on pages (is_singular instead of is_single).
* Tweak: Fixed a backend image path bug.
* Tweak: Check if post types are used in other relations before deleting word cache.
* Tweak: generate_related_posts_list method now has a template file parameter.
* Tweak: rp4wp_children function now has a template parameter.
* Tweak: Show love HTML is now a template part.
* Tweak: Removed 'display image' option, configurator will take care of this.
* Tweak: Removed 'Styling' tab and options in favor of configurator.
* Tweak: Updated French stop words.
* Tweak: Implemented Composer autoloader in favor of custom autoloader.
* Tweak: Loading hooks and filters from static files now instead of dynamic directory loading.
* Tweak: Updated translations.

#### 1.3.4: April 20, 2015
* Escaped view filter URL when manually linking posts to prevent possible XSS.

#### 1.3.3: March 26, 2015
* Feature: Add option to disable SSL verification in licensing requests.
* Fix: Check if settings is set in step 3 of installer to prevent fatal error.
* Tweak: Made themes filterable, new filter: rp4wp_themes.
* Tweak: Added premium constant to detect Premium version of plugin.
* Tweak: Added 'rp4wp_get_related_posts_sql' filter to alter related posts SQL.
* Tweak: Added 'rp4wp_ignored_words_lang' filter to alter ignored words.

#### 1.3.2: January 5, 2015
* Fixed a bug where UTF-8 encoded characters were not correctly parsed.
* Introduced icon alternative for when iconv isn't installed on server.
* Added CSS media query to themes, mobile is always one column.
* Display Post Type labels instead of raw post type name on settings page.

#### 1.3.1: January 1, 2015
* Now preventing double form submitting in settings screen.
* Added 'show love' option.
* Added related post object as second parameter to 'rp4wp_post_title' filter.

#### 1.3.0: December 28, 2014
* Added cross post type related posts.
* Moved license settings to as separate tab in settings.
* Added nonces to all AJAX calls in wizard.
* Made related Posts block title WPML string translatable.
* Added translations: French, Italian, Portuguese, Portuguese (Brazil), Swedish.
* Updated translations: Dutch, German, Serbian.

#### 1.2.8: December 19, 2014
* Added the possibility of using post meta by filter.

#### 1.2.7: December 18, 2014
* Fixed an updater conflict.
* Improved template system.

#### 1.2.6: December 3, 2014
* Fixed a widget error.
* Fixed a shortcode error.

#### 1.2.5: December 2, 2014
* Implemented automatic RTL detection.

#### 1.2.4: December 1, 2014
* Added RTL support.
* Fixed a bug where only posts where cached & linked.
* Fixed a bug where scheduled posts where not linked.

#### 1.2.3: November 25, 2014
* Added related post id as second argument to 'rp4wp_post_excerpt' filter.
* Only run upgrade script if there are posts to upgrade.

#### 1.2.2: November 18, 2014
* Fixed an free to premium upgrade bug.

#### 1.2.1: November 17, 2014
* Fixed an excerpt length bug.
* Added a dynamic per option filter.
* Display notice per setting if overwritten by filter.

#### 1.2.0: November 14, 2014
* Added full Network / Multisite support.
* Fixed a hardcoded database table bug.

#### 1.1.2: November 12, 2014
* Fixed multisite/network compatibility.
* Fixed an UTF-8 - iconv bug.
* Remove shortcodes from the related posts excerpt.

#### 1.1.1: November 7, 2014
* Fixed a display thumbnail bug.
* Fixed an auto post link on new post bug.
* Added filter 'rp4wp_disable_css' to disable all CSS generated on website.

#### 1.1.0: October 30, 2014
* Weights are now manageable via options, see weights tab.
* Implemented a template system.
* Added filter 'rp4wp_post_title'.
* Added filter 'rp4wp_post_excerpt'.
* Manually added links (starting this release) will no longer be deleted on de-/installation.

#### 1.0.2: October 28, 2014
* Fixed a bug where permission were checked to soon.
* Fixed a rp4wp_children template function bug.
* Removed an unused query var.
* Updated Dutch, German, Serbian, Swedish translations.

#### 1.0.1: October 17, 2014
* Fixed a link screen post type bug.
* Fixed a "Skip linking" button bug.
* Dutch translation update.

#### 1.0.0: October 14, 2014
* Initial release
