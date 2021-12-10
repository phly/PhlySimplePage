<?php

declare(strict_types=1);

namespace PhlySimplePage;

use Psr\Container\ContainerInterface;

class PageCacheListenerFactory
{
    public function __invoke(ContainerInterface $container): PageCacheListener
    {
        return new PageCacheListener(
            $container->get(__NAMESPACE__ . '\PageCache')
        );
    }
}
