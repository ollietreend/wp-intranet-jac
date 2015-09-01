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
  if (has_nav_menu('top_navigation')) {
    wp_nav_menu(['theme_location' => 'top_navigation', 'walker' => new \Roots\Soil\Nav\NavWalker()]);
  }
  ?>
  <ul>
    <li class="noBorder">
      <a href="directory.htm">Staff Directory</a>
    </li>
    <li class="">
      <a href="accessibility.htm">Accessibility</a>
    </li>
    <li class="">
      <a href="853.htm">MoJ peopleFinder</a>
    </li>
    <li class="">Google Search
      <form method="get" action="http://www.google.com/search">
        <label for="q">
          <input type="text" id="q" name="q" class="searchbox">
          <input id="searchButton" class="SearchBtn" type="submit" name="searchButton" value="">
        </label>
      </form>
    </li>
    <li class="">Intranet Search
      <form action="http://intranet.justice.gsi.gov.uk/SearchJAC.do" onsubmit="Search('searchText', 'Enter keywords'); return false;" method="get" name"query"="">
      <label for="searchfunction">
        <input id="searchText" class="searchbox" name="query" type="text">
        <input id="searchButton" class="SearchBtn" type="submit" name="searchButton" value="">
      </label>
      </form>
    </li>
    <li class="hidden"><a href="http://intranet.justice.gsi.gov.uk/SearchJAC.do">Search</a></li>
  </ul>
</div>
