<?php

namespace Scraper\WordPress;

class Base {
    public function __construct() {
        if (!function_exists('\wp')) {
            throw new \Exception('The WordPress core must be loaded before using ' . get_class($this));
        }
    }
}