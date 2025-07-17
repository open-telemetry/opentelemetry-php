<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\API\Signals;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Defaults;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Registry;

class MetricExporterFactory implements MetricExporterFactoryInterface
{
    private const DEFAULT_COMPRESSION = 'none';

    public function __construct(private readonly ?TransportFactoryInterface $transportFactory = null)
    {
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    #[\Override]
    public function create(): MetricExporterInterface
    {
        $protocol = Configuration::has(Variables::OTEL_EXPORTER_OTLP_METRICS_PROTOCOL)
            ? Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_METRICS_PROTOCOL)
            : Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_PROTOCOL);
        $temporality = $this->getTemporality();

        return new MetricExporter($this->buildTransport($protocol), $temporality);
    }

    /**
     * @psalm-suppress UndefinedClass
     */
    private function buildTransport(string $protocol): TransportInterface
    {
        /**
         * @todo (https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/sdk.md#periodic-exporting-metricreader)
         * - OTEL_METRIC_EXPORT_INTERVAL
         */
        $endpoint = $this->getEndpoint($protocol);

        $headers = OtlpUtil::getHeaders(Signals::METRICS);
        $compression = $this->getCompression();
        $timeout = $this->getTimeout();

        $factoryClass = Registry::transportFactory($protocol);
        $factory = $this->transportFactory ?: new $factoryClass();

        return $factory->create(
            $endpoint,
            Protocols::contentType($protocol),
            $headers,
            $compression,
            $timeout,
        );
    }

    /**
     * @phpstan-ignore-next-line
     */
    private function getTemporality(): string|Temporality|null
    {
        $value = Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE);

        return match (strtolower($value)) {
            'cumulative' => Temporality::CUMULATIVE,
            'delta' => Temporality::DELTA,
            'lowmemory' => null,
            default => throw new \UnexpectedValueException('Unknown temporality: ' . $value),
        };
    }

    private function getCompression(): string
    {
        return Configuration::has(Variables::OTEL_EXPORTER_OTLP_METRICS_COMPRESSION) ?
            Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_METRICS_COMPRESSION) :
            Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_COMPRESSION, self::DEFAULT_COMPRESSION);
    }

    private function getTimeout(): float
    {
        $value = Configuration::has(Variables::OTEL_EXPORTER_OTLP_METRICS_TIMEOUT) ?
            Configuration::getInt(Variables::OTEL_EXPORTER_OTLP_METRICS_TIMEOUT) :
            Configuration::getInt(Variables::OTEL_EXPORTER_OTLP_TIMEOUT);

        return $value/1000;
    }

    private function getEndpoint(string $protocol): string
    {
        if (Configuration::has(Variables::OTEL_EXPORTER_OTLP_METRICS_ENDPOINT)) {
            return Configuration::getString(Variables::OTEL_EXPORTER_OTLP_METRICS_ENDPOINT);
        }
        $endpoint = Configuration::has(Variables::OTEL_EXPORTER_OTLP_ENDPOINT)
            ? Configuration::getString(Variables::OTEL_EXPORTER_OTLP_ENDPOINT)
            : Defaults::OTEL_EXPORTER_OTLP_ENDPOINT;
        if ($protocol === Protocols::GRPC) {
            return $endpoint . OtlpUtil::method(Signals::METRICS);
        }

        return HttpEndpointResolver::create()->resolveToString($endpoint, Signals::METRICS);
    }
}
