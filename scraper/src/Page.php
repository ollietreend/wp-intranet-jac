<?php

/**
 * Represents a single page.
 */

namespace Scraper;

use Goutte\Client;
use Scraper\Page\NavigationMenu;
use Scraper\Page\Content;

class Page {
    /**
     * Absolute URL for the page.
     *
     * @var null|string
     */
    public $url = null;

    /**
     * Page URL relative to the scrape root.
     *
     * @var null|string
     */
    public $relativeUrl = null;

    public $data = null;

    public $wpPostId = null;

    /**
     * Holds instances of objects related to this page.
     *
     * @var array
     */
    protected $objects = [];

    /**
     * Class constructor
     *
     * @param string $url
     * @param string $relativeUrl
     * @param array $data
     */
    public function __construct($url, $relativeUrl, $data) {
        $this->url = $url;
        $this->relativeUrl = $relativeUrl;
        $this->data = $data;
    }

    public function hasDownloads() {
        // @TODO
    }

    public function isFrontPage() {
        return ( $this->data['title'] == 'Judicial Appointments Commission | index' );
    }

    public function isNewsArchivePage() {
        // @TODO
    }

    /**
     * Get Crawler object for this page.
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function getCrawler() {
        if (!isset($this->objects['Crawler'])) {
            $client = new Client();
            $this->objects['Crawler'] = $client->request('GET', $this->url);
        }

        return $this->objects['Crawler'];
    }

    /**
     * Get Page\NavigationMenu object for this page.
     *
     * @return NavigationMenu
     */
    public function getNavigationMenu() {
        if (!isset($this->objects['NavigationMenu'])) {
            $this->objects['NavigationMenu'] = new NavigationMenu($this);
        }

        return $this->objects['NavigationMenu'];
    }

    /**
     * Get Page\Content object for this page.
     *
     * @return Content
     */
    public function getContent() {
        if (!isset($this->objects['Content'])) {
            $this->objects['Content'] = new Content($this);
        }

        return $this->objects['Content'];
    }
}