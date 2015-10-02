<?php

/**
 * Collection To Scrape
 *
 * This class represents a collection of resources which
 * are to be scraped. This collection is generated by crawling
 * the supplied URL.
 *
 * Results of the crawl results will be cached to improve
 * performance.
 *
 * Class CollectionToScrape
 */

namespace Scraper;

use FileSystemCache;
use FileSystemCacheKey;

class CollectionToScrape {
    /**
     * The URL (resource) which is to be crawled.
     *
     * @var null|string
     */
    public $urlToSpider = null;

    /**
     * Whether to ignore the crawl cache and force a crawl.
     *
     * @var bool
     */
    public $forceSpider = false;

    /**
     * Holds the cache key used to store the crawl results.
     *
     * @var FileSystemCacheKey|null
     */
    protected $cacheKey = null;

    /**
     * Results of crawl process.
     *
     * @var null|array
     */
    protected $crawlResults = null;

    /**
     * Array of Page objects representing
     * internal pages from crawl results.
     *
     * @var null|Page[]
     */
    protected $pages = null;

    /**
     * Class constructor
     *
     * Configure object properties.
     *
     * @param $urlToSpider
     * @param bool|false $forceSpider
     * @param int $crawlDepth
     */
    public function __construct($urlToSpider, $forceSpider = false, $crawlDepth = 3) {
        $this->urlToSpider = $urlToSpider;
        $this->forceSpider = $forceSpider;
        $this->crawlDepth = $crawlDepth;
        $this->cacheKey = FileSystemCache::generateCacheKey([get_class($this), $urlToSpider, $crawlDepth]);
    }

    /**
     * Perform a crawl of the resource.
     */
    protected function crawlResource() {
        $crawler = new \Arachnid\Crawler($this->urlToSpider, $this->crawlDepth);
        $crawler->traverse();
        $this->setCrawlResults($crawler->getLinks());
    }

    /**
     * Get the results of the crawl.
     *
     * @return array|mixed|null
     */
    public function getCrawlResults() {
        if (is_null($this->crawlResults)) {
            if ($this->forceSpider || FileSystemCache::retrieve($this->cacheKey) === false) {
                $this->crawlResource();
                $this->forceSpider = false;
            } else {
                $this->crawlResults = FileSystemCache::retrieve($this->cacheKey);
            }
        }

        return $this->crawlResults;
    }

    /**
     * Set the results of the crawl.
     *
     * @param $crawlResults
     */
    public function setCrawlResults($crawlResults) {
        $this->crawlResults = $crawlResults;
        FileSystemCache::store($this->cacheKey, $crawlResults);
    }

    /**
     * Generate an array of internal pages from the spider results.
     * Each page will be represented as a Page object.
     *
     * @return Page[]
     */
    protected function generatePagesFromCrawlResults() {
        $results = $this->getCrawlResults();

        // Filter resources to find internal HTML pages
        $results = array_filter($results, function($resource) {
            // If $resource['title'] exists, we know this is a HTML page
            $hasTitle = isset($resource['title']);
            $isInternalPage = !$resource['external_link'];
            $isNotBaseUrl = !( isset($resource['links_text'][0]) && $resource['links_text'][0] == 'BASE_URL' );
            return ( $hasTitle && $isInternalPage && $isNotBaseUrl );
        });

        $pages = array_map(function($page) {
            $absoluteUrl = $page['absolute_url'];
            $relativeUrl = str_replace($this->urlToSpider, '', $absoluteUrl);
            return new Page($absoluteUrl, $relativeUrl, $page);
        }, $results);

        return $pages;
    }

    /**
     * Get the crawled page objects.
     *
     * @return Page[]
     */
    public function getPages() {
        if (is_null($this->pages)) {
            $this->pages = $this->generatePagesFromCrawlResults();
        }

        return $this->pages;
    }
}