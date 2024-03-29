<?php

declare(strict_types=1);

namespace PhlySimplePage;

use Laminas\Cache\Storage\Adapter\AbstractAdapter;
use Laminas\Cache\StorageFactory;
use Laminas\ServiceManager\Exception;
use Psr\Container\ContainerInterface;

use function sprintf;

class PageCacheFactory
{
    /**
     * Create and return cache storage adapter
     *
     * @throws Exception\ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container): AbstractAdapter
    {
        $config = $container->get('config')['phly-simple-page'] ?? [];
        if (! isset($config['cache'])) {
            throw new Exception\ServiceNotCreatedException(sprintf(
                '%s could not create a cache storage adapter, as the ["phly-simple-page"]["cache"]'
                . ' key is missing',
                self::class
            ));
        }

        return StorageFactory::factory($config['cache']);
    }
}
