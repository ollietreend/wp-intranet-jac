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

/**
 * MOJ People Finder form shortcode
 */
function people_finder_form() {
  ob_start();
  ?>
  <form id="finder" name="finder" action="http://intranet-applications.dca.gsi.gov.uk/peopleFinder/UserSearch2.do?" method="get" target="_new">
    <p><label for="forename">Firstname:</label> <input id="forename" title="firstname" size="14" name="forename"><label for="lastname">Lastname:</label> <input id="surname" title="lastname" size="9" name="surname"><input id="frompage" type="hidden" value="mojdir" name="frompage"><input title="This is a link to People Finder which will open in a new browser window" accesskey="4" onclick="pageTracker._trackPageview('peopleFinder person search');" type="submit" size="11" value="Find"></p>
  </form>
  <?php
  return ob_get_clean();
}
add_shortcode('people_finder_form', __NAMESPACE__ . '\\people_finder_form');
