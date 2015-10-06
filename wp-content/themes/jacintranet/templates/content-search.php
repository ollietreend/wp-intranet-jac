<div <?php post_class(); ?>>
  <div class="post_header">
    <a href="<?php the_permalink(); ?>"><strong class="entry-title"><?php the_title(); ?></strong></a>
    <?php if (get_post_type() === 'post'): ?>
      &ndash; <time datetime="<?= get_post_time('c', true); ?>" class="post_date" title="<?php the_time(get_option('date_format')); ?>"><?php the_time('j M Y'); ?></time>
    <?php endif; ?>
  </div>
  <div class="post_content">
    <?php the_excerpt(); ?>
  </div>
</div>
<hr/>
