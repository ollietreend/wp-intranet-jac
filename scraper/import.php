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

use Scraper\CollectionToScrape as CollectionToScrape;
use Scraper\NavigationStructure as NavigationStructure;
use Scraper\PageHierarchy as PageHierarchy;
use Scraper\WordPress\Importer\Page as PageImporter;

// Configure filesystem cache
FileSystemCache::$cacheDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cache';

// Always end output with a newline
register_shutdown_function(function() {
    echo PHP_EOL;
});

$scrapeConfig = [
    'url'  => 'http://jacintranet.dev/scraper/import_content/',
    'path' => $_SERVER['DOCUMENT_ROOT'] . '/scraper/import_content/',
];

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
$pageImporter = new PageImporter();
$pageImporter->authorId = 2;
$pageImporter->skipExisting = true;

foreach ($pages as $page) {
    if ($page->isFrontPage()) {
        continue;
    }

    $pageImporter->import($page);
}

/**
 * Import page hierarchy
 */
echo "Importing page hierarchy <br/>";
$pageHierarchy = new PageHierarchy($navigationStructure, $collection->getPages());
$pageHierarchy->getHierarchyMap();

foreach ($pages as $page) {
    if ($page->isFrontPage()) {
        continue;
    }

    $parentWpPostId = $pageHierarchy->getParentWpPostId($page);
    if ($page->wpPost->WP_Post->post_parent != $parentWpPostId) {
        $page->wpPost->save([
            'post_parent' => $parentWpPostId,
        ]);
    }
}

/**
 * Testing new stuff here.
 */

foreach ($pages as $page) {
    if (!$page->hasDownloads()) {
        continue;
    }

    $downloads = $page->getContent()->getDownloads();
    foreach ($downloads as $download) {
        $filePath = $scrapeConfig['path'] . $download['relativeUrl'];

        if (!file_exists($filePath)) {
            throw new \Exception('Missing file: ' . $download['relativeUrl']);
        }

        $tmpFilePath = tempnam(sys_get_temp_dir(), 'jacimport');
        copy($filePath, $tmpFilePath);
        clearstatcache(true, $tmpFilePath); // PHP bug: https://bugs.php.net/bug.php?id=65701

        $fileArray = [
            'name' => basename($filePath),
            'tmp_name' => $tmpFilePath,
        ];
        $assocPostId = $page->getWpPost()->WP_Post->ID;
        $desc = $download['title'];

        $success = media_handle_sideload($fileArray, $assocPostId, $desc);

        if (file_exists($tmpFilePath)) {
            @unlink($tmpFilePath);
        }

        var_dump($success);

        exit;
    }

    exit;
}

echo "Done";
