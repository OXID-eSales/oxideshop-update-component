<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

$aTheme = [
    'id'          => 'testTheme',
    'title'       => 'Test Theme',
    'description' => 'Theme for migration tests',
    'thumbnail'   => 'theme.jpg',
    'version'     => '1.0.0',
    'author'      => 'Partner Author',
    'parentTheme' => 'apex',
    'parentVersions' => ['1.0'],
    'settings'    => [
        [
            'group' => 'display',
            'name'  => 'blShowSomething',
            'type'  => 'bool',
            'value' => 1,
        ],
        [
            'group'    => 'display',
            'name'     => 'sPartnerCustomSetting',
            'type'     => 'str',
            'value'    => 'partnerValue',
            'position' => 5,
        ],
        [
            'group'       => 'features',
            'name'        => 'sListDisplayType',
            'type'        => 'select',
            'value'       => 'grid',
            'constraints' => 'grid|line|infogrid',
        ],
        [
            'group' => 'features',
            'name'  => 'aMultiValues',
            'type'  => 'arr',
            'value' => ['one', 'two'],
        ],
    ],
];
