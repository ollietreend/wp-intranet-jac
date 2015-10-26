<?php

/**
 * Register the required plugins for this theme.
 */

require 'classes/class-tgm-plugin-activation.php';

add_action('tgmpa_register', 'jacintranet_register_required_plugins');
function jacintranet_register_required_plugins() {
  /*
   * Array of plugin arrays. Required keys are name and slug.
   * If the source is NOT from the .org repo, then source is also required.
   */
  $plugins = array(

    array(
      'name'        => 'Soil',
      'slug'        => 'soil',
      'required'    => true,
      'source'      => 'https://github.com/roots/soil/archive/3.4.0.zip',
      'is_callable' => 'Roots\Soil\load_modules',
    ),

    array(
      'name'        => 'Simple Image Widget',
      'slug'        => 'simple-image-widget',
      'required'    => true,
    ),

    array(
      'name'        => 'Advanced Custom Fields Pro',
      'slug'        => 'advanced-custom-fields-pro',
      'source'      => get_stylesheet_directory() . '/lib/plugins/advanced-custom-fields-pro.zip',
      'required'    => true,
    ),

    array(
      'name'        => 'Breadcrumb NavXT',
      'slug'        => 'breadcrumb-navxt',
      'required'    => true,
    ),

    array(
      'name'        => 'TinyMCE Advanced',
      'slug'        => 'tinymce-advanced',
      'required'    => false,
    ),

    array(
      'name'        => 'Google Analytics Dashboard for WP',
      'slug'        => 'google-analytics-dashboard-for-wp',
      'required'    => false,
    ),

  );

  /*
   * Array of configuration settings.
   */
  $config = array(
    'id'           => 'tgmpa',                 // Unique ID for hashing notices for multiple instances of TGMPA.
    'default_path' => '',                      // Default absolute path to bundled plugins.
    'menu'         => 'tgmpa-install-plugins', // Menu slug.
    'parent_slug'  => 'themes.php',            // Parent menu slug.
    'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
    'has_notices'  => true,                    // Show admin notices or not.
    'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
    'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
    'is_automatic' => false,                   // Automatically activate plugins after installation or not.
    'message'      => '',                      // Message to output right before the plugins table.
  );

  tgmpa($plugins, $config);
}
