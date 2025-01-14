<?php
/**
 * @brief widgets, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @copyright Olivier Meunier & Association Dotclear
 * @copyright GPL-2.0-only
 */
class dcWidgets
{
    /**
     * Stack of known widgets
     *
     * @var        array
     */
    private $widgets = [];

    /**
     * Load widgets from string setting (base64 encoded)
     *
     * @param      string  $s      Setting
     *
     * @return     self
     */
    public static function load($s): self
    {
        $o = @unserialize(base64_decode($s));

        if ($o instanceof self) {
            return $o;
        }

        return self::loadArray($o, dcCore::app()->widgets);
    }

    /**
     * Return encoded widgets
     *
     * @return     string
     */
    public function store()
    {
        $serialized = [];
        foreach ($this->widgets as $pos => $w) {
            $serialized[] = ($w->serialize($pos));
        }

        return base64_encode(serialize($serialized));
    }

    /**
     * Create a new widget
     *
     * @param      string         $id               The identifier
     * @param      string         $name             The name
     * @param      mixed          $callback         The callback
     * @param      mixed          $append_callback  The append callback
     * @param      string         $desc             The description
     *
     * @return     dcWidget
     */
    public function create(string $id, string $name, $callback, $append_callback = null, string $desc = ''): dcWidget
    {
        $this->widgets[$id]                  = new dcWidget($id, $name, $callback, $desc);
        $this->widgets[$id]->append_callback = $append_callback;

        return $this->widgets[$id];
    }

    /**
     * Append a widget
     *
     * @param      dcWidget  $widget  The widget
     */
    public function append(dcWidget $widget): void
    {
        if ($widget instanceof dcWidget) {
            if (is_callable($widget->append_callback)) {
                call_user_func($widget->append_callback, $widget);
            }
            $this->widgets[] = $widget;
        }
    }

    /**
     * Determines if widgets list is empty.
     *
     * @return     bool  True if empty, False otherwise.
     */
    public function isEmpty(): bool
    {
        return count($this->widgets) == 0;
    }

    /**
     * Return list of widgets
     *
     * @param      bool   $sorted  Sort the list
     *
     * @return     array  ( description_of_the_return_value )
     */
    public function elements(bool $sorted = false): array
    {
        if ($sorted) {
            uasort($this->widgets, function ($a, $b) {
                $c = dcUtils::removeDiacritics(mb_strtolower($a->name()));
                $d = dcUtils::removeDiacritics(mb_strtolower($b->name()));
                if ($c == $d) {
                    return 0;
                }

                return ($c < $d) ? -1 : 1;
            });
        }

        return $this->widgets;
    }

    /**
     * Get a widget
     *
     * @param      string  $id     The widget identifier
     *
     * @return     mixed
     */
    public function __get($id)
    {
        if (!isset($this->widgets[$id])) {
            return;
        }

        return $this->widgets[$id];
    }

    /**
     * Unset all widgets
     */
    public function __wakeup()
    {
        foreach ($this->widgets as $i => $w) {
            if (!($w instanceof dcWidget)) {
                unset($this->widgets[$i]);
            }
        }
    }

    /**
     * Loads an array of widgets.
     *
     * @param      array                $A        { parameter_description }
     * @param      dcWidgets            $widgets  The widgets
     *
     * @return     bool|dcWidgets|self
     */
    public static function loadArray(array $A, dcWidgets $widgets)
    {
        if (!($widgets instanceof self)) {
            return false;
        }

        uasort($A, fn ($a, $b) => $a['order'] <=> $b['order']);

        $result = new self();
        foreach ($A as $v) {
            if ($widgets->{$v['id']} != null) {
                $w = clone $widgets->{$v['id']};

                // Settings
                unset($v['id'], $v['order']);

                foreach ($v as $sid => $s) {
                    $w->{$sid} = $s;
                }

                $result->append($w);
            }
        }

        return $result;
    }
}

class dcWidget
{
    // Constants

    public const ALL_PAGES   = 0; // Widget displayed on every page
    public const HOME_ONLY   = 1; // Widget displayed on home page only
    public const EXCEPT_HOME = 2; // Widget displayed on every page but home page

    /**
     * Widget ID
     *
     * @var string
     */
    private $id;

    /**
     * Widget name
     *
     * @var string
     */
    private $name;

    /**
     * Widget description
     *
     * @var string
     */
    private $desc;

    /**
     * Widget callback
     *
     * @var null|callable
     */
    private $public_callback = null;

    /**
     * Widget append callback
     *
     * @var null|callable
     */
    public $append_callback = null;

    /**
     * Widget settings
     *
     * @var array
     */
    protected $settings = [];

    /**
     * Get array of widget settings
     *
     * @param      int  $order  The order
     *
     * @return     array
     */
    public function serialize(int $order): array
    {
        $values = [];
        foreach ($this->settings as $k => $v) {
            $values[$k] = $v['value'];
        }

        $values['id']    = $this->id;
        $values['order'] = $order;

        return $values;
    }

    /**
     * Constructs a new instance.
     *
     * @param      string   $id        The identifier
     * @param      string   $name      The name
     * @param      mixed    $callback  The callback
     * @param      string   $desc      The description
     */
    public function __construct(string $id, string $name, $callback, string $desc = '')
    {
        $this->public_callback = $callback;
        $this->id              = $id;
        $this->name            = $name;
        $this->desc            = $desc;
    }

    /**
     * Get widget ID
     *
     * @return     string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Get widget name
     *
     * @return     string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Get widget description
     *
     * @return     string
     */
    public function desc(): string
    {
        return $this->desc;
    }

    /**
     * Gets the widget callback.
     *
     * @return     null|callable  The callback.
     */
    public function getCallback(): ?callable
    {
        return $this->public_callback;
    }

    /**
     * Call a widget callback
     *
     * @param      mixed     $i
     *
     * @return     string
     */
    public function call($i = 0)
    {
        if (is_callable($this->public_callback)) {
            return call_user_func($this->public_callback, $this, $i);
        }

        return '<p>Callback not found for widget ' . $this->id . '</p>';
    }

    /**
     * Widget rendering tool
     *
     * @param      bool    $content_only  The content only
     * @param      string  $class         The class
     * @param      string  $attr          The attribute
     * @param      string  $content       The content
     *
     * @return     string
     */
    public function renderDiv(bool $content_only, string $class, string $attr, string $content): string
    {
        if ($content_only) {
            return $content;
        }

        /*
         * widgetcontainerformat, if defined for the theme in his _define.php,
         * is a sprintf string format in which:
         * - %1$s is the class(es) affected to the container
         * - %2$s is the additional attributes affected to the container
         * - %3$s is the content of the widget
         *
         * Don't forget to set widgettitleformat and widgetsubtitleformat if necessary (see default rendering below)
        */
        $wtscheme = dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->system->theme, 'widgetcontainerformat');
        if (empty($wtscheme)) {
            $wtscheme = '<div class="widget %1$s" %2$s>%3$s</div>';
        }

        return sprintf($wtscheme . "\n", 'widget ' . html::escapeHTML($class), $attr, $content);
    }

    /**
     * Render widget title
     *
     * @param      null|string  $title  The title
     *
     * @return     string
     */
    public function renderTitle(?string $title): string
    {
        if (!$title) {
            return '';
        }

        $wtscheme = dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->system->theme, 'widgettitleformat');
        if (empty($wtscheme)) {
            $tplset = dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->system->theme, 'tplset');
            if (empty($tplset) || $tplset == DC_DEFAULT_TPLSET) {
                // Use H2 for mustek based themes
                $wtscheme = '<h2>%s</h2>';
            } else {
                // Use H3 for dotty based themes
                $wtscheme = '<h3>%s</h3>';
            }
        }

        return sprintf($wtscheme, $title);
    }

    /**
     * Render widget subtitle
     *
     * @param      null|string  $title   The title
     * @param      bool         $render  The render
     *
     * @return     string
     */
    public function renderSubtitle(?string $title, $render = true)
    {
        if (!$title && $render) {
            return '';
        }

        $wtscheme = dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->system->theme, 'widgetsubtitleformat');
        if (empty($wtscheme)) {
            $tplset = dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->system->theme, 'tplset');
            if (empty($tplset) || $tplset == DC_DEFAULT_TPLSET) {
                // Use H2 for mustek based themes
                $wtscheme = '<h3>%s</h3>';
            } else {
                // Use H3 for dotty based themes
                $wtscheme = '<h4>%s</h4>';
            }
        }
        if (!$render) {
            return $wtscheme;
        }

        return sprintf($wtscheme, $title);
    }

    // Widget settings

    /**
     * Gets the specified setting value.
     *
     * @param      string  $n      The setting name
     *
     * @return     mixed
     */
    public function __get(string $n)
    {
        if (isset($this->settings[$n])) {
            return $this->settings[$n]['value'];
        }
    }

    /**
     * Set the specified setting value
     *
     * @param      string  $n      The setting name
     * @param      mixed   $v      The new value
     */
    public function __set(string $n, $v)
    {
        if (isset($this->settings[$n])) {
            $this->settings[$n]['value'] = $v;
        }
    }

    /**
     * Store a setting
     *
     * @param      string     $name             The name
     * @param      string     $title            The title
     * @param      mixed      $value            The value
     * @param      string     $type             The type
     *
     * @return     bool|self
     */
    public function setting(string $name, string $title, $value, string $type = 'text')
    {
        $types = [
            // type (string) => list of items may be provided (boolean)
            'text'     => false,
            'textarea' => false,
            'check'    => false,
            'radio'    => true,
            'combo'    => true,
            'color'    => false,
            'email'    => false,
            'number'   => false,
        ];

        if (!array_key_exists($type, $types)) {
            return false;
        }

        $index = 4; // 1st optional argument (after type)

        if ($types[$type] && func_num_args() > $index) {
            $options = func_get_arg($index);
            if (!is_array($options)) {
                return false;
            }
            $index++;
        }

        // If any, the last argument should be an array (key → value) of opts
        if (func_num_args() > $index) {
            $opts = func_get_arg($index);
        }

        $this->settings[$name] = [
            'title' => $title,
            'type'  => $type,
            'value' => $value,
        ];

        if (isset($options)) {
            $this->settings[$name]['options'] = $options;
        }
        if (isset($opts)) {
            $this->settings[$name]['opts'] = $opts;
        }

        return $this;
    }

    /**
     * Get widget settings
     *
     * @return     array
     */
    public function settings(): array
    {
        return $this->settings;
    }

    /**
     * Get widget settings form
     *
     * @param      string  $pr     The prefix
     * @param      int     $i      The index
     *
     * @return     string
     */
    public function formSettings(string $pr = '', int &$i = 0): string
    {
        $res = '';
        foreach ($this->settings as $id => $s) {
            $res .= $this->formSetting($id, $s, $pr, $i);
            $i++;
        }

        return $res;
    }

    /**
     * Get a widget setting field
     *
     * @param      string       $id     The identifier
     * @param      array        $s      The setting
     * @param      string       $pr     The prefix
     * @param      int          $i      The index
     *
     * @return     string
     */
    public function formSetting(string $id, array $s, string $pr = '', int &$i = 0): string
    {
        $res   = '';
        $wfid  = 'wf-' . $i;
        $iname = $pr ? $pr . '[' . $id . ']' : $id;
        $class = (isset($s['opts']) && isset($s['opts']['class']) ? ' ' . $s['opts']['class'] : '');
        switch ($s['type']) {
            case 'text':
                $res .= '<p><label for="' . $wfid . '">' . $s['title'] . '</label> ' .
                form::field([$iname, $wfid], 20, 255, [
                    'default'    => html::escapeHTML($s['value']),
                    'class'      => 'maximal' . $class,
                    'extra_html' => 'lang="' . dcCore::app()->auth->getInfo('user_lang') . '" spellcheck="true"',
                ]) .
                '</p>';

                break;
            case 'textarea':
                $res .= '<p><label for="' . $wfid . '">' . $s['title'] . '</label> ' .
                form::textarea([$iname, $wfid], 30, 8, [
                    'default'    => html::escapeHTML($s['value']),
                    'class'      => 'maximal' . $class,
                    'extra_html' => 'lang="' . dcCore::app()->auth->getInfo('user_lang') . '" spellcheck="true"',
                ]) .
                '</p>';

                break;
            case 'check':
                $res .= '<p>' . form::hidden([$iname], '0') .
                '<label class="classic" for="' . $wfid . '">' .
                form::checkbox([$iname, $wfid], '1', $s['value'], $class) . ' ' . $s['title'] .
                '</label></p>';

                break;
            case 'radio':
                $res .= '<p>' . ($s['title'] ? '<label class="classic">' . $s['title'] . '</label><br/>' : '');
                if (!empty($s['options'])) {
                    foreach ($s['options'] as $k => $v) {
                        $res .= $k > 0 ? '<br/>' : '';
                        $res .= '<label class="classic" for="' . $wfid . '-' . $k . '">' .
                        form::radio([$iname, $wfid . '-' . $k], $v[1], $s['value'] == $v[1], $class) . ' ' . $v[0] .
                            '</label>';
                    }
                }
                $res .= '</p>';

                break;
            case 'combo':
                $res .= '<p><label for="' . $wfid . '">' . $s['title'] . '</label> ' .
                form::combo([$iname, $wfid], $s['options'], $s['value'], $class) .
                '</p>';

                break;
            case 'color':
                $res .= '<p><label for="' . $wfid . '">' . $s['title'] . '</label> ' .
                form::color([$iname, $wfid], [
                    'default' => $s['value'],
                ]) .
                '</p>';

                break;
            case 'email':
                $res .= '<p><label for="' . $wfid . '">' . $s['title'] . '</label> ' .
                form::email([$iname, $wfid], [
                    'default'      => html::escapeHTML($s['value']),
                    'autocomplete' => 'email',
                ]) .
                '</p>';

                break;
            case 'number':
                $res .= '<p><label for="' . $wfid . '">' . $s['title'] . '</label> ' .
                form::number([$iname, $wfid], [
                    'default' => $s['value'],
                ]) .
                '</p>';

                break;
        }

        return $res;
    }

    // Widget helpers

    /**
     * Adds a title setting.
     *
     * @param      string  $title  The title
     *
     * @return     bool|self
     */
    public function addTitle(string $title = '')
    {
        return $this->setting('title', __('Title (optional)') . ' :', $title);
    }

    /**
     * Adds a home only setting.
     *
     * @return     bool|self
     */
    public function addHomeOnly(?array $options = null)
    {
        $list = [
            __('All pages')           => self::ALL_PAGES,
            __('Home page only')      => self::HOME_ONLY,
            __('Except on home page') => self::EXCEPT_HOME, ];

        if ($options !== null) {
            $list = array_merge($list, $options);
        }

        return $this->setting(
            'homeonly',
            __('Display on:'),
            self::ALL_PAGES,
            'combo',
            $list
        );
    }

    /**
     * Check if the widget should be displayed, depending on its homeonly setting
     *
     * @param      string  $type          The type
     * @param      int     $alt_not_home  Alternate not home test value
     * @param      int     $alt_home      Alternate home test value
     *
     * @return     bool
     */
    public function checkHomeOnly($type, $alt_not_home = 1, $alt_home = 0)
    {
        if (isset($this->settings['homeonly']) && isset($this->settings['homeonly']['value'])) {
            if (($this->settings['homeonly']['value'] == self::HOME_ONLY && !dcCore::app()->url->isHome($type) && $alt_not_home) || ($this->settings['homeonly']['value'] == self::EXCEPT_HOME && (dcCore::app()->url->isHome($type) || $alt_home))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds a content only setting.
     *
     * @param      int     $content_only  The content only flag
     *
     * @return     self|bool
     */
    public function addContentOnly(int $content_only = 0)
    {
        return $this->setting('content_only', __('Content only'), $content_only, 'check');
    }

    /**
     * Adds a class setting.
     *
     * @param      string  $class  The class
     *
     * @return     self|bool
     */
    public function addClass(string $class = '')
    {
        return $this->setting('class', __('CSS class:'), $class);
    }

    /**
     * Adds an offline setting.
     *
     * @param      int     $offline  The offline flag
     *
     * @return     self|bool
     */
    public function addOffline(int $offline = 0)
    {
        return $this->setting('offline', __('Offline'), $offline, 'check');
    }

    /**
     * Determines if setting is offline.
     *
     * @return     bool  True if offline, False otherwise.
     */
    public function isOffline(): bool
    {
        return $this->settings['offline']['value'] ?? false;
    }
}
