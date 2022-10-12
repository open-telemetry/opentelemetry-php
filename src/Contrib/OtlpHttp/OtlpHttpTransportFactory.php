<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpHttp;

use InvalidArgumentException;
use OpenTelemetry\API\Common\Signal\Signals;
use OpenTelemetry\Contrib\Otlp\OtlpTransportFactoryInterface;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\Contrib\Otlp\Protocols;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransport;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Otlp\HttpEndpointResolver;

class OtlpHttpTransportFactory implements TransportFactoryInterface, OtlpTransportFactoryInterface
{
    use EnvironmentVariablesTrait;

    private const DEFAULT_ENDPOINT = 'http://localhost:4318';
    private const DEFAULT_COMPRESSION = 'none';
    private const DEFAULT_PROTOCOL = Protocols::HTTP_PROTOBUF;
    private const DEFAULT_SIGNAL = Signals::TRACE;
    private static array $protocols = [
        Protocols::HTTP_PROTOBUF,
        Protocols::HTTP_JSON,
    ];
    private string $signal = self::DEFAULT_SIGNAL;
    private string $protocol = self::DEFAULT_PROTOCOL;

    public function create(
        string $endpoint = null,
        string $contentType = null,
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
        $endpoint = HttpEndpointResolver::create()->resolveToString($endpoint, $this->signal);

        $headers += $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_HEADERS) ?
            $this->getMapFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_HEADERS) :
            $this->getMapFromEnvironment(Env::OTEL_EXPORTER_OTLP_HEADERS);
        $headers += OtlpUtil::getUserAgentHeader();

        $compression ??= $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION) ?
            $this->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION, self::DEFAULT_COMPRESSION) :
            $this->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_COMPRESSION, self::DEFAULT_COMPRESSION);

        $protocol = $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL) ?
            $this->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL, $this->protocol) :
            $this->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_PROTOCOL, $this->protocol);

        if (!in_array($protocol, self::$protocols)) {
            throw new InvalidArgumentException(sprintf('Invalid OTLP Protocol "%s" specified', $protocol));
        }
        if ($compression === self::DEFAULT_COMPRESSION) {
            $compression = null;
        }
        $contentType = Protocols::contentType($protocol);

        return PsrTransportFactory::discover()->create($endpoint, $contentType, $headers, $compression);
    }

    public function withSignal(string $signal): TransportFactoryInterface
    {
        Signals::validate($signal);
        $this->signal = $signal;

        return $this;
    }

    public function withProtocol(string $protocol): TransportFactoryInterface
    {
        Protocols::validate($protocol);
        $this->protocol = $protocol;

        return $this;
    }
}
