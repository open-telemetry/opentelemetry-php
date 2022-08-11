<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

final class Gauge implements DataInterface
{

    /**
     * @var iterable<NumberDataPoint>
     * @readonly
     */
    public iterable $dataPoints;
    /**
     * @param iterable<NumberDataPoint> $dataPoints
     */
    public function __construct(iterable $dataPoints)
    {
        $this->dataPoints = $dataPoints;
    }
}
