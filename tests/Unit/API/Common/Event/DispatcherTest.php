<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Common\Event;

use CloudEvents\V1\CloudEventInterface;
use OpenTelemetry\API\Common\Event\Dispatcher;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Common\Event\Dispatcher
 */
class DispatcherTest extends TestCase
{
    private Dispatcher $dispatcher;
    private CloudEventInterface $event;
    private \ReflectionMethod $method;

    public function setUp(): void
    {
        $this->dispatcher = new Dispatcher();

        $reflection = new \ReflectionClass($this->dispatcher);
        $this->method = $reflection->getMethod('getListenersForEvent');
        $this->method->setAccessible(true);

        $this->event = $this->createMock(CloudEventInterface::class);
        $this->event->method('getType')->willReturn('foo');
    }

    public function test_get_root_dispatcher(): void
    {
        $dispatcher = Dispatcher::getRoot();
        $this->assertInstanceOf(Dispatcher::class, $dispatcher);
        $this->assertSame($dispatcher, Dispatcher::getRoot());
    }

    public function test_add_listener(): void
    {
        $listenerFunction = function () {
        };
        $this->dispatcher->listen($this->event->getType(), $listenerFunction);
        $listeners = [...$this->method->invokeArgs($this->dispatcher, [$this->event->getType()])];
        $this->assertCount(1, $listeners);
        $this->assertSame($listenerFunction, $listeners[0]);
    }

    public function test_dispatch_event(): void
    {
        $handler = function ($receivedEvent) {
            $this->assertSame($this->event, $receivedEvent);
        };
        $this->dispatcher->listen($this->event->getType(), $handler);
        $this->dispatcher->dispatch($this->event);
    }

    public function test_add_multiple_listeners_with_same_priority(): void
    {
        $listenerOne = function (CloudEventInterface $event) {
        };
        $listenerTwo = function (CloudEventInterface $event) {
        };
        $this->dispatcher->listen($this->event->getType(), $listenerOne);
        $this->dispatcher->listen($this->event->getType(), $listenerTwo);
        $listeners = [...$this->method->invokeArgs($this->dispatcher, [$this->event->getType()])];
        $this->assertCount(2, $listeners);
        $this->assertSame($listenerOne, $listeners[0]);
        $this->assertSame($listenerTwo, $listeners[1]);
    }

    public function test_listener_priority(): void
    {
        $listenerOne = function () {
        };
        $listenerTwo = function () {
        };
        $listenerThree = function () {
        };
        $listenerFour = function () {
        };
        $this->dispatcher->listen($this->event->getType(), $listenerOne, 1);
        $this->dispatcher->listen($this->event->getType(), $listenerTwo, -1);
        $this->dispatcher->listen($this->event->getType(), $listenerThree, 0);
        $this->dispatcher->listen($this->event->getType(), $listenerFour, 1);
        $listeners = [...$this->method->invokeArgs($this->dispatcher, [$this->event->getType()])];
        $this->assertCount(4, $listeners);
        $this->assertSame($listenerTwo, $listeners[0]);
        $this->assertSame($listenerThree, $listeners[1]);
        $this->assertSame($listenerOne, $listeners[2]);
        $this->assertSame($listenerFour, $listeners[3]);
    }

    public function test_add_listener_to_multiple_events(): void
    {
        $event = $this->createMock(CloudEventInterface::class);
        $event->method('getType')->willReturn('bar');
        $listener = function () {
        };
        $this->dispatcher->listen($this->event->getType(), $listener);
        $this->dispatcher->listen($event->getType(), $listener);
        $this->assertSame([$listener], [...$this->method->invokeArgs($this->dispatcher, [$this->event->getType()])]);
    }
}
