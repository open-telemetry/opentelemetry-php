<?php

declare(strict_types=1);

\OpenTelemetry\SDK\Registry::registerSamplerFactory('always_on', \OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSamplerFactory::class);
\OpenTelemetry\SDK\Registry::registerSamplerFactory('always_off', \OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSamplerFactory::class);
\OpenTelemetry\SDK\Registry::registerSamplerFactory('traceidratio', \OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSamplerFactory::class);
\OpenTelemetry\SDK\Registry::registerSamplerFactory('parentbased_always_on', \OpenTelemetry\SDK\Trace\Sampler\ParentBasedAlwaysOnSamplerFactory::class);
\OpenTelemetry\SDK\Registry::registerSamplerFactory('parentbased_always_off', \OpenTelemetry\SDK\Trace\Sampler\ParentBasedAlwaysOffSamplerFactory::class);
\OpenTelemetry\SDK\Registry::registerSamplerFactory('parentbased_traceidratio', \OpenTelemetry\SDK\Trace\Sampler\ParentBasedTraceIdRatioSamplerFactory::class);
