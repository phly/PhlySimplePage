<?php
/**
 * @link      https://github.com/weierophinney/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2012 Matthew Weier O'Phinney (http://mwop.net)
 * @license   https://github.com/weierophinney/PhlySimplePage/blog/master/LICENSE.md New BSD License
 */

namespace PhlySimplePage;

use Zend\Cache\StorageFactory;
use Zend\ServiceManager\Exception;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Service factory for cache adapter used for page caching
 */
class PageCacheService implements FactoryInterface
{
    /**
     * Create and return cache storage adapter
     *
     * @param  ServiceLocatorInterface $services
     * @return \Zend\Cache\Storage\Adapter\AbstractAdapter
     * @throws Exception\ServiceNotCreatedException
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $config = $services->get('Config');
        if (!isset($config['phly-simple-page'], $config['phly-simple-page']['cache'])) {
            throw new Exception\ServiceNotCreatedException(sprintf(
                '%s could not create a cache storage adapter, as the ["phly-simple-page"] and/or ["phly-simple-page"]["cache"] key was missing',
                __CLASS__
            ));
        }

        $cacheConfig = $config['phly-simple-page']['cache'];

        $cache = StorageFactory::factory($cacheConfig);
        return $cache;
    }
}
