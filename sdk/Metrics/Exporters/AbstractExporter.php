<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Metrics\Exporters;

use Exception;
use OpenTelemetry\Metrics as API;
use OpenTelemetry\Sdk\Metrics\Exceptions\CantBeExported;
use OpenTelemetry\Sdk\Metrics\Exceptions\RetryableExportException;
use Webmozart\Assert\Assert;

abstract class AbstractExporter implements API\Exporter
{
    /**
     * {@inheritDoc}
     */
    public function export(iterable $metrics): int
    {
        if (empty($metrics)) {
            return API\Exporter::SUCCESS;
        }

        $preparedMetrics = [];

        try {
            Assert::allIsInstanceOf($metrics, API\Metrics::class);

            $this->doExport($metrics);

            return API\Exporter::SUCCESS;
        } catch (RetryableExportException $exception) {
            return API\Exporter::FAILED_RETRYABLE;
        } catch (Exception $exception) {
            return API\Exporter::FAILED_NOT_RETRYABLE;
        }
    }

    /**
     * Sends metrics to the destination system
     *
     * @access	protected
     * @param	iterable<API\Metrics> $metrics
     * @return	void
     */
    abstract protected function doExport(iterable $metrics): void;
}
