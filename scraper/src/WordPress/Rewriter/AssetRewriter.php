<?php

namespace Scraper\WordPress\Rewriter;

use \DOMDocument;
use Scraper\WordPress\Post\Base as WpPost;
use Scraper\WordPress\Rewriter\Element\Link;
use Scraper\WordPress\Rewriter\Element\Image;

class AssetRewriter {
    /**
     * Array of base URLs which internal assets could start with.
     *
     * @var array
     */
    public static $baseUrls = [];

    /**
     * Rewrite inline page assets.
     *
     * @param WpPost[] $wpPosts
     */
    public static function rewrite($wpPosts) {
        foreach ($wpPosts as $wpPost) {
            // Create DOMDocument object
            $doc = new DOMDocument();
            $doc->loadHTML($wpPost->WP_Post->post_content);

            // Perform rewrites
            self::rewriteLinks($doc, $wpPost);
            self::rewriteImages($doc, $wpPost);

            // Grab modified HTML and update WpPage object
            $bodyTag = $doc->getElementsByTagName('body')->item(0);
            $html = $doc->saveHTML($bodyTag);
            $html = preg_replace('/\s?<\/?body>\s?/', '', $html);
            $wpPost->save([
                'post_content' => $html,
            ]);
        }
    }

    /**
     * Rewrite inline page links.
     *
     * @param DOMDocument $document
     * @param WpPost $wpPost
     */
    private static function rewriteLinks(DOMDocument $document, WpPost $wpPost) {
        $links = $document->getElementsByTagName('a');

        foreach ($links as $linkElement) {
            $link = new Link($linkElement, $wpPost);

            if ($link->shouldBeRewritten()) {
                $link->rewrite();
            }
        }
    }

    /**
     * Rewrite inline page images.
     *
     * @param DOMDocument $document
     * @param WpPost $wpPost
     */
    private static function rewriteImages(DOMDocument $document, WpPost $wpPost) {
        $images = $document->getElementsByTagName('img');

        foreach ($images as $imageElement) {
            $image = new Image($imageElement, $wpPost);

            if ($image->shouldBeRewritten()) {
                $image->rewrite();
            }
        }
    }
}