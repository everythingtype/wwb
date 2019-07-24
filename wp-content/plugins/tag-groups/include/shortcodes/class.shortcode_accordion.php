<?php
/**
* @package     Tag Groups
* @author      Christoph Amthor
* @copyright   2018 Christoph Amthor (@ Chatty Mango, chattymango.com)
* @license     GPL-3.0+
*/

if ( ! class_exists('TagGroups_Shortcode_Accordion') ) {

  class TagGroups_Shortcode_Accordion extends TagGroups_Shortcode {


    /**
    *
    * Render the accordion tag cloud
    *
    * @param array $atts
    * @return string
    */
    static function tag_groups_accordion( $atts = array() ) {

      // create key that depends on settings
      $key_array = $atts;

      if ( isset( $key_array['div_id'] ) ) {

        unset( $key_array['div_id'] );

      }

      /**
      * In case we use the WPML plugin: consider the language
      */
      if ( defined( 'ICL_LANGUAGE_CODE' ) ) {

        $wpml_language = (string) ICL_LANGUAGE_CODE;

      } else {

        $wpml_language = '';

      }

      $key = md5( 'accordion' . serialize( $key_array ) . '-' . $wpml_language );

      // check for a cached version (premium plugin)
      $html = apply_filters( 'tag_groups_hook_cache_get', false, $key );

      if ( $html ) {

        return $html;

      }

      $post_id_terms = array();

      $assigned_terms = array();

      $include_tags_post_id_groups = array();

      $group = new TagGroups_Group();

      $data = $group->get_all_with_position_as_key();

      $tag_group_ids = $group->get_all_ids();

      extract( shortcode_atts( array(
        'active' => null,
        'adjust_separator_size' => true,
        'add_premium_filter' => 1,
        'amount' => 0,
        'append' => '',
        'assigned_class' => null,
        'collapsible' => null,
        'custom_title' => null,
        'div_class' => 'tag-groups-cloud', // tag-groups-cloud preserved to create accordion functionality and to apply styling downloaded from jQueryUI
        'div_id' => 'tag-groups-cloud-accordion-' . uniqid(),
        'exclude_terms' => '',
        'groups_post_id' => -1,
        'header_class' => '',
        'heightstyle' => 'content',
        'hide_empty_content' => false,
        'hide_empty' => true,
        'include' => '',
        'include_terms' => '',
        'inner_div_class' => '',
        'largest' => 22,
        'link_target' => '',
        'link_append' => '',
        'mouseover' => null,
        'not_assigned_name' => 'not assigned',
        'order' => 'ASC',
        'orderby' => 'name',
        'prepend' => '',
        'separator_size' => 12,
        'separator' => '',
        'show_not_assigned' => false,
        'show_all_groups' => false,
        'show_accordion' => 1,
        'show_tag_count' => true,
        'source' => 'shortcode',
        'smallest' => 12,
        'tags_post_id' => -1,
        'taxonomy' => null
      ), $atts ) );


      if ( 'shortcode' == $source ) {

        $prepend = html_entity_decode( $prepend );

        $append = html_entity_decode( $append );

        $separator = html_entity_decode( $separator );

      }

      if ( 'natural' == $orderby ) {

        $natural_sorting = true;
        $orderby = 'name';

      } else {

        $natural_sorting = false;

      }

      if ( $smallest < 1 ) {

        $smallest = 1;

      }

      if ( $largest < $smallest ) {

        $largest = $smallest;

      }

      if ( $amount < 0 ) {

        $amount = 0;

      }

      if ( ! empty( $link_append ) && mb_strpos( $link_append, '?' ) === 0 ) {

        $link_append = mb_substr( $link_append, 1 );

      }

      if ( ! empty( $show_not_assigned ) ) {

        $start_group = 0;

      } else {

        $start_group = 1;

      }

      if ( isset( $taxonomy ) ) {

        if ( empty( $taxonomy ) ) {

          unset( $taxonomy );

        } else {

          $taxonomy_array = explode( ',', $taxonomy );

          $taxonomy_array = array_filter( array_map( 'trim', $taxonomy_array ) );

        }
      }


      $taxonomies = TagGroups_Taxonomy::get_enabled_taxonomies();

      if ( ! empty( $taxonomy_array ) ) {

        $taxonomies = array_intersect( $taxonomies, $taxonomy_array );

        if ( empty( $taxonomies ) ) {

          if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

            error_log( sprintf( '[Tag Groups] Wrong taxonomy in shortcode "tag_groups_accordion": %s', $taxonomy ) );

          }

          return '';

        }

      }

      /**
      * Reduce the risk of interference from other plugins
      */
      remove_all_filters( 'get_terms_orderby' );
      remove_all_filters( 'list_terms_exclusions' );
      remove_all_filters( 'get_terms' );

      $posttags = get_terms( $taxonomies, array( 'hide_empty' => $hide_empty, 'orderby' => $orderby, 'order' => $order, 'include' => $include_terms, 'exclude' => $exclude_terms ) );

      /**
      * In case of errors: return empty array
      */
      if ( ! is_array( $posttags ) ) {

        $posttags = array();

      }

      /**
      * Keep always jQuery UI class to produce correct output
      */
      if ( strpos( $div_class, 'tag-groups-cloud' ) === false ) {

        $div_class .= ' tag-groups-cloud';

      }

      $div_id_output = $div_id ? ' id="' . TagGroups_Base::sanitize_html_classes( $div_id ) . '"' : '';

      $div_class_output = $div_class ? ' class="' . TagGroups_Base::sanitize_html_classes( $div_class ) . '"' : '';

      $header_class_output = $header_class ? ' class="' . TagGroups_Base::sanitize_html_classes( $header_class ) . '"' : '';

      $inner_div_class_output = $inner_div_class ? ' class="' . TagGroups_Base::sanitize_html_classes( $inner_div_class ) . '"' : '';

      if ( $include !== '' ) {

        $include_array = explode( ',', str_replace( ' ', '', $include ) );

      }  else {

        $include_array = $tag_group_ids;

      }

      if ( $separator_size < 1 ) {

        $separator_size = 12;

      } else {

        $separator_size = (int) $separator_size;

      }

      /*
      *  applying parameter tags_post_id
      */
      if ( $tags_post_id < -1 ) {

        $tags_post_id = -1;

      }

      if ( $tags_post_id == 0 ) {

        $tags_post_id = get_the_ID();

      }

      if ( $tags_post_id > 0 ) {

        /*
        *  we have a particular post ID
        *  get all tags of this post
        */
        foreach ( $taxonomies as $taxonomy_item ) {

          $terms = get_the_terms( (int) $tags_post_id, $taxonomy_item );

          /*
          *  merging the results of selected taxonomies
          */
          if ( ! empty( $terms ) && is_array( $terms ) ) {

            $post_id_terms = array_merge( $post_id_terms, $terms );



          }

        }


        /*
        *  clean all others from $posttags
        */
        foreach ( $posttags as $key => $tag ) {

          $found = false;

          foreach ( $post_id_terms as $id_tag ) {

            if ( $tag->term_id == $id_tag->term_id ) {

              $found = true;

              break;
            }
          }

          if ( ! empty( $assigned_class ) ) {

            /*
            *  Keep all terms but mark for different styling
            */
            if ( $found ) {

              $assigned_terms[ $tag->term_id ] = true;

            }

          } else {

            /*
            *  Remove unused terms.
            */
            if ( ! $found ) {

              unset( $posttags[ $key ] );

            }

          }

        }

        /**
        *  get all involved groups
        */
        if ( class_exists( 'TagGroups_Premium_Post' ) ) {

          $post_o = new TagGroups_Premium_Post( $tags_post_id );

          $terms_by_group_tmp = $post_o->get_terms_by_group( null, $group );

          foreach ( $terms_by_group_tmp as $key => $value ) {

            if ( ! isset( $include_tags_post_id_groups[ $key ] ) ) {

              $include_tags_post_id_groups[ $key ] = array();

            }

            $include_tags_post_id_groups[ $key ] = array_merge( $include_tags_post_id_groups[ $key ], $value );

          }

        }

      }


      /*
      *  applying parameter groups_post_id
      */
      if ( $groups_post_id < -1 ) {

        $groups_post_id = -1;

      }

      if ( $groups_post_id == 0 ) {

        $groups_post_id = get_the_ID();

      }

      if ( $groups_post_id > 0 ) {

        /*
        *  get all tags of this post
        */
        foreach ( $taxonomies as $taxonomy_item ) {

          $terms = get_the_terms( (int) $groups_post_id, $taxonomy_item );

          if ( ! empty( $terms ) && is_array( $terms ) ) {

            $post_id_terms = array_merge( $post_id_terms, $terms );

          }

        }


        /*
        *  get all involved groups, append them to $include
        */
        if ( $post_id_terms ) {

          foreach ( $post_id_terms as $term ) {

            $term_o = new TagGroups_Term( $term );

            if ( ! $term_o->is_in_group( $include_array ) ) {

              $include_array = array_merge( $include_array, $term_o->get_groups() );

            }

          }

        }

      }

      // apply sorting that cannot be done on database level
      if ( $natural_sorting ) {

        $posttags = self::natural_sorting( $posttags, $order );

      } elseif ( 'random' == $orderby ) {

        $posttags = self::random_sorting( $posttags );

      }

      /*
      *  return as html (in the shape of clouds in an accordion)
      */

      $post_counts = array();

      if ( class_exists( 'TagGroups_Premium_Term' ) && method_exists( 'TagGroups_Premium_Term', 'get_post_counts' ) ) {

        $post_counts = TagGroups_Premium_Term::get_post_counts();

      }

      $min_max = self::determine_min_max( $posttags, $amount, $tag_group_ids, $include_tags_post_id_groups, $data, $post_counts );

      $html = '<div' . $div_id_output . $div_class_output . '>';

      for ( $i = $start_group; $i <= $group->get_max_position(); $i++ ) {

        $html_header = '';

        $html_tags = '';

        $count_amount = 0;


        if ( $show_all_groups || in_array( $data[ $i ]['term_group'], $include_array ) ) {

          /*
          *  render the accordion headers
          */
          if ( $show_accordion == 1 ) {

            if ( $i == 0 ) {

              $group_name = $not_assigned_name;

            } else {

              $group_name = $data[ $i ]['label']; // self::translate_string_wpml( 'Group Label ID ' . $data[ $i ]['term_group'], $data[ $i ]['label'] );

            }

            $html_header .= '<h3' . $header_class_output . '>'
            . htmlentities( $group_name, ENT_QUOTES, "UTF-8" )
            . '</h3>';
          }

          /*
          *  render the accordion content
          */
          foreach ( $posttags as $tag ) {

            $other_tag_classes = '';

            $description = '';

            if ( ! empty( $amount ) && $count_amount >= $amount ) {

              break;

            }

            $term_o = new TagGroups_Term( $tag );

            if ( $term_o->is_in_group( $data[ $i ]['term_group'] ) ) {

              if ( empty( $include_tags_post_id_groups ) || in_array( $tag->term_id, $include_tags_post_id_groups[ $data[ $i ]['term_group'] ] ) ) {

                // check if tag has posts for this particular group
                if ( ! empty( $post_counts ) ) {

                  if ( isset( $post_counts[ $tag->term_id ][ $data[ $i ]['term_group'] ] ) ) {

                    $tag_count = $post_counts[ $tag->term_id ][ $data[ $i ]['term_group'] ];

                  } else {

                    $tag_count = 0;

                  }

                } else {

                  $tag_count = $tag->count;

                }

                if ( ! $hide_empty || $tag_count > 0 ) {

                  $tag_link = get_term_link( $tag );

                  if ( ! empty( $link_append ) ) {

                    if ( mb_strpos( $tag_link, '?' ) === false ) {

                      $tag_link = esc_url( $tag_link . '?' . $link_append );

                    } else {

                      $tag_link = esc_url( $tag_link . '&' . $link_append );

                    }
                  }

                  /**
                  * Append a parameter to separate terms by group on the archive page
                  */
                  if ( class_exists( 'TagGroups_Premium_Term' ) && $add_premium_filter ) {

                    if ( mb_strpos( $tag_link, '?' ) === false ) {

                      $tag_link = esc_url( $tag_link . '?term_group=' . $data[ $i ]['term_group'] . '&term_id=' . $tag->term_id );

                    } else {

                      $tag_link = esc_url( $tag_link . '&term_group=' . $data[ $i ]['term_group'] . '&term_id=' . $tag->term_id );

                    }

                  }

                  $font_size = self::font_size( $tag_count, $min_max[ $data[ $i ]['term_group'] ]['min'], $min_max[ $data[ $i ]['term_group'] ]['max'], $smallest, $largest );

                  $font_size_separator = $adjust_separator_size ? $font_size : $separator_size;

                  if ( $count_amount > 0 && ! empty( $separator ) ) {

                    $html_tags .= '<span style="font-size:' . $font_size_separator . 'px">' . $separator . '</span> ';

                  }

                  if ( ! empty( $assigned_class ) ) {

                    if ( ! empty( $assigned_terms[ $tag->term_id ] ) ) {

                      $other_tag_classes = ' ' . $assigned_class . '_1';

                    } else {

                      $other_tag_classes = ' ' . $assigned_class . '_0';

                    }
                  }

                  if ( ! empty( $custom_title ) ) {

                    $description = ! empty( $tag->description ) ? esc_html( $tag->description ) : '';

                    $title = preg_replace("/(\{description\})/", $description, $custom_title);

                    $title = preg_replace("/(\{count\})/", $tag_count, $title);

                  } else {
                    // description and number
                    $description = ! empty( $tag->description ) ? esc_html( $tag->description ) . ' ' : '';

                    $tag_count_brackets = $show_tag_count ? '(' . $tag_count . ')' : '';

                    $title = $description . $tag_count_brackets;
                  }

                  // replace placeholders in prepend and append
                  if ( ! empty( $prepend ) ) {

                    $prepend_output = preg_replace("/(\{count\})/", $tag_count, $prepend );

                  } else {

                    $prepend_output = '';

                  }

                  if ( ! empty( $append ) ) {

                    $append_output = preg_replace("/(\{count\})/", $tag_count, $append );

                  } else {

                    $append_output = '';

                  }

                  // adding link target
                  $link_target_html = ! empty( $link_target ) ? 'target="' . $link_target . '"' : '';

                  // assembling a tag
                  $html_tags .= '<span class="tag-groups-tag' . $other_tag_classes . '" style="font-size:' . $font_size . 'px"><a href="' . $tag_link . '" ' . $link_target_html . ' title="' . $title . '"  class="' . $tag->slug . '">';

                  if ( '' != $prepend_output ) {

                    $html_tags .= '<span class="tag-groups-prepend" style="font-size:' . $font_size . 'px">' . htmlentities( $prepend_output, ENT_QUOTES, "UTF-8" ) . '</span>';

                  }

                  $html_tags .= '<span class="tag-groups-label" style="font-size:' . $font_size . 'px">' . $tag->name . '</span>';

                  if ( '' != $append_output ) {

                    $html_tags .= '<span class="tag-groups-append" style="font-size:' . $font_size . 'px">' . htmlentities( $append_output, ENT_QUOTES, "UTF-8" ) . '</span>';

                  }

                  $html_tags .= '</a></span> ';

                  $count_amount++;

                }

              }

            }

          }

        }

        if ( ! empty( $html_header ) && ( ! $hide_empty_content || $count_amount ) ) {

          $html .= $html_header . '<div' . $inner_div_class_output . '>' . $html_tags . '</div>';
        }
      }

      /*
      * Closing the accordion
      */
      $html .= '</div>';

      $html .= self::custom_js_accordion( $div_id, $mouseover, $collapsible, $active, $heightstyle );

      // create a cached version (premium plugin)
      do_action( 'tag_groups_hook_cache_set', $key, $html );

      return $html;

    }


  } // class

}
