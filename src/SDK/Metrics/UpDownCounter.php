<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics\UpDownCounterInterface;

/**
 * @internal
 */
final class UpDownCounter implements UpDownCounterInterface, InstrumentHandle
{
    use SynchronousInstrumentTrait { write as add; }
}
