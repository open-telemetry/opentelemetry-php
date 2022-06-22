<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

class SimpleDispatcher implements EventDispatcherInterface
{
    private ListenerProviderInterface $listenerProvider;

    public function __construct(ListenerProviderInterface $listenerProvider)
    {
        $this->listenerProvider = $listenerProvider;
    }

    public function getListenerProvider(): ListenerProviderInterface
    {
        return $this->listenerProvider;
    }

    public function listen(string $event, callable $listener, int $priority = 0): void
    {
        if (is_a($this->listenerProvider, SimpleListenerProvider::class)) {
            $this->listenerProvider->listen($event, $listener, $priority);
        }
        /* there is no standard interface to register listeners, nor access a listener provider. Using a different listener provider
           requires also setting up all required listeners for that provider */
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
