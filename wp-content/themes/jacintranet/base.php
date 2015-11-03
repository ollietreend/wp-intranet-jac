<?php

use Roots\Sage\Config;
use Roots\Sage\Wrapper;

?>
<!doctype html>
<html class="no-js" <?php language_attributes(); ?>>
  <?php get_template_part('templates/head'); ?>
  <body <?php body_class(); ?>>
    <div id="outerwrapper">
      <div id="innerwrapper">
        <?php
          do_action('get_header');
          get_template_part('templates/header');
        ?>

        <?php if (!is_front_page()): ?>
          <div id="breadcrumbs">
            <ul>
              <?php echo bcn_display_list(); ?>
            </ul>
          </div>
        <?php endif; ?>

        <div id="PageWrapper">
          <div id="PageContent" role="main">
            <?php

            if (!is_front_page()) {
              echo '<div class="wrap nested">';
            }

            include Wrapper\template_path();

            if (!is_front_page()) {
              echo '</div>';
            }

            ?>
          </div>

          <div id="PageLeft">
            <div class="LeftNav">
              <?php
              if (has_nav_menu('primary_navigation')) {
                wp_nav_menu([
                  'theme_location' => 'primary_navigation',
                  'walker' => new \JACIntranet\Theme\Nav\LeftNavWalker(),
                  'items_wrap' => '%3$s',
                ]);
              }
              ?>
            </div>
          </div>
        </div>

        <?php if (Config\display_sidebar()): ?>
          <?php include Wrapper\sidebar_path(); ?>
        <?php endif; ?>

        <?php
          do_action('get_footer');
          get_template_part('templates/footer');
        ?>
      </div>
    </div>
    <?php wp_footer(); ?>
  </body>
</html>
