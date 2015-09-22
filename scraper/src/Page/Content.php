<?php

/**
 * Created by PhpStorm.
 * User: ollietreend
 * Date: 15/09/2015
 * Time: 15:02
 */

namespace Scraper\Page;

use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\DomCrawler\Crawler;
use Scraper\Page;
use tidy;

class Content {
    /**
     * Holds the page object.
     *
     * @var null|Page
     */
    protected $page = null;

    /**
     * Cache to hold scraped content to aid performance
     * upon repeat calls to get*() methods.
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Constructor method
     *
     * @param Page $page
     */
    public function __construct(Page $page) {
        $this->page = $page;
    }

    public function getTitle() {
        if (!isset($this->cache['title'])) {
            $crawler = $this->page->getCrawler();
            $h1 = $crawler->filter('#PageContent h1');

            if (count($h1) < 1) {
                throw new \Exception('Could not find h1 tag');
            }

            if (count($h1) > 1) {
                throw new \Exception('Found multiple h1 tags');
            }

            // Filter and clean title
            $title = $h1->text();
            $title = utf8_decode($title);
            $title = trim($title);

            $this->cache['title'] = $title;
        }

        return $this->cache['title'];
    }

    public function getBody() {
        if (!isset($this->cache['body'])) {
            $crawler = $this->page->getCrawler();
            $filter = $crawler->filter('#PageContent .wrap.nested');

            // Clone elements into new Crawler instance
            // This allows us to modify the DOM without modifying the DOM of the original crawler object.
            $content = new Crawler();
            foreach ($filter as $matchedElement) {
                $content->add(clone $matchedElement);
            }

            if (count($content) < 1) {
                throw new \Exception('Could not find body content element');
            }

            if (count($content) > 1) {
                throw new \Exception('Found multiple body content elements');
            }

            // Remove unwanted elements from content
            $content->filter('.mainContent, .PanelsRight, h1')->each(function($crawler) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            });

            // Filter and clean HTML
            $html = $content->html();
            $html = utf8_decode($html);
            $html = $this->tidyHtml($html);

            $this->cache['body'] = $html;
        }

        return $this->cache['body'];
    }

    public function getDownloads() {
        if (!isset($this->cache['downloads'])) {
            if ($this->page->hasDownloads()) {
                $crawler = $this->page->getCrawler();

                $links = $crawler->filter('.PanelsRight > .GenericRight ul .Headline > a');

                // Prefix to use for absolute URLs
                $absUrlPrefix = substr($this->page->url, 0, 0 - strlen($this->page->relativeUrl));

                $return = [];
                foreach ($links as $link) {
                    $text = utf8_decode($link->nodeValue);
                    $href = $link->getAttribute('href');

                    // Ignore links which do not begin with "static/" (e.g. and thus belong in the uploads directory)
                    if (substr($href, 0, 7) !== 'static/') {
                        continue;
                    }

                    $return[] = [
                        'title' => $text,
                        'relativeUrl' => $href,
                        'url' => $absUrlPrefix . $href,
                    ];
                }
            } else {
                $return = [];
            }

            $this->cache['downloads'] = $return;
        }

        return $this->cache['downloads'];
    }

    /**
     * Clean HTML by running it through the PHP Tidy class.
     *
     * @see http://php.net/manual/en/book.tidy.php
     * @param $html
     * @return string
     */
    private function tidyHtml($html) {
        $config = [
            'bare' => true,
            'enclose-text' => true,
            'hide-comments' => true,
            'logical-emphasis' => true,
            'show-body-only' => true,
            'wrap' => 0,
        ];

        $tidy = new tidy();
        $tidy->parseString($html, $config);
        $tidy->cleanRepair();

        return (string) $tidy;
    }
}