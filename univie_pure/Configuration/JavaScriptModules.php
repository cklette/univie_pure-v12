<?php

return [
    // required import configurations of other extensions,
    // in case a module imports from another package
    'imports' => [
        // recursive definiton, all *.js files in this folder are import-mapped
        // trailing slash is required per importmap-specification
        '@univie/univiepure/' => [
            'path' => 'EXT:univie_pure/Resources/Public/JavaScript/',
        ],
    ],
];
