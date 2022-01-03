<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Metrics\Exporters;

use OpenTelemetry\API\Metrics as API;
use OpenTelemetry\SDK\Metrics\Exporters\AbstractExporter;
use PHPUnit\Framework\TestCase;

class AbstractExporterTest extends TestCase
{
    public function test_empty_metrics_export_returns_success()
    {
        $this->assertEquals(
            API\ExporterInterface::SUCCESS,
            $this->getExporter()->export([])
        );
    }

    public function test_error_returns_if_trying_to_export_not_a_metric()
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
