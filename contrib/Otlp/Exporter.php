<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use GuzzleHttp\Psr7\Request;
use Http\Adapter\Guzzle7\Client;
use OpenTelemetry\Sdk\Trace;
use OpenTelemetry\Trace as API;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;

class Exporter implements Trace\Exporter
{
    /**
     * @var string
     */
    private $endpointURL;

    /**
     * @var string
     */
    private $protocol;

    /**
     * @var string
     */
    private $insecure;

    /**
     * @var string
     */
    private $certificateFile;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var string
     */
    private $compression;

    /**
     * @var int
     */
    private $timeout;
    /**
     * @var SpanConverter
     */
    private $spanConverter;

    /**
    * @var bool
    */
    private $running = true;

    /**
     * @var ClientInterface
     */

    private $client;

    /**
     * Exporter constructor.
     * @param string $serviceName
     */
    public function __construct(
        $serviceName,
        ClientInterface $client=null
    ) {

        // Set default values based on presence of env variable
        $this->endpointURL = getenv('OTEL_EXPORTER_OTLP_ENDPOINT') ?: 'localhost:55681';
        $this->protocol = getenv('OTEL_EXPORTER_OTLP_PROTOCOL') ?: 'json';
        $this->insecure = getenv('OTEL_EXPORTER_OTLP_INSECURE') ?: 'false';
        $this->certificateFile = getenv('OTEL_EXPORTER_OTLP_CERTIFICATE') ?: 'none';
        $this->headers[] = getenv('OTEL_EXPORTER_OTLP_HEADERS') ?: 'none';
        $this->compression = getenv('OTEL_EXPORTER_OTLP_COMPRESSION') ?: 'none';
        $this->timeout =(int) getenv('OTEL_EXPORTER_OTLP_TIMEOUT') ?: 10;

        $this->client = $client ?? $this->createDefaultClient();
        $this->spanConverter = new SpanConverter($serviceName);
    }

    /**
     * Exports the provided Span data via the OTLP protocol
     *
     * @param iterable<API\Span> $spans Array of Spans
     * @return int return code, defined on the Exporter interface
     */
    public function export(iterable $spans): int
    {
        if (!$this->running) {
            return Exporter::FAILED_NOT_RETRYABLE;
        }

        if (empty($spans)) {
            return Trace\Exporter::SUCCESS;
        }

        $convertedSpans = [];
        foreach ($spans as $span) {
            array_push($convertedSpans, $this->spanConverter->convert($span));
        }

        try {
            $json = json_encode($convertedSpans);

            $this->headers[] = '';

            if ($this->protocol == 'json') {
                $this->headers = ['content-type' => 'application/json'];
            }

            $request = new Request('POST', $this->endpointURL, $this->headers, $json);
            $response = $this->client->sendRequest($request);
        } catch (RequestExceptionInterface $e) {
            return Trace\Exporter::FAILED_NOT_RETRYABLE;
        } catch (NetworkExceptionInterface | ClientExceptionInterface $e) {
            return Trace\Exporter::FAILED_RETRYABLE;
        }

        if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500) {
            return Trace\Exporter::FAILED_NOT_RETRYABLE;
        }

        if ($response->getStatusCode() >= 500 && $response->getStatusCode() < 600) {
            return Trace\Exporter::FAILED_RETRYABLE;
        }

        return Trace\Exporter::SUCCESS;
    }

    public function shutdown(): void
    {
        $this->running = false;
    }

    protected function createDefaultClient(): ClientInterface
    {
        return Client::createWithConfig([
            'timeout' => 30,
        ]);
    }
}
