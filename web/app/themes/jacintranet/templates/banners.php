<?php if (have_rows('banners')): ?>
  <div id="PromotionWrapper">
    <?php while (have_rows('banners')): the_row(); ?>
      <div class="CandidatePromotion">
        <?php

        $image = get_sub_field('image');
        $heading = get_sub_field('heading');
        $content = get_sub_field('content');

        switch (get_sub_field('link_type')) {
          case 'external':
            $link = get_sub_field('external_link');
            break;

          case 'internal':
            $link = get_sub_field('internal_link');
            break;

          default:
            $link = false;
            break;
        }

        ?>
        <img src="<?php echo $image['sizes']['banner-image']; ?>" alt="<?php echo esc_attr($heading); ?>">
        <div class="Inner">
          <div class="InnerContent">
            <?php

            if (!empty($heading)) {
              echo '<h2>' . $heading . '</h2>';
            }

            if (!empty($content)) {
              echo $content;
            }

            ?>
          </div>
          <?php if ($link): ?>
            <div class="Anchor">
              <a href="<?php echo $link; ?>">Read more <span><?php echo $heading; ?></span></a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
<?php endif; ?>
