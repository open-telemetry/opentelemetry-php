<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Event;

use CloudEvents\V1\CloudEventInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;

class Dispatcher
{
    private static ?ContextKey $key = null;
    /** @var array<string, array<int, array<callable>>> */
    private array $listeners = [];

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        self::$key ??= new ContextKey(self::class);
        $dispatcher = Context::getCurrent()->get(self::$key);
        if ($dispatcher === null) {
            $dispatcher = new self();
            Context::getCurrent()->with(self::$key, $dispatcher)->activate();
        }

        return $dispatcher;
    }

    /**
     * @internal
     */
    public function reset(): void
    {
        $this->listeners = [];
    }

    public function getListenersForEvent(CloudEventInterface $event): iterable
    {
        foreach ($this->listeners[$event->getType()] as $listeners) {
            foreach ($listeners as $listener) {
                yield $listener;
            }
        }
    }

    public function listen(string $type, callable $listener, int $priority = 0): void
    {
        $this->listeners[$type][$priority][] = $listener;
        ksort($this->listeners[$type]);
    }

    public function dispatch(CloudEventInterface $event): object
    {
        $listeners = $this->getListenersForEvent($event);
        $this->dispatchEvent($listeners, $event);

        return $event;
    }

    private function dispatchEvent(iterable $listeners, CloudEventInterface $event): void
    {
        foreach ($listeners as $listener) {
            $listener($event);
        }
    }
}
