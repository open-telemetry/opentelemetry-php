<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Common\Event\Event;

use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Common\Event\Event\ErrorEvent
 */
class ErrorEventTest extends TestCase
{
    public function test_error_event(): void
    {
        $message = 'foo';
        $exception = new \Exception();
        $event = new \OpenTelemetry\API\Common\Event\Event\ErrorEvent($message, $exception);
        $this->assertSame($message, $event->getMessage());
        $this->assertSame($exception, $event->getException());
    }
}
