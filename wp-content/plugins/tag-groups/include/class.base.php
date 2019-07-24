<?php
/**
* @package     Tag Groups
* @author      Christoph Amthor
* @copyright   2018 Christoph Amthor (@ Chatty Mango, chattymango.com)
* @license     GPL-3.0+
*/

if ( ! class_exists('TagGroups_Base') ) {

  /**
  *
  */
  class TagGroups_Base {

    // private $include_path;

    // private $blogurl;

    private $tagGroupsStartup, $tagGroups_Admin, $tagGroups_Shortcode;

    /**
    *
    */
    function __construct() {

      if ( ! defined( 'TAG_GROUPS_VERSION') ){

        define( 'TAG_GROUPS_VERSION', TagGroups_Base::get_version_from_plugin_data( TAG_GROUPS_PLUGIN_ABSOLUTE_PATH . '/tag-groups.php' ) );

      }

      $this->check_preconditions();

      $this->register_actions();

    }


    /**
    * get the version
    *
    *
    * @param string $path Absolute path of this plugin
    * @return void
    */
    public static function get_version_from_plugin_data( $path )
    {

      if ( ! function_exists('get_plugin_data') ){

        require_once ABSPATH . '/wp-admin/includes/plugin.php';

      }

      $plugin_header = get_plugin_data( $path, false, false );

      if ( isset( $plugin_header['Version'] ) ) {

        return $plugin_header['Version'];

      } else {

        return '1.0';

      }

    }


    /**
    *   Registers all required actions with WP
    *
    * @param void
    * @return void
    */
    private function register_actions() {

      // general stuff
      add_action( 'plugins_loaded', array( 'TagGroups_Base', 'register_textdomain' ) );


      if ( is_admin() ) {

        // backend stuff
        add_action( 'admin_init', array( 'TagGroups_Admin', 'admin_init' ) );

        add_action( 'admin_init', array( 'TagGroups_Base', 'check_old_premium' ) );

        add_action( 'admin_menu', array( 'TagGroups_Admin', 'register_menus' ) );

        add_action( 'admin_enqueue_scripts', array( 'TagGroups_Admin', 'add_admin_js_css' ) );

        add_action( 'admin_notices', array( 'TagGroups_Admin_Notice', 'display' ) );

      } else {

        // frontend stuff
        add_action( 'wp_enqueue_scripts', array( 'TagGroups_Base', 'add_js_css' ) );

        add_action( 'init', array( 'TagGroups_Shortcode', 'widget_hook' ) );

      }

      // Register shortcodes also for admin so that we can remove them with strip_shortcodes in Ajax call
      TagGroups_Shortcode::register();

      /**
      * REST API
      */
      TagGroups_REST_API::register();


    }


    /**
    * Check if WordPress meets the minimum version
    *
    * @param void
    * @return void
    */
    private function check_preconditions() {

      global $wp_version;

      // Check the minimum WP version
      if ( version_compare( $wp_version, TAG_GROUPS_MINIMUM_VERSION_WP , '<' ) ) {

        error_log( '[Tag Groups] Insufficient WordPress version for Tag Groups plugin.' );

        TagGroups_Admin_Notice::add( 'error', sprintf( __( 'The plugin %1$s requires WordPress %2$s to function properly.', 'tag-groups'), '<b>Tag Groups</b>', TAG_GROUPS_MINIMUM_VERSION_WP ) .
        __( 'Please upgrade WordPress and then try again.', 'tag-groups' ) );

        return;

      }

    }


    /**
    * Check if we don't have any old Tag Groups Premium
    *
    * @param void
    * @return void
    */
    public static function check_old_premium() {

      // Check the minimum WP version
      if (
        defined( 'TAG_GROUPS_VERSION' ) &&
        defined( 'TAG_GROUPS_PREMIUM_VERSION' ) &&
        version_compare( TAG_GROUPS_VERSION, '0.38' , '>' ) &&
        version_compare( TAG_GROUPS_PREMIUM_VERSION, '1.12' , '<' )
      ) {

        error_log( '[Tag Groups Premium] Incompatible versions of Tag Groups and Tag Groups Premium.' );

        TagGroups_Admin_Notice::add( 'info', sprintf( __( 'Your version of Tag Groups Premium is out of date and will not work with this version of Tag Groups. Please <a %s>update Tag Groups Premium</a>.', 'tag-groups'), 'href="https://documentation.chattymango.com/documentation/tag-groups-premium/maintenance-and-troubleshooting/updating-tag-groups-premium/" target="_blank"' ), '<b>Tag Groups</b>' );

        return;

      }

    }


    /**
    *   Initializes values and prevents errors that stem from wrong values, e.g. based on earlier bugs.
    *   Runs when plugin is activated.
    *
    * @param void
    * @return void
    */
    static function on_activation() {

      if ( ! defined( 'TAG_GROUPS_VERSION') ){

        define( 'TAG_GROUPS_VERSION', TagGroups_Base::get_version_from_plugin_data( TAG_GROUPS_PLUGIN_ABSOLUTE_PATH . '/tag-groups.php' ) );

        update_option( 'tag_group_base_version', TAG_GROUPS_VERSION );

      }

      /*
      * Taxonomy should not be empty
      */
      $tag_group_taxonomy = get_option( 'tag_group_taxonomy', array() );

      if ( empty( $tag_group_taxonomy ) ) {

        update_option( 'tag_group_taxonomy', array('post_tag') );

      } elseif ( ! is_array( $tag_group_taxonomy ) ) {

        // Prevent some weird errors
        update_option( 'tag_group_taxonomy', array( $tag_group_taxonomy ) );

      }

      /*
      * Theme should not be empty
      */
      if ( '' == get_option( 'tag_group_theme', '' )  ) {

        update_option( 'tag_group_theme', TAG_GROUPS_STANDARD_THEME );

      }


      /**
      * Register time of first use
      */
      if ( ! get_option( 'tag_group_base_first_activation_time', false ) ) {

        update_option( 'tag_group_base_first_activation_time', time() );

      }


      // after 0.26.1
      delete_option( 'tag_group_cache' );


      // Make sure that the groups have been loaded and saved at least once.
      $group_o = new TagGroups_Group();

      $group_o->save();

      // If requested and new options exist, then remove old options.
      if (
        defined( 'TAG_GROUPS_REMOVE_OLD_OPTIONS' )
        && TAG_GROUPS_REMOVE_OLD_OPTIONS
        && get_option( 'term_groups', false )
        && get_option( 'term_group_positions', false )
        && get_option( 'term_group_labels', false )
      ) {

        delete_option( 'tag_group_labels' );

        delete_option( 'tag_group_ids' );

        delete_option( 'max_tag_group_id' );

        if ( defined( 'WP_DEBUG') && WP_DEBUG ) {

          error_log( '[Tag Groups] Deleted deprecated options' );

        }

      }


      // purge cache
      if ( class_exists( 'ChattyMango_Cache' ) ) {
        $cache = new ChattyMango_Cache();
        $cache
        ->type( get_option( 'tag_group_object_cache', ChattyMango_Cache::WP_OPTIONS ) )
        ->path( WP_CONTENT_DIR . '/chatty-mango/cache/' )
        ->purge_all();
      }


      if ( get_option( 'tag_group_onboarding', false ) === false ) {

        /*
        * Seems to be a first-time user - display some help
        */
        $onboarding_link =  admin_url( 'admin.php?page=tag-groups-settings-first-steps' );

        if ( defined( 'TAG_GROUPS_PREMIUM_VERSION' ) ) {

          $plugin_name = 'Tag Groups Premium';

        } else {

          $plugin_name = 'Tag Groups';

        }

        TagGroups_Admin_Notice::add(
          'info',
          '<h3>' . sprintf( __( 'Thank you for installing %s!', 'tag-groups' ), $plugin_name ) . '</h3>' .
          '<p>' . sprintf( __( 'Click <a %s>here</a> to get some help on how to get started.', 'tag-groups' ), 'href="' . $onboarding_link . '"') . '</p>'
        );

        update_option( 'tag_group_onboarding', 1 );

      }

    }


    /**
    * Adds js and css to frontend
    *
    *
    * @param void
    * @return void
    */
    static function add_js_css() {

      /* enqueue frontend scripts and styling only if shortcode in use */
      global $post;

      if (
        get_option( 'tag_group_shortcode_enqueue_always', 1 ) || (
          ! is_a( $post, 'WP_Post' ) || (
            has_shortcode( $post->post_content, 'tag_groups_cloud' ) ||
            has_shortcode( $post->post_content, 'tag_groups_accordion' ) ||
            has_shortcode( $post->post_content, 'tag_groups_table' ) )
            )
          ) {

            $theme = get_option( 'tag_group_theme', TAG_GROUPS_STANDARD_THEME );

            $default_themes = explode( ',', TAG_GROUPS_BUILT_IN_THEMES );

            $tag_group_enqueue_jquery = get_option( 'tag_group_enqueue_jquery', 1 );


            if ( $tag_group_enqueue_jquery ) {

              wp_enqueue_script( 'jquery' );

              wp_enqueue_script( 'jquery-ui-core' );

              wp_enqueue_script( 'jquery-ui-tabs' );

              wp_enqueue_script( 'jquery-ui-accordion' );

            }

            if ( $theme == '' ) {

              return;

            }

            wp_register_style( 'tag-groups-css-frontend-structure', TAG_GROUPS_PLUGIN_URL . '/css/jquery-ui.structure.min.css', array(), TAG_GROUPS_VERSION );


            if ( in_array( $theme, $default_themes ) ) {

              wp_register_style( 'tag-groups-css-frontend-theme', TAG_GROUPS_PLUGIN_URL . '/css/' . $theme . '/jquery-ui.theme.min.css', array(), TAG_GROUPS_VERSION );

            } else {
              /*
              * Load minimized css if available
              */
              if ( file_exists( WP_CONTENT_DIR . '/uploads/' . $theme . '/jquery-ui.theme.min.css' ) ) {

                wp_register_style( 'tag-groups-css-frontend-theme', get_bloginfo( 'wpurl' ) . '/wp-content/uploads/' . $theme . '/jquery-ui.theme.min.css', array(), TAG_GROUPS_VERSION );

              } else if ( file_exists( WP_CONTENT_DIR . '/uploads/' . $theme . '/jquery-ui.theme.css' ) ) {

                wp_register_style( 'tag-groups-css-frontend-theme', get_bloginfo( 'wpurl' ) . '/wp-content/uploads/' . $theme . '/jquery-ui.theme.css', array(), TAG_GROUPS_VERSION );

              } else {
                /*
                * Fallback: Is this a custom theme of an old version?
                */
                try {

                  $dh = opendir( WP_CONTENT_DIR . '/uploads/' . $theme );

                } catch ( ErrorException $e ) {

                  error_log( '[Tag Groups] Error searching ' . WP_CONTENT_DIR . '/uploads/' . $theme );

                }

                if ( $dh ) {

                  while ( false !== ( $filename = readdir( $dh ) ) ) {

                    if ( preg_match( "/jquery-ui-\d+\.\d+\.\d+\.custom\.(min\.)?css/i", $filename ) ) {

                      wp_register_style( 'tag-groups-css-frontend-theme', get_bloginfo( 'wpurl' ) . '/wp-content/uploads/' . $theme . '/' . $filename, array(), TAG_GROUPS_VERSION );

                      break;

                    }
                  }
                }
              }
            }

            wp_enqueue_style( 'tag-groups-css-frontend-structure' );

            wp_enqueue_style( 'tag-groups-css-frontend-theme' );

          }
        }



        /**
        * Loads text domain for internationalization
        */
        static function register_textdomain() {

          load_plugin_textdomain( 'tag-groups', false, TAG_GROUPS_PLUGIN_RELATIVE_PATH . '/languages/' );

        }


        /**
        * @deprecated since 0.31.1
        * moved out to be better usable in uninstall.php
        *
        * @param void
        * @return array
        */
        function get_option_names() {

          $tagGroups_options = new TagGroups_Options();

          return $tagGroups_options->get_option_names();

        }


        /**
        * Checks if an admin notice is pending and, if necessary, display it
        *
        * @deprecated since 0.38.9; use class TagGroups_Admin_Notice
        *
        * @param void
        * @return void
        */
        static function admin_notice() {

          error_log( '[Tag Groups] Deprecated: TagGroups_Base::admin_notice()' );

          $notice = get_option( 'tag_group_admin_notice', array() );

          if ( ! empty( $notice ) ) {

            $html ='<div class="notice notice-' . $notice['type'] . ' is-dismissible"><p>' .
            $notice['content'] .
            '</p></div><div clear="all" /></div>';

            echo $html;

            update_option( 'tag_group_admin_notice', array() );

          }


        }


        /**
        * Displays the message for first-time users
        *
        * Not to be triggered by on_activation so that localization can work
        *
        * @deprecated 0.38.9
        *
        * @param void
        * @return void
        */
        public static function display_onboarding_message()
        {

          $settings_link = admin_url( 'options-general.php?page=tag-groups-settings' );

          if ( defined( 'TAG_GROUPS_PREMIUM_VERSION' ) ) {

            $documentation_link = 'https://documentation.chattymango.com/documentation/tag-groups-premium/';

          } else {

            $documentation_link = 'https://documentation.chattymango.com/documentation/tag-groups/';

          }


          $html ='<div class="notice notice-info is-dismissible"><p>' .
          '<h3>' . __( 'Thank you for installing Tag Groups!', 'tag-groups' ) . '</h3>' .
          '<p>' . __( 'Get started in 3 easy steps:', 'tag-groups' ) . '</p>' .
          '<ol>
          <li>' . sprintf( __( 'Go to the <span class="dashicons dashicons-admin-settings"></span>&nbsp;<a %s>settings</a> and select the <b>taxonomy</b> of your tags. In most cases just leave the default: post_tag.', 'tag-groups' ), 'href="' . $settings_link . '"' ) . '</li>
          <li>' . __( 'Go to the <span class="dashicons dashicons-index-card"></span>&nbsp;<b>Tag Groups</b> page and create some groups. The default location of this page is under <span class="dashicons dashicons-admin-post"></span>Posts.', 'tag-groups' ) . '</li>
          <li>' . __( 'Go to your <b>tags</b> and assign them to these groups.', 'tag-groups' ) . '</li>
          </ol>
          <p>' . sprintf( __( 'Now your tags are organized in groups. You can use them, for example, in a tag cloud. Just insert a shortcode into a page or post - try: [tag_groups_cloud]. You find all shortcodes and <a %s>links to the documentation</a> in the <span class="dashicons dashicons-admin-settings"></span> settings.', 'tag-groups' ), 'href="' . $documentation_link . '?pk_campaign=tg&pk_kwd=onboarding" target="_blank"' ) . '</p>
          <p>' . __( 'Happy tagging!', 'tag-groups' ) . '</p>' .
          '</p></div><div clear="all" /></div>';

          echo $html;

        }


        /**
        * Returns the first element of an array without changing the original array
        *
        * @param array $array
        * @return array
        */
        public static function get_first_element( $array = array() )
        {

          return reset( $array );

        }


        /**
        * sanitizes many classes separated by space
        *
        * @param string $classes
        * @return string
        */
        public static function sanitize_html_classes( $classes ){

          // replace multiple spaces by one
          $classes = preg_replace( '!\s+!', ' ', $classes );

          // turn into array
          $classes = explode( ' ', $classes );

          if( ! empty( $classes ) ) {

            $classes = array_map( 'sanitize_html_class', $classes );

          }

          // turn back
          $classes = implode( ' ', $classes );

          return $classes;

        }

      } // class

    }
