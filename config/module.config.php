<?php

namespace PhlySimplePage;

return array(
    'controllers' => array(
        'invokables' => array(
            'PhlySimplePage\Controller\Page' => PageController::class,
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            ClearCacheCommand::class => ClearCacheCommandFactory::class,
            PageCacheListener::class => PageCacheListenerFactory::class,
        ),
    ),
);
