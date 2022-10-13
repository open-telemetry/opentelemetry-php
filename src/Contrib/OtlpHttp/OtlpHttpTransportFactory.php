<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpHttp;

use OpenTelemetry\API\Common\Signal\Signals;
use OpenTelemetry\Contrib\Otlp\OtlpTransportFactoryInterface;
use OpenTelemetry\Contrib\Otlp\Protocols;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransport;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;

class OtlpHttpTransportFactory implements OtlpTransportFactoryInterface
{
    use EnvironmentVariablesTrait;

    private const DEFAULT_COMPRESSION = 'none';
    public function create(
        string $endpoint,
        string $contentType,
        array $headers = [],
        $compression = null,
        float $timeout = 10.,
        int $retryDelay = 100,
        int $maxRetries = 3,
        ?string $cacert = null,
        ?string $cert = null,
        ?string $key = null
    ): PsrTransport {
        if ($compression === self::DEFAULT_COMPRESSION) {
            $compression = null;
        }

        return PsrTransportFactory::discover()->create($endpoint, $contentType, $headers, $compression);
    }

    public function withSignal(string $signal): OtlpTransportFactoryInterface
    {
        return $this;
    }

    public function withProtocol(string $protocol): OtlpTransportFactoryInterface
    {
        return $this;
    }
}
