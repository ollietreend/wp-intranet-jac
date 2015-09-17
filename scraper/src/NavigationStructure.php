<?php

/**
 * Navigation Structure
 *
 * This class represents a the navigation structure of the
 * supplied collection of pages.
 *
 * Results of the structure generation will be cached to
 * improve performance.
 *
 * Class NavigationStructure
 */

namespace Scraper;

use FileSystemCache;

class NavigationStructure {
    /**
     * Holds the collection of resources to scrape.
     * This is used to generate the navigation structure.
     *
     * @var null|CollectionToScrape
     */
    public $collection = null;

    /**
     * Holds the navigation menu structure.
     *
     * @var null|array
     */
    public $structure = null;

    /**
     * Class constructor
     *
     * @param CollectionToScrape $collection
     */
    public function __construct(CollectionToScrape $collection) {
        $this->collection = $collection;
        $this->cacheKey = FileSystemCache::generateCacheKey([get_class($this), $collection->urlToSpider, $collection->crawlDepth]);
    }

    /**
     * Generate the navigation structure.
     */
    public function generateNavigationStructure() {
        $pages = $this->collection->getPages();

        $structure = [];

        foreach ($pages as $page) {
            $pageNav = $page->getNavigationMenu();
            $structure = $this->nestedMerge($structure, $pageNav->menu);
        }

        $this->setStructure($structure);
    }

    /**
     * Perform a nested merge of the navigation structure array.
     *
     * @param array $data Navigation structure
     * @param array $merge New structure to be merged in
     * @return array
     */
    public function nestedMerge(array $data, array $merge) {
        // If $data is empty, return the $merge array. No further processing required.
        if (count($data) === 0) {
            $data = $merge;
            return $data;
        }

        // Loop through each $merge array value...
        foreach ($merge as $k => $mergeMenuItem) {
            // ...and try to find a matching value in the $data array that we're merging in to.
            $matchingKey = false;
            foreach ($data as $l => $menuItem) {
                if ($menuItem['text'] == $mergeMenuItem['text'] && $menuItem['url'] == $mergeMenuItem['url']) {
                    // We found a matching menu item! Remember its key in the $data array.
                    $matchingKey = $l;
                }
            }

            if ($matchingKey === false) { // No match was found – add the new menu item to the $data array
                $data[] = $mergeMenuItem;
            } else { // A matching menu item was found. Merge in children if necessary.
                if (isset($mergeMenuItem['children'])) {
                    $menuItemChildren = isset($data[$matchingKey]['children']) ? $data[$matchingKey]['children'] : [];
                    $data[$matchingKey]['children'] = $this->nestedMerge($menuItemChildren, $mergeMenuItem['children']);
                }
            }
        }

        return $data;
    }

    /**
     * Get the navigation structure.
     *
     * @return array|null
     */
    public function getStructure() {
        if (FileSystemCache::retrieve($this->cacheKey) === false) {
            $this->generateNavigationStructure();
        }

        if (is_null($this->structure)) {
            $this->structure = FileSystemCache::retrieve($this->cacheKey);
        }

        return $this->structure;
    }

    /**
     * Set the navigation structure.
     *
     * @param $structure
     */
    public function setStructure($structure) {
        $this->structure = $structure;
        FileSystemCache::store($this->cacheKey, $structure);
    }
}