<?php

namespace Scraper\WordPress\Rewriter\Element;

use Scraper\WordPress\Rewriter\AssetRewriter;
use Scraper\WordPress\Post\Page;
use Scraper\WordPress\Post\Attachment;
use Scraper\WordPress\Importer\Attachment as AttachmentImporter;
use \DOMElement;

class Base {
    /**
     * The element attribute to be rewritten.
     *
     * @var null
     */
    public static $rewriteAttr = null;

    /**
     * The element which is being rewritten.
     *
     * @var DOMElement $element
     */
    public $element = null;

    /**
     * URL of element which is to be rewritten.
     *
     * @var string
     */
    public $url = null;

    /**
     * The WpPage object which contains the element which is being rewritten.
     *
     * @var Page $originatingPage
     */
    public $originatingPage = null;

    /**
     * Class constructor
     *
     * @param DOMElement $element
     * @param Page $originatingPage
     */
    public function __construct($element, $originatingPage) {
        $this->element = $element;
        $this->url = $element->getAttribute(static::$rewriteAttr);
        $this->originatingPage = $originatingPage;
    }

    /**
     * Determine if this element's needs rewriting.
     *
     * @return bool
     */
    public function shouldBeRewritten() {
        // If the URL looks like it's already been rewritten, don't rewrite again.
        // There's definite room for improvement here – but this works for now.
        if (
            preg_match('/\/wp-content\/uploads\//i', $this->url) ||
            preg_match('/\/$/', $this->url)
        ) {
            return false;
        }

        // Check if it starts with a specified base URL.
        foreach ($this->getBaseUrls() as $baseUrl) {
            if (stripos($this->url, $baseUrl) === 0) {
                return true;
            }
        }

        // Check if URL begins with a protocol, "www." or "//" (protocol-relative).
        if (!preg_match('/^([a-z][a-z0-9+\.-]*:|www\.|\/\/)/i', $this->url)) {
            return true;
        }

        // If none of the above, then we don't want to rewrite it.
        return false;
    }

    /**
     * Rewrite the attribute value to reference the new URL.
     */
    public function rewrite() {
        try {
            $newAttributeValue = $this->getNewAttributeValue();
            $this->element->setAttribute(static::$rewriteAttr, $newAttributeValue);
        } catch (\Exception $e) {
            trigger_error('Failed with error: "' . $e->getMessage() . '" for URL: ' . $this->url);
        }
    }

    /**
     * Get the new href for the link.
     *
     * @return false|string
     */
    public function getNewAttributeValue() {
        $post = $this->mapToNewPost();
        $newHref = $post->getUrl();
        return $newHref;
    }

    /**
     * Map link to new WordPress post.
     *
     * @return \WP_Post
     * @throws \Exception
     */
    protected function mapToNewPost() {
        $meta = [
            'reddot_import' => true,
            'reddot_url' => $this->relativeUrl(),
        ];

        if ($this->isUrlToAsset()) {
            $post = Attachment::getByMeta($meta);
            if (empty($post)) {
                $post = $this->importAsset();
            }
        } else {
            $post = Page::getByMeta($meta);
        }

        if (!$post) {
            throw new \Exception('Matching post not found.');
        }

        return $post;
    }

    /**
     * If we have a URL to an asset file.
     *
     * @return int
     */
    public function isUrlToAsset() {
        $url = $this->relativeUrl();
        return preg_match('/^static\//i', $url);
    }

    /**
     * Import the new asset.
     *
     * @return bool|int
     * @throws \Exception
     */
    protected function importAsset() {
        if (!$this->isUrlToAsset()) {
            return false;
        }

        $path = $this->relativeUrl();
        $associatedPostId = $this->originatingPage->WP_Post->ID;

        return AttachmentImporter::import($path, $associatedPostId);
    }

    /**
     * Normalize the URL to be relative to the base of the scraper URL.
     *
     * @return string
     */
    public function relativeUrl() {
        $url = $this->url;

        // Remove base URL if there is one
        foreach ($this->getBaseUrls() as $baseUrl) {
            if (stripos($url, $baseUrl) === 0) {
                $url = substr($url, strlen($baseUrl));
                break;
            }
        }

        return $url;
    }

    /**
     * Get baseUrls
     *
     * @return array
     */
    protected function getBaseUrls() {
        return AssetRewriter::$baseUrls;
    }
}