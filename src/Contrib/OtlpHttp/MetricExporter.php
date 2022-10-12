<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpHttp;

use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class MetricExporter
{
    /**
     * @param string $endpoint endpoint to connect to
     * @param array<string|string[]> $headers headers to set
     * @param string|null $compression compression to apply
     * @param int $retryDelay retry delay in milliseconds
     * @param int $maxRetries maximum number of retries
     * @param string|Temporality|null $temporality temporality to use
     */
    public static function create(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $endpoint = 'https://localhost:4318/v1/metrics',
        array $headers = [],
        ?string $compression = null,
        int $retryDelay = 100,
        int $maxRetries = 1,
        $temporality = null
    ): MetricExporterInterface {
        return new \OpenTelemetry\Contrib\Otlp\MetricExporter(
            (new PsrTransportFactory($client, $requestFactory, $streamFactory))->create(
                $endpoint,
                'application/x-protobuf',
                $headers,
                $compression,
                $retryDelay,
                $maxRetries,
            ),
            $temporality,
        );
    }
}
