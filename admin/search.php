<?php
/**
 * @package Dotclear
 * @subpackage Backend
 *
 * @copyright Olivier Meunier & Association Dotclear
 * @copyright GPL-2.0-only
 */
require __DIR__ . '/../inc/admin/prepend.php';

dcPage::check('usage,contentadmin');
dcCore::app()->addBehavior('adminSearchPageComboV2', ['adminSearchPageDefault','typeCombo']);
dcCore::app()->addBehavior('adminSearchPageHeadV2', ['adminSearchPageDefault','pageHead']);
// posts search
dcCore::app()->addBehavior('adminSearchPageProcessV2', ['adminSearchPageDefault','processPosts']);
dcCore::app()->addBehavior('adminSearchPageDisplayV2', ['adminSearchPageDefault','displayPosts']);
// comments search
dcCore::app()->addBehavior('adminSearchPageProcessV2', ['adminSearchPageDefault','processComments']);
dcCore::app()->addBehavior('adminSearchPageDisplayV2', ['adminSearchPageDefault','displayComments']);

$qtype_combo = [];

# --BEHAVIOR-- adminSearchPageCombo
dcCore::app()->callBehavior('adminSearchPageComboV2', [& $qtype_combo]);

$q     = !empty($_REQUEST['q']) ? $_REQUEST['q'] : (!empty($_REQUEST['qx']) ? $_REQUEST['qx'] : null);
$qtype = !empty($_REQUEST['qtype']) ? $_REQUEST['qtype'] : 'p';
if (!empty($q) && !in_array($qtype, $qtype_combo)) {
    $qtype = 'p';
}

dcCore::app()->auth->user_prefs->addWorkspace('interface');
$page = !empty($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$nb   = adminUserPref::getUserFilters('search', 'nb');
if (!empty($_GET['nb']) && (int) $_GET['nb'] > 0) {
    $nb = (int) $_GET['nb'];
}

$args = ['q' => $q, 'qtype' => $qtype, 'page' => $page, 'nb' => $nb];

# --BEHAVIOR-- adminSearchPageHead
$starting_scripts = $q ? dcCore::app()->callBehavior('adminSearchPageHeadV2', $args) : '';

if ($q) {

    # --BEHAVIOR-- adminSearchPageProcess
    dcCore::app()->callBehavior('adminSearchPageProcessV2', $args);
}

dcPage::open(
    __('Search'),
    $starting_scripts,
    dcPage::breadcrumb(
        [
            html::escapeHTML(dcCore::app()->blog->name) => '',
            __('Search')                                => '',
        ]
    )
);

echo
'<form action="' . dcCore::app()->adminurl->get('admin.search') . '" method="get" role="search">' .
'<div class="fieldset"><h3>' . __('Search options') . '</h3>' .
'<p><label for="q">' . __('Query:') . ' </label>' .
form::field('q', 30, 255, html::escapeHTML($q)) . '</p>' .
'<p><label for="qtype">' . __('In:') . '</label> ' .
form::combo('qtype', $qtype_combo, $qtype) . '</p>' .
'<p><input type="submit" value="' . __('Search') . '" />' .
' <input type="button" value="' . __('Cancel') . '" class="go-back reset hidden-if-no-js" />' .
'</p>' .
'</div>' .
'</form>';

if ($q && !dcCore::app()->error->flag()) {
    ob_start();

    # --BEHAVIOR-- adminSearchPageDisplay
    dcCore::app()->callBehavior('adminSearchPageDisplayV2', $args);

    $res = ob_get_contents();
    ob_end_clean();
    echo $res ?: '<p>' . __('No results found') . '</p>';
}

dcPage::helpBlock('core_search');
dcPage::close();

class adminSearchPageDefault
{
    protected static $count   = null;
    protected static $list    = null;
    protected static $actions = null;

    public static function typeCombo(array $combo)
    {
        $combo[0][__('Search in entries')]  = 'p';
        $combo[0][__('Search in comments')] = 'c';
    }

    public static function pageHead(array $args)
    {
        if ($args['qtype'] == 'p') {
            return dcPage::jsLoad('js/_posts_list.js');
        } elseif ($args['qtype'] == 'c') {
            return dcPage::jsLoad('js/_comments.js');
        }
    }

    public static function processPosts(array $args)
    {
        if ($args['qtype'] != 'p') {
            return null;
        }

        $params = [
            'search'     => $args['q'],
            'limit'      => [(($args['page'] - 1) * $args['nb']), $args['nb']],
            'no_content' => true,
            'order'      => 'post_dt DESC',
        ];

        try {
            self::$count   = dcCore::app()->blog->getPosts($params, true)->f(0);
            self::$list    = new adminPostList(dcCore::app()->blog->getPosts($params), self::$count);
            self::$actions = new dcPostsActions(dcCore::app()->adminurl->get('admin.search'), $args);
            if (self::$actions->process()) {
                return;
            }
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }
    }

    public static function displayPosts(array $args)
    {
        if ($args['qtype'] != 'p' || self::$count === null) {
            return null;
        }

        if (self::$count > 0) {
            printf('<h3>' . __('one entry found', '%d entries found', self::$count) . '</h3>', self::$count);
        }

        self::$list->display(
            $args['page'],
            $args['nb'],
            '<form action="' . dcCore::app()->adminurl->get('admin.search') . '" method="post" id="form-entries">' .

            '%s' .

            '<div class="two-cols">' .
            '<p class="col checkboxes-helpers"></p>' .

            '<p class="col right"><label for="action" class="classic">' . __('Selected entries action:') . '</label> ' .
            form::combo('action', self::$actions->getCombo()) .
            '<input id="do-action" type="submit" value="' . __('ok') . '" /></p>' .
            dcCore::app()->formNonce() .
            str_replace('%', '%%', self::$actions->getHiddenFields()) .
            '</div>' .
            '</form>'
        );
    }

    public static function processComments(array $args)
    {
        if ($args['qtype'] != 'c') {
            return null;
        }

        $params = [
            'search'     => $args['q'],
            'limit'      => [(($args['page'] - 1) * $args['nb']), $args['nb']],
            'no_content' => true,
            'order'      => 'comment_dt DESC',
        ];

        try {
            self::$count   = dcCore::app()->blog->getComments($params, true)->f(0);
            self::$list    = new adminCommentList(dcCore::app()->blog->getComments($params), self::$count);
            self::$actions = new dcCommentsActions(dcCore::app()->adminurl->get('admin.search'), $args);
            if (self::$actions->process()) {
                return;
            }
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }
    }

    public static function displayComments(array $args)
    {
        if ($args['qtype'] != 'c' || self::$count === null) {
            return null;
        }

        if (self::$count > 0) {
            printf('<h3>' . __('one comment found', '%d comments found', self::$count) . '</h3>', self::$count);
        }

        self::$list->display(
            $args['page'],
            $args['nb'],
            '<form action="' . dcCore::app()->adminurl->get('admin.search') . '" method="post" id="form-comments">' .

            '%s' .

            '<div class="two-cols">' .
            '<p class="col checkboxes-helpers"></p>' .

            '<p class="col right"><label for="action" class="classic">' . __('Selected comments action:') . '</label> ' .
            form::combo('action', self::$actions->getCombo()) .
            '<input id="do-action" type="submit" value="' . __('ok') . '" /></p>' .
            dcCore::app()->formNonce() .
            str_replace('%', '%%', self::$actions->getHiddenFields()) .
            '</div>' .
            '</form>'
        );
    }
}
