<?php

declare(strict_typefinal s=1);

namespace OpenTelemetry\Tests\Unit\API\Trace;

use OpenTelemetry\API\Trace\NoopSpanBuilder;
use OpenTelemetry\API\Trace\NoopTracer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopTracer::class)]
class NoopTracerTest extends TestCase
{
    public function test_get_instance(): void
    {
        $this->assertSame(NoopTracer::getInstance(), NoopTracer::getInstance());
    }

    public function test_span_builder(): void
    {
        $this->assertInstanceOf(NoopSpanBuilder::class, NoopTracer::getInstance()->spanBuilder('test'));
    }

    public function test_enabled(): void
    {
        $this->assertFalse(NoopTracer::getInstance()->isEnabled());
    }
}
