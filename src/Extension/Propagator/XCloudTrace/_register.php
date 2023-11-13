<?php

declare(strict_types=1);

use OpenTelemetry\Extension\Propagator\XCloudTrace\XCloudTracePropagator;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Registry;

Registry::registerTextMapPropagator(
    KnownValues::VALUE_XCLOUD_TRACE,
    XCloudTracePropagator::getInstance()
);
