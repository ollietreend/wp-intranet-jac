<?php get_template_part('templates/page', 'header'); ?>

<?php if (!have_posts()) : ?>
  <p>Sorry, no results were found.</p>
<?php endif; ?>

<?php while (have_posts()) : the_post(); ?>
  <?php get_template_part('templates/content', 'search'); ?>
<?php endwhile; ?>

<?php if (isset($wp_query->max_num_pages) && $wp_query->max_num_pages > 1): ?>
  <div class="pagination pagination-search">
    <p><strong>More search results</strong></p>
    <?php echo paginate_links([
      'show_all' => true,
    ]); ?>
  </div>
<?php endif; ?>
