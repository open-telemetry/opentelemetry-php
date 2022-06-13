<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Event\Event;

use OpenTelemetry\SDK\Common\Event\Event\WarningEvent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Event\Event\WarningEvent
 */
class WarningEventTest extends TestCase
{
    public function test_warning_event_with_throwable(): void
    {
        $message = 'foo';
        $exception = new \Exception();
        $event = new WarningEvent($message, $exception);
        $this->assertSame($message, $event->getMessage());
        $this->assertTrue($event->hasError());
        $this->assertSame($exception, $event->getError());
    }

    public function test_warning_event_without_throwable(): void
    {
        $message = 'foo';
        $event = new WarningEvent($message);
        $this->assertSame($message, $event->getMessage());
        $this->assertFalse($event->hasError());
        $this->assertNull($event->getError());
    }
}
