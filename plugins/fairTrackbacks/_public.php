<?php
/**
 * @brief fairTrackbacks, an antispam filter plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @copyright Olivier Meunier & Association Dotclear
 * @copyright GPL-2.0-only
 */
if (!defined('DC_RC_PATH')) {
    return;
}

if (DC_FAIRTRACKBACKS_FORCE) {
    Clearbricks::lib()->autoload([
        'dcFilterFairTrackbacks' => __DIR__ . '/filters/filter.fairtrackbacks.php',
    ]);

    dcCore::app()->spamfilters[] = 'dcFilterFairTrackbacks';
}
