<?php

the_post();
get_template_part('templates/banners');
the_content();

// Recent news posts
$posts = new WP_Query([
  'post_type' => 'post',
  'date_query' => [
    'after' => '-1 month',
  ],
]);

while ($posts->have_posts()) {
  $posts->the_post();
  get_template_part('templates/content');
}
wp_reset_postdata();

?>
<p>Older news is available in the <a href="#" title="See older posts in the news archive">news archive</a>.</p>
