<?php

namespace Scraper\WordPress\Importer;

use Scraper\Page as ScraperPage;
use Scraper\WordPress\WordPress;
use Scraper\WordPress\Post\Page as WpPage;

class Page extends Base {
    /**
     * @param ScraperPage $page
     * @return int
     * @throws \Exception
     */
    public function import(ScraperPage $page) {
        $existingPost = WpPage::getByMeta([
            'reddot_import' => 1,
            'reddot_url' => $page->relativeUrl,
        ]);

        if ($existingPost) {
            if ($this->skipExisting) {
                // Stop processing
                WordPress::attachWpPageToScraperPage($page, $existingPost);
                return true;
            } else {
                // Delete existing post and continue with import
                $existingPost->delete();
            }
        }

        $save = $this->getSaveData($page);
        $newPost = WpPage::create($save);
        WordPress::attachWpPageToScraperPage($page, $newPost);
        return $newPost;
    }

    /**
     * Get array of data to use when creating new post.
     *
     * @param ScraperPage $page
     * @return array
     * @throws \Exception
     */
    private function getSaveData(ScraperPage $page) {
        return [
            'post_title' => $page->getContent()->getTitle(),
            'post_content' => $page->getContent()->getBody(),
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => $this->authorId,
            'post_parent' => 25,
            'meta' => [
                'reddot_import' => 1,
                'reddot_url' => $page->relativeUrl,
            ],
        ];
    }
}