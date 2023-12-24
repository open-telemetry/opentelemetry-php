<?php

declare(strict_types=1);

use OpenTelemetry\Extension\Propagator\B3\B3Propagator;
use OpenTelemetry\SDK\Registry;

if (!class_exists(Registry::class)) {
    return;
}

Registry::registerTextMapPropagator(
    'b3',
    B3Propagator::getB3SingleHeaderInstance()
);
Registry::registerTextMapPropagator(
    'b3multi',
    B3Propagator::getB3MultiHeaderInstance()
);
