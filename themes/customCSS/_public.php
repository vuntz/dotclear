<?php
/**
 * @brief Custom, a theme for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Themes
 *
 * @copyright Olivier Meunier & Association Dotclear
 * @copyright GPL-2.0-only
 */

namespace Dotclear\Theme\CustomCSS;

use dcCore;

if (!defined('DC_RC_PATH')) {
    return;
}

dcCore::app()->addBehavior('publicHeadContent', [__NAMESPACE__ . '\tplCustomTheme', 'publicHeadContent']);

class tplCustomTheme
{
    public static function publicHeadContent()
    {
        echo '<link rel="stylesheet" type="text/css" href="' . dcCore::app()->blog->settings->system->public_url . '/custom_style.css" media="screen">' . "\n";
    }
}
