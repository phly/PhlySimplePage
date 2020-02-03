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
    'console' => array('router' => array('routes' => array(
        'phly-simple-page-clearall' => array('options' => array(
            'route' => 'phlysimplepage cache clear all',
            'defaults' => array(
                'controller' => 'PhlySimplePage\Controller\Cache',
                'action'     => 'clearAll',
            ),
        )),
        'phly-simple-page-clearone' => array('options' => array(
            'route' => 'phlysimplepage cache clear --page=',
            'defaults' => array(
                'controller' => 'PhlySimplePage\Controller\Cache',
                'action'     => 'clearOne',
            ),
        )),
    ))),
);
