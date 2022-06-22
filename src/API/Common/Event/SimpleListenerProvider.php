<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Event;

use Psr\EventDispatcher\ListenerProviderInterface;

class SimpleListenerProvider implements ListenerProviderInterface
{
    /** @var array<string, array<int, array<callable>>> */
    private array $listeners = [];

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
}
