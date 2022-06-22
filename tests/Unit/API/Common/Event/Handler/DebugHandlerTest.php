<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Common\Event\Handler;

use OpenTelemetry\API\Common\Event\Event\DebugEvent;
use Psr\Log\LogLevel;

/**
 * @covers \OpenTelemetry\API\Common\Event\Handler\DebugEventHandler
 */
class DebugHandlerTest extends LogBasedHandlerTest
{
    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function test_logs_event(): void
    {
        $event = new DebugEvent('foo');
        $handler = new \OpenTelemetry\API\Common\Event\Handler\DebugEventHandler();
        // @phpstan-ignore-next-line
        $this->logger->expects($this->once())->method('log')->with($this->equalTo(LogLevel::DEBUG));
        $handler($event);
    }
}
