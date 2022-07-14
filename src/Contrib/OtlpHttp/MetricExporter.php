<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpHttp;

use function array_filter;
use OpenTelemetry\Contrib\Otlp\MetricConverter;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class MetricExporter implements MetricExporterInterface
{
    use LogsMessagesTrait;

    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    private string $endpoint;
    private array $headers;
    private ?string $compression;
    private int $retryDelay;
    private int $maxRetries;
    private $temporality;

    private bool $closed = false;

    /**
     * @param array<string|string[]> $headers
     * @param string|Temporality|null $temporality
     */
    private function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $endpoint,
        array $headers,
        ?string $compression,
        int $retryDelay,
        int $maxRetries,
        $temporality
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->endpoint = $endpoint;
        $this->headers = $headers;
        $this->compression = $compression;
        $this->retryDelay = $retryDelay;
        $this->maxRetries = $maxRetries;
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
            $client,
            $requestFactory,
            $streamFactory,
            $endpoint,
            $headers,
            $compression,
            $retryDelay * 1000,
            $maxRetries,
            $temporality,
        );
    }

    public function temporality(MetricMetadataInterface $metric)
    {
        return $this->temporality ?? $metric->temporality();
    }

    public function export(iterable $batch): bool
    {
        if ($this->closed) {
            return false;
        }

        $payload = (new MetricConverter())->convert($batch)->serializeToString();
        $request = $this->requestFactory
            ->createRequest('POST', $this->endpoint)
            ->withHeader('Content-Type', 'application/x-protobuf')
        ;

        if ($this->compression && $encoder = self::encoder($this->compression)) {
            $payload = $encoder($payload);
            $request = $request->withHeader('Content-Encoding', $this->compression);
        }

        foreach ($this->headers as $header => $value) {
            $request = $request->withAddedHeader((string) $header, $value);
        }

        for ($retries = 0;;) {
            $statusCode = null;
            $e = null;

            $request = $request->withBody($this->streamFactory->createStream($payload));

            try {
                $response = $this->client->sendRequest($request);

                $statusCode = $response->getStatusCode();
                if ($statusCode >= 200 && $statusCode < 300) {
                    return true;
                }
                if ($statusCode >= 400 && $statusCode < 500 && $statusCode !== 408) {
                    break;
                }
            } catch (RequestExceptionInterface $e) {
                break;
            } catch (ClientExceptionInterface $e) {
            }

            if (++$retries >= $this->maxRetries) {
                break;
            }

            $wait = $this->retryDelay << $retries - 1;
            /** @psalm-suppress InvalidArgument */
            usleep(rand($wait >> 1, $wait));
        }

        /** @psalm-suppress PossiblyUndefinedVariable */
        self::logError('Metric export failed', [
            'exception' => $e,
            'status_code' => $statusCode,
        ]);

        return false;
    }

    public function shutdown(): bool
    {
        if ($this->closed) {
            return false;
        }

        $this->closed = true;

        return true;
    }

    public function forceFlush(): bool
    {
        return !$this->closed;
    }

    private static function encoder(string $encoding): ?callable
    {
        static $encoders;
        $encoders ??= array_filter([
            'gzip' => 'gzencode',
        ], 'function_exists');

        return $encoders[$encoding] ?? null;
    }
}
