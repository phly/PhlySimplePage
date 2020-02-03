<?php
/**
 * @link      https://github.com/weierophinney/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2012-2020 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/weierophinney/PhlySimplePage/blog/master/LICENSE.md New BSD License
 */

namespace PhlySimplePageTest;

use PhlySimplePage\PageController;
use PHPUnit\Framework\TestCase;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\Application;
use Laminas\Mvc\Exception\DomainException;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\Request;

/**
 * Unit tests for PhlySimplePage\PageController
 */
class PageControllerTest extends TestCase
{
    use RouteMatchCreationTrait;

    public function setUp()
    {
        $this->event      = new MvcEvent();
        $this->controller = new PageController();
        $this->controller->setEvent($this->event);
    }

    public function testIsEventManagerAware()
    {
        $this->assertInstanceOf('Laminas\EventManager\EventManagerAwareInterface', $this->controller);
    }

    public function testIsDispatchable()
    {
        $this->assertInstanceOf('Laminas\Stdlib\DispatchableInterface', $this->controller);
    }

    public function testIsEventInjectable()
    {
        $this->assertInstanceOf('Laminas\Mvc\InjectApplicationEventInterface', $this->controller);
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
        $matches = $this->createRouteMatch();
        $this->event->setRouteMatch($matches);
        $request = new Request();
        $this->controller->dispatch($request);

        $error = $this->event->getError();
        $this->assertEquals(Application::ERROR_CONTROLLER_INVALID, $error);
    }

    public function testSets404ResponseStatusOnDispatchIfRouteMatchDoesNotContainTemplate()
    {
        $matches = $this->createRouteMatch();
        $this->event->setRouteMatch($matches);

        $request  = new Request();
        $response = new HttpResponse();
        $this->controller->dispatch($request, $response);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testReturnsViewModelWithTemplateFromRouteMatchOnSuccess()
    {
        $matches = $this->createRouteMatch(['template' => 'this/template']);
        $this->event->setRouteMatch($matches);
        $request = new Request();
        $this->controller->dispatch($request);
        $result = $this->event->getResult();
        $this->assertInstanceOf('Laminas\View\Model\ModelInterface', $result);
        $this->assertEquals('this/template', $result->getTemplate());
    }

    public function testSetsLayoutTemplateIfLayoutFromRouteMatchIsSet()
    {
        $matches = $this->createRouteMatch(['template' => 'this/template', 'layout' => 'this/layout']);
        $this->event->setRouteMatch($matches);
        $request = new Request();
        $this->controller->dispatch($request);
        $layoutViewModel = $this->event->getViewModel();
        $this->assertInstanceOf('Laminas\View\Model\ModelInterface', $layoutViewModel);
        $this->assertEquals('this/layout', $layoutViewModel->getTemplate());
    }
}
