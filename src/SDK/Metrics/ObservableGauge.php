<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics\ObservableGaugeInterface;

/**
 * @internal
 */
final class ObservableGauge implements ObservableGaugeInterface, InstrumentHandle
{
    use ObservableInstrumentTrait;
}
