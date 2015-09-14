<?php

namespace Scraper\NavigationStructure;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class SinglePageNav {
    public $page = [];

    public $menu = [];

    /**
     * Constructor method
     *
     * @param array $page A page array, as supplied by Arachnid
     */
    public function __construct(array $page) {
        $this->page = $page;
        $this->menu = $this->scrapeMenu();
    }

    /**
     * Perform a scrape of the page menu.
     *
     * @return array
     */
    public function scrapeMenu() {
        $client = new Client();
        $crawler = $client->request('GET', $this->page['absolute_url']);

        $menu = [];

        $crawler->filter('#PageLeft > .LeftNav > h2')->each(function($node) use (&$menu) {
            $menuItem = [
                'text' => trim($node->text()),
                'url' => '#',
            ];

            try {
                $sibling = $node->nextAll()->getNode(0);
                $sibling = new Crawler($sibling);
            } catch (InvalidArgumentException $e) {
                // There are no siblings after the current node.
                // Ignore this empty menu – don't add it to the $menu array.
                return false;
            }

            if ($sibling->nodeName() == 'ul' && count($sibling->filter('ul > li > a')) > 0) {
                $menuItem['children'] = $this->scrapeChildMenu($sibling);
                $menu[] = $menuItem;
            }
        });

        return $menu;
    }

    /**
     * Recursively scrape menu items from a UL node
     * Return an array of menu items with 'text' and 'url' keys,
     * and a 'children' key if a sub-menu exists.
     *
     * @param Crawler $ul
     * @return array
     */
    public function scrapeChildMenu(Crawler $ul) {
        $return = [];

        $menuItems = $ul->filterXPath('ul/li');
        $menuItems->each(function($node) use (&$return) {
            $link = $node->filterXPath('li/a');
            $submenu = $node->filterXPath('li/ul');

            $returnItem = [
                'text' => trim($link->text()),
                'url' => trim($link->attr('href')),
            ];

            if (count($submenu) > 0) {
                $returnItem['children'] = $this->scrapeChildMenu($submenu);
            }

            $return[] = $returnItem;
        });

        return $return;
    }
}