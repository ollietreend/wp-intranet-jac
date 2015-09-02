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

    // This is an example of the use of 'is_callable' functionality. A user could - for instance -
    // have WPSEO installed *or* WPSEO Premium. The slug would in that last case be different, i.e.
    // 'wordpress-seo-premium'.
    // By setting 'is_callable' to either a function from that plugin or a class method
    // `array( 'class', 'method' )` similar to how you hook in to actions and filters, TGMPA can still
    // recognize the plugin as being installed.
    array(
      'name'        => 'WordPress SEO by Yoast',
      'slug'        => 'wordpress-seo',
      'is_callable' => 'wpseo_init',
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
