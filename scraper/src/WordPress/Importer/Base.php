<?php

namespace Scraper\WordPress\Importer;

use Scraper\Page as ScraperPage;

class Base {
    /**
     * User ID to use as author of imported posts.
     *
     * @var int
     */
    public static $authorId = null;

    /**
     * Whether to skip importing posts which have already been imported.
     * If false, existing post will be deleted and a new post created.
     *
     * @var bool
     */
    public static $skipExisting = true;

    /**
     * Filesystem path to the base of the import content directory.
     *
     * @var string
     */
    public static $baseFilePath = null;
}