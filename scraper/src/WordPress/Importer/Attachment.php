<?php

namespace Scraper\WordPress\Importer;

use Scraper\WordPress\WordPress;
use Scraper\WordPress\Post\Attachment as WpAttachment;

class Attachment extends Base {
    /**
     * @param string $assetPath
     * @param int $associatedPostId
     * @param array $meta (optional) Meta fields be saved with the media item.
     * @return int
     * @throws \Exception
     */
    public static function import($assetPath, $associatedPostId, $meta = []) {
        $existingPost = WpAttachment::getByMeta([
            'reddot_import' => 1,
            'reddot_url' => $assetPath,
        ]);

        if ($existingPost) {
            if (static::$skipExisting) {
                // Stop processing
                return $existingPost;
            } else {
                // Delete existing post and continue with import
                $existingPost->delete();
            }
        }

        $meta = array_merge($meta, [
            'reddot_import' => true,
            'reddot_url' => $assetPath,
        ]);

        $absoluteAssetPath = static::$baseFilePath . $assetPath;
        $attachmentId = WordPress::importMedia($absoluteAssetPath, $associatedPostId, null, $meta);

        if (!is_int($attachmentId)) {
            throw new \Exception('Unable to import media item.');
        }

        return WpAttachment::getById($attachmentId);
    }
}