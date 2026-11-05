<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation;

ServiceLoader::register(Instrumentation::class, \OpenTelemetry\SDK\Trace\AutoRootSpan\AutoRootSpanInstrumentation::class);
ServiceLoader::register(ComponentProvider::class, \OpenTelemetry\SDK\Trace\AutoRootSpan\ComponentProvider::class);
ServiceLoader::register(EnvComponentLoader::class, \OpenTelemetry\SDK\Trace\AutoRootSpan\EnvComponentLoader::class);
