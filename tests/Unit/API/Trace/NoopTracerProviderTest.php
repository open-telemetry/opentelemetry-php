<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Trace;

use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\API\Trace\TracerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopTracerProvider::class)]
class NoopTracerProviderTest extends TestCase
{
    public function test_get_tracer_returns_noop_tracer(): void
    {
        $provider = new NoopTracerProvider();
        $tracer = $provider->getTracer('test');
        $this->assertInstanceOf(TracerInterface::class, $tracer);
        $this->assertInstanceOf(NoopTracer::class, $tracer);
    }

    public function test_get_tracer_with_all_parameters(): void
    {
        $provider = new NoopTracerProvider();
        $tracer = $provider->getTracer('test', '1.0.0', 'https://example.com', []);
        $this->assertInstanceOf(NoopTracer::class, $tracer);
    }
}
