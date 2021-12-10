<?php

declare(strict_types=1);

namespace PhlySimplePageTest\TestAsset;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\Application as MvcApplication;
use Laminas\Mvc\ApplicationInterface;
use Psr\Container\ContainerInterface;

/**
 * Application stub for testing purposes
 */
class Application implements ApplicationInterface
{
    /** @var null|EventManagerInterface */
    protected $events;

    /** @var null|ContainerInterface */
    public $services;

    public function setEventManager(EventManagerInterface $events): self
    {
        $events->setIdentifiers([
            self::class,
            static::class,
            MvcApplication::class,
            ApplicationInterface::class,
        ]);
        $this->events = $events;
        return $this;
    }

    public function getEventManager(): EventManagerInterface
    {
        if (! $this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    public function getRequest(): void
    {
    }

    public function getResponse(): void
    {
    }

    public function getServiceManager(): ?ContainerInterface
    {
        return $this->services;
    }

    public function run(): void
    {
    }
}
