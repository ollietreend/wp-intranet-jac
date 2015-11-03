<footer class="content-info" role="contentinfo">
  <div class="container">
    <?php dynamic_sidebar('sidebar-footer'); ?>
  </div>
</footer>

<div id="footer">
  <?php
  if (has_nav_menu('footer_navigation')) {
    wp_nav_menu(['theme_location' => 'footer_navigation']);
  }
  ?>
</div>
