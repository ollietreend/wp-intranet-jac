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
use Scraper\Page\ContentHelper;
use DateTime;

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
            $html = ContentHelper::tidyHtml($html);

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

    public function getNewsPosts() {
        if (!isset($this->cache['news_posts'])) {
            if ($this->page->isFrontPage()) {
                $return = $this->getNewsPostsFromFrontPage();
            } else if ($this->page->isNewsArchivePage()) {
                $return = $this->getNewsPostsFromNewsArchivePage();
            } else {
                $return = [];
            }

            $this->cache['news_posts'] = $return;
        }

        return $this->cache['news_posts'];
    }

    private function getNewsPostsFromFrontPage() {
        $crawler = $this->page->getCrawler();

        $promo = $crawler->filter('#PageContent > #PromotionWrapper');
        $newsElements = $promo->nextAll()->filter('p,hr');

        $stories = [];
        $storyIndex = 0; // Story index, incremented after each <hr> element which indicates a new story.
        $storyElIndex = 0; // Element index within the current story, incremented after each non-empty element.
        foreach ($newsElements as $element) {
            $el = new Crawler($element);

            // If this is a <hr> element, increment $storyIndex, reset $storyElIndex, and skip to the next element.
            if ($element->nodeName == 'hr') {
                $storyIndex++;
                $storyElIndex = 0;
                continue;
            }

            // Check for non-empty characters. Skip this element if it's empty.
            if (!preg_match('/[^\s]/', $el->text())) {
                continue;
            }

            // Check for existence of <strong> when $storyElIndex is 0.
            // If <strong> doesn't exist, we can assume we've reached the end of the stories and are now on the
            // "Older news is in the news archive on the left side" paragraph, so will safely break the loop.
            if ($storyElIndex == 0) {
                $strongTag = $el->filter('strong');
                if (count($strongTag) < 1) {
                    break;
                }
            }

            if (!isset($stories[$storyIndex])) {
                $stories[$storyIndex] = [];
            }

            // Scrape content from the current node
            if ($storyElIndex == 0) {
                // Post date and title
                $headline = ContentHelper::tidyText($el->text());
                $headline = ContentHelper::extractStoryHeadline($headline);
                $stories[$storyIndex]['title'] = $headline['title'];
                $stories[$storyIndex]['date'] = new DateTime($headline['date']);
            } else {
                // Post body (append if it already exists)
                $html = $element->ownerDocument->saveHTML($element);
                $body = isset($stories[$storyIndex]['body']) ? $stories[$storyIndex]['body'] : '';
                $body .= $html . "\n";
                $stories[$storyIndex]['body'] = $body;
            }

            $storyElIndex++;
        }

        // Clean up body HTML
        $stories = array_map(function($story) {
            $story['body'] = ContentHelper::tidyHtml($story['body']);
            return $story;
        }, $stories);

        return $stories;
    }

    private function getNewsPostsFromNewsArchivePage() {
        $stories = $this->getNewsPostsHtmlArray();

        // Get the month and year for this archive page
        $archiveMonth = ContentHelper::dateFromNewsArchivePageTitle($this->getTitle());

        $lastUsedDateInMonth = $archiveMonth->format('t');

        $return = [];

        foreach ($stories as $story) {
            $matches = [];
            $closingStrongAndParagraphTags = preg_match_all('/([\S\s]*?)<\/(strong|p)>/', $story, $matches, PREG_OFFSET_CAPTURE);

            if (count($matches) < 1) {
                // This story is either empty or too inconsistent to scrape. Skip it.
                continue;
            }

            // Extract title and date from story headline
            // Iterate through the matched closing tags until we find one which has content.
            // This is necessary due to an unfortunate inconsistency in the original markup. *sigh*
            $i = 0;
            $headline = '';
            $headlineExtract = false;
            $headlineDoesNotHaveDate = false;
            while ($i < 1 || ( !$headlineExtract && !$headlineDoesNotHaveDate )) {
                if (isset($matches[0][$i][0])) { // Check for existence of the match key
                    $headline = trim($headline . ' ' . ContentHelper::tidyText($matches[0][$i][0]));
                    $headlineExtract = ContentHelper::extractStoryHeadline($headline);
                } else { // Match key does not exist – i.e. we've reached the end of the road, and we haven't
                         // found a date in the headline.
                    $headlineDoesNotHaveDate = true;
                }

                $i++;
            }
            $i--; // Decrement $i so that it equals the index of the key which was used for $headline

            // Normalise the title and date for the story
            if ($headlineExtract) {
                $title = trim($headlineExtract['title']);
                $date = new DateTime($headlineExtract['date'] . ' ' . $archiveMonth->format('Y'));
                $lastUsedDateInMonth = $date->format('d');
            } else {
                $title = $headline;
                $date = new DateTime($lastUsedDateInMonth . ' ' . $archiveMonth->format('F Y'));
            }

            // Grab the story content
            $useI = ($headlineDoesNotHaveDate ? $i-1 : $i);
            $bodyStartIndex = ( $matches[0][$useI][1] + strlen($matches[0][$useI][0]) );
            $body = substr($story, $bodyStartIndex);
            $body = ContentHelper::tidyHtml(utf8_decode($body));

            $return[] = [
                'title' => $title,
                'date' => $date,
                'body' => $body,
            ];
        }

        return $return;
    }

    private function getNewsPostsHtmlArray() {
        $crawler = $this->page->getCrawler();

        $filter = $crawler->filter('#PageContent > div');
        if (count($filter) < 1) {
            return false;
        }

        // Clone elements into new Crawler instance
        // This allows us to modify the DOM without modifying the DOM of the original crawler object.
        $content = new Crawler();
        foreach ($filter as $matchedElement) {
            $content->add(clone $matchedElement);
        }

        // Remove h1 element from content
        $content->filter('h1')->each(function($crawler) {
            foreach ($crawler as $node) {
                $node->parentNode->removeChild($node);
            }
        });

        $pageContentDom = $content->getNode(0);
        $contentHtml = $pageContentDom->ownerDocument->saveHTML($pageContentDom);

        // Remove outer 'wrap' div from HTML
        $contentHtml = preg_replace('/^<div class="wrap nested">\s+([\S\s]*)\s+<\/div>$/', '$1', $contentHtml);
        $contentHtml = trim($contentHtml);

        // Split HTML into stories based on the location of <hr> tags
        $stories = preg_split('/<hr\s?\/?>/', $contentHtml);

        return $stories;
    }
}