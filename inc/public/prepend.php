<?php
/**
 * @package Dotclear
 * @subpackage Public
 *
 * @copyright Olivier Meunier & Association Dotclear
 * @copyright GPL-2.0-only
 */
if (!empty($_GET['pf'])) {
    require __DIR__ . '/../load_plugin_file.php';
    exit;
}

if (!empty($_GET['vf'])) {
    require __DIR__ . '/../load_var_file.php';
    exit;
}

if (!isset($_SERVER['PATH_INFO'])) {
    $_SERVER['PATH_INFO'] = '';
}

require_once __DIR__ . '/../prepend.php';
require_once __DIR__ . '/rs.extension.php';

# Loading blog
if (defined('DC_BLOG_ID')) {
    try {
        dcCore::app()->setBlog(DC_BLOG_ID);
    } catch (Exception $e) {
        init_prepend_l10n();
        /* @phpstan-ignore-next-line */
        __error(__('Database problem'), DC_DEBUG ?
            __('The following error was encountered while trying to read the database:') . '</p><ul><li>' . $e->getMessage() . '</li></ul>' :
            __('Something went wrong while trying to read the database.'), 620);
    }
}

if (dcCore::app()->blog->id == null) {
    __error(__('Blog is not defined.'), __('Did you change your Blog ID?'), 630);
}

if ((bool) !dcCore::app()->blog->status) {
    dcCore::app()->unsetBlog();
    __error(__('Blog is offline.'), __('This blog is offline. Please try again later.'), 670);
}

# Prepare for further notices, if any
dcCore::app()->notices = new dcNotices(dcCore::app());

# Cope with static home page option
if (dcCore::app()->blog->settings->system->static_home) {
    dcCore::app()->url->registerDefault(['dcUrlHandlers', 'static_home']);
}

# Loading media
try {
    dcCore::app()->media = new dcMedia(dcCore::app());
} catch (Exception $e) {
}

# Creating template context
$_ctx = new context();

try {
    dcCore::app()->tpl = new dcTemplate(DC_TPL_CACHE, 'dcCore::app()->tpl', dcCore::app());
} catch (Exception $e) {
    __error(__('Can\'t create template files.'), $e->getMessage(), 640);
}

# Loading locales
$_lang = dcCore::app()->blog->settings->system->lang;
$_lang = preg_match('/^[a-z]{2}(-[a-z]{2})?$/', $_lang) ? $_lang : 'en';

l10n::lang($_lang);
if (l10n::set(__DIR__ . '/../../locales/' . $_lang . '/date') === false && $_lang != 'en') {
    l10n::set(__DIR__ . '/../../locales/en/date');
}
l10n::set(__DIR__ . '/../../locales/' . $_lang . '/public');
l10n::set(__DIR__ . '/../../locales/' . $_lang . '/plugins');

// Set lexical lang
dcUtils::setlexicalLang('public', $_lang);

# Loading plugins
try {
    dcCore::app()->plugins->loadModules(DC_PLUGINS_ROOT, 'public', $_lang);
} catch (Exception $e) {
}

# Loading themes
dcCore::app()->themes = new dcThemes(dcCore::app());
dcCore::app()->themes->loadModules(dcCore::app()->blog->themes_path);

# Defining theme if not defined
if (!isset($__theme)) {
    $__theme = dcCore::app()->blog->settings->system->theme;
}

if (!dcCore::app()->themes->moduleExists($__theme)) {
    $__theme = dcCore::app()->blog->settings->system->theme = 'default';
}

$__parent_theme = dcCore::app()->themes->moduleInfo($__theme, 'parent');
if ($__parent_theme) {
    if (!dcCore::app()->themes->moduleExists($__parent_theme)) {
        $__theme        = dcCore::app()->blog->settings->system->theme        = 'default';
        $__parent_theme = null;
    }
}

# If theme doesn't exist, stop everything
if (!dcCore::app()->themes->moduleExists($__theme)) {
    __error(__('Default theme not found.'), __('This either means you removed your default theme or set a wrong theme ' .
            'path in your blog configuration. Please check theme_path value in ' .
            'about:config module or reinstall default theme. (' . $__theme . ')'), 650);
}

# Ensure theme's settings namespace exists
dcCore::app()->blog->settings->addNamespace('themes');

# Loading _public.php file for selected theme
dcCore::app()->themes->loadNsFile($__theme, 'public');

# Loading translations for selected theme
if ($__parent_theme) {
    dcCore::app()->themes->loadModuleL10N($__parent_theme, $_lang, 'main');
}
dcCore::app()->themes->loadModuleL10N($__theme, $_lang, 'main');

# --BEHAVIOR-- publicPrepend
dcCore::app()->callBehavior('publicPrepend', dcCore::app());

# Prepare the HTTP cache thing
$mod_files = get_included_files();
$mod_ts    = [];
$mod_ts[]  = dcCore::app()->blog->upddt;

$__theme_tpl_path = [
    dcCore::app()->blog->themes_path . '/' . $__theme . '/tpl',
];
if ($__parent_theme) {
    $__theme_tpl_path[] = dcCore::app()->blog->themes_path . '/' . $__parent_theme . '/tpl';
}
$tplset = dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->system->theme, 'tplset');
if (!empty($tplset) && is_dir(__DIR__ . '/default-templates/' . $tplset)) {
    dcCore::app()->tpl->setPath(
        $__theme_tpl_path,
        __DIR__ . '/default-templates/' . $tplset,
        dcCore::app()->tpl->getPath()
    );
} else {
    dcCore::app()->tpl->setPath(
        $__theme_tpl_path,
        dcCore::app()->tpl->getPath()
    );
}
dcCore::app()->url->mode = dcCore::app()->blog->settings->system->url_scan;

try {
    # --BEHAVIOR-- publicBeforeDocument
    dcCore::app()->callBehavior('publicBeforeDocument', dcCore::app());

    dcCore::app()->url->getDocument();

    # --BEHAVIOR-- publicAfterDocument
    dcCore::app()->callBehavior('publicAfterDocument', dcCore::app());
} catch (Exception $e) {
    __error($e->getMessage(), __('Something went wrong while loading template file for your blog.'), 660);
}
