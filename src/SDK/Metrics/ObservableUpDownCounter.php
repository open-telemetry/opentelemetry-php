<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics\ObservableUpDownCounterInterface;

/**
 * @internal
 */
final class ObservableUpDownCounter implements ObservableUpDownCounterInterface, InstrumentHandle
{
    use ObservableInstrumentTrait;
}
