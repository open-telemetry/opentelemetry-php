<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

class Dispatcher implements EventDispatcherInterface, ListenerProviderInterface
{
    private static ?self $instance = null;
    /** @var array<string, array<int, array<callable>>> */
    private array $listeners = [];

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function unset(): void
    {
        self::$instance = null;
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function getListenersForEvent(object $event): iterable
    {
        foreach ($this->listeners as $key => $priority) {
            if (is_a($event, $key)) {
                foreach ($priority as $listeners) {
                    foreach ($listeners as $listener) {
                        yield $listener;
                    }
                }
            }
        }
    }

    public function listen(string $event, callable $listener, int $priority = 0): void
    {
        $this->listeners[$event][$priority][] = $listener;
        ksort($this->listeners[$event]);
    }

    public function dispatch(object $event): object
    {
        $listeners = $this->getListenersForEvent($event);

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
