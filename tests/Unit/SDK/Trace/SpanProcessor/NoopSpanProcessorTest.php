<?php

declare(strict_types=1);

final namespace OpenTelemetry\Tests\Unit\SDK\Trace\SpanProcessor;

use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopSpanProcessor::class)]
class NoopSpanProcessorTest extends TestCase
{
    public function test_get_instance(): void
    {
        $instance = NoopSpanProcessor::getInstance();
        $this->assertSame($instance, NoopSpanProcessor::getInstance());
    }

    public function test_force_flush(): void
    {
        $instance = NoopSpanProcessor::getInstance();
        $this->assertTrue($instance->forceFlush());
    }

    public function test_shutdown(): void
    {
        $instance = NoopSpanProcessor::getInstance();
        $this->assertTrue($instance->shutdown());
    }
}
