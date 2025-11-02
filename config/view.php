<?php
return [
    'view_path' => ROOT_DIR . 'resource/view/',
    'engine' => 'bladeone',
    'drives' => [
        // BladeOne view drive configuration
        'bladeone' => [
            'class' => \Core\Application\Drive\View\BladeOneDrive::class,
            'options' => [
                'compiled_path' => INDEX_DIR . 'cache/view/compiled/',
                'cache' => true,
                'debug' => true,
            ]
        ]
    ]
];