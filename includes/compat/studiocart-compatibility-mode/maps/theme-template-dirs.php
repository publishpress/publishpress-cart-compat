<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical theme override subdirectory mapped to leftover StudioCart dir.
 *
 * Compatibility Mode substitutes yourtheme/studiocart/{relative} when the
 * canonical yourtheme/publishpress-cart/{relative} file is missing.
 * Theme files are not copied or renamed on disk.
 *
 * @return array<string, array<int, string>>
 */
return [
    'publishpress-cart' => [ 'studiocart' ],
];
