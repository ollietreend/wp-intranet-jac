<?php

namespace Scraper\WordPress\Importer;

use Scraper\Page as ScraperPage;
use Scraper\WordPress\Post\Post as WpPost;

class Post extends Base {
    /**
     * @param array $story
     * @param ScraperPage $page
     * @return int
     * @throws \Exception
     */
    public static function import($story, ScraperPage $page) {
        $existingPost = WpPost::getByMeta([
            'reddot_import' => 1,
            'reddot_news_post_title' => $story['title'],
            // Don't find based on reddot_url because we want to find existing posts regardless of originating page.
        ]);

        if ($existingPost) {
            if (static::$skipExisting) {
                // Stop processing
                return true;
            } else {
                // Delete existing post and continue with import
                $existingPost->delete();
            }
        }

        $save = static::getSaveData($story, $page);
        $newPost = WpPost::create($save);
        return $newPost;
    }

    /**
     * Get array of data to use when creating new post.
     *
     * @param array $story
     * @param ScraperPage $page
     * @return array
     * @throws \Exception
     */
    private static function getSaveData($story, ScraperPage $page) {
        return [
            'post_title' => $story['title'],
            'post_date' => $story['date']->format('Y-m-d H:i:s'),
            'post_content' => $story['body'],
            'post_status' => 'publish',
            'post_type' => 'post',
            'post_author' => static::$authorId,
            'meta' => [
                'reddot_import' => 1,
                'reddot_url' => $page->relativeUrl,
                'reddot_news_post_title' => $story['title'],
            ],
        ];
    }
}