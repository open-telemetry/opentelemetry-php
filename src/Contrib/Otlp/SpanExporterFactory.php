<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Signals;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Defaults;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

class SpanExporterFactory implements SpanExporterFactoryInterface
{
    use LogsMessagesTrait;

    private ?TransportFactoryInterface $transportFactory;

    private const DEFAULT_COMPRESSION = 'none';

    public function __construct(?TransportFactoryInterface $transportFactory = null)
    {
        $this->transportFactory = $transportFactory;
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function create(): SpanExporterInterface
    {
        $transport = $this->buildTransport();

        return new SpanExporter($transport);
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress UndefinedClass
     */
    private function buildTransport(): TransportInterface
    {
        $protocol = $this->getProtocol();
        $contentType = Protocols::contentType($protocol);
        $endpoint = $this->getEndpoint($protocol);
        $headers = OtlpUtil::getHeaders(Signals::TRACE);
        $compression = $this->getCompression();

        $factoryClass = Registry::transportFactory($protocol);
        $factory = $this->transportFactory ?: new $factoryClass();

        return $factory->create($endpoint, $contentType, $headers, $compression);
    }

    private function getProtocol(): string
    {
        return Configuration::has(Variables::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL) ?
            Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL) :
            Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_PROTOCOL);
    }

    private function getEndpoint(string $protocol): string
    {
        if (Configuration::has(Variables::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT)) {
            return Configuration::getString(Variables::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT);
        }
        $endpoint = Configuration::has(Variables::OTEL_EXPORTER_OTLP_ENDPOINT)
            ? Configuration::getString(Variables::OTEL_EXPORTER_OTLP_ENDPOINT)
            : Defaults::OTEL_EXPORTER_OTLP_ENDPOINT;
        if ($protocol === Protocols::GRPC) {
            return $endpoint . OtlpUtil::method(Signals::TRACE);
        }

        return HttpEndpointResolver::create()->resolveToString($endpoint, Signals::TRACE);
    }

    private function getCompression(): string
    {
        return Configuration::has(Variables::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION) ?
            Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION) :
            Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_COMPRESSION, self::DEFAULT_COMPRESSION);
    }
}
