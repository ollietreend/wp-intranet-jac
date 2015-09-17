<?php

namespace Scraper\WordPress\Importer;

use Scraper\Page as ScraperPage;

class Page extends Base {
    /**
     * @param ScraperPage $page
     * @return int
     * @throws \Exception
     */
    public function import(ScraperPage $page) {
        if ($this->isAlreadyImported($page)) {
            if ($this->skipExisting) {
                // Stop processing
                $query = $this->wpQueryExistingPost($page);
                $postId = $query->posts[0]->ID;
                $this->setWpPostId($page, $postId);
                return true;
            } else {
                // Delete existing post and continue with import
                $this->deleteExisting($page);
            }
        }

        $postId = $this->insertPost($page);
        $this->insertPostMeta($postId, $page);
        $this->setWpPostId($page, $postId);
        return $postId;
    }

    /**
     * Create WordPress post for supplied ScraperPage.
     * Return the created post ID, or throw exception on failure.
     *
     * @param ScraperPage $page
     * @return int
     * @throws \Exception
     */
    public function insertPost(ScraperPage $page) {
        $importPost = [
            'post_title' => $page->getContent()->getTitle(),
            'post_content' => $page->getContent()->getBody(),
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => $this->authorId,
            'post_parent' => 25,
        ];

        $postId = wp_insert_post($importPost, true);

        if (!is_int($postId)) {
            $message = 'Unable to create WordPress post for page ' . $page->relativeUrl . '. Failed with error(s): ';
            $message .= join(', ', $postId->get_error_messages());
            throw new \Exception($message);
        } else {
            return $postId;
        }
    }

    /**
     * Insert meta fields for the supplied page.
     *
     * @param int $postId – The WordPress post ID
     * @param ScraperPage $page – The page object
     */
    public function insertPostMeta($postId, ScraperPage $page) {
        $metaFields = $this->getPostMetaFields($page);

        foreach ($metaFields as $name => $value) {
            update_post_meta($postId, $name, $value);
        }
    }

    public function getPostMetaFields(ScraperPage $page, $extraFields = []) {
        return parent::getPostMetaFields($page, [
            'reddot_url' => $page->relativeUrl,
        ]);
    }

    public function deleteExisting(ScraperPage $page) {
        $query = $this->wpQueryExistingPost($page);

        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                wp_delete_post($post->ID, true);
            }

            return true;
        }

        return false;
    }

    private function wpQueryExistingPost(ScraperPage $page) {
        $metaFields = $this->getPostMetaFields($page);

        $queryArgs = [
            'post_type' => 'page',
            'meta_query' => [],
        ];

        foreach ($metaFields as $key => $value) {
            $queryArgs['meta_query'][] = [
                'key' => $key,
                'value' => $value,
                'compare' => '=',
            ];
        }

        return new \WP_Query($queryArgs);
    }

    public function isAlreadyImported(ScraperPage $page) {
        $query = $this->wpQueryExistingPost($page);
        return $query->have_posts();
    }

    private function setWpPostId(ScraperPage $page, $wpPostId) {
        $page->wpPostId = $wpPostId;
    }

    /**
     * @param ScraperPage[] $pages
     */
    public function setPostIdForPages($pages) {
        foreach ($pages as $page) {
            if (isset($page->wpPostId)) {
                continue;
            }

            $query = $this->wpQueryExistingPost($page);
            if ($query->have_posts()) {
                $page->wpPostId = $query->posts[0]->ID;
            }
        }
    }
}