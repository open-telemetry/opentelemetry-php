<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Event\Handler;

use Exception;
use OpenTelemetry\SDK\Common\Event\Event\ErrorEvent;
use OpenTelemetry\SDK\Common\Event\Handler\ErrorEventHandler;
use Psr\Log\LogLevel;

/**
 * @covers \OpenTelemetry\SDK\Common\Event\Handler\ErrorEventHandler
 */
class ErrorHandlerTest extends LogBasedHandlerTest
{
    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function test_logs_event(): void
    {
        $event = new ErrorEvent('foo', new Exception());
        $handler = new ErrorEventHandler();
        // @phpstan-ignore-next-line
        $this->logger->expects($this->once())->method('log')->with($this->equalTo(LogLevel::ERROR));
        $handler($event);
    }
}
