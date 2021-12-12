<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\InstrumentationLibrary;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\SpanBuilder;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\TracerSharedState;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TracerTest extends TestCase
{
    private API\TracerInterface $tracer;

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
        /** @var ReadableSpanInterface $span */
        $span = $this->tracer->spanBuilder('span')->startSpan();

        $this->assertSame('name', $span->getInstrumentationLibrary()->getName());
        $this->assertSame('version', $span->getInstrumentationLibrary()->getVersion());
    }

    public function test_spanBuilder_fallbackSpanName(): void
    {
        /** @var ReadableSpanInterface $span */
        $span = $this->tracer->spanBuilder('  ')->startSpan();

        $this->assertSame(
            'empty',
            $span->getName()
        );
    }

    /**
     * @test
     * @testdox Logs warning on actions when tracer has shut down
     */
    public function testActionsAfterTracerShutDown(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $tracerSharedState = $this->createMock(TracerSharedState::class);
        $tracer = new \OpenTelemetry\SDK\Trace\Tracer($tracerSharedState, InstrumentationLibrary::getEmpty());
        $tracer->setLogger($logger);
        $tracerSharedState->method('hasShutdown')->willReturn(true);
        $logger->expects($this->once())->method('log');

        $tracer->spanBuilder('foo')->startSpan();
    }
}
