<?php

declare(strict_types=1);

\OpenTelemetry\SDK\FactoryRegistry::registerTextMapPropagatorFactory(
    \OpenTelemetry\SDK\Common\Configuration\KnownValues::VALUE_B3,
    \OpenTelemetry\Extension\Propagator\B3\B3SinglePropagatorFactory::class
);
\OpenTelemetry\SDK\FactoryRegistry::registerTextMapPropagatorFactory(
    \OpenTelemetry\SDK\Common\Configuration\KnownValues::VALUE_B3_MULTI,
    \OpenTelemetry\Extension\Propagator\B3\B3MultiPropagatorFactory::class
);
