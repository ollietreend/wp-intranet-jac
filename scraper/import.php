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

// Configure filesystem cache
FileSystemCache::$cacheDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cache';

// Always end output with a newline
register_shutdown_function(function() {
    echo PHP_EOL;
});

echo "Spidering site\n";
$collection = new Scraper\CollectionToScrape('http://jacintranet.dev/scraper/import_content/', false, 1000);
$collection->getCrawlResults();

echo "Generating navigation structure\n";
$navigationStructure = new Scraper\NavigationStructure($collection);
$structure = $navigationStructure->getStructure();

/*
echo "Diffing between pages and navigation structure... ";
function extractNavUrls($structure) {
    $return = [];

    foreach ($structure as $menuItem) {
        if ($menuItem['url'] !== '#') {
            $return[] = $menuItem['url'];
        }

        if (isset($menuItem['children'])) {
            $return = array_merge($return, extractNavUrls($menuItem['children']));
        }
    }

    return $return;
}

function extractSpideredUrls($pages) {
    $return = [];

    foreach ($pages as $page) {
        $return[] = $page->relativeUrl;
    }

    return $return;
}

$navUrls = extractNavUrls($structure);
$spideredUrls = extractSpideredUrls($collection->getPages());

$missingFromPrimaryNav = array_diff($spideredUrls, $navUrls);

echo count($missingFromPrimaryNav) . " pages not in the primary nav.\n";
*/

echo "Importing pages into WordPress...\n";
$pages = $collection->getPages();
$pageImporter = new Scraper\WordPress\Importer\Page();
$pageImporter->authorId = 2;
$pageImporter->skipExisting = false;

foreach ($pages as $page) {
    if ($page->isFrontPage()) {
        continue;
    }

    echo '<p>Importing page ' . $page->getContent()->getTitle() . '</p>';
    $pageImporter->import($page);
}

echo "Importing page hierarchy...";
$pageHierarchy = new \Scraper\PageHierarchy($navigationStructure, $collection->getPages());
$pageHierarchy->getHierarchyMap();

foreach ($pages as $page) {
    if (is_null($page->wpPostId)) {
        continue;
    }

    echo '<p>Assigning parent to ' . $page->getContent()->getTitle() . '</p>';

    $parentWpPostId = $pageHierarchy->getParentWpPostId($page);
    $thePost = get_post($page->wpPostId);
    $thePost->post_parent = ( is_null($parentWpPostId) ? 0 : $parentWpPostId );
    $update = wp_insert_post($thePost, true);

    if (!is_int($update)) {
        var_dump($update);
    }
}

echo "Done";