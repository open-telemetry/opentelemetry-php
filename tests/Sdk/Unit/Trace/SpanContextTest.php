<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\SpanContext;
use PHPUnit\Framework\TestCase;

class SpanContextTest extends TestCase
{
    /**
     * @test
     */
    public function testDefaultSpansFromContextAreNotSampled()
    {
        $span = SpanContext::generate();

        $this->assertFalse($span->isSampled());
    }

    /**
     * @test
     */
    public function testSpanContextCanCreateSampledSpans()
    {
        $span = SpanContext::generate(true);

        $this->assertTrue($span->isSampled());
    }
}
