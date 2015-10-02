<?php

namespace Scraper;

use Scraper\WordPress\WordPress;

class PageHierarchy {
    /**
     * Holds the site navigation structure
     *
     * @var NavigationStructure
     */
    protected $nav = null;

    /**
     * Holds the page objects
     *
     * @var Page[]
     */
    protected $pages = null;

    /**
     * Holds a hierarchical map between the navigation structure and page objects.
     *
     * @var null|array
     */
    protected $hierarchyMap = null;

    /**
     * Class constructor
     *
     * @param NavigationStructure $nav
     * @param Page[] $pages
     */
    public function __construct(NavigationStructure $nav, $pages) {
        $this->nav = $nav;
        $this->pages = $pages;
    }

    public function getHierarchyMap() {
        if (is_null($this->hierarchyMap)) {
            $this->hierarchyMap = $this->generateHierarchyMap();
        }

        return $this->hierarchyMap;
    }

    private function generateHierarchyMap() {
        $structure = $this->nav->getStructure();
        $structure = $this->mapPagesToStructure($structure);

        return $structure;
    }

    private function mapPagesToStructure($items) {
        foreach ($items as $k => $item) {
            $page = $this->getPageWithRelativeUrl($item['url']);

            if ($page !== false) {
                $items[$k]['page'] = $page;
            }

            if (isset($item['children'])) {
                $items[$k]['children'] = $this->mapPagesToStructure($item['children']);
            }
        }

        return $items;
    }

    private function getPageWithRelativeUrl($relativeUrl) {
        foreach ($this->pages as $page) {
            if ($page->relativeUrl == $relativeUrl) {
                return $page;
            }
        }

        return false;
    }

    public function getParentWpPostId(Page $page) {
        $hierarchy = $this->getHierarchyMap();
        $parent = $this->findParentInHierarchy($hierarchy, $page);

        if (!$parent || !isset($parent['page'])) {
            return null;
        } else {
            return $parent['page']->getWpPost()->WP_Post->ID;
        }
    }

    private function findParentInHierarchy($items, Page $page, $parent = false) {
        foreach ($items as $item) {
            // Check for match on current item
            if (isset($item['page']) && $item['page'] == $page) {
                return $parent;
            }

            // Check for match in children
            if (isset($item['children'])) {
                $newParent = $item;
                unset($newParent['children']);
                $parentInChildren = $this->findParentInHierarchy($item['children'], $page, $newParent);
                if ($parentInChildren !== false) {
                    return $parentInChildren;
                }
            }
        }

        return false;
    }
}