<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\SpanProcessor;

use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor
 */
class NoopSpanProcessorTest extends TestCase
{
    /**
     * @covers ::getInstance
     */
    public function test_get_instance(): void
    {
        $instance = NoopSpanProcessor::getInstance();
        $this->assertSame($instance, NoopSpanProcessor::getInstance());
    }

    /**
     * @covers ::forceFlush
     */
    public function test_force_flush(): void
    {
        $instance = NoopSpanProcessor::getInstance();
        $this->assertTrue($instance->forceFlush());
    }

    /**
     * @covers ::shutDown
     */
    public function test_shutdown(): void
    {
        $instance = NoopSpanProcessor::getInstance();
        $this->assertTrue($instance->shutdown());
    }
}
