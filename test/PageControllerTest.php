<?php

/**
 * @link      https://github.com/phly/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2012-2020 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/phly/PhlySimplePage/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlySimplePageTest;

use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\Application;
use Laminas\Mvc\Exception\DomainException;
use Laminas\Mvc\InjectApplicationEventInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\DispatchableInterface;
use Laminas\Stdlib\Request;
use Laminas\View\Model\ModelInterface;
use PhlySimplePage\PageController;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for PhlySimplePage\PageController
 */
class PageControllerTest extends TestCase
{
    use RouteMatchCreationTrait;

    public function setUp(): void
    {
        $this->event      = new MvcEvent();
        $this->controller = new PageController();
        $this->controller->setEvent($this->event);
    }

    public function testIsEventManagerAware(): void
    {
        $this->assertInstanceOf(EventManagerAwareInterface::class, $this->controller);
    }

    public function testIsDispatchable(): void
    {
        $this->assertInstanceOf(DispatchableInterface::class, $this->controller);
    }

    public function testIsEventInjectable(): void
    {
        $this->assertInstanceOf(InjectApplicationEventInterface::class, $this->controller);
    }

    public function testRaisesExceptionOnDispatchIfEventDoesNotContainRouteMatch(): void
    {
        $request = new Request();
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('RouteMatch');
        $this->controller->dispatch($request);
    }

    public function testSetsNotFoundErrorOnDispatchIfRouteMatchDoesNotContainTemplate(): void
    {
        $matches = $this->createRouteMatch();
        $this->event->setRouteMatch($matches);
        $request = new Request();
        $this->controller->dispatch($request);

        $error = $this->event->getError();
        $this->assertEquals(Application::ERROR_CONTROLLER_INVALID, $error);
    }

    public function testSets404ResponseStatusOnDispatchIfRouteMatchDoesNotContainTemplate(): void
    {
        $matches = $this->createRouteMatch();
        $this->event->setRouteMatch($matches);

        $request  = new Request();
        $response = new HttpResponse();
        $this->controller->dispatch($request, $response);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testReturnsViewModelWithTemplateFromRouteMatchOnSuccess(): void
    {
        $matches = $this->createRouteMatch(['template' => 'this/template']);
        $this->event->setRouteMatch($matches);
        $request = new Request();
        $this->controller->dispatch($request);
        $result = $this->event->getResult();
        $this->assertInstanceOf(ModelInterface::class, $result);
        $this->assertEquals('this/template', $result->getTemplate());
    }

    public function testSetsLayoutTemplateIfLayoutFromRouteMatchIsSet(): void
    {
        $matches = $this->createRouteMatch(['template' => 'this/template', 'layout' => 'this/layout']);
        $this->event->setRouteMatch($matches);
        $request = new Request();
        $this->controller->dispatch($request);
        $layoutViewModel = $this->event->getViewModel();
        $this->assertInstanceOf(ModelInterface::class, $layoutViewModel);
        $this->assertEquals('this/layout', $layoutViewModel->getTemplate());
    }
}
