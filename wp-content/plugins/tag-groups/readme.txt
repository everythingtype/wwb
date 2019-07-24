=== Tag Groups ===
Contributors: camthor
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=FD5ZU4EEBGSC8
Tags: cloud, tags, tag cloud, tabs, accordion, taxonomy, tag, woocommerce
Requires at least: 4.0
Tested up to: 5.0.2
Stable tag: 0.41.0
Requires PHP: 5.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

== Description ==

Organize your tags in groups. Use a highly customizable tag cloud (tabs or accordion).

Most websites mix up tags that should actually be separated by topics: places, people, brands, features, activities and more.

Your tags, however, will become much more useful if you organize them in groups. Imagine a tag cloud where all town names appear under a "Towns" heading or where you keep the names of actors under "Actors" and directors under "Directors". The Tag Groups plugin lets you do exactly that.

With Tag Groups you can add a parent level to tags and make them hierarchical.

Tag Groups works also great with other (flat) taxonomies, like WooCommerce product tags. Filters and bulk actions make your work with tags much easier, and you can even filter the list of posts in your backend by the groups that their tags belong to.

The plugin comes with two tag clouds where tags are sorted by groups. These tag clouds can be customized with numerous parameters, and you can insert them as shortcodes or Gutenberg blocks.

Possible applications are:

* Display only specific tags in your tag cloud.
* Change the sorting order in your tag cloud.
* Customize the links, the text or the separator of your tag cloud items.
* Organize your tags under a parent level.
* Easily manage huge amounts of tags or posts in the backend by dividing them into groups.
* Display your tags grouped by language or by topic.
* Display with each post a selection of tags that are related to the tags of this post.
* Choose which tags to display in different sections of your blog.

Please find more information [here](https://chattymango.com/tag-groups/?pk_campaign=tg&pk_kwd=readme "plugin website").

**If you find this plugin useful, please give it a [5-star rating](https://wordpress.org/support/plugin/tag-groups/reviews/?filter=5 "reviews"). Thank you!**


> ### Extra Features
>
> If you want to get more out of your tag groups, check out [Tag Groups Premium](https://chattymango.com/tag-groups-premium/?pk_campaign=tg&pk_kwd=readme "plugin website"). The premium plugin comes with
>
> * a meta box for the post edit screen so that you can enter and edit post tags segmented by groups;
> * the option to bulk-add all tags of a group to a post with one click;
> * add the same tag to multiple groups;
> * the option to prevent authors from creating new tags;
> * a Dynamic Post Filter for the frontend: Your visitors can pick tags from groups and see all matching posts in a list;
> * custom permissions who can edit tag groups;
> * a new tag cloud where you can combine the tags of specific groups into one cloud;
> * and more.

https://www.youtube.com/watch?v=xonGSR9VswQ

> See the difference between the free and the premium plugin [in this table](https://chattymango.com/tag-groups-base-premium-comparison/?pk_campaign=tg&pk_kwd=readme "feature comparison table").
>
> **You can also [get Tag Groups Premium for free if you help us translate](https://chattymango.com/tag-groups-premium/free-premium-plugin-for-your-help/?pk_campaign=tg&pk_kwd=readme).**


Follow us on [Facebook](https://www.facebook.com/chattymango/) or [Twitter](https://twitter.com/ChattyMango).

= Other Notes =

Styling created by jQuery UI who also provided the JavaScript that is used for the tabs to do their magic. Find their license in the package.

We are also using the SumoSelect JavaScript plugin.

== Installation ==

1. Find the plugin in the list at the backend and click to install it. Or, upload the ZIP file through the admin backend. Or, upload the unzipped tag-groups folder to the /wp-content/plugins/ directory.

2. Activate the plugin through the ‘Plugins’ menu in WordPress.

The plugin will create a new menu "Tag Groups" and a submenu in the Post section (depending on the chosen taxonomy) where you find the tag groups. After you have created some groups, you can edit your tags (or other terms) and assign them to one of these groups. A filter and a bulk action menu are available on the Tags page and you also find a filter on the Posts pages.

The tabbed tag cloud (or an accordion containing the tags) can be inserted with a shortcode or a Gutenberg block. Options are listed under the "Tag Groups" main menu.

Extensive information, examples and help for troubleshooting are listed [here](https://chattymango.com/tag-groups/?pk_campaign=tg&pk_kwd=readme "plugin website").


== Frequently Asked Questions ==

= When I use the shortcode I can see the content but the tags are not displayed in tabs. =

Make sure you have "Use jQuery" checked on the settings page. If you use a plugin for caching pages, purge the cache and see if that helps. If you use plugins for minifying scripts or style sheets, turn them off and purge their caches.

= After an update the styling of the custom tag cloud is lost =

This problem might appear when updating from a version before 0.33 to this or a later version. You can solve it by adding a new parameter to the shortcode:
`div_id="tag-groups-cloud-tabs"`

= How can I use one of these tag clouds in a widget? =

Please use a text widget and insert the shortcode.

= Does this plugin support tags for pages? =

No. Although it might work (with additional 3rd-party pluings), it is not an officially supported feature.

= I need the plugin for a very special purpose - can you help? =

Please check first if the [premium plugin](https://chattymango.com/tag-groups-premium/?pk_campaign=tg&pk_kwd=readme "Tag Groups Premium") can help. If not, you can ask for [support here](https://wordpress.org/support/plugin/tag-groups).

= I am desperately missing my language. But, wait.. is this actually a "frequently asked question"? =

No, unfortunately it isn't. But, nevertheless, I'm glad you asked! You are warmly invited to [help us translate](https://translate.wordpress.org/projects/wp-plugins/tag-groups).

== Screenshots ==

1. Group administration
2. Assigning tags to groups
3. Configuring a tag cloud in Gutenberg
4. Settings
5. Tag cloud ("Blitzer" theme)
6. Accordion ("Blitzer" theme)
7. Group administration
8. Tag cloud with first letters of tags as tabs

== Privacy ==

This plugin does not collect or process any personal user data.

== Changelog ==

= 0.41.0 =

FEATURES

* Added experimental shortcode [tag_groups_alphabet_tabs] to show first letters of tags instead of groups (beta). Try parameters include_letters and exclude_letters="abc". Not yet available as Gutenberg block.

BUG FIXES

* Fixed issues in class.group.php (credits @luke4roads)
* Fixed issues in determining number of tags per posts for a particular group

= 0.40.4 =

BUG FIXES

* Fixed issue when default post_tag taxonomy is used with custom posts

OTHER

* Debugging messages during uninstallation

= 0.40.3 =

OTHER

* Fixed compatibility issues with the latest version of Gutenberg

= 0.40.2 =

BUG FIXES

* Fixed an occasional error when Gutenberg is not installed

OTHER

* Get the latest 100 posts to Gutenberg block

= 0.40.1 =

OTHER

* Adjustments for changes in Gutenberg API (apiFetch replacing wp.api)

= 0.40.0 =

FEATURES

* Compatibility with WPML: Group names can now be translated.

BUG FIXES

* Fixed wrong entry in help search index.
* Fixed wrong HTML structure in settings.

OTHER

* Enhanced keyword search on settings home.
* Some refactoring and removed old WPML function calls.
* Encouragement in system information to upgrade PHP if using an outdated version.


= Older Versions =

The complete changelog is available [here](https://chattymango.com/tag-groups/tag-groups-changelog/?pk_campaign=tg&pk_kwd=readme).


== Upgrade Notice ==

none
