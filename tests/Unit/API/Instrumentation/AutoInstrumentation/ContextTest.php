<?php

declare(strict_types=1);

namespace Unit\API\Instrumentation\AutoInstrumentation;

use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Context;
use OpenTelemetry\API\Logs\NoopLoggerProvider;
use OpenTelemetry\API\Metrics\Noop\NoopMeterProvider;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Context::class)]
class ContextTest extends TestCase
{
    public function test_default_noops(): void
    {
        $context = new Context();
        $this->assertInstanceOf(NoopTracerProvider::class, $context->tracerProvider);
        $this->assertInstanceOf(NoopMeterProvider::class, $context->meterProvider);
        $this->assertInstanceOf(NoopLoggerProvider::class, $context->loggerProvider);
        $this->assertInstanceOf(NoopTextMapPropagator::class, $context->propagator);
    }
}
