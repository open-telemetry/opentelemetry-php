<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\ReadableSpan;
use OpenTelemetry\Sdk\Trace\SpanBuilder;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Trace as API;
use PHPUnit\Framework\TestCase;

class TracerTest extends TestCase
{
    private API\Tracer $tracer;

    protected function setUp(): void
    {
        $this->tracer = (new TracerProvider())
            ->getTracer('name', 'version');
    }

    public function test_spanBuilder_default(): void
    {
        $this->assertInstanceOf(
            SpanBuilder::class,
            $this->tracer->spanBuilder('name')
        );
    }

    public function test_spanBuilder_propagatesInstrumentationLibraryInfoToSpan(): void
    {
        /** @var ReadableSpan $span */
        $span = $this->tracer->spanBuilder('span')->startSpan();

        $this->assertSame('name', $span->getInstrumentationLibrary()->getName());
        $this->assertSame('version', $span->getInstrumentationLibrary()->getVersion());
    }

    public function test_spanBuilder_fallbackSpanName(): void
    {
        /** @var ReadableSpan $span */
        $span = $this->tracer->spanBuilder('  ')->startSpan();

        $this->assertSame(
            'empty',
            $span->getName()
        );
    }
}
