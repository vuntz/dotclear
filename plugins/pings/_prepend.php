<?php
/**
 * @brief pings, a plugin for Dotclear 2
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

Clearbricks::lib()->autoload([
    'pingsAPI'            => __DIR__ . '/inc/lib.pings.php',
    'pingsCoreBehaviour'  => __DIR__ . '/inc/core.behaviors.php',
    'pingsAdminBehaviors' => __DIR__ . '/inc/admin.behaviors.php',
]);

dcCore::app()->addBehavior('coreFirstPublicationEntries', [pingsCoreBehaviour::class, 'doPings']);
