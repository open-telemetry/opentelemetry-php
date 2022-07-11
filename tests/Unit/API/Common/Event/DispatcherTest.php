<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Common\Event;

use OpenTelemetry\API\Common\Event\Dispatcher;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\StoppableEventInterface;
use stdClass;

/**
 * @covers \OpenTelemetry\API\Common\Event\Dispatcher
 */
class DispatcherTest extends TestCase
{
    public function setUp(): void
    {
        Dispatcher::unset();
    }

    public function tearDown(): void
    {
        Dispatcher::unset();
    }

    public function test_configures_self(): void
    {
        $dispatcher = Dispatcher::getInstance();
        $this->assertInstanceOf(Dispatcher::class, $dispatcher);
    }

    public function test_dispatch_event(): void
    {
        $event = new stdClass();
        $handler = function ($receivedEvent) use ($event) {
            $this->assertSame($event, $receivedEvent);
        };
        $dispatcher = Dispatcher::getInstance();
        $dispatcher->listen(get_class($event), $handler);
        $dispatcher->dispatch($event);
    }

    public function test_dispatch_stoppable_event(): void
    {
        $event = $this->createMock(StoppableEventInterface::class);
        $event->method('isPropagationStopped')->willReturnOnConsecutiveCalls(false, true);
        $handlerOne = function (StoppableEventInterface $event) {
            $this->assertTrue(true, 'handler called');
        };
        $handlerTwo = function (StoppableEventInterface $event) {
            $this->fail('method should not have been called');
        };
        $dispatcher = Dispatcher::getInstance();
        $dispatcher->listen(get_class($event), $handlerOne);
        $dispatcher->listen(get_class($event), $handlerTwo, 1);
        $dispatcher->dispatch($event);
    }

    public function test_add_listeners(): void
    {
        $event = new stdClass();
        $listenerFunction = function () {
        };
        $dispatcher = Dispatcher::getInstance();
        $dispatcher->listen(get_class($event), $listenerFunction);
        $listeners = [...$dispatcher->getListenersForEvent($event)];
        $this->assertCount(1, $listeners);
        $this->assertSame($listenerFunction, $listeners[0]);
    }

    public function test_can_add_multiple_listeners_with_same_priority(): void
    {
        $event = new stdClass();
        $listenerOne = function ($event) {
        };
        $listenerTwo = function ($event) {
        };
        $dispatcher = Dispatcher::getInstance();
        $dispatcher->listen(get_class($event), $listenerOne);
        $dispatcher->listen(get_class($event), $listenerTwo);
        $listeners = [...$dispatcher->getListenersForEvent($event)];
        $this->assertCount(2, $listeners);
        $this->assertSame($listenerOne, $listeners[0]);
        $this->assertSame($listenerTwo, $listeners[1]);
    }

    public function test_listener_priority(): void
    {
        $event = new stdClass();
        $listenerOne = function () {
        };
        $listenerTwo = function () {
        };
        $listenerThree = function () {
        };
        $listenerFour = function () {
        };
        $dispatcher = Dispatcher::getInstance();
        $dispatcher->listen(get_class($event), $listenerOne, 1);
        $dispatcher->listen(get_class($event), $listenerTwo, -1);
        $dispatcher->listen(get_class($event), $listenerThree, 0);
        $dispatcher->listen(get_class($event), $listenerFour, 1);
        $listeners = [...$dispatcher->getListenersForEvent($event)];
        $this->assertCount(4, $listeners);
        $this->assertSame($listenerTwo, $listeners[0]);
        $this->assertSame($listenerThree, $listeners[1]);
        $this->assertSame($listenerOne, $listeners[2]);
        $this->assertSame($listenerFour, $listeners[3]);
    }

    public function test_get_listener_for_subclass(): void
    {
        $event = new stdClass();
        $subclass = $this->createMock(stdClass::class);
        $listener = function () {
        };
        $dispatcher = Dispatcher::getInstance();
        $dispatcher->listen(stdClass::class, $listener);
        $listeners = [...$dispatcher->getListenersForEvent($subclass)];
        $this->assertCount(1, $listeners);
        $this->assertSame($listener, $listeners[0]);
    }
}
