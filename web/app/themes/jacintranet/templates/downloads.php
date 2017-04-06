<?php if (have_rows('file_downloads')): ?>
<div class="PanelsRight">
  <div class="GenericRight">
    <div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/images-structure/Downloads.gif" alt="Downloads"></div>
    <h2>Downloads</h2>

    <ul>
      <?php while (have_rows('file_downloads')): the_row(); ?>
        <?php
        $fileId = get_sub_field('file');
        $file = get_post($fileId);
        $url = wp_get_attachment_url($fileId);
        $title = $file->post_title;
        ?>
        <li>
          <div class="Headline">
            <a title="This document will open in a new window" href="<?php echo $url; ?>" target="_blank"><?php echo $title; ?></a>
          </div>
          <?php echo new \Roots\Sage\Extras\FileDownloadLink($file); ?>
        </li>
      <?php endwhile; ?>
    </ul>
  </div>
</div>
<?php endif; ?>
