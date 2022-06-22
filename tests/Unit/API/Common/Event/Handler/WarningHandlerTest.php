<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Common\Event\Handler;

use Exception;
use OpenTelemetry\API\Common\Event\Event\WarningEvent;
use Psr\Log\LogLevel;

/**
 * @covers \OpenTelemetry\API\Common\Event\Handler\WarningEventHandler
 */
class WarningHandlerTest extends LogBasedHandlerTest
{
    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function test_logs_event(): void
    {
        $event = new WarningEvent('foo', new Exception());
        $handler = new \OpenTelemetry\API\Common\Event\Handler\WarningEventHandler();
        // @phpstan-ignore-next-line
        $this->logger->expects($this->once())->method('log')->with($this->equalTo(LogLevel::WARNING));
        $handler($event);
    }
}
