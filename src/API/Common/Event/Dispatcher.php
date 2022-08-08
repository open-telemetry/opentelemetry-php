<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Event;

use CloudEvents\V1\CloudEventInterface;

class Dispatcher implements DispatcherInterface
{
    private static ?self $root = null;
    /** @var array<string, array<int, array<callable>>> */
    private array $listeners = [];

    public static function getRoot(): self
    {
        return self::$root ??= new self();
    }

    public function dispatch(CloudEventInterface $event): void
    {
        $this->dispatchEvent($this->getListenersForEvent($event->getType()), $event);
    }

    public function listen(string $type, callable $listener, int $priority = 0): void
    {
        $this->listeners[$type][$priority][] = $listener;
        ksort($this->listeners[$type]);
    }

    private function getListenersForEvent(string $key): iterable
    {
        foreach ($this->listeners[$key] as $listeners) {
            foreach ($listeners as $listener) {
                yield $listener;
            }
        }
    }

    private function dispatchEvent(iterable $listeners, CloudEventInterface $event): void
    {
        foreach ($listeners as $listener) {
            $listener($event);
        }
    }
}
