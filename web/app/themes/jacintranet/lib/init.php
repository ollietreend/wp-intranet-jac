<?php

namespace Roots\Sage\Init;

use Roots\Sage\Assets;

require 'classes/leftnav-walker.php';
require 'classes/moj-user-roles.php';

/**
 * Theme setup
 */
function setup() {
  // Make theme available for translation
  // Community translations can be found at https://github.com/roots/sage-translations
  load_theme_textdomain('sage', get_template_directory() . '/lang');

  // Enable plugins to manage the document title
  // http://codex.wordpress.org/Function_Reference/add_theme_support#Title_Tag
  add_theme_support('title-tag');

  // Register wp_nav_menu() menus
  // http://codex.wordpress.org/Function_Reference/register_nav_menus
  register_nav_menus([
    'primary_navigation' => 'Primary Navigation',
    'top_navigation'     => 'Top Navigation',
    'footer_navigation'  => 'Footer Navigation',
  ]);

  // Add post thumbnails
  // http://codex.wordpress.org/Post_Thumbnails
  // http://codex.wordpress.org/Function_Reference/set_post_thumbnail_size
  // http://codex.wordpress.org/Function_Reference/add_image_size
  add_theme_support('post-thumbnails');

  // Add HTML5 markup for captions
  // http://codex.wordpress.org/Function_Reference/add_theme_support#HTML5
  add_theme_support('html5', ['caption', 'comment-form', 'comment-list']);

  // Tell the TinyMCE editor to use a custom stylesheet
  add_editor_style(Assets\asset_path('styles/editor-style.css'));
}
add_action('after_setup_theme', __NAMESPACE__ . '\\setup');

/**
 * Register sidebars
 */
function widgets_init() {
  register_sidebar([
    'name'          => __('Primary', 'sage'),
    'id'            => 'sidebar-primary',
    'before_widget' => '<div class="GenericRight widget %1$s %2$s">',
    'after_widget'  => '</div>',
    'before_title'  => '<h3>',
    'after_title'   => '</h3>'
  ]);

  register_sidebar([
    'name'          => 'Top Navigation Bar',
    'id'            => 'topnav',
    'before_widget' => '<li>',
    'after_widget'  => '</li>',
    'before_title'  => '',
    'after_title'   => ''
  ]);
}
add_action('widgets_init', __NAMESPACE__ . '\\widgets_init');

/**
 * Unregister category and tag taxonomies.
 */
function unregister_categories_and_tags() {
  register_taxonomy('category', array());
  register_taxonomy('post_tag', array());
}
add_action('init', __NAMESPACE__ . '\\unregister_categories_and_tags');

/**
 * Define user roles
 */
new \MOJ_User_Roles();

/**
 * Remove Comments functionality
 */
// Removes from admin menu
function admin_menu_remove_comments() {
  remove_menu_page( 'edit-comments.php' );
}
add_action('admin_menu', __NAMESPACE__ . '\\admin_menu_remove_comments');

// Removes from post and pages
function init_remove_comments() {
  remove_post_type_support( 'post', 'comments' );
  remove_post_type_support( 'page', 'comments' );
}
add_action('init', __NAMESPACE__ . '\\init_remove_comments', 100);

// Removes from admin bar
function admin_bar_remove_comments() {
  global $wp_admin_bar;
  $wp_admin_bar->remove_menu('comments');
}
add_action('wp_before_admin_bar_render', __NAMESPACE__ . '\\admin_bar_remove_comments');
