<?php

declare(strict_types=1);

namespace PhlySimplePageTest;

use Laminas\Cache\Storage\Adapter\AbstractAdapter;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\SharedEventManager;
use Laminas\EventManager\Test\EventListenerIntrospectionTrait;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PhlySimplePage\Module;
use PhlySimplePage\PageCacheListener;
use PhlySimplePage\PageController;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use stdClass;

class ModuleTest extends TestCase
{
    use EventListenerIntrospectionTrait;
    use RouteMatchCreationTrait;

    public function setUp(): void
    {
        $this->sharedEvents = new SharedEventManager();
        $this->events       = new EventManager($this->sharedEvents);

        $this->application = new TestAsset\Application();
        $this->application->setEventManager($this->events);

        $this->matches = $this->createRouteMatch([
            'controller' => 'PhlySimplePage\Controller\Page',
        ]);

        $this->event = new MvcEvent();
        $this->event->setApplication($this->application);
        $this->event->setRouteMatch($this->matches);
        $this->event->setTarget($this->application);

        $this->module = new Module();
    }

    protected function getEmptyMockForServiceManager(): ObjectProphecy
    {
        $services = $this->prophesize(ServiceLocatorInterface::class);
        $services
            ->has('PhlySimplePage\PageCache')
            ->willReturn(false)
            ->shouldBeCalled();
        return $services;
    }

    public function testBootstrapListenerRegistersRouteListener(): void
    {
        $cache    = $this->prophesize(AbstractAdapter::class);
        $listener = new PageCacheListener($cache->reveal());
        $services = $this->getEmptyMockForServiceManager();
        $services
            ->get(PageCacheListener::class)
            ->willReturn($listener);

        $this->application->services = $services->reveal();

        $this->module->onBootstrap($this->event);
        $listeners = $this->getListenersForEvent('route', $this->events, true);
        $count     = 0;
        foreach ($listeners as $priority => $listener) {
            $count += 1;
            $this->assertSame(-100, $priority);
            $this->assertSame([$this->module, 'onRoutePost'], $listener);
        }
        $this->assertSame(1, $count);
    }

    public function testRouteListenerRegistersSharedListenerForPageControllerDispatchEvent(): void
    {
        $this->module->onRoutePost($this->event);

        $listeners = $this->sharedEvents->getListeners([PageController::class], 'dispatch');

        $this->assertCount(1, $listeners);

        // EventManager v3
        // phpcs:disable
        foreach ($listeners as $priority => $listener) {
        }
        // phpcs:enable
        $this->assertSame(-1, $priority);
        $this->assertSame([[$this->module, 'onDispatchPost']], $listener);
    }

    public function testRouteListenerDoesNotRegistersSharedDispatchEventListenerWhenNoRouteMatchesPresentInEvent(): void
    {
        $event = new MvcEvent();
        $event->setApplication($this->application);
        $event->setTarget($this->application);

        $this->module->onRoutePost($event);
        $listeners = $this->sharedEvents->getListeners([PageController::class], 'dispatch');

        $this->assertEmpty($listeners);
    }

    public function testRouteListenerDoesNotRegistersSharedDispatchEventListenerWhenControllerUnmatched(): void
    {
        $matches = $this->createRouteMatch([
            'controller' => 'Some\Controller\Other',
        ]);

        $event = new MvcEvent();
        $event->setApplication($this->application);
        $event->setRouteMatch($matches);
        $event->setTarget($this->application);

        $this->module->onRoutePost($event);
        $listeners = $this->sharedEvents->getListeners([PageController::class], 'dispatch');

        $this->assertEmpty($listeners);
    }

    public function testOnDispatchPostTriggersDispatchErrorIfEventHasInvalidControllerErrorComposed(): void
    {
        $controller = new PageController();
        $this->event->setTarget($controller);
        $this->event->setError(Application::ERROR_CONTROLLER_INVALID);

        $test         = new stdClass();
        $test->called = false;
        $this->events->attach('dispatch.error', function ($e) use ($test) {
            $test->called = true;
        });

        $this->module->onDispatchPost($this->event);
        $this->assertTrue($test->called);
    }

    public function testOnDispatchDoesNotTriggerDispatchErrorIfTargetIsNotPageController(): void
    {
        $this->event->setError(Application::ERROR_CONTROLLER_INVALID);

        $test         = new stdClass();
        $test->called = false;
        $this->events->attach('dispatch.error', function ($e) use ($test) {
            $test->called = true;
        });

        $this->module->onDispatchPost($this->event);
        $this->assertFalse($test->called);
    }

    public function testOnDispatchPostDoesNotTriggerDispatchErrorIfeInvalidControllerErrorComposedInEvent(): void
    {
        $controller = new PageController();
        $this->event->setTarget($controller);

        $test         = new stdClass();
        $test->called = false;
        $this->events->attach('dispatch.error', function ($e) use ($test) {
            $test->called = true;
        });

        $this->module->onDispatchPost($this->event);
        $this->assertFalse($test->called);
    }
}
