<?php

return [
    'mxLocDocCacheClear' => [
        'description' => 'Clears mxLocDoc cache when MODX manager cache is cleared.',
        'content' => 'file:elements/plugins/plugin.mxlocdoc_cache_clear.php',
        'events' => [
            'OnBeforeCacheUpdate',
        ],
    ],
];
