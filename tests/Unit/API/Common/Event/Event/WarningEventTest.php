<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Common\Event\Event;

use OpenTelemetry\API\Common\Event\Event\WarningEvent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Common\Event\Event\WarningEvent
 */
class WarningEventTest extends TestCase
{
    public function test_warning_event_with_throwable(): void
    {
        $message = 'foo';
        $exception = new \Exception();
        $event = new \OpenTelemetry\API\Common\Event\Event\WarningEvent($message, $exception);
        $this->assertSame($message, $event->getMessage());
        $this->assertTrue($event->hasError());
        $this->assertSame($exception, $event->getException());
    }

    public function test_warning_event_without_throwable(): void
    {
        $message = 'foo';
        $event = new WarningEvent($message);
        $this->assertSame($message, $event->getMessage());
        $this->assertFalse($event->hasError());
        $this->assertNull($event->getException());
    }
}
