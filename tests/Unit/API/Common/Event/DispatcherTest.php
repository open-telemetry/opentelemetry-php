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
    private CloudEventInterface $event;

    public function setUp(): void
    {
        $this->event = $this->createMock(CloudEventInterface::class);
        $this->event->method('getType')->willReturn('foo');
    }

    public function tearDown(): void
    {
        Dispatcher::getInstance()->reset();
    }

    public function test_get_instance(): void
    {
        $dispatcher = Dispatcher::getInstance();
        $this->assertInstanceOf(Dispatcher::class, $dispatcher);
        $this->assertSame($dispatcher, Dispatcher::getInstance());
    }

    public function test_get_instance_from_parent_context(): void
    {
        $dispatcher = Dispatcher::getInstance();
        $this->assertInstanceOf(Dispatcher::class, $dispatcher);
        $parent = Context::getCurrent()->with(new ContextKey('foo'), 'bar');
        $parent->activate();
        $this->assertSame($dispatcher, Dispatcher::getInstance());
    }

    public function test_add_listener(): void
    {
        $listenerFunction = function () {
        };
        $dispatcher = Dispatcher::getInstance();
        $dispatcher->listen($this->event->getType(), $listenerFunction);
        $listeners = [...$dispatcher->getListenersForEvent($this->event)];
        $this->assertCount(1, $listeners);
        $this->assertSame($listenerFunction, $listeners[0]);
    }

    public function test_dispatch_event(): void
    {
        $handler = function ($receivedEvent) {
            $this->assertSame($this->event, $receivedEvent);
        };
        $dispatcher = Dispatcher::getInstance();
        $dispatcher->listen($this->event->getType(), $handler);
        $dispatcher->dispatch($this->event);
    }

    public function test_add_multiple_listeners_with_same_priority(): void
    {
        $listenerOne = function (CloudEventInterface $event) {
        };
        $listenerTwo = function (CloudEventInterface $event) {
        };
        $dispatcher = Dispatcher::getInstance();
        $dispatcher->listen($this->event->getType(), $listenerOne);
        $dispatcher->listen($this->event->getType(), $listenerTwo);
        $listeners = [...$dispatcher->getListenersForEvent($this->event)];
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
        $dispatcher = Dispatcher::getInstance();
        $dispatcher->listen($this->event->getType(), $listenerOne, 1);
        $dispatcher->listen($this->event->getType(), $listenerTwo, -1);
        $dispatcher->listen($this->event->getType(), $listenerThree, 0);
        $dispatcher->listen($this->event->getType(), $listenerFour, 1);
        $listeners = [...$dispatcher->getListenersForEvent($this->event)];
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
        $dispatcher = Dispatcher::getInstance();
        $dispatcher->listen($this->event->getType(), $listener);
        $dispatcher->listen($event->getType(), $listener);
        $this->assertSame([$listener], [...$dispatcher->getListenersForEvent($event)]);
        $this->assertSame([$listener], [...$dispatcher->getListenersForEvent($this->event)]);
    }
}
