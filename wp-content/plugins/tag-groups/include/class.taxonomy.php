<?php
/**
* Tag Groups
*
* @package     Tag Groups
* @author      Christoph Amthor
* @copyright   2018 Christoph Amthor (@ Chatty Mango, chattymango.com)
* @license     GPL-3.0+
* @since       0.38
*
*/

if ( ! class_exists( 'TagGroups_Taxonomy' ) ) {

  class TagGroups_Taxonomy {



    /**
    * Constructor
    *
    *
    */
    public function __construct() {
    }


    /**
    * Removes taxonomy names that are not registered with this WP site as public
    *
    *
    * @param array $taxonomy_slugs
    * @return array
    */
    public static function remove_invalid( $taxonomy_slugs )
    {

      if ( ! is_array( $taxonomy_slugs ) ) {

        $taxonomy_slugs = array( $taxonomy_slugs );

      }

      $valid_taxonomy_slugs = self::get_public_taxonomies();

      return array_intersect( $taxonomy_slugs, $valid_taxonomy_slugs );

    }


    /**
    * Returns taxonomy names that are enabled in the options
    *
    *
    * @param array $merge_taxonomy_slugs Optional array of taxonomy names that needs to be intersected
    * @return array
    */
    public static function get_enabled_taxonomies( $intersect_taxonomy_slugs = null )
    {

      $tag_group_taxonomies = get_option( 'tag_group_taxonomy', array('post_tag') );

      $valid_taxonomy_slugs = self::get_public_taxonomies();

      if ( empty( $intersect_taxonomy_slugs ) ) {

        return array_intersect( $tag_group_taxonomies, $valid_taxonomy_slugs );

      } else {

        return array_intersect( $tag_group_taxonomies, $valid_taxonomy_slugs, $intersect_taxonomy_slugs );

      }

    }


    /**
    * Returns taxonomy names that are enabled in the options for the metabox
    *
    *
    * @param array $merge_taxonomy_slugs Optional array of taxonomy names that needs to be intersected
    * @return array
    */
    public static function get_taxonomies_for_metabox( $intersect_taxonomy_slugs = null )
    {

      $tag_group_taxonomies = get_option( 'tag_group_taxonomy', array('post_tag') );

      $tag_group_meta_box_taxonomies = get_option( 'tag_group_meta_box_taxonomy', array() );

      $valid_taxonomy_slugs = self::get_public_taxonomies();

      if ( empty( $intersect_taxonomy_slugs ) ) {

        return array_intersect( $tag_group_taxonomies, $valid_taxonomy_slugs, $tag_group_meta_box_taxonomies );

      } else {

        return array_intersect( $tag_group_taxonomies, $valid_taxonomy_slugs, $tag_group_meta_box_taxonomies, $intersect_taxonomy_slugs );

      }

    }


    /**
    * Returns taxonomy names that are registered with this WP site
    *
    *
    * @param void
    * @return array
    */
    public static function get_public_taxonomies()
    {

      return get_taxonomies( array( 'public' => true ), 'names' );

    }


    /**
    *   Retrieves post types from taxonomies
    *
    * @param array|string $taxonomy_slugs
    * @return array
    */
    static function post_types_from_taxonomies( $taxonomy_slugs = array() ) {

      if ( ! is_array( $taxonomy_slugs ) ) {

        $taxonomy_slugs = array( $taxonomy_slugs );

      }

      asort( $taxonomy_slugs ); // avoid duplicate cache entries

      $key = md5( serialize( $taxonomy_slugs ) );

      $transient_value = get_transient( 'tag_groups_post_types' );

      if ( false === $transient_value  || ! isset( $transient_value[ $key ] ) ) {

        $post_types = array();

        foreach ( $taxonomy_slugs as $taxonomy ) {

          $post_type_a = array();

          // if ( 'post_tag' == $taxonomy ) {
          //
          //   $post_type_a = array( 'post' );
          //
          // } else {

            $taxonomy_o = get_taxonomy( $taxonomy );

            /**
            * The return value of get_taxonomy can be false
            */
            if ( ! empty( $taxonomy_o )) {

              $post_type_a = $taxonomy_o->object_type;

            }
          // }

          if ( ! empty( $post_type_a )) {

            foreach ( $post_type_a as $post_type ) {

              if ( ! in_array( $post_type, $post_types ) ) {

                $post_types[] = $post_type;

              }
            }
          }
        }

        if ( ! is_array( $transient_value ) ) {

          $transient_value = array();

        }

        $transient_value[ $key ] = $post_types;

        // Limit lifetime, since base plugin does not have a function to manually clear the cache
        set_transient( 'tag_groups_post_types', $transient_value, 6 * HOUR_IN_SECONDS );

        return $post_types;

      } else {

        return $transient_value[ $key ];

      }

    }


    /**
    * Gets the taxonomy name for a given slug
    *
    *
    * @param string $taxonomy_slug
    * @return string name
    */
    public static function get_name_from_slug( $taxonomy_slug )
    {

      $taxonomy = get_taxonomy( $taxonomy_slug );

      if ( is_object( $taxonomy ) && is_object( $taxonomy->labels ) ) {

        return $taxonomy->labels->name;

      } else {

        return $taxonomy_slug;

      }

    }


    /**
    * Wrapper for backwards compatibility
    *
    *
    * @param array $merge_taxonomy_slugs Optional array of taxonomy names that needs to be intersected
    * @return array
    */
    public static function get_metabox( $intersect_taxonomy_slugs = null )
    {

      return self::get_taxonomies_for_metabox( $intersect_taxonomy_slugs );

    }


    /**
    * Returns the URL to a tag groups admin page
    *
    *
    * @param string $taxonomy
    * @return string
    */
    public static function get_tag_group_admin_url( $taxonomy )
    {

      $post_type = TagGroups_Base::get_first_element( self::post_types_from_taxonomies( $taxonomy ) );

      if ( 'post' == $post_type ) {

        $rel_url = 'edit.php?page=tag-groups_' . $post_type;

      } else {

        $rel_url = 'edit.php?post_type=' . $post_type . '&page=tag-groups_' . $post_type;

      }

      return admin_url( $rel_url );

    }

  }
}
