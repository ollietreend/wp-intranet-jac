<?php

namespace Scraper\WordPress\Importer;

use Scraper\Page as ScraperPage;
use Scraper\WordPress\WordPress;
use Scraper\WordPress\Post\Page as WpPage;
use Scraper\WordPress\Post\Attachment as WpAttachment;

class Page extends Base {
    /**
     * @param ScraperPage $page
     * @return int
     * @throws \Exception
     */
    public static function import(ScraperPage $page) {
        $existingPost = WpPage::getByMeta([
            'reddot_import' => 1,
            'reddot_url' => $page->relativeUrl,
        ]);

        if ($existingPost) {
            if (static::$skipExisting) {
                // Stop processing
                $page->setWpPost($existingPost);
                return true;
            } else {
                // Delete existing post and continue with import
                $existingPost->delete();
            }
        }

        $save = static::getSaveData($page);
        $newPost = WpPage::create($save);
        $page->setWpPost($newPost);
        return $newPost;
    }

    /**
     * Get array of data to use when creating new post.
     *
     * @param ScraperPage $page
     * @return array
     * @throws \Exception
     */
    private static function getSaveData(ScraperPage $page) {
        return [
            'post_title' => $page->getContent()->getTitle(),
            'post_content' => $page->getContent()->getBody(),
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => static::$authorId,
            'meta' => [
                'reddot_import' => 1,
                'reddot_url' => $page->relativeUrl,
            ],
        ];
    }
}