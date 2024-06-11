<?php

return [
    'templates' => [
        'extension' => 'twig',
        'paths' => [
            // // namespace / path pairs
            // //
            // // Numeric namespaces imply the default/main namespace. Paths may be
            // // strings or arrays of string paths to associate with the namespace.
        ],
    ],
    'twig' => [
        'autoescape' => 'html',
        'cache_dir' => __DIR__ . '/../data/cache/',
        'auto_reload' => true, // Recompile the template whenever the source code changes
    ],
];