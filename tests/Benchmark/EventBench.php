<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Benchmark;

use CloudEvents\V1\CloudEvent;
use CloudEvents\V1\CloudEventInterface;
use Generator;
use OpenTelemetry\API\Common\Event\Dispatcher;

class EventBench
{
    private Dispatcher $dispatcher;
    private $listener;
    private CloudEventInterface $event;

    public function __construct()
    {
        $this->dispatcher = Dispatcher::getRoot();
        $this->listener = function () {
        };
        $this->event = new CloudEvent(uniqid(), self::class, 'foo');
    }

    public function addEvents(): void
    {
        for ($i=0; $i<10; $i++) {
            $this->dispatcher->listen('event_' . $i, $this->listener);
        }
        $this->dispatcher->listen($this->event->getType(), $this->listener);
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
            $this->dispatcher->listen('event_' . $i, $this->listener);
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
            $this->dispatcher->listen('event', $this->listener);
        }
    }

    /**
     * @BeforeMethods("addEvents")
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
