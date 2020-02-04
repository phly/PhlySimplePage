<?php

namespace PhlySimplePage;

use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'controllers'     => [
        'aliases'   => [
            'PhlySimplePage\Controller\Page' => PageController::class,
        ],
        'factories' => [
            PageController::class => InvokableFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            ClearCacheCommand::class => ClearCacheCommandFactory::class,
            PageCacheListener::class => PageCacheListenerFactory::class,
        ],
    ],
];
