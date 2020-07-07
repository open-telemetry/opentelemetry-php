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
            self::createMock(Span::class)
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
        // grap a reference to the InvocationOrder object so we can inspect/assert *when* the call happens
        $exporter->expects($spy = $this->exactly(1))->method('shutdown');

        $this->assertEquals(0, $spy->getInvocationCount());

        $proc = new SimpleSpanProcessor($exporter);
        $proc->shutdown();
        // calling SpanProcessor's shutdown() calls Exporter's shutdown()
        $this->assertEquals(1, $spy->getInvocationCount());

        $span = self::createMock(Span::class);
        $proc->onStart($span);
        $proc->onEnd($span);

        // calling onEnd here does NOT result in another call to shutdown
        $this->assertEquals(1, $spy->getInvocationCount());
    }

    /**
     * @test
     */
    public function shouldNotExportSampledSpans()
    {
        $exporter = self::createMock(Exporter::class);
        $exporter->expects($this->never())->method('export');

        (new SimpleSpanProcessor($exporter))->onEnd(
            new Span('sampled_span', new SpanContext('40de9aea7305cced3bb10ed45ba6870d', '277c169397adf2ec', 1))
        );
    }
}
