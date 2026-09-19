<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\API\Signals;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Registry;
use function parse_url;
use const PHP_URL_SCHEME;

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

        $cacert = OtlpUtil::getStringVar(Variables::OTEL_EXPORTER_OTLP_METRICS_CERTIFICATE, Variables::OTEL_EXPORTER_OTLP_CERTIFICATE);
        $cert = OtlpUtil::getStringVar('OTEL_EXPORTER_OTLP_METRICS_CERTIFICATE', 'OTEL_EXPORTER_OTLP_CERTIFICATE');
        $key = OtlpUtil::getStringVar('OTEL_EXPORTER_OTLP_METRICS_CLIENT_KEY', 'OTEL_EXPORTER_OTLP_CLIENT_KEY');

        $factoryClass = Registry::transportFactory($protocol);
        $factory = $this->transportFactory ?: new $factoryClass();

        return $factory->create(
            $endpoint,
            Protocols::contentType($protocol),
            $headers,
            $compression,
            $timeout,
            cacert: $cacert,
            cert: $cert,
            key: $key,
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
        if ($protocol === Protocols::GRPC) {
            $endpoint = OtlpUtil::getStringVar(
                Variables::OTEL_EXPORTER_OTLP_METRICS_ENDPOINT,
                Variables::OTEL_EXPORTER_OTLP_ENDPOINT,
            ) ?? 'http://localhost:4317';

            if (parse_url($endpoint, PHP_URL_SCHEME) === null) {
                $insecure = OtlpUtil::getBoolVar(
                    Variables::OTEL_EXPORTER_OTLP_METRICS_INSECURE,
                    Variables::OTEL_EXPORTER_OTLP_INSECURE,
                ) ?? false;
                $endpoint = $insecure
                    ? 'http://' . $endpoint
                    : 'https://' . $endpoint;
            }

            return $endpoint . OtlpUtil::method(Signals::METRICS);
        }

        if (Configuration::has(Variables::OTEL_EXPORTER_OTLP_METRICS_ENDPOINT)) {
            return Configuration::getString(Variables::OTEL_EXPORTER_OTLP_METRICS_ENDPOINT);
        }

        $endpoint = Configuration::getString(Variables::OTEL_EXPORTER_OTLP_ENDPOINT);

        return HttpEndpointResolver::create()->resolveToString($endpoint, Signals::METRICS);
    }
}
