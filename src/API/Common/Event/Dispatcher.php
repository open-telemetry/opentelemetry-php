<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

class Dispatcher implements EventDispatcherInterface
{
    private static ?self $instance = null;
    private ListenerProviderInterface $listenerProvider;

    public function __construct(ListenerProviderInterface $listenerProvider)
    {
        $this->listenerProvider = $listenerProvider;
    }

    public static function getInstance(): EventDispatcherInterface
    {
        if (self::$instance === null) {
            self::$instance = new self(new ListenerProvider());
        }

        return self::$instance;
    }

    public static function unset(): void
    {
        self::$instance = null;
    }

    public function getListenerProvider(): ListenerProviderInterface
    {
        return $this->listenerProvider;
    }

    public function listen(string $event, callable $listener, int $priority = 0): void
    {
        if ($this->listenerProvider instanceof \OpenTelemetry\API\Common\Event\ListenerProvider) {
            $this->listenerProvider->listen($event, $listener, $priority);
        }
    }

    public function dispatch(object $event): object
    {
        $listeners = $this->listenerProvider->getListenersForEvent($event);

        $event instanceof StoppableEventInterface
            ? $this->dispatchStoppableEvent($listeners, $event)
            : $this->dispatchEvent($listeners, $event);

        return $event;
    }

    private function dispatchStoppableEvent(iterable $listeners, StoppableEventInterface $event): void
    {
        foreach ($listeners as $listener) {
            if ($event->isPropagationStopped()) {
                break;
            }

            $listener($event);
        }
    }

    private function dispatchEvent(iterable $listeners, object $event): void
    {
        foreach ($listeners as $listener) {
            $listener($event);
        }
    }
}
