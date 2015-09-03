<?php

namespace Roots\Sage\Extras;

use Roots\Sage\Config;

/**
 * Add <body> classes
 */
function body_class($classes) {
  // Add page slug if it doesn't exist
  if (is_single() || is_page() && !is_front_page()) {
    if (!in_array(basename(get_permalink()), $classes)) {
      $classes[] = basename(get_permalink());
    }
  }

  // Add class if sidebar is active
  if (Config\display_sidebar()) {
    $classes[] = 'sidebar-primary';
  }

  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\body_class');

/**
 * Clean up the_excerpt()
 */
function excerpt_more() {
  return ' &hellip; <a href="' . get_permalink() . '">' . __('Continued', 'sage') . '</a>';
}
add_filter('excerpt_more', __NAMESPACE__ . '\\excerpt_more');

function topnav_widget_nav_menu_args($nav_menu_args, $nav_menu, $args) {
  if ($args['id'] == 'topnav') {
    // Set menu walker
  }

  return [$nav_menu_args, $nav_menu, $args];
}
add_filter('widget_nav_menu_args', __NAMESPACE__ . '\\topnav_widget_nav_menu_args', 10, 3);

/**
 * Add image sizes
 */
function add_image_sizes() {
  add_image_size('sidebar-image', 202);
}
add_action('after_setup_theme', __NAMESPACE__ . '\\add_image_sizes');
