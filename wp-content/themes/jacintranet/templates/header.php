<?php /*<header class="banner" role="banner">
  <div class="container">
    <a class="brand" href="<?= esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
    <nav role="navigation">
      <?php
      if (has_nav_menu('primary_navigation')) :
        wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav']);
      endif;
      ?>
    </nav>
  </div>
</header>*/ ?>

<div id="topnav">
  <?php

  if (is_active_sidebar('topnav')) {
    echo '<ul>';
    dynamic_sidebar('topnav');
    echo '</ul>';
  }
  ?>
</div>

<div id="banner">
  <?php if (is_front_page()): ?>
  <h1>Judicial Appointments Commission</h1>
  <?php endif; ?>
  <p>
    <a title="Judicial Appointments Commission" href="<?php echo home_url(); ?>">
      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/images-structure/home_logo.jpg" alt="Judicial Appointments Commission"  />
    </a>
  </p>
</div>

<div id="Accesslinks">
  <ul>
    <li><a href="#PageWrapper" accesskey="s">Skip Navigation</a></li>
    <li><a href="<?php echo home_url(); ?>" accesskey="1">Go to the home page</a></li>
    <li><a href="<?php // @TODO: Link to help page ?>" accesskey="6">Go to the help page</a></li>
    <li><a href="<?php // @TODO: Link to accessibility statement page ?>" accesskey="0">Go to the accessibility statement page</a></li>
  </ul>
</div>
