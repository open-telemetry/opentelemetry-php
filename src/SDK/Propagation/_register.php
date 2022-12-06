<?php

declare(strict_types=1);

\OpenTelemetry\SDK\FactoryRegistry::registerTextMapPropagatorFactory(
    \OpenTelemetry\SDK\Common\Configuration\KnownValues::VALUE_BAGGAGE,
    \OpenTelemetry\API\Baggage\Propagation\BaggagePropagatorFactory::class
);
\OpenTelemetry\SDK\FactoryRegistry::registerTextMapPropagatorFactory(
    \OpenTelemetry\SDK\Common\Configuration\KnownValues::VALUE_TRACECONTEXT,
    \OpenTelemetry\API\Trace\Propagation\TraceContextPropagatorFactory::class
);
\OpenTelemetry\SDK\FactoryRegistry::registerTextMapPropagatorFactory(
    \OpenTelemetry\SDK\Common\Configuration\KnownValues::VALUE_NONE,
    \OpenTelemetry\Context\Propagation\NoopTextMapPropagatorFactory::class
);
