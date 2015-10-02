<?php
/**
 * ContentHelper Utility Class
 * Provides helper methods for parsing and formatting scraped page content.
 */

namespace Scraper\Page;

use tidy;
use DateTime;

class ContentHelper {
    static public function tidyText($input) {
        // Strip tags
        $output = strip_tags($input);

        // Replace &nbsp; with space
        // Why? Because preg_replace's \s token doesn't match non-breaking space characters.
        $output = htmlentities($output);
        $output = str_replace('&nbsp;', ' ', $output);
        $output = html_entity_decode($output);

        // Replace multiple spaces with single spaces
        $output = preg_replace('/\s+/', ' ', $output);

        // Trip the string for whitespace
        $output = trim($output);

        return $output;
    }

    /**
     * Clean HTML by running it through the PHP Tidy class.
     *
     * @see http://php.net/manual/en/book.tidy.php
     * @param $html
     * @return string
     */
    public static function tidyHtml($html) {
        $config = [
            'bare' => true,
            'enclose-text' => true,
            'hide-comments' => true,
            'logical-emphasis' => true,
            'show-body-only' => true,
            'wrap' => 0,
            'drop-empty-paras' => true,
        ];

        $tidy = new tidy();
        $tidy->parseString($html, $config);
        $tidy->cleanRepair();

        return (string) $tidy;
    }

    public static function extractStoryHeadline($headline) {
        $pregMatch = preg_match('/^(.+) (\d+)\s?(September|November|December|February|January|October|August|March|April|July|June|Sept|May|Jan|Feb|Sep|Oct|Nov|Dec|Aug|Jul|Mar|Apr|May|Jun)$/i', $headline, $matches);

        if ($pregMatch) {
            $title = $matches[1];
            $date = $matches[2] . ' ' . $matches[3];
            return compact('title', 'date');
        } else {
            return false;
        }
    }

    public static function dateFromNewsArchivePageTitle($title) {
        $pregMatch = preg_match('/^News archive\s?[-–—]\s?(September|November|December|February|January|October|August|March|April|July|June|Sept|May|Jan|Feb|Sep|Oct|Nov|Dec|Aug|Jul|Mar|Apr|May|Jun)\s?(20\d{2})$/i', $title, $match);

        if ($pregMatch) {
            $date = new DateTime('1st ' . $match[1] . ' ' . $match[2]);
            return $date;
        } else {
            return false;
        }
    }
}