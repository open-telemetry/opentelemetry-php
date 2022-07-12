<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics\ObservableCounterInterface;

final class ObservableCounter implements ObservableCounterInterface
{
    use ObservableInstrumentTrait;
}
