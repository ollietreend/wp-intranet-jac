<?php

namespace Scraper\WordPress;

use Scraper\Page as ScraperPage;
use Scraper\WordPress\Post\Page;

class WordPress extends Base {
    /**
     * Attach WordPress Page objects to ScraperPage objects.
     *
     * @param ScraperPage[] $pages
     * @return void
     */
    public static function attachWpPagesToScraperPages($pages) {
        foreach ($pages as $page) {
            if (isset($page->wpPostId) && isset($page->wpPost)) {
                continue;
            }

            $foundPost = Page::getByMeta([
                'reddot_import' => 1,
                'reddot_url' => $page->relativeUrl,
            ]);

            if ($foundPost) {
                static::attachWpPageToScraperPage($page, $foundPost);
            }
        }
    }

    /**
     * Attach a WordPress Page object to the ScraperPage.
     *
     * @param ScraperPage $scraperPage
     * @param Page $wpPage
     */
    public static function attachWpPageToScraperPage(ScraperPage $scraperPage, Page $wpPage) {
        $scraperPage->wpPostId = $wpPage->WP_Post->ID;
        $scraperPage->wpPost = $wpPage;
    }
}