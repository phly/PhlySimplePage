<?php

/**
 * @link      https://github.com/weierophinney/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2012 Matthew Weier O'Phinney (http://mwop.net)
 * @license   https://github.com/weierophinney/PhlySimplePage/blog/master/LICENSE.md New BSD License
 */

namespace PhlySimplePage;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Service factory for page cache service
 */
class PageCacheListenerService implements FactoryInterface
{
    /**
     * Create and return page cache listener
     *
     * @param  ServiceLocatorInterface $services
     * @return PageCacheListener
     * @throws Exception\ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new PageCacheListener($container->get('PhlySimplePage\PageCache'));
    }

    /**
     * Create and return page cache listener
     *
     * @param  ServiceLocatorInterface $services
     * @return PageCacheListener
     * @throws Exception\ServiceNotCreatedException
     */
    public function createService(ServiceLocatorInterface $services)
    {
        return $this($services);
    }
}
