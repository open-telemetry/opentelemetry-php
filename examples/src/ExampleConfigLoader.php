<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoaderRegistry;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvResolver;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\InstrumentationConfiguration;

/**
 * @implements EnvComponentLoader<InstrumentationConfiguration>
 */
final class ExampleConfigLoader implements EnvComponentLoader
{
    public function load(EnvResolver $env, EnvComponentLoaderRegistry $registry, Context $context): InstrumentationConfiguration
    {
        return new ExampleConfig(
            spanName: $env->string('OTEL_PHP_EXAMPLE_INSTRUMENTATION_SPAN_NAME') ?? 'example',
        );
    }

    public function name(): string
    {
        return ExampleConfig::class;
    }
}
