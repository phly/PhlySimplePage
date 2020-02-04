<?php
/**
 * @link      https://github.com/weierophinney/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2012 Matthew Weier O'Phinney (http://mwop.net)
 * @license   https://github.com/weierophinney/PhlySimplePage/blog/master/LICENSE.md New BSD License
 */

namespace PhlySimplePage;

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
    public function createService(ServiceLocatorInterface $services)
    {
        $cache    = $services->get('PhlySimplePage\PageCache');
        return new PageCacheListener($cache);
    }
}
