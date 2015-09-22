<?php

namespace Roots\Sage\Extras;

use Roots\Sage\Config;

/**
 * Add <body> classes
 */
function body_class($classes) {
  // Add page slug if it doesn't exist
  if (is_single() || is_page() && !is_front_page()) {
    if (!in_array(basename(get_permalink()), $classes)) {
      $classes[] = basename(get_permalink());
    }
  }

  // Add class if sidebar is active
  if (Config\display_sidebar()) {
    $classes[] = 'sidebar-primary';
  }

  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\body_class');

/**
 * Clean up the_excerpt()
 */
function excerpt_more() {
  return ' &hellip; <a href="' . get_permalink() . '">' . __('Continued', 'sage') . '</a>';
}
add_filter('excerpt_more', __NAMESPACE__ . '\\excerpt_more');

/**
 * Add image sizes
 */
function add_image_sizes() {
  set_post_thumbnail_size(90, 90, true);
  add_image_size('sidebar-image', 202);
  add_image_size('banner-image', 222, 140, true);
}
add_action('after_setup_theme', __NAMESPACE__ . '\\add_image_sizes');

/**
 * Default settings for Breadcumb NavXT plugin
 *
 * @param $settings
 * @return mixed
 */
function bcn_settings_init($settings) {
  $settings['Hhome_template'] = '<span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" title="Go to %title%." href="%link%" class="%type%">Home</a></span>';
  $settings['Hhome_template_no_anchor'] = '<span typeof="v:Breadcrumb"><span property="v:title">Home</span></span>';

  return $settings;
}
add_filter('bcn_settings_init', __NAMESPACE__ . '\\bcn_settings_init');

/**
 * Generate a file download link
 * Given a file array, return a link in the format:
 * "Download 1.77Mb (PDF)"
 */
class FileDownloadLink {
  public $file = null;

  public $post = null;

  public function __construct($post) {
    $file = [];
    $file['url'] = wp_get_attachment_url($post->ID);
    $file['path'] = get_attached_file($post->ID);
    $file['filetype'] = wp_check_filetype($file['path']);
    $file['filesize'] = filesize($file['path']);
    $this->file = $file;
  }

  public function __toString() {
    $divClass = 'Icon' . strtoupper($this->file['filetype']['ext']);
    $text = $this->linkText();
    $url = $this->file['url'];

    $output = '<div class="' . $divClass . ' file_download_link">';
    $output .= '<a title="This document will open in a new window" href="' . $url . '" target="_blank">' . $text . '</a>';
    $output .= '</div>';

    return $output;
  }

  public function linkText() {
    $size = size_format($this->file['filesize']);
    $ext = strtoupper($this->file['filetype']['ext']);
    return sprintf('Download %s (%s)', $size, $ext);
  }
}

/**
 * Filter archive titles
 */
function get_the_archive_title($title) {
  return preg_replace('/^(Month|Year):/', 'News Archive:', $title);
}
add_filter('get_the_archive_title', __NAMESPACE__ . '\\get_the_archive_title');
