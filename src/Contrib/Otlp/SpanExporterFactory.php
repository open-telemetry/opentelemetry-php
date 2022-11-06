<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\API\Common\Signal\Signals;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Configuration\Variables as Env;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Otlp\HttpEndpointResolver;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

class SpanExporterFactory
{
    private const DEFAULT_COMPRESSION = 'none';
    private const FACTORIES = [
        KnownValues::VALUE_GRPC => GrpcTransportFactory::class,
        KnownValues::VALUE_HTTP_PROTOBUF => OtlpHttpTransportFactory::class,
        KnownValues::VALUE_HTTP_JSON => OtlpHttpTransportFactory::class,
    ];

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
        $protocol = Configuration::has(Env::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL) ?
            Configuration::getEnum(Env::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL) :
            Configuration::getEnum(Env::OTEL_EXPORTER_OTLP_PROTOCOL);
        $contentType = Protocols::contentType($protocol);

        $endpoint = Configuration::has(Env::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT)
            ? Configuration::getString(Env::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT)
            : Configuration::getString(Env::OTEL_EXPORTER_OTLP_ENDPOINT);
        if ($protocol === Protocols::GRPC) {
            $endpoint .= OtlpUtil::method(Signals::TRACE);
        } else {
            $endpoint = HttpEndpointResolver::create()->resolveToString($endpoint, Signals::TRACE);
        }

        $headers = Configuration::has(Env::OTEL_EXPORTER_OTLP_TRACES_HEADERS) ?
            Configuration::getMap(Env::OTEL_EXPORTER_OTLP_TRACES_HEADERS) :
            Configuration::getMap(Env::OTEL_EXPORTER_OTLP_HEADERS);
        $headers += OtlpUtil::getUserAgentHeader();

        $compression = Configuration::has(Env::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION) ?
            Configuration::getEnum(Env::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION) :
            Configuration::getEnum(Env::OTEL_EXPORTER_OTLP_COMPRESSION, self::DEFAULT_COMPRESSION);

        $factoryClass = self::FACTORIES[$protocol];

        return (new $factoryClass())->create($endpoint, $contentType, $headers, $compression);
    }
}
