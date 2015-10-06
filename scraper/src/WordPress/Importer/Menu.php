<?php

/**
 * Import navigation structure and pages in to WordPress menus.
 */

namespace Scraper\WordPress\Importer;

use Scraper\PageHierarchy;
use Scraper\WordPress\NavMenu\NavMenu;

class Menu extends Base {
    public static function importPageHierarchy(PageHierarchy $pageHierarchy, NavMenu $menu) {
        // Delete all exiting menu items from nav
        foreach ($menu->getItems() as $item) {
            $item->delete();
        }

        // Import page hierarchy into menu
        $menuItems = $pageHierarchy->getHierarchyMap();
        static::recursiveImportMenuItems($menu, $menuItems);
    }

    private static function recursiveImportMenuItems(NavMenu $menu, $menuItems, $parentId = null) {
        foreach ($menuItems as $menuItem) {
            if (isset($menuItem['page'])) {
                $isValidPage = (
                    !$menuItem['page']->isFrontPage() &&
                    !$menuItem['page']->isNewsArchivePage() &&
                    $menuItem['page']->shouldBeImported()
                );

                if ($isValidPage) {
                    $postId = $menuItem['page']->getWpPost()->WP_Post->ID;
                    $itemId = $menu->addLinkToPost($menuItem['text'], $postId, $parentId);
                }
            } else {
                $itemId = $menu->addCustomLink($menuItem['text'], $menuItem['url']);
            }

            if (isset($menuItem['children'])) {
                $newParentId = (isset($itemId) ? $itemId : null);
                static::recursiveImportMenuItems($menu, $menuItem['children'], $newParentId);
            }
        }
    }
}