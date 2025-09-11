<?php

declare(strict_types=1);

\OpenTelemetry\SDK\Registry::registerTextMapPropagator(
    \OpenTelemetry\SDK\Common\Configuration\KnownValues::VALUE_BAGGAGE,
    \OpenTelemetry\API\Baggage\Propagation\BaggagePropagator::getInstance()
);
\OpenTelemetry\SDK\Registry::registerTextMapPropagator(
    \OpenTelemetry\SDK\Common\Configuration\KnownValues::VALUE_TRACECONTEXT,
    \OpenTelemetry\API\Trace\Propagation\TraceContextPropagator::getInstance()
);
\OpenTelemetry\SDK\Registry::registerTextMapPropagator(
    \OpenTelemetry\SDK\Common\Configuration\KnownValues::VALUE_NONE,
    \OpenTelemetry\Context\Propagation\NoopTextMapPropagator::getInstance()
);

\OpenTelemetry\SDK\Registry::registerResponsePropagator(
    \OpenTelemetry\SDK\Common\Configuration\KnownValues::VALUE_NONE,
    \OpenTelemetry\Context\Propagation\NoopResponsePropagator::getInstance()
);
