<?php

declare(strict_types=1);

namespace OpenTelemetry\Experimental\Config;

use OpenTelemetry\Experimental\Config\Exporter\Otlp\Config as OtlpConfig;
use OpenTelemetry\Experimental\Config\SpanProcessor\BatchSpanProcessorConfig;
use OpenTelemetry\Experimental\Config\SpanProcessor\NoopSpanProcessorConfig;
use OpenTelemetry\Experimental\Config\SpanProcessor\SimpleSpanProcessorConfig;

class ConfigBuilder
{
    //known exporter types (exluding exotic contrib exporters)
    private array $exporters = [
        OtlpConfig::class,
    ];
    //known span processor types
    private array $spanProcessors = [
        BatchSpanProcessorConfig::class,
        SimpleSpanProcessorConfig::class,
        NoopSpanProcessorConfig::class,
    ];
    private array $userConfig = [];
    private array $environmentConfig = [];

    public function __construct()
    {
        $this->environmentConfig = $this->getEnvironmentConfig();
    }

    public function build(): object
    {
        $config = new Config($this->userConfig, $this->environmentConfig);
        foreach ($this->buildExporters() as $name => $exporterConfig) {
            $config->addExporter($name, $exporterConfig);
        }
        foreach ($this->buildSpanProcessors() as $name => $spanProcessorConfig) {
            $config->addSpanProcessor($name, $spanProcessorConfig);
        }
        return $config;
    }

    public function withUserConfig(array $config): self
    {
        $this->userConfig = $config;
        return $this;
    }

    public function withExporterConfig(string $klass): self
    {
        $this->exporters[] = $klass;
        return $this;
    }

    /**
     * Retrieve all OTEL_* environment variables
     */
    private function getEnvironmentConfig(): array
    {
        return array_filter(getenv(), function ($value, $key) {
            return strpos($key, 'OTEL_') === 0 && !in_array($value, ['', null]);
        }, ARRAY_FILTER_USE_BOTH);
    }

    private function buildExporters(): array
    {
        $configs = [];
        //@var array $exporter
        foreach ($this->userConfig['exporters'] ?? explode(',', $this->environmentConfig['OTEL_TRACES_EXPORTER'] ?? '') as $name) {
            foreach ($this->exporters as $klass) {
                //@var ExporterInterface $klass
                if ($klass::provides($name)) {
                    $configs[$name] = new $klass($this->userConfig, $this->environmentConfig);
                }
            }
        }
        return $configs;
    }

    private function buildSpanProcessors(): array
    {
        $configs = [];
        foreach ($this->userConfig['span.processors'] ?? explode(',', $this->environmentConfig['OTEL_PHP_TRACES_PROCESSOR'] ?? '') as $name) {
            foreach ($this->spanProcessors as $klass) {
                if ($klass::provides($name)) {
                    $configs[$name] = new $klass($this->userConfig, $this->environmentConfig);
                }
            }
        }
        return $configs;
    }
}
