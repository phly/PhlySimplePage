<?php

namespace PhlySimplePage;

return array(
    'controllers' => array(
        'invokables' => array(
            'PhlySimplePage\Controller\Page' => PageController::class,
        ),
        'factories' => array(
            'PhlySimplePage\Controller\Cache' => CacheControllerService::class,
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            ClearCacheCommand::class => ClearCacheCommandFactory::class,
            PageCacheListener::class => PageCacheListenerService::class,
        ),
    ),
);
