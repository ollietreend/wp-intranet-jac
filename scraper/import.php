<?php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', true);

// Set the default timezone (required by \Symfony\Component\BrowserKit\Cookie)
date_default_timezone_set('Europe/London');

// Set page time limit
set_time_limit(0);

// Import autoloader
require 'vendor/autoload.php';
require '../wp-load.php';
require '../wp-admin/includes/image.php';
require '../wp-admin/includes/file.php';
require '../wp-admin/includes/media.php';

use Scraper\CollectionToScrape;
use Scraper\NavigationStructure;
use Scraper\PageHierarchy;
use Scraper\WordPress\Importer\Base as BaseImporter;
use Scraper\WordPress\Importer\Page as PageImporter;
use Scraper\WordPress\Importer\Menu as MenuImporter;
use Scraper\WordPress\Importer\Post as PostImporter;
use Scraper\WordPress\Importer\PageDownloads as PageDownloadsImporter;
use Scraper\WordPress\NavMenu\NavMenu;

// Configure filesystem cache
FileSystemCache::$cacheDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cache';

// Always end output with a newline
register_shutdown_function(function() {
    echo PHP_EOL;
});

// Configure import
$scrapeConfig = [
    'url'  => 'http://jacintranet.dev/scraper/import_content/',
    'path' => $_SERVER['DOCUMENT_ROOT'] . '/scraper/import_content/',
];
BaseImporter::$authorId = 2;
BaseImporter::$skipExisting = true;
BaseImporter::$baseFilePath = $scrapeConfig['path'];
PageDownloadsImporter::$acfFieldKey = 'field_55eda9ba9771a';

/**
 * Spider site
 */
echo "Spidering site <br/>";
$collection = new CollectionToScrape($scrapeConfig['url'], false, 1000);
$collection->getCrawlResults();

/**
 * Generate navigation structure
 */
echo "Generating navigation structure <br/>";
$navigationStructure = new NavigationStructure($collection);
$structure = $navigationStructure->getStructure();

/**
 * Import page content
 */
echo "Importing pages into WordPress <br/>";
$pages = $collection->getPages();

foreach ($pages as $page) {
    if (
        $page->isFrontPage() ||
        $page->isNewsArchivePage() ||
        $page->shouldBeImported() == false
    ) {
        continue;
    }

    PageImporter::import($page);
    PageDownloadsImporter::import($page);
}

/**
 * Import page hierarchy
 */
echo "Importing page hierarchy <br/>";
$pageHierarchy = new PageHierarchy($navigationStructure, $collection->getPages());
$pageHierarchy->getHierarchyMap();

foreach ($collection->getPages() as $page) {
    if (
        $page->isFrontPage() ||
        $page->isNewsArchivePage() ||
        $page->shouldBeImported() == false ||
        $page->getWpPost() == false
    ) {
        continue;
    }

    $parentWpPostId = $pageHierarchy->getParentWpPostId($page);
    if ($page->getWpPost()->WP_Post->post_parent != $parentWpPostId) {
        $page->getWpPost()->save([
            'post_parent' => $parentWpPostId,
        ]);
    }

}

/**
 * Import page hierarchy into primary navigation menu
 */
$menu = NavMenu::getMenu('Primary Navigation');
MenuImporter::importPageHierarchy($pageHierarchy, $menu);

/**
 * Import news posts
 */
echo "Importing news posts <br/>";
foreach ($pages as $page) {
    if (!$page->isFrontPage() && !$page->isNewsArchivePage()) {
        continue;
    }

    $stories = $page->getContent()->getNewsPosts();
    foreach ($stories as $story) {
        PostImporter::import($story, $page);
    }
}

echo "Done";
