<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\AutoRootSpan;

use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader as EnvComponentLoaderInterface;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoaderRegistry;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvResolver;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\InstrumentationConfiguration;
use OpenTelemetry\SDK\Common\Configuration\Variables;

class EnvComponentLoader implements EnvComponentLoaderInterface
{
    #[\Override]
    public function load(EnvResolver $env, EnvComponentLoaderRegistry $registry, Context $context): InstrumentationConfiguration
    {
        return new AutoRootSpanConfiguration(
            enabled: $env->bool(Variables::OTEL_PHP_TRANSACTION_SPAN_ENABLED) ?? false,
        );
    }

    #[\Override]
    public function name(): string
    {
        return self::class;
    }
}
