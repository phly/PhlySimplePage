<?php

namespace PhlySimplePageTest;

use PhlySimplePage\PageController;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Stdlib\Request;
use Zend\Stdlib\Response;
use Zend\View\Model;

class PageControllerTest extends TestCase
{
    public function setUp()
    {
        $this->event      = new MvcEvent();
        $this->controller = new PageController();
        $this->controller->setEvent($this->event);
    }

    public function testIsEventManagerAware()
    {
        $this->assertInstanceOf('Zend\EventManager\EventManagerAwareInterface', $this->controller);
    }

    public function testIsDispatchable()
    {
        $this->assertInstanceOf('Zend\Stdlib\DispatchableInterface', $this->controller);
    }

    public function testIsEventInjectable()
    {
        $this->assertInstanceOf('Zend\Mvc\InjectApplicationEventInterface', $this->controller);
    }

    public function testRaisesExceptionOnDispatchIfEventDoesNotContainRouteMatch()
    {
        $request = new Request();
        $this->setExpectedException('Zend\Mvc\Exception\DomainException', 'RouteMatch');
        $this->controller->dispatch($request);
    }

    public function testRaisesExceptionOnDispatchIfRouteMatchDoesNotContainTemplate()
    {
        $matches = new RouteMatch(array());
        $this->event->setRouteMatch($matches);
        $request = new Request();
        $this->setExpectedException('Zend\Mvc\Exception\DomainException', 'template');
        $this->controller->dispatch($request);
    }

    public function testReturnsViewModelWithTemplateFromRouteMatchOnSuccess()
    {
        $matches = new RouteMatch(array('template' => 'this/template'));
        $this->event->setRouteMatch($matches);
        $request = new Request();
        $this->controller->dispatch($request);
        $result = $this->event->getResult();
        $this->assertInstanceOf('Zend\View\Model\ModelInterface', $result);
        $this->assertEquals('this/template', $result->getTemplate());
    }
}
