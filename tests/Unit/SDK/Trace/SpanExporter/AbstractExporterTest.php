<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

abstract class AbstractExporterTest extends TestCase
{
    /**
     * Must be implemented by concrete TestCases
     *
     * @return SpanExporterInterface
     */
    abstract public function createExporter(): SpanExporterInterface;

    public function test_shutdown(): void
    {
        $this->assertTrue(
            $this->createExporter()->shutdown()
        );
    }

    public function test_force_flush(): void
    {
        $this->assertTrue(
            $this->createExporter()->forceFlush()
        );
    }

    public function test_fails_if_not_test_running(): void
    {
        $exporter = $this->createExporter();

        $span = $this->createMock(SpanData::class);
        $exporter->shutdown();

        $this->assertSame(SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE, $exporter->export([$span]));
    }

    public function test_export_empty_span_collection(): void
    {
        $this->assertEquals(
            SpanExporterInterface::STATUS_SUCCESS,
            $this->createExporter()->export([])
        );
    }
}
