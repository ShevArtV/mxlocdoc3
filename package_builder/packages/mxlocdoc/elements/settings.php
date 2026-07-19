<?php

return [
    'mxlocdoc.docs_path' => [
        'xtype' => 'textfield',
        'value' => '[[+corePath]]components/mxlocdoc/docs/',
        'area' => 'mxlocdoc:filesystem',
    ],
    'mxlocdoc.languages' => [
        'xtype' => 'textfield',
        'value' => 'en,ru',
        'area' => 'mxlocdoc:navigation',
    ],
    'mxlocdoc.default_file' => [
        'xtype' => 'textfield',
        'value' => 'README.md',
        'area' => 'mxlocdoc:navigation',
    ],
    'mxlocdoc.nav_file' => [
        'xtype' => 'textfield',
        'value' => '_sidebar.json',
        'area' => 'mxlocdoc:navigation',
    ],
    'mxlocdoc.search_enabled' => [
        'xtype' => 'combo-boolean',
        'value' => '1',
        'area' => 'mxlocdoc:search',
    ],
    'mxlocdoc.cache_ttl' => [
        'xtype' => 'numberfield',
        'value' => '300',
        'area' => 'mxlocdoc:cache',
    ],
    'mxlocdoc.max_file_size' => [
        'xtype' => 'numberfield',
        'value' => '1048576',
        'area' => 'mxlocdoc:filesystem',
    ],
    'mxlocdoc.allowed_asset_extensions' => [
        'xtype' => 'textfield',
        'value' => 'jpg,jpeg,png,gif,webp,svg',
        'area' => 'mxlocdoc:filesystem',
    ],
];
