<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

final class Histogram implements DataInterface
{

    /**
     * @var iterable<HistogramDataPoint>
     * @readonly
     */
    public iterable $dataPoints;
    /**
     * @var string|Temporality
     * @readonly
     */
    public $temporality;
    /**
     * @param iterable<HistogramDataPoint> $dataPoints
     * @param string|Temporality $temporality
     */
    public function __construct(iterable $dataPoints, $temporality)
    {
        $this->dataPoints = $dataPoints;
        $this->temporality = $temporality;
    }
}
