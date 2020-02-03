<?php
/**
 * @link      https://github.com/weierophinney/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2012 Matthew Weier O'Phinney (http://mwop.net)
 * @license   https://github.com/weierophinney/PhlySimplePage/blog/master/LICENSE.md New BSD License
 */

namespace PhlySimplePageTest\TestAsset;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\ApplicationInterface;

/**
 * Application stub for testing purposes
 */
class Application implements ApplicationInterface
{
    protected $events;
    public $services;

    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers([
            __CLASS__,
            get_class($this),
            'Laminas\Mvc\Application',
            'Laminas\Mvc\ApplicationInterface',
        ]);
        $this->events = $events;
        return $this;
    }

    public function getEventManager()
    {
        if (! $this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    public function getRequest()
    {
    }

    public function getResponse()
    {
    }

    public function getServiceManager()
    {
        return $this->services;
    }

    public function run()
    {
    }
}
