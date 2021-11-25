<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\SpanExporter;

use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\Tests\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

abstract class AbstractExporterTest extends TestCase
{
    /**
     * Must be implemented by concrete TestCases
     *
     * @return SpanExporterInterface
     */
    abstract public function createExporter(): SpanExporterInterface;

    public function testShutdown(): void
    {
        $this->assertTrue(
            $this->createExporter()->shutdown()
        );
    }

    public function testForceFlush(): void
    {
        $this->assertTrue(
            $this->createExporter()->forceFlush()
        );
    }

    public function testFailsIfNotRunning(): void
    {
        $exporter = $this->createExporter();

        $span = $this->createMock(SpanData::class);
        $exporter->shutdown();

        $this->assertSame(SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE, $exporter->export([$span]));
    }

    public function testExportEmptySpanCollection(): void
    {
        $this->assertEquals(
            SpanExporterInterface::STATUS_SUCCESS,
            $this->createExporter()->export([])
        );
    }
}
