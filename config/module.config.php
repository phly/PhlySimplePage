<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'PhlySimplePage\Controller\Page' => 'PhlySimplePage\PageController',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'PhlySimplePage\PageCacheListener' => 'PhlySimplePage\PageCacheListenerService',
        ),
    ),
);
