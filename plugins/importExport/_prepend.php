<?php
/**
 * @brief importExport, a plugin for Dotclear 2
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
    'dcIeModule'              => __DIR__ . '/inc/class.dc.ieModule.php',
    'dcImportFlat'            => __DIR__ . '/inc/class.dc.import.flat.php',
    'dcImportFeed'            => __DIR__ . '/inc/class.dc.import.feed.php',
    'dcExportFlat'            => __DIR__ . '/inc/class.dc.export.flat.php',
    'dcImportDC1'             => __DIR__ . '/inc/class.dc.import.dc1.php',
    'dcImportWP'              => __DIR__ . '/inc/class.dc.import.wp.php',
    'flatBackup'              => __DIR__ . '/inc/flat/class.flat.backup.php',
    'flatImportV2'            => __DIR__ . '/inc/flat/class.flat.import.php',
    'flatExport'              => __DIR__ . '/inc/flat/class.flat.export.php',
    'ieMaintenanceExportblog' => __DIR__ . '/inc/lib.ie.maintenance.php',
    'ieMaintenanceExportfull' => __DIR__ . '/inc/lib.ie.maintenance.php',
    'importExportBehaviors'   => __DIR__ . '/inc/admin.behaviors.php',
]);
