<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Event;

use OpenTelemetry\SDK\Common\Event\Dispatcher;
use OpenTelemetry\SDK\Common\Event\Event\DebugEvent;
use OpenTelemetry\SDK\Common\Event\Event\ErrorEvent;
use OpenTelemetry\SDK\Common\Event\Event\WarningEvent;
use OpenTelemetry\SDK\Common\Event\SimpleDispatcher;
use OpenTelemetry\SDK\Common\Event\SimpleListenerProvider;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @covers \OpenTelemetry\SDK\Common\Event\Dispatcher
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
        $this->assertInstanceOf(SimpleDispatcher::class, $dispatcher);
        $this->assertInstanceOf(SimpleListenerProvider::class, $dispatcher->getListenerProvider());
        $this->assertGreaterThan(0, count([...$dispatcher->getListenerProvider()->getListenersForEvent($this->createMock(ErrorEvent::class))]));
        $this->assertGreaterThan(0, count([...$dispatcher->getListenerProvider()->getListenersForEvent($this->createMock(WarningEvent::class))]));
        $this->assertGreaterThan(0, count([...$dispatcher->getListenerProvider()->getListenersForEvent($this->createMock(DebugEvent::class))]));
    }

    public function test_set_instance(): void
    {
        $mock = $this->createMock(EventDispatcherInterface::class);
        Dispatcher::setInstance($mock);
        $this->assertSame($mock, Dispatcher::getInstance());
    }
}
