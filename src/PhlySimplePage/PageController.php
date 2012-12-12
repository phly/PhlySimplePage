<?php

namespace PhlySimplePage;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Application;
use Zend\Mvc\Exception;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Stdlib\DispatchableInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;
use Zend\View\Model\ViewModel;

class PageController implements
    EventManagerAwareInterface,
    InjectApplicationEventInterface,
    DispatchableInterface
{
    protected $event;
    protected $events;

    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_class($this),
            'Zend\Stdlib\DispatchableInterface',
        ));
        $events->attach('dispatch', array($this, 'onDispatch'));
        $this->events = $events;
        return $this;
    }

    public function getEventManager()
    {
        if (!$this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    public function setEvent(EventInterface $event)
    {
        $this->event = $event;
        return $this;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        $event = $this->getEvent();
        if (!$event) {
            $event = new MvcEvent();
        }

        $event->setRequest($request);
        if ($response) {
            $event->setResponse($response);
        }
        $event->setTarget($this);

        $results = $this->getEventManager()->trigger(__FUNCTION__, $event, function ($r) {
            return ($r instanceof ResponseInterface);
        });

        if ($results->stopped()) {
            return $results->last();
        }

        return $event->getResult();
    }

    public function onDispatch(EventInterface $e)
    {
        if (!$e instanceof MvcEvent) {
            throw new Exception\DomainException(sprintf(
                '%s requires an MvcEvent instance; received "%s"',
                __CLASS__,
                get_class($e)
            ));
        }

        $matches = $e->getRouteMatch();
        if (!$matches instanceof RouteMatch) {
            throw new Exception\DomainException(sprintf(
                'No RouteMatch instance provided to event passed to %s',
                __CLASS__
            ));
        }

        $template = $matches->getParam('template', false);
        if (!$template) {
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
    }
}
