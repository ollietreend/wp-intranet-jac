<?php

the_post();
get_template_part('templates/banners');

if (get_the_content()) {
  the_content();
  echo '<hr/>';
}

// Recent news posts
$news_posts = new WP_Query([
  'post_type' => 'post',
  'date_query' => [
    'after' => '-1 month',
  ],
]);

while ($news_posts->have_posts()) {
  $news_posts->the_post();
  get_template_part('templates/content');
}
wp_reset_postdata();

?>
<p>Older news is available in the <a href="<?php echo get_permalink(get_option('page_for_posts')); ?>" title="See older posts in the news archive">news archive</a>.</p>
