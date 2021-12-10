<?php

/**
 * @link      https://github.com/phly/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2012-2020 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/phly/PhlySimplePage/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlySimplePage;

use Laminas\Cache\Storage\Adapter\AbstractAdapter;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\CallbackHandler;
use Laminas\Stdlib\ResponseInterface;

use function in_array;

/**
 * Event listener implementing page level caching for pages provided by the
 * PageController.
 */
class PageCacheListener implements ListenerAggregateInterface
{
    /** @var AbstractAdapter */
    protected $cache;

    /** @var CallbackHandler[] */
    protected $listeners = [];

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
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1): void
    {
        $this->listeners[] = $events->attach('route', [$this, 'onRoutePost'], -99);
        $this->listeners[] = $events->attach('finish', [$this, 'onFinishPost'], -10001);
    }

    /**
     * Detach any registered listeners from the given event manager instance.
     */
    public function detach(EventManagerInterface $events): void
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
     */
    public function onRoutePost(MvcEvent $e): ?ResponseInterface
    {
        $matches = $e->getRouteMatch();
        if (! $matches) {
            return null;
        }

        $controller = $matches->getParam('controller');
        if (! in_array($controller, ['PhlySimplePage\Controller\Page', PageController::class], true)) {
            return null;
        }

        // Is caching disabled for this route?
        $doNotCache = $matches->getParam('do_not_cache', false);
        if ($doNotCache) {
            return null;
        }

        $template = $matches->getParam('template', false);
        if (! $template) {
            return null;
        }

        $cacheKey = Module::normalizeCacheKey($template);

        $result = $this->cache->getItem($cacheKey, $success);
        if (! $success) {
            // Not a cache hit; keep working, but indicate we should cache this
            $this->cacheThisRequest = true;
            $this->cacheKey         = $cacheKey;
            return null;
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
     */
    public function onFinishPost(MvcEvent $e): void
    {
        if (! $this->cacheThisRequest || ! $this->cacheKey) {
            return;
        }

        // Cache the result
        $response = $e->getResponse();
        $content  = $response->getContent();
        $this->cache->setItem($this->cacheKey, $content);
    }
}
