<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Metrics;

use Exception;
use OpenTelemetry\Metrics as API;
use OpenTelemetry\Sdk\Metrics\Exceptions\CantBeExported;
use OpenTelemetry\Sdk\Metrics\Exceptions\RetryableExportException;

abstract class AbstractExporter implements API\Exporter
{
    /**
     * {@inheritDoc}
     */
    public function export(iterable $metrics): int
    {
        // todo: we need to implement Logger?
        if (empty($metrics)) {
            return API\Exporter::SUCCESS;
        }

        $preparedMetrics = [];

        try {
            foreach ($metrics as $metric) {
                if (!($metric instanceof API\Metrics)) {
                    throw new CantBeExported('Metric must be an instance of API\Metrics');
                }

                $preparedMetrics[] = $this->getFormatted($metric);
            }

            $this->send($preparedMetrics);

            return API\Exporter::SUCCESS;
        } catch (RetryableExportException $exception) {
            return API\Exporter::FAILED_RETRYABLE;
        } catch (Exception $exception) {
            return API\Exporter::FAILED_NOT_RETRYABLE;
        }
    }

    /**
     * Returns formatted metric data
     *
     * @access	protected
     * @param	API\Metrics	$metric
     * @return	mixed
     */
    abstract protected function getFormatted(API\Metrics $metric);

    /**
     * Sends formatted metrics to the destination system
     *
     * @access	protected
     * @param	array $preparedMetrics
     * @return	void
     */
    abstract protected function send(array $preparedMetrics): void;
}
