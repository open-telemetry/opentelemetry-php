<?php

declare(strict_types=1);

use OpenTelemetry\Extension\Propagator\B3\B3Propagator;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\FactoryRegistry;

FactoryRegistry::registerTextMapPropagator(
    KnownValues::VALUE_B3,
    B3Propagator::getB3SingleHeaderInstance()
);
FactoryRegistry::registerTextMapPropagator(
    KnownValues::VALUE_B3_MULTI,
    B3Propagator::getB3MultiHeaderInstance()
);
