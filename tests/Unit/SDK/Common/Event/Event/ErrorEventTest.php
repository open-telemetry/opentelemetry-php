<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Event\Event;

use OpenTelemetry\SDK\Common\Event\Event\ErrorEvent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Event\Event\ErrorEvent
 */
class ErrorEventTest extends TestCase
{
    public function test_error_event(): void
    {
        $message = 'foo';
        $exception = new \Exception();
        $event = new ErrorEvent($message, $exception);
        $this->assertSame($message, $event->getMessage());
        $this->assertSame($exception, $event->getException());
    }
}
