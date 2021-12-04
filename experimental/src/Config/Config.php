<?php

declare(strict_types=1);

namespace OpenTelemetry\Experimental\Config;

class Config implements ConfigInterface
{
    public array $log;
    public array $exporters = [];
    public array $spanProcessors = [];
    public array $sampler = [];
    public array $service;
    public ResourceConfig $resource;

    public function __construct(array $userConfig, array $environmentConfig)
    {
        $this->log = [
            'level' => $userConfig['log.level'] ?? $environmentConfig['OTEL_LOG_LEVEL'] ?? null,
            'destination' => $userConfig['log.destination'] ?? $environmentConfig['OTEL_PHP_LOG_DESTINATION'] ?? 'php://stdout',
        ];
        $this->service = [
            'name' => $userConfig['service.name'] ?? $environmentConfig['OTEL_SERVICE_NAME'] ?? 'unknown_service', //TODO can come from resource attributes
        ];
        $this->sampler = [
            'name' => $userConfig['trace.sampler'] ?? $environmentConfig['OTEL_TRACES_SAMPLER'] ?? 'parentbased_always_on',
            'arg' => $userConfig['trace.sampler_arg'] ?? $environmentConfig['OTEL_TRACES_SAMPLER_ARG'] ?? null,
        ];
        $this->resource = new ResourceConfig($userConfig, $environmentConfig);
    }

    public function addExporter(string $name, ExporterConfigInterface $config): void
    {
        $this->exporters[$name] = $config;
    }

    public function addSpanProcessor(string $name, SpanProcessorConfigInterface $config): void
    {
        $this->spanProcessors[$name] = $config;
    }
}
