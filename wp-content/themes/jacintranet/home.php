<?php

$query = new WP_Query([
  'p' => intval(get_option('page_for_posts')),
  'post_type' => 'page',
]);

$query->the_post();

get_template_part('templates/page', 'header');
get_template_part('templates/content', 'page');
