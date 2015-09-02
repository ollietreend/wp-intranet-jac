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
        <div class="wrap container" role="document">
          <div class="content row">
            <main class="main" role="main">
              <?php include Wrapper\template_path(); ?>
            </main><!-- /.main -->
            <?php if (Config\display_sidebar()) : ?>
              <aside class="sidebar" role="complementary">
                <?php include Wrapper\sidebar_path(); ?>
              </aside><!-- /.sidebar -->
            <?php endif; ?>
          </div><!-- /.content -->
        </div><!-- /.wrap -->
        <?php
          do_action('get_footer');
          get_template_part('templates/footer');
        ?>
      </div>
    </div>
    <?php wp_footer(); ?>
  </body>
</html>
