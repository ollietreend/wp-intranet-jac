<?php

namespace Scraper\WordPress;

use Scraper\Page as ScraperPage;
use Scraper\WordPress\Post\Page;

class WordPress {
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
                $page->setWpPost($foundPost);
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

    /**
     * Import a file into the WordPress media library.
     * Return the inserted media post ID.
     *
     * @param string $filePath Path of file to be imported.
     * @param int $associatedPostId The post ID the media is associated with
     * @param null $title Title for the sideloaded file (optional)
     * @return int ID of the media library item
     * @throws \Exception
     */
    public static function importMedia($filePath, $associatedPostId, $title = null) {
        if (!file_exists($filePath)) {
            throw new \Exception('Unable to find file for import: ' . $filePath);
        }

        // Create temporary file
        $prefix = substr(md5(__FILE__), 0, 6);
        $tmpFilePath = tempnam(sys_get_temp_dir(), $prefix);

        // Duplicate file to be imported to the temporary file path
        copy($filePath, $tmpFilePath);
        clearstatcache(true, $tmpFilePath); // PHP bug: https://bugs.php.net/bug.php?id=65701

        // Import the file into WordPress
        $fileArray = [
            'name' => basename($filePath),
            'tmp_name' => $tmpFilePath,
        ];

        $success = media_handle_sideload($fileArray, $associatedPostId, $title);

        if (!is_int($success)) {
            throw new \Exception('Error importing file: ' . $filePath);
        }

        // Remove temporary file if it still exists (WordPress would usually move this)
        if (file_exists($tmpFilePath)) {
            unlink($tmpFilePath);
        }

        return $success;
    }
}