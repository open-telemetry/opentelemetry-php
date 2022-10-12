<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpHttp;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use InvalidArgumentException;
use OpenTelemetry\API\Common\Signal\Signals;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Otlp\HttpEndpointResolver;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use function sprintf;

class Exporter
{
    private const DEFAULT_ENDPOINT = 'https://localhost:4318';
    private const DEFAULT_COMPRESSION = 'none';
    private const OTLP_PROTOCOL = 'http/protobuf';

    /**
     * @internal
     *
     * @psalm-return TransportInterface<"application/x-protobuf">
     */
    public static function createTransport(
        TransportFactoryInterface $transportFactory
    ): TransportInterface {
        $env = new class() {
            use EnvironmentVariablesTrait;
        };

        $endpoint = $env->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT)
            ? $env->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT)
            : HttpEndpointResolver::create()->resolveToString(
                $env->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_ENDPOINT, self::DEFAULT_ENDPOINT),
                Signals::TRACE,
            );

        $headers = $env->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_HEADERS) ?
            $env->getMapFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_HEADERS) :
            $env->getMapFromEnvironment(Env::OTEL_EXPORTER_OTLP_HEADERS);

        $compression = $env->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION) ?
            $env->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION, self::DEFAULT_COMPRESSION) :
            $env->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_COMPRESSION, self::DEFAULT_COMPRESSION);

        $protocol = $env->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL) ?
            $env->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL, self::OTLP_PROTOCOL) :
            $env->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_PROTOCOL, self::OTLP_PROTOCOL);

        if ($protocol !== self::OTLP_PROTOCOL) {
            throw new InvalidArgumentException(sprintf('Invalid OTLP Protocol "%s" specified', $protocol));
        }
        if ($compression === self::DEFAULT_COMPRESSION) {
            $compression = null;
        }

        return $transportFactory->create(
            $endpoint,
            'application/x-protobuf',
            $headers,
            $compression,
            10,
            100,
            1,
        );
    }

    public static function fromConnectionString(string $endpointUrl = null, string $name = null, $args = null): SpanExporterInterface
    {
        return new SpanExporter(self::createTransport(new PsrTransportFactory(
            Psr18ClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory(),
        )));
    }
}
