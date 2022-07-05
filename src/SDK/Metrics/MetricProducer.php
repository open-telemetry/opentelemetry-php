<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Data\Metric;

interface MetricProducer {

    /**
     * @return list<Metric>
     */
    public function collect(): array;
}
