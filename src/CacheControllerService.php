<?php

/**
 * @link      https://github.com/weierophinney/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2012 Matthew Weier O'Phinney (http://mwop.net)
 * @license   https://github.com/weierophinney/PhlySimplePage/blog/master/LICENSE.md New BSD License
 */

namespace PhlySimplePage;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Service factory for CacheController
 */
class CacheControllerService implements FactoryInterface
{
    /**
     * Create and return CacheController
     *
     * @param  ServiceLocatorInterface $controllers
     * @return CacheController
     * @throws Exception\ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator();
        }

        $cache    = $container->get('PhlySimplePage\PageCache');
        $console  = $container->get('Console');

        $controller = new CacheController();
        $controller->setCache($cache);
        $controller->setConsole($console);
        return $controller;
    }

    /**
     * Create and return CacheController
     *
     * @param  ServiceLocatorInterface $controllers
     * @return CacheController
     * @throws Exception\ServiceNotCreatedException
     */
    public function createService(ServiceLocatorInterface $controllers)
    {
        $container = $controllers->getServiceLocator();
        return $this($container);
    }
}
