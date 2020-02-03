<?php
/**
 * @link      https://github.com/weierophinney/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2012-2020 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/weierophinney/PhlySimplePage/blog/master/LICENSE.md New BSD License
 */

namespace PhlySimplePageTest;

use PhlySimplePage\Module;
use PhlySimplePage\PageCacheListener;
use PhlySimplePage\PageController;
use PHPUnit\Framework\TestCase;
use stdClass;
use Zend\Cache\Storage\Adapter\AbstractAdapter;
use Zend\EventManager\EventManager;
use Zend\EventManager\SharedEventManager;
use Zend\EventManager\StaticEventManager;
use Zend\EventManager\Test\EventListenerIntrospectionTrait;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Router\RouteMatch;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Unit tests for PhlySimplePage\Module
 */
class ModuleTest extends TestCase
{
    use EventListenerIntrospectionTrait;

    public function setUp()
    {
        $this->sharedEvents = new SharedEventManager();

        if (class_exists(StaticEventManager::class)) {
            $this->events       = new EventManager();
            $this->events->setSharedManager($this->sharedEvents);
        } else {
            $this->events       = new EventManager($this->sharedEvents);
        }

        $this->application = new TestAsset\Application();
        $this->application->setEventManager($this->events);

        $this->matches = new RouteMatch(array(
            'controller' => 'PhlySimplePage\Controller\Page',
        ));

        $this->event = new MvcEvent();
        $this->event->setApplication($this->application);
        $this->event->setRouteMatch($this->matches);
        $this->event->setTarget($this->application);

        $this->module = new Module();
    }

    public function tearDown()
    {
        // Need to do this to ensure other tests in suite do not get state
        if (class_exists(StaticEventManager::class)) {
            StaticEventManager::resetInstance();
        }
    }

    protected function getEmptyMockForServiceManager()
    {
        $services = $this->prophesize(ServiceLocatorInterface::class);
        $services
            ->has('PhlySimplePage\PageCache')
            ->willReturn(false)
            ->shouldBeCalled();
        return $services;
    }

    public function testBootstrapListenerRegistersRouteListener()
    {
        $cache = $this->prophesize(AbstractAdapter::class);
        $listener = new PageCacheListener($cache->reveal());
        $services = $this->getEmptyMockForServiceManager();
        $services
            ->get(PageCacheListener::class)
            ->willReturn($listener);

        $this->application->services = $services->reveal();

        $this->module->onBootstrap($this->event);
        $listeners = $this->getListenersForEvent('route', $this->events, true);
        $count = 0;
        foreach ($listeners as $priority => $listener) {
            $count += 1;
            $this->assertSame(-100, $priority);
            $this->assertSame([$this->module, 'onRoutePost'], $listener);
        }
        $this->assertSame(1, $count);
    }

    public function testRouteListenerRegistersSharedListenerForPageControllerDispatchEvent()
    {
        $this->module->onRoutePost($this->event);

        $listeners = class_exists(StaticEventManager::class)
            ? $this->sharedEvents->getListeners(PageController::class, 'dispatch')
            : $this->sharedEvents->getListeners([PageController::class], 'dispatch');

        $this->assertCount(1, $listeners);

        if (class_exists(StaticEventManager::class)) {
            // EventManager v2
            foreach ($listeners as $listener) {
            }
            $callback = $listener->getCallback();
            $this->assertSame(-1, $listener->getMetadatum('priority'));
            $this->assertSame([$this->module, 'onDispatchPost'], $callback);
            return;
        }

        // EventManager v3
        foreach ($listeners as $priority => $listener) {
        }
        $this->assertSame(-1, $priority);
        $this->assertSame([[$this->module, 'onDispatchPost']], $listener);
    }

    public function testRouteListenerDoesNotRegistersSharedDispatchEventListenerWhenNoRouteMatchesPresentInEvent()
    {
        $event = new MvcEvent();
        $event->setApplication($this->application);
        $event->setTarget($this->application);

        $this->module->onRoutePost($event);
        $listeners = class_exists(StaticEventManager::class)
            ? $this->sharedEvents->getListeners(PageController::class, 'dispatch')
            : $this->sharedEvents->getListeners([PageController::class], 'dispatch');

        class_exists(StaticEventManager::class)
            ? $this->assertFalse($listeners)
            : $this->assertEmpty($listeners);
    }

    public function testRouteListenerDoesNotRegistersSharedDispatchEventListenerWhenRouteMatchControllerDoesNotMatch()
    {
        $matches = new RouteMatch(array(
            'controller' => 'Some\Controller\Other',
        ));

        $event = new MvcEvent();
        $event->setApplication($this->application);
        $event->setRouteMatch($matches);
        $event->setTarget($this->application);

        $this->module->onRoutePost($event);
        $listeners = class_exists(StaticEventManager::class)
            ? $this->sharedEvents->getListeners(PageController::class, 'dispatch')
            : $this->sharedEvents->getListeners([PageController::class], 'dispatch');

        class_exists(StaticEventManager::class)
            ? $this->assertFalse($listeners)
            : $this->assertEmpty($listeners);
    }

    public function testOnDispatchPostTriggersDispatchErrorIfEventHasInvalidControllerErrorComposed()
    {
        $controller = new PageController();
        $this->event->setTarget($controller);
        $this->event->setError(Application::ERROR_CONTROLLER_INVALID);

        $test = new stdClass;
        $test->called = false;
        $this->events->attach('dispatch.error', function ($e) use ($test) {
            $test->called = true;
        });

        $this->module->onDispatchPost($this->event);
        $this->assertTrue($test->called);
    }

    public function testOnDispatchDoesNotTriggerDispatchErrorIfTargetIsNotPageController()
    {
        $this->event->setError(Application::ERROR_CONTROLLER_INVALID);

        $test = new stdClass;
        $test->called = false;
        $this->events->attach('dispatch.error', function ($e) use ($test) {
            $test->called = true;
        });

        $this->module->onDispatchPost($this->event);
        $this->assertFalse($test->called);
    }

    public function testOnDispatchPostDoesNotTriggerDispatchErrorIfEventDoesNotHaveInvalidControllerErrorComposed()
    {
        $controller = new PageController();
        $this->event->setTarget($controller);

        $test = new stdClass;
        $test->called = false;
        $this->events->attach('dispatch.error', function ($e) use ($test) {
            $test->called = true;
        });

        $this->module->onDispatchPost($this->event);
        $this->assertFalse($test->called);
    }
}
