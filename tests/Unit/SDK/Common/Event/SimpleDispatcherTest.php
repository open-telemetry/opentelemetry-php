<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Event;

use OpenTelemetry\SDK\Common\Event\SimpleDispatcher;
use OpenTelemetry\SDK\Common\Event\SimpleListenerProvider;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use stdClass;

/**
 * @covers \OpenTelemetry\SDK\Common\Event\SimpleDispatcher
 */
class SimpleDispatcherTest extends TestCase
{
    private ListenerProviderInterface $listenerProvider;

    public function setUp(): void
    {
        $this->listenerProvider = $this->createMock(ListenerProviderInterface::class);
    }

    public function test_get_listener_provider(): void
    {
        $dispatcher = new SimpleDispatcher($this->listenerProvider);
        $this->assertSame($this->listenerProvider, $dispatcher->getListenerProvider());
    }

    public function test_proxies_listen_for_simple_listener_provider(): void
    {
        $provider = $this->createMock(SimpleListenerProvider::class);
        $callable = function () {
        };
        $eventName = 'my.event';
        $priority = 99;
        $dispatcher = new SimpleDispatcher($provider);
        $provider
            ->expects($this->once())
            ->method('listen')
            ->with(
                $this->equalTo($eventName),
                $this->equalTo($callable),
                $this->equalTo($priority)
            );
        $dispatcher->listen($eventName, $callable, $priority);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_skips_other_listener_providers(): void
    {
        $provider = $this->createMock(ListenerProviderInterface::class);
        $dispatcher = new SimpleDispatcher($provider);
        $dispatcher->listen('my.event', function () {
        });
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function test_dispatch_event(): void
    {
        $event = new stdClass();
        $handler = function ($receivedEvent) use ($event) {
            $this->assertSame($event, $receivedEvent);
        };
        $this->listenerProvider->expects($this->once())->method('getListenersForEvent')->willReturn([$handler]); //@phpstan-ignore-line
        $dispatcher = new SimpleDispatcher($this->listenerProvider);
        $dispatcher->dispatch($event);
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
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
        $this->listenerProvider->method('getListenersForEvent')->willReturn([$handlerOne, $handlerTwo]); //@phpstan-ignore-line
        $dispatcher = new SimpleDispatcher($this->listenerProvider);
        $dispatcher->dispatch($event);
    }
}
