<?php

declare(strict_types=1);

use OpenTelemetry\Extension\Propagator\CloudTrace\CloudTracePropagator;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Registry;

Registry::registerTextMapPropagator(
    KnownValues::VALUE_CLOUD_TRACE,
    CloudTracePropagator::getInstance()
);

Registry::registerTextMapPropagator(
    KnownValues::VALUE_CLOUD_TRACE_ONEWAY,
    CloudTracePropagator::getOneWayInstance()
);
