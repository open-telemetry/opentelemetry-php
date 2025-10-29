<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use InvalidArgumentException;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterConsole;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterMemory;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterOtlpFile;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterOtlpGrpc;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterOtlpHttp;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterZipkin;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Registry;
use RuntimeException;

/**
 * ComponentProvider-based Exporter factory that reads configuration from environment variables
 * and uses the modern ComponentProvider system for component creation.
 */
class ComponentProviderBasedExporterFactory
{
    private Context $context;

    public function __construct()
    {
        $this->context = new Context();
    }

    /**
     * @throws RuntimeException
     */
    public function create(): ?SpanExporterInterface
    {
        $exporters = Configuration::getList(Variables::OTEL_TRACES_EXPORTER);
        if (1 !== count($exporters)) {
            throw new InvalidArgumentException(sprintf('Configuration %s requires exactly 1 exporter', Variables::OTEL_TRACES_EXPORTER));
        }

        $exporterName = $exporters[0];
        if ($exporterName === 'none') {
            return null;
        }

        return match ($exporterName) {
            'console' => $this->createConsoleExporter(),
            'memory' => $this->createMemoryExporter(),
            'otlp' => $this->createOtlpExporter(),
            'zipkin' => $this->createZipkinExporter(),
            default => $this->createRegistryExporter($exporterName),
        };
    }

    private function createConsoleExporter(): SpanExporterInterface
    {
        $provider = new SpanExporterConsole();
        return $provider->createPlugin([], $this->context);
    }

    private function createMemoryExporter(): SpanExporterInterface
    {
        $provider = new SpanExporterMemory();
        return $provider->createPlugin([], $this->context);
    }

    private function createOtlpExporter(): SpanExporterInterface
    {
        // Determine OTLP protocol from environment
        $protocol = Configuration::getString(Variables::OTEL_EXPORTER_OTLP_PROTOCOL)
            ?? Configuration::getString(Variables::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL)
            ?? 'http/protobuf';

        return match ($protocol) {
            'http/protobuf', 'http/json' => $this->createOtlpHttpExporter(),
            'grpc' => $this->createOtlpGrpcExporter(),
            'file' => $this->createOtlpFileExporter(),
            default => throw new InvalidArgumentException(sprintf('Unknown OTLP protocol: %s', $protocol)),
        };
    }

    private function createOtlpHttpExporter(): SpanExporterInterface
    {
        $provider = new SpanExporterOtlpHttp();

        // Build configuration from environment variables
        $config = [];

        // Add endpoint if specified
        $endpoint = Configuration::getString(Variables::OTEL_EXPORTER_OTLP_ENDPOINT)
            ?? Configuration::getString(Variables::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT);
        if ($endpoint !== null) {
            $config['endpoint'] = $endpoint;
        }

        // Add headers if specified
        $headers = Configuration::getString(Variables::OTEL_EXPORTER_OTLP_HEADERS)
            ?? Configuration::getString(Variables::OTEL_EXPORTER_OTLP_TRACES_HEADERS);
        if ($headers !== null) {
            $config['headers'] = $headers;
        }

        return $provider->createPlugin($config, $this->context);
    }

    private function createOtlpGrpcExporter(): SpanExporterInterface
    {
        $provider = new SpanExporterOtlpGrpc();

        $config = [];

        $endpoint = Configuration::getString(Variables::OTEL_EXPORTER_OTLP_ENDPOINT)
            ?? Configuration::getString(Variables::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT);
        if ($endpoint !== null) {
            $config['endpoint'] = $endpoint;
        }

        $headers = Configuration::getString(Variables::OTEL_EXPORTER_OTLP_HEADERS)
            ?? Configuration::getString(Variables::OTEL_EXPORTER_OTLP_TRACES_HEADERS);
        if ($headers !== null) {
            $config['headers'] = $headers;
        }

        return $provider->createPlugin($config, $this->context);
    }

    private function createOtlpFileExporter(): SpanExporterInterface
    {
        $provider = new SpanExporterOtlpFile();

        $config = [];

        $endpoint = Configuration::getString(Variables::OTEL_EXPORTER_OTLP_ENDPOINT)
            ?? Configuration::getString(Variables::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT);
        if ($endpoint !== null) {
            $config['endpoint'] = $endpoint;
        }

        return $provider->createPlugin($config, $this->context);
    }

    private function createZipkinExporter(): SpanExporterInterface
    {
        $provider = new SpanExporterZipkin();

        $config = [];

        $endpoint = Configuration::getString(Variables::OTEL_EXPORTER_ZIPKIN_ENDPOINT);
        if ($endpoint !== null) {
            $config['endpoint'] = $endpoint;
        }

        return $provider->createPlugin($config, $this->context);
    }

    /**
     * Fallback to the old registry system for unknown exporters
     */
    private function createRegistryExporter(string $exporterName): SpanExporterInterface
    {
        $factory = Registry::spanExporterFactory($exporterName);
        return $factory->create();
    }
}
