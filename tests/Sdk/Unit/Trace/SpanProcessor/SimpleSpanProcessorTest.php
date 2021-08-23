<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace\SpanProcessor;

use OpenTelemetry\Sdk\Trace\Exporter;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\Trace\SpanContext;
use PHPUnit\Framework\TestCase;

class SimpleSpanProcessorTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCallExporterOnEnd()
    {
        $exporter = self::createMock(Exporter::class);
        $exporter->expects($this->atLeastOnce())->method('export');

        $spanContext = self::createStub(SpanContext::class);
        $spanContext->method('isSampled')->willReturn(true); // only sampled spans are exported
        $span = self::createStub(Span::class);
        $span->method('getSpanContext')->willReturn($spanContext);

        (new SimpleSpanProcessor($exporter))->onEnd($span);
    }

    /**
     * @test
     */
    public function shouldAllowNullExporter()
    {
        $proc = new SimpleSpanProcessor(null);
        $span = self::createMock(Span::class);
        $proc->onStart($span);
        $proc->onEnd($span);
        $proc->forceFlush();
        $proc->shutdown();
        $this->assertTrue(true); // phpunit requires an assertion
    }

    /**
     * @test
     */
    public function shutdownCallsExporterShutdown()
    {
        $exporter = self::createMock(Exporter::class);
        $proc = new SimpleSpanProcessor($exporter);

        $exporter->expects($this->exactly(1))->method('shutdown');
        $proc->shutdown();
    }

    /**
     * @test
     */
    public function noExportAfterShutdown()
    {
        $exporter = self::createMock(Exporter::class);
        $exporter->expects($this->exactly(1))->method('shutdown');

        $proc = new SimpleSpanProcessor($exporter);
        $proc->shutdown();

        $span = self::createMock(Span::class);
        $proc->onStart($span);
        $proc->onEnd($span);
    }

    /**
     * @test
     */
    public function shouldExportOnlySampledSpans()
    {
        $exporter = self::createMock(Exporter::class);
        $exporter->expects($this->never())->method('export');

        $spanContext = self::createStub(SpanContext::class);
        $spanContext->method('isSampled')->willReturn(false);
        $span = self::createStub(Span::class);
        $span->method('getSpanContext')->willReturn($spanContext);

        (new SimpleSpanProcessor($exporter))->onEnd($span);
    }
}
