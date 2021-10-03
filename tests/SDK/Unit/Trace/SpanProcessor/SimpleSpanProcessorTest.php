<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\SpanProcessor;

use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\SDK\Trace\Exporter;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\Span;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use PHPUnit\Framework\TestCase;

class SimpleSpanProcessorTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCallExporterOnEnd(): void
    {
        $exporter = $this->createMock(Exporter::class);
        $exporter->expects($this->atLeastOnce())->method('export');

        $spanContext = $this->createStub(SpanContextInterface::class);
        $spanContext->method('isSampled')->willReturn(true); // only sampled spans are exported
        $span = $this->createStub(Span::class);
        $span->method('getContext')->willReturn($spanContext);

        (new SimpleSpanProcessor($exporter))->onEnd($span);
    }

    /**
     * @test
     */
    public function shouldAllowNullExporter(): void
    {
        $proc = new SimpleSpanProcessor(null);
        $span = $this->createMock(ReadWriteSpanInterface::class);
        $proc->onStart($span);
        $proc->onEnd($span);
        $proc->forceFlush();
        $proc->shutdown();
        $this->assertTrue(true); // phpunit requires an assertion
    }

    /**
     * @test
     */
    public function shutdownCallsExporterShutdown(): void
    {
        $exporter = $this->createMock(Exporter::class);
        $proc = new SimpleSpanProcessor($exporter);

        $exporter->expects($this->once())->method('shutdown');
        $proc->shutdown();
    }

    /**
     * @test
     */
    public function noExportAfterShutdown(): void
    {
        $exporter = $this->createMock(Exporter::class);
        $exporter->expects($this->once())->method('shutdown');

        $proc = new SimpleSpanProcessor($exporter);
        $proc->shutdown();

        $span = $this->createMock(ReadWriteSpanInterface::class);
        $proc->onStart($span);
        $proc->onEnd($span);
    }

    /**
     * @test
     */
    public function shouldExportOnlySampledSpans(): void
    {
        $exporter = $this->createMock(Exporter::class);
        $exporter->expects($this->never())->method('export');

        $spanContext = $this->createStub(SpanContextInterface::class);
        $spanContext->method('isSampled')->willReturn(false);
        $span = $this->createStub(Span::class);
        $span->method('getContext')->willReturn($spanContext);

        (new SimpleSpanProcessor($exporter))->onEnd($span);
    }
}
