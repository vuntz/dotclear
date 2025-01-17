<?php
/**
 * @brief breadcrumb, a plugin for Dotclear 2
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
    'breadcrumbBehaviors' => __DIR__ . '/inc/admin.behaviors.php',
    'tplBreadcrumb'       => __DIR__ . '/inc/public.tpl.php',
]);
