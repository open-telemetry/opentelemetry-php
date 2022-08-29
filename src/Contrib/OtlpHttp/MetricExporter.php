<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpHttp;

use OpenTelemetry\Contrib\Otlp\MetricConverter;
use Opentelemetry\Proto\Collector\Metrics\V1\ExportMetricsServiceResponse;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Throwable;

final class MetricExporter implements MetricExporterInterface
{
    use LogsMessagesTrait;

    private TransportInterface $transport;
    private $temporality;

    /**
     * @param string|Temporality|null $temporality
     */
    public function __construct(TransportInterface $transport, $temporality)
    {
        $this->transport = $transport;
        $this->temporality = $temporality;
    }

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
        return new self(
            (new PsrTransportFactory($client, $requestFactory, $streamFactory))->create(
                $endpoint,
                $headers,
                $compression,
                $retryDelay,
                $maxRetries,
            ),
            $temporality,
        );
    }

    public function temporality(MetricMetadataInterface $metric)
    {
        return $this->temporality ?? $metric->temporality();
    }

    public function export(iterable $batch): bool
    {
        return $this->transport
            ->send((new MetricConverter())->convert($batch)->serializeToString(), 'application/x-protobuf')
            ->map(static function (string $payload): bool {
                $serviceResponse = new ExportMetricsServiceResponse();
                $serviceResponse->mergeFromString($payload);

                return true;
            })
            ->catch(static function (Throwable $throwable): bool {
                self::logError('Export failure', ['exception' => $throwable]);

                return false;
            })
            ->await();
    }

    public function shutdown(): bool
    {
        return $this->transport->shutdown();
    }

    public function forceFlush(): bool
    {
        return $this->transport->forceFlush();
    }
}
