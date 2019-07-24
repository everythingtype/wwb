<?php
/**
* @package     Tag Groups
* @author      Christoph Amthor
* @copyright   2018 Christoph Amthor (@ Chatty Mango, chattymango.com)
* @license     GPL-3.0+
*/

if ( ! class_exists('TagGroups_Settings') ) {

  /**
  *
  */
  class TagGroups_Settings {


    function __construct() {

    }

    /**
    * renders the top of all setting pages
    *
    * @param void
    * @return void
    */
    public static function add_header()
    {

      echo '<div class="wrap">';

      $admin_page_title = get_admin_page_title();

      if ( ! empty( $admin_page_title ) ) {

        echo '<h2>' . get_admin_page_title() . '</h2>';

      }

    }


    /**
    * renders the bottom of all settings pages
    *
    * @param void
    * @return void
    */
    public static function add_footer()
    {

      echo '
      </div>
      <script>
      jQuery(document).ready(function(){
        jQuery(".chatty-mango-help-icon").click(function(){
          var topic = jQuery(this).attr("data-topic");
          jQuery(".chatty-mango-help-container-"+topic).slideToggle();
        });
      });
      </script>
      ';

    }


    /**
    * gets the slug of the currently selected tab
    *
    * @param string $default
    * @return string
    */
    public static function get_active_tab( $tabs )
    {

      if ( isset( $_GET['active-tab'] ) ) {

        return sanitize_title( $_GET['active-tab'] );

      } else {

        $keys = array_keys( $tabs );

        return reset( $keys );

      }

    }


    /**
    * gets the HTML of the header of tabbed view
    *
    * @param string $default
    * @return string
    */
    public static function get_tabs_html( $page, $tabs, $active_tab )
    {

      $html = '<h2 class="nav-tab-wrapper">';

      foreach ( $tabs as $slug => $label ) {

        $settings_url = add_query_arg( array( 'active-tab' => $slug ), menu_page_url( $page, false ) );

        if ( count( $tabs ) < 2 ) {

          if ( ! empty( $label ) ) {

            return '<h2>' . $label . '</h2>';

          } else {

            return '';

          }

        }

        $html .= '<a href="' . esc_url( $settings_url ) . '" class="nav-tab ';

        if ( $slug == $active_tab) {

          $html .= 'nav-tab-active';

        }

        $html .= '">' . $label .'</a>';

      }

      $html .= '</h2>';

      return $html;

    }


    /**
    * renders a settings page: home
    *
    * @param void
    * @return void
    */
    public static function settings_page_home()
    {

      $tag_group_taxonomy = TagGroups_Taxonomy::get_enabled_taxonomies(); //get_option( 'tag_group_taxonomy', array('post_tag') );

      self::add_header();

      $html = '';

      $tg_group = new TagGroups_Group;

      $group_count = $tg_group->get_number_of_term_groups();

      $tag_group_base_first_activation_time = get_option( 'tag_group_base_first_activation_time', 0 );

      $tag_group_premium_first_activation_time = get_option( 'tag_group_base_first_activation_time', 0 );

      $absolute_first_activation_time = ( $tag_group_base_first_activation_time < $tag_group_premium_first_activation_time ) ? $tag_group_base_first_activation_time : $tag_group_premium_first_activation_time;

      $html .= self::get_setting_help();


      $alerts = array();

      if ( time() - $absolute_first_activation_time < 60*60*24*7 || $group_count < 2 ) {

        $alerts[] = sprintf( __( 'See the <a %s>First Steps</a> for some basic instructions on how to get started.', 'tag-groups' ), 'href="' . menu_page_url( 'tag-groups-settings-first-steps', false ) . '"' );

      }


      if ( defined( 'ICL_LANGUAGE_CODE' ) ) {

        $alerts[] = __( 'We detected WPML. Your tag group names are translatable.', 'tag-groups' );

      }

      $alerts = apply_filters( 'tag_groups_settings_alerts', $alerts );

      if ( ! empty( $alerts ) ) {

        $html .= '<div style="background-color:#FFF; margin:10px 0 20px; padding:5px 0; float:left; width:100%;">
        <ul style="list-style-type:disc; margin-left:25px;">
        <li>' .

        implode( '</li><li>', $alerts ) .

        '</li>
        </ul>
        </div>';

      }

      $html .= '<div style="float:left;">
      <h2>' . __( 'Active Taxonomies', 'tag-groups' ) . '</h2>
      <table class="widefat fixed striped">';

      foreach ( $tag_group_taxonomy as $taxonomy ) {

        $link_to_group_admin = '<span class="dashicons dashicons-arrow-right-alt tg_no_underline"></span>&nbsp;<a href="' . TagGroups_Taxonomy::get_tag_group_admin_url( $taxonomy ) . '">' . __( 'go to tag groups page', 'tag-groups' ) . '</a>';

        /**
        * We try to avoid excessive loading times on this page
        */
        $term_count = get_terms( array(
          'hide_empty'  => false,
          'taxonomy'  => $taxonomy,
          'fields' => 'count'
        ) );

        if ( is_object( $term_count ) ) {

          continue;

        }

        $html .= '<tr><td><div class="tg_admin_accordion"><h4>' . TagGroups_Taxonomy::get_name_from_slug( $taxonomy ) . ' ('. $taxonomy . ')</h4>
        <div style="display:none;">';


        if ( $group_count < 100 && $term_count < 10000 ) {

          $html .= '<h4>' . __( 'Group Statistics', 'tag-groups' );

          if ( defined( 'ICL_LANGUAGE_CODE' ) ) {

            $html .= ' (' . __( 'for the selected language', 'tag-groups' ) . ')';

          }

          $html .= '</h4>';

          $html .= TagGroups_Shortcode_Info::tag_groups_info( array( 'taxonomy' => $taxonomy, 'group_id' => 'all', 'html_class' => 'widefat fixed striped' ) );

        } else {

          $html .= sprintf( 'This taxonomy has %d tags in %d groups.', $term_count, $group_count );

        }

        $html .= '</div></td><td style="max-width:300px;">' . $link_to_group_admin . '</td></tr>';

      }

      $html .= '</table>';

      $html .= '</div>';

      $html .= '
      <!-- begin Tag Groups plugin -->
      <script>
      jQuery(document).ready(function() {
        var icons = {
          header: "dashicons dashicons-arrow-right",
          activeHeader: "dashicons dashicons-arrow-down"
        };
        jQuery( ".tg_admin_accordion" ).accordion({
          icons:icons,
          collapsible: true,
          active: false,
          heightStyle: "content"
        });
      });
      </script>
      <!-- end Tag Groups plugin -->
      ';

      echo $html;

      self::add_footer();

    }


    /**
    * renders a settings page: taxonomies
    *
    * @param void
    * @return void
    */
    public static function settings_page_taxonomies()
    {

      $tag_group_taxonomy = get_option( 'tag_group_taxonomy', array('post_tag') );

      $public_taxonomies = TagGroups_Taxonomy::get_public_taxonomies();

      self::add_header();

      $html = '';


      $tabs = array();

      $tabs['taxonomies'] = '';

      $tabs = apply_filters( 'tag_groups_settings_taxonomies_tabs', $tabs );

      $active_tab = self::get_active_tab( $tabs );

      $html .= self::get_tabs_html( 'tag-groups-settings-taxonomies', $tabs, $active_tab );

      $html .= '<div class="tg_settings_tabs_content">';

      switch ( $active_tab ) {

        case 'taxonomies':

        $html .=
        '<p>' . __( 'Choose the taxonomies for which you want to use tag groups.', 'tag-groups' ) . '<span class="dashicons dashicons-editor-help chatty-mango-help-icon" data-topic="taxonomies"></span></p>' .

        '<div class="chatty-mango-help-container chatty-mango-help-container-taxonomies" style="display:none;">' .
        '<p>' . __( "The default texonomy is <b>Tags (post_tag)</b>. Please note that the tag clouds might not work with all taxonomies and that some taxonomies listed here may not be accessible in the admin backend. If you don't understand what is going on here, just leave the default.", 'tag-groups' ) . '</p>' .
        '<p>' . __( "<b>Please deselect taxonomies that you don't use. Using several taxonomies for the same post type or hierarchical taxonomies (like categories) is experimental and not supported.</b>", 'tag-groups' ) . '</p>' .
        '<p>' . __( 'To see the post type, hover your mouse over the option.', 'tag-groups' ) . '</p>' .
        '</div>' .
        '<div class="chatty-mango-settings-container">' .
        '<form method="POST" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">' .
        wp_nonce_field( 'tag-groups-taxonomy', 'tag-groups-taxonomy-nonce', true, false ) .
        '<ul>';

        $html .= '<p><input id="tg_advanced_options_checkbox" type="checkbox" value=1 autocomplete="off" />
        <label for="tg_advanced_options_checkbox">' . __( 'Show hierarchical taxonomies', 'tag-groups' ) . '</label></p>
        <script>
        jQuery(document).ready(function(){
          jQuery("#tg_advanced_options_checkbox").change(function(){
            if ( jQuery("#tg_advanced_options_checkbox").is(":checked") ) {
              jQuery(".tg_advanced_options_items").slideDown();
            } else {
              jQuery(".tg_advanced_options_items").slideUp();
            }
          });
        });
        </script>
        <p>&nbsp;</p>';

        foreach ( $public_taxonomies as $taxonomy ) {

          $post_types = TagGroups_Taxonomy::post_types_from_taxonomies( $taxonomy );

          $html .= '<li';


          if ( is_taxonomy_hierarchical( $taxonomy ) ) {

            $html .= ' class="tg_advanced_options_items" style="display:none;"';

          }

          $html .= '><input type="checkbox" name="taxonomies[]" id="' . $taxonomy . '" value="' . $taxonomy . '"';

          if ( in_array( $taxonomy, $tag_group_taxonomy ) ) {

            $html .= 'checked';

            $link_to_group_admin = '<a href="' . TagGroups_Taxonomy::get_tag_group_admin_url( $taxonomy ) . '" title="' . __( 'go to tag groups page', 'tag-groups' ) . '"><span class="dashicons dashicons-index-card tg_no_underline"></span></a>';

          } else {

            $link_to_group_admin = '<span class="dashicons dashicons-index-card tg_no_underline tg_faded"></span>';

          }

          $html .= '/>&nbsp;' . $link_to_group_admin . ' <label for="' . $taxonomy . '" class="tg_unhide_trigger">' . TagGroups_Taxonomy::get_name_from_slug( $taxonomy ) . ' ('. $taxonomy . ') <span style="display:none; color:#999;">(' . __( 'post type', 'tag-groups') . ': ' . implode( ', ', $post_types ) . ')</span></label></li>';

        }

        $html .= '</ul>
        <script>
        jQuery(document).ready(function () {
          jQuery(".tg_unhide_trigger").mouseover(function () {
            jQuery(this).find("span").show();
          });
          jQuery(".tg_unhide_trigger").mouseout(function () {
            jQuery(this).find("span").hide();
          });
        });
        </script>
        <input type="hidden" name="tg_action" value="taxonomy">
        <input class="button-primary" type="submit" name="Save" value="' .
        __( 'Save Taxonomies', 'tag-groups' ) . '" id="submitbutton" />
        </form>
        </div>';

        break;

        default:

        if ( class_exists( 'TagGroups_Premium_Settings' ) ) {

          $html .= TagGroups_Premium_Settings::get_content( $active_tab );

        }

        break;

      }

      $html .= '</div>';

      echo $html;

      self::add_footer();

    }


    /**
    * renders a settings page: back end
    *
    * @param void
    * @return void
    */
    public static function settings_page_back_end()
    {

      $show_filter_posts = get_option( 'tag_group_show_filter', 1 );

      $show_filter_tags = get_option( 'tag_group_show_filter_tags', 1 );


      self::add_header();

      $html = '';

      $tabs = array();

      $tabs['filters'] = __( 'Filters', 'tag-groups' );

      $tabs = apply_filters( 'tag_groups_settings_back_end_tabs', $tabs );

      $active_tab = self::get_active_tab( $tabs );

      $html .= self::get_tabs_html( 'tag-groups-settings-back-end', $tabs, $active_tab );

      $html .= '<div class="tg_settings_tabs_content">';

      switch ( $active_tab ) {

        case 'filters':

        $html .= '<form method="POST" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">' .
        wp_nonce_field( 'tag-groups-backend', 'tag-groups-backend-nonce', true, false ) .

        '<p><input type="checkbox" id="tg_filter_posts" name="filter_posts" value="1"';

        if ( $show_filter_posts ) {

          $html .= ' checked';

        }

        $html .= '/>&nbsp;<label for="tg_filter_posts">' .
        __( 'Display filter on post admin', 'tag-groups' ) .
        '</label><span class="dashicons dashicons-editor-help chatty-mango-help-icon" data-topic="filter-posts"></span></p>' .

        '<div class="chatty-mango-help-container chatty-mango-help-container-filter-posts" style="display:none;">' .
        __( 'Add a pull-down menu to the filters above the list of posts. If you filter posts by tag groups, then only items will be shown that have tags (terms) in that particular group. This feature can be turned off so that the menu won\'t obstruct your screen if you use a high number of groups. May not work with all taxonomies.', 'tag-groups' ) .
        '</div>
        <p><input type="checkbox" id="tg_filter_tags" name="filter_tags" value="1"';

        if ( $show_filter_tags ) {

          $html .= ' checked';

        }

        $html .= '/>&nbsp;<label for="tg_filter_tags">' .
        __( 'Display filter on tag admin', 'tag-groups' ) .
        '</label><span class="dashicons dashicons-editor-help chatty-mango-help-icon" data-topic="filter-tags"></span></p>' .

        '<div class="chatty-mango-help-container chatty-mango-help-container-filter-tags" style="display:none;">' .
        __( 'Add a filter to the list of tags. Disable it here if it conflicts with other plugins or themes.', 'tag-groups' ) .
        '</div>
        <input type="hidden" name="tg_action" value="backend">
        <input class="button-primary" type="submit" name="Save" value="' .
        __( 'Save Settings', 'tag-groups' ) .
        '" id="submitbutton" />
        </form>';

        break; // filters

        default:

        if ( class_exists( 'TagGroups_Premium_Settings' ) ) {

          $html .= TagGroups_Premium_Settings::get_content( $active_tab );

        }

        break;

      }

      $html .= '</div>';

      echo $html;

      self::add_footer();

    }


    /**
    * renders a settings page: front end
    *
    * @param void
    * @return void
    */
    public static function settings_page_front_end()
    {


      $default_themes = explode( ',', TAG_GROUPS_BUILT_IN_THEMES );

      $tag_group_theme = get_option( 'tag_group_theme', TAG_GROUPS_STANDARD_THEME );

      $tag_group_mouseover = get_option( 'tag_group_mouseover', '' );

      $tag_group_collapsible = get_option( 'tag_group_collapsible', '' );

      $tag_group_enqueue_jquery = get_option( 'tag_group_enqueue_jquery', 1 );

      $tag_group_html_description = get_option( 'tag_group_html_description', 0 );

      $tag_group_shortcode_widget = get_option( 'tag_group_shortcode_widget' );

      $tag_group_shortcode_enqueue_always = get_option( 'tag_group_shortcode_enqueue_always', 1 );

      self::add_header();

      $html = '';

      $tabs = array();

      $tabs['shortcodes'] = __( 'Shortcodes', 'tag-groups' );

      $tabs['themes'] = __( 'Themes for Tabs and Accordion', 'tag-groups' );

      $tabs = apply_filters( 'tag_groups_settings_front_end_tabs', $tabs );

      $active_tab = self::get_active_tab( $tabs );

      $html .= self::get_tabs_html( 'tag-groups-settings-front-end', $tabs, $active_tab );

      $html .= '<div class="tg_settings_tabs_content">';

      switch ( $active_tab ) {

        case 'shortcodes':

        $html .= '<p>' . __( 'You can use a shortcode to embed the tag cloud directly in a post, page or widget or you call the function in the PHP code of your theme.', 'tag-groups' ) . ' ' . __( 'Several tag clouds are also available as blocks for the Gutenberg editor.', 'tag-groups' ) . '<p>';
        $html .= '<form method="POST" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">
        <input type="hidden" name="tag-groups-shortcode-nonce" id="tag-groups-shortcode-nonce" value="' . wp_create_nonce( 'tag-groups-shortcode' ). '" />
        <p><input type="checkbox" name="widget" id="tg_widget" value="1"';

        if ( $tag_group_shortcode_widget ) {

          $html .= 'checked';

        }

        $html .= '>&nbsp;<label for="tg_widget">' . __( 'Enable shortcode in sidebar widgets (if not visible anyway).', 'tag-groups' ) . '</label></p>
        <p><input type="checkbox" name="enqueue" id="tg_enqueue" value="1" ';

        if ( $tag_group_shortcode_enqueue_always ) {

          $html .= 'checked';

        }

        $html .= '>&nbsp;<label for="tg_enqueue">' . __( 'Always load shortcode scripts.', 'tag-groups' ) .
        '</label><span class="dashicons dashicons-editor-help chatty-mango-help-icon" data-topic="load-scripts"></span></p>' .

        '<div class="chatty-mango-help-container chatty-mango-help-container-load-scripts" style="display:none;">
        <p>' . __( 'Turn off to load the scripts only on posts and pages where a shortcode appears.', 'tag-groups' ) . '</p>
        <p><span class="dashicons dashicons-warning"></span>' . __( 'Turn on if you use these shortcodes in widgets or if you use Gutenberg blocks.', 'tag-groups' ) . '</p>
        </div>
        <input type="hidden" id="action" name="tg_action" value="shortcode">
        <input class="button-primary" type="submit" name="save" value="' . __( 'Save Settings', 'tag-groups' ) . '" id="submitbutton" />
        </form>';

        $html .= '<p>&nbsp;<p>';

        $html .= '<p>' . __('Click for more information.', 'tag-groups') . '</p>';

        $html .= '<h3>' . __('Shortcodes', 'tag-groups') . '</h3>
        <div class="tg_admin_accordion" >
        <h4>' . __( 'Tabbed Tag Cloud', 'tag-groups' ) . '</h4>';

        $html .= '<div>
        <h4>[tag_groups_cloud]</h4>
        <p>' . __( 'Display the tags in a tabbed tag cloud.', 'tag-groups' ) . '</p>
        <h4>' . __( 'Example', 'tag-groups' ) . '</h4>
        <p>[tag_groups_cloud smallest=9 largest=30 include=1,2,10]<p>';
        $html .= '<h4>' . __( 'Parameters', 'tag-groups' ) . '</h4>
        <p>' . sprintf( __( 'Please find the parameters in the <a %s>documentation</a>.', 'tag-groups' ), 'href="https://documentation.chattymango.com/documentation/tag-groups-premium/tabbed-tag-cloud/tabbed-tag-cloud-parameters/?pk_campaign=tg&pk_kwd=documentation" target="_blank"' ) . '</p>
        </div>';

        $html .= '<h4>' . __( 'Accordion', 'tag-groups' ) .'</h4>';
        $html .= '<div>
        <h4>[tag_groups_accordion]</h4>
        <p>' . __( 'Display the tags in an accordion.', 'tag-groups' ) . '</p>
        <h4>' . __( 'Example', 'tag-groups' ) . '</h4>
        <p>[tag_groups_accordion smallest=9 largest=30 include=1,2,10]</p>
        <h4>' . __( 'Parameters', 'tag-groups' ) . '</h4>
        <p>' . sprintf( __( 'Please find the parameters in the <a %s>documentation</a>.', 'tag-groups' ), 'href="https://documentation.chattymango.com/documentation/tag-groups-premium/accordion-tag-cloud/accordion-tag-cloud-parameters/?pk_campaign=tg&pk_kwd=documentation" target="_blank"' ) . '</p>
        </div>';

        /**
        * Let the premium plugin add own shortcode information.
        */
        $html = apply_filters( 'tag_groups_hook_shortcodes', $html );

        $html .= '<h4>' . __( 'Group Information', 'tag-groups' ) . '</h4>';
        $html .= '<div>
        <h4>[tag_groups_info]</h4>
        <p>' . __( 'Display information about tag groups.', 'tag-groups' ) . '</p>
        <h4>' . __( 'Example', 'tag-groups' ) . '</h4>
        <p>[tag_groups_info group_id="all"]</p>
        <h4>' . __( 'Parameters', 'tag-groups' ) . '</h4>
        <p>' . sprintf( __( 'Please find the parameters in the <a %s>documentation</a>.', 'tag-groups' ), 'href="https://documentation.chattymango.com/documentation/tag-groups-premium/tag-groups-info/tag-groups-info-parameters/?pk_campaign=tg&pk_kwd=documentation" target="_blank"' ) . '</p>
        </div>
        </div>';

        $html .= '<h3>PHP</h3>
        <div class="tg_admin_accordion">
        <h4>tag_groups_cloud()</h4>';
        $html .= '<div>
        <p>' . __( 'The function <b>tag_groups_cloud</b> accepts the same parameters as the [tag_groups_cloud] shortcode, except for those that determine tabs and styling.', 'tag-groups' ) . '</p>';
        $html .= '<p>' . __( 'By default it returns a string with the html for a tabbed tag cloud.', 'tag-groups' ) . '<p>
        <h4>' . __( 'Example', 'tag-groups' ) . '</h4>';

        $html .= '<p><code>' . htmlentities( "<?php if ( function_exists( 'tag_groups_cloud' ) ) echo tag_groups_cloud( array( 'include' => '1,2,5,6' ) ) . '" ) . '</code><p>';
        $html .= '<p>&nbsp;<p>';
        $html .= '<p>' . __( 'If the optional second parameter is set to \'true\', the function returns a multidimensional array containing tag groups and tags.', 'tag-groups' ) . '<p>';
        $html .= '<h4>' . __( 'Example', 'tag-groups' ) . '</h4>';
        $html .= '<p><code>' . htmlentities( "<?php if ( function_exists( 'tag_groups_cloud' ) ) print_r( tag_groups_cloud( array( 'orderby' => 'count', 'order' => 'DESC' ), true ) ) . '" ) . '</code><p>
        </div>
        </div>';

        $html .= '
        <!-- begin Tag Groups plugin -->
        <script>
        jQuery(function() {
          var icons = {
            header: "dashicons dashicons-arrow-right",
            activeHeader: "dashicons dashicons-arrow-down"
          };
          jQuery( ".tg_admin_accordion" ).accordion({
            icons:icons,
            collapsible: true,
            active: false,
            heightStyle: "content"
          });
        });
        </script>
        <!-- end Tag Groups plugin -->
        ';
        break;

        case 'themes':

        $html .= '<form method="POST" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">' .
        wp_nonce_field( 'tag-groups-settings', 'tag-groups-settings-nonce', true, false ) .
        '<p>' .
        __( "Here you can choose a theme for the tabbed and the accordion tag cloud. The path to own themes is relative to the <i>uploads</i> folder of your WordPress installation. Leave empty if you don't use any.", 'tag-groups' ) .
        '<span class="dashicons dashicons-editor-help chatty-mango-help-icon" data-topic="themes"></span>' .
        '</p>' .

        '<div class="chatty-mango-help-container chatty-mango-help-container-themes" style="display:none;">' .
        '<p>' .
        __( 'New themes can be created with the <a href="http://jqueryui.com/themeroller/" target="_blank">jQuery UI ThemeRoller</a>:', 'tag-groups' ) .
        '<ol>
        <li>' . __( 'On the page "Theme Roller" you can customize all features or pick one set from the gallery. Finish with the "download" button.', 'tag-groups' ) . '</li>
        <li>' . __( 'On the next page ("Download Builder") you will need to select the version 1.12.x and the components "Core", "Widget", "Accordion" and "Tabs". Make sure that before downloading you enter at the bottom as "CSS Scope" <b>.tag-groups-cloud</b> (including the dot).', 'tag-groups' ) . '</li>
        <li>' . __( 'Then you unpack the downloaded zip file. You will need the "images" folder and the "jquery-ui.theme.min.css" file.', 'tag-groups' ) . '</li>
        <li>' . __( 'Create a new folder inside your <i>wp-content/uploads</i> folder (for example "my-theme") and copy there these two items.', 'tag-groups' ) . '</li>
        <li>' . __( 'Enter the name of this new folder (for example "my-theme") below.', 'tag-groups' ) .
        '</li>
        </ol>
        </p>
        </div>
        <div class="chatty-mango-settings-container">
        <div style="width:50%;min-width:500px;float:left">
        <ul>';

        foreach ( $default_themes as $theme ) {

          $html .= '<li><input type="radio" name="theme" id="tg_' . $theme . '" value="' . $theme . '"';

          if ( $tag_group_theme == $theme ) {

            $html .= ' checked';
          }

          $html .= '/>&nbsp;<label for="tg_' . $theme . '">' . $theme . '</label></li>';

        }

        $html .= '<li><input type="radio" name="theme" value="own" id="tg_own"';

        if ( !in_array( $tag_group_theme, $default_themes ) ) {

          $html .= ' checked';

        }

        $html .= '/>&nbsp;<label for="tg_own">own: /wp-content/uploads/</label><input type="text" id="theme-name" name="theme-name" value="';

        if ( !in_array( $tag_group_theme, $default_themes ) ) {

          $html .= $tag_group_theme;

        }

        $html .= '" /></li>
        <li><input type="checkbox" name="enqueue-jquery" id="tg_enqueue-jquery" value="1"';

        if ( $tag_group_enqueue_jquery ) {

          $html .= ' checked';

        }

        $html .= '/>&nbsp;<label for="tg_enqueue-jquery">' .
        __( 'Use jQuery.  (Default is on. Other plugins might override this setting.)', 'tag-groups' ) ;
        $html .= '</label></li>
        </div>
        <div style="width:50%;min-width:500px;float:left">
        <h4>' . __( 'Further options', 'tag-groups' ) . '</h4>
        <ul>
        <li><input type="checkbox" name="mouseover" id="mouseover" value="1"';

        if ( $tag_group_mouseover ) {

          $html .= ' checked';

        }

        $html .= '>&nbsp;<label for="mouseover">' . __( 'Tabs triggered by hovering mouse pointer (without clicking).', 'tag-groups' ) .
        '</label></li>
        <li><input type="checkbox" name="collapsible" id="collapsible" value="1"';

        if ( $tag_group_collapsible ) {

          $html .= ' checked';

        }

        $html .= '>&nbsp;<label for="collapsible">' . __( 'Collapsible tabs (toggle open/close).', 'tag-groups' ) . '</label></li>
        <li><input type="checkbox" name="html_description" id="html_description" value="1" ';

        if ( $tag_group_html_description ) {

          $html .= 'checked';

        }

        $html .= '>&nbsp;<label for="html_description">' . __( 'Allow HTML in tag description.', 'tag-groups' ) . '</label></li>
        </ul>
        </div>
        <div style="width:100%;min-width:500px;float:left;padding:20px 0;">
        <input type="hidden" id="action" name="tg_action" value="theme">
        <input class="button-primary" type="submit" name="save" value="' .
        __( "Save Theme Options", "tag-groups" ) .
        '" id="submitbutton" />
        </div>
        </div>
        </form>';

        break;

        default:

        if ( class_exists( 'TagGroups_Premium_Settings' ) ) {

          $html .= TagGroups_Premium_Settings::get_content( $active_tab );

        }

        break;

      }

      $html .= '</div>';

      echo $html;

      self::add_footer();

    }


    /**
    * renders a settings page: tools
    *
    * @param void
    * @return void
    */
    public static function settings_page_tools()
    {

      $tag_group_reset_when_uninstall = get_option( 'tag_group_reset_when_uninstall', 0 );

      self::add_header();

      $html = '';

      $tabs = array();

      $tabs['export_import'] = __( 'Export/Import', 'tag-groups' );

      $tabs['reset'] = __( 'Reset', 'tag-groups' );

      $tabs = apply_filters( 'tag_groups_settings_tools_tabs', $tabs );

      $active_tab = self::get_active_tab( $tabs );

      $html .= self::get_tabs_html( 'tag-groups-settings-tools', $tabs, $active_tab );

      $html .= '<div class="tg_settings_tabs_content">';

      switch ( $active_tab ) {

        case 'export_import':

        $html .= '<p>
        <form method="POST" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">
        <h3>' . __( 'Export', 'tag-groups' ) . '<span class="dashicons dashicons-editor-help chatty-mango-help-icon" data-topic="export"></span></h3>
        <div class="chatty-mango-help-container chatty-mango-help-container-export" style="display:none;">
        <input type="hidden" name="tag-groups-export-nonce" id="tag-groups-export-nonce" value="' . wp_create_nonce( 'tag-groups-export' ) . '" />
        <p>' . __( 'Use this button to export all Tag Groups settings and groups and all terms that are assigned to a group into files.', 'tag-groups' ) . '</p>
        <p>' . __( "You can import both files separately. Category hierarchy won't be saved. When you restore terms that were deleted, they receive new IDs and you must assign them to posts again. Exporting cannot substitute a backup.", 'tag-groups' ) . '</p>
        </div>
        <input type="hidden" id="action" name="tg_action" value="export">
        <p><input class="button-primary" type="submit" name="export" value="'. __( 'Export Files', 'tag-groups' ) . '" id="submitbutton" /></p>
        </form>
        </p>';
        $html .= '<p>&nbsp;</p>';
        $html .= '<p>
        <form method="POST" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" enctype="multipart/form-data">
        <h3>' . __( 'Import', 'tag-groups' ) . '<span class="dashicons dashicons-editor-help chatty-mango-help-icon" data-topic="import"></span></h3>
        <div class="chatty-mango-help-container chatty-mango-help-container-import" style="display:none;">
        <input type="hidden" name="tag-groups-import-nonce" id="tag-groups-import-nonce" value="' . wp_create_nonce( 'tag-groups-import' ) . '" />
        <p>' . __( 'Below you can import previously exported settings/groups or terms from a file.', 'tag-groups' ) . '</p>
        <p>' . __( 'It is recommended to back up the database of your site before proceeding.', 'tag-groups' ) . '</p>
        </div>
        <input type="hidden" id="action" name="tg_action" value="import">
        <p><input type="file" id="settings_file" name="settings_file"></p>
        <p><input class="button-primary" type="submit" name="import" value="' . __( 'Import File', 'tag-groups' ) . '" id="submitbutton" /></p>
        </form>
        </p>';

        break;

        case 'reset':

        $html .= '<form method="POST" action="' . esc_url( $_SERVER['REQUEST_URI'] ). '">' .
        wp_nonce_field( 'tag-groups-reset', 'tag-groups-reset-nonce', true, false ) .
        '<p>' .
        __( 'Use this button to delete all tag groups and assignments. Your tags will not be changed. Check the checkbox to confirm.', 'tag-groups' ) .
        '<span class="dashicons dashicons-editor-help chatty-mango-help-icon" data-topic="delete"></span></p>
        <div class="chatty-mango-help-container chatty-mango-help-container-delete" style="display:none;">' .
        __( 'Please keep in mind that the tag assignments cannot be recovered by the export/import function.', 'tag-groups' ) .
        '</div>
        <input type="checkbox" id="ok" name="ok" value="yes" />
        <label>' .
        __( 'I know what I am doing.', 'tag-groups' ) .
        '</label>
        <input type="hidden" id="action" name="tg_action" value="reset">
        <p><input class="button-primary" type="submit" name="delete" value="' .
        __( "Delete Groups", "tag-groups" ) . '" id="submitbutton" /></p>
        </form>
        <p>&nbsp;</p>
        <h2>' .
        __( 'Delete Settings and Groups', 'tag-groups' ) .
        '</h2>
        <form method="POST" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">
        <p>' .
        wp_nonce_field( 'tag-groups-uninstall', 'tag-groups-uninstall-nonce', true, false ) .
        '<input type="checkbox" id="ok" name="ok" value="yes"';

        if ( $tag_group_reset_when_uninstall ) {

          $html .= ' checked';

        }

        $html .= '/>
        <label>' .
        __( "Delete all groups and settings when uninstalling the plugin.", "tag-groups" ) .
        '</label>
        <input type="hidden" id="action" name="tg_action" value="uninstall">
        </p>
        <input class="button-primary" type="submit" name="save" value="' .
        __( "Save Settings", "tag-groups" ) . '" id="submitbutton" />
        </form>';

        break;

        default:

        if ( class_exists( 'TagGroups_Premium_Settings' ) ) {

          $html .= TagGroups_Premium_Settings::get_content( $active_tab );

        }

        break;

      }

      $html .= '</div>';

      echo $html;

      self::add_footer();

    }


    /**
    * renders a settings page: troubleshooting
    *
    * @param void
    * @return void
    */
    public static function settings_page_troubleshooting()
    {

      self::add_header();

      $html = '';

      $tabs = array();

      $tabs['faq'] = __( 'FAQ and Common Issues', 'tag-groups' );

      $tabs['documentation'] = __( 'Documentation', 'tag-groups' );

      $tabs['support'] = __( 'Get Support', 'tag-groups' );

      $tabs['system'] = __( 'System Information', 'tag-groups' );

      $tabs = apply_filters( 'tag_groups_settings_troubleshooting_tabs', $tabs );

      $active_tab = self::get_active_tab( $tabs );

      $html .= self::get_tabs_html( 'tag-groups-settings-troubleshooting', $tabs, $active_tab );

      $html .= '<div class="tg_settings_tabs_content">';

      switch ( $active_tab ) {

        case 'faq':

        $html .= '<div class="chatty-mango-settings-columns">';

        $html .= '<div><h3>' . __( 'How can I use a tag cloud in a widget?', 'tag-groups' ) . '</h3>';

        $html .= '<p>' . __( 'Please insert the shortcode into a text widget.', 'tag-groups' ) . '</p></div>';


        $html .= '<div><h3>' . __( 'There is a gray box around the tag cloud or shortcode output', 'tag-groups' ) . '</h3>';

        $html .= '<p>' . __( 'Please check your shortcode in the editor and make sure that it is formatted as “Paragraph”, not “Preformatted”.', 'tag-groups' ) . '</p></div>';


        $html .= '<div><h3>' . __( 'One or more shortcode parameters are not effective', 'tag-groups' ) . '</h3>';

        $html .= '<p>' . __( 'Please check your shortcode in the editor and make sure that quotes are not formatted, i.e. not tilted or curled (re-type all quotes) and that there is no invisible HTML code inside the shortcode.', 'tag-groups' ) . '</p></div>';


        $html .= '<div><h3>' . __( "The list on the Tag Groups page doesn't load. I only see the wheel spinning forever", 'tag-groups' ) . '</h3>';

        $html .= '<p>' . sprintf( __( 'This usually means that somewhere your site outputs a warning or alert that interferes with the data transfer to your browser. In most cases it is caused by another plugin. Please try to find out the reason <a %s>according to these instructions</a>.', 'tag-groups' ), 'href="https://documentation.chattymango.com/documentation/tag-groups/troubleshooting/the-list-on-the-tag-groups-page-doesnt-load-i-only-see-the-wheel-spinning-forever/?pk_campaign=tg&pk_kwd=dashboard" target="_blank"' ). '</p></div>';


        $html .= '<div><h3>' . __( 'More FAQ', 'tag-groups' ) . '</h3>';

        $html .= '<p>' . sprintf( __( 'Please continue <a %s>here</a>.', 'tag-groups' ), 'href="https://documentation.chattymango.com/documentation/tag-groups/faq-and-troubleshooting-tag-groups/?pk_campaign=tg&pk_kwd=dashboard" target="_blank"') . '</p></div>';

        $html .= '</div>';

        break;

        case 'documentation':

        $html .= '<div class="chatty-mango-settings-columns">';

        $html .= '<div><h3><span class="dashicons dashicons-clipboard"></span> ' . sprintf( __( '<a %s>Working with tag groups</a>', 'tag-groups' ), 'href="https://documentation.chattymango.com/documentation/tag-groups/working-with-tag-groups/?pk_campaign=tg&pk_kwd=dashboard" target="_blank"' ) . '</h3></div>';

        $html .= '<div><h3><span class="dashicons dashicons-clipboard"></span> ' . sprintf( __( '<a %s>Shortcodes</a>', 'tag-groups' ), 'href="https://documentation.chattymango.com/documentation/tag-groups/tag-clouds-and-groups-info/shortcodes/?pk_campaign=tg&pk_kwd=dashboard" target="_blank"' ) . '</h3></div>';

        $html .= '<div><h3><span class="dashicons dashicons-clipboard"></span> ' . sprintf( __( '<a %s>How to use tag groups with Gutenberg</a>', 'tag-groups' ), 'href="https://documentation.chattymango.com/documentation/tag-groups/tag-clouds-and-groups-info/using-gutenberg/?pk_campaign=tg&pk_kwd=dashboard" target="_blank"' ) . '</h3></div>';

        $html .= '<div><h3><span class="dashicons dashicons-clipboard"></span> ' . sprintf( __( '<a %s>All topics</a>', 'tag-groups' ), 'href="https://documentation.chattymango.com/documentation/tag-groups/?pk_campaign=tg&pk_kwd=dashboard" target="_blank"' ) . '</h3></div>';

        $html .= '</div>';

        break;

        case 'support':

        $html .= '<p>' . sprintf( __( 'If you need help or want to report a bug or suggest a new feature, you can do so on the <a %s>official support forum at WordPress.org</a>.', 'tag-groups' ), 'href="https://wordpress.org/support/plugin/tag-groups#new-post" target="_blank"' ) .
          '</p>' .
          '<p>' . __( 'Please make sure that you have the latest version of this plugin.' ) . '</p>' .
          '<p>' . __( 'If you want to report a bug, please try first to reproduce the error with all other plugins disabled and with a default theme so that we know if there is any interference by another plugin or theme.' ) . '</p>'.
          '<p>' . __( 'Thank you for your cooperation!' ) . '</p>';

          break;

          case 'system':

          $html .= '<div class="tg_admin_accordion" >
          <h3>' . __( 'Server', 'tag-groups-premium' ) . '</h3>';

          $html .= '<table class="widefat fixed">';

          $phpversion = phpversion();

          $php_upgrade_text = '';

          if ( version_compare( $phpversion, '7.0.0', '<' ) ) {

            $php_upgrade_text = sprintf( ' <a href="%s" target="_blank">%s</a>', 'https://wordpress.org/support/upgrade-php/', '<span class="dashicons dashicons-warning"></span>' );

          }

          $html .= '<tr><td>PHP Version</td><td>' . $phpversion . $php_upgrade_text . '</td></tr>';

          $html .= '<tr><td>PHP Memory Limit</td><td>' . ini_get('memory_limit') . '</td></tr>';

          $html .= '</table></div>';

          $html .= '<div class="tg_admin_accordion" >
          <h3>WordPress</h3>';

          $html .= '<table class="widefat fixed">';

          $html .= '<tr><td>WordPress Version</td><td>' . get_bloginfo('version') . '</td></tr>';
          $html .= '<tr><td>Site URL</td><td>' . site_url() . '</td></tr>';
          $html .= '<tr><td>Home URL</td><td>' . home_url() . '</td></tr>';
          $active_theme = wp_get_theme();
          $html .= '<tr><td>Active Theme</td><td>' . $active_theme->get( 'Name' ) . ' (Version ' . $active_theme->get( 'Version' ) . ')' . '</td></tr>';
          $html .= '<tr><td>Ajax Test</td><td>
          <span id="ajax_test_field">' . __( 'Checking...', 'tag-groups' ) .  '</span>
          <input type="button" id="chatty-mango-help-button-ajax" class="button button-primary chatty-mango-help-icon" style="display:none;float:right;" value="'. __( 'Show the Response', 'tag-groups' ) .  '" data-topic="ajax">
          <div id="ajax_error_field" class="chatty-mango-help-container chatty-mango-help-container-ajax" style="display:none;"></div>
          </td></tr>';

          $html .= '</table></div>';

          $protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';

          $url = admin_url( 'admin-ajax.php', $protocol );

          $html .= '
          <script>
          jQuery(document).ready(function(){
            jQuery.ajax({
              url: "' . $url . '",
              data: {
                action: "tg_ajax_manage_groups",
                task: "test"
              },
              method: "post",
            })
            .done(function(){
              jQuery("#ajax_test_field").html("<span class=\"dashicons dashicons-yes\" style=\"color:green;\" title=\"' . __( 'passed', 'tag-groups' ) . '\"></span>");
            })
            .fail(function(response){
              jQuery("#ajax_error_field").text(response.responseText);

              jQuery("#chatty-mango-help-button-ajax").show();

              let learnHowToFixLink = " <a href=\"https://documentation.chattymango.com/documentation/tag-groups-premium/maintenance-and-troubleshooting/debugging-a-wordpress-ajax-error/?pk_campaign=tg&pk_kwd=ajax-failure\" target=\”_blank\">Learn more</a>";

              jQuery("#ajax_test_field").html("<span class=\"dashicons dashicons-no\" style=\"color:red;\" title=\"' . __( 'failed', 'tag-groups' ) . '\"></span> " + learnHowToFixLink);
            });
          });
          </script>';

          /* constants */
          $wp_constants = array(
            'WP_DEBUG',
            'WP_DEBUG_DISPLAY',
            'WP_DEBUG_LOG',
            'ABSPATH',
            // 'WP_HOME',
            'MULTISITE',
            'WP_CACHE',
            'COMPRESS_SCRIPTS',
            // 'FS_CHMOD_DIR',
            // 'FS_CHMOD_FILE',
            'FORCE_SSL_ADMIN',
            'CM_UPDATE_CHECK',
            'WP_MEMORY_LIMIT',
            'WP_MAX_MEMORY_LIMIT'
          );

          $html .= '<div class="tg_admin_accordion" >
          <h3>' . __( 'Constants', 'tag-groups-premium' ) . '</h3>';

          $html .= '<table class="widefat fixed">';

          $constants = get_defined_constants();

          sort( $wp_constants );

          foreach ( $wp_constants as $wp_constant ) {

            if ( isset( $constants[ $wp_constant ] ) ) {

              $html .= '<tr><td>' . $wp_constant . '</td><td>' . self::echo_var( $constants[$wp_constant] ) . '</td></tr>';

            } else {

              $html .= '<tr><td>' . $wp_constant . '</td><td>not set</td></tr>';

            }
          }

          ksort( $constants );

          foreach ( $constants as $key => $value ) {

            if ( preg_match( "/^TAG_GROUPS_/", $key ) == 1 ) {

              $html .= '<tr><td>' . $key . '</td><td>' . self::echo_var( $value ) . '</td></tr>';

            }
          }
          $html .= '</table></div>';

          break;

          default:

          if ( class_exists( 'TagGroups_Premium_Settings' ) ) {

            $html .= TagGroups_Premium_Settings::get_content( $active_tab );

          }

          break;

        }

        $html .= '</div>';

        echo $html;

        self::add_footer();

      }


      /**
      * renders a settings page: premium
      *
      * @param void
      * @return void
      */
      public static function settings_page_premium()
      {

        self::add_header();

        $html = '';

        $html .= '<div style="margin:20px 0 40px;">
        <img src="' . TAG_GROUPS_PLUGIN_URL . '/images/tgp-preview-800.jpg" alt="Tag Groups Premium banner" border="0" style="clear:both; width:100%; max-width:800px;"/>
        </div>
        <div style="border:solid 2px #CCC; float:left; max-width:770px; padding:15px;">
        <h1>' . __( 'Get more features', 'tag-groups' ) . '</h1>
        <p>' . sprintf( __( 'The <b>Tag Groups</b> plugin can be extended by <a %s>Tag Groups Premium</a>, which offers you many more useful features to take your tags to the next level:', 'tag-groups' ), 'href="https://chattymango.com/tag-groups-premium/?pk_campaign=tg&pk_kwd=dashboard" target="_blank"' ) . '</p>
        <ul style="list-style:disc;">
        <li style="padding:0 1em; margin-left:1em;">' . __( 'The <b>Shuffle Box</b>, a filterable tag cloud: Filter your tags live by group or by name with a nifty animation. See the image below.' ) . '</li>
        <li style="padding:0 1em; margin-left:1em;">' . __( 'A <b>tag input tool</b> on the post edit screen allows you to work with tags on two levels: first select the group, and then choose among the tags of that group.' ) . '</li>
        <li style="padding:0 1em; margin-left:1em;">' . __( '<b>Color coding</b> minimizes the risk of accidentally creating a new tag with a typo: New tags are green, tags that changed their groups are yellow.' ) . '</li>
        <li style="padding:0 1em; margin-left:1em;">' . __( '<b>Control new tags:</b> Optionally restrict the creation of new tags or prevent moving tags to another group on the post edit screen. These restrictions can be overridden per user role.' ) . '</li>
        <li style="padding:0 1em; margin-left:1em;">' . __( '<b>Bulk-add tags:</b> If you often need to insert the same set of tags, simply join them in one group and insert them with the push of a button.' ) . '</li>
        <li style="padding:0 1em; margin-left:1em;">' . __( 'The option to add each term to <b>multiple groups</b>.' ) . '</li>
        <li style="padding:0 1em; margin-left:1em;">' . __( '<b>Filter posts</b> on the front end by tag group through a URL parameter.' ) . '</li>
        <li style="padding:0 1em; margin-left:1em;">' . __( '<b>Dynamic Post Filter</b>: While visitors choose from available tags, the list shows posts that match these tags. Tags are organized under groups, which allows for useful logical operators. (e.g. show products that are red OR blue (group "color") AND have a size of M OR XL OR XXL.)' ) . '</li>
        <li style="padding:0 1em; margin-left:1em;">' . __( 'Display <b>post tags</b> segmented into groups under you posts.' ) . '</li>
        <li style="padding:0 1em; margin-left:1em;">' . __( '<b>New tag clouds:</b> Display your tags in a table or tags from multiple groups combined into one tag cloud.' ) . '</li>
        </ul>
        <p>' . sprintf( __( 'See the complete <a %1$s>feature comparison</a> or check out the <a %2$s>demos</a>.', 'tag-groups' ), 'href="https://chattymango.com/tag-groups-base-premium-comparison/?pk_campaign=tg&pk_kwd=dashboard" target="_blank"', 'href="https://demo.chattymango.com/tag-group-premium-demos/?pk_campaign=tg&pk_kwd=dashboard" target="_blank"' ) . '</p>
        </div>
        <div style="float:left; margin:10px; width:300px; clear:right;">
        <img src="' .  TAG_GROUPS_PLUGIN_URL . '/images/tgp-meta-box.png" alt="Tag Groups Meta Box" title="Replace the default tag meta box with one that understands your tag groups!" border="0" style="width:298px;height:400px;clear:both;"/>
        <span>Replace the default tag meta box with one that understands your tag groups!</span>
        </div>
        <div style="margin:20px 0; float:left;">
        <a href="https://chattymango.com/tag-groups-premium/?pk_campaign=tg&pk_kwd=dashboard" target="_blank">
        <img src="' .  TAG_GROUPS_PLUGIN_URL . '/images/tag-groups-premium-shuffle-box-animated-800.gif" style="width:800px;height:280px;clear:both;">
        </a>
        </div>';

        echo $html;

        self::add_footer();

      }


      /**
      * renders a settings page: about
      *
      * @param void
      * @return void
      */
      public static function settings_page_about()
      {

        self::add_header();

        $html = '';

        $tabs = array();

        $tabs['info'] = __( 'Info', 'tag-groups' );

        $tabs['licenses'] = __( 'Licenses', 'tag-groups' );

        $tabs['news'] = __( 'Development News', 'tag-groups' );

        $tabs = apply_filters( 'tag_groups_settings_about_tabs', $tabs );

        $active_tab = self::get_active_tab( $tabs );

        $html .= self::get_tabs_html( 'tag-groups-settings-about', $tabs, $active_tab );

        $html .= '<div class="tg_settings_tabs_content">';

        switch ( $active_tab ) {

          case 'info':

          $html .= '<h4><img src="' . TAG_GROUPS_PLUGIN_URL . '/images/cm-tg-icon-64x64.png" alt="Tag Groups icon" style="float:left;margin: 0 10px 10px 0;">Tag Groups, Version: ' . TAG_GROUPS_VERSION . '</h4>
          <p>Developed by Christoph Amthor @ <a href="https://chattymango.com?pk_campaign=tg&pk_kwd=dashboard" target="_blank">Chatty Mango</a></p>';

          $html .= '<p>&nbsp;</p>
          <h2>' . __( 'Follow Us', 'tag-groups' ) . '</h2>
          <p><a href="https://www.facebook.com/chattymango/" target="_blank" class="tg_social_media_admin"><span class="dashicons dashicons-facebook"></span></a> <a href="https://twitter.com/ChattyMango" target="_blank" class="tg_social_media_admin"><span class="dashicons dashicons-twitter"></span></a></p>';

          $html .= '<p>&nbsp;</p>
          <h2>' . __( 'Donations', 'tag-groups' ) . '</h2>
          <p>' . __( 'This plugin is the result of many years of development, adding new features, fixing bugs and answering to support questions.', 'tag-groups' ) . '</p>
          <p>' . __( 'If you find <b>Tag Groups</b> useful or use it to make money, I would appreciate a donation:', 'tag-groups' ). '</p>
          <p><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=NUR3YJG7VAENA" target="_blank"><img src="' . TAG_GROUPS_PLUGIN_URL . '/images/btn_donateCC_LG.gif" alt="Donate via Paypal" title="Donate via Paypal" border="0" /></a></p>
          <p>&nbsp;</p>
          <h2>' . __( 'Reviews', 'tag-groups' ) . '</h2>
          <p>' . sprintf( __( 'I would be glad if you could give my plugin a <a %s>five-star rating</a>.', 'tag-groups' ), 'href="https://wordpress.org/support/plugin/tag-groups/reviews/?filter=5" target="_blank"' ) . '
            <div style="display:inline-block;"><a href="https://wordpress.org/support/plugin/tag-groups/reviews/?filter=5" target="_blank" style="color: #ffb900;text-decoration: none;">
            <span class="dashicons dashicons-star-filled"></span>
            <span class="dashicons dashicons-star-filled"></span>
            <span class="dashicons dashicons-star-filled"></span>
            <span class="dashicons dashicons-star-filled"></span>
            <span class="dashicons dashicons-star-filled"></span>
            </a></div>
            </p>' . __( 'Thanks!', 'tag-groups' ) . '</p>
            <p>Christoph</p>';

            break;

            case 'licenses':

            $html .=
            '<p>' . sprintf( __( 'Tag Groups (free) is provided under the terms of the <a %s>GNU GENERAL PUBLIC LICENSE, Version 3</a>.', 'tag-groups' ), 'href="http://www.gnu.org/licenses/gpl.html" target="_blank"') . '</p>
            <p>' . sprintf( __( 'This plugin uses css and images by <a %s>jQuery UI</a>.', 'tag-groups' ), 'href="https://jqueryui.com/" target="_blank"') . '</p>
            <p>' . sprintf( __( 'jQuery plugin <a %1$s>SumoSelect</a>: <a %2$s>MIT License</a>. Copyright (c) 2016 Hemant Negi', 'tag-groups' ), 'href="https://github.com/HemantNegi/jquery.sumoselect"', 'href="http://www.opensource.org/licenses/mit-license.php" target="_blank"') . '</p>
            <p>' . sprintf( __( 'React JS plugin <a %1$s>React-Select</a>: <a %2$s>MIT License</a>. Copyright (c) 2018 Jed Watson', 'tag-groups' ), 'href="https://github.com/JedWatson/react-select"', 'href="http://www.opensource.org/licenses/mit-license.php" target="_blank"') . '</p>';

            break;

            case 'news':

            $html .= '<h2>' . __( 'Newsletter', 'tag-groups' ) . '</h2>
            <p>' . sprintf( __( '<a %s>Sign up for our newsletter</a> to receive updates about new versions and related tipps and news.', 'tag-groups' ), 'href="http://eepurl.com/c6AeK1" target="_blank"' ) . '</p>
              <p>&nbsp;</p>';

              $html .= '<h2>' . __( 'Latest Posts', 'tag-groups' ) . '</h2>
              <table class="widefat fixed" cellspacing="0">
              <thead>
              <tr>
              <th style="min-width:200px; width:30%;"></th>
              <th></th>
              </tr>
              </thead>
              <tbody id="tg_feed_container"><tr><td colspan="2" style="text-align:center;">' .
              __( 'Loading...', 'tag-groups') .
              '</td></tr></tbody>
              </table>

              <script>
              jQuery(document).ready(function(){
                var tg_feed_amount = jQuery("#tg_feed_amount").val();
                var data = {
                  action: "tg_ajax_get_feed",
                  url: "' . TAG_GROUPS_UPDATES_RSS_URL . '",
                  amount: 5
                };

                jQuery.post("';

                $protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
                $html .= admin_url( 'admin-ajax.php', $protocol ) .

                '", data, function (data) {
                  var status = jQuery(data).find("response_data").text();
                  if (status == "success") {
                    var output = jQuery(data).find("output").text();
                    jQuery("#tg_feed_container").html(output);
                  }
                });
              });
              </script>';

              break;

              default:

              if ( class_exists( 'TagGroups_Premium_Settings' ) ) {

                $html .= TagGroups_Premium_Settings::get_content( $active_tab );

              }

              break;

            }

            $html .= '</div>';

            echo $html;

            self::add_footer();

          }


          /**
          * renders a menu-less settings page: onboarding
          *
          * @param void
          * @return void
          */
          public static function settings_page_onboarding()
          {

            self::add_header();

            $html = '';

            $html .= '<h2>' . __( 'First Steps', 'tag-groups' ) . '</h2>'; // Title not automatically retrieved for admin pages without menu entry

            $html .= '<div class="chatty-mango-help-container">';

            $settings_taxonomy_link = admin_url( 'admin.php?page=tag-groups-settings-taxonomies' );

            $settings_home_link = admin_url( 'admin.php?page=tag-groups-settings' );

            $settings_premium_link = admin_url( 'admin.php?page=tag-groups-settings-premium' );

            if ( defined( 'TAG_GROUPS_PREMIUM_VERSION' ) ) {

              $title = 'Tag Groups Premium';

              $documentation_link = 'https://documentation.chattymango.com/documentation/tag-groups-premium/';

            } else {

              $title = 'Tag Groups';

              $documentation_link = 'https://documentation.chattymango.com/documentation/tag-groups/';

            }

            $html .= '<p>' . sprintf( __( 'Welcome to %s!', 'tag-groups' ), $title ) . '</p>
            <p>&nbsp;</p>';

            $html .= '<h3>' . __( 'Get started in 3 easy steps:', 'tag-groups' ) . '</h3>' .
            '<ol>
            <li>' . sprintf( __( 'Go to the <span class="dashicons dashicons-tag"></span>&nbsp;Tag Groups <a %s>taxonomy settings</a> and <b>select the taxonomy</b> (tag type) of your tags. In most cases just leave the default: Tags (post_tag).', 'tag-groups' ), 'href="' . esc_url( $settings_taxonomy_link ) . '" target="_blank"' ) . '</li>
            <li>' . __( 'Go to the tag groups admin page and <b>create some groups</b>.', 'tag-groups' ) . ' '  . sprintf( __( 'You can find these pages listed on your <a %s>Tag Groups home page</a>.', 'tag-groups' ), 'href="' . esc_url( $settings_home_link ) . '" target="_blank"' ) .  '</li>
            <li>' . __( 'Go to your tags and <b>assign them to these groups</b>.', 'tag-groups' ) . '</li>
            </ol>
            <p>' . sprintf( __( 'Now your tags are organized in groups. You can use them, for example, in a tag cloud. Just insert a shortcode into a page or post - try: [tag_groups_cloud]. You find all shortcodes and <a %s>links to the documentation</a> in the <span class="dashicons dashicons-tag"></span>&nbsp;Tag Groups settings.', 'tag-groups' ), 'href="' . esc_url( $documentation_link .'?pk_campaign=tg&pk_kwd=onboarding' ) . '" target="_blank"' ) . '</p>';

            if ( ! defined( 'TAG_GROUPS_PREMIUM_VERSION' ) ) {

              $html .= '<p>' . sprintf( __( 'You get many more features with the <a %s>premium plugin</a>.', 'tag-groups' ), 'href="' . esc_url( $settings_premium_link ) . '" target="_blank"' ) . '</p>';

            }

            $html .= '<p>&nbsp;</p>
            <p>' . __( 'Happy tagging!', 'tag-groups' ) . '</p>';

            $html .= '</div>';

            echo $html;

            self::add_footer();

          }


          /**
          * Processes form submissions from the settings page
          *
          * @param void
          * @return void
          */
          static function settings_page_actions() {

            global $tagGroups_Base_instance;

            if ( ! empty( $_REQUEST['tg_action'] ) ) {

              $tg_action = $_REQUEST['tg_action'];

            } else {

              return;

            }

            if ( isset( $_GET['id'] ) ) {

              $tag_groups_id = (int) $_GET['id'];

            } else {

              $tag_groups_id = 0;

            }

            if ( isset( $_POST['ok'] ) ) {

              $ok = $_POST['ok'];

            } else {

              $ok = '';

            }


            switch ( $tg_action ) {

              case 'shortcode':

              if ( ! isset( $_POST['tag-groups-shortcode-nonce'] ) || ! wp_verify_nonce( $_POST['tag-groups-shortcode-nonce'], 'tag-groups-shortcode' ) ) {

                die( "Security check" );

              }

              if ( isset( $_POST['widget'] ) && ($_POST['widget'] == '1') ) {

                update_option( 'tag_group_shortcode_widget', 1 );

              } else {

                update_option( 'tag_group_shortcode_widget', 0 );

              }


              if ( isset( $_POST['enqueue'] ) && ($_POST['enqueue'] == '1') ) {

                update_option( 'tag_group_shortcode_enqueue_always', 1 );

              } else {

                update_option( 'tag_group_shortcode_enqueue_always', 0 );

              }

              TagGroups_Admin_Notice::add( 'success', __( 'Your settings have been saved.', 'tag-groups' ) );

              break;


              case 'reset':

              if ( ! isset( $_POST['tag-groups-reset-nonce'] ) || ! wp_verify_nonce( $_POST['tag-groups-reset-nonce'], 'tag-groups-reset' ) ) {

                die( "Security check" );

              }


              if ( $ok == 'yes' ) {

                $group = new TagGroups_Group();

                $group->reset();

                /**
                * Remove filters
                */
                delete_option( 'tag_group_tags_filter' );

                TagGroups_Admin_Notice::add( 'success', __( 'All groups have been deleted and assignments reset.', 'tag-groups' ) );

              }

              break;


              case 'uninstall':

              if ( ! isset( $_POST['tag-groups-uninstall-nonce'] ) || ! wp_verify_nonce( $_POST['tag-groups-uninstall-nonce'], 'tag-groups-uninstall' ) ) {

                die( "Security check" );

              }


              if ( $ok == 'yes' ) {

                update_option( 'tag_group_reset_when_uninstall', 1 );

              } else {

                update_option( 'tag_group_reset_when_uninstall', 0 );

              }

              TagGroups_Admin_Notice::add( 'success', __( 'Your settings have been saved.' ) );

              break;


              case 'theme':

              if ( isset( $_POST['theme-name'] ) ) {

                $theme_name = stripslashes( sanitize_text_field( $_POST['theme-name'] ) );

              } else {

                $theme_name = '';

              }

              if ( isset( $_POST['theme'] ) ) {

                $theme = stripslashes( sanitize_text_field( $_POST['theme'] ) );

              } else {

                $theme = '';

              }

              if ( $theme == 'own' ) {

                $theme = $theme_name;

              }

              if ( ! isset( $_POST['tag-groups-settings-nonce'] ) || ! wp_verify_nonce( $_POST['tag-groups-settings-nonce'], 'tag-groups-settings' ) ) {

                die( "Security check" );

              }

              update_option( 'tag_group_theme', $theme );

              $mouseover = (isset( $_POST['mouseover'] ) && $_POST['mouseover'] == '1') ? 1 : 0;

              $collapsible = (isset( $_POST['collapsible'] ) && $_POST['collapsible'] == '1') ? 1 : 0;

              $html_description = (isset( $_POST['html_description'] ) && $_POST['html_description'] == '1') ? 1 : 0;

              update_option( 'tag_group_mouseover', $mouseover );

              update_option( 'tag_group_collapsible', $collapsible );

              update_option( 'tag_group_html_description', $html_description );

              $tag_group_enqueue_jquery = ( isset( $_POST['enqueue-jquery'] ) && $_POST['enqueue-jquery'] == '1' ) ? 1 : 0;

              update_option( 'tag_group_enqueue_jquery', $tag_group_enqueue_jquery );

              TagGroups_Admin::clear_cache();

              TagGroups_Admin_Notice::add( 'success', __( 'Your settings have been saved.' ) );

              break;


              case 'taxonomy':

              if ( ! isset( $_POST['tag-groups-taxonomy-nonce'] ) || ! wp_verify_nonce( $_POST['tag-groups-taxonomy-nonce'], 'tag-groups-taxonomy' ) ) {

                die( "Security check" );

              }

              if ( isset( $_POST['taxonomies'] ) ) {

                $taxonomies = $_POST['taxonomies'];

                if ( is_array( $taxonomies ) ) {

                  $taxonomies = array_map( 'sanitize_text_field', $taxonomies );

                  $taxonomies = array_map( 'stripslashes', $taxonomies );

                } else {

                  $taxonomies = array( 'post_tag' );

                }

              } else {

                $taxonomies = array( 'post_tag' );

              }

              $public_taxonomies = TagGroups_Taxonomy::get_public_taxonomies();

              foreach ( $taxonomies as $taxonomy_item ) {

                if ( ! in_array( $taxonomy_item, $public_taxonomies ) ) {

                  die( "Security check: taxonomies" );

                }

              }

              update_option( 'tag_group_taxonomy', $taxonomies );

              // trigger actions
              do_action( 'taxonomies_saved', $taxonomies );

              if ( class_exists( 'TagGroups_Premium_Post' ) && ( ! defined( 'TAG_GROUPS_DISABLE_CACHE_REBUILD' ) || TAG_GROUPS_DISABLE_CACHE_REBUILD ) ) {

                // schedule rebuild of cache
                wp_schedule_single_event( time() + 10, 'tag_groups_rebuild_post_terms' );

              }

              TagGroups_Admin_Notice::add( 'success', __( 'Your settings have been saved.' ) );

              break;


              case 'backend':

              if ( ! isset( $_POST['tag-groups-backend-nonce'] ) || ! wp_verify_nonce( $_POST['tag-groups-backend-nonce'], 'tag-groups-backend' ) ) {

                die( "Security check" );

              }

              $show_filter_posts = isset( $_POST['filter_posts'] ) ? 1 : 0;

              update_option( 'tag_group_show_filter', $show_filter_posts );

              $show_filter_tags = isset( $_POST['filter_tags'] ) ? 1 : 0;

              update_option( 'tag_group_show_filter_tags', $show_filter_tags );

              TagGroups_Admin_Notice::add( 'success', __( 'Your settings have been saved.' ) );

              break;


              case 'export':

              if ( ! isset( $_POST['tag-groups-export-nonce'] ) || ! wp_verify_nonce( $_POST['tag-groups-export-nonce'], 'tag-groups-export' ) ) {

                die( "Security check" );

              }

              $options = array(
                'name' => 'tag_groups_options',
                'version' => TAG_GROUPS_VERSION,
                'date' => current_time( 'mysql' )
              );

              $option_names = $tagGroups_Base_instance->get_option_names();

              foreach ( $option_names as $key => $value ) {

                if ( $option_names[ $key ][ 'export' ] ) {

                  $options[ $key ] = get_option( $key );

                }

              }

              // generate array of all terms
              $terms = get_terms( array(
                'hide_empty' => false,
              ) );

              $cm_terms = array(
                'name' => 'tag_groups_terms',
                'version' => TAG_GROUPS_VERSION,
                'date' => current_time( 'mysql' )
              );

              $cm_terms['terms'] = array();

              $tag_group_taxonomy = get_option( 'tag_group_taxonomy', array('post_tag') );

              foreach ( $terms as $term ) {

                if ( in_array( $term->taxonomy, $tag_group_taxonomy ) ) {

                  if ( class_exists('TagGroups_Premium_Term') && get_term_meta( $term->term_id, '_cm_term_group_array', true ) != '' ) {

                    $term_group = explode( ',', get_term_meta( $term->term_id, '_cm_term_group_array', true ) );

                  } else {

                    $term_group = $term->term_group;

                  }

                  $cm_terms['terms'][] = array(
                    'term_id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'term_group' => $term_group,
                    'term_taxonomy_id' => $term->term_taxonomy_id,
                    'taxonomy' => $term->taxonomy,
                    'description' => $term->description,
                    'parent' => $term->parent,
                    'count' => $term->count,
                    'filter' => $term->filter,
                    'meta' => $term->meta,
                  );

                }

              }


              /**
              * Writing file
              */
              try {

                // misusing the password generator to get a hash
                $hash = wp_generate_password( 10, false );

                /*
                * Write settings/groups and tags separately
                */
                $fp = fopen( WP_CONTENT_DIR . '/uploads/tag_groups_settings-' . $hash . '.json', 'w' );

                fwrite( $fp, json_encode( $options ) );

                fclose( $fp );


                $fp = fopen( WP_CONTENT_DIR . '/uploads/tag_groups_terms-' . $hash . '.json', 'w' );

                fwrite( $fp, json_encode( $cm_terms ) );

                fclose( $fp );


                TagGroups_Admin_Notice::add( 'success', __( 'Your settings/groups and your terms have been exported. Please download the resulting files with right-click or ctrl-click:', 'tag-groups' ) .
                '  <p>
                <a href="' . get_bloginfo( 'wpurl' ) . '/wp-content/uploads/tag_groups_settings-' . $hash . '.json" target="_blank">tag_groups_settings-' . $hash . '.json</a>
                </p>' .
                '  <p>
                <a href="' . get_bloginfo( 'wpurl' ) . '/wp-content/uploads/tag_groups_terms-' . $hash . '.json" target="_blank">tag_groups_terms-' . $hash . '.json</a>
                </p>' );

              } catch ( Exception $e ) {

                TagGroups_Admin_Notice::add( 'error', __( 'Writing of the exported settings failed.', 'tag-groups' ) );

              }

              break;

              case 'import':

              if ( ! isset( $_POST['tag-groups-import-nonce'] ) || ! wp_verify_nonce( $_POST['tag-groups-import-nonce'], 'tag-groups-import' ) ) {

                die( "Security check" );

              }

              // Make very sure that only administrators can upload stuff
              if ( ! current_user_can( 'manage_options' ) ) {

                die( "Capability check failed" );

              }

              if ( ! isset( $_FILES['settings_file'] ) ) {

                die( "File missing" );

              }

              if ( ! function_exists( 'wp_handle_upload' ) ) {

                require_once ABSPATH . 'wp-admin/includes/file.php';

              }

              $settings_file = $_FILES['settings_file'];

              // Check file name, but allow for some additional characters in file name since downloading multiple times may add something to the original name.
              // Allow extension txt for backwards compatibility
              preg_match( '/^tag_groups_settings-\w{10}[\w,\s-]*\.((txt)|(json))$/', $_FILES['settings_file']['name'], $matches_settings );

              preg_match( '/^tag_groups_terms-\w{10}[\w,\s-]*\.json$/', $_FILES['settings_file']['name'], $matches_terms );

              if ( ! empty( $matches_settings ) && ! empty( $matches_settings[0] ) && $matches_settings[0] == $_FILES['settings_file']['name'] ) {

                $contents = @file_get_contents( $settings_file['tmp_name'] );

                if ( $contents === false ) {

                  TagGroups_Admin_Notice::add( 'error', __( 'Error reading the file.', 'tag-groups' ) );

                } else {

                  $options = @json_decode( $contents , true);

                  if ( empty( $options ) || !is_array( $options ) || $options['name'] != 'tag_groups_options' ) {

                    TagGroups_Admin_Notice::add( 'error', __( 'Error parsing the file.', 'tag-groups' ) );

                  } else {

                    $option_names = $tagGroups_Base_instance->get_option_names();

                    $changed = 0;

                    // import only whitelisted options
                    foreach ( $option_names as $key => $value ) {

                      if ( isset( $options[ $key ] ) ) {

                        $changed += update_option( $key, $options[ $key ] ) ? 1 : 0;

                      }

                    }

                    if ( ! isset( $options['date'] ) ) {
                      $options['date'] = ' - ' . __( 'date unknown', 'tag-groups' ) . ' - ';
                    }

                    TagGroups_Admin_Notice::add( 'success', sprintf( __( 'Your settings and groups have been imported from the file %1$s (created with plugin version %2$s on %3$s).', 'tag-groups' ), '<b>' . $_FILES['settings_file']['name'] . '</b>', $options['version'], $options['date'] ) . '</p><p>' .
                    sprintf( _n( '%d option was added or changed.','%d options were added or changed.', $changed, 'tag-groups' ), $changed ) );

                  }

                }

              } elseif ( ! empty( $matches_terms ) && ! empty( $matches_terms[0] ) && $matches_terms[0] == $_FILES['settings_file']['name'] ) {

                $contents = @file_get_contents( $settings_file['tmp_name'] );

                if ( $contents === false ) {

                  TagGroups_Admin_Notice::add( 'error', __( 'Error reading the file.', 'tag-groups' ) );

                } else {

                  $terms = @json_decode( $contents , true);

                  if ( empty( $terms ) || !is_array( $terms ) || $terms['name'] != 'tag_groups_terms' ) {

                    TagGroups_Admin_Notice::add( 'error', __( 'Error parsing the file.', 'tag-groups' ) );

                  } else {

                    $changed = 0;

                    foreach ( $terms['terms'] as $term ) {

                      // change only terms with the same name, else create new one
                      if ( !term_exists( $term['term_id'], $term['taxonomy'] ) ) {

                        $inserted_term = wp_insert_term( $term['name'], $term['taxonomy'] );

                        if ( is_array( $inserted_term ) ) {

                          if ( is_array( $term['term_group'] ) && class_exists( 'TagGroups_Premium_Term' ) ) {

                            TagGroups_Premium_Term::save( $inserted_term['term_id'], $term['taxonomy'], $term['term_group'] );

                            unset( $term['term_group'] );

                          }

                          $result = wp_update_term( $inserted_term['term_id'], $term['taxonomy'], $term );

                          if ( is_array( $result ) ) {

                            $changed++;

                          }

                        }

                      } else {

                        $result = wp_update_term( $term['term_id'], $term['taxonomy'], $term );

                        if ( is_array( $result ) ) {

                          $changed++;

                        }

                      }

                    }

                    if ( ! isset( $terms['date'] ) ) {

                      $terms['date'] = ' - ' . __( 'date unknown', 'tag-groups' ) . ' - ';

                    }

                    TagGroups_Admin_Notice::add( 'success', sprintf( __( 'Your terms have been imported from the file %1$s (created with plugin version %2$s on %3$s).', 'tag-groups' ), '<b>' . $_FILES['settings_file']['name'] . '</b>', $terms['version'], $terms['date'] ) . '</p><p>' .
                    sprintf( _n( '%d term was added or updated.','%d terms were added or updated.', $changed, 'tag-groups' ), $changed ) );

                  }

                }

              } else {

                if ( ! empty( $_FILES['settings_file']['name'] ) ) {

                  $file_info = ' ' . $_FILES['settings_file']['name'];

                } else {

                  $file_info = '';

                }

                TagGroups_Admin_Notice::add( 'error', __( 'Error uploading the file.', 'tag-groups' ) . $file_info );

              }

              break;

              default:
              // hook for premium plugin
              do_action( 'tag_groups_hook_settings_action', $tg_action );

              break;
            }


          }


          /**
          * Prepares variable for echoing as string
          *
          *
          * @param mixed $var Mixed type that needs to be echoed as string.
          * @return return string
          */
          private static function echo_var( $var = '' )
          {

            if ( is_bool( $var ) ) {

              return $var ? 'true' : 'false';

            } elseif ( is_array( $var ) )  {

              return print_r( $var, true );

            } else {

              return (string) $var;

            }

          }


          /**
          * Returns an array that contains topics covered in the settings
          *
          * @param void
          * @return array
          */
          public static function get_setting_topics()
          {

            $public_taxonomies_slugs = TagGroups_Taxonomy::get_public_taxonomies();

            $public_taxonomies_names = array_map( array( 'TagGroups_Taxonomy', 'get_name_from_slug' ), $public_taxonomies_slugs );

            $topics = array(
              'taxonomies'  => array(
                'title' => __( 'Taxonomies', 'tag-groups' ),
                'page' => 'tag-groups-settings-taxonomies',
                'keywords'  => array_merge(
                  array_keys( $public_taxonomies_names ),
                  array_values( $public_taxonomies_names ),
                  array(
                    __( 'tag groups', 'tag-groups' ),
                  )
                ),
              ),
              'shortcodes'  => array(
                'title' => __( 'Shortcodes', 'tag-groups' ),
                'page' => 'tag-groups-settings-front-end' ,
                'keywords'  => array(
                  __( 'tag cloud', 'tag-groups' ),
                  __( 'group info', 'tag-groups' ),
                  __( 'sidebar widget', 'tag-groups' ),
                  'Gutenberg',
                ),
              ),
              'themes'  => array(
                'title' => __( 'Themes for Tabs and Accordion', 'tag-groups' ),
                'page' => 'tag-groups-settings-front-end' ,
                'keywords'  => array(
                  __( 'tag cloud', 'tag-groups' ),
                  'CSS',
                  'style',
                  __( 'colors', 'tag-groups' ),
                ),
              ),
              'filters'  => array(
                'title' => __( 'Filters', 'tag-groups' ),
                'page' => 'tag-groups-settings-back-end' ,
                'keywords'  => array(
                  __( 'tag filter', 'tag-groups' ),
                  __( 'post filter', 'tag-groups' ),
                ),
              ),
              'export_import'  => array(
                'title' => __( 'Export/Import', 'tag-groups' ),
                'page' => 'tag-groups-settings-tools' ,
                'keywords'  => array(
                  __( 'backup', 'tag-groups' ),
                ),
              ),
              'reset'  => array(
                'title' => __( 'Reset', 'tag-groups' ),
                'page' => 'tag-groups-settings-tools' ,
                'keywords'  => array(
                  __( 'remove plugin', 'tag-groups' ),
                  __( 'remove data', 'tag-groups' ),
                  __( 'delete groups', 'tag-groups' ),
                ),
              ),
              'faq'  => array(
                'title' => __( 'FAQ and Common Issues', 'tag-groups' ),
                'page' => 'tag-groups-settings-troubleshooting' ,
                'keywords'  => array(
                  __( 'frequently asked questions', 'tag-groups' ),
                  __( 'help', 'tag-groups' ),
                  __( 'bug', 'tag-groups' ),
                  __( 'problem', 'tag-groups' ),
                  __( 'troubleshooting', 'tag-groups' ),
                ),
              ),
              'documentation'  => array(
                'title' => __( 'Documentation', 'tag-groups' ),
                'page' => 'tag-groups-settings-troubleshooting' ,
                'keywords'  => array(
                  __( 'instructions', 'tag-groups' ),
                  __( 'help', 'tag-groups' ),
                  __( 'problem', 'tag-groups' ),
                  __( 'troubleshooting', 'tag-groups' ),
                  'Gutenberg',
                  'CSS',
                  'style',
                  'PHP',
                  'API'
                ),
              ),
              'support'  => array(
                'title' => __( 'Get Support', 'tag-groups' ),
                'page' => 'tag-groups-settings-troubleshooting' ,
                'keywords'  => array(
                  __( 'support', 'tag-groups' ),
                  __( 'contact', 'tag-groups' ),
                  __( 'forum', 'tag-groups' ),
                  __( 'bug', 'tag-groups' ),
                  __( 'problem', 'tag-groups' ),
                  __( 'help', 'tag-groups' ),
                ),
              ),
              'system'  => array(
                'title' => __( 'System Information', 'tag-groups' ),
                'page' => 'tag-groups-settings-troubleshooting' ,
                'keywords'  => array(
                  __( 'debugging', 'tag-groups' ),
                  __( 'PHP Version', 'tag-groups' ),
                  __( 'Ajax Test', 'tag-groups' ),
                  __( 'troubleshooting', 'tag-groups' ),
                ),
              ),
              'premium'  => array(
                'title' => __( 'Premium', 'tag-groups' ),
                'page' => 'tag-groups-settings-premium' ,
                'keywords'  => array(
                  __( 'upgrade', 'tag-groups' ),
                  __( 'more groups', 'tag-groups' ),
                  __( 'posts', 'tag-groups' ),
                  __( 'tag cloud', 'tag-groups' ),
                  __( 'filter', 'tag-groups' ),
                  'WooCommerce'
                ),
              ),
              'info'  => array(
                'title' => __( 'Info', 'tag-groups' ),
                'page' => 'tag-groups-settings-about' ,
                'keywords'  => array(
                  __( 'author', 'tag-groups' ),
                  __( 'version', 'tag-groups' ),
                  __( 'contact', 'tag-groups' ),
                  __( 'about', 'tag-groups' ),
                ),
              ),
              'licenses'  => array(
                'title' => __( 'Licenses', 'tag-groups' ),
                'page' => 'tag-groups-settings-about' ,
                'keywords'  => array(
                  __( 'Credits', 'tag-groups' ),
                ),
              ),
              'news'  => array(
                'title' => __( 'Development News', 'tag-groups' ),
                'page' => 'tag-groups-settings-about' ,
                'keywords'  => array(
                  __( 'blog', 'tag-groups'),
                  __( 'updates', 'tag-groups' ),
                ),
              ),
              'getting_started'  => array(
                'title' => __( 'First Steps', 'tag-groups' ),
                'page' => 'tag-groups-settings-first-steps' ,
                'keywords'  => array(
                  __( 'getting started', 'tag-groups' ),
                  __( 'introduction', 'tag-groups' ),
                  __( 'help', 'tag-groups' ),
                ),
              ),
            );

            $topics = apply_filters( 'tag_groups_setting_topics' , $topics );

            return $topics;

          }


          /**
          * undocumented function summary
          *
          * Undocumented function long description
          *
          * @param type var Description
          * @return return type
          */
          public static function get_setting_help()
          {

            $html = '<div id="tg_setting_help_search">';

            $html .= '<div style="float:right;" title="' . __( 'Search and get direct links to setting pages. Hint: Type *, space or \'all\' to show all.', 'tag-groups' ) . '"><span class="dashicons dashicons-search tg_setting_help_search_icon"></span><input id="tg_setting_help_search_field" placeholder="' . __( 'Search for settings', 'tag-groups' ) . '" autocomplete="off"></div>
            <div id="tg_setting_help_search_results" style="display:none;">
            <h2>' . __( 'Search Results', 'tag-groups' ) . '</h2>';

            $html .= '<div class="chatty-mango-settings-columns tg_setting_help_search_results_inner">';

            $html .= '<h4 id="tg_setting_help_nothing_found" style="display:none">' . __( 'Nothing found', 'tag-groups' ) . '</h4>';

            $topics = self::get_setting_topics();

            asort( $topics );

            foreach ( $topics as $tab => $atts ) {

              $keywords = strtolower( implode( ',', $atts['keywords'] ) ) . ',' . strtolower( $atts['title'] ) . ',' . $tab;

              $html .= '<div id="tg_topic_' . $tab . '" class="tg_settings_topic" data-keywords="' . esc_html( $keywords) . '"><h4><span class="dashicons dashicons-arrow-right-alt tg_no_underline"></span>&nbsp; <a href="' . admin_url( 'admin.php?page=' . $atts['page'] . '&active-tab=' . $tab ) . '">' . $atts['title'] . '</a></h4></div>';

            }

            $html .= '</div>';

            $html .= '</div>
            </div>
            <script>
            var searchText = "";
            jQuery(document).ready(function(){
              jQuery("#tg_setting_help_search_field").keyup(function(){
                searchText = jQuery("#tg_setting_help_search_field").val().toLowerCase();
                if (searchText.length === 0) {
                  jQuery("#tg_setting_help_search_results").slideUp();
                } else {
                  if (jQuery("#tg_setting_help_search_results").css("display") === "none") {
                    jQuery("#tg_setting_help_search_results").slideDown();
                  }
                  if (searchText==="' . __( 'all', 'tag-groups') . '" || searchText==="*" || searchText===" ") {
                    jQuery(".tg_settings_topic").removeClass("tg_hide");
                  } else {
                    jQuery(".tg_settings_topic")
                    .addClass("tg_hide")
                    .filter(function(index){
                      var keywords = jQuery(this).attr("data-keywords");
                      return keywords.indexOf(searchText) > -1;
                    })
                    .removeClass("tg_hide");
                  }
                  if (jQuery(".tg_settings_topic:not(.tg_hide)").length === 0 ){
                    jQuery("#tg_setting_help_nothing_found").slideDown();
                  } else {
                    jQuery("#tg_setting_help_nothing_found").slideUp();
                  }
                  jQuery(".tg_settings_topic.tg_hide").slideUp();
                  jQuery(".tg_settings_topic:not(.tg_hide)").slideDown();
                }
              });
            });
            </script>
            ';

            return $html;

          }


        }

      }
