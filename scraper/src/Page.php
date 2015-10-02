<?php

/**
 * Represents a single page.
 */

namespace Scraper;

use Goutte\Client;
use Scraper\Page\NavigationMenu;
use Scraper\Page\Content;
use Scraper\WordPress\Post\Page as WpPage;

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

    /**
     * Holds the WordPress post ID
     *
     * @var int
     */
    public $wpPostId = null;

    /**
     * Holds the post object
     *
     * @var WpPage
     */
    public $wpPost = null;

    /**
     * Holds instances of objects related to this page.
     *
     * @var array
     */
    protected $objects = [];

    /**
     * Cache of page properties.
     * Will be populated by methods with their respective names – e.g. $this->hasDownloads()
     *
     * @var array
     */
    protected $properties = [
        'hasDownloads' => null,
        'isFrontPage' => null,
        'isNewsArchivePage' => null,
    ];

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

    /**
     * Return whether this page has download links.
     *
     * @return boolean
     */
    public function hasDownloads() {
        if (is_null($this->properties['hasDownloads'])) {
            $crawler = $this->getCrawler();
            $h2 = $crawler->filter('.PanelsRight > .GenericRight h2');
            $this->properties['hasDownloads'] = ( count($h2) > 0 && $h2->text() == 'Downloads' );
        }

        return $this->properties['hasDownloads'];
    }

    /**
     * Return whether this is the front page/homepage.
     *
     * @return boolean
     */
    public function isFrontPage() {
        if (is_null($this->properties['isFrontPage'])) {
            $this->properties['isFrontPage'] = ( $this->data['title'] == 'Judicial Appointments Commission | index' );
        }

        return $this->properties['isFrontPage'];
    }

    /**
     * Return whether this is a news archive page.
     *
     * @return boolean
     */
    public function isNewsArchivePage() {
        if (is_null($this->properties['isNewsArchivePage'])) {
            $crawler = $this->getCrawler();

            $newsLink = $crawler->filter('#navitem5 > li > ul > li > ul > li > a.active');
            if (count($newsLink) == 1 && substr(strtolower($newsLink->text()), 0, 12) == 'news archive') {
                $return = true;
            } else {
                $return = false;
            }

            $this->properties['isNewsArchivePage'] = $return;
        }

        return $this->properties['isNewsArchivePage'];
    }

    /**
     * Return whether this page should be imported into WordPress.
     * Some pages will be ignored and not imported, based on these rules.
     *
     * @return boolean
     */
    public function shouldBeImported() {
        return !(
            // Page should NOT be imported if:
            stristr($this->getContent()->getTitle(), 'News Archive') !== false || // It has 'News Archive' in the title
            stristr($this->getContent()->getTitle(), 'On The Spot') !== false // It's an 'On The Spot' page
        );
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

    /**
     * Get WpPost (Scraper\WordPress\Post\Page) object for this page.
     *
     * @return WpPage|false
     */
    public function getWpPost() {
        if (!isset($this->objects['WpPost'])) {
            $wpPost = WpPage::getByMeta([
                'reddot_import' => 1,
                'reddot_url' => $this->relativeUrl,
            ]);

            $this->setWpPost($wpPost);
        }

        return $this->objects['WpPost'];
    }

    /**
     * Set WpPost object for this page.
     * Also sets $this->wpPostId
     *
     * @param WpPage|false $wpPost
     */
    public function setWpPost($wpPost) {
        $this->objects['WpPost'] = $wpPost;

        if ($wpPost) {
            $this->wpPostId = $wpPost->WP_Post->ID;
        } else {
            $this->wpPostId = null;
        }
    }
}