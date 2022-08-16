<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

final class Sum implements DataInterface
{

    /**
     * @var iterable<NumberDataPoint>
     * @readonly
     */
    public iterable $dataPoints;
    /**
     * @var string|Temporality
     * @readonly
     */
    public $temporality;
    /**
     * @readonly
     */
    public bool $monotonic;
    /**
     * @param iterable<NumberDataPoint> $dataPoints
     * @param string|Temporality $temporality
     */
    public function __construct(iterable $dataPoints, $temporality, bool $monotonic)
    {
        $this->dataPoints = $dataPoints;
        $this->temporality = $temporality;
        $this->monotonic = $monotonic;
    }
}
