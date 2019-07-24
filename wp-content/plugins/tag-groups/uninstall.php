<?php

/*

This script is executed when the (inactive) plugin is deleted through the admin backend.

It removes the plugin settings from the option table and all tag groups. It does not change the term_group field of the taxonomies.

last change for version 0.31.2
*/


/**
* Delete options only if requested
*/
if ( defined( 'WP_UNINSTALL_PLUGIN' ) ) {

  if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

    error_log( '[Tag Groups Premium] Starting uninstall routine.' );

  }

  /**
  * Purge cache
  */
  require_once plugin_dir_path( __FILE__ ) . 'include/class.cache.php';

  if ( class_exists( 'ChattyMango_Cache' ) ) {
    $cache = new ChattyMango_Cache();
    $cache
    ->type( get_option( 'tag_group_object_cache', ChattyMango_Cache::WP_OPTIONS ) )
    ->path( WP_CONTENT_DIR . '/chatty-mango/cache/' )
    ->purge_all();
  }



  $tag_group_reset_when_uninstall = get_option( 'tag_group_reset_when_uninstall', 0 );

  $tag_group_premium_version = get_option( 'tag_group_premium_version', false );

  $option_count = 0;

  if ( $tag_group_reset_when_uninstall && ! $tag_group_premium_version ) {

    require_once dirname( __FILE__ ) . '/include/class.options.php';

    $tagGroups_options = new TagGroups_Options();

    $option_names = $tagGroups_options->get_option_names();

    if ( isset( $option_names[ 'tag_group_group_languages' ] ) ) {

      foreach ( $option_names[ 'tag_group_group_languages' ] as $language ) {

        if ( delete_option( 'term_group_labels_' . $language ) ) {

          $option_count++;

        }

      }

    }

    foreach ( $option_names as $key => $value ) {

      if ( delete_option( $key ) ) {

        $option_count++;

      }

    }

    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

      error_log( sprintf( '[Tag Groups] %d options deleted.', $option_count ) );

    }

  }


  /**
  * For backwards compatibility, erase /tag-groups/cache directory
  */
  if ( file_exists( WP_CONTENT_DIR . '/tag-groups/cache' ) && is_dir( WP_CONTENT_DIR . '/tag-groups/cache' ) ) {
    /**
    * Attempt to empty and remove tag-groups directory
    * (Different from purging cache because the previous one can be database.)
    */
    foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( WP_CONTENT_DIR . '/tag-groups/cache/' ) ) as $file) {

      // filter out "." and ".."
      if ( $file->isDir() ) continue;

      @unlink( $file->getPathname() );

    }

    @rmdir( WP_CONTENT_DIR . '/tag-groups/cache' );

  }

  /**
  * For backwards compatibility, erase /tag-groups/ directory
  */
  if ( file_exists( WP_CONTENT_DIR . '/tag-groups' ) && is_dir( WP_CONTENT_DIR . '/tag-groups' ) ) {

    foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( WP_CONTENT_DIR . '/tag-groups/' ) ) as $file) {

      // filter out "." and ".."
      if ($file->isDir()) continue;

      @unlink( $file->getPathname() );

    }

    @rmdir( WP_CONTENT_DIR . '/tag-groups' );
  }


  /**
  * Erase /chatty-mango/cache/ directory
  */
  if ( file_exists( WP_CONTENT_DIR . '/chatty-mango/cache' ) && is_dir( WP_CONTENT_DIR . '/chatty-mango/cache' ) ) {
    /**
    * Attempt to empty and remove chatty-mango/cache directory
    * (Different from purging cache because the previous one can be database.)
    */
    foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( WP_CONTENT_DIR . '/chatty-mango/cache/' ) ) as $file) {

      // filter out "." and ".."
      if ( $file->isDir() ) continue;

      @unlink( $file->getPathname() );

    }

    @rmdir( WP_CONTENT_DIR . '/chatty-mango/cache' );

  }

  /**
  * Remove transients
  *
  * Don't call the method clear_term_cache since we don't know if it is still available.
  */
  if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

    error_log( '[Tag Groups] Removing transients.' );

  }

  delete_transient( 'tag_groups_post_counts' );

  delete_transient( 'tag_groups_group_terms' );

  delete_transient( 'tag_groups_post_terms' );

  delete_transient( 'tag_groups_post_types' );

  if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

    error_log( '[Tag Groups] Finished uninstall routine.' );

  }

}
