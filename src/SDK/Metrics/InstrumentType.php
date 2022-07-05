<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

final class InstrumentType
{
    public const COUNTER = 'Counter';
    public const UP_DOWN_COUNTER = 'UpDownCounter';
    public const HISTOGRAM = 'Histogram';

    public const ASYNCHRONOUS_COUNTER = 'AsynchronousCounter';
    public const ASYNCHRONOUS_UP_DOWN_COUNTER = 'AsynchronousUpDownCounter';
    public const ASYNCHRONOUS_GAUGE = 'AsynchronousGauge';
}
