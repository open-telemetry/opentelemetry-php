<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exporters;

use Exception;
use OpenTelemetry\API\Metrics as API;
use OpenTelemetry\SDK\Metrics\Exceptions\RetryableExportException;

abstract class AbstractExporter implements API\ExporterInterface
{
    /**
     * {@inheritDoc}
     */
    public function export(iterable $metrics): int
    {
        if (empty($metrics)) {
            return API\ExporterInterface::SUCCESS;
        }

        try {
            foreach ($metrics as $metric) {
                if (! $metric instanceof API\MetricInterface) {
                    throw new \InvalidArgumentException('Metric must implement ' . API\MetricInterface::class);
                }
            }

            $this->doExport($metrics);

            return API\ExporterInterface::SUCCESS;
        } catch (RetryableExportException $exception) {
            return API\ExporterInterface::FAILED_RETRYABLE;
        } catch (Exception $exception) {
            return API\ExporterInterface::FAILED_NOT_RETRYABLE;
        }
    }

    /**
     * Sends metrics to the destination system
     *
     * @access	protected
     * @param	iterable<API\MetricInterface> $metrics
     * @return	void
     */
    abstract protected function doExport(iterable $metrics): void;
}
