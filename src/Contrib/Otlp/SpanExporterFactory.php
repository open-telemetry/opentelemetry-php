<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\API\Common\Signal\Signals;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Defaults;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Otlp\HttpEndpointResolver;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

class SpanExporterFactory
{
    use LogsMessagesTrait;

    private ?TransportFactoryInterface $transportFactory;

    private const DEFAULT_COMPRESSION = 'none';
    private const FACTORIES = [
        KnownValues::VALUE_GRPC => GrpcTransportFactory::class,
        KnownValues::VALUE_HTTP_PROTOBUF => OtlpHttpTransportFactory::class,
        KnownValues::VALUE_HTTP_JSON => OtlpHttpTransportFactory::class,
        KnownValues::VALUE_HTTP_NDJSON => OtlpHttpTransportFactory::class,
    ];

    public function __construct(?TransportFactoryInterface $transportFactory = null)
    {
        $this->transportFactory = $transportFactory;
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function fromEnvironment(): SpanExporterInterface
    {
        $transport = $this->buildTransport();

        return new SpanExporter($transport);
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    private function buildTransport(): TransportInterface
    {
        $protocol = $this->getProtocol();
        $contentType = Protocols::contentType($protocol);
        $endpoint = $this->getEndpoint($protocol);
        $headers = $this->getHeaders();
        $compression = $this->getCompression();

        if (!$this->transportFactory && !array_key_exists($protocol, self::FACTORIES)) {
            throw new \UnexpectedValueException('Unknown OTLP protocol: ' . $protocol);
        }
        $factoryClass = self::FACTORIES[$protocol];
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

    private function getHeaders(): array
    {
        $headers = Configuration::has(Variables::OTEL_EXPORTER_OTLP_TRACES_HEADERS) ?
            Configuration::getMap(Variables::OTEL_EXPORTER_OTLP_TRACES_HEADERS) :
            Configuration::getMap(Variables::OTEL_EXPORTER_OTLP_HEADERS);

        return $headers + OtlpUtil::getUserAgentHeader();
    }

    private function getCompression(): string
    {
        return Configuration::has(Variables::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION) ?
            Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION) :
            Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_COMPRESSION, self::DEFAULT_COMPRESSION);
    }
}
