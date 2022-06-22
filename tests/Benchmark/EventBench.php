<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Benchmark;

use Generator;
use OpenTelemetry\API\Common\Event\SimpleDispatcher;
use OpenTelemetry\API\Common\Event\SimpleListenerProvider;
use stdClass;

class EventBench
{
    private SimpleDispatcher $dispatcher;
    private SimpleListenerProvider $listenerProvider;
    private $listener;
    private object $event;

    public function __construct()
    {
        $this->listenerProvider = new SimpleListenerProvider();
        $this->dispatcher = new SimpleDispatcher($this->listenerProvider);
        $this->listener = function () {
        };
        $this->event = new stdClass();
    }

    public function addEventsToListener(): void
    {
        for ($i=0; $i<10; $i++) {
            $this->listenerProvider->listen('event_' . $i, $this->listener);
        }
        $this->listenerProvider->listen(get_class($this->event), $this->listener);
    }

    /**
     * @ParamProviders("provideListenerCounts")
     * @Revs(1000)
     * @Iterations(10)
     * @OutputTimeUnit("microseconds")
     */
    public function benchAddListeners(array $params): void
    {
        for ($i=0; $i<$params[0]; $i++) {
            $this->listenerProvider->listen('event_' . $i, $this->listener);
        }
    }

    /**
     * @ParamProviders("provideListenerCounts")
     * @Revs(1000)
     * @Iterations(10)
     * @OutputTimeUnit("microseconds")
     */
    public function benchAddListenersForSameEvent(array $params): void
    {
        for ($i=0; $i<$params[0]; $i++) {
            $this->listenerProvider->listen('event', $this->listener);
        }
    }

    /**
     * @BeforeMethods("addEventsToListener")
     * @Revs(1000)
     * @Iterations(10)
     * @OutputTimeUnit("microseconds")
     */
    public function benchDispatchEvent(): void
    {
        $this->dispatcher->dispatch($this->event);
    }

    public function provideListenerCounts(): Generator
    {
        yield [1];
        yield [4];
        yield [16];
        yield [256];
    }
}
