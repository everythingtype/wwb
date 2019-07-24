<?php
/**
* Tag Groups
*
* @package     Tag Groups
* @author      Christoph Amthor
* @copyright   2018 Christoph Amthor (@ Chatty Mango, chattymango.com)
* @license     GPL-3.0+
* @since      1.8.0
*
*/

if ( ! class_exists('TagGroups_Group') ) {

  class TagGroups_Group {

    /**
    * term_group id
    *
    * @var int
    */
    private $term_group;

    /**
    * array of all term_group values
    *
    * @var array
    */
    private $term_groups;

    /**
    * array of positions[term_group]
    *
    * @var array
    */
    private $positions;

    /**
    * array of labels[term_group]
    *
    * @var array
    */
    private $labels;


    /**
    * Constructor
    *
    *
    * @param int $term_group optional term_group
    * @return return type
    */
    public function __construct( $term_group = 0 )
    {

      if ( ! empty( $term_group ) ) {

        $this->term_group = $term_group;

      }

      $this->load();

      if ( count( $this->term_groups ) == 0 ) {

        $this->add_not_assigned();

        $this->save();

      }

      return $this;
    }


    /**
    * Load data from database
    *
    *
    * @param int $term_group optional term_group
    * @return return type
    */
    public function load()
    {
      /*
      * For historical reasons, term_groups and labels have been defined dependent of the position.
      * In future the way how it is saved in the database should be dependent on term_group.
      */
      $this->term_groups = get_option( 'term_groups', array() );

      if ( empty( $this->term_groups ) ) {

        $term_groups_position = get_option( 'tag_group_ids', array() ); // position -> id

        $labels_position = get_option( 'tag_group_labels', array() ); // position -> label

        $this->positions = array_flip( $term_groups_position );

        $this->term_groups = array_keys( $this->positions );

        $this->labels = array();

        foreach ( $term_groups_position as $position => $id ) {

          $this->labels[ $id ] = $labels_position[ $position ];

        }

      } else {

        $this->positions = get_option( 'term_group_positions', array() );

        $this->labels = get_option( self::get_tag_group_label_option_name(), array() );

        if ( empty( $this->labels ) ) {

          /**
          * This language has not yet been saved. We return the default language.
          */
          $this->labels = get_option( 'term_group_labels', array() );

        } elseif ( self::is_wpml_translated_language() ) {

          /**
          * Check for untranslated names
          */
          $default_language_labels = get_option( 'term_group_labels', array() );

          foreach ( $default_language_labels as $group_id => $default_language_label ) {

            if ( ! isset( $this->labels[ $group_id ] ) ) {

              $this->labels[ $group_id ] = $default_language_label;

            }

          }

        }

        // sanity check
        if ( count( $this->term_groups ) != count( $this->positions ) ) {

          // recreate $this->term_groups from positions

          $this->term_groups = array_keys( $this->positions );

          update_option( 'term_groups', $this->term_groups );

        }

      }

      return $this;

    }


    /**
    * checks whether this group exists, identified by its ID
    *
    *
    * @param void
    * @return boolean
    */
    public function exists()
    {

      return isset( $this->term_group ) && isset( $this->term_groups[ $this->term_group ] );

    }


    /**
    * checks and, if needed, initialize values for first use
    *
    * @param void
    * @return object $this
    */
    public function add_not_assigned()
    {

      $this->term_groups[0] = 0;

      $this->labels[0] = __('not assigned', 'tag-groups');

      $this->positions[0] = 0;

      // $this->save();

      return $this;
    }


    /**
    * Saves tag group-relevant information to the database
    *
    *
    * @param type var Description
    * @return return type
    */
    public function save()
    {

      update_option( 'term_groups', $this->term_groups );

      update_option( 'term_group_positions', $this->positions );

      update_option( self::get_tag_group_label_option_name(), $this->labels );

      /**
      * If we save translated groups, make sure we have untranslated ones. If not, give them the translations.
      */
      if ( self::is_wpml_translated_language() ) {

        $default_language_labels = get_option( 'term_group_labels', array() );

        $changed = false;

        foreach ( $this->labels as $group_id => $group_label ) {

          if ( ! isset( $default_language_labels[ $group_id ] ) ) {

            $default_language_labels[ $group_id ] = $group_label;

            $changed = true;

          }

        }

        if ( $changed ) {

          update_option( 'term_group_labels', $default_language_labels );

        }

      }

      // TODO: remove this part
      $version = get_option( 'tag_group_base_version', '0' );

      if ( -1 == version_compare( $version, '0.35' ) ) {

        /**
        * Save also in the previous fields for backwards compatibility (downgrading) and to make sure no data is lost during transition. After that, the options will be deleted in TagGroups_Base::on_activation().
        */

        $tag_group_ids = array_flip( $this->positions );

        $tag_group_labels = array();

        foreach ( $this->labels as $term_group => $label ) {

          if ( ! empty( $this->positions[ $term_group ] ) ) {

            $tag_group_labels[ $this->positions[ $term_group ] ] = $label;

          }

        }


        ksort( $tag_group_ids );

        ksort( $tag_group_labels );

        update_option( 'tag_group_labels', array_values( $tag_group_labels ) );

        update_option( 'tag_group_ids', array_values( $tag_group_ids ) );

        $max = count( $this->term_groups ) == 0 ? 0: max( $this->term_groups );

        update_option( 'max_tag_group_id', $max );

      }

      do_action( 'term_group_saved' );


      if ( class_exists( 'TagGroups_Premium_Post' ) && ( ! defined( 'TAG_GROUPS_DISABLE_CACHE_REBUILD' ) || TAG_GROUPS_DISABLE_CACHE_REBUILD ) ) {

        // schedule rebuild of cache
        wp_schedule_single_event( time() + 10, 'tag_groups_rebuild_post_terms' );

      }


      return $this;

    }


    /**
    * getter for the term_group value
    *
    *
    * @param void
    * @return int term_group
    */
    public function get_term_group()
    {

      return $this->term_group;

    }


    /**
    * setter for the term_group value
    *
    *
    * @param int $term_group
    * @return object $this
    */
    public function set_term_group( $term_group )
    {

      $this->term_group = $term_group;

      if ( array_search( $this->term_group, $this->term_groups ) === false ) {

        array_push( $this->term_groups, $this->term_group );

      }

      return $this;

    }


    /**
    * returns the highest term_group in use
    *
    * @param void
    * @return int
    */
    public function get_max_term_group()
    {
      if ( count( $this->term_groups ) == 0 ) {

        return 0;

      } else {

        return max( $this->term_groups );

      }
    }


    /**
    * returns the highest position in use
    *
    * @param void
    * @return int
    */
    public function get_max_position()
    {
      if ( count( $this->positions ) == 0 ) {

        return 0;

      } else {

        return max( $this->positions );

      }
    }


    /**
    * returns the number of term groups_only
    *
    *
    * @param void
    * @return int
    */
    public function get_number_of_term_groups()
    {
      return count( $this->term_groups );
    }


    /**
    * adds a new group and saves it
    *
    *
    * @param int $position position of the new group
    * @param string $label label of the new group
    * @return int
    */
    public function create( $position, $label )
    {

      $this->set_term_group( $this->get_max_term_group() + 1 );

      $this->set_label( $label );

      $end_position = $this->get_max_position() + 1;

      $this->set_position( $end_position );

      if ( $position != $end_position ) {

        $this->move_to_position( $position );

      }

      $this->save();

      // TagGroups_Admin::register_string_wpml( 'Group Label ID ' . $this->term_group, $label );

      return $this;

    }


    /**
    * returns all terms that are associated with this term group
    *
    * @param string|array $taxonomy See get_terms
    * @param string $hide_empty See get_terms
    * @param string $fields See get_terms
    * @return array
    */
    public function get_group_terms( $taxonomy = 'post_tag', $hide_empty = false, $fields = 'all', $post_id = 0, $orderby = 'name', $order = 'ASC' )
    {

      if ( ! isset( $this->term_group ) ) {

        return array();

      }

      if ( class_exists('TagGroups_Premium_Group') ) {

        // Get the number of required arguments for compatibility
        $r = new \ReflectionMethod( 'TagGroups_Premium_Group', 'get_group_terms' );

        if ( 7 == $r->getNumberOfParameters() ) {

          $terms = TagGroups_Premium_Group::get_group_terms( $this->term_group, $taxonomy, $hide_empty, $fields, $post_id, $orderby, $order );

        } else {

          $terms = TagGroups_Premium_Group::get_group_terms( $this->term_group, $taxonomy, $hide_empty, $fields, $post_id );

        }

        if ( is_array( $terms ) ) {

          return $terms;

        }

      }

      /**
      * Remove invalid taxonomies
      */
      $taxonomy = TagGroups_Taxonomy::remove_invalid( $taxonomy );

      $args = array(
        'taxonomy'    => $taxonomy,
        'hide_empty'  => $hide_empty,
        'orderby'     => $orderby,
        'order'       => $order,
        'fields'      => 'all' // we need an array of objects, regardless of what was requested
      );

      $terms = get_terms( $args );

      // need to sort manually
      if ( strtolower( $fields ) == 'ids' ) {

        $result = array();

        foreach ( $terms as $key => $term ) {

          $tg_term = new TagGroups_Term( $term );

          if ( $tg_term->is_in_group( $this->term_group ) && ! in_array( $term->term_id, $result ) ) {

            $result[] = $term->term_id;

          }

        }

        return $result;

      } elseif ( strtolower( $fields ) == 'names' ) {

        $result = array();

        foreach ( $terms as $key => $term ) {

          $tg_term = new TagGroups_Term( $term );

          if ( $tg_term->is_in_group( $this->term_group ) && ! in_array( $term->term_id, $result ) ) {

            $result[] = $term->name;

          }

        }

        return $result;

      } else {

        $result = array();

        foreach ( $terms as $key => $term ) {

          $tg_term = new TagGroups_Term( $term );

          if ( $tg_term->is_in_group( $this->term_group ) && ! in_array( $term->term_id, $result ) ) {

            $result[] = $term;

          }

        }

        return $result;

      }

    }


    /**
    * adds terms to this group
    *
    *
    * @param array $terms one-dimensional array of term IDs
    * @return object $this
    */
    public function add_terms( $group_ids )
    {
      foreach ( $group_ids as $group_id ) {

        $term = new TagGroups_Term( $group_id );

        $term->add_group( $this->term_group );

        $term->save();

      }

      return $this;
    }


    /**
    * removes terms from this group
    *
    *
    * @param array $term_ids one-dimensional array of term IDs
    * @return object $this
    */
    public function remove_terms( $term_ids = '' )
    {

      $terms = get_terms( array( 'include' => $term_ids, 'hide_empty' => false ) );

      foreach ( $terms as $term ) {

        $term_o = new TagGroups_Term( $term );

        // make sure this term is really in this group
        if ( $term_o->is_in_group( $this ) ) {

          $term_o->remove_group( $this );

          $term_o->save();

        }

      }

      return $this;
    }


    /**
    * removes all terms from all groups
    *
    *
    * @param void
    * @return object $this
    */
    public function unassign_all_terms()
    {

      $terms = get_terms( array( 'hide_empty' => false ) );

      foreach ( $terms as $term ) {

        $term_o = new TagGroups_Term( $term );

        $old_groups = $term_o->get_groups();

        $term_o->set_group( 0 );

        $term_o->save();

      }

      return $this;
    }


    /**
    * deletes this group
    *
    *
    * @param int $term_group ID of this group
    * @return object $this
    */
    public function delete()
    {

      unset( $this->labels[ $this->term_group ] );

      unset( $this->positions[ $this->term_group ] );

      if ( ( $key = array_search( $this->term_group, $this->term_groups ) ) !== false) {

        unset( $this->term_groups[ $key ] );

      }

      $this->reindex_positions();

      $this->remove_terms();

      // TagGroups_Admin::unregister_string_wpml( 'Group Label ID ' . $this->term_group );

      $this->save();

      do_action( 'term_group_deleted' );

      return $this;

    }


    /**
    * returns the position of this group
    *
    * @param void
    * @return int|boolean
    */
    public function get_position()
    {
      if ( empty( $this->term_group ) ) {

        return false;

      }

      if ( isset( $this->positions[ $this->term_group ] ) ) {

        return $this->positions[ $this->term_group ];

      } else {

        return 0;

      }
    }


    /**
    * sets the position of this group
    *
    *
    * @param int $position position of this group
    * @return object $this
    */
    public function set_position( $position )
    {

      if ( empty( $this->term_group ) ) {

        return false;

      }

      $this->positions[ $this->term_group ] = $position;

      // $this->save();

      return $this;
    }


    /**
    * sets the position of this group
    *
    *
    * @param int $position position of this group
    * @return object $this
    */
    public function move_to_position( $new_position )
    {

      if ( empty( $this->term_group ) ) {

        return false;

      }

      $old_position = $this->get_position();


      /**
      * 1. move down on old position
      */
      foreach ( $this->positions as $key => $value ) {

        if ( $value > $old_position ) {

          $this->positions[ $key ] = $value - 1;

        }

      }

      /**
      * 2. make space at new position
      */
      foreach ( $this->positions as $key => $value ) {

        if ( $value >= $new_position ) {

          $this->positions[ $key ] = $value + 1;

        }

      }

      /**
      * 3. Insert
      */
      $this->positions[ $this->term_group ] = $new_position;

      $this->reindex_positions();

      return $this;
    }


    /**
    * returns the label of this group
    *
    * @param void
    * @return string|boolean
    */
    public function get_label()
    {

      if ( ! isset( $this->term_group ) ) { // allow also "not assigned"

        return false;

      }

      if ( isset( $this->labels[ $this->term_group ] ) ) {

        return $this->labels[ $this->term_group ];

      } else {

        return '';

      }

    }


    /**
    * sets the label of this group
    *
    *
    * @param string $label label of this group
    * @return object $this
    */
    public function set_label( $label )
    {

      if ( empty( $this->term_group ) ) {

        return false;

      }

      $this->labels[ $this->term_group ] = $label;

      return $this;
    }



    /**
    * returns the labels for an array of ids, sorted by position
    *
    * @param array $group_ids
    * @return array
    */
    public function get_labels( $group_ids )
    {
      $result = array();

      if ( ! is_array( $group_ids ) ) {

        $group_ids = array( $group_ids );

      }

      foreach ( $group_ids as $group_id) {
        if ( ! empty( $this->labels[ $group_id ] ) && isset( $this->positions[ $group_id ] ) ) {

          $result[ $this->positions[ $group_id ] ] = $this->labels[ $group_id ];

        }
      }

      ksort( $result );

      return array_values( $result );

    }


    /**
    * changes the label of this group; involves registration with translation plugins
    *
    *
    * @param string $label label of this group
    * @return object $this
    */
    public function change_label( $label )
    {

      if ( empty( $this->term_group ) ) {

        return false;

      }

      // TagGroups_Admin::unregister_string_wpml( 'Group Label ID ' . $this->term_group );

      $this->set_label( $label );

      $this->save();

      // TagGroups_Admin::register_string_wpml( 'Group Label ID ' . $this->term_group, $this->labels );

      return $this;
    }


    /**
    * returns the number of terms associated with this group
    *
    *
    * @param void
    * @return int
    */
    public function get_number_of_terms( $taxonomies, $term_group = null )
    {

      if ( ! isset( $this->term_group ) && is_null( $term_group ) ) {

        return false;

      }

      if ( is_null( $term_group ) ) {

        $term_group = $this->term_group;

      }

      if ( ! is_array( $taxonomies ) ) {

        $taxonomies = array( $taxonomies );

      }

      /**
      * Consider only taxonomies that
      * 1. are among $tag_group_taxonomies
      * 2. actually exist
      */
      $search_taxonomies = TagGroups_Taxonomy::get_enabled_taxonomies( $taxonomies );

      if ( class_exists( 'TagGroups_Premium_Group' ) ) {

        return TagGroups_Premium_Group::get_group_terms( $term_group, $search_taxonomies, false, 'count' );

      } else {

        $terms = get_terms( array( 'hide_empty' => false, 'taxonomy' => $search_taxonomies ) );

        $number = 0;

        foreach ( $terms as $term ) {

          $term_o = new TagGroups_Term( $term );

          if ( $term_o->is_in_group( $term_group ) ) {

            $number++;

          }

        }

      }

      return $number;

    }


    /**
    * sets $this->term_group by label
    *
    *
    * @param string $label
    * @return boolean|int
    */
    public function find_by_label( $label )
    {
      if ( in_array( $label, $this->labels ) ) {

        $this->term_group = array_search( $label, $this->labels );

        return $this;

      } else {

        return false;

      }
    }


    /**
    * sets $this->term_group by position
    *
    *
    * @param int $position
    * @return boolean|int
    */
    public function find_by_position( $position )
    {

      if ( in_array( $position, $this->positions ) ) {

        $this->term_group = array_search( $position, $this->positions );

        return $this;

      } else {

        $this->term_group = 0;

        return false;

      }
    }


    /**
    * returns an array of group properties as values
    *
    * @param void
    * @return array
    */
    public function get_info( $taxonomy = null, $hide_empty = false, $fields = null, $orderby = 'name', $order = 'ASC' ) {

      // dealing with NULL values
      if ( empty( $fields ) ) {

        $fields = 'ids';

      }

      if ( empty( $taxonomy ) ) {

        $taxonomy = get_option( 'tag_group_taxonomy', array('post_tag') );

      }

      if ( ! isset( $hide_empty ) || empty( $hide_empty ) ) {

        $hide_empty = false;

      }

      $terms = $this->get_group_terms( $taxonomy, $hide_empty, $fields, 0, $orderby, $order );

      if ( ! is_array( $terms ) ) {

        $terms = array();

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

          error_log( '[Tag Groups] Error retrieving terms in get_info().' );

        }

      }

      return array(
        'term_group' => (int) $this->term_group,
        'label' => $this->labels[ $this->term_group ],
        'position'  => (int) $this->positions[ $this->term_group ],
        'terms'  => $terms
      );

    }


    /**
    * returns an array of group properties as values
    *
    * @param void
    * @return array
    */
    public function get_info_of_all( $taxonomy = null, $hide_empty = false, $fields = null, $orderby = 'name', $order = 'ASC' ) {

      // dealing with NULL values
      if ( empty( $fields ) ) {

        $fields = 'ids';

      }

      if ( empty( $taxonomy ) ) {

        $taxonomy = get_option( 'tag_group_taxonomy', array('post_tag') );

      }

      if ( ! isset( $hide_empty ) || empty( $hide_empty ) ) {

        $hide_empty = false;

      }

      $result = array();

      foreach ( $this->term_groups as $term_group ) {
        if ( isset( $this->positions[ $term_group ] ) && isset( $this->labels[ $term_group ] ) ) { // allow unassigned

          $this->set_term_group( $term_group ); // for get_group_terms()

          $terms = $this->get_group_terms( $taxonomy, $hide_empty, $fields, 0, $orderby, $order );

          if ( ! is_array( $terms ) ) {

            $terms = array();

            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

              error_log( '[Tag Groups] Error retrieving terms in get_info().' );

            }

          }

          $result[ $this->positions[ $term_group ] ] = array(
            'term_group' => (int) $term_group,
            'label' => $this->labels[ $term_group ],
            'position'  => (int) $this->positions[ $term_group ],
            'terms'  => $terms
          );

        }
      }

      /**
      * The position should determine the order.
      */
      ksort( $result );

      return $result;

    }

    /**
    * returns all tag groups with the position as keys and an array of group properties as values
    * including unassigned
    *
    * @param void
    * @return array
    */
    public function get_all_with_position_as_key() {

      $result = array();

      foreach ( $this->term_groups as $term_group ) {

        if ( isset( $this->positions[ $term_group ] ) && isset( $this->labels[ $term_group ] ) ) { // allow unassigned

          $result[ $this->positions[ $term_group ] ] = array(
            'term_group' => (int) $term_group,
            'label' => $this->labels[ $term_group ],
            'position'  => (int) $this->positions[ $term_group ]
          );

        }

      }

      /**
      * The position should determine the order.
      */
      ksort( $result );

      return $result;

    }


    /**
    * returns all tag groups with the term_group as keys and labels as values
    * sorted by position
    *
    * @param void
    * @return array
    */
    public function get_all_term_group_label() {

      $result = array();

      $positions_flipped = array_flip( $this->positions );

      ksort( $positions_flipped ); // ksort doesn't like return values of functions

      foreach ( $positions_flipped as $term_group ) {

        $result[ $term_group ] = $this->labels[ $term_group ];

      }

      return $result;

    }


    /**
    * returns all tag group ids
    * sorted by position
    *
    * @param void
    * @return array
    */
    public function get_all_ids() {

      $result = array();

      $position_flipped = array_flip( $this->positions );

      ksort( $position_flipped );

      foreach ( $position_flipped as $term_group ) {
        $result[] = $term_group;
      }

      return $result;
    }


    /**
    * returns all labels
    * sorted by position
    *
    * @param void
    * @return array
    */
    public function get_all_labels() {

      $result = array();

      $positions = $this->positions;

      asort( $positions );

      $positions_keys = array_keys( $positions );

      foreach ( $positions_keys as $term_group ) {
        $result[] = $this->labels[ $term_group ];
      }

      return $result;
    }


    /**
    * Deletes all groups
    *
    * @param void
    * @return void
    */
    public function reset() {

      $this->term_groups = array();

      $this->positions = array();

      $this->labels = array();

      $this->unassign_all_terms();

      $this->add_not_assigned();

      $this->save();

    }


    /**
    * Remove "holes" in position array
    *
    *
    * @param void
    * @return void
    */
    private function reindex_positions()
    {

      $positions_flipped = array_flip( $this->positions ); // result: position => id

      ksort( $positions_flipped );

      // re-index
      $positions_flipped = array_values( $positions_flipped );

      $this->positions = array_flip( $positions_flipped );

      return $this;

    }


    /**
    * Check for WPML and use the correct option name
    *
    * @param void
    * @return string
    */
    public static function get_tag_group_label_option_name()
    {

      if ( ! defined( 'ICL_LANGUAGE_CODE' ) || ! self::is_wpml_translated_language() ) {

        return 'term_group_labels';

      }

      $language = (string) ICL_LANGUAGE_CODE;

      /**
      * Make sure we can delete this option during uninstallation
      */
      $tag_group_group_languages = get_option( 'tag_group_group_languages', array() );

      if ( ! in_array( $language, $tag_group_group_languages ) ) {

        $tag_group_group_languages[] = $language;

        update_option( 'tag_group_group_languages', $tag_group_group_languages );

      }

      return 'term_group_labels_' . $language;

    }


    /**
    * Returns true if WPML is installed and we are not using the default language.
    *
    * @param void
    * @return boolean
    */
    public static function is_wpml_translated_language()
    {

      if ( ! defined( 'ICL_LANGUAGE_CODE' ) ) {

        return false;

      }

      $default_language = apply_filters( 'wpml_default_language', NULL );

      if ( $default_language == ICL_LANGUAGE_CODE ) {

        return false;

      }

      return true;

    }

  }
}
