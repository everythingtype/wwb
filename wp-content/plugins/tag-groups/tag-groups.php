<?php
/*
Plugin Name: Tag Groups
Plugin URI: https://chattymango.com/tag-groups/
Description: Organize your tags in groups and display them in a highly customizable tag cloud (tabs or accordion).
Author: Chatty Mango
Author URI: https://chattymango.com/about/
Version: 0.41.0
License: GNU GENERAL PUBLIC LICENSE, Version 3
Text Domain: tag-groups
Domain Path: /languages
*/

// Don't call this file directly
if ( ! defined( 'ABSPATH' ) ) {

  die;

}

if ( ! defined( 'TAG_GROUPS_PLUGIN_BASENAME' ) ) {

  /**
  * The plugin's relative path (starting below the plugin directory), including the name of this file.
  */
  define( "TAG_GROUPS_PLUGIN_BASENAME", plugin_basename( __FILE__ ) );

  /**
  * The required minimum version of WordPress.
  */
  define( "TAG_GROUPS_MINIMUM_VERSION_WP", "4.0" );

  /**
  * Comma-separated list of default themes that come bundled with this plugin.
  */
  define( "TAG_GROUPS_BUILT_IN_THEMES", "ui-gray,ui-lightness,ui-darkness" );

  /**
  * The theme that is selected by default. Must be among TAG_GROUPS_BUILT_IN_THEMES.
  */
  define( "TAG_GROUPS_STANDARD_THEME", "ui-gray" );

  /**
  * The default number of groups on one page on the edit group screen.
  */
  define( "TAG_GROUPS_ITEMS_PER_PAGE", 20 );

  /**
  * This plugin's last piece of the path, i.e. basically the plugin's name
  */
  define( "TAG_GROUPS_PLUGIN_RELATIVE_PATH", basename( dirname( __FILE__ ) ) );

  /**
  * This plugin's absolute path on this server - starting from root.
  */
  define( "TAG_GROUPS_PLUGIN_ABSOLUTE_PATH", dirname( __FILE__ ) );

  /**
  * The assumed name of the premium plugin, should we need it.
  */
  define( "TAG_GROUPS_PREMIUM_PLUGIN_PATH", WP_PLUGIN_DIR . '/tag-groups-premium' );

  /**
  * The full URL (including protocol) of the RSS channel that informas about updates.
  */
  define( "TAG_GROUPS_UPDATES_RSS_URL", "https://chattymango.com/category/updates/tag-groups-base/feed/" );

}


/*
* Require all classes of this plugin
*/
foreach ( glob( dirname( __FILE__ ) . '/include/*.php') as $filename ) {

    require_once $filename;

}

foreach ( glob( dirname( __FILE__ ) . '/include/shortcodes/*.php') as $filename ) {

    require_once $filename;

}


/**
* add Gutenberg functionality
*/
require_once TAG_GROUPS_PLUGIN_ABSOLUTE_PATH . '/src/init.php';


if ( ! function_exists( 'tag_groups_init' ) ) {

  /**
  * Do all initial stuff: register hooks, check dependencies
  *
  *
  * @param void
  * @return void
  */
  function tag_groups_init() {

    global $tagGroups_Base_instance;

    // URL must be defined after WP has finished loading its settings
    if ( ! defined( 'TAG_GROUPS_PLUGIN_URL' ) ){

      define ( "TAG_GROUPS_PLUGIN_URL", plugins_url( '', __FILE__ ) );

      // start all initializations, registration of hooks, housekeeping, menus, ...
      $tagGroups_Base_instance = new TagGroups_Base();

    }

  }


  add_action( 'plugins_loaded', 'tag_groups_init' );


  register_activation_hook( __FILE__, array( 'TagGroups_Base', 'on_activation' ) );

}


if ( ! function_exists( 'tag_groups_cloud') ) {

  /**
  *
  * Wrapper for the static method tag_groups_cloud
  *
  * @param array $atts
  * @param bool $return_array
  * @return string
  */
  function tag_groups_cloud( $atts = array(), $return_array = false ) {

    return TagGroups_Shortcode_Tabs::tag_groups_cloud( $atts, $return_array );

  }

}


if ( ! function_exists( 'tag_groups_accordion') ) {

  /**
  *
  * Wrapper for the static method tag_groups_accordion
  *
  * @param array $atts
  * @return string
  */
  function tag_groups_accordion( $atts = array() ) {

    return TagGroups_Shortcode_Accordion::tag_groups_accordion( $atts );

  }

}


if ( ! function_exists( 'post_in_tag_group' ) ) {
  /**
  * Checks if the post with $post_id has a tag that is in the tag group with $tag_group_id.
  *
  * @param int $post_id
  * @param int $tag_group_id
  * @return boolean
  */
  function post_in_tag_group( $post_id, $tag_group_id )
  {

    if ( class_exists( 'TagGroups_Premium_Post' ) ) {

      $post = new TagGroups_Premium_Post( $post_id );

      if ( method_exists( $post, 'has_group' ) ) {

        return $post->has_group( $tag_group_id );

      } else {

        return 'not implemented';

      }

    } else {

      $tag_group_taxonomy = get_option( 'tag_group_taxonomy', array('post_tag') );

      $tags = get_the_terms( $post_id, $tag_group_taxonomy );

      if ( $tags ) {

        foreach ( $tags as $tag ) {

          if ( $tag->term_group == $tag_group_id ) {
            return true;
          }
        }
      }

      return false;
    }

  }
}

/**
* guess what - the end
*/
