<?php
/**
 * @brief attachments, a plugin for Dotclear 2
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

$this->registerModule(
    'attachments',             // Name
    'Manage post attachments', // Description
    'Dotclear Team',           // Author
    '1.1',                     // Version
    [
        'permissions' => dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_USAGE,
            dcAuth::PERMISSION_CONTENT_ADMIN,
            dcPages::PERMISSION_PAGES,
        ]),
        'priority' => 999,
        'type'     => 'plugin',
    ]
);
