<?php

declare(strict_types=1);

use OpenTelemetry\Extension\Propagator\Jaeger\JaegerBaggagePropagator;
use OpenTelemetry\Extension\Propagator\Jaeger\JaegerPropagator;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Registry;

Registry::registerTextMapPropagator(
    KnownValues::VALUE_JAEGER,
    JaegerPropagator::getInstance()
);

Registry::registerTextMapPropagator(
    KnownValues::VALUE_JAEGER_BAGGAGE,
    JaegerBaggagePropagator::getInstance()
);
