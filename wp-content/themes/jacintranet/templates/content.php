<div <?php post_class(); ?>>
  <div class="post_header">
    <strong class="entry-title"><?php the_title(); ?></strong> &ndash;
    <time datetime="<?= get_post_time('c', true); ?>" class="post_date" title="<?php the_time(get_option('date_format')); ?>"><?php the_time('j M'); ?></time>
  </div>
  <div class="post_content">
    <?php

    if (has_post_thumbnail()) {
      the_post_thumbnail();
    }

    the_content();

    ?>
  </div>
</div>
<hr/>
