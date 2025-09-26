<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\API\Signals;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Defaults;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Services\Loader;
use OpenTelemetry\SDK\Metrics\AggregationTemporalitySelector;
use OpenTelemetry\SDK\Metrics\AggregationTemporalitySelectorInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;

class MetricExporterFactory implements MetricExporterFactoryInterface
{
    private const DEFAULT_COMPRESSION = 'none';

    public function __construct(private readonly ?TransportFactoryInterface $transportFactory = null)
    {
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function create(): MetricExporterInterface
    {
        $protocol = Configuration::has(Variables::OTEL_EXPORTER_OTLP_METRICS_PROTOCOL)
            ? Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_METRICS_PROTOCOL)
            : Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_PROTOCOL);
        $selector = $this->getTemporalitySelector();

        return new MetricExporter($this->buildTransport($protocol), $selector);
    }

    public function type(): string
    {
        return 'otlp';
    }

    public function priority(): int
    {
        return 0;
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

        $factory = $this->transportFactory ?? Loader::transportFactory($protocol);

        return $factory->create(
            $endpoint,
            Protocols::contentType($protocol),
            $headers,
            $compression,
            $timeout,
        );
    }

    private function getTemporalitySelector(): AggregationTemporalitySelectorInterface
    {
        $value = Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE);

        return match (strtolower($value)) {
            'cumulative' => AggregationTemporalitySelector::alwaysCumulative(),
            'delta' => AggregationTemporalitySelector::deltaPreferred(),
            'lowmemory' => AggregationTemporalitySelector::lowMemory(),
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
