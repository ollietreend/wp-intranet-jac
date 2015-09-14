<?php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', true);

// Set the default timezone (required by \Symfony\Component\BrowserKit\Cookie)
date_default_timezone_set('Europe/London');

// Import autoloader
require 'vendor/autoload.php';

// Configure filesystem cache
FileSystemCache::$cacheDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cache';

// Always end output with a newline
register_shutdown_function(function() {
    echo PHP_EOL;
});

echo "Spidering site\n";
$collection = new Scraper\CollectionToScrape('http://jacintranet.dev/scraper/import_content/', false, 1000);

echo "Generating navigation structure\n";
$navigationStructure = new Scraper\NavigationStructure($collection);
$structure = $navigationStructure->getStructure();

echo "Done";