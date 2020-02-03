<?php
/**
 * @link      https://github.com/weierophinney/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2012 Matthew Weier O'Phinney (http://mwop.net)
 * @license   https://github.com/weierophinney/PhlySimplePage/blog/master/LICENSE.md New BSD License
 */

namespace PhlySimplePage;

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
    public function createService(ServiceLocatorInterface $controllers)
    {
        $services = $controllers->getServiceLocator();
        $cache    = $services->get('PhlySimplePage\PageCache');
        $console  = $services->get('Console');

        $controller = new CacheController();
        $controller->setCache($cache);
        $controller->setConsole($console);
        return $controller;
    }
}
