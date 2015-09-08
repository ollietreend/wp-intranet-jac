<?php

namespace Roots\Sage\Shortcodes;

/**
 * News archive links shortcode
 */
function archive_links() {
  $links =  wp_get_archives([
    'type' => 'monthly',
    'echo' => false,
  ]);

  return '<ul>' . $links . '</ul>';
}
add_shortcode('archive_links', __NAMESPACE__ . '\\archive_links');
