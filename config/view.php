<?php
return [
    'view_path' => ROOT_DIR . 'resource/view/',
    'engine' => 'twig', // Access default view engine: 'php', 'bladeone', 'twig', etc.
    'drives' => [
        // BladeOne view drive configuration
        'bladeone' => [
            'class' => \DFrame\Application\Drive\View\BladeOneDrive::class,
            'options' => [
                'compiled_path' => INDEX_DIR . 'cache/view/compiled/',
                'cache' => true,
                'debug' => true,
            ],
        ],
        // Twig view drive configuration
        'twig' => [
            'class' => \DFrame\Application\Drive\View\TwigDrive::class,
            'options' => [
                'cache' => false,
                // Register custom functions for Twig templates
                'functions' => [
                    // key is the function name in Twig, value is the callable
                    'route' => [\DFrame\Application\Router::class, 'route'],
                    'dump' => 'dump',
                    'dd' => 'dd',
                    // Add more functions if needed
                ],
            ],
        ],
    ]
];