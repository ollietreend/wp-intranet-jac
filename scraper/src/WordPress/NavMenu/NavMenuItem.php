<?php

/**
 * Represents a nav menu item.
 */

namespace Scraper\WordPress\NavMenu;

class NavMenuItem {
    public $NavMenu = null;

    public $WP_Post = null;

    public function __construct(NavMenu $navMenu, \WP_Post $navMenuItem) {
        $this->NavMenu = $navMenu;
        $this->WP_Post = $navMenuItem;
    }

    public function delete() {
        $delete = wp_delete_post($this->WP_Post->ID, true);
        return ( $delete !== false );
    }
}