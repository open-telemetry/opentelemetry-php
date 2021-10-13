<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\ZipkinToNewrelic;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use InvalidArgumentException;
use OpenTelemetry\SDK\Trace;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Class ZipkinExporter - implements the export interface for data transfer via Zipkin protocol
 * @package OpenTelemetry\Exporter
 *
 * This is an experimental, non-supported exporter.
 * It will send PHP Otel trace data end to end across the internet to a functional backend.
 * Needs a license key to connect.  For a free account/key, go to: https://newrelic.com/signup/
 */
class Exporter implements Trace\SpanExporterInterface
{
    /**
     * @var string
     */
    private $endpointUrl;

    /**
     * @var string
     */
    private $licenseKey;

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
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    /**
    * @var string
    */
    private $name;

    public function __construct(
        $name,
        string $endpointUrl,
        string $licenseKey,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        SpanConverter $spanConverter = null
    ) {
        $parsedDsn = parse_url($endpointUrl);

        if (!is_array($parsedDsn)) {
            throw new InvalidArgumentException('Unable to parse provided DSN');
        }
        if (
            !isset($parsedDsn['scheme'])
            || !isset($parsedDsn['host'])
            || !isset($parsedDsn['path'])
        ) {
            throw new InvalidArgumentException('Endpoint should have scheme, host, port and path');
        }

        $this->name = $name;
        $this->endpointUrl = $endpointUrl;
        $this->licenseKey = $licenseKey;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->spanConverter = $spanConverter ?? new SpanConverter($name);
    }

    /** @inheritDoc */
    public function export(iterable $spans): int
    {
        if (!$this->running) {
            return Exporter::STATUS_FAILED_NOT_RETRYABLE;
        }

        if (empty($spans)) {
            return self::STATUS_SUCCESS;
        }

        $convertedSpans = [];
        foreach ($spans as $span) {
            $convertedSpans[] = $this->spanConverter->convert($span);
        }

        try {
            $body = $this->streamFactory->createStream(json_encode($convertedSpans));
            $request = $this->requestFactory
                ->createRequest('POST', $this->endpointUrl)
                ->withBody($body)
                ->withHeader('content-type', 'application/json')
                ->withAddedHeader('Api-Key', $this->licenseKey)
                ->withAddedHeader('Data-Format', 'zipkin')
                ->withAddedHeader('Data-Format-Version', '2');

            $response = $this->client->sendRequest($request);
        } catch (RequestExceptionInterface $e) {
            return self::STATUS_FAILED_NOT_RETRYABLE;
        } catch (NetworkExceptionInterface | ClientExceptionInterface $e) {
            return self::STATUS_FAILED_RETRYABLE;
        }

        if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500) {
            return self::STATUS_FAILED_NOT_RETRYABLE;
        }

        if ($response->getStatusCode() >= 500 && $response->getStatusCode() < 600) {
            return self::STATUS_FAILED_RETRYABLE;
        }

        return self::STATUS_SUCCESS;
    }

    /** @inheritDoc */
    public function shutdown(): bool
    {
        if (!$this->running) {
            return false;
        }

        $this->running = false;

        return true;
    }

    public function forceFlush(): bool
    {
        return true;
    }

    /** @inheritDoc */
    public static function fromConnectionString(string $endpointUrl, string $name, $args)
    {
        if ($args == false) {
            throw new Exception('Invalid license key.');
        }
        $factory = new HttpFactory();
        $exporter = new Exporter(
            $name,
            $endpointUrl,
            $args,
            new Client(),
            $factory,
            $factory
        );

        return $exporter;
    }
}
