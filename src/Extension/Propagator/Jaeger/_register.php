<?php

declare(strict_types=1);

use OpenTelemetry\Extension\Propagator\Jaeger\JaegerBaggagePropagator;
use OpenTelemetry\Extension\Propagator\Jaeger\JaegerPropagator;
use OpenTelemetry\SDK\Registry;

if (!class_exists(Registry::class)) {
    return;
}

Registry::registerTextMapPropagator(
    'jaeger',
    JaegerPropagator::getInstance()
);

Registry::registerTextMapPropagator(
    'jaeger-baggage',
    JaegerBaggagePropagator::getInstance()
);
