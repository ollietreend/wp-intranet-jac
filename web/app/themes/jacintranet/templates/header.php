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
    <li><a href="/accessibility/" accesskey="6">Go to the help page</a></li>
    <li><a href="/accessibility/" accesskey="0">Go to the accessibility statement page</a></li>
  </ul>
</div>
