<?php
/**
* @package     Tag Groups
* @author      Christoph Amthor
* @copyright   2018 Christoph Amthor (@ Chatty Mango, chattymango.com)
* @license     GPL-3.0+
*/

if ( ! class_exists('TagGroups_Admin') ) {

  class TagGroups_Admin {


    function __construct() {
    }


    /**
    * Initial settings after calling the plugin
    * Effective only for admin backend
    */
    static function admin_init() {

      $tag_group_taxonomy = get_option( 'tag_group_taxonomy', array('post_tag') );

      foreach ( $tag_group_taxonomy as $taxonomy ) {

        // creating and editing tags
        add_action( "{$taxonomy}_edit_form_fields", array( 'TagGroups_Admin', 'tag_input_metabox' ) );

        add_action( "{$taxonomy}_add_form_fields", array( 'TagGroups_Admin', 'create_new_tag' ) );

        // extra columns on tag page
        add_filter( "manage_edit-{$taxonomy}_columns", array( 'TagGroups_Admin', 'add_taxonomy_columns' ) );

        add_filter( "manage_{$taxonomy}_custom_column", array( 'TagGroups_Admin', 'add_taxonomy_column_content' ), 10, 3 );
      }

      //admin_head
      add_action( 'in_admin_header', array( 'TagGroups_Settings', 'settings_page_actions' ) );

      add_action( 'quick_edit_custom_box', array( 'TagGroups_Admin', 'quick_edit_tag' ), 10, 3 );

      add_action( 'create_term', array( 'TagGroups_Admin', 'update_edit_term_group' ) );

      add_action( 'create_term', array( 'TagGroups_Admin', 'copy_term_group' ), 20 );

      add_action( 'edit_term', array( 'TagGroups_Admin', 'update_edit_term_group' ) );

      add_action( 'delete_term', array( 'TagGroups_Admin', 'update_post_meta' ), 10, 2 );

      add_action( 'groups_of_term_saved', array( 'TagGroups_Admin', 'update_post_meta' ), 10, 2 );

      add_action( 'load-edit-tags.php', array( 'TagGroups_Admin', 'bulk_action' ) );

      add_filter( "plugin_action_links_" . TAG_GROUPS_PLUGIN_BASENAME, array( 'TagGroups_Admin', 'add_plugin_settings_link' ) );

      add_action( 'admin_footer-edit-tags.php', array( 'TagGroups_Admin', 'quick_edit_javascript' ) );

      add_action( 'admin_footer-edit-tags.php', array( 'TagGroups_Admin', 'bulk_admin_footer' ) );

      add_filter( 'tag_row_actions', array( 'TagGroups_Admin', 'expand_quick_edit_link' ), 10, 2 );

      add_action( 'restrict_manage_posts', array( 'TagGroups_Admin', 'add_post_filter' ) );

      add_filter( 'parse_query', array( 'TagGroups_Admin', 'apply_post_filter' ) );

      // Ajax Handler
      add_action( 'wp_ajax_tg_ajax_manage_groups', array( 'TagGroups_Admin', 'ajax_manage_groups' ) );

      add_action( 'wp_ajax_tg_ajax_get_feed', array( 'TagGroups_Admin', 'ajax_get_feed' ) );

    }


    /**
    * Adds the submenus and the option page to the admin backend
    */
    static function register_menus()
    {

      // general settings
      if ( defined( 'TAG_GROUPS_PREMIUM_VERSION' ) ) {

        $title = 'Tag Groups Premium';

      } else {

        $title = 'Tag Groups';

      }

      // add_options_page( $title, $title, 'manage_options', 'tag-groups-settings', array( 'TagGroups_Admin', 'settings_page' ) );

      // Add the main menu
      add_menu_page(
        __( 'Home', 'tag-groups' ),
        'Tag Groups',
        'manage_options',
        'tag-groups-settings',
        array( 'TagGroups_Settings', 'settings_page_home' ),
        'dashicons-tag',
        '99.01'
      );

      // Define the menu structure
      $tag_groups_admin_structure = array(
        0 => array(
          'title'     => __( 'Home', 'tag-groups' ),
          'slug'      => 'tag-groups-settings', // repeating the slug of the top-level menu page to prevent it from reappearing as submenu
          'parent'    => 'tag-groups-settings',
          'user_can'  => 'manage_options',
          'function'  => array( 'TagGroups_Settings', 'settings_page_home' ),
        ),
        1 => array(
          'title'     => __( 'Taxonomies', 'tag-groups' ),
          'slug'      => 'tag-groups-settings-taxonomies',
          'parent'    => 'tag-groups-settings',
          'user_can'  => 'manage_options',
          'function'  => array( 'TagGroups_Settings', 'settings_page_taxonomies' ),
        ),
        3 => array(
          'title'     => __( 'Front End', 'tag-groups' ),
          'slug'      => 'tag-groups-settings-front-end',
          'parent'    => 'tag-groups-settings',
          'user_can'  => 'manage_options',
          'function'  => array( 'TagGroups_Settings', 'settings_page_front_end' ),
        ),
        4 => array(
          'title'     => __( 'Back End', 'tag-groups' ),
          'slug'      => 'tag-groups-settings-back-end',
          'parent'    => 'tag-groups-settings',
          'user_can'  => 'manage_options',
          'function'  => array( 'TagGroups_Settings', 'settings_page_back_end' ),
        ),
        5 => array(
          'title'     => __( 'Tools', 'tag-groups' ),
          'slug'      => 'tag-groups-settings-tools',
          'parent'    => 'tag-groups-settings',
          'user_can'  => 'manage_options',
          'function'  => array( 'TagGroups_Settings', 'settings_page_tools' ),
        ),
        6 => array(
          'title'     => __( 'Troubleshooting', 'tag-groups' ),
          'slug'      => 'tag-groups-settings-troubleshooting',
          'parent'    => 'tag-groups-settings',
          'user_can'  => 'manage_options',
          'function'  => array( 'TagGroups_Settings', 'settings_page_troubleshooting' ),
        ),
        // /: back end
        7 => array(
          'title'     => __( 'Premium', 'tag-groups' ),
          'slug'      => 'tag-groups-settings-premium',
          'parent'    => 'tag-groups-settings',
          'user_can'  => 'manage_options',
          'function'  => array( 'TagGroups_Settings', 'settings_page_premium' ),
        ),
        8 => array(
          'title'     => __( 'About', 'tag-groups' ),
          'slug'      => 'tag-groups-settings-about',
          'parent'    => 'tag-groups-settings',
          'user_can'  => 'manage_options',
          'function'  => array( 'TagGroups_Settings', 'settings_page_about' ),
        ),
        9 => array(
          'title'     => __( 'First Steps', 'tag-groups' ),
          'slug'      => 'tag-groups-settings-first-steps',
          'parent'    => null, // no menu
          'user_can'  => 'manage_options',
          'function'  => array( 'TagGroups_Settings', 'settings_page_onboarding' ),
        ),
      );

      // hook for premium plugin to modify the menu
      $tag_groups_admin_structure = apply_filters( 'tag_groups_admin_structure', $tag_groups_admin_structure );

      // make sure they all have the right order
      ksort( $tag_groups_admin_structure );

      // register the menus and pages
      foreach ( $tag_groups_admin_structure as $tag_groups_admin_page ) {

        add_submenu_page(
          $tag_groups_admin_page['parent'],
          $tag_groups_admin_page['title'],
          $tag_groups_admin_page['title'],
          $tag_groups_admin_page['user_can'],
          $tag_groups_admin_page['slug'],
          $tag_groups_admin_page['function']
        );

      }


      // for each registered taxonomy a tag group admin page

      $tag_group_taxonomies = get_option( 'tag_group_taxonomy', array('post_tag') );

      if ( class_exists( 'TagGroups_Premium' ) ) {

        $tag_group_role_edit_groups = get_option( 'tag_group_role_edit_groups', 'edit_pages');

      } else {

        $tag_group_role_edit_groups = 'edit_pages';

      }

      $tag_group_post_types = TagGroups_Taxonomy::post_types_from_taxonomies( $tag_group_taxonomies );

      foreach ( $tag_group_post_types as $post_type ) {

        if ( 'post' == $post_type ) {

          $post_type_query = '';

        } else {

          $post_type_query = '?post_type=' . $post_type;

        }

        $submenu_page = add_submenu_page( 'edit.php' . $post_type_query, 'Tag Groups', 'Tag Groups', $tag_group_role_edit_groups, 'tag-groups_' . $post_type, array( 'TagGroups_Admin', 'group_administration' ) );

        if ( class_exists( 'TagGroups_Premium_Admin' ) && method_exists( 'TagGroups_Premium_Admin', 'add_screen_option' ) ) {

          add_action( "load-$submenu_page", array( 'TagGroups_Premium_Admin', 'add_screen_option' ) );

        }

      }

    }


    /**
    *   Retrieves post types from taxonomies
    *   @DEPRECATED since 0.37; use TagGroups_Taxonomy::post_types_from_taxonomies()
    */
    static function post_types_from_taxonomies( $taxonomies = array() ) {

      if ( ! is_array( $taxonomies ) ) {

        $taxonomies = array( $taxonomies );

      }

      asort( $taxonomies ); // avoid duplicate cache entries

      $key = md5( serialize( $taxonomies ) );

      $transient_value = get_transient( 'tag_groups_post_types' );

      if ( $transient_value === false || ! isset( $transient_value[ $key ] ) ) {

        $post_types = array();

        foreach ( $taxonomies as $taxonomy ) {

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

          if ( ! empty( $post_type_a ) ) {

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
    * Create the html to add tags to tag groups on single tag view (after clicking tag for editing)
    * @param type $tag
    */
    static function tag_input_metabox( $tag )
    {
      $screen = get_current_screen();

      $group = new TagGroups_Group();

      if ( 'post' == $screen->post_type ) {

        $url_post_type = '';

      } else {

        $url_post_type = '&post_type=' . $screen->post_type;

      }

      $tag_group_admin_url = admin_url( 'edit.php?page=tag-groups_' . $screen->post_type . $url_post_type );

      $data = $group->get_all_with_position_as_key();

      unset( $data[0] );

      $term = new TagGroups_Term( $tag );

      ?>
      <tr class="form-field">
        <th scope="row" valign="top"><label for="tag_widget"><?php
        _e( 'Tag Groups', 'tag-groups' )
        ?></label></th>
        <td>
          <select id="term-group" name="term-group<?php if ( class_exists( 'TagGroups_Premium_Group' ) ) echo '[]' ?>"<?php if ( class_exists( 'TagGroups_Premium_Group' ) ) echo ' multiple' ?>>
            <?php if ( ! class_exists( 'TagGroups_Premium_Group' ) ) : ?>
              <option value="0" <?php
              if ( $term->is_in_group( 0 ) ) {
                echo 'selected';
              }
              ?> ><?php
              _e( 'not assigned', 'tag-groups' )
              ?></option>
              <?php
            endif;

            foreach ( $data as $term_group ) : ?>

            <option value="<?php echo $term_group[ 'term_group' ]; ?>"

              <?php
              if ( $term->is_in_group( $term_group[ 'term_group' ] ) ) {
                echo 'selected';
              }
              ?> ><?php echo htmlentities( $term_group[ 'label' ], ENT_QUOTES, "UTF-8" ); ?></option>

            <?php endforeach; ?>

          </select>
          <input type="hidden" name="tag-groups-nonce" id="tag-groups-nonce" value="<?php echo wp_create_nonce( 'tag-groups-nonce' )
          ?>" />
          <input type="hidden" name="tag-groups-taxonomy" id="tag-groups-taxonomy" value="<?php echo $screen->taxonomy; ?>" />

          <script>
          jQuery(document).ready(function () {
            jQuery('#term-group').SumoSelect({
              search: true,
              forceCustomRendering: true,
              <?php if ( class_exists( 'TagGroups_Premium_Group' ) ) : ?>
              triggerChangeCombined: true,
              selectAll: true,
              captionFormatAllSelected: '<?php _e( 'all {0} selected', 'tag-groups-premium' ) ?>',
              captionFormat: '<?php _e( '{0} selected', 'tag-groups-premium' ) ?>',
              <?php endif; ?>
            });
          });
          </script>
          <p>&nbsp;</p>
          <p><a href="<?php echo $tag_group_admin_url ?>"><?php
          _e( 'Edit tag groups', 'tag-groups' )
          ?></a>. (<?php
          _e( 'Clicking will leave this page without saving.', 'tag-groups' )
          ?>)</p>
        </td>
      </tr>
      <?php

    }


    /**
    * Create the html to assign tags to tag groups upon new tag creation (left of the table)
    * @param type $tag
    */
    static function create_new_tag( $tag )
    {

      $screen = get_current_screen();

      $group = new TagGroups_Group();

      $data = $group->get_all_with_position_as_key();

      unset( $data[0] );
      ?>

      <div class="form-field">
        <label for="term-group"><?php _e( 'Tag Groups', 'tag-groups' ) ?></label>

        <select id="term-group" name="term-group<?php if ( class_exists( 'TagGroups_Premium_Group' ) ) echo '[]' ?>"<?php if ( class_exists( 'TagGroups_Premium_Group' ) ) echo ' multiple' ?>>
          <?php if ( ! class_exists( 'TagGroups_Premium_Group' ) ) : ?>
            <option value="0" selected ><?php
            _e( 'not assigned', 'tag-groups' )
            ?></option>
          <?php endif;

          foreach ( $data as $term_group ) :
            ?>
            <option value="<?php echo $term_group['term_group']; ?>"><?php echo htmlentities( $term_group['label'], ENT_QUOTES, "UTF-8" ); ?></option>
          <?php endforeach; ?>
        </select>
        <script>
        jQuery(document).ready(function () {
          jQuery('#term-group').SumoSelect({
            search: true,
            forceCustomRendering: true,
            <?php if ( class_exists( 'TagGroups_Premium_Group' ) ) : ?>
            triggerChangeCombined: true,
            selectAll: true,
            captionFormatAllSelected: '<?php _e( 'all {0} selected', 'tag-groups-premium' ) ?>',
            captionFormat: '<?php _e( '{0} selected', 'tag-groups-premium' ) ?>',
            <?php endif; ?>
          });
        });
        </script>
        <input type="hidden" name="tag-groups-nonce" id="tag-groups-nonce" value="<?php echo wp_create_nonce( 'tag-groups-nonce' )
        ?>" />
        <input type="hidden" name="new-tag-created" id="new-tag-created" value="1" />
        <input type="hidden" name="tag-groups-taxonomy" id="tag-groups-taxonomy" value="<?php echo $screen->taxonomy; ?>" />
      </div>

      <?php

    }



    /**
    * adds a custom column to the table of tags/terms
    * thanks to http://coderrr.com/add-columns-to-a-taxonomy-terms-table/
    * @global object $wp
    * @param array $columns
    * @return string
    */
    static function add_taxonomy_columns( $columns )
    {

      global $wp;

      $new_order = (isset( $_GET['order'] ) && $_GET['order'] == 'asc' && isset( $_GET['orderby'] ) && $_GET['orderby'] == 'term_group') ? 'desc' : 'asc';

      $screen = get_current_screen();
      if ( ! empty( $screen )) {

        $taxonomy = $screen->taxonomy;


        $link = add_query_arg( array('orderby' => 'term_group', 'order' => $new_order, 'taxonomy' => $taxonomy), admin_url( "edit-tags.php" . $wp->request ) );

        $link_escaped = esc_url( $link );

        $columns['term_group'] = '<a href="' . $link_escaped . '"><span>' . __( 'Tag Group', 'tag-groups' ) . '</span><span class="sorting-indicator"></span></a>';

      }  else {

        $columns['term_group'] = '';

      }

      return $columns;

    }



    /**
    * adds data into custom column of the table for each row
    * thanks to http://coderrr.com/add-columns-to-a-taxonomy-terms-table/
    * @param type $a
    * @param type $b
    * @param type $term_id
    * @return string
    */
    static function add_taxonomy_column_content( $a = '', $b = '', $term_id = 0 )
    {

      if ( 'term_group' != $b ) {

        return $a;

      } // credits to Navarro (http://navarradas.com)

      if ( ! empty( $_REQUEST['taxonomy'] ) ) {

        $taxonomy = sanitize_title( $_REQUEST['taxonomy'] );

      } else {

        return '';
      }

      $term = get_term( $term_id, $taxonomy );

      $group = new TagGroups_Group();

      if ( isset( $term ) ) {

        $term_o = new TagGroups_Term( $term );

        return implode( ', ', $group->get_labels( $term_o->get_groups() ) ) ;

      } else {

        return '';

      }

    }


    /**
    *
    * processing actions defined in bulk_admin_footer()
    * credits http://www.foxrunsoftware.net
    * @global int $tg_update_edit_term_group_called
    * @return void
    */
    static function bulk_action()
    {

      global $tg_update_edit_term_group_called;

      $tag_group_taxonomy = get_option( 'tag_group_taxonomy', array('post_tag') );

      $screen = get_current_screen();

      $taxonomy = $screen->taxonomy;

      if ( is_object( $screen ) && ( !in_array( $taxonomy, $tag_group_taxonomy ) ) ) {

        return;

      }

      $show_filter_tags = get_option( 'tag_group_show_filter_tags', 1 );

      if ( $show_filter_tags ) {

        $tag_group_tags_filter = get_option( 'tag_group_tags_filter', array() );

        /*
        * Processing the filter
        * Values come as POST (via menu, precedence) or GET (via link from group admin)
        */
        if ( isset( $_POST['term-filter'] ) ) {

          $term_filter = (int) $_POST['term-filter'];

        } elseif ( isset( $_GET['term-filter'] ) ) {

          $term_filter = (int) $_GET['term-filter'];

          // We need to remove the term-filter piece, or it will stay forever
          $sendback = remove_query_arg( array( 'term-filter' ), $_SERVER['REQUEST_URI']);

        }

        if ( isset( $term_filter ) ) {

          if ( '-1' == $term_filter ) {

            unset( $tag_group_tags_filter[ $taxonomy ] );

            update_option( 'tag_group_tags_filter', $tag_group_tags_filter );

          } else {

            $tag_group_tags_filter[ $taxonomy ] = $term_filter;

            update_option( 'tag_group_tags_filter', $tag_group_tags_filter );

            /*
            * Modify the query
            */
            add_action( 'terms_clauses', array( 'TagGroups_Admin', 'terms_clauses' ), 10, 3 );

          }

          if ( isset( $sendback ) ) {

            // remove filter that destroys WPML's "&lang="
            remove_all_filters( 'wp_redirect' );

            // escaping $sendback
            wp_redirect( esc_url_raw( $sendback ) );

            exit;

          }

        } else {

          /*
          * If filter is set, make sure to modify the query
          */
          if ( isset( $tag_group_tags_filter[ $taxonomy ] ) ) {

            add_action( 'terms_clauses', array( 'TagGroups_Admin', 'terms_clauses' ), 10, 3 );

          }
        }

      }

      $wp_list_table = _get_list_table( 'WP_Terms_List_Table' );

      $action = $wp_list_table->current_action();

      $allowed_actions = array( 'assign' );

      if ( ! in_array( $action, $allowed_actions ) ) {

        return;

      }

      if ( isset( $_REQUEST['delete_tags'] ) ) {

        $term_ids = $_REQUEST['delete_tags'];

      }

      if ( isset( $_REQUEST['term-group-top'] ) ) {

        $term_group = (int) $_REQUEST['term-group-top'];

      } else {

        return;

      }

      $sendback = remove_query_arg( array( 'assigned', 'deleted' ), wp_get_referer() );

      if ( !$sendback ) {

        $sendback = admin_url( 'edit-tags.php?taxonomy=' . $taxonomy );

      }

      if ( empty( $term_ids ) ) {

        $sendback = add_query_arg( array( 'number_assigned' => 0, 'group_id' => $term_group ), $sendback );

        $sendback = remove_query_arg( array( 'action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view' ), $sendback );

        // escaping $sendback
        wp_redirect( esc_url_raw( $sendback ) );

        exit();

      }

      $pagenum = $wp_list_table->get_pagenum();

      $sendback = add_query_arg( 'paged', $pagenum, $sendback );

      $tg_update_edit_term_group_called = true; // skip update_edit_term_group()

      switch ( $action ) {
        case 'assign':

        $assigned = 0;

        foreach ( $term_ids as $term_id ) {

          $term = new TagGroups_Term( $term_id );

          if ( false !== $term ) {

            if ( 0 == $term_group ) {

              $term->remove_all_groups()->save();

            } else {

              $term->add_group( $term_group )->save();

            }

            $assigned++;

          }

        }

        if ( 0 == $term_group ) {

          $message = _n( 'The term has been removed from all groups.', sprintf( '%d terms have been removed from all groups.', number_format_i18n( (int) $assigned ) ), (int) $assigned, 'tag-groups' );

        } else {

          $group = new TagGroups_Group( $term_group );

          $message = _n( sprintf( 'The term has been assigned to the group %s.', '<i>' . $group->get_label() . '</i>' ), sprintf( '%d terms have been assigned to the group %s.', number_format_i18n( (int) $assigned ), '<i>' . $group->get_label() . '</i>' ), (int) $assigned, 'tag-groups' );
        }

        break;

        default:
        // Need to show a message?

        break;
      }

      TagGroups_Admin_Notice::add( 'success', $message );

      $sendback = remove_query_arg( array( 'action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view' ), $sendback );

      wp_redirect( esc_url_raw( $sendback ) );

      exit();

    }


    /**
    * modifies Quick Edit link to call JS when clicked
    * thanks to http://shibashake.com/WordPress-theme/expand-the-WordPress-quick-edit-menu
    * @param array $actions
    * @param object $tag
    * @return array
    */
    static function expand_quick_edit_link( $actions, $tag )
    {

      $screen = get_current_screen();

      $tag_group_taxonomy = get_option( 'tag_group_taxonomy', array('post_tag') );

      if ( is_object( $screen ) && (!in_array( $screen->taxonomy, $tag_group_taxonomy ) ) ) {

        return $actions;

      }

      $term_o = new TagGroups_Term( $tag );

      $groups = htmlspecialchars( json_encode( $term_o->get_groups() ) );


      $nonce = wp_create_nonce( 'tag-groups-nonce' );

      $actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="';

      $actions['inline hide-if-no-js'] .= esc_attr( __( 'Edit this item inline', 'tag-groups' ) ) . '" ';

      $actions['inline hide-if-no-js'] .= " onclick=\"set_inline_tag_group_selected('{$groups}', '{$nonce}')\">";

      $actions['inline hide-if-no-js'] .= __( 'Quick&nbsp;Edit', 'tag-groups' );

      $actions['inline hide-if-no-js'] .= '</a>';

      return $actions;

    }


    /**
    * adds JS function that sets the saved tag group for a given element when it's opened in quick edit
    * thanks to http://shibashake.com/WordPress-theme/expand-the-WordPress-quick-edit-menu
    * @return void
    */
    static function quick_edit_javascript()
    {

      $screen = get_current_screen();

      $tag_group_taxonomy = get_option( 'tag_group_taxonomy', array('post_tag') );

      if ( ! in_array( $screen->taxonomy, $tag_group_taxonomy ) ) {

        return;
      }

      ?>
      <script type="text/javascript">
      <!--
      function set_inline_tag_group_selected(termGroupsSelectedJson, nonce) {
        var termGroupsSelected = JSON.parse(termGroupsSelectedJson);
        inlineEditTax.revert();
        var tagGroupsSelectElement = document.getElementById('term-group-option');
        var nonceInput = document.getElementById('tag-groups-option-nonce');
        nonceInput.value = nonce;
        for (i = 0; i < tagGroupsSelectElement.options.length; i++) {
          if (termGroupsSelected.indexOf(parseInt(tagGroupsSelectElement.options[i].value)) > -1) {
            tagGroupsSelectElement.options[i].setAttribute("selected", "selected");
          } else {
            tagGroupsSelectElement.options[i].removeAttribute("selected");
          }
          if (i + 1 == tagGroupsSelectElement.options.length) callSumoSelect();
        }
      }

      function callSumoSelect() {
        setTimeout(function() {
          jQuery('#term-group-option').SumoSelect({
            search: true,
            forceCustomRendering: true,
            <?php if ( class_exists( 'TagGroups_Premium_Group' ) ) : ?>
            selectAll: true,
            captionFormatAllSelected: '<?php _e( 'all {0} selected', 'tag-groups-premium' ) ?>',
            captionFormat: '<?php _e( '{0} selected', 'tag-groups-premium' ) ?>',
            <?php endif; ?>
          });
        }, 50);
      }


      //-->
      </script>
      <?php

    }


    /**
    * Create the html to assign tags to tag groups directly in tag table ('quick edit')
    * @return type
    */
    static function quick_edit_tag()
    {

      global $tg_quick_edit_tag_called;

      if ( $tg_quick_edit_tag_called ) {

        return;

      }

      $tg_quick_edit_tag_called = true;

      $screen = get_current_screen();

      $tag_group_taxonomy = get_option( 'tag_group_taxonomy', array('post_tag') );

      if ( ! in_array( $screen->taxonomy, $tag_group_taxonomy ) ) {

        return;

      }

      $group = new TagGroups_Group();

      $data = $group->get_all_with_position_as_key();

      unset( $data[0] );
      ?>

      <fieldset><div class="inline-edit-col">

        <label><span class="title"><?php
        _e( 'Groups', 'tag-groups' )
        ?></span><span class="input-text-wrap">

          <select id="term-group-option" name="term-group<?php if ( class_exists( 'TagGroups_Premium_Group' ) ) echo '[]' ?>" class="ptitle"<?php if ( class_exists( 'TagGroups_Premium_Group' ) ) echo ' multiple' ?>>
            <?php if ( ! class_exists( 'TagGroups_Premium_Group' ) ) : ?>
              <option value="0" ><?php
              _e( 'not assigned', 'tag-groups' )
              ?></option>
            <?php endif;

            foreach ( $data as $term_group ) :
              ?>

              <option value="<?php echo $term_group['term_group']; ?>" ><?php echo htmlentities( $term_group['label'], ENT_QUOTES, "UTF-8" ); ?></option>

            <?php endforeach; ?>
          </select>

          <?php // id must be "tag-groups-option-nonce" because otherwise identical with "Add New Tag" form on the left side. ?>
          <input type="hidden" name="tag-groups-nonce" id="tag-groups-option-nonce" value="" />

          <input type="hidden" name="tag-groups-taxonomy" id="tag-groups-taxonomy" value="<?php echo $screen->taxonomy; ?>" />

        </span></label>

      </div></fieldset>
      <?php

    }


    /**
    * Updates the post meta
    *
    *
    * @param type var Description
    * @return return type
    */
    public static function update_post_meta( $term_id, $term_groups = array() )
    {

      /**
      * update the post meta
      */
      if ( class_exists( 'TagGroups_Premium_Post' ) ) {

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

          error_log( '[Tag Groups Premium] Checking if posts need to be migrated.' );

          $start_time = microtime( true );

        }

        $count = TagGroups_Premium_Post::update_post_meta_for_term( $term_id, $term_groups );

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

          error_log( sprintf( '[Tag Groups Premium] Meta of %d post(s) updated in %d milliseconds.', $count, round( ( microtime( true ) - $start_time ) * 1000 ) ) );

        }

      }

    }


    /**
    * Get the $_POSTed value after saving a tag/term and save it in the table
    *
    * @global int $tg_update_edit_term_group_called
    * @param int $term_id
    * @return void
    */
    public static function update_edit_term_group( $term_id )
    {

      // next lines to prevent infinite loops when the hook edit_term is called again from the function wp_update_term
      global $tg_update_edit_term_group_called;

      if ( $tg_update_edit_term_group_called ) {

        return;

      }

      $screen = get_current_screen();

      // $_POST['term-group'] won't be submitted if multi select is empty
      if ( ! isset( $_POST['term-group'] ) ) {

        return;

      }

      $tag_group_taxonomy = get_option( 'tag_group_taxonomy', array('post_tag') );

      if ( is_object( $screen ) && ( ! in_array( $screen->taxonomy, $tag_group_taxonomy ) ) && ( ! isset( $_POST['new-tag-created'] ) ) ) {

        return;

      }

      $tg_update_edit_term_group_called = true;

      if ( empty( $_POST['tag-groups-nonce'] ) || ! wp_verify_nonce( $_POST['tag-groups-nonce'], 'tag-groups-nonce' ) ) {

        die( "Security check" );

      }

      $term_id = (int) $term_id;

      $term = new TagGroups_Term( $term_id );


      if ( ! empty( $_POST['term-group'] ) ) {

        if ( is_array( $_POST['term-group'] ) ) {

          $term_group = array_map( 'intval', $_POST['term-group'] );

        } else {

          $term_group = (int) $_POST['term-group'];

        }

        $term->set_group( $term_group )->save();

      } else {

        $term->set_group( 0 )->save();

      }

      /**
      *   If necessary we also save default WP term properties.
      *   Make sure we have a taxonomy
      */
      if ( isset( $_POST['tag-groups-taxonomy'] ) ) {

        $taxonomy = sanitize_title( $_POST['tag-groups-taxonomy'] );

        $args = array();

        /**
        * Save the tag name
        */
        if ( isset( $_POST['name'] ) && ( $_POST['name'] != '' ) ) { // allow zeros

          $args['name'] = stripslashes( sanitize_text_field( $_POST['name'] ) );

        }

        /**
        * Save the tag slug
        */
        if ( isset( $_POST['slug'] ) ) { // allow empty values

          $args['slug'] = sanitize_title( $_POST['slug'] );

        }

        /**
        * Save the tag description
        */
        if ( isset( $_POST['description'] ) ) { // allow empty values

          /**
          * Check if the settings require us to omit sanitization
          */
          if ( get_option( 'tag_group_html_description', 0 ) ) {

            $args['description'] = $_POST['description'];

          } else {

            $args['description'] = stripslashes( sanitize_text_field( $_POST['description'] ) );

          }

        }

        /**
        * Save the parent
        */
        if ( isset( $_POST['parent'] ) && ($_POST['parent'] != '') ) {

          $args['parent'] = (int) $_POST['parent'] ;

        }

        wp_update_term( $term_id, $taxonomy, $args );

      }

    }


    /**
    * WPML: Check if we need to copy group info to the translation
    *
    * Copy the groups of an original term to its translation if a translation is saved
    *
    * @param type $term_id
    * @return type
    */
    public static function copy_term_group( $term_id ) {

      /**
      * Check if WPML is available
      */
      $default_language_code = apply_filters( 'wpml_default_language', null );

      if ( ! isset( $default_language_code ) ) {

        return;

      }


      /**
      * Check if the new tag has no group set or groups set to unassigned
      */
      $term = new TagGroups_Term( $term_id );

      $translated_term_groups = $term->get_groups();

      if ( ! empty( $translated_term_groups ) && $translated_term_groups != array( 0 ) ) {

        return;

      }


      /**
      *   edit-tags.php form
      */
      if (
        isset( $_POST['icl_tax_post_tag_language'] )
        && $_POST['icl_tax_post_tag_language'] != $default_language_code
      ) {

        if ( ! empty( $_POST['icl_translation_of'] ) ) {
          // translated from the default language

          $original_term_id = $_POST['icl_translation_of'];

        } elseif ( ! empty( $_POST['icl_trid'] ) ) {
          // translated from another translated language

          $translations = apply_filters( 'wpml_get_element_translations', null, $_POST['icl_trid'] );

          if ( isset( $translations[ $default_language_code ]->element_id ) ) {

            $original_term_id = $translations[ $default_language_code ]->element_id;

          }

        }

      }


      /**
      *   taxonomy-translation.php form
      */
      elseif (
        isset( $_POST['term_language_code'] )
        && $_POST['term_language_code'] != $default_language_code
        && ! empty( $_POST['trid'] )
      ) {

        $translations = apply_filters( 'wpml_get_element_translations', null, $_POST['trid'] );

        if ( isset( $translations[ $default_language_code ]->element_id ) ) {

          $original_term_id = $translations[ $default_language_code ]->element_id;

        }

      }


      if ( isset( $original_term_id ) ) {

        $tg_original_term = new TagGroups_Term( $original_term_id );

        $original_term_groups = $tg_original_term->get_groups();

        if ( ! empty( $original_term_groups) ) {

          $term->set_group( $original_term_groups )->save();

        }

      }

    }


    /**
    * Adds a bulk action menu to a term list page
    * credits http://www.foxrunsoftware.net
    * @return void
    */
    static function bulk_admin_footer()
    {

      $tag_group_taxonomy = get_option( 'tag_group_taxonomy', array('post_tag') );

      $screen = get_current_screen();

      if ( is_object( $screen ) && ( ! in_array( $screen->taxonomy, $tag_group_taxonomy ) ) ) {

        return;

      }

      $show_filter_tags = get_option( 'tag_group_show_filter_tags', 1 );

      $group = new TagGroups_Group();

      $data = $group->get_all_with_position_as_key();

      /*
      * 	constructing the action menu
      *
      *   Using .html() instead of .text() to avoid ampersands displaying
      */
      ?>
      <script type="text/javascript">
      jQuery(document).ready(function () {
        jQuery('<option>').val('assign').html('<?php
        _e( 'Assign to', 'tag-groups' )
        ?>').appendTo("select[name='action']");
        jQuery('<option>').val('assign').html('<?php
        _e( 'Assign to', 'tag-groups' )
        ?>').appendTo("select[name='action2']");
        var sel_top = jQuery("<select name='term-group-top'>").insertAfter("select[name='action']");
        var sel_bottom = jQuery("<select name='term-group-bottom'>").insertAfter("select[name='action2']");
        <?php foreach ( $data as $term_group ) : ?>
        sel_top.append(jQuery("<option>").attr("value", "<?php echo $term_group['term_group'] ?>").html("<?php echo htmlentities( $term_group['label'], ENT_QUOTES, "UTF-8" )
        ?>"));
        sel_bottom.append(jQuery("<option>").attr("value", "<?php echo $term_group['term_group'] ?>").html("<?php echo htmlentities( $term_group['label'], ENT_QUOTES, "UTF-8" )
        ?>"));
        <?php endforeach; ?>

        <?php if ( isset( $_GET['orderby'] ) && $_GET['orderby'] == 'term_group' ) : ?>
        jQuery('th#term_group').addClass('sorted');
        <?php else: ?>
        jQuery('th#term_group').addClass('sortable');
        <?php endif; ?>
        <?php if ( isset( $_GET['order'] ) && $_GET['order'] == 'asc' ) : ?>
        jQuery('th#term_group').addClass('asc');
        <?php else: ?>
        jQuery('th#term_group').addClass('desc');
        <?php endif; ?>

        jQuery('[name="term-group-top"]').change(function () {
          jQuery('[name="action"]').val('assign');
          jQuery('[name="action2"]').val('assign');
          var selected = jQuery(this).val();
          jQuery('[name="term-group-bottom"]').val(selected);
        });
        jQuery('[name="term-group-bottom"]').change(function () {
          jQuery('[name="action"]').val('assign');
          jQuery('[name="action2"]').val('assign');
          var selected = jQuery(this).val();
          jQuery('[name="term-group-top"]').val(selected);
        });
        <?php
        /*
        * 	constructing the filter menu
        */
        if ( $show_filter_tags ) :

          $tag_group_tags_filter = get_option( 'tag_group_tags_filter', array() );

          if ( isset( $tag_group_tags_filter[ $screen->taxonomy ] ) ) {

            $tag_filter = $tag_group_tags_filter[ $screen->taxonomy ];

          } else {

            $tag_filter = -1;

          }

          /**
          *   Using .html() instead of .text() to avoid ambersands displaying
          */
          ?>
          var sel_filter = jQuery("<select id='tag_filter' name='term-filter' style='margin-left: 20px;'>").insertAfter("select[name='term-group-top']");
          sel_filter.append(jQuery("<option>").attr("value", "-1").html("<?php
          _e( 'Filter off', 'tag-groups' )
          ?>"));
          <?php foreach ( $data as $term_group ) : ?>
          sel_filter.append(jQuery("<option>").attr("value", "<?php echo $term_group['term_group'] ?>").html("<?php echo htmlentities( $term_group['label'], ENT_QUOTES, "UTF-8" )?>"));
          <?php endforeach; ?>
          jQuery("#tag_filter option[value=<?php echo $tag_filter ?>]").prop('selected', true);
        });</script>
        <?php
      endif;

    }


    /**
    * Adds a pull-down menu to the filters above the posts.
    * Based on the code by Ohad Raz, http://wordpress.stackexchange.com/q/45436/2487
    * License: Creative Commons Share Alike
    * @return void
    */
    static function add_post_filter()
    {

      if ( ! get_option( 'tag_group_show_filter', 1 ) ) {

        return;

      }

      $tag_group_taxonomy = get_option( 'tag_group_taxonomy', array('post_tag') );


      $post_type = ( isset( $_GET['post_type'] ) ) ? sanitize_title( $_GET['post_type'] ) : 'post';

      if ( count( array_intersect( $tag_group_taxonomy, get_object_taxonomies( $post_type ) ) ) ) {

        $group = new TagGroups_Group();

        $data = $group->get_all_term_group_label();

        ?>
        <select name="tg_filter_posts_value">
          <option value=""><?php
          _e( 'Filter by tag group', 'tag-groups' ); ?></option>
          <?php
          $current_term_group = isset( $_GET['tg_filter_posts_value'] ) ? sanitize_text_field( $_GET['tg_filter_posts_value'] ) : '';

          foreach ( $data as $term_group => $label ) {
            printf( '<option value="%s"%s>%s</option>', $term_group, ( '' != $current_term_group && $term_group == $current_term_group ) ? ' selected="selected"' : '', htmlentities( $label, ENT_QUOTES, "UTF-8" ) );
          }
          ?>
        </select>
        <script>
        jQuery(document).ready(function(){
          jQuery('#cat').hide();
        });
        </script>
        <?php
      }

    }


    /**
    * Applies the filter, if used.
    * Based on the code by Ohad Raz, http://wordpress.stackexchange.com/q/45436/2487
    * License: Creative Commons Share Alike
    *
    * @global type $pagenow
    * @param type $query
    * @return type
    */
    static function apply_post_filter( $query )
    {

      global $pagenow;

      if ( $pagenow != 'edit.php' ) {

        return $query;

      }

      $show_filter_posts = get_option( 'tag_group_show_filter', 1 );

      if ( ! $show_filter_posts ) {

        return;

      }

      if ( isset( $_GET['post_type'] ) ) {

        $post_type = sanitize_title( $_GET['post_type'] );

      } else {

        $post_type = 'post';

      }

      /**
      * Losing here the filter by language from Polylang, but currently no other way to show any posts when combining tax_query and meta_query
      */
      unset( $query->query_vars['tax_query'] );


      $tg_taxonomy = get_option( 'tag_group_taxonomy', array('post_tag') );
      // note: removed restriction count( $tg_taxonomy ) <= 1 - rather let user figure out if the result works

      $taxonomy_intersect = array_intersect( $tg_taxonomy, get_object_taxonomies( $post_type ) );

      if ( count( $taxonomy_intersect ) && isset( $_GET['tg_filter_posts_value'] ) &&  $_GET['tg_filter_posts_value'] !== '' ) {

        if ( ! class_exists( 'TagGroups_Premium_Post' ) ) {
          // one tag group per tag

          $filter_terms = array( );

          $query->query_vars['tax_query'] = array(
            'relation' => 'OR'
          );

          $args = array(
            'taxonomy' => $taxonomy_intersect
          );

          $terms = get_terms( $args );

          if ( $terms ) {

            $selected_term_group = (int) $_GET['tg_filter_posts_value'];

            /**
            * Filtering for terms that are not assigned to group $selected_term_group
            * Add per taxonomy for future extensibility
            */
            foreach ( $terms as $term ) {

              if ( $term->term_group == $selected_term_group ) {

                $filter_terms[$term->taxonomy][] = $term->term_id;
              }

            }

            foreach ( $taxonomy_intersect as $taxonomy ) {

              /**
              * Add a dummy so that the taxonomy condition will not be ignored even if no applicable tags were found.
              */
              if ( ! isset( $filter_terms[$taxonomy] ) ) {
                $filter_terms[$taxonomy][] = 0;
              }

              $query->query_vars['tax_query'][] = array(
                'taxonomy'  => $taxonomy,
                'field'     => 'term_id',
                'terms'     => $filter_terms[$taxonomy],
                'compare'   => 'IN',
              );
            }

          }

        } else {
          // multiple tag groups per tag

          $query->query_vars['meta_query'] = TagGroups_Premium_Post::get_meta_query_group( (int) $_GET['tg_filter_posts_value'] );

        }

        /**
        * In case we use the Polylang plugin: get the terms for the language of that post.
        */
        if ( function_exists( 'pll_current_language' ) ) {

          /**
          * Better sanitize what we get from other plugins
          */
          $query->query_vars['lang'] = sanitize_text_field( pll_current_language( 'locale' ) );

        }

      }

      return $query;
    }


    /**
    * AJAX handler to get a feed
    */
    static function ajax_get_feed()
    {

      $response = new WP_Ajax_Response;

      if ( isset( $_REQUEST['url'] ) ) {
        $url = esc_url_raw( $_REQUEST['url'] );
      } else {
        $url = '';
      }

      if ( isset( $_REQUEST['amount'] ) ) {
        $amount = (int) $_REQUEST['amount'];
      } else {
        $amount = 5;
      }

      /**
      * Assuming that the posts URL is the $url minus the trailing /feed
      */
      $posts_url = preg_replace( '/(.+)feed\/?/i', '$1', $url );

      $rss = new TagGroups_Feed;

      $rss->debug( WP_DEBUG )->url( $url );
      $cache = $rss->cache_get();

      if ( empty( $cache ) ) {

        $cache = $rss->posts_url( $posts_url )->load()->parse()->render( $amount );

      }

      $response->add( array(
        'data' => 'success',
        'supplemental' => array(
          'output' => $cache,
        ),
      ));

      // Cannot use the method $response->send() because it includes die()
      header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ) );
      echo "<?xml version='1.0' encoding='" . get_option( 'blog_charset' ) . "' standalone='yes'?><wp_ajax>";
      foreach ( (array) $response->responses as $response_item ){
        echo $response_item;
      }
      echo '</wp_ajax>';


      // check if we received expired cache content
      if ( false !== $cache && $rss->expired ) {

        // load in background for next time
        $rss->posts_url( $posts_url )->load()->parse()->render( $amount );

        if ( WP_DEBUG ) {
          error_log('[Tag Groups] Preloaded feed into cache.');
        }

      }

      if ( wp_doing_ajax() ) {

        wp_die();

      } else {

        die();

      }

    }


    /**
    * AJAX handler to manage Tag Groups
    */
    static function ajax_manage_groups()
    {

      $response = new WP_Ajax_Response;

      if ( isset( $_REQUEST['task'] ) ) {

        $task = $_REQUEST['task'];

      } else {

        $task = 'refresh';

      }

      if ( isset( $_REQUEST['taxonomy'] ) ) {

        $taxonomy = $_REQUEST['taxonomy'];

      } else {

        $taxonomy = array( 'post_tag' );

      }

      $message = '';

      if ( class_exists( 'TagGroups_Premium' ) ) {

        $tag_group_role_edit_groups = get_option( 'tag_group_role_edit_groups', 'edit_pages');

      } else {

        $tag_group_role_edit_groups = 'edit_pages';

      }

      if (
        $task == 'refresh' ||
        $task == 'test' ||
        ( current_user_can( $tag_group_role_edit_groups ) && wp_verify_nonce( $_REQUEST['nonce'], 'tg_groups_management' ) )
      ) {

        if ( isset( $_REQUEST['position'] ) ) {

          $position = (int) $_REQUEST['position'];

        } else {

          $position = 0;

        }

        if ( isset( $_REQUEST['new_position'] ) ) {

          $new_position = (int) $_REQUEST['new_position'];

        } else {

          $new_position = 0;

        }

        if ( isset( $_REQUEST['start_position'] ) ) {

          $start_position = (int) $_REQUEST['start_position'];

        }

        if ( empty( $start_position ) || $start_position < 1 ) {

          $start_position = 1;

        }

        if ( isset( $_REQUEST['end_position'] ) ) {

          $end_position = (int) $_REQUEST['end_position'];

        }

        if ( empty( $end_position ) || $end_position < 1 ) {

          $end_position = 1;

        }

        $group = new TagGroups_Group();

        switch ( $task ) {

          case "new":

          if ( isset( $_REQUEST['label'] ) ) {

            $label = stripslashes( sanitize_text_field( $_REQUEST['label'] ) );

          }

          if ( empty( $label ) ) {

            $message = __( 'The label cannot be empty.', 'tag-groups' );
            TagGroups_Admin::send_error( $response, $message, $task );

          } elseif ( $group->find_by_label( $label ) ) {

            $message = sprintf( __( 'A tag group with the label \'%s\' already exists, or the label has not changed. Please choose another one or go back.', 'tag-groups' ), $label );
            TagGroups_Admin::send_error( $response, $message, $task );

          } else {

            $group->create( $position + 1, $label );

            $message = sprintf( __( 'A new tag group with the label \'%s\' has been created!', 'tag-groups' ), $label );

          }
          break;

          case "update":
          if ( isset( $_REQUEST['label'] ) ) {

            $label = stripslashes( sanitize_text_field( $_REQUEST['label'] ) );

          }

          if ( empty( $label ) ) {

            $message = __( 'The label cannot be empty.', 'tag-groups' );
            TagGroups_Admin::send_error( $response, $message, $task );

          } elseif ( $group->find_by_label( $label ) ) {

            if ( ! empty( $position ) && $position == $group->get_position() ) {
              // Label hast not changed, just ignore

            } else {

              $message = sprintf( __( 'A tag group with the label \'%s\' already exists.', 'tag-groups' ), $label );
              TagGroups_Admin::send_error( $response, $message, $task );

            }
          } else {

            if ( ! empty( $position ) ) {

              if ( $group->find_by_position( $position ) ) {

                $group->change_label( $label );

              }

            } else {

              TagGroups_Admin::send_error( $response, 'error: invalid position: ' . $position, $task );

            }

            $message = sprintf( __( 'The tag group with the label \'%s\' has been saved!', 'tag-groups' ), $label );

          }

          break;

          case "delete":
          if ( ! empty( $position ) && $group->find_by_position( $position ) ) {

            $message = sprintf( __( 'A tag group with the id %1$s and the label \'%2$s\' has been deleted.', 'tag-groups' ), $group->get_term_group(), $group->get_label() );

            $group->delete();

          } else {

            TagGroups_Admin::send_error( $response, 'error: invalid position: ' . $position, $task );

          }

          break;

          case "up":
          if ( $position > 1 && $group->find_by_position( $position ) ) {

            if ( $group->move_to_position( $position - 1 ) !== false ) {

              $group->save();

            }

          }
          break;

          case "down":
          if ( $position < $group->get_max_position() && $group->find_by_position( $position ) ) {

            if ( $group->move_to_position( $position + 1 ) !== false ) {

              $group->save();

            }

          }
          break;

          case "move":

          if ( $new_position < 1 ) {

            $new_position = 1;

          }

          if ( $new_position > $group->get_max_position() ) {

            $new_position = $group->get_max_position();

          }

          if ( $position == $new_position ) {

            break;

          }

          if ( $group->find_by_position( $position ) ) {

            if ( $group->move_to_position( $new_position ) !== false ) {

              $group->save();

              $message = __( 'New order saved.', 'tag-groups' );

            }

          }

          break;

          case "refresh":
          // do nothing here
          break;


          case 'test':

          $response->add( array(
            'data' => 'success',
            'supplemental' => array(
              'message' => 'This is the regular Ajax response.'
            )
          ) );

          $response->send();

          exit();

          break;

        }

        $number_of_term_groups = $group->get_number_of_term_groups() - 1; // "not assigned" won't be displayed

        if ( $start_position > $number_of_term_groups ) {

          $start_position = $number_of_term_groups;

        }

        $items_per_page = self::get_items_per_page();

        // calculate start and end positions
        $start_position = floor( ($start_position - 1) / $items_per_page ) * $items_per_page + 1;

        if ( $start_position + $items_per_page - 1 < $number_of_term_groups ) {

          $end_position = $start_position + $items_per_page - 1;

        } else {

          $end_position = $number_of_term_groups;

        }

        $response->add( array(
          'data' => 'success',
          'supplemental' => array(
            'task' => $task,
            'message' => $message,
            'nonce' => wp_create_nonce( 'tg_groups_management' ),
            'start_position' => $start_position,
            'groups' => json_encode( TagGroups_Admin::group_table( $start_position, $end_position, $taxonomy, $group ) ),
            'max_number' => $number_of_term_groups
          ),
        ));

      } else {

        TagGroups_Admin::send_error( $response, 'Security check', $task );

      }

      $response->send();

      exit();

    }



    /**
    *  Rerturns an error message to AJAX
    */
    static function send_error( $response, $message = 'error', $task = 'unknown' )
    {
      $response->add( array(
        'data' => 'error',
        'supplemental' => array(
          'message' => $message,
          'task' => $task,
        )
      ) );
      $response->send();
      exit();

    }


    /**
    * Assemble the content of the table of tag groups for AJAX
    */
    static function group_table( $start_position, $end_position, $taxonomy, $group )
    {

      $data = $group->get_all_with_position_as_key();

      $output = array();

      if ( count( $data ) > 1 ) {

        for ( $i = $start_position; $i <= $end_position; $i++ ) {
          if ( ! empty( $data[$i] ) ) {

            array_push( $output, array(
              'id' => $data[ $i ]['term_group'],
              'label' => $data[ $i ]['label'],
              'amount' => $group->get_number_of_terms( $taxonomy, $data[ $i ]['term_group'] )
            ) );
          }
        }
      }

      return $output;

    }


    /**
    * Outputs a table on a submenu page where you can add, delete, change tag groups, their labels and their order.
    */
    static function group_administration()
    {

      $tag_group_show_filter_tags = get_option( 'tag_group_show_filter_tags', 1 );

      $tag_group_show_filter = get_option( 'tag_group_show_filter', 1 );

      $taxonomy_link = '';

      $post_type_link = '';


      if ( $tag_group_show_filter_tags || $tag_group_show_filter ) {

        $post_type = preg_replace( '/tag-groups_(.+)/', '$1', sanitize_title( $_GET['page'] ) );

      }

      /**
      * Check if the tag filter is activated
      */
      if ( $tag_group_show_filter_tags ) {

        // get first of taxonomies that are associated with that $post_type
        $tg_taxonomies = get_option( 'tag_group_taxonomy', array('post_tag') );

        $taxonomy_names = get_object_taxonomies( $post_type );

        $taxonomies = array_intersect( $tg_taxonomies, $taxonomy_names );

        /**
        * Show the link to the taxonomy filter only if there is only one taxonomy for this post type (otherwise ambiguous where to link)
        */
        if ( ! empty( $taxonomies ) && count( $taxonomies ) == 1 ) {

          $taxonomy_link = reset( $taxonomies );

        }

      }


      /**
      * Check if the post filter is activated
      */
      if ( $tag_group_show_filter ) {

        $post_type_link = $post_type;

      }

      /**
      * In case we use the WPML plugin: consider the language
      */
      if ( defined( 'ICL_LANGUAGE_CODE' ) ) {

        $wpml_piece = '&lang=' . (string) ICL_LANGUAGE_CODE;

      } else {

        $wpml_piece = '';

      }


      $items_per_page = self::get_items_per_page();

      ?>

      <div class='wrap'>
        <h2><?php _e( 'Tag Groups', 'tag-groups' ) ?></h2>

        <p><?php
        _e( 'On this page you can define tag groups. Tags (or terms) can be assigned to these groups on the page where you edit the tags (terms).', 'tag-groups' ); ?></p>
        <p><?php _e( 'Change the order by drag and drop or with the up/down icons. Click into a labels for editing.', 'tag-groups' );
        ?></p>

        <div id="tg_message_container"></div>

        <table class="widefat tg_groups_table">
          <thead>
            <tr>
              <th style="min-width:30px;"><?php
              _e( 'Group ID', 'tag-groups' )
              ?></th>
              <th><?php
              _e( 'Label displayed on the frontend', 'tag-groups' )
              ?></th>
              <th><?php
              _e( 'Number of assigned tags', 'tag-groups' )
              ?></th>
              <th><?php
              _e( 'Action', 'tag-groups' )
              ?></th>
              <th><?php
              _e( 'Change sort order', 'tag-groups' )
              ?></th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th><?php
              _e( 'Group ID', 'tag-groups' )
              ?></th>
              <th><?php
              _e( 'Label displayed on the frontend', 'tag-groups' )
              ?></th>
              <th><?php
              _e( 'Number of assigned tags', 'tag-groups' )
              ?></th>
              <th><?php
              _e( 'Action', 'tag-groups' )
              ?></th>
              <th><?php
              _e( 'Change sort order', 'tag-groups' )
              ?></th>
            </tr>
          </tfoot>
          <tbody id="tg_groups_container">
            <tr>
              <td colspan="5" style="padding: 50px; text-align: center;">
                <img src="<?php echo admin_url('images/spinner.gif') ?>" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div id="tg_pager_container_adjuster">
        <div id="tg_pager_container"></div>
      </div>
      <input type="hidden" id="tg_nonce" value="">
      <input type="hidden" id="tg_start_position" value="1">

      <script>
      var labels = new Object();
      labels.edit = '<?php
      _e( 'Edit', 'tag-groups' )
      ?>';
      labels.create = '<?php
      _e( 'Create', 'tag-groups' )
      ?>';
      labels.newgroup = '<?php
      _e( 'new', 'tag-groups' )
      ?>';
      labels.placeholder_new = '<?php
      _e( 'label', 'tag-groups' )
      ?>';
      labels.tooltip_delete = '<?php
      _e( 'Delete this group.', 'tag-groups' )
      ?>';
      labels.tooltip_newbelow = '<?php
      _e( 'Create a new group below.', 'tag-groups' )
      ?>';
      labels.tooltip_move_up = '<?php
      _e( 'move up', 'tag-groups' )
      ?>';
      labels.tooltip_move_down = '<?php
      _e( 'move down', 'tag-groups' )
      ?>';
      labels.tooltip_reload = '<?php
      _e( 'reload', 'tag-groups' )
      ?>';
      labels.tooltip_showposts = '<?php
      _e( 'Show posts', 'tag-groups' )
      ?>';
      labels.tooltip_showtags = '<?php
      _e( 'Show tags', 'tag-groups' )
      ?>';

      var tg_params = {"ajaxurl": "<?php
        $protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
        echo admin_url( 'admin-ajax.php', $protocol );
        ?>", "postsurl": "<?php

        if ( ! empty( $post_type_link ) ) {

          echo admin_url( 'edit.php?post_type=' . $post_type_link . $wpml_piece, $protocol );

        }

        ?>", "tagsurl": "<?php

        if ( ! empty( $taxonomy_link ) ) {

          echo admin_url( 'edit-tags.php?taxonomy=' . $taxonomy_link . $wpml_piece, $protocol );

        }

        ?>", "items_per_page": "<?php echo $items_per_page ?>"};
        var data = {
          taxonomy: <?php echo json_encode( $taxonomies ) ?>
        };

        jQuery(document).ready(function () {
          data.task = "refresh";
          tg_do_ajax(tg_params, data, labels);

          jQuery(".tg_edit_label").live('click', function () {
            tg_close_all_textfields();
            var element = jQuery(this);
            var position = element.attr("data-position");
            var label = escape_html(element.attr("data-label"));
            element.replaceWith('<span class="tg_edit_label_active"><input data-position="' + position + '" data-label="' + label + '" value="' + label + '"> <span class="tg_edit_label_yes dashicons dashicons-yes tg_pointer" ></span> <span class="tg_edit_label_no dashicons dashicons-no-alt tg_pointer"></span></span>');
          });

          jQuery(".tg_edit_label_active").live('keypress', function (e) {
            if (e.keyCode == 13) {
              var input = jQuery(this).children(":first");
              var data = {
                task: 'update',
                position: input.attr('data-position'),
                label: input.val(),
                taxonomy: <?php echo json_encode( $taxonomies ) ?>,
              };
              tg_do_ajax(tg_params, data, labels);
            }
          });

          jQuery(".tg_edit_label_yes").live('click', function () {
            var input = jQuery(this).parent().children(":first");
            var data = {
              task: 'update',
              position: input.attr('data-position'),
              label: input.val(),
              taxonomy: <?php echo json_encode( $taxonomies ) ?>,
            };
            tg_do_ajax(tg_params, data, labels);
          });

          jQuery(".tg_edit_label_no").live('click', function () {
            var input = jQuery(this).parent().children(":first");
            tg_close_textfield(jQuery(this).parent(), false);
          });

          jQuery("[id^='tg_new_']:visible").live('keypress', function (e) {
            if (e.keyCode == 13) {
              var input = jQuery(this).find("input");
              var data = {
                task: 'new',
                position: input.attr('data-position'),
                label: input.val(),
                taxonomy: <?php echo json_encode( $taxonomies ) ?>,
              };
              tg_do_ajax(tg_params, data, labels);
            }
          });

          jQuery(".tg_new_yes").live('click', function () {
            var input = jQuery(this).parent().children(":first");
            var data = {
              task: 'new',
              position: input.attr('data-position'),
              label: input.val(),
              taxonomy: <?php echo json_encode( $taxonomies ) ?>,
            };
            tg_do_ajax(tg_params, data, labels);
          });

          jQuery(".tg_delete").live('click', function () {
            var position = jQuery(this).attr("data-position");
            jQuery('.tg_sort_tr[data-position='+position+'] td').addClass('tg_ask_delete');
            var answer = confirm('<?php
            _e( 'Do you really want to delete this tag group?', 'tag-groups' )
            ?> ');
            if (answer) {
              var data = {
                task: 'delete',
                position: position,
                taxonomy: <?php echo json_encode( $taxonomies ) ?>,
              };
              tg_do_ajax(tg_params, data, labels);

            } else {
              jQuery('.tg_sort_tr[data-position='+position+'] td').removeClass('tg_ask_delete')
            }
          });

          jQuery(".tg_edit_label").live('mouseenter', function () {
            jQuery(this).children(".dashicons-edit").fadeIn();
          });

          jQuery(".tg_edit_label").live('mouseleave', function () {
            jQuery(this).children(".dashicons-edit").fadeOut();
          });

          jQuery(".tg_pager_button").live('click', function () {
            var page = jQuery(this).attr('data-page');
            jQuery("#tg_start_position").val((page - 1) * <?php echo $items_per_page ?> + 1);
            data.task = "refresh";
            tg_do_ajax(tg_params, data, labels);
          });

          jQuery(".tg_up").live('click', function () {
            data.position = jQuery(this).attr('data-position');
            data.task = "up";
            tg_do_ajax(tg_params, data, labels);
          });

          jQuery(".tg_down").live('click', function () {
            data.position = jQuery(this).attr('data-position');
            data.task = "down";
            tg_do_ajax(tg_params, data, labels);
          });

          var element, start_pos, end_pos;
          jQuery("#tg_groups_container").sortable({
            start: function (event, ui) {
              element = Number(ui.item.attr("data-position"));
              start_pos = ui.item.index(".tg_sort_tr") + 1;
            },
            update: function (event, ui) {
              end_pos = ui.item.index(".tg_sort_tr") + 1;
              data.position = element;
              data.task = "move";
              data.new_position = element + end_pos - start_pos;
              tg_do_ajax(tg_params, data, labels);
            }
          });
          jQuery("#tg_groups_container").disableSelection();

          jQuery("#tg_groups_reload").live('click', function () {
            data.task = "refresh";
            tg_do_ajax(tg_params, data, labels);
          });
        });
        </script>
        <?php if ( current_user_can( 'manage_options' ) ) :
          $settings_url = admin_url( 'admin.php?page=tag-groups-settings' );
          ?>
          <p><a href="<?php echo $settings_url ?>" class="dashicons-before dashicons-admin-settings tg_no_underline">&nbsp;<?php
          _e( 'Go to the settings.', 'tag-groups' )
          ?></a></p>
        <?php endif;

      }


      /**
      * Good idea to purge the cache after changing theme options - else your visitors won't see the change for a while. Currently implemented for W3T Total Cache and WP Super Cache.
      */
      static function clear_cache()
      {

        if ( function_exists( 'flush_pgcache' ) ) {
          flush_pgcache;
        }

        if ( function_exists( 'flush_minify' ) ) {
          flush_minify;
        }

        if ( function_exists( 'wp_cache_clear_cache' ) ) {
          wp_cache_clear_cache();
        }

      }


      /**
      * Makes sure that WPML knows about the tag group label that can have different language versions.
      *
      * @deprecated
      *
      * @param string $name
      * @param string $value
      */
      static function register_string_wpml( $name, $value )
      {

        if ( function_exists( 'icl_register_string' ) ) {

          icl_register_string( 'tag-groups', $name, $value );

        }

      }


      /**
      * Asks WPML to forget about $name
      *
      * @deprecated
      *
      * @param string $name
      */
      static function unregister_string_wpml( $name )
      {

        if ( function_exists( 'icl_unregister_string' ) ) {

          icl_unregister_string( 'tag-groups', $name );

        }

      }


      /**
      *
      * Modifies the query to retrieve tags for filtering in the backend.
      *
      * @param array $pieces
      * @param array $taxonomies
      * @param array $args
      * @return array
      */
      static function terms_clauses( $pieces, $taxonomies, $args )
      {
        $taxonomy = TagGroups_Base::get_first_element( $taxonomies );

        if ( empty( $taxonomy ) || is_array( $taxonomy ) ) {

          $taxonomy = 'post_tag';

        }

        $show_filter_tags = get_option( 'tag_group_show_filter_tags', 1 );

        if ( $show_filter_tags ) {

          $tag_group_tags_filter = get_option( 'tag_group_tags_filter', array() );

          if ( isset( $tag_group_tags_filter[ $taxonomy ] ) ) {

            $group_id = $tag_group_tags_filter[ $taxonomy ];

          } else {

            $group_id = -1;

          }


          // check if group exists (could be deleted since last time the filter was set)
          $group_o = new TagGroups_Group();

          if ( $group_id > $group_o->get_max_term_group() ) {

            $group_id = -1;

          }


          if ( $group_id > -1 ) {

            if ( ! class_exists('TagGroups_Premium_Group') ) {

              if ( ! empty( $pieces['where'] ) ) {

                $pieces['where'] .= sprintf( " AND t.term_group = %d ", $group_id );

              } else {

                $pieces['where'] = sprintf( "t.term_group = %d ", $group_id );

              }

            } else {

              $mq_sql = TagGroups_Premium_Group::terms_clauses( $group_id );

              if ( ! empty( $pieces['join'] ) ) {

                $pieces['join'] .= $mq_sql['join'];

              } else {

                $pieces['join'] = $mq_sql['join'];

              }

              if ( ! empty( $pieces['where'] ) ) {

                $pieces['where'] .= $mq_sql['where'];

              } else {

                $pieces['where'] = $mq_sql['where'];

              }

            }
          }
        }

        return $pieces;

      }


      /**
      * Adds css to backend
      */
      static function add_admin_js_css( $where )
      {

        if ( strpos( $where, 'tag-groups-settings' ) !== false ) {

          wp_enqueue_script( 'jquery' );

          wp_enqueue_script( 'jquery-ui-core' );

          wp_enqueue_script( 'jquery-ui-accordion' );

          wp_register_style( 'tag-groups-css-backend-tgb', TAG_GROUPS_PLUGIN_URL .  '/css/admin-style.css', array(), TAG_GROUPS_VERSION );

          wp_enqueue_style( 'tag-groups-css-backend-tgb' );

          wp_register_style( 'tag-groups-css-backend-structure', TAG_GROUPS_PLUGIN_URL . '/css/jquery-ui.structure.min.css', array(), TAG_GROUPS_VERSION );

          wp_enqueue_style( 'tag-groups-css-backend-structure' );

          wp_register_script( 'sumoselect-js', TAG_GROUPS_PLUGIN_URL . '/js/jquery.sumoselect.min.js', array(), TAG_GROUPS_VERSION );

          wp_enqueue_script( 'sumoselect-js' );

          wp_register_style( 'sumoselect-css', TAG_GROUPS_PLUGIN_URL .  '/css/sumoselect.css', array(), TAG_GROUPS_VERSION );

          wp_enqueue_style( 'sumoselect-css' );


        } elseif ( strpos( $where, '_page_tag-groups' ) !== false ) {

          wp_register_style( 'tag-groups-css-backend-tgb', TAG_GROUPS_PLUGIN_URL .  '/css/admin-style.css', array(), TAG_GROUPS_VERSION );

          wp_enqueue_style( 'tag-groups-css-backend-tgb' );

          if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

            wp_register_script( 'tag-groups-js-backend', TAG_GROUPS_PLUGIN_URL . '/js/taggroups.js', array(), TAG_GROUPS_VERSION );

          } else {

            wp_register_script( 'tag-groups-js-backend', TAG_GROUPS_PLUGIN_URL . '/js/taggroups.min.js', array(), TAG_GROUPS_VERSION );

          }

          wp_enqueue_script( 'tag-groups-js-backend' );

          wp_enqueue_script( 'jquery-ui-sortable' );

        } elseif ( strpos( $where, 'edit-tags.php' ) !== false || strpos( $where, 'term.php' ) !== false  || strpos( $where, 'edit.php' ) !== false ) {

          wp_register_script( 'sumoselect-js', TAG_GROUPS_PLUGIN_URL . '/js/jquery.sumoselect.min.js', array(), TAG_GROUPS_VERSION );

          wp_enqueue_script( 'sumoselect-js' );

          wp_register_style( 'sumoselect-css', TAG_GROUPS_PLUGIN_URL .  '/css/sumoselect.css', array(), TAG_GROUPS_VERSION );

          wp_enqueue_style( 'sumoselect-css' );

          wp_register_style( 'tag-groups-css-backend-tgb', TAG_GROUPS_PLUGIN_URL .  '/css/admin-style.css', array(), TAG_GROUPS_VERSION );

          wp_enqueue_style( 'tag-groups-css-backend-tgb' );

        } elseif ( strpos( $where, 'post-new.php' ) !== false || strpos( $where, 'post.php' ) !== false ) {

          wp_register_style( 'react-select-css', TAG_GROUPS_PLUGIN_URL .  '/css/react-select.css', array(), TAG_GROUPS_VERSION );

          wp_enqueue_style( 'react-select-css' );

        }

      }


      /**
      * Adds Settings link to plugin list
      *
      * @param array $links
      * @return array
      */
      static function add_plugin_settings_link( $links )
      {

        $settings_link = '<a href="' . admin_url( 'admin.php?page=tag-groups-settings' ) . '">' . __( 'Settings', 'tag-groups' ) . '</a>';

        array_unshift( $links, $settings_link );


        if ( ! class_exists('TagGroups_Premium') ) {

          $settings_link = '<a href="https://chattymango.com/tag-groups-premium/?pk_campaign=tg&pk_kwd=settings_link" target="_blank"><span style="color:#3A0;">' . __( 'Upgrade to Premium', 'tag-groups' ) . '</span></a>';

          array_unshift( $links, $settings_link );

        }

        return $links;

      }


      /**
      * Returns the items per page on the tag groups screen
      *
      *
      * @param void
      * @return int
      */
      public static function get_items_per_page()
      {

        if ( class_exists( 'TagGroups_Premium_Admin' ) && method_exists( 'TagGroups_Premium_Admin', 'add_screen_option' ) ) {

          $items_per_page_all_users = get_option( 'tag_groups_per_page', array() );

          $user = get_current_user_id();

          if ( isset( $items_per_page_all_users[ $user ] ) ) {

            $items_per_page = intval( $items_per_page_all_users[ $user ] );

          }


          if ( ! isset( $items_per_page_all_users[ $user ] ) || $items_per_page < 1 ) {

            $items_per_page = TAG_GROUPS_ITEMS_PER_PAGE;

          }

        } else {

          $items_per_page = TAG_GROUPS_ITEMS_PER_PAGE;

        }

        return $items_per_page;

      }

    } // class

  }
