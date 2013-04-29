<?php
/**
 * @link      https://github.com/weierophinney/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2012 Matthew Weier O'Phinney (http://mwop.net)
 * @license   https://github.com/weierophinney/PhlySimplePage/blog/master/LICENSE.md New BSD License
 */

namespace PhlySimplePage;

use Zend\Cache\Storage\Adapter\AbstractAdapter;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

/**
 * Event listener implementing page level caching for pages provided by the
 * PageController.
 */
class PageCacheListener implements ListenerAggregateInterface
{
    /**
     * @var AbstractAdapter
     */
    protected $cache;

    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * Whether or not to cache this request
     *
     * @var bool
     */
    protected $cacheThisRequest = false;

    /**
     * Key to use when caching
     *
     * @var string
     */
    protected $cacheKey;

    /**
     * Constructor
     *
     * @param AbstractAdapter $cache
     */
    public function __construct(AbstractAdapter $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Attach listeners to the application event system.
     *
     * Registers two event listeners:
     *
     * - route, at priority -99,
     * - finish, at priority -10001,
     *
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $events->attach('route', array($this, 'onRoutePost'), -99);
        $events->attach('finish', array($this, 'onFinishPost'), -10001);
    }

    /**
     * Detach any registered listeners from the given event manager instance.
     *
     * @param EventManagerInterface $events
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * "route" event listener
     *
     * Checks to see if (a) we have a controller we're interested in, (b) if
     * a "template" was provided in the route matches, and (c) if we have a
     * cache hit for that template name. If we do, we return a populated
     * response; if not, we continue, but indicate that we should cache the
     * response on completion.
     *
     * @param  \Zend\Mvc\MvcEvent $e
     * @return null|\Zend\Stdlib\ResponseInterface
     */
    public function onRoutePost($e)
    {
        $matches = $e->getRouteMatch();
        if (!$matches) {
            return;
        }

        $controller = $matches->getParam('controller');
        if ($controller != 'PhlySimplePage\Controller\Page') {
            return;
        }

        // Is caching disabled for this route?
        $doNotCache = $matches->getParam('do_not_cache', false);
        if ($doNotCache) {
            return;
        }

        $template = $matches->getParam('template', false);
        if (!$template) {
            return;
        }

        $cacheKey = Module::normalizeCacheKey($template);

        $result = $this->cache->getItem($cacheKey, $success);
        if (!$success) {
            // Not a cache hit; keep working, but indicate we should cache this
            $this->cacheThisRequest = true;
            $this->cacheKey         = $cacheKey;
            return;
        }

        // Got a cache hit; inject it in the response and return the response.
        $response = $e->getResponse();
        $response->setContent($result);
        return $response;
    }

    /**
     * "finish" event listener
     *
     * Checks to see if we should cache the current request; if so, it does.
     *
     * @param  \Zend\Mvc\MvcEvent $e
     */
    public function onFinishPost($e)
    {
        if (!$this->cacheThisRequest || !$this->cacheKey) {
            return;
        }

        // Cache the result
        $response = $e->getResponse();
        $content  = $response->getContent();
        $this->cache->setItem($this->cacheKey, $content);
    }
}
