<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpHttp;

use InvalidArgumentException;
use OpenTelemetry\API\Common\Signal\Signals;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Common\Environment\KnownValues;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransport;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Otlp\HttpEndpointResolver;

class OtlpHttpTransportFactory implements TransportFactoryInterface
{
    use EnvironmentVariablesTrait;

    private const DEFAULT_ENDPOINT = 'http://localhost:4318';
    private const DEFAULT_COMPRESSION = 'none';
    private const DEFAULT_OTLP_PROTOCOL = KnownValues::VALUE_HTTP_PROTOBUF;
    private static array $protocols = [
        KnownValues::VALUE_HTTP_PROTOBUF,
    ];

    public function create(
        string $endpoint = null,
        array $headers = [],
        $compression = null,
        float $timeout = 10.,
        int $retryDelay = 100,
        int $maxRetries = 3,
        ?string $cacert = null,
        ?string $cert = null,
        ?string $key = null
    ): PsrTransport {
        $endpoint ??= $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT)
            ? $this->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT)
            : $this->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_ENDPOINT, self::DEFAULT_ENDPOINT);
        $endpoint = HttpEndpointResolver::create()->resolveToString($endpoint, Signals::TRACE);

        $headers += $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_HEADERS) ?
            $this->getMapFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_HEADERS) :
            $this->getMapFromEnvironment(Env::OTEL_EXPORTER_OTLP_HEADERS);
        $headers += OtlpUtil::getUserAgentHeader();

        $compression ??= $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION) ?
            $this->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION, self::DEFAULT_COMPRESSION) :
            $this->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_COMPRESSION, self::DEFAULT_COMPRESSION);

        $protocol = $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL) ?
            $this->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL, self::DEFAULT_OTLP_PROTOCOL) :
            $this->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_PROTOCOL, self::DEFAULT_OTLP_PROTOCOL);

        if (!in_array($protocol, self::$protocols)) {
            throw new InvalidArgumentException(sprintf('Invalid OTLP Protocol "%s" specified', $protocol));
        }
        if ($compression === self::DEFAULT_COMPRESSION) {
            $compression = null;
        }

        return PsrTransportFactory::discover()->create($endpoint, $headers, $compression);
    }
}
