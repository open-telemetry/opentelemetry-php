<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Event\Handler;

use Exception;
use OpenTelemetry\SDK\Common\Event\Event\WarningEvent;
use OpenTelemetry\SDK\Common\Event\Handler\WarningEventHandler;
use Psr\Log\LogLevel;

/**
 * @covers \OpenTelemetry\SDK\Common\Event\Handler\WarningEventHandler
 */
class WarningHandlerTest extends LogBasedHandlerTest
{
    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function test_logs_event(): void
    {
        $event = new WarningEvent('foo', new Exception());
        $handler = new WarningEventHandler();
        // @phpstan-ignore-next-line
        $this->logger->expects($this->once())->method('log')->with($this->equalTo(LogLevel::WARNING));
        $handler($event);
    }
}
