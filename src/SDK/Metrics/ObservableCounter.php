<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics\ObservableCounterInterface;

/**
 * @internal
 */
final class ObservableCounter implements ObservableCounterInterface, InstrumentHandle
{
    use ObservableInstrumentTrait;
}
