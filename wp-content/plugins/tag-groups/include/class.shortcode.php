<?php
/**
* @package     Tag Groups
* @author      Christoph Amthor
* @copyright   2018 Christoph Amthor (@ Chatty Mango, chattymango.com)
* @license     GPL-3.0+
*/

if ( ! class_exists('TagGroups_Shortcode') ) {

  class TagGroups_Shortcode {

    /*
    * Register the shortcodes with WordPress
    */
    static function register() {

      /**
      * Tabbed tag cloud
      */
      add_shortcode( 'tag_groups_cloud', array( 'TagGroups_Shortcode_Tabs', 'tag_groups_cloud' ) );

      if ( function_exists( 'register_block_type' ) ) {

        register_block_type( 'chatty-mango/tag-groups-cloud-tabs', array(
          'render_callback' => array( 'TagGroups_Shortcode_Tabs', 'tag_groups_cloud' ),
        ) );

      }

      /**
      * Accordion tag cloud
      */
      add_shortcode( 'tag_groups_accordion', array( 'TagGroups_Shortcode_Accordion', 'tag_groups_accordion' ) );

      if ( function_exists( 'register_block_type' ) ) {

        register_block_type( 'chatty-mango/tag-groups-cloud-accordion', array(
          'render_callback' => array( 'TagGroups_Shortcode_Accordion', 'tag_groups_accordion' ) ,
        ) );

      }

      /**
      * Tabbed tag cloud with first letters as tabs
      */
      add_shortcode( 'tag_groups_alphabet_tabs', array( 'TagGroups_Shortcode_Alphabet_Tabs', 'tag_groups_alphabet_tabs' ) );

      // if ( function_exists( 'register_block_type' ) ) {
      //
      //   register_block_type( 'chatty-mango/tag-groups-alphabet-tabs', array(
      //     'render_callback' => array( 'TagGroups_Shortcode_Alphabet_Tabs', 'tag_groups_alphabet_tabs' ),
      //   ) );
      //
      // }

      /**
      * Accordion tag cloud with first letters as panels
      */
      // add_shortcode( 'tag_groups_alphabet_accordion', array( 'TagGroups_Shortcode_Alphabet_Accordion', 'tag_groups_alphabet_accordion' ) );
      //
      // if ( function_exists( 'register_block_type' ) ) {
      //
      //   register_block_type( 'chatty-mango/tag-groups-alphabet-accordion', array(
      //     'render_callback' => array( 'TagGroups_Shortcode_Alphabet_Accordion', 'tag_groups_alphabet_accordion' ) ,
      //   ) );
      //
      // }

      /**
      * Group info
      */
      add_shortcode( 'tag_groups_info', array( 'TagGroups_Shortcode_Info', 'tag_groups_info' ) );

    }


    /**
    * Makes sure that shortcodes work in text widgets.
    */
    static function widget_hook() {

      $tag_group_shortcode_widget = get_option( 'tag_group_shortcode_widget', 0 );

      if ( $tag_group_shortcode_widget ) {

        add_filter( 'widget_text', 'do_shortcode' );

      }

    }



        /**
        * If WPML is installed: return translation; otherwise return original
        *
      * @deprecated
      *
        * @param type $name
        * @param type $string
        * @return type
        */
        static function translate_string_wpml( $name, $string )
        {

          if ( function_exists( 'icl_t' ) ) {
            return icl_t( 'tag-groups', $name, $string );
          } else {
            return $string;
          }

        }


        /**
        * Calculates the font size for the cloud tag for a particular tag ($min, $max and $size with same unit, e.g. pt.)
        *
        * @param int $count
        * @param int $min
        * @param int $max
        * @param int $smallest
        * @param int $largest
        * @return int
        */
        static function font_size( $count, $min, $max, $smallest, $largest )
        {

          if ( $max > $min ) {

            $size = round( ( $count - $min ) * ( $largest - $smallest ) / ( $max - $min ) + $smallest );

          } else {

            $size = round( $smallest );

          }

          return $size;

        }


        /**
        * A piece of script for the tabs to work, including options, for each individual cloud
        *
        * @param type $id
        * @param type $option_mouseover
        * @param type $option_collapsible
        * @return string
        */
        static function custom_js_tabs( $id = null, $option_mouseover = null, $option_collapsible = null, $option_active = null )
        {

          $options = array();

          if ( isset( $option_mouseover ) ) {

            if ( $option_mouseover ) {

              $options[] = 'event: "mouseover"';

            }

          } else {

            if ( get_option( 'tag_group_mouseover', '' ) ) {

              $options[] = 'event: "mouseover"';

            }

          }

          if ( isset( $option_collapsible ) ) {

            if ( $option_collapsible ) {

              $options[] = 'collapsible: true';

            }

          } else {

            if ( get_option( 'tag_group_collapsible', '' ) ) {

              $options[] = 'collapsible: true';

            }

          }

          if ( isset( $option_active ) ) {

            if ( $option_active ) {

              $options[] = 'active: true';

            } else {

              $options[] = 'active: false';

            }

          }

          if ( empty( $options ) ) {

            $options_serialized = '';

          } else {

            $options_serialized = "{\n" . implode( ",\n", $options ) . "\n}";

          }

          if ( empty( $id ) ) {

            $id = 'tag-groups-cloud-tabs';

          } else {

            $id = TagGroups_Base::sanitize_html_classes( $id );

          }

          $html = '
          <!-- begin Tag Groups plugin -->
          <script type="text/javascript">
          jQuery(function() {
            if (jQuery.isFunction(jQuery.fn.tabs) ) {
              jQuery( "#' . $id . '" ).tabs(' . $options_serialized . ');
            }
          });
          </script>
          <!-- end Tag Groups plugin -->
          ';

          return $html;

        }


        /**
        * A piece of script for the tabs to work, including options, for each individual cloud
        *
        * @param type $id
        * @param type $option_mouseover
        * @param type $option_collapsible
        * @return string
        */
        static function custom_js_accordion( $id = null, $option_mouseover = null, $option_collapsible = null, $option_active = null, $heightstyle = null )
        {

          $options = array();

          if ( isset( $option_mouseover ) ) {

            if ( $option_mouseover ) {

              $options[] = 'event: "mouseover"';

            }

          } else {

            if ( get_option( 'tag_group_mouseover', '' ) ) {

              $options[] = 'event: "mouseover"';

            }

          }

          if ( isset( $option_collapsible ) ) {

            if ( $option_collapsible ) {

              $options[] = 'collapsible: true';

            }

          } else {

            if ( get_option( 'tag_group_collapsible', '' ) ) {

              $options[] = 'collapsible: true';

            }

          }

          if ( ! empty( $heightstyle ) ) {

            $options[] = 'heightStyle: "' . sanitize_title( $heightstyle ) . '"';

          }

          if ( isset( $option_active ) ) {

            if ( $option_active ) {

              $options[] = 'active: true';

            } else {

              $options[] = 'active: false';

            }

          }


          if ( empty( $options ) ) {

            $options_serialized = '';

          } else {

            $options_serialized = "{\n" . implode( ",\n", $options ) . "\n}";

          }

          if ( !isset( $id ) ) {

            $id = 'tag-groups-cloud-accordion';

          } else {

            $id = TagGroups_Base::sanitize_html_classes( $id );

          }

          $html = '
          <!-- begin Tag Groups plugin -->
          <script type="text/javascript">
          jQuery(function() {
            if (jQuery.isFunction(jQuery.fn.accordion) ) {
              jQuery( "#' . $id . '" ).accordion(' . $options_serialized . ');
            }
          });
          </script>
          <!-- end Tag Groups plugin -->
          ';

          return $html;

        }


        /*
        *  find minimum and maximum of quantity of posts for each tag
        *
        * @param
        * @return array $min_max
        */
        static function determine_min_max( $tags, $amount, $tag_group_ids, $include_tags_post_id_groups = null, $data = null, $post_counts = null ) {

          $min_max = array();

          $count_amount = array();

          foreach ( $tag_group_ids as $tag_group_id ) {

            $count_amount[ $tag_group_id ] = 0;

            $min_max[ $tag_group_id ]['min'] = 0;

            $min_max[ $tag_group_id ]['max'] = 0;

          }

          if ( empty( $tags ) || ! is_array( $tags ) ) {

            return $min_max;

          }

          foreach ( $tags as $tag ) {

            $term_o = new TagGroups_Term( $tag );

            if ( $term_o->is_in_group( $tag_group_ids ) ) {

              // check if tag has posts for a particular group
              if ( ! empty( $data ) && ! empty( $post_counts ) ) {

                foreach ( $tag_group_ids as $tag_group_id ) {

                  if ( isset( $post_counts[ $tag->term_id ][ $tag_group_id ] ) ) {

                    $tag_count = $post_counts[ $tag->term_id ][ $tag_group_id ];

                  } else {

                    $tag_count = 0;

                  }

                }

              } else {

                $tag_count = $tag->count;

              }

              if ( $tag_count > 0 ) {

                /**
                * Use only groups that are in the list
                */
                $term_groups = array_intersect( $term_o->get_groups(), $tag_group_ids );

                foreach ( $term_groups as $term_group ){

                  if ( 0 == $amount || $count_amount[ $term_group ] < $amount ) {

                    if ( empty( $include_tags_post_id_groups ) || in_array( $tag->term_id, $include_tags_post_id_groups[ $term_group ] ) ) {

                      if ( isset( $min_max[ $term_group ]['max'] ) && $tag_count > $min_max[ $term_group ]['max'] ) {

                        $min_max[ $term_group ]['max'] = $tag_count;

                      }

                      if ( isset( $min_max[ $term_group ]['min'] ) && ( $tag_count < $min_max[ $term_group ]['min'] || 0 == $min_max[ $term_group ]['min'] ) ) {

                        $min_max[ $term_group ]['min'] = $tag_count;

                      }

                      $count_amount[ $term_group ]++;

                    }

                  }

                }

              }

            }

          }

          return $min_max;

        }


        /*
        *  find minimum and maximum of quantity of posts for each tag
        * DEPRECATED since version 0.31
        */
        static function min_max( $tags, $amount, $tag_group_id ) {

          $count_amount = 0;

          $max = 0;

          $min = 0;

          foreach ( $tags as $tag ) {

            if ( $amount > 0 && $count_amount >= $amount ) {

              break;

            }

            if ( $tag->term_group == $tag_group_id ) {

              if ( $tag->count > $max ) {

                $max = $tag->count;

              }

              if ( $tag->count < $min || 0 == $min) {

                $min = $tag->count;

              }

              $count_amount++;
            }

          }

          return array(
            'min' => $min,
            'max' => $max
          );

        }


        /**
        * Helper for natural sorting of names
        *
        * Inspired by _wp_object_name_sort_cb
        *
        * @param array $terms
        * @param string $order asc or desc
        * @return array
        */
        static function natural_sorting( $terms, $order )
        {
          $factor = ( 'desc' == strtolower( $order ) ) ? -1 : 1;

          // "use" requires PHP 5.3+
          uasort( $terms, function( $a, $b ) use ( $factor ) {
            return $factor * strnatcasecmp( $a->name, $b->name );
          });

          return $terms;

        }


        /**
        * Helper for (pseudo-)random sorting
        *
        *
        * @param array $terms
        * @return array
        */
        static function random_sorting( $terms )
        {

          uasort( $terms, function( $a, $b ) {
            return 2 * mt_rand( 0, 1 ) - 1;
          });

          return $terms;

        }


  } // class

}
