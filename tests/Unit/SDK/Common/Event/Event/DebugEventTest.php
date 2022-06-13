<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Event\Event;

use OpenTelemetry\SDK\Common\Event\Event\DebugEvent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Event\Event\DebugEvent
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
