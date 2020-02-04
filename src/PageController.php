<?php

/**
 * @link      https://github.com/weierophinney/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2012-2020 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/weierophinney/PhlySimplePage/blog/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlySimplePage;

use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\Application;
use Laminas\Mvc\Exception;
use Laminas\Mvc\InjectApplicationEventInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\RouteMatch as LegacyRouteMatch;
use Laminas\Router\RouteMatch;
use Laminas\Stdlib\DispatchableInterface;
use Laminas\Stdlib\RequestInterface;
use Laminas\Stdlib\ResponseInterface;
use Laminas\View\Model\ViewModel;

use function get_class;
use function sprintf;

/**
 * Page controller
 *
 * Generic page controller for mapping route end points directly to the
 * template that provides the display.
 */
class PageController implements
    EventManagerAwareInterface,
    InjectApplicationEventInterface,
    DispatchableInterface
{
    /** @var MvcEvent */
    protected $event;

    /** @var EventManagerInterface */
    protected $events;

    /**
     * Set the event manager instance
     *
     * Sets the event manager instance, after first setting the identifiers:
     *
     * - PhlySimplePage\PageController
     * - current class name
     * - Laminas\Stdlib\DispatchableInterface
     *
     * It also registers the onDispatch method as the default handler for the
     * dispatch event.
     */
    public function setEventManager(EventManagerInterface $events): self
    {
        $events->setIdentifiers([
            self::class,
            static::class,
            DispatchableInterface::class,
        ]);
        $events->attach('dispatch', [$this, 'onDispatch']);
        $this->events = $events;
        return $this;
    }

    /**
     * Retrieve the event manager instance
     *
     * Lazy-creates an instance if none registered.
     */
    public function getEventManager(): EventManagerInterface
    {
        if (! $this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    /**
     * Set the current application event instance
     *
     * @param  EventInterface $event Typically an MvcEvent
     */
    public function setEvent(EventInterface $event): self
    {
        $this->event = $event;
        return $this;
    }

    /**
     * Retrieve the current application event instance
     */
    public function getEvent(): ?EventInterface
    {
        return $this->event;
    }

    /**
     * Dispatch the current request
     *
     * @trigger dispatch
     * @return  mixed
     */
    public function dispatch(RequestInterface $request, ?ResponseInterface $response = null)
    {
        $event = $this->getEvent();
        if (! $event) {
            $event = new MvcEvent();
        }

        $event->setRequest($request);
        if ($response) {
            $event->setResponse($response);
        }

        $event->setTarget($this);
        $event->setName(__FUNCTION__);

        $results = $this->getEventManager()
            ->triggerEventUntil(function ($r) {
                return $r instanceof ResponseInterface;
            }, $event);

        if ($results->stopped()) {
            return $results->last();
        }

        return $event->getResult();
    }

    /**
     * Handle the dispatch event
     *
     * If the event is not an MvcEvent, raises an exception.
     *
     * If the event does not have route matches, raises an exception.
     *
     * If the route matches do not contain a template, sets the event error to
     * Application::ERROR_CONTROLLER_INVALID, and, if the response is an HTTP
     * response type, sets the status code to 404.
     *
     * Otherwise, it creates a ViewModel instance, and sets the template to
     * the value in the route match, and sets the ViewModel as the event result.
     *
     * @param  EventInterface $e Usually an MvcEvent
     * @throws Exception\DomainException
     */
    public function onDispatch(EventInterface $e): void
    {
        if (! $e instanceof MvcEvent) {
            throw new Exception\DomainException(sprintf(
                '%s requires an MvcEvent instance; received "%s"',
                self::class,
                get_class($e)
            ));
        }

        $matches = $e->getRouteMatch();
        if (
            ! $matches instanceof RouteMatch
            && ! $matches instanceof LegacyRouteMatch
        ) {
            throw new Exception\DomainException(sprintf(
                'No RouteMatch instance provided to event passed to %s',
                self::class
            ));
        }

        $template = $matches->getParam('template', false);
        if (! $template) {
            $e->setError(Application::ERROR_CONTROLLER_INVALID);
            $response = $e->getResponse();
            if ($response instanceof HttpResponse) {
                $response->setStatusCode(404);
            }
            return;
        }

        $model = new ViewModel();
        $model->setTemplate($template);

        $e->setResult($model);

        $layout = $matches->getParam('layout', false);
        if ($layout) {
            $e->getViewModel()->setTemplate($layout);
        }
    }
}
