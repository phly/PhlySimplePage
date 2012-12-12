<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'PhlySimplePage\Controller\Page' => 'PhlySimplePage\PageController',
        ),
        'factories' => array(
            'PhlySimplePage\Controller\Cache' => 'PhlySimplePage\CacheControllerService',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'PhlySimplePage\PageCacheListener' => 'PhlySimplePage\PageCacheListenerService',
        ),
    ),
    'console' => array('router' => array('routes' => array(
        'phly-simple-page-clearall' => array(
            'type' => 'Simple',
            'options' => array(
                'route' => 'PhlySimplePage cache clear all',
                'defaults' => array(
                    'controller' => 'PhlySimplePage\Controller\Cache',
                    'action'     => 'clearAll',
                ),
            ),
        ),
        'phly-simple-page-clearone' => array(
            'type' => 'Simple',
            'options' => array(
                'route' => 'PhlySimplePage cache clear --page=',
                'defaults' => array(
                    'controller' => 'PhlySimplePage\Controller\Cache',
                    'action'     => 'clearOne',
                ),
            ),
        ),
    ))),
);
