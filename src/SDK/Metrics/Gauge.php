<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics\GaugeInterface;

/**
 * @internal
 */
final class Gauge implements GaugeInterface, InstrumentHandle
{
    use SynchronousInstrumentTrait { write as record; }
}
