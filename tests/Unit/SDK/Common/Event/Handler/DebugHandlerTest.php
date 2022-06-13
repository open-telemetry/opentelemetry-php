<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Event\Handler;

use OpenTelemetry\SDK\Common\Event\Event\DebugEvent;
use OpenTelemetry\SDK\Common\Event\Handler\DebugEventHandler;
use Psr\Log\LogLevel;

/**
 * @covers \OpenTelemetry\SDK\Common\Event\Handler\DebugEventHandler
 */
class DebugHandlerTest extends LogBasedHandlerTest
{
    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function test_logs_event(): void
    {
        $event = new DebugEvent('foo');
        $handler = new DebugEventHandler();
        // @phpstan-ignore-next-line
        $this->logger->expects($this->once())->method('log')->with($this->equalTo(LogLevel::DEBUG));
        $handler($event);
    }
}
