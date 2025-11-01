<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\Configuration\General\ConfigEnv;

use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoaderRegistry;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvResolver;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\GeneralInstrumentationConfiguration;
use OpenTelemetry\API\Instrumentation\Configuration\General\HttpConfig;

/**
 * @implements EnvComponentLoader<GeneralInstrumentationConfiguration>
 */
final class EnvComponentLoaderHttpConfig implements EnvComponentLoader
{
    #[\Override]
    public function load(EnvResolver $env, EnvComponentLoaderRegistry $registry, Context $context): GeneralInstrumentationConfiguration
    {
        return new HttpConfig([
            'client' => [
                'request_captured_headers' => $env->list('OTEL_PHP_INSTRUMENTATION_HTTP_REQUEST_HEADERS') ?? [],
                'response_captured_headers' => $env->list('OTEL_PHP_INSTRUMENTATION_HTTP_RESPONSE_HEADERS') ?? [],
            ],
            'server' => [
                'request_captured_headers' => $env->list('OTEL_PHP_INSTRUMENTATION_HTTP_REQUEST_HEADERS') ?? [],
                'response_captured_headers' => $env->list('OTEL_PHP_INSTRUMENTATION_HTTP_RESPONSE_HEADERS') ?? [],
            ],
        ]);
    }

    #[\Override]
    public function name(): string
    {
        return HttpConfig::class;
    }
}
