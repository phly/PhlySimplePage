<?php
/**
 * @link      https://github.com/weierophinney/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2012 Matthew Weier O'Phinney (http://mwop.net)
 * @license   https://github.com/weierophinney/PhlySimplePage/blog/master/LICENSE.md New BSD License
 */

namespace PhlySimplePageTest;

use PhlySimplePage\Module;
use PhlySimplePage\PageController;
use PHPUnit_Framework_TestCase as TestCase;
use stdClass;
use Zend\EventManager\EventManager;
use Zend\EventManager\SharedEventManager;
use Zend\EventManager\StaticEventManager;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

/**
 * Unit tests for PhlySimplePage\Module
 */
class ModuleTest extends TestCase
{
    public function setUp()
    {
        $this->events       = new EventManager();
        $this->sharedEvents = new SharedEventManager();
        $this->events->setSharedManager($this->sharedEvents);

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
        StaticEventManager::resetInstance();
    }

    protected function getEmptyMockForServiceManager()
    {
        $services = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $services->expects($this->once())
            ->method('has')
            ->with($this->equalTo('PhlySimplePage\PageCache'))
            ->will($this->returnValue(false));
        return $services;
    }

    public function testBootstrapListenerRegistersRouteListener()
    {
        $this->application->services = $this->getEmptyMockForServiceManager();
        $this->module->onBootstrap($this->event);
        $listeners = $this->events->getListeners('route');
        $this->assertCount(1, $listeners);
        foreach ($listeners as $listener) {
        }
        $this->assertEquals(-100, $listener->getMetadatum('priority'));
        $callback = $listener->getCallback();
        $this->assertEquals(array($this->module, 'onRoutePost'), $callback);
    }

    public function testRouteListenerRegistersSharedListenerForPageControllerDispatchEvent()
    {
        $this->module->onRoutePost($this->event);
        $listeners = $this->sharedEvents->getListeners('PhlySimplePage\PageController', 'dispatch');
        $this->assertCount(1, $listeners);
        foreach ($listeners as $listener) {
        }
        $callback = $listener->getCallback();
        $this->assertEquals(-1, $listener->getMetadatum('priority'));
        $this->assertEquals(array($this->module, 'onDispatchPost'), $callback);
    }

    public function testRouteListenerDoesNotRegistersSharedDispatchEventListenerWhenNoRouteMatchesPresentInEvent()
    {
        $event = new MvcEvent();
        $event->setApplication($this->application);
        $event->setTarget($this->application);

        $this->module->onRoutePost($event);
        $listeners = $this->sharedEvents->getListeners('PhlySimplePage\PageController', 'dispatch');
        $this->assertFalse($listeners);
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
        $listeners = $this->sharedEvents->getListeners('PhlySimplePage\PageController', 'dispatch');
        $this->assertFalse($listeners);
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
