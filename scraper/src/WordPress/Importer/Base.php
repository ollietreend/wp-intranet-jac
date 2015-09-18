<?php

namespace Scraper\WordPress\Importer;

use Scraper\WordPress\Base as WPBase;
use Scraper\Page as ScraperPage;

class Base extends WPBase {
    /**
     * User ID to use as author of imported posts.
     *
     * @var int
     */
    public $authorId = null;

    /**
     * Whether to skip importing posts which have already been imported.
     * If false, existing post will be deleted and a new post created.
     *
     * @var bool
     */
    public $skipExisting = true;
}