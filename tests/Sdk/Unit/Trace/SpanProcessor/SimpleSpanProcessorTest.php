<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace\SpanProcessor;

use OpenTelemetry\Sdk\Trace\Exporter;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\SpanProcessor\SimpleSpanProcessor;
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

        (new SimpleSpanProcessor($exporter))->onEnd(
            new Span('sampled_span', new SpanContext('40de9aea7305cced3bb10ed45ba6872d', '277c169397adf2ec', 1))
        );
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

        (new SimpleSpanProcessor($exporter))->onEnd(
            new Span('sampled_span', new SpanContext('40de9aea7305cced3bb10ed45ba6870d', '277c169397adf2ec', 0))
        );
    }
}
