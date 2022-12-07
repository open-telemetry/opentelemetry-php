<?php

declare(strict_types=1);

\OpenTelemetry\SDK\FactoryRegistry::registerTextMapPropagator(
    \OpenTelemetry\SDK\Common\Configuration\KnownValues::VALUE_BAGGAGE,
    \OpenTelemetry\API\Baggage\Propagation\BaggagePropagator::getInstance()
);
\OpenTelemetry\SDK\FactoryRegistry::registerTextMapPropagator(
    \OpenTelemetry\SDK\Common\Configuration\KnownValues::VALUE_TRACECONTEXT,
    \OpenTelemetry\API\Trace\Propagation\TraceContextPropagator::getInstance()
);
\OpenTelemetry\SDK\FactoryRegistry::registerTextMapPropagator(
    \OpenTelemetry\SDK\Common\Configuration\KnownValues::VALUE_NONE,
    \OpenTelemetry\Context\Propagation\NoopTextMapPropagator::getInstance()
);
