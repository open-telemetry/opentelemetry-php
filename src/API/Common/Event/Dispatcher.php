<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Event;

use CloudEvents\V1\CloudEventInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;

class Dispatcher implements DispatcherInterface
{
    private static ?ContextKey $key = null;
    /** @var array<string, array<int, array<callable>>> */
    private array $listeners = [];

    public static function getInstance(): self
    {
        $key = self::getConstantKeyInstance();

        return Context::getCurrent()->get($key) ?? self::createInstance();
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

    private static function getConstantKeyInstance(): ContextKey
    {
        return self::$key ??= new ContextKey(self::class);
    }

    private static function createInstance(): self
    {
        $dispatcher = new self();
        Context::getCurrent()->with(self::getConstantKeyInstance(), $dispatcher)->activate();

        return $dispatcher;
    }
}
