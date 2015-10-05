<?php

namespace Scraper\WordPress\Rewriter\Element;

class Image extends Base {
    /**
     * The element attribute to be rewritten.
     *
     * @var null
     */
    public static $rewriteAttr = 'src';

    /**
     * Determine if this element's needs rewriting.
     *
     * @return bool
     */
    public function shouldBeRewritten() {
        return ( parent::shouldBeRewritten() && parent::isUrlToAsset() );
    }
}