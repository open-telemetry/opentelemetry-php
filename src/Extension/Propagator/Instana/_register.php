<?php

declare(strict_types=1);
use OpenTelemetry\Extension\Propagator\Instana\InstanaMultiPropagator;
use OpenTelemetry\SDK\Registry;

if (!class_exists(Registry::class)) {
    return;
}
Registry::registerTextMapPropagator(
    'instana',
    InstanaMultiPropagator::getInstance()
);
