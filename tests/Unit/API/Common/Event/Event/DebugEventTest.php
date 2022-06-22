<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Common\Event\Event;

use OpenTelemetry\API\Common\Event\Event\DebugEvent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Common\Event\Event\DebugEvent
 */
class DebugEventTest extends TestCase
{
    public function test_debug_event(): void
    {
        $message = 'foo';
        $extra = ['bar' => 'baz'];
        $event = new DebugEvent($message, $extra);
        $this->assertSame($message, $event->getMessage());
        $this->assertSame($extra, $event->getExtra());
    }
}
