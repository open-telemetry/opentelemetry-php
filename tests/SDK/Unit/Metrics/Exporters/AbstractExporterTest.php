<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Metrics\Exporters;

use OpenTelemetry\API\Metrics as API;
use OpenTelemetry\SDK\Metrics\Exporters\AbstractExporter;
use PHPUnit\Framework\TestCase;

class AbstractExporterTest extends TestCase
{
    public function testEmptyMetricsExportReturnsSuccess()
    {
        $this->assertEquals(
            API\ExporterInterface::SUCCESS,
            $this->getExporter()->export([])
        );
    }

    public function testErrorReturnsIfTryingToExportNotAMetric()
    {
        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress InvalidArgument
         */
        $export = $this->getExporter()->export([1]);

        $this->assertEquals(API\ExporterInterface::FAILED_NOT_RETRYABLE, $export);
    }

    protected function getExporter(): AbstractExporter
    {
        return new class() extends AbstractExporter {
            protected function doExport(iterable $metrics): void
            {
            }
        };
    }
}
