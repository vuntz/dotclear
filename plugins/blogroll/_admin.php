<?php
/**
 * @brief blogroll, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @copyright Olivier Meunier & Association Dotclear
 * @copyright GPL-2.0-only
 */
if (!defined('DC_CONTEXT_ADMIN')) {
    return;
}

dcCore::app()->auth->setPermissionType(dcLinks::PERMISSION_BLOGROLL, __('manage blogroll'));

dcCore::app()->addBehaviors([
    'adminDashboardFavoritesV2' => function (dcFavorites $favs) {
        $favs->register('blogroll', [
            'title'       => __('Blogroll'),
            'url'         => dcCore::app()->adminurl->get('admin.plugin.blogroll'),
            'small-icon'  => [dcPage::getPF('blogroll/icon.svg'), dcPage::getPF('blogroll/icon-dark.svg')],
            'large-icon'  => [dcPage::getPF('blogroll/icon.svg'), dcPage::getPF('blogroll/icon-dark.svg')],
            'permissions' => dcCore::app()->auth->makePermissions([
                dcAuth::PERMISSION_USAGE,
                dcAuth::PERMISSION_CONTENT_ADMIN,
            ]),
        ]);
    },
    'adminUsersActionsHeaders'  => fn () => dcPage::jsModuleLoad('blogroll/js/_users_actions.js'),
]);

dcCore::app()->menu[dcAdmin::MENU_BLOG]->addItem(
    __('Blogroll'),
    dcCore::app()->adminurl->get('admin.plugin.blogroll'),
    [dcPage::getPF('blogroll/icon.svg'), dcPage::getPF('blogroll/icon-dark.svg')],
    preg_match('/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.blogroll')) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
    dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
        dcAuth::PERMISSION_USAGE,
        dcAuth::PERMISSION_CONTENT_ADMIN,
    ]), dcCore::app()->blog->id)
);

require __DIR__ . '/_widgets.php';
