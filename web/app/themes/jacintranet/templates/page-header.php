<?php use Roots\Sage\Titles; ?>

<?php

$headerClass = ['page_header'];
if (have_rows('file_downloads') && !is_search()) {
  $headerClass[] = 'mainContent';
}

?>

<div class="<?php echo esc_attr(join(' ', $headerClass)); ?>">
  <h1><?= Titles\title(); ?></h1>
</div>
