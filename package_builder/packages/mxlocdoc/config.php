<?php

return [
    'name' => 'mxLocDoc',
    'name_lower' => 'mxlocdoc',
    'name_short' => 'mxlocdoc',
    'version' => '2.0.0',
    'release' => 'pl',
    'php_version' => '8.1',

    'paths' => [
        'core' => 'core/components/mxlocdoc/',
        'assets' => 'assets/components/mxlocdoc/',
    ],

    'elements' => [
        'category' => 'mxLocDoc',
        'settings' => 'elements/settings.php',
        'plugins' => 'elements/plugins.php',
        'menus' => 'elements/menus.php',
    ],

    'static' => [
        'plugins' => false,
    ],

    'encrypt' => false,

    'build' => [
        'download' => false,
        'install' => false,
        'update' => [
            'plugins' => true,
            'settings' => false,
            'menus' => true,
        ],
    ],
];
