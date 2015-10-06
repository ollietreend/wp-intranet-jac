<?php

/**
 * Represents a navigation menu.
 */

namespace Scraper\WordPress\NavMenu;

class NavMenu {
    /**
     * Holds the WordPress menu object.
     *
     * @var \stdClass WordPress menu object
     */
    public $menu = null;

    public $items = null;

    public function __construct($menu) {
        $this->menu = $menu;
    }

    /**
     * Return an array of menu items belonging to the current nav menu.
     *
     * @return NavMenuItem[]
     */
    public function getItems() {
        if (is_null($this->items)) {
            $items = wp_get_nav_menu_items($this->menu->term_id);
            $itemPosts = [];
            foreach ($items as $item) {
                $itemPosts[] = new NavMenuItem($this, $item);
            }
            $this->items = $itemPosts;
        }

        return $this->items;
    }

    public function hasLinkToPost($postId) {
        $item = $this->findLinkToPost($postId);
        return ( $item !== false );
    }

    public function findLinkToPost($postId) {
        $items = array_filter($this->getItems(), function($item) use ($postId) {
            return (
                $item->WP_Post->type == 'post_type' &&
                $item->WP_Post->object_id == $postId
            );
        });

        if (count($items) > 0) {
            $item = array_shift($items);
            return $item->WP_Post;
        } else {
            return false;
        }
    }

    public function addLinkToPost($text, $postId, $parentId = null) {
        $menuId = $this->menu->term_id;

        $update = wp_update_nav_menu_item($menuId, 0, [
            'menu-item-title' =>  $text,
            'menu-item-object-id' => $postId,
            'menu-item-object' => 'page',
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
            'menu-item-parent-id' => ( is_int($parentId) ? $parentId : 0 ),
        ]);

        if (is_int($update)) {
            return $update;
        } else {
            throw new \Exception('Unable to add or update menu item');
        }
    }

    public function addCustomLink($text, $url, $parentId = null) {
        $menuId = $this->menu->term_id;

        $update = wp_update_nav_menu_item($menuId, 0, [
            'menu-item-title' =>  $text,
            'menu-item-url' => $url,
            'menu-item-status' => 'publish',
            'menu-item-parent-id' => ( is_int($parentId) ? $parentId : 0 ),
        ]);

        if (is_int($update)) {
            return $update;
        } else {
            throw new \Exception('Unable to add or update menu item');
        }
    }

    /**
     * Get the menu and instantiate a new NavMenu object.
     *
     * @param string $menu Menu ID, slug, or name - or the menu object.
     * @return static
     */
    static public function getMenu($menu) {
        $menu = wp_get_nav_menu_object('Primary Navigation');
        if ($menu) {
            return new static($menu);
        } else {
            return false;
        }
    }
}