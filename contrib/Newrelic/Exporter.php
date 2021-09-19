<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Newrelic;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use InvalidArgumentException;
use OpenTelemetry\Sdk\Trace;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Class NewrelicExporter - implements the export interface for data transfer via Newrelic protocol
 * @package OpenTelemetry\Exporter
 *
 * This is an experimental, non-supported exporter.
 * It will send PHP Otel trace data end to end across the internet to a functional backend.
 * Needs a license key to connect.  For a free account/key, go to: https://newrelic.com/signup/
 */
class Exporter implements Trace\Exporter
{
    private const DATA_FORMAT = 'newrelic';
    private const DATA_FORMAT_VERSION_DEFAULT = '1';

    /**
     * @var string
     */
    private $endpointUrl;

    /**
     * @var string
     */
    private $licenseKey;

    /**
     * @var string
     */
    private $name;

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

    private $dataFormatVersion;

    public function __construct(
        $name,
        string $endpointUrl,
        string $licenseKey,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        SpanConverter $spanConverter = null,
        string $dataFormatVersion = Exporter::DATA_FORMAT_VERSION_DEFAULT
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
        $this->dataFormatVersion = $dataFormatVersion;
    }

    /** @inheritDoc */
    public function export(iterable $spans): int
    {
        if (!$this->running) {
            return Trace\Exporter::FAILED_NOT_RETRYABLE;
        }

        if (empty($spans)) {
            return Trace\Exporter::SUCCESS;
        }

        $convertedSpans = [];
        foreach ($spans as $span) {
            $convertedSpans[] = $this->spanConverter->convert($span);
        }
        $commonAttributes = ['attributes' => [ 'service.name' => $this->name,
                                               'host' => $this->endpointUrl, ]];
        $payload = [[ 'common' => $commonAttributes,
                     'spans' => $convertedSpans, ]];

        try {
            $body = $this->streamFactory->createStream(json_encode($payload));
            $request = $this->requestFactory
                ->createRequest('POST', $this->endpointUrl)
                ->withBody($body)
                ->withHeader('content-type', 'application/json')
                ->withAddedHeader('Api-Key', $this->licenseKey)
                ->withAddedHeader('Data-Format', self::DATA_FORMAT)
                ->withAddedHeader('Data-Format-Version', $this->dataFormatVersion);

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

    public static function fromConnectionString(string $endpointUrl, string $name, $args)
    {
        if ($args == false) {
            throw new Exception('Invalid license key.');
        }
        $factory = new HttpFactory();

        return new Exporter(
            $name,
            $endpointUrl,
            $args,
            new Client(),
            $factory,
            $factory
        );
    }
}
