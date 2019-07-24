<?php
require_once 'partials/headers.php';
// echo get_template_directory_uri() . '/partials/headers.php';

add_action('after_setup_theme', 'uncode_language_setup');
function uncode_language_setup()
{
  load_child_theme_textdomain('uncode', get_stylesheet_directory() . '/languages');
}

function theme_enqueue_styles()
{
  $production_mode = ot_get_option('_uncode_production');
  $resources_version = ($production_mode === 'on') ? null : rand();
  $parent_style = 'uncode-style';
  $child_style = array('uncode-custom-style');
  $in_footer = true;
  wp_enqueue_style($parent_style, get_template_directory_uri() . '/library/css/style.css', array(), $resources_version);
  wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', $child_style, $resources_version);
  wp_enqueue_script('child-scripts', get_stylesheet_directory_uri() . '/app.js', array( 'jquery' ), $resources_version, $in_footer);
}
add_action('wp_enqueue_scripts', 'theme_enqueue_styles');

add_action('admin_head', 'my_custom_fonts');

function my_custom_fonts() {
  echo '<style>
  #TB_overlay {
    z-index: 60000 !important;
  }

  body.pardot-modal-open #TB_window {
    z-index: 60001 !important;
  }
  </style>';
}

add_rewrite_rule('^take-action/attend-an-event/page/([0-9]+)','index.php?pagename=take-action/attend-an-event&paged=$matches[1]', 'top');
