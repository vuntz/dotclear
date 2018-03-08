<?php
# -- BEGIN LICENSE BLOCK ---------------------------------------
#
# This file is part of Antispam, a plugin for Dotclear 2.
#
# Copyright (c) 2003-2013 Olivier Meunier & Association Dotclear
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK -----------------------------------------
if (!defined('DC_RC_PATH')) {return;}

$this->registerModule(
    "Antispam",                             // Name
    "Generic antispam plugin for Dotclear", // Description
    "Alain Vagner",                         // Author
    '1.4.1',                                // Version
    array(
        'permissions' => 'usage,contentadmin',
        'priority'    => 10,
        'settings'    => array(
            'self' => '',
            'blog' => '#params.antispam_params'
        )
    )
);
