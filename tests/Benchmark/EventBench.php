<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Benchmark;

use OpenTelemetry\API\Common\Event\SimpleDispatcher;
use OpenTelemetry\API\Common\Event\SimpleListenerProvider;
use stdClass;

class EventBench
{
    private SimpleDispatcher $dispatcher;
    private SimpleListenerProvider $listenerProvider;
    private $function;
    private object $event;

    public function __construct()
    {
        $this->listenerProvider = new SimpleListenerProvider();
        $this->dispatcher = new SimpleDispatcher($this->listenerProvider);
        $this->function = function(){};
        $this->event = new stdClass();
    }

    public function addEventsToListener(): void
    {
        for ($i=0; $i<10; $i++) {
            $this->listenerProvider->listen('event_'.$i, $this->function);
        }
        $this->listenerProvider->listen(get_class($this->event), $this->function);
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
            $this->listenerProvider->listen('event_'.$i, $this->function);
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
            $this->listenerProvider->listen('event', $this->function);
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

    public function provideListenerCounts(): \Generator
    {
        yield [1];
        yield [4];
        yield [16];
        yield [256];
    }
}