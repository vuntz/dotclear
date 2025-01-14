<?php
/**
 * @package Dotclear
 * @subpackage Backend
 *
 * @copyright Olivier Meunier & Association Dotclear
 * @copyright GPL-2.0-only
 */
class flatImport extends flatImportV2
{
    /**
     * Constructs a new instance.
     */
    public function __construct(dcCore $core, $file)    // @phpstan-ignore-line
    {
        parent::__construct($file);
    }
}
