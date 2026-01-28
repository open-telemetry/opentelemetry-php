<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\SDK\ConfigEnv\Trace\SamplerLoaderAlwaysOff;
use OpenTelemetry\SDK\ConfigEnv\Trace\SamplerLoaderAlwaysOn;
use OpenTelemetry\SDK\ConfigEnv\Trace\SamplerLoaderParentBasedAlwaysOff;
use OpenTelemetry\SDK\ConfigEnv\Trace\SamplerLoaderParentBasedAlwaysOn;
use OpenTelemetry\SDK\ConfigEnv\Trace\SamplerLoaderParentBasedTraceIdRatio;
use OpenTelemetry\SDK\ConfigEnv\Trace\SamplerLoaderTraceIdRatio;

ServiceLoader::register(EnvComponentLoader::class, SamplerLoaderAlwaysOff::class);
ServiceLoader::register(EnvComponentLoader::class, SamplerLoaderAlwaysOn::class);
ServiceLoader::register(EnvComponentLoader::class, SamplerLoaderParentBasedAlwaysOff::class);
ServiceLoader::register(EnvComponentLoader::class, SamplerLoaderParentBasedAlwaysOn::class);
ServiceLoader::register(EnvComponentLoader::class, SamplerLoaderParentBasedTraceIdRatio::class);
ServiceLoader::register(EnvComponentLoader::class, SamplerLoaderTraceIdRatio::class);
