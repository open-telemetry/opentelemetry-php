<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Event;

use OpenTelemetry\SDK\Common\Event\SimpleListenerProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \OpenTelemetry\SDK\Common\Event\SimpleListenerProvider
 */
class SimpleListenerProviderTest extends TestCase
{
    private SimpleListenerProvider $provider;

    public function setUp(): void
    {
        $this->provider = new SimpleListenerProvider();
    }

    public function test_add_listeners(): void
    {
        $event = new stdClass();
        $listenerFunction = function () {
        };
        $this->provider->listen(get_class($event), $listenerFunction);
        $listeners = [...$this->provider->getListenersForEvent($event)];
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
        $this->provider->listen(get_class($event), $listenerOne);
        $this->provider->listen(get_class($event), $listenerTwo);
        $listeners = [...$this->provider->getListenersForEvent($event)];
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
        $this->provider->listen(get_class($event), $listenerOne, 1);
        $this->provider->listen(get_class($event), $listenerTwo, -1);
        $this->provider->listen(get_class($event), $listenerThree, 0);
        $this->provider->listen(get_class($event), $listenerFour, 1);
        $listeners = [...$this->provider->getListenersForEvent($event)];
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
        $this->provider->listen(stdClass::class, $listener);
        $listeners = [...$this->provider->getListenersForEvent($subclass)];
        $this->assertCount(1, $listeners);
        $this->assertSame($listener, $listeners[0]);
    }
}
