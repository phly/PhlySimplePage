<?php

/**
 * @link      https://github.com/weierophinney/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2012-2020 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/weierophinney/PhlySimplePage/blog/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlySimplePage;

use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\ResponseInterface;

use function str_replace;

class Module
{
    /**
     * Normalize a cache key
     */
    public static function normalizeCacheKey(string $key): string
    {
        return str_replace(['/', '\\', '.'], '_', $key);
    }

    /**
     * Retrieve application configuration for this module
     */
    public function getConfig(): array
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Listen to the application bootstrap event
     *
     * Registers a post-routing event. Additionally, if the
     * "PhlySimplePage\PageCache" service is registered, it will pull the
     * "PhlySimplePage\PageCacheListener" service and attach it to the
     * event manager.
     */
    public function onBootstrap(MvcEvent $e): void
    {
        $app    = $e->getTarget();
        $events = $app->getEventManager();
        $events->attach('route', [$this, 'onRoutePost'], -100);

        $services = $app->getServiceManager();
        if ($services->has('PhlySimplePage\PageCache')) {
            $listener = $services->get(PageCacheListener::class);
            $listener->attach($events);
        }
    }

    /**
     * Listen to the application route event
     *
     * Registers a post-dispatch listener on the controller if the matched
     * controller is the PageController from this module.
     */
    public function onRoutePost(MvcEvent $e): void
    {
        $matches = $e->getRouteMatch();
        if (! $matches) {
            return;
        }

        $controller = $matches->getParam('controller');
        if (! in_array($controller, ['PhlySimplePage\Controller\Page', PageController::class], true)) {
            return;
        }

        $app    = $e->getTarget();
        $events = $app->getEventManager();
        $shared = $events->getSharedManager();
        $shared->attach(PageController::class, 'dispatch', [$this, 'onDispatchPost'], -1);
    }

    /**
     * Listen to the dispatch event from the PageController
     *
     * If the controller result is a 404 status, triggers the application
     * dispatch.error event.
     */
    public function onDispatchPost(MvcEvent $e): ?ResponseInterface
    {
        $target = $e->getTarget();
        if (! $target instanceof PageController) {
            return null;
        }

        $error = $e->getError();
        if ($error !== Application::ERROR_CONTROLLER_INVALID) {
            return null;
        }

        $app     = $e->getApplication();
        $results = $app->getEventManager()->trigger('dispatch.error', $app, $e);
        $return  = $results->last();

        if ($return instanceof ResponseInterface) {
            return $return;
        }

        if ($return) {
            $e->setResult($return);
        }

        return null;
    }
}
