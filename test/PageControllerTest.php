<?php
/**
 * @link      https://github.com/weierophinney/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2012-2020 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/weierophinney/PhlySimplePage/blog/master/LICENSE.md New BSD License
 */

namespace PhlySimplePageTest;

use PhlySimplePage\PageController;
use PHPUnit\Framework\TestCase;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Application;
use Zend\Mvc\Exception\DomainException;
use Zend\Mvc\MvcEvent;
use Zend\Router\RouteMatch;
use Zend\Stdlib\Request;

/**
 * Unit tests for PhlySimplePage\PageController
 */
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
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('RouteMatch');
        $this->controller->dispatch($request);
    }

    public function testSetsNotFoundErrorOnDispatchIfRouteMatchDoesNotContainTemplate()
    {
        $matches = new RouteMatch(array());
        $this->event->setRouteMatch($matches);
        $request = new Request();
        $this->controller->dispatch($request);

        $error = $this->event->getError();
        $this->assertEquals(Application::ERROR_CONTROLLER_INVALID, $error);
    }

    public function testSets404ResponseStatusOnDispatchIfRouteMatchDoesNotContainTemplate()
    {
        $matches = new RouteMatch(array());
        $this->event->setRouteMatch($matches);

        $request  = new Request();
        $response = new HttpResponse();
        $this->controller->dispatch($request, $response);

        $this->assertEquals(404, $response->getStatusCode());
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

    public function testSetsLayoutTemplateIfLayoutFromRouteMatchIsSet()
    {
        $matches = new RouteMatch(array('template' => 'this/template', 'layout' => 'this/layout'));
        $this->event->setRouteMatch($matches);
        $request = new Request();
        $this->controller->dispatch($request);
        $layoutViewModel = $this->event->getViewModel();
        $this->assertInstanceOf('Zend\View\Model\ModelInterface', $layoutViewModel);
        $this->assertEquals('this/layout', $layoutViewModel->getTemplate());
    }
}
