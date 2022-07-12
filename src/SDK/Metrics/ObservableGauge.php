<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics\ObservableGaugeInterface;

final class ObservableGauge implements ObservableGaugeInterface
{
    use ObservableInstrumentTrait;
}
