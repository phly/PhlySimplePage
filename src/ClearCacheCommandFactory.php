<?php

declare(strict_types=1);

namespace PhlySimplePage;

use Psr\Container\ContainerInterface;

class ClearCacheCommandFactory
{
    public function __invoke(ContainerInterface $container): ClearCacheCommand
    {
        return new ClearCacheCommand(
            $container->get('PhlySimplePage\PageCache')
        );
    }
}
